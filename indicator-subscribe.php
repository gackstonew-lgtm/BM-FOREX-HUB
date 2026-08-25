<?php
/**
 * BM Forex Hub — BM Quantum Edge Subscription Page
 * File: indicator-subscribe.php
 *
 * Dedicated, minimal presentation for BM Quantum Edge ($299 One-Time Payment).
 * Initiates secure checkout directly via Kora Pay payment gateway.
 */
require_once __DIR__ . '/engine_config.php';
require_once __DIR__ . '/api/kora-config.php';

$planKey = 'indicator_quantum_edge';
$planName = 'BM Quantum Edge';
$planPriceUsd = 299;
$planPriceKes = $KORA_PLANS[$planKey]['amount_kes'] ?? 38696.58;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require_once __DIR__ . '/components/pwa_meta.php'; ?>
<title>BM Quantum Edge | BM Forex Hub</title>
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/ai-assistant.css">

<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:#0B0F14;color:#FFFFFF;font-family:'Inter',system-ui,sans-serif;min-height:100vh;display:flex;flex-direction:column;overflow-x:hidden}

/* ── Header ── */
.sp-top{display:flex;align-items:center;justify-content:space-between;padding:18px 32px;border-bottom:1px solid #283548;position:sticky;top:0;z-index:10;background:rgba(11,15,20,0.92);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px)}
.sp-logo{display:flex;align-items:center;gap:12px;text-decoration:none;color:#fff;font-weight:600;font-size:1rem;font-family:'Space Grotesk',sans-serif}
.sp-logo img{width:36px;height:36px;border-radius:50%;border:1px solid rgba(22,119,255,0.4)}
.sp-top-right{display:flex;align-items:center;gap:14px}
.sp-top-right a{color:rgba(255,255,255,0.6);text-decoration:none;font-size:0.82rem;transition:color .2s}
.sp-top-right a:hover{color:#1677FF}

/* ── Hero ── */
.sp-hero{text-align:center;padding:56px 24px 16px;position:relative}
.sp-hero::before{content:'';position:absolute;top:-120px;left:50%;transform:translateX(-50%);width:600px;height:600px;background:radial-gradient(circle,rgba(22,119,255,0.12) 0%,transparent 70%);pointer-events:none}
.sp-eyebrow{font-family:'IBM Plex Mono',monospace;font-size:0.72rem;letter-spacing:0.16em;text-transform:uppercase;color:#1677FF;margin-bottom:12px}
.sp-hero h1{font-family:'Space Grotesk',sans-serif;font-size:clamp(1.7rem,4vw,2.4rem);font-weight:700;color:#fff;line-height:1.2;margin-bottom:10px}
.sp-hero h1 em{font-style:normal;color:#1677FF}
.sp-hero p{color:rgba(255,255,255,0.6);font-size:0.92rem;max-width:480px;margin:0 auto;line-height:1.55}

/* ── Alert ── */
.sp-alert{max-width:440px;width:100%;margin:16px auto 0;padding:0 20px}
.sp-alert__inner{border-radius:10px;padding:12px 16px;font-size:0.84rem;display:none}
.sp-alert__inner.show{display:flex;align-items:center;gap:10px}
.sp-alert__inner.success{background:rgba(22,199,132,0.1);border:1px solid rgba(22,199,132,0.3);color:#16C784}
.sp-alert__inner.error{background:rgba(246,70,93,0.1);border:1px solid rgba(246,70,93,0.3);color:#F6465D}
.sp-alert__icon{font-size:1.1rem;flex-shrink:0}

/* ── Subscription Card Section ── */
.sp-section{padding:24px 20px 60px;max-width:440px;width:100%;margin:0 auto;flex:1;display:flex;flex-direction:column;justify-content:center}
.sp-card{background:linear-gradient(180deg,#151D29 0%,#101722 100%);border:1px solid #283548;border-radius:16px;padding:32px 28px 28px;text-align:center;position:relative;transition:all .25s ease;display:flex;flex-direction:column;width:100%;box-shadow:0 12px 36px rgba(0,0,0,0.35)}
.sp-card:hover{border-color:#1677FF;transform:translateY(-3px);box-shadow:0 16px 40px rgba(22,119,255,0.18)}

.sp-card__icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.4rem;background:rgba(22,119,255,0.12);color:#1677FF;border:1px solid rgba(22,119,255,0.25)}
.sp-card h3{font-family:'Space Grotesk',sans-serif;font-size:1.3rem;font-weight:700;color:#fff;margin-bottom:6px;letter-spacing:-0.2px}
.sp-card__desc{font-size:0.84rem;color:#B8C3D1;margin-bottom:20px;line-height:1.45}

.sp-card__price{margin-bottom:24px;padding:16px 20px;background:rgba(22,119,255,0.05);border:1px solid rgba(22,119,255,0.18);border-radius:12px;display:flex;flex-direction:column;justify-content:center;align-items:center}
.sp-card__price .amt{font-family:'Space Grotesk',sans-serif;font-size:2.4rem;font-weight:700;color:#fff;line-height:1}
.sp-card__price .amt .cur{font-size:1.35rem;color:#1677FF;font-weight:600;vertical-align:super;margin-right:2px}
.sp-card__price .per{display:inline-block;font-size:0.75rem;color:#16C784;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-top:8px}

.sp-card__cta-wrap{width:100%;margin-top:auto}
.sp-card .btn-primary{width:100%;padding:15px;border:none;border-radius:10px;background:linear-gradient(135deg,#1677FF 0%,#0D47A1 100%);color:#FFFFFF;font-family:'Space Grotesk',sans-serif;font-size:1rem;font-weight:700;cursor:pointer;transition:all .2s;letter-spacing:0.3px;display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;box-shadow:0 4px 16px rgba(22,119,255,0.3)}
.sp-card .btn-primary:hover{box-shadow:0 6px 24px rgba(22,119,255,0.5);transform:translateY(-1px)}
.sp-card .btn-primary:disabled{opacity:0.6;cursor:not-allowed;transform:none;box-shadow:none}

/* Spinner inside button */
.btn-spinner{width:18px;height:18px;border:2px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:btnSpin .7s linear infinite;display:inline-block}
@keyframes btnSpin{to{transform:rotate(360deg)}}

/* ── Footer ── */
.sp-footer{margin-top:auto;padding:20px 24px;border-top:1px solid #283548;text-align:center}
.sp-footer p{font-size:0.72rem;color:#7F8B99}

/* ── Animation ── */
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.sp-fade{animation:fadeUp .5s ease both}
</style>
<script>var API_BASE = '<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>';</script>
</head>
<body>

<!-- Header -->
<header class="sp-top" aria-label="Header">
  <a href="index.php" class="sp-logo" aria-label="BM Forex Hub home">
    <img src="BM-ForexHub-Logo-Circle.png" alt="BM Forex Hub">
    <span>BM <em style="color:#1677FF;font-style:normal">FOREX</em> HUB</span>
  </a>
  <div class="sp-top-right">
    <a href="trading.php">Back to Dashboard</a>
  </div>
</header>

<!-- Hero Section -->
<section class="sp-hero sp-fade" aria-label="Introduction">
  <div class="sp-eyebrow">PREMIUM ALGORITHMS</div>
  <h1>BM <em>Quantum Edge</em></h1>
  <p>Get instant access to BM Quantum Edge multi-engine trading algorithms.</p>
</section>

<!-- Feedback notifications -->
<div class="sp-alert" role="alert">
  <div class="sp-alert__inner" id="spAlertInner">
    <span class="sp-alert__icon">ℹ</span>
    <span id="spAlertMsg">Alert text</span>
  </div>
</div>

<!-- Subscription Card -->
<main class="sp-section sp-fade" id="spPlansSection">
  <div class="sp-card" role="region" aria-label="BM Quantum Edge Subscription">
    <div class="sp-card__icon" aria-hidden="true">⚡</div>
    <h3>BM Quantum Edge</h3>
    <p class="sp-card__desc">Institutional Algorithmic Trading Suite</p>
    
    <div class="sp-card__price">
      <span class="amt"><span class="cur">$</span>299</span>
      <span class="per">One-Time Payment</span>
    </div>

    <div class="sp-card__cta-wrap">
      <button type="button" class="btn-primary" id="spPayBtn" onclick="initiateQuantumCheckout()">
        <span id="btnText">Get BM Quantum Edge</span>
      </button>
    </div>
  </div>
</main>

<!-- Footer -->
<footer class="sp-footer">
  <p>&copy; <?= date('Y') ?> BM Forex Hub. All rights reserved.</p>
</footer>

<!-- Supabase + Auth Scripts -->
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>

<script>
  var PLAN_KEY = 'indicator_quantum_edge';
  var _alert = document.getElementById('spAlertInner');
  var _alertMsg = document.getElementById('spAlertMsg');
  var _btn = document.getElementById('spPayBtn');
  var _btnText = document.getElementById('btnText');

  function showAlert(msg, type) {
    if (!_alert || !_alertMsg) return;
    _alertMsg.textContent = msg;
    _alert.className = 'sp-alert__inner show ' + (type || 'error');
  }

  function hideAlert() {
    if (_alert) _alert.className = 'sp-alert__inner';
  }

  (async function() {
    try {
      var s = await BMAuth.getSession();
      if (!s.user) {
        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
        return;
      }
    } catch(e) {}

    var params = new URLSearchParams(window.location.search);
    var payStatus = params.get('status');
    if (payStatus === 'failed') {
      showAlert('Payment was not completed. Please try again.', 'error');
    }
  })();

  window.initiateQuantumCheckout = async function() {
    hideAlert();
    _btn.disabled = true;
    _btnText.innerHTML = '<span class="btn-spinner"></span> Connecting to Kora Pay...';

    try {
      var s = await BMAuth.getSession();
      if (!s || !s.user) {
        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
        return;
      }

      var token = s.session ? s.session.access_token : '';
      var resp = await fetch('api/indicator-subscribe.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({
          plan: PLAN_KEY
        })
      });

      var data;
      try {
        data = await resp.json();
      } catch(jsonErr) {
        throw new Error('Payment server returned an unexpected response. Please try again.');
      }

      if (data && data.ok) {
        if (data.checkout_url) {
          window.location.href = data.checkout_url;
          return;
        }
        // Fallback: If immediately activated
        showAlert(data.message || 'Subscription activated! Redirecting...', 'success');
        setTimeout(function() {
          window.location.href = 'trading.php';
        }, 2000);
      } else {
        throw new Error((data && data.error) ? data.error : 'Unable to initialize checkout. Please try again.');
      }
    } catch(err) {
      console.error('Checkout error:', err);
      showAlert(err.message || 'Payment initiation failed. Please try again.', 'error');
      _btn.disabled = false;
      _btnText.textContent = 'Get BM Quantum Edge';
    }
  };
</script>

<!-- Load AI Support assistant -->
<script src="js/ai-assistant.js" defer></script>

</body>
</html>
