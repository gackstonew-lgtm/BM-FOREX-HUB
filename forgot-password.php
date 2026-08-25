<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require_once __DIR__ . '/components/pwa_meta.php'; ?>
<title>Reset Password | BM Forex Hub Signal Terminal</title>
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
      <h1>Reset password</h1>
      <p class="auth-sub">Enter your email address and we'll send you a verification code.</p>

      <div class="auth-alert" id="resetError" hidden></div>
      <div class="auth-alert auth-success" id="resetSuccess" hidden></div>

      <form id="resetForm" novalidate>
        <label class="auth-field">
          <span>Email</span>
          <input type="email" name="email" id="resetEmail" autocomplete="email" required>
        </label>
        <button type="submit" class="btn btn-primary auth-submit" id="resetBtn">Send code</button>
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

      <button class="btn btn-primary auth-submit" id="verifyBtn">Verify code</button>
    </div>

    <!-- STEP 3: Set new password -->
    <div class="auth-new-pw-wrap" id="step3">
      <h1>Set new password</h1>
      <p class="auth-sub">Enter your new password below.</p>

      <div class="auth-alert" id="newPwError" hidden></div>
      <div class="auth-alert auth-success" id="newPwSuccess" hidden></div>

      <form id="newPwForm" novalidate>
        <label class="auth-field auth-field--password">
          <span>New password</span>
          <div class="auth-pw-wrap">
          <input type="password" name="password" id="newPassword" autocomplete="new-password" minlength="8" required>
          <button type="button" class="auth-toggle-pw" aria-label="Show password" data-target="newPassword">
            <svg class="eye-open" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-closed" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
          </button>
          </div>
        </label>

        <label class="auth-field auth-field--password">
          <span>Confirm new password</span>
          <div class="auth-pw-wrap">
          <input type="password" name="confirm_password" id="newConfirm" autocomplete="new-password" minlength="8" required>
          <button type="button" class="auth-toggle-pw" aria-label="Show password" data-target="newConfirm">
            <svg class="eye-open" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-closed" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
          </button>
          </div>
        </label>

        <button type="submit" class="btn btn-primary auth-submit" id="newPwBtn">Update password</button>
      </form>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script>
(function() {
  /* ---- elements ---- */
  var step1      = document.getElementById('step1');
  var step2      = document.getElementById('step2');
  var step3      = document.getElementById('step3');
  var resetForm  = document.getElementById('resetForm');
  var resetErr   = document.getElementById('resetError');
  var resetSucc  = document.getElementById('resetSuccess');
  var resetBtn   = document.getElementById('resetBtn');
  var otpEmail   = document.getElementById('otpEmail');
  var otpErr     = document.getElementById('otpError');
  var otpInputs  = document.querySelectorAll('#otpInputs input');
  var verifyBtn  = document.getElementById('verifyBtn');
  var resendBtn  = document.getElementById('resendBtn');
  var newPwForm  = document.getElementById('newPwForm');
  var newPwErr   = document.getElementById('newPwError');
  var newPwSucc  = document.getElementById('newPwSuccess');
  var newPwBtn   = document.getElementById('newPwBtn');

  var _email = '';
  var _resendTimer = null;

  function showErr(el, msg)  { el.textContent = msg; el.hidden = false; }
  function hideAlerts()      { resetErr.hidden = true; resetSucc.hidden = true; otpErr.hidden = true; newPwErr.hidden = true; newPwSucc.hidden = true; }

  /* ---- password toggles ---- */
  document.querySelectorAll('.auth-toggle-pw').forEach(function(toggleBtn) {
    toggleBtn.addEventListener('click', function() {
      var input = document.getElementById(toggleBtn.dataset.target);
      var show  = input.type === 'password';
      input.type = show ? 'text' : 'password';
      toggleBtn.querySelector('.eye-open').style.display  = show ? 'none' : '';
      toggleBtn.querySelector('.eye-closed').style.display = show ? '' : 'none';
    });
  });

  /* ---- OTP input behavior ---- */
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
      if (paste.length > 0) otpInputs[Math.min(paste.length, otpInputs.length - 1)].focus();
    });
  });

  /* ---- resend timer ---- */
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

  /* ---- STEP 1: Send OTP ---- */
  resetForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    hideAlerts();

    _email = document.getElementById('resetEmail').value.trim();
    if (!_email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(_email)) {
      showErr(resetErr, 'Please enter a valid email address.');
      return;
    }

    resetBtn.disabled = true;
    resetBtn.textContent = 'Sending code...';

    var result = await BMAuth.resetPasswordOtp(_email);

    resetBtn.disabled = false;
    resetBtn.textContent = 'Send code';

    if (result.error) {
      showErr(resetErr, result.error);
      return;
    }

    /* move to step 2 */
    otpEmail.textContent = _email;
    step1.classList.add('hidden');
    step1.style.display = 'none';
    step2.classList.add('active');
    otpInputs[0].focus();
    startResendTimer(60);
  });

  /* ---- STEP 2: Verify OTP ---- */
  verifyBtn.addEventListener('click', async function() {
    otpErr.hidden = true;
    var code = getOtpCode();
    if (code.length !== 6) {
      showErr(otpErr, 'Please enter the full 6-digit code.');
      return;
    }

    verifyBtn.disabled = true;
    verifyBtn.textContent = 'Verifying...';

    try {
      var resp = await fetch('admin/api/password-reset.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'verify', email: _email, code: code })
      });
      var result = await resp.json();

      if (!resp.ok || !result.success) {
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify code';
        showErr(otpErr, result.error || result.message || 'Invalid code. Please try again.');
        return;
      }

      /* verified — move to step 3 */
      step2.classList.remove('active');
      step2.style.display = 'none';
      step3.classList.add('active');
      document.getElementById('newPassword').focus();
    } catch (err) {
      verifyBtn.disabled = false;
      verifyBtn.textContent = 'Verify code';
      showErr(otpErr, 'Could not reach the server. Please try again.');
    }
  });

  /* ---- RESEND ---- */
  resendBtn.addEventListener('click', async function() {
    otpErr.hidden = true;
    resendBtn.disabled = true;

    startResendTimer(60);

    try {
      var resp = await fetch('admin/api/otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'resend', email: _email, type: 'recovery' })
      });
      var data = await resp.json();

      if (resp.ok && data.success) {
        otpErr.className = 'auth-alert auth-success';
        otpErr.style.cssText = 'background:rgba(79,209,197,.12);border-color:rgba(79,209,197,.3);color:var(--teal);';
        otpErr.textContent = 'A new verification code has been sent to your email.';
        otpErr.hidden = false;
      } else {
        otpErr.className = 'auth-alert';
        otpErr.style.cssText = '';
        otpErr.textContent = data.error || 'Unable to send OTP. Please try again later.';
        otpErr.hidden = false;
      }
    } catch (err) {
      otpErr.className = 'auth-alert';
      otpErr.style.cssText = '';
      otpErr.textContent = 'Unable to send OTP. Please try again later.';
      otpErr.hidden = false;
    }
  });

  /* ---- STEP 3: Set new password ---- */
  newPwForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    hideAlerts();

    var pw = document.getElementById('newPassword').value;
    var confirm = document.getElementById('newConfirm').value;

    if (pw.length < 8) {
      showErr(newPwErr, 'Password must be at least 8 characters long.');
      return;
    }
    if (pw !== confirm) {
      showErr(newPwErr, 'Passwords do not match.');
      return;
    }

    newPwBtn.disabled = true;
    newPwBtn.textContent = 'Updating...';

    try {
      var resp = await fetch('admin/api/password-reset.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update', email: _email, code: getOtpCode(), password: pw })
      });
      var result = await resp.json();

      newPwBtn.disabled = false;
      newPwBtn.textContent = 'Update password';

      if (!resp.ok || !result.success) {
        showErr(newPwErr, result.error || 'Failed to update password. Please try again.');
        return;
      }

      showErr(newPwSucc, '');
      newPwSucc.textContent = 'Password updated! Redirecting to sign in...';
      newPwSucc.hidden = false;

      setTimeout(function() {
        window.location.href = 'login.php?reset=1';
      }, 2000);
    } catch (err) {
      newPwBtn.disabled = false;
      newPwBtn.textContent = 'Update password';
      showErr(newPwErr, 'Could not reach the server. Please try again.');
    }
  });

})();
</script>
<script src="js/motion.js" defer></script>
</body>
</html>
