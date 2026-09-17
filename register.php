<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require_once __DIR__ . '/components/pwa_meta.php'; ?>
<title>Create Account | BM Forex Hub Signal Terminal</title>
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

    <!-- STEP 1: Registration Form -->
    <div class="auth-form-wrap" id="step1">
      <h1>Create your account</h1>
      <p class="auth-sub">Already have an account? <a href="login.php" style="color:var(--gold);text-decoration:underline">Sign in</a></p>

      <div class="auth-alert" id="regError" hidden></div>
      <div class="auth-alert auth-success" id="regSuccess" hidden></div>

      <form id="registerForm" novalidate>
        <div class="auth-phone-row">
          <label class="auth-field">
            <span>First Name</span>
            <input type="text" name="first_name" id="regFirstName" autocomplete="given-name" maxlength="50" required>
          </label>
          <label class="auth-field">
            <span>Last Name</span>
            <input type="text" name="last_name" id="regLastName" autocomplete="family-name" maxlength="50" required>
          </label>
        </div>

        <label class="auth-field">
          <span>Email</span>
          <input type="email" name="email" id="regEmail" autocomplete="email" required>
        </label>

        <div class="auth-phone-row">
          <label class="auth-field">
            <span>Country</span>
            <select id="regCountry" required></select>
          </label>
          <label class="auth-field">
            <span>Phone Number</span>
            <input type="tel" name="phone" id="regPhone" placeholder="712345678" autocomplete="tel" required>
          </label>
        </div>

        <label class="auth-field auth-field--password">
          <span>Password</span>
          <div class="auth-pw-wrap">
          <input type="password" name="password" id="regPassword" autocomplete="new-password" minlength="8" required>
          <button type="button" class="auth-toggle-pw" aria-label="Show password" data-target="regPassword">
            <svg class="eye-open" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-closed" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
          </button>
          </div>
        </label>

        <label class="auth-field auth-field--password">
          <span>Confirm password</span>
          <div class="auth-pw-wrap">
          <input type="password" name="confirm_password" id="regConfirm" autocomplete="new-password" minlength="8" required>
          <button type="button" class="auth-toggle-pw" aria-label="Show password" data-target="regConfirm">
            <svg class="eye-open" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-closed" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
          </button>
          </div>
        </label>

        <label class="auth-field" style="display:flex;align-items:flex-start;gap:10px;cursor:pointer">
          <input type="checkbox" id="regTerms" style="width:18px;height:18px;margin-top:3px;accent-color:var(--gold);flex-shrink:0" required>
          <span style="font-size:.82rem;color:var(--muted);line-height:1.5;text-transform:none;letter-spacing:0;font-family:Inter,sans-serif">I agree to the <a href="terms.php" target="_blank" style="color:var(--gold);text-decoration:underline">Terms &amp; Conditions</a> and <a href="privacy.php" target="_blank" style="color:var(--gold);text-decoration:underline">Privacy Policy</a></span>
        </label>

        <button type="submit" class="btn btn-primary auth-submit" id="regBtn">Create account</button>
      </form>

      <div class="auth-divider"><span>OR</span></div>

      <button type="button" class="auth-google-btn" id="googleRegBtn">
        <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
        <span>Continue with Google</span>
      </button>

      <p class="auth-footnote">Already have an account? <a href="login.php">Sign in</a></p>
    </div>

    <!-- STEP 2: OTP Verification -->
    <div class="auth-otp-wrap" id="step2">
      <h1>Verify your email</h1>
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

      <button class="btn btn-primary auth-submit" id="verifyBtn">Verify</button>

      <p class="auth-footnote"><a href="register.php" id="backToReg">Back to registration</a></p>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
<script src="js/auth.js?v=<?= filemtime('js/auth.js') ?>"></script>
<script>
(function() {
  /* ---- elements ---- */
  var form      = document.getElementById('registerForm');
  var errEl     = document.getElementById('regError');
  var succEl    = document.getElementById('regSuccess');
  var btn       = document.getElementById('regBtn');
  var googleBtn = document.getElementById('googleRegBtn');

  if (googleBtn) {
    googleBtn.addEventListener('click', async function() {
      hideAlerts();
      googleBtn.disabled = true;
      googleBtn.querySelector('span').textContent = 'Connecting to Google...';
      var result = await BMAuth.signInWithGoogle();
      if (result.error) {
        googleBtn.disabled = false;
        googleBtn.querySelector('span').textContent = 'Continue with Google';
        showErr(result.error);
      }
    });
  }
  var step1     = document.getElementById('step1');
  var step2     = document.getElementById('step2');
  var otpEmail  = document.getElementById('otpEmail');
  var otpErr    = document.getElementById('otpError');
  var otpInputs = document.querySelectorAll('#otpInputs input');
  var verifyBtn = document.getElementById('verifyBtn');
  var resendBtn = document.getElementById('resendBtn');

  var _email = '';
  var _password = '';
  var _firstName = '';
  var _lastName = '';
  var _countryCode = '';
  var _phone = '';
  var _resendTimer = null;

  function showErr(msg)  { errEl.textContent = (typeof msg === 'object' && msg !== null) ? JSON.stringify(msg) : String(msg || 'An error occurred'); errEl.hidden = false; succEl.hidden = true; }
  function showSucc(msg) { succEl.textContent = msg; succEl.hidden = false; errEl.hidden = true; }
  function hideAlerts()  { errEl.hidden = true; succEl.hidden = true; }

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

  /* ---- country dropdown ---- */
  var countries = [
    {c:'AF',d:'+93',n:'Afghanistan'},
    {c:'AX',d:'+35818',n:'Åland Islands'},
    {c:'AL',d:'+355',n:'Albania'},
    {c:'DZ',d:'+213',n:'Algeria'},
    {c:'AS',d:'+1684',n:'American Samoa'},
    {c:'AD',d:'+376',n:'Andorra'},
    {c:'AO',d:'+244',n:'Angola'},
    {c:'AI',d:'+1264',n:'Anguilla'},
    {c:'AG',d:'+1268',n:'Antigua and Barbuda'},
    {c:'AR',d:'+54',n:'Argentina'},
    {c:'AM',d:'+374',n:'Armenia'},
    {c:'AW',d:'+297',n:'Aruba'},
    {c:'AU',d:'+61',n:'Australia'},
    {c:'AT',d:'+43',n:'Austria'},
    {c:'AZ',d:'+994',n:'Azerbaijan'},
    {c:'BS',d:'+1242',n:'Bahamas'},
    {c:'BH',d:'+973',n:'Bahrain'},
    {c:'BD',d:'+880',n:'Bangladesh'},
    {c:'BB',d:'+1246',n:'Barbados'},
    {c:'BY',d:'+375',n:'Belarus'},
    {c:'BE',d:'+32',n:'Belgium'},
    {c:'BZ',d:'+501',n:'Belize'},
    {c:'BJ',d:'+229',n:'Benin'},
    {c:'BM',d:'+1441',n:'Bermuda'},
    {c:'BT',d:'+975',n:'Bhutan'},
    {c:'BO',d:'+591',n:'Bolivia'},
    {c:'BA',d:'+387',n:'Bosnia and Herzegovina'},
    {c:'BW',d:'+267',n:'Botswana'},
    {c:'BV',d:'+47',n:'Bouvet Island'},
    {c:'BR',d:'+55',n:'Brazil'},
    {c:'IO',d:'+246',n:'British Indian Ocean Territory'},
    {c:'VG',d:'+1284',n:'British Virgin Islands'},
    {c:'BN',d:'+673',n:'Brunei'},
    {c:'BG',d:'+359',n:'Bulgaria'},
    {c:'BF',d:'+226',n:'Burkina Faso'},
    {c:'BI',d:'+257',n:'Burundi'},
    {c:'KH',d:'+855',n:'Cambodia'},
    {c:'CM',d:'+237',n:'Cameroon'},
    {c:'CA',d:'+1',n:'Canada'},
    {c:'CV',d:'+238',n:'Cape Verde'},
    {c:'BQ',d:'+599',n:'Caribbean Netherlands'},
    {c:'KY',d:'+1345',n:'Cayman Islands'},
    {c:'CF',d:'+236',n:'Central African Republic'},
    {c:'TD',d:'+235',n:'Chad'},
    {c:'CL',d:'+56',n:'Chile'},
    {c:'CN',d:'+86',n:'China'},
    {c:'CX',d:'+61',n:'Christmas Island'},
    {c:'CC',d:'+61',n:'Cocos (Keeling) Islands'},
    {c:'CO',d:'+57',n:'Colombia'},
    {c:'KM',d:'+269',n:'Comoros'},
    {c:'CG',d:'+242',n:'Congo'},
    {c:'CK',d:'+682',n:'Cook Islands'},
    {c:'CR',d:'+506',n:'Costa Rica'},
    {c:'HR',d:'+385',n:'Croatia'},
    {c:'CU',d:'+53',n:'Cuba'},
    {c:'CW',d:'+599',n:'Curaçao'},
    {c:'CY',d:'+357',n:'Cyprus'},
    {c:'CZ',d:'+420',n:'Czechia'},
    {c:'DK',d:'+45',n:'Denmark'},
    {c:'DJ',d:'+253',n:'Djibouti'},
    {c:'DM',d:'+1767',n:'Dominica'},
    {c:'DO',d:'+1809',n:'Dominican Republic'},
    {c:'DO',d:'+1829',n:'Dominican Republic'},
    {c:'DO',d:'+1849',n:'Dominican Republic'},
    {c:'CD',d:'+243',n:'DR Congo'},
    {c:'EC',d:'+593',n:'Ecuador'},
    {c:'EG',d:'+20',n:'Egypt'},
    {c:'SV',d:'+503',n:'El Salvador'},
    {c:'GQ',d:'+240',n:'Equatorial Guinea'},
    {c:'ER',d:'+291',n:'Eritrea'},
    {c:'EE',d:'+372',n:'Estonia'},
    {c:'SZ',d:'+268',n:'Eswatini'},
    {c:'ET',d:'+251',n:'Ethiopia'},
    {c:'FK',d:'+500',n:'Falkland Islands'},
    {c:'FO',d:'+298',n:'Faroe Islands'},
    {c:'FJ',d:'+679',n:'Fiji'},
    {c:'FI',d:'+358',n:'Finland'},
    {c:'FR',d:'+33',n:'France'},
    {c:'GF',d:'+594',n:'French Guiana'},
    {c:'PF',d:'+689',n:'French Polynesia'},
    {c:'TF',d:'+262',n:'French Southern and Antarctic Lands'},
    {c:'GA',d:'+241',n:'Gabon'},
    {c:'GM',d:'+220',n:'Gambia'},
    {c:'GE',d:'+995',n:'Georgia'},
    {c:'DE',d:'+49',n:'Germany'},
    {c:'GH',d:'+233',n:'Ghana'},
    {c:'GI',d:'+350',n:'Gibraltar'},
    {c:'GR',d:'+30',n:'Greece'},
    {c:'GL',d:'+299',n:'Greenland'},
    {c:'GD',d:'+1473',n:'Grenada'},
    {c:'GP',d:'+590',n:'Guadeloupe'},
    {c:'GU',d:'+1671',n:'Guam'},
    {c:'GT',d:'+502',n:'Guatemala'},
    {c:'GG',d:'+44',n:'Guernsey'},
    {c:'GN',d:'+224',n:'Guinea'},
    {c:'GW',d:'+245',n:'Guinea-Bissau'},
    {c:'GY',d:'+592',n:'Guyana'},
    {c:'HT',d:'+509',n:'Haiti'},
    {c:'HN',d:'+504',n:'Honduras'},
    {c:'HK',d:'+852',n:'Hong Kong'},
    {c:'HU',d:'+36',n:'Hungary'},
    {c:'IS',d:'+354',n:'Iceland'},
    {c:'IN',d:'+91',n:'India'},
    {c:'ID',d:'+62',n:'Indonesia'},
    {c:'IR',d:'+98',n:'Iran'},
    {c:'IQ',d:'+964',n:'Iraq'},
    {c:'IE',d:'+353',n:'Ireland'},
    {c:'IM',d:'+44',n:'Isle of Man'},
    {c:'IL',d:'+972',n:'Israel'},
    {c:'IT',d:'+39',n:'Italy'},
    {c:'CI',d:'+225',n:'Ivory Coast'},
    {c:'JM',d:'+1876',n:'Jamaica'},
    {c:'JP',d:'+81',n:'Japan'},
    {c:'JE',d:'+44',n:'Jersey'},
    {c:'JO',d:'+962',n:'Jordan'},
    {c:'KZ',d:'+7',n:'Kazakhstan'},
    {c:'KE',d:'+254',n:'Kenya'},
    {c:'KI',d:'+686',n:'Kiribati'},
    {c:'XK',d:'+383',n:'Kosovo'},
    {c:'KW',d:'+965',n:'Kuwait'},
    {c:'KG',d:'+996',n:'Kyrgyzstan'},
    {c:'LA',d:'+856',n:'Laos'},
    {c:'LV',d:'+371',n:'Latvia'},
    {c:'LB',d:'+961',n:'Lebanon'},
    {c:'LS',d:'+266',n:'Lesotho'},
    {c:'LR',d:'+231',n:'Liberia'},
    {c:'LY',d:'+218',n:'Libya'},
    {c:'LI',d:'+423',n:'Liechtenstein'},
    {c:'LT',d:'+370',n:'Lithuania'},
    {c:'LU',d:'+352',n:'Luxembourg'},
    {c:'MO',d:'+853',n:'Macau'},
    {c:'MG',d:'+261',n:'Madagascar'},
    {c:'MW',d:'+265',n:'Malawi'},
    {c:'MY',d:'+60',n:'Malaysia'},
    {c:'MV',d:'+960',n:'Maldives'},
    {c:'ML',d:'+223',n:'Mali'},
    {c:'MT',d:'+356',n:'Malta'},
    {c:'MH',d:'+692',n:'Marshall Islands'},
    {c:'MQ',d:'+596',n:'Martinique'},
    {c:'MR',d:'+222',n:'Mauritania'},
    {c:'MU',d:'+230',n:'Mauritius'},
    {c:'YT',d:'+262',n:'Mayotte'},
    {c:'MX',d:'+52',n:'Mexico'},
    {c:'FM',d:'+691',n:'Micronesia'},
    {c:'MD',d:'+373',n:'Moldova'},
    {c:'MC',d:'+377',n:'Monaco'},
    {c:'MN',d:'+976',n:'Mongolia'},
    {c:'ME',d:'+382',n:'Montenegro'},
    {c:'MS',d:'+1664',n:'Montserrat'},
    {c:'MA',d:'+212',n:'Morocco'},
    {c:'MZ',d:'+258',n:'Mozambique'},
    {c:'MM',d:'+95',n:'Myanmar'},
    {c:'NA',d:'+264',n:'Namibia'},
    {c:'NR',d:'+674',n:'Nauru'},
    {c:'NP',d:'+977',n:'Nepal'},
    {c:'NL',d:'+31',n:'Netherlands'},
    {c:'NC',d:'+687',n:'New Caledonia'},
    {c:'NZ',d:'+64',n:'New Zealand'},
    {c:'NI',d:'+505',n:'Nicaragua'},
    {c:'NE',d:'+227',n:'Niger'},
    {c:'NG',d:'+234',n:'Nigeria'},
    {c:'NU',d:'+683',n:'Niue'},
    {c:'NF',d:'+672',n:'Norfolk Island'},
    {c:'KP',d:'+850',n:'North Korea'},
    {c:'MK',d:'+389',n:'North Macedonia'},
    {c:'MP',d:'+1670',n:'Northern Mariana Islands'},
    {c:'NO',d:'+47',n:'Norway'},
    {c:'OM',d:'+968',n:'Oman'},
    {c:'PK',d:'+92',n:'Pakistan'},
    {c:'PW',d:'+680',n:'Palau'},
    {c:'PS',d:'+970',n:'Palestine'},
    {c:'PA',d:'+507',n:'Panama'},
    {c:'PG',d:'+675',n:'Papua New Guinea'},
    {c:'PY',d:'+595',n:'Paraguay'},
    {c:'PE',d:'+51',n:'Peru'},
    {c:'PH',d:'+63',n:'Philippines'},
    {c:'PN',d:'+64',n:'Pitcairn Islands'},
    {c:'PL',d:'+48',n:'Poland'},
    {c:'PT',d:'+351',n:'Portugal'},
    {c:'PR',d:'+1787',n:'Puerto Rico'},
    {c:'PR',d:'+1939',n:'Puerto Rico'},
    {c:'QA',d:'+974',n:'Qatar'},
    {c:'RE',d:'+262',n:'Réunion'},
    {c:'RO',d:'+40',n:'Romania'},
    {c:'RU',d:'+7',n:'Russia'},
    {c:'RW',d:'+250',n:'Rwanda'},
    {c:'BL',d:'+590',n:'Saint Barthélemy'},
    {c:'SH',d:'+290',n:'Saint Helena, Ascension and Tristan da Cunha'},
    {c:'SH',d:'+247',n:'Saint Helena, Ascension and Tristan da Cunha'},
    {c:'KN',d:'+1869',n:'Saint Kitts and Nevis'},
    {c:'LC',d:'+1758',n:'Saint Lucia'},
    {c:'MF',d:'+590',n:'Saint Martin'},
    {c:'PM',d:'+508',n:'Saint Pierre and Miquelon'},
    {c:'VC',d:'+1784',n:'Saint Vincent and the Grenadines'},
    {c:'WS',d:'+685',n:'Samoa'},
    {c:'SM',d:'+378',n:'San Marino'},
    {c:'ST',d:'+239',n:'São Tomé and Príncipe'},
    {c:'SA',d:'+966',n:'Saudi Arabia'},
    {c:'SN',d:'+221',n:'Senegal'},
    {c:'RS',d:'+381',n:'Serbia'},
    {c:'SC',d:'+248',n:'Seychelles'},
    {c:'SL',d:'+232',n:'Sierra Leone'},
    {c:'SG',d:'+65',n:'Singapore'},
    {c:'SX',d:'+1721',n:'Sint Maarten'},
    {c:'SK',d:'+421',n:'Slovakia'},
    {c:'SI',d:'+386',n:'Slovenia'},
    {c:'SB',d:'+677',n:'Solomon Islands'},
    {c:'SO',d:'+252',n:'Somalia'},
    {c:'ZA',d:'+27',n:'South Africa'},
    {c:'GS',d:'+500',n:'South Georgia'},
    {c:'KR',d:'+82',n:'South Korea'},
    {c:'SS',d:'+211',n:'South Sudan'},
    {c:'ES',d:'+34',n:'Spain'},
    {c:'LK',d:'+94',n:'Sri Lanka'},
    {c:'SD',d:'+249',n:'Sudan'},
    {c:'SR',d:'+597',n:'Suriname'},
    {c:'SJ',d:'+4779',n:'Svalbard and Jan Mayen'},
    {c:'SE',d:'+46',n:'Sweden'},
    {c:'CH',d:'+41',n:'Switzerland'},
    {c:'SY',d:'+963',n:'Syria'},
    {c:'TW',d:'+886',n:'Taiwan'},
    {c:'TJ',d:'+992',n:'Tajikistan'},
    {c:'TZ',d:'+255',n:'Tanzania'},
    {c:'TH',d:'+66',n:'Thailand'},
    {c:'TL',d:'+670',n:'Timor-Leste'},
    {c:'TG',d:'+228',n:'Togo'},
    {c:'TK',d:'+690',n:'Tokelau'},
    {c:'TO',d:'+676',n:'Tonga'},
    {c:'TT',d:'+1868',n:'Trinidad and Tobago'},
    {c:'TN',d:'+216',n:'Tunisia'},
    {c:'TR',d:'+90',n:'Türkiye'},
    {c:'TM',d:'+993',n:'Turkmenistan'},
    {c:'TC',d:'+1649',n:'Turks and Caicos Islands'},
    {c:'TV',d:'+688',n:'Tuvalu'},
    {c:'UG',d:'+256',n:'Uganda'},
    {c:'UA',d:'+380',n:'Ukraine'},
    {c:'AE',d:'+971',n:'United Arab Emirates'},
    {c:'GB',d:'+44',n:'United Kingdom'},
    {c:'US',d:'+1',n:'United States'},
    {c:'UM',d:'+268',n:'United States Minor Outlying Islands'},
    {c:'VI',d:'+1340',n:'United States Virgin Islands'},
    {c:'UY',d:'+598',n:'Uruguay'},
    {c:'UZ',d:'+998',n:'Uzbekistan'},
    {c:'VU',d:'+678',n:'Vanuatu'},
    {c:'VA',d:'+3906698',n:'Vatican City'},
    {c:'VA',d:'+379',n:'Vatican City'},
    {c:'VE',d:'+58',n:'Venezuela'},
    {c:'VN',d:'+84',n:'Vietnam'},
    {c:'WF',d:'+681',n:'Wallis and Futuna'},
    {c:'EH',d:'+2125288',n:'Western Sahara'},
    {c:'EH',d:'+2125289',n:'Western Sahara'},
    {c:'YE',d:'+967',n:'Yemen'},
    {c:'ZM',d:'+260',n:'Zambia'},
    {c:'ZW',d:'+263',n:'Zimbabwe'}
  ];

  /* deduplicate by code */
  var seen = {};
  var unique = [];
  countries.forEach(function(c) {
    if (!seen[c.c]) { seen[c.c] = true; unique.push(c); }
  });
  countries = unique;

  /* sort by country name */
  countries.sort(function(a, b) { return a.n.localeCompare(b.n); });

  var select = document.getElementById('regCountry');
  var defaultOpt = document.createElement('option');
  defaultOpt.value = '';
  defaultOpt.textContent = 'Select country';
  defaultOpt.disabled = true;
  defaultOpt.selected = true;
  select.appendChild(defaultOpt);

  countries.forEach(function(c) {
    var opt = document.createElement('option');
    opt.value = c.d;
    opt.textContent = c.d + '  ' + c.n;
    opt.dataset.code = c.c;
    select.appendChild(opt);
  });

  /* preselect Nigeria */
  select.value = '+254';

  /* ---- OTP input behavior ---- */
  otpInputs.forEach(function(input, idx) {
    input.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
      if (this.value && idx < otpInputs.length - 1) {
        otpInputs[idx + 1].focus();
      }
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
      if (paste.length >= otpInputs.length) {
        otpInputs[otpInputs.length - 1].focus();
      } else if (paste.length > 0) {
        otpInputs[Math.min(paste.length, otpInputs.length - 1)].focus();
      }
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

  /* ---- get OTP code from inputs ---- */
  function getOtpCode() {
    var code = '';
    otpInputs.forEach(function(inp) { code += inp.value; });
    return code;
  }

  /* ---- step transitions ---- */
  function goToStep2(email) {
    _email = email;
    otpEmail.textContent = email;
    step1.classList.add('hidden');
    step1.style.display = 'none';
    step2.classList.add('active');
    otpInputs[0].focus();
    startResendTimer(60);
  }

  /* ---- REGISTER ---- */
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    hideAlerts();

    _firstName   = document.getElementById('regFirstName').value.trim();
    _lastName    = document.getElementById('regLastName').value.trim();
    _email       = document.getElementById('regEmail').value.trim();
    _countryCode = document.getElementById('regCountry').value;
    _phone       = document.getElementById('regPhone').value.trim();
    _password    = document.getElementById('regPassword').value;
    var confirm  = document.getElementById('regConfirm').value;

    if (!_firstName || !_lastName || !_email || !_password || !_countryCode || !_phone) {
      showErr('Please fill in all required fields.');
      return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(_email)) {
      showErr('Please enter a valid email address.');
      return;
    }
    if (_password.length < 8) {
      showErr('Password must be at least 8 characters long.');
      return;
    }
    if (_password !== confirm) {
      showErr('Passwords do not match.');
      return;
    }
    if (!document.getElementById('regTerms').checked) {
      showErr('You must accept the Terms & Conditions and Privacy Policy to create an account.');
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Creating account...';

    try {
      var result = await BMAuth.signUp(_firstName, _lastName, _email, _password, {
        country_code: _countryCode,
        phone: _phone
      });

      if (result.error) {
        btn.disabled = false;
        btn.textContent = 'Create account';
        showErr(result.error);
        return;
      }

      if (!result.user) {
        btn.disabled = false;
        btn.textContent = 'Create account';
        showErr('Account could not be created. The email may already be in use.');
        return;
      }

      btn.disabled = false;
      btn.textContent = 'Create account';

      goToStep2(_email);
    } catch (err) {
      btn.disabled = false;
      btn.textContent = 'Create account';
      showErr(err.message || "We couldn't connect to the server. Please check your connection and try again.");
      console.error('Registration error:', err);
    }
  });

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

  /* ---- VERIFY OTP ---- */
  verifyBtn.addEventListener('click', async function() {
    otpErr.hidden = true;
    var code = getOtpCode();
    if (code.length !== 6) {
      otpErr.textContent = 'Please enter the full 6-digit code.';
      otpErr.hidden = false;
      return;
    }

    verifyBtn.disabled = true;
    verifyBtn.textContent = 'Verifying...';

    try {
      var resp = await postApi('api/password-reset.php', { action: 'verify', email: _email, code: code, otp_type: 'signup' });
      var result = await parseResponse(resp);

      if (!resp.ok || !result || !result.success) {
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify';
        var msg = (result && (result.error || result.message)) || 'Invalid verification code.';
        if (resp.status === 403) msg = 'Verification request was rejected. Please request a new code and try again.';
        if (resp.status === 429) msg = 'Too many attempts. Please wait before trying again.';
        if (resp.status === 401) msg = 'Verification code expired or invalid. Please request a new code.';
        otpErr.textContent = msg;
        otpErr.hidden = false;
        return;
      }

      // OTP verified — now confirm email and sign in
      var confirmResp = await postApi('api/password-reset.php', { action: 'confirm-email', email: _email });
      var confirmData = await parseResponse(confirmResp);

      await handleVerified(confirmData);
    } catch (err) {
      verifyBtn.disabled = false;
      verifyBtn.textContent = 'Verify';
      otpErr.textContent = "Could not connect to the server. Please check your connection.";
      otpErr.hidden = false;
    }
  });

  async function handleVerified(data) {
    if (!data?.success) {
      otpErr.textContent = data?.error || 'Verification failed. Please try again.';
      otpErr.hidden = false;
      verifyBtn.disabled = false;
      verifyBtn.textContent = 'Verify';
      return;
    }

    var user = data?.user;
    if (user) {
      try {
        var signInResult = await BMAuth.signIn(_email, _password);
        if (signInResult.user) {
          await BMAuth.updateProfile(signInResult.user.id, {
            first_name: _firstName,
            last_name: _lastName,
            country_code: _countryCode,
            phone: _phone
          });
          await BMAuth.updateMetadata({
            first_name: _firstName,
            last_name: _lastName,
            country_code: _countryCode,
            phone: _phone,
            phone_number: _phone
          });
        }
      } catch (e) { /* sign-in will work on next login */ }
    }

    step2.classList.remove('active');
    step2.style.display = 'none';
    step1.style.display = 'none';

    var successWrap = document.createElement('div');
    successWrap.style.cssText = 'text-align:center;padding:20px 0';
    successWrap.innerHTML = '<h1 style="font-family:Space Grotesk,sans-serif;font-size:1.4rem;margin-bottom:10px">Account verified!</h1>' +
      '<p class="auth-sub">successfully verified, sign in</p>';
    document.querySelector('.auth-card').appendChild(successWrap);

    setTimeout(function() {
      window.location.href = 'index.php';
    }, 1500);
  }

  /* ---- RESEND OTP ---- */
  resendBtn.addEventListener('click', async function() {
    otpErr.hidden = true;
    resendBtn.disabled = true;

    // Start 60-second countdown immediately
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
        var msg = (data && (data.error || data.message)) || 'Unable to send OTP. Please try again later.';
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

  /* ---- BACK TO REGISTRATION ---- */
  document.getElementById('backToReg').addEventListener('click', function(e) {
    e.preventDefault();
    step2.classList.remove('active');
    step2.style.display = 'none';
    step1.style.display = '';
    step1.classList.remove('hidden');
  });

})();
</script>
<script src="js/motion.js" defer></script>
</body>
</html>
