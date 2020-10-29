<?php

namespace App\Controllers;

use \Core\View;
use App\Models\User;
use App\Auth;

class Login extends \Core\Controller
{
    public function newAction()
    {
        View::renderTemplate('Login/new.html');
    }

    public function createAction()
    {
        $user = User::authenticate($_POST['login'], $_POST['password']);

        //$remember_me = isset($_POST['remember_me']);

        if ($user)
        {
            Auth::login($user);
            $user->sendTestEmail();
            $this->redirect('/');
            //$this->redirect(Auth::getReturnToPage());
        }
        else
        {
            View::renderTemplate('Login/new.html',
            [
                'login' => $_POST['login']
                //'remember_me' => $remember_me
            ]);
        }
    }

    public function destroyAction()
    {
        Auth::logout();

        $this->redirect('/');
    }
}