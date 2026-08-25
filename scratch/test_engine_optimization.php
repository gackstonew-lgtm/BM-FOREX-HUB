<?php
$_GET['route'] = 'health';
ob_start();
require_once __DIR__ . '/../api/engine.php';
ob_get_clean();

// Fetch raw signals from Python backend directly
$ctx = stream_context_create(['http' => ['timeout' => 15, 'header' => "User-Agent: BMForexHub/1.0\r\n"]]);
$body = file_get_contents('http://157.173.193.93:5000/api/signals', false, $ctx);
$data = json_decode($body, true);

echo "=== RAW SIGNALS FROM PYTHON ENGINE ===\n";
foreach ($data['signals'] as $s) {
    $code = $s['pair']['code'] ?? 'N/A';
    if (!empty($s['setup'])) {
        $st = $s['setup'];
        echo sprintf("PAIR: %-7s | DIR: %-4s | ENTRY: %-10.4f | SL: %-10.4f | TP1: %-10.4f | RAW SL PIPS: %-5.1f | RAW RR: %-4s\n",
            $code, $st['direction'], $st['entry'], $st['sl'], $st['tp1'], $st['risk_pips'], $st['rr']
        );
    }
}

// Clear old log file for clean output
$logFile = sys_get_temp_dir() . '/bm_signal_engine.log';
if (file_exists($logFile)) @unlink($logFile);

// Now run our optimization & validation layer
engine_optimize_and_validate_signals($data);

echo "\n=== OPTIMIZED & VALIDATED SIGNALS ===\n";
foreach ($data['signals'] as $s) {
    $code = $s['pair']['code'] ?? 'N/A';
    if (!empty($s['setup'])) {
        $st = $s['setup'];
        echo sprintf("PAIR: %-7s | DIR: %-4s | ENTRY: %-10.4f | SL: %-10.4f | TP1: %-10.4f | OPT SL PIPS: %-5.1f | OPT R:R: 1:%-4.2f\n",
            $code, $st['direction'], $st['entry'], $st['sl'], $st['tp1'], $st['risk_pips'], $st['rr']
        );
    } else {
        echo "PAIR: $code | SETUP: REJECTED / FILTERED OUT\n";
    }
}

if (file_exists($logFile)) {
    echo "\n=== SERVER-SIDE LOG DEBUG OUTPUT ===\n";
    echo file_get_contents($logFile);
}
