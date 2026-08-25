<?php
/**
 * BM Forex Hub — Production Email Worker
 * Processes email batches using pure PHP (shared hosting & cPanel compatible).
 */

require_once __DIR__ . '/../Services/MailService.php';
require_once __DIR__ . '/../Services/EmailTemplateService.php';
require_once __DIR__ . '/../Services/EmailQueueService.php';

class EmailWorker {
    public function run($batchSize = 50) {
        return EmailQueueService::processBatch($batchSize);
    }
}

// Execute worker batch
$worker = new EmailWorker();
$worker->run();

