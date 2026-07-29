<?php
  $pageTitle = 'Sprawdzanie Linków — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-4">
    <div class="row">
      <div class="col-12">
        <h2 class="mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-link-45deg me-2" viewBox="0 0 16 16">
            <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/>
            <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/>
          </svg>
          Sprawdzanie Linków
        </h2>

        <!-- Podsumowanie -->
        <div class="row mb-4">
          <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-primary">
              <div class="card-body text-center">
                <h5 class="card-title">Wszystkie</h5>
                <h3 class="mb-0 text-primary"><?= $summary['total_links'] ?? 0 ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-success">
              <div class="card-body text-center">
                <h5 class="card-title">Zdrowe</h5>
                <h3 class="mb-0 text-success"><?= $summary['healthy'] ?? 0 ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-danger">
              <div class="card-body text-center">
                <h5 class="card-title">Uszkodzone</h5>
                <h3 class="mb-0 text-danger"><?= $summary['broken'] ?? 0 ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-warning">
              <div class="card-body text-center">
                <h5 class="card-title">
                  Ignorowane
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-info-circle text-muted ms-1" viewBox="0 0 16 16" data-bs-toggle="tooltip" data-bs-placement="top" title="Oznaczone jako ignorowane, w koszu oraz wygasłe">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                  </svg>
                </h5>
                <h3 class="mb-0 text-warning"><?= $summary['ignored'] ?? 0 ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-info">
              <div class="card-body text-center">
                <h5 class="card-title">Naprawione</h5>
                <h3 class="mb-0 text-info"><?= $summary['resolved'] ?? 0 ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-secondary">
              <div class="card-body text-center">
                <h5 class="card-title">Niesprawdzone</h5>
                <h3 class="mb-0 text-secondary"><?= $summary['unchecked'] ?? 0 ?></h3>
              </div>
            </div>
          </div>
        </div>

        <!-- Uszkodzone linki -->
        <div class="card">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Uszkodzone linki (<?= count($brokenLinks) ?>)</strong>
            <form method="POST" action="<?= $basePath ?>/admin/index.php?action=broken_links_run_check" class="d-inline">
              <input type="hidden" name="csrf" value="<?= $csrf ?>">
              <button type="submit" class="btn btn-sm btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat me-1" viewBox="0 0 16 16">
                  <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/>
                  <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/>
                </svg>
                Uruchom sprawdzanie
              </button>
            </form>
          </div>
          <div class="card-body p-0">
            <?php if (!empty($brokenLinks)): ?>
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead>
                    <tr>
                      <th class="col-w-350">Alias / Tytuł</th>
                      <th>URL docelowy</th>
                      <th class="col-w-100">Status HTTP</th>
                      <th>Błąd</th>
                      <th class="col-w-160">Ostatnie sprawdzenie</th>
                      <th class="col-w-240">Akcje</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($brokenLinks as $link): ?>
                      <tr>
                        <td>
                          <div class="d-flex align-items-center">
                            <div>
                              <strong><?= htmlspecialchars($link['slug']) ?></strong>
                              <?php if ($link['page_title']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($link['page_title']) ?></small>
                              <?php endif; ?>
                            </div>
                          </div>
                        </td>
                        <td>
                          <div class="link-truncate">
                            <?php if (Utils::isSafeHttpUrl($link['target_url'])): ?>
                            <a href="<?= htmlspecialchars($link['target_url']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                              <?= htmlspecialchars($link['target_url']) ?>
                              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-box-arrow-up-right ms-1" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
                                <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
                              </svg>
                            </a>
                            <?php else: ?>
                            <span class="text-muted" title="Zablokowany adres (nieobsługiwany schemat URL)"><?= htmlspecialchars((string)$link['target_url']) ?></span>
                            <?php endif; ?>
                          </div>
                        </td>
                        <td>
                          <?php if ($link['http_status']): ?>
                            <span class="badge bg-<?= $link['http_status'] >= 200 && $link['http_status'] < 300 ? 'success' : ($link['http_status'] >= 300 && $link['http_status'] < 400 ? 'warning' : 'danger') ?>-subtle border border-<?= $link['http_status'] >= 200 && $link['http_status'] < 300 ? 'success' : ($link['http_status'] >= 300 && $link['http_status'] < 400 ? 'warning' : 'danger') ?> text-<?= $link['http_status'] >= 200 && $link['http_status'] < 300 ? 'success' : ($link['http_status'] >= 300 && $link['http_status'] < 400 ? 'warning' : 'danger') ?>-emphasis">
                              <?= $link['http_status'] ?>
                            </span>
                          <?php else: ?>
                            <span class="badge bg-warning-subtle border border-warning text-warning-emphasis">Błąd</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if ($link['error_message']): ?>
                            <small class="text-danger"><?= htmlspecialchars($link['error_message']) ?></small>
                          <?php elseif ($link['final_url'] && $link['final_url'] !== $link['target_url']): ?>
                            <small class="text-info">Przekierowano (<?= $link['redirect_count'] ?? 0 ?>×)</small>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if ($link['last_health_check_at']): ?>
                            <small><?= date('Y-m-d H:i', strtotime($link['last_health_check_at'])) ?></small>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <div class="btn-group btn-group-sm" role="group">
                            <form method="POST" action="<?= $basePath ?>/admin/index.php?action=broken_link_action" class="form-inline">
                              <input type="hidden" name="csrf" value="<?= $csrf ?>">
                              <input type="hidden" name="link_id" value="<?= $link['id'] ?>">
                              <input type="hidden" name="action" value="recheck">
                              <button type="submit" class="btn btn-outline-primary" title="Sprawdź ponownie">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                                  <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/>
                                  <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/>
                                </svg>
                              </button>
                            </form>
                            <form method="POST" action="<?= $basePath ?>/admin/index.php?action=broken_link_action" class="form-inline">
                              <input type="hidden" name="csrf" value="<?= $csrf ?>">
                              <input type="hidden" name="link_id" value="<?= $link['id'] ?>">
                              <input type="hidden" name="action" value="ignore">
                              <button type="submit" class="btn btn-outline-warning" title="Ignoruj">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-eye-slash" viewBox="0 0 16 16">
                                  <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/>
                                  <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/>
                                  <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/>
                                </svg>
                              </button>
                            </form>
                            <form method="POST" action="<?= $basePath ?>/admin/index.php?action=broken_link_action" class="form-inline">
                              <input type="hidden" name="csrf" value="<?= $csrf ?>">
                              <input type="hidden" name="link_id" value="<?= $link['id'] ?>">
                              <input type="hidden" name="action" value="resolve">
                              <button type="submit" class="btn btn-outline-success" title="Oznacz jako naprawiony">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                                  <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/>
                                </svg>
                              </button>
                            </form>
                            <a href="<?= $basePath ?>/admin/index.php?action=edit&id=<?= $link['id'] ?>" class="btn btn-outline-secondary" title="Edytuj link">
                              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                              </svg>
                            </a>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="alert bg-success-subtle border border-success text-success-emphasis m-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-circle me-2" viewBox="0 0 16 16">
                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                  <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                </svg>
                Brak uszkodzonych linków. Wszystkie linki działają prawidłowo!
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Wszystkie linki -->
        <div class="card mt-4">
          <div class="card-header bg-white">
            <strong>Wszystkie linki (<?= count($allLinks) ?>)</strong>
          </div>
          <div class="card-body p-0">
            <?php if (!empty($allLinks)): ?>
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead>
                    <tr>
                      <th class="col-w-350">Alias / Tytuł</th>
                      <th>URL docelowy</th>
                      <th class="col-w-100">Status</th>
                      <th class="col-w-100">HTTP</th>
                      <th class="col-w-160">Ostatnie sprawdzenie</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($allLinks as $link): ?>
                      <tr>
                        <td>
                          <strong><?= htmlspecialchars($link['slug']) ?></strong>
                          <?php if ($link['page_title']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($link['page_title']) ?></small>
                          <?php endif; ?>
                        </td>
                        <td>
                          <div class="link-truncate">
                            <?php if (Utils::isSafeHttpUrl($link['target_url'])): ?>
                            <a href="<?= htmlspecialchars($link['target_url']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                              <?= htmlspecialchars($link['target_url']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted" title="Zablokowany adres (nieobsługiwany schemat URL)"><?= htmlspecialchars((string)$link['target_url']) ?></span>
                            <?php endif; ?>
                          </div>
                        </td>
                        <td>
                          <?php
                            $statusMap = [
                              'healthy'  => ['bg-success-subtle border border-success text-success-emphasis', 'Zdrowy'],
                              'broken'   => ['bg-danger-subtle border border-danger text-danger-emphasis', 'Uszkodzony'],
                              'ignored'  => ['bg-warning-subtle border border-warning text-warning-emphasis', 'Ignorowany'],
                              'resolved' => ['bg-info-subtle border border-info text-info-emphasis', 'Naprawiony'],
                            ];
                            $s = $link['last_health_status'] ?? null;
                            if ($s && isset($statusMap[$s])):
                          ?>
                            <span class="badge <?= $statusMap[$s][0] ?>"><?= $statusMap[$s][1] ?></span>
                          <?php else: ?>
                            <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">Niesprawdzony</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if ($link['http_status']): ?>
                            <span class="badge bg-<?= $link['http_status'] >= 200 && $link['http_status'] < 400 ? 'success' : 'danger' ?>-subtle border border-<?= $link['http_status'] >= 200 && $link['http_status'] < 400 ? 'success' : 'danger' ?> text-<?= $link['http_status'] >= 200 && $link['http_status'] < 400 ? 'success' : 'danger' ?>-emphasis">
                              <?= $link['http_status'] ?>
                            </span>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if ($link['last_health_check_at']): ?>
                            <small><?= date('Y-m-d H:i', strtotime($link['last_health_check_at'])) ?></small>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-muted text-center p-3">Brak opublikowanych linków.</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="mt-3">
          <small class="text-muted">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-info-circle me-1" viewBox="0 0 16 16">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
              <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
            </svg>
            Sprawdzanie linków odbywa się automatycznie co 24 godziny. Kliknij „Uruchom sprawdzanie" aby sprawdzić wszystkie linki teraz.
          </small>
        </div>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
