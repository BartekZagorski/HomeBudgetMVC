<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
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
    public function resetAction()
    {
        $token = $this->route_params['token'];

        $user = $this->getUserOrExit($token);

        View::renderTemplate('Password/reset.html',
        [
            'token' => $token
        ]);
    }

    public function resetPasswordAction()
    {
        $user = $this->getUserOrExit($_POST['token']);

        if ($user->resetPassword($_POST['password'], $_POST['passwordConfirm']))
        {
            $_SESSION['resetSucceed'] = true;
            $this->redirect('/password/reset-succeed');
        }
        else
        {
            View::renderTemplate('Password/reset.html',
            [
                'token' => $_POST['token'],
                'user' => $user
            ]);
        }
    }

    protected function getUserOrExit($token)
    {
        $user = User::findByPasswordReset($token);

        if ($user)
        {
            return $user;
        }
        else
        {
            View::renderTemplate('Password/token_expired.html');
            exit;
        }
    }

    protected function resetSucceedAction()
    {
        if (isset($_SESSION['resetSucceed']))
        {
            unset($_SESSION['resetSucceed']);
            View::renderTemplate('Password/reset_success.html');
        }
        else $this->redirect('/');
    }

    public function changePasswordAction()
    {
        View::renderTemplate('Password/change.html');
    }

    public function updatePasswordAction()
    {
        $user = Auth::getUser();
        if ($user->updatePassword($_POST))
        {
            View::renderTemplate('Settings/success.html',[
                'message' => "Zmiany zostały zapisane!"
            ]);
        }
        else
        {
            View::renderTemplate('Password/change.html',[
                'user' => $user
            ]);
        }
    }


}