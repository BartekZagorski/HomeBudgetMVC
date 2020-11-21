<?php

namespace App\Controllers;

use \Core\View;
use App\Models\Income;
use App\Models\Expense;

class Settings extends Authenticated
{
    public function showAction()
    {
        $incomeCattegories = Income::getIncomeCattegoriesAssignedToUser($_SESSION['user_id']);
        $expenseCattegories = Expense::getExpenseCattegoriesAssignedToUser($_SESSION['user_id']);
        $paymentMethods = Expense::getPaymentMethodsAssignedToUser($_SESSION['user_id']);
        View::renderTemplate("Settings/show.html",
        [
            'incomeCattegories' => $incomeCattegories,
            'expenseCattegories' => $expenseCattegories,
            'paymentMethods' => $paymentMethods
        ]);
    }

    public function loadIncomesCattegoriesAction()
    {
        $incomeCattegories = Income::getIncomeCattegoriesAssignedToUser($_SESSION['user_id']);
        View::renderTemplate('Settings/cattegories.html',
            [
                'incomeCattegories' => $incomeCattegories,
            ]);
    }

    public function loadExpensesCattegoriesAction()
    {
        $expenseCattegories = Expense::getExpenseCattegoriesAssignedToUser($_SESSION['user_id']);
        View::renderTemplate('Settings/cattegories.html',
            [
                'expenseCattegories' => $expenseCattegories,
            ]);
    }
}