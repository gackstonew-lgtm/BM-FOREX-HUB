<?php
require_once __DIR__ . '/engine_config.php';
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
<title>BM FOREX HUB Elite Circle Terms &amp; Conditions | Legal Agreement</title>
<meta name="description" content="Official Terms and Conditions for the BM FOREX HUB Elite Circle managed trading program (Version 1.0).">
<meta name="robots" content="index, follow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">
<style>
:root {
  --void: #0B0F14;
  --panel: #101722;
  --panel2: #151D29;
  --line: #283548;
  --ink: #FFFFFF;
  --muted: #B8C3D1;
  --dim: #7F8B99;
  --gold: #1677FF;
  --gold-accent: #F0B429;
  --teal: #16C784;
  --coral: #F6465D;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body { background: var(--void); color: var(--ink); font: 16px/1.7 'Inter', Arial, sans-serif; -webkit-font-smoothing: antialiased; }
a { color: var(--gold); text-decoration: none; }
a:hover { text-decoration: underline; }
::selection { background: var(--gold); color: #fff; }

.shell { width: min(880px, calc(100% - 40px)); margin: auto; }
.topnav { position: sticky; top: 0; z-index: 50; background: rgba(11, 15, 20, 0.94); border-bottom: 1px solid var(--line); backdrop-filter: blur(15px); }
.topnavin { min-height: 64px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
.brand { display: flex; gap: 10px; align-items: center; font-family: "Space Grotesk", sans-serif; font-weight: 700; font-size: 0.95rem; color: #fff; text-decoration: none; }
.brand img { width: 32px; height: 32px; border-radius: 50%; }
.brand em { color: var(--gold); font-style: normal; }

.legal-header-box {
  background: linear-gradient(180deg, #151D29 0%, #101722 100%);
  border: 1px solid var(--line);
  border-radius: 16px;
  padding: 32px 28px;
  margin: 40px 0 32px;
  position: relative;
  overflow: hidden;
}
.legal-header-box::before {
  content: '';
  position: absolute;
  top: 0;
  left: 10%;
  right: 10%;
  height: 2px;
  background: linear-gradient(90deg, transparent, #1677FF, #F0B429, #1677FF, transparent);
}
.badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 20px;
  background: rgba(240, 180, 41, 0.12);
  border: 1px solid rgba(240, 180, 41, 0.3);
  color: var(--gold-accent);
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  margin-bottom: 12px;
}
h1 { font-family: "Space Grotesk", sans-serif; font-size: 2rem; font-weight: 700; margin-bottom: 8px; line-height: 1.25; }
.meta-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
  margin-top: 20px;
  padding-top: 18px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 0.82rem;
  color: var(--dim);
}
.meta-item strong { color: #fff; font-family: 'IBM Plex Mono', monospace; }

.toc-card {
  background: #101722;
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 20px 24px;
  margin-bottom: 36px;
}
.toc-title {
  font-family: "Space Grotesk", sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.toc-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 8px;
  font-size: 0.8rem;
}
.toc-grid a {
  color: var(--muted);
  text-decoration: none;
  padding: 4px 0;
  transition: color 0.15s;
}
.toc-grid a:hover { color: var(--gold); }

.legal-section {
  background: #151D29;
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 24px 28px;
  margin-bottom: 20px;
}
.legal-section h2 {
  font-family: "Space Grotesk", sans-serif;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--gold-accent);
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.legal-section p {
  color: var(--muted);
  font-size: 0.92rem;
  line-height: 1.7;
  margin-bottom: 12px;
}
.legal-section p:last-child { margin-bottom: 0; }
.legal-section strong { color: #fff; }
.legal-section ul {
  color: var(--muted);
  margin: 0 0 12px 24px;
  font-size: 0.92rem;
}
.legal-section li { margin-bottom: 6px; }

.callout-box {
  background: rgba(246, 70, 93, 0.08);
  border: 1px solid rgba(246, 70, 93, 0.25);
  border-radius: 10px;
  padding: 16px 20px;
  margin: 16px 0;
  color: #F6465D;
  font-size: 0.88rem;
  line-height: 1.6;
}

.acceptance-actions {
  text-align: center;
  margin: 40px 0 60px;
}
.btn-enroll-cta {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 16px 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, #1677FF 0%, #0D47A1 100%);
  color: #fff;
  font-family: "Space Grotesk", sans-serif;
  font-size: 1rem;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 8px 24px rgba(22, 119, 255, 0.35);
  transition: transform 0.2s, box-shadow 0.2s;
}
.btn-enroll-cta:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(22, 119, 255, 0.5);
  text-decoration: none;
}
</style>
</head>
<body>

<header class="topnav">
  <div class="shell topnavin">
    <a class="brand" href="index.php">
      <img src="BM-ForexHub-Logo-Circle.png" alt="BM Forex Hub" width="32" height="32">
      BM <em>FOREX</em> HUB
    </a>
    <div style="display:flex; align-items:center; gap:16px;">
      <button type="button" class="theme-toggle" title="Toggle light / dark theme" aria-label="Toggle light / dark theme">
        <svg class="theme-toggle__sun" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
        <svg class="theme-toggle__moon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
      <a href="bm_elites.php" style="color:var(--dim); font-size:0.85rem;">&larr; Back to BM Elites</a>
    </div>
  </div>
</header>

<main class="shell">
  
  <div class="legal-header-box">
    <div class="badge-pill">👑 BM ELITES TRADING CIRCLE</div>
    <h1>Elite Circle Terms and Conditions</h1>
    <p style="color:var(--muted); font-size:0.92rem; max-width:760px; line-height:1.6;">
      This document constitutes the binding legal agreement governing participation in the BM FOREX HUB Elite Circle managed trading program.
    </p>

    <div class="meta-grid">
      <div class="meta-item">Effective Date: <strong>05 January 2026</strong></div>
      <div class="meta-item">Version: <strong>1.0</strong></div>
      <div class="meta-item">Operating Entity: <strong>Varban Company Limited</strong></div>
      <div class="meta-item">Associated Parent: <strong>Stillrock Ventures</strong></div>
      <div class="meta-item">Jurisdiction: <strong>Republic of Kenya</strong></div>
      <div class="meta-item">Cycle Duration: <strong>4 Months Fixed</strong></div>
    </div>
  </div>

  <div class="callout-box">
    <strong>IMPORTANT NOTICE:</strong> Please read these Terms and Conditions carefully before enrolling. By completing an application, clicking an acceptance button, accepting the Terms electronically, making a payment, submitting an enrollment form, or otherwise proceeding with the Elite Circle program, the Member confirms that they have read, understood and accepted these Terms and Conditions. If the Member does not agree, they should not proceed with enrollment or payment.
  </div>

  <!-- Table of Contents -->
  <div class="toc-card">
    <div class="toc-title">Table of Contents (39 Sections)</div>
    <div class="toc-grid">
      <a href="#sec1">1. Definitions</a>
      <a href="#sec2">2. Parties &amp; Corporate Structure</a>
      <a href="#sec3">3. Eligibility</a>
      <a href="#sec4">4. Acceptance of Terms</a>
      <a href="#sec5">5. Investment / Trading Cycle</a>
      <a href="#sec6">6. No Guarantee of Returns</a>
      <a href="#sec7">7. Trading and Management</a>
      <a href="#sec8">8. Compounding</a>
      <a href="#sec9">9. Commissions &amp; Distributions</a>
      <a href="#sec10">10. Withdrawals</a>
      <a href="#sec11">11. Payment Channels</a>
      <a href="#sec12">12. Fees and Charges</a>
      <a href="#sec13">13. Account Suspension</a>
      <a href="#sec14">14. Termination</a>
      <a href="#sec15">15. Member Representations</a>
      <a href="#sec16">16. Personnel &amp; Representatives</a>
      <a href="#sec17">17. Protection of Directors &amp; Officers</a>
      <a href="#sec18">18. Limitation of Liability</a>
      <a href="#sec19">19. Indemnity</a>
      <a href="#sec20">20. Confidentiality</a>
      <a href="#sec21">21. Intellectual Property</a>
      <a href="#sec22">22. Data Protection &amp; Privacy</a>
      <a href="#sec23">23. Electronic Communications</a>
      <a href="#sec24">24. Complaints and Disputes</a>
      <a href="#sec25">25. Governing Law</a>
      <a href="#sec26">26. Force Majeure</a>
      <a href="#sec27">27. Amendments</a>
      <a href="#sec28">28. Severability</a>
      <a href="#sec29">29. No Waiver</a>
      <a href="#sec30">30. Entire Agreement</a>
      <a href="#sec31">31. No Assignment</a>
      <a href="#sec32">32. Account Security</a>
      <a href="#sec33">33. Prohibited Conduct</a>
      <a href="#sec34">34. Unauthorized Representations</a>
      <a href="#sec35">35. Risk Acknowledgment</a>
      <a href="#sec36">36. Regulatory Status &amp; Compliance</a>
      <a href="#sec37">37. Important Member Acknowledgment</a>
      <a href="#sec38">38. Electronic Acceptance</a>
      <a href="#sec39">39. Company Details</a>
    </div>
  </div>

  <!-- Sections 1 to 39 -->
  <div class="legal-section" id="sec1">
    <h2>1. DEFINITIONS</h2>
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

  <div class="legal-section" id="sec2">
    <h2>2. PARTIES AND CORPORATE STRUCTURE</h2>
    <p>The Elite Circle may be marketed, administered, supported or operated through BM FOREX HUB and/or entities within the relevant corporate structure. The legal entity responsible for a transaction shall be identified in the relevant documentation. Where applicable, Varban Company Limited shall be the contracting or operating entity. Stillrock Ventures may act as a parent, holding, administrative, strategic or associated entity where applicable. Association with BM FOREX HUB does not automatically impose liability on shareholders, directors, employees, consultants, contractors or agents.</p>
  </div>

  <div class="legal-section" id="sec3">
    <h2>3. ELIGIBILITY</h2>
    <p>The Member must provide accurate and complete information. The Company may request identity, address, source-of-funds, payment, tax and compliance information reasonably necessary for administration or legal compliance. The Company may decline, suspend or terminate an application where information is incomplete, inaccurate, misleading or unverifiable.</p>
  </div>

  <div class="legal-section" id="sec4">
    <h2>4. ACCEPTANCE OF TERMS</h2>
    <p>Acceptance may occur electronically through an acceptance box, button, online application, electronic signature, email/message confirmation, payment after presentation of these Terms, or proceeding after access to the Terms. The Company may retain timestamps, application records, system logs, emails, messages, transaction records and other acceptance evidence, subject to applicable law.</p>
  </div>

  <div class="legal-section" id="sec5">
    <h2>5. INVESTMENT / TRADING CYCLE</h2>
    <p>Unless otherwise expressly stated in account documentation, the Elite Circle operates on a four (4) month complete trading/investment cycle. The cycle commences on the applicable date recorded by the Company. The commencement and completion dates may be communicated through account records, confirmation notices, payment records or other official communications. The investment cycle is a material condition of participation. Early withdrawal of invested capital is not permitted during the active cycle unless expressly permitted by the applicable agreement or approved by the Company in writing. Discontinuing compounding does not automatically terminate the cycle or create an entitlement to immediate withdrawal. Completion of the cycle does not constitute an automatic payment instruction; applicable verification and withdrawal procedures may still apply.</p>
  </div>

  <div class="legal-section" id="sec6">
    <h2>6. NO GUARANTEE OF RETURNS</h2>
    <p>Trading and investment involve financial risk. Past performance does not guarantee future performance. No employee, representative, marketer, affiliate or agent may guarantee a particular profit unless expressly authorized and legally permissible. Historical, illustrative, projected or estimated returns are not guarantees. Market conditions may result in profits, reduced returns or losses.</p>
  </div>

  <div class="legal-section" id="sec7">
    <h2>7. TRADING AND MANAGEMENT</h2>
    <p>Trading decisions may use strategies, systems, technology, algorithms or discretionary decisions determined by the Company or appointed managers. The Company does not guarantee that every trade will be profitable and may modify strategies, instruments, brokers, platforms, technology or risk procedures where reasonably necessary. Trading may be temporarily suspended due to market, liquidity, technology, broker, regulatory or operational circumstances.</p>
  </div>

  <div class="legal-section" id="sec8">
    <h2>8. COMPOUNDING</h2>
    <p>Where applicable, qualifying amounts may be retained and reinvested rather than distributed periodically. Compounding does not guarantee a compounded return. A Member may request discontinuation of compounding in accordance with the account procedure, but this does not alter the minimum investment cycle unless expressly agreed in writing.</p>
  </div>

  <div class="legal-section" id="sec9">
    <h2>9. COMMISSIONS AND DISTRIBUTIONS</h2>
    <p>Commissions/distributions are calculated and processed according to the applicable arrangement. A pending, projected or estimated amount is not necessarily a presently payable debt. Final amounts are determined from official records following reconciliation. Payments may be delayed by banking systems, payment processors, technical problems, compliance reviews, holidays, network interruptions, incorrect client information or other circumstances outside reasonable Company control.</p>
  </div>

  <div class="legal-section" id="sec10">
    <h2>10. WITHDRAWALS</h2>
    <p>Withdrawals are subject to the applicable investment cycle and these Terms. Unless expressly permitted otherwise, invested capital cannot be withdrawn before completion of the four-month cycle. Withdrawal requests may require identity/account verification, payment confirmation, compliance checks, reconciliation, applicable fees and an approved withdrawal channel. The Company may delay processing where reasonably necessary to verify a request.</p>
  </div>

  <div class="legal-section" id="sec11">
    <h2>11. PAYMENT CHANNELS</h2>
    <p>The Company may use banks, payment processors, mobile-money providers, cryptocurrency networks or other third-party services. Third-party channels are outside the Company's direct control and may experience interruptions, restrictions, reviews, congestion or delays.</p>
  </div>

  <div class="legal-section" id="sec12">
    <h2>12. FEES AND CHARGES</h2>
    <p>Applicable fees shall be disclosed through the enrollment, invoice, account documentation or official communication. Third-party charges may apply where properly disclosed and applicable.</p>
  </div>

  <div class="legal-section" id="sec13">
    <h2>13. ACCOUNT SUSPENSION</h2>
    <p>The Company may suspend an account for suspected fraud, verification issues, unauthorized activity, breach of Terms, unlawful activity, abusive conduct, system manipulation, fraudulent chargebacks, legal/regulatory requirements, cybersecurity concerns or other material operational risks. Suspension of website or portal access does not necessarily terminate the underlying contractual relationship.</p>
  </div>

  <div class="legal-section" id="sec14">
    <h2>14. TERMINATION</h2>
    <p>The Company may terminate or suspend participation for material breach or where continuation creates legal, regulatory, financial, security or operational risk. A Member's termination request remains subject to the applicable investment cycle and withdrawal restrictions. Accrued rights and obligations survive termination where applicable.</p>
  </div>

  <div class="legal-section" id="sec15">
    <h2>15. MEMBER REPRESENTATIONS</h2>
    <p>The Member represents that information supplied is accurate; they have capacity to contract; understand financial risk; use lawfully obtained funds; are not relying on unauthorized promises; had reasonable opportunity to review the Terms; and may obtain independent professional advice.</p>
  </div>

  <div class="legal-section" id="sec16">
    <h2>16. COMPANY PERSONNEL AND REPRESENTATIVES</h2>
    <p>Employees, consultants, agents, marketers and representatives must act within authorized roles. No employee or representative may personally guarantee returns, authorize unauthorized withdrawals, alter contractual terms or make binding commitments unless expressly authorized. Unauthorized representations do not bind the Company subject to applicable law. Threats, harassment, intimidation, abusive or discriminatory conduct may result in appropriate account or communication restrictions, subject to law.</p>
  </div>

  <div class="legal-section" id="sec17">
    <h2>17. PROTECTION OF DIRECTORS, OFFICERS, EMPLOYEES AND AGENTS</h2>
    <p>To the extent permitted by law, directors, officers, employees, consultants, contractors, agents and representatives are not personally liable for contractual obligations properly undertaken by the Company. Claims should ordinarily be directed against the contracting entity identified in the documentation.</p>
  </div>

  <div class="legal-section" id="sec18">
    <h2>18. LIMITATION OF LIABILITY</h2>
    <p>To the maximum extent permitted by law, the Company is not liable for indirect, consequential, incidental, special or unforeseeable losses arising from market movements, trading losses, third-party payment failures, banking delays, internet interruptions, platform downtime, force majeure, unauthorized third-party access, telecommunications failures or circumstances outside reasonable control. Nothing excludes liability that cannot legally be excluded.</p>
  </div>

  <div class="legal-section" id="sec19">
    <h2>19. INDEMNITY</h2>
    <p>To the extent permitted by law, the Member indemnifies the Company and its directors, officers, employees, agents and authorized representatives against losses, claims, costs or expenses arising from the Member's breach, fraud/unlawful activity, inaccurate information, unauthorized account use, violation of rights, system misuse or unlawful communications. This does not apply to losses caused by the Company's own unlawful conduct.</p>
  </div>

  <div class="legal-section" id="sec20">
    <h2>20. CONFIDENTIALITY</h2>
    <p>The Member shall keep confidential non-public Company systems, trading methodologies, proprietary strategies, internal processes, account information and other confidential business information, except where disclosure is legally required, needed for professional advice or authorized.</p>
  </div>

  <div class="legal-section" id="sec21">
    <h2>21. INTELLECTUAL PROPERTY</h2>
    <p>BM FOREX HUB names, logos, strategies, training materials, software, content, documentation and proprietary systems remain the property of their respective owners. Participation does not transfer ownership. Unauthorized copying, resale, redistribution, reverse engineering or commercial exploitation is prohibited.</p>
  </div>

  <div class="legal-section" id="sec22">
    <h2>22. DATA PROTECTION AND PRIVACY</h2>
    <p>The Company may process personal information reasonably required for administration, verification, payment processing, compliance, security, customer support and legitimate business purposes. Personal data shall be handled in accordance with applicable Kenyan data-protection law and may be shared with authorized processors, service providers, advisers or regulators where legally permitted or required.</p>
  </div>

  <div class="legal-section" id="sec23">
    <h2>23. ELECTRONIC COMMUNICATIONS AND RECORDS</h2>
    <p>The Member consents to electronic account communications where legally permissible. Emails, electronic notices, platform records, transaction records and other electronic communications may constitute business records. The Company may retain evidence of enrollment, acceptance, payments, communications and account activity.</p>
  </div>

  <div class="legal-section" id="sec24">
    <h2>24. COMPLAINTS AND DISPUTES</h2>
    <p>Members should submit complaints in writing through the designated support channel with sufficient information for investigation. The Company shall review complaints in accordance with internal procedures and applicable law. Nothing restricts statutory or regulatory rights.</p>
  </div>

  <div class="legal-section" id="sec25">
    <h2>25. GOVERNING LAW</h2>
    <p>These Terms are governed by the laws of the Republic of Kenya unless mandatory law requires otherwise. Disputes shall be addressed through applicable dispute-resolution mechanisms and courts or regulators having lawful jurisdiction.</p>
  </div>

  <div class="legal-section" id="sec26">
    <h2>26. FORCE MAJEURE</h2>
    <p>The Company is not responsible for delay or failure caused by circumstances beyond reasonable control, including natural disasters, war, civil unrest, government action, regulatory intervention, cyberattacks, telecommunications failures, internet outages, banking failures, payment-provider failures, broker failures, market closures or extraordinary market events.</p>
  </div>

  <div class="legal-section" id="sec27">
    <h2>27. AMENDMENTS</h2>
    <p>The Company may update these Terms where reasonably necessary due to changes in law, regulation, technology, operations, security or risk management. Material changes shall be communicated where required by law. Mandatory legal rights are not retrospectively removed.</p>
  </div>

  <div class="legal-section" id="sec28">
    <h2>28. SEVERABILITY</h2>
    <p>If any provision is invalid, unlawful or unenforceable, it shall be modified or severed to the minimum extent necessary and the remaining provisions continue to the extent legally permissible.</p>
  </div>

  <div class="legal-section" id="sec29">
    <h2>29. NO WAIVER</h2>
    <p>Failure to immediately enforce a provision does not constitute a waiver or prevent later enforcement.</p>
  </div>

  <div class="legal-section" id="sec30">
    <h2>30. ENTIRE AGREEMENT</h2>
    <p>These Terms together with applicable enrollment documentation, account confirmation, schedules and incorporated documents form the contractual framework. No oral statement by an unauthorized person amends these Terms. Conflicts are resolved according to the applicable written agreement and mandatory law.</p>
  </div>

  <div class="legal-section" id="sec31">
    <h2>31. NO ASSIGNMENT</h2>
    <p>The Member may not transfer, assign, sell, pledge or dispose of the account or contractual rights without prior written approval, except where prohibited by law. The Company may assign or transfer rights and obligations as permitted by law.</p>
  </div>

  <div class="legal-section" id="sec32">
    <h2>32. ACCOUNT SECURITY</h2>
    <p>The Member is responsible for passwords and credentials and must immediately notify the Company of suspected unauthorized access. The Company is not responsible for losses caused by failure to protect credentials except where liability cannot lawfully be excluded.</p>
  </div>

  <div class="legal-section" id="sec33">
    <h2>33. PROHIBITED CONDUCT</h2>
    <p>The Member shall not provide false information, manipulate systems, attempt unauthorized access, submit fraudulent payment disputes, impersonate personnel, misuse intellectual property, threaten or harass employees, knowingly publish false information, interfere with systems or use the Elite Circle unlawfully. Nothing restricts lawful complaints, regulatory reporting, legal proceedings or truthful statements supported by evidence.</p>
  </div>

  <div class="legal-section" id="sec34">
    <h2>34. NO RELIANCE ON UNAUTHORIZED REPRESENTATIONS</h2>
    <p>Only authorized Company representatives may make binding representations. Marketing statements, social-media posts, educational content, historical examples and informal communications do not amend the governing agreement unless expressly incorporated.</p>
  </div>

  <div class="legal-section" id="sec35">
    <h2>35. RISK ACKNOWLEDGMENT</h2>
    <p>The Member acknowledges market volatility, possible losses, non-guaranteed historical performance, payment delays, possible strategy failure, changing market conditions and financial risk.</p>
  </div>

  <div class="legal-section" id="sec36">
    <h2>36. REGULATORY STATUS AND COMPLIANCE</h2>
    <p>The Company shall operate the Elite Circle only to the extent permitted by applicable law and regulatory requirements. Nothing grants an authorization the Company does not legally possess. Where an activity requires licensing, approval, registration or authorization, applicable requirements must be satisfied before undertaking the regulated activity.</p>
  </div>

  <div class="legal-section" id="sec37">
    <h2>37. IMPORTANT MEMBER ACKNOWLEDGMENT</h2>
    <p><strong>I HAVE READ AND UNDERSTOOD THESE TERMS AND CONDITIONS. I UNDERSTAND THAT THE ELITE CIRCLE HAS A FOUR-MONTH INVESTMENT/TRADING CYCLE, SUBJECT TO THE SPECIFIC TERMS APPLICABLE TO MY ACCOUNT. I UNDERSTAND THAT EARLY WITHDRAWAL OF INVESTED CAPITAL MAY NOT BE AVAILABLE DURING THE ACTIVE CYCLE. I UNDERSTAND THAT TRADING INVOLVES FINANCIAL RISK AND THAT RETURNS ARE NOT GUARANTEED. I CONFIRM THAT THE INFORMATION I PROVIDED IS ACCURATE. I ACCEPT THESE TERMS VOLUNTARILY AND HAVE HAD THE OPPORTUNITY TO SEEK INDEPENDENT PROFESSIONAL ADVICE BEFORE PARTICIPATING.</strong></p>
  </div>

  <div class="legal-section" id="sec38">
    <h2>38. ELECTRONIC ACCEPTANCE</h2>
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

  <div class="legal-section" id="sec39">
    <h2>39. COMPANY DETAILS</h2>
    <p><strong>Trading/Business Brand:</strong> BM FOREX HUB<br>
    <strong>Operating Entity:</strong> Varban Company Limited<br>
    <strong>Parent / Associated Company:</strong> Stillrock Ventures<br>
    <strong>Official Support Contact:</strong> +254 780 618 608<br>
    <strong>Official Website:</strong> bmforexhub.exchange<br>
    <strong>Registered Office:</strong> Nairobi, Kenya<br>
    <strong>Effective Date:</strong> 05 January 2026 | <strong>Version:</strong> 1.0</p>
  </div>

  <div class="acceptance-actions">
    <a href="elite_enrollment.php" class="btn-enroll-cta">
      Proceed to Electronic Enrollment &amp; Acceptance &rarr;
    </a>
  </div>

</main>

<footer style="padding: 24px 0; border-top: 1px solid var(--line); text-align: center; font-size: 0.82rem; color: var(--dim); margin-top: 60px;">
  <div class="shell">
    <p>&copy; <?= date('Y') ?> BM FOREX HUB. All Rights Reserved. Operated by Varban Company Limited (Parent: Stillrock Ventures).</p>
    <div style="display:flex; justify-content:center; gap:16px; margin-top:8px; flex-wrap:wrap;">
      <a href="terms.php">General Terms</a>
      <span style="color:var(--line);">|</span>
      <a href="terms-elite.php" style="color:var(--gold); font-weight:600;">Elite Circle Terms</a>
      <span style="color:var(--line);">|</span>
      <a href="privacy.php">Privacy Policy</a>
      <span style="color:var(--line);">|</span>
      <a href="risk-disclosure.php">Risk Disclaimer</a>
      <span style="color:var(--line);">|</span>
      <a href="contact.php">Support</a>
    </div>
  </div>
</footer>

<script src="js/motion.js" defer></script>
</body>
</html>
