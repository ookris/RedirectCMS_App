<?php
$pageTitle = $pageTitle ?? 'Panel — RedirectCMS';
$extraHead = $extraHead ?? '';
?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="icon" type="image/png" href="<?= htmlspecialchars($basePath . '/admin/static/img/logo_ikona_full.png') ?>" />
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.8/dist/flatly/bootstrap.min.css" rel="stylesheet" />
  
  <link href="<?= $basePath ?>/assets/admin.css" rel="stylesheet" />
  <?= $extraHead ?>
</head>
