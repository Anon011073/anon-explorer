<?php

namespace App\Core;

use PDO;

class Settings {
    protected static $settings = [];

    public static function load(PDO $db) {
        $stmt = $db->query("SELECT key, value FROM settings");
        while ($row = $stmt->fetch()) {
            self::$settings[$row['key']] = $row['value'];
        }
    }

    public static function get($key, $default = null) {
        return self::$settings[$key] ?? $default;
    }

    public static function set(PDO $db, $key, $value) {
        $stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
        self::$settings[$key] = $value;
    }
}
