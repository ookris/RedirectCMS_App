<?php
  $pageTitle = 'Cache Geolokalizacji — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-5">
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>Zarządzanie cache geolokalizacji</h2>
        </div>

        <?php if (!empty($success)): ?>
          <div class="alert bg-success-subtle border border-success text-success-emphasis alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <!-- Statystyki cache -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="card h-100">
              <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-muted">Łącznie wpisów</h6>
                <h2 class="card-title mb-0"><?= number_format($stats['total_entries']) ?></h2>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card h-100">
              <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-muted">Wygasłe wpisy</h6>
                <h2 class="card-title mb-0 text-warning"><?= number_format($stats['expired_entries']) ?></h2>
                <small class="text-muted">>30 dni</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card h-100">
              <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-muted">Zakres dat</h6>
                <div class="mb-1">
                  <small class="text-muted">Najstarszy:</small>
                  <?php if ($stats['oldest_entry']): ?>
                    <div><small><?= date('Y-m-d H:i', strtotime($stats['oldest_entry'])) ?></small></div>
                  <?php else: ?>
                    <div><small class="text-muted">Brak</small></div>
                  <?php endif; ?>
                </div>
                <div>
                  <small class="text-muted">Najnowszy:</small>
                  <?php if ($stats['newest_entry']): ?>
                    <div><small><?= date('Y-m-d H:i', strtotime($stats['newest_entry'])) ?></small></div>
                  <?php else: ?>
                    <div><small class="text-muted">Brak</small></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card h-100">
              <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-muted">Rozmiar tabeli</h6>
                <h2 class="card-title mb-0">
                  <?php 
                  $sizeKb = $stats['table_size_kb'] ?? 0;
                  if ($sizeKb >= 1024) {
                    echo number_format($sizeKb / 1024, 2) . ' MB';
                  } else {
                    echo number_format($sizeKb, 2) . ' KB';
                  }
                  ?>
                </h2>
              </div>
            </div>
          </div>
        </div>

        <!-- Akcje czyszczenia -->
        <div class="row">
          <div class="col-md-6 mb-4">
            <div class="card">
              <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Wyczyść stare wpisy</h5>
              </div>
              <div class="card-body">
                <p class="text-muted">Usuń wpisy cache starsze niż określona liczba dni. Domyślnie 60 dni (TTL wynosi 30 dni).</p>
                <form method="post" action="<?= $basePath ?>/admin/index.php?action=geo_cache_cleanup" onsubmit="return confirm('Czy na pewno chcesz usunąć stare wpisy cache?');">
                  <input type="hidden" name="csrf" value="<?= $csrf ?>" />
                  <input type="hidden" name="action" value="clean_old" />
                  <div class="mb-3">
                    <label for="days" class="form-label">Starsze niż (dni)</label>
                    <input type="number" class="form-control" id="days" name="days" value="60" min="30" max="365" required />
                    <div class="form-text">Minimalnie 30 dni</div>
                  </div>
                  <button type="submit" class="btn btn-warning">Wyczyść stare wpisy</button>
                </form>
              </div>
            </div>
          </div>

          <div class="col-md-6 mb-4">
            <div class="card border-danger">
              <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Wyczyść cały cache</h5>
              </div>
              <div class="card-body">
                <p class="text-muted">Usuń wszystkie wpisy cache geolokalizacji. Użyj tylko w razie konieczności (wszystkie IP będą ponownie sprawdzane przez API).</p>
                <form method="post" action="<?= $basePath ?>/admin/index.php?action=geo_cache_cleanup" onsubmit="return confirm('UWAGA: To usunie CAŁY cache geolokalizacji. Czy na pewno kontynuować?');">
                  <input type="hidden" name="csrf" value="<?= $csrf ?>" />
                  <input type="hidden" name="action" value="clear_all" />
                  <button type="submit" class="btn btn-danger">Wyczyść cały cache</button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Ostatnie logi -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">Ostatnie logi</h5>
          </div>
          <div class="card-body">
            <?php if (!empty($logs)): ?>
              <pre class="mb-0" class="code-block-dark max-h-300-scroll">
<?php foreach ($logs as $line): ?><?= htmlspecialchars($line) . "\n" ?><?php endforeach; ?>
              </pre>
            <?php else: ?>
              <p class="text-muted mb-0">Brak wpisów logów.</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Informacje -->
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Informacje o cache</h5>
          </div>
          <div class="card-body">
            <ul class="mb-0">
              <li><strong>TTL cache:</strong> 30 dni — po tym okresie dane są ponownie pobierane z API</li>
              <li><strong>API:</strong> ip-api.com (darmowe, limit 45 req/min)</li>
              <li><strong>Anonimizacja:</strong> IP są maskowane przed zapisem (ostatni oktet IPv4)</li>
              <li><strong>Zalecenie:</strong> Czyszczenie starych wpisów (>60 dni) co miesiąc dla optymalizacji</li>
            </ul>
          </div>
        </div>

      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
