<?php
  $pageTitle = 'Backup i przywracanie — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10">

        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 class="mb-0">Backup i przywracanie</h2>
            <p class="text-muted mb-0">Twórz kopie zapasowe bazy danych i plików, przywracaj z backupu.</p>
          </div>
          <form method="post" action="<?= $basePath ?>/admin/index.php?action=backup_create">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
            <button type="submit" class="btn btn-primary" id="createBackupBtn" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Tworzenie...'; this.form.submit();">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/>
              </svg>
              Utwórz backup
            </button>
          </form>
        </div>

        <!-- Toast -->
        <?php if (!empty($_SESSION['toast_message'])): ?>
          <div class="alert <?= ($_SESSION['toast_type'] ?? 'info') === 'success' ? 'bg-success-subtle border border-success text-success-emphasis' : (($_SESSION['toast_type'] ?? 'info') === 'error' ? 'bg-danger-subtle border border-danger text-danger-emphasis' : 'bg-info-subtle border border-info text-info-emphasis') ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['toast_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
          </div>
          <?php unset($_SESSION['toast_message'], $_SESSION['toast_type']); ?>
        <?php endif; ?>

        <!-- Auto backup settings -->
        <div class="card shadow-sm mb-4">
          <div class="card-header">
            <h5 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/>
              </svg>
              Automatyczne kopie zapasowe
            </h5>
          </div>
          <div class="card-body">
            <?php
              $unitOptions = [
                'minutes' => ['label' => 'minut',   'seconds' => 60],
                'hours'   => ['label' => 'godzin',  'seconds' => 3600],
                'days'    => ['label' => 'dni',     'seconds' => 86400],
                'weeks'   => ['label' => 'tygodni', 'seconds' => 604800],
                'months'  => ['label' => 'miesięcy','seconds' => 2592000],
              ];
              $selectedUnit  = 'days';
              $selectedValue = 1;
              foreach (array_reverse($unitOptions) as $uKey => $uData) {
                if ($autoInterval >= $uData['seconds'] && $autoInterval % $uData['seconds'] === 0) {
                  $selectedUnit  = $uKey;
                  $selectedValue = intdiv($autoInterval, $uData['seconds']);
                  break;
                }
              }
            ?>
            <form method="post" action="<?= $basePath ?>/admin/index.php?action=backup_auto_settings">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken(), ENT_QUOTES, 'UTF-8') ?>" />

              <div class="mb-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" role="switch" name="backup_auto_enabled" id="backup_auto_enabled"<?= $autoEnabled ? ' checked' : '' ?>>
                  <label class="form-check-label" for="backup_auto_enabled">Włącz automatyczne kopie zapasowe</label>
                </div>
              </div>

              <div class="row g-3 align-top mb-3">
                <div class="col-auto">
                  <label class="form-label">Interwał</label>
                  <div class="input-group">
                    <input type="number" class="form-control" name="auto_interval_value" min="1" max="365" value="<?= (int)$selectedValue ?>" style="max-width: 80px;">
                    <select class="form-select" name="auto_interval_unit">
                      <?php foreach ($unitOptions as $uKey => $uData): ?>
                        <option value="<?= $uKey ?>"<?= $selectedUnit === $uKey ? ' selected' : '' ?>><?= $uData['label'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-auto">
                  <label for="backup_auto_keep" class="form-label">Zachowaj ostatnich</label>
                  <div class="input-group">
                    <input type="number" class="form-control" id="backup_auto_keep" name="backup_auto_keep" min="1" max="30" value="<?= (int)$autoKeep ?>" style="max-width: 80px;">
                    <span class="input-group-text">kopii</span>
                  </div>
                  <div class="form-text">Zakres: 1–30</div>
                </div>
                <div class="col-auto">
                  <label for="backup_preferred_hour" class="form-label">Preferowana godzina</label>
                  <select class="form-select" id="backup_preferred_hour" name="backup_preferred_hour" style="min-width: 160px;">
                    <option value=""<?= $preferredHour === null ? ' selected' : '' ?>>Bez preferencji</option>
                    <?php for ($h = 0; $h <= 23; $h++): ?>
                      <option value="<?= $h ?>"<?= $preferredHour === $h ? ' selected' : '' ?>><?= sprintf('%02d:00', $h) ?></option>
                    <?php endfor; ?>
                  </select>
                  <div class="form-text">Godzina w dobie (czas serwera)</div>
                </div>
              </div>

              <button type="submit" class="btn btn-primary">Zapisz ustawienia</button>

              <?php if ($autoEnabled && !empty($autoNextRun)): ?>
                <?php
                  $nextTs  = strtotime($autoNextRun);
                  $diffSec = $nextTs - time();
                  if ($diffSec <= 0) {
                      $diffStr = 'wkrótce';
                  } elseif ($diffSec < 3600) {
                      $diffStr = 'za ' . max(1, (int)round($diffSec / 60)) . ' min';
                  } elseif ($diffSec < 86400) {
                      $h = (int)floor($diffSec / 3600);
                      $m = (int)round(($diffSec % 3600) / 60);
                      $diffStr = 'za ' . $h . 'h' . ($m > 0 ? ' ' . $m . 'min' : '');
                  } else {
                      $days = (int)floor($diffSec / 86400);
                      $hrs  = (int)floor(($diffSec % 86400) / 3600);
                      $diffStr = 'za ' . $days . 'd ' . $hrs . 'h';
                  }
                ?>
                <div class="mt-3 d-flex align-items-center gap-2 text-muted small">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-clock flex-shrink-0" viewBox="0 0 16 16">
                    <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/>
                  </svg>
                  Następna automatyczna kopia:
                  <strong class="text-body"><?= htmlspecialchars($autoNextRun, ENT_QUOTES, 'UTF-8') ?></strong>
                  <span>(<?= $diffStr ?>)</span>
                </div>
              <?php endif; ?>
            </form>
          </div>
        </div>

        <!-- Restore from file -->
        <div class="card shadow-sm mb-4">
          <div class="card-header">
            <h5 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
              </svg>
              Przywróć z pliku
            </h5>
          </div>
          <div class="card-body">
            <form method="post" action="<?= $basePath ?>/admin/index.php?action=backup_restore_upload" enctype="multipart/form-data">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
              <div class="row g-3 align-items-end">
                <div class="col-md-8">
                  <label for="backup_file" class="form-label">Plik backupu (.zip)</label>
                  <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".zip" required />
                </div>
                <div class="col-md-4">
                  <button type="submit" class="btn btn-warning w-100" onclick="return confirm('UWAGA: Przywracanie nadpisze aktualną bazę danych i pliki! Kontynuować?')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                      <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/>
                      <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/>
                    </svg>
                    Przywróć
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Existing backups -->
        <div class="card shadow-sm">
          <div class="card-header">
            <h5 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5M5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1z"/>
                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1"/>
              </svg>
              Istniejące kopie zapasowe
              <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis ms-1"><?= count($backups) ?></span>
            </h5>
          </div>
          <div class="card-body p-0">
            <?php if (empty($backups)): ?>
              <div class="text-center text-muted py-5">
                <p class="mb-0">Brak kopii zapasowych. Utwórz pierwszą!</p>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Nazwa pliku</th>
                      <th>Typ</th>
                      <th>Rozmiar</th>
                      <th>Data utworzenia</th>
                      <th style="width: 200px;">Akcje</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($backups as $backup): ?>
                      <tr>
                        <td>
                          <code><?= htmlspecialchars($backup['filename']) ?></code>
                        </td>
                        <td>
                          <?php if (($backup['type'] ?? 'manual') === 'auto'): ?>
                            <span class="badge bg-info-subtle border border-info text-info-emphasis">Auto</span>
                          <?php else: ?>
                            <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">Ręczna</span>
                          <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($backup['size_formatted']) ?></td>
                        <td><?= htmlspecialchars($backup['created_at']) ?></td>
                        <td>
                          <div class="d-flex gap-1">
                            <form method="post" action="<?= $basePath ?>/admin/index.php?action=backup_download&file=<?= urlencode($backup['filename']) ?>" class="d-inline">
                              <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                              <button type="submit" class="btn btn-sm btn-outline-primary" title="Pobierz">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                  <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                  <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                                </svg>
                              </button>
                            </form>
                            <form method="post" action="<?= $basePath ?>/admin/index.php?action=backup_restore&file=<?= urlencode($backup['filename']) ?>" class="d-inline">
                              <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                              <button type="submit" class="btn btn-sm btn-outline-warning" title="Przywróć" onclick="return confirm('UWAGA: Przywracanie nadpisze aktualną bazę danych! Kontynuować?')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                  <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/>
                                  <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/>
                                </svg>
                              </button>
                            </form>
                            <form method="post" action="<?= $basePath ?>/admin/index.php?action=backup_delete&file=<?= urlencode($backup['filename']) ?>" class="d-inline">
                              <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                              <button type="submit" class="btn btn-sm btn-outline-danger" title="Usuń" onclick="return confirm('Usunąć tę kopię zapasową?')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                  <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                </svg>
                              </button>
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
        </div>

        <?php
          $uploadMax = ini_get('upload_max_filesize') ?: 'nieznany';
          $postMax   = ini_get('post_max_size') ?: 'nieznany';
          $maxUploadReadable = htmlspecialchars($uploadMax, ENT_QUOTES, 'UTF-8');
          $postMaxReadable   = htmlspecialchars($postMax, ENT_QUOTES, 'UTF-8');
        ?>
        <div class="text-muted small mt-3">
          Backupy są przechowywane w <code>App/storage/private_backups/</code> (poza zasięgiem publicznego HTTP). Ręczne: maks. 10 najnowszych; automatyczne: maks. tyle ile ustawiono w konfiguracji.<br>
          Maks. rozmiar wysyłanego pliku wg PHP: <strong><?= $maxUploadReadable ?></strong> (upload_max_filesize), limit żądania: <strong><?= $postMaxReadable ?></strong> (post_max_size). Większe archiwa zostaną odrzucone przez serwer.
        </div>

      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
