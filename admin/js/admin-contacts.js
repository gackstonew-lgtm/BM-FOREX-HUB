/* ============================================================
   BM Forex Hub — Admin Contacts Directory JS
   ============================================================ */
(function(){
  'use strict';

  /* ── Select All Checkbox ── */
  var masterChk = document.getElementById('contactsMasterChk');
  if (masterChk) {
    masterChk.addEventListener('change', function(){
      var checked = this.checked;
      document.querySelectorAll('.contact-chk').forEach(function(c){ c.checked = checked; });
      updateBulkBar();
    });
  }
  document.addEventListener('change', function(e){
    if (e.target.classList.contains('contact-chk')) updateBulkBar();
  });
  function updateBulkBar() {
    var selected = document.querySelectorAll('.contact-chk:checked');
    var bar = document.getElementById('contactsBulkBar');
    var count = document.getElementById('contactsBulkCount');
    if (bar) bar.style.display = selected.length > 0 ? 'flex' : 'none';
    if (count) count.textContent = selected.length + ' Users Selected';
  }

  /* ── Export Contacts ── */
  var exportBtn = document.getElementById('contactsTriggerExportBtn');
  if (exportBtn) {
    exportBtn.addEventListener('click', function(){
      var count = document.querySelectorAll('.contact-chk:checked').length || 0;
      document.getElementById('exportRecordCount').textContent = count || 'all';
      openModal('contactsExportModal');
    });
  }

  var executeExportBtn = document.getElementById('executeExportBtn');
  if (executeExportBtn) {
    executeExportBtn.addEventListener('click', function(){
      var format = document.getElementById('exportFormatSelect').value;
      var fields = [];
      document.querySelectorAll('.export-field-chk:checked').forEach(function(f){ fields.push(f.value); });
      if (!fields.length) { alert('Please select at least one field.'); return; }
      // Get selected contacts or all
      var selectedIds = [];
      document.querySelectorAll('.contact-chk:checked').forEach(function(c){ selectedIds.push(c.value); });
      alert('Export feature requires a server endpoint. Format: ' + format + ', Fields: ' + fields.join(', '));
      closeModal('contactsExportModal');
    });
  }

  /* ── Audit Logs ── */
  var auditBtn = document.getElementById('contactsAuditBtn');
  if (auditBtn) {
    auditBtn.addEventListener('click', function(){
      openModal('contactsAuditModal');
      // Load audit logs from admin_audit_logs table
      var SB_URL = 'https://iwoytmcxmbhmmbrbpzvf.supabase.co';
      var SB_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4Mzk4MzU2MSwiZXhwIjoyMDk5NTU5NTYxfQ.1ExI-o8zwDkUIH_loQPC_ZJhJaDgrB6gSf1KgAdigCU';
      fetch(SB_URL + '/rest/v1/admin_audit_logs?select=*&order=created_at.desc&limit=50', {
        headers: { 'apikey': SB_KEY, 'Authorization': 'Bearer ' + SB_KEY }
      }).then(function(r){ return r.json(); }).then(function(data){
        var tbody = document.getElementById('auditLogsTableBody');
        if (!Array.isArray(data) || !data.length) { tbody.innerHTML = '<tr><td colspan="5" class="table-empty">No audit logs found.</td></tr>'; return; }
        tbody.innerHTML = data.map(function(l){
          return '<tr><td>'+esc(l.admin_user||l.admin_id||'--')+'</td><td>'+esc(l.action||'--')+'</td><td>'+esc(String(l.details||'--'))+'</td><td>'+esc(l.ip_address||'--')+'</td><td>'+(l.created_at?new Date(l.created_at).toLocaleString():'--')+'</td></tr>';
        }).join('');
      }).catch(function(){});
    });
  }

  function esc(s){ return (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

})();
