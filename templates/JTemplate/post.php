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
function jt_date(string $dt): string {
    return date('d.m.Y', strtotime($dt));
}
function jt_excerpt(string $html, int $len): string {
    $text = strip_tags($html);
    if ($len > 0 && mb_strlen($text) > $len) return mb_substr($text, 0, $len) . '…';
    return $text;
}
?>
<!doctype html>
<html lang="pl">
<head>
  <!-- Dark mode init — must be FIRST to prevent FOUC -->
  <script>
  (function(){
    var t=localStorage.getItem('jt-theme')||(window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');
    if(t==='dark')document.documentElement.setAttribute('data-theme','dark');
  }());
  </script>

  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title><?= htmlspecialchars(!empty($postMetaTitle) ? $postMetaTitle : $postTitle) ?> — <?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($postMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars($postMetaDescription) ?>"/>
  <?php endif; ?>
  <meta property="og:type"  content="article"/>
  <meta property="og:title" content="<?= htmlspecialchars($postTitle) ?>"/>
  <?php if (!empty($postMetaDescription)): ?>
    <meta property="og:description" content="<?= htmlspecialchars($postMetaDescription) ?>"/>
  <?php endif; ?>
  <?php if (!empty($ogImageUrl)): ?>
    <meta property="og:image"        content="<?= htmlspecialchars($ogImageUrl) ?>"/>
    <meta property="og:image:width"  content="900"/>
    <meta property="og:image:height" content="506"/>
  <?php endif; ?>
  <meta property="og:url" content="<?= htmlspecialchars($shareUrl ?? '') ?>"/>
  <?php if (!empty($postPublishedAt)): ?>
    <meta property="article:published_time" content="<?= htmlspecialchars($postPublishedAt) ?>"/>
  <?php endif; ?>
  <meta name="twitter:card"  content="summary_large_image"/>
  <meta name="twitter:title" content="<?= htmlspecialchars($postTitle) ?>"/>
  <?php if (!empty($postMetaDescription)): ?>
    <meta name="twitter:description" content="<?= htmlspecialchars($postMetaDescription) ?>"/>
  <?php endif; ?>
  <?php if (!empty($ogImageUrl)): ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($ogImageUrl) ?>"/>
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet"/>
  <?php if (!empty($lightboxEnabled)): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css"/>
  <?php endif; ?>
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
      font-family: var(--jt-font); font-size: 15px; color: var(--jt-text);
      background: var(--jt-bg); -webkit-font-smoothing: antialiased;
      transition: background .25s, color .25s;
    }
    a { color: inherit; text-decoration: none; }
    img { display: block; max-width: 100%; }

    /* ── READING PROGRESS BAR ──────────────────────────────────────── */
    #jtProgress {
      position: fixed; top: 0; left: 0; z-index: 9999;
      height: 3px; width: 0; background: var(--jt-accent);
      transition: width .05s linear;
    }

    /* ── HEADER ─────────────────────────────────────────────────────── */
    .jt-header {
      position: sticky; top: 0; z-index: 300;
      background: var(--jt-header); border-bottom: 1px solid var(--jt-border);
      box-shadow: 0 2px 8px rgba(0,0,0,.06); transition: background .25s, border-color .25s;
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
    .jt-nav { flex: 1; display: flex; align-items: center; gap: 2px; overflow: hidden; }
    .jt-nav a {
      font-size: .82rem; font-weight: 500; padding: 6px 12px;
      border-radius: 6px; white-space: nowrap; color: var(--jt-text);
      transition: background .15s, color .15s;
    }
    .jt-nav a:hover { color: var(--jt-accent); }
    .jt-header-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
    .jt-icon-btn {
      width: 38px; height: 38px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      border: none; background: none; cursor: pointer;
      color: var(--jt-muted); transition: background .15s, color .15s;
    }
    .jt-icon-btn:hover { background: var(--jt-border); color: var(--jt-text); }
    .jt-sun-icon { display: none; }
    [data-theme="dark"] .jt-moon-icon { display: none; }
    [data-theme="dark"] .jt-sun-icon  { display: block; }
    .jt-hamburger { display: none; }
    @media (max-width: 900px) {
      .jt-nav { display: none; }
      .jt-hamburger { display: flex; }
      .jt-header-inner { padding: 0 16px; }
    }

    /* ── MOBILE MENU ────────────────────────────────────────────────── */
    .jt-mobile-overlay {
      display: none; position: fixed; inset: 0; z-index: 400;
      background: rgba(0,0,0,.45); opacity: 0; transition: opacity .25s;
    }
    .jt-mobile-overlay.open { opacity: 1; }
    .jt-mobile-drawer {
      position: fixed; top: 0; right: -280px; z-index: 500;
      width: 280px; height: 100dvh; background: var(--jt-card);
      box-shadow: -6px 0 24px rgba(0,0,0,.15);
      transition: right .28s cubic-bezier(.4,0,.2,1);
      display: flex; flex-direction: column; overflow-y: auto;
    }
    .jt-mobile-drawer.open { right: 0; }
    .jt-drawer-head {
      display: flex; align-items: center; justify-content: space-between;
      padding: 16px 20px; border-bottom: 1px solid var(--jt-border);
    }
    .jt-drawer-head span { font-size: 1rem; font-weight: 700; }
    .jt-drawer-nav { padding: 12px 0; }
    .jt-drawer-nav a {
      display: block; padding: 11px 22px; font-size: .9rem; font-weight: 500;
      color: var(--jt-text); border-left: 3px solid transparent;
      transition: color .15s, border-color .15s, background .15s;
    }
    .jt-drawer-nav a:hover {
      color: var(--jt-accent); border-left-color: var(--jt-accent);
      background: color-mix(in srgb, var(--jt-accent) 6%, transparent);
    }

    /* ── LAYOUT ─────────────────────────────────────────────────────── */
    .jt-container {
      max-width: 1280px; margin: 0 auto; padding: 36px 24px 56px;
    }
    .jt-post-layout { display: flex; gap: 36px; align-items: flex-start; }
    .jt-post-col { flex: 1; min-width: 0; }
    .jt-post-aside {
      width: 300px; flex-shrink: 0;
      position: sticky; top: 84px;
      max-height: calc(100vh - 100px); overflow-y: auto;
    }
    .jt-post-aside::-webkit-scrollbar { width: 4px; }
    .jt-post-aside::-webkit-scrollbar-thumb { background: var(--jt-border); border-radius: 99px; }
    @media (max-width: 1024px) { .jt-post-aside { display: none; } }
    @media (max-width: 600px) { .jt-container { padding: 20px 16px 40px; } }

    /* ── POST CARD (wrapper) ──────────────────────────────────────────── */
    .jt-post-card {
      background: var(--jt-card); border-radius: var(--jt-radius);
      box-shadow: var(--jt-shadow); overflow: hidden;
    }

    /* ── HERO IMAGE ─────────────────────────────────────────────────── */
    .jt-post-hero {
      width: 100%; aspect-ratio: 16/9;
      overflow: hidden; position: relative;
      background: var(--jt-dark);
    }
    .jt-post-hero img {
      width: 100%; height: 100%; object-fit: cover;
    }
    .jt-post-hero-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(to top, rgba(0,0,0,.55) 0%, transparent 50%);
    }

    /* ── POST HEADER ─────────────────────────────────────────────────── */
    .jt-post-header { padding: 28px 36px 0; }
    @media (max-width: 600px) { .jt-post-header { padding: 20px 18px 0; } }
    .jt-post-cat {
      display: inline-block; padding: 3px 14px;
      font-size: .68rem; font-weight: 700; letter-spacing: .06em;
      text-transform: uppercase; border-radius: 4px;
      color: #fff; background: var(--jt-accent);
      margin-bottom: 14px; transition: background .2s;
    }
    .jt-post-cat:hover { background: var(--jt-dark); }
    .jt-post-title {
      font-size: clamp(1.45rem, 3.5vw, 2.1rem); font-weight: 700;
      line-height: 1.28; color: var(--jt-text); margin: 0 0 16px;
      letter-spacing: -.01em;
    }
    .jt-post-meta {
      display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
      font-size: .77rem; color: var(--jt-muted); padding-bottom: 20px;
      border-bottom: 1px solid var(--jt-border);
    }
    .jt-post-meta-item { display: flex; align-items: center; gap: 5px; }

    /* ── POST BODY ──────────────────────────────────────────────────── */
    .jt-post-body {
      padding: 28px 36px;
      font-family: 'Merriweather', Georgia, serif;
      font-size: .975rem; line-height: 1.9; color: var(--jt-text);
    }
    @media (max-width: 600px) { .jt-post-body { padding: 20px 18px; font-size: .92rem; } }
    .jt-post-body h2 {
      font-family: var(--jt-font); font-size: 1.35rem; font-weight: 700;
      margin: 32px 0 12px; color: var(--jt-text);
    }
    .jt-post-body h3 {
      font-family: var(--jt-font); font-size: 1.1rem; font-weight: 600;
      margin: 24px 0 10px; color: var(--jt-text);
    }
    .jt-post-body p { margin: 0 0 20px; }
    .jt-post-body a { color: var(--jt-accent); text-decoration: underline; text-underline-offset: 3px; }
    .jt-post-body a:hover { opacity: .8; }
    .jt-post-body img { max-width: 100%; border-radius: 8px; margin: 20px 0; }
    .jt-post-body blockquote {
      border-left: 4px solid var(--jt-accent);
      background: color-mix(in srgb, var(--jt-accent) 6%, transparent);
      padding: 14px 22px; margin: 24px 0;
      border-radius: 0 8px 8px 0; font-style: italic; color: var(--jt-muted);
    }
    .jt-post-body ul, .jt-post-body ol { padding-left: 22px; margin-bottom: 20px; }
    .jt-post-body li { margin-bottom: 6px; }
    .jt-post-body pre {
      background: var(--jt-dark); color: #f0f0f0;
      padding: 16px 20px; border-radius: 8px; overflow-x: auto;
      font-size: .85rem; margin: 20px 0;
    }
    [data-theme="dark"] .jt-post-body pre { background: #111; }

    /* ── TAGS ───────────────────────────────────────────────────────── */
    .jt-post-tags {
      padding: 0 36px 24px; display: flex; gap: 6px; flex-wrap: wrap; align-items: center;
    }
    @media (max-width: 600px) { .jt-post-tags { padding: 0 18px 18px; } }
    .jt-post-tags-label { font-size: .75rem; font-weight: 700; color: var(--jt-muted); margin-right: 4px; }
    .jt-tag {
      display: inline-block; padding: 4px 12px; border-radius: 99px;
      font-size: .72rem; font-weight: 600;
      background: color-mix(in srgb, var(--jt-accent) 10%, transparent);
      color: var(--jt-accent); border: 1px solid color-mix(in srgb, var(--jt-accent) 25%, transparent);
      transition: background .15s, color .15s;
    }
    .jt-tag:hover { background: var(--jt-accent); color: #fff; }

    /* ── SHARE BAR ──────────────────────────────────────────────────── */
    .jt-share {
      padding: 16px 36px; border-top: 1px solid var(--jt-border);
      display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    @media (max-width: 600px) { .jt-share { padding: 14px 18px; } }
    .jt-share-label { font-size: .75rem; font-weight: 700; color: var(--jt-muted); }
    .jt-share-btn {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 6px 14px; border-radius: 6px; font-size: .74rem; font-weight: 600;
      border: 1.5px solid var(--jt-border); color: var(--jt-text);
      transition: border-color .15s, color .15s, background .15s;
      cursor: pointer; background: none; font-family: var(--jt-font);
    }
    .jt-share-btn:hover { border-color: var(--jt-accent); color: var(--jt-accent); }

    /* ── GALLERY ────────────────────────────────────────────────────── */
    .jt-gallery {
      padding: 0 36px 28px;
    }
    @media (max-width: 600px) { .jt-gallery { padding: 0 18px 18px; } }
    .jt-gallery-title {
      font-size: .78rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: .07em; color: var(--jt-text); margin-bottom: 12px;
      padding-bottom: 10px; border-bottom: 2px solid var(--jt-accent);
    }
    .jt-gallery-grid {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px;
    }
    .jt-gallery-grid a { display: block; overflow: hidden; border-radius: 6px; aspect-ratio: 3/2; }
    .jt-gallery-grid img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
    .jt-gallery-grid a:hover img { transform: scale(1.08); }

    /* ── REDIRECT BUTTON ─────────────────────────────────────────────── */
    .jt-redirect-wrap { padding: 16px 36px 24px; }
    @media (max-width: 600px) { .jt-redirect-wrap { padding: 14px 18px 18px; } }
    .jt-redirect-btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 12px 28px; border-radius: 8px;
      background: var(--jt-accent); color: #fff;
      font-family: var(--jt-font); font-size: .85rem; font-weight: 600;
      transition: background .2s, transform .15s; letter-spacing: .02em;
    }
    .jt-redirect-btn:hover { background: var(--jt-dark); transform: translateY(-2px); }

    /* ── RELATED POSTS ──────────────────────────────────────────────── */
    .jt-related { margin-top: 36px; }
    .jt-section-title {
      font-size: .95rem; font-weight: 700; color: var(--jt-text); margin: 0 0 20px;
      padding-bottom: 10px; border-bottom: 2px solid var(--jt-accent);
      display: flex; align-items: center; gap: 8px;
    }
    .jt-section-title::before {
      content: ''; display: block; width: 4px; height: 18px;
      background: var(--jt-accent); border-radius: 2px;
    }
    .jt-related-grid {
      display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;
    }
    @media (max-width: 800px) { .jt-related-grid { grid-template-columns: 1fr; } }
    .jt-rel-card {
      background: var(--jt-card); border-radius: var(--jt-radius);
      box-shadow: var(--jt-shadow); overflow: hidden;
      transition: transform .25s, box-shadow .25s;
    }
    .jt-rel-card:hover { transform: translateY(-5px); box-shadow: var(--jt-shadow-h); }
    .jt-rel-thumb {
      display: block; aspect-ratio: 16/9; overflow: hidden;
      background: var(--jt-border);
    }
    .jt-rel-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
    .jt-rel-card:hover .jt-rel-thumb img { transform: scale(1.06); }
    .jt-rel-body { padding: 14px; }
    .jt-rel-title {
      font-size: .83rem; font-weight: 700; line-height: 1.4; color: var(--jt-text);
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
      overflow: hidden; transition: color .15s;
    }
    .jt-rel-card:hover .jt-rel-title { color: var(--jt-accent); }
    .jt-rel-date { font-size: .7rem; color: var(--jt-muted); margin-top: 6px; }

    /* ── SIDEBAR WIDGET ──────────────────────────────────────────────── */
    .jt-widget {
      background: var(--jt-card); border-radius: var(--jt-radius);
      box-shadow: 0 4px 20px rgba(0,0,0,.05); padding: 20px; margin-bottom: 20px;
      border: 1px solid var(--jt-border);
    }
    .jt-widget-title {
      font-size: .78rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: .07em; color: var(--jt-text); margin-bottom: 14px;
      padding-bottom: 10px; border-bottom: 2px solid var(--jt-accent);
    }

    /* ── FOOTER ──────────────────────────────────────────────────────── */
    .jt-footer {
      background: var(--jt-dark); color: rgba(255,255,255,.7);
      padding: 48px 0 0; margin-top: 40px;
    }
    [data-theme="dark"] .jt-footer { background: #111; border-top: 1px solid var(--jt-border); }
    .jt-footer-inner {
      max-width: 1280px; margin: 0 auto; padding: 0 24px 32px;
      display: flex; gap: 40px; flex-wrap: wrap;
    }
    .jt-footer-brand { flex: 1.5; min-width: 220px; }
    .jt-footer-brand .name { font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 10px; }
    .jt-footer-brand p { font-size: .82rem; line-height: 1.7; margin: 0; max-width: 320px; }
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
      border-top: 1px solid rgba(255,255,255,.1); padding: 14px 24px;
      display: flex; justify-content: space-between; align-items: center;
      flex-wrap: wrap; gap: 8px; font-size: .73rem; color: rgba(255,255,255,.45);
    }
    .jt-footer-copy a { color: var(--jt-accent); }
    @media (max-width: 600px) {
      .jt-footer-inner { padding: 0 16px 24px; gap: 24px; }
      .jt-footer-copy { padding: 12px 16px; flex-direction: column; text-align: center; }
    }

    /* ── BREADCRUMB ─────────────────────────────────────────────────── */
    .jt-breadcrumb {
      background: var(--jt-card); border-bottom: 1px solid var(--jt-border);
    }
    .jt-breadcrumb-inner {
      max-width: 1280px; margin: 0 auto; padding: 10px 24px;
      display: flex; align-items: center; gap: 6px; font-size: .73rem; color: var(--jt-muted);
    }
    .jt-breadcrumb-inner a { color: var(--jt-accent); }
    .jt-breadcrumb-inner a:hover { text-decoration: underline; }
  </style>
</head>
<body>

<div id="jtProgress"></div>

<!-- Mobile overlay & drawer -->
<div class="jt-mobile-overlay" id="jtOverlay"></div>
<div class="jt-mobile-drawer" id="jtDrawer">
  <div class="jt-drawer-head">
    <span><?= htmlspecialchars($homeTitle) ?></span>
    <button class="jt-icon-btn" id="jtDrawerClose" aria-label="Zamknij">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <nav class="jt-drawer-nav">
    <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
    <?php foreach (($navPages ?? []) as $_np): ?>
      <a href="<?= jt_page_url($basePath, $_np['slug']) ?>"><?= htmlspecialchars($_np['title']) ?></a>
    <?php endforeach; ?>
    <?php if (!empty($contactEnabled)): ?>
      <a href="<?= jt_page_url($basePath, 'contact') ?>">Kontakt</a>
    <?php endif; ?>
  </nav>
</div>

<!-- Header -->
<header class="jt-header">
  <div class="jt-header-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="jt-logo">
      <?php if (!empty($brandingLogo)): ?>
        <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($homeTitle) ?>"/>
      <?php endif; ?>
      <span><?= htmlspecialchars($homeTitle) ?></span>
    </a>
    <nav class="jt-nav">
      <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
      <?php foreach (($navPages ?? []) as $_np): ?>
        <a href="<?= jt_page_url($basePath, $_np['slug']) ?>"><?= htmlspecialchars($_np['title']) ?></a>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= jt_page_url($basePath, 'contact') ?>">Kontakt</a>
      <?php endif; ?>
    </nav>
    <div class="jt-header-actions">
      <button class="jt-icon-btn" id="jtDarkToggle" aria-label="Tryb ciemny">
        <svg class="jt-moon-icon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="jt-sun-icon"  width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <button class="jt-icon-btn jt-hamburger" id="jtHamburger" aria-label="Menu">
        <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
    </div>
  </div>
</header>

<!-- Breadcrumb -->
<div class="jt-breadcrumb">
  <div class="jt-breadcrumb-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
    <?php if (!empty($link['category'])): ?>
      <span>›</span>
      <a href="<?= jt_cat_url($basePath, $link['category']['slug'], $prettyUrls ?? false) ?>">
        <?= htmlspecialchars($link['category']['name']) ?>
      </a>
    <?php endif; ?>
    <span>›</span>
    <span><?= htmlspecialchars(mb_strimwidth($postTitle, 0, 55, '…')) ?></span>
  </div>
</div>

<div class="jt-container">
  <div class="jt-post-layout">
    <!-- Main column -->
    <article class="jt-post-col">
      <div class="jt-post-card">

        <!-- Hero image -->
        <?php if (!empty($ogImageUrl)): ?>
          <div class="jt-post-hero">
            <img src="<?= htmlspecialchars($basePath . '/uploads/cropped/post/' . basename($link['og_image'] ?? ''), ENT_QUOTES) ?>"
                 onerror="this.src='<?= htmlspecialchars($ogImageUrl, ENT_QUOTES) ?>'"
                 alt="<?= htmlspecialchars($postTitle) ?>"/>
            <div class="jt-post-hero-overlay"></div>
          </div>
        <?php endif; ?>

        <!-- Post header -->
        <div class="jt-post-header">
          <?php if (!empty($link['category'])): ?>
            <a href="<?= jt_cat_url($basePath, $link['category']['slug'], $prettyUrls ?? false) ?>"
               class="jt-post-cat"
               style="background:<?= htmlspecialchars($link['category']['color'] ?: 'var(--jt-accent)', ENT_QUOTES) ?>">
              <?= htmlspecialchars($link['category']['name']) ?>
            </a>
          <?php endif; ?>
          <h1 class="jt-post-title"><?= htmlspecialchars($postTitle) ?></h1>
          <div class="jt-post-meta">
            <span class="jt-post-meta-item">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <?= jt_date($postCreatedAt) ?>
            </span>
            <?php if (!empty($link['click_count'])): ?>
              <span class="jt-post-meta-item">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <?= (int)$link['click_count'] ?> odsłon
              </span>
            <?php endif; ?>
            <?php if (!empty($link['category'])): ?>
              <span class="jt-post-meta-item">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                <a href="<?= jt_cat_url($basePath, $link['category']['slug'], $prettyUrls ?? false) ?>"
                   style="color:var(--jt-accent)">
                  <?= htmlspecialchars($link['category']['name']) ?>
                </a>
              </span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Post body -->
        <div class="jt-post-body">
          <?= Utils::sanitizeHtml($postDescription ?? '') ?>
        </div>

        <!-- Reactions -->
        <?php require __DIR__ . '/../../src/PostReactions.php'; ?>

        <!-- Redirect button -->
        <?php if (!empty($link['target_url'])): ?>
          <div class="jt-redirect-wrap">
            <a href="<?= htmlspecialchars($directLinkUrl, ENT_QUOTES) ?>"
               class="jt-redirect-btn">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              Sprawdź ofertę
            </a>
          </div>
        <?php endif; ?>

        <!-- Gallery -->
        <?php if (!empty($galleryImages)): ?>
          <div class="jt-gallery">
            <div class="jt-gallery-title">Galeria</div>
            <div class="jt-gallery-grid">
              <?php foreach ($galleryImages as $_gi): ?>
                <?php
                $_full  = htmlspecialchars($basePath . '/' . ltrim($_gi['path'], '/'), ENT_QUOTES);
                $_thumb = !empty($_gi['url']) ? htmlspecialchars($_gi['url'], ENT_QUOTES) : $_full;
                ?>
                <?php if (!empty($lightboxEnabled)): ?>
                  <a href="<?= $_full ?>" data-lightbox="post-gallery" data-title="<?= htmlspecialchars($postTitle) ?>">
                    <img src="<?= $_thumb ?>" alt="" loading="lazy"/>
                  </a>
                <?php else: ?>
                  <a href="<?= $_full ?>" target="_blank" rel="noopener noreferrer">
                    <img src="<?= $_thumb ?>" alt="" loading="lazy"/>
                  </a>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Tags -->
        <?php if (!empty($link['tags'])): ?>
          <div class="jt-post-tags">
            <span class="jt-post-tags-label">Tagi:</span>
            <?php foreach ($link['tags'] as $_t): ?>
              <a href="<?= jt_tag_url($basePath, $_t['slug'], $prettyUrls ?? false) ?>" class="jt-tag">
                #<?= htmlspecialchars($_t['name']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Share -->
        <div class="jt-share">
          <span class="jt-share-label">Udostępnij:</span>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl ?? '') ?>"
             target="_blank" rel="noopener noreferrer" class="jt-share-btn">
            <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/></svg>
            Facebook
          </a>
          <a href="https://twitter.com/intent/tweet?text=<?= urlencode($postTitle) ?>&url=<?= urlencode($shareUrl ?? '') ?>"
             target="_blank" rel="noopener noreferrer" class="jt-share-btn">
            <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>
            Twitter/X
          </a>
          <button class="jt-share-btn" id="jtCopyLink">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            Kopiuj link
          </button>
        </div>

      </div><!-- /.jt-post-card -->

      <!-- Related posts -->
      <?php if (!empty($relatedPosts)): ?>
        <div class="jt-related">
          <div class="jt-section-title">Podobne wpisy</div>
          <div class="jt-related-grid">
            <?php foreach ($relatedPosts as $_rp): ?>
              <article class="jt-rel-card">
                <a href="<?= jt_post_url($basePath, $_rp['slug'], $prettyUrls ?? false) ?>" class="jt-rel-thumb">
                  <?php if (!empty($_rp['og_image'])): ?>
                    <img src="<?= htmlspecialchars($basePath . '/' . $_rp['og_image'], ENT_QUOTES) ?>"
                         alt="<?= htmlspecialchars($_rp['page_title']) ?>" loading="lazy"/>
                  <?php else: ?>
                    <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--jt-accent),var(--jt-dark))"></div>
                  <?php endif; ?>
                </a>
                <div class="jt-rel-body">
                  <div class="jt-rel-title">
                    <a href="<?= jt_post_url($basePath, $_rp['slug'], $prettyUrls ?? false) ?>">
                      <?= htmlspecialchars($_rp['page_title']) ?>
                    </a>
                  </div>
                  <div class="jt-rel-date"><?= jt_date($_rp['created_at']) ?></div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </article><!-- /.jt-post-col -->

    <!-- Sidebar -->
    <?php if (!empty($sidebarData)): ?>
      <aside class="jt-post-aside">
        <?php require __DIR__ . '/_sidebar.php'; ?>
      </aside>
    <?php endif; ?>

  </div><!-- /.jt-post-layout -->
</div><!-- /.jt-container -->

<!-- Footer -->
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

<?php if (!empty($lightboxEnabled)): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<?php endif; ?>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>

<script>
(function () {
  // Dark mode
  document.getElementById('jtDarkToggle').addEventListener('click', function () {
    var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('jt-theme', next);
  });

  // Reading progress
  window.addEventListener('scroll', function () {
    var docH  = document.documentElement.scrollHeight - window.innerHeight;
    var pct   = docH > 0 ? (window.scrollY / docH) * 100 : 0;
    document.getElementById('jtProgress').style.width = pct + '%';
  }, { passive: true });

  // Copy link
  var copyBtn = document.getElementById('jtCopyLink');
  if (copyBtn) {
    copyBtn.addEventListener('click', function () {
      navigator.clipboard.writeText(window.location.href).then(function () {
        var orig = copyBtn.textContent;
        copyBtn.textContent = '✓ Skopiowano!';
        setTimeout(function () { copyBtn.textContent = orig; }, 2000);
      });
    });
  }

  // Mobile hamburger
  var hamburger   = document.getElementById('jtHamburger');
  var drawer      = document.getElementById('jtDrawer');
  var overlay     = document.getElementById('jtOverlay');
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
