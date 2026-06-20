<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars(!empty($pageMetaTitle) ? $pageMetaTitle : $pageTitle) ?> | <?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($pageMetaDescription)): ?><meta name="description" content="<?= htmlspecialchars((string)$pageMetaDescription) ?>" /><?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>

  <style>
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body { font-family: "Roboto Slab", Georgia, serif; line-height: 1.5; color: var(--theme-text, #111111); background: var(--theme-body_bg, #fafafa); }
    a { color: #000; text-decoration: none; }
    a:hover { text-decoration: underline; }

    .mn-shell { max-width: 900px; margin: 0 auto; padding: 0 15px; }
    .mn-header { border-top: 5px solid var(--theme-primary, #666666); border-bottom: 4px double var(--theme-primary, #666666); text-align: center; padding: 15px 0 8px; background: var(--theme-header_bg, #ffffff); margin-bottom: 14px; }
    .mn-logo { margin: 0; line-height: 1; font-size: clamp(2rem, 5vw, 3.4rem); }
    .mn-tagline { display: block; margin-top: .55rem; color: #666; font-size: .95rem; }

    .mn-menu { display: flex; justify-content: space-between; gap: .7rem; flex-wrap: wrap; align-items: center; margin-bottom: .9rem; }
    .mn-menu ul { list-style: none; margin: 0; padding: 0; }
    .mn-menu li { display: inline-block; font-weight: 700; margin-right: .3rem; }
    .mn-menu a { display: inline-block; padding: .35rem .55rem; }

    .article-meta { text-align: center; padding: 5px; border-radius: 5px; }
    .title { font-size: 2rem; line-height: 1.2em; }
    main { margin: 1rem 0 15px; background: #fff; padding: 1rem; }
    main img, main iframe, main video { max-width: 100%; height: auto; }

    .mn-footer { margin-top: 1rem; background: var(--theme-footer_bg, #666666); color: var(--theme-footer_text, #ffffff); padding: 1rem 0; text-align: center; }
    .mn-copyright { color: #ddd; font-size: .9rem; }
  </style>
</head>
<body>
<header class="mn-header">
  <div class="mn-shell">
    <h1 class="mn-logo"><a href="<?= htmlspecialchars($basePath) ?>/"><?= htmlspecialchars($homeTitle) ?></a></h1>
    <span class="mn-tagline"><?= htmlspecialchars(!empty($homeSubtitle) ? $homeSubtitle : 'mini newspaper template') ?></span>
  </div>
</header>

<div class="mn-shell">
  <nav class="mn-menu">
    <ul>
      <li><a href="<?= htmlspecialchars($basePath) ?>/">Start</a></li>
      <?php foreach ($navPages as $_np): ?>
        <?php $_isActivePage = ((string)($_GET['page'] ?? '') === (string)$_np['slug']); ?>
        <li><a style="<?= $_isActivePage ? 'text-decoration: underline;' : '' ?>" href="<?= htmlspecialchars($basePath . '/?page=' . rawurlencode((string)$_np['slug'])) ?>"><?= htmlspecialchars((string)$_np['title']) ?></a></li>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?><li><a href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a></li><?php endif; ?>
    </ul>
  </nav>

  <article class="article-meta">
    <h1><span class="title"><?= htmlspecialchars($pageTitle) ?></span></h1>
  </article>

  <main>
    <div><?= $pageHtml ?? '' ?></div>
  </main>
</div>

<footer class="mn-footer">
  <div class="mn-shell">
    <div class="mn-copyright"><?= $homeFooter ?: ('&copy; ' . date('Y') . ' ' . htmlspecialchars($homeTitle)) ?></div>
  </div>
</footer>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
