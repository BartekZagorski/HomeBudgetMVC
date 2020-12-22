<?php

namespace App\Controllers;

use \Core\View;
use App\Models\Expense as ExpModel;
use App\Models\ExpenseCattegory;
use App\Models\PaymentMethod;

class Expense extends Authenticated
{
    public function newAction()
    {
        $expenseCattegories = ExpenseCattegory::getExpenseCattegoriesAssignedToUser($_SESSION['user_id']);
        $paymentMethods = PaymentMethod::getPaymentMethodsAssignedToUSer($_SESSION['user_id']);
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
            $expenseCattegories = ExpenseCattegory::getExpenseCattegoriesAssignedToUser($_SESSION['user_id']);
            $paymentMethods = PaymentMethod::getPaymentMethodsAssignedToUSer($_SESSION['user_id']);
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

    public function removeAction()
    {
        $id = $_POST["id"];
        View::renderTemplate('Expense/remove.html', [
            'id' => $id
        ]);
    }

    public function destroyAction()
    {
        $id = $_POST["id"];
        $expense = ExpModel::findById($id);
        if ($expense)
        {
            $expense->destroy();
            View::renderTemplate('Settings/success.html',
            [
                'message' => "wydatek został usunięty!"
            ]);
        }
        
    }

    public function editAction()
    {
        $id = $_POST["id"];
        $expense = ExpModel::findById($id);
        if ($expense)
        {
            $expenseCattegories = ExpenseCattegory::getExpenseCattegoriesAssignedToUser($_SESSION['user_id']);
            $paymentMethods = PaymentMethod::getPaymentMethodsAssignedToUSer($_SESSION['user_id']);
            View::renderTemplate('Expense/edit.html', [
                'expense' => $expense,
                'cattegories' => $expenseCattegories,
                'methods' => $paymentMethods
            ]);
        }
    }

    public function updateAction()
    {
        $expense = new ExpModel($_POST);

        if ($expense->update())
        {
            View::renderTemplate('Settings/success.html',
            [
                'message' => "edycja przebiegła pomyślnie!"
            ]);
        }
        else
        {
            $expenseCattegories = ExpenseCattegory::getExpenseCattegoriesAssignedToUser($_SESSION['user_id']);
            $paymentMethods = PaymentMethod::getPaymentMethodsAssignedToUSer($_SESSION['user_id']);
            View::renderTemplate('Expense/edit.html', [
                'expense' => $expense,
                'cattegories' => $expenseCattegories,
                'methods' => $paymentMethods
            ]);
        }

    }

    public function getLimitAction()
    {     
        $cattegory = ExpenseCattegory::findByName($_POST['cattegory']);
        $cattegory->sumOfExpenses = ExpenseCattegory::getSumOfAmountInRequestedMonth($_POST['date'], $cattegory->name, $_POST["expenseId"]);
        $amount  = floatval($_POST['amount']);
        $cattegory->sumAfterAddExpense = $amount + $cattegory->sumOfExpenses;
        $cattegory->sumAfterAddExpense > $cattegory->cattegory_limit ? $background = "bg-danger" : $background = "bg-success";        
        View::renderTemplate('Expense/limit.html',
        [
            'cattegory' => $cattegory,
            'background' => $background
        ]
        );
    }

}
