<?php
$_GET['route'] = 'signals';

// Capture output of api/engine.php
ob_start();
include __DIR__ . '/../api/engine.php';
$output = ob_get_clean();

$data = json_decode($output, true);
if (!$data) {
    echo "ERROR decoding JSON from api/engine.php:\n";
    echo $output . "\n";
    exit(1);
}

echo "Proxy Response Signal count: " . (isset($data['signals']) ? count($data['signals']) : 0) . "\n\n";

if (isset($data['signals']) && is_array($data['signals'])) {
    foreach ($data['signals'] as $s) {
        $code = $s['pair']['code'] ?? 'N/A';
        echo "PAIR: $code\n";
        echo "  CurrentPrice: " . ($s['currentPrice'] ?? 'N/A') . "\n";
        if (!empty($s['setup'])) {
            $st = $s['setup'];
            echo "  SETUP:\n";
            echo "    Direction: " . ($st['direction'] ?? '') . "\n";
            echo "    Entry:     " . ($st['entry'] ?? '') . "\n";
            echo "    SL:        " . ($st['sl'] ?? '') . "\n";
            echo "    TP1:       " . ($st['tp1'] ?? '') . "\n";
            echo "    TP2:       " . ($st['tp2'] ?? '') . "\n";
            echo "    Risk Pips: " . ($st['risk_pips'] ?? '') . "\n";
            echo "    R:R Ratio: 1:" . ($st['rr'] ?? '') . "\n";
        } else {
            echo "  SETUP: [REJECTED / NO SETUP]\n";
        }
        echo "----------------------------------------\n";
    }
}
