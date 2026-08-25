<?php
/**
 * /api/crypto-capability.php
 * Admin API: reports which crypto settlement capabilities are live.
 * Requires an active admin session.
 */

if (!function_exists('sb_admin_required')) {
    require_once __DIR__ . '/../admin/config.php';
}
sb_admin_required();

require_once __DIR__ . '/crypto-config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

try {
    $kora   = new \App\Services\KoraCryptoCapabilityService();
    $kotani = new \App\Services\KotaniCryptoCapabilityService();

    $providers = [
        'kora'   => $kora->report(),
        'kotani' => $kotani->report(),
    ];

    $anyEnabled = false;
    foreach ($providers as $report) {
        if (is_array($report['capabilities'] ?? null) && in_array(true, $report['capabilities'], true)) {
            $anyEnabled = true;
            break;
        }
    }

    echo json_encode([
        'success'   => true,
        'providers' => $providers,
        'summary'   => $anyEnabled
            ? 'A crypto settlement provider is configured — crypto delivery/receipt is live.'
            : 'No settlement provider is configured yet — fiat payments work, but crypto delivery/receipt is pending a settlement provider (e.g. KOTANI_API_KEY).',
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
