<?php

namespace App\Core;

class Plugin {
    public static function load() {
        $pluginDir = __DIR__ . '/../../plugins';
        if (!is_dir($pluginDir)) return;

        $plugins = array_diff(scandir($pluginDir), ['.', '..']);
        foreach ($plugins as $plugin) {
            $file = $pluginDir . '/' . $plugin . '/index.php';
            if (file_exists($file)) {
                include_once $file;
            }
        }
    }
}
