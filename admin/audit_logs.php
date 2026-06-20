<?php
  $pageTitle = 'Logi Audytu — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-4">
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-shield-check me-2" viewBox="0 0 16 16">
              <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
              <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
            </svg>
            Logi Audytu
          </h2>
        </div>

        <!-- Filtry -->
        <div class="card mb-3">
          <div class="card-body">
            <form method="GET" action="<?= $basePath ?>/admin/index.php" id="filterForm">
              <input type="hidden" name="action" value="audit_logs">
              <div class="row g-3">
                <div class="col-md-3">
                  <label for="action_type" class="form-label">Typ akcji</label>
                  <select class="form-select" id="action_type" name="action_type">
                    <option value="">Wszystkie</option>
                    <?php foreach ($actionTypes as $type): ?>
                      <option value="<?= htmlspecialchars($type) ?>" <?= ($filters['actionType'] ?? '') === $type ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-3">
                  <label for="entity_type" class="form-label">Typ encji</label>
                  <select class="form-select" id="entity_type" name="entity_type">
                    <option value="">Wszystkie</option>
                    <?php foreach ($entityTypes as $type): ?>
                      <option value="<?= htmlspecialchars($type) ?>" <?= ($filters['entityType'] ?? '') === $type ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-2">
                  <label for="date_from" class="form-label">Od daty</label>
                  <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($filters['dateFrom'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                  <label for="date_to" class="form-label">Do daty</label>
                  <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($filters['dateTo'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                  <label for="search" class="form-label">Szukaj</label>
                  <input type="text" class="form-control" id="search" name="search" placeholder="IP, nazwa..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
              </div>
              <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search me-1" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                  </svg>
                  Filtruj
                </button>
                <a href="<?= $basePath ?>/admin/index.php?action=audit_logs" class="btn btn-secondary">Wyczyść filtry</a>
              </div>
            </form>
          </div>
        </div>

        <!-- Tabela logów -->
        <div class="card">
          <div class="card-header bg-white">
            <strong>Znaleziono: <?= $total ?> wpisów</strong>
          </div>
          <div class="card-body p-0">
            <?php if (!empty($logs)): ?>
              <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                  <thead>
                    <tr>
                      <th>Data</th>
                      <th>Akcja</th>
                      <th>Encja</th>
                      <th>Nazwa/ID</th>
                      <th>IP</th>
                      <th>Szczegóły</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($logs as $log): ?>
                      <tr>
                        <td class="text-nowrap">
                          <small><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></small>
                        </td>
                        <td>
                          <?php
                            $actionColor = match ($log['action_type']) {
                              'login' => 'success',
                              'logout' => 'info',
                              'login_failed' => 'danger',
                              'create' => 'success',
                              'update' => 'primary',
                              'delete', 'permanent_delete' => 'danger',
                              'restore' => 'warning',
                              'settings_change' => 'warning',
                              'bulk_action' => 'info',
                              default => 'secondary'
                            };
                          ?>
                          <span class="badge bg-<?= $actionColor ?>-subtle border border-<?= $actionColor ?> text-<?= $actionColor ?>-emphasis"><?= htmlspecialchars($log['action_type']) ?></span>
                        </td>
                        <td><?= $log['entity_type'] ? htmlspecialchars($log['entity_type']) : '<span class="text-muted">—</span>' ?></td>
                        <td>
                          <?php if ($log['entity_name']): ?>
                            <strong><?= htmlspecialchars($log['entity_name']) ?></strong>
                            <?php if ($log['entity_id']): ?>
                              <small class="text-muted">(#<?= $log['entity_id'] ?>)</small>
                            <?php endif; ?>
                          <?php elseif ($log['entity_id']): ?>
                            <small class="text-muted">#<?= $log['entity_id'] ?></small>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td><code><?= htmlspecialchars($log['ip_address'] ?? '—') ?></code></td>
                        <td>
                          <?php if ($log['old_values'] || $log['new_values']): ?>
                            <button
                              class="btn btn-sm btn-outline-primary"
                              type="button"
                              data-bs-toggle="modal"
                              data-bs-target="#auditDetailModal"
                              data-log-id="<?= $log['id'] ?>"
                              data-action="<?= htmlspecialchars($log['action_type']) ?>"
                              data-entity="<?= htmlspecialchars($log['entity_type'] ?? '') ?>"
                              data-entity-name="<?= htmlspecialchars($log['entity_name'] ?? '') ?>"
                              data-entity-id="<?= htmlspecialchars($log['entity_id'] ?? '') ?>"
                              data-date="<?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?>"
                              data-ip="<?= htmlspecialchars($log['ip_address'] ?? '') ?>"
                              data-user-agent="<?= htmlspecialchars($log['user_agent'] ?? '') ?>"
                              data-old-values="<?= htmlspecialchars($log['old_values'] ?? '') ?>"
                              data-new-values="<?= htmlspecialchars($log['new_values'] ?? '') ?>">
                              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                              </svg>
                              Pokaż
                            </button>
                          <?php else: ?>
                            <span class="text-muted">—</span>
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
                        <a class="page-link" href="<?= $basePath ?>/admin/index.php?action=audit_logs&page=<?= max(1, $page - 1) ?><?= !empty($filters['actionType']) ? '&action_type=' . urlencode($filters['actionType']) : '' ?><?= !empty($filters['entityType']) ? '&entity_type=' . urlencode($filters['entityType']) : '' ?><?= !empty($filters['dateFrom']) ? '&date_from=' . urlencode($filters['dateFrom']) : '' ?><?= !empty($filters['dateTo']) ? '&date_to=' . urlencode($filters['dateTo']) : '' ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?>">Poprzednia</a>
                      </li>
                      <?php for ($i = max(1, $page - 2); $i <= min($lastPage, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                          <a class="page-link" href="<?= $basePath ?>/admin/index.php?action=audit_logs&page=<?= $i ?><?= !empty($filters['actionType']) ? '&action_type=' . urlencode($filters['actionType']) : '' ?><?= !empty($filters['entityType']) ? '&entity_type=' . urlencode($filters['entityType']) : '' ?><?= !empty($filters['dateFrom']) ? '&date_from=' . urlencode($filters['dateFrom']) : '' ?><?= !empty($filters['dateTo']) ? '&date_to=' . urlencode($filters['dateTo']) : '' ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?>"><?= $i ?></a>
                        </li>
                      <?php endfor; ?>
                      <li class="page-item <?= $page >= $lastPage ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $basePath ?>/admin/index.php?action=audit_logs&page=<?= min($lastPage, $page + 1) ?><?= !empty($filters['actionType']) ? '&action_type=' . urlencode($filters['actionType']) : '' ?><?= !empty($filters['entityType']) ? '&entity_type=' . urlencode($filters['entityType']) : '' ?><?= !empty($filters['dateFrom']) ? '&date_from=' . urlencode($filters['dateFrom']) : '' ?><?= !empty($filters['dateTo']) ? '&date_to=' . urlencode($filters['dateTo']) : '' ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?>">Następna</a>
                      </li>
                    </ul>
                  </nav>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <div class="alert bg-info-subtle border border-info text-info-emphasis m-3 mb-0">
                Brak logów audytu spełniających kryteria.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal szczegółów audytu -->
  <div class="modal fade" id="auditDetailModal" tabindex="-1" aria-labelledby="auditDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="auditDetailModalLabel">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-info-circle me-2" viewBox="0 0 16 16">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
              <path d="M8.93 6.588l-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
            </svg>
            Szczegóły logu audytu
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <h6 class="text-muted mb-2">Podstawowe informacje</h6>
            <table class="table table-sm table-borderless">
              <tr>
                <td class="text-muted col-w-150">Data:</td>
                <td><strong id="modal-date"></strong></td>
              </tr>
              <tr>
                <td class="text-muted">Akcja:</td>
                <td><span id="modal-action" class="badge"></span></td>
              </tr>
              <tr>
                <td class="text-muted">Encja:</td>
                <td id="modal-entity"></td>
              </tr>
              <tr>
                <td class="text-muted">Nazwa/ID:</td>
                <td id="modal-entity-name"></td>
              </tr>
              <tr>
                <td class="text-muted">Adres IP:</td>
                <td><code id="modal-ip"></code></td>
              </tr>
            </table>
          </div>

          <div id="modal-values-section">
            <h6 class="text-muted mb-2">Zmiany</h6>
            <div class="row">
              <div class="col-md-6" id="modal-old-values-container">
                <strong>Stare wartości:</strong>
                <pre class="mt-2 p-3 bg-light border rounded audit-log-pre" id="modal-old-values"></pre>
              </div>
              <div class="col-md-6" id="modal-new-values-container">
                <strong>Nowe wartości:</strong>
                <pre class="mt-2 p-3 bg-light border rounded audit-log-pre" id="modal-new-values"></pre>
              </div>
            </div>
          </div>

          <div class="mt-3" id="modal-user-agent-section">
            <h6 class="text-muted mb-2">Dodatkowe informacje</h6>
            <div class="card bg-light">
              <div class="card-body">
                <strong>User Agent:</strong>
                <code class="d-block mt-2 user-agent-code" id="modal-user-agent"></code>
              </div>
            </div>
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
      const modal = document.getElementById('auditDetailModal');

      modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;

        // Pobierz dane z atrybutów data-*
        const action = button.getAttribute('data-action');
        const entity = button.getAttribute('data-entity');
        const entityName = button.getAttribute('data-entity-name');
        const entityId = button.getAttribute('data-entity-id');
        const date = button.getAttribute('data-date');
        const ip = button.getAttribute('data-ip');
        const userAgent = button.getAttribute('data-user-agent');
        const oldValues = button.getAttribute('data-old-values');
        const newValues = button.getAttribute('data-new-values');

        // Mapowanie kolorów dla akcji
        const actionColors = {
          'login': 'success',
          'logout': 'info',
          'login_failed': 'danger',
          'create': 'success',
          'update': 'primary',
          'delete': 'danger',
          'permanent_delete': 'danger',
          'restore': 'warning',
          'settings_change': 'warning',
          'bulk_action': 'info'
        };

        // Wypełnij modal danymi
        document.getElementById('modal-date').textContent = date;

        const modalAction = document.getElementById('modal-action');
        modalAction.textContent = action;
        modalAction.className = 'badge bg-' + (actionColors[action] || 'secondary');

        document.getElementById('modal-entity').textContent = entity || '—';

        let entityNameText = '';
        if (entityName) {
          entityNameText = entityName;
          if (entityId) {
            entityNameText += ' (#' + entityId + ')';
          }
        } else if (entityId) {
          entityNameText = '#' + entityId;
        } else {
          entityNameText = '—';
        }
        document.getElementById('modal-entity-name').textContent = entityNameText;

        document.getElementById('modal-ip').textContent = ip || '—';

        // Obsługa wartości
        const oldValuesContainer = document.getElementById('modal-old-values-container');
        const newValuesContainer = document.getElementById('modal-new-values-container');
        const valuesSection = document.getElementById('modal-values-section');

        if (oldValues || newValues) {
          valuesSection.style.display = 'block';

          if (oldValues) {
            try {
              const parsed = JSON.parse(oldValues);
              document.getElementById('modal-old-values').textContent = JSON.stringify(parsed, null, 2);
              oldValuesContainer.style.display = 'block';
            } catch (e) {
              oldValuesContainer.style.display = 'none';
            }
          } else {
            oldValuesContainer.style.display = 'none';
          }

          if (newValues) {
            try {
              const parsed = JSON.parse(newValues);
              document.getElementById('modal-new-values').textContent = JSON.stringify(parsed, null, 2);
              newValuesContainer.style.display = 'block';
            } catch (e) {
              newValuesContainer.style.display = 'none';
            }
          } else {
            newValuesContainer.style.display = 'none';
          }
        } else {
          valuesSection.style.display = 'none';
        }

        // User Agent
        const userAgentSection = document.getElementById('modal-user-agent-section');
        if (userAgent) {
          document.getElementById('modal-user-agent').textContent = userAgent;
          userAgentSection.style.display = 'block';
        } else {
          userAgentSection.style.display = 'none';
        }
      });
    });
  </script>

  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
