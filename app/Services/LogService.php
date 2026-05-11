<?php

namespace App\Services;

use App\Core\App;

class LogService {
    public static function log($action, $details = null) {
        $db = App::get('db');
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $details, $ip]);
    }

    public static function getRecent($limit = 10, $action = null, $offset = 0) {
        $db = App::get('db');
        $query = "
            SELECT l.*, u.username
            FROM activity_logs l
            LEFT JOIN users u ON l.user_id = u.id
        ";

        if ($action) {
            $query .= " WHERE l.action = ? ";
        }

        $query .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ? ";

        $stmt = $db->prepare($query);
        $idx = 1;
        if ($action) {
            $stmt->bindValue($idx++, $action);
        }
        $stmt->bindValue($idx++, $limit, \PDO::PARAM_INT);
        $stmt->bindValue($idx++, $offset, \PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
