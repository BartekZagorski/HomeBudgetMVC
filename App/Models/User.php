<?php

namespace App\Models;

use PDO;
use \App\Mail;

/**
 * Example user model
 *
 * PHP version 7.0
 */
class User extends \Core\Model
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
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            /*$token = new Token();
            $hashed_token = $token->getHash();
            $this->activationToken = $token->getValue();*/
            
            $sql = 'INSERT INTO users(login, email, password) VALUES (:login, :email, :pass_hash)';

            $db = static::getDB();

            $stmt = $db->prepare($sql);

            $stmt->bindValue(':login', $this->login, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':pass_hash', $password_hash, PDO::PARAM_STR);
            //$stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

            return $stmt -> execute();
        }
        else return false;
    }

    public function validate()
    {
        //login validation
        if ($this->login == '')
        {
            $this->errors['login'] = "Login jest wymagany";
        }
        else if (strlen($this->login)<3 || strlen($this->login)>20)
        {
            $this->errors['login'] = "Login musi posiadać od 3 do 20 znaków";
        }
        else if (ctype_alnum($this->login)==false)
        {
            $this->errors['login'] = "Login może składać się tylko z liter i cyfr bez polskich znaków.";
        }

        //email validation
        $emailSafe = filter_var($this->email, FILTER_SANITIZE_EMAIL);
        if ($emailSafe != $this->email || !(filter_var($emailSafe, FILTER_VALIDATE_EMAIL)))
        {
            $this->errors['email'] = "Niepoprawny adres email";
        }

        if (static::emailExists($this->email, $this->id ?? null))
        {
            $this->errors['email'] = "Email $this->email jest już zajęty";
        }

        if (isset($this->password))
        {
            if (strlen($this->password)<6)
            {
                $this->errors['password'] = "Hasło musi składać się z minimum 6 znaków";
            }
            else if (!(preg_match('/.*[a-z]+.*/i', $this->password)))
            {
                $this->errors['password'] = "Hasło musi zawierać minimum 1 literkę";
            }
            else if(!(preg_match('/.*\d+.*/i',$this->password)))
            {
                $this->errors['password'] = "Hasło musi zawierać minimum jedną cyfrę";
            }
            else if($this->password !== $this->passwordConfirm)
            {
                $this->errors['password'] = "Podane hasła nie są identyczne";
            }
        }
    }

    public static function emailExists($email, $ignoreId = NULL)
    {
        $user = static::findByEmail($email);
        
        if ($user)
        {
            if ($user->id != $ignoreId)
            {
                return true;
            }
        }
        return false;
    }

    public static function findByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public static function authenticate($login, $password)
    {
        $user = static::findByLogin($login);

        if ($user)
        {
            if (password_verify($password, $user->password))
            return $user;
        }

        return false;
    }

    public static function findByLogin($login)
    {
        $sql = 'SELECT * FROM users WHERE login = :login';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':login', $login, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public static function findById($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public static function sendPasswordReset($email)
    {
        $user = static::findByEmail($email);

        if ($user)
        {
            echo "tutaj zaczniemy procedurę resetowania hasła gdyż mamy użytkownika";
            /*if ($user->startPasswordReset())
            {
                $user->sendPasswordResetEmail();
            }*/
        }
    }

}
