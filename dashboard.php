
<?php
// dashboard.php — Page protégée VidGenius

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/helpers/auth_helper.php';

// ── Protection : redirige si non connecté ─────────────────
$user = requireAuth();

// Données fictives pour le dashboard
$stats = [
    ['label' => 'Videos Created',  'value' => '0',     'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',  'color' => '#2563eb'],
    ['label' => 'Total Views',      'value' => '0',     'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'color' => '#7c3aed'],
    ['label' => 'Active Series',    'value' => '0',     'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'color' => '#059669'],
    ['label' => 'Plan',             'value' => ucfirst($user['plan'] ?? 'Free'), 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z', 'color' => '#d97706'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Dashboard — VidGenius</title>
<style>
/* ═══════════════════════════════
   VARIABLES & RESET
═══════════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --sidebar-w:260px;
  --header-h:64px;
  --bg:#f0f2f8;
  --surface:#ffffff;
  --surface-2:#f7f8fc;
  --border:#e3e7f0;
  --blue:#2563eb;
  --blue-d:#1d4ed8;
  --blue-l:#eff6ff;
  --indigo:#4f46e5;
  --green:#16a34a;
  --green-l:#dcfce7;
  --yellow:#ca8a04;
  --yellow-l:#fef9c3;
  --red:#dc2626;
  --red-l:#fee2e2;
  --purple:#7c3aed;
  --purple-l:#ede9fe;
  --txt-1:#0f172a;
  --txt-2:#475569;
  --txt-3:#94a3b8;
  --shadow-sm:0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);
  --shadow-md:0 4px 16px rgba(0,0,0,.08),0 2px 6px rgba(0,0,0,.04);
  --shadow-lg:0 12px 40px rgba(37,99,235,.1),0 4px 12px rgba(0,0,0,.05);
  --shadow-xl:0 24px 60px rgba(37,99,235,.14),0 8px 24px rgba(0,0,0,.07);
  --r:.875rem;
  --r-lg:1.25rem;
  --r-full:9999px;
  --font-d:'Bricolage Grotesque',sans-serif;
  --font-b:'DM Sans',sans-serif;
  --ease:cubic-bezier(.22,1,.36,1);
}
html{scroll-behavior:smooth}
body{font-family:var(--font-b);background:var(--bg);color:var(--txt-1);min-height:100vh;display:flex;overflow-x:hidden}
a{color:inherit;text-decoration:none}
button{font-family:var(--font-b);cursor:pointer}
input,select,textarea{font-family:var(--font-b)}
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px}

/* ═══════════════════════════════
   SIDEBAR
═══════════════════════════════ */
.sidebar{
  display:flex;flex-direction:column;
  width:var(--sidebar-w);min-height:100vh;
  background:var(--surface);border-right:1px solid var(--border);
  position:fixed;top:0;left:0;bottom:0;z-index:50;
  transition:transform .3s var(--ease);
}
.sidebar__logo{
  display:flex;align-items:center;justify-content:center;
  height:var(--header-h);border-bottom:1px solid var(--border);flex-shrink:0;
}
.logo-link{
  display:flex;align-items:center;gap:.5rem;
  font-family:var(--font-d);font-size:1.2rem;font-weight:800;letter-spacing:-.03em;
  background:none;border:none;color:var(--txt-1);
}
.logo-link svg{color:var(--blue)}

.sidebar__nav{flex:1;padding:1.25rem .875rem;display:flex;flex-direction:column;gap:.2rem;overflow-y:auto}
.nav-section-label{font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--txt-3);padding:.75rem .875rem .35rem}

.nav-item{
  display:flex;align-items:center;gap:.75rem;
  padding:.7rem .875rem;border-radius:var(--r);
  font-size:.9rem;font-weight:500;color:var(--txt-2);
  transition:background .15s,color .15s;
  border:none;background:none;width:100%;text-align:left;position:relative;
}
.nav-item svg{flex-shrink:0;color:var(--txt-3);transition:color .15s}
.nav-item:hover{background:#f1f5fb;color:var(--txt-1)}
.nav-item:hover svg{color:var(--txt-2)}
.nav-item.active{background:var(--blue-l);color:var(--blue);font-weight:600}
.nav-item.active svg{color:var(--blue)}
.nav-item.active::before{content:'';position:absolute;left:0;top:20%;bottom:20%;width:3px;background:var(--blue);border-radius:0 3px 3px 0}

.sidebar__upgrade{padding:.875rem;border-top:1px solid var(--border);flex-shrink:0}
.upgrade-btn{
  display:flex;align-items:center;justify-content:space-between;width:100%;
  padding:.8rem 1rem;background:linear-gradient(135deg,var(--blue),var(--indigo));
  color:#fff;border-radius:var(--r);border:none;font-weight:600;font-size:.875rem;
  box-shadow:0 4px 14px rgba(37,99,235,.35);transition:transform .2s,box-shadow .2s;
}
.upgrade-btn:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(37,99,235,.45)}
.upgrade-btn__left{display:flex;align-items:center;gap:.5rem}
.tier-badge{font-size:.7rem;background:rgba(255,255,255,.22);padding:.2rem .6rem;border-radius:var(--r-full);font-weight:700}

.sidebar__profile{position:relative;padding:.75rem;border-top:1px solid var(--border);flex-shrink:0}
.profile-btn{
  display:flex;align-items:center;justify-content:space-between;width:100%;
  padding:.625rem .75rem;border-radius:var(--r);background:none;border:none;text-align:left;transition:background .15s;
}
.profile-btn:hover{background:var(--surface-2)}
.profile-info{display:flex;align-items:center;gap:.75rem;min-width:0}
.avatar{
  width:38px;height:38px;border-radius:.625rem;
  background:linear-gradient(135deg,var(--blue-l),rgba(99,102,241,.12));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--font-d);font-size:.95rem;font-weight:800;color:var(--blue);flex-shrink:0;
}
.profile-text{min-width:0}
.profile-name{font-size:.8rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:115px;display:block}
.profile-email{font-size:.72rem;color:var(--txt-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:115px;display:block}
.chevron{color:var(--txt-3);transition:transform .25s;flex-shrink:0}
.chevron.open{transform:rotate(180deg)}

.profile-dropdown{
  display:none;position:absolute;bottom:calc(100% - .5rem);left:.75rem;right:.75rem;
  background:var(--surface);border:1px solid var(--border);border-radius:var(--r);
  box-shadow:var(--shadow-md);overflow:hidden;z-index:60;
}
.profile-dropdown.open{display:block;animation:dropUp .2s var(--ease)}
@keyframes dropUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.dd-item{
  display:flex;align-items:center;gap:.75rem;padding:.8rem 1rem;
  font-size:.875rem;font-weight:500;width:100%;border:none;
  background:none;text-align:left;transition:background .15s;color:var(--txt-1);
}
.dd-item:hover{background:var(--surface-2)}
.dd-item svg{color:var(--txt-2);flex-shrink:0}
.dd-item--danger{color:var(--red)}
.dd-item--danger svg{color:var(--red)}

/* ═══════════════════════════════
   MOBILE HEADER
═══════════════════════════════ */
.mobile-header{
  display:none;align-items:center;justify-content:space-between;
  padding:0 1rem;height:var(--header-h);
  background:var(--surface);border-bottom:1px solid var(--border);
  position:sticky;top:0;z-index:40;
}
.hamburger{
  display:flex;align-items:center;justify-content:center;
  width:40px;height:40px;border-radius:.625rem;
  border:none;background:none;color:var(--txt-1);transition:background .15s;
}
.hamburger:hover{background:var(--surface-2)}
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(15,23,42,.35);backdrop-filter:blur(2px);z-index:49}
.sidebar-overlay.open{display:block}

/* ═══════════════════════════════
   MAIN LAYOUT
═══════════════════════════════ */
.main-wrap{flex:1;margin-left:var(--sidebar-w);display:flex;flex-direction:column;min-height:100vh}
.page-content{flex:1;padding:2.5rem;max-width:1280px;width:100%}

/* ═══════════════════════════════
   PAGES
═══════════════════════════════ */
.page{display:none}
.page.active{display:block;animation:pageIn .35s var(--ease)}
@keyframes pageIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

/* ═══════════════════════════════
   SHARED COMPONENTS
═══════════════════════════════ */
.page-header{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:2rem}
.page-header__title{font-family:var(--font-d);font-size:1.75rem;font-weight:800;letter-spacing:-.03em}
.page-header__sub{color:var(--txt-2);font-size:.9rem;margin-top:.3rem}

.btn{display:inline-flex;align-items:center;gap:.5rem;padding:.7rem 1.375rem;border-radius:var(--r-full);font-weight:600;font-size:.875rem;border:none;transition:transform .2s,box-shadow .2s,background .15s}
.btn-primary{background:linear-gradient(135deg,var(--blue),var(--indigo));color:#fff;box-shadow:0 4px 14px rgba(37,99,235,.3)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(37,99,235,.38)}
.btn-secondary{background:var(--surface);color:var(--txt-1);border:1.5px solid var(--border)}
.btn-secondary:hover{background:var(--surface-2)}
.btn-danger{background:var(--red-l);color:var(--red);border:none}
.btn-danger:hover{background:#fecaca}

.card{background:var(--surface);border-radius:var(--r-lg);border:1px solid var(--border);box-shadow:var(--shadow-sm)}

.badge{display:inline-flex;align-items:center;padding:.2rem .7rem;border-radius:var(--r-full);font-size:.72rem;font-weight:700}
.badge-green{background:var(--green-l);color:var(--green)}
.badge-yellow{background:var(--yellow-l);color:var(--yellow)}
.badge-gray{background:#f1f5f9;color:#64748b}
.badge-red{background:var(--red-l);color:var(--red)}
.badge-purple{background:var(--purple-l);color:var(--purple)}

.spinner{width:20px;height:20px;border:2.5px solid rgba(37,99,235,.2);border-top-color:var(--blue);border-radius:50%;animation:spin .7s linear infinite}
.spinner-white{border-color:rgba(255,255,255,.3);border-top-color:#fff}
@keyframes spin{to{transform:rotate(360deg)}}

.input{width:100%;padding:.8rem 1rem;border:1.5px solid var(--border);border-radius:var(--r);font-family:var(--font-b);font-size:.9rem;color:var(--txt-1);background:var(--surface);transition:border-color .15s,box-shadow .15s;outline:none}
.input:focus{border-color:var(--blue);box-shadow:0 0 0 4px rgba(37,99,235,.1)}
.input:disabled{opacity:.55;background:var(--surface-2)}
.select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2394a3b8' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .875rem center;padding-right:2.5rem}
.field{display:flex;flex-direction:column;gap:.4rem}
.field label{font-size:.8rem;font-weight:600;color:var(--txt-1)}

/* ═══════════════════════════════
   PAGE: SERIES
═══════════════════════════════ */
.stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem}
.stat-card{padding:1.5rem;display:flex;align-items:center;gap:1rem}
.stat-icon{width:48px;height:48px;border-radius:.875rem;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-label{font-size:.8rem;color:var(--txt-2);margin-bottom:.2rem}
.stat-value{font-family:var(--font-d);font-size:1.75rem;font-weight:800;letter-spacing:-.03em}
.series-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem}
.series-card{padding:1.5rem;transition:transform .2s,box-shadow .2s,border-color .2s;cursor:pointer}
.series-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-lg);border-color:#bfdbfe}
.series-card__head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem}
.series-card__name{font-family:var(--font-d);font-size:1.05rem;font-weight:700;margin-bottom:.25rem}
.series-card__niche{font-size:.8rem;color:var(--txt-2)}
.series-meta{display:flex;flex-direction:column;gap:.625rem;margin-bottom:1.25rem}
.series-meta-row{display:flex;align-items:center;justify-content:space-between;font-size:.82rem}
.series-meta-row span:first-child{color:var(--txt-2)}
.series-meta-row span:last-child{font-weight:600}
.series-card__footer{display:flex;align-items:center;justify-content:space-between;padding-top:1rem;border-top:1px solid var(--border);font-size:.75rem;color:var(--txt-3)}
.series-card__footer a{color:var(--blue);font-weight:600;display:flex;align-items:center;gap:.25rem}
.empty-state{padding:5rem 2rem;text-align:center;border:2px dashed var(--border);border-radius:var(--r-lg);background:var(--surface)}
.empty-icon{width:72px;height:72px;background:var(--blue-l);border-radius:1.25rem;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem}
.empty-icon svg{color:var(--blue)}
.empty-title{font-family:var(--font-d);font-size:1.3rem;font-weight:800;margin-bottom:.5rem}
.empty-sub{color:var(--txt-2);max-width:360px;margin:0 auto 2rem;font-size:.9rem;line-height:1.65}
.loading-center{display:flex;align-items:center;justify-content:center;min-height:40vh}

/* ═══════════════════════════════
   PAGE: BILLING
═══════════════════════════════ */
.plans-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;margin-bottom:2rem}
.plan-card{padding:2rem;display:flex;flex-direction:column;position:relative;transition:transform .2s,box-shadow .2s}
.plan-card--popular{border-color:var(--blue);box-shadow:var(--shadow-lg)}
.plan-badge-pop{position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,var(--blue),var(--indigo));color:#fff;padding:.3rem 1rem;border-radius:var(--r-full);font-size:.72rem;font-weight:800;letter-spacing:.06em;white-space:nowrap}
.plan-name{font-family:var(--font-d);font-size:1.1rem;font-weight:800;margin-bottom:.875rem}
.plan-price{display:flex;align-items:baseline;gap:.375rem;margin-bottom:.25rem}
.plan-price__amount{font-family:var(--font-d);font-size:2.25rem;font-weight:800;letter-spacing:-.04em}
.plan-price__period{font-size:.85rem;color:var(--txt-2)}
.plan-desc{font-size:.8rem;color:var(--txt-2);margin-bottom:1.5rem;line-height:1.6;min-height:2.5rem}
.plan-series-selector{background:var(--surface-2);border-radius:var(--r);padding:1rem;margin-bottom:1.5rem;border:1px solid var(--border)}
.plan-series-selector label{font-size:.78rem;font-weight:700;color:var(--txt-1);margin-bottom:.75rem;display:block}
.counter{display:flex;align-items:center;justify-content:space-between}
.counter-btn{width:36px;height:36px;border-radius:.625rem;border:1.5px solid var(--border);background:var(--surface);display:flex;align-items:center;justify-content:center;transition:background .15s,border-color .15s;color:var(--txt-1)}
.counter-btn:hover:not(:disabled){background:var(--blue-l);border-color:var(--blue)}
.counter-btn:disabled{opacity:.4;cursor:not-allowed}
.counter-val{font-family:var(--font-d);font-size:1.25rem;font-weight:800}
.counter-range{display:flex;justify-content:space-between;font-size:.7rem;color:var(--txt-3);margin-top:.5rem}
.plan-features{flex:1;list-style:none;display:flex;flex-direction:column;gap:.625rem;margin-bottom:1.75rem}
.plan-features li{display:flex;align-items:flex-start;gap:.625rem;font-size:.82rem;color:var(--txt-2)}
.plan-features li svg{color:var(--green);flex-shrink:0;margin-top:.1rem}
.plan-btn{width:100%;padding:.9rem;border-radius:var(--r);font-weight:700;font-size:.9rem;border:1.5px solid var(--border);background:var(--surface-2);color:var(--txt-1);transition:background .15s,border-color .15s,transform .15s}
.plan-btn:hover{background:var(--blue-l);border-color:var(--blue);color:var(--blue)}
.plan-btn--selected{background:linear-gradient(135deg,var(--blue),var(--indigo));color:#fff;border:none;box-shadow:0 4px 14px rgba(37,99,235,.3)}
.plan-btn--selected:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(37,99,235,.4)}
.trust-badges{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:2rem}
.trust-item{display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:var(--txt-2)}
.trust-item svg{color:var(--green)}

/* ═══════════════════════════════
   PAGE: CREATE WIZARD
═══════════════════════════════ */
.wizard-wrap{max-width:680px;margin:0 auto}
.progress-bar{height:4px;background:var(--border);border-radius:2px;overflow:hidden;margin-bottom:.5rem}
.progress-fill{height:100%;background:linear-gradient(90deg,var(--blue),var(--indigo));border-radius:2px;transition:width .4s var(--ease)}
.step-indicator{text-align:right;font-size:.78rem;color:var(--txt-3);margin-bottom:2rem}
.step-panel{display:none}
.step-panel.active{display:block;animation:pageIn .3s var(--ease)}
.step-title{font-family:var(--font-d);font-size:1.3rem;font-weight:800;letter-spacing:-.02em;margin-bottom:.35rem}
.step-sub{font-size:.875rem;color:var(--txt-2);margin-bottom:2rem}
.step-fields{display:flex;flex-direction:column;gap:1.375rem}
.voice-speed-label{display:flex;justify-content:space-between;font-size:.8rem;font-weight:600;color:var(--txt-1);margin-bottom:.5rem}
.voice-speed-label span:last-child{color:var(--blue)}
input[type=range]{width:100%;accent-color:var(--blue);cursor:pointer}
.platform-opt{display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;border:1.5px solid var(--border);border-radius:var(--r);cursor:pointer;transition:border-color .15s,background .15s}
.platform-opt:has(input:checked){border-color:var(--blue);background:var(--blue-l)}
.platform-opt input{width:18px;height:18px;accent-color:var(--blue)}
.platform-label p:first-child{font-weight:600;font-size:.9rem}
.platform-label p:last-child{font-size:.78rem;color:var(--txt-2)}
.tag-input-row{display:flex;gap:.625rem}
.tag-input-row .input{flex:1}
.tags{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.625rem}
.tag{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--r-full);font-size:.78rem;font-weight:500}
.tag-remove{color:var(--txt-3);background:none;border:none;font-size:1.1rem;line-height:1;transition:color .15s}
.tag-remove:hover{color:var(--red)}
.caption-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.875rem}
.caption-opt{border:2px solid var(--border);border-radius:var(--r);padding:.875rem;cursor:pointer;text-align:center;transition:border-color .15s;position:relative}
.caption-opt.selected{border-color:var(--blue)}
.caption-preview{aspect-ratio:9/16;background:linear-gradient(160deg,#3b3b4b,#1a1a2e);border-radius:.5rem;margin-bottom:.625rem;display:flex;align-items:center;justify-content:center}
.caption-preview span{color:rgba(255,255,255,.4);font-size:.65rem}
.caption-name{font-size:.78rem;font-weight:600}
.caption-check{position:absolute;top:.5rem;right:.5rem;width:20px;height:20px;background:var(--blue);border-radius:50%;display:none;align-items:center;justify-content:center}
.caption-opt.selected .caption-check{display:flex}
.days-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:.5rem}
.day-btn{padding:.625rem;border-radius:.625rem;border:1.5px solid var(--border);background:var(--surface-2);font-size:.78rem;font-weight:700;text-align:center;transition:background .15s,border-color .15s,color .15s;color:var(--txt-2)}
.day-btn.active{background:var(--blue);border-color:var(--blue);color:#fff}
.review-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem}
.review-item{background:var(--surface-2);border-radius:var(--r);padding:1rem;border:1px solid var(--border)}
.review-item label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-3);display:block;margin-bottom:.35rem}
.review-item p{font-weight:600;font-size:.9rem}
.wizard-footer{display:flex;justify-content:space-between;align-items:center;padding:1.5rem 0 0;margin-top:2rem;border-top:1px solid var(--border)}

/* ═══════════════════════════════
   PAGE: TUTORIALS
═══════════════════════════════ */
.tutorials-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem}
.tutorial-card{padding:1.75rem;transition:transform .2s,box-shadow .2s}
.tutorial-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-md)}
.tutorial-card__head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem}
.tutorial-duration{font-size:.78rem;color:var(--txt-3);font-weight:500}
.tutorial-title{font-family:var(--font-d);font-size:1.05rem;font-weight:800;margin-bottom:.5rem;letter-spacing:-.02em}
.tutorial-desc{font-size:.85rem;color:var(--txt-2);line-height:1.6;margin-bottom:1.25rem}
.tutorial-watch{display:inline-flex;align-items:center;gap:.4rem;font-size:.83rem;font-weight:700;color:var(--blue);background:none;border:none;transition:gap .2s}
.tutorial-watch:hover{gap:.65rem}

/* ═══════════════════════════════
   PAGE: SETTINGS
═══════════════════════════════ */
.settings-card{border-radius:1.25rem;overflow:hidden;max-width:720px}
.settings-section{padding:1.75rem;border-bottom:1px solid var(--border)}
.settings-section:last-child{border-bottom:none}
.settings-title{font-family:var(--font-d);font-size:1rem;font-weight:800;margin-bottom:1.25rem;letter-spacing:-.02em}
.settings-fields{display:flex;flex-direction:column;gap:1.125rem}
.toggle-row{display:flex;align-items:center;justify-content:space-between;gap:1rem}
.toggle-info p:first-child{font-weight:600;font-size:.875rem}
.toggle-info p:last-child{font-size:.78rem;color:var(--txt-2);margin-top:.15rem}
.toggle{position:relative;width:44px;height:24px;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0}
.toggle-track{position:absolute;inset:0;background:#cbd5e1;border-radius:var(--r-full);transition:background .25s;cursor:pointer}
.toggle-track::before{content:'';position:absolute;left:3px;top:3px;width:18px;height:18px;background:#fff;border-radius:50%;transition:transform .25s;box-shadow:0 1px 4px rgba(0,0,0,.15)}
.toggle input:checked + .toggle-track{background:var(--blue)}
.toggle input:checked + .toggle-track::before{transform:translateX(20px)}
.settings-save{padding:1.25rem 1.75rem;background:var(--surface-2)}
.danger-zone{border:1.5px solid var(--red-l) !important}
.danger-title{color:var(--red)}

/* ═══════════════════════════════
   RESPONSIVE
═══════════════════════════════ */
@media(max-width:768px){
  .sidebar{transform:translateX(-100%)}
  .sidebar.open{transform:translateX(0)}
  .mobile-header{display:flex}
  .main-wrap{margin-left:0}
  .page-content{padding:1.25rem}
  .stats-grid{grid-template-columns:1fr}
  .plans-grid{grid-template-columns:1fr}
  .caption-grid{grid-template-columns:repeat(2,1fr)}
  .days-grid{grid-template-columns:repeat(4,1fr)}
  .review-grid{grid-template-columns:1fr}
  .wizard-footer{flex-direction:column-reverse;gap:.75rem}
  .wizard-footer .btn{width:100%;justify-content:center}
}
@media(max-width:480px){
  .series-grid{grid-template-columns:1fr}
  .tutorials-grid{grid-template-columns:1fr}
}



 
/* ─── VIDEO PAGE OVERRIDE ─── */
#page-videos { font-family: 'DM Sans', sans-serif; }
 
/* Filtres */
.vf-bar {
  display: flex;
  flex-wrap: wrap;
  gap: .625rem;
  align-items: center;
  margin-bottom: 1.75rem;
}
.vf-pills {
  display: flex;
  gap: .2rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r-full);
  padding: .2rem;
}
.vf-btn {
  padding: .4rem .95rem;
  border-radius: var(--r-full);
  font-size: .8rem;
  font-weight: 600;
  border: none;
  background: none;
  color: var(--txt-2);
  cursor: pointer;
  transition: all .15s;
  white-space: nowrap;
}
.vf-btn.active { background: var(--blue); color: #fff; }
.vf-btn:hover:not(.active) { background: var(--blue-l); color: var(--blue); }
.vf-selects { display: flex; gap: .625rem; margin-left: auto; }
 
/* Grille portrait */
#videosGrid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1.125rem;
}
 
/* Card portrait */
.vcard {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  overflow: hidden;
  cursor: pointer;
  transition: transform .2s var(--ease), box-shadow .2s var(--ease), border-color .2s;
}
.vcard:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
  border-color: #bfdbfe;
}
.vcard-thumb {
  position: relative;
  width: 100%;
  aspect-ratio: 9/16;
  background: linear-gradient(160deg, #1e293b, #0f172a);
  overflow: hidden;
}
.vcard-thumb img {
  width: 100%; height: 100%;
  object-fit: cover;
  display: block;
  transition: transform .35s var(--ease);
}
.vcard:hover .vcard-thumb img { transform: scale(1.06); }
 
.vcard-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,.72) 0%, transparent 55%);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: .625rem;
}
.vcard-top { display: flex; justify-content: flex-end; }
.vcard-bottom { display: flex; align-items: flex-end; justify-content: space-between; }
 
.vcard-play {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 46px; height: 46px;
  background: rgba(255,255,255,.92);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  opacity: 0;
  transition: opacity .2s;
  box-shadow: 0 4px 20px rgba(0,0,0,.35);
}
.vcard:hover .vcard-play { opacity: 1; }
.vcard-play svg { color: var(--blue); margin-left: 3px; }
 
.vcard-duration {
  background: rgba(0,0,0,.72);
  color: #fff;
  font-size: .68rem;
  font-weight: 700;
  padding: .2rem .45rem;
  border-radius: .35rem;
  letter-spacing: .02em;
}
.vcard-status-dot {
  width: 7px; height: 7px;
  border-radius: 50%;
  box-shadow: 0 0 0 2px rgba(255,255,255,.35);
}
.vcard-body {
  padding: .75rem;
  border-top: 1px solid var(--border);
}
.vcard-series {
  font-family: var(--font-d);
  font-size: .82rem;
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: .2rem;
}
.vcard-meta { font-size: .72rem; color: var(--txt-3); }
 
/* ── Vue détail ── */
#videosDetailView { animation: pageIn .3s var(--ease); }
 
.vd-layout {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 2.5rem;
  align-items: start;
}
 
/* Téléphone */
.vd-phone-wrap { position: sticky; top: 1.5rem; }
.vd-phone {
  width: 100%;
  aspect-ratio: 9/16;
  background: #0f172a;
  border-radius: 22px;
  overflow: hidden;
  position: relative;
  box-shadow: 0 24px 60px rgba(0,0,0,.35), 0 0 0 1px rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.08);
}
.vd-phone video {
  width: 100%; height: 100%;
  object-fit: cover;
  display: block;
}
.vd-phone-placeholder {
  width: 100%; height: 100%;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: 12px;
}
.vd-phone-placeholder svg { opacity: .25; }
.vd-phone-placeholder span { color: rgba(255,255,255,.2); font-size: .78rem; }
.vd-phone-caption {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  padding: 1.25rem .875rem .875rem;
  background: linear-gradient(to top, rgba(0,0,0,.8) 0%, transparent 100%);
}
.vd-caption-word {
  display: inline-block;
  background: rgba(255,220,0,.94);
  color: #111;
  font-family: var(--font-d);
  font-size: .75rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 4px;
  letter-spacing: .04em;
  margin-bottom: 5px;
}
.vd-phone-series { color: rgba(255,255,255,.7); font-size: .72rem; }
 
.vd-phone-actions { display: flex; flex-direction: column; gap: .5rem; margin-top: 1rem; }
.vd-action-btn {
  display: flex; align-items: center; justify-content: center; gap: .5rem;
  width: 100%; padding: .65rem;
  border-radius: var(--r);
  font-size: .82rem; font-weight: 600;
  border: 1.5px solid var(--border);
  background: var(--surface);
  color: var(--txt-1);
  cursor: pointer;
  transition: all .15s;
}
.vd-action-btn:hover { background: var(--surface-2); border-color: #93c5fd; }
.vd-action-btn.primary {
  background: linear-gradient(135deg, var(--blue), var(--indigo));
  color: #fff; border: none;
  box-shadow: 0 4px 14px rgba(37,99,235,.3);
}
.vd-action-btn.primary:hover { box-shadow: 0 6px 20px rgba(37,99,235,.4); transform: translateY(-1px); }
.vd-action-btn.danger { color: var(--red); border-color: #fecaca; }
.vd-action-btn.danger:hover { background: var(--red-l); }
 
.vd-notice {
  margin-top: .75rem;
  padding: .65rem .875rem;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: var(--r);
  font-size: .75rem;
  color: #92400e;
  line-height: 1.5;
}
 
/* Panneau droit */
.vd-right { display: flex; flex-direction: column; gap: 1.375rem; }
 
.vd-topbar {
  display: flex; align-items: center;
  justify-content: space-between; flex-wrap: wrap; gap: .75rem;
}
 
/* Champs */
.vd-field { display: flex; flex-direction: column; gap: .4rem; }
.vd-label {
  font-size: .72rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .07em;
  color: var(--txt-3);
}
.vd-input, .vd-textarea {
  width: 100%;
  padding: .75rem .875rem;
  border: 1.5px solid var(--border);
  border-radius: var(--r);
  font-family: var(--font-b);
  font-size: .875rem;
  color: var(--txt-1);
  background: var(--surface);
  outline: none;
  transition: border-color .15s, box-shadow .15s;
}
.vd-input:focus, .vd-textarea:focus {
  border-color: var(--blue);
  box-shadow: 0 0 0 4px rgba(37,99,235,.08);
}
.vd-textarea { resize: none; min-height: 80px; line-height: 1.6; }
.vd-field-note {
  font-size: .72rem; color: var(--txt-3); line-height: 1.4;
}
.vd-charcount { font-size: .72rem; color: var(--txt-3); text-align: right; margin-top: .2rem; }
 
/* Plateformes */
.vd-plat-row { display: flex; flex-wrap: wrap; gap: .5rem; }
.vd-plat {
  display: flex; align-items: center; gap: .5rem;
  padding: .45rem .875rem;
  border: 1.5px solid var(--border);
  border-radius: var(--r-full);
  font-size: .8rem; font-weight: 600;
  color: var(--txt-2);
  cursor: pointer;
  background: var(--surface);
  transition: all .15s;
  user-select: none;
}
.vd-plat.on { border-color: var(--blue); color: var(--blue); background: var(--blue-l); }
.vd-plat-icon {
  width: 18px; height: 18px;
  border-radius: 4px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
 
/* Schedule */
.vd-sched-row { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
.vd-pub-row { display: flex; align-items: center; gap: .625rem; margin-top: .5rem; }
.vd-pub-label { font-size: .82rem; color: var(--txt-2); }
 
/* Save btn */
.vd-save-btn {
  display: flex; align-items: center; justify-content: center; gap: .5rem;
  width: 100%; padding: .875rem;
  background: linear-gradient(135deg, var(--blue), var(--indigo));
  color: #fff; border: none;
  border-radius: var(--r);
  font-family: var(--font-b);
  font-size: .9rem; font-weight: 700;
  cursor: pointer;
  transition: transform .2s, box-shadow .2s;
  box-shadow: 0 4px 14px rgba(37,99,235,.3);
}
.vd-save-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(37,99,235,.38); }
.vd-save-btn.saved { background: linear-gradient(135deg, #059669, #047857); }
 
/* Stats row */
.vd-stats-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: .75rem;
}
.vd-stat {
  background: var(--surface-2);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: .875rem;
  text-align: center;
}
.vd-stat-val {
  font-family: var(--font-d);
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: -.03em;
}
.vd-stat-lbl { font-size: .72rem; color: var(--txt-3); margin-top: .2rem; }
 
/* Responsive */
@media (max-width: 900px) {
  .vd-layout { grid-template-columns: 1fr; }
  .vd-phone-wrap { position: static; max-width: 280px; margin: 0 auto; }
  .vd-sched-row { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
  #videosGrid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
  .vd-stats-row { grid-template-columns: 1fr 1fr; }
}



/* Responsive */
@media (max-width: 640px) {
  #videosGridFaceless {
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1rem;
  }
  
  .page-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .page-header > div:last-child {
    display: flex;
    gap: 0.5rem;
  }
  
  .page-header .btn-secondary {
    flex: 1;
    justify-content: center;
    padding: 0.5rem;
    font-size: 0.75rem;
  }
}
</style>
</head>
<body>

<!-- ══════════════════════════════════
     SIDEBAR
══════════════════════════════════ -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar__logo">
    <button class="logo-link" onclick="navigate('series')">
      <svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
      </svg>
      VidGenius
    </button>
  </div>

  <nav class="sidebar__nav">
    <span class="nav-section-label">Content</span>
    <button class="nav-item" data-page="series" onclick="navigate('series')">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
      Series
    </button>
    <button class="nav-item" data-page="videos" onclick="navigate('videos')">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
      Videos
    </button>
    <button class="nav-item" data-page="create" onclick="navigate('create')">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Create Series
    </button>
    <button class="nav-item" data-page="tutorials" onclick="navigate('tutorials')">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      Tutorials
    </button>
    <span class="nav-section-label" style="margin-top:.5rem">Account</span>
   <!-- <button class="nav-item" data-page="billing" onclick="navigate('billing')">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
      Billing
    </button>-->
    <button class="nav-item" data-page="settings" onclick="navigate('settings')">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      Settings
    </button>
  </nav>

  <div class="sidebar__upgrade">
    <button class="upgrade-btn" onclick="navigate('billing')">
      <div class="upgrade-btn__left">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Upgrade Plan
      </div>
      <span class="tier-badge">Free</span>
    </button>
  </div>

  <div class="sidebar__profile">
    <button class="profile-btn" id="profileBtn">
      <div class="profile-info">
        <div class="avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
<span class="profile-name"><?= htmlspecialchars($user['name']) ?></span>

      </div>
      <svg class="chevron" id="chevron" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>
    <div class="profile-dropdown" id="profileDropdown">
      <a href="mailto:support@vidgenius.com" class="dd-item">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Contact Support
      </a>
      <button class="dd-item dd-item--danger" onclick="logout()">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Logout
      </button>
    </div>
  </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ══════════════════════════════════
     MAIN
══════════════════════════════════ -->
<div class="main-wrap">

  <header class="mobile-header">
    <button class="logo-link" onclick="navigate('series')" style="font-size:1.1rem">
      <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--blue)">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
      </svg>
      VidGenius
    </button>
    <button class="hamburger" id="hamburger">
      <svg id="hambIcon" width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </header>

  <div class="page-content">

    <!-- ── SERIES ── -->
    <div class="page" id="page-series">
      <div class="page-header">
        <div>
          <h1 class="page-header__title">Your Series</h1>
          <p class="page-header__sub">Manage and monitor your automated video series</p>
        </div>
        <button class="btn btn-primary" onclick="navigate('create')">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
          New Series
        </button>
      </div>
      <div class="stats-grid">
        <div class="card stat-card">
          <div class="stat-icon" style="background:var(--blue-l)">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--blue)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
          </div>
          <div><p class="stat-label">Total Series</p><p class="stat-value" id="statTotal">0</p></div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon" style="background:var(--green-l)">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--green)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div><p class="stat-label">Active Series</p><p class="stat-value" id="statActive">0</p></div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon" style="background:var(--purple-l)">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--purple)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          </div>
          <div><p class="stat-label">Total Video</p><p class="stat-value" id="statVideos">0</p></div>
        </div>
      </div>
      <div class="empty-state" id="seriesEmpty">
        <div class="empty-icon"><svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg></div>
        <h2 class="empty-title">No series yet</h2>
        <p class="empty-sub">Create your first automated video series. Pick a niche, set your style, and let AI generate &amp; post for you.</p>
        <button class="btn btn-primary" onclick="navigate('create')">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
          Create first series
        </button>
      </div>
      <div class="series-grid" id="seriesGrid" style="display:none"></div>
    </div>

    <!-- ── VIDEOS ── -->

<div class="page" id="page-videos">
 
  <!-- ── Vue liste ── -->
  <div id="videosListView">
    <div class="page-header">
      <div>
        <h1 class="page-header__title">Videos</h1>
        <p class="page-header__sub">All your generated videos</p>
      </div>
      <button class="btn btn-primary" onclick="navigate('create')">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        New Series
      </button>
    </div>
 
    <!-- Filtres -->
    <div class="vf-bar">
      <div class="vf-pills">
        <button class="vf-btn active" onclick="setVideoFilter('all',this)">All</button>
        <button class="vf-btn" onclick="setVideoFilter('completed',this)">Completed</button>
        <button class="vf-btn" onclick="setVideoFilter('processing',this)">Processing</button>
        <button class="vf-btn" onclick="setVideoFilter('pending',this)">Pending</button>
        <button class="vf-btn" onclick="setVideoFilter('failed',this)">Failed</button>
      </div>
      <div class="vf-selects">
        <select id="filterSeries" class="input select"
          style="width:auto;min-width:140px;padding:.5rem 2.5rem .5rem 1rem;font-size:.82rem"
          onchange="filterVideos()">
          <option value="all">All Series</option>
        </select>
        <select id="sortVideos" class="input select"
          style="width:auto;min-width:130px;padding:.5rem 2.5rem .5rem 1rem;font-size:.82rem"
          onchange="filterVideos()">
          <option value="newest">Newest first</option>
          <option value="oldest">Oldest first</option>
          <option value="duration">By duration</option>
        </select>
      </div>
      <span id="videoCount" style="font-size:.82rem;color:var(--txt-3);font-weight:500;white-space:nowrap"></span>
    </div>
 
    <!-- Empty state -->
    <div class="empty-state" id="videosEmpty" style="display:none">
      <div class="empty-icon">
        <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
      </div>
      <h2 class="empty-title">No videos found</h2>
      <p class="empty-sub">Try changing your filters or create a new series to start generating videos.</p>
      <button class="btn btn-primary" onclick="navigate('create')">Create a series</button>
    </div>
 
    <!-- Grille portrait -->
    <div id="videosGrid"></div>
  </div>
 
  <!-- ── Vue détail ── -->
  <div id="videosDetailView" style="display:none">
 
    <!-- Breadcrumb -->
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:2rem">
      <button class="btn btn-secondary" onclick="closeVideoDetail()"
        style="padding:.5rem 1rem;font-size:.82rem">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back
      </button>
      <div style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:var(--txt-3)">
        <span>Videos</span>
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span id="detailBreadcrumb" style="color:var(--txt-1);font-weight:600"></span>
      </div>
    </div>
 
    <!-- Layout -->
    <div class="vd-layout">
 
      <!-- Colonne gauche : téléphone -->
      <div class="vd-phone-wrap">
        <div class="vd-phone">
          <video id="detailVideo" controls style="display:none"></video>
          <div class="vd-phone-placeholder" id="detailPlaceholder">
            <svg width="44" height="44" fill="none" stroke="rgba(255,255,255,0.25)" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <span id="placeholderLabel">No preview available</span>
          </div>
          <div class="vd-phone-caption" id="detailPhoneCaption" style="display:none">
            <div class="vd-caption-word" id="detailCaptionWord"></div>
            <div class="vd-phone-series" id="detailPhoneSeries"></div>
          </div>
        </div>
 
        <!-- Actions sous le téléphone -->
        <div class="vd-phone-actions">
          <a id="detailDownloadLink" href="#" download style="display:none;text-decoration:none">
            <button class="vd-action-btn primary" style="width:100%">
              <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Download video
            </button>
          </a>
          <a id="detailOpenLink" href="#" target="_blank" style="display:none;text-decoration:none">
            <button class="vd-action-btn" style="width:100%">
              <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
              Open in new tab
            </button>
          </a>
          <button class="vd-action-btn danger" onclick="deleteCurrentVideo()">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Delete video
          </button>
        </div>
 
        <div class="vd-notice" id="detailNotice" style="display:none">
          <strong>⏱</strong> This video has already been rendered.
          You have 90 minutes remaining before publishing.
        </div>
 
        <div id="detailError"
          style="display:none;margin-top:.75rem;padding:.75rem .875rem;background:var(--red-l);border-radius:var(--r);color:var(--red);font-size:.82rem">
        </div>
      </div>
 
      <!-- Colonne droite : infos & édition -->
      <div class="vd-right">
 
        <!-- Status + date -->
        <div class="vd-topbar">
          <div style="display:flex;align-items:center;gap:.625rem">
            <span id="detailBadge" class="badge"></span>
            <span id="detailDate" style="font-size:.82rem;color:var(--txt-3)"></span>
          </div>
          <span id="detailDuration"
            style="font-family:var(--font-d);font-size:.9rem;font-weight:700;color:var(--txt-2)">
          </span>
        </div>
 
        <!-- Stats mini -->
      <!--  <div class="vd-stats-row">
          <div class="vd-stat">
            <div class="vd-stat-val" id="statViews">—</div>
            <div class="vd-stat-lbl">Views</div>
          </div>
          <div class="vd-stat">
            <div class="vd-stat-val" id="statLikes">—</div>
            <div class="vd-stat-lbl">Likes</div>
          </div>
          <div class="vd-stat">
            <div class="vd-stat-val" id="statShares">—</div>
            <div class="vd-stat-lbl">Shares</div>
          </div>
        </div>-->
 
        <!-- Titre -->
        <div class="vd-field">
          <label class="vd-label">Title</label>
          <textarea class="vd-textarea" id="vdTitle" rows="2" maxlength="100"
            oninput="updateVdCount('vdTitle','vdTitleCount',100)"></textarea>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <span class="vd-field-note">YouTube only · Max 100 chars · No &lt; &gt; symbols</span>
            <span class="vd-charcount"><span id="vdTitleCount">0</span>/100</span>
          </div>
        </div>
 
        <!-- Description -->
        <div class="vd-field">
          <label class="vd-label">Description</label>
          <textarea class="vd-textarea" id="vdDesc" rows="4" maxlength="2200"
            oninput="updateVdCount('vdDesc','vdDescCount',2200)"></textarea>
          <div class="vd-charcount"><span id="vdDescCount">0</span>/2200</div>
        </div>
 
        <!-- Plateformes -->
        <div class="vd-field">
          <label class="vd-label">Supported platforms</label>
          <div class="vd-plat-row">
            <div class="vd-plat on" id="vdPlatYoutube" onclick="this.classList.toggle('on')">
              <div class="vd-plat-icon" style="background:#ef4444">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="#fff">
                  <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
              </div>
              YouTube
            </div>
            <div class="vd-plat" id="vdPlatTiktok" onclick="this.classList.toggle('on')">
              <div class="vd-plat-icon" style="background:#000;border-radius:4px">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="#fff">
                  <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.34 6.34 0 00-.79-.05A6.34 6.34 0 003.15 15.3a6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.34-6.34V8.72a8.16 8.16 0 004.77 1.52v-3.5a4.83 4.83 0 01-1.01-.05z"/>
                </svg>
              </div>
              TikTok
            </div>
            <div class="vd-plat" id="vdPlatInstagram" onclick="this.classList.toggle('on')">
              <div class="vd-plat-icon" style="background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);border-radius:4px">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="#fff">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z"/>
                </svg>
              </div>
              Instagram
            </div>
          </div>
        </div>
 
        <!-- Série + planification -->
        <div class="vd-field">
          <label class="vd-label">Series</label>
          <input class="vd-input" type="text" id="vdSeriesName" disabled/>
        </div>
 
        <div class="vd-field">
          <label class="vd-label">Schedule</label>
          <div class="vd-sched-row">
            <input class="vd-input" type="date" id="vdSchedDate"/>
            <input class="vd-input" type="time" id="vdSchedTime" value="14:30"/>
          </div>
          <div class="vd-pub-row">
            <label class="toggle" style="width:40px;height:22px">
              <input type="checkbox" id="vdPublished" onchange="togglePublished()"/>
              <span class="toggle-track"></span>
            </label>
            <span class="vd-pub-label" id="vdPublishedLabel">Has this video already been published?</span>
          </div>
        </div>
 
        <!-- Save -->
        <button class="vd-save-btn" id="vdSaveBtn" onclick="saveVideoDetail()">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          All changes saved
        </button>
      </div>
    </div>
  </div>
</div>
    <!-- ── CREATE ── -->
    <div class="page" id="page-create">
      <div class="wizard-wrap">
        <div class="page-header" style="margin-bottom:1.5rem">
          <div>
            <h1 class="page-header__title">Create Series</h1>
            <p class="page-header__sub">Configure your automated video pipeline</p>
          </div>
        </div>
        <div class="progress-bar"><div class="progress-fill" id="progressFill" style="width:14.28%"></div></div>
        <p class="step-indicator">Step <span id="stepCurrent">1</span> of 7</p>

        <!-- Step 1 -->
        <div class="step-panel active" id="step-1">
          <h2 class="step-title">Basic Information</h2>
          <p class="step-sub">Tell us about your series</p>
          <div class="step-fields">
            <div class="field"><label>Series Name</label><input class="input" type="text" id="seriesName" placeholder="e.g., Scary Stories, History Facts"/></div>
            <div class="field"><label>Niche</label>
              <select class="input select" id="seriesNiche">
                <option value="">Select a niche</option>
                <option value="true-crime">True Crime</option>
                <option value="history">History</option>
                <option value="scary-stories">Scary Stories</option>
                <option value="anime">Anime</option>
                <option value="motivation">Motivation</option>
                <option value="facts">Facts</option>
                <option value="conspiracy">Conspiracy</option>
                <option value="technology">Technology</option>
                <option value="finance">Finance</option>
              </select>
            </div>
            <div class="field"><label>Description <span style="color:var(--txt-3);font-weight:400">(optional)</span></label><textarea class="input" id="seriesDesc" rows="3" placeholder="Describe what your series is about…"></textarea></div>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="step-panel" id="step-2">
          <h2 class="step-title">Content Configuration</h2>
          <p class="step-sub">Customize how your videos look and sound</p>
          <div class="step-fields">
            <div class="field"><label>Voice</label>
              <select class="input select" id="voiceSelect">
                <option value="adam">Adam (Male)</option>
                <option value="antoni">Antoni (Male)</option>
                <option value="bella">Bella (Female)</option>
                <option value="domi">Domi (Female)</option>
                <option value="josh">Josh (Male)</option>
                <option value="rachel">Rachel (Female)</option>
              </select>
            </div>
            <div class="field">
              <div class="voice-speed-label"><span>Voice Speed</span><span id="speedVal">1.0x</span></div>
              <input type="range" min="0.5" max="2.0" step="0.1" value="1.0" id="voiceSpeed" oninput="document.getElementById('speedVal').textContent=this.value+'x'"/>
            </div>
            <div class="field"><label>Image Style</label>
              <select class="input select" id="imageStyle">
                <option value="cinematic">Cinematic</option>
                <option value="anime">Anime</option>
                <option value="realistic">Realistic</option>
                <option value="sketch">Sketch</option>
                <option value="vibrant">Vibrant</option>
                <option value="dark">Dark &amp; Moody</option>
              </select>
            </div>
            <div class="field"><label>Video Length</label>
              <select class="input select" id="videoLength">
                <option value="30">30 seconds</option>
                <option value="60" selected>60 seconds</option>
                <option value="90">90 seconds</option>
                <option value="120">120 seconds</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="step-panel" id="step-3">
          <h2 class="step-title">Platforms</h2>
          <p class="step-sub">Choose where to post your videos</p>
          <div class="step-fields">
            <label class="platform-opt"><input type="checkbox" id="platYoutube"/>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="#ef4444"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
              <div class="platform-label"><p>YouTube</p><p>Post as Shorts</p></div>
            </label>
            <label class="platform-opt"><input type="checkbox" id="platTiktok"/>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 1.48.4V8.23a6.39 6.39 0 0 0-1.48-.2 6.34 6.34 0 0 0-6.04 8.39 6.34 6.34 0 0 0 11.4 2.44 6.34 6.34 0 0 0 1.09-3.54V8.72a8.16 8.16 0 0 0 4.77 1.52v-3.5a4.83 4.83 0 0 1-1.19-.05z"/></svg>
              <div class="platform-label"><p>TikTok</p><p>Auto-post to TikTok</p></div>
            </label>
            <label class="platform-opt"><input type="checkbox" id="platInstagram"/>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="#e1306c"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0 2.163c-3.259 0-3.667.014-4.947.072-2.404.107-3.636 1.202-3.743 3.743-.058 1.28-.072 1.688-.072 4.947 0 3.259.014 3.668.072 4.947.107 2.402 1.202 3.636 3.743 3.743 1.28.058 1.688.072 4.947.072 3.259 0 3.668-.014 4.947-.072 2.404-.107 3.636-1.202 3.743-3.743.058-1.28.072-1.688.072-4.947 0-3.259-.014-3.667-.072-4.947-.107-2.402-1.202-3.636-3.743-3.743-1.28-.058-1.688-.072-4.947-.072z"/></svg>
              <div class="platform-label"><p>Instagram</p><p>Post as Reels</p></div>
            </label>
          </div>
        </div>

        <!-- Step 4 -->
        <div class="step-panel" id="step-4">
          <h2 class="step-title">Content Rules</h2>
          <p class="step-sub">Set up your content preferences</p>
          <div class="step-fields">
            <div class="field">
              <label>Default Hashtags</label>
              <div class="tag-input-row">
                <input class="input" type="text" id="tagInput" placeholder="Enter hashtag (without #)"/>
                <button class="btn btn-primary" type="button" onclick="addTag()">Add</button>
              </div>
              <div class="tags" id="tagsList"></div>
            </div>
            <div class="field"><label>Call to Action</label><input class="input" type="text" id="callToAction" value="Follow for more!"/></div>
            <div class="field"><label>Tone</label>
              <select class="input select" id="toneSelect">
                <option value="motivational">Motivational</option>
                <option value="serious">Serious</option>
                <option value="humorous">Humorous</option>
                <option value="dramatic">Dramatic</option>
                <option value="mysterious">Mysterious</option>
                <option value="educational">Educational</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Step 5 -->
        <div class="step-panel" id="step-5">
          <h2 class="step-title">Caption Style</h2>
          <p class="step-sub">Choose how captions appear in your videos</p>
          <div class="caption-grid" id="captionGrid"></div>
          <input type="hidden" id="captionStyle" value="karaoke"/>
        </div>

        <!-- Step 6 -->
        <div class="step-panel" id="step-6">
          <h2 class="step-title">Schedule</h2>
          <p class="step-sub">Set your posting schedule</p>
          <div class="step-fields">
            <div class="field">
              <label>Posting Days</label>
              <div class="days-grid" id="daysGrid"></div>
            </div>
            <div class="field"><label>Posting Time</label><input class="input" type="time" id="scheduleTime" value="08:00"/></div>
            <div class="field"><label>Timezone</label>
              <select class="input select" id="timezoneSelect">
                <option value="UTC">UTC</option>
                <option value="America/New_York">Eastern Time</option>
                <option value="America/Chicago">Central Time</option>
                <option value="America/Denver">Mountain Time</option>
                <option value="America/Los_Angeles">Pacific Time</option>
                <option value="Europe/London">London</option>
                <option value="Europe/Paris">Paris</option>
                <option value="Asia/Tokyo">Tokyo</option>
                <option value="Asia/Shanghai">Shanghai</option>
                <option value="Australia/Sydney">Sydney</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Step 7 -->
        <div class="step-panel" id="step-7">
          <h2 class="step-title">Review &amp; Create</h2>
          <p class="step-sub">Review your series configuration before creating</p>
          <div class="review-grid" id="reviewGrid"></div>
        </div>

        <div class="wizard-footer">
          <button class="btn btn-secondary" id="btnBack" onclick="wizardBack()" disabled>
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
          </button>
          <button class="btn btn-primary" id="btnNext" onclick="wizardNext()">
            Continue
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- ── TUTORIALS ── -->
    <div class="page" id="page-tutorials">
      <div class="page-header">
        <div>
          <h1 class="page-header__title">Tutorials</h1>
          <p class="page-header__sub">Learn how to get the most out of VidGenius</p>
        </div>
      </div>
      <div class="tutorials-grid" id="tutorialsGrid"></div>
    </div>

    <!-- ── BILLING ── -->
    <div class="page" id="page-billing">
      <div class="page-header">
        <div>
          <h1 class="page-header__title">Subscription Plans</h1>
          <p class="page-header__sub">Choose the perfect plan for your content needs</p>
        </div>
      </div>
      <div class="plans-grid" id="plansGrid"></div>
      <div class="trust-badges">
        <div class="trust-item"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> Service guarantee</div>
        <div class="trust-item"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Secure checkout</div>
        <div class="trust-item"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg> Cancel anytime</div>
      </div>
    </div>

    <!-- ── SETTINGS ── -->
    <div class="page" id="page-settings">
      <div class="page-header">
        <div>
          <h1 class="page-header__title">Settings</h1>
          <p class="page-header__sub">Manage your account preferences</p>
        </div>
      </div>
      <div class="card settings-card">
     <div class="settings-section">
  <h2 class="settings-title">Profile Information</h2>
  <div class="settings-fields">
    <div class="field">
      <label>Display Name</label>
      <input class="input" type="text" id="settingName" value="<?= htmlspecialchars($user['name']) ?>"/>
    </div>
    <div class="field">
      <label>Email address</label>
      <input class="input" type="email" id="settingEmail" value="<?= htmlspecialchars($user['email']) ?>" disabled/>
    </div>
    <div class="field">
      <label>Current Password</label>
      <input class="input" type="password" id="settingCurrentPwd" placeholder="Required to change password"/>
    </div>
    <div class="field">
      <label>New Password</label>
      <input class="input" type="password" id="settingPwd" placeholder="Leave empty to keep current"/>
    </div>
  </div>
</div>
        <div class="settings-section">
          <h2 class="settings-title">Notifications</h2>
          <div class="settings-fields">
            <div class="toggle-row">
              <div class="toggle-info"><p>Email notifications</p><p>Get notified when new videos are published</p></div>
              <label class="toggle"><input type="checkbox" checked><span class="toggle-track"></span></label>
            </div>
            <div class="toggle-row">
              <div class="toggle-info"><p>Weekly reports</p><p>Receive a weekly summary of your channel performance</p></div>
              <label class="toggle"><input type="checkbox"><span class="toggle-track"></span></label>
            </div>
          </div>
        </div>
        <div class="settings-section">
          <h2 class="settings-title">Preferences</h2>
          <div class="settings-fields">
            <div class="field"><label>Timezone</label>
              <select class="input select" id="settingTz">
                <option value="UTC">UTC</option>
                <option value="America/New_York">Eastern Time</option>
                <option value="America/Los_Angeles">Pacific Time</option>
                <option value="Europe/London">London</option>
                <option value="Europe/Paris">Paris</option>
                <option value="Asia/Tokyo">Tokyo</option>
              </select>
            </div>
            <div class="field"><label>Default Background Music</label>
              <select class="input select" id="settingMusic">
                <option value="none">No music</option>
                <option value="chill">Chill</option>
                <option value="upbeat">Upbeat</option>
                <option value="dramatic">Dramatic</option>
              </select>
            </div>
          </div>
        </div>
        <div class="settings-section danger-zone">
          <h2 class="settings-title danger-title">Danger Zone</h2>
          <div class="settings-fields">
            <div class="toggle-row">
              <div class="toggle-info"><p style="color:var(--red);font-weight:600">Delete account</p><p>Permanently delete your account and all data. Irreversible.</p></div>
              <button class="btn btn-danger" onclick="deleteAccount()">Delete Account</button>
            </div>
          </div>
        </div>
        <div class="settings-save">
          <button class="btn btn-primary" id="saveSettingsBtn" onclick="saveSettings()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Save Changes
          </button>
        </div>
      </div>
    </div>

   
    <!-- Modal vidéo -->
<div id="videoModal" style="display:none;position:fixed;inset:0;z-index:999;background:rgba(0,0,0,.85);backdrop-filter:blur(6px);align-items:center;justify-content:center;" onclick="closeVideoModal()">
  <div style="position:relative;width:90%;max-width:900px;background:#000;border-radius:1.25rem;overflow:hidden;box-shadow:0 32px 80px rgba(0,0,0,.6);" onclick="event.stopPropagation()">
    <button onclick="closeVideoModal()" style="position:absolute;top:.75rem;right:.75rem;z-index:10;background:rgba(255,255,255,.15);border:none;color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:1.25rem;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);">✕</button>
    <video id="modalVideo" controls autoplay style="width:100%;max-height:80vh;display:block;"></video>
    <div style="padding:1rem 1.25rem;background:#111;">
      <p id="modalVideoTitle" style="color:#fff;font-weight:700;font-size:1rem;"></p>
      <p id="modalVideoNiche" style="color:#94a3b8;font-size:.85rem;margin-top:.25rem;"></p>
    </div>
  </div>
</div>
    

  </div><!-- /.page-content -->
</div><!-- /.main-wrap -->



<script>
/* ══════════════════════════════════════
   DATA
══════════════════════════════════════ */
const PLANS_DATA = [
  {id:'hobby',name:'Hobby',base:19,desc:'Best for creators starting with faceless content',min:1,max:3,popular:false,
   features:['Posts 3 times per week','Automated posting to all platforms','Background music library','6+ video art styles','Custom AI voiceover','No watermark','Email support']},
  {id:'daily',name:'Daily',base:39,desc:'Best for creators who want to grow fast',min:1,max:5,popular:true,
   features:['Posts every day','Automated posting to all platforms','Background music library','6+ video art styles','Custom AI voiceover','No watermark','Priority email support','Analytics dashboard']},
  {id:'pro',name:'Pro',base:69,desc:'Best for creators who want to grow super fast',min:1,max:10,popular:false,
   features:['Posts 2 times per day','Automated posting to all platforms','Background music library','6+ video art styles','Custom AI voiceover','No watermark','Priority support','Advanced analytics','API access','Dedicated account manager']},
];
const TUTORIALS_DATA = [
  {title:'Getting Started with VidGenius',desc:'Learn the basics of creating your first series',duration:'5 min',level:'Beginner'},
  {title:'Advanced Caption Styling',desc:'Master different caption styles for maximum engagement',duration:'8 min',level:'Intermediate'},
  {title:'Optimizing Your Posting Schedule',desc:'Find the best times to post for your audience',duration:'6 min',level:'Intermediate'},
  {title:'Multi-Platform Strategy',desc:'How to succeed on YouTube, TikTok, and Instagram',duration:'10 min',level:'Advanced'},
  {title:'AI Voiceover Deep Dive',desc:'Customize voice speed, tone and delivery for your niche',duration:'7 min',level:'Intermediate'},
  {title:'Growing to 100k Subscribers',desc:'Real strategies from top faceless channels',duration:'12 min',level:'Advanced'},
];
const CAPTION_STYLES = ['Bold Stroke','Red Highlight','Sleek','Karaoke','Majestic','Beast','Elegant','Clarity'];
const DAYS = [['mon','monday'],['tue','tuesday'],['wed','wednesday'],['thu','thursday'],['fri','friday'],['sat','saturday'],['sun','sunday']];
const DEFAULT_ACTIVE_DAYS = ['monday','wednesday','friday'];
const VALID_PAGES = ['series','videos','create','tutorials','billing','settings'];

let seriesCounts = {hobby:1,daily:1,pro:1};
let selectedPlan = 'daily';
let wizardStep   = 1;
const TOTAL_STEPS = 7;
let tags = [];

/* ══════════════════════════════════════
   UTILITAIRE — écriture sécurisée
══════════════════════════════════════ */
function setEl(id, val) {
  const el = document.getElementById(id);
  if (el) el.textContent = val;
}

/* ══════════════════════════════════════
   HASH ROUTING
══════════════════════════════════════ */
function applyHash() {
  const raw  = window.location.hash.replace('#','').trim().toLowerCase();
  const page = VALID_PAGES.includes(raw) ? raw : 'series';
  if (!VALID_PAGES.includes(raw)) history.replaceState(null,'','#series');
  _activatePage(page);
}

function navigate(page) {
  if (!VALID_PAGES.includes(page)) page = 'series';
  window.location.hash = page;
  closeSidebar();
}

function _activatePage(page) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const pg  = document.getElementById('page-' + page);
  const nav = document.querySelector(`.nav-item[data-page="${page}"]`);
  if (pg)  pg.classList.add('active');
  if (nav) nav.classList.add('active');
  if (page === 'create') { wizardStep = 1; showStep(1); }
  if (page === 'series') loadSeries();
  if (page === 'videos') loadVideos();
  window.scrollTo({ top:0, behavior:'smooth' });
}

window.addEventListener('hashchange', applyHash);

/* ══════════════════════════════════════
   INIT
══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  buildBilling();
  buildTutorials();
  buildCaptions();
  buildDays();
  loadSeries();
  applyHash();
});

/* ══════════════════════════════════════
   SIDEBAR MOBILE
══════════════════════════════════════ */
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
  document.getElementById('hambIcon').innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>`;
}
document.getElementById('hamburger').addEventListener('click', () => {
  const isOpen = document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('open', isOpen);
  document.getElementById('hambIcon').innerHTML = isOpen
    ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>`
    : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>`;
});
window.addEventListener('resize', () => { if (window.innerWidth >= 768) closeSidebar(); });

/* ══════════════════════════════════════
   PROFILE DROPDOWN
══════════════════════════════════════ */
const profileBtn = document.getElementById('profileBtn');
const profileDD  = document.getElementById('profileDropdown');
const chevron    = document.getElementById('chevron');
profileBtn.addEventListener('click', e => {
  e.stopPropagation();
  const open = profileDD.classList.toggle('open');
  chevron.classList.toggle('open', open);
});
document.addEventListener('click', () => { profileDD.classList.remove('open'); chevron.classList.remove('open'); });
profileDD.addEventListener('click', e => e.stopPropagation());

/* ══════════════════════════════════════
   BILLING
══════════════════════════════════════ */
function buildBilling() {
  const grid = document.getElementById('plansGrid');
  grid.innerHTML = PLANS_DATA.map(p => `
    <div class="card plan-card ${p.popular?'plan-card--popular':''}" id="plan-${p.id}">
      ${p.popular?`<div class="plan-badge-pop">MOST POPULAR</div>`:''}
      <p class="plan-name">${p.name}</p>
      <div class="plan-price">
        <span class="plan-price__amount" id="price-${p.id}">$${p.base}</span>
        <span class="plan-price__period">/month</span>
      </div>
      <p class="plan-desc" id="desc-${p.id}">${p.desc}</p>
      <div class="plan-series-selector">
        <label>Number of Series</label>
        <div class="counter">
          <button class="counter-btn" id="minus-${p.id}" onclick="updateSeries('${p.id}',-1)" ${seriesCounts[p.id]<=p.min?'disabled':''}>
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
          </button>
          <span class="counter-val" id="count-${p.id}">${seriesCounts[p.id]}</span>
          <button class="counter-btn" id="plus-${p.id}" onclick="updateSeries('${p.id}',1)" ${seriesCounts[p.id]>=p.max?'disabled':''}>
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
          </button>
        </div>
        <div class="counter-range"><span>Min: ${p.min}</span><span>Max: ${p.max}</span></div>
      </div>
      <ul class="plan-features">
        ${p.features.map(f=>`<li><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>${f}</li>`).join('')}
      </ul>
      <button class="plan-btn ${p.id===selectedPlan?'plan-btn--selected':''}" id="planBtn-${p.id}" onclick="selectPlan('${p.id}')">
        ${p.id===selectedPlan?'✓ Selected':'Choose '+p.name}
      </button>
    </div>
  `).join('');
}
function calcPrice(planId) {
  const p = PLANS_DATA.find(x=>x.id===planId);
  const c = seriesCounts[planId];
  return c===1 ? p.base : Math.round(p.base + p.base*0.8*(c-1));
}
function updateSeries(planId, delta) {
  const p  = PLANS_DATA.find(x=>x.id===planId);
  const nv = seriesCounts[planId] + delta;
  if (nv < p.min || nv > p.max) return;
  seriesCounts[planId] = nv;
  document.getElementById(`count-${planId}`).textContent = nv;
  document.getElementById(`price-${planId}`).textContent = '$' + calcPrice(planId);
  document.getElementById(`minus-${planId}`).disabled = (nv <= p.min);
  document.getElementById(`plus-${planId}`).disabled  = (nv >= p.max);
  document.getElementById(`desc-${planId}`).textContent = nv > 1
    ? `Base $${p.base} + ${nv-1} additional series at 20% off`
    : p.desc;
}
function selectPlan(planId) {
  selectedPlan = planId;
  PLANS_DATA.forEach(p => {
    const btn = document.getElementById(`planBtn-${p.id}`);
    if (p.id===planId) { btn.className='plan-btn plan-btn--selected'; btn.textContent='✓ Selected'; }
    else { btn.className='plan-btn'; btn.textContent='Choose '+p.name; }
  });
}

/* ══════════════════════════════════════
   TUTORIALS
══════════════════════════════════════ */
function buildTutorials() {
  const levelClass = {Beginner:'badge-green',Intermediate:'badge-yellow',Advanced:'badge-red'};
  document.getElementById('tutorialsGrid').innerHTML = TUTORIALS_DATA.map(t=>`
    <div class="card tutorial-card">
      <div class="tutorial-card__head">
        <span class="badge ${levelClass[t.level]}">${t.level}</span>
        <span class="tutorial-duration">${t.duration}</span>
      </div>
      <h3 class="tutorial-title">${t.title}</h3>
      <p class="tutorial-desc">${t.desc}</p>
      <button class="tutorial-watch">
        Watch Tutorial
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </button>
    </div>
  `).join('');
}

/* ══════════════════════════════════════
   WIZARD
══════════════════════════════════════ */
function buildCaptions() {
  document.getElementById('captionGrid').innerHTML = CAPTION_STYLES.map(s=>{
    const id = s.toLowerCase().replace(' ','-');
    const sel= id==='karaoke';
    return `<div class="caption-opt ${sel?'selected':''}" onclick="selectCaption('${id}')">
      <div class="caption-preview"><span>Preview</span></div>
      <p class="caption-name">${s}</p>
      <div class="caption-check"><svg width="12" height="12" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
    </div>`;
  }).join('');
}
function buildDays() {
  document.getElementById('daysGrid').innerHTML = DAYS.map(([short,full])=>
    `<button type="button" class="day-btn ${DEFAULT_ACTIVE_DAYS.includes(full)?'active':''}" data-day="${full}" onclick="this.classList.toggle('active')">${short}</button>`
  ).join('');
}
function selectCaption(id) {
  document.querySelectorAll('.caption-opt').forEach(el => {
    const match = el.getAttribute('onclick')?.includes(`'${id}'`);
    el.classList.toggle('selected', !!match);
  });
  document.getElementById('captionStyle').value = id;
}
function showStep(n) {
  document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
  document.getElementById(`step-${n}`).classList.add('active');
  const pct = (n / TOTAL_STEPS) * 100;
  document.getElementById('progressFill').style.width = pct + '%';
  document.getElementById('stepCurrent').textContent  = n;
  document.getElementById('btnBack').disabled = (n === 1);
  const btnNext = document.getElementById('btnNext');
  if (n === TOTAL_STEPS) {
    btnNext.innerHTML = `Create Series`;
    btnNext.onclick = submitSeries;
    buildReview();
  } else {
    btnNext.innerHTML = `Continue <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>`;
    btnNext.onclick = wizardNext;
  }
}
function wizardNext() {
  if (wizardStep >= TOTAL_STEPS) return;
  wizardStep++;
  showStep(wizardStep);
  document.querySelector('.wizard-wrap').scrollIntoView({behavior:'smooth',block:'start'});
}
function wizardBack() {
  if (wizardStep <= 1) return;
  wizardStep--;
  showStep(wizardStep);
}
function buildReview() {
  const platforms = ['youtube','tiktok','instagram'].filter(p => document.getElementById('plat'+p.charAt(0).toUpperCase()+p.slice(1)).checked).join(', ') || 'None';
  const days = [...document.querySelectorAll('.day-btn.active')].map(b=>b.dataset.day.slice(0,3)).join(', ') || '—';
  const fields = [
    ['Series Name', document.getElementById('seriesName').value || '—'],
    ['Niche',       document.getElementById('seriesNiche').value || '—'],
    ['Voice',       document.getElementById('voiceSelect').value],
    ['Image Style', document.getElementById('imageStyle').value],
    ['Platforms',   platforms],
    ['Schedule',    `${days} at ${document.getElementById('scheduleTime').value}`],
    ['Caption',     document.getElementById('captionStyle').value],
    ['Tone',        document.getElementById('toneSelect').value],
  ];
  document.getElementById('reviewGrid').innerHTML = fields.map(([l,v])=>
    `<div class="review-item"><label>${l}</label><p>${v}</p></div>`
  ).join('');
}

/* ══════════════════════════════════════
   HASHTAGS
══════════════════════════════════════ */
function addTag() {
  const input = document.getElementById('tagInput');
  const val   = input.value.trim().replace(/^#/,'');
  if (!val || tags.includes(val)) return;
  tags.push(val);
  input.value = '';
  renderTags();
}
function removeTag(tag) { tags = tags.filter(t => t !== tag); renderTags(); }
function renderTags() {
  document.getElementById('tagsList').innerHTML = tags.map(t =>
    `<span class="tag">#${t}<button class="tag-remove" onclick="removeTag('${t}')" type="button">×</button></span>`
  ).join('');
}
document.getElementById('tagInput').addEventListener('keydown', e => {
  if (e.key === 'Enter') { e.preventDefault(); addTag(); }
});

/* ══════════════════════════════════════
   SETTINGS
══════════════════════════════════════ */
async function saveSettings() {
  const btn  = document.getElementById('saveSettingsBtn');
  const name = document.getElementById('settingName').value.trim();
  const pwd  = document.getElementById('settingPwd').value;
  const currEl = document.getElementById('settingCurrentPwd');
  const curr   = currEl ? currEl.value : '';
  if (!name) { alert('Name cannot be empty.'); return; }
  btn.disabled = true;
  btn.innerHTML = `<div class="spinner spinner-white"></div> Saving…`;
  try {
    const body = { name };
    if (pwd) { body.new_password = pwd; body.current_password = curr; }
    const res  = await fetch('/video/app/api/user/update_profile.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify(body), credentials:'include',
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Update failed');
    document.querySelector('.profile-name').textContent = data.name;
    document.querySelector('.avatar').textContent       = data.name.charAt(0).toUpperCase();
    btn.innerHTML = `<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Saved!`;
    document.getElementById('settingPwd').value = '';
  } catch (err) {
    alert(err.message || 'An error occurred.');
    btn.innerHTML = `<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save Changes`;
  }
  setTimeout(() => {
    btn.innerHTML = `<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save Changes`;
    btn.disabled = false;
  }, 2500);
}

async function deleteAccount() {
  if (!confirm('Are you sure? This action is IRREVERSIBLE.')) return;
  const password = prompt('Enter your password to confirm deletion:');
  if (password === null) return;
  try {
    const res  = await fetch('/video/app/api/user/delete_account.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ password }), credentials:'include',
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Deletion failed');
    alert('Your account has been deleted.');
    window.location.href = '/video/index.php';
  } catch (err) { alert(err.message || 'An error occurred.'); }
}

async function logout() {
  await fetch('/video/app/api/auth/logout.php', { method:'POST', credentials:'include' });
  window.location.href = '/video/login.php';
}

/* ══════════════════════════════════════
   SERIES
══════════════════════════════════════ */
async function loadSeries() {
  try {
    const res  = await fetch('/video/app/api/series/list.php', { credentials:'include' });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    setEl('statTotal',  data.stats.total);
    setEl('statActive', data.stats.active);
    setEl('statVideos', data.stats.total_videos);
    if (data.series.length === 0) {
      document.getElementById('seriesEmpty').style.display = 'block';
      document.getElementById('seriesGrid').style.display  = 'none';
    } else {
      document.getElementById('seriesEmpty').style.display = 'none';
      document.getElementById('seriesGrid').style.display  = 'grid';
      renderSeries(data.series);
    }
  } catch (err) { console.error('Error loading series:', err); }
}

function renderSeries(seriesList) {
  const grid = document.getElementById('seriesGrid');
  grid.innerHTML = seriesList.map(s => {
    const statusClass = s.status === 'active' ? 'badge-green' : 'badge-yellow';
    const statusLabel = s.status === 'active' ? 'Active' : 'Paused';
    const platforms   = Array.isArray(s.platforms) ? s.platforms.join(', ') : '—';
    const created     = new Date(s.created_at).toLocaleDateString();
    return `
      <div class="card series-card" id="series-${s.id}">
        <div class="series-card__head">
          <div>
            <p class="series-card__name">${s.name}</p>
            <p class="series-card__niche">${s.niche}</p>
          </div>
          <span class="badge ${statusClass}">${statusLabel}</span>
        </div>
        <div class="series-meta">
          <div class="series-meta-row"><span>Platforms</span><span>${platforms || '—'}</span></div>
          <div class="series-meta-row"><span>Total Posts</span><span>${s.total_posts || 0}</span></div>
          <div class="series-meta-row"><span>Created</span><span>${created}</span></div>
        </div>
        <div class="series-card__footer">
          <span>${s.last_post_at ? 'Last post: ' + new Date(s.last_post_at).toLocaleDateString() : 'No posts yet'}</span>
          <div style="display:flex;gap:.5rem">
            <button class="btn btn-secondary" style="padding:.4rem .875rem;font-size:.78rem" onclick="toggleSeries('${s.id}')">
              ${s.status === 'active' ? 'Pause' : 'Resume'}
            </button>
            <button class="btn btn-danger" style="padding:.4rem .875rem;font-size:.78rem" onclick="deleteSeries('${s.id}', '${s.name}')">
              Delete
            </button>
          </div>
        </div>
      </div>`;
  }).join('');
}

async function toggleSeries(seriesId) {
  try {
    const res  = await fetch('/video/app/api/series/update.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'toggle', series_id:seriesId }), credentials:'include',
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    loadSeries();
  } catch (err) { alert(err.message || 'Error updating series.'); }
}

async function deleteSeries(seriesId, seriesName) {
  if (!confirm(`Delete "${seriesName}"? This cannot be undone.`)) return;
  try {
    const res  = await fetch('/video/app/api/series/update.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'delete', series_id:seriesId }), credentials:'include',
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    loadSeries();
  } catch (err) { alert(err.message || 'Error deleting series.'); }
}

/* ══════════════════════════════════════
   WIZARD SUBMIT
══════════════════════════════════════ */
async function submitSeries() {
  const btn = document.getElementById('btnNext');
  btn.disabled = true;
  btn.innerHTML = `<div class="spinner spinner-white"></div> Creating…`;
  const platforms = [];
  if (document.getElementById('platYoutube').checked)   platforms.push('youtube');
  if (document.getElementById('platTiktok').checked)    platforms.push('tiktok');
  if (document.getElementById('platInstagram').checked) platforms.push('instagram');
  const days = [...document.querySelectorAll('.day-btn.active')].map(b => b.dataset.day);
  const body = {
    name:        document.getElementById('seriesName').value.trim(),
    niche:       document.getElementById('seriesNiche').value,
    description: document.getElementById('seriesDesc').value.trim(),
    platforms,
    content_config: {
      voice:         document.getElementById('voiceSelect').value,
      voice_speed:   document.getElementById('voiceSpeed').value,
      image_style:   document.getElementById('imageStyle').value,
      video_length:  document.getElementById('videoLength').value,
      caption_style: document.getElementById('captionStyle').value,
    },
    content_rules: {
      hashtags:       tags,
      call_to_action: document.getElementById('callToAction').value,
      tone:           document.getElementById('toneSelect').value,
    },
    schedule_config: {
      days,
      time:     document.getElementById('scheduleTime').value,
      timezone: document.getElementById('timezoneSelect').value,
    },
  };
  if (!body.name)  { alert('Series name is required.'); btn.disabled=false; btn.innerHTML='Create Series'; return; }
  if (!body.niche) { alert('Please select a niche.');   btn.disabled=false; btn.innerHTML='Create Series'; return; }
  try {
    const res  = await fetch('/video/app/api/series/create.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify(body), credentials:'include',
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Failed to create series');
    wizardStep = 1; showStep(1);
    document.getElementById('seriesName').value = '';
    document.getElementById('seriesDesc').value = '';
    tags = []; renderTags();
    navigate('series');
    loadSeries();
  } catch (err) { alert(err.message || 'An error occurred.'); }
  btn.disabled = false;
  btn.innerHTML = 'Create Series';
}

/* ══════════════════════════════════════
   VIDEOS
══════════════════════════════════════ */
let allVideos          = [];
let activeStatusFilter = 'all';
let currentVideoId     = null;

function getPortraitThumb(url) {
  if (!url) return null;
  if (url.includes('picsum.photos')) return url.replace(/\/(\d+)\/(\d+)$/, '/400/711');
  return url;
}

async function loadVideos() {
  try {
    const res  = await fetch('/video/app/api/videos/list.php', { credentials:'include' });
    const data = await res.json();
    if (!data.success) return;
    allVideos = data.videos;
    const seriesSet = [...new Set(allVideos.map(v => v.series_name).filter(Boolean))];
    const sel = document.getElementById('filterSeries');
    if (sel) {
      sel.innerHTML = '<option value="all">All Series</option>' +
        seriesSet.map(s => `<option value="${s}">${s}</option>`).join('');
    }
    filterVideos();
  } catch(err) { console.error(err); }
}

function setVideoFilter(value, btn) {
  activeStatusFilter = value;
  document.querySelectorAll('.vf-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  filterVideos();
}

function filterVideos() {
  const sel  = document.getElementById('filterSeries');
  const sort = document.getElementById('sortVideos');
  if (!sel || !sort) return;

  const seriesFilter = sel.value;
  const sortVal      = sort.value;

  let filtered = allVideos.filter(v => {
    const ms  = activeStatusFilter === 'all' || v.status === activeStatusFilter;
    const mse = seriesFilter === 'all' || v.series_name === seriesFilter;
    return ms && mse;
  });

  if (sortVal === 'newest')   filtered.sort((a,b) => new Date(b.created_at) - new Date(a.created_at));
  if (sortVal === 'oldest')   filtered.sort((a,b) => new Date(a.created_at) - new Date(b.created_at));
  if (sortVal === 'duration') filtered.sort((a,b) => (b.duration_seconds||0) - (a.duration_seconds||0));

  renderVideos(filtered);
}

function renderVideos(videos) {
  const grid  = document.getElementById('videosGrid');
  const empty = document.getElementById('videosEmpty');
  const count = document.getElementById('videoCount');
  if (!grid) return;

  if (count) count.textContent = `${videos.length} video${videos.length !== 1 ? 's' : ''}`;

  if (videos.length === 0) {
    grid.style.display  = 'none';
    if (empty) empty.style.display = 'block';
    return;
  }
  if (empty) empty.style.display = 'none';
  grid.style.display = 'grid';

  const fmtDur = s => s ? `${Math.floor(s/60)}:${String(s%60).padStart(2,'0')}` : null;
  const statusDot   = { completed:'#16a34a', processing:'#7c3aed', pending:'#ca8a04', failed:'#dc2626' };
  const statusBadge = { completed:'badge-green', processing:'badge-purple', pending:'badge-yellow', failed:'badge-red' };

  grid.innerHTML = videos.map(v => {
    const dur   = fmtDur(v.duration_seconds);
    const dot   = statusDot[v.status] || '#94a3b8';
    const thumb = getPortraitThumb(v.thumbnail_url);
    return `
      <div class="card vcard" onclick="openVideoDetail('${v.id}')">
        <div class="vcard-thumb">
          ${thumb
            ? `<img src="${thumb}" alt="" loading="lazy" onerror="this.style.display='none'"
                style="width:100%;height:100%;object-fit:cover;object-position:center top;display:block"/>`
            : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                <svg width="32" height="32" fill="none" stroke="rgba(148,163,184,.4)" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
               </div>`}
          <div class="vcard-overlay">
            <div class="vcard-top">
              <span class="vcard-status-dot" style="background:${dot}"></span>
            </div>
            <div class="vcard-bottom">
              <span class="badge ${statusBadge[v.status]||'badge-gray'}" style="font-size:.65rem">${v.status}</span>
              ${dur ? `<span class="vcard-duration">${dur}</span>` : '<span></span>'}
            </div>
          </div>
          ${v.status === 'completed' && v.video_url ? `
            <div class="vcard-play">
              <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            </div>` : ''}
        </div>
        <div class="vcard-body">
          <p class="vcard-series">${v.series_name || 'Unknown'}</p>
          <p class="vcard-meta">${v.niche ? v.niche+' · ' : ''}${new Date(v.created_at).toLocaleDateString()}</p>
        </div>
      </div>`;
  }).join('');
}

function openVideoDetail(videoId) {
  const v = allVideos.find(x => x.id === videoId);
  if (!v) return;
  currentVideoId = videoId;

  const fmtDur = s => s ? `${Math.floor(s/60)}:${String(s%60).padStart(2,'0')}` : '—';
  const statusClass = { completed:'badge-green', processing:'badge-purple', pending:'badge-yellow', failed:'badge-red' };

  setEl('detailBreadcrumb', v.series_name || 'Video');
  setEl('detailDate',       new Date(v.created_at).toLocaleDateString('en-US',{day:'numeric',month:'short',year:'numeric'}));
  setEl('detailDuration',   fmtDur(v.duration_seconds));
  setEl('statViews',        v.views  ? v.views.toLocaleString()  : '—');
  setEl('statLikes',        v.likes  ? v.likes.toLocaleString()  : '—');
  setEl('statShares',       v.shares ? v.shares.toLocaleString() : '—');

  const badge = document.getElementById('detailBadge');
  if (badge) { badge.className = `badge ${statusClass[v.status]||'badge-gray'}`; badge.textContent = v.status; }

  const videoEl     = document.getElementById('detailVideo');
  const placeholder = document.getElementById('detailPlaceholder');
  const caption     = document.getElementById('detailPhoneCaption');

  if (v.video_url) {
    if (videoEl)     { videoEl.src = v.video_url; videoEl.style.display = 'block'; }
    if (placeholder)   placeholder.style.display = 'none';
    if (caption)       caption.style.display = 'block';
    setEl('detailCaptionWord',  (v.niche || 'VIDEO').toUpperCase().slice(0,8));
    setEl('detailPhoneSeries',  (v.series_name || '') + (v.episode ? ' · Episode ' + v.episode : ''));
    const notice = document.getElementById('detailNotice');
    if (notice) notice.style.display = 'block';
  } else {
    if (videoEl)     { videoEl.src = ''; videoEl.style.display = 'none'; }
    if (placeholder)   placeholder.style.display = 'flex';
    setEl('placeholderLabel', v.status === 'processing' ? 'Rendering…' : 'No preview yet');
    if (caption)       caption.style.display = 'none';
    const notice = document.getElementById('detailNotice');
    if (notice) notice.style.display = 'none';
  }

  const dl   = document.getElementById('detailDownloadLink');
  const open = document.getElementById('detailOpenLink');
  if (v.video_url) {
    if (dl)   { dl.href   = v.video_url; dl.style.display   = 'block'; }
    if (open) { open.href = v.video_url; open.style.display = 'block'; }
  } else {
    if (dl)   dl.style.display   = 'none';
    if (open) open.style.display = 'none';
  }

  const titleEl = document.getElementById('vdTitle');
  const descEl  = document.getElementById('vdDesc');
  if (titleEl) { titleEl.value = v.title || v.series_name || ''; updateVdCount('vdTitle','vdTitleCount',100); }
  if (descEl)  { descEl.value  = v.description || '';             updateVdCount('vdDesc','vdDescCount',2200); }

  const sn = document.getElementById('vdSeriesName');
  if (sn) sn.value = v.series_name || '—';

  const sd = document.getElementById('vdSchedDate');
  const st = document.getElementById('vdSchedTime');
  const sp = document.getElementById('vdPublished');
  if (sd) sd.value = new Date().toISOString().split('T')[0];
  if (st) st.value = '14:30';
  if (sp) sp.checked = false;
  setEl('vdPublishedLabel', 'Has this video already been published?');

  const errEl = document.getElementById('detailError');
  if (errEl) {
    if (v.error_message) { errEl.textContent = '⚠ ' + v.error_message; errEl.style.display = 'block'; }
    else { errEl.style.display = 'none'; }
  }

  const saveBtn = document.getElementById('vdSaveBtn');
  if (saveBtn) {
    saveBtn.classList.remove('saved');
    saveBtn.innerHTML = `<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
      All changes saved`;
  }

  const listView   = document.getElementById('videosListView');
  const detailView = document.getElementById('videosDetailView');
  if (listView)   listView.style.display   = 'none';
  if (detailView) detailView.style.display = 'block';
  window.scrollTo({ top:0, behavior:'smooth' });
}

function closeVideoDetail() {
  const video = document.getElementById('detailVideo');
  if (video) { video.pause(); video.src = ''; }
  const listView   = document.getElementById('videosListView');
  const detailView = document.getElementById('videosDetailView');
  if (detailView) detailView.style.display = 'none';
  if (listView)   listView.style.display   = 'block';
  currentVideoId = null;
}

async function deleteCurrentVideo() {
  if (!currentVideoId) return;
  if (!confirm('Delete this video? This cannot be undone.')) return;
  try {
    const res  = await fetch('/video/app/api/videos/delete.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ video_id: currentVideoId }), credentials:'include',
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    closeVideoDetail();
    loadVideos();
    loadSeries();
  } catch(err) { alert(err.message || 'Error deleting video.'); }
}

async function deleteVideo(videoId) {
  if (!confirm('Delete this video? This cannot be undone.')) return;
  try {
    const res  = await fetch('/video/app/api/videos/delete.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ video_id: videoId }), credentials:'include',
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    loadVideos(); loadSeries();
  } catch(err) { alert(err.message || 'Error deleting video.'); }
}

function updateVdCount(fieldId, countId, max) {
  const el  = document.getElementById(fieldId);
  const cel = document.getElementById(countId);
  if (!el || !cel) return;
  const len = el.value.length;
  cel.textContent = len;
  cel.style.color = len > max * 0.9 ? 'var(--red)' : 'var(--txt-3)';
}

function togglePublished() {
  const c = document.getElementById('vdPublished');
  setEl('vdPublishedLabel', c && c.checked ? 'Video already published ✓' : 'Has this video already been published?');
}

async function saveVideoDetail() {
  const btn = document.getElementById('vdSaveBtn');
  if (!btn) return;
  btn.disabled = true;
  btn.innerHTML = `<div class="spinner spinner-white"></div> Saving…`;
  await new Promise(r => setTimeout(r, 900));
  btn.classList.add('saved');
  btn.innerHTML = `<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Saved!`;
  btn.disabled = false;
  setTimeout(() => {
    btn.classList.remove('saved');
    btn.innerHTML = `<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> All changes saved`;
  }, 2500);
}

/* ══════════════════════════════════════
   MODAL VIDEO + ESCAPE
══════════════════════════════════════ */
function openVideoModal(el) {
  const modal = document.getElementById('videoModal');
  if (!modal) return;
  document.getElementById('modalVideo').src              = el.dataset.url;
  document.getElementById('modalVideoTitle').textContent = el.dataset.title;
  document.getElementById('modalVideoNiche').textContent = el.dataset.niche;
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeVideoModal() {
  const modal = document.getElementById('videoModal');
  const video = document.getElementById('modalVideo');
  if (video) { video.pause(); video.src = ''; }
  if (modal) modal.style.display = 'none';
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeVideoModal();
    if (currentVideoId) closeVideoDetail();
  }
});
</script>

<script>
window.addEventListener('load', function() {
    let link = document.createElement('link');
    link.href = "https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600&display=swap";
    link.rel = "stylesheet";
    document.head.appendChild(link);

    link = document.createElement('link');
    link.href = "https://fonts.googleapis.com";
    link.rel = "preconnect";
    document.head.appendChild(link);

     link = document.createElement('link');
    link.href = "https://fonts.gstatic.com";
    link.rel = "preconnect";
    document.head.appendChild(link);

    
});
</script>


</body>
</html>