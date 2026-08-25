<?php
$_GET['route'] = 'health';
ob_start();
require_once __DIR__ . '/../api/engine.php';
ob_get_clean();

// Fetch raw signals from Python backend directly
$ctx = stream_context_create(['http' => ['timeout' => 15, 'header' => "User-Agent: BMForexHub/1.0\r\n"]]);
$body = file_get_contents('http://157.173.193.93:5000/api/signals', false, $ctx);
$data = json_decode($body, true);

echo "=== RAW INPUT SETUPS FROM PYTHON ENGINE ===\n";
foreach ($data['signals'] as $s) {
    $code = $s['pair']['code'] ?? 'N/A';
    if (!empty($s['setup'])) {
        $st = $s['setup'];
        $type = $st['type'] ?? $st['strategy'] ?? 'N/A';
        echo sprintf("PAIR: %-7s | DIR: %-4s | TYPE: %-8s | ENTRY: %-10.4f | RAW SL PIPS: %-5.1f | CONF: %s\n",
            $code, $st['direction'], $type, $st['entry'], $st['risk_pips'], $st['confidence'] ?? 'N/A'
        );
    }
}

// Clear log
$logFile = sys_get_temp_dir() . '/bm_signal_engine.log';
if (file_exists($logFile)) @unlink($logFile);

// Process signals through Day-Trading refinement pipeline
engine_optimize_and_validate_signals($data);

echo "\n=== PROCESSED DAY-TRADING SIGNALS ===\n";
foreach ($data['signals'] as $s) {
    $code = $s['pair']['code'] ?? 'N/A';
    if (!empty($s['setup'])) {
        $st = $s['setup'];
        echo sprintf("PAIR: %-7s | DIR: %-4s | ENTRY: %-10.4f | SL: %-10.4f | TP1: %-10.4f | SL PIPS: %-5.1f | R:R: 1:%-4.2f [ACTIVE DAY-TRADE]\n",
            $code, $st['direction'], $st['entry'], $st['sl'], $st['tp1'], $st['risk_pips'], $st['rr']
        );
    } else {
        echo "PAIR: $code | STATUS: NO SETUP / WATCHING (Filtered as Scalp / Low HTF Quality)\n";
    }
}

if (file_exists($logFile)) {
    echo "\n=== SERVER-SIDE LOG DEBUG OUTPUT ===\n";
    echo file_get_contents($logFile);
}
