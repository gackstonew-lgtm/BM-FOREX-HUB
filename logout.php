<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Signing out... | BM Forex Hub</title>
</head>
<body>
<p id="status" style="font-family:sans-serif;text-align:center;margin-top:40vh;color:#ccc;">Signing you out...</p>
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script>
(async function() {
  try {
    await BMAuth.signOut();
  } catch (e) {
    /* ignore errors on logout */
  }
  window.location.href = 'landing.php';
})();
</script>
</body>
</html>
