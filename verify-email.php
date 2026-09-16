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
<title>Verify Email | BM Forex Hub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="auth.css?v=<?= filemtime('auth.css') ?>">
</head>
<body>
<div class="auth-shell">
  <div class="auth-card">
    <div class="auth-card__tools"><button type="button" class="theme-toggle" title="Toggle light / dark theme" aria-label="Toggle light / dark theme"><svg class="theme-toggle__sun" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><svg class="theme-toggle__moon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button></div>
    <div class="auth-brand">BM Forex Hub Signal Terminal</div>

    <!-- STEP 1: Enter email -->
    <div class="auth-form-wrap" id="step1">
      <a class="auth-back" href="login.php">
        <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to sign in
      </a>
      <h1>Verify your email</h1>
      <p class="auth-sub">Enter your email address and we'll send you a verification code.</p>

      <div class="auth-alert" id="verifyError" hidden></div>
      <div class="auth-alert auth-success" id="verifySuccess" hidden></div>

      <form id="verifyForm" novalidate>
        <label class="auth-field">
          <span>Email</span>
          <input type="email" name="email" id="verifyEmail" autocomplete="email" required>
        </label>
        <button type="submit" class="btn btn-primary auth-submit" id="sendBtn">Send code</button>
      </form>
    </div>

    <!-- STEP 2: Enter OTP -->
    <div class="auth-otp-wrap" id="step2">
      <h1>Enter verification code</h1>
      <p class="auth-otp-info">We sent a 6-digit code to <strong id="otpEmail"></strong></p>
      <p class="auth-otp-hint" style="font-size:.82rem;margin-top:8px;color:var(--ink-muted);">Check your <strong style="color:var(--gold)">spam/junk folder</strong> if you don't see it in your inbox.</p>

      <div class="auth-alert" id="otpError" hidden></div>

      <div class="auth-otp-inputs" id="otpInputs">
        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code">
        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
      </div>

      <div class="auth-otp-resend">
        Didn't receive the code?
        <button type="button" id="resendBtn">Resend code</button>
      </div>

      <button class="btn btn-primary auth-submit" id="verifyBtn">Verify email</button>
    </div>

    <!-- STEP 3: Success -->
    <div class="auth-new-pw-wrap" id="step3" style="text-align:center">
      <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#16C784" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin:20px auto 16px"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <h1>Email verified!</h1>
      <p class="auth-sub">successfully verified, sign in</p>
      <a href="login.php" class="btn btn-primary auth-submit" style="display:inline-block;text-decoration:none;margin-top:16px">Sign in</a>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script>
(function() {
  var step1      = document.getElementById('step1');
  var step2      = document.getElementById('step2');
  var step3      = document.getElementById('step3');
  var verifyForm = document.getElementById('verifyForm');
  var verifyErr  = document.getElementById('verifyError');
  var verifySucc = document.getElementById('verifySuccess');
  var sendBtn    = document.getElementById('sendBtn');
  var otpEmail   = document.getElementById('otpEmail');
  var otpErr     = document.getElementById('otpError');
  var otpInputs  = document.querySelectorAll('#otpInputs input');
  var verifyBtn  = document.getElementById('verifyBtn');
  var resendBtn  = document.getElementById('resendBtn');

  var _email = '';
  var _resendTimer = null;

  function showErr(el, msg) { el.textContent = msg; el.hidden = false; }
  function hideAlerts() { verifyErr.hidden = true; verifySucc.hidden = true; otpErr.hidden = true; }

  /* Pre-fill email from URL */
  var params = new URLSearchParams(window.location.search);
  var prefilledEmail = params.get('email');
  if (prefilledEmail) {
    document.getElementById('verifyEmail').value = prefilledEmail;
  }

  /* OTP input behavior */
  otpInputs.forEach(function(input, idx) {
    input.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
      if (this.value && idx < otpInputs.length - 1) otpInputs[idx + 1].focus();
      this.classList.toggle('filled', !!this.value);
    });
    input.addEventListener('keydown', function(e) {
      if (e.key === 'Backspace' && !this.value && idx > 0) {
        otpInputs[idx - 1].focus();
        otpInputs[idx - 1].value = '';
        otpInputs[idx - 1].classList.remove('filled');
      }
    });
    input.addEventListener('paste', function(e) {
      e.preventDefault();
      var paste = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
      for (var i = 0; i < Math.min(paste.length, otpInputs.length); i++) {
        otpInputs[i].value = paste[i];
        otpInputs[i].classList.add('filled');
      }
      if (paste.length > 0) otpInputs[Math.min(paste.length, otpInputs.length) - 1].focus();
    });
  });

  function startResendTimer(seconds) {
    resendBtn.disabled = true;
    var remaining = seconds;
    resendBtn.textContent = 'Resend in ' + remaining + 's';
    _resendTimer = setInterval(function() {
      remaining--;
      if (remaining <= 0) {
        clearInterval(_resendTimer);
        resendBtn.disabled = false;
        resendBtn.textContent = 'Resend code';
      } else {
        resendBtn.textContent = 'Resend in ' + remaining + 's';
      }
    }, 1000);
  }

  function getOtpCode() {
    var code = '';
    otpInputs.forEach(function(inp) { code += inp.value; });
    return code;
  }

  async function parseResponse(resp) {
    try {
      return await resp.json();
    } catch (e) {
      return null;
    }
  }

  async function postApi(endpoint, body) {
    var primaryUrl = endpoint;
    var fallbackUrl = endpoint.startsWith('api/') ? 'admin/' + endpoint : 'api/' + endpoint.replace(/^admin\/api\//, '');
    try {
      var resp = await fetch(primaryUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      });
      if (resp.status === 404) {
        return await fetch(fallbackUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(body)
        });
      }
      return resp;
    } catch (err) {
      try {
        return await fetch(fallbackUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(body)
        });
      } catch (err2) {
        throw err;
      }
    }
  }

  /* STEP 1: Request OTP */
  verifyForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    hideAlerts();

    _email = document.getElementById('verifyEmail').value.trim();
    if (!_email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(_email)) {
      showErr(verifyErr, 'Please enter a valid email address.');
      return;
    }

    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending code...';

    try {
      var resp = await postApi('api/otp.php', { action: 'resend', email: _email, type: 'signup' });
      var data = await parseResponse(resp);

      sendBtn.disabled = false;
      sendBtn.textContent = 'Send code';

      if (resp.ok && data && data.success) {
        otpEmail.textContent = _email;
        step1.classList.add('hidden');
        step1.style.display = 'none';
        step2.classList.add('active');
        otpInputs[0].focus();
        startResendTimer(60);
      } else {
        var msg = (data && (data.error || data.message)) || 'Unable to send verification code. Please try again.';
        if (resp.status === 429) msg = 'Too many attempts. Please wait before trying again.';
        showErr(verifyErr, msg);
      }
    } catch (err) {
      sendBtn.disabled = false;
      sendBtn.textContent = 'Send code';
      showErr(verifyErr, "Could not connect to the server. Please check your connection.");
    }
  });

  /* STEP 2: Verify OTP */
  verifyBtn.addEventListener('click', async function() {
    otpErr.hidden = true;
    var code = getOtpCode();
    if (code.length !== 6) {
      showErr(otpErr, 'Please enter the full 6-digit code.');
      return;
    }

    verifyBtn.disabled = true;
    verifyBtn.textContent = 'Verifying...';

    var resp, result;
    try {
      /* Verify OTP */
      resp = await postApi('api/password-reset.php', { action: 'verify', email: _email, code: code, otp_type: 'signup' });
      result = await parseResponse(resp);
    } catch (netErr) {
      verifyBtn.disabled = false;
      verifyBtn.textContent = 'Verify email';
      showErr(otpErr, 'Could not connect to the server. Please check your connection.');
      return;
    }

    if (!resp.ok || !result || !result.success) {
      verifyBtn.disabled = false;
      verifyBtn.textContent = 'Verify email';
      var rawErr = (result && (result.error || result.message)) || '';
      var lower = rawErr.toLowerCase();
      if (resp.status === 403) {
        showErr(otpErr, 'Verification request was rejected. Please request a new code and try again.');
      } else if (resp.status === 429 || lower.includes('too many')) {
        showErr(otpErr, 'Too many attempts. Please wait before trying again.');
      } else if (resp.status === 401 || lower.includes('expired')) {
        showErr(otpErr, 'This verification code has expired. Please request a new code.');
      } else if (lower.includes('already used') || lower.includes('used')) {
        showErr(otpErr, 'Successfully verified, sign in');
      } else {
        showErr(otpErr, rawErr || 'Invalid verification code. Please check the code and try again.');
      }
      return;
    }

    /* Primary OTP verification succeeded. Perform secondary confirm-email step safely. */
    try {
      var confirmResp = await postApi('api/password-reset.php', { action: 'confirm-email', email: _email });
      var confirmData = await parseResponse(confirmResp);
      if (!confirmResp.ok || !confirmData || !confirmData.success) {
        console.warn('Secondary confirm-email note:', confirmData ? confirmData.error : 'HTTP ' + confirmResp.status);
      }
    } catch (secErr) {
      console.warn('Secondary confirm-email network warning:', secErr);
    }

    /* Primary verification succeeded — transition to success state */
    verifyBtn.disabled = false;
    verifyBtn.textContent = 'Verify email';
    step2.classList.remove('active');
    step2.style.display = 'none';
    step3.classList.add('active');
  });

  /* Resend */
  resendBtn.addEventListener('click', async function() {
    otpErr.hidden = true;
    resendBtn.disabled = true;
    startResendTimer(60);

    try {
      var resp = await postApi('api/otp.php', { action: 'resend', email: _email, type: 'signup' });
      var data = await parseResponse(resp);

      if (resp.ok && data && data.success) {
        otpErr.className = 'auth-alert auth-success';
        otpErr.style.cssText = 'background:rgba(79,209,197,.12);border-color:rgba(79,209,197,.3);color:var(--teal);';
        otpErr.textContent = 'A new verification code has been sent to your email.';
        otpErr.hidden = false;
      } else {
        otpErr.className = 'auth-alert';
        otpErr.style.cssText = '';
        var msg = (data && (data.error || data.message)) || 'Unable to send verification code. Please try again.';
        if (resp.status === 429) msg = 'Too many attempts. Please wait before trying again.';
        otpErr.textContent = msg;
        otpErr.hidden = false;
      }
    } catch (err) {
      otpErr.className = 'auth-alert';
      otpErr.style.cssText = '';
      otpErr.textContent = 'Could not connect to the server. Please check your connection.';
      otpErr.hidden = false;
    }
  });

})();
</script>
<script src="js/motion.js" defer></script>
</body>
</html>
