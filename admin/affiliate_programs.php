<?php
  $pageTitle = 'Programy afiliacyjne — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>


  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Programy afiliacyjne</h2>
      <a href="<?= $basePath ?>/admin/index.php?action=affiliate_program_new" class="btn btn-primary">+ Nowy program</a>
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

    <?php if (empty($programs)): ?>
      <div class="alert bg-info-subtle border border-info text-info-emphasis">
        <strong>Brak programów.</strong> <a href="<?= $basePath ?>/admin/index.php?action=affiliate_program_new">Dodaj pierwszy program</a>.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-info">
            <tr>
              <th>Nazwa</th>
              <th class="col-w-100">Kolor</th>
              <th class="col-w-200">Data utworzenia</th>
              <th class="col-w-150">Akcje</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($programs as $program): ?>
              <tr>
                <td><strong><?= htmlspecialchars($program['name']) ?></strong></td>
                <td>
                  <span class="badge badge-dynamic" style="--badge-color: <?= htmlspecialchars($program['color'] ?? '#4CAF50') ?>;">
                    <?= htmlspecialchars($program['color'] ?? '#4CAF50') ?>
                  </span>
                </td>
                <td><small class="text-muted"><?= !empty($program['created_at']) ? date('Y-m-d H:i', strtotime($program['created_at'])) : '—' ?></small></td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="<?= $basePath ?>/admin/index.php?action=affiliate_program_edit&id=<?= (int)$program['id'] ?>" class="btn btn-sm btn-primary">Edytuj</a>
                    <form method="post" action="<?= $basePath ?>/admin/index.php?action=affiliate_program_delete&id=<?= (int)$program['id'] ?>" class="d-inline">
                      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Usunąć program? Powiązane linki pozostaną, ale bez przypisanego programu.')">Usuń</button>
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
