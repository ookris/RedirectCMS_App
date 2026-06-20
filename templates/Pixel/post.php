<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($postTitle) ?> — <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)($postDescription ?? '')), 0, 160)) ?>" />

  <!-- Open Graph -->
  <meta property="og:type"        content="article" />
  <meta property="og:title"       content="<?= htmlspecialchars($postTitle) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)($postDescription ?? '')), 0, 200)) ?>" />
  <meta property="og:url"         content="<?= htmlspecialchars($shareUrl) ?>" />
  <?php if (!empty($ogImageUrl)): ?>
    <meta property="og:image"       content="<?= htmlspecialchars($ogImageUrl) ?>" />
    <meta property="og:image:width"  content="1200" />
    <meta property="og:image:height" content="630" />
  <?php endif; ?>
  <?php if (!empty($postCreatedAt)): ?>
    <meta property="article:published_time" content="<?= htmlspecialchars($postCreatedAt) ?>" />
  <?php endif; ?>

  <!-- Twitter Card -->
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:title"       content="<?= htmlspecialchars($postTitle) ?>" />
  <meta name="twitter:description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)($postDescription ?? '')), 0, 200)) ?>" />
  <?php if (!empty($ogImageUrl)): ?>
    <meta name="twitter:image"     content="<?= htmlspecialchars($ogImageUrl) ?>" />
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;400;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
  <?php if (!empty($lightboxEnabled)): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" />
  <?php endif; ?>
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    * { transition: color .2s ease, background-color .2s ease, border-color .2s ease, opacity .2s ease, transform .2s ease, box-shadow .2s ease; }

    body {
      background: var(--theme-body-bg, #f7f7f7);
      font-family: 'Open Sans', sans-serif;
      font-size: 14px;
      color: #2e2e2e;
    }

    /* ─── TOPBAR ─────────────────────────────────────────────── */
    .px-topbar {
      background: var(--theme-topbar-bg, #1a1a2e);
      color: var(--theme-topbar-text, #ccccdd);
      font-family: 'Josefin Sans', sans-serif;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: .04em;
      padding: 7px 0;
    }
    .px-topbar a { color: var(--theme-topbar-text, #ccccdd); text-decoration: none; opacity: .75; }
    .px-topbar a:hover { opacity: 1; }
    .px-social-icon {
      display: inline-flex; align-items: center; justify-content: center;
      width: 26px; height: 26px; border-radius: 4px; font-size: 13px;
    }
    .px-social-icon:hover { opacity: 1 !important; background: rgba(255,255,255,.12); }
    .px-topnav a {
      font-size: 11px; letter-spacing: .06em; text-transform: uppercase;
      padding: 0 8px; border-right: 1px solid rgba(255,255,255,.15);
    }
    .px-topnav a:last-child { border-right: none; }

    /* ─── HEADER ────────────────────────────────────────────── */
    .px-header {
      background: var(--theme-header-bg, #ffffff);
      color: var(--theme-header-text, #2e2e2e);
      padding: 22px 0;
      border-bottom: 1px solid #e8e8e8;
    }
    .px-logo { height: 48px; width: auto; object-fit: contain; }
    .px-site-title {
      font-family: 'Josefin Sans', sans-serif;
      font-size: 1.9rem; font-weight: 700; letter-spacing: -.02em;
      color: var(--theme-header-text, #2e2e2e); text-decoration: none;
    }
    .px-site-title:hover { color: var(--theme-primary, #2942ee); }
    .px-site-subtitle { font-size: .8rem; color: #8e8e95; margin-top: 3px; font-family: 'Josefin Sans', sans-serif; letter-spacing: .04em; }
    .px-search-btn {
      background: none; border: 1.5px solid #ddd; border-radius: 6px;
      padding: 6px 12px; font-size: .8rem; color: #8e8e95; cursor: pointer;
      display: flex; align-items: center; gap: 6px;
    }
    .px-search-btn:hover { border-color: var(--theme-primary, #2942ee); color: var(--theme-primary, #2942ee); }

    /* ─── NAVIGATION ─────────────────────────────────────────── */
    .px-nav {
      background: var(--theme-nav-bg, #ffffff);
      border-bottom: 3px solid var(--theme-primary, #2942ee);
      position: sticky; top: 0; z-index: 200;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    .px-nav .nav-link {
      font-family: 'Josefin Sans', sans-serif; font-size: 13px; font-weight: 600;
      letter-spacing: .05em; text-transform: uppercase;
      color: var(--theme-nav-text, #2e2e2e); padding: 12px 14px;
      border-bottom: 3px solid transparent; margin-bottom: -3px;
    }
    .px-nav .nav-link:hover { color: var(--theme-primary, #2942ee); border-bottom-color: var(--theme-primary, #2942ee); }
    .px-cat-badge {
      display: inline-block; padding: 2px 8px; border-radius: 2px;
      font-family: 'Josefin Sans', sans-serif; font-size: 10px; font-weight: 700;
      letter-spacing: .06em; text-transform: uppercase; color: #fff;
      text-decoration: none; white-space: nowrap;
    }
    .px-cat-badge:hover { opacity: .85; color: #fff; }

    /* ─── BREADCRUMB ─────────────────────────────────────────── */
    .px-breadcrumb {
      background: #fff;
      border-bottom: 1px solid #eee;
      padding: 10px 0;
    }
    .px-breadcrumb ol { margin: 0; }
    .px-breadcrumb .breadcrumb-item {
      font-family: 'Josefin Sans', sans-serif;
      font-size: .72rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase;
    }
    .px-breadcrumb .breadcrumb-item a { color: var(--theme-primary, #2942ee); text-decoration: none; }
    .px-breadcrumb .breadcrumb-item.active { color: #8e8e95; }
    .px-breadcrumb .breadcrumb-item + .breadcrumb-item::before { color: #ccc; }

    /* ─── POST HERO IMAGE ────────────────────────────────────── */
    .px-post-hero-wrap {
      width: 100%;
      max-height: 480px;
      overflow: hidden;
      border-radius: 8px;
      margin-bottom: 28px;
      box-shadow: 0 4px 20px rgba(0,0,0,.1);
      position: relative;
    }
    .px-post-hero-wrap img {
      width: 100%;
      height: 100%;
      max-height: 480px;
      object-fit: cover;
      display: block;
    }
    .px-post-hero-cat {
      position: absolute;
      top: 16px; left: 16px;
    }

    /* ─── ARTICLE BOX ────────────────────────────────────────── */
    .px-article {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 1px 6px rgba(0,0,0,.07);
      padding: 36px 40px;
    }
    .px-article h1 {
      font-family: 'Josefin Sans', sans-serif;
      font-size: 2rem; font-weight: 700; line-height: 1.3;
      color: #1a1a2a; margin-bottom: 14px;
    }
    @media (max-width: 576px) {
      .px-article { padding: 22px 18px; }
      .px-article h1 { font-size: 1.45rem; }
    }

    /* ─── POST META ──────────────────────────────────────────── */
    .px-post-meta {
      display: flex; flex-wrap: wrap; align-items: center; gap: 12px;
      font-family: 'Josefin Sans', sans-serif;
      font-size: .72rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase;
      color: #8e8e95; margin-bottom: 24px;
      padding-bottom: 20px; border-bottom: 2px solid #f0f0f0;
    }
    .px-post-meta svg { flex-shrink: 0; vertical-align: -2px; }
    .px-post-meta a { color: var(--theme-primary, #2942ee); text-decoration: none; }

    /* ─── DESCRIPTION ─────────────────────────────────────────── */
    .px-post-body {
      font-size: .95rem; line-height: 1.85; color: #374151;
    }
    .px-post-body h2, .px-post-body h3, .px-post-body h4 {
      font-family: 'Josefin Sans', sans-serif;
      font-weight: 700; margin-top: 28px; margin-bottom: 12px;
    }
    .px-post-body h2 { font-size: 1.4rem; }
    .px-post-body h3 { font-size: 1.15rem; }
    .px-post-body img { max-width: 100%; border-radius: 6px; }
    .px-post-body a { color: var(--theme-primary, #2942ee); }
    .px-post-body blockquote {
      border-left: 4px solid var(--theme-primary, #2942ee);
      background: #f8f9fe; padding: 14px 20px; border-radius: 0 6px 6px 0;
      font-style: italic; color: #555; margin: 20px 0;
    }
    .px-post-body ul, .px-post-body ol { padding-left: 22px; }
    .px-post-body li { margin-bottom: 5px; }

    /* ─── TAGS ROW ───────────────────────────────────────────── */
    .px-tag-row {
      display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
      margin-top: 28px; padding-top: 20px; border-top: 1px solid #f0f0f0;
    }
    .px-tag-label {
      font-family: 'Josefin Sans', sans-serif;
      font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
      color: #8e8e95;
    }
    .px-tag-pill {
      display: inline-block; padding: 3px 10px; border-radius: 3px;
      background: #f0f0f0; color: #555;
      font-family: 'Josefin Sans', sans-serif; font-size: .72rem; font-weight: 600;
      text-decoration: none; letter-spacing: .03em;
    }
    .px-tag-pill:hover { background: var(--theme-accent, #e83e8c); color: #fff; }

    /* ─── SHARE BAR ──────────────────────────────────────────── */
    .px-share-bar {
      display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
      margin-top: 24px; padding: 18px 0; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0;
    }
    .px-share-label {
      font-family: 'Josefin Sans', sans-serif;
      font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
      color: #8e8e95; margin-right: 4px;
    }
    .px-share-btn {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 7px 14px; border-radius: 4px; font-size: .75rem;
      font-family: 'Josefin Sans', sans-serif; font-weight: 700; letter-spacing: .04em;
      text-transform: uppercase; text-decoration: none; color: #fff;
    }
    .px-share-btn:hover { opacity: .88; color: #fff; }
    .px-share-facebook  { background: #1877f2; }
    .px-share-twitter   { background: #000; }
    .px-share-linkedin  { background: #0a66c2; }
    .px-share-copy {
      background: #f0f0f0; color: #444;
      cursor: pointer; border: none; font-family: 'Josefin Sans', sans-serif;
      font-weight: 700; font-size: .75rem; letter-spacing: .04em; text-transform: uppercase;
      padding: 7px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px;
    }
    .px-share-copy:hover { background: #e0e0e0; }

    /* ─── AUTHOR BOX ─────────────────────────────────────────── */
    .px-redirect-box {
      background: var(--theme-primary, #2942ee);
      color: #fff;
      border-radius: 8px;
      padding: 20px 24px;
      margin-top: 28px;
      display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    }
    .px-redirect-box a {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.2); color: #fff;
      padding: 9px 20px; border-radius: 5px;
      font-family: 'Josefin Sans', sans-serif; font-size: .8rem; font-weight: 700;
      letter-spacing: .05em; text-transform: uppercase; text-decoration: none;
      border: 1.5px solid rgba(255,255,255,.4);
    }
    .px-redirect-box a:hover { background: rgba(255,255,255,.3); }
    .px-redirect-text { flex: 1; font-size: .9rem; font-family: 'Josefin Sans', sans-serif; font-weight: 600; }

    /* ─── GALLERY ────────────────────────────────────────────── */
    .px-gallery-head {
      font-family: 'Josefin Sans', sans-serif; font-size: .9rem; font-weight: 700;
      letter-spacing: .06em; text-transform: uppercase; color: #2e2e2e;
      margin: 28px 0 14px; padding-bottom: 10px; border-bottom: 2px solid #eee;
    }
    .px-gallery-grid {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px;
      margin-bottom: 24px;
    }
    .px-gthumb { border-radius: 6px; overflow: hidden; aspect-ratio: 4/3; display: block; cursor: zoom-in; }
    .px-gthumb img { width: 100%; height: 100%; object-fit: cover; }
    .px-gthumb:hover img { transform: scale(1.06); }

    /* ─── GALLERY MODAL ──────────────────────────────────────── */
    .px-gmodal {
      position: fixed; inset: 0; z-index: 9998;
      background: rgba(0,0,0,.92);
      display: flex; align-items: center; justify-content: center;
      opacity: 0; visibility: hidden;
    }
    .px-gmodal.open { opacity: 1; visibility: visible; }
    .px-gmodal-img {
      max-width: min(90vw, 1100px);
      max-height: 85vh;
      object-fit: contain;
      border-radius: 4px;
      box-shadow: 0 8px 40px rgba(0,0,0,.6);
      display: block;
    }
    .px-gmodal-close {
      position: absolute; top: 20px; right: 24px;
      background: none; border: none; color: #fff;
      font-size: 1.8rem; cursor: pointer; opacity: .7; line-height: 1;
      font-family: 'Josefin Sans', sans-serif;
    }
    .px-gmodal-close:hover { opacity: 1; }
    .px-gmodal-caption {
      position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%);
      color: rgba(255,255,255,.7); font-family: 'Josefin Sans', sans-serif;
      font-size: .75rem; letter-spacing: .06em; text-transform: uppercase;
      white-space: nowrap; max-width: 90vw; overflow: hidden; text-overflow: ellipsis;
    }
    .px-gmodal-nav {
      position: absolute; top: 50%; transform: translateY(-50%);
      background: rgba(255,255,255,.12); border: none; color: #fff;
      width: 44px; height: 44px; border-radius: 50%; font-size: 1.2rem;
      cursor: pointer; display: flex; align-items: center; justify-content: center;
    }
    .px-gmodal-nav:hover { background: rgba(255,255,255,.25); }
    .px-gmodal-prev { left: 16px; }
    .px-gmodal-next { right: 16px; }
    .px-gmodal-nav.hidden { display: none; }

    /* ─── RELATED POSTS ──────────────────────────────────────── */
    .px-related { margin-top: 40px; }
    .px-related-head {
      font-family: 'Josefin Sans', sans-serif; font-size: .8rem; font-weight: 700;
      letter-spacing: .1em; text-transform: uppercase; color: #2e2e2e;
      margin-bottom: 16px; padding-bottom: 10px;
      border-bottom: 2px solid var(--theme-primary, #2942ee);
      display: inline-block;
    }
    .px-rel-card {
      background: #fff; border-radius: 6px; overflow: hidden;
      box-shadow: 0 1px 4px rgba(0,0,0,.06); height: 100%;
    }
    .px-rel-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.1); transform: translateY(-3px); }
    .px-rel-card-img { width: 100%; aspect-ratio: 16/9; object-fit: cover; }
    .px-rel-placeholder {
      width: 100%; aspect-ratio: 16/9;
      display: flex; align-items: center; justify-content: center;
      font-family: 'Josefin Sans', sans-serif; font-size: 1.5rem; font-weight: 700;
      color: rgba(255,255,255,.6);
    }
    .px-rel-body { padding: 12px 14px; }
    .px-rel-title {
      font-family: 'Josefin Sans', sans-serif; font-size: .85rem; font-weight: 700;
      line-height: 1.4; margin: 0; color: #2e2e2e; text-decoration: none;
    }
    .px-rel-title:hover { color: var(--theme-primary, #2942ee); }
    .px-rel-meta { font-size: .7rem; color: #8e8e95; margin-top: 6px; }

    /* ─── SIDEBAR ────────────────────────────────────────────── */
    .px-sidebar { position: sticky; top: 70px; align-self: flex-start; }
    .px-widget { background: #fff; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 24px; overflow: hidden; }
    .px-widget-head {
      background: var(--theme-primary, #2942ee); color: #fff;
      font-family: 'Josefin Sans', sans-serif; font-size: .75rem; font-weight: 700;
      letter-spacing: .1em; text-transform: uppercase; padding: 10px 16px;
    }
    .px-widget-body { padding: 14px 16px; }

    /* ─── FOOTER ─────────────────────────────────────────────── */
    .px-footer { background: var(--theme-footer-bg, #1a1a2e); color: var(--theme-footer-text, #9999aa); margin-top: 60px; padding: 48px 0 0; }
    .px-footer h5 { font-family: 'Josefin Sans', sans-serif; font-size: .8rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #fff; margin-bottom: 16px; }
    .px-footer a { color: var(--theme-footer-text, #9999aa); text-decoration: none; }
    .px-footer a:hover { color: #fff; }
    .px-footer-copy { font-size: .72rem; border-top: 1px solid rgba(255,255,255,.08); padding: 14px 0; margin-top: 36px; text-align: center; opacity: .55; }
    .px-footer-social a {
      display: inline-flex; align-items: center; justify-content: center;
      width: 34px; height: 34px; border: 1px solid rgba(255,255,255,.15);
      border-radius: 5px; margin-right: 6px; margin-bottom: 6px;
      color: var(--theme-footer-text, #9999aa); font-size: 14px;
    }
    .px-footer-social a:hover { background: var(--theme-primary, #2942ee); border-color: var(--theme-primary, #2942ee); color: #fff; }

    /* ─── SEARCH OVERLAY ─────────────────────────────────────── */
    .px-search-overlay { position: fixed; inset: 0; z-index: 9999; background: rgba(10,12,20,.95); display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; }
    .px-search-overlay.open { opacity: 1; visibility: visible; }
    .px-search-inner { width: min(600px, 90vw); }
    .px-search-inner input { background: transparent; border: 0; border-bottom: 2px solid rgba(255,255,255,.4); color: #fff; font-family: 'Josefin Sans', sans-serif; font-size: 1.5rem; font-weight: 300; width: 100%; padding: 12px 0; outline: none; }
    .px-search-inner input::placeholder { color: rgba(255,255,255,.4); }
    .px-search-inner input:focus { border-bottom-color: var(--theme-primary, #2942ee); }
    .px-search-close { position: absolute; top: 24px; right: 28px; background: none; border: none; color: #fff; font-size: 1.4rem; cursor: pointer; opacity: .6; font-family: 'Josefin Sans', sans-serif; }
    .px-search-close:hover { opacity: 1; }

    @media (max-width: 767px) {
      .px-topbar .px-topnav { display: none; }
    }
  </style>
</head>
<body>

<?php
function px_post_url(string $basePath, string $slug, bool $prettyUrls): string {
    return $prettyUrls
        ? htmlspecialchars($basePath . '/blog/' . $slug, ENT_QUOTES)
        : htmlspecialchars($basePath . '/?post=' . $slug, ENT_QUOTES);
}
function px_cat_url(string $basePath, string $slug, bool $prettyUrls): string {
    return $prettyUrls
        ? htmlspecialchars($basePath . '/category/' . $slug, ENT_QUOTES)
        : htmlspecialchars($basePath . '/?category=' . $slug, ENT_QUOTES);
}
function px_tag_url(string $basePath, string $slug, bool $prettyUrls): string {
    return $prettyUrls
        ? htmlspecialchars($basePath . '/tag/' . $slug, ENT_QUOTES)
        : htmlspecialchars($basePath . '/?tag=' . $slug, ENT_QUOTES);
}
function px_page_url(string $basePath, string $slug, bool $prettyUrls): string {
    return htmlspecialchars($basePath . '/?page=' . $slug, ENT_QUOTES);
}
function px_thumb(string $basePath, string $ogImage, string $size = 'thumbnail'): string {
    $file = basename($ogImage);
    $cropped = $_SERVER['DOCUMENT_ROOT'] . '/uploads/cropped/' . $size . '/' . $file;
    if ($file && file_exists($cropped)) {
        return htmlspecialchars($basePath . '/uploads/cropped/' . $size . '/' . $file, ENT_QUOTES);
    }
    return htmlspecialchars($basePath . '/' . ltrim($ogImage, '/'), ENT_QUOTES);
}
function px_date_post(string $datetime): string {
    $ts = strtotime($datetime);
    return $ts ? date('d F Y', $ts) : '';
}
function px_palette_post(int $idx): string {
    $c = ['#2942ee','#e83e8c','#fd7e14','#20c997','#6f42c1','#0dcaf0','#198754'];
    return $c[$idx % count($c)];
}

$_category = $link['category'] ?? null;
$_tags     = $link['tags'] ?? [];
$_dateStr  = px_date_post($postCreatedAt ?? '');
$_catColor = $_category['color'] ?? 'var(--theme-primary,#2942ee)';
?>

<!-- SEARCH OVERLAY -->
<div class="px-search-overlay" id="pxSearchOverlay">
  <button class="px-search-close" id="pxSearchClose" aria-label="Zamknij">✕</button>
  <div class="px-search-inner">
    <form method="get" action="<?= htmlspecialchars($basePath) ?>/">
      <input type="text" name="q" placeholder="Szukaj wpisów…" autofocus autocomplete="off" />
    </form>
    <p style="color:rgba(255,255,255,.35);font-size:.75rem;margin-top:10px;font-family:'Josefin Sans',sans-serif;">
      Naciśnij ENTER, aby wyszukać &nbsp;·&nbsp; ESC, aby zamknąć
    </p>
  </div>
</div>

<!-- TOPBAR -->
<div class="px-topbar">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex gap-1">
        <?php
        $socialSVGs = [
            'facebook'  => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/></svg>',
            'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"/></svg>',
            'twitter'   => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>',
            'youtube'   => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.007 2.007 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.007 2.007 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31.4 31.4 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.007 2.007 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A99.788 99.788 0 0 1 7.858 2h.193zM6.4 5.209v4.818l4.157-2.408L6.4 5.209z"/></svg>',
            'linkedin'  => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/></svg>',
            'tiktok'    => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3V0z"/></svg>',
            'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0a8 8 0 0 0-2.915 15.452c-.07-.633-.134-1.606.027-2.297.188-.69 1.279-5.42 1.279-5.42s-.327-.653-.327-1.618c0-1.517.879-2.651 1.97-2.651.93 0 1.38.698 1.38 1.535 0 .936-.594 2.338-.902 3.636-.257 1.086.542 1.969 1.608 1.969 1.93 0 3.42-2.034 3.42-4.972 0-2.599-1.87-4.418-4.537-4.418-3.089 0-4.902 2.317-4.902 4.709 0 .932.358 1.93.806 2.476a.32.32 0 0 1 .075.31c-.083.342-.266 1.088-.301 1.239-.048.2-.16.242-.369.146-1.379-.641-2.241-2.658-2.241-4.278 0-3.479 2.528-6.676 7.29-6.676 3.829 0 6.8 2.73 6.8 6.38 0 3.803-2.398 6.864-5.726 6.864-1.118 0-2.17-.581-2.53-1.265l-.688 2.566c-.248.961-.921 2.164-1.371 2.898A8 8 0 1 0 8 0z"/></svg>',
        ];
        foreach ($socialSVGs as $key => $svg):
            if (!empty($socialLinks[$key])): ?>
              <a href="<?= htmlspecialchars($socialLinks[$key], ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer" class="px-social-icon" title="<?= htmlspecialchars(ucfirst($key)) ?>"><?= $svg ?></a>
            <?php endif;
        endforeach; ?>
      </div>
      <nav class="px-topnav d-flex">
        <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
        <?php if (!empty($contactEnabled)): ?>
          <a href="<?= px_page_url($basePath, 'contact', $prettyUrls ?? false) ?>">Kontakt</a>
        <?php endif; ?>
        <?php foreach (($navPages ?? []) as $_np): ?>
          <a href="<?= px_page_url($basePath, $_np['slug'], $prettyUrls ?? false) ?>"><?= htmlspecialchars($_np['title']) ?></a>
        <?php endforeach; ?>
      </nav>
    </div>
  </div>
</div>

<!-- HEADER -->
<header class="px-header">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <a href="<?= htmlspecialchars($basePath) ?>/" class="d-flex align-items-center gap-3 text-decoration-none">
        <?php if (!empty($brandingLogo)): ?>
          <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($homeTitle) ?>" class="px-logo" />
        <?php endif; ?>
        <div>
          <div class="px-site-title"><?= htmlspecialchars($homeTitle) ?></div>
          <?php if (!empty($homeSubtitle)): ?>
            <div class="px-site-subtitle"><?= htmlspecialchars($homeSubtitle) ?></div>
          <?php endif; ?>
        </div>
      </a>
      <button class="px-search-btn" id="pxSearchOpen" type="button">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.44 1.368a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/></svg>
        Szukaj
      </button>
    </div>
  </div>
</header>

<!-- NAVIGATION -->
<nav class="px-nav">
  <div class="container d-flex align-items-center">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="nav-link" style="color:var(--theme-primary,#2942ee)">Strona główna</a>
    <?php foreach (($navPages ?? []) as $_np): ?>
      <a href="<?= px_page_url($basePath, $_np['slug'], $prettyUrls ?? false) ?>" class="nav-link"><?= htmlspecialchars($_np['title']) ?></a>
    <?php endforeach; ?>
    <?php if (!empty($contactEnabled)): ?>
      <a href="<?= px_page_url($basePath, 'contact', $prettyUrls ?? false) ?>" class="nav-link">Kontakt</a>
    <?php endif; ?>
    <?php if (!empty($allCategories)): ?>
      <div class="d-flex ms-auto gap-1 flex-wrap py-1">
        <?php foreach (array_slice($allCategories, 0, 5) as $_i => $_cat): ?>
          <a href="<?= px_cat_url($basePath, $_cat['slug'], $prettyUrls ?? false) ?>"
             class="px-cat-badge" style="background:<?= htmlspecialchars($_cat['color'] ?: px_palette_post($_i), ENT_QUOTES) ?>">
            <?= htmlspecialchars($_cat['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</nav>

<!-- BREADCRUMB -->
<div class="px-breadcrumb">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a></li>
        <?php if (!empty($_category)): ?>
          <li class="breadcrumb-item">
            <a href="<?= px_cat_url($basePath, $_category['slug'], $prettyUrls ?? false) ?>"><?= htmlspecialchars($_category['name']) ?></a>
          </li>
        <?php endif; ?>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars(mb_substr($postTitle, 0, 50)) ?><?= mb_strlen($postTitle) > 50 ? '…' : '' ?></li>
      </ol>
    </nav>
  </div>
</div>

<!-- MAIN -->
<div class="container" style="margin-top:28px; margin-bottom:40px;">
  <div class="row g-4">

    <!-- ARTICLE -->
    <main class="col-lg-8">

      <!-- Hero image -->
      <?php if (!empty($ogImageUrl)): ?>
        <div class="px-post-hero-wrap">
          <img src="<?= htmlspecialchars($ogImageUrl, ENT_QUOTES) ?>"
               alt="<?= htmlspecialchars($postTitle) ?>"
               loading="eager" />
          <?php if (!empty($_category)): ?>
            <div class="px-post-hero-cat">
              <a href="<?= px_cat_url($basePath, $_category['slug'], $prettyUrls ?? false) ?>"
                 class="px-cat-badge"
                 style="background:<?= htmlspecialchars($_category['color'] ?: 'var(--theme-primary,#2942ee)', ENT_QUOTES) ?>">
                <?= htmlspecialchars($_category['name']) ?>
              </a>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Article box -->
      <article class="px-article">
        <h1><?= htmlspecialchars($postTitle) ?></h1>

        <!-- Meta -->
        <div class="px-post-meta">
          <?php if (!empty($_category) && empty($ogImageUrl)): ?>
            <a href="<?= px_cat_url($basePath, $_category['slug'], $prettyUrls ?? false) ?>"
               class="px-cat-badge"
               style="background:<?= htmlspecialchars($_category['color'] ?: 'var(--theme-primary,#2942ee)', ENT_QUOTES) ?>">
              <?= htmlspecialchars($_category['name']) ?>
            </a>
          <?php endif; ?>
          <?php if (!empty($_dateStr)): ?>
            <span>
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/></svg>
              <?= htmlspecialchars($_dateStr) ?>
            </span>
          <?php endif; ?>
          <?php if (!empty($link['click_count'])): ?>
            <span>
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
              <?= (int)$link['click_count'] ?> odsłon
            </span>
          <?php endif; ?>
        </div>

        <!-- Body -->
        <div class="px-post-body">
          <?= Utils::sanitizeHtml($postDescription ?? '') ?>
        </div>

        <!-- Gallery -->
        <?php if (!empty($galleryImages)): ?>
          <div class="px-gallery-head">Galeria</div>
          <div class="px-gallery-grid">
            <?php foreach ($galleryImages as $_gi):
              $_fullSrc  = htmlspecialchars($basePath . '/' . ltrim($_gi['path'], '/'), ENT_QUOTES);
              $_thumbSrc = !empty($_gi['url']) ? htmlspecialchars($_gi['url'], ENT_QUOTES) : $_fullSrc;
            ?>
              <?php if (!empty($lightboxEnabled)): ?>
                <a href="<?= $_fullSrc ?>"
                   data-lightbox="post-gallery"
                   data-title="<?= htmlspecialchars($postTitle) ?>"
                   class="px-gthumb">
                  <img src="<?= $_thumbSrc ?>" alt="<?= htmlspecialchars($postTitle) ?>" loading="lazy" />
                </a>
              <?php else: ?>
                <a href="<?= $_fullSrc ?>"
                   class="px-gthumb px-gallery-trigger"
                   data-full="<?= $_fullSrc ?>"
                   data-caption="<?= htmlspecialchars($postTitle) ?>">
                  <img src="<?= $_thumbSrc ?>" alt="<?= htmlspecialchars($postTitle) ?>" loading="lazy" />
                </a>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Tags -->
        <?php if (!empty($_tags)): ?>
          <div class="px-tag-row">
            <span class="px-tag-label">Tagi:</span>
            <?php foreach ($_tags as $_tag): ?>
              <a href="<?= px_tag_url($basePath, $_tag['slug'], $prettyUrls ?? false) ?>" class="px-tag-pill">
                #<?= htmlspecialchars($_tag['name']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Share -->
        <div class="px-share-bar">
          <span class="px-share-label">Udostępnij:</span>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>"
             target="_blank" rel="noopener" class="px-share-btn px-share-facebook">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/></svg>
            Facebook
          </a>
          <a href="https://twitter.com/intent/tweet?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($postTitle) ?>"
             target="_blank" rel="noopener" class="px-share-btn px-share-twitter">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>
            X / Twitter
          </a>
          <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($shareUrl) ?>"
             target="_blank" rel="noopener" class="px-share-btn px-share-linkedin">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/></svg>
            LinkedIn
          </a>
          <button class="px-share-copy" onclick="navigator.clipboard&&navigator.clipboard.writeText('<?= htmlspecialchars($shareUrl, ENT_JS) ?>').then(function(){var b=this;b.textContent='Skopiowano!';setTimeout(function(){b.textContent='Kopiuj link';},2000)}.bind(this))" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
            Kopiuj link
          </button>
        </div>

        <!-- Reactions -->
        <?php require __DIR__ . '/../../src/PostReactions.php'; ?>

        <!-- Redirect CTA -->
        <?php if (!empty($link['target_url'])): ?>
          <div class="px-redirect-box">
            <div class="px-redirect-text">Przejdź do pełnej oferty lub źródła</div>
            <a href="<?= htmlspecialchars($directLinkUrl, ENT_QUOTES) ?>">
              Sprawdź ofertę
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/></svg>
            </a>
          </div>
        <?php endif; ?>
      </article>

      <!-- Related posts -->
      <?php if (!empty($relatedPosts)): ?>
        <div class="px-related">
          <div class="px-related-head">Podobne wpisy</div>
          <div class="row g-3">
            <?php foreach ($relatedPosts as $_i => $_rp): ?>
              <div class="col-sm-4 col-6 d-flex">
                <article class="px-rel-card w-100">
                  <a href="<?= px_post_url($basePath, $_rp['slug'], $prettyUrls ?? false) ?>" class="d-block" tabindex="-1">
                    <?php if (!empty($_rp['og_image'])): ?>
                      <img src="<?= px_thumb($basePath, $_rp['og_image'], 'thumbnail') ?>"
                           alt="<?= htmlspecialchars($_rp['page_title']) ?>"
                           class="px-rel-card-img" loading="lazy" />
                    <?php else: ?>
                      <div class="px-rel-placeholder" style="background:<?= htmlspecialchars($_rp['category_color'] ?: px_palette_post($_i), ENT_QUOTES) ?>">
                        <?= mb_strtoupper(mb_substr(strip_tags($_rp['page_title']), 0, 2)) ?>
                      </div>
                    <?php endif; ?>
                  </a>
                  <div class="px-rel-body">
                    <a href="<?= px_post_url($basePath, $_rp['slug'], $prettyUrls ?? false) ?>" class="px-rel-title d-block">
                      <?= htmlspecialchars($_rp['page_title']) ?>
                    </a>
                    <div class="px-rel-meta"><?= htmlspecialchars(date('d.m.Y', strtotime($_rp['created_at'] ?? 'now'))) ?></div>
                  </div>
                </article>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </main><!-- /article -->

    <!-- SIDEBAR -->
    <aside class="col-lg-4">
      <div class="px-sidebar">
        <?php require __DIR__ . '/_sidebar.php'; ?>
        <!-- Categories fallback (shown if no sidebar widgets configured) -->
        <?php if (empty($sidebarData) && !empty($allCategories)): ?>
          <div class="px-widget">
            <div class="px-widget-head">Kategorie</div>
            <div class="px-widget-body" style="padding:0">
              <?php foreach ($allCategories as $_i => $_cat): ?>
                <a href="<?= px_cat_url($basePath, $_cat['slug'], $prettyUrls ?? false) ?>"
                   style="display:flex;align-items:center;justify-content:space-between;padding:9px 16px;border-bottom:1px solid #f5f5f5;color:#2e2e2e;text-decoration:none;font-size:.82rem;font-family:'Josefin Sans',sans-serif;font-weight:600">
                  <span style="display:flex;align-items:center;gap:8px">
                    <span style="width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($_cat['color'] ?: px_palette_post($_i), ENT_QUOTES) ?>;flex-shrink:0"></span>
                    <?= htmlspecialchars($_cat['name']) ?>
                  </span>
                  <span style="font-size:.7rem;color:#8e8e95;background:#f5f5f5;padding:1px 7px;border-radius:10px"><?= (int)$_cat['post_count'] ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </aside>

  </div><!-- /row -->
</div><!-- /container -->

<!-- FOOTER -->
<footer class="px-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <h5><?= htmlspecialchars($homeTitle) ?></h5>
        <?php if (!empty($homeSubtitle)): ?>
          <p style="font-size:.82rem;line-height:1.7;margin-bottom:16px"><?= htmlspecialchars($homeSubtitle) ?></p>
        <?php endif; ?>
        <?php if (!empty($socialLinks)): ?>
          <div class="px-footer-social">
            <?php foreach ($socialSVGs as $key => $svg):
                if (!empty($socialLinks[$key])): ?>
                  <a href="<?= htmlspecialchars($socialLinks[$key], ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars(ucfirst($key)) ?>"><?= $svg ?></a>
                <?php endif;
            endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <?php if (!empty($allCategories)): ?>
      <div class="col-lg-2 col-md-6">
        <h5>Kategorie</h5>
        <ul style="list-style:none;padding:0;margin:0">
          <?php foreach ($allCategories as $_cat): ?>
            <li style="margin-bottom:7px">
              <a href="<?= px_cat_url($basePath, $_cat['slug'], $prettyUrls ?? false) ?>" style="font-size:.8rem;display:flex;align-items:center;gap:7px">
                <span style="width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($_cat['color'] ?: '#777', ENT_QUOTES) ?>;flex-shrink:0"></span>
                <?= htmlspecialchars($_cat['name']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <?php if (!empty($navPages) || !empty($contactEnabled)): ?>
      <div class="col-lg-2 col-md-6">
        <h5>Strony</h5>
        <ul style="list-style:none;padding:0;margin:0">
          <li style="margin-bottom:7px"><a href="<?= htmlspecialchars($basePath) ?>/" style="font-size:.8rem">Strona główna</a></li>
          <?php foreach (($navPages ?? []) as $_np): ?>
            <li style="margin-bottom:7px">
              <a href="<?= px_page_url($basePath, $_np['slug'], $prettyUrls ?? false) ?>" style="font-size:.8rem"><?= htmlspecialchars($_np['title']) ?></a>
            </li>
          <?php endforeach; ?>
          <?php if (!empty($contactEnabled)): ?>
            <li style="margin-bottom:7px"><a href="<?= px_page_url($basePath, 'contact', $prettyUrls ?? false) ?>" style="font-size:.8rem">Kontakt</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
    <?php if (!empty($homeFooter)): ?>
      <div style="margin-top:28px;padding-top:20px;border-top:1px solid rgba(255,255,255,.08);font-size:.78rem"><?= $homeFooter ?></div>
    <?php endif; ?>
    <div class="px-footer-copy">
      &copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>. Wszystkie prawa zastrzeżone.
      &nbsp;&middot;&nbsp; Powered by <a href="https://redirectcms.pl" target="_blank" rel="noopener noreferrer" style="opacity:1;color:inherit">RedirectCMS</a>
      &nbsp;&middot;&nbsp; Theme: <a href="https://github.com/puikinsh/Pixel-Blogger-Template" target="_blank" rel="noopener noreferrer" style="opacity:1;color:inherit">Pixel</a>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($lightboxEnabled)): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<?php endif; ?>
<!-- GALLERY MODAL -->
<?php if (!empty($lightboxEnabled) === false): ?>
<div class="px-gmodal" id="pxGalleryModal" role="dialog" aria-modal="true" aria-label="Podgląd zdjęcia">
  <button class="px-gmodal-close" id="pxGalleryClose" aria-label="Zamknij">✕</button>
  <button class="px-gmodal-nav px-gmodal-prev hidden" id="pxGalleryPrev" aria-label="Poprzednie">&#8249;</button>
  <img class="px-gmodal-img" id="pxGalleryImg" src="" alt="" />
  <button class="px-gmodal-nav px-gmodal-next hidden" id="pxGalleryNext" aria-label="Następne">&#8250;</button>
  <div class="px-gmodal-caption" id="pxGalleryCaption"></div>
</div>
<?php endif; ?>

<script>
(function() {
  // ── Search overlay ──────────────────────────────────────────
  var btn     = document.getElementById('pxSearchOpen');
  var close   = document.getElementById('pxSearchClose');
  var overlay = document.getElementById('pxSearchOverlay');
  if (btn && overlay) {
    btn.addEventListener('click', function() {
      overlay.classList.add('open');
      var inp = overlay.querySelector('input');
      if (inp) setTimeout(function() { inp.focus(); }, 60);
    });
    close.addEventListener('click', function() { overlay.classList.remove('open'); });
    overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.classList.remove('open'); });
  }

  // ── Gallery modal (when lightbox2 not used) ─────────────────
  var modal   = document.getElementById('pxGalleryModal');
  if (!modal) return; // lightbox2 is handling it

  var img     = document.getElementById('pxGalleryImg');
  var cap     = document.getElementById('pxGalleryCaption');
  var btnPrev = document.getElementById('pxGalleryPrev');
  var btnNext = document.getElementById('pxGalleryNext');
  var items   = Array.from(document.querySelectorAll('.px-gallery-trigger'));
  var current = 0;

  function openAt(index) {
    current = index;
    var item = items[current];
    img.src = item.getAttribute('data-full') || item.href || '';
    img.alt = item.getAttribute('data-caption') || '';
    cap.textContent = item.getAttribute('data-caption') || '';
    btnPrev.classList.toggle('hidden', items.length <= 1);
    btnNext.classList.toggle('hidden', items.length <= 1);
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    modal.classList.remove('open');
    document.body.style.overflow = '';
    img.src = '';
  }

  function navigate(dir) {
    current = (current + dir + items.length) % items.length;
    var item = items[current];
    img.src = item.getAttribute('data-full') || item.href || '';
    img.alt = item.getAttribute('data-caption') || '';
    cap.textContent = item.getAttribute('data-caption') || '';
  }

  items.forEach(function(el, i) {
    el.addEventListener('click', function(e) {
      e.preventDefault();
      openAt(i);
    });
  });

  document.getElementById('pxGalleryClose').addEventListener('click', closeModal);
  btnPrev.addEventListener('click', function() { navigate(-1); });
  btnNext.addEventListener('click', function() { navigate(1); });

  modal.addEventListener('click', function(e) {
    if (e.target === modal) closeModal();
  });

  document.addEventListener('keydown', function(e) {
    if (!modal.classList.contains('open')) return;
    if (e.key === 'Escape')      closeModal();
    if (e.key === 'ArrowLeft')   navigate(-1);
    if (e.key === 'ArrowRight')  navigate(1);
  });
})();
</script>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
