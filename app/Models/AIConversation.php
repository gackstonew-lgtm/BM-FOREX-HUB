<?php
/**
 * BM Forex Hub — AI Conversation & Settings Model
 */

class AIConversation {
    private static function getPdo() {
        $dbDir = __DIR__ . '/../../storage/db';
        if (!file_exists($dbDir)) {
            @mkdir($dbDir, 0755, true);
        }
        $sqliteFile = $dbDir . '/ai_system.sqlite';
        $pdo = new PDO('sqlite:' . $sqliteFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::initDbTables($pdo);
        return $pdo;
    }

    private static function initDbTables(PDO $pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ai_conversations (
                id TEXT PRIMARY KEY,
                session_id TEXT NOT NULL,
                user_id TEXT DEFAULT 'visitor',
                user_message TEXT NOT NULL,
                ai_response TEXT NOT NULL,
                escalated INTEGER DEFAULT 0,
                ip_address TEXT DEFAULT '',
                user_agent TEXT DEFAULT '',
                created_at INTEGER DEFAULT 0
            );

            CREATE TABLE IF NOT EXISTS ai_settings (
                setting_key TEXT PRIMARY KEY,
                setting_value TEXT NOT NULL,
                updated_at INTEGER DEFAULT 0
            );
        ");
    }

    public static function logConversation($sessionId, $userId, $userMessage, $aiResponse, $escalated = 0) {
        $pdo = self::getPdo();
        $id = 'conv_' . bin2hex(random_bytes(8));
        $now = time();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt = $pdo->prepare("
            INSERT INTO ai_conversations (id, session_id, user_id, user_message, ai_response, escalated, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id,
            $sessionId ?: 'anon_session',
            $userId ?: 'visitor',
            trim($userMessage),
            trim($aiResponse),
            (int)$escalated,
            $ip,
            $ua,
            $now
        ]);

        return $id;
    }

    public static function getLogs($limit = 100, $search = '', $escalatedFilter = 'all') {
        $pdo = self::getPdo();
        $sql = "SELECT * FROM ai_conversations WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (user_message LIKE ? OR ai_response LIKE ? OR user_id LIKE ? OR session_id LIKE ?)";
            $term = "%$search%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($escalatedFilter === 'yes') {
            $sql .= " AND escalated = 1";
        } elseif ($escalatedFilter === 'no') {
            $sql .= " AND escalated = 0";
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = (int)$limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSetting($key, $default = null) {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("SELECT setting_value FROM ai_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['setting_value'] : $default;
    }

    public static function setSetting($key, $value) {
        $pdo = self::getPdo();
        $now = time();
        $stmt = $pdo->prepare("
            INSERT INTO ai_settings (setting_key, setting_value, updated_at)
            VALUES (?, ?, ?)
            ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, updated_at = excluded.updated_at
        ");
        return $stmt->execute([$key, is_array($value) ? json_encode($value) : $value, $now]);
    }

    public static function getAllSettings() {
        $config = require __DIR__ . '/../Config/ai.php';
        $pdo = self::getPdo();
        $stmt = $pdo->query("SELECT * FROM ai_settings");
        $dbSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'enabled'              => isset($dbSettings['enabled']) ? ($dbSettings['enabled'] === '1' || $dbSettings['enabled'] === 'true') : ($config['enabled'] ?? true),
            'welcome_message'      => $dbSettings['welcome_message'] ?? $config['welcome_message'],
            'suggested_questions'  => isset($dbSettings['suggested_questions']) ? (json_decode($dbSettings['suggested_questions'], true) ?: $config['suggested_questions']) : $config['suggested_questions'],
            'personality'          => $dbSettings['personality'] ?? $config['personality'],
            'fallback_message'     => $dbSettings['fallback_message'] ?? $config['fallback_message'],
            'escalation_message'   => $dbSettings['escalation_message'] ?? $config['escalation_message'],
            'whatsapp_url'         => $dbSettings['whatsapp_url'] ?? $config['whatsapp_url'],
            'office_hours'         => $dbSettings['office_hours'] ?? '24/7 Mon-Sun (East Africa Time EAT)',
        ];
    }
}
