<?php
// migrations/20260801_refactor_live_classes.php
// Comprehensive Database Migration for Live Classes Refactor

$databasePath = __DIR__ . '/../storage/database.sqlite';
if (!file_exists($databasePath)) {
    die("Database file not found at $databasePath\n");
}

try {
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = <<<SQL
BEGIN TRANSACTION;

-- Create or alter live_classes table
CREATE TABLE IF NOT EXISTS live_classes_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    instructor_name TEXT NOT NULL,
    instructor_photo TEXT,
    meeting_platform TEXT DEFAULT 'Google Meet',
    meeting_link TEXT NOT NULL,
    meeting_id TEXT,
    meeting_password TEXT,
    start_datetime TEXT NOT NULL,
    end_datetime TEXT,
    timezone TEXT DEFAULT 'Africa/Nairobi (EAT)',
    duration_minutes INTEGER NOT NULL DEFAULT 60,
    class_banner TEXT,
    class_notes_pdf TEXT,
    recording_link TEXT,
    max_attendees INTEGER DEFAULT 500,
    visibility TEXT DEFAULT 'published',
    live_status TEXT DEFAULT 'scheduled',
    meeting_status TEXT DEFAULT 'waiting',
    class_status TEXT DEFAULT 'active',
    is_live INTEGER DEFAULT 0,
    meeting_ready INTEGER DEFAULT 0,
    created_by TEXT DEFAULT 'Admin',
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
);

-- Copy existing data from old table if exists
INSERT INTO live_classes_new (
    id, title, description, instructor_name, meeting_link, start_datetime, duration_minutes, is_live, meeting_ready, created_at
)
SELECT 
    id, 
    title, 
    COALESCE(description, ''), 
    COALESCE(instructor, 'BM Forex Hub Team'), 
    meeting_link, 
    COALESCE(class_date || 'T' || class_time, datetime('now')), 
    COALESCE(duration_minutes, 60), 
    CASE WHEN is_live THEN 1 ELSE 0 END, 
    CASE WHEN meeting_ready THEN 1 ELSE 0 END,
    COALESCE(created_at, datetime('now'))
FROM live_classes;

DROP TABLE live_classes;
ALTER TABLE live_classes_new RENAME TO live_classes;

-- Create notifications table if not exists
CREATE TABLE IF NOT EXISTS notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER DEFAULT 0,
    type TEXT NOT NULL,
    payload TEXT NOT NULL,
    read_flag INTEGER NOT NULL DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now'))
);

COMMIT;
SQL;

    $pdo->exec($sql);
    echo "Migration 20260801_refactor_live_classes executed successfully.\n";

} catch (Exception $e) {
    // If live_classes didn't exist or table copy failed, run fallback CREATE TABLE
    try {
        $pdo = new PDO('sqlite:' . $databasePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sqlFallback = <<<SQL
CREATE TABLE IF NOT EXISTS live_classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    instructor_name TEXT NOT NULL,
    instructor_photo TEXT,
    meeting_platform TEXT DEFAULT 'Google Meet',
    meeting_link TEXT NOT NULL,
    meeting_id TEXT,
    meeting_password TEXT,
    start_datetime TEXT NOT NULL,
    end_datetime TEXT,
    timezone TEXT DEFAULT 'Africa/Nairobi (EAT)',
    duration_minutes INTEGER NOT NULL DEFAULT 60,
    class_banner TEXT,
    class_notes_pdf TEXT,
    recording_link TEXT,
    max_attendees INTEGER DEFAULT 500,
    visibility TEXT DEFAULT 'published',
    live_status TEXT DEFAULT 'scheduled',
    meeting_status TEXT DEFAULT 'waiting',
    class_status TEXT DEFAULT 'active',
    is_live INTEGER DEFAULT 0,
    meeting_ready INTEGER DEFAULT 0,
    created_by TEXT DEFAULT 'Admin',
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER DEFAULT 0,
    type TEXT NOT NULL,
    payload TEXT NOT NULL,
    read_flag INTEGER NOT NULL DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now'))
);
SQL;
        $pdo->exec($sqlFallback);
        echo "Fallback migration applied successfully.\n";
    } catch (Exception $ex) {
        die("Migration failed: " . $ex->getMessage() . "\n");
    }
}
