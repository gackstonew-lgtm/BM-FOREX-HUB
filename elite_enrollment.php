<?php 
require_once __DIR__ . '/engine_config.php';
$planParam = htmlspecialchars($_GET['plan'] ?? ($_GET['service'] ?? ''));
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
<title>Elite Circle Electronic Enrollment &amp; Terms Acceptance | BM FOREX HUB</title>
<meta name="description" content="Mandatory electronic enrollment and terms acceptance for the BM FOREX HUB Elite Circle.">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<meta name="robots" content="noindex, nofollow, noarchive">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">

<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:#0B0F14;color:#FFFFFF;font-family:'Inter',system-ui,sans-serif;min-height:100vh;display:flex;flex-direction:column;overflow-x:hidden}

/* Topbar */
.enroll-top{display:flex;align-items:center;justify-content:space-between;padding:16px 28px;border-bottom:1px solid #283548;background:rgba(11,15,20,0.95);position:sticky;top:0;z-index:50;backdrop-filter:blur(12px)}
.enroll-logo{display:flex;align-items:center;gap:12px;text-decoration:none;color:#fff;font-weight:700;font-size:0.95rem;font-family:'Space Grotesk',sans-serif}
.enroll-logo img{width:36px;height:36px;border-radius:50%;border:1px solid rgba(22,119,255,0.4)}
.enroll-top-right{display:flex;align-items:center;gap:16px}
.enroll-top-right a{color:#8fa3b8;text-decoration:none;font-size:0.83rem;transition:color .2s}
.enroll-top-right a:hover{color:#1677FF}

/* Shell & Layout */
.enroll-container{max-width:960px;width:100%;margin:0 auto;padding:32px 20px 60px;flex:1}

/* Step Progress Indicator */
.enroll-steps{display:flex;align-items:center;justify-content:center;gap:0;margin:0 auto 32px;max-width:440px}
.enroll-step{display:flex;align-items:center;gap:8px;font-size:0.78rem;color:rgba(255,255,255,0.4);font-weight:500}
.enroll-step.active{color:#1677FF;font-weight:600}
.enroll-step.done{color:#16C784}
.enroll-step__num{width:26px;height:26px;border-radius:50%;border:1.5px solid #283548;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;flex-shrink:0}
.enroll-step.active .enroll-step__num{border-color:#1677FF;background:rgba(22,119,255,0.15);color:#1677FF}
.enroll-step.done .enroll-step__num{border-color:#16C784;background:rgba(22,199,132,0.15);color:#16C784}
.enroll-step__line{width:48px;height:1px;background:#283548;margin:0 12px;flex-shrink:0}
.enroll-step.active+.enroll-step__line{background:rgba(22,119,255,0.4)}

/* Hero Banner */
.enroll-hero{background:linear-gradient(180deg,#151D29 0%,#101722 100%);border:1px solid #283548;border-radius:16px;padding:28px 24px;margin-bottom:28px;position:relative;overflow:hidden}
.enroll-hero::before{content:'';position:absolute;top:0;left:10%;right:10%;height:2px;background:linear-gradient(90deg,transparent,#1677FF,#F0B429,#1677FF,transparent)}
.enroll-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;background:rgba(240,180,41,0.12);border:1px solid rgba(240,180,41,0.3);color:#F0B429;font-size:0.72rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:12px}
.enroll-title{font-family:'Space Grotesk',sans-serif;font-size:1.55rem;font-weight:700;color:#fff;margin-bottom:8px;line-height:1.3}
.enroll-subtitle{color:#8fa3b8;font-size:0.86rem;line-height:1.55;max-width:760px}

/* Card Sections */
.enroll-card{background:#151D29;border:1px solid #283548;border-radius:14px;padding:26px 24px;margin-bottom:24px}
.enroll-card-header{display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(255,255,255,0.06)}
.enroll-card-header h3{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:600;color:#fff;margin:0}
.enroll-card-header .icon-wrap{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.icon-wrap--blue{background:rgba(22,119,255,0.15);color:#1677FF}
.icon-wrap--gold{background:rgba(240,180,41,0.15);color:#F0B429}
.icon-wrap--green{background:rgba(22,199,132,0.15);color:#16C784}

/* Grid & Form Fields */
.enroll-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px}
.enroll-field{display:flex;flex-direction:column;gap:6px}
.enroll-field label{font-size:0.78rem;font-weight:600;color:#8fa3b8;display:flex;align-items:center;gap:4px}
.enroll-field label .req{color:#F6465D}
.enroll-field label .auto-badge{margin-left:auto;font-size:0.65rem;background:rgba(22,199,132,0.12);color:#16C784;padding:2px 8px;border-radius:10px;font-weight:500}
.enroll-field input,.enroll-field select{background:#101722;border:1px solid #283548;color:#fff;border-radius:8px;padding:12px 14px;font-size:0.88rem;outline:none;transition:border-color .2s,box-shadow .2s;font-family:'Inter',sans-serif}
.enroll-field input:focus,.enroll-field select:focus{border-color:#1677FF;box-shadow:0 0 0 3px rgba(22,119,255,0.18)}
.enroll-field input[readonly]{background:#192230;color:#B8C3D1;cursor:not-allowed}
.enroll-hint{font-size:0.7rem;color:#7F8B99;margin-top:2px}

/* Tier Quick-Pills */
.tier-pills{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
.tier-pill{background:#101722;border:1px solid #283548;color:#8fa3b8;border-radius:8px;padding:8px 12px;font-size:0.78rem;cursor:pointer;transition:all .2s;font-weight:500}
.tier-pill:hover{border-color:#1677FF;color:#fff}
.tier-pill.active{border-color:#1677FF;background:rgba(22,119,255,0.15);color:#1677FF;font-weight:700}

/* Cycle Notice Box */
.cycle-box{background:rgba(22,119,255,0.06);border:1px solid rgba(22,119,255,0.22);border-radius:10px;padding:16px;margin-top:16px;display:flex;align-items:flex-start;gap:12px}
.cycle-box svg{width:20px;height:20px;color:#1677FF;flex-shrink:0;margin-top:2px}
.cycle-box-text{font-size:0.8rem;color:#B8C3D1;line-height:1.5}
.cycle-box-text strong{color:#fff}

/* Terms Document Viewer */
.terms-viewer{background:#0E141E;border:1px solid #283548;border-radius:10px;padding:24px;max-height:380px;overflow-y:auto;font-size:0.82rem;line-height:1.65;color:#B8C3D1;margin-bottom:20px;border-left:3px solid #1677FF}
.terms-viewer::-webkit-scrollbar{width:7px}
.terms-viewer::-webkit-scrollbar-track{background:#0B0F14}
.terms-viewer::-webkit-scrollbar-thumb{background:#283548;border-radius:4px}
.terms-viewer::-webkit-scrollbar-thumb:hover{background:#1677FF}
.terms-header{padding-bottom:16px;margin-bottom:18px;border-bottom:1px solid #1E2D42}
.terms-header h4{font-family:'Space Grotesk',sans-serif;color:#fff;font-size:1.15rem;margin:0 0 6px}
.terms-meta{font-size:0.72rem;color:#7F8B99;display:flex;gap:16px;flex-wrap:wrap;font-family:'IBM Plex Mono',monospace}
.terms-section{margin-bottom:18px}
.terms-section h5{font-size:0.88rem;color:#F0B429;margin:0 0 6px;font-family:'Space Grotesk',sans-serif}
.terms-section p{margin:0 0 8px}

/* Mandatory Acceptance Declaration */
.accept-card{background:linear-gradient(180deg,#17212F 0%,#101722 100%);border:1px solid rgba(22,119,255,0.35);border-radius:14px;padding:24px;margin-bottom:28px}
.legal-statement{background:#0E141E;border:1px solid rgba(255,255,255,0.08);border-radius:8px;padding:16px;font-size:0.82rem;line-height:1.6;color:#D8E2ED;margin-bottom:18px}
.accept-check-wrap{display:flex;align-items:flex-start;gap:12px;cursor:pointer;user-select:none;padding:12px;background:rgba(22,119,255,0.06);border:1px solid rgba(22,119,255,0.2);border-radius:8px;transition:all .2s}
.accept-check-wrap:hover{border-color:#1677FF;background:rgba(22,119,255,0.1)}
.accept-check-wrap input[type="checkbox"]{width:20px;height:20px;margin-top:2px;cursor:pointer;accent-color:#1677FF}
.accept-check-wrap label{font-size:0.88rem;font-weight:700;color:#fff;cursor:pointer}

/* Action Controls */
.enroll-actions{display:flex;flex-direction:column;gap:12px;align-items:center}
.btn-submit-enroll{width:100%;max-width:540px;padding:16px 28px;border:none;border-radius:10px;background:linear-gradient(135deg,#1677FF 0%,#0D47A1 100%);color:#fff;font-family:'Space Grotesk',sans-serif;font-size:1rem;font-weight:700;cursor:pointer;transition:all .25s ease;display:flex;align-items:center;justify-content:center;gap:10px;box-shadow:0 8px 24px rgba(22,119,255,0.3)}
.btn-submit-enroll:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 12px 32px rgba(22,119,255,0.45)}
.btn-submit-enroll:disabled{opacity:0.45;cursor:not-allowed;box-shadow:none}

/* Alerts */
.enroll-alert{padding:14px 18px;border-radius:10px;font-size:0.84rem;display:none;margin-bottom:20px;align-items:center;gap:10px}
.enroll-alert.show{display:flex}
.enroll-alert.error{background:rgba(246,70,93,0.12);border:1px solid rgba(246,70,93,0.35);color:#F6465D}
.enroll-alert.success{background:rgba(22,199,132,0.12);border:1px solid rgba(22,199,132,0.35);color:#16C784}

/* Success Confirmation Overlay */
.confirmation-overlay{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(11,15,20,0.92);backdrop-filter:blur(10px);z-index:999;align-items:center;justify-content:center;padding:20px}
.confirmation-overlay.show{display:flex}
.confirmation-card{background:#151D29;border:1px solid #1677FF;border-radius:18px;padding:36px 30px;max-width:520px;width:100%;text-align:center;box-shadow:0 24px 60px rgba(0,0,0,0.6),0 0 40px rgba(22,119,255,0.25);animation:fadeInScale 0.3s ease-out}
@keyframes fadeInScale{from{opacity:0;transform:scale(0.92)}to{opacity:1;transform:scale(1)}}
.confirmation-icon{width:64px;height:64px;border-radius:50%;background:rgba(22,199,132,0.15);color:#16C784;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 18px;border:2px solid rgba(22,199,132,0.4)}
.confirmation-title{font-family:'Space Grotesk',sans-serif;font-size:1.45rem;font-weight:700;color:#fff;margin-bottom:8px}
.confirmation-desc{color:#8fa3b8;font-size:0.88rem;line-height:1.5;margin-bottom:24px}
.confirmation-meta{background:#101722;border:1px solid #1E2D42;border-radius:10px;padding:16px;font-size:0.8rem;text-align:left;color:#B8C3D1;margin-bottom:24px}
.confirmation-meta-row{display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid rgba(255,255,255,0.04)}
.confirmation-meta-row:last-child{border-bottom:none}
.confirmation-meta-row span{color:#7F8B99}
.confirmation-meta-row strong{color:#fff}
</style>
</head>
<body>

<!-- Header -->
<header class="enroll-top">
  <a href="bm_elites.php" class="enroll-logo">
    <img src="BM-ForexHub-Logo-Circle.png" alt="BM Forex Hub">
    <span>BM FOREX HUB</span>
  </a>
  <div class="enroll-top-right">
    <button type="button" class="theme-toggle" title="Toggle theme" aria-label="Toggle theme">
      <svg class="theme-toggle__sun" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
      <svg class="theme-toggle__moon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    </button>
    <a href="bm_elites.php">&larr; Back to BM Elites</a>
  </div>
</header>

<main class="enroll-container">

  <!-- Step Indicator -->
  <div class="enroll-steps">
    <div class="enroll-step active">
      <div class="enroll-step__num">1</div>
      <span>Terms &amp; Enrollment</span>
    </div>
    <div class="enroll-step__line"></div>
    <div class="enroll-step">
      <div class="enroll-step__num">2</div>
      <span>Subscription &amp; Payment</span>
    </div>
  </div>

  <!-- Hero Header -->
  <div class="enroll-hero">
    <div class="enroll-badge">👑 BM ELITES TRADING CIRCLE</div>
    <h1 class="enroll-title">Electronic Enrollment &amp; Terms Acceptance</h1>
    <p class="enroll-subtitle">
      Welcome to the BM FOREX HUB Elite Circle. In compliance with applicable regulations, all prospective members must review the governing Terms and Conditions, verify their registration details, and execute electronic acceptance before continuing to payment.
    </p>
  </div>

  <!-- Alerts -->
  <div class="enroll-alert error" id="enrollAlertError"></div>
  <div class="enroll-alert success" id="enrollAlertSuccess"></div>

  <form id="enrollForm" novalidate>

    <!-- SECTION 1: MEMBER DETAILS -->
    <div class="enroll-card">
      <div class="enroll-card-header">
        <div class="icon-wrap icon-wrap--blue">&#128100;</div>
        <div>
          <h3>Member Identification Details</h3>
          <p style="font-size:0.75rem;color:#7F8B99;margin:2px 0 0">Verified from your authenticated account session</p>
        </div>
      </div>

      <div class="enroll-grid">
        <div class="enroll-field">
          <label for="memberName">Full Legal Name <span class="req">*</span> <span class="auto-badge">Auto-Populated</span></label>
          <input type="text" id="memberName" name="member_name" required placeholder="Your full name as registered">
          <span class="enroll-hint">Authoritative account name</span>
        </div>

        <div class="enroll-field">
          <label for="idPassportNumber">National ID / Passport Number <span class="req">*</span></label>
          <input type="text" id="idPassportNumber" name="id_passport_number" required placeholder="e.g. 12345678 or A01234567">
          <span class="enroll-hint">Required for electronic signature record</span>
        </div>

        <div class="enroll-field">
          <label for="memberEmail">Email Address <span class="req">*</span> <span class="auto-badge">Auto-Populated</span></label>
          <input type="email" id="memberEmail" name="email" required readonly placeholder="user@example.com">
          <span class="enroll-hint">Bound to your authenticated login</span>
        </div>

        <div class="enroll-field">
          <label for="memberPhone">Phone Number <span class="req">*</span> <span class="auto-badge">Auto-Populated</span></label>
          <input type="tel" id="memberPhone" name="phone" required placeholder="e.g. +254712345678">
          <span class="enroll-hint">Used for official communications</span>
        </div>

        <div class="enroll-field" style="grid-column: 1 / -1;">
          <label for="memberCountry">Country of Residence <span class="req">*</span></label>
          <input type="text" id="memberCountry" name="country" required placeholder="e.g. Kenya, United Kingdom, United States">
          <span class="enroll-hint">Primary tax and jurisdiction residency</span>
        </div>
      </div>
    </div>

    <!-- SECTION 2: INVESTMENT DETAILS -->
    <div class="enroll-card">
      <div class="enroll-card-header">
        <div class="icon-wrap icon-wrap--gold">&#128178;</div>
        <div>
          <h3>Investment &amp; Trading Cycle Allocation</h3>
          <p style="font-size:0.75rem;color:#7F8B99;margin:2px 0 0">Select your intended investment package and review cycle timelines</p>
        </div>
      </div>

      <div class="enroll-grid">
        <div class="enroll-field">
          <label for="investmentAmount">Intended Investment Amount <span class="req">*</span></label>
          <input type="number" id="investmentAmount" name="investment_amount" min="100" step="100" required placeholder="e.g. 1000">
          
          <div class="tier-pills" id="tierPills">
            <button type="button" class="tier-pill" data-amt="1000" data-plan="elite_starter">$1,000 (Starter)</button>
            <button type="button" class="tier-pill" data-amt="2000" data-plan="elite_intermediate">$2,000 (Intermediate)</button>
            <button type="button" class="tier-pill" data-amt="3000" data-plan="elite_advanced">$3,000 (Advanced)</button>
            <button type="button" class="tier-pill" data-amt="5000" data-plan="elite_professional">$5,000 (Professional)</button>
            <button type="button" class="tier-pill" data-amt="6000" data-plan="elite_premium">$6,000 (Premium)</button>
            <button type="button" class="tier-pill" data-amt="10000" data-plan="elite_elite">$10,000 (VIP Elite)</button>
          </div>
        </div>

        <div class="enroll-field">
          <label for="currencySelect">Currency <span class="req">*</span></label>
          <select id="currencySelect" name="currency" required>
            <option value="USD" selected>USD — United States Dollar</option>
            <option value="KES">KES — Kenyan Shilling</option>
            <option value="EUR">EUR — Euro</option>
            <option value="GBP">GBP — British Pound</option>
            <option value="USDT">USDT — Tether</option>
          </select>
          <span class="enroll-hint">Settlement and accounting denomination</span>
        </div>

        <div class="enroll-field">
          <label for="startDate">Account / Cycle Commencement Date <span class="req">*</span></label>
          <input type="date" id="startDate" name="investment_start_date" required value="<?= date('Y-m-d') ?>">
          <span class="enroll-hint">Effective enrollment initiation date</span>
        </div>

        <div class="enroll-field">
          <label for="completionDateDisplay">Expected Cycle Completion Date</label>
          <input type="text" id="completionDateDisplay" readonly value="<?= date('Y-m-d', strtotime('+4 months')) ?>" style="font-family:'IBM Plex Mono',monospace;color:#16C784;">
          <span class="enroll-hint">Server-computed 4-month complete trading cycle</span>
        </div>
      </div>

      <div class="cycle-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <div class="cycle-box-text">
          <strong>4-Month Managed Investment Cycle Notice:</strong>
          In accordance with Section 5 of the Terms &amp; Conditions, the Elite Circle operates on a fixed four (4) month investment cycle. Invested capital is actively managed and early withdrawals are generally unavailable until completion of the full cycle. Returns are performance-based and not guaranteed.
        </div>
      </div>
    </div>

    <!-- SECTION 3: TERMS & CONDITIONS PRESENTATION -->
    <div class="enroll-card">
      <div class="enroll-card-header">
        <div class="icon-wrap icon-wrap--green">&#128220;</div>
        <div>
          <h3>BM FOREX HUB Elite Circle Terms &amp; Conditions</h3>
          <p style="font-size:0.75rem;color:#7F8B99;margin:2px 0 0">Please read the complete governing agreement before accepting</p>
        </div>
      </div>

      <!-- In-App Document Viewer -->
      <div class="terms-viewer" id="termsViewer">
        <div class="terms-header">
          <h4>BM FOREX HUB — ELITE CIRCLE TERMS AND CONDITIONS</h4>
          <div class="terms-meta">
            <span><strong>Effective Date:</strong> 05 January 2026</span>
            <span><strong>Version:</strong> 1.0</span>
            <span><strong>Operating Entity:</strong> Varban Company Limited</span>
            <span><strong>Associated:</strong> Stillrock Ventures</span>
            <span><strong>Jurisdiction:</strong> Republic of Kenya</span>
          </div>
        </div>

        <div class="terms-section">
          <h5>IMPORTANT NOTICE</h5>
          <p>Please read these Terms and Conditions carefully before enrolling. By completing an application, clicking an acceptance button, accepting the Terms electronically, making a payment, submitting an enrollment form, or otherwise proceeding with the Elite Circle program, the Member confirms that they have read, understood and accepted these Terms and Conditions. If the Member does not agree, they should not proceed with enrollment or payment.</p>
        </div>

        <div class="terms-section">
          <h5>1. DEFINITIONS</h5>
          <p><strong>Company</strong> means Varban Company Limited and, where applicable, related entities involved in administering BM FOREX HUB.<br>
          <strong>BM FOREX HUB</strong> means the trading, education, technology, marketing and/or financial-services brand operated by or on behalf of the Company.<br>
          <strong>Parent Company</strong> means Stillrock Ventures, where applicable.<br>
          <strong>Elite Circle</strong> means the program offered under the BM FOREX HUB brand.<br>
          <strong>Member</strong> means the person whose application is accepted.<br>
          <strong>Investment Amount</strong> means funds actually received and accepted by the Company.<br>
          <strong>Trading Cycle</strong> means the applicable investment period and, unless otherwise stated, four (4) months.<br>
          <strong>Compounding</strong> means retention/reinvestment of applicable returns or commissions.<br>
          <strong>Commission/Return/Profit</strong> means an amount recorded or calculated under the applicable arrangement and is not a guarantee.</p>
        </div>

        <div class="terms-section">
          <h5>2. PARTIES AND CORPORATE STRUCTURE</h5>
          <p>The Elite Circle may be marketed, administered, supported or operated through BM FOREX HUB and/or entities within the relevant corporate structure. The legal entity responsible for a transaction shall be identified in the relevant documentation. Where applicable, Varban Company Limited shall be the contracting or operating entity. Stillrock Ventures may act as a parent, holding, administrative, strategic or associated entity where applicable. Association with BM FOREX HUB does not automatically impose liability on shareholders, directors, employees, consultants, contractors or agents.</p>
        </div>

        <div class="terms-section">
          <h5>3. ELIGIBILITY</h5>
          <p>The Member must provide accurate and complete information. The Company may request identity, address, source-of-funds, payment, tax and compliance information reasonably necessary for administration or legal compliance. The Company may decline, suspend or terminate an application where information is incomplete, inaccurate, misleading or unverifiable.</p>
        </div>

        <div class="terms-section">
          <h5>4. ACCEPTANCE OF TERMS</h5>
          <p>Acceptance may occur electronically through an acceptance box, button, online application, electronic signature, email/message confirmation, payment after presentation of these Terms, or proceeding after access to the Terms. The Company may retain timestamps, application records, system logs, emails, messages, transaction records and other acceptance evidence, subject to applicable law.</p>
        </div>

        <div class="terms-section">
          <h5>5. INVESTMENT / TRADING CYCLE</h5>
          <p>Unless otherwise expressly stated in account documentation, the Elite Circle operates on a four (4) month complete trading/investment cycle. The cycle commences on the applicable date recorded by the Company. The commencement and completion dates may be communicated through account records, confirmation notices, payment records or other official communications. The investment cycle is a material condition of participation. Early withdrawal of invested capital is not permitted during the active cycle unless expressly permitted by the applicable agreement or approved by the Company in writing. Discontinuing compounding does not automatically terminate the cycle or create an entitlement to immediate withdrawal. Completion of the cycle does not constitute an automatic payment instruction; applicable verification and withdrawal procedures may still apply.</p>
        </div>

        <div class="terms-section">
          <h5>6. NO GUARANTEE OF RETURNS</h5>
          <p>Trading and investment involve financial risk. Past performance does not guarantee future performance. No employee, representative, marketer, affiliate or agent may guarantee a particular profit unless expressly authorized and legally permissible. Historical, illustrative, projected or estimated returns are not guarantees. Market conditions may result in profits, reduced returns or losses.</p>
        </div>

        <div class="terms-section">
          <h5>7. TRADING AND MANAGEMENT</h5>
          <p>Trading decisions may use strategies, systems, technology, algorithms or discretionary decisions determined by the Company or appointed managers. The Company does not guarantee that every trade will be profitable and may modify strategies, instruments, brokers, platforms, technology or risk procedures where reasonably necessary. Trading may be temporarily suspended due to market, liquidity, technology, broker, regulatory or operational circumstances.</p>
        </div>

        <div class="terms-section">
          <h5>8. COMPOUNDING</h5>
          <p>Where applicable, qualifying amounts may be retained and reinvested rather than distributed periodically. Compounding does not guarantee a compounded return. A Member may request discontinuation of compounding in accordance with the account procedure, but this does not alter the minimum investment cycle unless expressly agreed in writing.</p>
        </div>

        <div class="terms-section">
          <h5>9. COMMISSIONS AND DISTRIBUTIONS</h5>
          <p>Commissions/distributions are calculated and processed according to the applicable arrangement. A pending, projected or estimated amount is not necessarily a presently payable debt. Final amounts are determined from official records following reconciliation. Payments may be delayed by banking systems, payment processors, technical problems, compliance reviews, holidays, network interruptions, incorrect client information or other circumstances outside reasonable Company control.</p>
        </div>

        <div class="terms-section">
          <h5>10. WITHDRAWALS</h5>
          <p>Withdrawals are subject to the applicable investment cycle and these Terms. Unless expressly permitted otherwise, invested capital cannot be withdrawn before completion of the four-month cycle. Withdrawal requests may require identity/account verification, payment confirmation, compliance checks, reconciliation, applicable fees and an approved withdrawal channel. The Company may delay processing where reasonably necessary to verify a request.</p>
        </div>

        <div class="terms-section">
          <h5>11. PAYMENT CHANNELS</h5>
          <p>The Company may use banks, payment processors, mobile-money providers, cryptocurrency networks or other third-party services. Third-party channels are outside the Company's direct control and may experience interruptions, restrictions, reviews, congestion or delays.</p>
        </div>

        <div class="terms-section">
          <h5>12. FEES AND CHARGES</h5>
          <p>Applicable fees shall be disclosed through the enrollment, invoice, account documentation or official communication. Third-party charges may apply where properly disclosed and applicable.</p>
        </div>

        <div class="terms-section">
          <h5>13. ACCOUNT SUSPENSION</h5>
          <p>The Company may suspend an account for suspected fraud, verification issues, unauthorized activity, breach of Terms, unlawful activity, abusive conduct, system manipulation, fraudulent chargebacks, legal/regulatory requirements, cybersecurity concerns or other material operational risks. Suspension of website or portal access does not necessarily terminate the underlying contractual relationship.</p>
        </div>

        <div class="terms-section">
          <h5>14. TERMINATION</h5>
          <p>The Company may terminate or suspend participation for material breach or where continuation creates legal, regulatory, financial, security or operational risk. A Member's termination request remains subject to the applicable investment cycle and withdrawal restrictions. Accrued rights and obligations survive termination where applicable.</p>
        </div>

        <div class="terms-section">
          <h5>15. MEMBER REPRESENTATIONS</h5>
          <p>The Member represents that information supplied is accurate; they have capacity to contract; understand financial risk; use lawfully obtained funds; are not relying on unauthorized promises; had reasonable opportunity to review the Terms; and may obtain independent professional advice.</p>
        </div>

        <div class="terms-section">
          <h5>16. COMPANY PERSONNEL AND REPRESENTATIVES</h5>
          <p>Employees, consultants, agents, marketers and representatives must act within authorized roles. No employee or representative may personally guarantee returns, authorize unauthorized withdrawals, alter contractual terms or make binding commitments unless expressly authorized. Unauthorized representations do not bind the Company subject to applicable law. Threats, harassment, intimidation, abusive or discriminatory conduct may result in appropriate account or communication restrictions, subject to law.</p>
        </div>

        <div class="terms-section">
          <h5>17. PROTECTION OF DIRECTORS, OFFICERS, EMPLOYEES AND AGENTS</h5>
          <p>To the extent permitted by law, directors, officers, employees, consultants, contractors, agents and representatives are not personally liable for contractual obligations properly undertaken by the Company. Claims should ordinarily be directed against the contracting entity identified in the documentation.</p>
        </div>

        <div class="terms-section">
          <h5>18. LIMITATION OF LIABILITY</h5>
          <p>To the maximum extent permitted by law, the Company is not liable for indirect, consequential, incidental, special or unforeseeable losses arising from market movements, trading losses, third-party payment failures, banking delays, internet interruptions, platform downtime, force majeure, unauthorized third-party access, telecommunications failures or circumstances outside reasonable control. Nothing excludes liability that cannot legally be excluded.</p>
        </div>

        <div class="terms-section">
          <h5>19. INDEMNITY</h5>
          <p>To the extent permitted by law, the Member indemnifies the Company and its directors, officers, employees, agents and authorized representatives against losses, claims, costs or expenses arising from the Member's breach, fraud/unlawful activity, inaccurate information, unauthorized account use, violation of rights, system misuse or unlawful communications. This does not apply to losses caused by the Company's own unlawful conduct.</p>
        </div>

        <div class="terms-section">
          <h5>20. CONFIDENTIALITY</h5>
          <p>The Member shall keep confidential non-public Company systems, trading methodologies, proprietary strategies, internal processes, account information and other confidential business information, except where disclosure is legally required, needed for professional advice or authorized.</p>
        </div>

        <div class="terms-section">
          <h5>21. INTELLECTUAL PROPERTY</h5>
          <p>BM FOREX HUB names, logos, strategies, training materials, software, content, documentation and proprietary systems remain the property of their respective owners. Participation does not transfer ownership. Unauthorized copying, resale, redistribution, reverse engineering or commercial exploitation is prohibited.</p>
        </div>

        <div class="terms-section">
          <h5>22. DATA PROTECTION AND PRIVACY</h5>
          <p>The Company may process personal information reasonably required for administration, verification, payment processing, compliance, security, customer support and legitimate business purposes. Personal data shall be handled in accordance with applicable Kenyan data-protection law and may be shared with authorized processors, service providers, advisers or regulators where legally permitted or required.</p>
        </div>

        <div class="terms-section">
          <h5>23. ELECTRONIC COMMUNICATIONS AND RECORDS</h5>
          <p>The Member consents to electronic account communications where legally permissible. Emails, electronic notices, platform records, transaction records and other electronic communications may constitute business records. The Company may retain evidence of enrollment, acceptance, payments, communications and account activity.</p>
        </div>

        <div class="terms-section">
          <h5>24. COMPLAINTS AND DISPUTES</h5>
          <p>Members should submit complaints in writing through the designated support channel with sufficient information for investigation. The Company shall review complaints in accordance with internal procedures and applicable law. Nothing restricts statutory or regulatory rights.</p>
        </div>

        <div class="terms-section">
          <h5>25. GOVERNING LAW</h5>
          <p>These Terms are governed by the laws of the Republic of Kenya unless mandatory law requires otherwise. Disputes shall be addressed through applicable dispute-resolution mechanisms and courts or regulators having lawful jurisdiction.</p>
        </div>

        <div class="terms-section">
          <h5>26. FORCE MAJEURE</h5>
          <p>The Company is not responsible for delay or failure caused by circumstances beyond reasonable control, including natural disasters, war, civil unrest, government action, regulatory intervention, cyberattacks, telecommunications failures, internet outages, banking failures, payment-provider failures, broker failures, market closures or extraordinary market events.</p>
        </div>

        <div class="terms-section">
          <h5>27. AMENDMENTS</h5>
          <p>The Company may update these Terms where reasonably necessary due to changes in law, regulation, technology, operations, security or risk management. Material changes shall be communicated where required by law. Mandatory legal rights are not retrospectively removed.</p>
        </div>

        <div class="terms-section">
          <h5>28. SEVERABILITY</h5>
          <p>If any provision is invalid, unlawful or unenforceable, it shall be modified or severed to the minimum extent necessary and the remaining provisions continue to the extent legally permissible.</p>
        </div>

        <div class="terms-section">
          <h5>29. NO WAIVER</h5>
          <p>Failure to immediately enforce a provision does not constitute a waiver or prevent later enforcement.</p>
        </div>

        <div class="terms-section">
          <h5>30. ENTIRE AGREEMENT</h5>
          <p>These Terms together with applicable enrollment documentation, account confirmation, schedules and incorporated documents form the contractual framework. No oral statement by an unauthorized person amends these Terms. Conflicts are resolved according to the applicable written agreement and mandatory law.</p>
        </div>

        <div class="terms-section">
          <h5>31. NO ASSIGNMENT</h5>
          <p>The Member may not transfer, assign, sell, pledge or dispose of the account or contractual rights without prior written approval, except where prohibited by law. The Company may assign or transfer rights and obligations as permitted by law.</p>
        </div>

        <div class="terms-section">
          <h5>32. ACCOUNT SECURITY</h5>
          <p>The Member is responsible for passwords and credentials and must immediately notify the Company of suspected unauthorized access. The Company is not responsible for losses caused by failure to protect credentials except where liability cannot lawfully be excluded.</p>
        </div>

        <div class="terms-section">
          <h5>33. PROHIBITED CONDUCT</h5>
          <p>The Member shall not provide false information, manipulate systems, attempt unauthorized access, submit fraudulent payment disputes, impersonate personnel, misuse intellectual property, threaten or harass employees, knowingly publish false information, interfere with systems or use the Elite Circle unlawfully. Nothing restricts lawful complaints, regulatory reporting, legal proceedings or truthful statements supported by evidence.</p>
        </div>

        <div class="terms-section">
          <h5>34. NO RELIANCE ON UNAUTHORIZED REPRESENTATIONS</h5>
          <p>Only authorized Company representatives may make binding representations. Marketing statements, social-media posts, educational content, historical examples and informal communications do not amend the governing agreement unless expressly incorporated.</p>
        </div>

        <div class="terms-section">
          <h5>35. RISK ACKNOWLEDGMENT</h5>
          <p>The Member acknowledges market volatility, possible losses, non-guaranteed historical performance, payment delays, possible strategy failure, changing market conditions and financial risk.</p>
        </div>

        <div class="terms-section">
          <h5>36. REGULATORY STATUS AND COMPLIANCE</h5>
          <p>The Company shall operate the Elite Circle only to the extent permitted by applicable law and regulatory requirements. Nothing grants an authorization the Company does not legally possess. Where an activity requires licensing, approval, registration or authorization, applicable requirements must be satisfied before undertaking the regulated activity.</p>
        </div>

        <div class="terms-section">
          <h5>37. IMPORTANT MEMBER ACKNOWLEDGMENT</h5>
          <p><strong>I HAVE READ AND UNDERSTOOD THESE TERMS AND CONDITIONS. I UNDERSTAND THAT THE ELITE CIRCLE HAS A FOUR-MONTH INVESTMENT/TRADING CYCLE, SUBJECT TO THE SPECIFIC TERMS APPLICABLE TO MY ACCOUNT. I UNDERSTAND THAT EARLY WITHDRAWAL OF INVESTED CAPITAL MAY NOT BE AVAILABLE DURING THE ACTIVE CYCLE. I UNDERSTAND THAT TRADING INVOLVES FINANCIAL RISK AND THAT RETURNS ARE NOT GUARANTEED. I CONFIRM THAT THE INFORMATION I PROVIDED IS ACCURATE. I ACCEPT THESE TERMS VOLUNTARILY AND HAVE HAD THE OPPORTUNITY TO SEEK INDEPENDENT PROFESSIONAL ADVICE BEFORE PARTICIPATING.</strong></p>
        </div>

        <div class="terms-section">
          <h5>38. ELECTRONIC ACCEPTANCE</h5>
          <p>Member Name: __________________________________________<br>
          Identification / Account Number: __________________________<br>
          Investment Amount: ______________________________________<br>
          Account / Cycle Commencement Date: _______________________<br>
          Expected Cycle Completion Date: ___________________________<br>
          Member Email: ___________________________________________<br>
          Member Phone: ___________________________________________<br>
          Date of Acceptance: ______________________________________<br>
          Electronic Acceptance / Signature: _________________________</p>
        </div>

        <div class="terms-section">
          <h5>39. COMPANY DETAILS</h5>
          <p><strong>Trading/Business Brand:</strong> BM FOREX HUB<br>
          <strong>Operating Entity:</strong> Varban Company Limited<br>
          <strong>Parent / Associated Company:</strong> Stillrock Ventures<br>
          <strong>Official Support Contact:</strong> +254 785 618608<br>
          <strong>Official Website:</strong> bmforexhub.exchange<br>
          <strong>Registered Office:</strong> Nairobi, Kenya<br>
          <strong>Effective Date:</strong> 05 January 2026 | <strong>Version:</strong> 1.0</p>
        </div>
      </div>
    </div>

    <!-- SECTION 4: MANDATORY ACCEPTANCE CHECKBOX -->
    <div class="accept-card">
      <div class="legal-statement">
        "I confirm that I have read, understood and agree to the BM FOREX HUB Elite Circle Terms and Conditions effective 05 January 2026. I understand and accept that the Elite Circle operates on a four (4) month investment/trading cycle and that invested funds are subject to the withdrawal conditions stated in the Terms and Conditions. I understand that trading involves financial risk and that returns are not guaranteed. I confirm that the information provided above is accurate and complete, and I agree to be bound by the applicable Terms and Conditions to the extent permitted by law."
      </div>

      <div class="accept-check-wrap" id="acceptCheckWrap">
        <input type="checkbox" id="acceptTermsCheckbox" name="accepted" value="1">
        <label for="acceptTermsCheckbox">I ACCEPT THE TERMS &amp; CONDITIONS</label>
      </div>
    </div>

    <!-- Form Submit Button -->
    <div class="enroll-actions">
      <button type="submit" class="btn-submit-enroll" id="btnSubmitEnroll" disabled>
        <span>Submit Electronic Enrollment &amp; Proceed</span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
      <p style="font-size:0.75rem;color:#7F8B99;text-align:center;">
        Upon submission, an immutable audit timestamp and confirmation record will be generated.
      </p>
    </div>

  </form>

</main>

<!-- Success Confirmation Modal Overlay -->
<div class="confirmation-overlay" id="confirmationModal">
  <div class="confirmation-card">
    <div class="confirmation-icon">&#10003;</div>
    <h2 class="confirmation-title">Elite Circle Enrollment Completed</h2>
    <p class="confirmation-desc">Your Terms &amp; Conditions electronic acceptance has been securely verified and committed to the system ledger.</p>

    <div class="confirmation-meta">
      <div class="confirmation-meta-row">
        <span>Terms Version:</span>
        <strong id="confTermsVersion">1.0</strong>
      </div>
      <div class="confirmation-meta-row">
        <span>Effective Date:</span>
        <strong id="confEffectiveDate">05 January 2026</strong>
      </div>
      <div class="confirmation-meta-row">
        <span>Acceptance Date:</span>
        <strong id="confAcceptDate">--</strong>
      </div>
      <div class="confirmation-meta-row">
        <span>Acceptance Time:</span>
        <strong id="confAcceptTime">--</strong>
      </div>
      <div class="confirmation-meta-row">
        <span>Record ID:</span>
        <strong id="confRecordId" style="font-family:monospace;font-size:0.75rem;">--</strong>
      </div>
    </div>

    <button type="button" class="btn-submit-enroll" id="btnContinueToPayment" style="max-width:100%;">
      Continue to Subscription Page &rarr;
    </button>
  </div>
</div>

<!-- Footer -->
<footer style="margin-top:40px;padding:20px 24px;border-top:1px solid #1e2d42;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;font-size:0.77rem;color:#5b6475;">
  <div>&copy; <?= date('Y') ?> BM Forex Hub. Operating Entity: Varban Company Limited.</div>
  <div style="display:flex;gap:16px;flex-wrap:wrap;">
    <a href="terms.php" style="color:#5b6475;text-decoration:none;">Terms of Use</a>
    <span style="color:#1e2d42;">|</span>
    <a href="terms-elite.php" style="color:#5b6475;text-decoration:none;">Elite Circle Terms</a>
    <span style="color:#1e2d42;">|</span>
    <a href="privacy.php" style="color:#5b6475;text-decoration:none;">Privacy Policy</a>
    <span style="color:#1e2d42;">|</span>
    <a href="risk-disclosure.php" style="color:#5b6475;text-decoration:none;">Risk Disclaimer</a>
    <span style="color:#1e2d42;">|</span>
    <a href="contact.php" style="color:#5b6475;text-decoration:none;">Support</a>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script>
(async function() {
  'use strict';

  const form          = document.getElementById('enrollForm');
  const alertErr      = document.getElementById('enrollAlertError');
  const alertSucc     = document.getElementById('enrollAlertSuccess');
  const submitBtn     = document.getElementById('btnSubmitEnroll');
  const acceptCheck   = document.getElementById('acceptTermsCheckbox');
  const checkWrap     = document.getElementById('acceptCheckWrap');
  const nameInput     = document.getElementById('memberName');
  const idInput       = document.getElementById('idPassportNumber');
  const emailInput    = document.getElementById('memberEmail');
  const phoneInput    = document.getElementById('memberPhone');
  const countryInput  = document.getElementById('memberCountry');
  const amountInput   = document.getElementById('investmentAmount');
  const currencyInput = document.getElementById('currencySelect');
  const startDateInput= document.getElementById('startDate');
  const complDateInput= document.getElementById('completionDateDisplay');
  const tierPills     = document.getElementById('tierPills');
  const modalOverlay  = document.getElementById('confirmationModal');
  const continueBtn   = document.getElementById('btnContinueToPayment');

  let currentAuthToken = '';
  let selectedPlanKey  = '<?= $planParam ?>';
  let redirectTargetUrl= 'subscribe.php' + (selectedPlanKey ? '?plan=' + encodeURIComponent(selectedPlanKey) : '?service=elite');

  function showError(msg) {
    alertErr.textContent = msg;
    alertErr.classList.add('show');
    alertSucc.classList.remove('show');
    alertErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function hideAlerts() {
    alertErr.classList.remove('show');
    alertSucc.classList.remove('show');
  }

  // Update completion date dynamically based on start date (+4 months)
  function recalculateCycleCompletion(startDateStr) {
    if (!startDateStr) return;
    try {
      const d = new Date(startDateStr);
      if (!isNaN(d.getTime())) {
        d.setMonth(d.getMonth() + 4);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        complDateInput.value = `${yyyy}-${mm}-${dd}`;
      }
    } catch(e) {}
  }

  startDateInput.addEventListener('change', function() {
    recalculateCycleCompletion(this.value);
  });

  // Tier pills selection handler
  const planTierMap = {
    '1000': 'elite_starter',
    '2000': 'elite_intermediate',
    '3000': 'elite_advanced',
    '5000': 'elite_professional',
    '6000': 'elite_premium',
    '10000': 'elite_elite'
  };

  const planAmountMap = {
    'elite_starter': 1000,
    'elite_intermediate': 2000,
    'elite_advanced': 3000,
    'elite_professional': 5000,
    'elite_premium': 6000,
    'elite_elite': 10000
  };

  if (tierPills) {
    tierPills.querySelectorAll('.tier-pill').forEach(btn => {
      btn.addEventListener('click', function() {
        tierPills.querySelectorAll('.tier-pill').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        amountInput.value = this.dataset.amt;
        selectedPlanKey = this.dataset.plan || planTierMap[this.dataset.amt] || '';
        validateFormState();
      });
    });
  }

  amountInput.addEventListener('input', function() {
    const val = this.value.trim();
    if (tierPills) {
      tierPills.querySelectorAll('.tier-pill').forEach(b => {
        b.classList.toggle('active', b.dataset.amt === val);
      });
    }
    selectedPlanKey = planTierMap[val] || selectedPlanKey;
    validateFormState();
  });

  // Validation validator
  function validateFormState() {
    const isNameOk    = nameInput.value.trim().length >= 2;
    const isIdOk      = idInput.value.trim().length >= 3;
    const isEmailOk   = emailInput.value.trim().includes('@');
    const isPhoneOk   = phoneInput.value.trim().length >= 5;
    const isCountryOk = countryInput.value.trim().length >= 2;
    const isAmountOk  = parseFloat(amountInput.value) > 0;
    const isAccepted  = acceptCheck.checked;

    submitBtn.disabled = !(isNameOk && isIdOk && isEmailOk && isPhoneOk && isCountryOk && isAmountOk && isAccepted);
  }

  [nameInput, idInput, phoneInput, countryInput, amountInput, currencyInput, startDateInput].forEach(el => {
    el.addEventListener('input', validateFormState);
    el.addEventListener('change', validateFormState);
  });

  acceptCheck.addEventListener('change', validateFormState);

  // URL and mode state
  const urlParams       = new URLSearchParams(window.location.search);
  let isComplianceMode  = urlParams.get('mode') === 'compliance';
  let isExistingMember  = isComplianceMode;
  let existingSubRef    = '';

  // Initialize Auth & Data Population
  try {
    const { user, session } = await BMAuth.getSession();
    if (!user) {
      window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
      return;
    }

    currentAuthToken = session ? session.access_token : '';

    // Check if user has already accepted the current terms version
    const checkResp = await fetch('api/elite-enrollment.php?action=check', {
      headers: { 'Authorization': 'Bearer ' + currentAuthToken }
    });

    if (checkResp.ok) {
      const checkData = await checkResp.json();
      if (checkData.ok && checkData.accepted) {
        // User already has valid acceptance on record
        if (checkData.is_elite || isComplianceMode) {
          window.location.replace('bm_elites.php?compliance_success=1');
        } else {
          const target = 'subscribe.php' + (selectedPlanKey ? '?plan=' + encodeURIComponent(selectedPlanKey) : '?service=elite');
          window.location.replace(target);
        }
        return;
      }
      if (checkData.is_elite) {
        isExistingMember = true;
      }
    }

    // Prefill profile and subscription data from server
    const prefillResp = await fetch('api/elite-enrollment.php?action=prefill', {
      headers: { 'Authorization': 'Bearer ' + currentAuthToken }
    });

    if (prefillResp.ok) {
      const prefillData = await prefillResp.json();
      if (prefillData.ok) {
        if (prefillData.is_elite) {
          isExistingMember = true;
        }

        if (prefillData.profile) {
          const p = prefillData.profile;
          if (p.full_name && !nameInput.value) nameInput.value = p.full_name;
          if (p.email) emailInput.value = p.email;
          if (p.phone && !phoneInput.value) phoneInput.value = p.phone;
          if (p.country && !countryInput.value) countryInput.value = p.country;
        }

        // Prefill existing active subscription if present
        if (prefillData.existing_subscription) {
          const sub = prefillData.existing_subscription;
          existingSubRef = sub.id || '';
          if (sub.amount_usd && parseFloat(sub.amount_usd) > 0) {
            amountInput.value = sub.amount_usd;
          }
          if (sub.starts_at) {
            const cleanDate = sub.starts_at.substring(0, 10);
            if (cleanDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
              startDateInput.value = cleanDate;
              recalculateCycleCompletion(cleanDate);
            }
          }
          if (sub.plan_key || sub.plan) {
            selectedPlanKey = sub.plan_key || sub.plan;
          }
        }
      }
    }

    // Update UI elements if in existing member compliance mode
    if (isExistingMember || isComplianceMode) {
      const heroBadge = document.querySelector('.enroll-badge');
      if (heroBadge) heroBadge.textContent = '👑 EXISTING BM ELITE MEMBER COMPLIANCE';

      const heroTitle = document.querySelector('.enroll-title');
      if (heroTitle) heroTitle.textContent = 'Elite Circle Terms & Conditions Acceptance (v1.0)';

      const heroSubtitle = document.querySelector('.enroll-subtitle');
      if (heroSubtitle) {
        heroSubtitle.textContent = 'As an active BM Elite member, reviewing and accepting the updated Terms & Conditions (Effective 05 January 2026) verifies your compliance and unlocks your active VIP countdown and WhatsApp mentorship group.';
      }

      const stepIndicator = document.querySelector('.enroll-steps');
      if (stepIndicator) {
        stepIndicator.innerHTML = `
          <div class="enroll-step active">
            <div class="enroll-step__num">✓</div>
            <span>Elite Compliance Verification &amp; Terms Acceptance</span>
          </div>
        `;
      }

      submitBtn.innerHTML = '<span>Verify Compliance &amp; Accept Terms &rarr;</span>';
      if (continueBtn) continueBtn.textContent = 'Enter BM Elites Trading Circle \u2192';
    }

    // Preselect plan from URL if present and not already set
    if (!amountInput.value) {
      if (selectedPlanKey && planAmountMap[selectedPlanKey]) {
        const presetAmt = planAmountMap[selectedPlanKey];
        amountInput.value = presetAmt;
        if (tierPills) {
          const pill = tierPills.querySelector(`[data-amt="${presetAmt}"]`);
          if (pill) pill.classList.add('active');
        }
      } else {
        amountInput.value = 1000;
        if (tierPills) {
          const pill = tierPills.querySelector('[data-amt="1000"]');
          if (pill) pill.classList.add('active');
        }
      }
    } else if (tierPills) {
      const pill = tierPills.querySelector(`[data-amt="${amountInput.value}"]`);
      if (pill) pill.classList.add('active');
    }

    validateFormState();
  } catch(e) {
    console.warn('Auth prefill notice:', e);
  }

  // Handle Form Submission
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    hideAlerts();

    if (!acceptCheck.checked) {
      showError('Please check the box to accept the Elite Circle Terms & Conditions.');
      return;
    }

    const payload = {
      action: 'submit',
      member_name: nameInput.value.trim(),
      id_passport_number: idInput.value.trim(),
      email: emailInput.value.trim(),
      phone: phoneInput.value.trim(),
      country: countryInput.value.trim(),
      investment_amount: parseFloat(amountInput.value) || 0,
      currency: currencyInput.value,
      investment_start_date: startDateInput.value,
      payment_reference: existingSubRef || '',
      is_existing_member: isExistingMember,
      mode: isComplianceMode ? 'compliance' : 'standard',
      accepted: true,
      plan: selectedPlanKey || planTierMap[amountInput.value] || ''
    };

    if (!payload.member_name || !payload.id_passport_number || !payload.email || !payload.phone || !payload.country || payload.investment_amount <= 0) {
      showError('Please fill in all required fields marked with an asterisk (*).');
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Processing Legal Enrollment...</span>';

    try {
      const resp = await fetch('api/elite-enrollment.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer ' + currentAuthToken
        },
        body: JSON.stringify(payload)
      });

      const data = await resp.json();

      if (!resp.ok || !data.ok) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = isExistingMember 
          ? '<span>Verify Compliance &amp; Accept Terms &rarr;</span>' 
          : '<span>Submit Electronic Enrollment &amp; Proceed</span>';
        showError((data && data.error) || 'Submission failed. Please check your network and try again.');
        return;
      }

      // Success Display
      document.getElementById('confTermsVersion').textContent = data.terms_version || '1.0';
      document.getElementById('confEffectiveDate').textContent = data.terms_effective_date || '05 January 2026';
      document.getElementById('confAcceptDate').textContent = data.accepted_date || '--';
      document.getElementById('confAcceptTime').textContent = data.accepted_time || '--';
      document.getElementById('confRecordId').textContent = data.enrollment_id || '--';

      if (data.redirect_url) {
        redirectTargetUrl = data.redirect_url;
      } else if (isExistingMember) {
        redirectTargetUrl = 'bm_elites.php?compliance_success=1';
      }

      modalOverlay.classList.add('show');

      // Auto-continue after short delay
      setTimeout(function() {
        window.location.href = redirectTargetUrl;
      }, 2800);

    } catch(err) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = isExistingMember 
        ? '<span>Verify Compliance &amp; Accept Terms &rarr;</span>' 
        : '<span>Submit Electronic Enrollment &amp; Proceed</span>';
      showError('Could not connect to the server. Please check your internet connection.');
    }
  });

  continueBtn.addEventListener('click', function() {
    window.location.href = redirectTargetUrl;
  });

})();
</script>
</body>
</html>
