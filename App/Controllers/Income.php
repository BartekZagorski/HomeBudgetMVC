<?php

namespace App\Controllers;

use \Core\View;
use App\Models\Income as IncModel;
use App\Models\IncomeCattegory;

class Income extends Authenticated
{
    public function newAction()
    {
        $incomeCattegories = IncomeCattegory::getIncomeCattegoriesAssignedToUser($_SESSION['user_id']);
        View::renderTemplate('Income/new.html', 
            [
                'cattegories' => $incomeCattegories
            ]
        );
    }

    public function createAction()
    {
        $income = new IncModel($_POST);

        if($income->save())
        {
            $_SESSION['addIncomeSuccess'] = true;
            $this->redirect('/income/success'); 
        }
        else
        {
            $incomeCattegories = IncomeCattegory::getIncomeCattegoriesAssignedToUser($_SESSION['user_id']);
            View::renderTemplate('Income/new.html',
            [
                'cattegories' => $incomeCattegories,
                'income' => $income
            ]);
        }
    }
    
    public function successAction()
    {
        if (isset($_SESSION['addIncomeSuccess']))
        {
            unset($_SESSION['addIncomeSuccess']);
            View::renderTemplate('Income/success.html');
        }
        else $this->redirect('/');
    }

    public function removeAction()
    {
        $id = $_POST["id"];
        View::renderTemplate('Income/remove.html', [
            'id' => $id
        ]);
    }

    public function destroyAction()
    {
        $id = $_POST["id"];
        $income = IncModel::findById($id);
        if ($income)
        {
            $income->destroy();
            View::renderTemplate('Settings/success.html',
            [
                'message' => "przychód został usunięty!"
            ]);
        }        
    }

    public function editAction()
    {
        $id = $_POST["id"];
        $income = IncModel::findById($id);
        if ($income)
        {
            $incomeCattegories = IncomeCattegory::getIncomeCattegoriesAssignedToUser($_SESSION['user_id']);
            View::renderTemplate('Income/edit.html', [
                'income' => $income,
                'cattegories' => $incomeCattegories
            ]);
        }
    }

    public function updateAction()
    {
        $income = new IncModel($_POST);

        if ($income->update())
        {
            View::renderTemplate('Settings/success.html',
            [
                'message' => "edycja przebiegła pomyślnie!"
            ]);
        }
        else
        {
            $incomeCattegories = IncomeCattegory::getIncomeCattegoriesAssignedToUser($_SESSION['user_id']);
            View::renderTemplate('Income/edit.html', [
                'income' => $income,
                'cattegories' => $incomeCattegories
            ]);
        }

    }

}