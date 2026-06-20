<?php
  $pageTitle = 'Strony — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>


  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10">

        <?php if (!empty($success)): ?>
          <?php $toastBootstrapType = match($toast_type ?? 'success') { 'error' => 'danger', 'warning' => 'warning', 'info' => 'info', default => 'success' }; ?>
          <div class="alert alert-<?= $toastBootstrapType ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <div class="card shadow-sm">
          <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h2 class="mb-0 fs-5">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-text me-2" viewBox="0 0 16 16">
                <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 0 1-1z"/>
              </svg>
              Własne strony
            </h2>
            <a href="<?= $basePath ?>/admin/index.php?action=page_new" class="btn btn-sm btn-light">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg me-1" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
              </svg>
              Nowa strona
            </a>
          </div>
          <div class="card-body p-0">
            <?php if (empty($pages)): ?>
              <div class="text-center text-muted py-5">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-file-earmark-plus mb-3 opacity-50" viewBox="0 0 16 16">
                  <path d="M8 6.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V11a.5.5 0 0 1-1 0V9.5H6a.5.5 0 0 1 0-1h1.5V7a.5.5 0 0 1 .5-.5"/>
                  <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>
                </svg>
                <p>Brak stron. <a href="<?= $basePath ?>/admin/index.php?action=page_new">Utwórz pierwszą stronę</a></p>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Tytuł</th>
                      <th>Slug / URL</th>
                      <th class="text-center">Status</th>
                      <th class="text-center">Nawigacja</th>
                      <th class="text-center">Layout</th>
                      <th>Zaktualizowano</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($pages as $p): ?>
                      <tr>
                        <td>
                          <a href="<?= $basePath ?>/admin/index.php?action=page_edit&id=<?= (int)$p['id'] ?>" class="fw-medium text-decoration-none">
                            <?= htmlspecialchars($p['title']) ?>
                          </a>
                        </td>
                        <td>
                          <a href="<?= $basePath ?>/?page=<?= htmlspecialchars($p['slug']) ?>" target="_blank" class="text-muted small font-monospace text-decoration-none">
                            /?page=<?= htmlspecialchars($p['slug']) ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" class="bi bi-box-arrow-up-right ms-1" viewBox="0 0 16 16">
                              <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
                              <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
                            </svg>
                          </a>
                        </td>
                        <td class="text-center">
                          <?php if ($p['status'] === 'published'): ?>
                            <span class="badge bg-success">Opublikowana</span>
                          <?php else: ?>
                            <span class="badge bg-secondary">Szkic</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center">
                          <?php if ($p['show_in_nav']): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                              <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center">
                          <small class="text-muted"><?= $p['use_theme'] ? 'Motyw' : 'Własny' ?></small>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars(substr((string)$p['updated_at'], 0, 16)) ?></td>
                        <td class="text-end">
                          <a href="<?= $basePath ?>/admin/index.php?action=page_edit&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Edytuj</a>
                          <form method="post" action="<?= $basePath ?>/admin/index.php?action=page_delete&id=<?= (int)$p['id'] ?>" class="d-inline"
                                onsubmit="return confirm('Usunąć stronę \'<?= htmlspecialchars(addslashes($p['title'])) ?>\'?')">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Usuń</button>
                          </form>
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
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
