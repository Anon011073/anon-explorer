<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\View;
use App\Services\StorageService;

class ShareController {
    public function create() {
        $data = json_decode(file_get_contents('php://input'), true);
        $path = $data['path'] ?? '';
        $password = $data['password'] ?? null;
        $expires = $data['expires'] ?? null; // in hours
        $max_downloads = $data['max_downloads'] ?? null;

        if (!$path) {
            return json_encode(['success' => false, 'message' => 'Path is required']);
        }

        $token = bin2hex(random_bytes(16));
        $hashed_password = $password ? password_hash($password, PASSWORD_DEFAULT) : null;

        $expires_at = null;
        if ($expires) {
            $expires_at = date('Y-m-d H:i:s', strtotime("+$expires hours"));
        }

        $user_id = $_SESSION['user_id'] ?? null;

        $db = App::get('db');
        $stmt = $db->prepare("INSERT INTO shares (file_path, user_id, token, password, expires_at, max_downloads) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$path, $user_id, $token, $hashed_password, $expires_at, $max_downloads]);

        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $shareLink = $baseUrl . "/s/" . $token;

        return json_encode(['success' => true, 'link' => $shareLink]);
    }

    public function view($vars) {
        $token = $vars['token'];
        $db = App::get('db');
        $stmt = $db->prepare("SELECT * FROM shares WHERE token = ?");
        $stmt->execute([$token]);
        $share = $stmt->fetch();

        if (!$share) {
            return "Share not found or expired.";
        }

        if ($share['expires_at'] && strtotime($share['expires_at']) < time()) {
            return "Share has expired.";
        }

        if ($share['max_downloads'] && $share['download_count'] >= $share['max_downloads']) {
            return "Maximum download limit reached.";
        }

        // Check if password protected
        if ($share['password'] && !isset($_SESSION['share_auth_' . $token])) {
            return View::render('share/password', ['token' => $token]);
        }

        // Get file info
        $userPath = App::config('uploads_path') . '/user_' . $share['user_id'];
        $storage = new StorageService($userPath);

        if (!$storage->exists($share['file_path'])) {
            return "File no longer exists.";
        }

        $meta = $storage->getMetadata($share['file_path']);
        return View::render('share/view', ['share' => $share, 'meta' => $meta]);
    }

    public function auth($vars) {
        $token = $vars['token'];
        $password = $_POST['password'] ?? '';

        $db = App::get('db');
        $stmt = $db->prepare("SELECT password FROM shares WHERE token = ?");
        $stmt->execute([$token]);
        $share = $stmt->fetch();

        if ($share && password_verify($password, $share['password'])) {
            $_SESSION['share_auth_' . $token] = true;
            header('Location: ' . App::url('/s/' . $token));
            exit;
        }

        return "Invalid password.";
    }

    public function download($vars) {
        $token = $vars['token'];
        $db = App::get('db');
        $stmt = $db->prepare("SELECT * FROM shares WHERE token = ?");
        $stmt->execute([$token]);
        $share = $stmt->fetch();

        if (!$share) die("Invalid share.");
        if ($share['password'] && !isset($_SESSION['share_auth_' . $token])) die("Unauthorized.");

        $userPath = App::config('uploads_path') . '/user_' . $share['user_id'];
        $storage = new StorageService($userPath);
        $fullPath = $storage->getFullPath($share['file_path']);

        if (!file_exists($fullPath)) die("File not found.");

        // Increment download count
        $db->prepare("UPDATE shares SET download_count = download_count + 1 WHERE id = ?")->execute([$share['id']]);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}
