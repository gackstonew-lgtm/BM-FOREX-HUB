/**
 * BM Forex Hub — Quick Trading Tools & Calculators Engine
 * Shared module for Pip, Lot Size, Margin, Position Size, Profit Calculators,
 * and World Live Currency Converter (featuring KSH, USD, EUR, GBP, BTC, XAU).
 */
(function() {
  'use strict';

  // Live Exchange Rates cache (base USD)
  var liveRatesCache = {
    USD: 1.0,
    KES: 129.50, // Kenyan Shilling (KSh)
    EUR: 0.92,   // Euro
    GBP: 0.78,   // British Pound
    JPY: 154.20, // Japanese Yen
    AUD: 1.52,   // Australian Dollar
    CAD: 1.36,   // Canadian Dollar
    CHF: 0.89,   // Swiss Franc
    NZD: 1.65,   // New Zealand Dollar
    ZAR: 18.25,  // South African Rand
    UGX: 3720.0, // Ugandan Shilling
    TZS: 2680.0, // Tanzanian Shilling
    RWF: 1315.0, // Rwandan Franc
    NGN: 1520.0, // Nigerian Naira
    GHS: 15.40,  // Ghanaian Cedi
    EGP: 48.60,  // Egyptian Pound
    AED: 3.67,   // UAE Dirham
    SAR: 3.75,   // Saudi Riyal
    INR: 83.50,  // Indian Rupee
    CNY: 7.24,   // Chinese Yuan
    SGD: 1.35,   // Singapore Dollar
    BTC: 0.000015, // Bitcoin (approx $66,000)
    XAU: 0.00041   // Gold Ounce (approx $2,420)
  };

  var ratesLastFetched = 0;

  // Fetch live exchange rates from open API with fallback
  async function fetchLiveWorldRates() {
    if (Date.now() - ratesLastFetched < 300000 && ratesLastFetched > 0) return; // cache 5 min
    try {
      var res = await fetch('https://open.er-api.com/v6/latest/USD', { cache: 'no-store' });
      if (res.ok) {
        var data = await res.json();
        if (data && data.rates) {
          Object.assign(liveRatesCache, data.rates);
          // Special assets if not present
          if (!data.rates.BTC && liveRatesCache.BTC) liveRatesCache.BTC = liveRatesCache.BTC;
          if (!data.rates.XAU && liveRatesCache.XAU) liveRatesCache.XAU = liveRatesCache.XAU;
          ratesLastFetched = Date.now();
        }
      }
    } catch (e) {
      console.warn('Live world currency rates fetch fallback active:', e.message);
    }
  }

  // Pre-fetch live rates
  fetchLiveWorldRates();

  var CURRENCY_OPTIONS = `
    <option value="KES">KES — Kenyan Shilling (KSh)</option>
    <option value="USD" selected>USD — US Dollar ($)</option>
    <option value="EUR">EUR — Euro (€)</option>
    <option value="GBP">GBP — British Pound (£)</option>
    <option value="JPY">JPY — Japanese Yen (¥)</option>
    <option value="AUD">AUD — Australian Dollar (A$)</option>
    <option value="CAD">CAD — Canadian Dollar (C$)</option>
    <option value="CHF">CHF — Swiss Franc (Fr)</option>
    <option value="NZD">NZD — New Zealand Dollar (NZ$)</option>
    <option value="ZAR">ZAR — South African Rand (R)</option>
    <option value="UGX">UGX — Ugandan Shilling (USh)</option>
    <option value="TZS">TZS — Tanzanian Shilling (TSh)</option>
    <option value="RWF">RWF — Rwandan Franc (RF)</option>
    <option value="NGN">NGN — Nigerian Naira (₦)</option>
    <option value="GHS">GHS — Ghanaian Cedi (GH₵)</option>
    <option value="EGP">EGP — Egyptian Pound (E£)</option>
    <option value="AED">AED — UAE Dirham (AED)</option>
    <option value="SAR">SAR — Saudi Riyal (SR)</option>
    <option value="INR">INR — Indian Rupee (₹)</option>
    <option value="CNY">CNY — Chinese Yuan (¥)</option>
    <option value="SGD">SGD — Singapore Dollar (S$)</option>
    <option value="BTC">BTC — Bitcoin (₿)</option>
    <option value="XAU">XAU — Gold (Ounce)</option>
  `;

  var CALC_HTML = {
    pip: {
      title: 'Pip Calculator',
      body: '<div class="mo-calc-field"><label>Currency Pair</label><select id="calcPair"><option value="EURUSD">EUR/USD</option><option value="GBPUSD">GBP/USD</option><option value="USDJPY">USD/JPY</option><option value="XAUUSD">XAU/USD</option></select></div><div class="mo-calc-field"><label>Trade Size (lots)</label><input type="number" id="calcLots" value="1" step="0.01" min="0.01"></div><div class="mo-calc-field"><label>Account Currency</label><select id="calcAccCur"><option value="USD">USD</option><option value="EUR">EUR</option><option value="GBP">GBP</option><option value="KES">KES (KSh)</option></select></div><button class="mo-promo__cta" onclick="calcPip()">Calculate</button><div class="mo-calc-result" id="calcResult" style="display:none"><div class="mo-calc-result__label">Pip Value</div><div class="mo-calc-result__value" id="calcResultValue">--</div></div>'
    },
    lot: {
      title: 'Lot Size Calculator',
      body: '<div class="mo-calc-field"><label>Account Balance ($)</label><input type="number" id="calcBalance" value="1000" step="100" min="100"></div><div class="mo-calc-field"><label>Risk (%)</label><input type="number" id="calcRiskPct" value="2" step="0.5" min="0.1" max="100"></div><div class="mo-calc-field"><label>Stop Loss (pips)</label><input type="number" id="calcSlPips" value="50" step="5" min="1"></div><div class="mo-calc-field"><label>Currency Pair</label><select id="calcPair2"><option value="EURUSD">EUR/USD</option><option value="GBPUSD">GBP/USD</option><option value="USDJPY">USD/JPY</option><option value="XAUUSD">XAU/USD</option></select></div><button class="mo-promo__cta" onclick="calcLot()">Calculate</button><div class="mo-calc-result" id="calcResult" style="display:none"><div class="mo-calc-result__label">Recommended Lot Size</div><div class="mo-calc-result__value" id="calcResultValue">--</div></div>'
    },
    margin: {
      title: 'Margin Calculator',
      body: '<div class="mo-calc-field"><label>Currency Pair</label><select id="calcPair"><option value="EURUSD">EUR/USD</option><option value="GBPUSD">GBP/USD</option><option value="USDJPY">USD/JPY</option><option value="XAUUSD">XAU/USD</option></select></div><div class="mo-calc-field"><label>Trade Size (lots)</label><input type="number" id="calcLots" value="1" step="0.01" min="0.01"></div><div class="mo-calc-field"><label>Leverage</label><select id="calcLeverage"><option value="500">1:500</option><option value="200">1:200</option><option value="100">1:100</option><option value="50">1:50</option><option value="30">1:30</option></select></div><button class="mo-promo__cta" onclick="calcMargin()">Calculate</button><div class="mo-calc-result" id="calcResult" style="display:none"><div class="mo-calc-result__label">Required Margin</div><div class="mo-calc-result__value" id="calcResultValue">--</div></div>'
    },
    position: {
      title: 'Position Size Calculator',
      body: '<div class="mo-calc-field"><label>Account Balance ($)</label><input type="number" id="calcBalance" value="1000" step="100" min="100"></div><div class="mo-calc-field"><label>Risk Amount ($)</label><input type="number" id="calcRiskAmt" value="20" step="5" min="1"></div><div class="mo-calc-field"><label>Stop Loss (pips)</label><input type="number" id="calcSlPips" value="50" step="5" min="1"></div><div class="mo-calc-field"><label>Currency Pair</label><select id="calcPair"><option value="EURUSD">EUR/USD</option><option value="GBPUSD">GBP/USD</option><option value="USDJPY">USD/JPY</option><option value="XAUUSD">XAU/USD</option></select></div><button class="mo-promo__cta" onclick="calcPosition()">Calculate</button><div class="mo-calc-result" id="calcResult" style="display:none"><div class="mo-calc-result__label">Position Size (lots)</div><div class="mo-calc-result__value" id="calcResultValue">--</div></div>'
    },
    profit: {
      title: 'Profit Calculator',
      body: '<div class="mo-calc-field"><label>Currency Pair</label><select id="calcPair"><option value="EURUSD">EUR/USD</option><option value="GBPUSD">GBP/USD</option><option value="USDJPY">USD/JPY</option><option value="XAUUSD">XAU/USD</option></select></div><div class="mo-calc-field"><label>Trade Size (lots)</label><input type="number" id="calcLots" value="1" step="0.01" min="0.01"></div><div class="mo-calc-field"><label>Entry Price</label><input type="number" id="calcEntry" value="1.1000" step="0.0001"></div><div class="mo-calc-field"><label>Exit Price</label><input type="number" id="calcExit" value="1.1050" step="0.0001"></div><div class="mo-calc-field"><label>Direction</label><select id="calcDir"><option value="long">Long (Buy)</option><option value="short">Short (Sell)</option></select></div><button class="mo-promo__cta" onclick="calcProfit()">Calculate</button><div class="mo-calc-result" id="calcResult" style="display:none"><div class="mo-calc-result__label">Profit / Loss</div><div class="mo-calc-result__value" id="calcResultValue">--</div></div>'
    },
    converter: {
      title: 'Live World Currency Converter',
      body: '<div class="mo-calc-field"><label>Amount</label><input type="number" id="convAmount" value="100" step="1" min="0.01"></div>'
          + '<div class="mo-calc-field"><label>From Currency</label><select id="convFrom">' + CURRENCY_OPTIONS + '</select></div>'
          + '<div class="mo-calc-field"><label>To Currency</label><select id="convTo">' + CURRENCY_OPTIONS.replace('value="KES"', 'value="KES" selected').replace('value="USD" selected', 'value="USD"') + '</select></div>'
          + '<button class="mo-promo__cta" onclick="convertCurrency()">Convert Live</button>'
          + '<div class="mo-calc-result" id="calcResult" style="display:none">'
          + '<div class="mo-calc-result__label" id="convRateLabel">Live Converted Value</div>'
          + '<div class="mo-calc-result__value" id="calcResultValue">--</div>'
          + '<div style="font-size:0.72rem;color:#16C784;margin-top:6px;font-weight:600" id="convSubText">Updated with current world market rates</div>'
          + '</div>'
    }
  };

  window.openCalc = function(type) {
    var c = CALC_HTML[type];
    if (!c) return;
    var modal = document.getElementById('calcModal');
    var title = document.getElementById('calcTitle');
    var body = document.getElementById('calcBody');
    if (!modal || !title || !body) return;
    title.textContent = c.title;
    body.innerHTML = c.body;
    modal.classList.add('open');

    if (type === 'converter') {
      fetchLiveWorldRates().then(function() {
        convertCurrency();
      });
    }
  };

  window.closeCalc = function() {
    var modal = document.getElementById('calcModal');
    if (modal) modal.classList.remove('open');
  };

  window.convertCurrency = function() {
    var amt = parseFloat(document.getElementById('convAmount').value) || 0;
    var from = document.getElementById('convFrom').value;
    var to = document.getElementById('convTo').value;

    var fromRateUSD = liveRatesCache[from] || 1;
    var toRateUSD = liveRatesCache[to] || 1;

    // Convert from -> USD -> to
    var amtInUSD = amt / fromRateUSD;
    var resultAmt = amtInUSD * toRateUSD;

    // Rate ratio: 1 FROM = X TO
    var singleRate = (1 / fromRateUSD) * toRateUSD;

    var fmtDec = (to === 'BTC' || to === 'XAU') ? 6 : (to === 'KES' || to === 'UGX' || to === 'TZS' || to === 'JPY') ? 2 : 4;
    var formattedVal = resultAmt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: fmtDec });

    var formattedRate = singleRate.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: fmtDec });

    var resLabel = document.getElementById('convRateLabel');
    if (resLabel) resLabel.textContent = `1 ${from} = ${formattedRate} ${to}`;

    var subText = document.getElementById('convSubText');
    if (subText) subText.textContent = `Live exchange rate updated for ${from} / ${to}`;

    showCalcResult(`${formattedVal} ${to}`);
  };

  window.calcPip = function() {
    var pair = document.getElementById('calcPair').value;
    var lots = parseFloat(document.getElementById('calcLots').value) || 1;
    var accCur = document.getElementById('calcAccCur').value;
    var pipVal;
    if (pair === 'USDJPY') pipVal = 0.01 / 110 * lots * 100000;
    else pipVal = 0.0001 * lots * 100000;
    if (accCur === 'KES') pipVal = pipVal * (liveRatesCache.KES || 129.5);
    else if (accCur !== 'USD' && pair.indexOf('USD') === 0) pipVal = pipVal / 1.1;
    showCalcResult(pipVal.toFixed(2) + ' ' + accCur);
  };

  window.calcLot = function() {
    var balance = parseFloat(document.getElementById('calcBalance').value) || 1000;
    var riskPct = parseFloat(document.getElementById('calcRiskPct').value) || 2;
    var slPips = parseFloat(document.getElementById('calcSlPips').value) || 50;
    var riskAmt = balance * riskPct / 100;
    var pipVal = 10;
    var lots = riskAmt / (slPips * pipVal);
    showCalcResult(lots.toFixed(2) + ' lots');
  };

  window.calcMargin = function() {
    var pair = document.getElementById('calcPair').value;
    var lots = parseFloat(document.getElementById('calcLots').value) || 1;
    var leverage = parseInt(document.getElementById('calcLeverage').value) || 100;
    var notional = lots * 100000;
    var margin = notional / leverage;
    showCalcResult('$' + margin.toFixed(2));
  };

  window.calcPosition = function() {
    var balance = parseFloat(document.getElementById('calcBalance').value) || 1000;
    var riskAmt = parseFloat(document.getElementById('calcRiskAmt').value) || 20;
    var slPips = parseFloat(document.getElementById('calcSlPips').value) || 50;
    var pipVal = 10;
    var lots = riskAmt / (slPips * pipVal);
    showCalcResult(lots.toFixed(2) + ' lots');
  };

  window.calcProfit = function() {
    var lots = parseFloat(document.getElementById('calcLots').value) || 1;
    var entry = parseFloat(document.getElementById('calcEntry').value) || 1;
    var exit = parseFloat(document.getElementById('calcExit').value) || 1;
    var dir = document.getElementById('calcDir').value;
    var diff = dir === 'long' ? (exit - entry) : (entry - exit);
    var pips = diff / 0.0001;
    var profit = pips * 10 * lots;
    var sign = profit >= 0 ? '+' : '';
    showCalcResult(sign + '$' + profit.toFixed(2));
  };

  function showCalcResult(val) {
    var el = document.getElementById('calcResult');
    if (!el) return;
    el.style.display = 'block';
    var resVal = document.getElementById('calcResultValue');
    if (resVal) resVal.textContent = val;
  }

  document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('calcModal');
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === this) closeCalc();
      });
    }
  });
})();
