<?php require_once __DIR__ . '/config.php'; sb_admin_required(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="../css/theme-light.css?v=1">
<script src="../js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Promotions | BM Forex Hub Admin</title>
<link rel="icon" type="image/png" href="../BM-ForexHub-Logo-Circle.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Inter:wght@400;500;600&display=swap">
<link rel="stylesheet" href="../css/motion.css?v=<?= filemtime('../css/motion.css') ?>">
<link rel="stylesheet" href="css/admin.css?v=<?= filemtime('css/admin.css') ?>">
<link rel="stylesheet" href="css/admin-mobile.css?v=<?= filemtime('css/admin-mobile.css') ?>">
<style>
body{font-family:'Inter',sans-serif;background:#0B0F14;color:#fff;margin:0;padding:24px}
h1{font-family:'Space Grotesk',sans-serif;font-size:1.3rem}
.back-link{color:#1677FF;text-decoration:none;font-size:0.85rem;display:inline-flex;align-items:center;gap:4px;margin-bottom:16px}
.header-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.add-btn{background:#1677FF;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;font-size:0.85rem}
.add-btn:hover{background:#2F80FF}
input,select,textarea{width:100%;padding:10px 12px;background:#202B3A;border:1px solid #283548;border-radius:8px;color:#fff;font-size:0.85rem;outline:none;box-sizing:border-box}
input:focus,select:focus{border-color:#1677FF}
label{display:block;font-size:0.78rem;color:#B8C3D1;margin-bottom:4px;font-weight:500}
.form-group{margin-bottom:14px}
table{width:100%;border-collapse:collapse;margin-top:12px}
th{text-align:left;font-size:0.7rem;text-transform:uppercase;color:#7F8B99;padding:10px 12px;border-bottom:1px solid #283548;letter-spacing:0.04em}
td{padding:10px 12px;border-bottom:1px solid rgba(40,53,72,.4);font-size:0.82rem;color:#E2E8F0}
tr:hover td{background:rgba(22,119,255,.04)}
.action-btn{background:transparent;border:1px solid #283548;border-radius:6px;color:#B8C3D1;padding:4px 10px;cursor:pointer;font-size:0.72rem;margin-right:4px}
.action-btn:hover{background:rgba(22,119,255,.1);color:#fff;border-color:#1677FF}
.action-btn.danger:hover{background:rgba(246,70,93,.1);border-color:#F6465D;color:#F6465D}
.status-badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:0.65rem;font-weight:600;text-transform:uppercase}
.status-badge.active{background:rgba(22,199,132,.15);color:#16C784}
.status-badge.inactive{background:rgba(100,116,139,.15);color:#7F8B99}
.status-badge.expired{background:rgba(246,70,93,.15);color:#F6465D}
.modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.7);z-index:500;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
.modal-overlay.open{display:flex}
.modal{background:#151D29;border:1px solid #283548;border-radius:14px;padding:28px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto}
.modal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.modal-header h2{font-family:'Space Grotesk',sans-serif;font-size:1rem;margin:0}
.modal-close{background:none;border:none;color:#7F8B99;font-size:1.3rem;cursor:pointer}
.btn-primary{background:#1677FF;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-weight:600;cursor:pointer;width:100%;font-size:0.88rem}
.btn-primary:hover{background:#2F80FF}
.table-empty{text-align:center;padding:40px;color:#7F8B99;font-size:0.85rem}
.search-box{display:flex;gap:8px;align-items:center}
.search-box input{width:240px}
@media(max-width:640px){body{padding:14px}table{font-size:0.75rem}th,td{padding:6px 8px}}
</style>
</head>
<body>
<a href="index.php" class="back-link">&larr; Back to Dashboard</a>
<div class="header-bar">
  <h1>Promotions</h1>
  <button class="add-btn" onclick="openModal()">+ Add Promotion</button>
</div>
<div class="search-box" style="margin-bottom:16px">
  <input type="text" id="searchInput" placeholder="Search promotions..." oninput="loadData()">
</div>
<table>
  <thead><tr><th>Title</th><th>Status</th><th>Start</th><th>End</th><th>Actions</th></tr></thead>
  <tbody id="tableBody"><tr><td colspan="5" class="table-empty">Loading...</td></tr></tbody>
</table>

<div class="modal-overlay" id="modal">
  <div class="modal">
    <div class="modal-header">
      <h2 id="modalTitle">Add Promotion</h2>
      <button class="modal-close" onclick="closeModal()">&times;</button>
    </div>
    <form onsubmit="save(event)">
      <input type="hidden" id="editId">
      <div class="form-group"><label>Title</label><input type="text" id="fTitle" required></div>
      <div class="form-group"><label>Description</label><textarea id="fDesc" style="min-height:60px"></textarea></div>
      <div class="form-group"><label>Banner Image URL</label><input type="text" id="fBanner" placeholder="https://..."></div>
      <div class="form-group"><label>Button Text</label><input type="text" id="fBtn" placeholder="e.g. Learn More"></div>
      <div class="form-group"><label>Link URL</label><input type="text" id="fLink" placeholder="https://..."></div>
      <div class="form-group"><label>Status</label><select id="fStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
      <div class="form-group"><label>Start Date</label><input type="date" id="fStart"></div>
      <div class="form-group"><label>End Date</label><input type="date" id="fEnd"></div>
      <button type="submit" class="btn-primary" id="saveBtn">Save Promotion</button>
    </form>
  </div>
</div>

<script>
var API = '../api/promotions.php';
function loadData() {
  var s = document.getElementById('searchInput').value;
  fetch(API + (s ? '?search=' + encodeURIComponent(s) : ''))
    .then(function(r){return r.json()}).then(function(d){
      var tbody = document.getElementById('tableBody');
      if (!d.data || !d.data.length) { tbody.innerHTML = '<tr><td colspan="5" class="table-empty">No promotions found.</td></tr>'; return; }
      var html = '';
      d.data.forEach(function(p){
        var start = p.start_date ? new Date(p.start_date).toLocaleDateString('en-GB') : '--';
        var end = p.end_date ? new Date(p.end_date).toLocaleDateString('en-GB') : '--';
        html += '<tr><td><strong>' + esc(p.title) + '</strong></td><td data-label="Status"><span class="status-badge ' + p.status + '">' + p.status + '</span></td><td data-label="Start">' + start + '</td><td data-label="End">' + end + '</td><td data-label="Actions"><button class="action-btn" onclick="editPromo(\'' + p.id + '\')">Edit</button><button class="action-btn danger" onclick="delPromo(\'' + p.id + '\')">Delete</button></td></tr>';
      });
      tbody.innerHTML = html;
    });
}
function esc(s){return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function openModal(data) {
  document.getElementById('modalTitle').textContent = data ? 'Edit Promotion' : 'Add Promotion';
  document.getElementById('editId').value = data ? data.id : '';
  document.getElementById('fTitle').value = data ? (data.title||'') : '';
  document.getElementById('fDesc').value = data ? (data.description||'') : '';
  document.getElementById('fBanner').value = data ? (data.banner||'') : '';
  document.getElementById('fBtn').value = data ? (data.button_text||'') : '';
  document.getElementById('fLink').value = data ? (data.link||'') : '';
  document.getElementById('fStatus').value = data ? (data.status||'active') : 'active';
  document.getElementById('fStart').value = data && data.start_date ? data.start_date.slice(0,10) : '';
  document.getElementById('fEnd').value = data && data.end_date ? data.end_date.slice(0,10) : '';
  document.getElementById('saveBtn').textContent = data ? 'Update Promotion' : 'Save Promotion';
  document.getElementById('modal').classList.add('open');
}
function closeModal() { document.getElementById('modal').classList.remove('open'); }
function save(e) {
  e.preventDefault();
  var body = {
    title: document.getElementById('fTitle').value,
    description: document.getElementById('fDesc').value,
    banner: document.getElementById('fBanner').value,
    button_text: document.getElementById('fBtn').value,
    link: document.getElementById('fLink').value,
    status: document.getElementById('fStatus').value,
    start_date: document.getElementById('fStart').value || null,
    end_date: document.getElementById('fEnd').value || null
  };
  var id = document.getElementById('editId').value;
  var url = API;
  var method = 'POST';
  if (id) { body.id = id; method = 'PATCH'; }
  fetch(url, {method:method, headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)})
    .then(function(r){return r.json()}).then(function(d){
      if (d.success) { closeModal(); loadData(); } else alert(d.error||'Failed.');
    });
}
function editPromo(id) {
  fetch(API).then(function(r){return r.json()}).then(function(d){
    if (!d.data) return;
    var item = d.data.find(function(p){return p.id===id});
    if (item) openModal(item);
  });
}
function delPromo(id) {
  if (!confirm('Delete this promotion?')) return;
  fetch(API + '?id=' + id, {method:'DELETE'}).then(function(r){return r.json()}).then(function(d){
    if (d.success) loadData(); else alert(d.error||'Failed.');
  });
}
loadData();
document.getElementById('modal').addEventListener('click', function(e){if(e.target===this)closeModal();});
</script>
<script src="../js/motion.js" defer></script>
</body>
</html>
