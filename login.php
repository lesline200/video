<?php
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/helpers/auth_helper.php';

if (isLoggedIn()) {
    header('Location: /video/dashboard.php');
    exit;
}

$api_url = APP_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>login — VidGenius</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@500;600;700&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <script src="https://accounts.google.com/gsi/client"></script>

  <style>
    /* ─── RESET & VARIABLES ─── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:           #f7f8fc;
      --surface:      #ffffff;
      --border:       #e4e7f0;
      --border-focus: #2563eb;
      --blue:         #2563eb;
      --blue-dark:    #1d4ed8;
      --blue-light:   #eff6ff;
      --indigo:       #4f46e5;
      --text-primary: #0f172a;
      --text-secondary:#475569;
      --text-muted:   #94a3b8;
      --green:        #16a34a;
      --green-bg:     #f0fdf4;
      --green-border: #bbf7d0;
      --red:          #dc2626;
      --red-bg:       #fef2f2;
      --red-border:   #fecaca;
      --shadow-md:    0 4px 16px rgba(0,0,0,.08), 0 2px 6px rgba(0,0,0,.04);
      --shadow-lg:    0 12px 40px rgba(37,99,235,.12), 0 4px 12px rgba(0,0,0,.06);
      --shadow-xl:    0 24px 60px rgba(37,99,235,.15), 0 8px 24px rgba(0,0,0,.08);
      --radius:       .875rem;
      --radius-full:  9999px;
      --font-display: 'Clash Display', sans-serif;
      --font-body:    'Sora', sans-serif;
      --transition:   .2s ease;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: var(--font-body);
      background: var(--bg);
      color: var(--text-primary);
      min-height: 100vh;
    }

    a { color: inherit; text-decoration: none; }

    /* ─── HEADER ─── */
    .header {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 100;
      background: rgba(255,255,255,.82);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--border);
    }
    .header__inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1.5rem;
      height: 68px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .logo {
      display: flex;
      align-items: center;
      gap: .5rem;
      font-family: var(--font-display);
      font-size: 1.25rem;
      font-weight: 700;
      letter-spacing: -.02em;
      color: var(--text-primary);
    }
    .logo svg { color: var(--blue); }

    .header__link {
      font-size: .9375rem;
      color: var(--text-secondary);
      font-weight: 500;
      transition: color var(--transition);
    }
    .header__link:hover { color: var(--text-primary); }
    .header__link span { color: var(--blue); font-weight: 600; }

    /* ─── MAIN / CENTERING ─── */
    .page-wrap {
      padding-top: 68px;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding-left: 1rem;
      padding-right: 1rem;
      padding-bottom: 3rem;
      position: relative;
    }

    .page-wrap::before,
    .page-wrap::after {
      content: '';
      position: fixed;
      border-radius: 50%;
      filter: blur(80px);
      pointer-events: none;
      z-index: 0;
    }
    .page-wrap::before {
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(37,99,235,.06) 0%, transparent 70%);
      top: 0; right: -100px;
    }
    .page-wrap::after {
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(99,102,241,.05) 0%, transparent 70%);
      bottom: 0; left: -100px;
    }

    .card-wrapper {
      width: 100%;
      max-width: 460px;
      position: relative;
      z-index: 1;
    }

    /* ─── CARD ─── */
    .card {
      background: var(--surface);
      border-radius: 1.5rem;
      box-shadow: var(--shadow-xl);
      padding: 2.5rem;
      border: 1px solid var(--border);
      animation: cardIn .5s cubic-bezier(.22,1,.36,1) both;
    }
    @keyframes cardIn {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .card__head {
      text-align: center;
      margin-bottom: 2rem;
    }
    .card__icon {
      width: 56px; height: 56px;
      background: linear-gradient(135deg, var(--blue-light), rgba(99,102,241,.1));
      border-radius: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.25rem;
    }
    .card__icon svg { color: var(--blue); }
    .card__title {
      font-family: var(--font-display);
      font-size: 1.75rem;
      font-weight: 700;
      letter-spacing: -.025em;
      margin-bottom: .375rem;
    }
    .card__sub {
      color: var(--text-secondary);
      font-size: .9375rem;
    }

    /* ─── ALERT ─── */
    .alert {
      display: none;
      align-items: center;
      gap: .625rem;
      padding: .875rem 1rem;
      border-radius: .75rem;
      font-size: .875rem;
      font-weight: 500;
      margin-bottom: 1.5rem;
      border: 1px solid;
      animation: alertIn .3s ease both;
    }
    .alert.visible { display: flex; }
    @keyframes alertIn {
      from { opacity: 0; transform: translateY(-6px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .alert--success { background: var(--green-bg); border-color: var(--green-border); color: var(--green); }
    .alert--error   { background: var(--red-bg);   border-color: var(--red-border);   color: var(--red); }
    .alert svg { flex-shrink: 0; }

    /* ─── FORM ─── */
    .form { display: flex; flex-direction: column; gap: 1.25rem; }
    .field { display: flex; flex-direction: column; gap: .375rem; }
    .field__label { font-size: .875rem; font-weight: 600; color: var(--text-primary); }

    .input-wrap { position: relative; }
    .input-wrap .icon-left,
    .input-wrap .icon-right {
      position: absolute;
      top: 50%; transform: translateY(-50%);
      display: flex; align-items: center;
      pointer-events: none;
      color: var(--text-muted);
    }
    .input-wrap .icon-left  { left: .875rem; }
    .input-wrap .icon-right { right: .875rem; pointer-events: all; cursor: pointer; transition: color var(--transition); }
    .input-wrap .icon-right:hover { color: var(--text-secondary); }

    .input {
      width: 100%;
      padding: .8125rem 1rem .8125rem 2.625rem;
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      font-family: var(--font-body);
      font-size: .9375rem;
      color: var(--text-primary);
      background: var(--surface);
      transition: border-color var(--transition), box-shadow var(--transition);
      outline: none;
    }
    .input:focus { border-color: var(--border-focus); box-shadow: 0 0 0 4px rgba(37,99,235,.1); }
    .input:disabled { opacity: .55; cursor: not-allowed; background: #f8fafc; }
    .input::placeholder { color: var(--text-muted); }
    .input--password { padding-right: 2.875rem; }

    .field__footer { display: flex; justify-content: flex-end; }
    .forgot-link {
      font-size: .8125rem; color: var(--blue); font-weight: 500;
      transition: opacity var(--transition);
    }
    .forgot-link:hover { opacity: .75; text-decoration: underline; }

    /* ─── SUBMIT BUTTON ─── */
    .btn-submit {
      width: 100%; padding: .9375rem;
      background: linear-gradient(135deg, var(--blue) 0%, var(--indigo) 100%);
      color: #fff; border: none; border-radius: var(--radius);
      font-family: var(--font-body); font-size: 1rem; font-weight: 600;
      cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .5rem;
      box-shadow: 0 4px 16px rgba(37,99,235,.3);
      transition: transform var(--transition), box-shadow var(--transition), opacity var(--transition);
      margin-top: .5rem;
    }
    .btn-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,99,235,.38); }
    .btn-submit:disabled { opacity: .6; cursor: not-allowed; transform: none; }

    .spinner {
      width: 18px; height: 18px;
      border: 2.5px solid rgba(255,255,255,.35);
      border-top-color: #fff; border-radius: 50%;
      animation: spin .7s linear infinite; display: none;
    }
    .spinner.active { display: block; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ─── DIVIDER ─── */
    .divider { position: relative; display: flex; align-items: center; margin: 1.5rem 0; }
    .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
    .divider__text { padding: 0 .875rem; font-size: .8125rem; color: var(--text-muted); font-weight: 500; white-space: nowrap; }

    /* ─── GOOGLE BUTTON ─── */
    .google-btn {
      width: 100%; padding: .875rem;
      background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius);
      font-family: var(--font-body); font-size: .9375rem; font-weight: 600; color: var(--text-primary);
      cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .75rem;
      transition: background var(--transition), border-color var(--transition), box-shadow var(--transition);
    }
    .google-btn:hover { background: #fafbff; border-color: #c7cfe0; box-shadow: var(--shadow-md); }

    .terms { margin-top: 1.5rem; text-align: center; font-size: .8rem; color: var(--text-muted); line-height: 1.6; }
    .terms a { color: var(--blue); font-weight: 500; }
    .terms a:hover { text-decoration: underline; }

    .back-link {
      display: flex; align-items: center; justify-content: center; gap: .375rem;
      margin-top: 1.5rem; font-size: .875rem; color: var(--text-muted);
      transition: color var(--transition);
    }
    .back-link:hover { color: var(--text-secondary); }

    @media (max-width: 480px) {
      .card { padding: 1.75rem 1.25rem; }
      .card__title { font-size: 1.5rem; }
    }
  </style>
</head>
<body>

<header class="header">
  <div class="header__inner">
    <a href="/video/index.php" class="logo">
      <svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
      </svg>
      VidGenius
    </a>
    <a href="signup.php" class="header__link">
      Don't have an account? <span>Sign up</span>
    </a>
  </div>
</header>

<div class="page-wrap">
  <div class="card-wrapper">
    <div class="card">

      <div class="card__head">
        <div class="card__icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
              d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
          </svg>
        </div>
        <h1 class="card__title">Welcome back</h1>
        <p class="card__sub">Sign in to continue to your dashboard</p>
      </div>

      <div class="alert" id="alertBox" role="alert">
        <svg id="alertIcon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
        <span id="alertText"></span>
      </div>

      <form class="form" id="loginForm" novalidate>

        <div class="field">
          <label class="field__label" for="email">Email address</label>
          <div class="input-wrap">
            <span class="icon-left">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </span>
            <input class="input" type="email" id="email" name="email" placeholder="Enter your email" autocomplete="email" required/>
          </div>
        </div>

        <div class="field">
          <label class="field__label" for="password">Password</label>
          <div class="input-wrap">
            <span class="icon-left">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </span>
            <input class="input input--password" type="password" id="password" name="password" autocomplete="current-password" required/>
            <button type="button" class="icon-right" id="togglePwd" aria-label="Toggle password visibility">
              <svg id="eyeOpen" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              <svg id="eyeOff" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
              </svg>
            </button>
          </div>
          <div class="field__footer">
            <a href="/video/forgot_password.php" class="forgot-link">Forgot password?</a>
          </div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
          <span class="spinner" id="spinner"></span>
          <span id="btnText">Sign in</span>
        </button>

      

      <div class="divider"><span class="divider__text">Or continue with</span></div>

      <button type="button" class="google-btn" id="googleBtn">
        <svg width="20" height="20" viewBox="0 0 24 24">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Continue with Google
      </button>

      <p class="terms">
        By signing in, you agree to our
        <a href="/video/tos.php">Terms of Service</a> and
        <a href="/video/privacy.php">Privacy Policy</a>
      </p>

    </div>

    <a href="/video/index.php" class="back-link">
      <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Back to home
    </a>

  </div>
</div>

<script>
  // ← $api_url est injecté par PHP en haut du fichier
  const API_URL = '<?= htmlspecialchars($api_url, ENT_QUOTES) ?>';

  const $ = id => document.getElementById(id);

  function showAlert(type, text) {
    const box  = $('alertBox');
    const icon = $('alertIcon');
    box.className = `alert visible alert--${type}`;
    $('alertText').textContent = text;
    if (type === 'success') {
      icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>`;
    } else {
      icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>`;
    }
  }

  function hideAlert() { $('alertBox').classList.remove('visible'); }

  function setLoading(state) {
    $('submitBtn').disabled = state;
    $('googleBtn').disabled = state;
    $('spinner').classList.toggle('active', state);
    $('btnText').textContent = state ? 'Signing in…' : 'Sign in';
    document.querySelectorAll('.input').forEach(i => i.disabled = state);
  }

  $('togglePwd').addEventListener('click', () => {
    const pwd = $('password');
    const isText = pwd.type === 'text';
    pwd.type = isText ? 'password' : 'text';
    $('eyeOpen').style.display = isText ? 'block' : 'none';
    $('eyeOff').style.display  = isText ? 'none'  : 'block';
  });

  $('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    hideAlert();
    setLoading(true);

    const email    = $('email').value.trim();
    const password = $('password').value;

    try {
      const res = await fetch(`${API_URL}/app/api/auth/login.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
        credentials: 'include',
      });

      const data = await res.json();

      if (!res.ok) throw new Error(data.error || 'Login failed');

      showAlert('success', 'Login successful! Redirecting…');
      setTimeout(() => { window.location.href = '/video/dashboard.php'; }, 1500);

    } catch (err) {
      showAlert('error', err.message || 'An unexpected error occurred.');
      setLoading(false);
    }
  });

  window.addEventListener('load', () => {
    if (typeof google === 'undefined') return;
    google.accounts.id.initialize({
      client_id: '<?= htmlspecialchars(GOOGLE_CLIENT_ID, ENT_QUOTES) ?>',
      callback: handleGoogleCallback,
      auto_select: false,
    });
  });

  $('googleBtn').addEventListener('click', () => {
    if (typeof google === 'undefined') {
      showAlert('error', 'Google services unavailable.');
      return;
    }
    google.accounts.id.prompt();
  });

  async function handleGoogleCallback(credentialResponse) {
    hideAlert();
    setLoading(true);
    try {
      const res = await fetch(`${API_URL}/app/api/auth/google.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ credential: credentialResponse.credential }),
        credentials: 'include',
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Google authentication failed');
      showAlert('success', 'Authenticated! Redirecting…');
      setTimeout(() => { window.location.href = '/video/dashboard.php'; }, 1500);
    } catch (err) {
      showAlert('error', err.message || 'Google login failed.');
      setLoading(false);
    }
  }
</script>
</body>
</html>
