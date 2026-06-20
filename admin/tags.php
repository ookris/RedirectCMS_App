<?php
  $pageTitle = 'Tagi — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Tagi</h2>
      <a href="<?= $basePath ?>/admin/index.php?action=tag_new" class="btn btn-primary">+ Nowy tag</a>
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

    <?php if (empty($tags)): ?>
      <div class="alert bg-info-subtle border border-info text-info-emphasis">
        <strong>Brak tagów.</strong> <a href="<?= $basePath ?>/admin/index.php?action=tag_new">Dodaj pierwszy tag</a>.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-info">
            <tr>
              <th>Nazwa</th>
              <th>Alias</th>
              <th style="width: 100px;">Linki</th>
              <th style="width: 200px;">Data utworzenia</th>
              <th style="width: 150px;">Akcje</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tags as $tag): ?>
              <tr>
                <td>
                  <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis"><?= htmlspecialchars($tag['name']) ?></span>
                </td>
                <td><code><?= htmlspecialchars($tag['slug']) ?></code></td>
                <td class="text-center">
                  <span class="badge bg-info-subtle border border-info text-info-emphasis"><?= (int)$tag['link_count'] ?></span>
                </td>
                <td><small class="text-muted"><?= date('Y-m-d H:i', strtotime($tag['created_at'])) ?></small></td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="<?= $basePath ?>/admin/index.php?action=tag_edit&id=<?= (int)$tag['id'] ?>" class="btn btn-sm btn-primary">Edytuj</a>
                    <form method="post" action="<?= $basePath ?>/admin/index.php?action=tag_delete&id=<?= (int)$tag['id'] ?>" class="d-inline">
                      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Usunąć tag? Linki z tym tagiem nie zostaną usunięte.')">Usuń</button>
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
