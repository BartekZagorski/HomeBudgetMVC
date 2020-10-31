<?php

namespace App\Models;

use PDO;
use App\Token;
use App\Models\User;

class RememberedLogin extends \Core\Model
{
    public static function findByToken($token)
    {
        $token = new Token ($token);
        $hashed_token = $token->getHash();

        $sql = 'SELECT * FROM remembered_logins WHERE token_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);
        $stmt->bindValue(':token_hash', $hashed_token,PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public function getUser()
    {
        return User::findById($this->user_id);
    }

    public function hasExpired()
    {
        return strtotime($this->expires_at) < time();
    }

    public function delete()
    {
        $sql = 'DELETE FROM remembered_logins WHERE token_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);
        $stmt->bindValue(':token_hash', $this->token_hash ,PDO::PARAM_STR);

        $stmt->execute();

    }
}