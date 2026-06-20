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
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;400;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    * { transition: color .2s ease, background-color .2s ease, border-color .2s ease, opacity .2s ease, transform .2s ease; }

    body { background: var(--theme-body-bg, #f7f7f7); font-family: 'Open Sans', sans-serif; font-size: 14px; color: #2e2e2e; }

    .px-topbar { background: var(--theme-topbar-bg, #1a1a2e); color: var(--theme-topbar-text, #ccccdd); font-family: 'Josefin Sans', sans-serif; font-size: 12px; font-weight: 600; padding: 7px 0; }
    .px-topbar a { color: var(--theme-topbar-text, #ccccdd); text-decoration: none; opacity: .75; }
    .px-topbar a:hover { opacity: 1; }

    .px-header { background: var(--theme-header-bg, #ffffff); color: var(--theme-header-text, #2e2e2e); padding: 22px 0; border-bottom: 1px solid #e8e8e8; }
    .px-logo { height: 48px; width: auto; object-fit: contain; }
    .px-site-title { font-family: 'Josefin Sans', sans-serif; font-size: 1.9rem; font-weight: 700; letter-spacing: -.02em; color: var(--theme-header-text, #2e2e2e); text-decoration: none; }
    .px-site-title:hover { color: var(--theme-primary, #2942ee); }
    .px-site-subtitle { font-size: .8rem; color: #8e8e95; margin-top: 3px; font-family: 'Josefin Sans', sans-serif; }

    .px-nav { background: var(--theme-nav-bg, #ffffff); border-bottom: 3px solid var(--theme-primary, #2942ee); position: sticky; top: 0; z-index: 200; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
    .px-nav .nav-link { font-family: 'Josefin Sans', sans-serif; font-size: 13px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--theme-nav-text, #2e2e2e); padding: 12px 14px; border-bottom: 3px solid transparent; margin-bottom: -3px; }
    .px-nav .nav-link:hover, .px-nav .nav-link.active { color: var(--theme-primary, #2942ee); border-bottom-color: var(--theme-primary, #2942ee); }

    .px-breadcrumb { background: #fff; border-bottom: 1px solid #eee; padding: 10px 0; }
    .px-breadcrumb .breadcrumb-item { font-family: 'Josefin Sans', sans-serif; font-size: .72rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
    .px-breadcrumb .breadcrumb-item a { color: var(--theme-primary, #2942ee); text-decoration: none; }
    .px-breadcrumb .breadcrumb-item.active { color: #8e8e95; }
    .px-breadcrumb .breadcrumb-item + .breadcrumb-item::before { color: #ccc; }

    .px-page-box { background: #fff; border-radius: 8px; box-shadow: 0 1px 6px rgba(0,0,0,.07); padding: 40px 48px; max-width: 820px; margin: 0 auto; }
    .px-page-box h1 { font-family: 'Josefin Sans', sans-serif; font-size: 2rem; font-weight: 700; color: #1a1a2a; margin-bottom: 24px; padding-bottom: 18px; border-bottom: 2px solid var(--theme-primary, #2942ee); }
    .px-page-content { font-size: .95rem; line-height: 1.85; color: #374151; }
    .px-page-content h2, .px-page-content h3 { font-family: 'Josefin Sans', sans-serif; font-weight: 700; margin-top: 28px; margin-bottom: 12px; }
    .px-page-content h2 { font-size: 1.4rem; }
    .px-page-content h3 { font-size: 1.1rem; }
    .px-page-content img { max-width: 100%; border-radius: 6px; }
    .px-page-content a { color: var(--theme-primary, #2942ee); }
    .px-page-content blockquote { border-left: 4px solid var(--theme-primary, #2942ee); background: #f8f9fe; padding: 14px 20px; border-radius: 0 6px 6px 0; font-style: italic; color: #555; margin: 20px 0; }
    .px-page-content ul, .px-page-content ol { padding-left: 22px; }
    .px-page-content li { margin-bottom: 5px; }

    @media (max-width: 576px) { .px-page-box { padding: 22px 18px; } .px-page-box h1 { font-size: 1.5rem; } }

    .px-footer { background: var(--theme-footer-bg, #1a1a2e); color: var(--theme-footer-text, #9999aa); margin-top: 60px; padding: 40px 0 0; }
    .px-footer h5 { font-family: 'Josefin Sans', sans-serif; font-size: .8rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #fff; margin-bottom: 14px; }
    .px-footer a { color: var(--theme-footer-text, #9999aa); text-decoration: none; }
    .px-footer a:hover { color: #fff; }
    .px-footer-copy { font-size: .72rem; border-top: 1px solid rgba(255,255,255,.08); padding: 14px 0; margin-top: 28px; text-align: center; opacity: .55; }
  </style>
</head>
<body>

<?php
function px_page_url_p(string $basePath, string $slug): string {
    return htmlspecialchars($basePath . '/?page=' . $slug, ENT_QUOTES);
}
function px_cat_url_p(string $basePath, string $slug, bool $prettyUrls): string {
    return $prettyUrls
        ? htmlspecialchars($basePath . '/category/' . $slug, ENT_QUOTES)
        : htmlspecialchars($basePath . '/?category=' . $slug, ENT_QUOTES);
}
?>

<!-- TOPBAR -->
<div class="px-topbar">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="<?= htmlspecialchars($basePath) ?>/" style="font-size:11px;letter-spacing:.06em;text-transform:uppercase">← Strona główna</a>
    <?php if (!empty($contactEnabled)): ?>
      <a href="<?= px_page_url_p($basePath, 'contact') ?>" style="font-size:11px;letter-spacing:.06em;text-transform:uppercase">Kontakt</a>
    <?php endif; ?>
  </div>
</div>

<!-- HEADER -->
<header class="px-header">
  <div class="container">
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
  </div>
</header>

<!-- NAVIGATION -->
<nav class="px-nav">
  <div class="container d-flex align-items-center">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="nav-link">Strona główna</a>
    <?php foreach (($navPages ?? []) as $_np): ?>
      <a href="<?= px_page_url_p($basePath, $_np['slug']) ?>"
         class="nav-link <?= (isset($currentSlug) && $currentSlug === $_np['slug']) ? 'active' : '' ?>">
        <?= htmlspecialchars($_np['title']) ?>
      </a>
    <?php endforeach; ?>
    <?php if (!empty($contactEnabled)): ?>
      <a href="<?= px_page_url_p($basePath, 'contact') ?>" class="nav-link <?= (isset($currentSlug) && $currentSlug === 'contact') ? 'active' : '' ?>">Kontakt</a>
    <?php endif; ?>
  </div>
</nav>

<!-- BREADCRUMB -->
<div class="px-breadcrumb">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($pageTitle) ?></li>
      </ol>
    </nav>
  </div>
</div>

<!-- PAGE CONTENT -->
<div class="container" style="margin-top:36px; margin-bottom:48px;">
  <div class="px-page-box">
    <h1><?= htmlspecialchars($pageTitle) ?></h1>
    <div class="px-page-content">
      <?= $pageHtml ?? '' ?>
    </div>
  </div>
  <div style="text-align:center;margin-top:24px">
    <a href="<?= htmlspecialchars($basePath) ?>/"
       style="display:inline-flex;align-items:center;gap:6px;font-family:'Josefin Sans',sans-serif;font-size:.78rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--theme-primary,#2942ee);text-decoration:none">
      ← Powrót do strony głównej
    </a>
  </div>
</div>

<!-- FOOTER -->
<footer class="px-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6">
        <h5><?= htmlspecialchars($homeTitle) ?></h5>
        <?php if (!empty($homeSubtitle)): ?>
          <p style="font-size:.82rem;line-height:1.7"><?= htmlspecialchars($homeSubtitle) ?></p>
        <?php endif; ?>
      </div>
      <?php if (!empty($navPages) || !empty($contactEnabled)): ?>
      <div class="col-md-3">
        <h5>Strony</h5>
        <ul style="list-style:none;padding:0;margin:0">
          <li style="margin-bottom:7px"><a href="<?= htmlspecialchars($basePath) ?>/" style="font-size:.8rem">Strona główna</a></li>
          <?php foreach (($navPages ?? []) as $_np): ?>
            <li style="margin-bottom:7px"><a href="<?= px_page_url_p($basePath, $_np['slug']) ?>" style="font-size:.8rem"><?= htmlspecialchars($_np['title']) ?></a></li>
          <?php endforeach; ?>
          <?php if (!empty($contactEnabled)): ?>
            <li style="margin-bottom:7px"><a href="<?= px_page_url_p($basePath, 'contact') ?>" style="font-size:.8rem">Kontakt</a></li>
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

<?php if (!empty($pageJs)): ?>
  <script><?= $pageJs ?></script>
<?php endif; ?>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
