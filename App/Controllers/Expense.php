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
    public function createAction()
    {
        $expense = new ExpModel($_POST);

        if($expense->save())
        {
            $_SESSION['addExpenseSuccess'] = true;
            $this->redirect('/expense/success'); 
        }
        else
        {
            $expenseCattegories = ExpModel::getExpenseCattegoriesAssignedToUser($_SESSION['user_id']);
            $paymentMethods = ExpModel::getPaymentMethodsAssignedToUSer($_SESSION['user_id']);
            View::renderTemplate('Expense/new.html',
            [
                'cattegories' => $expenseCattegories,
                'methods' => $paymentMethods,
                'expense' => $expense
            ]);
        }
    }

    public function successAction()
    {
        if (isset($_SESSION['addExpenseSuccess']))
        {
            unset($_SESSION['addExpenseSuccess']);
            View::renderTemplate('Expense/success.html');
        }
        else $this->redirect('/');
    }
    public function cattegoryInfoAction()
    {
        View::renderTemplate('Expense/test.html',[
            'cattegory' => $_POST['cattegory'],
            'date' => $_POST['date'],
            'amount' => $_POST['amount']
        ]);
    }
}
