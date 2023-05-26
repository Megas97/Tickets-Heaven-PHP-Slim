<?php

namespace auth;

use models\User;
use Carbon\Carbon;

class Auth {

    protected $c;

    public function __construct($container) {

        $this->c = $container;
    }
    
    public function user() {

        return isset($_SESSION['user']) ? User::find($_SESSION['user']) : null;
    }

    public function check() {

        return isset($_SESSION['user']);
    }

    public function attempt($usernameOrEmail, $password, $rememberMe) {

        $user = User::where(function($query) use ($usernameOrEmail) {

            return $query->where('username', $usernameOrEmail)->orWhere('email', $usernameOrEmail);
        })->first();

        if (!$user) {

            return false;
        }

        if ($this->c->get('hash')->passwordVerify($password, $user->password)) {
            
            if (!$user->active) {

                return -1;
            }

            $_SESSION['user'] = $user->id;

            if ($rememberMe === 'yes') {

                $rememberIdentifier = $this->c->get('randomlib')->generateString(128);

                $rememberToken = $this->c->get('randomlib')->generateString(128);

                $user->updateRememberCredentials($rememberIdentifier, $this->c->get('hash')->hash($rememberToken));
                
                setcookie($this->c->get('settings')['auth']['remember'], $rememberIdentifier . '___' . $rememberToken, Carbon::parse('+1 week')->timestamp);
            }

            return true;
        }

        return false;
    }

    public function attemptSocial($email, $service, $id) {

        $user = User::where('email', $email)->where($service . '_id', $id)->first();

        if (!$user) {

            return false;
        }

        $_SESSION['user'] = $user->id;

        return true;
    }

    public function logout($profileDeleted = false) {

        if (isset($_COOKIE[$this->c->get('settings')['auth']['remember']])) {

            if (!$profileDeleted) {
                
                $this->user()->removeRememberCredentials();
            }

            setcookie($this->c->get('settings')['auth']['remember'], null);
        }

        unset($_SESSION['user']);
    }
}
