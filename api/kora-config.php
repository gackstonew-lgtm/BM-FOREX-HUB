<?php
/**
 * Kora Pay Configuration & Environment Validator
 * NEVER expose secret keys to the frontend.
 */

// Load .env variables — always read from .env file to ensure keys are loaded
// regardless of server-level env var state
if (file_exists(__DIR__ . '/../.env')) {
    $env_lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $val) = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val, " \t\n\r\0\x0B\"'");
        // Always set from .env if the value is non-empty — fixes the intermittent
        // KORA_SECRET_KEY missing error caused by server-level blank env vars
        // overriding the .env file when using getenv() guard.
        if ($val !== '') {
            putenv("$key=$val");
            $_SERVER[$key] = $val;
            $_ENV[$key] = $val;
        }
    }
}

// Environment Variables Definition & Auto-Validation
// IMPORTANT: redirect_url MUST be a clean URL without query params.
// Kora Pay appends ?status=... params to the redirect_url —
// pre-existing query params create broken double-? URLs.
$kora_keys = [
    'KORA_PUBLIC_KEY'     => '',
    'KORA_SECRET_KEY'     => '',
    'KORA_ENCRYPTION_KEY' => '',
    'KORA_BASE_URL'       => 'https://api.korapay.com',
    'KORA_WEBHOOK_URL'    => 'https://bmforexhub.exchange/api/kora-webhook.php',
    'KORA_SUCCESS_URL'    => 'https://bmforexhub.exchange/subscribe.php',
    'KORA_FAILED_URL'     => 'https://bmforexhub.exchange/subscribe.php',
    'KORA_CANCEL_URL'     => 'https://bmforexhub.exchange/subscribe.php',
];

foreach ($kora_keys as $k => $default_val) {
    // Prefer .env-loaded value (already set above), then server env, then default
    $val = $_SERVER[$k] ?? getenv($k) ?: $default_val;
    // Strip any query string from redirect/webhook URLs that had old ?status= params
    // to prevent double-? malformed URLs when Kora appends its own parameters
    if (in_array($k, ['KORA_SUCCESS_URL', 'KORA_FAILED_URL', 'KORA_CANCEL_URL'])) {
        $val = strtok($val, '?');
    }
    putenv("$k=$val");
    $_SERVER[$k] = $val;
    $_ENV[$k] = $val;
}

// Core administrative dependencies
require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../engine_config.php';
require_once __DIR__ . '/../app/Services/Contracts/PaymentGatewayInterface.php';
require_once __DIR__ . '/../app/Services/KoraPaymentService.php';

if (!defined('SUPABASE_ANON_KEY'))   define('SUPABASE_ANON_KEY',   SUPABASE_ANON);
if (!defined('SUPABASE_SERVICE_KEY')) define('SUPABASE_SERVICE_KEY', SUPABASE_SERVICE);

require_once __DIR__ . '/../app/Services/CurrencyConversionService.php';

$currencyService = new \App\Services\CurrencyConversionService();

// Canonical plan definitions in USD with dynamic KES conversion
$KORA_PLANS_USD = [
    'copytrading' => [
        'name'          => 'Copy Trading Integration',
        'amount_usd'    => 249.00,
        'duration_days' => 36500,
        'description'   => 'Fully automated, professionally managed copy trading',
    ],
    'grid_monthly' => [
        'name'          => 'Grid Signal — Monthly',
        'amount_usd'    => 25.00,
        'duration_days' => 30,
        'description'   => 'Live market data and analysis tools',
    ],
    'grid_lifetime' => [
        'name'          => 'Grid Signal — Lifetime',
        'amount_usd'    => 499.00,
        'duration_days' => 36500,
        'description'   => 'Live market data and analysis tools — lifetime access',
    ],
    'classes_online' => [
        'name'          => 'Forex Classes — Online',
        'amount_usd'    => 399.00,
        'duration_days' => 36500,
        'description'   => 'Structured education from beginner to advanced — online',
    ],
    'classes_physical' => [
        'name'          => 'Forex Classes — Physical',
        'amount_usd'    => 599.00,
        'duration_days' => 36500,
        'description'   => 'Structured education from beginner to advanced — in person',
    ],
    // BM Elites Trading Circle Plans
    'elite_starter' => [
        'name'          => 'BM Elites — $1,000 USD',
        'amount_usd'    => 1000.00,
        'duration_days' => 36500,
        'description'   => 'BM Elites Starter Investment',
    ],
    'elite_intermediate' => [
        'name'          => 'BM Elites — $2,000 USD',
        'amount_usd'    => 2000.00,
        'duration_days' => 36500,
        'description'   => 'BM Elites Intermediate Investment',
    ],
    'elite_advanced' => [
        'name'          => 'BM Elites — $3,000 USD',
        'amount_usd'    => 3000.00,
        'duration_days' => 36500,
        'description'   => 'BM Elites Advanced Investment',
    ],
    'elite_professional' => [
        'name'          => 'BM Elites — $5,000 USD',
        'amount_usd'    => 5000.00,
        'duration_days' => 36500,
        'description'   => 'BM Elites Professional Investment',
    ],
    'elite_premium' => [
        'name'          => 'BM Elites — $6,000 USD',
        'amount_usd'    => 6000.00,
        'duration_days' => 36500,
        'description'   => 'BM Elites Premium Investment',
    ],
    'elite_elite' => [
        'name'          => 'BM Elites — $10,000 USD',
        'amount_usd'    => 10000.00,
        'duration_days' => 36500,
        'description'   => 'BM Elites Ultimate VIP Investment',
    ],
    // ── Indicator Platform Plans (Single $299 USD One-Time Payment) ──────
    'indicator_quantum_edge' => [
        'name'          => 'BM Quantum Edge',
        'amount_usd'    => 299.00,
        'duration_days' => 36500,
        'description'   => 'BM Quantum Edge — $299 One-Time Payment',
    ],
    // Aliases for backward compatibility with existing user records
    'indicator_vip' => [
        'name'          => 'BM Quantum Edge',
        'amount_usd'    => 299.00,
        'duration_days' => 36500,
        'description'   => 'BM Quantum Edge — $299 One-Time Payment',
    ],
    'indicator_gold' => [
        'name'          => 'BM Quantum Edge',
        'amount_usd'    => 299.00,
        'duration_days' => 36500,
        'description'   => 'BM Quantum Edge — $299 One-Time Payment',
    ],
    'indicator_silver' => [
        'name'          => 'BM Quantum Edge',
        'amount_usd'    => 299.00,
        'duration_days' => 36500,
        'description'   => 'BM Quantum Edge — $299 One-Time Payment',
    ],
];


$KORA_PLANS = [];
foreach ($KORA_PLANS_USD as $k => $p) {
    $kesVal = $currencyService->convertUsdToKes($p['amount_usd']);
    $KORA_PLANS[$k] = [
        'name'          => $p['name'],
        'amount_usd'    => $p['amount_usd'],
        'amount_kes'    => $kesVal,
        'duration_days' => $p['duration_days'],
        'description'   => $p['description'],
    ];
}

// Alias for backward compatibility
$FINGO_PLANS = &$KORA_PLANS;

// Base API URL
define('KORA_API_BASE', getenv('KORA_BASE_URL'));
