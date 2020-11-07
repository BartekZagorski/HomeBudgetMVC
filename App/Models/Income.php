<?php

namespace App\Models;

use PDO;

class Income extends \Core\Model
{
    public static function getIncomeCattegoriesAssignedToUser($loggedId)
    {
        $sql = 'SELECT name FROM `incomes_cattegories_assigned_to_users` WHERE user_id = :loggedID';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':loggedID', $loggedId, PDO::PARAM_INT);
        $stmt -> execute();

        return $stmt -> fetchAll();
    }
}