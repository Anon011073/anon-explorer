<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\AuthService;
use App\Core\App;
use App\Core\Settings;

class HomeController {
    public function index() {
        AuthMiddleware::handle();
        $user = AuthService::user();

        $userPath = App::config('uploads_path') . '/user_' . $user['id'];
        $usage = 0;
        if (is_dir($userPath)) {
            try {
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($userPath));
                foreach ($files as $file) {
                    if ($file->isFile()) {
                        $usage += $file->getSize();
                    }
                }
            } catch (\Exception $e) {
                // Ignore directory traversal errors
            }
        }

        return \App\Core\View::render('index', [
            'title' => 'My Files - ' . Settings::get('app_name', 'Zipply-Drive'),
            'storageUsage' => $usage,
            'storageLimit' => $user['storage_limit']
        ]);
    }
}
