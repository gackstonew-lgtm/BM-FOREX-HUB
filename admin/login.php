<?php
require_once __DIR__ . '/config.php';
if (!empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($username === '' || $password === '') {
    $error = 'Please enter both username and password.';
  } else {
    $is_email = filter_var($username, FILTER_VALIDATE_EMAIL);

    if ($is_email) {
      $email = $username;
      $ch = curl_init(SUPABASE_URL . '/auth/v1/token?grant_type=password');
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
          'apikey: ' . SUPABASE_ANON,
          'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode([
          'email'    => $email,
          'password' => $password,
        ]),
        CURLOPT_TIMEOUT => 15,
      ]);
      $resp = curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      $auth = json_decode($resp, true);

      if ($code === 200 && !empty($auth['access_token'])) {
        $parts = explode('.', $auth['access_token']);
        $payload = json_decode(base64_decode($parts[1]), true);
        $user_id = $payload['sub'] ?? null;

        if ($user_id) {
          $email_check = sb_admin_get('profiles', [
            'select' => 'id,username,role',
            'id' => 'eq.' . $user_id,
          ]);
        } else {
          $email_check = ['code' => 404, 'data' => null];
        }
      } else {
        $error = 'Invalid email or password.';
        $email_check = ['code' => 401, 'data' => null];
      }
    } else {
      $email_check = sb_admin_get('profiles', [
        'select' => 'id,username,role',
        'username' => 'eq.' . $username,
      ]);
    }

    if ($error === '' && $email_check['code'] === 200 && !empty($email_check['data'])) {
      $profile = $email_check['data'][0];
      if ($profile['role'] !== 'admin') {
        $error = 'Access denied. This account does not have admin privileges.';
      } else {
        if (!$is_email) {
          // Direct RPC call (sb_admin_get falls back to SQLite on non-array
          // responses, which would swallow the scalar email returned here)
          $ch = curl_init(SUPABASE_URL . '/rest/v1/rpc/get_email_by_username?' . http_build_query(['p_username' => $username]));
          curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => sb_admin_headers(),
            CURLOPT_TIMEOUT        => 15,
          ]);
          $rpcResp = curl_exec($ch);
          $rpcCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);
          $email = ($rpcCode >= 200 && $rpcCode < 300) ? json_decode($rpcResp, true) : null;

          if ($email && is_string($email)) {
            $ch = curl_init(SUPABASE_URL . '/auth/v1/token?grant_type=password');
            curl_setopt_array($ch, [
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_POST           => true,
              CURLOPT_HTTPHEADER     => [
                'apikey: ' . SUPABASE_ANON,
                'Content-Type: application/json',
              ],
              CURLOPT_POSTFIELDS => json_encode([
                'email'    => $email,
                'password' => $password,
              ]),
              CURLOPT_TIMEOUT => 15,
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $auth = json_decode($resp, true);

            if ($code === 200 && !empty($auth['access_token'])) {
              $_SESSION['admin_id']    = $profile['id'];
              $_SESSION['admin_user']  = $profile['username'];
              $_SESSION['admin_token'] = $auth['access_token'];
              header('Location: index.php');
              exit;
            } else {
              $error = 'Invalid password.';
            }
          } else {
            $error = 'Could not retrieve account email.';
          }
        } else {
          $_SESSION['admin_id']    = $profile['id'];
          $_SESSION['admin_user']  = $profile['username'];
          $_SESSION['admin_token'] = $auth['access_token'];
          header('Location: index.php');
          exit;
        }
      }
    } else if ($error === '' && !$is_email) {
      $error = 'No account found with that username.';
    } else if ($error === '' && $is_email) {
      $error = 'No account found with that email.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="../css/theme-light.css?v=1">
<script src="../js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | BM Forex Hub</title>
<link rel="icon" type="image/png" href="../BM-ForexHub-Logo-Circle.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500&display=swap">
<link rel="stylesheet" href="../css/motion.css?v=<?= filemtime('../css/motion.css') ?>">
<link rel="stylesheet" href="css/admin-mobile.css?v=<?= filemtime('css/admin-mobile.css') ?>">
<style>
:root{--void:#0B0F14;--panel:#101722;--hairline:#283548;--ink:#FFFFFF;--ink-muted:#B8C3D1;--ink-dim:#7F8B99;--gold:#1677FF;--gold-dim:#0D47A1;--radius:10px}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--void);color:var(--ink);font-family:'Inter',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;-webkit-font-smoothing:antialiased}
.login-card{background:var(--panel);border:1px solid var(--hairline);border-radius:var(--radius);padding:40px 36px;width:100%;max-width:400px;margin:20px;box-shadow:0 24px 60px rgba(0,0,0,.5)}
.login-card .brand{font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.3rem;text-align:center;margin-bottom:6px}
.login-card .brand span{background:linear-gradient(90deg,var(--ink),var(--gold));-webkit-background-clip:text;background-clip:text;color:transparent}
.login-card .sub{text-align:center;color:var(--ink-muted);font-size:.85rem;margin-bottom:32px}
.login-card .badge{display:inline-block;font-family:'IBM Plex Mono',monospace;font-size:.62rem;letter-spacing:.1em;background:rgba(22,119,255,.12);color:var(--gold);padding:3px 10px;border-radius:20px;margin-bottom:16px;text-align:center;width:100%}
.field{margin-bottom:18px}
.field label{display:block;font-family:'IBM Plex Mono',monospace;font-size:.72rem;letter-spacing:.08em;color:var(--ink-muted);text-transform:uppercase;margin-bottom:6px}
.field input{width:100%;padding:11px 14px;background:#202B3A;border:1px solid var(--hairline);border-radius:6px;color:var(--ink);font-family:'Inter',sans-serif;font-size:.9rem;transition:border-color .15s,box-shadow .15s}
.field input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(22,119,255,.2)}
.btn-login{width:100%;padding:12px;background:linear-gradient(135deg,#1677FF,#0D47A1);color:#FFFFFF;border:none;border-radius:6px;font-family:'IBM Plex Mono',monospace;font-size:.82rem;font-weight:600;letter-spacing:.03em;cursor:pointer;transition:box-shadow .15s,transform .15s}
.btn-login:hover{box-shadow:0 6px 24px rgba(22,119,255,.4);transform:translateY(-1px)}
.error{background:rgba(246,70,93,.1);border:1px solid rgba(246,70,93,.3);border-radius:6px;padding:10px 14px;color:#F6465D;font-size:.85rem;margin-bottom:18px;text-align:center}
.back{display:block;text-align:center;margin-top:20px;color:var(--ink-dim);font-size:.82rem;text-decoration:none;transition:color .15s}
.back:hover{color:var(--gold)}
.back-wrap{display:flex;justify-content:center;gap:20px;margin-top:20px}
.back-wrap a{color:var(--ink-dim);font-size:.82rem;text-decoration:none;transition:color .15s;display:inline-flex;align-items:center;gap:5px}
.back-wrap a:hover{color:var(--gold)}
</style>
</head>
<body>
<div class="login-card">
<div class="auth-card__tools"><button type="button" class="theme-toggle" title="Toggle light / dark theme" aria-label="Toggle light / dark theme"><svg class="theme-toggle__sun" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg><svg class="theme-toggle__moon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button></div>
  <div class="brand"><span>BM Forex Hub</span></div>
  <div class="badge">ADMIN ACCESS</div>
  <p class="sub">Sign in with your admin account.</p>

  <?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="field">
      <label for="username">Username or Email</label>
      <input type="text" id="username" name="username" autocomplete="username" required autofocus
             placeholder="Enter username or email"
             value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required>
    </div>
    <button type="submit" class="btn-login">Sign In</button>
  </form>
  <div class="back-wrap">
    <a href="https://bmforexhub.exchange/">&larr; Back to site</a>
  </div>
</div>
<script src="../js/motion.js" defer></script>
</body>
</html>
