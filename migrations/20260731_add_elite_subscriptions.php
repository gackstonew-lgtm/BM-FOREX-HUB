<?php
require_once __DIR__ . '/../engine_config.php';
/**
 * Migration: create elite_subscriptions table
 */
try {
    $db = new PDO('sqlite:' . DB_PATH);
    $sql = "CREATE TABLE IF NOT EXISTS elite_subscriptions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        plan TEXT NOT NULL,
        starts_at DATETIME NOT NULL,
        expires_at DATETIME NOT NULL,
        payment_id INTEGER NOT NULL,
        status TEXT CHECK(status IN ('active','cancelled','expired')) NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );";
    $db->exec($sql);
    echo "Migration elite_subscriptions applied successfully.";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
