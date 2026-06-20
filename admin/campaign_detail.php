<?php
  $pageTitle = htmlspecialchars($campaign['name']) . ' — Kampania — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-5">
    <?php if (!empty($_SESSION['toast_message'])): ?>
      <div class="alert <?= ($_SESSION['toast_type'] ?? 'info') === 'success' ? 'bg-success-subtle border border-success text-success-emphasis' : 'bg-danger-subtle border border-danger text-danger-emphasis' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['toast_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
      </div>
      <?php unset($_SESSION['toast_message'], $_SESSION['toast_type']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
      <div class="d-flex align-items-center gap-3">
        <div style="width: 8px; height: 65px; border-radius: 3px; background: <?= htmlspecialchars($campaign['color'] ?? '#2196F3') ?>;"></div>
        <div>
          <h2 class="mb-0"><?= htmlspecialchars($campaign['name']) ?></h2>
          <?php if (!empty($campaign['description'])): ?>
            <p class="text-muted mb-0"><?= htmlspecialchars($campaign['description']) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= $basePath ?>/admin/index.php?action=campaign_edit&id=<?= (int)$campaign['id'] ?>" class="btn btn-outline-primary">Edytuj</a>
      </div>
    </div>

    <!-- Stats cards -->
    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
      <div class="col">
        <div class="card shadow-sm text-center">
          <div class="card-body">
            <div class="fs-3 fw-bold text-primary"><?= number_format($stats['total_clicks']) ?></div>
            <small class="text-muted">Kliknięć łącznie</small>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card shadow-sm text-center">
          <div class="card-body">
            <div class="fs-3 fw-bold text-success"><?= number_format($stats['today_clicks']) ?></div>
            <small class="text-muted">Dzisiaj</small>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card shadow-sm text-center">
          <div class="card-body">
            <div class="fs-3 fw-bold"><?= count($links) ?></div>
            <small class="text-muted">Linków w kampanii</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Links table -->
    <div class="card shadow-sm">
      <div class="card-header">
        <h5 class="mb-0">Linki w kampanii</h5>
      </div>
      <div class="card-body p-0">
        <?php if (empty($links)): ?>
          <div class="text-center text-muted py-5">
            <p class="mb-3">Brak linków w kampanii.</p>
            <a href="<?= $basePath ?>/admin/index.php?action=campaign_edit&id=<?= (int)$campaign['id'] ?>" class="btn btn-primary">Dodaj linki</a>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Alias</th>
                  <th>Tytuł</th>
                  <th>Kategoria</th>
                  <th>Status</th>
                  <th class="text-end">Kliknięcia</th>
                  <th style="width: 80px;">Akcje</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($links as $link):
                  $ls = $linkStats[(int)$link['id']] ?? ['total' => 0, 'today' => 0];
                ?>
                  <tr>
                    <td><code><?= htmlspecialchars($link['slug']) ?></code></td>
                    <td><?= htmlspecialchars(mb_substr($link['page_title'] ?? '', 0, 40)) ?></td>
                    <td>
                      <?php if (!empty($link['category_name'])): ?>
                        <span class="badge" style="--badge-color: <?= htmlspecialchars($link['category_color'] ?? '#3A3F45') ?>;"><?= htmlspecialchars($link['category_name']) ?></span>
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($link['status'] === 'published'): ?>
                        <span class="badge bg-success-subtle border border-success text-success-emphasis">Aktywny</span>
                      <?php elseif ($link['status'] === 'draft'): ?>
                        <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">Szkic</span>
                      <?php else: ?>
                        <span class="badge bg-warning-subtle border border-warning text-warning-emphasis"><?= htmlspecialchars($link['status']) ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end"><?= number_format($ls['total']) ?></td>
                    <td>
                      <a href="<?= $basePath ?>/admin/index.php?action=edit&id=<?= (int)$link['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edytuj link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                          <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                        </svg>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
