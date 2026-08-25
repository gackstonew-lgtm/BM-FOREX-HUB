<?php
/**
 * BM Forex Hub — Production Mail Service Abstraction Layer
 * Standardized on PHPMailer & Hostinger Business Email SMTP
 * Supports: Hostinger SMTP, SendGrid, Amazon SES, Resend, Brevo, Mailgun
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    private static $instance = null;
    private $config;
    private $persistentSmtp = null;

    public function __construct() {
        $this->config = require __DIR__ . '/../Config/mail.php';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Send an email using the configured driver
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $htmlBody HTML body content
     * @param string|null $toName Recipient display name
     * @param array $headers Custom headers (e.g. List-Unsubscribe)
     * @return array ['success' => bool, 'error' => string|null, 'provider' => string]
     */
    public function send($to, $subject, $htmlBody, $toName = '', array $headers = []) {
        $driver = strtolower($this->config['driver'] ?: 'smtp');

        try {
            switch ($driver) {
                case 'sendgrid':
                    return $this->sendViaSendGrid($to, $subject, $htmlBody, $toName);
                case 'resend':
                    return $this->sendViaResend($to, $subject, $htmlBody, $toName, $headers);
                case 'brevo':
                    return $this->sendViaBrevo($to, $subject, $htmlBody, $toName);
                case 'mailgun':
                    return $this->sendViaMailgun($to, $subject, $htmlBody, $toName);
                case 'ses':
                    return $this->sendViaSes($to, $subject, $htmlBody, $toName);
                case 'smtp':
                default:
                    return $this->sendViaPhpMailerSmtp($to, $subject, $htmlBody, $toName, $headers);
            }
        } catch (\Throwable $e) {
            error_log("MailService Error ({$driver}): " . $e->getMessage());
            return [
                'success' => false,
                'error'   => "Mail Service Exception ({$driver}): " . $e->getMessage(),
                'provider'=> $driver
            ];
        }
    }

    /**
     * High-performance PHPMailer Hostinger SMTP Driver
     */
    public function sendViaPhpMailerSmtp($to, $subject, $htmlBody, $toName = '', array $extraHeaders = [], $keepAlive = false) {
        $smtp = $this->config['smtp'];
        $host = $smtp['host'] ?: 'smtp.hostinger.com';
        $port = (int)($smtp['port'] ?: 465);
        $user = $smtp['username'];
        $pass = $smtp['password'];
        $enc  = strtolower($smtp['encryption'] ?: 'ssl');

        $fromAddr = $this->config['from']['address'] ?: 'info@admin.bmforexhub.exchange';
        $fromName = $this->config['from']['name'] ?: 'BM Forex Hub';

        if (empty($host) || empty($user)) {
            return [
                'success'  => false,
                'error'    => 'SMTP credentials incomplete. Please check MAIL_USERNAME and MAIL_PASSWORD in .env configuration.',
                'provider' => 'smtp'
            ];
        }

        try {
            if ($keepAlive && $this->persistentSmtp !== null) {
                $mail = $this->persistentSmtp;
                $mail->clearAddresses();
                $mail->clearCustomHeaders();
            } else {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $host;
                $mail->Port       = $port;
                $mail->SMTPAuth   = true;
                $mail->Username   = $user;
                $mail->Password   = $pass;
                $mail->SMTPSecure = ($enc === 'tls' || $port === 587) ? 'tls' : 'ssl';
                $mail->CharSet    = 'utf-8';
                $mail->Timeout    = 25;
                $mail->SMTPKeepAlive = $keepAlive;

                if ($keepAlive) {
                    $this->persistentSmtp = $mail;
                }
            }

            $mail->setFrom($fromAddr, $fromName);
            $mail->addAddress($to, $toName ?: $to);
            $mail->addReplyTo($fromAddr, $fromName);
            $mail->isHTML(true);

            // Clean subject line of line breaks (prevent header injection)
            $mail->Subject = str_replace(["\r", "\n"], '', $subject);
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody));

            // Custom MIME headers for maximum deliverability & SPF/DKIM friendliness
            $mail->addCustomHeader('X-Auto-Response-Suppress', 'OOF, AutoReply');
            $mail->addCustomHeader('Precedence', 'bulk');

            foreach ($extraHeaders as $name => $value) {
                $mail->addCustomHeader($name, $value);
            }

            $mail->send();

            return ['success' => true, 'error' => null, 'provider' => 'smtp'];
        } catch (Exception $e) {
            return [
                'success'  => false,
                'error'    => 'PHPMailer SMTP Error: ' . $e->getMessage(),
                'provider' => 'smtp'
            ];
        } catch (\Throwable $e) {
            return [
                'success'  => false,
                'error'    => 'SMTP Exception: ' . $e->getMessage(),
                'provider' => 'smtp'
            ];
        }
    }

    /**
     * Bulk-campaign dispatcher that respects the configured driver.
     * Uses connection keep-alive for SMTP, plain API calls for Resend.
     */
    public function sendBulk($to, $subject, $htmlBody, $toName = '', array $extraHeaders = []) {
        $driver = strtolower($this->config['driver'] ?: 'smtp');
        if ($driver === 'resend') {
            return $this->sendViaResend($to, $subject, $htmlBody, $toName, $extraHeaders);
        }
        return $this->sendViaPhpMailerSmtp($to, $subject, $htmlBody, $toName, $extraHeaders, true);
    }

    /**
     * Close persistent SMTP connection if open
     */
    public function closePersistentSmtp() {
        if ($this->persistentSmtp !== null) {
            $this->persistentSmtp->smtpClose();
            $this->persistentSmtp = null;
        }
    }

    /**
     * SendGrid REST API Driver
     */
    private function sendViaSendGrid($to, $subject, $htmlBody, $toName = '') {
        $apiKey = $this->config['sendgrid']['api_key'];
        if (empty($apiKey)) return $this->sendViaPhpMailerSmtp($to, $subject, $htmlBody, $toName);

        $payload = [
            'personalizations' => [[
                'to' => [['email' => $to, 'name' => $toName ?: $to]]
            ]],
            'from' => [
                'email' => $this->config['from']['address'],
                'name'  => $this->config['from']['name']
            ],
            'subject' => $subject,
            'content' => [[
                'type'  => 'text/html',
                'value' => $htmlBody
            ]]
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 15
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            return ['success' => true, 'error' => null, 'provider' => 'sendgrid'];
        }
        return ['success' => false, 'error' => "SendGrid Error HTTP $code: $response", 'provider' => 'sendgrid'];
    }

    /**
     * Resend REST API Driver
     */
    private function sendViaResend($to, $subject, $htmlBody, $toName = '', array $extraHeaders = []) {
        $apiKey = $this->config['resend']['api_key'];
        if (empty($apiKey)) return $this->sendViaPhpMailerSmtp($to, $subject, $htmlBody, $toName);

        $payload = [
            'from'    => $this->config['from']['name'] . ' <' . $this->config['from']['address'] . '>',
            'to'      => [$to],
            'subject' => str_replace(["\r", "\n"], '', $subject),
            'html'    => $htmlBody
        ];
        if (!empty($extraHeaders)) {
            $payload['headers'] = $extraHeaders;
        }

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 15
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            return ['success' => true, 'error' => null, 'provider' => 'resend'];
        }
        return ['success' => false, 'error' => "Resend Error HTTP $code: $response", 'provider' => 'resend'];
    }

    /**
     * Brevo (Sendinblue) REST API Driver
     */
    private function sendViaBrevo($to, $subject, $htmlBody, $toName = '') {
        $apiKey = $this->config['brevo']['api_key'];
        if (empty($apiKey)) return $this->sendViaPhpMailerSmtp($to, $subject, $htmlBody, $toName);

        $payload = [
            'sender' => [
                'name'  => $this->config['from']['name'],
                'email' => $this->config['from']['address']
            ],
            'to' => [[
                'email' => $to,
                'name'  => $toName ?: $to
            ]],
            'subject'     => $subject,
            'htmlContent' => $htmlBody
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'api-key: ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 15
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            return ['success' => true, 'error' => null, 'provider' => 'brevo'];
        }
        return ['success' => false, 'error' => "Brevo Error HTTP $code: $response", 'provider' => 'brevo'];
    }

    /**
     * Mailgun REST API Driver
     */
    private function sendViaMailgun($to, $subject, $htmlBody, $toName = '') {
        $apiKey = $this->config['mailgun']['api_key'];
        $domain = $this->config['mailgun']['domain'];
        if (empty($apiKey) || empty($domain)) return $this->sendViaPhpMailerSmtp($to, $subject, $htmlBody, $toName);

        $postData = [
            'from'    => $this->config['from']['name'] . ' <' . $this->config['from']['address'] . '>',
            'to'      => $toName ? "$toName <$to>" : $to,
            'subject' => $subject,
            'html'    => $htmlBody
        ];

        $ch = curl_init("https://api.mailgun.net/v3/{$domain}/messages");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => 'api:' . $apiKey,
            CURLOPT_POSTFIELDS     => http_build_query($postData),
            CURLOPT_TIMEOUT        => 15
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            return ['success' => true, 'error' => null, 'provider' => 'mailgun'];
        }
        return ['success' => false, 'error' => "Mailgun Error HTTP $code: $response", 'provider' => 'mailgun'];
    }

    /**
     * Amazon SES Driver
     */
    private function sendViaSes($to, $subject, $htmlBody, $toName = '') {
        return $this->sendViaPhpMailerSmtp($to, $subject, $htmlBody, $toName);
    }
}
