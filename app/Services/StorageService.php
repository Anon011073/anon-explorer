<?php

namespace App\Services;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use App\Core\App;

class StorageService {
    protected $filesystem;
    protected $rootPath;

    public function __construct($path = null) {
        $this->rootPath = $path ?: App::config('uploads_path');
        if (!is_dir($this->rootPath)) {
            mkdir($this->rootPath, 0777, true);
        }
        $adapter = new LocalFilesystemAdapter($this->rootPath);
        $this->filesystem = new Filesystem($adapter);
    }

    public function listContents($path = '', $recursive = false) {
        return $this->filesystem->listContents($path, $recursive);
    }

    public function createDirectory($path) {
        $this->filesystem->createDirectory($path);
    }

    public function write($path, $contents) {
        $this->filesystem->write($path, $contents);
    }

    public function writeStream($path, $resource) {
        $this->filesystem->writeStream($path, $resource);
    }

    public function read($path) {
        return $this->filesystem->read($path);
    }

    public function delete($path) {
        $this->filesystem->delete($path);
    }

    public function deleteDirectory($path) {
        $this->filesystem->deleteDirectory($path);
    }

    public function move($source, $destination) {
        $this->filesystem->move($source, $destination);
    }

    public function copy($source, $destination) {
        $this->filesystem->copy($source, $destination);
    }

    public function exists($path) {
        return $this->filesystem->has($path);
    }

    public function getMetadata($path) {
        return [
            'name' => basename($path),
            'path' => $path,
            'size' => $this->filesystem->fileSize($path),
            'last_modified' => $this->filesystem->lastModified($path),
            'mime_type' => $this->filesystem->mimeType($path),
            'type' => $this->filesystem->fileExists($path) ? 'file' : 'dir'
        ];
    }

    public function getFullPath($path) {
        return $this->rootPath . '/' . ltrim($path, '/');
    }
}
