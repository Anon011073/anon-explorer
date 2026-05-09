<?php

namespace App\Core;

class CSRF {
    public static function generate() {
        if (!Session::get('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    public static function validate($token) {
        return hash_equals(Session::get('csrf_token', ''), $token);
    }
}
