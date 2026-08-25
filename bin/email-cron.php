<?php
/**
 * BM Forex Hub — CLI Cron Dispatcher Script
 * Execute periodically via Windows Task Scheduler or host crontab:
 * * * * * * C:\xampp\php\php.exe c:\xampp\htdocs\BM forex\bin\email-cron.php
 */

require_once __DIR__ . '/../app/Workers/EmailWorker.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting Email Queue Cron Processing...\n";
$worker = new EmailWorker();
$worker->run();
echo "[" . date('Y-m-d H:i:s') . "] Email Queue Cron Processing Completed.\n";
