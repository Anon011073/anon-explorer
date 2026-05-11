<?php

namespace App\Controllers;

use App\Services\StorageService;
use App\Services\AuthService;
use App\Middleware\AuthMiddleware;
use App\Core\App;
use App\Core\Settings;

class FileController {
    protected $storage;
    protected $context; // 'private', 'public', 'root'

    public function __construct() {
        AuthMiddleware::handle();
        $this->setContext();
    }

    protected function setContext() {
        $user = AuthService::user();
        $this->context = $_GET['context'] ?? 'private';

        if ($this->context === 'public') {
            $publicPath = Settings::get('public_path');
            if (!$publicPath) $publicPath = App::config('uploads_path') . '/public';
            if (!is_dir($publicPath)) mkdir($publicPath, 0777, true);
            $this->storage = new StorageService($publicPath);
        } elseif ($this->context === 'root' && AuthService::isAdmin()) {
            $rootPath = Settings::get('root_path');
            if (!$rootPath) $rootPath = realpath(__DIR__ . '/../../');
            $this->storage = new StorageService($rootPath);
        } else {
            $this->context = 'private';
            $userPath = App::config('uploads_path') . '/user_' . $user['id'];
            if (!is_dir($userPath)) mkdir($userPath, 0777, true);
            $this->storage = new StorageService($userPath);
        }
    }

    public function list($vars) {
        $path = $_GET['path'] ?? '';
        $path = str_replace('..', '', $path);
        $path = ltrim($path, '/');

        try {
            $contents = $this->storage->listContents($path);
            $items = [];
            $hideSystem = Settings::get('hide_system_files', '1') === '1';
            $systemFiles = ['.git', '.env', 'vendor', 'storage', 'config', 'app', 'plugins', 'composer.json', 'composer.lock', 'public/index.php', '.htaccess'];

            foreach ($contents as $item) {
                $name = basename($item->path());

                if ($this->context === 'root' && !AuthService::isAdmin()) continue;
                if ($this->context === 'root' && $hideSystem && in_array($name, $systemFiles)) continue;

                $items[] = [
                    'name' => $name,
                    'path' => $item->path(),
                    'type' => $item->type(),
                    'size' => $item instanceof \League\Flysystem\FileAttributes ? $item->fileSize() : 0,
                    'last_modified' => $item instanceof \League\Flysystem\StorageAttributes ? $item->lastModified() : 0,
                ];
            }

            usort($items, function($a, $b) {
                if ($a['type'] === $b['type']) {
                    return strcasecmp($a['name'], $b['name']);
                }
                return $a['type'] === 'dir' ? -1 : 1;
            });

            header('Content-Type: application/json');
            return json_encode(['success' => true, 'data' => $items]);
        } catch (\Exception $e) {
            header('Content-Type: application/json', true, 500);
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function createFolder() {
        $data = json_decode(file_get_contents('php://input'), true);
        $path = $data['path'] ?? '';
        $name = $data['name'] ?? 'New Folder';
        $fullPath = trim($path . '/' . $name, '/');

        try {
            $this->storage->createDirectory($fullPath);
            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete() {
        $data = json_decode(file_get_contents('php://input'), true);
        $path = $data['path'] ?? '';

        try {
            if ($this->storage->exists($path)) {
                try {
                    $this->storage->delete($path);
                } catch (\Exception $e) {
                    $this->storage->deleteDirectory($path);
                }
            }
            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function rename() {
        $data = json_decode(file_get_contents('php://input'), true);
        $oldPath = $data['old_path'] ?? '';
        $newName = $data['new_name'] ?? '';

        $parent = dirname($oldPath);
        $newPath = ($parent === '.' || $parent === '/' ? '' : $parent . '/') . $newName;

        try {
            $this->storage->move($oldPath, $newPath);
            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function upload() {
        $path = $_POST['path'] ?? '';
        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $target = trim($path . '/' . $file['name'], '/');

            try {
                $stream = fopen($file['tmp_name'], 'r+');
                $this->storage->writeStream($target, $stream);
                fclose($stream);
                return json_encode(['success' => true]);
            } catch (\Exception $e) {
                return json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        return json_encode(['success' => false, 'message' => 'No file uploaded']);
    }

    public function zip() {
        $data = json_decode(file_get_contents('php://input'), true);
        $paths = $data['paths'] ?? [];
        $name = $data['name'] ?? 'archive.zip';
        $currentPath = $data['current_path'] ?? '';

        if (empty($paths)) {
            return json_encode(['success' => false, 'message' => 'No items selected']);
        }

        if (!class_exists('ZipArchive')) {
            return json_encode(['success' => false, 'message' => 'ZipArchive extension is not enabled on this server.']);
        }

        $zip = new \ZipArchive();
        $zipName = trim($currentPath . '/' . $name, '/');
        $tempZip = tempnam(sys_get_temp_dir(), 'zip');

        $res = $zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($res === TRUE) {
            foreach ($paths as $path) {
                $fullPath = $this->storage->getFullPath($path);
                if (is_dir($fullPath)) {
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($fullPath),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );

                    foreach ($files as $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = basename($path) . '/' . substr($filePath, strlen($fullPath) + 1);
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                } else {
                    $zip->addFile($fullPath, basename($path));
                }
            }
            $zip->close();

            try {
                $stream = fopen($tempZip, 'r');
                $this->storage->writeStream($zipName, $stream);
                fclose($stream);
                unlink($tempZip);
                return json_encode(['success' => true]);
            } catch (\Exception $e) {
                return json_encode(['success' => false, 'message' => 'Failed to save ZIP: ' . $e->getMessage()]);
            }
        }

        return json_encode(['success' => false, 'message' => 'Could not create ZIP archive. Error code: ' . $res]);
    }

    public function getContent() {
        $path = $_GET['path'] ?? '';
        try {
            $content = $this->storage->read($path);
            return json_encode(['success' => true, 'content' => $content]);
        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function save() {
        $data = json_decode(file_get_contents('php://input'), true);
        $path = $data['path'] ?? '';
        $content = $data['content'] ?? '';

        try {
            $this->storage->write($path, $content);
            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function copyToSpace() {
        $data = json_decode(file_get_contents('php://input'), true);
        $path = $data['path'] ?? '';

        if ($this->context !== 'public') {
            return json_encode(['success' => false, 'message' => 'Can only copy from public space']);
        }

        $user = AuthService::user();
        $userPath = App::config('uploads_path') . '/user_' . $user['id'];
        $userStorage = new StorageService($userPath);

        try {
            $fullSourcePath = $this->storage->getFullPath($path);
            $destination = basename($path);

            if (is_dir($fullSourcePath)) {
                $this->recursiveCopy($fullSourcePath, $userPath . '/' . $destination);
            } else {
                $stream = fopen($fullSourcePath, 'r');
                $userStorage->writeStream($destination, $stream);
                fclose($stream);
            }
            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    protected function recursiveCopy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function downloadDirect() {
        $path = $_GET['path'] ?? '';
        $fullPath = $this->storage->getFullPath($path);

        if (!file_exists($fullPath)) die("File not found.");

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function thumbnail() {
        $path = $_GET['path'] ?? '';
        $fullPath = $this->storage->getFullPath($path);

        if (!file_exists($fullPath)) die("File not found.");

        $mime = mime_content_type($fullPath);
        if (strpos($mime, 'image/') !== 0) {
            die("Not an image.");
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}
