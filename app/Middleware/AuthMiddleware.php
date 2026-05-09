<?php

namespace App\Middleware;

use App\Services\AuthService;

class AuthMiddleware {
    public static function handle() {
        if (!AuthService::check()) {
            header('Location: /login');
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
