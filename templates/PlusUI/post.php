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
  <title><?= htmlspecialchars(!empty($postMetaTitle) ? $postMetaTitle : $postTitle) ?> — <?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($postMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars($postMetaDescription) ?>" />
  <?php endif; ?>
  <meta property="og:type"  content="article" />
  <meta property="og:title" content="<?= htmlspecialchars($postTitle) ?>" />
  <?php if (!empty($postMetaDescription)): ?>
    <meta property="og:description" content="<?= htmlspecialchars($postMetaDescription) ?>" />
  <?php endif; ?>
  <?php if (!empty($ogImageUrl)): ?>
    <meta property="og:image"        content="<?= htmlspecialchars($ogImageUrl) ?>" />
    <meta property="og:image:width"  content="800" />
    <meta property="og:image:height" content="450" />
  <?php endif; ?>
  <meta property="og:url" content="<?= htmlspecialchars($shareUrl ?? '') ?>" />
  <?php if (!empty($postPublishedAt)): ?>
    <meta property="article:published_time" content="<?= htmlspecialchars($postPublishedAt) ?>" />
  <?php endif; ?>
  <meta name="twitter:card"  content="summary_large_image" />
  <meta name="twitter:title" content="<?= htmlspecialchars($postTitle) ?>" />
  <?php if (!empty($postMetaDescription)): ?>
    <meta name="twitter:description" content="<?= htmlspecialchars($postMetaDescription) ?>" />
  <?php endif; ?>
  <?php if (!empty($ogImageUrl)): ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($ogImageUrl) ?>" />
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet" />
  <?php if (!empty($lightboxEnabled)): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" />
  <?php endif; ?>
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

    /* ─── READING PROGRESS BAR ────────────────────────────────────── */
    #puProgress {
      position: fixed; top: 0; left: 0; z-index: 9999;
      height: 3px; width: 0%; background: var(--pu-primary);
      transition: width .08s linear;
    }

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
      flex-shrink: 0; text-decoration: none; transition: color .2s;
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
    .pu-header-actions { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }
    .pu-icon-btn {
      width: 36px; height: 36px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      background: none; border: none; cursor: pointer;
      color: var(--pu-muted); transition: background .15s, color .15s;
    }
    .pu-icon-btn:hover { background: var(--pu-border); color: var(--pu-text); }
    .pu-sun-icon { display: none; }
    [data-theme="dark"] .pu-moon-icon { display: none; }
    [data-theme="dark"] .pu-sun-icon  { display: block; }
    @media (max-width: 767px) {
      .pu-nav { display: none; }
      .pu-header-inner { padding: 0 16px; }
    }

    /* ─── SEARCH OVERLAY ──────────────────────────────────────────── */
    .pu-search-overlay {
      position: fixed; inset: 0; z-index: 9998;
      background: rgba(0,0,0,.55);
      display: flex; align-items: flex-start; justify-content: center;
      padding-top: 90px;
      opacity: 0; visibility: hidden;
      transition: opacity .2s, visibility .2s;
    }
    .pu-search-overlay.open { opacity: 1; visibility: visible; }
    .pu-search-box {
      width: min(580px, 92vw); background: var(--pu-card);
      border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.35); overflow: hidden;
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
      font-size: 1.25rem; color: var(--pu-muted); padding: 4px; transition: color .15s;
    }
    .pu-search-close-btn:hover { color: var(--pu-text); }
    .pu-search-hint { padding: 10px 16px; font-size: .75rem; color: var(--pu-muted); }

    /* ─── POST LAYOUT ─────────────────────────────────────────────── */
    .pu-container {
      max-width: 1280px; margin: 0 auto;
      padding: 32px 20px 56px;
    }
    .pu-post-layout { display: flex; gap: 36px; align-items: flex-start; }
    .pu-post-col { flex: 1; min-width: 0; max-width: 780px; }
    .pu-post-aside {
      width: 280px; flex-shrink: 0;
      position: sticky; top: 80px;
      max-height: calc(100vh - 100px); overflow-y: auto;
    }
    .pu-post-aside::-webkit-scrollbar { width: 4px; }
    .pu-post-aside::-webkit-scrollbar-thumb { background: var(--pu-border); border-radius: 99px; }
    @media (max-width: 1023px) { .pu-post-aside { display: none; } .pu-post-col { max-width: 100%; } }
    @media (max-width: 767px) { .pu-container { padding: 20px 16px 40px; } }

    /* ─── POST HEADER ─────────────────────────────────────────────── */
    .pu-post-cat {
      display: inline-block; font-size: .72rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: .05em;
      color: var(--pu-primary); margin-bottom: 12px;
      text-decoration: none; transition: color .15s;
    }
    .pu-post-cat:hover { color: var(--pu-accent); }
    .pu-post-title {
      font-size: 2rem; font-weight: 700; line-height: 1.25;
      color: var(--pu-text); margin: 0 0 16px; letter-spacing: -.02em;
    }
    @media (max-width: 576px) { .pu-post-title { font-size: 1.5rem; } }
    .pu-post-meta-row {
      display: flex; align-items: center; gap: 12px;
      margin-bottom: 20px; flex-wrap: wrap;
    }
    .pu-post-avatar {
      width: 40px; height: 40px; border-radius: 50%;
      background: var(--pu-primary); color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: .95rem; flex-shrink: 0;
    }
    .pu-post-author-block { flex: 1; min-width: 0; }
    .pu-post-author-name { font-size: .88rem; font-weight: 600; }
    .pu-post-author-meta {
      font-size: .76rem; color: var(--pu-muted); margin-top: 1px;
    }
    .pu-share-icons { display: flex; gap: 6px; margin-left: auto; }
    .pu-share-btn {
      width: 32px; height: 32px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      border: 1px solid var(--pu-border); color: var(--pu-muted);
      text-decoration: none; background: none; cursor: pointer;
      transition: border-color .15s, color .15s;
    }
    .pu-share-btn:hover { border-color: var(--pu-primary); color: var(--pu-primary); }

    /* ─── FEATURED IMAGE ──────────────────────────────────────────── */
    .pu-post-feat-img {
      width: 100%; max-height: 440px; object-fit: cover;
      border-radius: var(--pu-radius);
      margin: 20px 0 28px;
    }

    /* ─── POST BODY ───────────────────────────────────────────────── */
    .pu-post-body {
      font-family: 'Merriweather', Georgia, serif;
      font-size: 1.05rem; line-height: 1.9;
      color: var(--pu-text); margin-bottom: 36px;
    }
    @media (min-width: 768px) { .pu-post-body { font-size: 1.1rem; } }
    .pu-post-body h2, .pu-post-body h3, .pu-post-body h4 {
      font-family: 'Roboto', sans-serif;
      font-weight: 700; margin-top: 36px; margin-bottom: 12px;
      scroll-margin-top: 80px;
    }
    .pu-post-body h2 { font-size: 1.5rem; }
    .pu-post-body h3 { font-size: 1.2rem; }
    .pu-post-body h4 { font-size: 1rem; }
    .pu-post-body p { margin: 0 0 20px; }
    .pu-post-body img { max-width: 100%; border-radius: 6px; margin: 20px 0; }
    .pu-post-body a { color: var(--pu-primary); text-decoration: underline; text-underline-offset: 3px; }
    .pu-post-body blockquote {
      border-left: 4px solid var(--pu-primary);
      background: color-mix(in srgb, var(--pu-primary) 6%, transparent);
      padding: 12px 20px; margin: 24px 0;
      border-radius: 0 6px 6px 0; font-style: italic;
      color: var(--pu-muted);
    }
    .pu-post-body ul, .pu-post-body ol { padding-left: 22px; margin-bottom: 20px; }
    .pu-post-body li { margin-bottom: 6px; }
    .pu-post-body code {
      font-size: .875em; background: var(--pu-border);
      padding: 2px 6px; border-radius: 4px;
      font-family: 'SFMono-Regular', Consolas, monospace;
    }
    .pu-post-body pre {
      background: #1a1a2e; color: #e0e0ff; padding: 20px;
      border-radius: 8px; overflow-x: auto; margin: 24px 0;
    }
    [data-theme="dark"] .pu-post-body pre { background: #111; }
    .pu-post-body pre code { background: none; color: inherit; font-size: .875rem; padding: 0; }

    /* ─── POST CHIPS ──────────────────────────────────────────────── */
    .pu-post-chips {
      display: flex; flex-wrap: wrap; gap: 6px;
      margin-bottom: 28px; padding-bottom: 24px;
      border-bottom: 1px solid var(--pu-border);
    }
    .pu-tag-chip {
      display: inline-block; padding: 4px 12px; border-radius: 99px;
      font-size: .75rem; font-weight: 500;
      background: var(--pu-border); color: var(--pu-text);
      text-decoration: none; transition: background .15s, color .15s;
    }
    .pu-tag-chip:hover { background: var(--pu-primary); color: #fff; }

    /* ─── AUTHOR BOX ──────────────────────────────────────────────── */
    .pu-author-box {
      display: flex; align-items: center; gap: 16px;
      padding: 20px; border-radius: var(--pu-radius);
      background: var(--pu-card); border: 1px solid var(--pu-border);
      margin-bottom: 36px;
    }
    .pu-author-avatar-lg {
      width: 56px; height: 56px; border-radius: 50%;
      background: var(--pu-primary); color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 1.3rem; flex-shrink: 0;
    }
    .pu-author-name { font-size: .95rem; font-weight: 700; margin-bottom: 3px; }
    .pu-author-bio { font-size: .82rem; color: var(--pu-muted); line-height: 1.6; }

    /* ─── RELATED POSTS ───────────────────────────────────────────── */
    .pu-related { margin-bottom: 16px; }
    .pu-related-title {
      font-size: .75rem; font-weight: 700; letter-spacing: .07em;
      text-transform: uppercase; color: var(--pu-text);
      margin: 0 0 16px; padding-bottom: 10px;
      border-bottom: 2px solid var(--pu-primary);
    }
    .pu-related-grid {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;
    }
    .pu-related-card {
      text-decoration: none; color: var(--pu-text);
      background: var(--pu-card); border-radius: var(--pu-radius);
      overflow: hidden; border: 1px solid var(--pu-border);
      transition: box-shadow .2s, transform .2s;
    }
    .pu-related-card:hover { box-shadow: var(--pu-shadow-md); transform: translateY(-2px); }
    .pu-related-img { width: 100%; aspect-ratio: 16/9; object-fit: cover; background: var(--pu-border); display: block; }
    .pu-related-body { padding: 10px 12px 12px; }
    .pu-related-title-text {
      font-size: .84rem; font-weight: 700; line-height: 1.4;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
      margin-bottom: 4px; transition: color .15s;
    }
    .pu-related-card:hover .pu-related-title-text { color: var(--pu-primary); }
    .pu-related-meta { font-size: .72rem; color: var(--pu-muted); }

    /* ─── REDIRECT BOX ────────────────────────────────────────────── */
    .pu-redirect-box {
      display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
      padding: 16px 20px; margin: 28px 0;
      border-radius: var(--pu-radius); border: 1px solid var(--pu-border);
      border-left: 4px solid var(--pu-primary);
      background: var(--pu-card);
    }
    .pu-redirect-text { flex: 1; font-size: .88rem; color: var(--pu-muted); }
    .pu-redirect-btn {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: .84rem; font-weight: 600; color: var(--pu-primary);
      border: 1.5px solid var(--pu-primary);
      padding: 8px 18px; border-radius: 99px; white-space: nowrap;
      text-decoration: none; transition: background .15s, color .15s;
    }
    .pu-redirect-btn:hover { background: var(--pu-primary); color: #fff; }

    /* ─── GALLERY ─────────────────────────────────────────────────── */
    .pu-gallery-label {
      font-size: .75rem; font-weight: 700; letter-spacing: .07em;
      text-transform: uppercase; color: var(--pu-muted);
      margin: 32px 0 14px; padding-bottom: 10px;
      border-bottom: 1px solid var(--pu-border);
    }
    .pu-gallery-grid {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 8px; margin-bottom: 32px;
    }
    .pu-gthumb {
      display: block; aspect-ratio: 3/2; overflow: hidden;
      border-radius: 4px; background: var(--pu-border); cursor: zoom-in;
    }
    .pu-gthumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
    .pu-gthumb:hover img { transform: scale(1.06); }

    /* Custom gallery modal */
    .pu-gmodal {
      position: fixed; inset: 0; z-index: 9997;
      background: rgba(0,0,0,.95);
      display: flex; align-items: center; justify-content: center;
      opacity: 0; visibility: hidden; transition: opacity .2s, visibility .2s;
    }
    .pu-gmodal.open { opacity: 1; visibility: visible; }
    .pu-gmodal-img { max-width: min(92vw, 1100px); max-height: 88vh; object-fit: contain; }
    .pu-gmodal-close {
      position: absolute; top: 20px; right: 24px;
      background: none; border: none; color: rgba(255,255,255,.7);
      font-size: 1.6rem; cursor: pointer; line-height: 1;
    }
    .pu-gmodal-close:hover { color: #fff; }
    .pu-gmodal-nav {
      position: absolute; top: 50%; transform: translateY(-50%);
      background: rgba(255,255,255,.1); border: none; color: #fff;
      width: 44px; height: 44px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.3rem; cursor: pointer; transition: background .15s;
    }
    .pu-gmodal-nav:hover { background: rgba(255,255,255,.25); }
    .pu-gmodal-prev { left: 16px; }
    .pu-gmodal-next { right: 16px; }
    .pu-gmodal-nav.hidden { display: none; }
    .pu-gmodal-cap {
      position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%);
      color: rgba(255,255,255,.5); font-size: .73rem; letter-spacing: .04em; white-space: nowrap;
    }

    /* ─── SIDEBAR WIDGETS ─────────────────────────────────────────── */
    .pu-widget { margin-bottom: 24px; }
    .pu-widget-title {
      font-size: .75rem; font-weight: 700; letter-spacing: .07em;
      text-transform: uppercase; color: var(--pu-text);
      margin: 0 0 12px; padding-bottom: 10px;
      border-bottom: 2px solid var(--pu-primary);
    }
    /* TOC */
    #puTocWrap { display: none; }
    #puTocList { list-style: none; padding: 0; margin: 0; }
    #puTocList li { margin-bottom: 2px; }
    #puTocList a {
      display: block; font-size: .82rem; line-height: 1.5;
      color: var(--pu-muted); padding: 4px 8px; border-radius: 4px;
      text-decoration: none; transition: background .12s, color .12s;
    }
    #puTocList a:hover, #puTocList a.active {
      background: color-mix(in srgb, var(--pu-primary) 12%, transparent);
      color: var(--pu-primary);
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
  </style>
</head>
<body>

<!-- READING PROGRESS BAR -->
<div id="puProgress"></div>

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
function pu_date_post(string $dt): string {
    $ts = strtotime($dt);
    return $ts ? date('d F Y', $ts) : '';
}
function pu_read_time(string $html): int {
    return max(1, (int)ceil(str_word_count(strip_tags($html)) / 200));
}
$_shareUrl = htmlspecialchars($shareUrl ?? '', ENT_QUOTES);
$_firstLetter = mb_strtoupper(mb_substr($postTitle ?? 'P', 0, 1));
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

<!-- GALLERY MODAL (custom, when lightbox not enabled) -->
<?php if (empty($lightboxEnabled)): ?>
<div class="pu-gmodal" id="puGalleryModal" role="dialog" aria-modal="true">
  <button class="pu-gmodal-close" id="puGModalClose" aria-label="Zamknij">✕</button>
  <button class="pu-gmodal-nav pu-gmodal-prev hidden" id="puGModalPrev" aria-label="Poprzednie">&#8249;</button>
  <img class="pu-gmodal-img" id="puGModalImg" src="" alt="" />
  <button class="pu-gmodal-nav pu-gmodal-next hidden" id="puGModalNext" aria-label="Następne">&#8250;</button>
  <div class="pu-gmodal-cap" id="puGModalCap"></div>
</div>
<?php endif; ?>

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
        <a href="<?= pu_cat_url($basePath, $_c['slug'], $prettyUrls ?? false) ?>"><?= htmlspecialchars($_c['name']) ?></a>
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

<!-- POST CONTENT -->
<div class="pu-container" id="puArticle">
  <div class="pu-post-layout">
    <article class="pu-post-col">

      <!-- Category -->
      <?php if (!empty($_category)): ?>
        <a href="<?= pu_cat_url($basePath, $_category['slug'], $prettyUrls ?? false) ?>" class="pu-post-cat">
          <?= htmlspecialchars($_category['name']) ?>
        </a>
      <?php endif; ?>

      <!-- Title -->
      <h1 class="pu-post-title"><?= htmlspecialchars($postTitle) ?></h1>

      <!-- Meta row -->
      <div class="pu-post-meta-row">
        <div class="pu-post-avatar"><?= $_firstLetter ?></div>
        <div class="pu-post-author-block">
          <div class="pu-post-author-name"><?= htmlspecialchars($homeTitle) ?></div>
          <div class="pu-post-author-meta">
            <?= pu_date_post($postCreatedAt ?? '') ?>
            <?php if (!empty($postDescription)): ?>
              &nbsp;·&nbsp; <?= pu_read_time($postDescription) ?> min czytania
            <?php endif; ?>
          </div>
        </div>
        <!-- Share -->
        <div class="pu-share-icons">
          <a href="https://twitter.com/intent/tweet?url=<?= urlencode($shareUrl ?? '') ?>&text=<?= urlencode($postTitle ?? '') ?>"
             target="_blank" rel="noopener" class="pu-share-btn" title="X / Twitter">
            <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>
          </a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl ?? '') ?>"
             target="_blank" rel="noopener" class="pu-share-btn" title="Facebook">
            <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/></svg>
          </a>
          <button class="pu-share-btn" title="Kopiuj link" type="button"
                  onclick="navigator.clipboard&&navigator.clipboard.writeText('<?= htmlspecialchars($shareUrl ?? '', ENT_QUOTES) ?>').then(function(){this.style.color='var(--pu-primary)';setTimeout(function(){this.style.color='';}.bind(this),2e3)}.bind(this))">
            <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
          </button>
        </div>
      </div>

      <!-- Featured image -->
      <?php if (!empty($ogImageUrl)): ?>
        <img src="<?= pu_thumb($basePath, $link['og_image'] ?? '', 'post') ?>"
             alt="<?= htmlspecialchars($postTitle) ?>"
             class="pu-post-feat-img" loading="lazy" />
      <?php endif; ?>

      <!-- Reactions -->
      <?php require __DIR__ . '/../../src/PostReactions.php'; ?>

      <!-- Redirect box -->
      <?php if (!empty($link['target_url'])): ?>
        <div class="pu-redirect-box">
          <div class="pu-redirect-text">Przejdź do źródła</div>
          <a href="<?= htmlspecialchars($directLinkUrl, ENT_QUOTES) ?>" class="pu-redirect-btn">
            Sprawdź ofertę
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/></svg>
          </a>
        </div>
      <?php endif; ?>

      <!-- Post body -->
      <div class="pu-post-body" id="puPostBody">
        <?= Utils::sanitizeHtml($postDescription ?? '') ?>
      </div>

      <!-- Tags -->
      <?php if (!empty($_tags)): ?>
        <div class="pu-post-chips">
          <?php foreach ($_tags as $_tag): ?>
            <a href="<?= pu_tag_url($basePath, $_tag['slug'], $prettyUrls ?? false) ?>" class="pu-tag-chip">
              #<?= htmlspecialchars($_tag['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Gallery -->
      <?php if (!empty($galleryImages)): ?>
        <div class="pu-gallery-label">Galeria</div>
        <div class="pu-gallery-grid">
          <?php foreach ($galleryImages as $_gi): ?>
            <?php $_full  = htmlspecialchars($basePath . '/' . ltrim($_gi['path'] ?? '', '/'), ENT_QUOTES); ?>
            <?php $_thumb = pu_thumb($basePath, $_gi['path'] ?? '', 'gallery_thumb'); ?>
            <?php if (!empty($lightboxEnabled)): ?>
              <a href="<?= $_full ?>" data-lightbox="pu-gallery" data-title="<?= htmlspecialchars($postTitle) ?>" class="pu-gthumb">
                <img src="<?= $_thumb ?>" alt="<?= htmlspecialchars($postTitle) ?>" loading="lazy" />
              </a>
            <?php else: ?>
              <div class="pu-gthumb pu-gallery-trigger" data-full="<?= $_full ?>">
                <img src="<?= $_thumb ?>" alt="<?= htmlspecialchars($postTitle) ?>" loading="lazy" />
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Author box -->
      <div class="pu-author-box">
        <div class="pu-author-avatar-lg"><?= $_firstLetter ?></div>
        <div>
          <div class="pu-author-name"><?= htmlspecialchars($homeTitle) ?></div>
          <?php if (!empty($homeSubtitle)): ?>
            <div class="pu-author-bio"><?= htmlspecialchars($homeSubtitle) ?></div>
          <?php endif; ?>
          <div style="font-size:.75rem;color:var(--pu-muted);margin-top:4px">
            Opublikowano: <?= pu_date_post($postCreatedAt ?? '') ?>
          </div>
        </div>
      </div>

      <!-- Related posts -->
      <?php if (!empty($relatedPosts)): ?>
        <div class="pu-related">
          <div class="pu-related-title">Powiązane wpisy</div>
          <div class="pu-related-grid">
            <?php foreach (array_slice($relatedPosts, 0, 4) as $_rp): ?>
              <a href="<?= pu_post_url($basePath, $_rp['slug'], $prettyUrls ?? false) ?>" class="pu-related-card">
                <?php if (!empty($_rp['og_image'])): ?>
                  <img class="pu-related-img" src="<?= pu_thumb($basePath, $_rp['og_image'], 'listing') ?>"
                       alt="<?= htmlspecialchars($_rp['page_title']) ?>" loading="lazy" />
                <?php else: ?>
                  <div class="pu-related-img" style="background:linear-gradient(135deg,var(--pu-primary),var(--pu-accent))"></div>
                <?php endif; ?>
                <div class="pu-related-body">
                  <div class="pu-related-title-text"><?= htmlspecialchars($_rp['page_title']) ?></div>
                  <div class="pu-related-meta"><?= !empty($_rp['created_at']) ? date('d.m.Y', strtotime($_rp['created_at'])) : '' ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Back link -->
      <div style="margin-top:32px;padding-top:20px;border-top:1px solid var(--pu-border)">
        <a href="<?= htmlspecialchars($basePath) ?>/"
           style="font-size:.84rem;font-weight:600;color:var(--pu-primary);text-decoration:none;display:inline-flex;align-items:center;gap:6px">
          ← Wróć do strony głównej
        </a>
      </div>
    </article>

    <!-- POST SIDEBAR -->
    <aside class="pu-post-aside">
      <!-- Table of Contents (auto-generated) -->
      <div class="pu-widget" id="puTocWrap">
        <div class="pu-widget-title">Spis treści</div>
        <ul id="puTocList"></ul>
      </div>

      <!-- Search -->
      <form class="pu-search-sidebar" method="get" action="<?= htmlspecialchars($basePath) ?>/" role="search">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="q" placeholder="Szukaj…" aria-label="Szukaj" />
      </form>

      <?php require __DIR__ . '/_sidebar.php'; ?>

      <!-- Fallback categories -->
      <?php if (!empty($allCategories) && empty($sidebarData)): ?>
        <div class="pu-widget">
          <div class="pu-widget-title">Kategorie</div>
          <?php foreach ($allCategories as $_cat): ?>
            <a href="<?= pu_cat_url($basePath, $_cat['slug'], $prettyUrls ?? false) ?>"
               style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--pu-border);font-size:.84rem;color:var(--pu-text);text-decoration:none">
              <span style="display:flex;align-items:center;gap:7px">
                <span style="width:7px;height:7px;border-radius:50%;background:<?= htmlspecialchars($_cat['color'] ?: 'var(--pu-primary)', ENT_QUOTES) ?>"></span>
                <?= htmlspecialchars($_cat['name']) ?>
              </span>
              <span style="font-size:.7rem;color:var(--pu-muted);background:var(--pu-border);padding:1px 7px;border-radius:99px"><?= (int)$_cat['post_count'] ?></span>
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

<?php if (!empty($lightboxEnabled)): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
  <script>lightbox.option({resizeDuration:200,wrapAround:true,albumLabel:'%1 / %2'});</script>
<?php endif; ?>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
<script>
(function () {
  /* ── Dark mode ── */
  document.getElementById('puDarkToggle').addEventListener('click', function () {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var next = isDark ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('pu-theme', next);
  });

  /* ── Search overlay ── */
  var overlay = document.getElementById('puSearchOverlay');
  var si = document.getElementById('puSearchInput');
  function openSearch() { overlay.classList.add('open'); if (si) setTimeout(function(){ si.focus(); }, 50); }
  function closeSearch() { overlay.classList.remove('open'); }
  document.getElementById('puSearchOpen').addEventListener('click', openSearch);
  document.getElementById('puSearchClose').addEventListener('click', closeSearch);
  overlay.addEventListener('click', function (e) { if (e.target === overlay) closeSearch(); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeSearch();
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); openSearch(); }
  });

  /* ── Reading progress bar ── */
  var progressBar = document.getElementById('puProgress');
  var article = document.getElementById('puArticle');
  function updateProgress() {
    if (!progressBar || !article) return;
    var top = article.offsetTop;
    var height = article.offsetHeight - window.innerHeight;
    var progress = height > 0 ? Math.max(0, Math.min(((window.scrollY - top) / height) * 100, 100)) : 0;
    progressBar.style.width = progress + '%';
  }
  window.addEventListener('scroll', updateProgress, { passive: true });
  updateProgress();

  /* ── Table of Contents ── */
  (function buildToc() {
    var body = document.getElementById('puPostBody');
    var tocList = document.getElementById('puTocList');
    var tocWrap = document.getElementById('puTocWrap');
    if (!body || !tocList || !tocWrap) return;
    var headings = body.querySelectorAll('h2, h3');
    if (headings.length < 2) return;
    var html = '';
    headings.forEach(function (h, i) {
      if (!h.id) h.id = 'pu-h-' + i;
      html += '<li' + (h.tagName === 'H3' ? ' style="padding-left:12px"' : '') + '>'
            + '<a href="#' + h.id + '">' + h.textContent + '</a></li>';
    });
    tocList.innerHTML = html;
    tocWrap.style.display = 'block';

    /* Highlight active heading */
    var links = tocList.querySelectorAll('a');
    window.addEventListener('scroll', function () {
      var current = '';
      headings.forEach(function (h) {
        if (h.getBoundingClientRect().top <= 90) current = h.id;
      });
      links.forEach(function (a) {
        a.classList.toggle('active', a.getAttribute('href') === '#' + current);
      });
    }, { passive: true });
  }());

  /* ── Custom gallery modal ── */
  <?php if (empty($lightboxEnabled)): ?>
  (function () {
    var modal   = document.getElementById('puGalleryModal');
    var img     = document.getElementById('puGModalImg');
    var cap     = document.getElementById('puGModalCap');
    var btnPrev = document.getElementById('puGModalPrev');
    var btnNext = document.getElementById('puGModalNext');
    var btnClose= document.getElementById('puGModalClose');
    if (!modal) return;

    var items = [], cur = 0;
    document.querySelectorAll('.pu-gallery-trigger').forEach(function (el, i) {
      items.push({ full: el.dataset.full });
      el.addEventListener('click', function () { openModal(i); });
    });

    function openModal(idx) {
      cur = idx; img.src = items[cur].full; cap.textContent = '';
      btnPrev.classList.toggle('hidden', items.length < 2);
      btnNext.classList.toggle('hidden', items.length < 2);
      modal.classList.add('open'); document.body.style.overflow = 'hidden';
    }
    function closeModal() { modal.classList.remove('open'); document.body.style.overflow = ''; }
    function showPrev() { cur = (cur - 1 + items.length) % items.length; img.src = items[cur].full; cap.textContent = ''; }
    function showNext() { cur = (cur + 1) % items.length; img.src = items[cur].full; cap.textContent = ''; }

    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (btnPrev)  btnPrev.addEventListener('click', showPrev);
    if (btnNext)  btnNext.addEventListener('click', showNext);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) {
      if (!modal.classList.contains('open')) return;
      if (e.key === 'Escape')     closeModal();
      if (e.key === 'ArrowLeft')  showPrev();
      if (e.key === 'ArrowRight') showNext();
    });
  }());
  <?php endif; ?>
}());
</script>
</body>
</html>
