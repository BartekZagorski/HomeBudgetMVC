<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use App\Models\IncomeCattegory;
use App\Models\ExpenseCattegory;
use App\Models\PaymentMethod;

class Settings extends Authenticated
{
    public function showAction()
    {
        $incomeCattegories = IncomeCattegory::getIncomeCattegoriesAssignedToUser($_SESSION['user_id']);
        $expenseCattegories = ExpenseCattegory::getExpenseCattegoriesAssignedToUser($_SESSION['user_id']);
        $paymentMethods = PaymentMethod::getPaymentMethodsAssignedToUser($_SESSION['user_id']);
        View::renderTemplate("Settings/show.html",
        [
            'incomeCattegories' => $incomeCattegories,
            'expenseCattegories' => $expenseCattegories,
            'paymentMethods' => $paymentMethods
        ]);
    }

    public function loadIncomesCattegoriesAction()
    {
        $incomeCattegories = IncomeCattegory::getIncomeCattegoriesAssignedToUser($_SESSION['user_id']);
        View::renderTemplate('Settings/IncomeCattegory/cattegories.html',
            [
                'incomeCattegories' => $incomeCattegories
            ]);
    }

    public function loadExpensesCattegoriesAction()
    {
        $expenseCattegories = ExpenseCattegory::getExpenseCattegoriesAssignedToUser($_SESSION['user_id']);
        View::renderTemplate('Settings/ExpenseCattegory/cattegories.html',
            [
                'expenseCattegories' => $expenseCattegories
            ]);
    }

    public function loadPaymentMethodsAction()
    {
        $paymentMethods = PaymentMethod::getPaymentMethodsAssignedToUser($_SESSION['user_id']);
        View::renderTemplate('Settings/PaymentMethod/methods.html',
            [
                'paymentMethods' => $paymentMethods
            ]);
    }

    public function editUserDataAction()
    {
        $user = Auth::getUser();
        View::renderTemplate('Settings/editUserData.html',[
            'user' => $user
        ]);
    }
}