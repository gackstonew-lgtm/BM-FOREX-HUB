<?php
/**
 * BM Forex Hub — Email Template Rendering Service
 */

require_once __DIR__ . '/MailService.php';

class EmailTemplateService {
    private static $secretKey = null;

    public static function getSecretKey() {
        if (self::$secretKey === null) {
            self::$secretKey = getenv('UNSUBSCRIBE_SECRET_KEY') ?: 'bm_forex_hub_secret_key_default';
        }
        return self::$secretKey;
    }

    /**
     * Generate HMAC token for unsubscribe link
     */
    public static function generateUnsubscribeLink($email) {
        $appUrl = rtrim(getenv('APP_URL') ?: 'https://bmforexhub.exchange', '/');
        $token  = hash_hmac('sha256', strtolower(trim($email)), self::getSecretKey());
        return $appUrl . '/unsubscribe.php?email=' . urlencode($email) . '&token=' . $token;
    }

    /**
     * Verify unsubscribe token
     */
    public static function verifyUnsubscribeToken($email, $token) {
        if (empty($email) || empty($token)) return false;
        $expected = hash_hmac('sha256', strtolower(trim($email)), self::getSecretKey());
        return hash_equals($expected, $token);
    }

    /**
     * Render Bulk Email HTML using notification.html template
     */
    public static function renderNotification($subject, $bodyContent, $user = []) {
        $templatePath = __DIR__ . '/../Templates/notification.html';
        $template = file_exists($templatePath) ? file_get_contents($templatePath) : '{body_content}';

        $appUrl = rtrim(getenv('APP_URL') ?: 'https://bmforexhub.exchange', '/');
        $logoUrl = $appUrl . '/BM-ForexHub-Logo-Circle.png';

        $email = $user['email'] ?? '';
        $unsubscribeLink = $email ? self::generateUnsubscribeLink($email) : '#';

        // Personalization replacements
        $replacements = [
            '{{firstName}}'        => htmlspecialchars($user['first_name'] ?? $user['username'] ?? 'Valued'),
            '{{lastName}}'         => htmlspecialchars($user['last_name'] ?? 'Trader'),
            '{{email}}'            => htmlspecialchars($email),
            '{{username}}'         => htmlspecialchars($user['username'] ?? 'trader'),
            '{{registrationDate}}' => !empty($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : date('F j, Y'),
            '{{unsubscribeLink}}'  => $unsubscribeLink,
        ];

        $processedBody = strtr($bodyContent, $replacements);

        $templateVars = [
            '{subject}'          => htmlspecialchars($subject),
            '{app_logo}'         => $logoUrl,
            '{app_url}'          => $appUrl,
            '{body_content}'     => $processedBody,
            '{unsubscribe_link}' => $unsubscribeLink,
            '{support_email}'    => 'support@bmforexhub.exchange',
            '{year}'             => date('Y'),
        ];

        return strtr($template, $templateVars);
    }

    /**
     * Render OTP Email HTML using otp.html template
     */
    public static function renderOtp($otpCode, $title = 'Verify Your Email') {
        $templatePath = __DIR__ . '/../Templates/otp.html';
        $template = file_exists($templatePath) ? file_get_contents($templatePath) : 'Code: {otp_code}';

        $appUrl = rtrim(getenv('APP_URL') ?: 'https://bmforexhub.exchange', '/');
        $logoUrl = $appUrl . '/BM-ForexHub-Logo-Circle.png';

        $templateVars = [
            '{title}'    => htmlspecialchars($title),
            '{otp_code}' => htmlspecialchars($otpCode),
            '{app_logo}' => $logoUrl,
            '{year}'     => date('Y'),
        ];

        return strtr($template, $templateVars);
    }
}
