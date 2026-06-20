<?php
  $pageTitle = 'Kategorie — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>


  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Kategorie</h2>
      <a href="<?= $basePath ?>/admin/index.php?action=category_new" class="btn btn-primary">+ Nowa kategoria</a>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert bg-success-subtle border border-success text-success-emphasis alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert bg-danger-subtle border border-danger text-danger-emphasis alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (empty($categories)): ?>
      <div class="alert bg-info-subtle border border-info text-info-emphasis">
        <strong>Brak kategorii.</strong> <a href="<?= $basePath ?>/admin/index.php?action=category_new">Dodaj pierwszą kategorię</a>.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-info">
            <tr>
              <th class="col-w-60">Ikona</th>
              <th>Nazwa</th>
              <th>Alias</th>
              <th>Opis</th>
              <th class="col-w-100">Linki</th>
              <th class="col-w-150">Akcje</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $cat): ?>
              <tr>
                <td class="text-center">
                  <?php if (!empty($cat['icon_image_thumb'])): ?>
                    <img src="<?= $basePath . '/' . htmlspecialchars($cat['icon_image_thumb']) ?>" alt="Ikona" class="rounded icon-preview" />
                  <?php else: ?>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-earmark-excel" viewBox="0 0 16 16">
                    <path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.54a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/>
                    <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                  </svg>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge" style="--badge-color: <?= htmlspecialchars($cat['color']) ?>;">
                    <?= htmlspecialchars($cat['name']) ?>
                  </span>
                </td>
                <td><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                <td>
                  <?php if (!empty($cat['description'])): ?>
                    <small class="text-muted"><?= htmlspecialchars(substr($cat['description'], 0, 60)) ?><?= strlen($cat['description']) > 60 ? '...' : '' ?></small>
                  <?php else: ?>
                    <small class="text-muted">—</small>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis"><?= (int)$cat['link_count'] ?></span>
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="<?= $basePath ?>/admin/index.php?action=category_edit&id=<?= (int)$cat['id'] ?>" class="btn btn-sm btn-primary">Edytuj</a>
                    <form method="post" action="<?= $basePath ?>/admin/index.php?action=category_delete&id=<?= (int)$cat['id'] ?>" class="d-inline">
                      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Usunąć kategorię? Linki z tej kategorii nie zostaną usunięte.')">Usuń</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
