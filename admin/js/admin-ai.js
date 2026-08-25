/* ============================================================
   BM Forex Hub — Admin AI Knowledge Base / Settings JS
   ============================================================ */
(function(){
  'use strict';

  var SB_URL = 'https://iwoytmcxmbhmmbrbpzvf.supabase.co';
  var SB_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4Mzk4MzU2MSwiZXhwIjoyMDk5NTU5NTYxfQ.1ExI-o8zwDkUIH_loQPC_ZJhJaDgrB6gSf1KgAdigCU';

  function sbHeaders(){ return { 'apikey': SB_KEY, 'Authorization': 'Bearer ' + SB_KEY, 'Content-Type': 'application/json', 'Prefer': 'return=representation' }; }

  /* ── AI Knowledge Base CRUD ── */
  var addKbBtn = document.getElementById('aiAddKbBtn');
  if (addKbBtn) {
    addKbBtn.addEventListener('click', function(){
      document.getElementById('aiKbModalTitle').textContent = 'Add Knowledge Base Entry';
      document.getElementById('aiKbId').value = '';
      document.getElementById('aiKbQuestion').value = '';
      document.getElementById('aiKbAnswer').value = '';
      document.getElementById('aiKbKeywords').value = '';
      document.getElementById('aiKbCategory').value = 'general';
      document.getElementById('aiKbPublished').checked = true;
      openModal('aiKbModal');
    });
  }

  var kbForm = document.getElementById('aiKbForm');
  if (kbForm) {
    kbForm.addEventListener('submit', function(e){
      e.preventDefault();
      var id = document.getElementById('aiKbId').value;
      var body = {
        category: document.getElementById('aiKbCategory').value,
        question: document.getElementById('aiKbQuestion').value,
        answer: document.getElementById('aiKbAnswer').value,
        keywords: document.getElementById('aiKbKeywords').value,
        published: document.getElementById('aiKbPublished').checked
      };
      var url = SB_URL + '/rest/v1/ai_knowledge_base';
      var method = id ? 'PATCH' : 'POST';
      if (id) url += '?id=eq.' + id;
      fetch(url, { method: method, headers: sbHeaders(), body: JSON.stringify(body) })
        .then(function(r){ return r.json(); })
        .then(function(d){
          closeModal('aiKbModal');
          if (typeof loadAiKb === 'function') loadAiKb();
          else location.reload();
        })
        .catch(function(err){ alert('Error: ' + err.message); });
    });
  }

  /* ── AI Settings Form ── */
  var aiSettingsForm = document.getElementById('aiSettingsForm');
  if (aiSettingsForm) {
    aiSettingsForm.addEventListener('submit', function(e){
      e.preventDefault();
      var body = {
        enabled: document.getElementById('aiSetEnabled').checked,
        welcome_message: document.getElementById('aiSetWelcomeMsg').value,
        suggested_questions: document.getElementById('aiSetSuggestedQ').value.split('\n').filter(function(l){return l.trim();}),
        personality: document.getElementById('aiSetPersonality').value,
        office_hours: document.getElementById('aiSetOfficeHours').value,
        fallback_message: document.getElementById('aiSetFallbackMsg').value,
        escalation_message: document.getElementById('aiSetEscalationMsg').value,
        whatsapp_url: document.getElementById('aiSetWhatsappUrl').value
      };
      // Upsert — try update first, insert if none exists
      fetch(SB_URL + '/rest/v1/ai_settings?limit=1', { headers: { 'apikey': SB_KEY, 'Authorization': 'Bearer ' + SB_KEY } })
        .then(function(r){ return r.json(); })
        .then(function(existing){
          var method = (Array.isArray(existing) && existing.length) ? 'PATCH' : 'POST';
          var url = SB_URL + '/rest/v1/ai_settings';
          if (method === 'PATCH') url += '?id=eq.' + existing[0].id;
          return fetch(url, { method: method, headers: sbHeaders(), body: JSON.stringify(body) });
        })
        .then(function(r){ return r.json(); })
        .then(function(){ alert('AI settings saved successfully.'); })
        .catch(function(err){ alert('Error saving AI settings: ' + err.message); });
    });
  }

  /* ── AI Conversations Export ── */
  var exportBtn = document.getElementById('aiLogsExportBtn');
  if (exportBtn) {
    exportBtn.addEventListener('click', function(){
      fetch(SB_URL + '/rest/v1/ai_conversations?select=*&order=created_at.desc&limit=1000', {
        headers: { 'apikey': SB_KEY, 'Authorization': 'Bearer ' + SB_KEY }
      })
      .then(function(r){ return r.json(); })
      .then(function(data){
        if (!Array.isArray(data) || !data.length) { alert('No conversations to export.'); return; }
        var csv = 'Session ID,User ID,Message,Response,Status,IP,Timestamp\n';
        data.forEach(function(c){
          csv += '"'+(c.session_id||'')+'","'+(c.user_id||'')+'","'+(c.user_message||'').replace(/"/g,'""')+'","'+(c.ai_response||'').replace(/"/g,'""')+'","'+(c.escalated?'Escalated':'AI Handled')+'","'+(c.ip_address||'')+'","'+(c.created_at||'')+'"\n';
        });
        var blob = new Blob([csv], { type: 'text/csv' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'ai_conversations_export.csv';
        a.click();
      });
    });
  }

  /* ── AI KB Category Filter ── */
  var kbFilter = document.getElementById('aiKbCategoryFilter');
  if (kbFilter) {
    kbFilter.addEventListener('change', function(){
      var cat = this.value;
      var rows = document.querySelectorAll('#aiKnowledgeTableBody tr');
      rows.forEach(function(row){
        if (cat === 'all') { row.style.display = ''; return; }
        var cell = row.querySelector('td');
        row.style.display = cell && cell.textContent.trim().toLowerCase() === cat ? '' : 'none';
      });
    });
  }

})();
