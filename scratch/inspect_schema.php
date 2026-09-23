<?php
require_once __DIR__ . '/../admin/config.php';
$pdo = getMarketPDO();
foreach (['subscriptions', 'user_subscriptions', 'elite_circle_enrollments'] as $t) {
    echo "=== Table $t ===" . PHP_EOL;
    $cols = $pdo->query("PRAGMA table_info($t)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "  {$c['name']} ({$c['type']})" . PHP_EOL;
    }
}
