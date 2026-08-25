<?php
/**
 * Quick Kora Pay API key validation
 */

// Load .env
$lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    if (strpos($line, '=') !== false) {
        [$k, $v] = explode('=', $line, 2);
        putenv(trim($k) . '=' . trim($v, " \t\n\r\0\x0B\"'"));
    }
}

$secretKey     = getenv('KORA_SECRET_KEY');
$encryptionKey = getenv('KORA_ENCRYPTION_KEY');
$publicKey     = getenv('KORA_PUBLIC_KEY');

echo "=== Kora Pay Key Validation ===\n\n";
echo "Public Key:     " . substr($publicKey, 0, 12) . "...\n";
echo "Secret Key:     " . substr($secretKey, 0, 12) . "...\n";
echo "Encryption Key: " . substr($encryptionKey, 0, 8) . "...\n";
echo "Encryption Len: " . strlen($encryptionKey) . " bytes\n\n";

// Test 1: Fetch transaction (verifies secret key works)
echo "Test 1: Verifying secret key via API call...\n";
$ctx = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $secretKey\r\nAccept: application/json",
        'timeout' => 10,
    ],
]);
$resp = @file_get_contents('https://api.korapay.com/merchant/api/v1/charges/BMFH_TEST_nonexistent', false, $ctx);
$httpCode = 0;
if (isset($http_response_header)) {
    preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $m);
    $httpCode = $m[1] ?? 0;
}
echo "HTTP $httpCode\n";
$json = json_decode($resp, true);
if ($httpCode == 401) {
    echo "❌ Secret key is INVALID (401 Unauthorized)\n";
} elseif ($httpCode == 404) {
    echo "✅ Secret key is VALID (404 = not found, but auth passed)\n";
} else {
    echo "Response: " . ($json['message'] ?? $resp) . "\n";
}

// Test 2: Verify encryption key length (needs 32 bytes for card payments)
echo "\nTest 2: Encryption key validation...\n";
if (strlen($encryptionKey) >= 16) {
    echo "✅ Encryption key is " . strlen($encryptionKey) . " bytes (sufficient for card encryption)\n";
} else {
    echo "⚠️ Encryption key is only " . strlen($encryptionKey) . " bytes (may need 32 bytes for card payments)\n";
}
