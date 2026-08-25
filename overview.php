<?php
require_once __DIR__ . '/engine_config.php';

$supabaseUrl = 'https://iwoytmcxmbhmmbrbpzvf.supabase.co';
$supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4Mzk4MzU2MSwiZXhwIjoyMDk5NTU5NTYxfQ.1ExI-o8zwDkUIH_loQPC_ZJhJaDgrB6gSf1KgAdigCU';

function sb_fetch_multi($queries) {
  global $supabaseUrl, $supabaseKey;
  $mh = curl_multi_init();
  $chs = [];
  foreach ($queries as $key => $q) {
    $url = $supabaseUrl . '/rest/v1/' . $q['table'];
    if (!empty($q['params'])) $url .= '?' . http_build_query($q['params']);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER     => ['apikey: ' . $supabaseKey, 'Authorization: Bearer ' . $supabaseKey, 'Content-Type: application/json'],
      CURLOPT_TIMEOUT        => 5,
      CURLOPT_CONNECTTIMEOUT => 3,
    ]);
    curl_multi_add_handle($mh, $ch);
    $chs[$key] = $ch;
  }
  $running = null;
  do { curl_multi_exec($mh, $running); curl_multi_select($mh, 1); } while ($running > 0);
  $results = [];
  foreach ($chs as $key => $ch) {
    $resp = curl_multi_getcontent($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
    $results[$key] = ($code >= 200 && $code < 300) ? (json_decode($resp) ?: []) : [];
  }
  curl_multi_close($mh);
  return $results;
}

$fetched = sb_fetch_multi([
  'announcements' => ['table' => 'market_overview_announcements', 'params' => ['select' => '*', 'status' => 'eq.published', 'order' => 'priority.desc', 'limit' => 1]],
  'articles'      => ['table' => 'educational_articles', 'params' => ['select' => '*', 'status' => 'eq.published', 'order' => 'created_at.desc', 'limit' => 6]],
  'videos'        => ['table' => 'featured_videos', 'params' => ['select' => '*', 'status' => 'eq.published', 'order' => 'created_at.desc', 'limit' => 4]],
  'promotions'    => ['table' => 'promotions', 'params' => ['select' => '*', 'status' => 'eq.active', 'order' => 'created_at.desc']],
]);
$announcements = is_array($fetched['announcements']) ? $fetched['announcements'] : [];
$articles      = is_array($fetched['articles']) ? $fetched['articles'] : [];
$videos        = is_array($fetched['videos']) ? $fetched['videos'] : [];
$promotions    = is_array($fetched['promotions']) ? $fetched['promotions'] : [];

// Local SQLite database fallback if Supabase returns empty
if (empty($announcements) || empty($articles) || empty($videos) || empty($promotions)) {
  require_once __DIR__ . '/admin/config.php';
  if (empty($announcements)) {
    $announcements = sqlite_admin_get('market_overview_announcements', ['status' => 'published', 'limit' => 1]);
  }
  if (empty($articles)) {
    $articles = sqlite_admin_get('educational_articles', ['status' => 'published', 'limit' => 6]);
  }
  if (empty($videos)) {
    $videos = sqlite_admin_get('featured_videos', ['status' => 'published', 'limit' => 4]);
  }
  if (empty($promotions)) {
    $promotions = sqlite_admin_get('promotions', ['status' => 'active']);
  }
}

$pageTitle = 'Market Overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require_once __DIR__ . '/components/pwa_meta.php'; ?>
<title><?= $pageTitle ?> | BM FOREX HUB</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">
<script>var API_BASE = '<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>';</script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:#0B0F14;color:#fff;font-family:'Inter',system-ui,sans-serif;min-height:100vh;overflow-x:hidden;display:flex;flex-direction:column}

/* ── Page header ── */
.mo-top{display:flex;align-items:center;justify-content:space-between;padding:14px 24px;border-bottom:1px solid #283548;background:#101722;position:sticky;top:0;z-index:100}
.mo-top__brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:#fff;font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:0.85rem}
.mo-top__brand img{width:32px;height:32px;border-radius:50%;border:1px solid rgba(22,119,255,.4)}
.mo-top__nav{display:flex;gap:8px}
.mo-top__nav a{color:rgba(255,255,255,.55);text-decoration:none;font-size:0.78rem;padding:6px 12px;border-radius:6px;transition:all .2s}
.mo-top__nav a:hover,.mo-top__nav a.active{color:#fff;background:rgba(22,119,255,.15)}
.mo-top__back{color:rgba(255,255,255,.5);text-decoration:none;font-size:0.82rem;display:flex;align-items:center;gap:4px;transition:color .2s}
.mo-top__back:hover{color:#1677FF}

.mo-wrap{max-width:1280px;width:100%;margin:0 auto;padding:24px;flex:1}
.mo-title{font-family:'Space Grotesk',sans-serif;font-size:1.3rem;font-weight:700;margin-bottom:4px}
.mo-sub{color:#7F8B99;font-size:0.82rem;margin-bottom:24px}

/* ── Market Status Bar ── */
.mo-status{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}
.mo-session{background:#151D29;border:1px solid #283548;border-radius:10px;padding:20px 16px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;transition:all .3s;min-height:110px}
.mo-session.open{background:rgba(34,197,94,.06);border-color:rgba(34,197,94,.5);box-shadow:0 0 20px rgba(34,197,94,.12)}
.mo-session.closed{border-color:rgba(100,116,139,.2)}
.mo-session__name{font-size:.7rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:#7F8B99}
.mo-session__status{display:flex;align-items:center;gap:6px;font-size:.88rem;font-weight:700}
.mo-session.open .mo-session__status{color:#22c55e}
.mo-session.closed .mo-session__status{color:#ef4444}
.mo-session__dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.mo-session.open .mo-session__dot{background:#22c55e;box-shadow:0 0 8px rgba(34,197,94,.6)}
.mo-session.closed .mo-session__dot{background:#ef4444}
.mo-session__countdown{font-size:.68rem;font-family:'IBM Plex Mono',monospace;color:#64748B;letter-spacing:.02em}
@media(max-width:900px){.mo-status{grid-template-columns:repeat(2,1fr)}}
@media(max-width:500px){.mo-status{grid-template-columns:1fr}}

/* ── Announcement Banner ── */
.mo-announce{background:linear-gradient(135deg,#1a2744,#151D29);border:1px solid rgba(22,119,255,.25);border-radius:12px;padding:18px 20px;margin-bottom:24px;display:flex;align-items:center;gap:16px;position:relative}
.mo-announce__icon{width:36px;height:36px;border-radius:50%;background:rgba(22,119,255,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem}
.mo-announce__content{flex:1}
.mo-announce__title{font-size:0.82rem;font-weight:600;color:#fff;margin-bottom:2px}
.mo-announce__desc{font-size:0.78rem;color:#B8C3D1;line-height:1.4}
.mo-announce__cta{flex-shrink:0;background:#1677FF;color:#fff;text-decoration:none;padding:8px 18px;border-radius:8px;font-size:0.78rem;font-weight:600;transition:all .2s;white-space:nowrap}
.mo-announce__cta:hover{background:#2F80FF;transform:translateY(-1px)}
.mo-announce__close{position:absolute;top:8px;right:10px;background:none;border:none;color:#7F8B99;font-size:1.1rem;cursor:pointer;padding:2px;line-height:1}
.mo-announce__close:hover{color:#fff}
.mo-announce.hidden{display:none}

/* ── Prices Row ── */
.mo-prices{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:24px}
.mo-price{background:#151D29;border:1px solid #283548;border-radius:10px;padding:14px;text-align:center;transition:border-color .2s}
.mo-price__pair{font-size:0.7rem;text-transform:uppercase;letter-spacing:0.06em;color:#7F8B99;font-weight:600}
.mo-price__value{font-family:'IBM Plex Mono',monospace;font-size:1.15rem;font-weight:700;color:#fff;margin:4px 0 2px;transition:color .3s}
.mo-price__change{font-size:0.7rem;font-weight:600;padding:2px 8px;border-radius:4px;display:inline-block}
.mo-price__change.up{color:#16C784;background:rgba(22,199,132,.12)}
.mo-price__change.down{color:#F6465D;background:rgba(246,70,93,.12)}
.mo-price__change.flat{color:#7F8B99;background:rgba(100,116,139,.12)}
.mo-price.loading .mo-price__value{color:#4B5563}
.mo-price--signal{position:relative}
.mo-price--signal .mo-price__signal{font-size:0.6rem;padding:1px 6px;border-radius:3px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px}
.mo-price--signal .mo-price__signal.buy{color:#16C784;background:rgba(22,199,132,.15)}
.mo-price--signal .mo-price__signal.sell{color:#F6465D;background:rgba(246,70,93,.15)}

/* ── Two-column layout ── */
.mo-cols{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:24px}
@media(max-width:860px){.mo-cols{grid-template-columns:1fr}}

/* ── Featured Signal Card ── */
.mo-signal{background:#151D29;border:1px solid #283548;border-radius:12px;padding:24px}
.mo-signal__header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.mo-signal__label{font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#7F8B99;font-weight:600}
.mo-signal__live{font-size:0.65rem;color:#16C784;display:flex;align-items:center;gap:4px}
.mo-signal__live::before{content:'';width:5px;height:5px;border-radius:50%;background:#16C784;animation:pulseD 1.2s infinite}
.mo-signal__pair{font-family:'Space Grotesk',sans-serif;font-size:1.5rem;font-weight:700}
.mo-signal__dir{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:6px;font-size:0.78rem;font-weight:700;text-transform:uppercase;margin:8px 0 14px}
.mo-signal__dir.buy{color:#16C784;background:rgba(22,199,132,.12)}
.mo-signal__dir.sell{color:#F6465D;background:rgba(246,70,93,.12)}
.mo-signal__details{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.mo-signal__detail{padding:8px 12px;background:rgba(0,0,0,.2);border-radius:6px}
.mo-signal__detail-label{font-size:0.62rem;text-transform:uppercase;color:#7F8B99;letter-spacing:0.03em}
.mo-signal__detail-value{font-family:'IBM Plex Mono',monospace;font-size:0.88rem;font-weight:600;color:#fff;margin-top:2px}
.mo-signal__conf{display:flex;align-items:center;gap:8px;margin-top:14px;padding:10px 12px;background:rgba(0,0,0,.2);border-radius:8px}
.mo-signal__conf-bar{height:6px;border-radius:3px;background:#283548;flex:1;overflow:hidden}
.mo-signal__conf-fill{height:100%;border-radius:3px;transition:width .5s}
.mo-signal__conf-fill.high{background:#16C784}
.mo-signal__conf-fill.medium{background:#F0B90B}
.mo-signal__conf-fill.low{background:#F6465D}
.mo-signal__conf-text{font-size:0.75rem;font-weight:600;color:#B8C3D1;flex-shrink:0}
.mo-signal__empty{text-align:center;padding:40px 20px;color:#7F8B99;font-size:0.88rem}

/* ── Market Sentiment ── */
.mo-sentiment{background:#151D29;border:1px solid #283548;border-radius:12px;padding:24px}
.mo-sentiment__header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.mo-sentiment__label{font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#7F8B99;font-weight:600}
.mo-sentiment__bias{font-size:0.78rem;font-weight:700;padding:4px 12px;border-radius:6px;text-transform:uppercase}
.mo-sentiment__bias.bullish{color:#16C784;background:rgba(22,199,132,.12)}
.mo-sentiment__bias.bearish{color:#F6465D;background:rgba(246,70,93,.12)}
.mo-sentiment__bias.neutral{color:#F0B90B;background:rgba(240,185,11,.12)}
.mo-sentiment__pairs{display:flex;flex-direction:column;gap:6px}
.mo-sentiment__row{display:flex;align-items:center;gap:10px;padding:6px 8px;border-radius:6px;background:rgba(0,0,0,.15)}
.mo-sentiment__pair{font-size:0.75rem;font-weight:600;color:#B8C3D1;width:60px;flex-shrink:0;font-family:'IBM Plex Mono',monospace}
.mo-sentiment__bar-wrap{flex:1;height:18px;background:#1E293B;border-radius:4px;overflow:hidden;position:relative}
.mo-sentiment__bar-fill{height:100%;border-radius:4px;transition:width .8s;position:relative}
.mo-sentiment__bar-fill.bullish{background:linear-gradient(90deg,rgba(22,199,132,.3),#16C784)}
.mo-sentiment__bar-fill.bearish{background:linear-gradient(90deg,rgba(246,70,93,.3),#F6465D)}
.mo-sentiment__pct{font-size:0.7rem;font-weight:700;width:36px;text-align:right;flex-shrink:0;font-family:'IBM Plex Mono',monospace}
.mo-sentiment__pct.bullish{color:#16C784}
.mo-sentiment__pct.bearish{color:#F6465D}
.mo-sentiment__empty{text-align:center;padding:40px;color:#7F8B99;font-size:0.85rem}

/* ── Economic Calendar ── */
.mo-eco{background:#151D29;border:1px solid #283548;border-radius:12px;padding:24px}
.mo-eco__header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.mo-eco__label{font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#7F8B99;font-weight:600}
.mo-eco__count{font-size:0.65rem;color:#4B5563;font-family:'IBM Plex Mono',monospace}
.mo-eco__event{display:grid;grid-template-columns:50px 1fr 60px 50px;gap:8px;padding:8px 10px;align-items:center;border-bottom:1px solid rgba(40,53,72,.4)}
.mo-eco__event:last-child{border-bottom:none}
.mo-eco__event.highlight{background:rgba(246,70,93,.05);border-radius:4px}
.mo-eco__time{font-family:'IBM Plex Mono',monospace;font-size:0.7rem;color:#64748B}
.mo-eco__name{font-size:0.78rem;color:#E2E8F0;font-weight:500}
.mo-eco__impact{font-size:0.6rem;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;padding:2px 6px;border-radius:3px;text-align:center}
.mo-eco__impact.high{color:#F6465D;background:rgba(246,70,93,.12)}
.mo-eco__impact.medium{color:#F0B90B;background:rgba(240,185,11,.12)}
.mo-eco__impact.low{color:#16C784;background:rgba(22,199,132,.12)}
.mo-eco__cur{font-family:'IBM Plex Mono',monospace;font-size:0.7rem;color:#7F8B99;text-align:right}
.mo-eco__empty{text-align:center;padding:30px;color:#7F8B99;font-size:0.85rem}
.mo-eco__more{margin-top:12px;text-align:center}
.mo-eco__more a{color:#1677FF;font-size:0.78rem;text-decoration:none}
.mo-eco__more a:hover{text-decoration:underline}

/* ── Forex News ── */
.mo-news-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.mo-news-header h2{font-family:'Space Grotesk',sans-serif;font-size:1rem;font-weight:600}
.mo-news{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px}
@media(max-width:900px){.mo-news{grid-template-columns:1fr 1fr}}
@media(max-width:600px){.mo-news{grid-template-columns:1fr}}
.mo-news-card{background:#151D29;border:1px solid #283548;border-radius:10px;padding:18px;transition:border-color .2s}
.mo-news-card:hover{border-color:rgba(22,119,255,.3)}
.mo-news-card__cat{font-size:0.6rem;text-transform:uppercase;letter-spacing:0.06em;color:#1677FF;font-weight:600;margin-bottom:6px}
.mo-news-card__title{font-size:0.88rem;font-weight:600;color:#fff;margin-bottom:6px;line-height:1.35}
.mo-news-card__summary{font-size:0.78rem;color:#B8C3D1;line-height:1.5;margin-bottom:10px}
.mo-news-card__date{font-size:0.65rem;color:#4B5563;font-family:'IBM Plex Mono',monospace}
.mo-news-card__read{color:#1677FF;font-size:0.72rem;text-decoration:none;font-weight:600;transition:color .2s}
.mo-news-card__read:hover{color:#2F80FF}
.mo-news__empty{text-align:center;padding:40px;grid-column:1/-1;color:#7F8B99;font-size:0.85rem}

/* ── Section Title ── */
.mo-section-title{font-family:'Space Grotesk',sans-serif;font-size:1rem;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px}

/* ── Educational Articles ── */
.mo-articles{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px}
@media(max-width:860px){.mo-articles{grid-template-columns:1fr 1fr}}
@media(max-width:540px){.mo-articles{grid-template-columns:1fr}}
.mo-article{background:#151D29;border:1px solid #283548;border-radius:10px;overflow:hidden;transition:border-color .2s,transform .2s}
.mo-article:hover{border-color:rgba(22,119,255,.3);transform:translateY(-2px)}
.mo-article__thumb{width:100%;height:140px;object-fit:cover;border-bottom:1px solid #283548}
.mo-article__body{padding:14px}
.mo-article__cat{font-size:0.6rem;text-transform:uppercase;letter-spacing:0.06em;color:#1677FF;font-weight:600;margin-bottom:4px}
.mo-article__title{font-size:0.82rem;font-weight:600;color:#fff;margin-bottom:4px;line-height:1.3}
.mo-article__desc{font-size:0.75rem;color:#B8C3D1;line-height:1.4}
.mo-article__empty{text-align:center;padding:40px;grid-column:1/-1;color:#7F8B99;font-size:0.85rem}

/* ── Videos ── */
.mo-videos{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:24px}
@media(max-width:640px){.mo-videos{grid-template-columns:1fr}}
.mo-video{background:#151D29;border:1px solid #283548;border-radius:10px;overflow:hidden}
.mo-video__embed{position:relative;padding-bottom:56.25%;height:0;overflow:hidden;background:#0B0F14}
.mo-video__embed iframe{position:absolute;top:0;left:0;width:100%;height:100%;border:0}
.mo-video__body{padding:12px 14px}
.mo-video__title{font-size:0.82rem;font-weight:600;color:#fff;margin-bottom:2px}
.mo-video__desc{font-size:0.72rem;color:#B8C3D1}

/* ── Promotions ── */
.mo-promos{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:24px}
@media(max-width:640px){.mo-promos{grid-template-columns:1fr}}
.mo-promo{background:linear-gradient(135deg,#1a2744,#151D29);border:1px solid rgba(22,119,255,.2);border-radius:12px;padding:20px;display:flex;flex-direction:column}
.mo-promo__banner{width:100%;height:100px;object-fit:cover;border-radius:8px;margin-bottom:12px}
.mo-promo__title{font-size:0.88rem;font-weight:600;color:#fff;margin-bottom:4px}
.mo-promo__desc{font-size:0.75rem;color:#B8C3D1;line-height:1.4;margin-bottom:12px;flex:1}
.mo-promo__cta{display:inline-block;background:#1677FF;color:#fff;text-decoration:none;padding:8px 18px;border-radius:8px;font-size:0.75rem;font-weight:600;transition:all .2s;align-self:flex-start}
.mo-promo__cta:hover{background:#2F80FF;transform:translateY(-1px)}
.mo-promo__empty{text-align:center;padding:40px;grid-column:1/-1;color:#7F8B99;font-size:0.85rem}

/* ── Quick Tools ── */
.mo-tools{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:24px}
@media(max-width:860px){.mo-tools{grid-template-columns:repeat(3,1fr)}}
@media(max-width:480px){.mo-tools{grid-template-columns:repeat(2,1fr)}}
.mo-tool{background:#151D29;border:1px solid #283548;border-radius:10px;padding:14px 10px;text-align:center;cursor:pointer;transition:all .2s}
.mo-tool:hover{border-color:#1677FF;background:rgba(22,119,255,.06);transform:translateY(-2px)}
.mo-tool__icon{font-size:1.2rem;margin-bottom:6px}
.mo-tool__name{font-size:0.7rem;font-weight:600;color:#B8C3D1}

/* ── Calculator Modal ── */
.mo-modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.7);z-index:500;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
.mo-modal-overlay.open{display:flex}
.mo-modal{background:#151D29;border:1px solid #283548;border-radius:14px;padding:28px;width:100%;max-width:420px;max-height:90vh;overflow-y:auto}
.mo-modal__header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.mo-modal__title{font-family:'Space Grotesk',sans-serif;font-size:1rem;font-weight:600}
.mo-modal__close{background:none;border:none;color:#7F8B99;font-size:1.3rem;cursor:pointer;padding:4px;line-height:1}
.mo-modal__close:hover{color:#fff}
.mo-calc-field{margin-bottom:14px}
.mo-calc-field label{display:block;font-size:0.72rem;color:#B8C3D1;margin-bottom:4px;font-weight:500}
.mo-calc-field input,.mo-calc-field select{width:100%;padding:10px 12px;background:#202B3A;border:1px solid #283548;border-radius:8px;color:#fff;font-size:0.85rem;outline:none;transition:border-color .2s}
.mo-calc-field input:focus,.mo-calc-field select:focus{border-color:#1677FF}
.mo-calc-field select option{background:#202B3A;color:#fff}
.mo-calc-result{padding:12px;background:rgba(22,199,132,.08);border:1px solid rgba(22,199,132,.2);border-radius:8px;margin-top:12px;text-align:center}
.mo-calc-result__label{font-size:0.65rem;color:#16C784;text-transform:uppercase;letter-spacing:0.03em}
.mo-calc-result__value{font-family:'IBM Plex Mono',monospace;font-size:1.2rem;font-weight:700;color:#fff;margin-top:2px}

/* ── Loading ── */
.mo-loading{text-align:center;padding:40px;color:#4B5563}
.mo-loading .spinner{width:28px;height:28px;border:3px solid rgba(22,119,255,.15);border-top-color:#1677FF;border-radius:50%;margin:0 auto 12px;animation:spR .7s linear infinite}
@keyframes spR{to{transform:rotate(360deg)}}
@keyframes pulseD{0%,100%{opacity:1}50%{opacity:.3}}

/* ── Skeleton ── */
.mo-skel{background:linear-gradient(90deg,#151D29 25%,#1E293B 50%,#151D29 75%);background-size:200% 100%;animation:skelSh 1.5s infinite;border-radius:6px}
@keyframes skelSh{0%{background-position:200% 0}100%{background-position:-200% 0}}

/* ── Responsive Mobile View (Exact Desktop Structure Match) ── */
@media(max-width:900px){
  .mo-top{padding:12px 18px}
  .mo-top__nav{display:flex!important;gap:6px;overflow-x:auto;padding-bottom:2px}
  .mo-top__nav a{padding:5px 10px;font-size:0.75rem;white-space:nowrap}
  .mo-status{grid-template-columns:repeat(4,minmax(125px,1fr))!important;overflow-x:auto;padding-bottom:4px}
  .mo-prices{grid-template-columns:repeat(5,minmax(95px,1fr))!important;overflow-x:auto;padding-bottom:4px}
  .mo-prices .mo-price:nth-child(4),.mo-prices .mo-price:nth-child(5){display:block!important}
  .mo-cols{grid-template-columns:1fr 1fr!important;gap:14px}
  .mo-news{grid-template-columns:repeat(3,minmax(210px,1fr))!important;overflow-x:auto;padding-bottom:4px}
  .mo-articles{grid-template-columns:repeat(3,minmax(210px,1fr))!important;overflow-x:auto;padding-bottom:4px}
  .mo-videos{grid-template-columns:repeat(2,1fr)!important}
  .mo-promos{grid-template-columns:repeat(2,1fr)!important}
  .mo-tools{grid-template-columns:repeat(5,1fr)!important}
}
@media(max-width:600px){
  .mo-wrap{padding:12px}
  .mo-top{padding:10px 12px;gap:8px}
  .mo-top__brand span{font-size:0.78rem}
  .mo-title{font-size:1.15rem}
  .mo-sub{font-size:0.75rem;margin-bottom:16px}
  .mo-status{grid-template-columns:repeat(4,minmax(115px,1fr))!important;overflow-x:auto;gap:8px}
  .mo-session{padding:12px 8px;min-height:92px}
  .mo-session__name{font-size:0.62rem}
  .mo-session__status{font-size:0.78rem}
  .mo-prices{grid-template-columns:repeat(5,minmax(85px,1fr))!important;overflow-x:auto;gap:6px}
  .mo-price{padding:10px 4px}
  .mo-price__pair{font-size:0.62rem}
  .mo-price__value{font-size:0.92rem}
  .mo-cols{grid-template-columns:1fr 1fr!important;gap:10px}
  .mo-signal,.mo-sentiment{padding:16px}
  .mo-signal__pair{font-size:1.2rem}
  .mo-news{grid-template-columns:repeat(3,minmax(190px,1fr))!important;overflow-x:auto}
  .mo-news-card{padding:14px}
  .mo-news-card__title{font-size:0.8rem}
  .mo-news-card__summary{font-size:0.72rem}
  .mo-articles{grid-template-columns:repeat(3,minmax(190px,1fr))!important;overflow-x:auto}
  .mo-article__thumb{height:110px}
  .mo-article__body{padding:10px}
  .mo-article__title{font-size:0.78rem}
  .mo-videos{grid-template-columns:repeat(2,1fr)!important;gap:10px}
  .mo-video__body{padding:10px}
  .mo-video__title{font-size:0.78rem}
  .mo-promos{grid-template-columns:repeat(2,1fr)!important;gap:10px}
  .mo-promo{padding:14px}
  .mo-promo__title{font-size:0.82rem}
  .mo-tools{grid-template-columns:repeat(5,1fr)!important;gap:6px}
  .mo-tool{padding:10px 4px}
  .mo-tool__icon{font-size:1rem;margin-bottom:4px}
  .mo-tool__name{font-size:0.6rem}
}
</style>
</head>
<body class="has-sidebar">

<!-- ===== Dashboard Sidebar ===== -->
<?php $activeNav = 'market'; include __DIR__ . '/components/global_sidebar.php'; ?>

<div class="dash-main-wrap">
<!-- Unified Global Header -->
<?php include __DIR__ . '/components/global_header.php'; ?>

<div class="mo-wrap">

  <!-- Title -->
  <h1 class="mo-title">Market Overview</h1>
  <p class="mo-sub">Real-time market data, news, and trading tools</p>

  <!-- 1. Market Status -->
  <div class="mo-status" id="marketStatus">
    <div class="mo-session closed" id="sessionSydney">
      <div class="mo-session__name">Sydney</div>
      <div class="mo-session__status"><span class="mo-session__dot"></span>Closed</div>
      <div class="mo-session__countdown" id="cdSydney"></div>
    </div>
    <div class="mo-session closed" id="sessionTokyo">
      <div class="mo-session__name">Tokyo</div>
      <div class="mo-session__status"><span class="mo-session__dot"></span>Closed</div>
      <div class="mo-session__countdown" id="cdTokyo"></div>
    </div>
    <div class="mo-session closed" id="sessionLondon">
      <div class="mo-session__name">London</div>
      <div class="mo-session__status"><span class="mo-session__dot"></span>Closed</div>
      <div class="mo-session__countdown" id="cdLondon"></div>
    </div>
    <div class="mo-session closed" id="sessionNewYork">
      <div class="mo-session__name">New York</div>
      <div class="mo-session__status"><span class="mo-session__dot"></span>Closed</div>
      <div class="mo-session__countdown" id="cdNewYork"></div>
    </div>
  </div>

  <!-- 2. Announcement Banner -->
  <?php if (!empty($announcements)): $a = $announcements[0]; ?>
  <div class="mo-announce" id="announceBanner">
    <div class="mo-announce__icon">&#128240;</div>
    <div class="mo-announce__content">
      <div class="mo-announce__title"><?= htmlspecialchars($a->title ?? '') ?></div>
      <?php if (!empty($a->description)): ?><div class="mo-announce__desc"><?= htmlspecialchars($a->description) ?></div><?php endif; ?>
    </div>
    <?php if (!empty($a->button_text) && !empty($a->button_link)): ?>
      <a href="<?= htmlspecialchars($a->button_link) ?>" class="mo-announce__cta" target="_blank"><?= htmlspecialchars($a->button_text) ?></a>
    <?php endif; ?>
    <button class="mo-announce__close" onclick="document.getElementById('announceBanner').classList.add('hidden')">&times;</button>
  </div>
  <?php endif; ?>

  <!-- 3. Live Prices -->
  <div class="mo-prices" id="pricesRow">
    <div class="mo-price loading"><div class="mo-price__pair">EURUSD</div><div class="mo-price__value">--</div><div class="mo-price__change">--</div></div>
    <div class="mo-price loading"><div class="mo-price__pair">GBPUSD</div><div class="mo-price__value">--</div><div class="mo-price__change">--</div></div>
    <div class="mo-price loading"><div class="mo-price__pair">USDJPY</div><div class="mo-price__value">--</div><div class="mo-price__change">--</div></div>
    <div class="mo-price loading"><div class="mo-price__pair">XAUUSD</div><div class="mo-price__value">--</div><div class="mo-price__change">--</div></div>
    <div class="mo-price loading"><div class="mo-price__pair">BTCUSD</div><div class="mo-price__value">--</div><div class="mo-price__change">--</div></div>
  </div>

  <!-- 4. Featured Signal + Market Sentiment (two columns) -->
  <div class="mo-cols">
    <!-- Featured Signal -->
    <div class="mo-signal" id="featuredSignal">
      <div class="mo-signal__header">
        <span class="mo-signal__label">Featured Signal</span>
        <span class="mo-signal__live">Live</span>
      </div>
      <div class="mo-signal__empty" id="signalEmpty">Loading latest signal...</div>
      <div id="signalContent" style="display:none">
        <div class="mo-signal__pair" id="sigPair"></div>
        <div class="mo-signal__dir" id="sigDir"></div>
        <div class="mo-signal__details">
          <div class="mo-signal__detail"><div class="mo-signal__detail-label">Entry</div><div class="mo-signal__detail-value" id="sigEntry">--</div></div>
          <div class="mo-signal__detail"><div class="mo-signal__detail-label">Take Profit</div><div class="mo-signal__detail-value" id="sigTp">--</div></div>
          <div class="mo-signal__detail"><div class="mo-signal__detail-label">Stop Loss</div><div class="mo-signal__detail-value" id="sigSl">--</div></div>
          <div class="mo-signal__detail"><div class="mo-signal__detail-label">Risk</div><div class="mo-signal__detail-value" id="sigRisk">--</div></div>
        </div>
        <div class="mo-signal__conf">
          <span style="font-size:0.7rem;color:#7F8B99">Confidence</span>
          <div class="mo-signal__conf-bar"><div class="mo-signal__conf-fill" id="sigConfBar" style="width:0%"></div></div>
          <span class="mo-signal__conf-text" id="sigConfText">0%</span>
        </div>
      </div>
    </div>

    <!-- Market Sentiment -->
    <div class="mo-sentiment" id="marketSentiment">
      <div class="mo-sentiment__header">
        <span class="mo-sentiment__label">Market Sentiment</span>
        <span class="mo-sentiment__bias" id="sentBias">Loading</span>
      </div>
      <div class="mo-sentiment__empty" id="sentEmpty">Loading sentiment data...</div>
      <div class="mo-sentiment__pairs" id="sentPairs" style="display:none"></div>
    </div>
  </div>

  <!-- 5. Economic Calendar -->
  <div class="mo-eco" id="ecoCalendar" style="margin-bottom:24px">
    <div class="mo-eco__header">
      <span class="mo-eco__label">Economic Calendar</span>
      <span class="mo-eco__count" id="ecoCount">Today</span>
    </div>
    <div id="ecoList">
      <div class="mo-eco__empty">Loading economic events...</div>
    </div>
    <div class="mo-eco__more"><a href="index.php?section=news" onclick="window.open('index.php?section=news','_self')">View full calendar &rarr;</a></div>
  </div>

  <!-- 6. Forex News -->
  <div style="margin-bottom:24px">
    <div class="mo-news-header">
      <h2>Forex News</h2>
    </div>
    <div class="mo-news" id="newsGrid">
      <div class="mo-loading" style="grid-column:1/-1"><div class="spinner"></div><div>Loading news...</div></div>
    </div>
  </div>

  <!-- 7. Educational Articles -->
  <?php if (!empty($articles)): ?>
  <h2 class="mo-section-title">&#128218; Educational Articles</h2>
  <div class="mo-articles">
    <?php foreach ($articles as $art): ?>
    <div class="mo-article">
      <?php if (!empty($art->thumbnail)): ?><img src="<?= htmlspecialchars($art->thumbnail) ?>" alt="" class="mo-article__thumb" loading="lazy"><?php endif; ?>
      <div class="mo-article__body">
        <?php if (!empty($art->category)): ?><div class="mo-article__cat"><?= htmlspecialchars($art->category) ?></div><?php endif; ?>
        <div class="mo-article__title"><?= htmlspecialchars($art->title ?? '') ?></div>
        <?php if (!empty($art->short_description)): ?><div class="mo-article__desc"><?= htmlspecialchars($art->short_description) ?></div><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- 8. Featured Videos -->
  <?php if (!empty($videos)): ?>
  <h2 class="mo-section-title">&#127909; Featured Videos</h2>
  <div class="mo-videos">
    <?php foreach ($videos as $vid): ?>
    <div class="mo-video">
      <div class="mo-video__embed">
        <iframe src="<?= htmlspecialchars($vid->video_url ?? '') ?>" allowfullscreen loading="lazy"></iframe>
      </div>
      <div class="mo-video__body">
        <div class="mo-video__title"><?= htmlspecialchars($vid->title ?? '') ?></div>
        <?php if (!empty($vid->description)): ?><div class="mo-video__desc"><?= htmlspecialchars($vid->description) ?></div><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- 9. Promotions -->
  <?php if (!empty($promotions)): ?>
  <h2 class="mo-section-title">&#127881; Promotions</h2>
  <div class="mo-promos">
    <?php foreach ($promotions as $promo): ?>
    <div class="mo-promo">
      <?php if (!empty($promo->banner)): ?><img src="<?= htmlspecialchars($promo->banner) ?>" alt="" class="mo-promo__banner" loading="lazy"><?php endif; ?>
      <div class="mo-promo__title"><?= htmlspecialchars($promo->title ?? '') ?></div>
      <?php if (!empty($promo->description)): ?><div class="mo-promo__desc"><?= htmlspecialchars($promo->description) ?></div><?php endif; ?>
      <?php if (!empty($promo->button_text) && !empty($promo->link)): ?>
        <a href="<?= htmlspecialchars($promo->link) ?>" class="mo-promo__cta" target="_blank"><?= htmlspecialchars($promo->button_text) ?></a>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- 10. Quick Trading Tools -->
  <h2 class="mo-section-title">&#128295; Quick Trading Tools</h2>
  <div class="mo-tools">
    <div class="mo-tool" onclick="openCalc('pip')"><div class="mo-tool__icon">&#128200;</div><div class="mo-tool__name">Pip Calculator</div></div>
    <div class="mo-tool" onclick="openCalc('lot')"><div class="mo-tool__icon">&#9878;</div><div class="mo-tool__name">Lot Size Calculator</div></div>
    <div class="mo-tool" onclick="openCalc('margin')"><div class="mo-tool__icon">&#128176;</div><div class="mo-tool__name">Margin Calculator</div></div>
    <div class="mo-tool" onclick="openCalc('position')"><div class="mo-tool__icon">&#128202;</div><div class="mo-tool__name">Position Size</div></div>
    <div class="mo-tool" onclick="openCalc('profit')"><div class="mo-tool__icon">&#128178;</div><div class="mo-tool__name">Profit Calculator</div></div>
  </div>

</div><!-- /.mo-wrap -->

<!-- Calculator Modal -->
<div class="mo-modal-overlay" id="calcModal">
  <div class="mo-modal">
    <div class="mo-modal__header">
      <span class="mo-modal__title" id="calcTitle">Calculator</span>
      <button class="mo-modal__close" onclick="closeCalc()">&times;</button>
    </div>
    <div id="calcBody"></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script src="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/js/calculators.js?v=<?= filemtime('js/calculators.js') ?>"></script>
<script>
var API_BASE = typeof API_BASE !== 'undefined' ? API_BASE : '';
var ENGINE_URL = API_BASE + '/api/engine.php';
var NEWS_URL = API_BASE + '/api/news.php';

/* ==================== Session Times ==================== */
var SESSIONS = [
  { id:'Sydney',   open:22, close:7  },
  { id:'Tokyo',    open:0,  close:9  },
  { id:'London',   open:8,  close:17 },
  { id:'NewYork',  open:13, close:22 }
];

function updateSessions() {
  var now = new Date();
  var utcH = now.getUTCHours();
  var utcM = now.getUTCMinutes();

  SESSIONS.forEach(function(s) {
    var el = document.getElementById('session' + s.id);
    if (!el) return;
    var open = s.open, close = s.close;
    var isOpen = (open <= close) ? (utcH >= open && utcH < close) : (utcH >= open || utcH < close);

    el.className = 'mo-session ' + (isOpen ? 'open' : 'closed');
    el.querySelector('.mo-session__status').childNodes[1].textContent = isOpen ? 'Open' : 'Closed';

    // Countdown for all markets
    var targetH = isOpen ? close : open;
    var diffH = targetH - utcH;
    if (diffH < 0) diffH += 24;
    var totalMin = diffH * 60 - utcM;
    if (totalMin < 0) totalMin += 24 * 60;
    var hrs = Math.floor(totalMin / 60);
    var mins = totalMin % 60;
    var label = isOpen ? 'Closes in' : 'Opens in';
    document.getElementById('cd' + s.id).textContent = label + ' ' + hrs + 'h ' + mins + 'm';
  });
}
updateSessions();
setInterval(updateSessions, 30000);

/* ==================== Live Prices ==================== */
var prevPrices = {};

function fetchPrices() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', ENGINE_URL + '?route=prices&_=' + Date.now(), true);
  xhr.timeout = 10000;
  xhr.onload = function() {
    if (xhr.status !== 200) return;
    try {
      var d = JSON.parse(xhr.responseText);
      var prices = d.prices || {};
      var PAIRS = ['EURUSD','GBPUSD','USDJPY','XAUUSD','BTCUSD'];
      var cards = document.querySelectorAll('#pricesRow .mo-price');
      PAIRS.forEach(function(pair, i) {
        var val = prices[pair];
        var card = cards[i];
        if (!card || !val) return;
        var prev = prevPrices[pair];
        var change = prev !== undefined ? ((val - prev) / prev * 100) : 0;
        prevPrices[pair] = val;
        card.classList.remove('loading');
        card.querySelector('.mo-price__value').textContent = val.toFixed(pair === 'USDJPY' ? 3 : pair === 'XAUUSD' ? 2 : 5);
        var chEl = card.querySelector('.mo-price__change');
        var cls = 'flat';
        var sign = '';
        if (change > 0.0001) { cls = 'up'; sign = '+'; }
        else if (change < -0.0001) { cls = 'down'; sign = ''; }
        chEl.className = 'mo-price__change ' + cls;
        chEl.textContent = sign + change.toFixed(3) + '%';
      });
    } catch(e) {}
  };
  xhr.send();
}
fetchPrices();
setInterval(fetchPrices, 15000);

/* ==================== Featured Signal ==================== */
function fetchSignal() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'api/featured-signal.php?_=' + Date.now(), true);
  xhr.timeout = 15000;
  xhr.onload = function() {
    if (xhr.status !== 200) return;
    try {
      var d = JSON.parse(xhr.responseText);
      var sig = d.data;
      if (!sig) {
        document.getElementById('signalEmpty').textContent = 'No featured signal available';
        document.getElementById('signalContent').style.display = 'none';
        document.getElementById('signalEmpty').style.display = '';
        return;
      }
      document.getElementById('signalEmpty').style.display = 'none';
      document.getElementById('signalContent').style.display = 'block';

      document.getElementById('sigPair').textContent = sig.pair || '--';
      var dir = (sig.direction || 'neutral').toLowerCase();
      var dirEl = document.getElementById('sigDir');
      dirEl.className = 'mo-signal__dir ' + dir;
      dirEl.textContent = dir === 'buy' ? '\u25B2 Buy' : dir === 'sell' ? '\u25BC Sell' : 'Neutral';

      document.getElementById('sigEntry').textContent = sig.entry_price || '--';
      document.getElementById('sigTp').textContent = sig.take_profit || '--';
      document.getElementById('sigSl').textContent = sig.stop_loss || '--';
      document.getElementById('sigRisk').textContent = sig.risk || '--';

      var conf = parseInt(sig.confidence) || 0;
      var bar = document.getElementById('sigConfBar');
      bar.style.width = conf + '%';
      bar.className = 'mo-signal__conf-fill ' + (conf >= 70 ? 'high' : conf >= 40 ? 'medium' : 'low');
      document.getElementById('sigConfText').textContent = conf + '%';
    } catch(e) {}
  };
  xhr.send();
}
fetchSignal();
setInterval(fetchSignal, 30000);

/* ==================== Market Sentiment ==================== */
function fetchSentiment() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', ENGINE_URL + '?route=strength&_=' + Date.now(), true);
  xhr.timeout = 10000;
  xhr.onload = function() {
    if (xhr.status !== 200) return;
    try {
      var d = JSON.parse(xhr.responseText);
      var currencies = d.currencies || [];
      if (!currencies.length) return;
      document.getElementById('sentEmpty').style.display = 'none';
      var container = document.getElementById('sentPairs');
      container.style.display = 'flex';
      container.innerHTML = '';

      // Overall bias
      var bullish = currencies.filter(function(c) { return (c.strength || c.score || 50) > 55; }).length;
      var bearish = currencies.filter(function(c) { return (c.strength || c.score || 50) < 45; }).length;
      var bias = bullish > bearish ? 'bullish' : bearish > bullish ? 'bearish' : 'neutral';
      var biasEl = document.getElementById('sentBias');
      biasEl.className = 'mo-sentiment__bias ' + bias;
      biasEl.textContent = bias.charAt(0).toUpperCase() + bias.slice(1);

      var pairs = [
        { label: 'EUR', key: 'EUR' },
        { label: 'GBP', key: 'GBP' },
        { label: 'JPY', key: 'JPY' },
        { label: 'USD', key: 'USD' },
        { label: 'CHF', key: 'CHF' },
        { label: 'AUD', key: 'AUD' },
        { label: 'CAD', key: 'CAD' },
        { label: 'NZD', key: 'NZD' }
      ];

      pairs.forEach(function(p) {
        var c = currencies.find(function(x) { return (x.currency || x.symbol || '').toUpperCase() === p.key; });
        var score = c ? (c.strength || c.score || 50) : 50;
        var isBull = score > 55;
        var isBear = score < 45;
        var pct = Math.round(score);

        var row = document.createElement('div');
        row.className = 'mo-sentiment__row';
        row.innerHTML =
          '<span class="mo-sentiment__pair">' + p.key + '</span>' +
          '<div class="mo-sentiment__bar-wrap"><div class="mo-sentiment__bar-fill ' + (isBull ? 'bullish' : isBear ? 'bearish' : '') + '" style="width:' + pct + '%"></div></div>' +
          '<span class="mo-sentiment__pct ' + (isBull ? 'bullish' : isBear ? 'bearish' : '') + '">' + pct + '%</span>';
        container.appendChild(row);
      });
    } catch(e) {}
  };
  xhr.send();
}
fetchSentiment();
setInterval(fetchSentiment, 30000);

/* ==================== Economic Calendar ==================== */
function fetchCalendar() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', NEWS_URL + '?_=' + Date.now(), true);
  xhr.timeout = 15000;
  xhr.onload = function() {
    if (xhr.status !== 200) return;
    try {
      var events = JSON.parse(xhr.responseText);
      if (!events || !events.length) {
        document.querySelector('#ecoList').innerHTML = '<div class="mo-eco__empty">No events today</div>';
        return;
      }
      // Filter today's high-impact events, limit to 8
      var now = new Date();
      var todayStr = now.toISOString().slice(0,10);
      var filtered = [];
      events.forEach(function(e) {
        var d = e.date || '';
        // Match today or upcoming
        if (d && d >= todayStr) {
          filtered.push(e);
        }
      });
      if (!filtered.length) filtered = events.slice(0, 8);
      else filtered = filtered.slice(0, 8);

      var html = '';
      filtered.forEach(function(e) {
        var impact = (e.impact || '').toLowerCase();
        var timeStr = e.time ? e.time.slice(0, 5) : '--:--';
        var hlClass = impact === 'high' ? ' highlight' : '';
        html += '<div class="mo-eco__event' + hlClass + '">'
          + '<span class="mo-eco__time">' + timeStr + '</span>'
          + '<span class="mo-eco__name">' + (e.title || e.event || '--') + '</span>'
          + '<span class="mo-eco__impact ' + impact + '">' + impact + '</span>'
          + '<span class="mo-eco__cur">' + (e.currency || '') + '</span>'
          + '</div>';
      });
      document.querySelector('#ecoList').innerHTML = html;
      document.getElementById('ecoCount').textContent = filtered.length + ' events';
    } catch(e) {}
  };
  xhr.send();
}
fetchCalendar();
setInterval(fetchCalendar, 60000);

/* ==================== Forex News ==================== */
function fetchNews() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', NEWS_URL + '?_=' + Date.now(), true);
  xhr.timeout = 15000;
  xhr.onload = function() {
    if (xhr.status !== 200) return;
    try {
      var events = JSON.parse(xhr.responseText);
      if (!events || !events.length) {
        document.querySelector('#newsGrid').innerHTML = '<div class="mo-news__empty">No news available</div>';
        return;
      }
      var newsItems = [];
      // Group by title to avoid duplicates, take unique news
      var seen = {};
      events.forEach(function(e) {
        var title = (e.title || e.event || '').trim();
        if (!title || seen[title]) return;
        seen[title] = true;
        if (newsItems.length >= 6) return;
        newsItems.push({
          title: title,
          currency: e.currency || '',
          impact: (e.impact || '').toLowerCase(),
          time: e.time || '',
          date: e.date || ''
        });
      });
      if (!newsItems.length) {
        document.querySelector('#newsGrid').innerHTML = '<div class="mo-news__empty">No news available</div>';
        return;
      }
      var html = '';
      newsItems.forEach(function(n) {
        html += '<div class="mo-news-card">'
          + '<div class="mo-news-card__cat">' + n.currency + ' &middot; ' + n.impact + '</div>'
          + '<div class="mo-news-card__title">' + n.title + '</div>'
          + '<div class="mo-news-card__summary">Impact: ' + n.impact.toUpperCase() + ' &middot; ' + n.date + ' ' + n.time + '</div>'
          + '<div class="mo-news-card__date">' + n.date + '</div>'
          + '</div>';
      });
      document.querySelector('#newsGrid').innerHTML = html;
    } catch(e) {}
  };
  xhr.send();
}
fetchNews();
setInterval(fetchNews, 60000);

/* ==================== Loading fallback ==================== */
setTimeout(function() {
  var els = document.querySelectorAll('.mo-price.loading .mo-price__value');
  els.forEach(function(el) { if (el.textContent === '--') el.textContent = 'N/A'; });
}, 12000);
</script>
<script src="js/motion.js" defer></script>
<?php $activeTab = 'markets'; include __DIR__ . '/components/mobile-tabbar.php'; ?>
</body>
</html>
