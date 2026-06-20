<?php
  $pageTitle = ($mode === 'create' ? 'Nowy tag' : 'Edycja tagu') . ' — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header bg-info text-white">
            <h2 class="mb-0"><?= $mode === 'create' ? 'Nowy tag' : 'Edycja tagu' ?></h2>
          </div>
          <div class="card-body">
            <form method="post" action="<?= $mode === 'create' ? $basePath . '/admin/index.php?action=tag_new' : $basePath . '/admin/index.php?action=tag_edit&id=' . (int)$tag['id'] ?>">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />

              <div class="mb-3">
                <label for="name" class="form-label">Nazwa *</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($tag['name']) ?>" required />
              </div>

              <div class="mb-3">
                <label for="slug" class="form-label">Alias</label>
                <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($tag['slug']) ?>" placeholder="zostanie wygenerowany automatycznie jeśli puste" maxlength="100" />
                <small class="form-text text-muted">Max 100 znaków</small>
                <small class="form-text text-muted">Unikalna nazwa używana w URL (np. dla trybu blog)</small>
              </div>

              <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="<?= $basePath ?>/admin/index.php?action=tags" class="btn btn-secondary">Anuluj</a>
                <button type="submit" class="btn btn-primary">Zapisz</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
