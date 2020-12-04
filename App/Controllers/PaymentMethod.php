<?php

namespace App\Controllers;

use App\Models\PaymentMethod as Method;
use \Core\View;

class PaymentMethod extends Authenticated
{
    public function createAction()
    {
        $method = new Method($_POST);

        if ($method->save())
        {
            View::renderTemplate('Settings/ExpenseCattegory/success.html');
        }
        else
        {
            View::renderTemplate('Settings/PaymentMethod/addMethod.html',
            [
                'method' => $method
            ]);
        }
    }

    public function renderAddModalAction()
    {
        View::renderTemplate('Settings/PaymentMethod/addMethod.html');
    }
}