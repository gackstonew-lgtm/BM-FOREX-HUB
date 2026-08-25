<?php
// migrations/20260801_refactor_cms_modules.php
// Database Migration for Market Overview, Articles, Videos, Promotions, Signals, & Announcements

$databasePath = __DIR__ . '/../storage/database.sqlite';
if (!file_exists($databasePath)) {
    die("Database file not found at $databasePath\n");
}

try {
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = <<<SQL
BEGIN TRANSACTION;

-- 1. Market Overview Table
CREATE TABLE IF NOT EXISTS market_overview (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    content TEXT,
    banner_url TEXT,
    button_label TEXT,
    button_url TEXT,
    priority INTEGER DEFAULT 0,
    status TEXT DEFAULT 'published',
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
);

-- 2. Educational Articles Table
CREATE TABLE IF NOT EXISTS articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT,
    category TEXT DEFAULT 'General',
    summary TEXT,
    content TEXT,
    cover_image TEXT,
    author_name TEXT DEFAULT 'BM Forex Hub Team',
    author_photo TEXT,
    read_time_minutes INTEGER DEFAULT 5,
    status TEXT DEFAULT 'published',
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
);

-- 3. Featured Videos Table
CREATE TABLE IF NOT EXISTS videos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    video_url TEXT NOT NULL,
    embed_url TEXT,
    thumbnail_url TEXT,
    duration TEXT DEFAULT '10:00',
    category TEXT DEFAULT 'Strategy',
    status TEXT DEFAULT 'published',
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
);

-- 4. Promotions Table
CREATE TABLE IF NOT EXISTS promotions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    banner_url TEXT,
    button_text TEXT DEFAULT 'Claim Now',
    button_link TEXT,
    start_date TEXT,
    end_date TEXT,
    discount_percentage INTEGER DEFAULT 0,
    status TEXT DEFAULT 'active',
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
);

-- 5. Featured Signals Table
CREATE TABLE IF NOT EXISTS featured_signals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pair TEXT NOT NULL,
    type TEXT NOT NULL DEFAULT 'BUY',
    entry_price TEXT NOT NULL,
    stop_loss TEXT NOT NULL,
    take_profit_1 TEXT NOT NULL,
    take_profit_2 TEXT,
    confidence_level INTEGER DEFAULT 90,
    notes TEXT,
    status TEXT DEFAULT 'active',
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
);

-- 6. Announcements Table
CREATE TABLE IF NOT EXISTS announcements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    message TEXT NOT NULL,
    category TEXT DEFAULT 'General',
    target_audience TEXT DEFAULT 'All Users',
    status TEXT DEFAULT 'active',
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
);

COMMIT;
SQL;

    $pdo->exec($sql);
    echo "Migration 20260801_refactor_cms_modules executed successfully.\n";

} catch (Exception $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
