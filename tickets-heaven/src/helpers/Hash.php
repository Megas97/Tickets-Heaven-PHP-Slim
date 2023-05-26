<?php

namespace helpers;

class Hash {

    public function passwordHash($password) {

        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function passwordVerify($input, $saved) {

        return password_verify($input, $saved);
    }

    public function hash($input) {

        return hash('sha256', $input);
    }

    public function hashCheck($input, $saved) {
        
        return hash_equals($input, $saved);
    }
}
