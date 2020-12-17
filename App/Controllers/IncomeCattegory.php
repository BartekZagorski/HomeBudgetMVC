<?php

namespace App\Controllers;

use App\Models\IncomeCattegory as Cattegory;
use App\Models\Income;
use \Core\View;

class IncomeCattegory extends Authenticated
{
    public function createAction()
    {
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
            View::renderTemplate('Settings/IncomeCattegory/addCattegory.html',
            [
                'cattegory' => $cattegory
            ]);
        }
    }

    public function updateAction()
    {
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
            View::renderTemplate('Settings/IncomeCattegory/editCattegory.html',
            [
                'cattegory' => $cattegory
            ]);
        }
    }

    public function addAction()
    {
        View::renderTemplate('Settings/IncomeCattegory/addCattegory.html');
    }

    public function editAction()
    {
        $name = $_POST['name'];
        $cattegory = Cattegory::findByName($name);
        View::renderTemplate('Settings/IncomeCattegory/editCattegory.html',
        [
            'cattegory' => $cattegory
        ]
        );
    }

    public function removeAction()
    {
        $name = $_POST['name'];
        $cattegory = Cattegory::findByName($name);
        $numberOfIncomes = Income::getCountOfIncomesBelongToGivenCattegory($cattegory->id);
        $cattegory->countOfIncomes = $numberOfIncomes[0];
        View::renderTemplate('Settings/IncomeCattegory/removeCattegory.html',
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
        if (Income::replaceRemovedCattegory($cattegory->id))
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