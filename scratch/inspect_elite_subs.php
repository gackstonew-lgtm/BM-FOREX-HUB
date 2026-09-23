<?php
require_once __DIR__ . '/../admin/config.php';
$pdo = getMarketPDO();
$rows = $pdo->query("SELECT id, user_id, username, plan, plan_key, plan_name, amount_usd, status, starts_at, expires_at, created_at FROM subscriptions WHERE plan LIKE 'elite_%' OR plan = 'all' OR plan_key LIKE 'elite_%'")->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($rows) . " elite subscriptions:" . PHP_EOL;
foreach ($rows as $r) {
    print_r($r);
}
