<?php
  $pageTitle = 'Powiadomienia — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-4">
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-bell me-2" viewBox="0 0 16 16">
              <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
            </svg>
            Powiadomienia
            <?php if ($unreadCount > 0): ?>
              <span class="badge bg-danger rounded-pill fs-6 align-middle">Nowe <?= $unreadCount ?></span>
            <?php endif; ?>
          </h2>
          <div class="d-flex gap-2">
            <?php if ($unreadCount > 0): ?>
              <form method="POST" action="<?= $basePath ?>/admin/index.php?action=notification_mark_read">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="mark_all" value="1">
                <button type="submit" class="btn btn-outline-success">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all me-1" viewBox="0 0 16 16">
                    <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0"/>
                    <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708"/>
                  </svg>
                  Oznacz wszystkie jako przeczytane
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($successMsg)): ?>
          <div class="alert bg-success-subtle border border-success text-success-emphasis mb-3">
            <?= htmlspecialchars($successMsg) ?>
          </div>
        <?php endif; ?>

        <!-- Filtry -->
        <div class="card mb-3">
          <div class="card-body">
            <form method="GET" action="<?= $basePath ?>/admin/index.php" id="filterForm">
              <input type="hidden" name="action" value="notifications">
              <div class="row g-3">
                <div class="col-md-3">
                  <label for="component" class="form-label">Komponent</label>
                  <select class="form-select" id="component" name="component">
                    <option value="">Wszystkie</option>
                    <?php foreach ($components as $comp): ?>
                      <option value="<?= htmlspecialchars($comp) ?>" <?= ($filters['component'] ?? '') === $comp ? 'selected' : '' ?>>
                        <?= htmlspecialchars($comp) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-3">
                  <label for="severity" class="form-label">Ważność</label>
                  <select class="form-select" id="severity" name="severity">
                    <option value="">Wszystkie</option>
                    <option value="info" <?= ($filters['severity'] ?? '') === 'info' ? 'selected' : '' ?>>Info</option>
                    <option value="warning" <?= ($filters['severity'] ?? '') === 'warning' ? 'selected' : '' ?>>Ostrzeżenie</option>
                    <option value="error" <?= ($filters['severity'] ?? '') === 'error' ? 'selected' : '' ?>>Błąd</option>
                    <option value="critical" <?= ($filters['severity'] ?? '') === 'critical' ? 'selected' : '' ?>>Krytyczny</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label for="status" class="form-label">Status</label>
                  <select class="form-select" id="status" name="status">
                    <option value="">Wszystkie</option>
                    <option value="unread" <?= ($filters['readStatus'] ?? '') === 'unread' ? 'selected' : '' ?>>Nowe (nieprzeczytane)</option>
                    <option value="read" <?= ($filters['readStatus'] ?? '') === 'read' ? 'selected' : '' ?>>Przeczytane</option>
                  </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary me-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search me-1" viewBox="0 0 16 16">
                      <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                    Filtruj
                  </button>
                  <a href="<?= $basePath ?>/admin/index.php?action=notifications" class="btn btn-secondary">Wyczyść</a>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Tabela powiadomień -->
        <div class="card">
          <div class="card-header bg-white">
            <strong>Znaleziono: <?= $total ?> powiadomień</strong>
          </div>
          <div class="card-body p-0">
            <?php if (!empty($notifications)): ?>
              <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                  <thead>
                    <tr>
                      <th>Data</th>
                      <th>Komponent</th>
                      <th>Ważność</th>
                      <th>Tytuł</th>
                      <th>Akcje</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($notifications as $notif): ?>
                      <tr class="<?= !$notif['is_read'] ? 'table-light' : '' ?>">
                        <td class="text-nowrap">
                          <small <?= !$notif['is_read'] ? 'class="fw-semibold"' : '' ?>><?= date('Y-m-d H:i:s', strtotime($notif['created_at'])) ?></small>
                        </td>
                        <td>
                          <?php
                            $componentIcon = match ($notif['component']) {
                              'cron' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-clock me-1" viewBox="0 0 16 16"><path d="M8 3.5a.5.5 0 0 0-1 0V8a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 7.71z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/></svg>',
                              'email' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-envelope me-1" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/></svg>',
                              'broken_links' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-link-45deg me-1" viewBox="0 0 16 16"><path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/><path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/></svg>',
                              'geo_cache' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-globe me-1" viewBox="0 0 16 16"><path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m7.5-6.923c-.67.204-1.335.82-1.887 1.855A8 8 0 0 0 5.145 4H7.5zM4.09 4a9.3 9.3 0 0 1 .64-1.539 7 7 0 0 1 .597-.933A7 7 0 0 0 2.255 4zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a7 7 0 0 0-.656 2.5zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5zM8.5 5v2.5h2.99a12.5 12.5 0 0 0-.337-2.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5zM5.145 12q.208.58.468 1.068c.552 1.035 1.218 1.65 1.887 1.855V12zm.182 2.472a7 7 0 0 1-.597-.933A9.3 9.3 0 0 1 4.09 12H2.255a7 7 0 0 0 3.072 2.472M3.82 11a13.7 13.7 0 0 1-.312-2.5h-2.49a7 7 0 0 0 .656 2.5zM8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855q.26-.487.468-1.068zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.7 13.7 0 0 1-.312 2.5m2.802-3.5a7 7 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7 7 0 0 0-3.072-2.472c.218.284.418.598.597.933M10.855 4a8 8 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4z"/></svg>',
                              default => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-gear me-1" viewBox="0 0 16 16"><path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/></svg>',
                            };
                          ?>
                          <?= $componentIcon ?><span class="<?= !$notif['is_read'] ? 'fw-semibold' : '' ?>"><?= htmlspecialchars($notif['component']) ?></span>
                        </td>
                        <td>
                          <?php
                            $severityColor = match ($notif['severity']) {
                              'info' => 'info',
                              'warning' => 'warning',
                              'error' => 'danger',
                              'critical' => 'danger',
                              default => 'secondary'
                            };
                            $severityLabel = match ($notif['severity']) {
                              'info' => 'Info',
                              'warning' => 'Ostrzeżenie',
                              'error' => 'Błąd',
                              'critical' => 'Krytyczny',
                              default => $notif['severity']
                            };
                          ?>
                          <span class="badge bg-<?= $severityColor ?>-subtle border border-<?= $severityColor ?> text-<?= $severityColor ?>-emphasis"><?= $severityLabel ?></span>
                        </td>
                        <td>
                          <span class="<?= !$notif['is_read'] ? 'fw-semibold' : '' ?>"><?= htmlspecialchars($notif['title']) ?></span>
                        </td>
                        <td class="text-nowrap">
                          <?php if ($notif['message'] || $notif['context']): ?>
                            <button
                              class="btn btn-sm btn-outline-primary"
                              type="button"
                              data-bs-toggle="modal"
                              data-bs-target="#notifDetailModal"
                              data-notif-id="<?= $notif['id'] ?>"
                              data-title="<?= htmlspecialchars($notif['title']) ?>"
                              data-component="<?= htmlspecialchars($notif['component']) ?>"
                              data-severity="<?= htmlspecialchars($notif['severity']) ?>"
                              data-message="<?= htmlspecialchars($notif['message'] ?? '') ?>"
                              data-context="<?= htmlspecialchars($notif['context'] ?? '') ?>"
                              data-date="<?= date('Y-m-d H:i:s', strtotime($notif['created_at'])) ?>"
                              data-read="<?= $notif['is_read'] ? '1' : '0' ?>"
                              data-read-at="<?= $notif['read_at'] ? date('Y-m-d H:i:s', strtotime($notif['read_at'])) : '' ?>">
                              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                              </svg>
                              Szczegóły
                            </button>
                          <?php endif; ?>
                          <?php if (!$notif['is_read']): ?>
                            <form method="POST" action="<?= $basePath ?>/admin/index.php?action=notification_mark_read" class="d-inline">
                              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                              <input type="hidden" name="id" value="<?= $notif['id'] ?>">
                              <button type="submit" class="btn btn-sm btn-outline-success" title="Oznacz jako przeczytane">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                  <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                </svg>
                              </button>
                            </form>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <!-- Paginacja -->
              <?php if ($lastPage > 1): ?>
                <div class="card-footer bg-white">
                  <nav>
                    <ul class="pagination mb-0">
                      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $basePath ?>/admin/index.php?action=notifications&page=<?= max(1, $page - 1) ?><?= !empty($filters['component']) ? '&component=' . urlencode($filters['component']) : '' ?><?= !empty($filters['severity']) ? '&severity=' . urlencode($filters['severity']) : '' ?><?= !empty($filters['readStatus']) ? '&status=' . urlencode($filters['readStatus']) : '' ?>">Poprzednia</a>
                      </li>
                      <?php for ($i = max(1, $page - 2); $i <= min($lastPage, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                          <a class="page-link" href="<?= $basePath ?>/admin/index.php?action=notifications&page=<?= $i ?><?= !empty($filters['component']) ? '&component=' . urlencode($filters['component']) : '' ?><?= !empty($filters['severity']) ? '&severity=' . urlencode($filters['severity']) : '' ?><?= !empty($filters['readStatus']) ? '&status=' . urlencode($filters['readStatus']) : '' ?>"><?= $i ?></a>
                        </li>
                      <?php endfor; ?>
                      <li class="page-item <?= $page >= $lastPage ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $basePath ?>/admin/index.php?action=notifications&page=<?= min($lastPage, $page + 1) ?><?= !empty($filters['component']) ? '&component=' . urlencode($filters['component']) : '' ?><?= !empty($filters['severity']) ? '&severity=' . urlencode($filters['severity']) : '' ?><?= !empty($filters['readStatus']) ? '&status=' . urlencode($filters['readStatus']) : '' ?>">Następna</a>
                      </li>
                    </ul>
                  </nav>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <div class="alert bg-info-subtle border border-info text-info-emphasis m-3">
                Brak powiadomień spełniających kryteria.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal szczegółów powiadomienia -->
  <div class="modal fade" id="notifDetailModal" tabindex="-1" aria-labelledby="notifDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="notifDetailModalLabel">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-info-circle me-2" viewBox="0 0 16 16">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
              <path d="M8.93 6.588l-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
            </svg>
            Szczegóły powiadomienia
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <h6 class="text-muted mb-2">Podstawowe informacje</h6>
            <table class="table table-sm table-borderless">
              <tr>
                <td class="text-muted" style="width: 150px;">Data:</td>
                <td><strong id="modal-date"></strong></td>
              </tr>
              <tr>
                <td class="text-muted">Komponent:</td>
                <td id="modal-component"></td>
              </tr>
              <tr>
                <td class="text-muted">Ważność:</td>
                <td><span id="modal-severity" class="badge"></span></td>
              </tr>
              <tr>
                <td class="text-muted">Tytuł:</td>
                <td><strong id="modal-title"></strong></td>
              </tr>
              <tr>
                <td class="text-muted">Status:</td>
                <td id="modal-status"></td>
              </tr>
            </table>
          </div>

          <div id="modal-message-section">
            <h6 class="text-muted mb-2">Wiadomość</h6>
            <pre class="p-3 bg-light border rounded" style="white-space: pre-wrap; word-break: break-word; max-height: 300px; overflow-y: auto;" id="modal-message"></pre>
          </div>

          <div id="modal-context-section" class="mt-3">
            <h6 class="text-muted mb-2">Dane kontekstowe</h6>
            <pre class="p-3 bg-light border rounded" style="white-space: pre-wrap; word-break: break-word; max-height: 200px; overflow-y: auto;" id="modal-context"></pre>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('notifDetailModal');

      modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;

        const title = button.getAttribute('data-title');
        const component = button.getAttribute('data-component');
        const severity = button.getAttribute('data-severity');
        const message = button.getAttribute('data-message');
        const context = button.getAttribute('data-context');
        const date = button.getAttribute('data-date');
        const isRead = button.getAttribute('data-read') === '1';
        const readAt = button.getAttribute('data-read-at');

        const severityColors = {
          'info': 'info',
          'warning': 'warning',
          'error': 'danger',
          'critical': 'danger'
        };
        const severityLabels = {
          'info': 'Info',
          'warning': 'Ostrzeżenie',
          'error': 'Błąd',
          'critical': 'Krytyczny'
        };

        document.getElementById('modal-date').textContent = date;
        document.getElementById('modal-component').textContent = component;
        document.getElementById('modal-title').textContent = title;

        const modalSeverity = document.getElementById('modal-severity');
        modalSeverity.textContent = severityLabels[severity] || severity;
        modalSeverity.className = 'badge bg-' + (severityColors[severity] || 'secondary') + '-subtle border border-' + (severityColors[severity] || 'secondary') + ' text-' + (severityColors[severity] || 'secondary') + '-emphasis';

        const statusEl = document.getElementById('modal-status');
        if (isRead) {
          statusEl.innerHTML = '<span class="badge bg-success-subtle border border-success text-success-emphasis">Przeczytane</span>' + (readAt ? ' <small class="text-muted">' + readAt + '</small>' : '');
        } else {
          statusEl.innerHTML = '<span class="badge bg-warning-subtle border border-warning text-warning-emphasis">Nowe</span>';
        }

        // Wiadomość
        const messageSection = document.getElementById('modal-message-section');
        if (message) {
          document.getElementById('modal-message').textContent = message;
          messageSection.style.display = 'block';
        } else {
          messageSection.style.display = 'none';
        }

        // Kontekst
        const contextSection = document.getElementById('modal-context-section');
        if (context) {
          try {
            const parsed = JSON.parse(context);
            document.getElementById('modal-context').textContent = JSON.stringify(parsed, null, 2);
            contextSection.style.display = 'block';
          } catch (e) {
            contextSection.style.display = 'none';
          }
        } else {
          contextSection.style.display = 'none';
        }
      });
    });
  </script>

  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
