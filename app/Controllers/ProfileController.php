<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\View;
use App\Services\AuthService;
use App\Middleware\AuthMiddleware;
use App\Services\LogService;

class ProfileController {
    public function __construct() {
        AuthMiddleware::handle();
    }

    public function show() {
        $user = AuthService::user();
        return View::render('profile', ['user' => $user]);
    }

    public function update() {
        $user = AuthService::user();
        $username = $_POST['username'] ?? $user['username'];
        $password = $_POST['password'] ?? '';

        $db = App::get('db');

        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $hashed, $user['id']]);
        } else {
            $stmt = $db->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->execute([$username, $user['id']]);
        }

        $_SESSION['username'] = $username;

        // Handle Avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $avatarName = 'avatar_' . $user['id'] . '.' . $ext;
                $path = __DIR__ . '/../../public/uploads/avatars';
                if (!is_dir($path)) mkdir($path, 0777, true);

                move_uploaded_file($_FILES['avatar']['tmp_name'], $path . '/' . $avatarName);
                $db->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute(['/uploads/avatars/' . $avatarName, $user['id']]);
                LogService::log('update_avatar', "Updated profile picture");
            }
        }

        header('Location: ' . App::url('/profile'));
        exit;
    }
}
