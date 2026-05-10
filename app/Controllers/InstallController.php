<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\View;
use App\Core\Database;
use App\Core\Settings;
use PDO;

class InstallController {
    public function show() {
        if (file_exists(__DIR__ . '/../../storage/install.lock')) {
            die("Application already installed. Delete storage/install.lock to reinstall.");
        }

        $checks = [
            'PHP Version >= 8.2' => PHP_VERSION_ID >= 80200,
            'PDO SQLite Extension' => extension_loaded('pdo_sqlite'),
            'Storage Directory Writable' => is_writable(__DIR__ . '/../../storage'),
            'Uploads Directory Writable' => is_writable(__DIR__ . '/../../storage/uploads'),
        ];

        return View::render('install', ['checks' => $checks]);
    }

    public function install() {
        if (file_exists(__DIR__ . '/../../storage/install.lock')) {
            return json_encode(['success' => false, 'message' => 'Already installed']);
        }

        $dbPath = __DIR__ . '/../../storage/database/database.sqlite';
        $pdo = new PDO("sqlite:" . $dbPath);
        Database::init($pdo);

        // Custom Admin Setup
        $username = $_POST['admin_user'] ?? 'admin';
        $password = $_POST['admin_pass'] ?? 'admin123';
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $pdo->exec("DELETE FROM users WHERE role = 'admin'");
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $stmt->execute([$username, $hashed]);

        file_put_contents(__DIR__ . '/../../storage/install.lock', date('Y-m-d H:i:s'));

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            return json_encode(['success' => true]);
        }

        header('Location: ' . App::url('/login'));
        exit;
    }
}
