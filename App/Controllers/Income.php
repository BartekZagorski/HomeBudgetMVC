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

}