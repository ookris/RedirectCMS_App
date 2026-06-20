<?php
// ─── URL helpers ──────────────────────────────────────────────────────────────
function jt_post_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? htmlspecialchars($bp . '/blog/' . $slug, ENT_QUOTES)
                   : htmlspecialchars($bp . '/?post=' . $slug, ENT_QUOTES);
}
function jt_cat_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? htmlspecialchars($bp . '/category/' . $slug, ENT_QUOTES)
                   : htmlspecialchars($bp . '/?category=' . $slug, ENT_QUOTES);
}
function jt_tag_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? htmlspecialchars($bp . '/tag/' . $slug, ENT_QUOTES)
                   : htmlspecialchars($bp . '/?tag=' . $slug, ENT_QUOTES);
}
function jt_page_url(string $bp, string $slug): string {
    return htmlspecialchars($bp . '/?page=' . $slug, ENT_QUOTES);
}
function jt_excerpt(string $html, int $len): string {
    $text = strip_tags($html);
    if ($len > 0 && mb_strlen($text) > $len) {
        return mb_substr($text, 0, $len) . '…';
    }
    return $text;
}
function jt_date(string $dt): string {
    return date('d.m.Y', strtotime($dt));
}
?>
<!doctype html>
<html lang="pl">
<head>
  <!-- Dark mode init — FIRST to prevent flash of unstyled content -->
  <script>
  (function(){
    var t=localStorage.getItem('jt-theme')||(window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');
    if(t==='dark')document.documentElement.setAttribute('data-theme','dark');
  }());
  </script>

  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title><?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($homeMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars(strip_tags($homeMetaDescription)) ?>"/>
  <?php endif; ?>
  <meta property="og:type"  content="website"/>
  <meta property="og:title" content="<?= htmlspecialchars($homeTitle) ?>"/>
  <?php if (!empty($homeMetaDescription)): ?>
    <meta property="og:description" content="<?= htmlspecialchars(strip_tags($homeMetaDescription)) ?>"/>
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>

  <style>
    /* ── VARIABLES ─────────────────────────────────────────────────── */
    :root {
      --jt-accent:   var(--theme-accent,    #dc5b5d);
      --jt-dark:     var(--theme-dark,      #2A2F36);
      --jt-muted:    var(--theme-muted,     #6C7A89);
      --jt-border:   var(--theme-border,    #e2e6ea);
      --jt-bg:       var(--theme-body_bg,   #f2f4f5);
      --jt-card:     var(--theme-card_bg,   #ffffff);
      --jt-text:     var(--theme-dark,      #2A2F36);
      --jt-header:   #ffffff;
      --jt-radius:   10px;
      --jt-shadow:   0 4px 16px rgba(0,0,0,.07);
      --jt-shadow-h: 0 12px 36px rgba(0,0,0,.14);
      --jt-font:     'Poppins', system-ui, sans-serif;
    }
    [data-theme="dark"] {
      --jt-bg:     var(--theme-dark_bg,   #212121);
      --jt-card:   var(--theme-dark_card, #2d2d2d);
      --jt-text:   #f0f0f0;
      --jt-muted:  #909090;
      --jt-border: #3a3a3a;
      --jt-header: var(--theme-dark_header, #1a1a1a);
      --jt-shadow:   0 4px 16px rgba(0,0,0,.3);
      --jt-shadow-h: 0 12px 36px rgba(0,0,0,.5);
    }

    /* ── RESET ─────────────────────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      margin: 0; padding: 0;
      font-family: var(--jt-font);
      font-size: 15px; color: var(--jt-text);
      background: var(--jt-bg);
      -webkit-font-smoothing: antialiased;
      transition: background .25s, color .25s;
    }
    a { color: inherit; text-decoration: none; }
    img { display: block; max-width: 100%; }

    /* ── HEADER ─────────────────────────────────────────────────────── */
    .jt-header {
      position: sticky; top: 0; z-index: 300;
      background: var(--jt-header);
      border-bottom: 1px solid var(--jt-border);
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
      transition: background .25s, border-color .25s;
    }
    .jt-header-inner {
      max-width: 1280px; margin: 0 auto; padding: 0 24px;
      height: 64px; display: flex; align-items: center; gap: 16px;
    }
    .jt-logo {
      display: flex; align-items: center; gap: 10px;
      font-size: 1.05rem; font-weight: 700; color: var(--jt-text);
      flex-shrink: 0; transition: color .2s;
    }
    .jt-logo:hover { color: var(--jt-accent); }
    .jt-logo img { max-height: 36px; width: auto; }
    .jt-nav {
      flex: 1; display: flex; align-items: center; gap: 2px;
      overflow: hidden;
    }
    .jt-nav a {
      font-size: .82rem; font-weight: 500;
      padding: 6px 12px; border-radius: 6px; white-space: nowrap;
      color: var(--jt-text); transition: background .15s, color .15s;
    }
    .jt-nav a:hover, .jt-nav a.active { color: var(--jt-accent); }
    .jt-header-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
    .jt-icon-btn {
      width: 38px; height: 38px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      border: none; background: none; cursor: pointer;
      color: var(--jt-muted); transition: background .15s, color .15s;
    }
    .jt-icon-btn:hover { background: var(--jt-border); color: var(--jt-text); }
    /* dark/light icon swap */
    .jt-sun-icon { display: none; }
    [data-theme="dark"] .jt-moon-icon { display: none; }
    [data-theme="dark"] .jt-sun-icon  { display: block; }

    /* hamburger */
    .jt-hamburger { display: none; }
    @media (max-width: 900px) {
      .jt-nav { display: none; }
      .jt-hamburger { display: flex; }
      .jt-header-inner { padding: 0 16px; }
    }

    /* ── MOBILE MENU ────────────────────────────────────────────────── */
    .jt-mobile-overlay {
      display: none;
      position: fixed; inset: 0; z-index: 400;
      background: rgba(0,0,0,.45);
      opacity: 0; transition: opacity .25s;
    }
    .jt-mobile-overlay.open { opacity: 1; }
    .jt-mobile-drawer {
      position: fixed; top: 0; right: -280px; z-index: 500;
      width: 280px; height: 100dvh;
      background: var(--jt-card);
      box-shadow: -6px 0 24px rgba(0,0,0,.15);
      transition: right .28s cubic-bezier(.4,0,.2,1);
      display: flex; flex-direction: column;
      overflow-y: auto;
    }
    .jt-mobile-drawer.open { right: 0; }
    .jt-drawer-head {
      display: flex; align-items: center; justify-content: space-between;
      padding: 16px 20px; border-bottom: 1px solid var(--jt-border);
    }
    .jt-drawer-head span { font-size: 1rem; font-weight: 700; }
    .jt-drawer-nav { padding: 12px 0; }
    .jt-drawer-nav a {
      display: block; padding: 11px 22px;
      font-size: .9rem; font-weight: 500; color: var(--jt-text);
      border-left: 3px solid transparent;
      transition: color .15s, border-color .15s, background .15s;
    }
    .jt-drawer-nav a:hover, .jt-drawer-nav a.active {
      color: var(--jt-accent); border-left-color: var(--jt-accent);
      background: color-mix(in srgb, var(--jt-accent) 6%, transparent);
    }

    /* ── SEARCH BAR ─────────────────────────────────────────────────── */
    .jt-search-wrap {
      position: fixed; top: 64px; left: 0; right: 0; z-index: 290;
      background: var(--jt-card);
      border-bottom: 2px solid var(--jt-accent);
      box-shadow: 0 6px 24px rgba(0,0,0,.12);
      transform: translateY(-110%);
      transition: transform .25s cubic-bezier(.4,0,.2,1);
    }
    .jt-search-wrap.open { transform: translateY(0); }
    .jt-search-inner {
      max-width: 700px; margin: 0 auto; padding: 0 20px;
      display: flex; align-items: center; gap: 10px; height: 52px;
    }
    .jt-search-inner input {
      flex: 1; border: none; outline: none; background: transparent;
      font-family: var(--jt-font); font-size: .95rem;
      color: var(--jt-text);
    }
    .jt-search-inner input::placeholder { color: var(--jt-muted); }
    .jt-search-inner button {
      background: none; border: none; cursor: pointer;
      color: var(--jt-muted); transition: color .15s;
    }
    .jt-search-inner button:hover { color: var(--jt-accent); }

    /* ── HERO ───────────────────────────────────────────────────────── */
    .jt-hero {
      position: relative; overflow: hidden;
      min-height: 420px; display: flex; align-items: flex-end;
      background: var(--jt-dark);
    }
    .jt-hero-img {
      position: absolute; inset: 0;
      background-size: cover; background-position: center;
      transition: transform 8s ease;
    }
    .jt-hero:hover .jt-hero-img { transform: scale(1.03); }
    .jt-hero-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(to top, rgba(0,0,0,.75) 0%, rgba(0,0,0,.25) 60%, transparent 100%);
    }
    .jt-hero-body {
      position: relative; z-index: 2;
      width: 100%; max-width: 1280px; margin: 0 auto;
      padding: 48px 24px 44px;
    }
    .jt-hero-cat {
      display: inline-block; padding: 3px 14px;
      background: var(--jt-accent); color: #fff;
      font-size: .7rem; font-weight: 700; letter-spacing: .06em;
      text-transform: uppercase; border-radius: 4px; margin-bottom: 14px;
    }
    .jt-hero-title {
      font-size: clamp(1.55rem, 4vw, 2.5rem);
      font-weight: 700; color: #fff; line-height: 1.25;
      margin: 0 0 14px; max-width: 700px;
    }
    .jt-hero-meta {
      font-size: .78rem; color: rgba(255,255,255,.75);
      display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
    }
    .jt-hero-btn {
      display: inline-block; margin-top: 18px;
      padding: 10px 28px; border-radius: 6px;
      background: var(--jt-accent); color: #fff;
      font-size: .82rem; font-weight: 600; letter-spacing: .03em;
      transition: background .2s, transform .15s;
    }
    .jt-hero-btn:hover {
      background: var(--jt-dark); transform: translateY(-2px);
    }
    @media (max-width: 600px) {
      .jt-hero { min-height: 280px; }
      .jt-hero-body { padding: 32px 16px 28px; }
    }

    /* ── FILTER BAR ─────────────────────────────────────────────────── */
    .jt-filters {
      background: var(--jt-card);
      border-bottom: 1px solid var(--jt-border);
    }
    .jt-filters-inner {
      max-width: 1280px; margin: 0 auto; padding: 0 24px;
      display: flex; align-items: center; gap: 8px;
      overflow-x: auto; scrollbar-width: none; height: 52px;
    }
    .jt-filters-inner::-webkit-scrollbar { display: none; }
    .jt-filter-pill {
      display: inline-flex; align-items: center;
      padding: 5px 16px; border-radius: 99px; white-space: nowrap;
      font-size: .76rem; font-weight: 600;
      border: 1.5px solid var(--jt-border);
      color: var(--jt-muted); background: transparent;
      text-decoration: none; flex-shrink: 0;
      transition: border-color .15s, color .15s, background .15s;
    }
    .jt-filter-pill:hover, .jt-filter-pill.active {
      border-color: var(--jt-accent); color: var(--jt-accent);
      background: color-mix(in srgb, var(--jt-accent) 8%, transparent);
    }
    @media (max-width: 600px) { .jt-filters-inner { padding: 0 16px; } }

    /* ── LAYOUT GRID ─────────────────────────────────────────────────── */
    .jt-container {
      max-width: 1280px; margin: 0 auto; padding: 36px 24px 56px;
    }
    .jt-layout { display: flex; gap: 32px; align-items: flex-start; }
    .jt-main { flex: 1; min-width: 0; }
    .jt-sidebar {
      width: 300px; flex-shrink: 0;
      position: sticky; top: 84px;
      max-height: calc(100vh - 100px); overflow-y: auto;
    }
    .jt-sidebar::-webkit-scrollbar { width: 4px; }
    .jt-sidebar::-webkit-scrollbar-thumb { background: var(--jt-border); border-radius: 99px; }
    @media (max-width: 1024px) { .jt-sidebar { display: none; } }
    @media (max-width: 600px)  { .jt-container { padding: 24px 16px 40px; } }

    /* ── SECTION HEADING ────────────────────────────────────────────── */
    .jt-section-title {
      font-size: .95rem; font-weight: 700;
      color: var(--jt-text); margin: 0 0 24px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--jt-accent);
      display: flex; align-items: center; gap: 8px;
    }
    .jt-section-title::before {
      content: ''; display: block;
      width: 4px; height: 18px;
      background: var(--jt-accent); border-radius: 2px;
    }

    /* ── POST GRID ──────────────────────────────────────────────────── */
    .jt-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 24px;
    }
    @media (max-width: 700px) { .jt-grid { grid-template-columns: 1fr; } }

    /* ── CARD ───────────────────────────────────────────────────────── */
    .jt-card {
      background: var(--jt-card);
      border-radius: var(--jt-radius);
      box-shadow: var(--jt-shadow);
      overflow: hidden;
      transition: transform .25s cubic-bezier(.4,0,.2,1), box-shadow .25s;
      display: flex; flex-direction: column;
    }
    .jt-card:hover {
      transform: translateY(-8px);
      box-shadow: var(--jt-shadow-h);
    }
    .jt-card-thumb {
      display: block; overflow: hidden;
      aspect-ratio: 16/9; position: relative;
      background: var(--jt-border);
    }
    .jt-card-thumb img {
      width: 100%; height: 100%; object-fit: cover;
      transition: transform .35s ease;
    }
    .jt-card:hover .jt-card-thumb img { transform: scale(1.06); }
    .jt-card-cat {
      position: absolute; top: 12px; left: 12px;
      padding: 3px 10px; border-radius: 4px;
      font-size: .65rem; font-weight: 700; letter-spacing: .05em;
      text-transform: uppercase; color: #fff;
      background: var(--jt-accent);
    }
    .jt-card-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
    .jt-card-title {
      font-size: .95rem; font-weight: 700; line-height: 1.45;
      color: var(--jt-text); margin: 0 0 10px;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
      overflow: hidden; transition: color .15s;
    }
    .jt-card:hover .jt-card-title { color: var(--jt-accent); }
    .jt-card-excerpt {
      font-size: .8rem; color: var(--jt-muted); line-height: 1.6;
      margin: 0 0 16px; flex: 1;
      display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .jt-card-meta {
      display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
      font-size: .72rem; color: var(--jt-muted);
      border-top: 1px solid var(--jt-border); padding-top: 12px;
    }
    .jt-card-meta-item { display: flex; align-items: center; gap: 4px; }
    .jt-card-meta svg { flex-shrink: 0; }

    /* no-image fallback */
    .jt-card-thumb-placeholder {
      display: flex; align-items: center; justify-content: center;
      background: linear-gradient(135deg, var(--jt-accent), var(--jt-dark));
      height: 100%; min-height: 160px;
    }
    .jt-card-thumb-placeholder svg { opacity: .25; }

    /* ── PAGINATION ──────────────────────────────────────────────────── */
    .jt-pagination {
      display: flex; align-items: center; justify-content: center;
      gap: 6px; margin-top: 40px; flex-wrap: wrap;
    }
    .jt-page-btn {
      min-width: 36px; height: 36px; padding: 0 10px;
      border-radius: 6px; font-family: var(--jt-font);
      font-size: .8rem; font-weight: 600; cursor: pointer;
      border: 1.5px solid var(--jt-border);
      background: var(--jt-card); color: var(--jt-text);
      text-decoration: none; display: flex; align-items: center; justify-content: center;
      transition: border-color .15s, color .15s, background .15s;
    }
    .jt-page-btn:hover, .jt-page-btn.active {
      border-color: var(--jt-accent); color: var(--jt-accent);
      background: color-mix(in srgb, var(--jt-accent) 8%, transparent);
    }
    .jt-page-btn.active { pointer-events: none; }

    /* ── SIDEBAR WIDGET ──────────────────────────────────────────────── */
    .jt-widget {
      background: var(--jt-card);
      border-radius: var(--jt-radius);
      box-shadow: 0 4px 20px rgba(0,0,0,.05);
      padding: 20px; margin-bottom: 20px;
      border: 1px solid var(--jt-border);
    }
    .jt-widget-title {
      font-size: .78rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: .07em;
      color: var(--jt-text); margin-bottom: 14px;
      padding-bottom: 10px; border-bottom: 2px solid var(--jt-accent);
    }

    /* ── FOOTER ──────────────────────────────────────────────────────── */
    .jt-footer {
      background: var(--jt-dark);
      color: rgba(255,255,255,.7);
      padding: 48px 0 0;
    }
    [data-theme="dark"] .jt-footer {
      background: #111; border-top: 1px solid var(--jt-border);
    }
    .jt-footer-inner {
      max-width: 1280px; margin: 0 auto; padding: 0 24px 32px;
      display: flex; gap: 40px; flex-wrap: wrap;
    }
    .jt-footer-brand { flex: 1.5; min-width: 220px; }
    .jt-footer-brand .name {
      font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 10px;
    }
    .jt-footer-brand p {
      font-size: .82rem; line-height: 1.7; margin: 0; max-width: 320px;
    }
    .jt-footer-nav { flex: 1; min-width: 140px; }
    .jt-footer-nav h4 {
      font-size: .78rem; font-weight: 700; letter-spacing: .07em;
      text-transform: uppercase; color: #fff; margin: 0 0 14px;
    }
    .jt-footer-nav a {
      display: block; font-size: .82rem; margin-bottom: 8px;
      color: rgba(255,255,255,.65); transition: color .15s;
    }
    .jt-footer-nav a:hover { color: var(--jt-accent); }
    .jt-footer-copy {
      border-top: 1px solid rgba(255,255,255,.1);
      padding: 14px 24px;
      display: flex; justify-content: space-between; align-items: center;
      flex-wrap: wrap; gap: 8px;
      font-size: .73rem; color: rgba(255,255,255,.45);
    }
    .jt-footer-copy a { color: var(--jt-accent); }
    .jt-footer-copy a:hover { opacity: .8; }
    @media (max-width: 600px) {
      .jt-footer-inner { padding: 0 16px 24px; gap: 24px; }
      .jt-footer-copy  { padding: 12px 16px; flex-direction: column; text-align: center; }
    }

    /* ── ACTIVE FILTER BANNER ────────────────────────────────────────── */
    .jt-active-filter {
      background: color-mix(in srgb, var(--jt-accent) 8%, transparent);
      border-left: 4px solid var(--jt-accent);
      border-radius: 0 8px 8px 0;
      padding: 10px 16px; margin-bottom: 24px;
      font-size: .82rem; display: flex; align-items: center; gap: 8px;
      flex-wrap: wrap;
    }
    .jt-active-filter strong { color: var(--jt-accent); }
    .jt-active-filter a {
      margin-left: auto; font-size: .75rem; color: var(--jt-muted);
      text-decoration: underline; white-space: nowrap;
    }
    .jt-active-filter a:hover { color: var(--jt-accent); }
  </style>
</head>
<body>

<!-- ── MOBILE OVERLAY & DRAWER ─────────────────────────────────────────── -->
<div class="jt-mobile-overlay" id="jtOverlay"></div>
<div class="jt-mobile-drawer" id="jtDrawer">
  <div class="jt-drawer-head">
    <span><?= htmlspecialchars($homeTitle) ?></span>
    <button class="jt-icon-btn" id="jtDrawerClose" aria-label="Zamknij menu">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <nav class="jt-drawer-nav">
    <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
    <?php foreach (($navPages ?? []) as $_np): ?>
      <a href="<?= jt_page_url($basePath, $_np['slug']) ?>">
        <?= htmlspecialchars($_np['title']) ?>
      </a>
    <?php endforeach; ?>
    <?php if (!empty($contactEnabled)): ?>
      <a href="<?= jt_page_url($basePath, 'contact') ?>">Kontakt</a>
    <?php endif; ?>
  </nav>
</div>

<!-- ── HEADER ─────────────────────────────────────────────────────────── -->
<header class="jt-header">
  <div class="jt-header-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="jt-logo">
      <?php if (!empty($brandingLogo)): ?>
        <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($homeTitle) ?>"/>
      <?php endif; ?>
      <span><?= htmlspecialchars($homeTitle) ?></span>
    </a>

    <nav class="jt-nav">
      <a href="<?= htmlspecialchars($basePath) ?>/" class="active">Strona główna</a>
      <?php foreach (($navPages ?? []) as $_np): ?>
        <a href="<?= jt_page_url($basePath, $_np['slug']) ?>">
          <?= htmlspecialchars($_np['title']) ?>
        </a>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= jt_page_url($basePath, 'contact') ?>">Kontakt</a>
      <?php endif; ?>
    </nav>

    <div class="jt-header-actions">
      <!-- search toggle -->
      <button class="jt-icon-btn" id="jtSearchToggle" aria-label="Szukaj">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
      </button>
      <!-- dark mode -->
      <button class="jt-icon-btn" id="jtDarkToggle" aria-label="Tryb ciemny">
        <svg class="jt-moon-icon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="jt-sun-icon"  width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <!-- hamburger -->
      <button class="jt-icon-btn jt-hamburger" id="jtHamburger" aria-label="Menu">
        <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
    </div>
  </div>
</header>

<!-- ── SEARCH BAR ─────────────────────────────────────────────────────── -->
<div class="jt-search-wrap" id="jtSearchBar" role="search">
  <form class="jt-search-inner" action="<?= htmlspecialchars($basePath) ?>/" method="get">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;color:var(--jt-muted)">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="text" name="search" placeholder="Szukaj wpisów…" id="jtSearchInput" autocomplete="off"/>
    <button type="button" id="jtSearchClose" aria-label="Zamknij">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>
  </form>
</div>

<?php
// Separate hero post from grid
$_hero  = null;
$_posts = $blogPosts ?? [];
if (!empty($_posts) && empty($activeCategory) && empty($activeTag) && ($currentPage ?? 1) === 1) {
    $_hero  = array_shift($_posts);
}
?>

<!-- ── HERO ───────────────────────────────────────────────────────────── -->
<?php if ($_hero): ?>
<section class="jt-hero">
  <?php if (!empty($_hero['og_image'])): ?>
    <div class="jt-hero-img" style="background-image:url('<?= htmlspecialchars($basePath . '/' . $_hero['og_image'], ENT_QUOTES) ?>')"></div>
  <?php else: ?>
    <div class="jt-hero-img" style="background:linear-gradient(135deg,<?= htmlspecialchars($basePath ? '#2A2F36' : '#2A2F36') ?>,#dc5b5d)"></div>
  <?php endif; ?>
  <div class="jt-hero-overlay"></div>
  <div class="jt-hero-body">
    <?php if (!empty($_hero['category_name'])): ?>
      <a href="<?= jt_cat_url($basePath, $_hero['category_slug'], $prettyUrls ?? false) ?>" class="jt-hero-cat">
        <?= htmlspecialchars($_hero['category_name']) ?>
      </a>
    <?php endif; ?>
    <h2 class="jt-hero-title">
      <a href="<?= jt_post_url($basePath, $_hero['slug'], $prettyUrls ?? false) ?>" style="color:inherit">
        <?= htmlspecialchars($_hero['page_title']) ?>
      </a>
    </h2>
    <div class="jt-hero-meta">
      <span>
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;margin-right:4px"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <?= jt_date($_hero['created_at']) ?>
      </span>
      <?php if (!empty($_hero['click_count'])): ?>
        <span>
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;margin-right:4px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          <?= (int)$_hero['click_count'] ?> odsłon
        </span>
      <?php endif; ?>
    </div>
    <a href="<?= jt_post_url($basePath, $_hero['slug'], $prettyUrls ?? false) ?>" class="jt-hero-btn">
      Czytaj więcej →
    </a>
  </div>
</section>
<?php endif; ?>

<!-- ── FILTER BAR ─────────────────────────────────────────────────────── -->
<?php if (!empty($allCategories)): ?>
<div class="jt-filters">
  <div class="jt-filters-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/"
       class="jt-filter-pill <?= empty($activeCategory) && empty($activeTag) ? 'active' : '' ?>">
      Wszystkie
    </a>
    <?php foreach (($allCategories ?? []) as $_cat): ?>
      <a href="<?= jt_cat_url($basePath, $_cat['slug'], $prettyUrls ?? false) ?>"
         class="jt-filter-pill <?= (!empty($activeCategory) && $activeCategory['slug'] === $_cat['slug']) ? 'active' : '' ?>"
         style="<?= (!empty($activeCategory) && $activeCategory['slug'] === $_cat['slug']) ? 'border-color:' . htmlspecialchars($_cat['color'] ?: '#dc5b5d', ENT_QUOTES) . ';color:' . htmlspecialchars($_cat['color'] ?: '#dc5b5d', ENT_QUOTES) : '' ?>">
        <?= htmlspecialchars($_cat['name']) ?>
        <span style="margin-left:5px;font-size:.65rem;opacity:.7"><?= (int)$_cat['post_count'] ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- ── MAIN CONTENT ────────────────────────────────────────────────────── -->
<main>
  <div class="jt-container">
    <div class="jt-layout">
      <!-- Posts column -->
      <div class="jt-main">

        <!-- Active filter banner -->
        <?php if (!empty($activeCategory)): ?>
          <div class="jt-active-filter">
            Kategoria: <strong><?= htmlspecialchars($activeCategory['name']) ?></strong>
            <a href="<?= htmlspecialchars($basePath) ?>/">✕ Wyczyść filtr</a>
          </div>
        <?php elseif (!empty($activeTag)): ?>
          <div class="jt-active-filter">
            Tag: <strong>#<?= htmlspecialchars($activeTag['name']) ?></strong>
            <a href="<?= htmlspecialchars($basePath) ?>/">✕ Wyczyść filtr</a>
          </div>
        <?php endif; ?>

        <?php if (!empty($_posts)): ?>
          <div class="jt-section-title">
            <?= !empty($activeCategory) ? htmlspecialchars($activeCategory['name']) : (!empty($activeTag) ? '#' . htmlspecialchars($activeTag['name']) : 'Najnowsze wpisy') ?>
          </div>
          <div class="jt-grid">
            <?php foreach ($_posts as $_post): ?>
            <article class="jt-card">
              <a href="<?= jt_post_url($basePath, $_post['slug'], $prettyUrls ?? false) ?>" class="jt-card-thumb">
                <?php if (!empty($_post['og_image']) && ($blogShowImages ?? true)): ?>
                  <img src="<?= htmlspecialchars($basePath . '/uploads/cropped/listing/' . basename($_post['og_image']), ENT_QUOTES) ?>"
                       onerror="this.src='<?= htmlspecialchars($basePath . '/' . $_post['og_image'], ENT_QUOTES) ?>'"
                       alt="<?= htmlspecialchars($_post['page_title']) ?>"
                       loading="lazy"/>
                <?php else: ?>
                  <div class="jt-card-thumb-placeholder">
                    <svg width="48" height="48" fill="none" stroke="#fff" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  </div>
                <?php endif; ?>
                <?php if (!empty($_post['category_name'])): ?>
                  <span class="jt-card-cat"
                        style="background:<?= htmlspecialchars($_post['category_color'] ?: 'var(--jt-accent)', ENT_QUOTES) ?>">
                    <?= htmlspecialchars($_post['category_name']) ?>
                  </span>
                <?php endif; ?>
              </a>
              <div class="jt-card-body">
                <h2 class="jt-card-title">
                  <a href="<?= jt_post_url($basePath, $_post['slug'], $prettyUrls ?? false) ?>">
                    <?= htmlspecialchars($_post['page_title']) ?>
                  </a>
                </h2>
                <?php if (!empty($_post['page_description'])): ?>
                  <p class="jt-card-excerpt">
                    <?= htmlspecialchars(jt_excerpt($_post['page_description'], $blogDescLength ?? 120)) ?>
                  </p>
                <?php endif; ?>
                <div class="jt-card-meta">
                  <span class="jt-card-meta-item">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <?= jt_date($_post['created_at']) ?>
                  </span>
                  <?php if (!empty($_post['click_count'])): ?>
                    <span class="jt-card-meta-item">
                      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                      <?= (int)$_post['click_count'] ?>
                    </span>
                  <?php endif; ?>
                  <?php if (!empty($_post['tags'])): ?>
                    <span style="margin-left:auto;display:flex;gap:4px;flex-wrap:wrap">
                      <?php foreach (array_slice($_post['tags'], 0, 2) as $_t): ?>
                        <a href="<?= jt_tag_url($basePath, $_t['slug'], $prettyUrls ?? false) ?>"
                           style="font-size:.65rem;background:color-mix(in srgb,var(--jt-accent) 10%,transparent);color:var(--jt-accent);padding:2px 8px;border-radius:99px;font-weight:600">
                          #<?= htmlspecialchars($_t['name']) ?>
                        </a>
                      <?php endforeach; ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </article>
            <?php endforeach; ?>
          </div>

        <?php elseif (empty($_hero)): ?>
          <div style="text-align:center;padding:60px 20px;color:var(--jt-muted)">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <p style="font-size:.9rem">Brak wpisów do wyświetlenia.</p>
          </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php
        $_curPage   = (int)($currentPage ?? 1);
        $_totPages  = (int)($totalPages ?? 1);
        if ($_totPages > 1):
            $pageBase = $basePath . '/?';
            if (!empty($activeCategory)) $pageBase .= 'category=' . urlencode($activeCategory['slug']) . '&';
            elseif (!empty($activeTag))  $pageBase .= 'tag=' . urlencode($activeTag['slug']) . '&';
            $pageBase .= 'page=';
        ?>
        <nav class="jt-pagination" aria-label="Paginacja">
          <?php if ($_curPage > 1): ?>
            <a href="<?= htmlspecialchars($pageBase . ($_curPage - 1), ENT_QUOTES) ?>" class="jt-page-btn">←</a>
          <?php endif; ?>
          <?php for ($i = max(1, $_curPage - 2); $i <= min($_totPages, $_curPage + 2); $i++): ?>
            <a href="<?= htmlspecialchars($pageBase . $i, ENT_QUOTES) ?>"
               class="jt-page-btn <?= $i === $_curPage ? 'active' : '' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>
          <?php if ($_curPage < $_totPages): ?>
            <a href="<?= htmlspecialchars($pageBase . ($_curPage + 1), ENT_QUOTES) ?>" class="jt-page-btn">→</a>
          <?php endif; ?>
        </nav>
        <?php endif; ?>

      </div><!-- /.jt-main -->

      <!-- Sidebar -->
      <?php if (!empty($sidebarData)): ?>
      <aside class="jt-sidebar">
        <?php require __DIR__ . '/_sidebar.php'; ?>
      </aside>
      <?php endif; ?>

    </div><!-- /.jt-layout -->
  </div><!-- /.jt-container -->
</main>

<!-- ── FOOTER ──────────────────────────────────────────────────────────── -->
<footer class="jt-footer">
  <div class="jt-footer-inner">
    <div class="jt-footer-brand">
      <div class="name"><?= htmlspecialchars($homeTitle) ?></div>
      <?php if (!empty($homeSubtitle)): ?>
        <p><?= htmlspecialchars($homeSubtitle) ?></p>
      <?php endif; ?>
    </div>
    <?php if (!empty($navPages) || !empty($contactEnabled)): ?>
    <div class="jt-footer-nav">
      <h4>Strony</h4>
      <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
      <?php foreach (($navPages ?? []) as $_np): ?>
        <a href="<?= jt_page_url($basePath, $_np['slug']) ?>"><?= htmlspecialchars($_np['title']) ?></a>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= jt_page_url($basePath, 'contact') ?>">Kontakt</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($allCategories)): ?>
    <div class="jt-footer-nav">
      <h4>Kategorie</h4>
      <?php foreach (array_slice($allCategories, 0, 6) as $_c): ?>
        <a href="<?= jt_cat_url($basePath, $_c['slug'], $prettyUrls ?? false) ?>">
          <?= htmlspecialchars($_c['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="jt-footer-copy">
    <?php if (!empty($homeFooter)): ?>
      <span><?= $homeFooter ?></span>
    <?php else: ?>
      <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>. Wszelkie prawa zastrzeżone.</span>
    <?php endif; ?>
    <span>Powered by <a href="https://redirectcms.pl" target="_blank" rel="noopener noreferrer">RedirectCMS</a> &middot; Theme: <a href="https://github.com/zaferzent/JTemplate" target="_blank" rel="noopener noreferrer">JTemplate</a></span>
  </div>
</footer>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>

<script>
(function () {
  // Dark mode toggle
  document.getElementById('jtDarkToggle').addEventListener('click', function () {
    var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('jt-theme', next);
  });

  // Search bar
  var searchBar    = document.getElementById('jtSearchBar');
  var searchInput  = document.getElementById('jtSearchInput');
  var searchToggle = document.getElementById('jtSearchToggle');
  var searchClose  = document.getElementById('jtSearchClose');
  searchToggle.addEventListener('click', function () {
    searchBar.classList.add('open');
    setTimeout(function () { searchInput.focus(); }, 50);
  });
  searchClose.addEventListener('click', function () {
    searchBar.classList.remove('open');
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') searchBar.classList.remove('open');
  });

  // Mobile hamburger
  var hamburger = document.getElementById('jtHamburger');
  var drawer    = document.getElementById('jtDrawer');
  var overlay   = document.getElementById('jtOverlay');
  var drawerClose = document.getElementById('jtDrawerClose');
  function openDrawer() {
    drawer.classList.add('open');
    overlay.style.display = 'block';
    setTimeout(function () { overlay.classList.add('open'); }, 10);
  }
  function closeDrawer() {
    drawer.classList.remove('open');
    overlay.classList.remove('open');
    setTimeout(function () { overlay.style.display = 'none'; }, 260);
  }
  hamburger.addEventListener('click', openDrawer);
  drawerClose.addEventListener('click', closeDrawer);
  overlay.addEventListener('click', closeDrawer);
}());
</script>
</body>
</html>
