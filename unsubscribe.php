<?php
/**
 * BM Forex Hub — Unsubscribe Handler Page
 */

require_once __DIR__ . '/app/Services/EmailTemplateService.php';
require_once __DIR__ . '/app/Services/EmailQueueService.php';

$email = trim($_GET['email'] ?? '');
$token = trim($_GET['token'] ?? '');
$statusMsg = '';
$isSuccess = false;

if (!empty($email) && !empty($token)) {
    if (EmailTemplateService::verifyUnsubscribeToken($email, $token)) {
        $isSuccess = true;
        EmailQueueService::addUnsubscribe($email, 'user_unsubscribe_link');
        $statusMsg = "You have been successfully unsubscribed from BM Forex Hub bulk marketing emails.";
    } else {
        $statusMsg = "Invalid or expired unsubscribe link token.";
    }
} else {
    $statusMsg = "Missing email or token parameters.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Unsubscribe | BM Forex Hub</title>
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="auth.css">
<style>
  body { background: #0B0F14; color: #FFFFFF; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
  .unsub-card { background: #151D29; border: 1px solid #283548; border-radius: 12px; padding: 40px; text-align: center; max-width: 480px; width: 90%; }
  .logo { width: 64px; height: 64px; margin-bottom: 20px; }
  h1 { font-family: 'Space Grotesk', sans-serif; font-size: 1.4rem; color: #1677FF; margin-bottom: 12px; }
  p { color: #B8C3D1; font-size: 0.95rem; line-height: 1.5; margin-bottom: 24px; }
  .btn-home { display: inline-block; padding: 12px 24px; background: #1677FF; color: #FFFFFF; font-weight: 600; border-radius: 6px; text-decoration: none; font-size: 0.9rem; transition: background .2s; }
  .btn-home:hover { background: #2F80FF; }
</style>
</head>
<body>
<button type="button" class="theme-toggle theme-toggle--corner" title="Toggle light / dark theme" aria-label="Toggle light / dark theme"><svg class="theme-toggle__sun" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><svg class="theme-toggle__moon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button>
<div class="unsub-card">
  <img src="BM-ForexHub-Logo-Circle.png" alt="BM Forex Hub" class="logo">
  <h1><?= $isSuccess ? 'Unsubscribed' : 'Unsubscribe Status' ?></h1>
  <p><?= htmlspecialchars($statusMsg) ?></p>
  <a href="index.php" class="btn-home">Return to Home</a>
</div>
<script src="js/motion.js" defer></script>
</body>
</html>
