<!doctype html>
<html lang="pl">
<head>
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
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;400;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
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
    .px-topbar a {
      color: var(--theme-topbar-text, #ccccdd);
      text-decoration: none;
      opacity: .75;
    }
    .px-topbar a:hover { opacity: 1; }
    .px-social-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 26px;
      height: 26px;
      border-radius: 4px;
      font-size: 13px;
    }
    .px-social-icon:hover { opacity: 1 !important; background: rgba(255,255,255,.12); }
    .px-topnav a {
      font-size: 11px;
      letter-spacing: .06em;
      text-transform: uppercase;
      padding: 0 8px;
      border-right: 1px solid rgba(255,255,255,.15);
    }
    .px-topnav a:last-child { border-right: none; }

    /* ─── HEADER (logo + title) ──────────────────────────────── */
    .px-header {
      background: var(--theme-header-bg, #ffffff);
      color: var(--theme-header-text, #2e2e2e);
      padding: 22px 0;
      border-bottom: 1px solid #e8e8e8;
    }
    .px-logo { height: 48px; width: auto; object-fit: contain; }
    .px-site-title {
      font-family: 'Josefin Sans', sans-serif;
      font-size: 1.9rem;
      font-weight: 700;
      letter-spacing: -.02em;
      color: var(--theme-header-text, #2e2e2e);
      text-decoration: none;
    }
    .px-site-title:hover { color: var(--theme-primary, #2942ee); }
    .px-site-subtitle {
      font-size: .8rem;
      color: #8e8e95;
      margin-top: 3px;
      font-family: 'Josefin Sans', sans-serif;
      letter-spacing: .04em;
    }
    .px-search-btn {
      background: none;
      border: 1.5px solid #ddd;
      border-radius: 6px;
      padding: 6px 12px;
      font-size: .8rem;
      color: #8e8e95;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .px-search-btn:hover { border-color: var(--theme-primary, #2942ee); color: var(--theme-primary, #2942ee); }

    /* ─── NAVIGATION ─────────────────────────────────────────── */
    .px-nav {
      background: var(--theme-nav-bg, #ffffff);
      border-bottom: 3px solid var(--theme-primary, #2942ee);
      position: sticky;
      top: 0;
      z-index: 200;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    .px-nav .nav-link {
      font-family: 'Josefin Sans', sans-serif;
      font-size: 13px;
      font-weight: 600;
      letter-spacing: .05em;
      text-transform: uppercase;
      color: var(--theme-nav-text, #2e2e2e);
      padding: 12px 14px;
      border-bottom: 3px solid transparent;
      margin-bottom: -3px;
    }
    .px-nav .nav-link:hover,
    .px-nav .nav-link.active {
      color: var(--theme-primary, #2942ee);
      border-bottom-color: var(--theme-primary, #2942ee);
    }
    .px-nav .nav-home { color: var(--theme-primary, #2942ee); }

    /* ─── HERO SECTION ───────────────────────────────────────── */
    .px-hero { padding: 28px 0 0; }
    .px-hero-main {
      position: relative;
      border-radius: 8px;
      overflow: hidden;
      height: 430px;
      background: #ddd;
      display: block;
      text-decoration: none;
    }
    .px-hero-main img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .px-hero-main .px-hero-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(0,0,0,.72) 0%, rgba(0,0,0,.1) 50%, transparent 100%);
    }
    .px-hero-main .px-hero-caption {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 20px 22px;
      color: #fff;
    }
    .px-hero-main .px-hero-caption .px-cat-badge {
      margin-bottom: 8px;
    }
    .px-hero-main .px-hero-caption h2 {
      font-family: 'Josefin Sans', sans-serif;
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1.35;
      margin: 0 0 6px;
      color: #fff;
    }
    .px-hero-main .px-hero-caption .px-meta {
      font-size: 11px;
      opacity: .8;
    }
    .px-hero-small {
      position: relative;
      border-radius: 8px;
      overflow: hidden;
      flex: 1;
      min-height: 0;
      background: #ddd;
      display: block;
      text-decoration: none;
    }
    .px-hero-small img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .px-hero-small .px-hero-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(0,0,0,.7) 0%, transparent 60%);
    }
    .px-hero-small .px-hero-caption {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 12px 14px;
      color: #fff;
    }
    .px-hero-small .px-hero-caption h3 {
      font-family: 'Josefin Sans', sans-serif;
      font-size: .92rem;
      font-weight: 700;
      line-height: 1.3;
      margin: 0;
      color: #fff;
    }
    .px-hero-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Josefin Sans', sans-serif;
      font-size: 3rem;
      font-weight: 700;
      color: rgba(255,255,255,.5);
    }
    .px-hero-right {
      display: flex;
      flex-direction: column;
      gap: 12px;
      height: 430px;
    }

    /* ─── CATEGORY FILTER ────────────────────────────────────── */
    .px-filter {
      background: #fff;
      border-bottom: 1px solid #e8e8e8;
      padding: 10px 0;
      margin-top: 28px;
    }
    .px-filter-inner {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-wrap: wrap;
    }
    .px-filter-label {
      font-family: 'Josefin Sans', sans-serif;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: #8e8e95;
      margin-right: 4px;
      white-space: nowrap;
    }
    .px-fpill {
      font-family: 'Josefin Sans', sans-serif;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: .04em;
      text-transform: uppercase;
      padding: 4px 12px;
      border-radius: 3px;
      text-decoration: none;
      border: 1.5px solid transparent;
      white-space: nowrap;
    }
    .px-fpill-all {
      background: var(--theme-primary, #2942ee);
      color: #fff;
    }
    .px-fpill-all.inactive {
      background: #f0f0f0;
      color: #777;
      border-color: #ddd;
    }
    .px-fpill-all.inactive:hover {
      background: var(--theme-primary, #2942ee);
      color: #fff;
      border-color: transparent;
    }
    .px-fpill-cat {
      color: #555;
      border-color: #e0e0e0;
      background: #fff;
    }
    .px-fpill-cat:hover,
    .px-fpill-cat.active {
      color: #fff;
      border-color: transparent;
    }
    .px-fpill-tag {
      color: #777;
      border-color: #e0e0e0;
    }
    .px-fpill-tag:hover,
    .px-fpill-tag.active {
      background: var(--theme-accent, #e83e8c);
      color: #fff;
      border-color: transparent;
    }

    /* ─── ACTIVE FILTER NOTICE ───────────────────────────────── */
    .px-active-notice {
      background: #fffbeb;
      border-bottom: 1px solid #fde68a;
      padding: 7px 0;
      font-size: .8rem;
      color: #92400e;
    }
    .px-active-notice a { color: #92400e; font-weight: 700; }

    /* ─── CAT BADGE ──────────────────────────────────────────── */
    .px-cat-badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 2px;
      font-family: 'Josefin Sans', sans-serif;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: #fff;
      text-decoration: none;
      white-space: nowrap;
    }
    .px-cat-badge:hover { opacity: .85; color: #fff; }

    /* ─── POST CARD ──────────────────────────────────────────── */
    .px-card {
      background: #fff;
      border-radius: 6px;
      overflow: hidden;
      box-shadow: 0 1px 4px rgba(0,0,0,.06);
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .px-card:hover {
      box-shadow: 0 4px 18px rgba(0,0,0,.12);
      transform: translateY(-3px);
    }
    .px-card-img-wrap {
      position: relative;
      overflow: hidden;
      aspect-ratio: 16/9;
      background: #eee;
    }
    .px-card-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .px-card-img-wrap:hover img { transform: scale(1.05); }
    .px-card-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Josefin Sans', sans-serif;
      font-size: 2rem;
      font-weight: 700;
      color: rgba(255,255,255,.6);
    }
    .px-card-img-overlay {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: flex-end;
      padding: 10px;
      opacity: 0;
      background: rgba(0,0,0,.4);
    }
    .px-card-img-wrap:hover .px-card-img-overlay { opacity: 1; }
    .px-card-share { display: flex; gap: 6px; }
    .px-card-share a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px; height: 28px;
      background: rgba(255,255,255,.9);
      border-radius: 4px;
      color: #333;
      font-size: 11px;
      text-decoration: none;
    }
    .px-card-share a:hover { background: var(--theme-primary, #2942ee); color: #fff; }
    .px-card-body { padding: 14px 16px 16px; display: flex; flex-direction: column; flex: 1; }
    .px-card-cat { margin-bottom: 7px; }
    .px-card-title {
      font-family: 'Josefin Sans', sans-serif;
      font-size: .97rem;
      font-weight: 700;
      line-height: 1.4;
      margin: 0 0 8px;
    }
    .px-card-title a { color: #2e2e2e; text-decoration: none; }
    .px-card-title a:hover { color: var(--theme-primary, #2942ee); }
    .px-card-desc { font-size: .81rem; color: #6b6b78; line-height: 1.65; flex-grow: 1; margin-bottom: 12px; }
    .px-card-meta {
      font-size: .72rem;
      color: #8e8e95;
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      border-top: 1px solid #f0f0f0;
      padding-top: 10px;
      margin-top: auto;
    }
    .px-card-meta svg { flex-shrink: 0; }
    .px-card-readmore {
      font-family: 'Josefin Sans', sans-serif;
      font-size: .75rem;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
      color: var(--theme-primary, #2942ee);
      text-decoration: none;
    }
    .px-card-readmore:hover { text-decoration: underline; }

    /* ─── SECTION HEADING ────────────────────────────────────── */
    .px-section-head {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 2px solid #e8e8e8;
    }
    .px-section-head h2 {
      font-family: 'Josefin Sans', sans-serif;
      font-size: 1rem;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #2e2e2e;
      margin: 0;
    }
    .px-section-head .px-sh-accent {
      width: 3px;
      height: 20px;
      background: var(--theme-primary, #2942ee);
      border-radius: 2px;
      flex-shrink: 0;
    }

    /* ─── PAGINATION ─────────────────────────────────────────── */
    .px-pagination { margin-top: 36px; }
    .px-pagination .page-link {
      font-family: 'Josefin Sans', sans-serif;
      font-size: .78rem;
      font-weight: 600;
      letter-spacing: .04em;
      color: #555;
      border-radius: 4px !important;
      margin: 0 2px;
      border: 1.5px solid #e0e0e0;
      padding: 6px 12px;
    }
    .px-pagination .page-link:hover {
      background: var(--theme-primary, #2942ee);
      color: #fff;
      border-color: var(--theme-primary, #2942ee);
    }
    .px-pagination .page-item.active .page-link {
      background: var(--theme-primary, #2942ee);
      border-color: var(--theme-primary, #2942ee);
      color: #fff;
    }
    .px-pagination .page-item.disabled .page-link { opacity: .4; }

    /* ─── SIDEBAR ────────────────────────────────────────────── */
    .px-sidebar { position: sticky; top: 70px; align-self: flex-start; }
    .px-widget {
      background: #fff;
      border-radius: 6px;
      box-shadow: 0 1px 4px rgba(0,0,0,.06);
      margin-bottom: 24px;
      overflow: hidden;
    }
    .px-widget-head {
      background: var(--theme-primary, #2942ee);
      color: #fff;
      font-family: 'Josefin Sans', sans-serif;
      font-size: .75rem;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      padding: 10px 16px;
    }
    .px-widget-body { padding: 14px 16px; }

    /* ─── FOOTER ─────────────────────────────────────────────── */
    .px-footer {
      background: var(--theme-footer-bg, #1a1a2e);
      color: var(--theme-footer-text, #9999aa);
      margin-top: 60px;
      padding: 48px 0 0;
    }
    .px-footer h5 {
      font-family: 'Josefin Sans', sans-serif;
      font-size: .8rem;
      font-weight: 700;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: #fff;
      margin-bottom: 16px;
    }
    .px-footer a {
      color: var(--theme-footer-text, #9999aa);
      text-decoration: none;
    }
    .px-footer a:hover { color: #fff; }
    .px-footer-copy {
      font-size: .72rem;
      border-top: 1px solid rgba(255,255,255,.08);
      padding: 14px 0;
      margin-top: 36px;
      text-align: center;
      opacity: .55;
    }
    .px-footer-social a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 34px; height: 34px;
      border: 1px solid rgba(255,255,255,.15);
      border-radius: 5px;
      margin-right: 6px;
      margin-bottom: 6px;
      color: var(--theme-footer-text, #9999aa);
      font-size: 14px;
    }
    .px-footer-social a:hover { background: var(--theme-primary, #2942ee); border-color: var(--theme-primary, #2942ee); color: #fff; }

    /* ─── SEARCH OVERLAY ─────────────────────────────────────── */
    .px-search-overlay {
      position: fixed;
      inset: 0;
      z-index: 9999;
      background: rgba(10,12,20,.95);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      visibility: hidden;
    }
    .px-search-overlay.open { opacity: 1; visibility: visible; }
    .px-search-inner { width: min(600px, 90vw); }
    .px-search-inner input {
      background: transparent;
      border: 0;
      border-bottom: 2px solid rgba(255,255,255,.4);
      color: #fff;
      font-family: 'Josefin Sans', sans-serif;
      font-size: 1.5rem;
      font-weight: 300;
      width: 100%;
      padding: 12px 0;
      outline: none;
    }
    .px-search-inner input::placeholder { color: rgba(255,255,255,.4); }
    .px-search-inner input:focus { border-bottom-color: var(--theme-primary, #2942ee); }
    .px-search-close {
      position: absolute;
      top: 24px; right: 28px;
      background: none;
      border: none;
      color: #fff;
      font-size: 1.4rem;
      cursor: pointer;
      opacity: .6;
      font-family: 'Josefin Sans', sans-serif;
    }
    .px-search-close:hover { opacity: 1; }

    /* ─── EMPTY STATE ────────────────────────────────────────── */
    .px-empty {
      text-align: center;
      padding: 60px 20px;
      color: #8e8e95;
    }
    .px-empty h3 {
      font-family: 'Josefin Sans', sans-serif;
      font-size: 1.1rem;
      color: #555;
      margin-bottom: 8px;
    }

    /* ─── RESPONSIVE ─────────────────────────────────────────── */
    @media (max-width: 991px) {
      .px-hero-main { height: 260px; }
      .px-hero-right { flex-direction: row; height: 180px; }
      .px-hero-small { flex: 1; min-height: 0; height: 100%; }
    }
    @media (max-width: 767px) {
      .px-topbar .px-topnav { display: none; }
      .px-hero-right { display: none; }
      .px-hero-main { height: 280px; }
      .px-hero-main .px-hero-caption h2 { font-size: 1.05rem; }
    }
    @media (max-width: 575px) {
      .px-site-title { font-size: 1.4rem; }
      .px-filter-label { display: none; }
    }
  </style>
</head>
<body>

<?php
// ─── HELPERS ───────────────────────────────────────────────────────────────
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
function px_excerpt(string $html, int $len): string {
    $text = strip_tags($html);
    return mb_strlen($text) > $len ? mb_substr($text, 0, $len) . '…' : $text;
}
function px_date(string $datetime): string {
    $ts = strtotime($datetime);
    return $ts ? date('d.m.Y', $ts) : '';
}
function px_palette(int $index): string {
    $colors = ['#2942ee','#e83e8c','#fd7e14','#20c997','#6f42c1','#0dcaf0','#198754'];
    return $colors[$index % count($colors)];
}
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
      <!-- Social icons -->
      <div class="d-flex gap-1">
        <?php
        $socialIcons = [
            'facebook'   => ['title' => 'Facebook',   'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/></svg>'],
            'instagram'  => ['title' => 'Instagram',  'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"/></svg>'],
            'twitter'    => ['title' => 'X / Twitter', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>'],
            'youtube'    => ['title' => 'YouTube',    'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.007 2.007 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.007 2.007 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31.4 31.4 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.007 2.007 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A99.788 99.788 0 0 1 7.858 2h.193zM6.4 5.209v4.818l4.157-2.408L6.4 5.209z"/></svg>'],
            'linkedin'   => ['title' => 'LinkedIn',   'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/></svg>'],
            'tiktok'     => ['title' => 'TikTok',     'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3V0z"/></svg>'],
            'pinterest'  => ['title' => 'Pinterest',  'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0a8 8 0 0 0-2.915 15.452c-.07-.633-.134-1.606.027-2.297.188-.69 1.279-5.42 1.279-5.42s-.327-.653-.327-1.618c0-1.517.879-2.651 1.97-2.651.93 0 1.38.698 1.38 1.535 0 .936-.594 2.338-.902 3.636-.257 1.086.542 1.969 1.608 1.969 1.93 0 3.42-2.034 3.42-4.972 0-2.599-1.87-4.418-4.537-4.418-3.089 0-4.902 2.317-4.902 4.709 0 .932.358 1.93.806 2.476a.32.32 0 0 1 .075.31c-.083.342-.266 1.088-.301 1.239-.048.2-.16.242-.369.146-1.379-.641-2.241-2.658-2.241-4.278 0-3.479 2.528-6.676 7.29-6.676 3.829 0 6.8 2.73 6.8 6.38 0 3.803-2.398 6.864-5.726 6.864-1.118 0-2.17-.581-2.53-1.265l-.688 2.566c-.248.961-.921 2.164-1.371 2.898A8 8 0 1 0 8 0z"/></svg>'],
        ];
        foreach ($socialIcons as $key => $icon):
            if (!empty($socialLinks[$key])): ?>
              <a href="<?= htmlspecialchars($socialLinks[$key], ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer" class="px-social-icon" title="<?= htmlspecialchars($icon['title']) ?>"><?= $icon['svg'] ?></a>
            <?php endif;
        endforeach; ?>
      </div>
      <!-- Top nav links -->
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
      <button class="px-search-btn" id="pxSearchOpen" type="button" aria-label="Szukaj">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.44 1.368a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/></svg>
        Szukaj
      </button>
    </div>
  </div>
</header>

<!-- NAVIGATION -->
<nav class="px-nav">
  <div class="container">
    <div class="d-flex align-items-center">
      <a href="<?= htmlspecialchars($basePath) ?>/" class="nav-link px-nav-home">Strona główna</a>
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
               class="px-cat-badge"
               style="background:<?= htmlspecialchars($_cat['color'] ?: px_palette($_i), ENT_QUOTES) ?>">
              <?= htmlspecialchars($_cat['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<?php
// ─── Determine hero posts ──────────────────────────────────────────────────
$heroPosts = array_filter($blogPosts ?? [], fn($p) => !empty($p['og_image']));
$heroMain  = array_values($heroPosts)[0] ?? ($blogPosts[0] ?? null);
$heroSmall = array_values(array_slice(array_values($heroPosts), 1, 2));
$listPosts = $blogPosts ?? [];
// Only show hero when we have at least one post with image and not filtering
$showHero  = !empty($heroMain) && empty($activeCategory) && empty($activeTag) && $currentPage === 1;
?>

<?php if ($showHero): ?>
<!-- HERO SECTION -->
<section class="px-hero">
  <div class="container">
    <div class="row g-3 align-items-stretch">
      <div class="col-lg-7 col-12">
        <a href="<?= px_post_url($basePath, $heroMain['slug'], $prettyUrls ?? false) ?>" class="px-hero-main">
          <?php if (!empty($heroMain['og_image'])): ?>
            <img src="<?= px_thumb($basePath, $heroMain['og_image'], 'hero_wide') ?>"
                 alt="<?= htmlspecialchars($heroMain['page_title']) ?>"
                 loading="eager" />
          <?php else: ?>
            <div class="px-hero-placeholder" style="background:var(--theme-primary,#2942ee)">
              <?= mb_strtoupper(mb_substr(strip_tags($heroMain['page_title']), 0, 2)) ?>
            </div>
          <?php endif; ?>
          <div class="px-hero-overlay"></div>
          <div class="px-hero-caption">
            <?php if (!empty($heroMain['category_name'])): ?>
              <span class="px-cat-badge"
                    style="background:<?= htmlspecialchars($heroMain['category_color'] ?: 'var(--theme-primary,#2942ee)', ENT_QUOTES) ?>">
                <?= htmlspecialchars($heroMain['category_name']) ?>
              </span>
            <?php endif; ?>
            <h2><?= htmlspecialchars($heroMain['page_title']) ?></h2>
            <div class="px-meta">
              <?= px_date($heroMain['created_at']) ?>
              <?php if (!empty($heroMain['click_count'])): ?>
                &nbsp;·&nbsp; <?= (int)$heroMain['click_count'] ?> odsłon
              <?php endif; ?>
            </div>
          </div>
        </a>
      </div>
      <?php if (!empty($heroSmall)): ?>
      <div class="col-lg-5 col-12">
        <div class="px-hero-right">
          <?php foreach ($heroSmall as $_hs): ?>
            <a href="<?= px_post_url($basePath, $_hs['slug'], $prettyUrls ?? false) ?>" class="px-hero-small">
              <?php if (!empty($_hs['og_image'])): ?>
                <img src="<?= px_thumb($basePath, $_hs['og_image'], 'hero_small') ?>"
                     alt="<?= htmlspecialchars($_hs['page_title']) ?>"
                     loading="lazy" />
              <?php else: ?>
                <div class="px-hero-placeholder" style="background:<?= htmlspecialchars($_hs['category_color'] ?: 'var(--theme-accent,#e83e8c)', ENT_QUOTES) ?>; height:100%">
                  <?= mb_strtoupper(mb_substr(strip_tags($_hs['page_title']), 0, 2)) ?>
                </div>
              <?php endif; ?>
              <div class="px-hero-overlay"></div>
              <div class="px-hero-caption">
                <?php if (!empty($_hs['category_name'])): ?>
                  <span class="px-cat-badge" style="background:<?= htmlspecialchars($_hs['category_color'] ?: '#555', ENT_QUOTES) ?>;margin-bottom:5px;display:inline-block">
                    <?= htmlspecialchars($_hs['category_name']) ?>
                  </span>
                <?php endif; ?>
                <h3><?= htmlspecialchars($_hs['page_title']) ?></h3>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CATEGORY / TAG FILTER -->
<div class="px-filter">
  <div class="container">
    <div class="px-filter-inner">
      <span class="px-filter-label">Filtry:</span>
      <a href="<?= htmlspecialchars($basePath) ?>/"
         class="px-fpill px-fpill-all <?= (empty($activeCategory) && empty($activeTag)) ? '' : 'inactive' ?>">
        Wszystkie
      </a>
      <?php foreach (($allCategories ?? []) as $_i => $_cat): ?>
        <a href="<?= px_cat_url($basePath, $_cat['slug'], $prettyUrls ?? false) ?>"
           class="px-fpill px-fpill-cat <?= (!empty($activeCategory) && $activeCategory['slug'] === $_cat['slug']) ? 'active' : '' ?>"
           style="<?= (!empty($activeCategory) && $activeCategory['slug'] === $_cat['slug']) ? 'background:' . htmlspecialchars($_cat['color'] ?: px_palette($_i), ENT_QUOTES) . ';border-color:transparent;color:#fff' : '' ?>">
          <?= htmlspecialchars($_cat['name']) ?>
          <sup style="font-size:9px;opacity:.7"><?= (int)$_cat['post_count'] ?></sup>
        </a>
      <?php endforeach; ?>
      <?php if (!empty($allTags)): ?>
        <span class="px-filter-label ms-2">Tagi:</span>
        <?php foreach (array_slice($allTags ?? [], 0, 8) as $_tag): ?>
          <a href="<?= px_tag_url($basePath, $_tag['slug'], $prettyUrls ?? false) ?>"
             class="px-fpill px-fpill-tag <?= (!empty($activeTag) && $activeTag['slug'] === $_tag['slug']) ? 'active' : '' ?>">
            #<?= htmlspecialchars($_tag['name']) ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (!empty($activeCategory) || !empty($activeTag)): ?>
<div class="px-active-notice">
  <div class="container">
    <?php if (!empty($activeCategory)): ?>
      Kategoria: <strong><?= htmlspecialchars($activeCategory['name']) ?></strong>
    <?php elseif (!empty($activeTag)): ?>
      Tag: <strong>#<?= htmlspecialchars($activeTag['name']) ?></strong>
    <?php endif; ?>
    &nbsp;·&nbsp; <a href="<?= htmlspecialchars($basePath) ?>/">Wyczyść filtr ×</a>
  </div>
</div>
<?php endif; ?>

<!-- MAIN CONTENT -->
<div class="container" style="margin-top:32px; margin-bottom:32px;">
  <div class="row g-4">

    <!-- POSTS GRID -->
    <div class="col-lg-8">
      <?php if (!empty($listPosts)): ?>
        <div class="px-section-head">
          <div class="px-sh-accent"></div>
          <h2>
            <?php if (!empty($activeCategory)): ?>
              <?= htmlspecialchars($activeCategory['name']) ?>
            <?php elseif (!empty($activeTag)): ?>
              #<?= htmlspecialchars($activeTag['name']) ?>
            <?php else: ?>
              Najnowsze wpisy
            <?php endif; ?>
          </h2>
        </div>
        <div class="row g-4">
          <?php foreach ($listPosts as $_i => $_post):
            $_excerpt = px_excerpt($_post['page_description'] ?? '', $blogDescLength ?? 120);
            $_dateStr = px_date($_post['created_at'] ?? '');
            ?>
            <div class="col-sm-6 col-12 d-flex">
              <article class="px-card w-100">
                <!-- Thumbnail -->
                <?php if (!empty($blogShowImages)): ?>
                  <div class="px-card-img-wrap">
                    <?php if (!empty($_post['og_image'])): ?>
                      <a href="<?= px_post_url($basePath, $_post['slug'], $prettyUrls ?? false) ?>" tabindex="-1">
                        <img src="<?= px_thumb($basePath, $_post['og_image'], 'thumbnail') ?>"
                             alt="<?= htmlspecialchars($_post['page_title']) ?>"
                             loading="lazy" />
                      </a>
                    <?php else: ?>
                      <?php $_bg = $_post['category_color'] ?: px_palette($_i); ?>
                      <a href="<?= px_post_url($basePath, $_post['slug'], $prettyUrls ?? false) ?>" tabindex="-1" style="display:block;width:100%;height:100%;background:<?= htmlspecialchars($_bg, ENT_QUOTES) ?>">
                        <div class="px-card-placeholder"><?= mb_strtoupper(mb_substr(strip_tags($_post['page_title']), 0, 2)) ?></div>
                      </a>
                    <?php endif; ?>
                    <div class="px-card-img-overlay">
                      <div class="px-card-share">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(px_post_url($basePath, $_post['slug'], $prettyUrls ?? false)) ?>"
                           target="_blank" rel="noopener" title="Udostępnij na Facebook">
                          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/></svg>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(px_post_url($basePath, $_post['slug'], $prettyUrls ?? false)) ?>&text=<?= urlencode($_post['page_title']) ?>"
                           target="_blank" rel="noopener" title="Udostępnij na X">
                          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>
                        </a>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>

                <!-- Body -->
                <div class="px-card-body">
                  <?php if (!empty($_post['category_name'])): ?>
                    <div class="px-card-cat">
                      <a href="<?= px_cat_url($basePath, $_post['category_slug'], $prettyUrls ?? false) ?>"
                         class="px-cat-badge"
                         style="background:<?= htmlspecialchars($_post['category_color'] ?: px_palette($_i), ENT_QUOTES) ?>">
                        <?= htmlspecialchars($_post['category_name']) ?>
                      </a>
                    </div>
                  <?php endif; ?>

                  <div class="px-card-title">
                    <a href="<?= px_post_url($basePath, $_post['slug'], $prettyUrls ?? false) ?>"><?= htmlspecialchars($_post['page_title']) ?></a>
                  </div>

                  <?php if (!empty($_excerpt)): ?>
                    <p class="px-card-desc"><?= htmlspecialchars($_excerpt) ?></p>
                  <?php endif; ?>

                  <div class="px-card-meta">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/></svg>
                    <?= htmlspecialchars($_dateStr) ?>
                    <?php if (!empty($_post['click_count'])): ?>
                      <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                      <?= (int)$_post['click_count'] ?>
                    <?php endif; ?>
                    <a href="<?= px_post_url($basePath, $_post['slug'], $prettyUrls ?? false) ?>" class="px-card-readmore ms-auto">Czytaj →</a>
                  </div>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- PAGINATION -->
        <?php if (($totalPages ?? 1) > 1): ?>
          <nav class="px-pagination" aria-label="Paginacja">
            <ul class="pagination justify-content-center flex-wrap gap-1">
              <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= htmlspecialchars($basePath . '/?p=' . ($currentPage - 1)) ?>">‹</a>
              </li>
              <?php
              $start = max(1, $currentPage - 2);
              $end   = min($totalPages, $currentPage + 2);
              if ($start > 1): ?>
                <li class="page-item"><a class="page-link" href="<?= htmlspecialchars($basePath . '/?p=1') ?>">1</a></li>
                <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
              <?php endif; ?>
              <?php for ($p = $start; $p <= $end; $p++): ?>
                <li class="page-item <?= ($p === $currentPage) ? 'active' : '' ?>">
                  <a class="page-link" href="<?= htmlspecialchars($basePath . '/?p=' . $p) ?>"><?= $p ?></a>
                </li>
              <?php endfor; ?>
              <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                <li class="page-item"><a class="page-link" href="<?= htmlspecialchars($basePath . '/?p=' . $totalPages) ?>"><?= $totalPages ?></a></li>
              <?php endif; ?>
              <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= htmlspecialchars($basePath . '/?p=' . ($currentPage + 1)) ?>">›</a>
              </li>
            </ul>
          </nav>
        <?php endif; ?>

      <?php else: ?>
        <div class="px-empty">
          <h3>Brak wpisów</h3>
          <p>Nie znaleziono żadnych wpisów pasujących do wybranych kryteriów.</p>
          <a href="<?= htmlspecialchars($basePath) ?>/" class="btn btn-sm" style="background:var(--theme-primary,#2942ee);color:#fff;font-family:'Josefin Sans',sans-serif;font-size:.8rem;letter-spacing:.04em;border-radius:3px">Wróć do wszystkich</a>
        </div>
      <?php endif; ?>
    </div><!-- /col posts -->

    <!-- SIDEBAR -->
    <aside class="col-lg-4">
      <div class="px-sidebar">
        <?php require __DIR__ . '/_sidebar.php'; ?>
      </div>
    </aside>

  </div><!-- /row -->
</div><!-- /container -->

<!-- FOOTER -->
<footer class="px-footer">
  <div class="container">
    <div class="row g-4">
      <!-- Brand -->
      <div class="col-lg-4 col-md-6">
        <h5><?= htmlspecialchars($homeTitle) ?></h5>
        <?php if (!empty($homeSubtitle)): ?>
          <p style="font-size:.82rem;line-height:1.7;margin-bottom:16px"><?= htmlspecialchars($homeSubtitle) ?></p>
        <?php endif; ?>
        <?php if (!empty($socialLinks)): ?>
          <div class="px-footer-social">
            <?php
            $footerSocialDefs = ['facebook'=>'F','instagram'=>'I','twitter'=>'X','youtube'=>'Y','linkedin'=>'in','tiktok'=>'T','pinterest'=>'P'];
            $footerSocialSVGs = [
                'facebook'  => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/></svg>',
                'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"/></svg>',
                'twitter'   => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>',
                'youtube'   => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.007 2.007 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.007 2.007 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31.4 31.4 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.007 2.007 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A99.788 99.788 0 0 1 7.858 2h.193zM6.4 5.209v4.818l4.157-2.408L6.4 5.209z"/></svg>',
                'linkedin'  => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/></svg>',
                'tiktok'    => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3V0z"/></svg>',
                'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0a8 8 0 0 0-2.915 15.452c-.07-.633-.134-1.606.027-2.297.188-.69 1.279-5.42 1.279-5.42s-.327-.653-.327-1.618c0-1.517.879-2.651 1.97-2.651.93 0 1.38.698 1.38 1.535 0 .936-.594 2.338-.902 3.636-.257 1.086.542 1.969 1.608 1.969 1.93 0 3.42-2.034 3.42-4.972 0-2.599-1.87-4.418-4.537-4.418-3.089 0-4.902 2.317-4.902 4.709 0 .932.358 1.93.806 2.476a.32.32 0 0 1 .075.31c-.083.342-.266 1.088-.301 1.239-.048.2-.16.242-.369.146-1.379-.641-2.241-2.658-2.241-4.278 0-3.479 2.528-6.676 7.29-6.676 3.829 0 6.8 2.73 6.8 6.38 0 3.803-2.398 6.864-5.726 6.864-1.118 0-2.17-.581-2.53-1.265l-.688 2.566c-.248.961-.921 2.164-1.371 2.898A8 8 0 1 0 8 0z"/></svg>',
            ];
            foreach ($footerSocialSVGs as $key => $svg):
                if (!empty($socialLinks[$key])): ?>
                  <a href="<?= htmlspecialchars($socialLinks[$key], ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars(ucfirst($key)) ?>"><?= $svg ?></a>
                <?php endif;
            endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <!-- Categories -->
      <?php if (!empty($allCategories)): ?>
      <div class="col-lg-2 col-md-6">
        <h5>Kategorie</h5>
        <ul style="list-style:none;padding:0;margin:0">
          <?php foreach ($allCategories as $_cat): ?>
            <li style="margin-bottom:7px">
              <a href="<?= px_cat_url($basePath, $_cat['slug'], $prettyUrls ?? false) ?>"
                 style="font-size:.8rem;display:flex;align-items:center;gap:7px">
                <span style="width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($_cat['color'] ?: '#777', ENT_QUOTES) ?>;flex-shrink:0"></span>
                <?= htmlspecialchars($_cat['name']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <!-- Pages -->
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
    </div><!-- /row -->
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
<script>
(function() {
  var btn   = document.getElementById('pxSearchOpen');
  var close = document.getElementById('pxSearchClose');
  var overlay = document.getElementById('pxSearchOverlay');
  if (!btn || !overlay) return;
  btn.addEventListener('click', function() {
    overlay.classList.add('open');
    var inp = overlay.querySelector('input');
    if (inp) setTimeout(function() { inp.focus(); }, 60);
  });
  close.addEventListener('click', function() { overlay.classList.remove('open'); });
  overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.classList.remove('open'); });
  document.addEventListener('keydown', function(e) { if (e.key === 'Escape') overlay.classList.remove('open'); });
})();
</script>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
