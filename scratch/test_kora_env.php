<?php
require_once 'api/kora-config.php';
echo 'SECRET_KEY: ' . (getenv('KORA_SECRET_KEY') ? 'LOADED (' . strlen(getenv('KORA_SECRET_KEY')) . ' chars)' : 'MISSING') . PHP_EOL;
echo 'SUCCESS_URL: ' . ($_SERVER['KORA_SUCCESS_URL'] ?? getenv('KORA_SUCCESS_URL') ?? 'NOT SET') . PHP_EOL;
echo 'WEBHOOK_URL: ' . ($_SERVER['KORA_WEBHOOK_URL'] ?? getenv('KORA_WEBHOOK_URL') ?? 'NOT SET') . PHP_EOL;
echo 'Public KEY: ' . (getenv('KORA_PUBLIC_KEY') ? 'LOADED (' . strlen(getenv('KORA_PUBLIC_KEY')) . ' chars)' : 'MISSING') . PHP_EOL;
