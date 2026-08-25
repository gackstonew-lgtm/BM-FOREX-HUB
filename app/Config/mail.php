<?php
/**
 * BM Forex Hub — Mail & Environment Configuration Loader
 */

if (!function_exists('bm_load_env')) {
    function bm_load_env($path) {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $val) = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val, "\"' ");
                if (!getenv($key)) {
                    putenv("{$key}={$val}");
                    $_ENV[$key] = $val;
                    $_SERVER[$key] = $val;
                }
            }
        }
    }
}

bm_load_env(__DIR__ . '/../../.env');

return [
    'driver' => getenv('MAIL_DRIVER') ?: 'smtp',
    'from' => [
        'address' => getenv('MAIL_FROM_ADDRESS') ?: 'info@admin.bmforexhub.exchange',
        'name'    => getenv('MAIL_FROM_NAME') ?: 'BM Forex Hub',
    ],
    'smtp' => [
        'host'       => getenv('MAIL_HOST') ?: 'smtp.hostinger.com',
        'port'       => (int)(getenv('MAIL_PORT') ?: 465),
        'username'   => getenv('MAIL_USERNAME') ?: '',
        'password'   => getenv('MAIL_PASSWORD') ?: '',
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'ssl',
    ],
    'sendgrid' => [
        'api_key' => getenv('SENDGRID_API_KEY') ?: '',
    ],
    'ses' => [
        'access_key' => getenv('SES_ACCESS_KEY') ?: '',
        'secret_key' => getenv('SES_SECRET_KEY') ?: '',
        'region'     => getenv('SES_REGION') ?: 'us-east-1',
    ],
    'resend' => [
        'api_key' => getenv('RESEND_API_KEY') ?: '',
    ],
    'brevo' => [
        'api_key' => getenv('BREVO_API_KEY') ?: '',
    ],
    'mailgun' => [
        'api_key' => getenv('MAILGUN_API_KEY') ?: '',
        'domain'  => getenv('MAILGUN_DOMAIN') ?: '',
    ],
];
