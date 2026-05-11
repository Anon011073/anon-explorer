<?php

namespace App\Middleware;

use App\Services\AuthService;
use App\Core\App;

class AuthMiddleware {
    public static function handle() {
        if (!AuthService::check() || !AuthService::user()) {
            header('Location: ' . App::url('/logout'));
            exit;
        }
    }

    public static function admin() {
        self::handle();
        if (!AuthService::isAdmin()) {
            http_response_code(403);
            die("403 Forbidden - Admins only");
        }
    }
}
