<?php

namespace App\Core;

class App {
    protected static $container;

    public static function setContainer(Container $container) {
        self::$container = $container;
    }

    public static function get($name) {
        return self::$container->get($name);
    }

    public static function config($key, $default = null) {
        $config = self::get('config');
        return $config[$key] ?? $default;
    }

    public static function url($path = '') {
        $base = rtrim(self::get('base_url') ?? '', '/');
        return $base . '/' . ltrim($path, '/');
    }
}
