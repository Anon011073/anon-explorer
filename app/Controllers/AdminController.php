<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\View;
use App\Core\Settings;
use App\Middleware\AuthMiddleware;

class AdminController {
    public function __construct() {
        AuthMiddleware::admin();
    }

    public function dashboard() {
        $db = App::get('db');
        $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $shareCount = $db->query("SELECT COUNT(*) FROM shares")->fetchColumn();

        return View::render('admin/dashboard', [
            'userCount' => $userCount,
            'shareCount' => $shareCount
        ]);
    }

    public function users() {
        $db = App::get('db');
        $users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
        return View::render('admin/users', ['users' => $users]);
    }

    public function updateUser() {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $limit = $data['storage_limit'];
        $role = $data['role'];

        $db = App::get('db');
        $stmt = $db->prepare("UPDATE users SET storage_limit = ?, role = ? WHERE id = ?");
        $stmt->execute([$limit, $role, $id]);

        return json_encode(['success' => true]);
    }

    public function deleteUser() {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];

        $db = App::get('db');
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$id]);

        return json_encode(['success' => true]);
    }

    public function settings() {
        $settings = [
            'app_name' => Settings::get('app_name', 'Zipply-Drive'),
            'registration_enabled' => Settings::get('registration_enabled', '1'),
            'default_storage_limit' => Settings::get('default_storage_limit', '1073741824'),
            'public_path' => Settings::get('public_path', ''),
            'root_path' => Settings::get('root_path', ''),
            'hide_system_files' => Settings::get('hide_system_files', '1'),
            'theme' => Settings::get('theme', 'dark'),
        ];
        return View::render('admin/settings', ['settings' => $settings]);
    }

    public function saveSettings() {
        $data = json_decode(file_get_contents('php://input'), true);
        $db = App::get('db');

        foreach ($data as $key => $value) {
            Settings::set($db, $key, (string)$value);
        }

        return json_encode(['success' => true]);
    }
}
