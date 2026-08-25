<?php
$_GET['route'] = 'signals';

// Mock permanent admin token for testing full access
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer admin_test';

// Run engine script
ob_start();
// Include kora-config to ensure bm_has_permanent_access is defined
require_once __DIR__ . '/../api/kora-config.php';
// Temporarily mock engine_signal_access_allowed if needed or override
include __DIR__ . '/../api/engine.php';
$output = ob_get_clean();

$data = json_decode($output, true);
if (!$data) {
    echo "ERROR decoding JSON:\n$output\n";
    exit(1);
}

echo "=== XAUUSD SIGNAL DATA FROM ENGINE ===\n";
if (isset($data['signals']) && is_array($data['signals'])) {
    foreach ($data['signals'] as $s) {
        $code = $s['pair']['code'] ?? '';
        if ($code === 'XAUUSD') {
            echo "PAIR: XAUUSD\n";
            echo "Current Price: " . ($s['currentPrice'] ?? 'N/A') . "\n";
            if (!empty($s['setup'])) {
                $st = $s['setup'];
                echo "SETUP:\n";
                echo "  Direction: " . ($st['direction'] ?? '') . "\n";
                echo "  Entry:     " . ($st['entry'] ?? '') . "\n";
                echo "  SL:        " . ($st['sl'] ?? '') . "\n";
                echo "  TP1:       " . ($st['tp1'] ?? '') . "\n";
                echo "  TP2:       " . ($st['tp2'] ?? '') . "\n";
                echo "  Risk Pips: " . ($st['risk_pips'] ?? '') . "\n";
                echo "  R:R Ratio: 1:" . ($st['rr'] ?? '') . "\n";
            } else {
                echo "SETUP: None / Filtered Out\n";
            }
            if (!empty($s['structure'])) {
                echo "STRUCTURE: " . json_encode($s['structure']) . "\n";
            }
        }
    }
}

// Check cache file
$cacheFile = sys_get_temp_dir() . '/bm_gold_cache.json';
if (file_exists($cacheFile)) {
    echo "\nGOLD CACHE FILE: " . file_get_contents($cacheFile) . "\n";
}
