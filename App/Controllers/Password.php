<?php

namespace App\Controllers;

use \Core\View;
use App\Models\User;

class Password extends \Core\Controller
{
    public function forgotAction()
    {
        View::renderTemplate('Password/forgot.html');
    }
    public function requestResetAction()
    {
        User::sendPasswordReset($_POST['email']);

        $_SESSION['resetRequested'] = true;
        $this->redirect('/password/reset-requested');
    }

    protected function resetRequestedAction()
    {
        if (isset($_SESSION['resetRequested']))
        {
            unset($_SESSION['resetRequested']);
            View::renderTemplate('Password/reset_requested.html');
        }
        else $this->redirect('/');
    }
}