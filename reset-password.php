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
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password — VidGenius</title>
  <link rel="stylesheet" href="/video/css/bootstrap/bootstrap.min.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@500;600;700&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --blue:#2563eb; --indigo:#4f46e5; --blue-light:#eff6ff;
      --border-c:#e4e7f0; --muted:#94a3b8;
    }
    body { font-family:'Sora',sans-serif; background:#f7f8fc; min-height:100vh; }

    .site-header {
      position:fixed;top:0;left:0;right:0;z-index:100;height:68px;
      background:rgba(255,255,255,.88);backdrop-filter:blur(16px);
      border-bottom:1px solid var(--border-c);
    }
    .site-header .inner {
      max-width:1200px;margin:0 auto;padding:0 1.5rem;height:100%;
      display:flex;align-items:center;justify-content:space-between;
    }
    .logo {
      display:flex;align-items:center;gap:.5rem;
      font-family:'Clash Display',sans-serif;font-weight:700;font-size:1.2rem;
      color:#0f172a;text-decoration:none;
    }
    .logo svg { color:var(--blue); }
    .hdr-link { font-size:.9rem;color:#475569;font-weight:500;text-decoration:none; }
    .hdr-link span { color:var(--blue);font-weight:600; }

    .page-wrap {
      padding-top:68px;min-height:100vh;
      display:flex;align-items:center;justify-content:center;padding-bottom:2rem;
    }
    .auth-card {
      background:#fff;border:1px solid var(--border-c);border-radius:1.5rem;
      box-shadow:0 24px 60px rgba(37,99,235,.12),0 8px 24px rgba(0,0,0,.07);
      padding:2.5rem;width:100%;max-width:460px;
      animation:cardIn .45s cubic-bezier(.22,1,.36,1) both;
    }
    @keyframes cardIn { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }

    .card-icon {
      width:54px;height:54px;border-radius:1rem;
      background:linear-gradient(135deg,var(--blue-light),rgba(99,102,241,.1));
      display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;
    }
    .card-icon svg { color:var(--blue); }
    .card-title { font-family:'Clash Display',sans-serif;font-size:1.7rem;font-weight:700;letter-spacing:-.025em; }
    .card-sub { color:#475569;font-size:.9rem; }

    .form-label { font-size:.875rem;font-weight:600;color:#0f172a; }
    .iw { position:relative; }
    .iw .il {
      position:absolute;left:.875rem;top:50%;transform:translateY(-50%);
      color:var(--muted);pointer-events:none;display:flex;align-items:center;
    }
    .form-control {
      padding-left:2.6rem !important;border:1.5px solid var(--border-c) !important;
      border-radius:.875rem !important;font-family:'Sora',sans-serif !important;
      font-size:.9375rem !important;color:#0f172a !important;
    }
    .form-control:focus {
      border-color:var(--blue) !important;
      box-shadow:0 0 0 4px rgba(37,99,235,.1) !important;
    }

    /* Code inputs */
    .code-inputs { display:flex;gap:.625rem;justify-content:center;margin:1rem 0; }
    .code-input {
      width:48px;height:56px;text-align:center;font-size:1.5rem;font-weight:700;
      border:1.5px solid var(--border-c);border-radius:.875rem;
      font-family:'Sora',sans-serif;color:#0f172a;outline:none;
      transition:border-color .15s,box-shadow .15s;
    }
    .code-input:focus { border-color:var(--blue);box-shadow:0 0 0 4px rgba(37,99,235,.1); }
    .code-input.filled { border-color:var(--blue);background:var(--blue-light); }

    .vg-alert {
      display:none;align-items:center;gap:.6rem;
      padding:.85rem 1rem;border-radius:.75rem;font-size:.875rem;font-weight:500;border:1px solid;
    }
    .vg-alert.show { display:flex; }
    .vg-alert.success { background:#f0fdf4;border-color:#bbf7d0;color:#16a34a; }
    .vg-alert.error   { background:#fef2f2;border-color:#fecaca;color:#dc2626; }

    .btn-vg {
      width:100%;padding:.9rem;
      background:linear-gradient(135deg,var(--blue),var(--indigo));
      color:#fff;border:none;border-radius:.875rem;
      font-family:'Sora',sans-serif;font-size:1rem;font-weight:600;
      display:flex;align-items:center;justify-content:center;gap:.5rem;
      box-shadow:0 4px 16px rgba(37,99,235,.28);
      transition:transform .2s,box-shadow .2s,opacity .2s;cursor:pointer;
    }
    .btn-vg:hover:not(:disabled) { transform:translateY(-2px);box-shadow:0 8px 24px rgba(37,99,235,.38); }
    .btn-vg:disabled { opacity:.6;cursor:not-allowed;transform:none; }

    .spin-sm {
      width:17px;height:17px;border:2.5px solid rgba(255,255,255,.35);
      border-top-color:#fff;border-radius:50%;
      animation:spin .7s linear infinite;display:none;
    }
    .spin-sm.on { display:block; }
    @keyframes spin { to{transform:rotate(360deg)} }

    .back-link {
      display:flex;align-items:center;justify-content:center;gap:.35rem;
      margin-top:1.25rem;font-size:.875rem;color:var(--muted);
      text-decoration:none;transition:color .2s;
    }
    .back-link:hover { color:#475569; }

    /* Password strength */
    .pwd-strength { margin-top:.5rem; }
    .pwd-strength-bar {
      height:4px;border-radius:2px;background:#e4e7f0;overflow:hidden;margin-bottom:.35rem;
    }
    .pwd-strength-fill {
      height:100%;border-radius:2px;transition:width .3s,background .3s;width:0;
    }
    .pwd-strength-label { font-size:.75rem;color:var(--muted); }

    /* Steps */
    .step { display:none; }
    .step.active { display:block; animation:cardIn .3s cubic-bezier(.22,1,.36,1) both; }

    /* Success check */
    .success-icon {
      width:64px;height:64px;border-radius:50%;
      background:linear-gradient(135deg,#dcfce7,#bbf7d0);
      display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;
    }
    .success-icon svg { color:#16a34a; }
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
    <a href="/video/login.php" class="hdr-link">Remember your password? <span>Sign in</span></a>
  </div>
</header>

<div class="page-wrap">
  <div style="width:100%;max-width:460px;padding:0 1rem;">

    <div class="auth-card">

      <!-- ── STEP 1 : Code ── -->
      <div class="step active" id="stepCode">
        <div class="text-center mb-4">
          <div class="card-icon">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <h1 class="card-title">Check your email</h1>
          <p class="card-sub mt-1">Enter the 6-digit code we sent you</p>
        </div>

        <div class="vg-alert mb-3" id="alertCode" role="alert">
          <svg id="alertCodeIcon" width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
          <span id="alertCodeText"></span>
        </div>

        <div class="code-inputs" id="codeInputs">
          <input class="code-input" type="text" maxlength="1" inputmode="numeric" autocomplete="one-time-code"/>
          <input class="code-input" type="text" maxlength="1" inputmode="numeric"/>
          <input class="code-input" type="text" maxlength="1" inputmode="numeric"/>
          <input class="code-input" type="text" maxlength="1" inputmode="numeric"/>
          <input class="code-input" type="text" maxlength="1" inputmode="numeric"/>
          <input class="code-input" type="text" maxlength="1" inputmode="numeric"/>
        </div>

        <button type="button" class="btn-vg mt-2" id="codeBtn" onclick="verifyCode()">
          <span class="spin-sm" id="codeSpinner"></span>
          <span id="codeBtnText">Verify code</span>
        </button>

        <p class="text-center mt-3" style="font-size:.85rem;color:var(--muted)">
          Didn't receive it?
          <a href="/video/forgot_password.php" style="color:var(--blue);font-weight:600">Send again</a>
        </p>
      </div>

      <!-- ── STEP 2 : New password ── -->
      <div class="step" id="stepReset">
        <div class="text-center mb-4">
          <div class="card-icon">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
          </div>
          <h1 class="card-title">New password</h1>
          <p class="card-sub mt-1">Choose a strong password for your account</p>
        </div>

        <div class="vg-alert mb-3" id="alertReset" role="alert">
          <svg id="alertResetIcon" width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
          <span id="alertResetText"></span>
        </div>

        <form id="resetForm" novalidate>
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <div class="iw">
              <span class="il">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </span>
              <input type="password" class="form-control" id="newPassword"
                placeholder="Min. 8 characters" minlength="8" required
                oninput="checkStrength(this.value)"/>
            </div>
            <!-- Barre de force -->
            <div class="pwd-strength">
              <div class="pwd-strength-bar">
                <div class="pwd-strength-fill" id="strengthFill"></div>
              </div>
              <span class="pwd-strength-label" id="strengthLabel"></span>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <div class="iw">
              <span class="il">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </span>
              <input type="password" class="form-control" id="confirmPassword"
                placeholder="Repeat your password" required/>
            </div>
          </div>

          <button type="submit" class="btn-vg" id="resetBtn">
            <span class="spin-sm" id="resetSpinner"></span>
            <span id="resetBtnText">Reset password</span>
          </button>
        </form>
      </div>

      <!-- ── STEP 3 : Success ── -->
      <div class="step" id="stepSuccess">
        <div class="text-center">
          <div class="success-icon">
            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <h1 class="card-title mb-2">Password reset!</h1>
          <p class="card-sub">Your password has been updated successfully.</p>
          <p class="card-sub mt-1">Redirecting to login<span id="dots">...</span></p>
        </div>
      </div>

    </div><!-- /.auth-card -->

    <a href="/video/login.php" class="back-link">
      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Back to login
    </a>

  </div>
</div>

<script src="/video/css/js/bootstrap.bundle.min.js"></script>
<script>
const API_URL  = '<?= htmlspecialchars($api_url, ENT_QUOTES) ?>';
let resetCode  = '';

// ── Helpers ──────────────────────────────────────────────
function showStep(id) {
  document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
  document.getElementById(id).classList.add('active');
}

function showAlert(id, type, text) {
  const box = document.getElementById('alert' + id);
  box.className = `vg-alert mb-3 show ${type}`;
  document.getElementById('alert' + id + 'Text').textContent = text;
  document.getElementById('alert' + id + 'Icon').innerHTML = type === 'success'
    ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>`
    : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>`;
}
function hideAlert(id) {
  document.getElementById('alert' + id).className = 'vg-alert mb-3';
}

// ── Code inputs navigation ───────────────────────────────
document.querySelectorAll('.code-input').forEach((input, i, inputs) => {
  input.addEventListener('input', () => {
    input.value = input.value.replace(/\D/g, '');
    if (input.value) {
      input.classList.add('filled');
      if (i < inputs.length - 1) inputs[i + 1].focus();
    } else {
      input.classList.remove('filled');
    }
  });
  input.addEventListener('keydown', e => {
    if (e.key === 'Backspace' && !input.value && i > 0) {
      inputs[i - 1].focus();
      inputs[i - 1].classList.remove('filled');
    }
  });
  // Support paste
  input.addEventListener('paste', e => {
    e.preventDefault();
    const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    [...pasted].forEach((char, j) => {
      if (inputs[j]) { inputs[j].value = char; inputs[j].classList.add('filled'); }
    });
    if (inputs[pasted.length]) inputs[pasted.length].focus();
  });
});

// ── Vérifier le code ─────────────────────────────────────
async function verifyCode() {
  hideAlert('Code');
  const inputs = document.querySelectorAll('.code-input');
  const code   = [...inputs].map(i => i.value).join('');

  if (code.length < 6) {
    showAlert('Code', 'error', 'Please enter the complete 6-digit code.');
    return;
  }

  const btn = document.getElementById('codeBtn');
  btn.disabled = true;
  document.getElementById('codeSpinner').classList.add('on');
  document.getElementById('codeBtnText').textContent = 'Verifying…';

  try {
    const res = await fetch(`${API_URL}/app/api/auth/verify_code.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ code }),
      credentials: 'include',
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Invalid code.');

    resetCode = code;
    showStep('stepReset');

  } catch (err) {
    showAlert('Code', 'error', err.message);
  }

  btn.disabled = false;
  document.getElementById('codeSpinner').classList.remove('on');
  document.getElementById('codeBtnText').textContent = 'Verify code';
}

// ── Force du mot de passe ────────────────────────────────
function checkStrength(pwd) {
  const fill  = document.getElementById('strengthFill');
  const label = document.getElementById('strengthLabel');
  let score = 0;
  if (pwd.length >= 8)  score++;
  if (pwd.length >= 12) score++;
  if (/[A-Z]/.test(pwd)) score++;
  if (/[0-9]/.test(pwd)) score++;
  if (/[^A-Za-z0-9]/.test(pwd)) score++;

  const levels = [
    { pct:'20%', color:'#ef4444', label:'Very weak' },
    { pct:'40%', color:'#f97316', label:'Weak' },
    { pct:'60%', color:'#eab308', label:'Fair' },
    { pct:'80%', color:'#22c55e', label:'Strong' },
    { pct:'100%',color:'#16a34a', label:'Very strong' },
  ];
  const lvl = levels[Math.max(0, score - 1)] || levels[0];
  fill.style.width      = pwd.length ? lvl.pct   : '0';
  fill.style.background = pwd.length ? lvl.color  : '';
  label.textContent     = pwd.length ? lvl.label  : '';
  label.style.color     = pwd.length ? lvl.color  : '';
}

// ── Reset password ───────────────────────────────────────
document.getElementById('resetForm').addEventListener('submit', async e => {
  e.preventDefault();
  hideAlert('Reset');

  const pwd     = document.getElementById('newPassword').value;
  const confirm = document.getElementById('confirmPassword').value;

  if (pwd.length < 8) {
    showAlert('Reset', 'error', 'Password must be at least 8 characters.');
    return;
  }
  if (pwd !== confirm) {
    showAlert('Reset', 'error', 'Passwords do not match.');
    return;
  }

  const btn = document.getElementById('resetBtn');
  btn.disabled = true;
  document.getElementById('resetSpinner').classList.add('on');
  document.getElementById('resetBtnText').textContent = 'Resetting…';

  try {
    const res  = await fetch(`${API_URL}/app/api/auth/reset_password.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ code: resetCode, new_password: pwd }),
      credentials: 'include',
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Reset failed.');

    // Succès
    showStep('stepSuccess');
    animateDots();
    setTimeout(() => { window.location.href = '/video/login.php'; }, 3000);

  } catch (err) {
    showAlert('Reset', 'error', err.message);
    btn.disabled = false;
    document.getElementById('resetSpinner').classList.remove('on');
    document.getElementById('resetBtnText').textContent = 'Reset password';
  }
});

// ── Animation dots ───────────────────────────────────────
function animateDots() {
  const el = document.getElementById('dots');
  let i = 0;
  setInterval(() => {
    el.textContent = '.'.repeat((i++ % 3) + 1);
  }, 500);
}

// ── Token depuis URL (lien email) ────────────────────────
window.addEventListener('load', () => {
  const params = new URLSearchParams(window.location.search);
  const token  = params.get('token');
  if (token) {
    // Accès direct depuis le lien email → skip code, aller au reset
    resetCode = token;
    showStep('stepReset');
  }
});
</script>
</body>
</html>