<?php

namespace App\Controllers;

use App\Core\View;
use App\Services\AuthService;
use App\Core\App;
use App\Core\Settings;
use App\Core\CSRF;

class AuthController {
    public function showLogin() {
        if (AuthService::check()) {
            header('Location: ' . App::url('/'));
            exit;
        }
        return View::render('auth/login');
    }

    public function login() {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            return View::render('auth/login', ['error' => 'Invalid CSRF token']);
        }
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (AuthService::attempt($username, $password)) {
            header('Location: ' . App::url('/'));
            exit;
        }

        return View::render('auth/login', ['error' => 'Invalid credentials']);
    }

    public function logout() {
        AuthService::logout();
        header('Location: ' . App::url('/login'));
        exit;
    }

    public function showRegister() {
        if (Settings::get('registration_enabled', '1') !== '1') {
            return "Registration is disabled.";
        }
        return View::render('auth/register');
    }

    public function register() {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            return View::render('auth/register', ['error' => 'Invalid CSRF token']);
        }
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm) {
            return View::render('auth/register', ['error' => 'Passwords do not match']);
        }

        $db = App::get('db');
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            return View::render('auth/register', ['error' => 'Username already exists']);
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $limit = Settings::get('default_storage_limit', '1073741824');
        $stmt = $db->prepare("INSERT INTO users (username, password, role, storage_limit) VALUES (?, ?, 'user', ?)");
        $stmt->execute([$username, $hashed, $limit]);

        header('Location: ' . App::url('/login'));
        exit;
    }
}
