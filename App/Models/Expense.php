<?php

namespace App\Models;

use PDO;

class Expense extends \Core\Model
{
    public static function getExpenseCattegoriesAssignedToUser($loggedId)
    {
        $sql = 'SELECT name FROM `expenses_cattegories_assigned_to_users` WHERE user_id = :loggedID';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':loggedID', $loggedId, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        return $stmt -> fetchAll();
    }

    public static function getPaymentMethodsAssignedToUser($loggedId)
    {
        $sql = 'SELECT name FROM `payment_method_assigned_to_user` WHERE user_id = :loggedID';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':loggedID', $loggedId, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        
        $stmt -> execute();

        return $stmt -> fetchAll();
    }
}