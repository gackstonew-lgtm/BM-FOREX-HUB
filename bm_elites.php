<?php 
require_once __DIR__ . '/engine_config.php';
require_once __DIR__ . '/app/Services/MembershipService.php';

$membership = new \App\Services\MembershipService();
$elitePlans = $membership->getElitesPlans();
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
<title>BM FOREX HUB | BM Elites Trading Circle</title>
<meta name="description" content="BM Elites Trading Circle is a premium managed investment model.">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<meta name="robots" content="noindex, nofollow, noarchive">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">

<style>
/* Push content right when sidebar visible */
@media (min-width: 900px) {
  body.has-sidebar .dash-main-wrap {
    margin-left: var(--sidebar-w, 260px);
    transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
}
@media (max-width: 899px) {
  .dash-sidebar {
    transform: translateX(-100%);
    transition: transform 0.25s ease;
  }
  .dash-sidebar.open {
    transform: translateX(0);
  }
  body.has-sidebar .dash-main-wrap {
    margin-left: 0;
  }
}
}

/* ===== NEW TOPBAR STYLE OVERRIDES ===== */
.topbar { background: #101722; border-bottom: 1px solid #1e2d42; }
.topbar .logo--text span { font-size: 0.85rem; font-weight: 700; letter-spacing: 0.06em; color: #fff; }
.topbar__user-info {
  display: flex; align-items: center; gap: 10px; color: #fff; font-size: 0.83rem;
}
.topbar__user-avatar {
  width: 34px; height: 34px; background: #1e2d42; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid rgba(22,119,255,0.35); flex-shrink: 0;
  font-size: 0.95rem; color: #8fa3b8; overflow: hidden;
}
.topbar__user-greeting { line-height: 1.2; }
.topbar__user-greeting small { display: block; color: #8fa3b8; font-size: 0.7rem; }
.topbar__user-greeting strong { font-weight: 600; }

.topbar__nav {
  display: flex;
  align-items: center;
  gap: 24px;
  margin: 0 auto;
}
.topbar__nav-link {
  color: #8fa3b8;
  text-decoration: none;
  font-size: 0.88rem;
  font-weight: 500;
  padding: 18px 0;
  border-bottom: 2px solid transparent;
  transition: color 0.15s, border-color 0.15s;
}
.topbar__nav-link:hover {
  color: #fff;
}
.topbar__nav-link.active {
  color: #fff;
  border-bottom-color: #1677FF;
}

@media (max-width: 768px) {
  .topbar__nav {
    display: none;
  }
}

/* ===== DASHBOARD OVERVIEW ===== */
.dash-overview {
  padding: 28px 30px 20px;
}
.dash-overview__title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.65rem; font-weight: 700; color: #fff; margin: 0 0 5px;
}
.dash-overview__subtitle { color: #8fa3b8; font-size: 0.88rem; margin: 0; }

/* ===== BM ELITES COUNTDOWN EXPERIENCE ===== */
.elite-hero-card {
  background: radial-gradient(120% 120% at 50% 0%, rgba(22, 119, 255, 0.16) 0%, #101722 75%), #101722;
  border: 1px solid rgba(22, 119, 255, 0.35);
  border-radius: 20px;
  padding: 40px 32px 36px;
  text-align: center;
  position: relative;
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.4), 0 0 32px rgba(22, 119, 255, 0.12);
  margin-bottom: 36px;
  overflow: hidden;
}
.elite-hero-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 15%;
  right: 15%;
  height: 2px;
  background: linear-gradient(90deg, transparent, #1677FF, #16C784, #1677FF, transparent);
}
.elite-badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 16px;
  border-radius: 30px;
  background: rgba(240, 180, 41, 0.12);
  border: 1px solid rgba(240, 180, 41, 0.35);
  color: #F0B429;
  font-size: 0.76rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 16px;
}
.elite-badge-pill.expired {
  background: rgba(246, 70, 93, 0.12);
  border-color: rgba(246, 70, 93, 0.35);
  color: #F6465D;
}
.elite-hero-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(1.6rem, 3.2vw, 2.3rem);
  font-weight: 700;
  color: #FFFFFF;
  line-height: 1.25;
  margin: 0 0 10px;
}
.elite-hero-subtitle {
  color: #8FA3B8;
  font-size: 0.95rem;
  max-width: 580px;
  margin: 0 auto 28px;
  line-height: 1.55;
}

/* ── 4-Block Countdown Grid ── */
.elite-countdown-grid {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin: 0 auto 30px;
  max-width: 680px;
  flex-wrap: nowrap;
}
.elite-countdown-block {
  flex: 1;
  min-width: 90px;
  max-width: 135px;
  background: #151D2A;
  border: 1px solid #283548;
  border-top: 2px solid #1677FF;
  border-radius: 14px;
  padding: 20px 10px 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05);
  transition: transform 0.2s, border-color 0.2s;
}
.elite-countdown-block:hover {
  transform: translateY(-2px);
  border-top-color: #16C784;
}
.elite-countdown-num {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(2rem, 4vw, 2.75rem);
  font-weight: 700;
  color: #FFFFFF;
  line-height: 1;
  margin-bottom: 6px;
  letter-spacing: -0.02em;
}
.elite-countdown-label {
  font-family: 'Inter', sans-serif;
  font-size: 0.68rem;
  font-weight: 700;
  color: #8FA3B8;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

/* ── Meta Info Bar ── */
.elite-meta-box {
  background: rgba(16, 23, 34, 0.85);
  border: 1px solid #1E2D42;
  border-radius: 12px;
  padding: 14px 22px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 24px;
  margin-bottom: 28px;
  font-size: 0.85rem;
  color: #B8C3D1;
  flex-wrap: wrap;
}
.elite-meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
}
.elite-meta-item strong {
  color: #FFFFFF;
}

/* ── Action Buttons ── */
.elite-cta-group {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  flex-wrap: wrap;
}
.btn-upgrade-elites {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px 28px;
  background: linear-gradient(135deg, #1677FF 0%, #0D47A1 100%);
  color: #FFFFFF;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  border-radius: 10px;
  text-decoration: none;
  border: none;
  box-shadow: 0 8px 24px rgba(22, 119, 255, 0.3);
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: pointer;
}
.btn-upgrade-elites:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(22, 119, 255, 0.45);
}
.btn-vip-whatsapp {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 26px;
  background: rgba(22, 199, 132, 0.12);
  border: 1px solid rgba(22, 199, 132, 0.35);
  color: #16C784;
  font-family: 'Inter', sans-serif;
  font-size: 0.92rem;
  font-weight: 600;
  border-radius: 10px;
  text-decoration: none;
  transition: background 0.2s, border-color 0.2s, transform 0.2s;
}
.btn-vip-whatsapp:hover {
  background: #16C784;
  color: #101722;
  transform: translateY(-2px);
}

@media (max-width: 600px) {
  .elite-countdown-grid {
    gap: 8px;
  }
  .elite-countdown-block {
    min-width: 68px;
    padding: 14px 6px 10px;
    border-radius: 10px;
  }
  .elite-countdown-num {
    font-size: 1.65rem;
  }
  .elite-countdown-label {
    font-size: 0.58rem;
    letter-spacing: 0.06em;
  }
  .elite-hero-card {
    padding: 30px 16px 24px;
  }
  .elite-meta-box {
    flex-direction: column;
    gap: 8px;
    padding: 12px 14px;
  }
  .elite-cta-group {
    flex-direction: column;
    width: 100%;
  }
  .btn-upgrade-elites, .btn-vip-whatsapp {
    width: 100%;
  }
}

/* Feature cards */
.live-class-card {
  background: #151d2a;
  border: 1px solid #1e2d42;
  border-radius: 14px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  transition: transform 0.2s, border-color 0.2s;
}
.live-class-card:hover {
  border-color: rgba(22, 119, 255, 0.4);
  transform: translateY(-2px);
}

/* Pricing Card Styles */
.sp-card{background:linear-gradient(180deg,#151D29 0%,#101722 100%);border:1px solid #283548;border-radius:16px;padding:32px 24px 28px;text-align:center;position:relative;transition:all .25s ease;cursor:pointer;display:flex;flex-direction:column;max-width:440px;margin:0 auto}
.sp-card:hover{border-color:#1677FF;transform:translateY(-3px);box-shadow:0 12px 40px rgba(22,119,255,0.18)}
.sp-card__badge{position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#1677FF,#0D47A1);color:#FFFFFF;font-size:0.6rem;font-weight:700;padding:4px 16px;border-radius:20px;text-transform:uppercase;letter-spacing:0.8px;white-space:nowrap}
.sp-card__icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.3rem;background:rgba(22,119,255,0.12)}
.sp-card h3{font-family:'Space Grotesk',sans-serif;font-size:1.15rem;font-weight:600;color:#fff;margin-bottom:4px}
.sp-card__desc{font-size:0.82rem;color:#B8C3D1;margin-bottom:20px;line-height:1.4}
.sp-elite-tiers{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px}
.sp-elite-tier{background:#202B3A;border:1px solid #283548;border-radius:10px;padding:12px 10px;cursor:pointer;transition:all .2s;font-family:'Inter',sans-serif;text-align:center;display:block;text-decoration:none}
.sp-elite-tier:hover{border-color:#1677FF;background:rgba(22,119,255,0.12);transform:translateY(-1px)}
.sp-elite-tier:active{transform:translateY(0)}
.sp-elite-tier--full{grid-column:1/-1}
.sp-elite-tier__amount{display:block;font-size:1.05rem;font-weight:700;color:#FFFFFF;line-height:1.2}
.sp-elite-tier__usd{display:block;font-size:0.7rem;color:#7F8B99;margin-top:3px}
.sp-card__feat{list-style:none;text-align:left;margin:0 0 24px;flex:1;padding:0}
.sp-card__feat li{font-size:0.82rem;color:#B8C3D1;padding:6px 0;display:flex;align-items:flex-start;gap:10px;line-height:1.4}
.sp-card__feat li::before{content:'';width:16px;height:16px;border-radius:50%;background:rgba(22,199,132,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M6.5 11.5L3.5 8.5l1-1 2 2 5-5 1 1z' fill='%2316C784'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:center;background-size:12px}
</style>
<script>var API_BASE = '<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>';</script>
</head>
<body id="top" class="has-sidebar">

<!-- ===== Dashboard Sidebar ===== -->
<?php $activeNav = 'elites'; include __DIR__ . '/components/global_sidebar.php'; ?>

<div class="dash-main-wrap">
<!-- Unified Global Header -->
<?php include __DIR__ . '/components/global_header.php'; ?>

  <!-- Main BM Elites View -->
  <div class="dash-overview">
    <?php if (isset($_GET['payment_success']) && $_GET['payment_success'] == 1): ?>
      <div class="card-inner-box" style="text-align: center; max-width: 650px; margin: 40px auto; padding: 40px; background:#151D2A; border:1px solid #1E2D42; border-radius:16px;">
        <div style="width: 64px; height: 64px; background: rgba(22, 199, 132, 0.15); color: #16C784; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 24px;">
          &check;
        </div>
        <h1 class="dash-overview__title" style="margin-bottom: 16px;">Subscription Successful!</h1>
        <p style="color: #8fa3b8; font-size: 1.05rem; line-height: 1.6; margin-bottom: 28px;">
          Welcome to the <strong>BM Elites Trading Circle</strong>. Your managed capital access is now active.
        </p>
        
        <div style="background: rgba(246, 70, 93, 0.08); border: 1px solid rgba(246, 70, 93, 0.2); border-radius: 12px; padding: 20px; text-align: left; margin-bottom: 32px;">
          <h4 style="color: #F6465D; margin: 0 0 10px; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Risk Disclaimer
          </h4>
          <p style="color: rgba(246, 70, 93, 0.9); font-size: 0.85rem; line-height: 1.55; margin: 0;">
            Trading Forex and leveraged financial instruments involves significant risk and may result in the loss of your invested capital. 
            BM FOREX HUB provides this managed investment model as a service, but past performance is not indicative of future results.
          </p>
        </div>

        <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
          <a href="https://wa.me/254700000000?text=Hi%20BM%20Forex%20Hub,%20I%20have%20successfully%20subscribed%20to%20BM%20Elites.%20Please%20add%20me%20to%20the%20VIP%20WhatsApp%20group." target="_blank" rel="noopener noreferrer" class="btn-vip-whatsapp" style="padding:14px 28px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            Join VIP WhatsApp Group
          </a>
          <a href="bm_elites.php" class="btn-upgrade-elites">
            Enter BM Elites Circle &rarr;
          </a>
        </div>
      </div>
    <?php else: ?>
    
    <!-- Title block -->
    <div class="dash-overview__header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom: 24px;">
      <div>
        <h1 class="dash-overview__title">BM Elites Trading Circle</h1>
        <p class="dash-overview__subtitle">BM Elites Trading Circle is an elite managed investment model where capital contributed by participants is pooled together and traded in the foreign exchange market by senior quantitative analysts.</p>
      </div>
      <div class="breadcrumbs" style="color:#8fa3b8; font-size:0.83rem;">
        <a href="index.php" style="color:#8fa3b8; text-decoration:none;">Dashboard</a> / <span style="color:#1677FF;">BM Elites</span>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- 1. ACTIVE BM ELITES SUBSCRIBER EXPERIENCE (COUNTDOWN)   -->
    <!-- ======================================================== -->
    <div id="eliteActiveSubscriberHero" style="display:none;">
      
      <!-- Primary Live Countdown Card (Inspired by reference aesthetic) -->
      <div class="elite-hero-card">
        <div class="elite-badge-pill" id="eliteHeroBadge">
          <span style="font-size:0.9rem;">👑</span>
          <span id="eliteHeroBadgeText">ACTIVE BM ELITES ACCESS</span>
        </div>

        <h2 class="elite-hero-title" id="eliteCountdownHeading">
          Your Exclusive BM Elites Session Begins In
        </h2>
        <p class="elite-hero-subtitle" id="eliteCountdownSubheading">
          Weekly managed trading cycle is live. Profit distributions and institutional trade setups update dynamically.
        </p>

        <!-- 4-Digit Live Countdown Block -->
        <div class="elite-countdown-grid" id="eliteCountdownGrid">
          <div class="elite-countdown-block">
            <span class="elite-countdown-num" id="cdDays">00</span>
            <span class="elite-countdown-label">Days</span>
          </div>
          <div class="elite-countdown-block">
            <span class="elite-countdown-num" id="cdHours">00</span>
            <span class="elite-countdown-label">Hours</span>
          </div>
          <div class="elite-countdown-block">
            <span class="elite-countdown-num" id="cdMinutes">00</span>
            <span class="elite-countdown-label">Minutes</span>
          </div>
          <div class="elite-countdown-block">
            <span class="elite-countdown-num" id="cdSeconds">00</span>
            <span class="elite-countdown-label" style="color:#16C784;">Seconds</span>
          </div>
        </div>

        <!-- Expired Notice Container (Hidden until expired) -->
        <div id="eliteExpiredNotice" style="display:none; margin: 0 auto 24px; max-width: 540px; background: rgba(246, 70, 93, 0.1); border: 1px solid rgba(246, 70, 93, 0.35); border-radius: 12px; padding: 18px 24px;">
          <h3 style="color:#F6465D; font-size:1.1rem; margin:0 0 6px; font-family:'Space Grotesk',sans-serif;">Subscription Period Expired</h3>
          <p style="color:#8fa3b8; font-size:0.85rem; margin:0; line-height:1.5;">Your previous BM Elites subscription cycle has completed. Renew or upgrade your allocation to continue participating in the next pool cycle.</p>
        </div>

        <!-- Active Subscription Metadata Details -->
        <div class="elite-meta-box" id="eliteMetaBox">
          <div class="elite-meta-item">
            <span style="color:#8fa3b8;">Tier Allocation:</span>
            <strong id="eliteActiveTierLabel" style="color:#16C784;">BM Elites Plan</strong>
          </div>
          <div class="elite-meta-item">
            <span style="color:#8fa3b8;">Access Expiry:</span>
            <strong id="eliteActiveExpiryLabel">Calculating...</strong>
          </div>
          <div class="elite-meta-item">
            <span style="color:#8fa3b8;">Status:</span>
            <strong id="eliteActiveStatusLabel" style="color:#F0B429;">● Active Member</strong>
          </div>
        </div>

        <!-- Action CTAs -->
        <div class="elite-cta-group">
          <a href="subscribe.php?service=elite" class="btn-upgrade-elites" id="eliteUpgradeBtn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
            <span id="eliteUpgradeBtnText">Upgrade Elites</span>
          </a>
          <a href="https://wa.me/254700000000?text=Hi%20BM%20Forex%20Hub,%20I%20am%20an%20active%20BM%20Elites%20member." target="_blank" rel="noopener noreferrer" class="btn-vip-whatsapp">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            Join VIP WhatsApp Group
          </a>
        </div>
      </div>

      <!-- Unlocked Privileges Overview -->
      <div style="padding: 28px 30px; background: #151D2A; border: 1px solid #1E2D42; border-radius: 16px; margin-bottom: 32px;">
        <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 1.15rem; font-weight: 600; color: #fff; margin: 0 0 18px; display:flex; align-items:center; gap:8px;">
          <span style="color:#16C784;">✓</span> Unlocked Elite Trading Privileges
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
          <div style="background: #101722; border: 1px solid #1e2d42; border-radius: 12px; padding: 18px; display: flex; gap: 14px; align-items: flex-start; text-align: left;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(22, 119, 255, 0.15); color: #1677FF; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">&#9889;</div>
            <div>
              <h4 style="color: #fff; font-size: 0.95rem; font-weight: 600; margin: 0 0 4px;">High-Conviction Signals</h4>
              <p style="color: #8fa3b8; font-size: 0.82rem; margin: 0; line-height: 1.45;">Real-time trade entries, Take-Profit & Stop-Loss targets for Forex, Gold (XAUUSD) & Crypto.</p>
            </div>
          </div>
          <div style="background: #101722; border: 1px solid #1e2d42; border-radius: 12px; padding: 18px; display: flex; gap: 14px; align-items: flex-start; text-align: left;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(34, 197, 94, 0.15); color: #22C55E; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">&#128172;</div>
            <div>
              <h4 style="color: #fff; font-size: 0.95rem; font-weight: 600; margin: 0 0 4px;">VIP Mentorship Group</h4>
              <p style="color: #8fa3b8; font-size: 0.82rem; margin: 0; line-height: 1.45;">Direct access to senior traders, daily market breakdowns & consultation on WhatsApp.</p>
            </div>
          </div>
          <div style="background: #101722; border: 1px solid #1e2d42; border-radius: 12px; padding: 18px; display: flex; gap: 14px; align-items: flex-start; text-align: left;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(240, 180, 41, 0.15); color: #F0B429; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">&#128200;</div>
            <div>
              <h4 style="color: #fff; font-size: 0.95rem; font-weight: 600; margin: 0 0 4px;">5% Weekly Target Returns</h4>
              <p style="color: #8fa3b8; font-size: 0.82rem; margin: 0; line-height: 1.45;">Managed trading pool allocation with slot-proportional weekly earnings payouts.</p>
            </div>
          </div>
          <div style="background: #101722; border: 1px solid #1e2d42; border-radius: 12px; padding: 18px; display: flex; gap: 14px; align-items: flex-start; text-align: left;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(167, 139, 250, 0.15); color: #a78bfa; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">&#127891;</div>
            <div>
              <h4 style="color: #fff; font-size: 0.95rem; font-weight: 600; margin: 0 0 4px;">Exclusive Live Classes</h4>
              <p style="color: #8fa3b8; font-size: 0.82rem; margin: 0; line-height: 1.45;">Live Google Meet sessions, Smart Money Concepts (SMC) & ICT trading strategy archives.</p>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- ======================================================== -->
    <!-- 2. NON-SUBSCRIBER OVERVIEW & TIER SELECTION              -->
    <!-- ======================================================== -->
    <div id="eliteNonSubscriberHero" style="display:none;">
      <!-- Key Highlights Grid -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 36px;">
        <div class="live-class-card">
          <h3 style="color: #F0B429; margin-bottom: 8px; font-size: 1.05rem; font-family:'Space Grotesk',sans-serif;">Expert Trading Management</h3>
          <p style="color: #8fa3b8; font-size: 0.9rem; line-height: 1.5; margin:0;">Experienced forex analysts execute trades on behalf of the pool to optimize risk-managed returns.</p>
        </div>
        <div class="live-class-card">
          <h3 style="color: #F0B429; margin-bottom: 8px; font-size: 1.05rem; font-family:'Space Grotesk',sans-serif;">5% Weekly Target Returns</h3>
          <p style="color: #8fa3b8; font-size: 0.9rem; line-height: 1.5; margin:0;">Investors earn a target profit of <strong>5% weekly</strong> on their allocated capital.</p>
        </div>
        <div class="live-class-card">
          <h3 style="color: #F0B429; margin-bottom: 8px; font-size: 1.05rem; font-family:'Space Grotesk',sans-serif;">Weekly Income Payout</h3>
          <p style="color: #8fa3b8; font-size: 0.9rem; line-height: 1.5; margin:0;">Earnings are processed and paid out weekly directly to verified investor accounts.</p>
        </div>
        <div class="live-class-card">
          <h3 style="color: #F0B429; margin-bottom: 8px; font-size: 1.05rem; font-family:'Space Grotesk',sans-serif;">Slot-Based Allocation</h3>
          <p style="color: #8fa3b8; font-size: 0.9rem; line-height: 1.5; margin:0;">Profit distribution is proportional based strictly on the number of capital shares held.</p>
        </div>
      </div>
      
      <!-- Primary CTA Banner -->
      <div style="text-align: center; margin-bottom: 40px;">
        <a href="subscribe.php" class="btn-upgrade-elites" style="padding:16px 36px; font-size:1.05rem;">
          Upgrade to BM Elites
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>

      <!-- Tier Selection Card -->
      <div style="display: flex; justify-content: center; margin-bottom: 40px;">
        <div class="sp-card">
          <span class="sp-card__badge">Exclusive Circle</span>
          <div class="sp-card__icon">&#128081;</div>
          <h3>Select Investment Tier</h3>
          <div class="sp-card__desc">Choose your capital allocation tier to join the managed circle</div>
          <div class="sp-elite-tiers">
            <a href="subscribe.php?plan=elite_starter" class="sp-elite-tier">
              <span class="sp-elite-tier__amount">$1,000</span>
              <span class="sp-elite-tier__usd">Starter Tier</span>
            </a>
            <a href="subscribe.php?plan=elite_intermediate" class="sp-elite-tier">
              <span class="sp-elite-tier__amount">$2,000</span>
              <span class="sp-elite-tier__usd">Intermediate Tier</span>
            </a>
            <a href="subscribe.php?plan=elite_advanced" class="sp-elite-tier">
              <span class="sp-elite-tier__amount">$3,000</span>
              <span class="sp-elite-tier__usd">Advanced Tier</span>
            </a>
            <a href="subscribe.php?plan=elite_professional" class="sp-elite-tier">
              <span class="sp-elite-tier__amount">$5,000</span>
              <span class="sp-elite-tier__usd">Professional Tier</span>
            </a>
            <a href="subscribe.php?plan=elite_premium" class="sp-elite-tier">
              <span class="sp-elite-tier__amount">$6,000</span>
              <span class="sp-elite-tier__usd">Premium Tier</span>
            </a>
            <a href="subscribe.php?plan=elite_elite" class="sp-elite-tier">
              <span class="sp-elite-tier__amount">$10,000</span>
              <span class="sp-elite-tier__usd">Ultimate VIP Elite Tier</span>
            </a>
          </div>
          <ul class="sp-card__feat">
            <li>Exclusive WhatsApp mentorship group</li>
            <li>Weekly managed profit distribution</li>
            <li>High-conviction institutional signals</li>
            <li>Priority consultation & senior trader access</li>
          </ul>
          <a href="subscribe.php" class="btn-upgrade-elites" style="width:100%;">
            Subscribe to BM Elites
          </a>
        </div>
      </div>
    </div>

    <!-- Risk Disclaimer (Preserved) -->
    <div style="margin-bottom: 40px; padding: 18px 22px; background: #151d2a; border: 1px solid rgba(220, 53, 69, 0.25); border-radius: 12px; display: flex; align-items: flex-start; gap: 14px; max-width: 860px; margin-left: auto; margin-right: auto;">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6465d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; margin-top: 2px; opacity: 0.85;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      <div>
        <strong style="color: #f6465d; display: block; margin-bottom: 6px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Risk Disclaimer</strong>
        <span style="color: #8fa3b8; font-size: 0.82rem; line-height: 1.6; display: block;">Trading in foreign exchange and leveraged financial instruments carries significant risk. Return targets such as <strong>5% weekly</strong> are performance objectives based on trading strategy and market conditions and are <strong>not guaranteed</strong>. Always review the full risk disclosure and terms before allocating capital.</span>
      </div>
    </div>
    
    <?php endif; ?>
  </div>
  
  <!-- Footer -->
  <footer style="margin-top: 40px; padding: 20px 24px; border-top: 1px solid #1e2d42; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; font-size: 0.77rem; color: #5b6475;">
    <div>&copy; <?= date('Y') ?> BM Forex Hub. All Rights Reserved.</div>
    <div style="display: flex; gap: 16px;">
      <a href="terms.php" style="color:#5b6475; text-decoration:none;">Terms of Use</a>
      <span style="color:#1e2d42;">|</span>
      <a href="privacy.php" style="color:#5b6475; text-decoration:none;">Privacy Policy</a>
      <span style="color:#1e2d42;">|</span>
      <a href="risk-disclosure.php" style="color:#5b6475; text-decoration:none;">Risk Disclaimer</a>
      <span style="color:#1e2d42;">|</span>
      <a href="contact.php" style="color:#5b6475; text-decoration:none;">Support</a>
    </div>
  </footer>
</div>

<!-- Scripts for Supabase, Auth and Dynamic UI functionality -->
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script>
(async function() {
  /* ---------- Auth Guard ---------- */
  const { user, session } = await BMAuth.getSession();
  if (!user) { window.location.href = 'login.php'; return; }

  const authData = await BMAuth.getMembershipStatus(user, session);
  const userDisplayName = BMAuth.displayName(user);
  const displayUserEl = document.getElementById('displayUsername');
  if (displayUserEl) displayUserEl.textContent = userDisplayName;

  // Evaluate whether user has BM Elites subscription
  const plans = authData.plans || [];
  const isPermanent = authData.permanent_access || plans.includes('all');
  const hasElitePlan = isPermanent || plans.some(p => p.startsWith('elite_'));

  // Find exact active elite subscription record
  let eliteSub = null;
  if (Array.isArray(authData.subscriptions)) {
    eliteSub = authData.subscriptions.find(s => s.plan === 'all' || (s.plan && s.plan.startsWith('elite_')));
    if (!eliteSub && hasElitePlan && authData.subscriptions.length > 0) {
      eliteSub = authData.subscriptions[0];
    }
  }

  // Active status check
  const nowTs = Date.now();
  let expiryTs = 0;
  let isExpired = false;

  if (eliteSub && eliteSub.expires_at) {
    expiryTs = new Date(eliteSub.expires_at).getTime();
    isExpired = expiryTs <= nowTs;
  } else if (authData.subscription_expiry) {
    expiryTs = new Date(authData.subscription_expiry).getTime();
    isExpired = expiryTs <= nowTs;
  } else if (isPermanent) {
    expiryTs = new Date('2036-12-31T23:59:59Z').getTime();
    isExpired = false;
  }

  const isEliteActive = hasElitePlan && !isExpired;

  // Plan name dictionary
  const planNameMap = {
    'elite_starter': 'BM Elites — $1,000 USD Starter Tier',
    'elite_intermediate': 'BM Elites — $2,000 USD Intermediate Tier',
    'elite_advanced': 'BM Elites — $3,000 USD Advanced Tier',
    'elite_professional': 'BM Elites — $5,000 USD Professional Tier',
    'elite_premium': 'BM Elites — $6,000 USD Premium Tier',
    'elite_elite': 'BM Elites — $10,000 USD VIP Elite Tier',
    'all': 'BM Elites — Permanent Admin / VIP Access'
  };

  const activePlanKey = (eliteSub && eliteSub.plan) || (plans.find(p => p.startsWith('elite_'))) || (isPermanent ? 'all' : '');
  const activePlanTitle = planNameMap[activePlanKey] || (eliteSub && eliteSub.plan_name) || 'BM Elites Trading Circle';

  // Section DOM elements
  const subscriberHero = document.getElementById('eliteActiveSubscriberHero');
  const nonSubscriberHero = document.getElementById('eliteNonSubscriberHero');
  const badgeTextEl = document.getElementById('eliteMembershipBadgeText');
  const badgeContainerEl = document.getElementById('eliteMembershipBadge');

  if (isEliteActive) {
    // Show active subscriber view
    if (subscriberHero) subscriberHero.style.display = 'block';
    if (nonSubscriberHero) nonSubscriberHero.style.display = 'none';

    // Topbar badge
    if (badgeTextEl) badgeTextEl.textContent = '👑 Elite Member Active';
    if (badgeContainerEl) {
      badgeContainerEl.style.background = 'linear-gradient(135deg, rgba(240, 180, 41, 0.15) 0%, rgba(22, 119, 255, 0.15) 100%)';
      badgeContainerEl.style.borderColor = 'rgba(240, 180, 41, 0.4)';
      badgeContainerEl.style.color = '#F0B429';
    }

    // Populate metadata details
    const tierLabel = document.getElementById('eliteActiveTierLabel');
    if (tierLabel) tierLabel.textContent = activePlanTitle;

    const statusLabel = document.getElementById('eliteActiveStatusLabel');
    if (statusLabel) {
      statusLabel.textContent = isPermanent ? '● Permanent VIP Access' : '● Active Member';
      statusLabel.style.color = '#16C784';
    }

    const expiryLabel = document.getElementById('eliteActiveExpiryLabel');
    if (expiryLabel) {
      if (isPermanent) {
        expiryLabel.textContent = 'Permanent (Perpetual)';
      } else if (expiryTs > 0) {
        const d = new Date(expiryTs);
        expiryLabel.textContent = d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
      }
    }

    // Initialize Countdown Engine
    initEliteCountdown(expiryTs, isPermanent);

  } else if (hasElitePlan && isExpired) {
    // Expired subscriber view
    if (subscriberHero) subscriberHero.style.display = 'block';
    if (nonSubscriberHero) nonSubscriberHero.style.display = 'none';

    // Show expired state in countdown hero
    const expiredNotice = document.getElementById('eliteExpiredNotice');
    if (expiredNotice) expiredNotice.style.display = 'block';
    const countdownGrid = document.getElementById('eliteCountdownGrid');
    if (countdownGrid) countdownGrid.style.display = 'none';

    const heading = document.getElementById('eliteCountdownHeading');
    if (heading) heading.textContent = 'Your BM Elites Access Has Ended';

    const subheading = document.getElementById('eliteCountdownSubheading');
    if (subheading) subheading.textContent = 'Renew or upgrade your capital allocation to participate in the upcoming live trading session.';

    const heroBadge = document.getElementById('eliteHeroBadge');
    if (heroBadge) {
      heroBadge.className = 'elite-badge-pill expired';
      heroBadge.innerHTML = '<span>⚠️</span> <span>SUBSCRIPTION EXPIRED</span>';
    }

    const upgradeBtnText = document.getElementById('eliteUpgradeBtnText');
    if (upgradeBtnText) upgradeBtnText.textContent = 'Renew / Upgrade Elites';

    const statusLabel = document.getElementById('eliteActiveStatusLabel');
    if (statusLabel) {
      statusLabel.textContent = '● Subscription Expired';
      statusLabel.style.color = '#F6465D';
    }

    const tierLabel = document.getElementById('eliteActiveTierLabel');
    if (tierLabel) tierLabel.textContent = activePlanTitle + ' (Expired)';

    const expiryLabel = document.getElementById('eliteActiveExpiryLabel');
    if (expiryLabel && expiryTs > 0) {
      const d = new Date(expiryTs);
      expiryLabel.textContent = 'Expired on ' + d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    if (badgeTextEl) badgeTextEl.textContent = '⚠️ Subscription Expired';
    if (badgeContainerEl) {
      badgeContainerEl.style.background = 'rgba(246, 70, 93, 0.1)';
      badgeContainerEl.style.borderColor = 'rgba(246, 70, 93, 0.3)';
      badgeContainerEl.style.color = '#f6465d';
    }

  } else {
    // Non-subscriber view
    if (subscriberHero) subscriberHero.style.display = 'none';
    if (nonSubscriberHero) nonSubscriberHero.style.display = 'block';

    if (badgeTextEl) badgeTextEl.textContent = '⚠️ Subscription Required';
    if (badgeContainerEl) {
      badgeContainerEl.style.background = 'rgba(246, 70, 93, 0.1)';
      badgeContainerEl.style.borderColor = 'rgba(246, 70, 93, 0.3)';
      badgeContainerEl.style.color = '#f6465d';
    }
  }

  // Countdown function
  function initEliteCountdown(targetTs, isPerm) {
    const elDays = document.getElementById('cdDays');
    const elHours = document.getElementById('cdHours');
    const elMinutes = document.getElementById('cdMinutes');
    const elSeconds = document.getElementById('cdSeconds');

    if (!elDays || !elHours || !elMinutes || !elSeconds) return;

    // For permanent admin accounts, calculate recurring weekly session cycle (e.g. next Sunday 18:00 UTC)
    let dynamicTargetTs = targetTs;
    if (isPerm) {
      const now = new Date();
      const nextCycle = new Date(now);
      const daysUntilSunday = (7 - now.getDay()) % 7 || 7;
      nextCycle.setDate(now.getDate() + daysUntilSunday);
      nextCycle.setHours(18, 0, 0, 0);
      dynamicTargetTs = nextCycle.getTime();
    }

    function update() {
      const current = Date.now();
      const diff = dynamicTargetTs - current;

      if (diff <= 0) {
        elDays.textContent = '00';
        elHours.textContent = '00';
        elMinutes.textContent = '00';
        elSeconds.textContent = '00';
        
        if (!isPerm) {
          clearInterval(timerId);
          window.location.reload();
        } else {
          dynamicTargetTs += 7 * 86400000;
        }
        return;
      }

      const totalSeconds = Math.max(0, Math.floor(diff / 1000));
      const days = Math.floor(totalSeconds / 86400);
      const hours = Math.floor((totalSeconds % 86400) / 3600);
      const minutes = Math.floor((totalSeconds % 3600) / 60);
      const seconds = totalSeconds % 60;

      elDays.textContent = String(days).padStart(2, '0');
      elHours.textContent = String(hours).padStart(2, '0');
      elMinutes.textContent = String(minutes).padStart(2, '0');
      elSeconds.textContent = String(seconds).padStart(2, '0');
    }

    update();
    const timerId = setInterval(update, 1000);
  }

  // Mobile hamburger navigation
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const mobileNav = document.getElementById('mobileNav');
  const mobileNavClose = document.getElementById('mobileNavClose');

  if (hamburgerBtn && mobileNav) {
    const openMenu = () => {
      mobileNav.classList.add('open');
      hamburgerBtn.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
    };
    const closeMenu = () => {
      mobileNav.classList.remove('open');
      hamburgerBtn.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    };
    hamburgerBtn.addEventListener('click', openMenu);
    if (mobileNavClose) mobileNavClose.addEventListener('click', closeMenu);
    mobileNav.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));
    mobileNav.addEventListener('click', e => { if (e.target === mobileNav) closeMenu(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && mobileNav.classList.contains('open')) closeMenu(); });
  }
})();
</script>
<script src="js/motion.js" defer></script>
<?php $activeTab = 'more'; include __DIR__ . '/components/mobile-tabbar.php'; ?>
</body>
</html>
