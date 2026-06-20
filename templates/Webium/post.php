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
    <meta property="og:image:width"  content="800" />
    <meta property="og:image:height" content="450" />
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet" />
  <?php if (!empty($lightboxEnabled)): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" />
  <?php endif; ?>
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    *, *::before, *::after { box-sizing: border-box; }

    /* ─── BASE ───────────────────────────────────────────────── */
    body {
      margin: 0; padding: 0;
      background: var(--theme-body-bg, #fff);
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      font-size: 16px; color: var(--theme-text, #292929);
      -webkit-font-smoothing: antialiased;
    }
    a { color: inherit; text-decoration: none; }
    img { display: block; max-width: 100%; }

    /* ─── TOP HEADER (mobile/tablet) ──────────────────────────── */
    .wb-topbar {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      height: 56px; background: #fff;
      border-bottom: 1px solid var(--theme-border, #e6e6e6);
      box-shadow: 0 1px 8px rgba(0,0,0,.06);
      display: flex; align-items: center;
    }
    .wb-topbar-inner {
      max-width: 1504px; width: 100%; margin: 0 auto;
      padding: 0 16px;
      display: flex; align-items: center; justify-content: space-between; gap: 12px;
    }
    .wb-topbar-logo {
      display: flex; align-items: center; gap: 8px;
      color: var(--theme-text, #292929); text-decoration: none;
      font-weight: 600; font-size: .95rem;
      overflow: hidden;
    }
    .wb-topbar-logo img { max-height: 28px; width: auto; }
    .wb-topbar-actions { display: flex; align-items: center; gap: 12px; }
    .wb-topbar-btn {
      background: none; border: none; cursor: pointer; padding: 4px;
      color: var(--theme-muted, #757575); display: flex; align-items: center;
      transition: color .15s;
    }
    .wb-topbar-btn:hover { color: var(--theme-text, #292929); }

    /* ─── VERTICAL LEFT SIDEBAR (xl, 1280px+) ────────────────── */
    .wb-leftrail {
      display: none;
    }
    @media (min-width: 1280px) {
      .wb-topbar { display: none; }
      .wb-leftrail {
        display: flex;
        position: fixed; top: 0; left: 0; bottom: 0; z-index: 100;
        width: 80px;
        flex-direction: column;
        align-items: center;
        padding: 32px 0;
        border-right: 1px solid var(--theme-border, #e6e6e6);
        background: #fff;
        gap: 28px;
      }
      .wb-leftrail-logo { display: flex; flex-direction: column; align-items: center; }
      .wb-leftrail-logo img { max-width: 44px; max-height: 44px; object-fit: contain; }
      .wb-leftrail-logo .wb-logo-initial {
        width: 40px; height: 40px; border-radius: 50%;
        background: var(--theme-primary, #ffc017);
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 1.1rem; color: #000;
      }
      .wb-leftrail-nav {
        display: flex; flex-direction: column; align-items: center; gap: 20px;
        margin-top: auto;
      }
      .wb-leftrail-nav a {
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: var(--theme-muted, #757575);
        transition: background .15s, color .15s;
      }
      .wb-leftrail-nav a:hover {
        background: var(--theme-chip-bg, #f2f2f2);
        color: var(--theme-text, #292929);
      }
    }

    /* ─── MAIN LAYOUT ────────────────────────────────────────── */
    .wb-post-layout {
      max-width: 1504px; margin: 0 auto;
      padding-top: 80px; /* topbar clearance, mobile */
      display: flex; flex-direction: row; gap: 60px;
      padding-left: 24px; padding-right: 24px;
    }
    @media (min-width: 1280px) {
      .wb-post-layout {
        padding-top: 40px;
        padding-left: 104px; /* 80px rail + 24px gap */
      }
    }
    .wb-post-col {
      flex: 1; min-width: 0;
      max-width: 695px; margin: 0 auto;
    }
    .wb-post-sidebar {
      width: 300px; flex-shrink: 0;
      position: sticky; top: 32px; align-self: flex-start;
    }
    @media (max-width: 1023px) {
      .wb-post-sidebar { display: none; }
      .wb-post-col { max-width: 100%; }
    }
    @media (max-width: 767px) {
      .wb-post-layout { padding: 72px 16px 0; }
    }

    /* ─── POST HEADER ────────────────────────────────────────── */
    .wb-post-author-row {
      display: flex; align-items: center; gap: 12px;
      margin-bottom: 20px; flex-wrap: wrap;
    }
    .wb-post-avatar {
      width: 48px; height: 48px; border-radius: 50%;
      background: var(--theme-primary, #ffc017);
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 1.1rem; color: #000; flex-shrink: 0;
    }
    .wb-post-author-info { flex: 1; min-width: 0; }
    .wb-post-author-name { font-size: .95rem; font-weight: 600; }
    .wb-post-author-meta { font-size: .8rem; color: var(--theme-muted, #757575); margin-top: 1px; }

    /* Share icons in author row */
    .wb-post-share-icons { display: flex; gap: 8px; margin-left: auto; }
    .wb-post-share-icon {
      width: 32px; height: 32px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      border: 1px solid var(--theme-border, #e6e6e6);
      color: var(--theme-muted, #757575);
      transition: color .15s, border-color .15s;
      text-decoration: none;
    }
    .wb-post-share-icon:hover { color: var(--theme-text, #292929); border-color: var(--theme-text, #292929); }

    /* ─── POST TITLE ─────────────────────────────────────────── */
    .wb-post-title {
      font-size: 2rem; font-weight: 700; line-height: 1.28;
      color: var(--theme-text, #292929); margin: 0 0 12px;
      letter-spacing: -.02em;
    }
    @media (max-width: 576px) { .wb-post-title { font-size: 1.5rem; } }

    /* Category / tags chips */
    .wb-post-chips {
      display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px;
    }
    .wb-chip-sm {
      display: inline-block; padding: 3px 12px; border-radius: 99px;
      font-size: .75rem; font-weight: 500;
      background: var(--theme-chip-bg, #f2f2f2);
      color: var(--theme-muted, #757575);
      text-decoration: none;
      transition: background .15s;
    }
    .wb-chip-sm:hover { background: var(--theme-border, #e6e6e6); color: var(--theme-text, #292929); }

    /* ─── FEATURED IMAGE ─────────────────────────────────────── */
    .wb-post-featured-img {
      width: 100%; max-height: 400px; object-fit: cover;
      margin: 20px 0 28px;
    }

    /* ─── POST BODY ──────────────────────────────────────────── */
    .wb-post-body {
      font-family: 'Lora', Georgia, serif;
      font-size: 1.1rem; line-height: 1.85;
      color: var(--theme-text, #292929);
      margin-bottom: 40px;
    }
    @media (min-width: 1280px) { .wb-post-body { font-size: 1.2rem; } }
    .wb-post-body h2, .wb-post-body h3, .wb-post-body h4 {
      font-family: 'Inter', sans-serif;
      font-weight: 700; margin-top: 32px; margin-bottom: 12px;
    }
    .wb-post-body h2 { font-size: 1.5rem; }
    .wb-post-body h3 { font-size: 1.2rem; }
    .wb-post-body p { margin: 0 0 20px; }
    .wb-post-body img { max-width: 100%; margin: 24px 0; cursor: zoom-in; }
    .wb-post-body a { color: var(--theme-accent, #1a8917); text-decoration: underline; text-underline-offset: 3px; }
    .wb-post-body blockquote {
      border-left: 3px solid var(--theme-text, #292929);
      padding: 4px 0 4px 20px;
      margin: 24px 0; font-style: italic;
      color: var(--theme-muted, #757575);
    }
    .wb-post-body ul, .wb-post-body ol { padding-left: 22px; margin-bottom: 20px; }
    .wb-post-body li { margin-bottom: 6px; }
    .wb-post-body code {
      font-family: 'SFMono-Regular', Consolas, monospace;
      font-size: .875em; background: var(--theme-chip-bg, #f2f2f2);
      padding: 2px 5px; border-radius: 3px;
    }
    .wb-post-body pre {
      background: #1e1e1e; color: #d4d4d4; padding: 20px;
      border-radius: 6px; overflow-x: auto; margin: 24px 0;
    }
    .wb-post-body pre code { background: none; color: inherit; font-size: .875rem; }

    /* ─── POST FOOTER ────────────────────────────────────────── */
    .wb-post-footer {
      border-top: 1px solid var(--theme-border, #e6e6e6);
      padding-top: 20px; margin-top: 12px;
      display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
    }
    .wb-post-footer-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .wb-post-footer-share { display: flex; gap: 8px; }

    /* ─── REDIRECT CTA ────────────────────────────────────────── */
    .wb-redirect-box {
      border: 1.5px solid var(--theme-border, #e6e6e6);
      border-left: 4px solid var(--theme-accent, #1a8917);
      border-radius: 4px; padding: 16px 20px;
      margin: 28px 0;
      display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    }
    .wb-redirect-box a {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: .85rem; font-weight: 600;
      color: var(--theme-accent, #1a8917);
      border: 1.5px solid var(--theme-accent, #1a8917);
      padding: 8px 18px; border-radius: 99px;
      white-space: nowrap; transition: background .15s, color .15s;
    }
    .wb-redirect-box a:hover {
      background: var(--theme-accent, #1a8917); color: #fff;
    }
    .wb-redirect-text { flex: 1; font-size: .9rem; color: var(--theme-muted, #757575); }

    /* ─── GALLERY ────────────────────────────────────────────── */
    .wb-gallery-heading {
      font-size: .75rem; font-weight: 700; letter-spacing: .08em;
      text-transform: uppercase; color: var(--theme-muted, #757575);
      margin: 32px 0 14px;
      padding-bottom: 10px;
      border-bottom: 1px solid var(--theme-border, #e6e6e6);
    }
    .wb-gallery-grid {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
      gap: 8px; margin-bottom: 28px;
    }
    .wb-gthumb {
      display: block; aspect-ratio: 3/2; overflow: hidden;
      background: var(--theme-chip-bg, #f2f2f2);
      cursor: zoom-in;
    }
    .wb-gthumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
    .wb-gthumb:hover img { transform: scale(1.05); }

    /* Gallery modal */
    .wb-gmodal {
      position: fixed; inset: 0; z-index: 9998;
      background: rgba(0,0,0,.94);
      display: flex; align-items: center; justify-content: center;
      opacity: 0; visibility: hidden;
      transition: opacity .2s ease, visibility .2s ease;
    }
    .wb-gmodal.open { opacity: 1; visibility: visible; }
    .wb-gmodal-img {
      max-width: min(92vw, 1100px); max-height: 88vh;
      object-fit: contain; display: block;
    }
    .wb-gmodal-close {
      position: absolute; top: 20px; right: 24px;
      background: none; border: none; color: rgba(255,255,255,.7);
      font-size: 1.6rem; cursor: pointer; line-height: 1;
    }
    .wb-gmodal-close:hover { color: #fff; }
    .wb-gmodal-nav {
      position: absolute; top: 50%; transform: translateY(-50%);
      background: rgba(255,255,255,.1); border: none; color: #fff;
      width: 44px; height: 44px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.3rem; cursor: pointer; transition: background .15s;
    }
    .wb-gmodal-nav:hover { background: rgba(255,255,255,.25); }
    .wb-gmodal-prev { left: 16px; }
    .wb-gmodal-next { right: 16px; }
    .wb-gmodal-nav.hidden { display: none; }
    .wb-gmodal-cap {
      position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%);
      color: rgba(255,255,255,.55); font-size: .75rem; font-family: 'Inter', sans-serif;
      letter-spacing: .04em; white-space: nowrap;
    }

    /* ─── RELATED POSTS ──────────────────────────────────────── */
    .wb-related { margin-top: 40px; padding-top: 28px; border-top: 1px solid var(--theme-border, #e6e6e6); }
    .wb-related-title {
      font-size: .75rem; font-weight: 700; letter-spacing: .08em;
      text-transform: uppercase; color: var(--theme-muted, #757575);
      margin: 0 0 20px;
    }
    .wb-related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px,1fr)); gap: 20px; }
    .wb-related-card { text-decoration: none; }
    .wb-related-img { width: 100%; aspect-ratio: 3/2; object-fit: cover; background: var(--theme-chip-bg, #f2f2f2); }
    .wb-related-title-text {
      font-size: .875rem; font-weight: 600; line-height: 1.4;
      color: var(--theme-text, #292929); margin: 8px 0 4px;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
      transition: color .15s;
    }
    .wb-related-card:hover .wb-related-title-text { color: var(--theme-accent, #1a8917); }
    .wb-related-meta { font-size: .72rem; color: var(--theme-muted, #757575); }

    /* ─── SIDEBAR WIDGETS ─────────────────────────────────────── */
    .wb-widget { margin-bottom: 36px; }
    .wb-widget-title {
      font-size: .75rem; font-weight: 700; letter-spacing: .08em;
      text-transform: uppercase; color: var(--theme-muted, #757575);
      margin: 0 0 14px; padding-bottom: 10px;
      border-bottom: 1px solid var(--theme-border, #e6e6e6);
    }
    .wb-search-form-sidebar {
      display: flex; align-items: center;
      border: 1px solid var(--theme-border, #e6e6e6);
      border-radius: 99px; padding: 6px 14px; gap: 8px;
      margin-bottom: 32px;
    }
    .wb-search-form-sidebar input {
      flex: 1; border: none; outline: none; background: transparent;
      font-family: 'Inter', sans-serif; font-size: .875rem;
      color: var(--theme-text, #292929);
    }
    .wb-search-form-sidebar input::placeholder { color: #c0c0c0; }
    .wb-chip-sm {
      display: inline-block; padding: 3px 12px; border-radius: 99px;
      font-size: .75rem; font-weight: 500;
      background: var(--theme-chip-bg, #f2f2f2);
      color: var(--theme-muted, #757575); text-decoration: none;
      margin: 3px 2px; transition: background .15s;
    }
    .wb-chip-sm:hover { background: var(--theme-border, #e6e6e6); color: var(--theme-text, #292929); }

    /* ─── SEARCH OVERLAY ─────────────────────────────────────── */
    .wb-search-overlay {
      position: fixed; inset: 0; z-index: 9999;
      background: rgba(255,255,255,.97);
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      opacity: 0; visibility: hidden;
      transition: opacity .2s ease, visibility .2s ease;
    }
    .wb-search-overlay.open { opacity: 1; visibility: visible; }
    .wb-search-form {
      width: min(640px, 88vw); display: flex; align-items: center; gap: 10px;
      border-bottom: 2px solid #292929; padding-bottom: 8px;
    }
    .wb-search-form input {
      flex: 1; background: transparent; border: none; outline: none;
      font-family: 'Inter', sans-serif; font-size: 1.4rem; font-weight: 500;
      color: #292929; padding: 0;
    }
    .wb-search-form input::placeholder { color: #c0c0c0; }
    .wb-search-hint { margin-top: 14px; font-size: .8rem; color: #a8a8a8; }
    .wb-search-close {
      position: absolute; top: 24px; right: 28px;
      background: none; border: none; cursor: pointer;
      font-size: 1.5rem; color: #757575; line-height: 1;
    }
    .wb-search-close:hover { color: #292929; }

    /* ─── FOOTER ─────────────────────────────────────────────── */
    .wb-footer {
      border-top: 1px solid var(--theme-border, #e6e6e6);
      padding: 24px 0; margin-top: 60px;
    }
    .wb-footer-inner {
      max-width: 1504px; margin: 0 auto;
      padding: 0 24px;
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 12px;
    }
    @media (min-width: 1280px) { .wb-footer-inner { padding-left: 104px; } }
    .wb-footer-copy { font-size: .75rem; color: var(--theme-muted, #757575); }
    .wb-footer-copy a { color: var(--theme-accent, #1a8917); }
    .wb-footer-copy a:hover { opacity: .75; }
    @media (max-width: 767px) { .wb-footer-inner { padding: 0 16px; } }
  </style>
</head>
<body>

<?php
function wb_post_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? htmlspecialchars($bp . '/blog/' . $slug, ENT_QUOTES)
                   : htmlspecialchars($bp . '/?post=' . $slug, ENT_QUOTES);
}
function wb_cat_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? htmlspecialchars($bp . '/category/' . $slug, ENT_QUOTES)
                   : htmlspecialchars($bp . '/?category=' . $slug, ENT_QUOTES);
}
function wb_tag_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? htmlspecialchars($bp . '/tag/' . $slug, ENT_QUOTES)
                   : htmlspecialchars($bp . '/?tag=' . $slug, ENT_QUOTES);
}
function wb_page_url(string $bp, string $slug): string {
    return htmlspecialchars($bp . '/?page=' . $slug, ENT_QUOTES);
}
function wb_thumb(string $bp, string $ogImage, string $size = 'listing'): string {
    $file = basename($ogImage);
    if ($file && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/cropped/' . $size . '/' . $file)) {
        return htmlspecialchars($bp . '/uploads/cropped/' . $size . '/' . $file, ENT_QUOTES);
    }
    return htmlspecialchars($bp . '/' . ltrim($ogImage, '/'), ENT_QUOTES);
}
function wb_date_post(string $dt): string {
    $ts = strtotime($dt);
    return $ts ? date('d F Y', $ts) : '';
}

$_category = $link['category'] ?? null;
$_tags     = $link['tags'] ?? [];
$_initials = mb_strtoupper(mb_substr(strip_tags($postTitle), 0, 1));
$_dateStr  = wb_date_post($postCreatedAt ?? '');
?>

<!-- SEARCH OVERLAY -->
<div class="wb-search-overlay" id="wbSearchOverlay">
  <button class="wb-search-close" id="wbSearchClose" aria-label="Zamknij">✕</button>
  <form class="wb-search-form" method="get" action="<?= htmlspecialchars($basePath) ?>/">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#a0a0a0;flex-shrink:0"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    <input type="text" name="q" placeholder="Szukaj wpisów…" autocomplete="off" />
  </form>
  <p class="wb-search-hint">Wpisz frazę i naciśnij ENTER &nbsp;·&nbsp; ESC aby zamknąć</p>
</div>

<!-- GALLERY MODAL -->
<?php if (empty($lightboxEnabled)): ?>
<div class="wb-gmodal" id="wbGalleryModal" role="dialog" aria-modal="true" aria-label="Podgląd zdjęcia">
  <button class="wb-gmodal-close" id="wbGalleryClose" aria-label="Zamknij">✕</button>
  <button class="wb-gmodal-nav wb-gmodal-prev hidden" id="wbGalleryPrev">&#8249;</button>
  <img class="wb-gmodal-img" id="wbGalleryImg" src="" alt="" />
  <button class="wb-gmodal-nav wb-gmodal-next hidden" id="wbGalleryNext">&#8250;</button>
  <div class="wb-gmodal-cap" id="wbGalleryCap"></div>
</div>
<?php endif; ?>

<!-- ─── VERTICAL LEFT RAIL (xl only) ──────────────────────── -->
<div class="wb-leftrail">
  <a href="<?= htmlspecialchars($basePath) ?>/" class="wb-leftrail-logo" aria-label="<?= htmlspecialchars($homeTitle) ?>">
    <?php if (!empty($brandingLogo)): ?>
      <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo, ENT_QUOTES) ?>"
           alt="<?= htmlspecialchars($homeTitle) ?>" />
    <?php else: ?>
      <div class="wb-logo-initial"><?= mb_strtoupper(mb_substr($homeTitle, 0, 1)) ?></div>
    <?php endif; ?>
  </a>
  <nav class="wb-leftrail-nav">
    <!-- Home -->
    <a href="<?= htmlspecialchars($basePath) ?>/" title="Strona główna">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    </a>
    <!-- Search -->
    <button class="wb-topbar-btn" id="wbSearchOpenRail" title="Szukaj" style="width:40px;height:40px;border-radius:50%;justify-content:center">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    </button>
    <?php if (!empty($contactEnabled)): ?>
      <a href="<?= wb_page_url($basePath, 'contact') ?>" title="Kontakt">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </a>
    <?php endif; ?>
    <?php foreach (array_slice($navPages ?? [], 0, 3) as $_np): ?>
      <a href="<?= wb_page_url($basePath, $_np['slug']) ?>" title="<?= htmlspecialchars($_np['title']) ?>">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </a>
    <?php endforeach; ?>
  </nav>
</div>

<!-- ─── TOP BAR (mobile/tablet) ──────────────────────────── -->
<header class="wb-topbar">
  <div class="wb-topbar-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="wb-topbar-logo">
      <?php if (!empty($brandingLogo)): ?>
        <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo, ENT_QUOTES) ?>" alt="" style="max-height:26px;width:auto" />
      <?php endif; ?>
      <span><?= htmlspecialchars($homeTitle) ?></span>
    </a>
    <div class="wb-topbar-actions">
      <button class="wb-topbar-btn" id="wbSearchOpenTop" aria-label="Szukaj">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </button>
      <a href="<?= htmlspecialchars($basePath) ?>/" class="wb-topbar-btn" aria-label="Strona główna">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </a>
    </div>
  </div>
</header>

<!-- ─── POST CONTENT ──────────────────────────────────────── -->
<div class="wb-post-layout">
  <article class="wb-post-col">

    <!-- Author row -->
    <div class="wb-post-author-row">
      <div class="wb-post-avatar"><?= $_initials ?></div>
      <div class="wb-post-author-info">
        <div class="wb-post-author-name"><?= htmlspecialchars($homeTitle) ?></div>
        <div class="wb-post-author-meta">
          <?= htmlspecialchars($_dateStr) ?>
          <?php if (!empty($link['click_count'])): ?>
            &nbsp;·&nbsp; <?= (int)$link['click_count'] ?> odsłon
          <?php endif; ?>
        </div>
      </div>
      <!-- Share icons -->
      <div class="wb-post-share-icons">
        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($postTitle) ?>"
           target="_blank" rel="noopener" class="wb-post-share-icon" title="X / Twitter">
          <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>"
           target="_blank" rel="noopener" class="wb-post-share-icon" title="Facebook">
          <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/></svg>
        </a>
        <button class="wb-post-share-icon" title="Kopiuj link"
                onclick="navigator.clipboard&&navigator.clipboard.writeText('<?= htmlspecialchars($shareUrl, ENT_QUOTES) ?>').then(function(){var b=this;b.style.color='#1a8917';setTimeout(function(){b.style.color='';},2000)}.bind(this))"
                type="button" style="cursor:pointer;background:none;border:1px solid var(--theme-border,#e6e6e6);border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;color:var(--theme-muted,#757575)">
          <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
        </button>
      </div>
    </div>

    <!-- Category + tag chips -->
    <?php if (!empty($_category) || !empty($_tags)): ?>
      <div class="wb-post-chips">
        <?php if (!empty($_category)): ?>
          <a href="<?= wb_cat_url($basePath, $_category['slug'], $prettyUrls ?? false) ?>"
             class="wb-chip-sm"><?= htmlspecialchars($_category['name']) ?></a>
        <?php endif; ?>
        <?php foreach ($_tags as $_tag): ?>
          <a href="<?= wb_tag_url($basePath, $_tag['slug'], $prettyUrls ?? false) ?>"
             class="wb-chip-sm">#<?= htmlspecialchars($_tag['name']) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Title -->
    <h1 class="wb-post-title"><?= htmlspecialchars($postTitle) ?></h1>

    <!-- Featured image -->
    <?php if (!empty($ogImageUrl)): ?>
      <img src="<?= htmlspecialchars($ogImageUrl, ENT_QUOTES) ?>"
           alt="<?= htmlspecialchars($postTitle) ?>"
           class="wb-post-featured-img"
           loading="eager" />
    <?php endif; ?>

    <!-- Post body -->
    <div class="wb-post-body">
      <?= Utils::sanitizeHtml($postDescription ?? '') ?>
    </div>

    <!-- Gallery -->
    <?php if (!empty($galleryImages)): ?>
      <div class="wb-gallery-heading">Galeria zdjęć</div>
      <div class="wb-gallery-grid">
        <?php foreach ($galleryImages as $_gi):
          $_full  = htmlspecialchars($basePath . '/' . ltrim($_gi['path'], '/'), ENT_QUOTES);
          $_thumb = !empty($_gi['url']) ? htmlspecialchars($_gi['url'], ENT_QUOTES) : $_full;
        ?>
          <?php if (!empty($lightboxEnabled)): ?>
            <a href="<?= $_full ?>" data-lightbox="gallery" data-title="<?= htmlspecialchars($postTitle) ?>" class="wb-gthumb">
              <img src="<?= $_thumb ?>" alt="<?= htmlspecialchars($postTitle) ?>" loading="lazy" />
            </a>
          <?php else: ?>
            <a href="<?= $_full ?>" class="wb-gthumb wb-gallery-trigger"
               data-full="<?= $_full ?>" data-caption="<?= htmlspecialchars($postTitle) ?>">
              <img src="<?= $_thumb ?>" alt="<?= htmlspecialchars($postTitle) ?>" loading="lazy" />
            </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Reactions -->
    <?php require __DIR__ . '/../../src/PostReactions.php'; ?>

    <!-- Redirect CTA -->
    <?php if (!empty($link['target_url'])): ?>
      <div class="wb-redirect-box">
        <div class="wb-redirect-text">Przejdź do źródła lub pełnej oferty</div>
        <a href="<?= htmlspecialchars($directLinkUrl, ENT_QUOTES) ?>">
          Sprawdź ofertę
          <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/></svg>
        </a>
      </div>
    <?php endif; ?>

    <!-- Post footer: tags + share -->
    <div class="wb-post-footer">
      <div class="wb-post-footer-tags">
        <?php foreach ($_tags as $_tag): ?>
          <a href="<?= wb_tag_url($basePath, $_tag['slug'], $prettyUrls ?? false) ?>" class="wb-chip-sm">
            #<?= htmlspecialchars($_tag['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="wb-post-footer-share" style="font-size:.78rem;color:var(--theme-muted,#757575);display:flex;align-items:center;gap:8px">
        <span>Udostępnij:</span>
        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener" class="wb-post-share-icon" style="width:28px;height:28px">
          <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener" class="wb-post-share-icon" style="width:28px;height:28px">
          <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/></svg>
        </a>
      </div>
    </div>

    <!-- Related posts -->
    <?php if (!empty($relatedPosts)): ?>
      <div class="wb-related">
        <div class="wb-related-title">Podobne wpisy</div>
        <div class="wb-related-grid">
          <?php foreach ($relatedPosts as $_i => $_rp): ?>
            <a href="<?= wb_post_url($basePath, $_rp['slug'], $prettyUrls ?? false) ?>" class="wb-related-card">
              <?php if (!empty($_rp['og_image'])): ?>
                <img src="<?= wb_thumb($basePath, $_rp['og_image'], 'listing_inner') ?>"
                     alt="<?= htmlspecialchars($_rp['page_title']) ?>"
                     class="wb-related-img" loading="lazy" />
              <?php else: ?>
                <div class="wb-related-img" style="display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:700;color:var(--theme-muted,#757575)">
                  <?= mb_strtoupper(mb_substr(strip_tags($_rp['page_title']), 0, 1)) ?>
                </div>
              <?php endif; ?>
              <div class="wb-related-title-text"><?= htmlspecialchars($_rp['page_title']) ?></div>
              <div class="wb-related-meta"><?= htmlspecialchars(date('d.m.Y', strtotime($_rp['created_at'] ?? 'now'))) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </article><!-- /wb-post-col -->

  <!-- ─── RIGHT SIDEBAR ────────────────────────────────────── -->
  <aside class="wb-post-sidebar">
    <!-- Search -->
    <form class="wb-search-form-sidebar" method="get" action="<?= htmlspecialchars($basePath) ?>/" role="search">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--theme-muted,#757575)"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" name="q" placeholder="Szukaj…" aria-label="Szukaj" />
    </form>

    <?php require __DIR__ . '/_sidebar.php'; ?>

    <!-- Categories fallback -->
    <?php if (empty($sidebarData) && !empty($allCategories)): ?>
      <div class="wb-widget">
        <div class="wb-widget-title">Kategorie</div>
        <?php foreach ($allCategories as $_cat): ?>
          <a href="<?= wb_cat_url($basePath, $_cat['slug'], $prettyUrls ?? false) ?>"
             style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--theme-border,#e6e6e6);font-size:.875rem;color:var(--theme-text,#292929);text-decoration:none">
            <?= htmlspecialchars($_cat['name']) ?>
            <span style="font-size:.75rem;color:var(--theme-muted,#757575)"><?= (int)$_cat['post_count'] ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </aside>

</div><!-- /wb-post-layout -->

<!-- FOOTER -->
<footer class="wb-footer">
  <div class="wb-footer-inner">
    <div class="wb-footer-copy">
      &copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>.
      Powered by <a href="https://redirectcms.pl" target="_blank" rel="noopener noreferrer">RedirectCMS</a>
      &middot; Theme: <a href="https://github.com/elhakimyasya/Webium-Blogger-Theme" target="_blank" rel="noopener noreferrer">Webium</a>
    </div>
    <div style="display:flex;gap:16px">
      <a href="<?= htmlspecialchars($basePath) ?>/" style="font-size:.75rem;color:var(--theme-muted,#757575);transition:color .15s">Strona główna</a>
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= wb_page_url($basePath, 'contact') ?>" style="font-size:.75rem;color:var(--theme-muted,#757575)">Kontakt</a>
      <?php endif; ?>
    </div>
  </div>
</footer>

<?php if (!empty($lightboxEnabled)): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<?php endif; ?>
<script>
(function() {
  // ── Search overlay ──────────────────────────────────────────
  var overlay  = document.getElementById('wbSearchOverlay');
  var closeBtn = document.getElementById('wbSearchClose');
  function openSearch() {
    overlay.classList.add('open');
    var inp = overlay.querySelector('input');
    if (inp) setTimeout(function() { inp.focus(); }, 60);
  }
  function closeSearch() { overlay.classList.remove('open'); }

  ['wbSearchOpenTop','wbSearchOpenRail'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('click', openSearch);
  });
  if (closeBtn) closeBtn.addEventListener('click', closeSearch);
  overlay.addEventListener('click', function(e) { if (e.target === overlay) closeSearch(); });

  // ── Gallery modal ────────────────────────────────────────────
  var gmodal = document.getElementById('wbGalleryModal');
  if (gmodal) {
    var gimg   = document.getElementById('wbGalleryImg');
    var gcap   = document.getElementById('wbGalleryCap');
    var gprev  = document.getElementById('wbGalleryPrev');
    var gnext  = document.getElementById('wbGalleryNext');
    var gtriggers = Array.from(document.querySelectorAll('.wb-gallery-trigger'));
    var gcurrent  = 0;

    function gopen(i) {
      gcurrent = i;
      var t = gtriggers[i];
      gimg.src = t.getAttribute('data-full') || '';
      gimg.alt = t.getAttribute('data-caption') || '';
      gcap.textContent = t.getAttribute('data-caption') || '';
      gprev.classList.toggle('hidden', gtriggers.length <= 1);
      gnext.classList.toggle('hidden', gtriggers.length <= 1);
      gmodal.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function gclose() {
      gmodal.classList.remove('open');
      document.body.style.overflow = '';
      gimg.src = '';
    }
    function gnav(d) {
      gcurrent = (gcurrent + d + gtriggers.length) % gtriggers.length;
      var t = gtriggers[gcurrent];
      gimg.src = t.getAttribute('data-full') || '';
      gcap.textContent = t.getAttribute('data-caption') || '';
    }
    gtriggers.forEach(function(el, i) {
      el.addEventListener('click', function(e) { e.preventDefault(); gopen(i); });
    });
    document.getElementById('wbGalleryClose').addEventListener('click', gclose);
    gprev.addEventListener('click', function() { gnav(-1); });
    gnext.addEventListener('click', function() { gnav(1); });
    gmodal.addEventListener('click', function(e) { if (e.target === gmodal) gclose(); });
  }

  // ── Global keyboard ─────────────────────────────────────────
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeSearch(); if (gmodal && gmodal.classList.contains('open')) gclose(); }
    if (gmodal && gmodal.classList.contains('open')) {
      if (e.key === 'ArrowLeft'  && gprev) gnav(-1);
      if (e.key === 'ArrowRight' && gnext) gnav(1);
    }
  });
})();
</script>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
