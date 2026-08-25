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
<title>Crypto Trading | BM FOREX HUB</title>
<meta name="description" content="Buy and sell digital assets quickly and securely with BM Forex Hub.">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<meta name="robots" content="noindex, nofollow, noarchive">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">
<link rel="stylesheet" href="css/crypto-widget.css?v=<?= filemtime('css/crypto-widget.css') ?>">
</head>
<body id="top" class="has-sidebar">

<!-- ===== Dashboard Sidebar ===== -->
<?php $activeNav = 'crypto'; include __DIR__ . '/components/global_sidebar.php'; ?>

<div class="dash-main-wrap">
<!-- Unified Global Header -->
<?php include __DIR__ . '/components/global_header.php'; ?>

<!-- Crypto Buy/Sell Widget (unchanged from the dashboard — moved here, not duplicated) -->
<div class="crypto-widget" id="cryptoWidget">
  <div class="crypto-card">
    <div class="crypto-card__head">
      <div>
        <h2 class="crypto-card__title">Crypto Trading</h2>
        <p class="crypto-card__subtitle">Buy and sell digital assets quickly and securely.</p>
      </div>
      <div class="crypto-tabs" role="tablist" aria-label="Buy or sell crypto">
        <button type="button" class="crypto-tab active" id="cryptoTabBuy" role="tab" aria-selected="true">Buy</button>
        <button type="button" class="crypto-tab" id="cryptoTabSell" role="tab" aria-selected="false">Sell</button>
      </div>
    </div>

    <div id="cryptoAlert" class="crypto-alert" hidden></div>

    <div class="crypto-convert">
      <div class="crypto-side">
        <div class="crypto-side__label" id="cryptoSpendLabel">Spend</div>
        <div class="crypto-side__row">
          <input type="number" id="cryptoSpendAmount" min="0" step="any" placeholder="0.00" inputmode="decimal">
          <select class="crypto-select" id="cryptoSpendCurrency" aria-label="Currency to spend"></select>
        </div>
      </div>
      <div class="crypto-swap-icon" aria-hidden="true">&#8594;</div>
      <div class="crypto-side">
        <div class="crypto-side__label" id="cryptoReceiveLabel">Receive</div>
        <div class="crypto-side__row">
          <input type="number" id="cryptoReceiveAmount" readonly placeholder="0.00" aria-label="Amount you receive">
          <select class="crypto-select" id="cryptoReceiveCurrency" aria-label="Currency to receive"></select>
        </div>
      </div>
    </div>

    <div class="crypto-quote-details" id="cryptoQuoteDetails" hidden></div>
    <div class="crypto-quote-timer" id="cryptoQuoteTimer" hidden></div>

    <div class="crypto-payment-method" id="cryptoPaymentMethodWrap">
      <label for="cryptoPaymentMethod">Payment Method</label>
      <select id="cryptoPaymentMethod">
        <option value="mpesa">M-Pesa (Kora)</option>
        <option value="card">Card (Kora)</option>
      </select>
      <div id="cryptoPhoneInput">
        <input type="tel" placeholder="+254 7XX XXX XXX" aria-label="M-Pesa phone number">
      </div>
      <div id="cryptoCardInput" hidden>
        <input type="text" id="cryptoCardName" placeholder="Cardholder name" aria-label="Cardholder name" autocomplete="cc-name">
        <input type="text" id="cryptoCardNumber" placeholder="Card number" inputmode="numeric" autocomplete="cc-number" aria-label="Card number">
        <div class="crypto-card-row">
          <input type="text" id="cryptoCardExpiry" placeholder="MM/YY" inputmode="numeric" autocomplete="cc-exp" aria-label="Card expiry">
          <input type="text" id="cryptoCardCvv" placeholder="CVV" inputmode="numeric" autocomplete="cc-csc" aria-label="Card CVV">
        </div>
      </div>
    </div>

    <div class="crypto-wallet-address" id="cryptoWalletAddressWrap">
      <label for="cryptoWalletAddress">Your wallet address (where we'll send your crypto)</label>
      <input type="text" id="cryptoWalletAddress" placeholder="Paste your USDT wallet address" aria-label="Destination wallet address">
    </div>

    <div class="crypto-deposit-address" id="cryptoDepositAddressWrap" hidden>
      <p>Send your crypto to the address below to complete this sale:</p>
      <code id="cryptoDepositAddressValue"></code>
    </div>

    <button type="button" class="crypto-submit-btn" id="cryptoSubmitBtn" disabled>Buy USDT</button>

    <p class="crypto-risk-note">Crypto asset prices are volatile. Buying or selling digital assets is not a guaranteed investment and carries risk of loss. Rates are indicative until you submit and may be revalidated before payment is accepted. Identity verification may be required before a transaction can be completed.</p>

    <div class="crypto-ticker" id="cryptoTickerRow"></div>

    <div class="crypto-history">
      <div class="crypto-history__head">
        <span class="crypto-history__title">Recent Crypto Transactions</span>
        <button type="button" class="crypto-history-toggle" id="cryptoHistoryToggle">View Crypto Transactions</button>
      </div>
      <div id="cryptoHistoryWrap" hidden>
        <table>
          <thead><tr><th>Date</th><th>Ref</th><th>Type</th><th>Crypto</th><th>Fiat</th><th>Crypto Amt</th><th>Status</th></tr></thead>
          <tbody id="cryptoHistoryBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- =====================================================================
     BM Forex Hub — Supabase auth guard (same rules as the dashboard):
     requires a logged-in user with full or trial access.
     ===================================================================== -->
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script src="js/crypto-widget.js?v=<?= filemtime('js/crypto-widget.js') ?>"></script>
<script>
(async function() {
  const { user, session } = await BMAuth.getSession();
  if (!user) { window.location.href = 'login.php'; return; }

  let authData = await BMAuth.getMembershipStatus(user, session);
  let hasCrypto = authData.level === 'trial' || authData.plans.includes('all') || authData.plans.some(function(p) { return p.startsWith('grid_'); });

  if (!hasCrypto) {
    window.location.href = 'subscribe.php?plan=grid';
    return;
  }

  const displayUserEl = document.getElementById('displayUsername');
  if (displayUserEl) displayUserEl.textContent = BMAuth.displayName(user);
})();

/* Client-side trial check as fallback when the PHP API is unreachable
   (mirrors the check used on the dashboard) */
async function clientSideTrialCheck(user) {
  try {
    const sb = BMAuth.getClient();
    const now = new Date().toISOString();
    const { data: subs, error: subErr } = await sb.from('subscriptions')
      .select('id,status,expires_at')
      .eq('user_id', user.id)
      .eq('status', 'active')
      .gt('expires_at', now)
      .order('expires_at', { ascending: false });
    if (!subErr && subs && subs.length > 0) return 'full';

    const { data: prof, error: profErr } = await sb.from('profiles')
      .select('trial_started_at')
      .eq('id', user.id)
      .single();
    if (profErr) return 'limited';

    let trialStarted = prof ? prof.trial_started_at : null;
    if (!trialStarted) return 'limited';

    const trialEnd = new Date(trialStarted).getTime() + (3 * 86400000);
    return Date.now() < trialEnd ? 'trial' : 'limited';
  } catch (e) {
    return 'limited';
  }
}
</script>
<script src="js/motion.js" defer></script>
<?php $activeTab = 'trade'; include __DIR__ . '/components/mobile-tabbar.php'; ?>
</body>
</html>
