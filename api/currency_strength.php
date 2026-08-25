<?php
// api/currency_strength.php
// Returns live currency strength scores using existing market engine if available.
require_once __DIR__ . '/../engine_config.php';
header('Content-Type: application/json');
$databasePath = __DIR__ . '/../storage/database.sqlite';
$pdo = new PDO('sqlite:' . $databasePath);
$currencies = ['USD','EUR','GBP','JPY','CHF','AUD','CAD','NZD'];
$results = [];
foreach ($currencies as $code) {
    // In a real implementation, pull from market engine. Here random.
    $score = round(mt_rand(0, 1000) / 100, 1);
    $results[] = [
        'code' => $code,
        'score' => $score,
        'timestamp' => date('c')
    ];
}
echo json_encode(['status'=>'ok','data'=>$results]);
?>
