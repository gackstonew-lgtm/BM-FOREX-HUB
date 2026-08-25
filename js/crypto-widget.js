/**
 * Crypto Buy/Sell widget — BM Forex Hub
 * Talks only to our own backend (api/crypto-*.php). Never computes or
 * trusts a price/fee itself — every number shown comes from the
 * server, and only the opaque quote token is sent back at purchase time.
 */
(function () {
  var state = {
    side: 'BUY',
    fiat: 'KES',
    crypto: 'USDT',
    assets: [],
    fiats: [],
    quote: null,
    quoteTimer: null,
    quoteSecondsLeft: 0,
    quoteDebounce: null,
  };

  var els = {};

  function q(id) { return document.getElementById(id); }

  async function getAuthHeader() {
    try {
      if (typeof BMAuth === 'undefined') return {};
      var result = await BMAuth.getSession();
      var token = result && result.session && result.session.access_token;
      return token ? { 'Authorization': 'Bearer ' + token } : {};
    } catch (e) { return {}; }
  }

  function fmtNum(n, decimals) {
    if (n === null || n === undefined || isNaN(n)) return '--';
    return Number(n).toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
  }

  function showAlert(msg, type) {
    if (!els.alert) return;
    els.alert.textContent = msg;
    els.alert.className = 'crypto-alert ' + (type || 'info');
    els.alert.hidden = false;
  }
  function clearAlert() {
    if (els.alert) els.alert.hidden = true;
  }

  // ── Init ─────────────────────────────────────────────────────────
  function init() {
    els.root = document.getElementById('cryptoWidget');
    if (!els.root) return;

    els.tabBuy = q('cryptoTabBuy');
    els.tabSell = q('cryptoTabSell');
    els.spendAmount = q('cryptoSpendAmount');
    els.spendCurrencySel = q('cryptoSpendCurrency');
    els.receiveAmount = q('cryptoReceiveAmount');
    els.receiveCurrencySel = q('cryptoReceiveCurrency');
    els.spendLabel = q('cryptoSpendLabel');
    els.receiveLabel = q('cryptoReceiveLabel');
    els.quoteDetails = q('cryptoQuoteDetails');
    els.quoteTimerEl = q('cryptoQuoteTimer');
    els.refreshBtn = q('cryptoRefreshQuoteBtn');
    els.submitBtn = q('cryptoSubmitBtn');
    els.alert = q('cryptoAlert');
    els.paymentMethodWrap = q('cryptoPaymentMethodWrap');
    els.paymentMethodSel = q('cryptoPaymentMethod');
    els.phoneInput = q('cryptoPhoneInput');
    els.cardInput = q('cryptoCardInput');
    els.cardNameInput = q('cryptoCardName');
    els.cardNumberInput = q('cryptoCardNumber');
    els.cardExpiryInput = q('cryptoCardExpiry');
    els.cardCvvInput = q('cryptoCardCvv');
    els.walletAddressWrap = q('cryptoWalletAddressWrap');
    els.walletAddressInput = q('cryptoWalletAddress');
    els.depositAddressWrap = q('cryptoDepositAddressWrap');
    els.depositAddressValue = q('cryptoDepositAddressValue');
    els.tickerRow = q('cryptoTickerRow');
    els.historyBody = q('cryptoHistoryBody');
    els.historyToggle = q('cryptoHistoryToggle');
    els.historyWrap = q('cryptoHistoryWrap');

    els.tabBuy.addEventListener('click', function () { setSide('BUY'); });
    els.tabSell.addEventListener('click', function () { setSide('SELL'); });
    els.spendAmount.addEventListener('input', debounceQuote);
    els.spendCurrencySel.addEventListener('change', onSpendCurrencyChange);
    els.receiveCurrencySel.addEventListener('change', onReceiveCurrencyChange);
    els.refreshBtn.addEventListener('click', requestQuote);
    els.submitBtn.addEventListener('click', submitTransaction);
    els.paymentMethodSel.addEventListener('change', updatePaymentMethodUI);
    if (els.historyToggle) {
      els.historyToggle.addEventListener('click', function () {
        var hidden = els.historyWrap.hasAttribute('hidden');
        if (hidden) { els.historyWrap.removeAttribute('hidden'); loadHistory(); }
        else { els.historyWrap.setAttribute('hidden', ''); }
      });
    }

    loadMarketData();
    setInterval(loadTicker, 30000);
  }

  function setSide(side) {
    state.side = side;
    els.tabBuy.classList.toggle('active', side === 'BUY');
    els.tabSell.classList.toggle('active', side === 'SELL');
    els.spendLabel.textContent = side === 'BUY' ? 'Spend' : 'Sell';
    els.receiveLabel.textContent = 'Receive';
    renderCurrencyOptions();
    els.spendAmount.value = '';
    els.receiveAmount.value = '';
    els.quoteDetails.hidden = true;
    els.quoteTimerEl.hidden = true;
    state.quote = null;
    els.submitBtn.disabled = true;
    if (els.depositAddressWrap) els.depositAddressWrap.hidden = true;
    updatePaymentMethodUI();
    clearAlert();
  }

  function renderCurrencyOptions() {
    // BUY: spend = fiat, receive = crypto. SELL: spend = crypto, receive = fiat.
    var fiatOptions = state.fiats.map(function (f) {
      return '<option value="' + f.symbol + '">' + f.symbol + '</option>';
    }).join('');
    var cryptoOptions = state.assets
      .filter(function (a) { return state.side === 'BUY' ? a.buy_enabled : a.sell_enabled; })
      .map(function (a) { return '<option value="' + a.symbol + '">' + a.symbol + '</option>'; })
      .join('');

    if (state.side === 'BUY') {
      els.spendCurrencySel.innerHTML = fiatOptions;
      els.receiveCurrencySel.innerHTML = cryptoOptions;
      els.spendAmount.readOnly = false;
      els.receiveAmount.readOnly = true;
    } else {
      els.spendCurrencySel.innerHTML = cryptoOptions;
      els.receiveCurrencySel.innerHTML = fiatOptions;
      els.spendAmount.readOnly = false;
      els.receiveAmount.readOnly = true;
    }

    if (els.spendCurrencySel.options.length === 0) {
      showAlert('No assets are currently enabled for ' + state.side.toLowerCase() + '.', 'info');
      els.submitBtn.disabled = true;
    }
  }

  function onSpendCurrencyChange() { debounceQuote(); }
  function onReceiveCurrencyChange() { debounceQuote(); }

  function currentCryptoSymbol() {
    return state.side === 'BUY' ? els.receiveCurrencySel.value : els.spendCurrencySel.value;
  }
  function currentFiatSymbol() {
    return state.side === 'BUY' ? els.spendCurrencySel.value : els.receiveCurrencySel.value;
  }

  function debounceQuote() {
    clearAlert();
    if (state.quoteDebounce) clearTimeout(state.quoteDebounce);
    state.quoteDebounce = setTimeout(requestQuote, 450);
  }

  // ── Market data / ticker ─────────────────────────────────────────
  async function loadMarketData() {
    try {
      var res = await fetch('api/crypto-prices.php');
      var data = await res.json();
      if (!data.ok) return;
      state.assets = data.assets || [];
      state.fiats = data.fiat || [];
      renderCurrencyOptions();
      renderTicker(data.prices || []);
    } catch (e) {
      console.warn('Crypto market data load failed', e);
    }
  }

  async function loadTicker() {
    try {
      var res = await fetch('api/crypto-prices.php');
      var data = await res.json();
      if (data.ok) renderTicker(data.prices || []);
    } catch (e) {}
  }

  function renderTicker(prices) {
    if (!els.tickerRow) return;
    els.tickerRow.innerHTML = '';
    prices.forEach(function (p) {
      var change = p.usd_24h_change || 0;
      var dir = change >= 0 ? 'up' : 'down';
      var sign = change >= 0 ? '+' : '';
      var item = document.createElement('div');
      item.className = 'crypto-ticker-item';
      item.innerHTML =
        '<div class="crypto-ticker-item__sym">' + p.symbol + '</div>' +
        '<div class="crypto-ticker-item__price">$' + fmtNum(p.usd, p.usd < 1 ? 4 : 2) + '</div>' +
        '<div class="crypto-ticker-item__change ' + dir + '">' + sign + change.toFixed(2) + '%</div>';
      els.tickerRow.appendChild(item);
    });
  }

  // ── Quotes ───────────────────────────────────────────────────────
  async function requestQuote() {
    var crypto = currentCryptoSymbol();
    var fiat = currentFiatSymbol();
    if (!crypto || !fiat) return;

    var amount = parseFloat(els.spendAmount.value);
    if (!amount || amount <= 0) {
      els.receiveAmount.value = '';
      els.quoteDetails.hidden = true;
      els.quoteTimerEl.hidden = true;
      els.submitBtn.disabled = true;
      return;
    }

    var body = { side: state.side, fiat: fiat, crypto: crypto };
    if (state.side === 'BUY') body.fiat_amount = amount;
    else body.crypto_amount = amount;

    var authHeader = await getAuthHeader();
    if (!authHeader.Authorization) {
      showAlert('Please log in to get a live quote.', 'info');
      return;
    }

    els.submitBtn.disabled = true;
    try {
      var res = await fetch('api/crypto-quote.php', {
        method: 'POST',
        headers: Object.assign({ 'Content-Type': 'application/json' }, authHeader),
        body: JSON.stringify(body),
      });
      var data = await res.json();
      if (!data.ok) {
        showAlert(data.error || 'Could not get a quote', 'error');
        els.quoteDetails.hidden = true;
        els.quoteTimerEl.hidden = true;
        return;
      }
      clearAlert();
      applyQuote(data.quote);
    } catch (e) {
      showAlert('Network error getting quote', 'error');
    }
  }

  function applyQuote(quote) {
    state.quote = quote;
    els.receiveAmount.value = state.side === 'BUY'
      ? fmtNum(quote.crypto_amount, 6)
      : fmtNum(quote.fiat_amount, 2);

    els.quoteDetails.hidden = false;
    els.quoteDetails.innerHTML =
      '<div class="row">Market rate<span class="val">1 ' + quote.crypto + ' &asymp; ' + quote.fiat + ' ' + fmtNum(quote.rate, 2) + '</span></div>' +
      '<div class="row">Fee (' + quote.fee_percent + '%)<span class="val">' + quote.fiat + ' ' + fmtNum(quote.fee_amount, 2) + '</span></div>' +
      '<div class="row">' + (state.side === 'BUY' ? 'You pay' : 'You receive') + '<span class="val">' + quote.fiat + ' ' + fmtNum(quote.fiat_amount, 2) + '</span></div>' +
      '<div class="row">' + (state.side === 'BUY' ? 'You receive' : 'You sell') + '<span class="val">' + fmtNum(quote.crypto_amount, 6) + ' ' + quote.crypto + '</span></div>';

    startQuoteCountdown(quote.expires_at);
    els.submitBtn.disabled = false;
    els.submitBtn.textContent = state.side === 'BUY' ? ('Buy ' + quote.crypto) : ('Sell ' + quote.crypto);
    updatePaymentMethodUI();
  }

  function startQuoteCountdown(expiresAt) {
    if (state.quoteTimer) clearInterval(state.quoteTimer);
    els.quoteTimerEl.hidden = false;
    els.quoteTimerEl.classList.remove('expired');

    function tick() {
      var left = expiresAt - Math.floor(Date.now() / 1000);
      if (left <= 0) {
        clearInterval(state.quoteTimer);
        els.quoteTimerEl.classList.add('expired');
        els.quoteTimerEl.innerHTML = '<span class="dot"></span> Quote expired &middot; <button type="button" class="crypto-refresh-quote-btn" id="cryptoRefreshInline">Refresh Quote</button>';
        var btn = q('cryptoRefreshInline');
        if (btn) btn.addEventListener('click', requestQuote);
        els.submitBtn.disabled = true;
        return;
      }
      els.quoteTimerEl.innerHTML = '<span class="dot"></span> Quote valid for ' + left + 's';
    }
    tick();
    state.quoteTimer = setInterval(tick, 1000);
  }

  function updatePaymentMethodUI() {
    if (state.side === 'BUY') {
      els.paymentMethodWrap.hidden = false;
      var method = els.paymentMethodSel.value;
      els.phoneInput.style.display = method === 'mpesa' ? 'block' : 'none';
      if (els.cardInput) els.cardInput.hidden = method !== 'card';
      if (els.walletAddressWrap) els.walletAddressWrap.hidden = false;
    } else {
      els.paymentMethodWrap.hidden = true;
      if (els.cardInput) els.cardInput.hidden = true;
      if (els.walletAddressWrap) els.walletAddressWrap.hidden = true;
    }
  }

  // ── Submit ───────────────────────────────────────────────────────
  async function submitTransaction() {
    if (!state.quote) return;
    clearAlert();
    els.submitBtn.disabled = true;
    var originalText = els.submitBtn.textContent;
    els.submitBtn.textContent = 'Processing...';

    var authHeader = await getAuthHeader();
    if (!authHeader.Authorization) {
      showAlert('Please log in to continue.', 'error');
      els.submitBtn.disabled = false;
      els.submitBtn.textContent = originalText;
      return;
    }

    try {
      if (state.side === 'BUY') {
        var method = els.paymentMethodSel.value;
        var body = {
          quote_token: state.quote.token,
          payment_method: method,
          phone: els.phoneInput.querySelector('input') ? els.phoneInput.querySelector('input').value : '',
          destination_address: els.walletAddressInput ? els.walletAddressInput.value.trim() : '',
        };
        if (method === 'card') {
          var cardName = els.cardNameInput ? els.cardNameInput.value.trim() : '';
          var cardNumber = els.cardNumberInput ? els.cardNumberInput.value.replace(/\s+/g, '') : '';
          var cardExpiry = els.cardExpiryInput ? els.cardExpiryInput.value.trim() : '';
          var cardCvv = els.cardCvvInput ? els.cardCvvInput.value.trim() : '';
          if (cardNumber.length < 13 || cardNumber.length > 19) {
            showAlert('Please enter a valid card number.', 'error');
            els.submitBtn.disabled = false;
            els.submitBtn.textContent = originalText;
            return;
          }
          if (!cardExpiry || !cardCvv) {
            showAlert('Please enter card expiry and CVV.', 'error');
            els.submitBtn.disabled = false;
            els.submitBtn.textContent = originalText;
            return;
          }
          body.card_name = cardName;
          body.card_number = cardNumber;
          body.card_expiry = cardExpiry;
          body.card_cvv = cardCvv;
        } else if (method === 'mpesa' && !body.phone) {
          showAlert('Please enter your M-Pesa phone number.', 'error');
          els.submitBtn.disabled = false;
          els.submitBtn.textContent = originalText;
          return;
        }
        var res = await fetch('api/crypto-buy.php', {
          method: 'POST',
          headers: Object.assign({ 'Content-Type': 'application/json' }, authHeader),
          body: JSON.stringify(body),
        });
        var data = await res.json();
        if (!data.ok) {
          showAlert(data.error || 'Purchase failed', 'error');
        } else if (data.checkout_url) {
          showAlert('Redirecting you to complete payment...', 'success');
          window.location.href = data.checkout_url;
        } else {
          showAlert(data.message || 'Payment initiated — check your phone to complete it.', 'success');
          loadHistory();
        }
      } else {
        var res2 = await fetch('api/crypto-sell.php', {
          method: 'POST',
          headers: Object.assign({ 'Content-Type': 'application/json' }, authHeader),
          body: JSON.stringify({ quote_token: state.quote.token }),
        });
        var data2 = await res2.json();
        if (!data2.ok) {
          showAlert(data2.error || 'Sell request failed', 'error');
        } else {
          showAlert(data2.message || 'Your sell request has been recorded.', 'info');
          if (data2.deposit_address && els.depositAddressWrap && els.depositAddressValue) {
            els.depositAddressValue.textContent = data2.deposit_address + (data2.network ? ' (' + data2.network + ')' : '');
            els.depositAddressWrap.hidden = false;
          }
          loadHistory();
        }
      }
    } catch (e) {
      showAlert('Network error — please try again.', 'error');
    }

    els.submitBtn.disabled = false;
    els.submitBtn.textContent = originalText;
  }

  // ── History ──────────────────────────────────────────────────────
  async function loadHistory() {
    if (!els.historyBody) return;
    var authHeader = await getAuthHeader();
    if (!authHeader.Authorization) return;
    try {
      var res = await fetch('api/crypto-transactions.php?limit=10', { headers: authHeader });
      var data = await res.json();
      if (!data.ok) return;
      els.historyBody.innerHTML = '';
      (data.transactions || []).forEach(function (t) {
        var tr = document.createElement('tr');
        var statusClass = 'pending';
        if (t.status === 'COMPLETED') statusClass = 'completed';
        else if (t.status === 'FAILED' || t.status === 'CANCELLED') statusClass = 'failed';
        else if (t.status === 'REFUNDED') statusClass = 'refunded';
        var date = new Date(t.created_at).toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
        tr.innerHTML =
          '<td>' + date + '</td>' +
          '<td>' + t.transaction_reference + '</td>' +
          '<td>' + t.type + '</td>' +
          '<td>' + t.crypto_symbol + '</td>' +
          '<td>' + t.fiat_currency + ' ' + fmtNum(t.fiat_amount, 2) + '</td>' +
          '<td>' + fmtNum(t.crypto_amount, 6) + '</td>' +
          '<td><span class="crypto-status-badge ' + statusClass + '">' + t.status.replace(/_/g, ' ') + '</span></td>';
        els.historyBody.appendChild(tr);
      });
    } catch (e) {}
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
