<?php

namespace App\Models;

use PDO;
use DateTime;

class Expense extends \Core\Model
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
                $sql = 'INSERT INTO expenses SELECT NULL, :user_id, e.id, p.id, :amount, :date, NULL FROM expenses_cattegories_assigned_to_users as e, payment_method_assigned_to_user as p WHERE e.name = :cattegory AND e.user_id = :user_id AND p.name = :method AND p.user_id = :user_id';
                
                $stmt = $db->prepare($sql);
            }
            else
            {
                $sql = 'INSERT INTO expenses SELECT NULL, :user_id, e.id, p.id, :amount, :date, :comment FROM expenses_cattegories_assigned_to_users as e, payment_method_assigned_to_user as p WHERE e.name = :cattegory AND e.user_id = :user_id AND p.name = :method AND p.user_id = :user_id';
                
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':comment', $this->comment, PDO::PARAM_STR);
            }
            
            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
            $stmt->bindValue(':date', $this->date, PDO::PARAM_STR);
            $stmt->bindValue(':cattegory', $this->cattegory, PDO::PARAM_STR);
            $stmt->bindValue(':method', $this->method, PDO::PARAM_STR);

            return $stmt->execute();
        }
        else return false;
    }

    protected function validate()
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

    public static function getExpensesOfCurrentUser($loggedId, $beginDate, $endDate)
    {   
        $db = static::getDB();

        $stmt = $db -> prepare('SELECT expenses.id as ID, amount, c.name as cattegory, p.name as method, date_of_expense, expense_comment FROM expenses_cattegories_assigned_to_users as c, payment_method_assigned_to_user as p, expenses WHERE expenses.user_id = :user_id AND expense_cattegory_assigned_to_user_id = c.id AND payment_method_assigned_to_user_id = p.id AND date_of_expense BETWEEN :begin AND :end ORDER BY date_of_expense DESC, amount DESC');
        $stmt -> bindValue(':user_id', $loggedId, PDO::PARAM_INT);
        $stmt -> bindValue(':begin', $beginDate, PDO::PARAM_STR);
        $stmt -> bindValue(':end', $endDate, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        return $stmt -> fetchAll();
    }

    public static function getSumsOfExpensesAccordingToCattegories($loggedId, $beginDate, $endDate)
    {
        $db = static::getDB();

        $stmt = $db -> prepare('SELECT name as kategoria, SUM(amount) as wydatek FROM expenses, expenses_cattegories_assigned_to_users as c WHERE expenses.user_id = :user_id AND c.id = expense_cattegory_assigned_to_user_id AND date_of_expense BETWEEN :begin AND :end GROUP BY expense_cattegory_assigned_to_user_id ORDER BY wydatek DESC');
        $stmt -> bindValue(':user_id', $loggedId, PDO::PARAM_INT);
        $stmt -> bindValue(':begin', $beginDate, PDO::PARAM_STR);
        $stmt -> bindValue(':end', $endDate, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        return $stmt -> fetchAll();
    }

    public static function getCountOfExpensesBelongToGivenPayMethod($methodId)
    {
        $db = static::getDB();

        $sql = 'SELECT COUNT(id) as countOfMethods FROM expenses WHERE payment_method_assigned_to_user_id = :methodId';

        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':methodId', $methodId, PDO::PARAM_INT);

        //$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        return $stmt -> fetch();
    }

    public static function replaceRemovedMethod($methodId)
    {
        $db = static::getDB();

        $sql = 'UPDATE expenses
                SET payment_method_assigned_to_user_id = 
                (SELECT id FROM payment_method_assigned_to_user WHERE user_id = :user_id AND name = "inna" LIMIT 1)
        
                WHERE payment_method_assigned_to_user_id = :method_id ';

        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':method_id', $methodId, PDO::PARAM_INT);
        $stmt -> bindValue(':user_id', $_SESSION['user_id'] , PDO::PARAM_INT);
    
        return $stmt -> execute();
    }

    public static function getCountOfExpensesBelongToGivenCattegory($cattegoryId)
    {
        $db = static::getDB();

        $sql = 'SELECT COUNT(id) as countOfExpenses FROM expenses WHERE expense_cattegory_assigned_to_user_id = :cattegoryId';

        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':cattegoryId', $cattegoryId, PDO::PARAM_INT);

        //$stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        return $stmt -> fetch();
    }

    public static function replaceRemovedCattegory($cattegoryId)
    {
        $db = static::getDB();

        $sql = 'UPDATE expenses
                SET expense_cattegory_assigned_to_user_id = 
                (SELECT id FROM expenses_cattegories_assigned_to_users WHERE user_id = :user_id AND name = "inne")
        
                WHERE expense_cattegory_assigned_to_user_id = :cattegory_id ';

        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':cattegory_id', $cattegoryId, PDO::PARAM_INT);
        $stmt -> bindValue(':user_id', $_SESSION['user_id'] , PDO::PARAM_INT);
    
        return $stmt -> execute();
    }

    public static function findById ($id)
    {
        $db = static::getDB();
        
        $sql = 'SELECT expenses.id as id, expenses.user_id as user_id, cattegories.name as cattegory, methods.name as method, amount, date_of_expense as date, expense_comment as comment FROM expenses, expenses_cattegories_assigned_to_users as cattegories, payment_method_assigned_to_user as methods WHERE expenses.id = :id AND expense_cattegory_assigned_to_user_id = cattegories.id AND payment_method_assigned_to_user_id = methods.id';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();

    }

    public function destroy()
    {
        $sql = 'DELETE FROM expenses WHERE id = :id';
        
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
                $stmt = $db->prepare('UPDATE expenses
                SET amount = :amount,
                    date_of_expense = :date,
                    payment_method_assigned_to_user_id = (SELECT id FROM payment_method_assigned_to_user WHERE name = :method),
                    expense_cattegory_assigned_to_user_id = (SELECT id FROM expenses_cattegories_assigned_to_users WHERE name = :cattegory),
                    expense_comment = NULL
                WHERE id = :id');
            }
            else
            {
                $stmt = $db->prepare('UPDATE expenses
                SET amount = :amount,
                    date_of_expense = :date,
                    payment_method_assigned_to_user_id = (SELECT id FROM payment_method_assigned_to_user WHERE name = :method),
                    expense_cattegory_assigned_to_user_id = (SELECT id FROM expenses_cattegories_assigned_to_users WHERE name = :cattegory),
                    expense_comment = :comment
                WHERE id = :id');
                $stmt->bindValue(':comment', $this->comment, PDO::PARAM_STR);
            }
            
            $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
            $stmt->bindValue(':date', $this->date, PDO::PARAM_STR);
            $stmt->bindValue(':method', $this->method, PDO::PARAM_STR);
            $stmt->bindValue(':cattegory', $this->cattegory, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        }
        else return false;
    }

    public static function deleteEveryExpenses($loggedId)
    {
        $sql = 'DELETE FROM expenses WHERE user_id = :user_id';

        $db = static::getDB();

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $loggedId, PDO::PARAM_INT);

        return $stmt->execute();

    }
}