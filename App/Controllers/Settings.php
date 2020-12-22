<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Models\User;
use App\Models\IncomeCattegory;
use App\Models\Income;
use App\Models\ExpenseCattegory;
use App\Models\Expense;
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

    public function updateUserDataAction()
    {
        $user = Auth::getUser();
        if ($user->update($_POST))
        {
            View::renderTemplate('Settings/success.html',[
                'message' => "Zmiany zostały zapisane!"
            ]);
        }
        else
        {
            View::renderTemplate('Settings/editUserData.html',[
                'user' => $user
            ]);
        }
    }

    public function loadEditUserAction()
    {
        View::renderTemplate('Settings/editUser.html');
    }

    public function removeIncomesAndExpenses()
    {
        View::renderTemplate('Settings/removeIncomesAndExpenses.html');
    }

    public function deleteIncomesAndExpenses()
    {
        $user = Auth::getUser();
        if (Income::deleteEveryIncomes($user->id) && Expense::deleteEveryExpenses($user->id))
        {
            View::renderTemplate('Settings/success.html',[
                'message' => "Przychody i wydatki zostały usunięte!"
            ]);
        }
    }

    public function deleteAccount()
    {        
        View::renderTemplate('Settings/deleteAccount.html');
    }

    public function destroyAccount()
    {
        $user = User::findByLogin($_POST['login']);
        if ($user->delete())
        {
            View::renderTemplate('Settings/success.html',[
                'message' => "Konto zostało usunięte! Kliknij OK aby przejść do strony głównej."
            ]);
        }
    }
}