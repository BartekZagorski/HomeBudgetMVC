<?php

namespace App;

use App\Models\User;
use App\Models\RememberedLogin;

class Auth extends \Core\Controller
{
    public static function login($user, $remember_me)
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;

        if ($remember_me)
        {
            if ($user->rememberLogin())
            {
                setcookie('remember_me', $user->remember_token, $user->expiry_timestamp, '/');
            }
        }
    }

    public static function getUser()
    {
        if (isset($_SESSION['user_id']))
        {
            return User::findById($_SESSION['user_id']);
        }
        else
        {
            return static::loginFromRememberCookie();
        }
    }

    public static function logout()
    {
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
            if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();

        static::forgetLogin();
    }

    public static function rememberRequestedPage()
    {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    }
    public static function getReturnToPage()
    {
        return $_SESSION['return_to'] ?? '/'; //if isset session variable return_to return it otherwise return '/'
    }

    protected static function loginFromRememberCookie()
    {
        $cookie = $_COOKIE['remember_me'] ?? false;

        if ($cookie)
        {
            $rememberedLogin = RememberedLogin::findByToken($cookie);

            if ($rememberedLogin && ! $rememberedLogin->hasExpired())
            {
                $user = $rememberedLogin->getUser();

                static::login($user, false);

                return $user;
            }
        }
    }

    protected static function forgetLogin()
    {
        $cookie = $_COOKIE['remember_me'] ?? false;

        if ($cookie)
        {
            $rememberedLogin = RememberedLogin::findByToken($cookie);

            if ($rememberedLogin)
            {
                $rememberedLogin->delete();
            }

        }

        setcookie('remember_me', '', time()-3600);
    }
}