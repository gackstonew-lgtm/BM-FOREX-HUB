<?php
$_GET['route'] = 'health';
ob_start();
require_once __DIR__ . '/../api/engine.php';
ob_get_clean();

echo "=== 1. TESTING MULTI-PROVIDER LIVE XAUUSD FETCHING ===\n";
$goldData = engine_fetch_live_xauusd_price();
if ($goldData) {
    echo "SUCCESS: Retrieved Live Gold Price Data!\n";
    echo "  Source:    " . ($goldData['source'] ?? 'N/A') . "\n";
    echo "  Price:     $" . ($goldData['price'] ?? 'N/A') . "\n";
    echo "  Bid:       $" . ($goldData['bid'] ?? 'N/A') . "\n";
    echo "  Ask:       $" . ($goldData['ask'] ?? 'N/A') . "\n";
    echo "  Symbol:    " . ($goldData['symbol'] ?? 'N/A') . "\n";
    echo "  Timestamp: " . ($goldData['timestamp'] ?? 'N/A') . "\n";
} else {
    echo "FAILED: Live Gold Price Data unavailable\n";
}

echo "\n=== 2. TESTING API/GOLD.PHP ENDPOINT ===\n";
ob_start();
include __DIR__ . '/../api/gold.php';
$goldEndpointOutput = ob_get_clean();
echo "ENDPOINT OUTPUT: $goldEndpointOutput\n";

echo "\n=== 3. TESTING API/ENGINE.PHP PROXY INTEGRATION ===\n";
$_GET['route'] = 'signals';
ob_start();
include __DIR__ . '/../api/engine.php';
$engineOutput = ob_get_clean();

$data = json_decode($engineOutput, true);
if (isset($data['signals']) && is_array($data['signals'])) {
    foreach ($data['signals'] as $s) {
        if (($s['pair']['code'] ?? '') === 'XAUUSD') {
            echo "PAIR: XAUUSD\n";
            echo "  Current Market Price: $" . ($s['currentPrice'] ?? 'N/A') . "\n";
            if (!empty($s['price_meta'])) {
                echo "  Price Metadata: " . json_encode($s['price_meta']) . "\n";
            }
            if (!empty($s['setup'])) {
                $st = $s['setup'];
                echo "  SETUP: DIR {$st['direction']} | ENTRY {$st['entry']} | SL {$st['sl']} | TP1 {$st['tp1']} | SL PIPS {$st['risk_pips']} | R:R 1:{$st['rr']}\n";
            } else {
                echo "  SETUP: Watching / No Setup\n";
            }
        }
    }
}
