<?php

namespace App\Models;

use PDO;
use DateTime;

class ExpenseCattegory extends \Core\Model
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

            if (isset($this->is_set_limit))
            {
                $sql = 'INSERT INTO expenses_cattegories_assigned_to_users(user_id, name, is_set_limit, cattegory_limit) VALUES (:user_id, :name, :is_set_limit, :cattegory_limit)';
            }
            else
            {
                $sql = 'INSERT INTO expenses_cattegories_assigned_to_users(user_id, name) VALUES (:user_id, :name)';
            }

            $stmt = $db->prepare($sql);

            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);

            if (isset($this->is_set_limit))
            {
                $stmt->bindValue(':is_set_limit', 1, PDO::PARAM_BOOL);
                $stmt->bindValue(':cattegory_limit', $this->cattegory_limit, PDO::PARAM_STR);
            }

            return $stmt -> execute();
        }
        else return false;
    }

    public function update()
    {
        $this->validate();

        if (empty($this->errors))
        {   
            $sql = 'UPDATE expenses_cattegories_assigned_to_users
                    SET name = :name,
                    is_set_limit = :check,
                    cattegory_limit = :cattegory_limit
                    WHERE id = :id';

            $db = static::getDB();

            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':check', $this->is_set_limit, PDO::PARAM_BOOL);
            if ($this->is_set_limit)
            {
                $stmt->bindValue(':cattegory_limit', $this->cattegory_limit, PDO::PARAM_STR);
            }
            else
            {
                $stmt->bindValue(':cattegory_limit', null, PDO::PARAM_NULL);
            }
            

            return $stmt -> execute();
        }
        else return false;
    }

    protected function validate()
    {
        $this->name = mb_strtolower($this->name, "UTF-8"); // not strtolower cause of wrongfully encoding polish chars

        if (strlen($this->name)>50)
        {
            $this->errors['name'] = 'Nazwa kategorii może mieć maksymalnie 50 znaków';
        }
        else if (!preg_match_all('/^[a-ząęćżźńłóś\d]+\s?([a-ząęćżźńłóś\d]+\s?){0,}[a-ząęćżźńłóś\d]+$/i', $this->name))
        {
            $this->errors['name'] = 'Nazwa kategorii musi składać się z ciągów liter i cyfr oddzielonych pojedynczymi spacjami';
        }
        else if (static::cattegoryExist($this->name, $this->id ?? null))
        {
            $this->errors['name'] = 'Podana nazwa kategorii jest już zajęta';
        }

        if (isset($this->is_set_limit) && $this->is_set_limit)
        {
            //cattegory limit validation

            $this->cattegory_limit = str_replace("," , "." , $this->cattegory_limit);

            //check whether $cattegory_limit Given is a numeric

            if (!is_numeric($this->cattegory_limit))
            {
                $this->errors['cattegory_limit'] = "Podana wartość nie jest liczbą ".$this->check;
            }
            //let cattegory_limit take values between 0 and 999 999.99
            else 
            {
                $this->cattegory_limit = number_format($this->cattegory_limit, 2, ".", "");
                if ($this->cattegory_limit<0 || $this->cattegory_limit > 999999.99)
                {
                    $this->errors['cattegory_limit'] = "Podana wartość nie jest liczbą z przedziału od 0 do 999,999.99";
                }
            }
            //end of cattegory_limit tests
        }
    }

    public static function cattegoryExist($name, $ignore_id = null)
    {
        $cattegory = static::findByName($name);

        if ($cattegory) 
        {
            if ($cattegory->id != $ignore_id)
            {
                return true;
            }
        }
        else return false;
    }

    public static function findByName($name)
    {
        $sql = 'SELECT * FROM expenses_cattegories_assigned_to_users WHERE user_id = :user_id AND name = :name';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public static function getExpenseCattegoriesAssignedToUser($loggedId)
    {
        $sql = 'SELECT * FROM `expenses_cattegories_assigned_to_users` WHERE user_id = :loggedID ORDER BY name != "inne" DESC, id ASC';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':loggedID', $loggedId, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        return $stmt -> fetchAll();
    }

    public function destroy()
    {
        $sql = 'DELETE FROM expenses_cattegories_assigned_to_users WHERE id = :id';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt -> execute();
    }

    public static function getSumOfAmountInRequestedMonth($date, $cattegoryName, $ignore_id = null)
    {
        $givenDate = new DateTime($date);
        $firstDayOfMonth = $givenDate->format('Y-m-1');
        $lastDayOfMonth = $givenDate->format('Y-m-t');

        $sql = 'SELECT SUM(amount) as sum FROM expenses, expenses_cattegories_assigned_to_users as cattegories WHERE expenses.expense_cattegory_assigned_to_user_id = cattegories.id AND expenses.id != :ignore_id AND cattegories.name = :name AND cattegories.user_id = :user_id AND date_of_expense BETWEEN :begin AND :end';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt -> bindValue(':ignore_id', $ignore_id, PDO::PARAM_INT);
        $stmt -> bindValue(':name', $cattegoryName, PDO::PARAM_STR);
        $stmt -> bindValue(':begin', $firstDayOfMonth, PDO::PARAM_STR);
        $stmt -> bindValue(':end', $lastDayOfMonth, PDO::PARAM_STR);

        $stmt -> execute();

        $sumOfAmounts = $stmt -> fetch();
        $sumOfAmounts = $sumOfAmounts["sum"] === null ? 0 : $sumOfAmounts["sum"];
        return $sumOfAmounts;
    }

}