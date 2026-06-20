<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars(!empty($pageMetaTitle) ? $pageMetaTitle : $pageTitle) ?> — <?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($pageMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars($pageMetaDescription) ?>">
  <?php endif; ?>
  <?php require __DIR__ . '/_head_css.php'; ?>
</head>
<body>

<?php require __DIR__ . '/_navbar.php'; ?>

<!-- BREADCRUMB BAR -->
<div class="post-breadcrumb-bar">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="<?= $basePath ?>/">Strona główna</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          <?= htmlspecialchars($pageTitle) ?>
        </li>
      </ol>
    </nav>
  </div>
</div>

<!-- TREŚĆ STRONY -->
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-9">
      <article class="page-content">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <?php if (!empty($pageHtml)): ?>
          <div class="post-body"><?= $pageHtml ?></div>
        <?php else: ?>
          <p class="text-muted">Ta strona nie ma jeszcze treści.</p>
        <?php endif; ?>
      </article>
      <div class="mt-4">
        <a href="<?= $basePath ?>/" class="post-back-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
          </svg>
          Strona główna
        </a>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
