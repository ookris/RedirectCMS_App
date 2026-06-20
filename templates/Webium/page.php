<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars(!empty($pageMetaTitle) ? $pageMetaTitle : $pageTitle) ?> — <?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($pageMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars($pageMetaDescription) ?>" />
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:wght@400;500;600&display=swap" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    *, *::before, *::after { box-sizing: border-box; }

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
      min-height: 57px;
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
      font-size: 1.1rem; font-weight: 600; color: #000;
      letter-spacing: -.01em; white-space: nowrap;
    }
    .wb-nav {
      display: flex; align-items: center; gap: 20px;
    }
    .wb-nav a {
      font-size: .875rem; font-weight: 500; color: #292929;
      transition: opacity .15s;
    }
    .wb-nav a:hover { opacity: .65; }
    @media (max-width: 767px) {
      .wb-nav { display: none; }
      .wb-header-inner { padding: 0 16px; }
    }

    /* ─── BREADCRUMB ─────────────────────────────────────────── */
    .wb-breadcrumb {
      border-bottom: 1px solid var(--theme-border, #e6e6e6);
      padding: 10px 0;
    }
    .wb-breadcrumb-inner {
      max-width: 1192px; margin: 0 auto; padding: 0 24px;
      display: flex; align-items: center; gap: 6px;
      font-size: .75rem; color: var(--theme-muted, #757575);
    }
    .wb-breadcrumb-inner a {
      color: var(--theme-accent, #1a8917);
      text-decoration: none;
    }
    .wb-breadcrumb-inner a:hover { text-decoration: underline; }
    .wb-breadcrumb-sep { color: var(--theme-border, #e6e6e6); }
    @media (max-width: 767px) {
      .wb-breadcrumb-inner { padding: 0 16px; }
    }

    /* ─── LAYOUT ─────────────────────────────────────────────── */
    .wb-container {
      max-width: 740px; margin: 0 auto;
      padding: 0 24px;
    }
    .wb-page-wrap {
      padding-top: 80px; /* header + gap */
      padding-bottom: 60px;
    }
    @media (max-width: 767px) {
      .wb-container { padding: 0 16px; }
      .wb-page-wrap { padding-top: 68px; }
    }

    /* ─── PAGE CONTENT ────────────────────────────────────────── */
    .wb-page-title {
      font-size: 2rem; font-weight: 700; line-height: 1.2;
      color: var(--theme-text, #292929);
      margin: 32px 0 24px;
      padding-bottom: 20px;
      border-bottom: 2px solid var(--theme-primary, #ffc017);
    }
    .wb-page-body {
      font-family: 'Lora', Georgia, serif;
      font-size: 1.05rem; line-height: 1.85;
      color: var(--theme-text, #292929);
    }
    .wb-page-body h2 {
      font-family: 'Inter', sans-serif;
      font-size: 1.35rem; font-weight: 700;
      margin: 32px 0 12px;
      color: var(--theme-text, #292929);
    }
    .wb-page-body h3 {
      font-family: 'Inter', sans-serif;
      font-size: 1.1rem; font-weight: 600;
      margin: 24px 0 10px;
      color: var(--theme-text, #292929);
    }
    .wb-page-body p { margin: 0 0 18px; }
    .wb-page-body img { max-width: 100%; border-radius: 4px; margin: 16px 0; }
    .wb-page-body a { color: var(--theme-accent, #1a8917); text-decoration: underline; }
    .wb-page-body blockquote {
      border-left: 3px solid var(--theme-primary, #ffc017);
      background: #fffbf0;
      padding: 14px 20px;
      margin: 20px 0;
      font-style: italic;
      color: var(--theme-muted, #757575);
    }
    .wb-page-body ul, .wb-page-body ol { padding-left: 22px; }
    .wb-page-body li { margin-bottom: 6px; }

    .wb-back-link {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: .82rem; font-weight: 600;
      color: var(--theme-accent, #1a8917);
      margin-top: 36px;
      text-decoration: none;
      border-bottom: 1px solid transparent;
      transition: border-color .15s;
    }
    .wb-back-link:hover { border-bottom-color: var(--theme-accent, #1a8917); }

    @media (max-width: 576px) { .wb-page-title { font-size: 1.5rem; } }

    /* ─── FOOTER ─────────────────────────────────────────────── */
    .wb-footer {
      border-top: 1px solid var(--theme-border, #e6e6e6);
      padding: 24px 0;
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
      .wb-footer-inner { flex-direction: column; align-items: flex-start; gap: 8px; padding: 0 16px; }
      .wb-footer { padding: 20px 0; }
    }
  </style>
</head>
<body>

<?php
function wb_page_url_p(string $bp, string $slug): string {
    return htmlspecialchars($bp . '/?page=' . $slug, ENT_QUOTES);
}
function wb_cat_url_p(string $bp, string $slug, bool $pretty): string {
    return $pretty
        ? htmlspecialchars($bp . '/category/' . $slug, ENT_QUOTES)
        : htmlspecialchars($bp . '/?category=' . $slug, ENT_QUOTES);
}
?>

<!-- HEADER -->
<header class="wb-header">
  <div class="wb-header-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="wb-logo-link">
      <?php if (!empty($brandingLogo)): ?>
        <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($homeTitle) ?>" class="wb-logo-img" />
      <?php endif; ?>
      <span class="wb-site-title"><?= htmlspecialchars($homeTitle) ?></span>
    </a>
    <nav class="wb-nav">
      <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
      <?php foreach (($navPages ?? []) as $_np): ?>
        <a href="<?= wb_page_url_p($basePath, $_np['slug']) ?>"
           <?= (isset($currentSlug) && $currentSlug === $_np['slug']) ? 'style="font-weight:700"' : '' ?>>
          <?= htmlspecialchars($_np['title']) ?>
        </a>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= wb_page_url_p($basePath, 'contact') ?>"
           <?= (isset($currentSlug) && $currentSlug === 'contact') ? 'style="font-weight:700"' : '' ?>>
          Kontakt
        </a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<!-- BREADCRUMB -->
<div class="wb-breadcrumb">
  <div class="wb-breadcrumb-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
    <span class="wb-breadcrumb-sep">›</span>
    <span><?= htmlspecialchars($pageTitle) ?></span>
  </div>
</div>

<!-- PAGE CONTENT -->
<div class="wb-page-wrap">
  <div class="wb-container">
    <h1 class="wb-page-title"><?= htmlspecialchars($pageTitle) ?></h1>
    <div class="wb-page-body">
      <?= $pageHtml ?? '' ?>
    </div>
    <a href="<?= htmlspecialchars($basePath) ?>/" class="wb-back-link">
      ← Wróć do strony głównej
    </a>
  </div>
</div>

<!-- FOOTER -->
<footer class="wb-footer">
  <div class="wb-footer-inner">
    <div class="wb-footer-copy">
      &copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>.
      Powered by <a href="https://redirectcms.pl" target="_blank" rel="noopener noreferrer">RedirectCMS</a>
      &middot; Theme: <a href="https://github.com/elhakimyasya/Webium-Blogger-Theme" target="_blank" rel="noopener noreferrer">Webium</a>
    </div>
    <div class="wb-footer-links">
      <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
      <?php foreach (array_slice($navPages ?? [], 0, 3) as $_np): ?>
        <a href="<?= wb_page_url_p($basePath, $_np['slug']) ?>"><?= htmlspecialchars($_np['title']) ?></a>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= wb_page_url_p($basePath, 'contact') ?>">Kontakt</a>
      <?php endif; ?>
    </div>
  </div>
</footer>

<?php if (!empty($pageJs)): ?>
  <script><?= $pageJs ?></script>
<?php endif; ?>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
