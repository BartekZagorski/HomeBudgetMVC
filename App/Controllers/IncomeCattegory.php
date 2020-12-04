<?php

namespace App\Controllers;

use App\Models\IncomeCattegory as Cattegory;
use \Core\View;

class IncomeCattegory extends Authenticated
{
    public function createAction()
    {
        $cattegory = new Cattegory($_POST);

        if ($cattegory->save())
        {
            View::renderTemplate('Settings/IncomeCattegory/success.html');
        }
        else
        {
            View::renderTemplate('Settings/IncomeCattegory/addCattegory.html',
            [
                'cattegory' => $cattegory
            ]);
        }
    }

    public function renderAddModalAction()
    {
        View::renderTemplate('Settings/IncomeCattegory/addCattegory.html');
    }
}