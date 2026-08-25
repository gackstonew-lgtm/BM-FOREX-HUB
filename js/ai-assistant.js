/**
 * BM Forex Hub — Floating AI Assistant Frontend Component
 */
(function() {
  'use strict';

  if (window.BMAIAssistantLoaded) return;
  window.BMAIAssistantLoaded = true;

  var state = {
    isOpen: false,
    isMinimized: false,
    welcomeMessage: '',
    suggestedQuestions: [],
    whatsappUrl: 'https://wa.me/message/K5RM7MSWXBNPC1',
    hasInteracted: false
  };

  /* Helper to format time */
  function getFormattedTime() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    return hours + ':' + minutes + ' ' + ampm;
  }

  /* Escape HTML */
  function escapeHtml(str) {
    if (!str && str !== 0) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  /* Create DOM elements */
  function initDOM() {
    // 1. Floating Trigger Button
    var floatBtn = document.createElement('button');
    floatBtn.id = 'bmAiTriggerBtn';
    floatBtn.className = 'bm-ai-float';
    floatBtn.setAttribute('aria-label', 'Open BM Forex Hub AI Support Assistant');
    floatBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5L2 22l5.1-1.31C8.54 21.53 10.22 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>' +
      '<span class="bm-ai-badge"></span>';
    document.body.appendChild(floatBtn);

    // 2. AI Window Component
    var win = document.createElement('div');
    win.id = 'bmAiWindow';
    win.className = 'bm-ai-window';
    win.setAttribute('role', 'dialog');
    win.setAttribute('aria-label', 'BM Forex Hub AI Support Assistant Chat');
    win.innerHTML = '<div class="bm-ai-header">' +
      '<div class="bm-ai-header-info">' +
        '<img src="BM-ForexHub-Logo-Circle.png" alt="BM Forex Hub" class="bm-ai-avatar">' +
        '<div class="bm-ai-title-wrap">' +
          '<span class="bm-ai-title">BM Forex Hub AI</span>' +
          '<span class="bm-ai-status"><span class="bm-ai-status-dot"></span>Online Support</span>' +
        '</div>' +
      '</div>' +
      '<div class="bm-ai-header-controls">' +
        '<button class="bm-ai-btn-ctrl" id="bmAiMinBtn" title="Minimize">—</button>' +
        '<button class="bm-ai-btn-ctrl" id="bmAiCloseBtn" title="Close">&times;</button>' +
      '</div>' +
    '</div>' +
    '<div class="bm-ai-messages" id="bmAiMessages"></div>' +
    '<form class="bm-ai-footer" id="bmAiForm">' +
      '<input type="text" class="bm-ai-input" id="bmAiInputText" placeholder="Ask about BM Forex Hub, SMC, ICT, Forex..." autocomplete="off">' +
      '<button type="submit" class="bm-ai-send-btn" id="bmAiSendBtn" aria-label="Send Message">' +
        '<svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>' +
      '</button>' +
    '</form>';
    document.body.appendChild(win);

    // Events
    floatBtn.addEventListener('click', toggleChat);
    document.getElementById('bmAiCloseBtn').addEventListener('click', closeChat);
    document.getElementById('bmAiMinBtn').addEventListener('click', minimizeChat);
    document.getElementById('bmAiForm').addEventListener('submit', handleSendMessage);
  }

  function toggleChat() {
    if (state.isOpen) {
      closeChat();
    } else {
      openChat();
    }
  }

  function openChat() {
    var win = document.getElementById('bmAiWindow');
    if (!win) return;
    win.classList.remove('minimized');
    win.classList.add('open');
    state.isOpen = true;
    state.isMinimized = false;

    if (!state.hasInteracted) {
      loadInitState();
    }

    setTimeout(function() {
      var input = document.getElementById('bmAiInputText');
      if (input) input.focus();
    }, 300);
  }

  function closeChat() {
    var win = document.getElementById('bmAiWindow');
    if (win) {
      win.classList.remove('open', 'minimized');
    }
    state.isOpen = false;
  }

  function minimizeChat() {
    var win = document.getElementById('bmAiWindow');
    if (win) {
      state.isMinimized = !state.isMinimized;
      win.classList.toggle('minimized', state.isMinimized);
    }
  }

  /* Fetch Init Config */
  function loadInitState() {
    state.hasInteracted = true;
    fetch('api/ai_chat.php?action=init')
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success) {
          state.welcomeMessage = data.welcome_message;
          state.suggestedQuestions = data.suggested_questions || [];
          state.whatsappUrl = data.whatsapp_url || state.whatsappUrl;

          appendBotMessage(state.welcomeMessage);
          if (state.suggestedQuestions.length > 0) {
            appendQuickReplies(state.suggestedQuestions);
          }
        }
      })
      .catch(function() {
        appendBotMessage("Welcome to BM Forex Hub! How can I assist your Forex trading journey today?");
      });
  }

  /* Append Messages */
  function appendUserMessage(text) {
    var container = document.getElementById('bmAiMessages');
    if (!container) return;

    var msgDiv = document.createElement('div');
    msgDiv.className = 'bm-ai-msg user';
    msgDiv.innerHTML = '<div class="bm-ai-bubble">' + escapeHtml(text) + '</div>' +
      '<span class="bm-ai-time">' + getFormattedTime() + '</span>';
    container.appendChild(msgDiv);

    // Remove old quick replies
    var oldReplies = document.getElementById('bmAiQuickReplies');
    if (oldReplies) oldReplies.remove();

    scrollToBottom();
  }

  function appendBotMessage(text, options) {
    options = options || {};
    var container = document.getElementById('bmAiMessages');
    if (!container) return;

    var msgDiv = document.createElement('div');
    msgDiv.className = 'bm-ai-msg bot';

    var innerHTML = '<div class="bm-ai-bubble">' + escapeHtml(text).replace(/\n/g, '<br>');

    // Action Route Button
    if (options.action_route) {
      innerHTML += '<br><a href="' + escapeHtml(options.action_route.url) + '" class="bm-ai-action-btn">' +
        escapeHtml(options.action_route.label) + ' &rarr;</a>';
    }

    // Escalation WhatsApp Button
    if (options.escalate) {
      var waUrl = options.whatsapp_url || state.whatsappUrl;
      innerHTML += '<br><a href="' + escapeHtml(waUrl) + '" target="_blank" rel="noopener noreferrer" class="bm-ai-wa-btn">' +
        '<svg width="18" height="18" viewBox="0 0 24 24" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>' +
        'Chat on WhatsApp</a>';
    }

    innerHTML += '</div><span class="bm-ai-time">' + getFormattedTime() + '</span>';
    msgDiv.innerHTML = innerHTML;

    container.appendChild(msgDiv);
    scrollToBottom();
  }

  function appendQuickReplies(questions) {
    var container = document.getElementById('bmAiMessages');
    if (!container) return;

    var wrap = document.createElement('div');
    wrap.id = 'bmAiQuickReplies';
    wrap.className = 'bm-ai-quick-replies';

    questions.forEach(function(q) {
      var chip = document.createElement('button');
      chip.className = 'bm-ai-chip';
      chip.textContent = q;
      chip.addEventListener('click', function() {
        sendPrompt(q);
      });
      wrap.appendChild(chip);
    });

    container.appendChild(wrap);
    scrollToBottom();
  }

  function showTyping() {
    var container = document.getElementById('bmAiMessages');
    if (!container) return;

    var typing = document.createElement('div');
    typing.id = 'bmAiTyping';
    typing.className = 'bm-ai-msg bot';
    typing.innerHTML = '<div class="bm-ai-typing">' +
      '<span class="bm-ai-dot"></span><span class="bm-ai-dot"></span><span class="bm-ai-dot"></span>' +
    '</div>';
    container.appendChild(typing);
    scrollToBottom();
  }

  function hideTyping() {
    var el = document.getElementById('bmAiTyping');
    if (el) el.remove();
  }

  function scrollToBottom() {
    var container = document.getElementById('bmAiMessages');
    if (container) {
      container.scrollTop = container.scrollHeight;
    }
  }

  /* Message Transmission */
  function handleSendMessage(e) {
    e.preventDefault();
    var input = document.getElementById('bmAiInputText');
    if (!input) return;

    var text = input.value.trim();
    if (!text) return;

    input.value = '';
    sendPrompt(text);
  }

  function sendPrompt(text) {
    appendUserMessage(text);
    showTyping();

    fetch('api/ai_chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'chat', message: text })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      hideTyping();
      if (data.success) {
        appendBotMessage(data.reply, {
          escalate: data.escalate,
          whatsapp_url: data.whatsapp_url,
          action_route: data.action_route
        });
      } else {
        appendBotMessage("Sorry, I encountered a temporary network issue. Please try again or contact support.");
      }
    })
    .catch(function() {
      hideTyping();
      appendBotMessage("Sorry, I encountered a network error. Please check your internet connection.");
    });
  }

  /* DOM Ready Init */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDOM);
  } else {
    initDOM();
  }
})();
