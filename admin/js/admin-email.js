/* ============================================================
   BM Forex Hub — Admin Bulk Notifications / Email JS
   ============================================================ */
(function(){
  'use strict';

  /* ── Tab Switching ── */
  document.querySelectorAll('.email-tab').forEach(function(tab){
    tab.addEventListener('click', function(){
      document.querySelectorAll('.email-tab').forEach(function(t){ t.classList.remove('active'); });
      this.classList.add('active');
      var target = this.getAttribute('data-tab');
      var compose = document.getElementById('viewEmailCompose');
      var drafts = document.getElementById('viewEmailDrafts');
      var history = document.getElementById('viewEmailHistory');
      if (compose) compose.style.display = target === 'compose' ? '' : 'none';
      if (drafts) drafts.style.display = target === 'drafts' ? '' : 'none';
      if (history) history.style.display = target === 'history' ? '' : 'none';
    });
  });

  /* ── Rich Text Editor Toolbar ── */
  document.querySelectorAll('.editor-btn[data-cmd]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var cmd = this.getAttribute('data-cmd');
      var editor = document.getElementById('bulkEditor');
      if (!editor) return;
      editor.focus();
      if (cmd === 'createLink') {
        var url = prompt('Enter URL:');
        if (url) document.execCommand(cmd, false, url);
      } else if (cmd === 'insertImage') {
        var img = prompt('Enter image URL:');
        if (img) document.execCommand(cmd, false, img);
      } else {
        document.execCommand(cmd, false, null);
      }
    });
  });

  /* ── Emoji Picker (basic) ── */
  var emojiBtn = document.getElementById('editorInsertEmojiBtn');
  if (emojiBtn) {
    emojiBtn.addEventListener('click', function(){
      var emojis = ['😀','🔥','💰','📈','📉','✅','⚡','🎯','🚀','💎','📊','🔑','💪','🏆','📣','💸'];
      var pick = prompt('Choose emoji (copy-paste): ' + emojis.join(' '));
      if (pick) {
        var editor = document.getElementById('bulkEditor');
        if (editor) { editor.focus(); document.execCommand('insertText', false, pick); }
      }
    });
  }

  /* ── Table Insert ── */
  var tableBtn = document.getElementById('editorInsertTableBtn');
  if (tableBtn) {
    tableBtn.addEventListener('click', function(){
      var rows = prompt('Number of rows:', '3');
      var cols = prompt('Number of columns:', '3');
      if (!rows || !cols) return;
      var html = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;margin:12px 0;">';
      for (var r = 0; r < parseInt(rows); r++) {
        html += '<tr>';
        for (var c = 0; c < parseInt(cols); c++) {
          html += r === 0 ? '<th style="background:#f0f0f0;">Header '+(c+1)+'</th>' : '<td>&nbsp;</td>';
        }
        html += '</tr>';
      }
      html += '</table>';
      var editor = document.getElementById('bulkEditor');
      if (editor) { editor.focus(); document.execCommand('insertHTML', false, html); }
    });
  }

  /* ── Dynamic Tags Insert ── */
  document.querySelectorAll('.tag-badge').forEach(function(tag){
    tag.addEventListener('click', function(){
      var tagText = this.getAttribute('data-tag');
      var editor = document.getElementById('bulkEditor');
      if (editor && tagText) { editor.focus(); document.execCommand('insertText', false, tagText); }
    });
  });

  /* ── Email Preview ── */
  var previewBtn = document.getElementById('bulkPreviewBtn');
  if (previewBtn) {
    previewBtn.addEventListener('click', function(){
      var subject = document.getElementById('bulkSubject').value || 'No Subject';
      var body = document.getElementById('bulkEditor').innerHTML || '';
      var modal = document.getElementById('emailPreviewModal');
      if (!modal) return;
      var container = modal.querySelector('.email-preview-frame') || modal.querySelector('[id="emailPreviewFrameContainer"]');
      if (container) {
        container.innerHTML = '<div style="padding:20px;"><h3 style="margin-bottom:12px;">'+subject+'</h3><div>'+body+'</div></div>';
      }
      modal.classList.add('show');
    });
  }

  /* ── Recipient Group Selection ── */
  var recipientGroup = document.getElementById('bulkRecipientGroup');
  var selectedInfo = document.getElementById('selectedRecipientsInfo');
  if (recipientGroup) {
    recipientGroup.addEventListener('change', function(){
      if (this.value === 'selected' && selectedInfo) {
        selectedInfo.style.display = 'flex';
      } else if (selectedInfo) {
        selectedInfo.style.display = 'none';
      }
    });
  }

  /* ── Send Broadcast (stub — requires server-side endpoint) ── */
  var sendBtn = document.getElementById('bulkSendBtn');
  if (sendBtn) {
    sendBtn.addEventListener('click', function(){
      var subject = document.getElementById('bulkSubject').value;
      var body = document.getElementById('bulkEditor').innerHTML;
      if (!subject || !body) { alert('Please enter a subject and message.'); return; }
      var group = document.getElementById('bulkRecipientGroup').value;
      document.getElementById('confirmSubject').textContent = subject;
      document.getElementById('confirmGroup').textContent = group;
      openModal('bulkConfirmModal');
    });
  }

  /* ── Save Draft ── */
  var saveDraftBtn = document.getElementById('bulkSaveDraftBtn');
  if (saveDraftBtn) {
    saveDraftBtn.addEventListener('click', function(){
      var subject = document.getElementById('bulkSubject').value;
      var body = document.getElementById('bulkEditor').innerHTML;
      if (!subject) { alert('Please enter a subject.'); return; }
      alert('Draft saved. (Server endpoint required for persistence)');
    });
  }

})();
