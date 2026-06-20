<!doctype html>
<html lang="pl">
<head>
  <script>
  (function(){
    var t=localStorage.getItem('pu-theme')||(window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');
    if(t==='dark')document.documentElement.setAttribute('data-theme','dark');
  }());
  </script>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars(!empty($pageMetaTitle) ? $pageMetaTitle : $pageTitle) ?> — <?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($pageMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars($pageMetaDescription) ?>" />
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
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
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
      margin: 0; padding: 0;
      background: var(--pu-bg); font-family: 'Roboto', system-ui, sans-serif;
      font-size: 15px; color: var(--pu-text);
      -webkit-font-smoothing: antialiased; transition: background .2s, color .2s;
    }
    a { color: inherit; text-decoration: none; }
    img { display: block; max-width: 100%; }

    /* HEADER */
    .pu-header {
      position: sticky; top: 0; z-index: 200;
      height: 60px; background: var(--pu-header-bg);
      border-bottom: 1px solid var(--pu-border);
      box-shadow: var(--pu-shadow); transition: background .2s, border-color .2s;
    }
    .pu-header-inner {
      max-width: 1280px; margin: 0 auto; padding: 0 20px; height: 100%;
      display: flex; align-items: center; gap: 16px;
    }
    .pu-logo {
      display: flex; align-items: center; gap: 10px;
      font-size: .98rem; font-weight: 700; color: var(--pu-text);
      flex-shrink: 0; transition: color .2s;
    }
    .pu-logo:hover { color: var(--pu-primary); }
    .pu-logo img { max-height: 32px; width: auto; }
    .pu-nav { flex: 1; display: flex; align-items: center; gap: 2px; overflow: hidden; }
    .pu-nav a {
      font-size: .83rem; font-weight: 500; padding: 5px 10px; border-radius: 6px;
      color: var(--pu-text); white-space: nowrap; transition: background .15s, color .15s;
    }
    .pu-nav a:hover { background: var(--pu-border); }
    .pu-nav a.active { color: var(--pu-primary); }
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
    @media (max-width: 767px) { .pu-nav { display: none; } .pu-header-inner { padding: 0 16px; } }

    /* BREADCRUMB */
    .pu-breadcrumb {
      border-bottom: 1px solid var(--pu-border);
      padding: 10px 0; background: var(--pu-card);
    }
    .pu-breadcrumb-inner {
      max-width: 1280px; margin: 0 auto; padding: 0 20px;
      display: flex; align-items: center; gap: 6px;
      font-size: .75rem; color: var(--pu-muted);
    }
    .pu-breadcrumb-inner a { color: var(--pu-primary); }
    .pu-breadcrumb-inner a:hover { text-decoration: underline; }
    @media (max-width: 767px) { .pu-breadcrumb-inner { padding: 0 16px; } }

    /* PAGE CONTENT */
    .pu-container { max-width: 820px; margin: 0 auto; padding: 36px 20px 56px; }
    .pu-page-card {
      background: var(--pu-card); border-radius: var(--pu-radius);
      box-shadow: var(--pu-shadow); padding: 40px 48px;
      border: 1px solid var(--pu-border);
    }
    .pu-page-title {
      font-size: 1.85rem; font-weight: 700; line-height: 1.25;
      color: var(--pu-text); margin: 0 0 24px; padding-bottom: 20px;
      border-bottom: 3px solid var(--pu-primary);
    }
    .pu-page-body {
      font-family: 'Merriweather', Georgia, serif;
      font-size: 1rem; line-height: 1.85; color: var(--pu-text);
    }
    .pu-page-body h2, .pu-page-body h3 {
      font-family: 'Roboto', sans-serif; font-weight: 700;
      margin-top: 28px; margin-bottom: 10px;
    }
    .pu-page-body h2 { font-size: 1.4rem; }
    .pu-page-body h3 { font-size: 1.15rem; }
    .pu-page-body p { margin: 0 0 18px; }
    .pu-page-body img { max-width: 100%; border-radius: 6px; margin: 16px 0; }
    .pu-page-body a { color: var(--pu-primary); text-decoration: underline; text-underline-offset: 3px; }
    .pu-page-body blockquote {
      border-left: 4px solid var(--pu-primary);
      background: color-mix(in srgb, var(--pu-primary) 6%, transparent);
      padding: 12px 20px; margin: 20px 0;
      border-radius: 0 6px 6px 0; font-style: italic; color: var(--pu-muted);
    }
    .pu-page-body ul, .pu-page-body ol { padding-left: 22px; margin-bottom: 18px; }
    .pu-page-body li { margin-bottom: 6px; }
    .pu-back-link {
      display: inline-flex; align-items: center; gap: 6px;
      margin-top: 32px; font-size: .84rem; font-weight: 600;
      color: var(--pu-primary); transition: opacity .15s;
    }
    .pu-back-link:hover { opacity: .75; }
    @media (max-width: 767px) {
      .pu-container { padding: 20px 16px 40px; }
      .pu-page-card { padding: 24px 20px; }
      .pu-page-title { font-size: 1.4rem; }
    }

    /* FOOTER */
    .pu-footer {
      background: var(--pu-card); border-top: 1px solid var(--pu-border);
      padding: 40px 0 0; margin-top: 20px;
    }
    .pu-footer-copy {
      max-width: 1280px; margin: 24px auto 0;
      padding: 14px 20px; border-top: 1px solid var(--pu-border);
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 8px; font-size: .74rem; color: var(--pu-muted);
    }
    .pu-footer-copy a { color: var(--pu-primary); }
    .pu-footer-copy a:hover { opacity: .8; }
    @media (max-width: 767px) { .pu-footer-copy { flex-direction: column; padding: 14px 16px; text-align: center; gap: 4px; } }
  </style>
</head>
<body>

<?php
function pu_page_url_pg(string $bp, string $slug): string {
    return htmlspecialchars($bp . '/?page=' . $slug, ENT_QUOTES);
}
?>

<header class="pu-header">
  <div class="pu-header-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="pu-logo">
      <?php if (!empty($brandingLogo)): ?>
        <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($homeTitle) ?>" />
      <?php endif; ?>
      <span><?= htmlspecialchars($homeTitle) ?></span>
    </a>
    <nav class="pu-nav">
      <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
      <?php foreach (($navPages ?? []) as $_np): ?>
        <a href="<?= pu_page_url_pg($basePath, $_np['slug']) ?>"
           class="<?= (isset($currentSlug) && $currentSlug === $_np['slug']) ? 'active' : '' ?>">
          <?= htmlspecialchars($_np['title']) ?>
        </a>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= pu_page_url_pg($basePath, 'contact') ?>"
           class="<?= (isset($currentSlug) && $currentSlug === 'contact') ? 'active' : '' ?>">Kontakt</a>
      <?php endif; ?>
    </nav>
    <div class="pu-header-actions">
      <button class="pu-icon-btn" id="puDarkToggle" aria-label="Tryb ciemny">
        <svg class="pu-moon-icon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="pu-sun-icon"  width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
    </div>
  </div>
</header>

<div class="pu-breadcrumb">
  <div class="pu-breadcrumb-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
    <span>›</span>
    <span><?= htmlspecialchars($pageTitle) ?></span>
  </div>
</div>

<div class="pu-container">
  <div class="pu-page-card">
    <h1 class="pu-page-title"><?= htmlspecialchars($pageTitle) ?></h1>
    <div class="pu-page-body">
      <?= $pageHtml ?? '' ?>
    </div>
    <a href="<?= htmlspecialchars($basePath) ?>/" class="pu-back-link">← Wróć do strony głównej</a>
  </div>
</div>

<footer class="pu-footer">
  <div class="pu-footer-copy">
    <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>. Wszystkie prawa zastrzeżone.</span>
    <span>
      Powered by <a href="https://redirectcms.pl" target="_blank" rel="noopener noreferrer">RedirectCMS</a>
      &middot; Theme: <a href="https://github.com/blogger-templates/Plus-UI-V3.7.0" target="_blank" rel="noopener noreferrer">Plus UI</a>
    </span>
  </div>
</footer>

<?php if (!empty($pageJs)): ?><script><?= $pageJs ?></script><?php endif; ?>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
<script>
document.getElementById('puDarkToggle').addEventListener('click', function () {
  var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('pu-theme', next);
});
</script>
</body>
</html>
