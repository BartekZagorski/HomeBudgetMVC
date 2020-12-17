<?php

namespace App\Models;

use PDO;

class IncomeCattegory extends \Core\Model
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
            $sql = 'INSERT INTO incomes_cattegories_assigned_to_users(user_id, name) VALUES (:user_id, :name)';

            $db = static::getDB();

            $stmt = $db->prepare($sql);

            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);

            return $stmt -> execute();
        }
        else return false;
    }

    public function update()
    {
        $this->validate();

        if (empty($this->errors))
        {   
            $sql = 'UPDATE incomes_cattegories_assigned_to_users
                    SET name = :name
                    WHERE id = :id';

            $db = static::getDB();

            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

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
        $sql = 'SELECT * FROM incomes_cattegories_assigned_to_users WHERE user_id = :user_id AND name = :name';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public static function findById($id)
    {
        $sql = 'SELECT * FROM incomes_cattegories_assigned_to_users WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public static function getIncomeCattegoriesAssignedToUser($loggedId)
    {
        $sql = 'SELECT name FROM `incomes_cattegories_assigned_to_users` WHERE user_id = :loggedID ORDER BY name != "inne" DESC, id ASC';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':loggedID', $loggedId, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        return $stmt -> fetchAll();
    }

    public function destroy()
    {
        $sql = 'DELETE FROM incomes_cattegories_assigned_to_users WHERE id = :id';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt -> execute();
    }

}