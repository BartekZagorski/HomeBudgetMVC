<?php

namespace App\Models;

use PDO;

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

            if (isset($this->check))
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

            if (isset($this->check))
            {
                $stmt->bindValue(':is_set_limit', 1, PDO::PARAM_BOOL);
                $stmt->bindValue(':cattegory_limit', $this->amount, PDO::PARAM_STR);
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
        else if (static::cattegoryExist($this->name))
        {
            $this->errors['name'] = 'Podana nazwa kategorii jest już zajęta';
        }

        if (isset($this->check))
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
        }
    }

    public static function cattegoryExist($name)
    {
        $cattegory = static::findByName($name);

        if ($cattegory) return true;
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

}