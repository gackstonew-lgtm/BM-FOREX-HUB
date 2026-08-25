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
<title>BM FOREX HUB | Live Classes</title>
<meta name="description" content="Join our live interactive forex trading classes and learn SMC strategies.">
<link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
<meta name="robots" content="noindex, nofollow, noarchive">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
<link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
<link rel="stylesheet" href="css/mobile.css?v=<?= filemtime('css/mobile.css') ?>">

<style>
/* Push content right when sidebar visible */
@media (min-width: 900px) {
  body.has-sidebar .dash-main-wrap {
    margin-left: var(--sidebar-w, 260px);
    transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
}
@media (max-width: 899px) {
  .dash-sidebar {
    transform: translateX(-100%);
    transition: transform 0.25s ease;
  }
  .dash-sidebar.open {
    transform: translateX(0);
  }
  body.has-sidebar .dash-main-wrap {
    margin-left: 0;
  }
}

/* ===== NEW TOPBAR STYLE OVERRIDES ===== */
.topbar { background: #101722; border-bottom: 1px solid #1e2d42; }
.topbar .logo--text span { font-size: 0.85rem; font-weight: 700; letter-spacing: 0.06em; color: #fff; }
.topbar__user-info {
  display: flex; align-items: center; gap: 10px; color: #fff; font-size: 0.83rem;
}
.topbar__user-avatar {
  width: 34px; height: 34px; background: #1e2d42; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid rgba(22,119,255,0.35); flex-shrink: 0;
  font-size: 0.95rem; color: #8fa3b8; overflow: hidden;
}
.topbar__user-greeting { line-height: 1.2; }
.topbar__user-greeting small { display: block; color: #8fa3b8; font-size: 0.7rem; }
.topbar__user-greeting strong { font-weight: 600; }

.topbar__nav {
  display: flex;
  align-items: center;
  gap: 24px;
  margin: 0 auto;
}
.topbar__nav-link {
  color: #8fa3b8;
  text-decoration: none;
  font-size: 0.88rem;
  font-weight: 500;
  padding: 18px 0;
  border-bottom: 2px solid transparent;
  transition: color 0.15s, border-color 0.15s;
}
.topbar__nav-link:hover {
  color: #fff;
}
.topbar__nav-link.active {
  color: #fff;
  border-bottom-color: #1677FF;
}

@media (max-width: 768px) {
  .topbar__nav {
    display: none;
  }
}

/* ===== DASHBOARD OVERVIEW ===== */
.dash-overview {
  padding: 28px 30px 20px;
}
.dash-overview__title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.65rem; font-weight: 700; color: #fff; margin: 0 0 5px;
}
.dash-overview__subtitle { color: #8fa3b8; font-size: 0.88rem; margin: 0; }

/* ===== LIVE CLASSES VIEW CUSTOM STYLING ===== */
.live-classes-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 24px;
  margin-top: 24px;
  align-items: stretch;
}
.live-class-card {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.card-section-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.05rem;
  font-weight: 600;
  color: #fff;
  margin: 0;
}
.card-inner-box {
  background: #151d2a;
  border: 1px solid #1e2d42;
  border-radius: 14px;
  padding: 24px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.class-title-row {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 24px;
}
.class-video-icon {
  background: rgba(139, 92, 246, 0.12);
  border: 1px solid rgba(139, 92, 246, 0.25);
  border-radius: 10px;
  width: 48px;
  height: 48px;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #a78bfa;
  flex-shrink: 0;
}
.class-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.15rem;
  font-weight: 600;
  color: #fff;
  margin: 0;
  flex: 1;
}
.class-live-badge {
  background: rgba(34, 197, 94, 0.15);
  border: 1px solid rgba(34, 197, 94, 0.4);
  color: #22C55E;
  font-size: 0.67rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  flex-shrink: 0;
}
.class-details-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
  flex: 1;
}
.class-detail-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  font-size: 0.88rem;
  border-bottom: 1px solid rgba(30, 45, 66, 0.3);
  padding-bottom: 12px;
}
.class-detail-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}
.class-detail-label {
  width: 110px;
  color: #8fa3b8;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
}
.class-detail-label svg {
  color: #1677FF;
}
.class-detail-value {
  color: #fff;
  font-weight: 500;
  line-height: 1.4;
}
.join-class-btn {
  background: #1677FF;
  color: #fff;
  border: none;
  padding: 14px 20px;
  border-radius: 10px;
  font-size: 0.92rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-decoration: none;
  margin-top: 24px;
  transition: background .15s;
}
.join-class-btn:hover {
  background: #1565e0;
}
.join-class-btn.disabled {
  background: #1e2d42;
  color: #5b6475;
  cursor: not-allowed;
  pointer-events: none;
}

/* Meet session specific */
.meet-session-box {
  background: #151d2a;
}
.meet-status-badge {
  background: rgba(34, 197, 94, 0.08);
  border: 1px solid rgba(34, 197, 94, 0.2);
  color: #22C55E;
  font-size: 0.82rem;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 20px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 24px;
}
.meet-status-badge.not-ready {
  background: rgba(240, 180, 41, 0.08);
  border: 1px solid rgba(240, 180, 41, 0.2);
  color: #F0B429;
}
.meet-status-badge.completed {
  background: rgba(246, 70, 93, 0.08);
  border: 1px solid rgba(246, 70, 93, 0.2);
  color: #F6465D;
}
.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #22C55E;
}
.meet-status-badge.not-ready .status-dot {
  background: #F0B429;
}
.meet-status-badge.completed .status-dot {
  background: #F6465D;
}
.open-meet-btn {
  background: transparent;
  color: #fff;
  border: 1px solid #1e2d42;
  width: 100%;
  padding: 14px 20px;
  border-radius: 10px;
  font-size: 0.92rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-decoration: none;
  transition: background .15s, border-color .15s;
}
.open-meet-btn:hover {
  background: rgba(255, 255, 255, 0.03);
  border-color: #5b6475;
}
.open-meet-btn.disabled {
  border-color: #1e2d42;
  color: #5b6475;
  cursor: not-allowed;
  pointer-events: none;
}

/* Bottom row */
.live-classes-bottom-row {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 24px;
  margin-top: 24px;
}
.bottom-card {
  background: #151d2a;
  border: 1px solid #1e2d42;
  border-radius: 14px;
  padding: 20px 22px;
  display: flex;
  gap: 16px;
  align-items: flex-start;
}
.bottom-card-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.note-icon {
  background: rgba(22, 119, 255, 0.1);
  color: #1677FF;
}
.help-icon {
  background: rgba(143, 163, 184, 0.1);
  color: #8fa3b8;
}
.bottom-card-content {
  flex: 1;
}
.bottom-card-content h4 {
  margin: 0 0 6px;
  font-family: 'Space Grotesk', sans-serif;
  color: #fff;
  font-size: 0.95rem;
  font-weight: 600;
}
.bottom-card-content p {
  margin: 0;
  color: #8fa3b8;
  font-size: 0.8rem;
  line-height: 1.5;
}
.bottom-card-content a {
  display: inline-block;
  margin-top: 8px;
  color: #1677FF;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 500;
}
.bottom-card-content a:hover {
  text-decoration: underline;
}

@media (max-width: 900px) {
  .live-classes-grid,
  .live-classes-bottom-row {
    grid-template-columns: 1fr;
  }
}
</style>
<script>var API_BASE = '<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>';</script>
</head>
<body id="top" class="has-sidebar">

<!-- ===== Dashboard Sidebar ===== -->
<?php $activeNav = 'classes'; include __DIR__ . '/components/global_sidebar.php'; ?>

<div class="dash-main-wrap">
<!-- Unified Global Header -->
<?php include __DIR__ . '/components/global_header.php'; ?>

  <!-- Main Classes View -->
  <div class="dash-overview">
    
    <!-- Title block -->
    <div class="dash-overview__header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom: 24px;">
      <div>
        <h1 class="dash-overview__title">Live Forex Classes</h1>
        <p class="dash-overview__subtitle">Join our live interactive trading classes</p>
      </div>
      <div class="breadcrumbs" style="color:#8fa3b8; font-size:0.83rem;">
        <a href="index.php" style="color:#8fa3b8; text-decoration:none;">Dashboard</a> / <span style="color:#1677FF;">Classes</span>
      </div>
    </div>
    
    <!-- Main content grid -->
    <div class="live-classes-grid">
      
      <!-- Upcoming Live Class Card -->
      <div class="live-class-card">
        <h3 class="card-section-title">Upcoming Live Class</h3>
        <div class="card-inner-box">
          <div class="class-title-row">
            <div class="class-video-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            </div>
            <h2 class="class-title" id="classTitle">Loading active class...</h2>
            <span class="class-live-badge" id="classLiveBadge" style="display:none;">LIVE</span>
          </div>
          
          <div class="class-details-list">
            <div class="class-detail-item">
              <div class="class-detail-label">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Instructor:
              </div>
              <div class="class-detail-value" id="classInstructor">...</div>
            </div>
            <div class="class-detail-item">
              <div class="class-detail-label">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Date:
              </div>
              <div class="class-detail-value" id="classDate">...</div>
            </div>
            <div class="class-detail-item">
              <div class="class-detail-label">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Time:
              </div>
              <div class="class-detail-value" id="classTime">...</div>
            </div>
            <div class="class-detail-item">
              <div class="class-detail-label">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Duration:
              </div>
              <div class="class-detail-value" id="classDuration">...</div>
            </div>
            <div class="class-detail-item">
              <div class="class-detail-label" style="align-self: flex-start; margin-top: 2px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Description:
              </div>
              <div class="class-detail-value" id="classDescription">...</div>
            </div>
          </div>

          <!-- Real-Time Countdown Box -->
          <div class="countdown-wrap" id="countdownWrap" style="display:flex; gap:12px; margin: 16px 0; background: rgba(22,119,255,0.06); padding: 12px 16px; border-radius: 10px; border: 1px solid rgba(22,119,255,0.15); align-items:center; flex-wrap:wrap;">
            <div style="font-size:0.78rem; font-weight:600; color:#8fa3b8; text-transform:uppercase; letter-spacing:0.05em;" id="countdownLabel">Starts In:</div>
            <div style="display:flex; gap:8px; font-family:'Space Grotesk',sans-serif; font-weight:700; color:#fff;">
              <div style="background:#101722; padding:6px 10px; border-radius:6px; text-align:center; min-width:44px;"><span id="cdDays">00</span><small style="display:block; font-size:0.58rem; color:#8fa3b8; font-weight:400;">DAYS</small></div>
              <div style="background:#101722; padding:6px 10px; border-radius:6px; text-align:center; min-width:44px;"><span id="cdHours">00</span><small style="display:block; font-size:0.58rem; color:#8fa3b8; font-weight:400;">HRS</small></div>
              <div style="background:#101722; padding:6px 10px; border-radius:6px; text-align:center; min-width:44px;"><span id="cdMins">00</span><small style="display:block; font-size:0.58rem; color:#8fa3b8; font-weight:400;">MINS</small></div>
              <div style="background:#101722; padding:6px 10px; border-radius:6px; text-align:center; min-width:44px;"><span id="cdSecs">00</span><small style="display:block; font-size:0.58rem; color:#8fa3b8; font-weight:400;">SECS</small></div>
            </div>
          </div>
          
          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="#" target="_blank" class="join-class-btn" id="joinClassBtn" style="flex:2; margin-top:0;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
              Join Live Class
            </a>
            <a href="#" target="_blank" class="open-meet-btn" id="downloadNotesBtn" style="flex:1; display:none; margin-top:0; text-decoration:none;">
              📥 PDF Notes
            </a>
            <a href="#" target="_blank" class="open-meet-btn" id="watchRecordingBtn" style="flex:1; display:none; margin-top:0; text-decoration:none;">
              🎬 Recording
            </a>
          </div>
        </div>
      </div>
      
      <!-- Google Meet Session Card -->
      <div class="live-class-card">
        <h3 class="card-section-title">Google Meet Session</h3>
        <div class="card-inner-box meet-session-box" style="display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding: 40px 24px;">
          <!-- Google Meet SVG Logo -->
          <div style="margin-bottom: 24px;">
            <!-- Custom Google Meet overlapping colored icon -->
            <svg width="76" height="76" viewBox="0 0 87.5 72" xmlns="http://www.w3.org/2000/svg">
              <path fill="#00832d" d="M49.5 36l8.53 9.75 11.47 7.33 2-17.02-2-16.64-11.69 6.44z"/>
              <path fill="#0066da" d="M0 51.5V66c0 3.315 2.685 6 6 6h14.5l3-10.96-3-9.54-9.95-3z"/>
              <path fill="#e94235" d="M20.5 0L0 20.5l10.55 3 9.95-3 2.95-9.41z"/>
              <path fill="#2684fc" d="M20.5 20.5H0v31h20.5z"/>
              <path fill="#00ac47" d="M82.6 8.68L69.5 19.42v33.66l13.16 10.79c1.97 1.54 4.85.135 4.85-2.37V11c0-2.535-2.945-3.925-4.91-2.32zM49.5 36v15.5h-29V72h43c3.315 0 6-2.685 6-6V53.08z"/>
              <path fill="#ffba00" d="M63.5 0h-43v20.5h29V36l20-16.57V6c0-3.315-2.685-6-6-6z"/>
            </svg>
          </div>
          
          <p style="color:#8fa3b8; font-size:0.88rem; line-height:1.5; max-width:280px; margin:0 0 20px;">
            The live class will start soon. Click the button to join the Google Meet.
          </p>
          
          <div class="meet-status-badge" id="meetStatusBadge">
            <span class="status-dot"></span>
            <span id="meetStatusText">Meeting is ready</span>
          </div>
          
          <a href="#" target="_blank" class="open-meet-btn" id="openMeetBtn">
            Open in Google Meet
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          </a>
        </div>
      </div>
      
    </div>
    
    <!-- Bottom row: Note and Help -->
    <div class="live-classes-bottom-row">
      <div class="bottom-card note-card">
        <div class="bottom-card-icon note-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        </div>
        <div class="bottom-card-content">
          <h4>Important Note</h4>
          <p>Please join the class 5-10 minutes before the start time to ensure a smooth experience. Check your internet connection and audio.</p>
        </div>
      </div>
      <div class="bottom-card help-card">
        <div class="bottom-card-icon help-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><circle cx="9" cy="10" r="1"/><circle cx="15" cy="10" r="1"/></svg>
        </div>
        <div class="bottom-card-content">
          <h4>Need Help?</h4>
          <p>If you face any issues joining the class, contact our support team.</p>
          <a href="https://wa.me/message/K5RM7MSWXBNPC1" target="_blank" rel="noopener noreferrer">Contact Support &rarr;</a>
        </div>
      </div>
    </div>
    
  </div>
  
  <!-- Footer -->
  <footer style="margin-top: 40px; padding: 20px 24px; border-top: 1px solid #1e2d42; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; font-size: 0.77rem; color: #5b6475;">
    <div>&copy; 2024 BM Forex Hub. All Rights Reserved.</div>
    <div style="display: flex; gap: 16px;">
      <a href="terms.php" style="color:#5b6475; text-decoration:none;">Terms of Use</a>
      <span style="color:#1e2d42;">|</span>
      <a href="privacy.php" style="color:#5b6475; text-decoration:none;">Privacy Policy</a>
      <span style="color:#1e2d42;">|</span>
      <a href="risk-disclosure.php" style="color:#5b6475; text-decoration:none;">Risk Disclaimer</a>
      <span style="color:#1e2d42;">|</span>
      <a href="contact.php" style="color:#5b6475; text-decoration:none;">Support</a>
    </div>
  </footer>
</div>

<!-- Scripts for Supabase, Auth and Dynamic UI functionality -->
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script>
(async function() {
  /* ---------- auth guard ---------- */
  const { user, session } = await BMAuth.getSession();
  if (!user) { window.location.href = 'login.php'; return; }

  let authData = await BMAuth.getMembershipStatus(user, session);
  let hasClasses = authData.plans.includes('all') || authData.plans.some(function(p) { return p.startsWith('classes_'); });

  // If limited subscriber, redirect to payment page
  if (!hasClasses) {
    window.location.href = 'subscribe.php?plan=classes';
    return;
  }

  const userDisplayName = BMAuth.displayName(user);
  const displayUserEl = document.getElementById('displayUsername');
  if (displayUserEl) displayUserEl.textContent = userDisplayName;

  // Initialize announcements bell list
  initAnnouncements();
  
  // Load classes from Supabase
  loadLiveClassesData();
})();

async function clientSideTrialCheck(user) {
  try {
    const sb = BMAuth.getClient();
    const now = new Date().toISOString();
    const { data: subs, error: subErr } = await sb.from('subscriptions')
      .select('id,status,expires_at')
      .eq('user_id', user.id)
      .eq('status', 'active')
      .gt('expires_at', now);
    if (!subErr && subs && subs.length > 0) return 'full';

    const { data: prof, error: profErr } = await sb.from('profiles')
      .select('trial_started_at')
      .eq('id', user.id)
      .single();
    if (profErr || !prof || !prof.trial_started_at) return 'limited';

    const trialEnd = new Date(prof.trial_started_at).getTime() + (3 * 86400000);
    if (Date.now() < trialEnd) return 'trial';
    return 'limited';
  } catch(e) {
    return 'limited';
  }
}

function initAnnouncements() {
  const bellBtn = document.getElementById('bellBtn');
  const bellDropdown = document.getElementById('bellDropdown');
  const bellBadge = document.getElementById('bellBadge');
  const bellList = document.getElementById('bellList');

  if (bellBtn && bellDropdown) {
    bellBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const visible = !bellDropdown.hidden;
      bellDropdown.hidden = visible;
      bellBtn.setAttribute('aria-expanded', !visible);
    });
    document.addEventListener('click', () => {
      bellDropdown.hidden = true;
      bellBtn.setAttribute('aria-expanded', 'false');
    });
  }

  fetch('https://iwoytmcxmbhmmbrbpzvf.supabase.co/rest/v1/announcements?active=eq.true&order=created_at.desc', {
    headers: {
      'apikey': 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml3b3l0bWN4bWJobW1icmJwenZmIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODM5ODM1NjEsImV4cCI6MjA5OTU1OTU2MX0.cKpLKN-Azoj7UUUp3_uodYraYlJH4fQtKpyRitnbMgk'
    }
  })
  .then(r => r.json())
  .then(items => {
    if (!items || !items.length) {
      if (bellList) bellList.innerHTML = '<div class="bell-empty">No announcements yet.</div>';
      return;
    }
    if (bellBadge) {
      bellBadge.textContent = items.length;
      bellBadge.hidden = false;
    }
    if (bellList) {
      bellList.innerHTML = items.map(a => {
        const d = a.created_at ? new Date(a.created_at).toLocaleDateString() : '';
        return `<div class="bell-item">
          <div class="bell-item__title">${esc(a.title)}</div>
          <div class="bell-item__msg">${esc(a.message)}</div>
          ${d ? `<div class="bell-item__date">${d}</div>` : ''}
        </div>`;
      }).join('');
    }
  })
  .catch(() => {
    if (bellList) bellList.innerHTML = '<div class="bell-empty">Could not load announcements.</div>';
  });
}

function esc(s) {
  const d = document.createElement('div');
  d.textContent = s || '';
  return d.innerHTML;
}

let countdownInterval = null;

async function loadLiveClassesData() {
  try {
    let classes = [];
    
    // 1. Fetch from dynamic PHP API
    try {
      const res = await fetch('api/classes.php');
      const data = await res.json();
      if (data.success && Array.isArray(data.data) && data.data.length > 0) {
        classes = data.data;
      }
    } catch(e) {}

    // 2. Fallback: Supabase Client
    if (!classes.length) {
      try {
        const sb = BMAuth.getClient();
        const { data, error } = await sb.from('live_classes')
          .select('*')
          .order('is_live', { ascending: false })
          .order('created_at', { ascending: false })
          .limit(5);
        if (!error && data && data.length > 0) {
          classes = data;
        }
      } catch(e) {}
    }

    if (!classes || classes.length === 0) {
      displayFallbackClass();
      return;
    }

    const c = classes[0];

    // Populate class card
    document.getElementById('classTitle').textContent = c.title;
    document.getElementById('classInstructor').textContent = c.instructor_name || c.instructor || 'BM Forex Hub Team';
    document.getElementById('classDate').textContent = c.formatted_date || c.class_date || 'TBA';
    document.getElementById('classTime').textContent = c.class_time || 'TBA';
    document.getElementById('classDuration').textContent = (c.duration_minutes || 60) + ' Minutes';
    document.getElementById('classDescription').textContent = c.description || 'No description provided for this session.';

    // Configure Live Badge
    const liveBadge = document.getElementById('classLiveBadge');
    liveBadge.style.display = (c.is_live || c.live_status === 'live') ? '' : 'none';

    // PDF Notes & Recording Buttons
    const downloadNotesBtn = document.getElementById('downloadNotesBtn');
    if (downloadNotesBtn) {
      if (c.class_notes_pdf) {
        downloadNotesBtn.href = c.class_notes_pdf;
        downloadNotesBtn.style.display = '';
      } else {
        downloadNotesBtn.style.display = 'none';
      }
    }

    const watchRecordingBtn = document.getElementById('watchRecordingBtn');
    if (watchRecordingBtn) {
      if (c.recording_link) {
        watchRecordingBtn.href = c.recording_link;
        watchRecordingBtn.style.display = '';
      } else {
        watchRecordingBtn.style.display = 'none';
      }
    }

    const joinClassBtn = document.getElementById('joinClassBtn');
    const openMeetBtn = document.getElementById('openMeetBtn');
    const badge = document.getElementById('meetStatusBadge');
    const text = document.getElementById('meetStatusText');

    badge.className = 'meet-status-badge';

    // Start Real-Time Countdown Timer
    if (countdownInterval) clearInterval(countdownInterval);

    const startTs = c.start_datetime ? new Date(c.start_datetime).getTime() : Date.now();
    const durationMs = (c.duration_minutes || 60) * 60 * 1000;
    const endTs = c.end_datetime ? new Date(c.end_datetime).getTime() : (startTs + durationMs);

    function updateTimer() {
      const now = Date.now();

      if (c.is_live || (now >= startTs && now <= endTs)) {
        // CLASS IS LIVE NOW
        document.getElementById('cdDays').textContent = '00';
        document.getElementById('cdHours').textContent = '00';
        document.getElementById('cdMins').textContent = '00';
        document.getElementById('cdSecs').textContent = '00';
        document.getElementById('countdownLabel').textContent = 'STATUS:';
        liveBadge.style.display = '';
        liveBadge.textContent = 'LIVE NOW';

        badge.className = 'meet-status-badge';
        text.textContent = 'Meeting is open & live';

        if (c.meeting_link) {
          joinClassBtn.href = c.meeting_link;
          joinClassBtn.classList.remove('disabled');
          joinClassBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg> Join Live Class Now';
          openMeetBtn.href = c.meeting_link;
          openMeetBtn.classList.remove('disabled');
        }
      } else if (now < startTs) {
        // UPCOMING / SCHEDULED
        const diff = Math.floor((startTs - now) / 1000);
        const days = Math.floor(diff / 86400);
        const hrs = Math.floor((diff % 86400) / 3600);
        const mins = Math.floor((diff % 3600) / 60);
        const secs = diff % 60;

        document.getElementById('cdDays').textContent = String(days).padStart(2, '0');
        document.getElementById('cdHours').textContent = String(hrs).padStart(2, '0');
        document.getElementById('cdMins').textContent = String(mins).padStart(2, '0');
        document.getElementById('cdSecs').textContent = String(secs).padStart(2, '0');
        document.getElementById('countdownLabel').textContent = 'Starts In:';

        liveBadge.style.display = 'none';
        badge.className = 'meet-status-badge not-ready';
        text.textContent = 'Meeting scheduled';

        if (c.meeting_link && c.meeting_ready) {
          joinClassBtn.href = c.meeting_link;
          joinClassBtn.classList.remove('disabled');
          openMeetBtn.href = c.meeting_link;
          openMeetBtn.classList.remove('disabled');
        } else {
          joinClassBtn.classList.add('disabled');
          openMeetBtn.classList.add('disabled');
        }
      } else {
        // ENDED
        document.getElementById('cdDays').textContent = '00';
        document.getElementById('cdHours').textContent = '00';
        document.getElementById('cdMins').textContent = '00';
        document.getElementById('cdSecs').textContent = '00';
        document.getElementById('countdownLabel').textContent = 'STATUS:';
        liveBadge.style.display = 'none';

        badge.className = 'meet-status-badge completed';
        text.textContent = 'Class Completed';

        joinClassBtn.classList.add('disabled');
        joinClassBtn.textContent = 'Class Ended';
        openMeetBtn.classList.add('disabled');
      }
    }

    updateTimer();
    countdownInterval = setInterval(updateTimer, 1000);

  } catch(e) {
    displayFallbackClass();
  }
}

function displayFallbackClass() {
  document.getElementById('classTitle').textContent = 'No Scheduled Class';
  document.getElementById('classInstructor').textContent = 'BM Forex Hub Team';
  document.getElementById('classDate').textContent = 'TBA';
  document.getElementById('classTime').textContent = 'TBA';
  document.getElementById('classDuration').textContent = '0 Minutes';
  document.getElementById('classDescription').textContent = 'Please check back later. Our next live forex trading session will be scheduled soon.';
  
  document.getElementById('classLiveBadge').style.display = 'none';
  document.getElementById('joinClassBtn').classList.add('disabled');
  document.getElementById('openMeetBtn').classList.add('disabled');
  
  const badge = document.getElementById('meetStatusBadge');
  const text = document.getElementById('meetStatusText');
  badge.className = 'meet-status-badge not-ready';
  text.textContent = 'No meeting scheduled';
}

/* ---------- mobile hamburger navigation ---------- */
const hamburgerBtn = document.getElementById('hamburgerBtn');
const mobileNav = document.getElementById('mobileNav');
const mobileNavClose = document.getElementById('mobileNavClose');

if (hamburgerBtn && mobileNav) {
  const openMenu = () => {
    mobileNav.classList.add('open');
    hamburgerBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  };
  const closeMenu = () => {
    mobileNav.classList.remove('open');
    hamburgerBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  };
  hamburgerBtn.addEventListener('click', openMenu);
  if (mobileNavClose) mobileNavClose.addEventListener('click', closeMenu);
  mobileNav.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));
  mobileNav.addEventListener('click', e => { if (e.target === mobileNav) closeMenu(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && mobileNav.classList.contains('open')) closeMenu(); });
}
</script>
<script src="js/motion.js" defer></script>
<?php $activeTab = 'learn'; include __DIR__ . '/components/mobile-tabbar.php'; ?>
</body>
</html>
