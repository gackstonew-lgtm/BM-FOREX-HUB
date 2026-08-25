<?php
/**
 * BM Forex Hub — Contact Export & Audit Log Model
 */

class ContactExport {
    private static function getPdo() {
        $dbDir = __DIR__ . '/../../storage/db';
        if (!file_exists($dbDir)) {
            @mkdir($dbDir, 0755, true);
        }
        $sqliteFile = $dbDir . '/email_system.sqlite';
        $pdo = new PDO('sqlite:' . $sqliteFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::initDbTables($pdo);
        return $pdo;
    }

    private static function initDbTables(PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS contact_export_logs (
                id TEXT PRIMARY KEY,
                admin_id TEXT NOT NULL,
                export_format TEXT NOT NULL,
                record_count INTEGER DEFAULT 0,
                included_fields TEXT NOT NULL,
                filter_criteria TEXT DEFAULT '{}',
                ip_address TEXT DEFAULT '',
                user_agent TEXT DEFAULT '',
                created_at INTEGER DEFAULT 0
            );
        ");
    }

    /**
     * Record an audit log entry for a contact export operation
     */
    public static function logExport($adminId, $format, $count, $fields = [], $filters = []) {
        $pdo = self::getPdo();
        $id = 'exp_' . bin2hex(random_bytes(10));
        $now = time();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt = $pdo->prepare("
            INSERT INTO contact_export_logs (id, admin_id, export_format, record_count, included_fields, filter_criteria, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id,
            $adminId,
            strtoupper($format),
            (int)$count,
            json_encode($fields),
            json_encode($filters),
            $ip,
            $ua,
            $now
        ]);

        return $id;
    }

    /**
     * Retrieve audit log entries for export history
     */
    public static function getAuditLogs($limit = 100) {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM contact_export_logs ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([(int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
