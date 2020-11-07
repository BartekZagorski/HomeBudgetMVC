<?php

namespace App\Controllers;

use \Core\View;
use App\Models\Expense as ExpModel;

class Expense extends Authenticated
{
    public function newAction()
    {
        $expenseCattegories = ExpModel::getExpenseCattegoriesAssignedToUser($_SESSION['user_id']);
        $paymentMethods = ExpModel::getPaymentMethodsAssignedToUSer($_SESSION['user_id']);
        View::renderTemplate('Expense/new.html',
            [
                'cattegories' => $expenseCattegories,
                'methods' => $paymentMethods
            ]
        );
    }
}
