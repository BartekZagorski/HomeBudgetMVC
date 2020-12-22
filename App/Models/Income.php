<?php

namespace App\Models;

use PDO;
use DateTime;

class Income extends \Core\Model
{
    public $errors = [];

    public function __construct($data = [])
    {
        foreach($data as $key=>$value)
        {
            $this->$key = $value;
        }
    }

    public function save()
    {
        $this->validate();
        
        if (empty($this->errors))
        {
            $db = static::getDB();

            if (!isset($this->comment))
            {
                $stmt = $db->prepare('INSERT INTO incomes SELECT NULL, :user_id, id, :amount, :date, NULL FROM incomes_cattegories_assigned_to_users WHERE name = :cattegory AND user_id = :user_id');
            }
            else
            {
                $stmt = $db->prepare('INSERT INTO incomes SELECT NULL, :user_id, id, :amount, :date, :comment FROM incomes_cattegories_assigned_to_users WHERE name = :cattegory AND user_id = :user_id');
                $stmt->bindValue(':comment', $this->comment, PDO::PARAM_STR);
            }
            
            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
            $stmt->bindValue(':date', $this->date, PDO::PARAM_STR);
            $stmt->bindValue(':cattegory', $this->cattegory, PDO::PARAM_STR);

            return $stmt->execute();
        }
        else return false;
    }

    public function validate()
    {
        //amount validation

        $this->amount = str_replace("," , "." , $this->amount);

        //check whether $amountGiven is a numeric

        if (!is_numeric($this->amount))
        {
            $this->errors['amount'] = "Podana wartość nie jest liczbą";
        }
        //let amount take values between 0 and 999 999.99
        else 
        {
            $this->amount = number_format($this->amount, 2, ".", "");
            if ($this->amount<0 || $this->amount > 999999.99)
            {
                $this->errors['amount'] = "Podana wartość nie jest liczbą z przedziału od 0 do 999,999.99";
            }
        }
        //end of amount tests

        //date tests

        if (static::validateDate($this->date))
        {
            $dateGiven = DateTime::createFromFormat('Y-m-d', $this->date);

            $lastDayOfThisMonth = new DateTime('last day of this month');
            $lastDayOfThisMonth -> modify('last day of this month');

            // let day take values between 2000-01-01 and last day of current month

            if ($dateGiven < DateTime::createFromFormat('Y-m-d', '2000-01-01') || $dateGiven > $lastDayOfThisMonth)
            {
                $this->errors['date'] = "Podana data nie jest z przedziału od 01-01-2000 do ".$lastDayOfThisMonth->format('d-m-Y');
            }  
        }
        else
        {
            $this->errors['date'] = "Podana data jest nieprawidłowa. Podaj datę w formacie rrrr-mm-dd";
        }
        //end of date tests

        //comment test

        //let comment has less than or equal 100 chars

        if (isset($this->comment) && strlen($this->comment)>100)
        {
            $this->errors['comment'] = "komentarz nie może mieć nie więcej niż 100 znaków. Aktualna liczba znaków wynosi: ".strlen($this->comment);
        }
    }

    protected static function validateDate ($date, $format='Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public static function getIncomesOfCurrentUser($loggedId, $beginDate, $endDate)
    {   
        $db = static::getDB();

        $stmt = $db -> prepare('SELECT incomes.id as ID, amount, name, date_of_income, income_comment FROM incomes_cattegories_assigned_to_users as c, incomes WHERE incomes.user_id = :user_id AND income_cattegory_assigned_to_user_id = c.id AND date_of_income BETWEEN :begin AND :end ORDER BY date_of_income DESC, amount DESC');
        $stmt -> bindValue(':user_id', $loggedId, PDO::PARAM_INT);
        $stmt -> bindValue(':begin', $beginDate, PDO::PARAM_STR);
        $stmt -> bindValue(':end', $endDate, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        return $stmt -> fetchAll();
    }

    public static function getSumsOfIncomesAccordingToCattegories($loggedId, $beginDate, $endDate)
    {
        $db = static::getDB();

        $stmt = $db -> prepare('SELECT name as kategoria, SUM(amount) as przychód FROM incomes, incomes_cattegories_assigned_to_users as c WHERE incomes.user_id = :user_id AND c.id = income_cattegory_assigned_to_user_id AND date_of_income BETWEEN :begin AND :end GROUP BY income_cattegory_assigned_to_user_id ORDER BY przychód DESC');
        $stmt -> bindValue(':user_id', $loggedId, PDO::PARAM_INT);
        $stmt -> bindValue(':begin', $beginDate, PDO::PARAM_STR);
        $stmt -> bindValue(':end', $endDate, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        return $stmt -> fetchAll();
    }

    public static function getCountOfIncomesBelongToGivenCattegory($cattegoryId)
    {
        $db = static::getDB();

        $sql = 'SELECT COUNT(id) as countOfIncomes FROM incomes WHERE income_cattegory_assigned_to_user_id = :cattegoryId';

        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':cattegoryId', $cattegoryId, PDO::PARAM_INT);

        //$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        return $stmt -> fetch();
    }

    public static function replaceRemovedCattegory($cattegoryId)
    {
        $db = static::getDB();

        $sql = 'UPDATE incomes
                SET income_cattegory_assigned_to_user_id = 
                (SELECT id FROM incomes_cattegories_assigned_to_users WHERE user_id = :user_id AND name = "inne")
        
                WHERE income_cattegory_assigned_to_user_id = :cattegory_id ';

        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':cattegory_id', $cattegoryId, PDO::PARAM_INT);
        $stmt -> bindValue(':user_id', $_SESSION['user_id'] , PDO::PARAM_INT);
    
        return $stmt -> execute();
    }

    public static function findById ($id)
    {
        $db = static::getDB();
        
        $sql = 'SELECT incomes.id as id, incomes.user_id as user_id, cattegories.name as cattegory, amount, date_of_income as date, income_comment as comment  FROM incomes, incomes_cattegories_assigned_to_users as cattegories WHERE incomes.id = :id AND income_cattegory_assigned_to_user_id = cattegories.id';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();

    }

    public function destroy()
    {
        $sql = 'DELETE FROM incomes WHERE id = :id';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt -> execute();
    }

    public function update()
    {
        $this->validate();
        
        if (empty($this->errors))
        {
            
            $db = static::getDB();

            if (!isset($this->comment))
            {
                $stmt = $db->prepare('UPDATE incomes
                SET amount = :amount,
                    date_of_income = :date,
                    income_cattegory_assigned_to_user_id = (SELECT id FROM incomes_cattegories_assigned_to_users WHERE name = :cattegory),
                    income_comment = NULL
                WHERE id = :id');
            }
            else
            {
                $stmt = $db->prepare('UPDATE incomes
                SET amount = :amount,
                    date_of_income = :date,
                    income_cattegory_assigned_to_user_id = (SELECT id FROM incomes_cattegories_assigned_to_users WHERE name = :cattegory),
                    income_comment = :comment
                WHERE id = :id');
                $stmt->bindValue(':comment', $this->comment, PDO::PARAM_STR);
            }
            
            $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
            $stmt->bindValue(':date', $this->date, PDO::PARAM_STR);
            $stmt->bindValue(':cattegory', $this->cattegory, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        }
        else return false;
    }

    public static function deleteEveryIncomes($loggedId)
    {
        $sql = 'DELETE FROM incomes WHERE user_id = :user_id';

        $db = static::getDB();

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $loggedId, PDO::PARAM_INT);

        return $stmt->execute();

    }
}

