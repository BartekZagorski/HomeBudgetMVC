<?php

namespace App\Controllers;

use \Core\View;
use App\Models\Income as IncModel;

class Income extends Authenticated
{
    public function newAction()
    {
        $incomeCattegories = IncModel::getIncomeCattegoriesAssignedToUser($_SESSION['user_id']);
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
            $incomeCattegories = IncModel::getIncomeCattegoriesAssignedToUser($_SESSION['user_id']);
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

}