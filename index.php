<?php
// index.php — VidGenius Landing Page





$page_title = "VidGenius — Turn ideas into viral faceless videos";
$stats = [
    ["value" => "297k+", "label" => "channels automated"],
    ["value" => "1.32M+", "label" => "videos autoposted"],
    ["value" => "4.9 ★★★★★", "label" => "creator rating"],
];
$showcases = [
    ["src" => "showcase_4", "label" => "Real Tragedies"],
    ["src" => "showcase_7", "label" => "Scary Stories"],
    ["src" => "showcase_9", "label" => "History"],
    ["src" => "showcase_11", "label" => "True Crime"],
    ["src" => "showcase_10", "label" => "Anime stories"],
    ["src" => "showcase_6", "label" => "Heists"],
];
$steps = [
    [
        "step" => "①",
        "title" => "Create a series",
        "desc" => "Choose your niche and format – AI builds the whole episode pipeline.",
        "icon" => "M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
    ],
    [
        "step" => "②",
        "title" => "Customize",
        "desc" => "Pick art direction, upload your music or paste a TikTok sound link.",
        "icon" => "M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
    ],
    [
        "step" => "③",
        "title" => "Auto-post & grow",
        "desc" => "Connect IG, TikTok or YouTube – we publish daily while you dream.",
        "icon" => "M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"
    ],
];
$faqs = [
    ["q" => "What is a series?", "a" => "A collection of videos with consistent style, niche, and posting schedule – fully managed by AI."],
    ["q" => "Is it safe to connect TikTok/IG?", "a" => "Absolutely. We use official APIs and never store passwords. You stay in full control."],
    ["q" => "How many videos per month?", "a" => "Our plans start at 30 videos/month – enough for daily posting across all platforms."],
    ["q" => "Can I get views guaranteed?", "a" => "We optimize for what's trending, but real reach depends on your niche and audience. Creators see consistent growth."],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($page_title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <style>
    /* ─────────────────────────────────────────
       CSS VARIABLES & RESET
    ───────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg: #f7f8fc;
      --surface: #ffffff;
      --border: #e4e7f0;
      --blue: #2563eb;
      --blue-dark: #1d4ed8;
      --blue-light: #eff6ff;
      --indigo: #4f46e5;
      --text-primary: #0f172a;
      --text-secondary: #475569;
      --text-muted: #94a3b8;
      --shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
      --shadow-md: 0 4px 16px rgba(0,0,0,.08), 0 2px 6px rgba(0,0,0,.04);
      --shadow-lg: 0 12px 40px rgba(37,99,235,.12), 0 4px 12px rgba(0,0,0,.06);
      --shadow-xl: 0 24px 60px rgba(37,99,235,.15), 0 8px 24px rgba(0,0,0,.08);
      --radius: 1rem;
      --radius-lg: 1.5rem;
      --radius-full: 9999px;
      --font-display: 'Clash Display', sans-serif;
      --font-body: 'Sora', sans-serif;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: var(--font-body);
      background: var(--bg);
      color: var(--text-primary);
      line-height: 1.6;
      min-height: 100vh;
    }

    a { color: inherit; text-decoration: none; }
    ul { list-style: none; }
    img, video { display: block; max-width: 100%; }

    /* ─────────────────────────────────────────
       UTILITY
    ───────────────────────────────────────── */
    .container {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1.5rem;
    }

    .gradient-text {
      background: linear-gradient(135deg, var(--blue) 0%, var(--indigo) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .btn-primary {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      padding: .875rem 2rem;
      background: linear-gradient(135deg, var(--blue) 0%, var(--indigo) 100%);
      color: #fff;
      border-radius: var(--radius-full);
      font-family: var(--font-body);
      font-weight: 600;
      font-size: 1rem;
      border: none;
      cursor: pointer;
      transition: transform .2s, box-shadow .2s;
      box-shadow: var(--shadow-lg);
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-xl);
    }

    /* ─────────────────────────────────────────
       HEADER
    ───────────────────────────────────────── */
    .header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 100;
      background: rgba(255,255,255,.82);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--border);
      transition: box-shadow .3s;
    }
    .header.scrolled { box-shadow: var(--shadow-md); }

    .header__inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 68px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: .5rem;
      font-family: var(--font-display);
      font-size: 1.25rem;
      font-weight: 700;
      letter-spacing: -.02em;
    }
    .logo svg { color: var(--blue); }

    .nav {
      display: flex;
      align-items: center;
      gap: 2rem;
    }
    .nav a {
      font-size: .9375rem;
      color: var(--text-secondary);
      font-weight: 500;
      transition: color .2s;
    }
    .nav a:hover { color: var(--text-primary); }

    .header__auth {
      display: flex;
      align-items: center;
      gap: .75rem;
    }
    .btn-ghost {
      padding: .5rem 1.25rem;
      color: var(--text-secondary);
      font-weight: 500;
      border-radius: var(--radius-full);
      transition: color .2s, background .2s;
      font-family: var(--font-body);
      font-size: .9375rem;
    }
    .btn-ghost:hover { color: var(--text-primary); background: var(--blue-light); }

    .btn-signup {
      padding: .5rem 1.375rem;
      background: linear-gradient(135deg, var(--blue), var(--indigo));
      color: #fff;
      border-radius: var(--radius-full);
      font-weight: 600;
      font-size: .9375rem;
      box-shadow: 0 2px 10px rgba(37,99,235,.3);
      transition: transform .2s, box-shadow .2s;
    }
    .btn-signup:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(37,99,235,.4); }

    /* ─────────────────────────────────────────
       HERO
    ───────────────────────────────────────── */
    main { padding-top: 68px; }

    .hero {
      padding: 5rem 0 4rem;
      position: relative;
      overflow: hidden;
    }
    .hero::before {
      content: '';
      position: absolute;
      top: -200px;
      right: -200px;
      width: 700px;
      height: 700px;
      background: radial-gradient(circle, rgba(99,102,241,.07) 0%, transparent 70%);
      pointer-events: none;
    }

    .hero__grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 4rem;
      align-items: center;
    }

    .hero__badge {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      background: var(--blue-light);
      color: var(--blue);
      padding: .375rem 1rem;
      border-radius: var(--radius-full);
      font-size: .875rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      border: 1px solid rgba(37,99,235,.15);
    }
    .hero__badge-dot {
      width: 8px;
      height: 8px;
      background: var(--blue);
      border-radius: 50%;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: .5; transform: scale(.8); }
    }

    .hero__title {
      font-family: var(--font-display);
      font-size: clamp(2.5rem, 5vw, 3.75rem);
      font-weight: 700;
      line-height: 1.1;
      letter-spacing: -.03em;
      margin-bottom: 1.5rem;
    }

    .hero__subtitle {
      font-size: 1.125rem;
      color: var(--text-secondary);
      max-width: 500px;
      margin-bottom: 2rem;
      line-height: 1.7;
    }

    .hero__cta-row {
      display: flex;
      align-items: center;
      gap: 1.25rem;
      flex-wrap: wrap;
      margin-bottom: 2.5rem;
    }

    .hero__check {
      display: flex;
      align-items: center;
      gap: .375rem;
      font-size: .9rem;
      color: var(--text-muted);
    }
    .hero__check svg { color: #22c55e; }

    .hero__platforms {
      display: flex;
      align-items: center;
      gap: 1.25rem;
    }
    .hero__platforms-label {
      font-size: .875rem;
      font-weight: 600;
      color: var(--text-muted);
    }
    .hero__platforms svg { opacity: .55; transition: opacity .2s; }
    .hero__platforms svg:hover { opacity: 1; }

    /* Video card */
    .hero__video-wrap {
      position: relative;
    }
    .hero__video-card {
      background: var(--surface);
      padding: .75rem;
      border-radius: 1.75rem;
      box-shadow: var(--shadow-xl);
      border: 1px solid var(--border);
    }
    .hero__video-card video {
      width: 100%;
      aspect-ratio: 16/9;
      object-fit: cover;
      border-radius: 1.25rem;
      background: #e2e8f0;
    }
    .hero__blob {
      position: absolute;
      border-radius: 50%;
      filter: blur(60px);
      pointer-events: none;
      z-index: -1;
    }
    .hero__blob--1 {
      width: 200px; height: 200px;
      background: rgba(99,102,241,.15);
      bottom: -40px; left: -40px;
    }
    .hero__blob--2 {
      width: 150px; height: 150px;
      background: rgba(37,99,235,.1);
      top: -30px; right: -20px;
    }

    /* ─────────────────────────────────────────
       STATS
    ───────────────────────────────────────── */
    .stats { padding: 2rem 0 4rem; }

    .stats__card {
      background: var(--surface);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-md);
      padding: 2.5rem;
      border: 1px solid var(--border);
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 3rem;
      flex-wrap: wrap;
    }

    .stats__item { text-align: center; }
    .stats__value {
      font-family: var(--font-display);
      font-size: 2rem;
      font-weight: 700;
      color: var(--blue);
      letter-spacing: -.02em;
    }
    .stats__label { font-size: .875rem; color: var(--text-muted); margin-top: .25rem; }

    .stats__divider {
      width: 1px;
      height: 3rem;
      background: var(--border);
    }

    /* ─────────────────────────────────────────
       SHOWCASE
    ───────────────────────────────────────── */
    .showcase { padding: 4rem 0; }

    .section-title {
      font-family: var(--font-display);
      font-size: clamp(1.75rem, 3.5vw, 2.5rem);
      font-weight: 700;
      text-align: center;
      letter-spacing: -.025em;
      margin-bottom: .75rem;
    }
    .section-sub {
      text-align: center;
      color: var(--text-secondary);
      max-width: 560px;
      margin: 0 auto 2.5rem;
      font-size: 1.0625rem;
    }

    .showcase__scroll {
      display: flex;
      gap: 1rem;
      overflow-x: auto;
      padding-bottom: 1rem;
      scrollbar-width: thin;
      scrollbar-color: var(--border) transparent;
    }
    .showcase__scroll::-webkit-scrollbar { height: 6px; }
    .showcase__scroll::-webkit-scrollbar-track { background: transparent; }
    .showcase__scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

    .showcase__card {
      flex-shrink: 0;
      width: 180px;
      background: var(--surface);
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
      border: 1px solid var(--border);
      transition: transform .2s, box-shadow .2s;
    }
    .showcase__card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
    .showcase__card video {
      width: 100%;
      aspect-ratio: 9/16;
      object-fit: cover;
      background: #e2e8f0;
    }
    .showcase__card-label {
      padding: .75rem;
      font-size: .875rem;
      font-weight: 600;
      text-align: center;
      color: var(--text-primary);
    }

    /* ─────────────────────────────────────────
       HOW IT WORKS
    ───────────────────────────────────────── */
    .how { padding: 4rem 0; }

    .how__grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
    }

    .how__card {
      background: var(--surface);
      border-radius: var(--radius-lg);
      padding: 2.25rem;
      box-shadow: var(--shadow-md);
      border: 1px solid var(--border);
      transition: transform .25s, box-shadow .25s;
      position: relative;
      overflow: hidden;
    }
    .how__card::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(37,99,235,.04) 0%, transparent 60%);
      pointer-events: none;
    }
    .how__card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); }

    .how__step {
      display: inline-flex;
      align-items: center;
      padding: .25rem .875rem;
      background: var(--blue-light);
      color: var(--blue);
      border-radius: var(--radius-full);
      font-size: .875rem;
      font-weight: 700;
      margin-bottom: 1.25rem;
    }
    .how__icon {
      width: 48px;
      height: 48px;
      background: linear-gradient(135deg, var(--blue-light), rgba(99,102,241,.1));
      border-radius: .875rem;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1.25rem;
    }
    .how__icon svg { color: var(--blue); }
    .how__title {
      font-family: var(--font-display);
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: .625rem;
    }
    .how__desc { color: var(--text-secondary); font-size: .9375rem; line-height: 1.65; }

    /* ─────────────────────────────────────────
       FAQ
    ───────────────────────────────────────── */
    .faq { padding: 4rem 0; }

    .faq__list {
      max-width: 720px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: .875rem;
    }

    .faq__item {
      background: var(--surface);
      border-radius: var(--radius);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }
    .faq__summary {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.375rem 1.5rem;
      cursor: pointer;
      list-style: none;
      font-weight: 600;
      font-size: 1rem;
      transition: background .2s;
    }
    .faq__summary::-webkit-details-marker { display: none; }
    .faq__summary:hover { background: rgba(37,99,235,.03); }
    .faq__item[open] .faq__summary { color: var(--blue); }

    .faq__chevron {
      flex-shrink: 0;
      transition: transform .3s;
      color: var(--text-muted);
    }
    .faq__item[open] .faq__chevron { transform: rotate(180deg); color: var(--blue); }

    .faq__body {
      padding: 0 1.5rem 1.375rem;
      color: var(--text-secondary);
      font-size: .9375rem;
      line-height: 1.7;
    }

    /* ─────────────────────────────────────────
       CTA SECTION
    ───────────────────────────────────────── */
    .cta-section { padding: 4rem 0 5rem; }

    .cta-card {
      background: linear-gradient(135deg, #eff6ff 0%, #fff 50%, #eef2ff 100%);
      border-radius: 2rem;
      padding: 4rem 2rem;
      text-align: center;
      box-shadow: var(--shadow-xl);
      border: 1px solid rgba(37,99,235,.12);
      position: relative;
      overflow: hidden;
    }
    .cta-card::before {
      content: '';
      position: absolute;
      top: -100px; left: 50%;
      transform: translateX(-50%);
      width: 500px; height: 300px;
      background: radial-gradient(ellipse, rgba(99,102,241,.08) 0%, transparent 70%);
    }
    .cta-card__title {
      font-family: var(--font-display);
      font-size: clamp(1.75rem, 3.5vw, 2.5rem);
      font-weight: 700;
      letter-spacing: -.025em;
      margin-bottom: 1rem;
    }
    .cta-card__sub {
      color: var(--text-secondary);
      max-width: 520px;
      margin: 0 auto 2rem;
      font-size: 1.0625rem;
    }

    /* ─────────────────────────────────────────
       FOOTER
    ───────────────────────────────────────── */
    .footer {
      background: var(--surface);
      border-top: 1px solid var(--border);
      padding: 3.5rem 0 2.5rem;
    }

    .footer__grid {
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr 1fr;
      gap: 2.5rem;
      margin-bottom: 2.5rem;
    }

    .footer__brand p {
      font-size: .875rem;
      color: var(--text-muted);
      margin-top: .5rem;
      line-height: 1.6;
    }

    .footer__col h3 {
      font-family: var(--font-display);
      font-weight: 700;
      font-size: .9375rem;
      margin-bottom: 1rem;
      letter-spacing: -.01em;
    }
    .footer__col ul li + li { margin-top: .625rem; }
    .footer__col ul a {
      font-size: .9rem;
      color: var(--text-secondary);
      transition: color .2s;
    }
    .footer__col ul a:hover { color: var(--blue); }

    .footer__bottom {
      border-top: 1px solid var(--border);
      padding-top: 1.5rem;
      text-align: center;
      font-size: .875rem;
      color: var(--text-muted);
    }

    /* ─────────────────────────────────────────
       ANIMATIONS
    ───────────────────────────────────────── */
    .fade-up {
      opacity: 0;
      transform: translateY(28px);
      transition: opacity .6s ease, transform .6s ease;
    }
    .fade-up.visible {
      opacity: 1;
      transform: translateY(0);
    }

    /* ─────────────────────────────────────────
       RESPONSIVE
    ───────────────────────────────────────── */
    @media (max-width: 1024px) {
      .how__grid { grid-template-columns: 1fr; }
      .footer__grid { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 768px) {
      .hero__grid { grid-template-columns: 1fr; }
      .hero__video-wrap { order: -1; }
      .nav { display: none; }
      .stats__divider { display: none; }
      .footer__grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 480px) {
      .hero__title { font-size: 2.125rem; }
      .btn-primary { padding: .75rem 1.5rem; font-size: .9375rem; }
    }
  </style>
</head>
<body>

<!-- ══════════ HEADER ══════════ -->
<header class="header" id="siteHeader">
  <div class="container">
    <div class="header__inner">

      <!-- Logo -->
      <a href="/" class="logo">
        <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
        </svg>
        VidGenius
      </a>

      <!-- Nav -->
      <nav class="nav">
        <a href="#how">How it works</a>
        <a href="#faq">FAQ</a>
      </nav>

      <!-- Auth -->
      <div class="header__auth">
        <a href="login.php" class="btn-ghost">Log in</a>
        <a href="signup.php" class="btn-signup">Sign up</a>
      </div>

    </div>
  </div>
</header>

<!-- ══════════ MAIN ══════════ -->
<main>

  <!-- ── HERO ── -->
  <section class="hero">
    <div class="container">
      <div class="hero__grid">

        <!-- Left: copy -->
        <div>
          <div class="hero__badge">
            <span class="hero__badge-dot"></span>
            trusted by 430k+ creators
          </div>

          <h1 class="hero__title">
            Turn ideas into<br>
            <span class="gradient-text">viral faceless videos</span><br>
            on autopilot
          </h1>

          <p class="hero__subtitle">
            The only AI that generates, edits &amp; posts reels while you sleep.
            Perfect for TikTok, Reels &amp; Shorts.
          </p>

          <div class="hero__cta-row">
            <a href="signup.php" class="btn-primary">
              <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              Create your first video
            </a>
            <span class="hero__check">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
              less than 5 min
            </span>
          </div>

          <div class="hero__platforms">
            <span class="hero__platforms-label">Perfect for</span>
            <!-- TikTok -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
              <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 1.48.4V8.23a6.39 6.39 0 0 0-1.48-.2 6.34 6.34 0 0 0-6.04 8.39 6.34 6.34 0 0 0 11.4 2.44 6.34 6.34 0 0 0 1.09-3.54V8.72a8.16 8.16 0 0 0 4.77 1.52v-3.5a4.83 4.83 0 0 1-1.19-.05z"/>
            </svg>
            <!-- Instagram -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0 2.163c-3.259 0-3.667.014-4.947.072-2.404.107-3.636 1.202-3.743 3.743-.058 1.28-.072 1.688-.072 4.947 0 3.259.014 3.668.072 4.947.107 2.402 1.202 3.636 3.743 3.743 1.28.058 1.688.072 4.947.072 3.259 0 3.668-.014 4.947-.072 2.404-.107 3.636-1.202 3.743-3.743.058-1.28.072-1.688.072-4.947 0-3.259-.014-3.667-.072-4.947-.107-2.402-1.202-3.636-3.743-3.743-1.28-.058-1.688-.072-4.947-.072zm0 3.282a5.4 5.4 0 1 0 0 10.8 5.4 5.4 0 0 0 0-10.8zm0 8.637a3.237 3.237 0 1 1 0-6.474 3.237 3.237 0 0 1 0 6.474z"/>
            </svg>
            <!-- YouTube -->
            <svg width="26" height="26" viewBox="0 0 24 24" fill="#ef4444">
              <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
          </div>
        </div>

        <!-- Right: video card -->
        <div class="hero__video-wrap">
          <div class="hero__video-card">
            <video
              src="https://cdn.facelessreels.com/how-it-works-1.mp4"
              autoplay loop muted playsinline
              poster="https://i.ytimg.com/vi_webp/IlZO4s3ITZo/maxresdefault.webp">
            </video>
          </div>
          <div class="hero__blob hero__blob--1"></div>
          <div class="hero__blob hero__blob--2"></div>
        </div>

      </div>
    </div>
  </section>

  <!-- ── STATS ── -->
  <section class="stats">
    <div class="container">
      <div class="stats__card fade-up">
        <?php foreach ($stats as $i => $stat): ?>
          <?php if ($i > 0): ?>
            <div class="stats__divider"></div>
          <?php endif; ?>
          <div class="stats__item">
            <div class="stats__value"><?= htmlspecialchars($stat['value']) ?></div>
            <div class="stats__label"><?= htmlspecialchars($stat['label']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── SHOWCASE ── -->
  <section class="showcase">
    <div class="container">
      <h2 class="section-title fade-up">
        Any niche. <span class="gradient-text">Real results.</span>
      </h2>
      <p class="section-sub fade-up">
        History, true crime, scary stories, anime – our AI does it all.
      </p>
      <div class="showcase__scroll">
        <?php foreach ($showcases as $item): ?>
          <div class="showcase__card">
            <video
              src="https://cdn.facelessreels.com/showcase-new/<?= htmlspecialchars($item['src']) ?>.mp4"
              muted loop playsinline
              loading="lazy">
            </video>
            <div class="showcase__card-label"><?= htmlspecialchars($item['label']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── HOW IT WORKS ── -->
  <section class="how" id="how">
    <div class="container">
      <h2 class="section-title fade-up">
        Three moves to <span class="gradient-text">go viral</span>
      </h2>
      <p class="section-sub fade-up">
        You just pick the niche, we handle scripting, visuals, sound &amp; posting.
      </p>
      <div class="how__grid">
        <?php foreach ($steps as $step): ?>
          <div class="how__card fade-up">
            <div class="how__step"><?= htmlspecialchars($step['step']) ?></div>
            <div class="how__icon">
              <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= htmlspecialchars($step['icon']) ?>"/>
              </svg>
            </div>
            <h3 class="how__title"><?= htmlspecialchars($step['title']) ?></h3>
            <p class="how__desc"><?= htmlspecialchars($step['desc']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── FAQ ── -->
  <section class="faq" id="faq">
    <div class="container">
      <h2 class="section-title fade-up">FAQs</h2>
      <div class="faq__list">
        <?php foreach ($faqs as $faq): ?>
          <details class="faq__item fade-up">
            <summary class="faq__summary">
              <?= htmlspecialchars($faq['q']) ?>
              <svg class="faq__chevron" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </summary>
            <div class="faq__body"><?= htmlspecialchars($faq['a']) ?></div>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── FINAL CTA ── -->
  <section class="cta-section">
    <div class="container">
      <div class="cta-card fade-up">
        <h2 class="cta-card__title">
          Your first video <span class="gradient-text">in &lt;5 min</span>
        </h2>
        <p class="cta-card__sub">
          Join 430k+ creators who stopped showing their face – and started growing.
        </p>
        <a href="signup.php" class="btn-primary" style="font-size:1.0625rem; padding:1rem 2.25rem;">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
          Create free video
        </a>
      </div>
    </div>
  </section>

</main>

<!-- ══════════ FOOTER ══════════ -->
<footer class="footer">
  <div class="container">
    <div class="footer__grid">

      <div class="footer__brand">
        <a href="/" class="logo" style="margin-bottom:.5rem; display:inline-flex;">
          <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
          </svg>
          VidGenius
        </a>
        <p>© <?= date('Y') ?> VidGenius.<br>Made for the creator economy.</p>
      </div>

      <div class="footer__col">
        <h3>Product</h3>
        <ul>
          <li><a href="#how">How it works</a></li>
          <li><a href="#faq">FAQ</a></li>
        </ul>
      </div>

      <div class="footer__col">
        <h3>Connect</h3>
        <ul>
          <li><a href="/contact.php">Contact</a></li>
          <li><a href="#">Discord</a></li>
        </ul>
      </div>

      <div class="footer__col">
        <h3>Legal</h3>
        <ul>
          <li><a href="/tos.php">Terms</a></li>
          <li><a href="/privacy.php">Privacy</a></li>
        </ul>
      </div>

    </div>
    <div class="footer__bottom">
      Built with ❤️ for creators everywhere.
    </div>
  </div>
</footer>

<!-- ══════════ JAVASCRIPT ══════════ -->
<script>
  // ── Scroll: header shadow ──
  const header = document.getElementById('siteHeader');
  window.addEventListener('scroll', () => {
    header.classList.toggle('scrolled', window.scrollY > 20);
  }, { passive: true });

  // ── Intersection Observer: fade-up animations ──
  const fadeEls = document.querySelectorAll('.fade-up');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        // Stagger children within the same parent
        setTimeout(() => {
          entry.target.classList.add('visible');
        }, 80 * (entry.target.dataset.delay || 0));
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  fadeEls.forEach((el, i) => {
    el.dataset.delay = i % 4;
    observer.observe(el);
  });

  // ── Hover: auto-play showcase videos ──
  document.querySelectorAll('.showcase__card video').forEach(video => {
    const card = video.closest('.showcase__card');
    card.addEventListener('mouseenter', () => video.play());
    card.addEventListener('mouseleave', () => video.pause());
  });
</script>
</body>
</html>