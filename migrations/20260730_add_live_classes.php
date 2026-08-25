<?php
// migrations/20260730_add_live_classes.php
// Execute this script once to create tables for Live Classes, Resources, and Notifications.

$databasePath = __DIR__ . '/../storage/database.sqlite';
if (!file_exists($databasePath)) {
    die('Database file not found');
}

try {
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = <<<SQL
BEGIN TRANSACTION;

CREATE TABLE IF NOT EXISTS live_classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    subtitle TEXT,
    description TEXT,
    instructor TEXT,
    start_datetime TEXT NOT NULL,
    duration_minutes INTEGER NOT NULL,
    difficulty TEXT,
    meet_url TEXT,
    host_name TEXT,
    status TEXT CHECK(status IN ('draft','scheduled','live','completed','canceled')) NOT NULL DEFAULT 'draft',
    max_participants INTEGER,
    thumbnail_path TEXT,
    cover_path TEXT,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS class_resources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL REFERENCES live_classes(id) ON DELETE CASCADE,
    type TEXT CHECK(type IN ('pdf','image','link')) NOT NULL,
    path_or_url TEXT NOT NULL,
    title TEXT,
    sort_order INTEGER DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    type TEXT NOT NULL,
    payload TEXT NOT NULL,
    read_flag INTEGER NOT NULL DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now'))
);

COMMIT;
SQL;
    $pdo->exec($sql);
    echo "Migration applied successfully\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
