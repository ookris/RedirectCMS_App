<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars(!empty($pageMetaTitle) ? $pageMetaTitle : $pageTitle) ?> | <?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($pageMetaDescription)): ?><meta name="description" content="<?= htmlspecialchars((string)$pageMetaDescription) ?>" /><?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=PT+Sans:wght@400;700&display=swap" rel="stylesheet">
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>

  <style>
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    html { font-family: "PT Sans", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; }
    @media (min-width: 58em) { html { font-size: 20px; } }
    body { color: var(--theme-text, #515151); background: var(--theme-body_bg, #fff); }

    a { color: var(--theme-primary, #268bd2); text-decoration: none; }
    a:hover, a:focus { text-decoration: underline; }

    .sidebar {
      text-align: center;
      padding: 2rem 1rem;
      color: rgba(255,255,255,.72);
      background: var(--theme-header_bg, #202020);
    }
    .sidebar a { color: var(--theme-header-text, #fff); }
    .sidebar-about h1 {
      margin: 0 0 .35rem;
      font-family: "Abril Fatface", serif;
      font-size: 2.35rem;
      line-height: 1.08;
      color: var(--theme-header-text, #fff);
    }
    .sidebar-about .lead { margin: 0 0 1rem; font-size: 0.95rem; }
    .sidebar-nav { margin: 0 0 1.2rem; }
    .sidebar-nav-item { display: block; line-height: 1.75; }
    .sidebar-nav-item.active { font-weight: 700; }

    .content {
      padding: 2rem 1.2rem 3rem;
      max-width: 44rem;
      margin: 0 auto;
    }
    .page-title { margin: 0 0 .8rem; font-size: 2rem; line-height: 1.25; color: #313131; }
    .page-body { font-size: .95rem; }
    .page-body img { max-width: 100%; height: auto; border-radius: 4px; }
    .sidebar-foot { margin-top: 1.2rem; font-size: .8rem; }

    @media (min-width: 48em) {
      .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 18rem;
        text-align: left;
      }
      .sidebar-sticky {
        position: absolute;
        right: 1rem;
        bottom: 1rem;
        left: 1rem;
      }
      .content {
        margin-left: 20rem;
        margin-right: 2rem;
        max-width: 38rem;
        padding-top: 4rem;
      }
    }
    @media (min-width: 64em) {
      .content { margin-left: 22rem; margin-right: 4rem; }
    }
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-sticky">
    <div class="sidebar-about">
      <h1><a href="<?= htmlspecialchars($basePath) ?>/"><?= htmlspecialchars($homeTitle) ?></a></h1>
      <?php if (!empty($homeSubtitle)): ?><p class="lead"><?= htmlspecialchars($homeSubtitle) ?></p><?php endif; ?>
    </div>

    <nav class="sidebar-nav">
      <a class="sidebar-nav-item" href="<?= htmlspecialchars($basePath) ?>/">Start</a>
      <?php foreach ($navPages as $_np): ?>
        <?php $_isActivePage = ((string)($_GET['page'] ?? '') === (string)$_np['slug']); ?>
        <a class="sidebar-nav-item <?= $_isActivePage ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath . '/?page=' . rawurlencode((string)$_np['slug'])) ?>"><?= htmlspecialchars((string)$_np['title']) ?></a>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?>
        <a class="sidebar-nav-item" href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a>
      <?php endif; ?>
    </nav>

    <p class="sidebar-foot"><?= $homeFooter ?: ('&copy; ' . date('Y') . ' ' . htmlspecialchars($homeTitle)) ?></p>
  </div>
</aside>

<main class="content">
  <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
  <div class="page-body"><?= $pageHtml ?? '' ?></div>
</main>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
