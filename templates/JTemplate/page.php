<?php
function jt_page_url_pg(string $bp, string $slug): string {
    return htmlspecialchars($bp . '/?page=' . $slug, ENT_QUOTES);
}
?>
<!doctype html>
<html lang="pl">
<head>
  <script>
  (function(){
    var t=localStorage.getItem('jt-theme')||(window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');
    if(t==='dark')document.documentElement.setAttribute('data-theme','dark');
  }());
  </script>

  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title><?= htmlspecialchars(!empty($pageMetaTitle) ? $pageMetaTitle : $pageTitle) ?> — <?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($pageMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars($pageMetaDescription) ?>"/>
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet"/>
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>

  <style>
    :root {
      --jt-accent:  var(--theme-accent,  #dc5b5d);
      --jt-dark:    var(--theme-dark,    #2A2F36);
      --jt-muted:   var(--theme-muted,   #6C7A89);
      --jt-border:  var(--theme-border,  #e2e6ea);
      --jt-bg:      var(--theme-body_bg, #f2f4f5);
      --jt-card:    var(--theme-card_bg, #ffffff);
      --jt-text:    var(--theme-dark,    #2A2F36);
      --jt-header:  #ffffff;
      --jt-radius:  10px;
      --jt-shadow:  0 4px 16px rgba(0,0,0,.07);
      --jt-font:    'Poppins', system-ui, sans-serif;
    }
    [data-theme="dark"] {
      --jt-bg:     var(--theme-dark_bg,   #212121);
      --jt-card:   var(--theme-dark_card, #2d2d2d);
      --jt-text:   #f0f0f0; --jt-muted: #909090; --jt-border: #3a3a3a;
      --jt-header: var(--theme-dark_header, #1a1a1a);
      --jt-shadow: 0 4px 16px rgba(0,0,0,.3);
    }
    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      margin: 0; padding: 0; font-family: var(--jt-font);
      font-size: 15px; color: var(--jt-text); background: var(--jt-bg);
      -webkit-font-smoothing: antialiased; transition: background .25s, color .25s;
    }
    a { color: inherit; text-decoration: none; }
    img { display: block; max-width: 100%; }

    /* HEADER */
    .jt-header {
      position: sticky; top: 0; z-index: 300; background: var(--jt-header);
      border-bottom: 1px solid var(--jt-border); box-shadow: 0 2px 8px rgba(0,0,0,.06);
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
    .jt-nav { flex: 1; display: flex; align-items: center; gap: 2px; overflow: hidden; }
    .jt-nav a {
      font-size: .82rem; font-weight: 500; padding: 6px 12px; border-radius: 6px;
      white-space: nowrap; color: var(--jt-text); transition: color .15s;
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
    .jt-sun-icon { display: none; }
    [data-theme="dark"] .jt-moon-icon { display: none; }
    [data-theme="dark"] .jt-sun-icon  { display: block; }
    .jt-hamburger { display: none; }
    @media (max-width: 900px) { .jt-nav { display: none; } .jt-hamburger { display: flex; } .jt-header-inner { padding: 0 16px; } }

    /* MOBILE DRAWER */
    .jt-mobile-overlay { display: none; position: fixed; inset: 0; z-index: 400; background: rgba(0,0,0,.45); opacity: 0; transition: opacity .25s; }
    .jt-mobile-overlay.open { opacity: 1; }
    .jt-mobile-drawer { position: fixed; top: 0; right: -280px; z-index: 500; width: 280px; height: 100dvh; background: var(--jt-card); box-shadow: -6px 0 24px rgba(0,0,0,.15); transition: right .28s cubic-bezier(.4,0,.2,1); display: flex; flex-direction: column; overflow-y: auto; }
    .jt-mobile-drawer.open { right: 0; }
    .jt-drawer-head { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--jt-border); }
    .jt-drawer-head span { font-size: 1rem; font-weight: 700; }
    .jt-drawer-nav { padding: 12px 0; }
    .jt-drawer-nav a { display: block; padding: 11px 22px; font-size: .9rem; font-weight: 500; color: var(--jt-text); border-left: 3px solid transparent; transition: color .15s, border-color .15s, background .15s; }
    .jt-drawer-nav a:hover { color: var(--jt-accent); border-left-color: var(--jt-accent); background: color-mix(in srgb, var(--jt-accent) 6%, transparent); }

    /* BREADCRUMB */
    .jt-breadcrumb { background: var(--jt-card); border-bottom: 1px solid var(--jt-border); }
    .jt-breadcrumb-inner { max-width: 1280px; margin: 0 auto; padding: 10px 24px; display: flex; align-items: center; gap: 6px; font-size: .73rem; color: var(--jt-muted); }
    .jt-breadcrumb-inner a { color: var(--jt-accent); }
    .jt-breadcrumb-inner a:hover { text-decoration: underline; }

    /* PAGE */
    .jt-container { max-width: 860px; margin: 0 auto; padding: 40px 24px 60px; }
    .jt-page-card { background: var(--jt-card); border-radius: var(--jt-radius); box-shadow: var(--jt-shadow); border: 1px solid var(--jt-border); overflow: hidden; }
    .jt-page-header { padding: 32px 40px 24px; border-bottom: 1px solid var(--jt-border); }
    .jt-page-title { font-size: 1.8rem; font-weight: 700; line-height: 1.25; color: var(--jt-text); margin: 0; }
    .jt-page-accent { display: block; width: 48px; height: 4px; background: var(--jt-accent); border-radius: 2px; margin-top: 12px; }
    .jt-page-body { padding: 32px 40px; font-family: 'Merriweather', Georgia, serif; font-size: .975rem; line-height: 1.9; color: var(--jt-text); }
    .jt-page-body h2 { font-family: var(--jt-font); font-size: 1.3rem; font-weight: 700; margin: 28px 0 10px; }
    .jt-page-body h3 { font-family: var(--jt-font); font-size: 1.08rem; font-weight: 600; margin: 22px 0 8px; }
    .jt-page-body p { margin: 0 0 18px; }
    .jt-page-body a { color: var(--jt-accent); text-decoration: underline; text-underline-offset: 3px; }
    .jt-page-body img { max-width: 100%; border-radius: 8px; margin: 16px 0; }
    .jt-page-body blockquote { border-left: 4px solid var(--jt-accent); background: color-mix(in srgb, var(--jt-accent) 6%, transparent); padding: 14px 22px; margin: 22px 0; border-radius: 0 8px 8px 0; font-style: italic; color: var(--jt-muted); }
    .jt-page-body ul, .jt-page-body ol { padding-left: 22px; margin-bottom: 18px; }
    .jt-page-body li { margin-bottom: 6px; }
    .jt-back { display: inline-flex; align-items: center; gap: 6px; padding: 24px 40px 32px; font-size: .82rem; font-weight: 600; color: var(--jt-accent); transition: opacity .15s; }
    .jt-back:hover { opacity: .75; }
    @media (max-width: 600px) {
      .jt-container { padding: 24px 16px 40px; }
      .jt-page-header { padding: 22px 20px 18px; }
      .jt-page-title  { font-size: 1.4rem; }
      .jt-page-body   { padding: 20px 20px; font-size: .92rem; }
      .jt-back { padding: 16px 20px 24px; }
    }

    /* FOOTER */
    .jt-footer { background: var(--jt-dark); color: rgba(255,255,255,.7); padding: 48px 0 0; margin-top: 40px; }
    [data-theme="dark"] .jt-footer { background: #111; border-top: 1px solid var(--jt-border); }
    .jt-footer-copy { border-top: 1px solid rgba(255,255,255,.1); padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; font-size: .73rem; color: rgba(255,255,255,.45); }
    .jt-footer-copy a { color: var(--jt-accent); }
    @media (max-width: 600px) { .jt-footer-copy { padding: 12px 16px; flex-direction: column; text-align: center; } }
  </style>
</head>
<body>

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
      <a href="<?= jt_page_url_pg($basePath, $_np['slug']) ?>"
         class="<?= (isset($currentSlug) && $currentSlug === $_np['slug']) ? 'active' : '' ?>">
        <?= htmlspecialchars($_np['title']) ?>
      </a>
    <?php endforeach; ?>
    <?php if (!empty($contactEnabled)): ?>
      <a href="<?= jt_page_url_pg($basePath, 'contact') ?>"
         class="<?= (isset($currentSlug) && $currentSlug === 'contact') ? 'active' : '' ?>">Kontakt</a>
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
        <a href="<?= jt_page_url_pg($basePath, $_np['slug']) ?>"
           class="<?= (isset($currentSlug) && $currentSlug === $_np['slug']) ? 'active' : '' ?>">
          <?= htmlspecialchars($_np['title']) ?>
        </a>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= jt_page_url_pg($basePath, 'contact') ?>"
           class="<?= (isset($currentSlug) && $currentSlug === 'contact') ? 'active' : '' ?>">Kontakt</a>
      <?php endif; ?>
    </nav>
    <div class="jt-header-actions">
      <button class="jt-icon-btn" id="jtDarkToggle" aria-label="Tryb ciemny">
        <svg class="jt-moon-icon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="jt-sun-icon"  width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <button class="jt-icon-btn jt-hamburger" id="jtHamburger" aria-label="Menu">
        <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>
  </div>
</header>

<!-- Breadcrumb -->
<div class="jt-breadcrumb">
  <div class="jt-breadcrumb-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
    <span>›</span>
    <span><?= htmlspecialchars($pageTitle) ?></span>
  </div>
</div>

<div class="jt-container">
  <div class="jt-page-card">
    <div class="jt-page-header">
      <h1 class="jt-page-title"><?= htmlspecialchars($pageTitle) ?></h1>
      <span class="jt-page-accent"></span>
    </div>
    <div class="jt-page-body">
      <?= $pageHtml ?? '' ?>
    </div>
    <a href="<?= htmlspecialchars($basePath) ?>/" class="jt-back">← Wróć do strony głównej</a>
  </div>
</div>

<footer class="jt-footer">
  <div class="jt-footer-copy">
    <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>. Wszelkie prawa zastrzeżone.</span>
    <span>Powered by <a href="https://redirectcms.pl" target="_blank" rel="noopener noreferrer">RedirectCMS</a> &middot; Theme: <a href="https://github.com/zaferzent/JTemplate" target="_blank" rel="noopener noreferrer">JTemplate</a></span>
  </div>
</footer>

<?php if (!empty($pageJs)): ?><script><?= $pageJs ?></script><?php endif; ?>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
<script>
(function () {
  document.getElementById('jtDarkToggle').addEventListener('click', function () {
    var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('jt-theme', next);
  });
  var hamburger   = document.getElementById('jtHamburger');
  var drawer      = document.getElementById('jtDrawer');
  var overlay     = document.getElementById('jtOverlay');
  var drawerClose = document.getElementById('jtDrawerClose');
  function openDrawer() { drawer.classList.add('open'); overlay.style.display = 'block'; setTimeout(function () { overlay.classList.add('open'); }, 10); }
  function closeDrawer() { drawer.classList.remove('open'); overlay.classList.remove('open'); setTimeout(function () { overlay.style.display = 'none'; }, 260); }
  hamburger.addEventListener('click', openDrawer);
  drawerClose.addEventListener('click', closeDrawer);
  overlay.addEventListener('click', closeDrawer);
}());
</script>
</body>
</html>
