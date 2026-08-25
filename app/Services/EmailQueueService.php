<?php
/**
 * BM Forex Hub — Email Queue, Campaign & Logging Service
 * Production Queue Management & Recipient Targeting System
 */

require_once __DIR__ . '/../../admin/config.php';
require_once __DIR__ . '/MailService.php';
require_once __DIR__ . '/EmailTemplateService.php';

class EmailQueueService {

    public static function getPdo() {
        $dbDir = __DIR__ . '/../../storage/db';
        if (!file_exists($dbDir)) {
            @mkdir($dbDir, 0755, true);
        }
        $sqliteFile = $dbDir . '/email_system.sqlite';
        $pdo = new PDO('sqlite:' . $sqliteFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        try {
            $pdo->exec("PRAGMA busy_timeout = 5000;");
            $pdo->exec("PRAGMA journal_mode = WAL;");
        } catch (\Throwable $e) {
            // Silence transient lock error during PRAGMA mode switch
        }
        self::initDbTables($pdo);
        return $pdo;
    }

    private static function initDbTables(PDO $pdo) {
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS email_queue (
                    id TEXT PRIMARY KEY,
                    campaign_id TEXT NOT NULL,
                    recipient_email TEXT NOT NULL,
                    recipient_name TEXT DEFAULT '',
                    user_data TEXT DEFAULT '{}',
                    subject TEXT NOT NULL,
                    body TEXT NOT NULL,
                    status TEXT DEFAULT 'queued',
                    attempts INTEGER DEFAULT 0,
                    max_attempts INTEGER DEFAULT 3,
                    error_message TEXT DEFAULT NULL,
                    scheduled_at INTEGER DEFAULT 0,
                    created_at INTEGER DEFAULT 0,
                    updated_at INTEGER DEFAULT 0
                );

                CREATE TABLE IF NOT EXISTS email_history (
                    id TEXT PRIMARY KEY,
                    admin_id TEXT DEFAULT 'admin',
                    subject TEXT NOT NULL,
                    message TEXT NOT NULL,
                    recipient_group TEXT NOT NULL,
                    selected_users TEXT DEFAULT NULL,
                    total_recipients INTEGER DEFAULT 0,
                    sent_count INTEGER DEFAULT 0,
                    failed_count INTEGER DEFAULT 0,
                    status TEXT DEFAULT 'queued',
                    processing_time INTEGER DEFAULT 0,
                    scheduled_at INTEGER DEFAULT 0,
                    created_at INTEGER DEFAULT 0,
                    updated_at INTEGER DEFAULT 0
                );

                CREATE TABLE IF NOT EXISTS email_drafts (
                    id TEXT PRIMARY KEY,
                    admin_id TEXT DEFAULT 'admin',
                    subject TEXT DEFAULT '',
                    message TEXT DEFAULT '',
                    recipient_group TEXT DEFAULT 'all',
                    selected_users TEXT DEFAULT NULL,
                    created_at INTEGER DEFAULT 0,
                    updated_at INTEGER DEFAULT 0
                );

                CREATE TABLE IF NOT EXISTS email_logs (
                    id TEXT PRIMARY KEY,
                    campaign_id TEXT DEFAULT NULL,
                    job_id TEXT DEFAULT NULL,
                    log_level TEXT DEFAULT 'info',
                    message TEXT NOT NULL,
                    details TEXT DEFAULT NULL,
                    created_at INTEGER DEFAULT 0
                );

                CREATE TABLE IF NOT EXISTS email_unsubscribes (
                    id TEXT PRIMARY KEY,
                    email TEXT UNIQUE NOT NULL,
                    reason TEXT DEFAULT 'user_opt_out',
                    created_at INTEGER DEFAULT 0
                );

                CREATE TABLE IF NOT EXISTS email_statistics (
                    id TEXT PRIMARY KEY,
                    campaign_id TEXT NOT NULL,
                    opens INTEGER DEFAULT 0,
                    clicks INTEGER DEFAULT 0,
                    last_event_at INTEGER DEFAULT 0
                );
            ");
        } catch (\Throwable $e) {
            // Tables already created or file locked by active worker
        }
    }

    /**
     * Log system events to both database & file
     */
    public static function logEvent($campaignId, $jobId, $level, $message, $details = null) {
        try {
            $pdo = self::getPdo();
            $logId = 'log_' . bin2hex(random_bytes(10));
            $now = time();

            $stmt = $pdo->prepare("
                INSERT INTO email_logs (id, campaign_id, job_id, log_level, message, details, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $logId,
                $campaignId,
                $jobId,
                $level,
                $message,
                is_array($details) || is_object($details) ? json_encode($details) : (string)$details,
                $now
            ]);

            // File logger fallback inside storage/logs/
            $logDir = __DIR__ . '/../../storage/logs';
            if (!file_exists($logDir)) @mkdir($logDir, 0755, true);
            $logFile = $logDir . '/email.log';
            $formatted = sprintf("[%s] [%s] Campaign: %s | Job: %s | %s %s\n",
                date('Y-m-d H:i:s', $now),
                strtoupper($level),
                $campaignId ?: 'GLOBAL',
                $jobId ?: 'N/A',
                $message,
                $details ? '(' . (is_string($details) ? $details : json_encode($details)) . ')' : ''
            );
            @file_put_contents($logFile, $formatted, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            error_log("EmailQueueService Logger Error: " . $e->getMessage());
        }
    }

    /**
     * Fetch unsubscribed email addresses
     */
    public static function getUnsubscribedEmails() {
        try {
            $pdo = self::getPdo();
            $stmt = $pdo->query("SELECT email FROM email_unsubscribes");
            return array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Record unsubscribed email
     */
    public static function addUnsubscribe($email, $reason = 'user_opt_out') {
        $email = strtolower(trim($email));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        try {
            $pdo = self::getPdo();
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO email_unsubscribes (id, email, reason, created_at) VALUES (?, ?, ?, ?)");
            $id = 'unsub_' . bin2hex(random_bytes(8));
            $stmt->execute([$id, $email, $reason, time()]);
            self::logEvent(null, null, 'info', "Email unsubscribed: {$email}", ['reason' => $reason]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Fetch active subscriptions from Supabase for targeted member grouping
     */
    private static function fetchSubscriptionsMap() {
        $subRes = sb_admin_get('subscriptions', [
            'select' => 'user_id,plan,status,expires_at',
            'order'  => 'expires_at.desc'
        ]);
        $subs = $subRes['data'] ?? [];
        if (!is_array($subs)) return [];

        $now = time();
        $map = [];

        foreach ($subs as $s) {
            $uid = $s['user_id'] ?? '';
            if (!$uid || isset($map[$uid])) continue;

            $expiresTs = strtotime($s['expires_at'] ?? '');
            $isActive = ($s['status'] === 'active' && $expiresTs > $now);

            $map[$uid] = [
                'plan'       => $s['plan'] ?? '',
                'status'     => $s['status'] ?? '',
                'expires_at' => $s['expires_at'] ?? '',
                'is_active'  => $isActive,
                'is_expired' => ($expiresTs > 0 && $expiresTs <= $now) || ($s['status'] === 'expired'),
            ];
        }

        return $map;
    }

    /**
     * Fetch recipient users based on target group selection
     */
    public static function fetchRecipients($recipientGroup, $selectedUserIds = []) {
        $allProfiles = [];
        $page = 1;
        $pageSize = 500;

        // Fetch profiles from Supabase
        while (true) {
            $offset = ($page - 1) * $pageSize;
            $res = sb_admin_get('profiles', [
                'select' => 'id,username,first_name,last_name,created_at,country_code,phone',
                'limit'  => $pageSize,
                'offset' => $offset
            ]);
            $list = $res['data'] ?? [];
            if (!is_array($list) || empty($list)) break;
            $allProfiles = array_merge($allProfiles, $list);
            if (count($list) < $pageSize) break;
            $page++;
        }

        // Fetch auth users for email addresses
        $authUsers = sb_auth_admin_users('?per_page=1000');
        $authList = [];
        $authData = $authUsers['data'] ?? null;
        if (is_array($authData)) {
            if (isset($authData['users']) && is_array($authData['users'])) {
                $authList = $authData['users'];
            } elseif (isset($authData[0]) && is_array($authData[0]['users'] ?? null)) {
                $authList = $authData[0]['users'];
            }
        }

        $authMap = [];
        foreach ($authList as $au) {
            $uid = $au['id'] ?? '';
            if ($uid) {
                $authMap[$uid] = [
                    'email'            => $au['email'] ?? '',
                    'email_confirmed'  => !empty($au['email_confirmed_at'] ?? ($au['confirmed_at'] ?? null)),
                    'last_sign_in'     => $au['last_sign_in_at'] ?? null,
                ];
            }
        }

        $subsMap = self::fetchSubscriptionsMap();
        $unsubscribedList = self::getUnsubscribedEmails();

        $now = time();
        $oneWeekAgo = $now - (7 * 86400);

        $matched = [];
        foreach ($allProfiles as $p) {
            $uid   = $p['id'] ?? '';
            $auth  = $authMap[$uid] ?? [];
            $email = strtolower(trim($auth['email'] ?? ''));

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

            // Skip unsubscribed users
            if (in_array($email, $unsubscribedList)) continue;

            $isVerified   = !empty($auth['email_confirmed']);
            $lastSignIn   = !empty($auth['last_sign_in']) ? strtotime($auth['last_sign_in']) : 0;
            $isActiveUser = ($lastSignIn > $oneWeekAgo);

            $subInfo     = $subsMap[$uid] ?? [];
            $hasActiveSub= !empty($subInfo['is_active']);
            $isExpiredSub= !empty($subInfo['is_expired']);
            $plan        = strtolower($subInfo['plan'] ?? '');
            $isVipPlan   = ($hasActiveSub && (strpos($plan, 'vip') !== false || strpos($plan, 'lifetime') !== false));

            $userObj = [
                'id'               => $uid,
                'email'            => $email,
                'username'         => $p['username'] ?? '',
                'first_name'       => $p['first_name'] ?? '',
                'last_name'        => $p['last_name'] ?? '',
                'created_at'       => $p['created_at'] ?? '',
                'is_verified'      => $isVerified,
                'is_active'        => $isActiveUser,
                'plan'             => $subInfo['plan'] ?? 'free',
            ];

            switch ($recipientGroup) {
                case 'subscribers':
                    if ($hasActiveSub) $matched[] = $userObj;
                    break;
                case 'premium':
                    if ($hasActiveSub) $matched[] = $userObj;
                    break;
                case 'vip':
                    if ($isVipPlan) $matched[] = $userObj;
                    break;
                case 'trial':
                    if (!$hasActiveSub && !$isExpiredSub) $matched[] = $userObj;
                    break;
                case 'expired':
                    if ($isExpiredSub) $matched[] = $userObj;
                    break;
                case 'verified':
                    if ($isVerified) $matched[] = $userObj;
                    break;
                case 'unverified':
                    if (!$isVerified) $matched[] = $userObj;
                    break;
                case 'active':
                    if ($isActiveUser) $matched[] = $userObj;
                    break;
                case 'inactive':
                    if (!$isActiveUser) $matched[] = $userObj;
                    break;
                case 'selected':
                    if (in_array($uid, $selectedUserIds) || in_array($email, $selectedUserIds)) {
                        $matched[] = $userObj;
                    }
                    break;
                case 'all':
                default:
                    $matched[] = $userObj;
                    break;
            }
        }

        return $matched;
    }

    /**
     * Dispatch or schedule a bulk campaign
     */
    public static function createCampaign($adminId, $subject, $message, $recipientGroup, $selectedUserIds = [], $scheduledAtTimestamp = 0) {
        $pdo = self::getPdo();

        $recipients = self::fetchRecipients($recipientGroup, $selectedUserIds);
        $total = count($recipients);

        if ($total === 0) {
            return ['success' => false, 'message' => 'No eligible recipients found for the selected filter group (or all selected recipients have unsubscribed).'];
        }

        $campaignId = 'cmp_' . bin2hex(random_bytes(12));
        $now = time();
        $scheduledTime = ($scheduledAtTimestamp > $now) ? $scheduledAtTimestamp : $now;
        $initialStatus = ($scheduledAtTimestamp > $now) ? 'scheduled' : 'queued';

        // Insert into email_history
        $stmt = $pdo->prepare("
            INSERT INTO email_history (id, admin_id, subject, message, recipient_group, selected_users, total_recipients, sent_count, failed_count, status, scheduled_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $campaignId,
            $adminId,
            $subject,
            $message,
            $recipientGroup,
            json_encode($selectedUserIds),
            $total,
            $initialStatus,
            $scheduledTime,
            $now,
            $now
        ]);

        // Batch insert into email_queue
        $pdo->beginTransaction();
        $jobStmt = $pdo->prepare("
            INSERT INTO email_queue (id, campaign_id, recipient_email, recipient_name, user_data, subject, body, status, scheduled_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($recipients as $idx => $r) {
            $jobId = 'job_' . bin2hex(random_bytes(10)) . '_' . $idx;
            $name  = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
            $jobStmt->execute([
                $jobId,
                $campaignId,
                $r['email'],
                $name,
                json_encode($r),
                $subject,
                $message,
                $initialStatus,
                $scheduledTime,
                $now,
                $now
            ]);
        }
        $pdo->commit();

        self::logEvent($campaignId, null, 'info', "Campaign created ({$initialStatus}) with {$total} recipients", [
            'recipient_group' => $recipientGroup,
            'scheduled_at'    => date('Y-m-d H:i:s', $scheduledTime)
        ]);

        // Trigger batch worker process
        self::triggerWorkerAsync();

        return [
            'success'          => true,
            'campaign_id'      => $campaignId,
            'total_recipients' => $total,
            'status'           => $initialStatus,
            'scheduled_at'     => date('Y-m-d H:i:s', $scheduledTime)
        ];
    }

    /**
     * Reliable, batch-based email processing mechanism in pure PHP.
     * Operates without exec(), popen(), or OS shell dependencies.
     * Compatible with Hostinger Shared Hosting & all PHP environments.
     */
    public static function processBatch($batchSize = 25) {
        $pdo = self::getPdo();
        $mailer = MailService::getInstance();
        $now = time();

        // 1. Promote scheduled campaigns/jobs if scheduled time has arrived
        try {
            $pdo->exec("UPDATE email_history SET status = 'queued', updated_at = {$now} WHERE status = 'scheduled' AND scheduled_at <= {$now}");
            $pdo->exec("UPDATE email_queue SET status = 'queued', updated_at = {$now} WHERE status = 'scheduled' AND scheduled_at <= {$now}");
        } catch (\Throwable $e) {}

        // 2. Fetch paused or cancelled campaign IDs to skip
        $pausedCampaigns = [];
        try {
            $pausedCampaigns = $pdo->query("SELECT id FROM email_history WHERE status IN ('paused', 'cancelled')")->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (\Throwable $e) {}

        // 3. Fetch up to $batchSize queued/retry jobs
        $stmt = $pdo->prepare("
            SELECT * FROM email_queue 
            WHERE status IN ('queued', 'retry') 
              AND scheduled_at <= ? 
              AND attempts < max_attempts 
            ORDER BY created_at ASC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $now, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$batchSize, PDO::PARAM_INT);
        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($jobs)) {
            self::checkAndFinalizeCampaigns();
            return ['processed' => 0, 'remaining' => 0];
        }

        $processedCount = 0;

        foreach ($jobs as $job) {
            $jobId      = $job['id'];
            $campaignId = $job['campaign_id'];
            $recipient  = $job['recipient_email'];
            $userData   = json_decode($job['user_data'] ?? '{}', true);
            $subject    = $job['subject'];
            $rawBody    = $job['body'];
            $attempts   = (int)$job['attempts'] + 1;

            if (in_array($campaignId, $pausedCampaigns)) {
                continue;
            }

            // Atomic claim: set status to 'processing'
            $upd = $pdo->prepare("UPDATE email_queue SET status = 'processing', attempts = ?, updated_at = ? WHERE id = ? AND status IN ('queued', 'retry')");
            $upd->execute([$attempts, time(), $jobId]);
            if ($upd->rowCount() === 0) {
                // Already claimed by another process
                continue;
            }

            // Mark parent campaign as processing if queued
            $pdo->prepare("UPDATE email_history SET status = 'processing', updated_at = ? WHERE id = ? AND status IN ('queued', 'scheduled')")
                ->execute([time(), $campaignId]);

            // Render template
            $htmlBody = EmailTemplateService::renderNotification($subject, $rawBody, $userData);
            $unsubLink = EmailTemplateService::generateUnsubscribeLink($recipient);
            $extraHeaders = [
                'List-Unsubscribe'      => '<' . $unsubLink . '>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click'
            ];

            // Send via configured driver (Resend / SMTP)
            $sendRes = $mailer->sendBulk(
                $recipient,
                $subject,
                $htmlBody,
                $job['recipient_name'] ?? '',
                $extraHeaders
            );

            $jobNow = time();

            if (!empty($sendRes['success'])) {
                $pdo->prepare("UPDATE email_queue SET status = 'completed', updated_at = ? WHERE id = ?")
                   ->execute([$jobNow, $jobId]);
                $pdo->prepare("UPDATE email_history SET sent_count = sent_count + 1, updated_at = ? WHERE id = ?")
                   ->execute([$jobNow, $campaignId]);

                self::logEvent($campaignId, $jobId, 'info', "Dispatched to {$recipient} via mail driver");
            } else {
                $error = $sendRes['error'] ?: 'Unknown sending error';

                if ($attempts >= (int)$job['max_attempts']) {
                    $pdo->prepare("UPDATE email_queue SET status = 'failed', error_message = ?, updated_at = ? WHERE id = ?")
                       ->execute([$error, $jobNow, $jobId]);
                    $pdo->prepare("UPDATE email_history SET failed_count = failed_count + 1, updated_at = ? WHERE id = ?")
                       ->execute([$error, $jobNow, $campaignId]);

                    self::logEvent($campaignId, $jobId, 'error', "Job permanently failed for {$recipient}", $error);
                } else {
                    $nextRetry = $jobNow + ($attempts * 60);
                    $pdo->prepare("UPDATE email_queue SET status = 'retry', error_message = ?, scheduled_at = ?, updated_at = ? WHERE id = ?")
                       ->execute([$error, $nextRetry, $jobNow, $jobId]);

                    self::logEvent($campaignId, $jobId, 'warning', "Temporary failure for {$recipient}. Retry #{$attempts} scheduled", $error);
                }
            }

            $processedCount++;
            unset($htmlBody, $sendRes);
            if (function_exists('gc_collect_cycles')) gc_collect_cycles();
        }

        self::checkAndFinalizeCampaigns();

        // Count remaining queued jobs
        $remStmt = $pdo->query("SELECT COUNT(*) FROM email_queue WHERE status IN ('queued', 'retry') AND scheduled_at <= {$now}");
        $remaining = (int)$remStmt->fetchColumn();

        return [
            'processed' => $processedCount,
            'remaining' => $remaining
        ];
    }

    /**
     * Finalize completed campaigns
     */
    public static function checkAndFinalizeCampaigns() {
        try {
            $pdo = self::getPdo();
            $activeCampaigns = $pdo->query("SELECT id FROM email_history WHERE status = 'processing'")->fetchAll(PDO::FETCH_COLUMN) ?: [];
            foreach ($activeCampaigns as $cId) {
                $stmtPending = $pdo->prepare("SELECT COUNT(*) FROM email_queue WHERE campaign_id = ? AND status IN ('queued', 'processing', 'retry', 'scheduled')");
                $stmtPending->execute([$cId]);
                $pendingCount = (int)$stmtPending->fetchColumn();

                if ($pendingCount === 0) {
                    $pdo->prepare("UPDATE email_history SET status = 'completed', updated_at = ? WHERE id = ?")
                       ->execute([time(), $cId]);

                    self::logEvent($cId, null, 'info', "Campaign completed all email dispatches");
                }
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Non-blocking background worker trigger (100% PHP, zero shell exec dependencies)
     */
    public static function triggerWorkerAsync() {
        try {
            self::processBatch(25);
        } catch (\Throwable $e) {
            error_log("triggerWorkerAsync Error: " . $e->getMessage());
        }
    }

    /**
     * Pause an active campaign
     */
    public static function pauseCampaign($campaignId) {
        $pdo = self::getPdo();
        $now = time();
        $pdo->prepare("UPDATE email_history SET status = 'paused', updated_at = ? WHERE id = ? AND status IN ('queued', 'processing', 'scheduled')")
            ->execute([$now, $campaignId]);
        $pdo->prepare("UPDATE email_queue SET status = 'paused', updated_at = ? WHERE campaign_id = ? AND status IN ('queued', 'processing', 'scheduled')")
            ->execute([$now, $campaignId]);
        self::logEvent($campaignId, null, 'warning', "Campaign paused by admin");
        return ['success' => true, 'message' => 'Campaign paused successfully.'];
    }

    /**
     * Resume a paused campaign
     */
    public static function resumeCampaign($campaignId) {
        $pdo = self::getPdo();
        $now = time();
        $pdo->prepare("UPDATE email_history SET status = 'queued', updated_at = ? WHERE id = ? AND status = 'paused'")
            ->execute([$now, $campaignId]);
        $pdo->prepare("UPDATE email_queue SET status = 'queued', updated_at = ? WHERE campaign_id = ? AND status = 'paused'")
            ->execute([$now, $campaignId]);
        self::logEvent($campaignId, null, 'info', "Campaign resumed by admin");
        self::triggerWorkerAsync();
        return ['success' => true, 'message' => 'Campaign resumed successfully.'];
    }

    /**
     * Cancel an active campaign
     */
    public static function cancelCampaign($campaignId) {
        $pdo = self::getPdo();
        $now = time();
        $pdo->prepare("UPDATE email_history SET status = 'cancelled', updated_at = ? WHERE id = ?")
            ->execute([$now, $campaignId]);
        $pdo->prepare("UPDATE email_queue SET status = 'cancelled', updated_at = ? WHERE campaign_id = ? AND status IN ('queued', 'processing', 'scheduled', 'paused')")
            ->execute([$now, $campaignId]);
        self::logEvent($campaignId, null, 'warning', "Campaign cancelled by admin");
        return ['success' => true, 'message' => 'Campaign cancelled successfully.'];
    }

    /**
     * Retry failed jobs in a campaign
     */
    public static function retryFailedJobs($campaignId) {
        $pdo = self::getPdo();
        $now = time();
        $stmt = $pdo->prepare("UPDATE email_queue SET status = 'queued', attempts = 0, error_message = NULL, updated_at = ? WHERE campaign_id = ? AND status = 'failed'");
        $stmt->execute([$now, $campaignId]);
        $resetCount = $stmt->rowCount();

        if ($resetCount > 0) {
            $pdo->prepare("UPDATE email_history SET status = 'queued', updated_at = ? WHERE id = ?")->execute([$now, $campaignId]);
            self::logEvent($campaignId, null, 'info', "Retrying {$resetCount} failed jobs");
            self::triggerWorkerAsync();
        }

        return ['success' => true, 'retried_count' => $resetCount];
    }

    /**
     * Get detailed status of a campaign
     */
    public static function getCampaignStatus($campaignId) {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM email_history WHERE id = ?");
        $stmt->execute([$campaignId]);
        $camp = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$camp) return null;

        $total  = (int)$camp['total_recipients'];
        $sent   = (int)$camp['sent_count'];
        $failed = (int)$camp['failed_count'];
        $remain = max(0, $total - ($sent + $failed));
        $pct    = ($total > 0) ? round((($sent + $failed) / $total) * 100) : 100;

        // Fetch last processed email
        $lastStmt = $pdo->prepare("SELECT recipient_email, updated_at FROM email_queue WHERE campaign_id = ? AND status = 'completed' ORDER BY updated_at DESC LIMIT 1");
        $lastStmt->execute([$campaignId]);
        $lastJob = $lastStmt->fetch(PDO::FETCH_ASSOC);

        return [
            'campaign_id'      => $camp['id'],
            'subject'          => $camp['subject'],
            'status'           => $camp['status'],
            'total'            => $total,
            'sent'             => $sent,
            'failed'           => $failed,
            'remaining'        => $remain,
            'progress_percent' => $pct,
            'last_processed'   => $lastJob['recipient_email'] ?? null,
            'created_at'       => date('Y-m-d H:i:s', $camp['created_at']),
        ];
    }

    /**
     * Draft Management
     */
    public static function saveDraft($id, $adminId, $subject, $message, $recipientGroup = 'all', $selectedUsers = []) {
        $pdo = self::getPdo();
        $now = time();

        if (empty($id)) {
            $id = 'drf_' . bin2hex(random_bytes(10));
            $stmt = $pdo->prepare("INSERT INTO email_drafts (id, admin_id, subject, message, recipient_group, selected_users, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $adminId, $subject, $message, $recipientGroup, json_encode($selectedUsers), $now, $now]);
        } else {
            $stmt = $pdo->prepare("UPDATE email_drafts SET subject = ?, message = ?, recipient_group = ?, selected_users = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$subject, $message, $recipientGroup, json_encode($selectedUsers), $now, $id]);
        }

        return ['success' => true, 'draft_id' => $id];
    }

    public static function duplicateDraft($id) {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM email_drafts WHERE id = ?");
        $stmt->execute([$id]);
        $draft = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$draft) return ['success' => false, 'error' => 'Draft not found'];

        $newId = 'drf_' . bin2hex(random_bytes(10));
        $now = time();
        $newSubj = $draft['subject'] . ' (Copy)';

        $ins = $pdo->prepare("INSERT INTO email_drafts (id, admin_id, subject, message, recipient_group, selected_users, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([$newId, $draft['admin_id'], $newSubj, $draft['message'], $draft['recipient_group'], $draft['selected_users'], $now, $now]);

        return ['success' => true, 'draft_id' => $newId];
    }

    public static function getDrafts() {
        $pdo = self::getPdo();
        $stmt = $pdo->query("SELECT * FROM email_drafts ORDER BY updated_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function deleteDraft($id) {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("DELETE FROM email_drafts WHERE id = ?");
        $stmt->execute([$id]);
        return ['success' => true];
    }

    public static function getHistory() {
        $pdo = self::getPdo();
        $stmt = $pdo->query("SELECT * FROM email_history ORDER BY created_at DESC LIMIT 100");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getLogs($campaignId = null, $limit = 50) {
        $pdo = self::getPdo();
        if ($campaignId) {
            $stmt = $pdo->prepare("SELECT * FROM email_logs WHERE campaign_id = ? ORDER BY created_at DESC LIMIT " . (int)$limit);
            $stmt->execute([$campaignId]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM email_logs ORDER BY created_at DESC LIMIT " . (int)$limit);
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
