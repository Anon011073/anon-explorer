<?php

namespace App\Core;

use PDO;

class Database {
    public static function init(PDO $db) {
        $queries = [
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'user',
                storage_limit INTEGER NOT NULL DEFAULT 1073741824, -- 1GB
                avatar TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS shares (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                file_path TEXT NOT NULL,
                storage_context TEXT NOT NULL DEFAULT 'private',
                user_id INTEGER,
                token TEXT UNIQUE NOT NULL,
                password TEXT,
                expires_at DATETIME,
                max_downloads INTEGER,
                download_count INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS settings (
                key TEXT PRIMARY KEY,
                value TEXT
            )",
            "CREATE TABLE IF NOT EXISTS activity_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                action TEXT NOT NULL,
                details TEXT,
                ip_address TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )"
        ];

        foreach ($queries as $query) {
            $db->exec($query);
        }

        // Insert default admin if not exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $db->exec("INSERT INTO users (username, password, role) VALUES ('admin', '$password', 'admin')");
        }
    }
}
