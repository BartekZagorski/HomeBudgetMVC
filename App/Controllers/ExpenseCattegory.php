<?php

namespace App\Controllers;

use App\Models\ExpenseCattegory as Cattegory;
use App\Models\Expense;
use \Core\View;

class ExpenseCattegory extends Authenticated
{
    public function createAction()
    {
        if (empty($_POST)) $this->redirect('/');

        $cattegory = new Cattegory($_POST);

        if ($cattegory->save())
        {
            View::renderTemplate('Settings/success.html',
            [
                'message' => "dodano pomyślnie!"
            ]);
        }
        else
        {
            View::renderTemplate('Settings/ExpenseCattegory/addCattegory.html',
            [
                'cattegory' => $cattegory
            ]);
        }
    }

    public function addAction()
    {
        if (empty($_POST)) $this->redirect('/');

        View::renderTemplate('Settings/ExpenseCattegory/addCattegory.html');
    }

    public function editAction()
    {
        if (empty($_POST)) $this->redirect('/');
        
        $name = $_POST['name'];
        $cattegory = Cattegory::findByName($name);
        View::renderTemplate('Settings/ExpenseCattegory/editCattegory.html',
        [
            'cattegory' => $cattegory
        ]
        );
    }

    public function updateAction()
    {
        if (empty($_POST)) $this->redirect('/');
        
        $cattegory = new Cattegory($_POST);

        if ($cattegory->update())
        {
            View::renderTemplate('Settings/success.html',
            [
                'message' => "edycja przebiegła pomyślnie!"
            ]);
        }
        else
        {
            View::renderTemplate('Settings/ExpenseCattegory/editCattegory.html',
            [
                'cattegory' => $cattegory
            ]);
        }
    }

    public function removeAction()
    {
        if (empty($_POST)) $this->redirect('/');

        $name = $_POST['name'];
        $cattegory = Cattegory::findByName($name);
        $numberOfExpenses = Expense::getCountOfExpensesBelongToGivenCattegory($cattegory->id);
        $cattegory->countOfExpenses = $numberOfExpenses[0];
        View::renderTemplate('Settings/ExpenseCattegory/removeCattegory.html',
        [
            'cattegory' => $cattegory
        ]
        );
    }

    public function destroyAction()
    {   
        if (empty($_POST)) $this->redirect('/');
        
        $name = $_POST['name'];
        $cattegory = Cattegory::findByName($name);
        if (Expense::replaceRemovedCattegory($cattegory->id))
        {
            $cattegory->destroy();
            View::renderTemplate('Settings/success.html',
        [
            'message' => "kategoria została usunięta!"
        ]
        );
        }
        
    }

    
}