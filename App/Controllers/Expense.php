<?php

namespace App\Controllers;

use \Core\View;

class Expense extends Authenticated
{
    public function newAction()
    {
        View::renderTemplate('Expense/new.html');
    }
}
