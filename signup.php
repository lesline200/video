<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/helpers/auth_helper.php';

if (isLoggedIn()) {
    header('Location: /video/dashboard.php');
    exit;
}
$api_url      = APP_URL;
$google_client = GOOGLE_CLIENT_ID;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>sing in — VidGenius</title>
  <link rel="stylesheet" href="/video/css/bootstrap/bootstrap.min.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@500;600;700&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <script src="https://accounts.google.com/gsi/client"></script>
  <style>
    :root {
      --blue:#2563eb; --indigo:#4f46e5; --blue-light:#eff6ff;
      --border-c:#e4e7f0; --muted:#94a3b8;
      --fd:'Clash Display',sans-serif; --fb:'Sora',sans-serif;
    }
    body { font-family:var(--fb); background:#f7f8fc; min-height:100vh; }

    .site-header { position:fixed;top:0;left:0;right:0;z-index:100;height:68px;background:rgba(255,255,255,.88);backdrop-filter:blur(16px);border-bottom:1px solid var(--border-c); }
    .site-header .inner { max-width:1200px;margin:0 auto;padding:0 1.5rem;height:100%;display:flex;align-items:center;justify-content:space-between; }
    .logo { display:flex;align-items:center;gap:.5rem;font-family:var(--fd);font-weight:700;font-size:1.2rem;color:#0f172a;text-decoration:none; }
    .logo svg { color:var(--blue); }
    .hdr-link { font-size:.9rem;color:#475569;font-weight:500;text-decoration:none; }
    .hdr-link span { color:var(--blue);font-weight:600; }

    .page-wrap { padding-top:68px;min-height:100vh;display:flex;align-items:center;justify-content:center;padding-bottom:2rem; }

    .auth-card {
      background:#fff;border:1px solid var(--border-c);border-radius:1.5rem;
      box-shadow:0 24px 60px rgba(37,99,235,.12),0 8px 24px rgba(0,0,0,.07);
      padding:2.5rem;width:100%;max-width:460px;
      animation:cardIn .45s cubic-bezier(.22,1,.36,1) both;
    }
    @keyframes cardIn { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }

    .card-icon { width:54px;height:54px;border-radius:1rem;background:linear-gradient(135deg,var(--blue-light),rgba(99,102,241,.1));display:flex;align-items:center;justify-content:center;margin:0 auto 1rem; }
    .card-icon svg { color:var(--blue); }
    .card-title { font-family:var(--fd);font-size:1.7rem;font-weight:700;letter-spacing:-.025em; }

    .badge-live { display:inline-flex;align-items:center;gap:.4rem;background:var(--blue-light);color:var(--blue);padding:.3rem .875rem;border-radius:9999px;font-size:.8125rem;font-weight:600;border:1px solid rgba(37,99,235,.15); }
    .badge-dot { width:7px;height:7px;background:var(--blue);border-radius:50%;animation:pulse 2s infinite; }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }

    .form-label { font-size:.875rem;font-weight:600;color:#0f172a; }
    .iw { position:relative; }
    .iw .il { position:absolute;left:.875rem;top:50%;transform:translateY(-50%);color:var(--muted);pointer-events:none;display:flex;align-items:center; }
    .iw .ir { position:absolute;right:.875rem;top:50%;transform:translateY(-50%);color:var(--muted);cursor:pointer;background:none;border:none;display:flex;align-items:center;padding:0; }
    .form-control { padding-left:2.6rem !important;border:1.5px solid var(--border-c) !important;border-radius:.875rem !important;font-family:var(--fb) !important;font-size:.9375rem !important;color:#0f172a !important; }
    .form-control:focus { border-color:var(--blue) !important;box-shadow:0 0 0 4px rgba(37,99,235,.1) !important; }
    .form-control.pr { padding-right:2.8rem !important; }

    /* Strength bar */
    .strength-bar { height:3px;border-radius:2px;background:var(--border-c);overflow:hidden;margin-top:.5rem; }
    .strength-fill { height:100%;border-radius:2px;width:0;transition:width .4s,background .4s; }
    .strength-lbl { font-size:.75rem;color:var(--muted);margin-top:.3rem; }

    .vg-alert { display:none;align-items:center;gap:.6rem;padding:.85rem 1rem;border-radius:.75rem;font-size:.875rem;font-weight:500;border:1px solid; }
    .vg-alert.show { display:flex; }
    .vg-alert.success { background:#f0fdf4;border-color:#bbf7d0;color:#16a34a; }
    .vg-alert.error   { background:#fef2f2;border-color:#fecaca;color:#dc2626; }

    .btn-vg { width:100%;padding:.9rem;background:linear-gradient(135deg,var(--blue),var(--indigo));color:#fff;border:none;border-radius:.875rem;font-family:var(--fb);font-size:1rem;font-weight:600;display:flex;align-items:center;justify-content:center;gap:.5rem;box-shadow:0 4px 16px rgba(37,99,235,.28);transition:transform .2s,box-shadow .2s,opacity .2s;cursor:pointer; }
    .btn-vg:hover:not(:disabled) { transform:translateY(-2px);box-shadow:0 8px 24px rgba(37,99,235,.38); }
    .btn-vg:disabled { opacity:.6;cursor:not-allowed;transform:none; }

    .spin-sm { width:17px;height:17px;border:2.5px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:none; }
    .spin-sm.on { display:block; }
    @keyframes spin { to{transform:rotate(360deg)} }

    .divider { display:flex;align-items:center;gap:.75rem;margin:1.25rem 0;color:var(--muted);font-size:.8rem;font-weight:500; }
    .divider::before,.divider::after { content:'';flex:1;height:1px;background:var(--border-c); }

    .btn-google { width:100%;padding:.85rem;background:#fff;border:1.5px solid var(--border-c);border-radius:.875rem;font-family:var(--fb);font-size:.9375rem;font-weight:600;color:#0f172a;display:flex;align-items:center;justify-content:center;gap:.75rem;cursor:pointer;transition:background .2s,border-color .2s,box-shadow .2s; }
    .btn-google:hover { background:#fafbff;border-color:#c7cfe0;box-shadow:0 4px 14px rgba(0,0,0,.07); }

    .terms-txt { font-size:.78rem;color:var(--muted);text-align:center;line-height:1.6; }
    .terms-txt a { color:var(--blue);font-weight:500;text-decoration:none; }
    .terms-txt a:hover { text-decoration:underline; }
    .back-link { display:flex;align-items:center;justify-content:center;gap:.35rem;margin-top:1.25rem;font-size:.875rem;color:var(--muted);text-decoration:none;transition:color .2s; }
    .back-link:hover { color:#475569; }

    @media(max-width:480px) { .auth-card{padding:1.75rem 1.25rem;} .card-title{font-size:1.45rem;} }
  </style>
</head>
<body>

<header class="site-header">
  <div class="inner">
    <a href="/video/index.php" class="logo">
      <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
      </svg>
      VidGenius
    </a>
    <a href="/video/login.php" class="hdr-link">Already have an account? <span>Sign in</span></a>
  </div>
</header>

<div class="page-wrap">
  <div style="width:100%;max-width:460px;padding:0 1rem;">

    <div class="auth-card">

      <div class="text-center mb-4">
        <div class="card-icon">
          <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>
          </svg>
        </div>
        <h1 class="card-title">Create your account</h1>
        <div class="badge-live mt-2">
          <span class="badge-dot"></span>
          Join 430k+ creators going faceless
        </div>
      </div>

      <div class="vg-alert mb-3" id="alertBox" role="alert">
        <svg id="alertIcon" width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
        <span id="alertText"></span>
      </div>

      <form id="signupForm" novalidate>

        <!-- Name -->
        <div class="mb-3">
          <label class="form-label" for="name">Full name</label>
          <div class="iw">
            <span class="il">
              <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </span>
            <input type="text" class="form-control" id="name" placeholder="Enter your full name" autocomplete="name" required/>
          </div>
        </div>

        <!-- Email -->
        <div class="mb-3">
          <label class="form-label" for="email">Email address</label>
          <div class="iw">
            <span class="il">
              <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </span>
            <input type="email" class="form-control" id="email" placeholder="Enter your email" autocomplete="email" required/>
          </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <div class="iw">
            <span class="il">
              <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </span>
            <input type="password" class="form-control pr" id="password" autocomplete="new-password" minlength="8" required/>
            <button type="button" class="ir" id="togglePwd">
              <svg id="eyeOpen" width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              <svg id="eyeOff" width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
              </svg>
            </button>
          </div>
          <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
          <p class="strength-lbl" id="strengthLbl">Must be at least 8 characters</p>
        </div>

        <button type="submit" class="btn-vg" id="submitBtn">
          <span class="spin-sm" id="spinner"></span>
          <span id="btnText">Create account</span>
        </button>

      </form>

      <div class="divider">Or continue with</div>

      <button type="button" class="btn-google" id="googleBtn">
        <svg width="19" height="19" viewBox="0 0 24 24">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Continue with Google
      </button>

      <p class="terms-txt mt-3">
        By signing up, you agree to our <a href="/video/tos.php">Terms of Service</a> and <a href="/video/privacy.php">Privacy Policy</a>
      </p>

    </div>

    <a href="/video/index.php" class="back-link">
      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Back to home
    </a>
  </div>
</div>

<script src="/video/css/js/bootstrap.bundle.min.js"></script>
<script>
  const API_URL       = '<?= htmlspecialchars($api_url,       ENT_QUOTES) ?>';
  const GOOGLE_CLIENT = '<?= htmlspecialchars($google_client, ENT_QUOTES) ?>';
  const $ = id => document.getElementById(id);

  function showAlert(type, text) {
    const box = $('alertBox');
    box.className = `vg-alert mb-3 show ${type}`;
    $('alertText').textContent = text;
    $('alertIcon').innerHTML = type === 'success'
      ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>`
      : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>`;
    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
  function hideAlert() { $('alertBox').className = 'vg-alert mb-3'; }

  function setLoading(state) {
    $('submitBtn').disabled = $('googleBtn').disabled = state;
    $('spinner').classList.toggle('on', state);
    $('btnText').textContent = state ? 'Creating account…' : 'Create account';
    document.querySelectorAll('.form-control').forEach(i => i.disabled = state);
  }

  $('togglePwd').addEventListener('click', () => {
    const p = $('password'), isT = p.type === 'text';
    p.type = isT ? 'password' : 'text';
    $('eyeOpen').style.display = isT ? 'block' : 'none';
    $('eyeOff').style.display  = isT ? 'none'  : 'block';
  });

  $('password').addEventListener('input', function () {
    const v = this.value;
    let s = 0;
    if (v.length >= 8)  s++;
    if (v.length >= 12) s++;
    if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
    if (/\d/.test(v))   s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;
    const lvls = [
      { p:'0%',   c:'transparent', t:'Must be at least 8 characters' },
      { p:'25%',  c:'#ef4444',     t:'Weak' },
      { p:'50%',  c:'#f97316',     t:'Fair' },
      { p:'75%',  c:'#eab308',     t:'Good' },
      { p:'100%', c:'#22c55e',     t:'Strong 💪' },
    ];
    const l = v.length === 0 ? lvls[0] : lvls[Math.min(s, 4)];
    const f = $('strengthFill');
    f.style.width = l.p; f.style.background = l.c;
    const lb = $('strengthLbl');
    lb.textContent = l.t;
    lb.style.color = v.length === 0 ? 'var(--muted)' : l.c;
  });

  $('signupForm').addEventListener('submit', async (e) => {
    e.preventDefault(); hideAlert();
    const name = $('name').value.trim();
    const password = $('password').value;
    if (!name)              { showAlert('error', 'Please enter your full name.'); return; }
    if (password.length < 8){ showAlert('error', 'Password must be at least 8 characters.'); return; }
    setLoading(true);
    try {
      const res = await fetch(`${API_URL}/app/api/auth/signup.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email: $('email').value.trim(), password }),
        credentials: 'include',
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Signup failed');
      showAlert('success', 'Account created successfully! Redirecting…');
      setTimeout(() => { window.location.href = '/video/dashboard.php'; }, 1500);
    } catch (err) {
      showAlert('error', err.message || 'An unexpected error occurred.');
      setLoading(false);
    }
  });

  window.addEventListener('load', () => {
    if (typeof google === 'undefined') return;
    google.accounts.id.initialize({ client_id: GOOGLE_CLIENT, callback: handleGoogle, auto_select: false });
  });
  $('googleBtn').addEventListener('click', () => {
    if (typeof google === 'undefined') { showAlert('error', 'Google services unavailable.'); return; }
    google.accounts.id.prompt();
  });
  async function handleGoogle(cr) {
    hideAlert(); setLoading(true);
    try {
      const res = await fetch(`${API_URL}/app/api/auth/google.php`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ credential: cr.credential }), credentials: 'include',
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Google authentication failed');
      showAlert('success', (data.message || 'Account created!') + ' Redirecting…');
      setTimeout(() => { window.location.href = '/video/dashboard.php'; }, 1500);
    } catch (err) {
      showAlert('error', err.message || 'Google sign-up failed.');
      setLoading(false);
    }
  }
</script>
</body>
</html>