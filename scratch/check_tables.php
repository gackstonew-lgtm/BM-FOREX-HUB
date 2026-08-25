<?php
require_once __DIR__ . '/../engine_config.php';
$dbFile = DB_PATH;
echo "DB File: " . $dbFile . "\n";
if (file_exists($dbFile)) {
    $pdo = new PDO('sqlite:' . $dbFile);
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables:\n";
    print_r($tables);
} else {
    echo "DB File does not exist.\n";
}
