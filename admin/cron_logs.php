<?php
  $pageTitle = 'Logi Cron — ' . (string)$job['name'] . ' — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container-xxl py-5">
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>Logi zadania: <code><?= htmlspecialchars($job['name']) ?></code></h2>
        </div>

        <!-- Info o zadaniu -->
        <div class="card mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <p class="mb-2"><strong>Opis:</strong> <?= htmlspecialchars($job['description'] ?? 'Brak opisu') ?></p>
                <p class="mb-2"><strong>Interwał:</strong> 
                  <?php
                    $interval = (int)$job['interval_seconds'];
                    if ($interval >= 86400) {
                      echo round($interval / 86400) . ' dni';
                    } elseif ($interval >= 3600) {
                      echo round($interval / 3600) . ' godzin';
                    } elseif ($interval >= 60) {
                      echo round($interval / 60) . ' minut';
                    } else {
                      echo $interval . ' sekund';
                    }
                  ?>
                </p>
              </div>
              <div class="col-md-6">
                <p class="mb-2"><strong>Callback:</strong> <code><?= htmlspecialchars($job['callback_class']) ?>::<?= htmlspecialchars($job['callback_method']) ?>()</code></p>
                <p class="mb-2"><strong>Status:</strong> 
                  <span class="badge bg-<?= (int)$job['is_active'] === 1 ? 'success' : 'secondary' ?>">
                    <?= (int)$job['is_active'] === 1 ? 'Aktywne' : 'Nieaktywne' ?>
                  </span>
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Logi -->
        <div class="card">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0">Historia wykonań (ostatnie 100)</h5>
          </div>
          <div class="card-body">
            <?php if (empty($logs)): ?>
              <p class="text-muted mb-0">Brak logów dla tego zadania</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th class="col-w-50">#</th>
                      <th class="col-w-150">Rozpoczęcie</th>
                      <th class="col-w-150">Zakończenie</th>
                      <th class="col-w-100">Status</th>
                      <th class="col-w-100">Czas wykonania</th>
                      <th>Błąd</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($logs as $log): 
                      $status = $log['status'];
                      $executionTime = $log['execution_time_ms'];
                    ?>
                      <tr>
                        <td><?= (int)$log['id'] ?></td>
                        <td><small><?= date('Y-m-d H:i:s', strtotime($log['started_at'])) ?></small></td>
                        <td>
                          <?php if (!empty($log['finished_at'])): ?>
                            <small><?= date('Y-m-d H:i:s', strtotime($log['finished_at'])) ?></small>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <span class="badge bg-<?= $status === 'success' ? 'success' : ($status === 'error' ? 'danger' : 'warning') ?>">
                            <?= ucfirst($status) ?>
                          </span>
                        </td>
                        <td>
                          <?php if ($executionTime !== null): ?>
                            <small><?= number_format($executionTime) ?>ms</small>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if (!empty($log['error_message'])): ?>
                            <div class="alert bg-danger-subtle border border-danger text-danger-emphasis mb-0 py-1 px-2" class="alert-compact">
                              <?= htmlspecialchars($log['error_message']) ?>
                            </div>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
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
