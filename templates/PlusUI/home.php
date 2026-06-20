<!doctype html>
<html lang="pl">
<head>
  <!-- Dark mode init — must be FIRST to prevent FOUC -->
  <script>
  (function(){
    var t=localStorage.getItem('pu-theme')||(window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');
    if(t==='dark')document.documentElement.setAttribute('data-theme','dark');
  }());
  </script>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($homeMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars(strip_tags($homeMetaDescription)) ?>" />
  <?php endif; ?>
  <meta property="og:type"  content="website" />
  <meta property="og:title" content="<?= htmlspecialchars($homeTitle) ?>" />
  <?php if (!empty($homeMetaDescription)): ?>
    <meta property="og:description" content="<?= htmlspecialchars(strip_tags($homeMetaDescription)) ?>" />
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    /* ─── THEME VARIABLES ────────────────────────────────────────── */
    :root {
      --pu-primary:   var(--theme-primary, #1976d2);
      --pu-accent:    var(--theme-accent, #e91e63);
      --pu-text:      var(--theme-text, #08102b);
      --pu-muted:     var(--theme-muted, #989b9f);
      --pu-border:    var(--theme-border, #e8ecf0);
      --pu-bg:        var(--theme-body-bg, #fdfcff);
      --pu-card:      var(--theme-card-bg, #ffffff);
      --pu-header-bg: #ffffff;
      --pu-radius:    8px;
      --pu-shadow:    0 2px 8px rgba(0,0,0,.06);
      --pu-shadow-md: 0 6px 24px rgba(0,0,0,.1);
    }
    [data-theme="dark"] {
      --pu-primary:   var(--theme-primary-d, #8775f5);
      --pu-bg:        var(--theme-dark-bg, #1e1e1e);
      --pu-card:      var(--theme-dark-card, #2a2a2a);
      --pu-text:      #f0eeff;
      --pu-muted:     #9a9a9a;
      --pu-border:    #3a3a3a;
      --pu-header-bg: #242424;
      --pu-shadow:    0 2px 8px rgba(0,0,0,.35);
      --pu-shadow-md: 0 6px 24px rgba(0,0,0,.45);
    }

    /* ─── BASE ────────────────────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      margin: 0; padding: 0;
      background: var(--pu-bg);
      font-family: 'Roboto', system-ui, sans-serif;
      font-size: 15px; color: var(--pu-text);
      -webkit-font-smoothing: antialiased;
      transition: background .2s, color .2s;
    }
    a { color: inherit; text-decoration: none; }
    img { display: block; max-width: 100%; }

    /* ─── HEADER ──────────────────────────────────────────────────── */
    .pu-header {
      position: sticky; top: 0; z-index: 200;
      height: 60px; background: var(--pu-header-bg);
      border-bottom: 1px solid var(--pu-border);
      box-shadow: var(--pu-shadow);
      transition: background .2s, border-color .2s;
    }
    .pu-header-inner {
      max-width: 1280px; margin: 0 auto;
      padding: 0 20px; height: 100%;
      display: flex; align-items: center; gap: 16px;
    }
    .pu-logo {
      display: flex; align-items: center; gap: 10px;
      font-size: .98rem; font-weight: 700; color: var(--pu-text);
      flex-shrink: 0; text-decoration: none;
      transition: color .2s;
    }
    .pu-logo:hover { color: var(--pu-primary); }
    .pu-logo img { max-height: 32px; width: auto; }
    .pu-nav {
      flex: 1; display: flex; align-items: center;
      gap: 2px; overflow: hidden;
    }
    .pu-nav a {
      font-size: .83rem; font-weight: 500;
      padding: 5px 10px; border-radius: 6px;
      color: var(--pu-text); white-space: nowrap;
      transition: background .15s, color .15s;
    }
    .pu-nav a:hover { background: var(--pu-border); }
    .pu-nav a.active { color: var(--pu-primary); background: color-mix(in srgb, var(--pu-primary) 10%, transparent); }
    .pu-header-actions { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }
    .pu-icon-btn {
      width: 36px; height: 36px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      background: none; border: none; cursor: pointer;
      color: var(--pu-muted); transition: background .15s, color .15s;
    }
    .pu-icon-btn:hover { background: var(--pu-border); color: var(--pu-text); }
    /* Dark mode icon sync via CSS */
    .pu-sun-icon { display: none; }
    [data-theme="dark"] .pu-moon-icon { display: none; }
    [data-theme="dark"] .pu-sun-icon  { display: block; }
    @media (max-width: 767px) {
      .pu-nav { display: none; }
      .pu-header-inner { padding: 0 16px; }
    }

    /* ─── SEARCH OVERLAY ──────────────────────────────────────────── */
    .pu-search-overlay {
      position: fixed; inset: 0; z-index: 9999;
      background: rgba(0,0,0,.55);
      display: flex; align-items: flex-start; justify-content: center;
      padding-top: 90px;
      opacity: 0; visibility: hidden;
      transition: opacity .2s, visibility .2s;
    }
    .pu-search-overlay.open { opacity: 1; visibility: visible; }
    .pu-search-box {
      width: min(580px, 92vw);
      background: var(--pu-card);
      border-radius: 12px;
      box-shadow: 0 24px 64px rgba(0,0,0,.35);
      overflow: hidden;
    }
    .pu-search-input-row {
      display: flex; align-items: center; gap: 12px;
      padding: 0 16px; border-bottom: 1px solid var(--pu-border);
    }
    .pu-search-input-row input {
      flex: 1; border: none; outline: none; background: transparent;
      font-family: 'Roboto', sans-serif; font-size: 1rem;
      color: var(--pu-text); padding: 16px 0;
    }
    .pu-search-input-row input::placeholder { color: var(--pu-muted); }
    .pu-search-close-btn {
      background: none; border: none; cursor: pointer;
      font-size: 1.25rem; color: var(--pu-muted); padding: 4px;
      transition: color .15s;
    }
    .pu-search-close-btn:hover { color: var(--pu-text); }
    .pu-search-hint { padding: 10px 16px; font-size: .75rem; color: var(--pu-muted); }

    /* ─── LAYOUT ──────────────────────────────────────────────────── */
    .pu-container {
      max-width: 1280px; margin: 0 auto;
      padding: 28px 20px 56px;
    }
    .pu-layout { display: flex; gap: 28px; align-items: flex-start; }
    .pu-main { flex: 1; min-width: 0; }
    .pu-aside {
      width: 300px; flex-shrink: 0;
      position: sticky; top: 80px;
    }
    @media (max-width: 1023px) { .pu-aside { display: none; } }
    @media (max-width: 767px) { .pu-container { padding: 16px 16px 40px; } }

    /* ─── FILTER BAR ──────────────────────────────────────────────── */
    .pu-filter {
      display: flex; align-items: center; gap: 8px;
      flex-wrap: wrap; margin-bottom: 20px;
    }
    .pu-chip {
      display: inline-flex; align-items: center;
      padding: 5px 14px; border-radius: 99px;
      font-size: .78rem; font-weight: 500;
      background: var(--pu-card); border: 1px solid var(--pu-border);
      color: var(--pu-text); text-decoration: none;
      transition: background .15s, border-color .15s, color .15s;
    }
    .pu-chip:hover, .pu-chip.active {
      background: var(--pu-primary); border-color: var(--pu-primary); color: #fff;
    }

    /* ─── HERO POST ───────────────────────────────────────────────── */
    .pu-hero {
      display: block; position: relative;
      border-radius: var(--pu-radius); overflow: hidden;
      margin-bottom: 24px; text-decoration: none;
      background: #111;
    }
    .pu-hero-img {
      width: 100%; aspect-ratio: 16/9; object-fit: cover;
      display: block; opacity: .92;
      transition: opacity .3s, transform .4s;
    }
    .pu-hero:hover .pu-hero-img { opacity: 1; transform: scale(1.02); }
    .pu-hero-overlay {
      position: absolute; bottom: 0; left: 0; right: 0;
      padding: 32px 28px 24px;
      background: linear-gradient(to top, rgba(0,0,0,.92) 0%, rgba(0,0,0,.5) 55%, transparent 100%);
    }
    .pu-hero-cat {
      display: inline-block;
      background: var(--pu-primary); color: #fff;
      padding: 3px 10px; border-radius: 4px;
      font-size: .7rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: .05em;
      margin-bottom: 10px; text-decoration: none;
    }
    .pu-hero-title {
      margin: 0 0 8px; font-size: 1.65rem; font-weight: 700;
      line-height: 1.3; color: #fff;
    }
    .pu-hero-meta { font-size: .78rem; color: rgba(255,255,255,.65); }
    @media (max-width: 576px) {
      .pu-hero-title { font-size: 1.2rem; }
      .pu-hero-overlay { padding: 20px 16px 16px; }
    }

    /* ─── CARD GRID ───────────────────────────────────────────────── */
    .pu-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px; margin-bottom: 36px;
    }
    @media (max-width: 900px) { .pu-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 576px) { .pu-grid { grid-template-columns: 1fr; } }
    .pu-card {
      background: var(--pu-card); border-radius: var(--pu-radius);
      overflow: hidden; box-shadow: var(--pu-shadow);
      text-decoration: none; color: var(--pu-text);
      display: flex; flex-direction: column;
      transition: box-shadow .2s, transform .2s;
      border: 1px solid var(--pu-border);
    }
    .pu-card:hover { box-shadow: var(--pu-shadow-md); transform: translateY(-2px); }
    .pu-card-thumb {
      aspect-ratio: 16/9; overflow: hidden;
      background: var(--pu-border); flex-shrink: 0;
    }
    .pu-card-thumb img {
      width: 100%; height: 100%; object-fit: cover;
      transition: transform .35s ease;
    }
    .pu-card:hover .pu-card-thumb img { transform: scale(1.04); }
    .pu-card-body {
      padding: 14px 16px 16px; flex: 1;
      display: flex; flex-direction: column;
    }
    .pu-card-cat {
      display: inline-block; font-size: .7rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: .05em;
      color: var(--pu-primary); margin-bottom: 6px; text-decoration: none;
    }
    .pu-card-cat:hover { color: var(--pu-accent); }
    .pu-card-title {
      margin: 0 0 8px; font-size: .93rem; font-weight: 700; line-height: 1.4;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .pu-card-excerpt {
      margin: 0 0 12px; font-size: .8rem; color: var(--pu-muted); line-height: 1.6; flex: 1;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .pu-card-meta {
      font-size: .74rem; color: var(--pu-muted);
      display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    }

    /* ─── PAGINATION ──────────────────────────────────────────────── */
    .pu-pagination {
      display: flex; align-items: center; justify-content: center;
      gap: 6px; margin-top: 8px;
    }
    .pu-page-btn {
      min-width: 36px; height: 36px; border-radius: 6px;
      display: flex; align-items: center; justify-content: center;
      font-size: .84rem; font-weight: 500;
      color: var(--pu-text); background: var(--pu-card);
      border: 1px solid var(--pu-border); text-decoration: none;
      transition: background .15s, color .15s, border-color .15s;
    }
    .pu-page-btn:hover { border-color: var(--pu-primary); color: var(--pu-primary); }
    .pu-page-btn.active { background: var(--pu-primary); border-color: var(--pu-primary); color: #fff; }
    .pu-page-btn.disabled { opacity: .4; pointer-events: none; }
    .pu-page-ellipsis { color: var(--pu-muted); padding: 0 4px; line-height: 36px; }

    /* ─── SIDEBAR ─────────────────────────────────────────────────── */
    .pu-widget { margin-bottom: 24px; }
    .pu-widget-title {
      font-size: .75rem; font-weight: 700; letter-spacing: .07em;
      text-transform: uppercase; color: var(--pu-text);
      margin: 0 0 12px; padding-bottom: 10px;
      border-bottom: 2px solid var(--pu-primary);
    }
    .pu-search-sidebar {
      display: flex; align-items: center; gap: 8px;
      background: var(--pu-card); border: 1px solid var(--pu-border);
      border-radius: 8px; padding: 8px 14px; margin-bottom: 24px;
    }
    .pu-search-sidebar input {
      flex: 1; border: none; outline: none; background: transparent;
      font-family: 'Roboto', sans-serif; font-size: .875rem; color: var(--pu-text);
    }
    .pu-search-sidebar input::placeholder { color: var(--pu-muted); }
    .pu-search-sidebar svg { color: var(--pu-muted); flex-shrink: 0; }

    /* ─── FOOTER ──────────────────────────────────────────────────── */
    .pu-footer {
      background: var(--pu-card);
      border-top: 1px solid var(--pu-border);
      padding: 40px 0 0; margin-top: 20px;
    }
    .pu-footer-inner {
      max-width: 1280px; margin: 0 auto;
      padding: 0 20px; display: flex; gap: 40px; flex-wrap: wrap;
    }
    .pu-footer-brand { flex: 1; min-width: 200px; }
    .pu-footer-brand-name { font-size: 1rem; font-weight: 700; margin-bottom: 8px; }
    .pu-footer-brand-desc { font-size: .82rem; color: var(--pu-muted); line-height: 1.7; }
    .pu-footer-nav { min-width: 140px; }
    .pu-footer-nav h5 {
      font-size: .73rem; font-weight: 700; letter-spacing: .07em;
      text-transform: uppercase; color: var(--pu-text); margin: 0 0 12px;
    }
    .pu-footer-nav ul { list-style: none; padding: 0; margin: 0; }
    .pu-footer-nav li { margin-bottom: 8px; }
    .pu-footer-nav a { font-size: .82rem; color: var(--pu-muted); }
    .pu-footer-nav a:hover { color: var(--pu-primary); }
    .pu-footer-copy {
      max-width: 1280px; margin: 28px auto 0;
      padding: 14px 20px; border-top: 1px solid var(--pu-border);
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 8px;
      font-size: .74rem; color: var(--pu-muted);
    }
    .pu-footer-copy a { color: var(--pu-primary); }
    .pu-footer-copy a:hover { opacity: .8; }
    @media (max-width: 767px) {
      .pu-footer-inner { padding: 0 16px; gap: 24px; }
      .pu-footer-copy { flex-direction: column; padding: 14px 16px; text-align: center; gap: 4px; }
    }

    /* ─── EMPTY STATE ─────────────────────────────────────────────── */
    .pu-empty { text-align: center; padding: 64px 20px; color: var(--pu-muted); }
    .pu-empty h3 { font-size: 1.1rem; font-weight: 700; color: var(--pu-text); margin: 0 0 8px; }
    .pu-empty a { color: var(--pu-primary); text-decoration: underline; }
  </style>
</head>
<body>

<?php
// ─── HELPERS ──────────────────────────────────────────────────────────────────
function pu_post_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? htmlspecialchars($bp . '/blog/' . $slug, ENT_QUOTES)
                   : htmlspecialchars($bp . '/?post=' . $slug, ENT_QUOTES);
}
function pu_cat_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? htmlspecialchars($bp . '/category/' . $slug, ENT_QUOTES)
                   : htmlspecialchars($bp . '/?category=' . $slug, ENT_QUOTES);
}
function pu_tag_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? htmlspecialchars($bp . '/tag/' . $slug, ENT_QUOTES)
                   : htmlspecialchars($bp . '/?tag=' . $slug, ENT_QUOTES);
}
function pu_page_url(string $bp, string $slug): string {
    return htmlspecialchars($bp . '/?page=' . $slug, ENT_QUOTES);
}
function pu_thumb(string $bp, string $ogImage, string $size = 'listing'): string {
    $file = basename($ogImage);
    if ($file && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/cropped/' . $size . '/' . $file)) {
        return htmlspecialchars($bp . '/uploads/cropped/' . $size . '/' . $file, ENT_QUOTES);
    }
    return htmlspecialchars($bp . '/' . ltrim($ogImage, '/'), ENT_QUOTES);
}
function pu_excerpt(string $html, int $len): string {
    $text = strip_tags($html);
    return mb_strlen($text) > $len ? mb_substr($text, 0, $len) . '…' : $text;
}
function pu_date(string $dt): string {
    $ts = strtotime($dt);
    return $ts ? date('d.m.Y', $ts) : '';
}
function pu_read_time(string $html): int {
    return max(1, (int)ceil(str_word_count(strip_tags($html)) / 200));
}
?>

<!-- SEARCH OVERLAY -->
<div class="pu-search-overlay" id="puSearchOverlay">
  <div class="pu-search-box">
    <form class="pu-search-input-row" method="get" action="<?= htmlspecialchars($basePath) ?>/" role="search">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--pu-muted);flex-shrink:0"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" name="q" placeholder="Szukaj wpisów…" autocomplete="off" id="puSearchInput" />
      <button type="button" class="pu-search-close-btn" id="puSearchClose">✕</button>
    </form>
    <div class="pu-search-hint">Wpisz frazę i naciśnij ENTER &nbsp;·&nbsp; ESC aby zamknąć</div>
  </div>
</div>

<!-- HEADER -->
<header class="pu-header">
  <div class="pu-header-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="pu-logo">
      <?php if (!empty($brandingLogo)): ?>
        <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($homeTitle) ?>" />
      <?php endif; ?>
      <span><?= htmlspecialchars($homeTitle) ?></span>
    </a>
    <nav class="pu-nav">
      <?php foreach (($allCategories ?? []) as $_c): ?>
        <a href="<?= pu_cat_url($basePath, $_c['slug'], $prettyUrls ?? false) ?>"
           class="<?= (!empty($currentCategory) && $currentCategory['slug'] === $_c['slug']) ? 'active' : '' ?>">
          <?= htmlspecialchars($_c['name']) ?>
        </a>
      <?php endforeach; ?>
      <?php foreach (($navPages ?? []) as $_np): ?>
        <a href="<?= pu_page_url($basePath, $_np['slug']) ?>"><?= htmlspecialchars($_np['title']) ?></a>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= pu_page_url($basePath, 'contact') ?>">Kontakt</a>
      <?php endif; ?>
    </nav>
    <div class="pu-header-actions">
      <button class="pu-icon-btn" id="puSearchOpen" aria-label="Szukaj">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </button>
      <button class="pu-icon-btn" id="puDarkToggle" aria-label="Tryb ciemny">
        <svg class="pu-moon-icon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="pu-sun-icon"  width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
    </div>
  </div>
</header>

<!-- MAIN CONTENT -->
<div class="pu-container">
  <div class="pu-layout">
    <main class="pu-main">

      <!-- Category filter -->
      <?php if (!empty($allCategories)): ?>
        <div class="pu-filter">
          <a href="<?= htmlspecialchars($basePath) ?>/" class="pu-chip <?= empty($currentCategory) ? 'active' : '' ?>">Wszystkie</a>
          <?php foreach ($allCategories as $_c): ?>
            <a href="<?= pu_cat_url($basePath, $_c['slug'], $prettyUrls ?? false) ?>"
               class="pu-chip <?= (!empty($currentCategory) && $currentCategory['slug'] === $_c['slug']) ? 'active' : '' ?>">
              <?= htmlspecialchars($_c['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($blogPosts)): ?>
        <?php $_remaining = $blogPosts; $_first = array_shift($_remaining); ?>

        <!-- HERO POST -->
        <a href="<?= pu_post_url($basePath, $_first['slug'], $prettyUrls ?? false) ?>" class="pu-hero">
          <?php if (!empty($_first['og_image'])): ?>
            <img class="pu-hero-img" src="<?= pu_thumb($basePath, $_first['og_image'], 'featured') ?>"
                 alt="<?= htmlspecialchars($_first['page_title']) ?>" loading="lazy" />
          <?php else: ?>
            <div style="aspect-ratio:16/9;background:linear-gradient(135deg,var(--pu-primary),var(--pu-accent))"></div>
          <?php endif; ?>
          <div class="pu-hero-overlay">
            <?php if (!empty($_first['category_name'])): ?>
              <span class="pu-hero-cat"><?= htmlspecialchars($_first['category_name']) ?></span>
            <?php endif; ?>
            <h2 class="pu-hero-title"><?= htmlspecialchars($_first['page_title']) ?></h2>
            <div class="pu-hero-meta">
              <?= pu_date($_first['created_at'] ?? '') ?>
              <?php if (!empty($_first['page_description'])): ?>
                &nbsp;·&nbsp; <?= pu_read_time($_first['page_description']) ?> min czytania
              <?php endif; ?>
            </div>
          </div>
        </a>

        <!-- POST GRID -->
        <?php if (!empty($_remaining)): ?>
          <div class="pu-grid">
            <?php foreach ($_remaining as $_p): ?>
              <a href="<?= pu_post_url($basePath, $_p['slug'], $prettyUrls ?? false) ?>" class="pu-card">
                <div class="pu-card-thumb">
                  <?php if (!empty($_p['og_image'])): ?>
                    <img src="<?= pu_thumb($basePath, $_p['og_image'], 'listing') ?>"
                         alt="<?= htmlspecialchars($_p['page_title']) ?>" loading="lazy" />
                  <?php endif; ?>
                </div>
                <div class="pu-card-body">
                  <?php if (!empty($_p['category_name'])): ?>
                    <span class="pu-card-cat"><?= htmlspecialchars($_p['category_name']) ?></span>
                  <?php endif; ?>
                  <div class="pu-card-title"><?= htmlspecialchars($_p['page_title']) ?></div>
                  <?php if (!empty($_p['page_description'])): ?>
                    <div class="pu-card-excerpt"><?= htmlspecialchars(pu_excerpt($_p['page_description'], 110)) ?></div>
                  <?php endif; ?>
                  <div class="pu-card-meta">
                    <span><?= pu_date($_p['created_at'] ?? '') ?></span>
                    <?php if (!empty($_p['page_description'])): ?>
                      <span>·</span>
                      <span><?= pu_read_time($_p['page_description']) ?> min</span>
                    <?php endif; ?>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- PAGINATION -->
        <?php if (!empty($totalPages) && $totalPages > 1): ?>
          <nav class="pu-pagination" aria-label="Strony">
            <?php
            if ($currentPage > 1): ?>
              <a href="<?= htmlspecialchars($basePath . '/?p=' . ($currentPage - 1)) ?>" class="pu-page-btn">‹</a>
            <?php endif;
            $start = max(1, $currentPage - 2);
            $end   = min($totalPages, $currentPage + 2);
            if ($start > 1): ?><a href="<?= htmlspecialchars($basePath . '/?p=1') ?>" class="pu-page-btn">1</a><?php endif;
            if ($start > 2): ?><span class="pu-page-ellipsis">…</span><?php endif;
            for ($p = $start; $p <= $end; $p++): ?>
              <a href="<?= htmlspecialchars($basePath . '/?p=' . $p) ?>"
                 class="pu-page-btn <?= $p === $currentPage ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor;
            if ($end < $totalPages - 1): ?><span class="pu-page-ellipsis">…</span><?php endif;
            if ($end < $totalPages): ?><a href="<?= htmlspecialchars($basePath . '/?p=' . $totalPages) ?>" class="pu-page-btn"><?= $totalPages ?></a><?php endif;
            if ($currentPage < $totalPages): ?>
              <a href="<?= htmlspecialchars($basePath . '/?p=' . ($currentPage + 1)) ?>" class="pu-page-btn">›</a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>

      <?php else: ?>
        <div class="pu-empty">
          <h3>Brak wpisów</h3>
          <p style="font-size:.9rem">Nie znaleziono żadnych wpisów.</p>
          <a href="<?= htmlspecialchars($basePath) ?>/">Wróć do strony głównej</a>
        </div>
      <?php endif; ?>
    </main>

    <!-- SIDEBAR -->
    <aside class="pu-aside">
      <form class="pu-search-sidebar" method="get" action="<?= htmlspecialchars($basePath) ?>/" role="search">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="q" placeholder="Szukaj…" aria-label="Szukaj" />
      </form>

      <?php require __DIR__ . '/_sidebar.php'; ?>

      <?php if (!empty($allCategories) && empty($sidebarData)): ?>
        <div class="pu-widget">
          <div class="pu-widget-title">Kategorie</div>
          <?php foreach ($allCategories as $_cat): ?>
            <a href="<?= pu_cat_url($basePath, $_cat['slug'], $prettyUrls ?? false) ?>"
               style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--pu-border);font-size:.84rem;color:var(--pu-text);text-decoration:none;transition:color .15s"
               onmouseover="this.style.color='var(--pu-primary)'" onmouseout="this.style.color='var(--pu-text)'">
              <span style="display:flex;align-items:center;gap:8px">
                <span style="width:7px;height:7px;border-radius:50%;background:<?= htmlspecialchars($_cat['color'] ?: 'var(--pu-primary)', ENT_QUOTES) ?>;flex-shrink:0"></span>
                <?= htmlspecialchars($_cat['name']) ?>
              </span>
              <span style="font-size:.7rem;color:var(--pu-muted);background:var(--pu-border);padding:1px 8px;border-radius:99px"><?= (int)$_cat['post_count'] ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </aside>
  </div>
</div>

<!-- FOOTER -->
<footer class="pu-footer">
  <div class="pu-footer-inner">
    <div class="pu-footer-brand">
      <div class="pu-footer-brand-name"><?= htmlspecialchars($homeTitle) ?></div>
      <?php if (!empty($homeSubtitle)): ?>
        <div class="pu-footer-brand-desc"><?= htmlspecialchars($homeSubtitle) ?></div>
      <?php endif; ?>
    </div>
    <?php if (!empty($navPages) || !empty($contactEnabled)): ?>
      <div class="pu-footer-nav">
        <h5>Strony</h5>
        <ul>
          <li><a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a></li>
          <?php foreach (($navPages ?? []) as $_np): ?>
            <li><a href="<?= pu_page_url($basePath, $_np['slug']) ?>"><?= htmlspecialchars($_np['title']) ?></a></li>
          <?php endforeach; ?>
          <?php if (!empty($contactEnabled)): ?>
            <li><a href="<?= pu_page_url($basePath, 'contact') ?>">Kontakt</a></li>
          <?php endif; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>
  <?php if (!empty($homeFooter)): ?>
    <div style="max-width:1280px;margin:0 auto;padding:16px 20px;font-size:.78rem;color:var(--pu-muted)"><?= $homeFooter ?></div>
  <?php endif; ?>
  <div class="pu-footer-copy">
    <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>. Wszystkie prawa zastrzeżone.</span>
    <span>
      Powered by <a href="https://redirectcms.pl" target="_blank" rel="noopener noreferrer">RedirectCMS</a>
      &middot; Theme: <a href="https://github.com/blogger-templates/Plus-UI-V3.7.0" target="_blank" rel="noopener noreferrer">Plus UI</a>
    </span>
  </div>
</footer>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
<script>
(function () {
  /* Dark mode toggle */
  var toggle = document.getElementById('puDarkToggle');
  if (toggle) {
    toggle.addEventListener('click', function () {
      var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      var next = isDark ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      localStorage.setItem('pu-theme', next);
    });
  }

  /* Search overlay */
  var overlay = document.getElementById('puSearchOverlay');
  var searchInput = document.getElementById('puSearchInput');
  function openSearch() { overlay.classList.add('open'); if (searchInput) setTimeout(function(){ searchInput.focus(); }, 50); }
  function closeSearch() { overlay.classList.remove('open'); }
  document.getElementById('puSearchOpen').addEventListener('click', openSearch);
  document.getElementById('puSearchClose').addEventListener('click', closeSearch);
  overlay.addEventListener('click', function (e) { if (e.target === overlay) closeSearch(); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeSearch();
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); openSearch(); }
  });
}());
</script>
</body>
</html>
