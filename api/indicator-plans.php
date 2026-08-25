<?php
/**
 * GET /api/indicator-plans.php
 * Returns available indicator subscription plans with live KES pricing.
 *
 * Public endpoint (no auth required).
 *
 * Response 200: array of plan objects
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=300');

require_once __DIR__ . '/kora-config.php';

// Canonical indicator plans with live KES conversion
$usdToKes = function(float $usd): float {
    try {
        $svc = new \App\Services\CurrencyConversionService();
        return $svc->convertUsdToKes($usd);
    } catch (\Throwable $e) {
        return round($usd * 130, 2); // fallback rate
    }
};

$plans = [
    'indicator_quantum_edge' => [
        'plan_key'         => 'indicator_quantum_edge',
        'plan_name'        => 'BM Quantum Edge',
        'tagline'          => 'Ultimate algorithmic trading suite',
        'description'      => 'Full lifetime access to BM Quantum Edge multi-engine indicator, live signals, AI assistant, and premium dashboard.',
        'price_usd'        => 299.00,
        'price_kes'        => $usdToKes(299.00),
        'duration_days'    => 36500,
        'payment_type'     => 'one_time',
        'is_one_time'      => true,
        'indicator_access' => true,
        'signals_access'   => true,
        'ai_access'        => true,
        'premium_dashboard'=> true,
        'is_popular'       => true,
        'badge'            => 'One-Time Payment',
        'color'            => '#1677FF',
        'features' => [
            'BM Quantum Edge Advanced Multi-Engine Indicator',
            'Full Lifetime Access — No Monthly or Recurring Fees',
            'XAUUSD, EURUSD, GBPUSD, USDJPY, BTCUSD, NAS100, US30',
            'HTF Trend Filter & Market Structure Detection',
            'Institutional Supply & Demand Zones',
            'Smart Entry Signals with Exact SL/TP Levels',
            'Signal Strength Score (0–100) & Session Overlay',
            'Live Trade Signals & BUY/SELL Alerts',
            'BM Forex AI Assistant Integration',
            '24/7 Premium Trading Dashboard Access',
            'VIP Private Trading Circle & Dedicated Support',
        ],
    ],
];

echo json_encode(array_values($plans));
exit;
