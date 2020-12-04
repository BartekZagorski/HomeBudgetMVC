<?php

namespace App\Controllers;

use App\Models\ExpenseCattegory as Cattegory;
use \Core\View;

class ExpenseCattegory extends Authenticated
{
    public function createAction()
    {
        $cattegory = new Cattegory($_POST);

        if ($cattegory->save())
        {
            View::renderTemplate('Settings/ExpenseCattegory/success.html');
        }
        else
        {
            View::renderTemplate('Settings/ExpenseCattegory/addCattegory.html',
            [
                'cattegory' => $cattegory
            ]);
        }
    }

    public function renderAddModalAction()
    {
        View::renderTemplate('Settings/ExpenseCattegory/addCattegory.html');
    }
}