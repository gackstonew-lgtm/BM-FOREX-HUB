<?php
$ctx = stream_context_create([
    'http' => [
        'timeout' => 15,
        'method'  => 'GET',
        'header'  => "User-Agent: BMForexHub/1.0\r\n",
    ],
]);
$body = @file_get_contents('http://157.173.193.93:5000/api/signals', false, $ctx);
if ($body === false) {
    echo "ERROR: Failed to connect to Python signal engine.\n";
    exit(1);
}

$data = json_decode($body, true);
echo "Signal count: " . (isset($data['signals']) ? count($data['signals']) : 0) . "\n\n";

if (isset($data['signals']) && is_array($data['signals'])) {
    foreach ($data['signals'] as $s) {
        echo "PAIR: " . ($s['pair']['code'] ?? 'N/A') . "\n";
        echo "CurrentPrice: " . ($s['currentPrice'] ?? 'N/A') . "\n";
        if (!empty($s['setup'])) {
            echo "  SETUP: " . json_encode($s['setup']) . "\n";
        } else {
            echo "  SETUP: none\n";
        }
        if (!empty($s['structure'])) {
            echo "  STRUCTURE: " . json_encode($s['structure']) . "\n";
        }
        echo "----------------------------------------\n";
    }
}
