<?php
/**
 * BM Forex Hub — REST API Route Bridge
 * Maps POST /api/admin/notifications/bulk-send -> admin/api/notifications.php
 */

$_GET['action'] = 'bulk-send';
require_once __DIR__ . '/../../../admin/api/notifications.php';
