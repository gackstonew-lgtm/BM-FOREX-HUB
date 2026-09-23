<?php require_once __DIR__ . '/engine_config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require_once __DIR__ . '/components/pwa_meta.php'; ?>
<title>Subscribe | BM Forex Hub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">
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
.sp-hero{text-align:center;padding:64px 24px 20px;position:relative}
.sp-hero::before{content:'';position:absolute;top:-120px;left:50%;transform:translateX(-50%);width:600px;height:600px;background:radial-gradient(circle,rgba(22,119,255,0.12) 0%,transparent 70%);pointer-events:none}
.sp-eyebrow{font-family:'IBM Plex Mono',monospace;font-size:0.7rem;letter-spacing:0.16em;text-transform:uppercase;color:#1677FF;margin-bottom:14px}
.sp-hero h1{font-family:'Space Grotesk',sans-serif;font-size:clamp(1.6rem,4vw,2.4rem);font-weight:700;color:#fff;line-height:1.2;margin-bottom:12px}
.sp-hero h1 em{font-style:normal;color:#1677FF}
.sp-hero p{color:rgba(255,255,255,0.6);font-size:0.95rem;max-width:520px;margin:0 auto;line-height:1.6}
.sp-trust{display:flex;align-items:center;justify-content:center;gap:24px;margin-top:24px;flex-wrap:wrap}
.sp-trust span{display:flex;align-items:center;gap:6px;font-size:0.75rem;color:rgba(255,255,255,0.5)}
.sp-trust svg{width:14px;height:14px;fill:rgba(255,255,255,0.4)}
.sp-card--elites .sp-card__icon{background:rgba(22,119,255,0.12)!important}
.sp-elite-tiers{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:18px}
.sp-elite-tier{background:#202B3A;border:1px solid #283548;border-radius:10px;padding:10px 8px;cursor:pointer;transition:all .2s;font-family:'Inter',sans-serif;text-align:center}
.sp-elite-tier:hover{border-color:#1677FF;background:rgba(22,119,255,0.08);transform:translateY(-1px)}
.sp-elite-tier:active{transform:translateY(0)}
.sp-elite-tier--full{grid-column:1/-1}
.sp-elite-tier__amount{display:block;font-size:1rem;font-weight:700;color:#FFFFFF;line-height:1.2}
.sp-elite-tier__usd{display:block;font-size:0.68rem;color:#7F8B99;margin-top:2px}

/* ── Step Indicator ── */
.sp-steps{display:flex;align-items:center;justify-content:center;gap:0;margin:36px auto 0;max-width:400px}
.sp-step{display:flex;align-items:center;gap:8px;font-size:0.78rem;color:rgba(255,255,255,0.4);font-weight:500}
.sp-step.active{color:#1677FF}
.sp-step.done{color:#16C784}
.sp-step__num{width:26px;height:26px;border-radius:50%;border:1.5px solid #283548;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;flex-shrink:0}
.sp-step.active .sp-step__num{border-color:#1677FF;background:rgba(22,119,255,0.15);color:#1677FF}
.sp-step.done .sp-step__num{border-color:#16C784;background:rgba(22,199,132,0.15);color:#16C784}
.sp-step__line{width:40px;height:1px;background:#283548;margin:0 10px;flex-shrink:0}
.sp-step.active+.sp-step__line{background:rgba(22,119,255,0.4)}

/* ── Alert ── */
.sp-alert{max-width:820px;width:100%;margin:24px auto 0;padding:0 24px}
.sp-alert__inner{border-radius:10px;padding:12px 18px;font-size:0.85rem;display:none}
.sp-alert__inner.show{display:flex;align-items:center;gap:10px}
.sp-alert__inner.success{background:rgba(22,199,132,0.1);border:1px solid rgba(22,199,132,0.3);color:#16C784}
.sp-alert__inner.error{background:rgba(246,70,93,0.1);border:1px solid rgba(246,70,93,0.3);color:#F6465D}
.sp-alert__icon{font-size:1.1rem;flex-shrink:0}

/* ── Plans Grid ── */
.sp-section{padding:20px 24px 0;max-width:1500px;width:100%;margin:0 auto}
.sp-section__title{font-family:'Space Grotesk',sans-serif;font-size:1.15rem;font-weight:600;color:#fff;margin-bottom:20px;text-align:center}
.sp-plans{display:grid;grid-template-columns:repeat(4,minmax(240px,1fr));gap:24px;align-items:stretch}
@media(max-width:1199px){.sp-plans{grid-template-columns:repeat(4,minmax(200px,1fr));gap:16px}.sp-card{padding:20px 16px 18px!important}}
@media(max-width:991px){.sp-plans{grid-template-columns:repeat(2,1fr);gap:20px;max-width:720px;margin:0 auto}}
@media(max-width:599px){.sp-plans{grid-template-columns:1fr;max-width:400px;margin:0 auto}}

.sp-card{background:linear-gradient(180deg,#151D29 0%,#101722 100%);border:1px solid #283548;border-radius:16px;padding:24px 20px 20px;text-align:center;position:relative;transition:all .25s ease;cursor:pointer;display:flex;flex-direction:column;height:100%}
.sp-card:hover{border-color:#1677FF;transform:translateY(-4px);box-shadow:0 12px 36px rgba(22,119,255,0.18)}
.sp-card.selected{border-color:#1677FF;box-shadow:0 0 0 1px #1677FF,0 12px 36px rgba(22,119,255,0.2)}
.sp-card__badge{position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#1677FF,#0D47A1);color:#FFFFFF;font-size:0.6rem;font-weight:700;padding:4px 14px;border-radius:20px;text-transform:uppercase;letter-spacing:0.8px;white-space:nowrap}
.sp-card__icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;font-size:1.2rem;flex-shrink:0}
.sp-card:nth-child(1) .sp-card__icon{background:rgba(22,119,255,0.12)}
.sp-card:nth-child(2) .sp-card__icon{background:rgba(22,199,132,0.12)}
.sp-card:nth-child(3) .sp-card__icon{background:rgba(139,92,246,0.12)}
.sp-card:nth-child(4) .sp-card__icon{background:rgba(255,193,7,0.12)}
.sp-card h3{font-family:'Space Grotesk',sans-serif;font-size:1.05rem;font-weight:600;color:#fff;margin-bottom:4px}
.sp-card__desc{font-size:0.76rem;color:#B8C3D1;margin-bottom:12px;line-height:1.4;min-height:36px;display:flex;align-items:center;justify-content:center}
.sp-card__price{margin-bottom:14px;min-height:46px;display:flex;flex-direction:column;justify-content:center;align-items:center}
.sp-card__price .amt{font-family:'Space Grotesk',sans-serif;font-size:1.55rem;font-weight:700;color:#fff;line-height:1}
.sp-card__price .cur{font-size:0.85rem;color:#B8C3D1;font-weight:400}
.sp-card__price .per{display:block;font-size:0.68rem;color:#7F8B99;margin-top:2px}
.sp-card__feat{list-style:none;text-align:left;margin:0 0 16px;flex:1;display:flex;flex-direction:column;gap:10px}
.sp-card__feat li{font-size:0.78rem;color:#B8C3D1;padding:0;display:flex;align-items:flex-start;gap:10px;line-height:1.35}
.sp-card__feat li::before{content:'';width:15px;height:15px;border-radius:50%;background:rgba(22,199,132,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M6.5 11.5L3.5 8.5l1-1 2 2 5-5 1 1z' fill='%2316C784'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:center;background-size:11px}
.sp-card__cta-wrap{margin-top:auto;width:100%}
.sp-card .btn{width:100%;margin-top:auto}
.sp-toggle{display:flex;gap:6px;margin-bottom:12px}
.sp-toggle__btn{flex:1;padding:6px 4px;border:1px solid #283548;border-radius:8px;background:transparent;color:#B8C3D1;font-family:'Inter',sans-serif;font-size:0.72rem;font-weight:600;cursor:pointer;transition:all .2s}
.sp-toggle__btn:hover{border-color:#1677FF}
.sp-toggle__btn.active{border-color:#1677FF;background:rgba(22,119,255,0.15);color:#1677FF}

/* Compact Elite Tiers Grid */
.sp-elite-tiers{display:grid;grid-template-columns:repeat(2,1fr);gap:6px;margin-bottom:12px}
.sp-elite-tier{padding:6px 4px;border:1px solid #283548;border-radius:8px;background:#151D29;color:#B8C3D1;font-family:'Inter',sans-serif;cursor:pointer;transition:all .2s;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center}
.sp-elite-tier:hover,.sp-elite-tier.active{border-color:#1677FF;background:rgba(22,119,255,0.15);color:#1677FF}
.sp-elite-tier--full{grid-column:span 2}
.sp-elite-tier__amount{font-family:'Space Grotesk',sans-serif;font-size:0.85rem;font-weight:700;color:#fff}
.sp-elite-tier__usd{font-size:0.62rem;color:#7F8B99;margin-top:1px}

/* ── Payment Panel ── */
.sp-pay-wrap{max-width:480px;width:100%;margin:36px auto 0;padding:0 24px;display:none}
.sp-pay-wrap.show{display:block}
.sp-pay-card{background:linear-gradient(180deg,#151D29 0%,#101722 100%);border:1px solid #283548;border-radius:16px;padding:32px 28px}
.sp-pay-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.sp-pay-header h3{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:600;color:#fff}
.sp-pay-back{background:none;border:none;color:#B8C3D1;cursor:pointer;font-size:0.82rem;display:flex;align-items:center;gap:4px;transition:color .2s}
.sp-pay-back:hover{color:#1677FF}
.sp-pay-selected{display:flex;align-items:center;gap:12px;padding:14px 16px;background:rgba(22,119,255,0.08);border:1px solid rgba(22,119,255,0.2);border-radius:10px;margin-bottom:24px}
.sp-pay-selected__name{font-weight:600;color:#fff;font-size:0.9rem}
.sp-pay-selected__price{color:#1677FF;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:0.9rem}

/* ── Payment Methods ── */
.sp-methods{margin-bottom:24px}
.sp-methods__label{font-size:0.78rem;color:#7F8B99;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600}
.sp-method{display:flex;align-items:center;gap:14px;padding:14px 16px;border:1px solid #283548;border-radius:10px;margin-bottom:8px;cursor:pointer;transition:all .2s}
.sp-method:hover{border-color:rgba(22,119,255,0.4);background:rgba(22,119,255,0.04)}
.sp-method.active{border-color:#1677FF;background:rgba(22,119,255,0.08)}
.sp-method.disabled{opacity:0.45;cursor:not-allowed;pointer-events:none}
.sp-method__icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.sp-method.mpesa .sp-method__icon{background:rgba(22,199,132,0.15)}
.sp-method.card .sp-method__icon{background:rgba(139,92,246,0.15)}
.sp-method__info{flex:1}
.sp-method__name{font-weight:600;color:#fff;font-size:0.88rem}
.sp-method__desc{font-size:0.75rem;color:#B8C3D1;margin-top:2px}
.sp-method__badge{font-size:0.6rem;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:0.5px;flex-shrink:0}
.sp-method.mpesa .sp-method__badge{background:rgba(22,199,132,0.15);color:#16C784}
.sp-method.card .sp-method__badge{background:rgba(255,255,255,0.08);color:#B8C3D1}

/* ── STK Form ── */
.sp-stk{display:none}
.sp-stk.show{display:block}
.sp-field{margin-bottom:16px;overflow:hidden}
.sp-field label{display:block;font-size:0.78rem;color:#B8C3D1;margin-bottom:6px;font-weight:500}
.sp-field__row{display:flex;align-items:stretch;border:1px solid #283548;border-radius:10px;overflow:hidden;transition:border-color .2s, box-shadow .2s;min-width:0;width:100%}
.sp-field__row:focus-within{border-color:#1677FF;box-shadow:0 0 0 3px rgba(22,119,255,0.2)}
.sp-field__prefix{display:flex;align-items:center;padding:0 14px;background:#202B3A;border-right:1px solid #283548;color:#B8C3D1;font-size:0.88rem;font-weight:500;white-space:nowrap}
.sp-field input[type="tel"],.sp-field input[type="text"],.sp-field input[type="password"]{flex:1;padding:14px;border:none;background:#202B3A;color:#fff;font-size:0.95rem;outline:none;min-width:0}
.sp-field input::placeholder{color:#7F8B99}
.sp-field__hint{font-size:0.72rem;color:#7F8B99;margin-top:6px}
.sp-submit{width:100%;padding:14px;border:none;border-radius:10px;background:linear-gradient(135deg,#1677FF 0%,#0D47A1 100%);color:#FFFFFF;font-family:'Space Grotesk',sans-serif;font-size:0.95rem;font-weight:700;cursor:pointer;transition:all .2s;letter-spacing:0.3px}
.sp-submit:hover{box-shadow:0 6px 24px rgba(22,119,255,0.4);transform:translateY(-1px)}
.sp-submit:disabled{opacity:0.5;cursor:not-allowed;transform:none;box-shadow:none}
.sp-submit:active{transform:translateY(0)}

/* ── Status ── */
.sp-status{text-align:center;padding:20px 0;display:none}
.sp-status.show{display:block}
.sp-status__ring{width:52px;height:52px;border:3px solid rgba(22,119,255,0.2);border-top-color:#1677FF;border-radius:50%;margin:0 auto 20px;animation:spR .7s linear infinite}
@keyframes spR{to{transform:rotate(360deg)}}
.sp-status__icon{width:52px;height:52px;border-radius:50%;margin:0 auto 20px;display:flex;align-items:center;justify-content:center;font-size:1.5rem}
.sp-status__icon.ok{background:rgba(22,199,132,0.1);border:2px solid rgba(22,199,132,0.3)}
.sp-status__icon.fail{background:rgba(246,70,93,0.1);border:2px solid rgba(246,70,93,0.3)}
.sp-status h3{font-family:'Space Grotesk',sans-serif;font-size:1.15rem;font-weight:600;color:#fff;margin-bottom:8px}
.sp-status p{color:#B8C3D1;font-size:0.88rem;line-height:1.55;margin-bottom:24px;max-width:360px;margin-left:auto;margin-right:auto}
.sp-status .sp-submit{max-width:280px;margin:0 auto}

/* ── Footer ── */
.sp-footer{margin-top:auto;padding:24px 32px;border-top:1px solid #283548;text-align:center}
.sp-footer p{font-size:0.72rem;color:#7F8B99}
.sp-footer__links{display:flex;justify-content:center;gap:20px;margin-bottom:8px}
.sp-footer__links a{font-size:0.72rem;color:#B8C3D1;text-decoration:none;transition:color .2s}
.sp-footer__links a:hover{color:#1677FF}

/* ── Animations ── */
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.sp-fade{animation:fadeUp .5s ease both}
.sp-fade.d1{animation-delay:.1s}
.sp-fade.d2{animation-delay:.2s}
.sp-fade.d3{animation-delay:.3s}
</style>
<script>var API_BASE = '<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>';</script>
</head>
<body class="has-sidebar">

<!-- ===== Dashboard Sidebar ===== -->
<?php $activeNav = 'copy_trading'; include __DIR__ . '/components/global_sidebar.php'; ?>

<div class="dash-main-wrap">
<!-- Unified Global Header -->
<?php include __DIR__ . '/components/global_header.php'; ?>

<!-- Hero -->
<section class="sp-hero sp-fade">
  <div class="sp-eyebrow">Premium Access</div>
  <h1>Unlock the <em>full</em> trading platform</h1>
  <p>Get live SMC signals, professional education, and real-time market intelligence. Cancel anytime.</p>
  <div class="sp-trust">
    <span>
      <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
      Secure payment
    </span>
    <span>
      <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
      Instant activation
    </span>
    <span>
      <svg viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
      Cancel anytime
    </span>
  </div>
</section>

<!-- Steps -->
<div class="sp-steps sp-fade d1">
  <div class="sp-step active" id="step1Ind"><span class="sp-step__num">1</span> Choose plan</div>
  <div class="sp-step__line"></div>
  <div class="sp-step" id="step2Ind"><span class="sp-step__num">2</span> Pay</div>
  <div class="sp-step__line"></div>
  <div class="sp-step" id="step3Ind"><span class="sp-step__num">3</span> Access</div>
</div>

<!-- Alert -->
<div class="sp-alert"><div class="sp-alert__inner" id="spAlert"><span class="sp-alert__icon"></span><span id="spAlertMsg"></span></div></div>

<!-- Plans -->
<section class="sp-section sp-fade d2" id="spPlansSection">
  <div class="sp-plans" id="spPlans">

    <!-- 1. Grid Signal -->
    <div class="sp-card" data-plan-group="grid">
      <div class="sp-card__icon" style="background:rgba(22,119,255,0.12)">&#128200;</div>
      <h3>Grid Signal</h3>
      <div class="sp-card__desc">Live data &amp; analysis tools</div>
      <div class="sp-toggle" role="tablist">
        <button type="button" class="sp-toggle__btn active" onclick="spSetSub(event,'grid_monthly')">Monthly</button>
        <button type="button" class="sp-toggle__btn" onclick="spSetSub(event,'grid_lifetime')">Lifetime</button>
      </div>
      <div class="sp-card__price">
        <span class="amt"><span class="cur">$</span><span class="sp-sub-amt">25</span></span>
        <span class="per sp-sub-per">per month</span>
      </div>
      <ul class="sp-card__feat">
        <li>SMC (Smart Money Concepts) Setups</li>
        <li>Real ECB Data Feed</li>
        <li>40+ Forex Pairs + Gold</li>
        <li>Currency Strength Meter</li>
        <li>Economic Calendar Integration</li>
        <li>Institutional Entry, SL &amp; TP Levels</li>
        <li>Daily High-Probability Trading Opportunities</li>
      </ul>
      <div class="sp-card__cta-wrap">
        <button class="btn btn-primary" type="button" data-sub="grid_monthly" onclick="spSelectSub(this)">Subscribe Monthly</button>
      </div>
    </div>

    <!-- 2. Copy Trading Integration -->
    <div class="sp-card" onclick="spSelect('copytrading')" data-plan="copytrading">
      <span class="sp-card__badge">Most Popular</span>
      <div class="sp-card__icon" style="background:rgba(22,199,132,0.12)">&#127919;</div>
      <h3>Copy Trading Integration</h3>
      <div class="sp-card__desc">Fully automated, professionally managed copy trading</div>
      <div class="sp-card__price">
        <span class="amt"><span class="cur">$</span>249</span>
        <span class="per">one-time payment</span>
      </div>
      <ul class="sp-card__feat">
        <li>Fully automated trade execution</li>
        <li>Professionally managed SMC-based strategies</li>
        <li>Smart risk management with optimized lot sizing</li>
        <li>Lifetime integration &amp; setup support</li>
      </ul>
      <div class="sp-card__cta-wrap">
        <button class="btn btn-primary" type="button">Select Plan</button>
      </div>
    </div>

    <!-- 3. Forex Classes -->
    <div class="sp-card" data-plan-group="classes">
      <div class="sp-card__icon" style="background:rgba(139,92,246,0.12)">&#128218;</div>
      <h3>Forex Classes</h3>
      <div class="sp-card__desc">Master trading from zero to pro</div>
      <div class="sp-toggle" role="tablist">
        <button type="button" class="sp-toggle__btn active" onclick="spSetSub(event,'classes_online')">Online</button>
        <button type="button" class="sp-toggle__btn" onclick="spSetSub(event,'classes_physical')">Physical</button>
      </div>
      <div class="sp-card__price">
        <span class="amt"><span class="cur">$</span><span class="sp-sub-amt">399</span></span>
        <span class="per sp-sub-per">online classes</span>
      </div>
      <ul class="sp-card__feat">
        <li>Beginner to Advanced Curriculum</li>
        <li>Smart Money Concepts (SMC) Masterclass</li>
        <li>Strategy Breakdowns with Live Market Examples</li>
        <li>Hands-on Chart Analysis</li>
        <li>Live Trading Sessions with Professional Mentors</li>
        <li>Risk Management &amp; Trading Psychology</li>
        <li>Weekly Q&amp;A Sessions, Assignments &amp; Performance Reviews</li>
      </ul>
      <div class="sp-card__cta-wrap">
        <button class="btn btn-primary" type="button" data-sub="classes_online" onclick="spSelectSub(this)">Enroll Online</button>
      </div>
    </div>

    <!-- 4. BM Elites -->
    <div class="sp-card sp-card--elites" data-plan-group="elites">
      <span class="sp-card__badge">Premium Access</span>
      <div class="sp-card__icon" style="background:rgba(255,193,7,0.12)">&#128081;</div>
      <h3>BM Elites</h3>
      <div class="sp-card__desc">Professionally managed investment</div>
      <div class="sp-elite-tiers">
        <button class="sp-elite-tier" type="button" onclick="spSelect('elite_starter')">
          <span class="sp-elite-tier__amount">$1,000</span>
          <span class="sp-elite-tier__usd">125,000 KES</span>
        </button>
        <button class="sp-elite-tier" type="button" onclick="spSelect('elite_intermediate')">
          <span class="sp-elite-tier__amount">$2,000</span>
          <span class="sp-elite-tier__usd">250,000 KES</span>
        </button>
        <button class="sp-elite-tier" type="button" onclick="spSelect('elite_advanced')">
          <span class="sp-elite-tier__amount">$3,000</span>
          <span class="sp-elite-tier__usd">375,000 KES</span>
        </button>
        <button class="sp-elite-tier" type="button" onclick="spSelect('elite_professional')">
          <span class="sp-elite-tier__amount">$5,000</span>
          <span class="sp-elite-tier__usd">625,000 KES</span>
        </button>
        <button class="sp-elite-tier" type="button" onclick="spSelect('elite_premium')">
          <span class="sp-elite-tier__amount">$6,000</span>
          <span class="sp-elite-tier__usd">750,000 KES</span>
        </button>
        <button class="sp-elite-tier" type="button" onclick="spSelect('elite_elite')">
          <span class="sp-elite-tier__amount">$10,000</span>
          <span class="sp-elite-tier__usd">1,250,000 KES</span>
        </button>
      </div>
      <ul class="sp-card__feat">
        <li>Elite Trading Community Access</li>
        <li>Weekly Income Distribution</li>
        <li>Consistent Income Through Managed Trading</li>
        <li>Professionally Managed Trading Capital</li>
        <li>Risk-Managed Investment Strategy</li>
        <li>Transparent Performance Reporting</li>
        <li>Dedicated Investor Support</li>
      </ul>
      <div class="sp-card__cta-wrap">
        <button class="btn btn-primary" type="button" onclick="spSelect('elite_starter')">Select Investment</button>
      </div>
    </div>

  </div>
</section>

<!-- Payment Panel -->
<div class="sp-pay-wrap" id="spPayWrap">
  <div class="sp-pay-card">

    <div class="sp-pay-header">
      <h3>Complete Payment</h3>
      <button class="sp-pay-back" onclick="spShowPlans()">&#8592; Change plan</button>
    </div>

    <div class="sp-pay-selected">
      <span class="sp-pay-selected__name" id="spSelName">Copy Trading Integration</span>
      <div style="text-align:right">
        <span class="sp-pay-selected__price" id="spSelPrice">KES 32,121</span>
        <span style="display:block;font-size:0.72rem;color:rgba(255,255,255,0.3);margin-top:2px" id="spSelUsd">$249 USD</span>
      </div>
    </div>

    <!-- Payment Methods -->
    <div class="sp-methods">
      <div class="sp-methods__label">Payment method</div>

      <div class="sp-method mpesa active" onclick="spPickMethod('mpesa')" id="methodMpesa">
        <div class="sp-method__icon">&#128176;</div>
        <div class="sp-method__info">
          <div class="sp-method__name">Mobile Money</div>
          <div class="sp-method__desc">Pay via M-Pesa, Airtel Money &amp; more</div>
        </div>
      </div>

      <div class="sp-method card" onclick="spPickMethod('card')" id="methodCard">
        <div class="sp-method__icon">&#128179;</div>
        <div class="sp-method__info">
          <div class="sp-method__name">Credit / Debit Card</div>
          <div class="sp-method__desc">Visa, Mastercard, Verve &amp; More</div>
        </div>
      </div>

    </div>

    <!-- STK Push / Card Form -->
    <div class="sp-stk show" id="spStk">
      <form onsubmit="spSubmit(event)">

        <div class="sp-redirect-hint" id="spMpesaHint">
          <div style="text-align:center;padding:20px 0">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#1677FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <p style="color:rgba(255,255,255,0.55);font-size:0.88rem;margin:0 0 4px">You'll be redirected to <strong style="color:#fff">Kora Pay</strong> to enter your phone number</p>
            <p style="color:rgba(255,255,255,0.3);font-size:0.75rem;margin:0">Select M-Pesa or your preferred mobile money provider on the next page</p>
          </div>
        </div>

        <!-- Credit / Debit Card Form -->
        <div id="spCardWrap" style="display:none">
          <div class="sp-field" style="margin-bottom:14px">
            <label for="spCardName">Cardholder Name</label>
            <div class="sp-field__row">
              <input type="text" id="spCardName" placeholder="Full Name on Card" autocomplete="cc-name">
            </div>
          </div>

          <div class="sp-field" style="margin-bottom:14px">
            <label for="spCardNumber">Card Number</label>
            <div class="sp-field__row">
              <input type="text" id="spCardNumber" placeholder="4000 0000 0000 0000" maxlength="19" autocomplete="cc-number">
            </div>
          </div>

          <div style="display:flex;gap:12px;margin-bottom:14px">
            <div class="sp-field" style="flex:1;min-width:0;margin-bottom:0">
              <label for="spCardExpiry">Expiry Date</label>
              <div class="sp-field__row">
                <input type="text" id="spCardExpiry" placeholder="MM/YY" maxlength="5" autocomplete="cc-exp">
              </div>
            </div>

            <div class="sp-field" style="width:110px;flex-shrink:0;margin-bottom:0">
              <label for="spCardCvv">CVV / CVC</label>
              <div class="sp-field__row">
                <input type="password" id="spCardCvv" placeholder="123" maxlength="4" autocomplete="cc-csc">
              </div>
            </div>
          </div>
        </div>

        <button type="submit" class="sp-submit" id="spPayBtn">Pay via Mobile Money</button>
      </form>
    </div>

    <!-- Status -->
    <div class="sp-status" id="spStatus">
      <div class="sp-status__ring" id="spRing"></div>
      <div class="sp-status__icon" id="spStatusIcon" style="display:none"></div>
      <h3 id="spStatusTitle">Sending payment prompt...</h3>
      <p id="spStatusMsg">Check your phone for the payment prompt</p>
      <button class="sp-submit" id="spStatusBtn" style="display:none"></button>
    </div>

  </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<?php
require_once __DIR__ . '/api/kora-config.php';
require_once __DIR__ . '/app/Services/CurrencyConversionService.php';

$currencyService = new \App\Services\CurrencyConversionService();
$plansDynamic = [];
foreach ($KORA_PLANS_USD as $k => $p) {
    $kesVal = $currencyService->convertUsdToKes($p['amount_usd']);
    $plansDynamic[$k] = [
        'name' => $p['name'],
        'usd'  => '$' . number_format($p['amount_usd']),
        'kes'  => 'KES ' . number_format($kesVal),
        'raw'  => $kesVal
    ];
}
?>
<script>
var API_BASE = typeof API_BASE !== 'undefined' ? API_BASE : '';
(function(){
  var WHATSAPP_NUMBER = '254700000000'; // ← set your WhatsApp number here
  var PLANS = <?php echo json_encode($plansDynamic); ?>;
  var SUB_META={
    grid_monthly:{amt:'25',per:'per month',cta:'Subscribe Monthly'},
    grid_lifetime:{amt:'499',per:'one-time payment',cta:'Buy Lifetime Access'},
    classes_online:{amt:'399',per:'online classes',cta:'Enroll Online'},
    classes_physical:{amt:'599',per:'physical classes',cta:'Join Physical Class'}
  };
  window.spSetSub=function(e,sub){
    var btn=e.currentTarget||e.target;
    var card=btn.closest('.sp-card');
    card.querySelectorAll('.sp-toggle__btn').forEach(function(b){b.classList.remove('active')});
    btn.classList.add('active');
    var meta=SUB_META[sub];
    card.querySelector('.sp-sub-amt').textContent=meta.amt;
    card.querySelector('.sp-sub-per').textContent=meta.per;
    var selBtn=card.querySelector('button.btn-primary');
    selBtn.textContent=meta.cta;
    selBtn.dataset.sub=sub;
  };
  window.spSelectSub=function(btn){
    spSelect(btn.dataset.sub);
  };
  var _selected='';
  var _alert=document.getElementById('spAlert');
  var _alertMsg=document.getElementById('spAlertMsg');

  function showAlert(msg,type){
    _alertMsg.textContent=msg;
    _alert.className='sp-alert__inner show '+(type||'success');
  }
  function hideAlert(){_alert.className='sp-alert__inner'}

  /* Auth + subscription check */
  (async function(){
    try{
      var s=await BMAuth.getSession();
      if(!s.user){window.location.href='login.php';return;}
      if(['bonfacewana3072@gmail.com','langatgift6@gmail.com','gackstoneb@gmail.com'].indexOf((s.user.email||'').trim().toLowerCase())!==-1){window.location.href='index.php';return;}
      let authData = await BMAuth.getMembershipStatus(s.user, s.session);
      window._authPlans = authData.plans || [];
    }catch(e){}
    if(window.location.search.includes('registered=1')){
      showAlert('Account created! Choose a plan below to unlock the dashboard.','success');
    }

    var params=new URLSearchParams(window.location.search);
    var payStatus=params.get('status');
    var errParam=params.get('error');
    if(errParam==='subscription_required'){
      showAlert('An active Copy Trading subscription is required to access MT5 account setup. Please complete payment below.','error');
    }

    if(payStatus==='success'){
      var _pendingPlan = sessionStorage.getItem('pendingPlan');
      if (_pendingPlan && (_pendingPlan === 'copytrading' || _pendingPlan === 'copy_trading')) {
        sessionStorage.removeItem('pendingPlan');
        window.location.href = 'copy_trading.php?payment_success=1';
        return;
      }
      if (_pendingPlan && _pendingPlan.indexOf('elite_') === 0) {
        sessionStorage.removeItem('pendingPlan');
        window.location.href = 'bm_elites.php?payment_success=1';
        return;
      }
      // Redirect to clean URL then poll for subscription
      sessionStorage.removeItem('pendingPlan');
      document.getElementById('spPlansSection').style.display='none';
      document.getElementById('spPayWrap').classList.add('show');
      document.getElementById('spStk').classList.remove('show');
      document.getElementById('spStatus').classList.add('show');
      document.getElementById('spRing').style.display='';
      document.getElementById('spStatusIcon').style.display='none';
      document.getElementById('spStatusTitle').textContent='Confirming your payment...';
      document.getElementById('spStatusMsg').textContent='Your payment was received. We are activating your subscription now.';
      setStep(2);
      spPoll(24);
    }else if(payStatus==='failed' || payStatus==='cancelled'){
      // Kora redirected back with failure status
      // Re-open the payment panel with the last selected plan so user can retry immediately
      var _failedPlan = sessionStorage.getItem('pendingPlan');
      var _failMsg = payStatus === 'cancelled'
        ? 'Payment was cancelled. Choose your plan and try again.'
        : 'Payment was not completed on the payment page. Please try again.';
      showAlert(_failMsg, 'error');
      if (_failedPlan && PLANS[_failedPlan]) {
        // Re-open the payment panel for this plan without clearing pendingPlan
        setTimeout(function(){
          window.spSelect(_failedPlan);
        }, 400);
      } else {
        sessionStorage.removeItem('pendingPlan');
      }
    }

    /* Deep-link: ?service=copy_trading | ?plan=copytrading | ?plan=classes */
      var deepPlan=params.get('plan') || params.get('service');
    if(deepPlan){
      var target=null;
      if(deepPlan==='copytrading' || deepPlan==='copy_trading'){
        target=document.querySelector('[data-plan="copytrading"]');
      }else if(deepPlan==='classes'){
        target=document.querySelector('[data-plan-group="classes"]');
      }else if(deepPlan==='grid'){
        target=document.querySelector('[data-plan-group="grid"]');
      }else if(deepPlan==='elite' || deepPlan==='elites' || deepPlan.indexOf('elite_')===0){
        target=document.querySelector('[data-plan-group="elites"]');
        if(deepPlan.indexOf('elite_')===0 && typeof window.spSelect === 'function' && PLANS[deepPlan]){
          window.spSelect(deepPlan);
          return;
        }
      }
      if(target){
        setTimeout(function(){
          target.scrollIntoView({behavior:'smooth',block:'center'});
          target.style.boxShadow='0 0 0 2px #1677FF';
          target.style.transition='box-shadow .3s';
          setTimeout(function(){target.style.boxShadow='';},3000);
        },300);
      }
    }
  })();

  /* Step indicator */
  function setStep(n){
    ['step1Ind','step2Ind','step3Ind'].forEach(function(id,i){
      var el=document.getElementById(id);
      el.className='sp-step'+(i+1===n?' active':i+1<n?' done':'');
    });
  }

  /* Select plan */
  window.spSelect=async function(plan){
    var m=PLANS[plan];if(!m)return;

    // Check mandatory Elite Circle Terms acceptance if selecting an Elite plan
    if(plan.indexOf('elite_') === 0){
      try {
        var s = await BMAuth.getSession();
        var tok = s && s.session ? s.session.access_token : '';
        var checkResp = await fetch((typeof API_BASE !== 'undefined' ? API_BASE : '') + '/api/elite-enrollment.php?action=check', {
          headers: { 'Authorization': 'Bearer ' + tok }
        });
        if(checkResp.ok){
          var checkData = await checkResp.json();
          if(!checkData.accepted){
            window.location.href = 'elite_enrollment.php?plan=' + encodeURIComponent(plan);
            return;
          }
        }
      } catch(e) {}
    }

    _selected=plan;
    sessionStorage.setItem('pendingPlan', plan);
    document.getElementById('spSelName').textContent=m.name;
    document.getElementById('spSelPrice').textContent=m.kes;
    document.getElementById('spSelUsd').textContent=m.usd+' USD';
    spPickMethod(_payMethod || 'mpesa');
    document.getElementById('spPlansSection').style.display='none';
    document.getElementById('spPayWrap').classList.add('show');
    document.getElementById('spStk').classList.add('show');
    document.getElementById('spStatus').classList.remove('show');
    hideAlert();
    setStep(2);
    window.scrollTo({top:0,behavior:'smooth'});
  };

  window.spShowPlans=function(){
    _selected='';
    sessionStorage.removeItem('pendingPlan');
    document.getElementById('spPlansSection').style.display='';
    document.getElementById('spPayWrap').classList.remove('show');
    hideAlert();
    setStep(1);
  };

  var _payMethod = 'mpesa';

  window.spPickMethod=function(m){
    _payMethod = m;
    var elM = document.getElementById('methodMpesa');
    var elC = document.getElementById('methodCard');
    var mpesaHint = document.getElementById('spMpesaHint');
    var cardWrap = document.getElementById('spCardWrap');
    var cardName = document.getElementById('spCardName');
    var cardNumber = document.getElementById('spCardNumber');
    var cardExpiry = document.getElementById('spCardExpiry');
    var cardCvv = document.getElementById('spCardCvv');
    var btn = document.getElementById('spPayBtn');
    var planKes = PLANS[_selected] ? PLANS[_selected].kes : '';

    if(m==='card'){
      if(elM) elM.classList.remove('active');
      if(elC) elC.classList.add('active');
      if(mpesaHint) mpesaHint.style.display='none';
      if(cardWrap) cardWrap.style.display='block';
      if(cardName) cardName.setAttribute('required','required');
      if(cardNumber) cardNumber.setAttribute('required','required');
      if(cardExpiry) cardExpiry.setAttribute('required','required');
      if(cardCvv) cardCvv.setAttribute('required','required');
      if(btn) btn.textContent = 'Pay ' + planKes + ' via Credit / Debit Card';
    }else{
      if(elC) elC.classList.remove('active');
      if(elM) elM.classList.add('active');
      if(cardWrap) cardWrap.style.display='none';
      if(cardName) cardName.removeAttribute('required');
      if(cardNumber) cardNumber.removeAttribute('required');
      if(cardExpiry) cardExpiry.removeAttribute('required');
      if(cardCvv) cardCvv.removeAttribute('required');
      if(mpesaHint) mpesaHint.style.display='';
      if(btn) btn.textContent = 'Pay ' + planKes + ' via Mobile Money';
    }
  };

  /* Auto-format Card Inputs */
  document.addEventListener('DOMContentLoaded', function(){
    var cardNumEl = document.getElementById('spCardNumber');
    if(cardNumEl){
      cardNumEl.addEventListener('input', function(e){
        var v = e.target.value.replace(/\D/g, '').substring(0, 16);
        var parts = [];
        for(var i=0; i<v.length; i+=4){ parts.push(v.substring(i, i+4)); }
        e.target.value = parts.join(' ');
      });
    }
    var cardExpEl = document.getElementById('spCardExpiry');
    if(cardExpEl){
      cardExpEl.addEventListener('input', function(e){
        var v = e.target.value.replace(/\D/g, '').substring(0, 4);
        if(v.length >= 3){
          e.target.value = v.substring(0, 2) + '/' + v.substring(2);
        }else{
          e.target.value = v;
        }
      });
    }
    var cardCvvEl = document.getElementById('spCardCvv');
    if(cardCvvEl){
      cardCvvEl.addEventListener('input', function(e){
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
      });
    }
  });

  /* Submit payment */
  window.spSubmit=async function(e){
    e.preventDefault();
    if(!_selected)return;

    var cardName = '', cardNumber = '', cardExpiry = '', cardCvv = '';
    if(_payMethod==='card'){
      cardName = (document.getElementById('spCardName').value || '').trim();
      cardNumber = (document.getElementById('spCardNumber').value || '').replace(/\s/g,'').trim();
      cardExpiry = (document.getElementById('spCardExpiry').value || '').trim();
      cardCvv = (document.getElementById('spCardCvv').value || '').trim();

      if(!cardName || !cardNumber || !cardExpiry || !cardCvv){
        alert('Please complete all card details (Name, Card Number, Expiry Date, CVV).');
        return;
      }
      if(cardNumber.length < 13){
        alert('Please enter a valid card number.');
        return;
      }
    }

    var btn=document.getElementById('spPayBtn');
    btn.disabled=true;btn.textContent='Processing request...';
    hideAlert();

    document.getElementById('spStk').classList.remove('show');
    document.getElementById('spStatus').classList.add('show');
    document.getElementById('spRing').style.display='';
    document.getElementById('spStatusIcon').style.display='none';
    document.getElementById('spStatusTitle').textContent=(_payMethod==='card')?'Processing Card Payment...':'Redirecting to payment gateway...';
    document.getElementById('spStatusMsg').textContent=(_payMethod==='card')?'Verifying your credit/debit card details with gateway...':'You\'ll be redirected to Kora Pay to complete your payment';
    document.getElementById('spStatusBtn').style.display='none';

    try{
      var s=await BMAuth.getSession();
      var t=s?s.session.access_token:'';
      var r=await fetch(API_BASE + '/api/deposit.php',{
        method:'POST',
        headers:{'Content-Type':'application/json','Authorization':'Bearer '+t},
        body:JSON.stringify({
          plan:_selected,
          payment_method:_payMethod,
          card_name:cardName,
          card_number:cardNumber,
          card_expiry:cardExpiry,
          card_cvv:cardCvv
        })
      });
      var d;
      try{ d=await r.json(); }catch(parseErr){
        console.error('API response not JSON:', parseErr);
        throw new Error('The payment server returned an invalid response. Please try again.');
      }
      if(d.ok){
        if(d.checkout_url){
          window.location.href=d.checkout_url;
          return;
        }
        document.getElementById('spRing').style.display='none';
        document.getElementById('spStatusIcon').className='sp-status__icon ok';
        document.getElementById('spStatusIcon').style.display='';
        document.getElementById('spStatusIcon').textContent='\u2713';
        document.getElementById('spStatusTitle').textContent=(_payMethod==='card')?'Payment Processed!':'Prompt sent!';
        document.getElementById('spStatusMsg').textContent=d.message||((_payMethod==='card')?'Your card payment has been confirmed successfully.':'You will be redirected to complete your payment.');
        setStep(3);
        spPoll(12);
      }else{
        document.getElementById('spRing').style.display='none';
        document.getElementById('spStatusIcon').className='sp-status__icon fail';
        document.getElementById('spStatusIcon').style.display='';
        document.getElementById('spStatusIcon').textContent='\u2717';
        document.getElementById('spStatusTitle').textContent='Payment failed';
        document.getElementById('spStatusMsg').textContent=d.error||d.message||'Something went wrong. Please try again.';
        document.getElementById('spStatusBtn').textContent='Try Again';
        document.getElementById('spStatusBtn').style.display='';
        document.getElementById('spStatusBtn').onclick=function(){
          document.getElementById('spStatus').classList.remove('show');
          document.getElementById('spStk').classList.add('show');
          btn.disabled=false;
          spPickMethod(_payMethod);
        };
      }
    }catch(err){
      console.error('Payment request failed:', err);
      document.getElementById('spRing').style.display='none';
      document.getElementById('spStatusIcon').className='sp-status__icon fail';
      document.getElementById('spStatusIcon').style.display='';
      document.getElementById('spStatusIcon').textContent='\u2717';
      document.getElementById('spStatusTitle').textContent='Connection error';
      var errMsg = 'Could not reach the payment server. Please check your connection and try again.';
      if(err && err.message) errMsg = err.message;
      document.getElementById('spStatusMsg').textContent=errMsg;
      document.getElementById('spStatusBtn').textContent='Try Again';
      document.getElementById('spStatusBtn').style.display='';
      document.getElementById('spStatusBtn').onclick=function(){
        document.getElementById('spStatus').classList.remove('show');
        document.getElementById('spStk').classList.add('show');
        btn.disabled=false;
        spPickMethod(_payMethod);
      };
    }
    btn.disabled=false;
    spPickMethod(_payMethod);
  };

  /* Poll for confirmation */
  function spPoll(n){
    if(n<=0){
      document.getElementById('spRing').style.display='none';
      document.getElementById('spStatusIcon').className='sp-status__icon ok';
      document.getElementById('spStatusIcon').style.display='';
      document.getElementById('spStatusIcon').textContent='\u23F3';
      document.getElementById('spStatusTitle').textContent='Still waiting...';
      document.getElementById('spStatusMsg').textContent='We haven\'t confirmed your payment yet. Click below to check again or try refreshing.';
      document.getElementById('spStatusBtn').textContent='Check Again';
      document.getElementById('spStatusBtn').style.display='';
      document.getElementById('spStatusBtn').onclick=function(){location.reload();};
      return;
    }
    setTimeout(async function(){
      try{
        var s=await BMAuth.getSession();
        var t=s?s.session.access_token:'';
      var r=await fetch(API_BASE + '/api/subscription-status.php',{headers:{'Authorization':'Bearer '+t}});
        if(r.ok){
          var d=await r.json();
          if(d.subscriptions&&d.subscriptions.length>0){
            var sub=d.subscriptions[0];
            if(sub.plan === 'copytrading' || sub.plan === 'copy_trading'){
              window.location.href='copy_trading.php?payment_success=1';
              return;
            }
            if(sub.plan.startsWith('elite_')){
              window.location.href='bm_elites.php?payment_success=1';
              return;
            }
            var m=PLANS[sub.plan]||{name:sub.plan};
            document.getElementById('spRing').style.display='none';
            document.getElementById('spStatusIcon').className='sp-status__icon ok';
            document.getElementById('spStatusIcon').style.display='';
            document.getElementById('spStatusIcon').textContent='\u2713';
            document.getElementById('spStatusTitle').textContent='Payment confirmed!';
            document.getElementById('spStatusMsg').textContent=m.name+' is active until '+new Date(sub.expires_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})+'. You can now access the dashboard.';
            document.getElementById('spStatusBtn').textContent='Sign In to Dashboard';
            document.getElementById('spStatusBtn').style.display='';
            document.getElementById('spStatusBtn').onclick=function(){window.location.href='login.php';};
            return;
          }
        }
      }catch(e){}
      spPoll(n-1);
    },5000);
  }
})();
</script>
<script src="js/motion.js" defer></script>
<script src="js/ai-assistant.js" defer></script>
<?php $activeTab = 'more'; include __DIR__ . '/components/mobile-tabbar.php'; ?>
</body>
</html>
