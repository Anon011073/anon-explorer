<?php

namespace App\Core;

class View {
    public static function render($path, $data = []) {
        extract($data);
        $viewFile = __DIR__ . '/../../views/' . $path . '.php';
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$path}");
        }

        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

    public static function layout($layout, $content, $data = []) {
        extract($data);
        $layoutFile = __DIR__ . '/../../views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout file not found: {$layout}");
        }

        ob_start();
        include $layoutFile;
        return ob_get_clean();
    }
}
