<?php
require_once __DIR__ . '/../admin/config.php';
$pdo = getMarketPDO();
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables: " . implode(', ', $tables) . PHP_EOL;
foreach (['profiles', 'subscriptions', 'elite_subscriptions', 'payments', 'elite_circle_enrollments'] as $t) {
    if (in_array($t, $tables)) {
        $cnt = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "$t: $cnt records" . PHP_EOL;
    }
}
