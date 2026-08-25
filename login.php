<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<?php require_once __DIR__ . '/components/pwa_meta.php'; ?>

<title>Sign In | BM Forex Hub Signal Terminal</title>

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

        <div class="auth-brand">
            BM Forex Hub Signal Terminal
        </div>

        <h1>Sign In</h1>

        <p class="auth-sub">
            Access your live signals, charts, and broker directory.
        </p>

        <div class="auth-alert" id="loginError" hidden></div>

        <div class="auth-alert auth-success" id="loginSuccess" hidden></div>

        <form id="loginForm" novalidate>

            <label class="auth-field">

                <span>Username or Email</span>

                <input
                    type="text"
                    name="username"
                    id="loginIdentifier"
                    autocomplete="username"
                    required>

            </label>

            <label class="auth-field auth-field--password">

                <span>Password</span>

                <div class="auth-pw-wrap">
                <input
                    type="password"
                    name="password"
                    id="loginPassword"
                    autocomplete="current-password"
                    required>

                <button type="button" class="auth-toggle-pw" aria-label="Show password" data-target="loginPassword">
                    <svg class="eye-open" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-closed" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
                </div>

            </label>

            <p style="text-align:right;margin:-10px 0 16px;font-size:.82rem">
              <a href="forgot-password.php" style="color:var(--gold);text-decoration:underline">Forgot password?</a>
            </p>

            <button type="submit" class="btn btn-primary auth-submit" id="loginBtn">
                Sign In
            </button>

        </form>

        <div class="auth-divider"><span>OR</span></div>

        <button type="button" class="auth-google-btn" id="googleLoginBtn">
          <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
          <span>Continue with Google</span>
        </button>

        <p class="auth-footnote">
            Don't have an account?
            <a href="register.php">Create one</a>
        </p>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script>
(function() {
  const form     = document.getElementById('loginForm');
  const errEl    = document.getElementById('loginError');
  const succEl   = document.getElementById('loginSuccess');
  const btn      = document.getElementById('loginBtn');
  const googleBtn= document.getElementById('googleLoginBtn');
  const idInput  = document.getElementById('loginIdentifier');
  const passInput= document.getElementById('loginPassword');

  function showErr(msg)  { errEl.textContent = msg; errEl.hidden = false; succEl.hidden = true; }
  function showSucc(msg) { succEl.textContent = msg; succEl.hidden = false; errEl.hidden = true; }
  function hideAlerts()  { errEl.hidden = true; succEl.hidden = true; }

  /* Google button redirect */
  if (googleBtn) {
    googleBtn.addEventListener('click', function() {
      window.location.href = 'https://bmforexhub.exchange/register.php';
    });
  }

  /* password show/hide toggles */
  document.querySelectorAll('.auth-toggle-pw').forEach(function(toggleBtn) {
    toggleBtn.addEventListener('click', function() {
      var input = document.getElementById(toggleBtn.dataset.target);
      var show  = input.type === 'password';
      input.type = show ? 'text' : 'password';
      toggleBtn.querySelector('.eye-open').style.display  = show ? 'none' : '';
      toggleBtn.querySelector('.eye-closed').style.display = show ? '' : 'none';
      toggleBtn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });

  /* Show success if redirected from registration or password reset */
  if (window.location.search.includes('registered=1')) {
    showSucc('Account created successfully!');
  } else if (window.location.search.includes('reset=1')) {
    showSucc('Password updated successfully! Please sign in with your new password.');
  }

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    hideAlerts();

    const identifier = idInput.value.trim();
    const password   = passInput.value;

    if (!identifier || !password) {
      showErr('Please fill in all fields.');
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Signing in...';

    const isEmail = identifier.includes('@');
    let result;

    if (isEmail) {
      result = await BMAuth.signIn(identifier, password);
    } else {
      result = await BMAuth.signInWithUsername(identifier, password);
    }

    btn.disabled = false;
    btn.textContent = 'Sign In';

    if (result.error) {
      var errMsg = result.error;
      if (errMsg.toLowerCase().includes('not confirmed') || errMsg.toLowerCase().includes('email not confirmed')) {
        errMsg = 'Your email has not been verified yet. <a href="verify-email.php?email=' + encodeURIComponent(identifier) + '" style="color:var(--gold);text-decoration:underline">Verify your email now</a>';
        errEl.innerHTML = errMsg;
        errEl.hidden = false;
      } else {
        showErr(errMsg);
      }
      return;
    }

    window.location.href = 'index.php';
  });
})();
</script>

<script src="js/motion.js" defer></script>

</body>

</html>
