<?php

namespace App\Controllers;

use \Core\View;
use App\Models\User;

class Register extends \Core\Controller
{
    public function newAction()
    {
        View::renderTemplate('Register/new.html');
    }

    public function createAction()
    {
        $user = new User($_POST);

        if ($user->save())
        {
            $user->addDefaultCattegories()
            $_SESSION['registrationSuccess'] = true;
            $this->redirect('/register/success'); 
        }
        else
        {
            View::renderTemplate('Register/new.html',
            [
                'user' => $user
            ]);
        }
    }

    public function successAction()
    {
        if (isset($_SESSION['registrationSuccess']))
        {
            unset($_SESSION['registrationSuccess']);
            View::renderTemplate('Register/success.html');           
        }
        else $this->redirect('/');
    }
}