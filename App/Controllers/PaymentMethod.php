<?php

namespace App\Controllers;

use App\Models\PaymentMethod as Method;
use App\Models\Expense;
use \Core\View;

class PaymentMethod extends Authenticated
{
    public function createAction()
    {
        if (empty($_POST)) $this->redirect('/');
        
        $method = new Method($_POST);

        if ($method->save())
        {
            View::renderTemplate('Settings/success.html',
            [
                'message' => "dodano pomyślnie!"
            ]);
        }
        else
        {
            View::renderTemplate('Settings/PaymentMethod/addMethod.html',
            [
                'method' => $method
            ]);
        }
    }

    public function addAction()
    {
        if (empty($_POST)) $this->redirect('/');
        
        View::renderTemplate('Settings/PaymentMethod/addMethod.html');
    }

    public function editAction()
    {
        if (empty($_POST)) $this->redirect('/');
        
        $name = $_POST['name'];
        $method = Method::findByName($name);
        View::renderTemplate('Settings/PaymentMethod/editMethod.html',
        [
            'method' => $method
        ]
        );
    }

    public function updateAction()
    {
        if (empty($_POST)) $this->redirect('/');
        
        $method = new Method($_POST);

        if ($method->update())
        {
            View::renderTemplate('Settings/success.html',
            [
                'message' => "edycja przebiegła pomyślnie!"
            ]);
        }
        else
        {
            View::renderTemplate('Settings/PaymentMethod/editMethod.html',
            [
                'method' => $method
            ]);
        }
    }

    public function removeAction()
    {
        if (empty($_POST)) $this->redirect('/');
        
        $name = $_POST['name'];
        $method = Method::findByName($name);
        $numberOfExpenses = Expense::getCountOfExpensesBelongToGivenPayMethod($method->id);
        $method->countOfExpenses = $numberOfExpenses[0];
        View::renderTemplate('Settings/PaymentMethod/removeMethod.html',
        [
            'method' => $method
        ]
        );
    }

    public function destroyAction()
    {   
        if (empty($_POST)) $this->redirect('/');
        
        $name = $_POST['name'];
        $method = Method::findByName($name);
        if (Expense::replaceRemovedMethod($method->id))
        {
           $method->destroy();
            View::renderTemplate('Settings/success.html',
        [
            'message' => "metoda została usunięta!"
        ]
        );
        }
        
    }
}