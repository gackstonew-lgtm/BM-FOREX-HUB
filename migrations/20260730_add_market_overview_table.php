<?php
require_once __DIR__ . '/../engine_config.php';
/**
 * Migration: create market_overview table
 */
try {
    $db = new PDO('sqlite:' . DB_PATH);
    $sql = "CREATE TABLE IF NOT EXISTS market_overview (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        subtitle TEXT,
        content TEXT NOT NULL,
        featured_image TEXT,
        banner_image TEXT,
        external_url TEXT,
        button_text TEXT,
        button_url TEXT,
        video_url TEXT,
        display_priority INTEGER DEFAULT 0,
        category TEXT,
        status TEXT CHECK(status IN ('draft','published','archived')) NOT NULL DEFAULT 'draft',
        start_date DATETIME,
        expiry_date DATETIME,
        is_featured INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );";
    $db->exec($sql);
    echo "Migration applied successfully.";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
