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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:wght@400;500;600&display=swap" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    *, *::before, *::after { box-sizing: border-box; }

    /* ─── BASE ───────────────────────────────────────────────── */
    body {
      margin: 0; padding: 0;
      background: var(--theme-body-bg, #fff);
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      font-size: 16px;
      color: var(--theme-text, #292929);
      -webkit-font-smoothing: antialiased;
    }
    a { color: inherit; text-decoration: none; }
    img { display: block; max-width: 100%; }

    /* ─── HEADER ─────────────────────────────────────────────── */
    .wb-header {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      min-height: 75px;
      background: var(--theme-primary, #ffc017);
      border-bottom: 1.5px solid #000;
      display: flex; align-items: center;
    }
    .wb-header-inner {
      max-width: 1192px; width: 100%; margin: 0 auto;
      padding: 0 24px;
      display: flex; align-items: center; justify-content: space-between;
      gap: 16px;
    }
    .wb-logo-link {
      display: inline-flex; align-items: center; gap: 10px;
      color: #000; text-decoration: none; flex-shrink: 0;
    }
    .wb-logo-img { max-height: 28px; width: auto; }
    .wb-site-title {
      font-size: 1.15rem;
      font-weight: 600;
      color: #000;
      letter-spacing: -.01em;
      white-space: nowrap;
    }
    .wb-site-subtitle {
      font-size: .78rem;
      font-weight: 400;
      color: rgba(0,0,0,.6);
      margin-top: 1px;
    }
    .wb-nav {
      display: flex; align-items: center; gap: 24px;
      flex-shrink: 0;
    }
    .wb-nav a {
      font-size: .875rem; font-weight: 500; color: #292929;
      transition: color .15s;
    }
    .wb-nav a:hover { color: #000; }
    .wb-nav-search {
      background: none; border: none; cursor: pointer; padding: 4px;
      color: #292929; display: flex; align-items: center;
    }
    .wb-nav-search:hover { color: #000; }
    @media (max-width: 767px) {
      .wb-nav { display: none; }
      .wb-header-inner { padding: 0 16px; }
    }

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
      width: min(640px, 88vw);
      display: flex; align-items: center; gap: 0;
      border-bottom: 2px solid #292929;
      padding-bottom: 8px;
    }
    .wb-search-form input {
      flex: 1; background: transparent; border: none; outline: none;
      font-family: 'Inter', sans-serif; font-size: 1.4rem; font-weight: 500;
      color: #292929; padding: 0;
    }
    .wb-search-form input::placeholder { color: #c0c0c0; }
    .wb-search-hint {
      margin-top: 14px;
      font-size: .8rem; color: #a8a8a8;
      font-family: 'Inter', sans-serif;
    }
    .wb-search-close {
      position: absolute; top: 24px; right: 28px;
      background: none; border: none; cursor: pointer;
      font-size: 1.5rem; color: #757575; line-height: 1;
    }
    .wb-search-close:hover { color: #292929; }

    /* ─── LAYOUT ─────────────────────────────────────────────── */
    .wb-container {
      max-width: 1192px; margin: 0 auto; padding: 0 24px;
    }
    .wb-body {
      padding-top: 115px; /* header 75px + gap */
      display: flex; flex-direction: row; gap: 64px; align-items: flex-start;
    }
    .wb-main { flex: 1; min-width: 0; max-width: 695px; }
    .wb-aside {
      width: 320px; flex-shrink: 0;
      position: sticky; top: 95px;
    }
    @media (max-width: 1023px) {
      .wb-aside { display: none; }
      .wb-main { max-width: 100%; }
    }
    @media (max-width: 767px) {
      .wb-container { padding: 0 16px; }
      .wb-body { padding-top: 95px; gap: 32px; }
    }

    /* ─── FILTER BAR ─────────────────────────────────────────── */
    .wb-filter {
      display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
      padding: 14px 0; border-bottom: 1px solid var(--theme-border, #e6e6e6);
      margin-bottom: 24px;
    }
    .wb-chip {
      display: inline-block;
      padding: 3px 12px; border-radius: 99px;
      font-size: .78rem; font-weight: 500;
      background: var(--theme-chip-bg, #f2f2f2);
      color: var(--theme-muted, #757575);
      border: none; cursor: pointer; text-decoration: none;
      transition: background .15s, color .15s;
    }
    .wb-chip:hover { background: var(--theme-border, #e6e6e6); color: var(--theme-text, #292929); }
    .wb-chip.active {
      background: var(--theme-text, #292929);
      color: #fff;
    }
    .wb-chip-cat { /* per-category: inherits wb-chip styles */ }

    /* ─── ACTIVE FILTER NOTICE ───────────────────────────────── */
    .wb-filter-notice {
      font-size: .82rem; color: var(--theme-muted, #757575);
      margin-bottom: 8px; padding-bottom: 8px;
      border-bottom: 1px solid var(--theme-border, #e6e6e6);
    }
    .wb-filter-notice a { color: var(--theme-accent, #1a8917); }

    /* ─── POST ROW (flat, Medium-style) ─────────────────────── */
    .wb-post-row {
      display: flex; flex-direction: row; justify-content: space-between;
      align-items: flex-start;
      padding: 24px 0;
      border-bottom: 1px solid var(--theme-border, #e6e6e6);
      gap: 20px;
    }
    .wb-post-row:first-child { padding-top: 0; }
    .wb-post-text { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .wb-author-row {
      display: flex; align-items: center; gap: 8px;
      margin-bottom: 6px;
    }
    .wb-author-avatar {
      width: 20px; height: 20px; border-radius: 50%;
      background: var(--theme-border, #e6e6e6);
      flex-shrink: 0; overflow: hidden; font-size: 10px; font-weight: 700;
      display: flex; align-items: center; justify-content: center;
      color: var(--theme-muted, #757575);
    }
    .wb-author-name {
      font-size: .8rem; font-weight: 500;
      color: var(--theme-text, #292929);
    }
    .wb-post-title {
      font-size: 1rem; font-weight: 700; line-height: 1.45;
      color: var(--theme-text, #292929);
      margin: 0 0 4px; display: -webkit-box;
      -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    @media (min-width: 768px) { .wb-post-title { font-size: 1.25rem; } }
    .wb-post-title a { color: inherit; text-decoration: none; }
    .wb-post-title a:hover { color: var(--theme-accent, #1a8917); }
    .wb-post-desc {
      display: none;
    }
    @media (min-width: 768px) {
      .wb-post-desc {
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden; font-size: .9rem; line-height: 1.55;
        color: var(--theme-muted, #757575); margin-bottom: 6px;
      }
    }
    .wb-post-meta {
      display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
      margin-top: 8px;
      font-size: .78rem; color: var(--theme-muted, #757575);
    }
    .wb-meta-chip {
      padding: 2px 10px; border-radius: 99px;
      background: var(--theme-chip-bg, #f2f2f2);
      font-size: .72rem; font-weight: 500;
      color: var(--theme-muted, #757575);
      transition: background .15s;
      text-decoration: none; white-space: nowrap;
    }
    .wb-meta-chip:hover { background: var(--theme-border, #e6e6e6); }
    .wb-star-badge {
      display: inline-flex; align-items: center;
      color: var(--theme-muted, #757575);
      flex-shrink: 0;
    }
    .wb-post-thumb {
      flex-shrink: 0;
      width: 100px; height: 67px;
      overflow: hidden;
      background: var(--theme-border, #e6e6e6);
    }
    @media (min-width: 768px) { .wb-post-thumb { width: 200px; height: 134px; } }
    .wb-post-thumb-img {
      width: 100%; height: 100%; object-fit: cover;
      transition: transform .3s ease;
    }
    .wb-post-thumb:hover .wb-post-thumb-img { transform: scale(1.04); }
    .wb-thumb-skeleton {
      width: 100%; height: 100%;
      background: #e2e8f0;
      animation: wb-pulse 2s ease-in-out infinite;
    }
    @keyframes wb-pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: .5; }
    }

    /* ─── SECTION HEADING ────────────────────────────────────── */
    .wb-section-title {
      font-size: .75rem; font-weight: 700; letter-spacing: .08em;
      text-transform: uppercase; color: var(--theme-muted, #757575);
      margin: 0 0 16px; padding-bottom: 10px;
      border-bottom: 1px solid var(--theme-border, #e6e6e6);
    }

    /* ─── PAGINATION ─────────────────────────────────────────── */
    .wb-pagination {
      display: flex; justify-content: center; gap: 6px;
      padding: 36px 0;
    }
    .wb-page-btn {
      min-width: 36px; height: 36px; padding: 0 10px;
      display: inline-flex; align-items: center; justify-content: center;
      border-radius: 50%; border: 1px solid var(--theme-border, #e6e6e6);
      font-size: .82rem; font-weight: 500;
      color: var(--theme-text, #292929); text-decoration: none;
      transition: background .15s, color .15s;
    }
    .wb-page-btn:hover, .wb-page-btn.active {
      background: var(--theme-text, #292929); color: #fff; border-color: transparent;
    }
    .wb-page-btn.disabled { opacity: .35; pointer-events: none; }
    .wb-page-ellipsis {
      display: inline-flex; align-items: center; justify-content: center;
      min-width: 36px; height: 36px; font-size: .82rem;
      color: var(--theme-muted, #757575);
    }

    /* ─── SIDEBAR ────────────────────────────────────────────── */
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
    .wb-search-form-sidebar svg { flex-shrink: 0; color: var(--theme-muted, #757575); }

    /* ─── FOOTER ─────────────────────────────────────────────── */
    .wb-footer {
      border-top: 1px solid var(--theme-border, #e6e6e6);
      padding: 24px 0;
      margin-top: 40px;
    }
    .wb-footer-inner {
      max-width: 1192px; margin: 0 auto; padding: 0 24px;
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 12px;
    }
    .wb-footer-copy {
      font-size: .75rem; color: var(--theme-muted, #757575);
    }
    .wb-footer-copy a {
      color: var(--theme-accent, #1a8917);
      transition: opacity .15s;
    }
    .wb-footer-copy a:hover { opacity: .75; }
    .wb-footer-links {
      display: flex; gap: 16px;
    }
    .wb-footer-links a {
      font-size: .75rem; color: var(--theme-muted, #757575);
      transition: color .15s;
    }
    .wb-footer-links a:hover { color: var(--theme-text, #292929); }
    @media (max-width: 767px) {
      .wb-footer-inner { flex-direction: column; align-items: flex-start; gap: 8px; }
      .wb-footer { padding: 20px 0; }
      .wb-footer-inner { padding: 0 16px; }
    }

    /* ─── EMPTY STATE ────────────────────────────────────────── */
    .wb-empty {
      padding: 60px 0; text-align: center;
      color: var(--theme-muted, #757575);
    }
    .wb-empty h3 {
      font-size: 1.1rem; font-weight: 600; color: var(--theme-text, #292929);
      margin: 0 0 8px;
    }
    .wb-empty a {
      display: inline-block; margin-top: 16px;
      font-size: .85rem; font-weight: 600; color: var(--theme-accent, #1a8917);
      border-bottom: 1px solid var(--theme-accent, #1a8917);
    }
  </style>
</head>
<body>

<?php
// ─── HELPERS ──────────────────────────────────────────────────────────────
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
function wb_excerpt(string $html, int $len): string {
    $text = strip_tags($html);
    return mb_strlen($text) > $len ? mb_substr($text, 0, $len) . '…' : $text;
}
function wb_date(string $dt): string {
    $ts = strtotime($dt);
    return $ts ? date('d.m.Y', $ts) : '';
}
// Star SVG badge (featured marker for first 3 posts)
function wb_star(): string {
    return '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
}
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

<!-- HEADER -->
<header class="wb-header">
  <div class="wb-header-inner">
    <!-- Logo / site title -->
    <a href="<?= htmlspecialchars($basePath) ?>/" class="wb-logo-link">
      <?php if (!empty($brandingLogo)): ?>
        <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo, ENT_QUOTES) ?>"
             alt="<?= htmlspecialchars($homeTitle) ?>"
             class="wb-logo-img" />
      <?php endif; ?>
      <div>
        <div class="wb-site-title"><?= htmlspecialchars($homeTitle) ?></div>
        <?php if (!empty($homeSubtitle)): ?>
          <div class="wb-site-subtitle"><?= htmlspecialchars($homeSubtitle) ?></div>
        <?php endif; ?>
      </div>
    </a>

    <!-- Desktop navigation -->
    <nav class="wb-nav">
      <?php foreach (($navPages ?? []) as $_np): ?>
        <a href="<?= wb_page_url($basePath, $_np['slug']) ?>"><?= htmlspecialchars($_np['title']) ?></a>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= wb_page_url($basePath, 'contact') ?>">Kontakt</a>
      <?php endif; ?>
      <button class="wb-nav-search" id="wbSearchOpen" type="button" aria-label="Szukaj">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </button>
    </nav>

    <!-- Mobile search -->
    <button class="wb-nav-search" id="wbSearchOpenMob" type="button" aria-label="Szukaj" style="display:none">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    </button>
  </div>
</header>

<div class="wb-container">
  <div class="wb-body">

    <!-- ─── MAIN COLUMN ──────────────────────────────────────── -->
    <main class="wb-main">

      <!-- Category / Tag filter -->
      <?php if (!empty($allCategories) && empty($activeCategory) && empty($activeTag)): ?>
        <div class="wb-filter">
          <a href="<?= htmlspecialchars($basePath) ?>/" class="wb-chip active">Wszystkie</a>
          <?php foreach ($allCategories as $_cat): ?>
            <a href="<?= wb_cat_url($basePath, $_cat['slug'], $prettyUrls ?? false) ?>"
               class="wb-chip wb-chip-cat">
              <?= htmlspecialchars($_cat['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php elseif (!empty($activeCategory) || !empty($activeTag)): ?>
        <div class="wb-filter-notice">
          <?php if (!empty($activeCategory)): ?>
            Kategoria: <strong><?= htmlspecialchars($activeCategory['name']) ?></strong>
          <?php else: ?>
            Tag: <strong>#<?= htmlspecialchars($activeTag['name']) ?></strong>
          <?php endif; ?>
          &nbsp;— <a href="<?= htmlspecialchars($basePath) ?>/">wyczyść filtr ×</a>
        </div>
      <?php endif; ?>

      <!-- Post listing -->
      <?php if (!empty($blogPosts)): ?>
        <?php foreach ($blogPosts as $_i => $_post):
          $_desc    = wb_excerpt($_post['page_description'] ?? '', $blogDescLength ?? 140);
          $_dateStr = wb_date($_post['created_at'] ?? '');
          $_initials = mb_strtoupper(mb_substr(strip_tags($_post['page_title']), 0, 1));
        ?>
          <article class="wb-post-row">
            <!-- Text column -->
            <div class="wb-post-text">
              <div class="wb-author-row">
                <div class="wb-author-avatar"><?= $_initials ?></div>
                <span class="wb-author-name"><?= htmlspecialchars($homeTitle) ?></span>
              </div>

              <h2 class="wb-post-title">
                <a href="<?= wb_post_url($basePath, $_post['slug'], $prettyUrls ?? false) ?>"><?= htmlspecialchars($_post['page_title']) ?></a>
              </h2>

              <?php if (!empty($_desc)): ?>
                <p class="wb-post-desc"><?= htmlspecialchars($_desc) ?></p>
              <?php endif; ?>

              <div class="wb-post-meta">
                <?php if (!empty($_dateStr)): ?>
                  <span><?= htmlspecialchars($_dateStr) ?></span>
                <?php endif; ?>
                <?php if (!empty($_post['category_name'])): ?>
                  <a href="<?= wb_cat_url($basePath, $_post['category_slug'], $prettyUrls ?? false) ?>"
                     class="wb-meta-chip">
                    <?= htmlspecialchars($_post['category_name']) ?>
                  </a>
                <?php endif; ?>
                <?php if (!empty($_post['tags'])): ?>
                  <?php foreach (array_slice($_post['tags'], 0, 2) as $_tag): ?>
                    <a href="<?= wb_tag_url($basePath, $_tag['slug'], $prettyUrls ?? false) ?>"
                       class="wb-meta-chip">
                      #<?= htmlspecialchars($_tag['name']) ?>
                    </a>
                  <?php endforeach; ?>
                <?php endif; ?>
                <?php if (!empty($_post['click_count'])): ?>
                  <span><?= (int)$_post['click_count'] ?> odsłon</span>
                <?php endif; ?>
                <?php if ($_i < 3): ?>
                  <span class="wb-star-badge" title="Wyróżniony"><?= wb_star() ?></span>
                <?php endif; ?>
              </div>
            </div>

            <!-- Thumbnail (right-aligned) -->
            <?php if (!empty($blogShowImages) && !empty($_post['og_image'])): ?>
              <a href="<?= wb_post_url($basePath, $_post['slug'], $prettyUrls ?? false) ?>"
                 class="wb-post-thumb" aria-hidden="true" tabindex="-1">
                <img class="wb-post-thumb-img"
                     src="<?= wb_thumb($basePath, $_post['og_image'], 'listing') ?>"
                     alt="<?= htmlspecialchars($_post['page_title']) ?>"
                     loading="lazy" />
              </a>
            <?php elseif (!empty($blogShowImages)): ?>
              <a href="<?= wb_post_url($basePath, $_post['slug'], $prettyUrls ?? false) ?>"
                 class="wb-post-thumb" aria-hidden="true" tabindex="-1">
                <div class="wb-thumb-skeleton"></div>
              </a>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>

        <!-- Pagination -->
        <?php if (($totalPages ?? 1) > 1): ?>
          <nav class="wb-pagination" aria-label="Paginacja">
            <a href="<?= htmlspecialchars($basePath . '/?p=' . ($currentPage - 1)) ?>"
               class="wb-page-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>" aria-label="Poprzednia">‹</a>
            <?php
            $start = max(1, $currentPage - 2);
            $end   = min($totalPages, $currentPage + 2);
            if ($start > 1): ?><a href="<?= htmlspecialchars($basePath . '/?p=1') ?>" class="wb-page-btn">1</a><?php endif;
            if ($start > 2): ?><span class="wb-page-ellipsis">…</span><?php endif;
            for ($p = $start; $p <= $end; $p++): ?>
              <a href="<?= htmlspecialchars($basePath . '/?p=' . $p) ?>"
                 class="wb-page-btn <?= $p === $currentPage ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor;
            if ($end < $totalPages - 1): ?><span class="wb-page-ellipsis">…</span><?php endif;
            if ($end < $totalPages): ?><a href="<?= htmlspecialchars($basePath . '/?p=' . $totalPages) ?>" class="wb-page-btn"><?= $totalPages ?></a><?php endif; ?>
            <a href="<?= htmlspecialchars($basePath . '/?p=' . ($currentPage + 1)) ?>"
               class="wb-page-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>" aria-label="Następna">›</a>
          </nav>
        <?php endif; ?>

      <?php else: ?>
        <div class="wb-empty">
          <h3>Brak wpisów</h3>
          <p style="font-size:.9rem">Nie znaleziono żadnych wpisów.</p>
          <a href="<?= htmlspecialchars($basePath) ?>/">Wróć do strony głównej</a>
        </div>
      <?php endif; ?>
    </main>

    <!-- ─── SIDEBAR ──────────────────────────────────────────── -->
    <aside class="wb-aside">
      <!-- Search -->
      <form class="wb-search-form-sidebar" method="get" action="<?= htmlspecialchars($basePath) ?>/" role="search">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="q" placeholder="Szukaj…" aria-label="Szukaj" />
      </form>

      <?php require __DIR__ . '/_sidebar.php'; ?>

      <!-- Tags -->
      <?php if (!empty($allTags) && empty($sidebarData)): ?>
        <div class="wb-widget">
          <div class="wb-widget-title">Tagi</div>
          <?php foreach ($allTags as $_tag): ?>
            <a href="<?= wb_tag_url($basePath, $_tag['slug'], $prettyUrls ?? false) ?>"
               class="wb-chip wb-meta-chip" style="margin:3px 2px;display:inline-block">
              #<?= htmlspecialchars($_tag['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Categories -->
      <?php if (!empty($allCategories) && empty($sidebarData)): ?>
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

  </div><!-- /wb-body -->
</div><!-- /wb-container -->

<!-- FOOTER -->
<footer class="wb-footer">
  <div class="wb-footer-inner">
    <div class="wb-footer-copy">
      &copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>.
      Powered by <a href="https://redirectcms.pl" target="_blank" rel="noopener noreferrer">RedirectCMS</a>
      &middot; Theme: <a href="https://github.com/elhakimyasya/Webium-Blogger-Theme" target="_blank" rel="noopener noreferrer">Webium</a>
    </div>
    <div class="wb-footer-links">
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= wb_page_url($basePath, 'contact') ?>">Kontakt</a>
      <?php endif; ?>
      <?php foreach (array_slice($navPages ?? [], 0, 3) as $_np): ?>
        <a href="<?= wb_page_url($basePath, $_np['slug']) ?>"><?= htmlspecialchars($_np['title']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php if (!empty($homeFooter)): ?>
    <div style="max-width:1192px;margin:12px auto 0;padding:12px 24px 0;border-top:1px solid var(--theme-border,#e6e6e6);font-size:.75rem;color:var(--theme-muted,#757575)"><?= $homeFooter ?></div>
  <?php endif; ?>
</footer>

<script>
(function() {
  var overlay = document.getElementById('wbSearchOverlay');
  var closeBtn = document.getElementById('wbSearchClose');
  function openSearch() {
    overlay.classList.add('open');
    var inp = overlay.querySelector('input');
    if (inp) setTimeout(function() { inp.focus(); }, 60);
  }
  function closeSearch() { overlay.classList.remove('open'); }

  var btn1 = document.getElementById('wbSearchOpen');
  var btn2 = document.getElementById('wbSearchOpenMob');
  if (btn1) btn1.addEventListener('click', openSearch);
  if (btn2) btn2.addEventListener('click', openSearch);
  if (closeBtn) closeBtn.addEventListener('click', closeSearch);
  overlay.addEventListener('click', function(e) { if (e.target === overlay) closeSearch(); });
  document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeSearch(); });

  // Show mobile search btn below 768px
  if (btn2) {
    function checkWidth() { btn2.style.display = window.innerWidth < 768 ? 'flex' : 'none'; }
    checkWidth();
    window.addEventListener('resize', checkWidth);
  }
})();
</script>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
