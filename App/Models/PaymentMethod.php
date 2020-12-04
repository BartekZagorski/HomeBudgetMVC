<?php

namespace App\Models;

use PDO;

class PaymentMethod extends \Core\Model
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
            $sql = 'INSERT INTO payment_method_assigned_to_user(user_id, name) VALUES (:user_id, :name)';

            $db = static::getDB();

            $stmt = $db->prepare($sql);

            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);

            return $stmt -> execute();
        }
        else return false;
    }

    protected function validate()
    {
        $this->name = mb_strtolower($this->name, "UTF-8"); // not strtolower cause of wrongfully encoding polish chars

        if (strlen($this->name)>50)
        {
            $this->errors['name'] = 'Nazwa metody może mieć maksymalnie 50 znaków';
        }
        else if (!preg_match_all('/^[a-ząęćżźńłóś\d]+\s?([a-ząęćżźńłóś\d]+\s?){0,}[a-ząęćżźńłóś\d]+$/i', $this->name))
        {
            $this->errors['name'] = 'Nazwa metody musi składać się z ciągów liter i cyfr oddzielonych pojedynczymi spacjami';
        }
        else if (static::methodExist($this->name))
        {
            $this->errors['name'] = 'Podana nazwa metody jest już zajęta';
        }
    }

    public static function methodExist($name)
    {
        $method = static::findByName($name);

        if ($method) return true;
        else return false;
    }

    public static function findByName($name)
    {
        $sql = 'SELECT * FROM payment_method_assigned_to_user WHERE user_id = :user_id AND name = :name';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public static function getPaymentMethodsAssignedToUser($loggedId)
    {
        $sql = 'SELECT name FROM `payment_method_assigned_to_user` WHERE user_id = :loggedID ORDER BY name != "inna" DESC, id ASC';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':loggedID', $loggedId, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        
        $stmt -> execute();

        return $stmt -> fetchAll();
    }

}