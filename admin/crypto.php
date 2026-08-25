<?php
require_once __DIR__ . '/config.php';
sb_admin_required();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="../css/theme-light.css?v=1">
<script src="../js/theme.js" defer></script>
<meta charset="UTF-8">
<title>Crypto Management | BM Forex Hub Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/motion.css?v=<?= filemtime('../css/motion.css') ?>">
<link rel="stylesheet" href="css/admin-mobile.css?v=<?= filemtime('css/admin-mobile.css') ?>">
<style>
  body{background:#0B0F14;color:#FFFFFF;font-family:Inter,Helvetica,sans-serif;margin:0;padding:24px}
  h1{font-size:20px;margin-bottom:4px}
  p.sub{color:#B8C3D1;font-size:13px;margin-top:0}
  .card{background:#151D29;border:1px solid #283548;border-radius:10px;padding:18px;margin-bottom:20px}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th,td{text-align:left;padding:8px 10px;border-bottom:1px solid #283548}
  th{color:#7F8B99;font-weight:600;text-transform:uppercase;font-size:11px}
  .badge{padding:3px 8px;border-radius:5px;font-size:11px;font-weight:600}
  .b-completed{background:rgba(22,199,132,.15);color:#16C784}
  .b-pending{background:rgba(22,119,255,.15);color:#2F80FF}
  .b-failed{background:rgba(246,70,93,.15);color:#F6465D}
  .b-other{background:rgba(184,195,209,.15);color:#B8C3D1}
  label{display:flex;align-items:center;gap:8px;font-size:13px;padding:6px 0}
  a.back{color:#2F80FF;text-decoration:none;font-size:13px}
</style>
</head>
<body>
<a class="back" href="index.php">&larr; Back to Admin Dashboard</a>
<h1>Crypto Buy/Sell Management</h1>
<p class="sub">Manage enabled assets and monitor crypto transactions. Fees are configured via server .env.</p>

<div class="card" id="capabilityCard">
  <h3 style="margin-top:0">Settlement Capability</h3>
  <p class="sub" id="capabilitySummary">Loading…</p>
  <table id="capabilityTable"><thead><tr><th>Provider</th><th>Capability</th><th>Status</th></tr></thead><tbody></tbody></table>
</div>

<div class="card">
  <h3 style="margin-top:0">Asset Configuration</h3>
  <table id="assetTable"><thead><tr><th>Symbol</th><th>Name</th><th>Enabled</th><th>Buy</th><th>Sell</th></tr></thead><tbody></tbody></table>
</div>

<div class="card">
  <h3 style="margin-top:0">Recent Crypto Transactions</h3>
  <table id="txnTable">
    <thead><tr><th>Date</th><th>Reference</th><th>Type</th><th>Crypto</th><th>Network</th><th>Fiat</th><th>Crypto Amt</th><th>Status</th><th>Note</th></tr></thead>
    <tbody></tbody>
  </table>
</div>

<script>
function badgeClass(status){
  if(status==='COMPLETED') return 'b-completed';
  if(status==='FAILED'||status==='CANCELLED') return 'b-failed';
  if(status==='PENDING_PAYMENT'||status==='PAYMENT_PROCESSING'||status==='PAYMENT_CONFIRMED'||status==='SETTLEMENT_PENDING') return 'b-pending';
  return 'b-other';
}

async function loadAssets(){
  const res = await fetch('../api/crypto.php?action=asset_config');
  const data = await res.json();
  const tbody = document.querySelector('#assetTable tbody');
  tbody.innerHTML = '';
  (data.assets||[]).forEach(a=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${a.symbol}</td><td data-label="Name">${a.name}</td>
      <td data-label="Enabled"><input type="checkbox" data-symbol="${a.symbol}" data-field="enabled" ${a.enabled?'checked':''}></td>
      <td data-label="Buy"><input type="checkbox" data-symbol="${a.symbol}" data-field="buy_enabled" ${a.buy_enabled?'checked':''}></td>
      <td data-label="Sell"><input type="checkbox" data-symbol="${a.symbol}" data-field="sell_enabled" ${a.sell_enabled?'checked':''}></td>`;
    tbody.appendChild(tr);
  });
  tbody.querySelectorAll('input[type=checkbox]').forEach(cb=>{
    cb.addEventListener('change', async ()=>{
      await fetch('../api/crypto.php', {method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'update_asset', symbol:cb.dataset.symbol, [cb.dataset.field]: cb.checked})});
    });
  });
}

async function loadTransactions(){
  const res = await fetch('../api/crypto.php?action=transactions&limit=50');
  const data = await res.json();
  const tbody = document.querySelector('#txnTable tbody');
  tbody.innerHTML = '';
  (data.transactions||[]).forEach(t=>{
    const tr = document.createElement('tr');
    const date = new Date(t.created_at).toLocaleString();
    const note = t.failure_reason || t.blockchain_tx_hash || '';
    tr.innerHTML = `<td>${date}</td><td data-label="Reference">${t.transaction_reference}</td><td data-label="Type">${t.type}</td>
      <td data-label="Crypto">${t.crypto_symbol}</td><td data-label="Network">${t.network || '—'}</td><td data-label="Fiat">${t.fiat_currency} ${Number(t.fiat_amount).toLocaleString()}</td>
      <td data-label="Crypto Amt">${Number(t.crypto_amount).toFixed(6)}</td>
      <td data-label="Status"><span class="badge ${badgeClass(t.status)}">${t.status}</span></td>
      <td data-label="Note" style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${note}">${note}</td>`;
    tbody.appendChild(tr);
  });
}

async function loadCapability(){
  const res = await fetch('../api/crypto-capability.php');
  const data = await res.json();
  document.querySelector('#capabilitySummary').textContent = data.summary || 'Unknown';
  const tbody = document.querySelector('#capabilityTable tbody');
  tbody.innerHTML = '';
  const providers = data.providers || {};
  Object.entries(providers).forEach(([providerName, providerData]) => {
    Object.entries(providerData.capabilities || {}).forEach(([name, enabled]) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${providerName}</td><td data-label="Capability">${name.replace(/_/g,' ')}</td>
        <td data-label="Status"><span class="badge ${enabled ? 'b-completed' : 'b-pending'}">${enabled ? 'Enabled' : 'Not available'}</span></td>`;
      tbody.appendChild(tr);
    });
  });
}

loadCapability();
loadAssets();
loadTransactions();
</script>
<script src="../js/motion.js" defer></script>
</body>
</html>
