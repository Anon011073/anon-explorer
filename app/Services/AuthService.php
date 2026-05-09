<?php

namespace App\Services;

use App\Core\App;
use App\Core\Session;

class AuthService {
    public static function attempt($username, $password) {
        $db = App::get('db');
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            Session::set('user_id', $user['id']);
            Session::set('username', $user['username']);
            Session::set('role', $user['role']);
            return true;
        }

        return false;
    }

    public static function check() {
        return Session::get('user_id') !== null;
    }

    public static function user() {
        if (!self::check()) return null;

        $db = App::get('db');
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([Session::get('user_id')]);
        return $stmt->fetch();
    }

    public static function logout() {
        Session::destroy();
    }

    public static function isAdmin() {
        return Session::get('role') === 'admin';
    }
}
