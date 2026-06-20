<?php
  $pageTitle = 'Kampanie — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>


  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
      <div>
        <h2 class="mb-0">Kampanie</h2>
        <p class="text-muted mb-0">Grupuj linki w kampanie i śledź zagregowane statystyki.</p>
      </div>
      <a href="<?= $basePath ?>/admin/index.php?action=campaign_new" class="btn btn-primary">+ Nowa kampania</a>
    </div>

    <?php if (!empty($_SESSION['toast_message'])): ?>
      <div class="alert <?= ($_SESSION['toast_type'] ?? 'info') === 'success' ? 'bg-success-subtle border border-success text-success-emphasis' : 'bg-danger-subtle border border-danger text-danger-emphasis' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['toast_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
      </div>
      <?php unset($_SESSION['toast_message'], $_SESSION['toast_type']); ?>
    <?php endif; ?>

    <?php if (empty($campaigns)): ?>
      <div class="card shadow-sm">
        <div class="card-body text-center text-muted py-5">
          <p class="mb-3">Brak kampanii. Utwórz pierwszą!</p>
          <a href="<?= $basePath ?>/admin/index.php?action=campaign_new" class="btn btn-primary">+ Nowa kampania</a>
        </div>
      </div>
    <?php else: ?>
      <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php foreach ($campaigns as $campaign): ?>
          <div class="col">
            <div class="card shadow-sm h-100">
              <div class="card-header d-flex align-items-center gap-2" style="border-left: 6px solid <?= htmlspecialchars($campaign['color'] ?? '#2196F3') ?>;">
                <h5 class="mb-0 flex-grow-1">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tags" viewBox="0 0 16 16">
                    <path d="M3 2v4.586l7 7L14.586 9l-7-7zM2 2a1 1 0 0 1 1-1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 2 6.586z"/>
                    <path d="M5.5 5a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1m0 1a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M1 7.086a1 1 0 0 0 .293.707L8.75 15.25l-.043.043a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 0 7.586V3a1 1 0 0 1 1-1z"/>
                  </svg>
                  <a href="<?= $basePath ?>/admin/index.php?action=campaign_detail&id=<?= (int)$campaign['id'] ?>" class="text-decoration-none">
                    <?= htmlspecialchars($campaign['name']) ?>
                  </a>
                </h5>
                <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis"><?= (int)$campaign['link_count'] ?> linków</span>
              </div>
              <div class="card-body">
                <?php if (!empty($campaign['description'])): ?>
                  <p class="text-muted small mb-3"><?= htmlspecialchars(mb_substr($campaign['description'], 0, 100)) ?><?= mb_strlen($campaign['description']) > 100 ? '...' : '' ?></p>
                <?php endif; ?>

                <div class="d-flex gap-2 mb-3">
                  <div class="text-center bg-light rounded border px-3 py-2">
                    <div class="fw-bold fs-5"><?= number_format($campaign['stats']['total_clicks']) ?></div>
                    <small class="text-muted">Kliknięć</small>
                  </div>
                  <div class="text-center bg-light rounded border px-3 py-2">
                    <div class="fw-bold fs-5"><?= number_format($campaign['stats']['today_clicks']) ?></div>
                    <small class="text-muted">Dziś</small>
                  </div>
                  <div class="text-center bg-light rounded border px-3 py-2">
                    <div class="fw-bold fs-5"><?= (int)$campaign['link_count'] ?></div>
                    <small class="text-muted">Linków</small>
                  </div>
                </div>

                <div class="d-flex gap-1">
                  <a href="<?= $basePath ?>/admin/index.php?action=campaign_detail&id=<?= (int)$campaign['id'] ?>" class="btn btn-sm btn-outline-success flex-fill">Szczegóły</a>
                  <a href="<?= $basePath ?>/admin/index.php?action=campaign_edit&id=<?= (int)$campaign['id'] ?>" class="btn btn-sm btn-outline-warning">Edytuj</a>
                  <form method="post" action="<?= $basePath ?>/admin/index.php?action=campaign_delete&id=<?= (int)$campaign['id'] ?>" class="d-inline">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Usunąć kampanię?')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                      </svg>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
