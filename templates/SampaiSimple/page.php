<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars(!empty($pageMetaTitle) ? $pageMetaTitle : $pageTitle) ?> | <?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($pageMetaDescription)): ?><meta name="description" content="<?= htmlspecialchars($pageMetaDescription) ?>" /><?php endif; ?>

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css?family=Google+Sans:300,400,700" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    body, html { height: 100%; }
    body { background: #e8e8e8; font-family: 'Google Sans', Arial, sans-serif; }
    #fullpage { background: #fff; min-height: 100%; }
    @media (min-width: 768px) { #fullpage { max-width: 1000px; margin: 0 auto; border-left: 1px solid rgba(0,0,0,.23); border-right: 1px solid rgba(0,0,0,.23); box-shadow: 0 0 10px 0 rgba(0,0,0,.24);} }
    #navlink ul { list-style:none; margin:0; padding:0; }
    #navlink li { display:inline-block; }
    #navlink li:not(:last-child) { margin-right:.75rem; }
    #page-content img { max-width:100%; height:auto; }
    .brand-logo { max-height:44px; width:auto; }
    .site-title { font-size:1.45rem; font-weight:700; color:#111; text-decoration:none; }
    .site-subtitle { font-size:.9rem; color:#6c757d; }
  </style>
</head>
<body>
<section id="fullpage" class="d-flex flex-column">
  <section class="border-bottom">
    <nav class="py-2 py-md-3">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-auto"><a href="<?= htmlspecialchars($basePath) ?>/">Start</a></div>
          <div class="col text-right"><?php if (!empty($contactEnabled)): ?><a href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a><?php endif; ?></div>
        </div>
      </div>
    </nav>
  </section>

  <section class="border-bottom py-3 py-md-4">
    <div class="container-fluid">
      <a class="d-flex align-items-center text-decoration-none" href="<?= htmlspecialchars($basePath) ?>/">
        <?php if (!empty($brandingLogo)): ?><img class="brand-logo mr-3" src="<?= htmlspecialchars($basePath . '/' . ltrim((string)$brandingLogo, '/')) ?>" alt="<?= htmlspecialchars($homeTitle) ?>" /><?php endif; ?>
        <span>
          <span class="site-title d-block"><?= htmlspecialchars($homeTitle) ?></span>
          <?php if (!empty($homeSubtitle)): ?><small class="site-subtitle d-block"><?= htmlspecialchars($homeSubtitle) ?></small><?php endif; ?>
        </span>
      </a>
    </div>
  </section>

  <section>
    <nav class="border-bottom bg-white">
      <div class="py-3 container-fluid" id="navlink">
        <ul>
          <li><a href="<?= htmlspecialchars($basePath) ?>/">Blog</a></li>
          <?php foreach ($navPages as $_np): ?>
            <li><a href="<?= htmlspecialchars($basePath . '/?page=' . rawurlencode((string)$_np['slug'])) ?>"><?= htmlspecialchars($_np['title']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </nav>
  </section>

  <div class="container-fluid py-3 flex-fill">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars($basePath) ?>/">Start</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($pageTitle) ?></li>
      </ol>
    </nav>

    <h3 class="text-primary"><?= htmlspecialchars($pageTitle) ?></h3>
    <div id="page-content"><?= $pageHtml ?? '' ?></div>
  </div>

  <footer class="border-top py-3 mt-auto">
    <div class="container-fluid small text-muted d-flex justify-content-between flex-wrap">
      <span><?= $homeFooter ?: ('&copy; ' . date('Y') . ' ' . htmlspecialchars($homeTitle)) ?></span>
      <span>Theme: SampaiSimple</span>
    </div>
  </footer>
</section>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
