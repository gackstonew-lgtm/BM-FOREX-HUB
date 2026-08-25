<?php
/**
 * BM Forex Hub — Secure OTP Service
 */

require_once __DIR__ . '/MailService.php';
require_once __DIR__ . '/EmailTemplateService.php';

class OtpService {

    /**
     * Get local storage directory for OTP state records
     */
    private static function getStorageDir() {
        $dir = __DIR__ . '/../../storage/otp';
        if (!file_exists($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Generate secure OTP, record rate limits, send email
     * @param string $email
     * @param string $type ('signup' or 'reset')
     * @return array ['success' => bool, 'message' => string]
     */
    public static function generateAndSend($email, $type = 'signup') {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address provided.'];
        }

        // Rate Limit Check: max 5 resend requests per hour
        $storageDir = self::getStorageDir();
        $key = md5($email . '_' . $type);
        $file = $storageDir . '/' . $key . '.json';

        $now = time();
        $record = file_exists($file) ? json_decode(file_get_contents($file), true) : null;

        if ($record) {
            // Check hourly window
            if (($now - $record['window_start']) < 3600) {
                if ($record['count'] >= 5) {
                    return [
                        'success' => false,
                        'message' => 'Maximum resend limit reached (5 per hour). Please try again later.'
                    ];
                }
                $count = $record['count'] + 1;
                $windowStart = $record['window_start'];
            } else {
                $count = 1;
                $windowStart = $now;
            }
        } else {
            $count = 1;
            $windowStart = $now;
        }

        // Generate brand new 6-digit secure code
        $code = sprintf("%06d", random_int(100000, 999999));
        $hash = password_hash($code, PASSWORD_DEFAULT);
        $expiresAt = $now + (15 * 60); // 15 mins expiry

        // Invalidate previous OTP immediately by replacing file record
        $newRecord = [
            'email'        => $email,
            'type'         => $type,
            'hash'         => $hash,
            'expires_at'   => $expiresAt,
            'count'        => $count,
            'window_start' => $windowStart,
            'last_sent_at' => $now,
            'attempts'     => 0
        ];

        file_put_contents($file, json_encode($newRecord), LOCK_EX);

        // Render HTML Email
        $title = ($type === 'signup') ? 'Verify Your Account' : 'Reset Your Password';
        $htmlBody = EmailTemplateService::renderOtp($code, $title);
        $subject  = "Your BM Forex Hub Verification Code: $code";

        // Dispatch via MailService
        $mailer = MailService::getInstance();
        $result = $mailer->send($email, $subject, $htmlBody);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'A new verification code has been sent to your email.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Unable to send OTP. Please try again later.'
        ];
    }

    /**
     * Verify an OTP code against stored record
     */
    public static function verify($email, $inputCode, $type = 'signup') {
        $email = strtolower(trim($email));
        $storageDir = self::getStorageDir();
        $key = md5($email . '_' . $type);
        $file = $storageDir . '/' . $key . '.json';

        if (!file_exists($file)) {
            return ['success' => false, 'message' => 'Verification code expired or invalid. Please request a new code.'];
        }

        $record = json_decode(file_get_contents($file), true);
        if (!$record) {
            return ['success' => false, 'message' => 'Invalid verification record.'];
        }

        // Expiry check
        if (time() > $record['expires_at']) {
            @unlink($file);
            return ['success' => false, 'message' => 'Verification code has expired. Please request a new code.'];
        }

        // Brute force protection: max 5 failed attempts per OTP code
        if (($record['attempts'] ?? 0) >= 5) {
            @unlink($file);
            return ['success' => false, 'message' => 'Too many failed attempts. Please request a new verification code.'];
        }

        if (password_verify(trim($inputCode), $record['hash'])) {
            // Code match — clear record to prevent replay
            @unlink($file);
            return ['success' => true, 'message' => 'Verification successful!'];
        }

        // Increment attempt counter
        $record['attempts'] = ($record['attempts'] ?? 0) + 1;
        file_put_contents($file, json_encode($record), LOCK_EX);

        return ['success' => false, 'message' => 'Invalid verification code. Please check and try again.'];
    }
}
