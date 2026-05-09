<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\AuthService;

class HomeController {
    public function index() {
        AuthMiddleware::handle();
        return \App\Core\View::render('index', ['title' => 'My Files - Zipply-Drive']);
    }
}
