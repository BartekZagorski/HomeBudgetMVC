<?php

namespace App\Models;

use PDO;
use \App\Mail;
use \App\Token;
use \Core\View;

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
            if ($user->startPasswordReset())
            {
                $user->sendPasswordResetEmail();
            }
        }
    }

    protected function startPasswordReset()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->passwordResetToken = $token->getValue();

        $expiryTimestamp = time() + 60*60*2; //2 hours from now

        $sql = 'UPDATE users SET password_reset_hash = :token_hash, password_reset_expiry = :expires_at WHERE id = :id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);
        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiryTimestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    protected function sendPasswordResetEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->passwordResetToken;

        $text = View::getTemplate('Password/reset_email.txt',
        [
            'url' => $url
        ]);
        $html = View::getTemplate('Password/reset_email.html',
        [
            'url' => $url
        ]);

        Mail::send($this->email, 'Password reset', $text, $html);
    }

    public static function findByPasswordReset($token)
    {
        $token = new Token($token);
        $hashed_token = $token->getHash();

        $sql = 'SELECT * FROM users WHERE password_reset_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);
        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        $user = $stmt->fetch();

        if ($user)
        {
            if (strtotime($user->password_reset_expiry) > time())
            {
                return $user;
            }
        }

    }

    public function resetPassword($password, $confirmedPassword)
    {
        $this->password = $password;
        $this->passwordConfirm = $confirmedPassword;

        $this -> validate();

        if (empty($this->errors))
        {
            $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users
                    SET password = :password_hash,
                        password_reset_hash = NULL,
                        password_reset_expiry = NULL
                    WHERE id = :id';
            
            $db = static::getDB();
            $stmt = $db -> prepare($sql);
            $stmt->bindValue(':password_hash', $passwordHash, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        }

        return false;
    }

}
