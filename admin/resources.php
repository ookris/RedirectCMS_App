<?php
  $pageTitle = 'Zasoby — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <!-- Toast Container -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 2000">
    <div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessage"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <div class="container py-4">
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-hdd me-2" viewBox="0 0 16 16">
              <path d="M4.5 11a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1M3 10.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
              <path d="M16 11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V9.51c0-.418.105-.83.305-1.197l2.472-4.531A1.5 1.5 0 0 1 4.094 3h7.812a1.5 1.5 0 0 1 1.317.782l2.472 4.53c.2.368.305.78.305 1.198zM3.655 4.26 1.592 8.043C1.724 8.014 1.86 8 2 8h12c.14 0 .276.014.408.042L12.345 4.26a.5.5 0 0 0-.439-.26H4.094a.5.5 0 0 0-.44.26M1 10v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1"/>
            </svg>
            Zasoby
          </h2>
        </div>

        <!-- Informacja o cache i przycisk odświeżania -->
        <?php if ($lastCalculated): ?>
        <div class="alert bg-success-subtle border border-success text-success-emphasis d-flex justify-content-between align-items-center mb-4">
          <div>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history me-2" viewBox="0 0 16 16">
              <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>
              <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
              <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
            </svg>
            Ostatnie przeliczenie: <strong><?= date('Y-m-d H:i:s', $lastCalculated) ?></strong>
            <span class="text-muted small">(automatycznie aktualizowane co 6 godzin)</span>
          </div>
          <form method="POST" action="<?= $basePath ?>/admin/index.php?action=recalculate_storage" class="mb-0">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit" class="btn btn-sm btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-clockwise me-1" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
              </svg>
              Przelicz teraz
            </button>
          </form>
        </div>
        <?php else: ?>
        <div class="alert bg-warning-subtle border border-warning text-warning-emphasis d-flex justify-content-between align-items-center mb-4">
          <div>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle me-2" viewBox="0 0 16 16">
              <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
              <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
            </svg>
            Rozmiary katalogów nie zostały jeszcze przeliczone. Kliknij przycisk aby obliczyć.
          </div>
          <form method="POST" action="<?= $basePath ?>/admin/index.php?action=recalculate_storage" class="mb-0">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit" class="btn btn-sm btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-clockwise me-1" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
              </svg>
              Przelicz teraz
            </button>
          </form>
        </div>
        <?php endif; ?>

        <!-- Limity miejsca na dysku -->
        <?php if (!empty($diskQuotaData)): ?>
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-secondary">
              <div class="card-header bg-white">
                <h5 class="mb-0">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16">
                    <path d="M11 2H9v3h2z"/>
                    <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/>
                  </svg>
                  Limity miejsca na dysku
                </h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <?php if (!empty($diskQuotaData['files'])): ?>
                  <div class="col-md-6 mb-3 mb-md-0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="fw-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-folder me-1" viewBox="0 0 16 16">
                          <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z"/>
                        </svg>
                        Pliki
                      </span>
                      <span class="text-muted small">
                        <?php
                          $filesCurrentDisplay = $diskQuotaData['files']['current_mb'] >= 1024
                            ? number_format($diskQuotaData['files']['current_mb'] / 1024, 2, ',', ' ') . ' GB'
                            : number_format($diskQuotaData['files']['current_mb'], 2, ',', ' ') . ' MB';
                          $filesLimitDisplay = $diskQuotaData['files']['limit_mb'] >= 1024
                            ? number_format($diskQuotaData['files']['limit_mb'] / 1024, 2, ',', ' ') . ' GB'
                            : number_format($diskQuotaData['files']['limit_mb'], 0, ',', ' ') . ' MB';
                        ?>
                        <?= $filesCurrentDisplay ?> / <?= $filesLimitDisplay ?>
                      </span>
                    </div>
                    <div class="progress" style="height: 20px;">
                      <div class="progress-bar bg-<?= $diskQuotaData['files']['color_class'] ?>"
                           role="progressbar"
                           style="width: <?= $diskQuotaData['files']['percentage'] ?>%"
                           aria-valuenow="<?= $diskQuotaData['files']['percentage'] ?>"
                           aria-valuemin="0"
                           aria-valuemax="100">
                        <?= $diskQuotaData['files']['percentage'] ?>%
                      </div>
                    </div>
                  </div>
                  <?php endif; ?>
                  <?php if (!empty($diskQuotaData['database'])): ?>
                  <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="fw-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-database me-1" viewBox="0 0 16 16">
                          <path d="M4.318 2.687C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4c0-.374.356-.875 1.318-1.313M13 5.698V7c0 .374-.356.875-1.318 1.313C10.766 8.729 9.464 9 8 9s-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777A5 5 0 0 0 13 5.698M14 4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16s3.022-.289 4.096-.777C13.125 14.755 14 14.007 14 13zm-1 4.698V10c0 .374-.356.875-1.318 1.313C10.766 11.729 9.464 12 8 12s-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10s3.022-.289 4.096-.777A5 5 0 0 0 13 8.698m0 3V13c0 .374-.356.875-1.318 1.313C10.766 14.729 9.464 15 8 15s-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13s3.022-.289 4.096-.777c.324-.147.633-.323.904-.525"/>
                        </svg>
                        Baza danych
                      </span>
                      <span class="text-muted small">
                        <?php
                          $dbCurrentDisplay = $diskQuotaData['database']['current_mb'] >= 1024
                            ? number_format($diskQuotaData['database']['current_mb'] / 1024, 2, ',', ' ') . ' GB'
                            : number_format($diskQuotaData['database']['current_mb'], 2, ',', ' ') . ' MB';
                          $dbLimitDisplay = $diskQuotaData['database']['limit_mb'] >= 1024
                            ? number_format($diskQuotaData['database']['limit_mb'] / 1024, 2, ',', ' ') . ' GB'
                            : number_format($diskQuotaData['database']['limit_mb'], 0, ',', ' ') . ' MB';
                        ?>
                        <?= $dbCurrentDisplay ?> / <?= $dbLimitDisplay ?>
                      </span>
                    </div>
                    <div class="progress" style="height: 20px;">
                      <div class="progress-bar bg-<?= $diskQuotaData['database']['color_class'] ?>"
                           role="progressbar"
                           style="width: <?= $diskQuotaData['database']['percentage'] ?>%"
                           aria-valuenow="<?= $diskQuotaData['database']['percentage'] ?>"
                           aria-valuemin="0"
                           aria-valuemax="100">
                        <?= $diskQuotaData['database']['percentage'] ?>%
                      </div>
                    </div>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Podsumowanie -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-3 mb-4">
          <div class="col">
            <div class="card border-primary h-100">
              <div class="card-body text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-hdd text-primary mb-3" viewBox="0 0 16 16">
                  <path d="M4.5 11a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1M3 10.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                  <path d="M16 11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V9.51c0-.418.105-.83.305-1.197l2.472-4.531A1.5 1.5 0 0 1 4.094 3h7.812a1.5 1.5 0 0 1 1.317.782l2.472 4.53c.2.368.305.78.305 1.198zM3.655 4.26 1.592 8.043C1.724 8.014 1.86 8 2 8h12c.14 0 .276.014.408.042L12.345 4.26a.5.5 0 0 0-.439-.26H4.094a.5.5 0 0 0-.44.26M1 10v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1"/>
                </svg>
                <h3 class="h5 mb-1">Łączna wielkość</h3>
                <p class="display-6 text-primary mb-0"><?= htmlspecialchars($totalSizeFormatted) ?></p>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="card border-success h-100">
              <div class="card-body text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-cloud-upload text-success mb-3" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383"/>
                  <path fill-rule="evenodd" d="M7.646 4.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V14.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708z"/>
                </svg>
                <h3 class="h5 mb-1">Katalog <code>/uploads</code></h3>
                <p class="display-6 text-success mb-0"><?= htmlspecialchars($uploadsSizeFormatted) ?></p>
                <p class="text-muted small mb-0"><?= number_format($uploadsCount, 0, ',', ' ') ?> <?= $uploadsCount === 1 ? 'plik' : 'plików' ?></p>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="card border-warning h-100">
              <div class="card-body text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-file-text text-warning mb-3" viewBox="0 0 16 16">
                  <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5M5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1z"/>
                  <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1"/>
                </svg>
                <h3 class="h5 mb-1">Katalog <code>/logs</code></h3>
                <p class="display-6 text-warning mb-0"><?= htmlspecialchars($logsSizeFormatted) ?></p>
                <p class="text-muted small mb-0"><?= number_format($logsCount, 0, ',', ' ') ?> <?= $logsCount === 1 ? 'plik' : 'plików' ?></p>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="card border-info h-100">
              <div class="card-body text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-lightning-charge text-info mb-3" viewBox="0 0 16 16">
                  <path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z"/>
                </svg>
                <h3 class="h5 mb-1">Katalog <code>/cache</code></h3>
                <p class="display-6 text-info mb-0"><?= htmlspecialchars($cacheSizeFormatted) ?></p>
                <p class="text-muted small mb-0"><?= number_format($cacheCount, 0, ',', ' ') ?> <?= $cacheCount === 1 ? 'plik' : 'plików' ?></p>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="card border-secondary h-100">
              <div class="card-body text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-archive text-secondary mb-3" viewBox="0 0 16 16">
                  <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                </svg>
                <h3 class="h5 mb-1">Katalog <code>/backups</code></h3>
                <p class="display-6 text-secondary mb-0"><?= htmlspecialchars($backupsSizeFormatted) ?></p>
                <p class="text-muted small mb-0"><?= number_format($backupsCount, 0, ',', ' ') ?> <?= $backupsCount === 1 ? 'plik' : 'plików' ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Szczegóły katalogu uploads -->
        <?php if (!empty($uploadsSubdirs)): ?>
        <div class="card mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-folder-open me-2" viewBox="0 0 16 16">
                <path d="M1 3.5A1.5 1.5 0 0 1 2.5 2h2.764c.958 0 1.76.56 2.311 1.184C7.985 3.648 8.48 4 9 4h4.5A1.5 1.5 0 0 1 15 5.5v.64c.57.265.94.876.856 1.546l-.64 5.124A2.5 2.5 0 0 1 12.733 15H3.266a2.5 2.5 0 0 1-2.481-2.19l-.64-5.124A1.5 1.5 0 0 1 1 6.14zM2 6h12v-.5a.5.5 0 0 0-.5-.5H9c-.964 0-1.71-.629-2.174-1.154C6.374 3.334 5.82 3 5.264 3H2.5a.5.5 0 0 0-.5.5zm-.367 1a.5.5 0 0 0-.496.562l.64 5.124A1.5 1.5 0 0 0 3.266 14h9.468a1.5 1.5 0 0 0 1.489-1.314l.64-5.124A.5.5 0 0 0 14.367 7z"/>
              </svg>
              Podkatalogi w /uploads
            </h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Nazwa katalogu</th>
                    <th class="text-end">Liczba plików</th>
                    <th class="text-end">Wielkość</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($uploadsSubdirs as $subdirData): ?>
                  <tr>
                    <td>
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-folder me-2 text-primary" viewBox="0 0 16 16">
                        <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z"/>
                      </svg>
                      <strong><?= htmlspecialchars($subdirData['name']) ?></strong>
                    </td>
                    <td class="text-end"><?= number_format($subdirData['file_count'], 0, ',', ' ') ?></td>
                    <td class="text-end"><span class="badge bg-success-subtle border border-success text-success-emphasis"><?= htmlspecialchars($subdirData['size_formatted']) ?></span></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Szczegóły katalogu storage -->
        <div class="card mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-folder-open me-2" viewBox="0 0 16 16">
                <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z"/>
              </svg>
              Katalogi w /storage
            </h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Nazwa katalogu</th>
                    <th class="text-end">Liczba plików</th>
                    <th class="text-end">Wielkość</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-folder me-2 text-warning" viewBox="0 0 16 16">
                        <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z"/>
                      </svg>
                      <strong>logs</strong>
                    </td>
                    <td class="text-end"><?= number_format($logsCount, 0, ',', ' ') ?></td>
                    <td class="text-end"><span class="badge bg-warning-subtle border border-warning text-warning-emphasis"><?= htmlspecialchars($logsSizeFormatted) ?></span></td>
                  </tr>
                  <tr>
                    <td>
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-folder me-2 text-info" viewBox="0 0 16 16">
                        <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z"/>
                      </svg>
                      <strong>cache</strong>
                    </td>
                    <td class="text-end"><?= number_format($cacheCount, 0, ',', ' ') ?></td>
                    <td class="text-end"><span class="badge bg-info-subtle border border-info text-info-emphasis"><?= htmlspecialchars($cacheSizeFormatted) ?></span></td>
                  </tr>
                  <tr>
                    <td>
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-folder me-2 text-secondary" viewBox="0 0 16 16">
                        <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z"/>
                      </svg>
                      <strong>backups</strong>
                    </td>
                    <td class="text-end"><?= number_format($backupsCount, 0, ',', ' ') ?></td>
                    <td class="text-end"><span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis"><?= htmlspecialchars($backupsSizeFormatted) ?></span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Informacje -->
        <div class="alert bg-info-subtle border border-info text-info-emphasis">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-info-circle me-2" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
            <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
          </svg>
          <strong>Informacje:</strong>
          <ul class="mb-0 mt-2">
            <li><strong>/uploads</strong> - katalog zawierający przesłane pliki (obrazy OG, zdjęcia w galeriach, branding)</li>
            <li><strong>/storage/logs</strong> - katalog zawierający pliki logów aplikacji</li>
            <li><strong>/storage/cache</strong> - katalog zawierający pliki cache (tymczasowe dane, sesje)</li>
            <li><strong>/storage/private_backups</strong> - katalog zawierający kopie zapasowe bazy danych i plików (niepubliczny)</li>
            <li>Rozmiary katalogów są automatycznie przeliczane co 6 godzin przez zadanie cron</li>
            <li>Regularne czyszczenie starych logów i cache pomoże zaoszczędzić miejsce na dysku</li>
            <li>Możesz skonfigurować automatyczne czyszczenie w sekcji <a href="<?= $basePath ?>/admin/index.php?action=cron_jobs">Zadania Cron</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Toast notifications
    <?php if (!empty($_SESSION['toast_message'])): ?>
      const toastEl = document.getElementById('liveToast');
      const toastBody = document.getElementById('toastMessage');
      const toastMessage = <?= json_encode($_SESSION['toast_message']) ?>;
      const toastType = <?= json_encode($_SESSION['toast_type'] ?? 'info') ?>;

      toastBody.textContent = toastMessage;

      // Ustaw kolor tła w zależności od typu
      if (toastType === 'success') {
        toastEl.classList.add('bg-success', 'text-white');
      } else if (toastType === 'error') {
        toastEl.classList.add('bg-danger', 'text-white');
      } else if (toastType === 'warning') {
        toastEl.classList.add('bg-warning', 'text-dark');
      } else {
        toastEl.classList.add('bg-info', 'text-white');
      }

      const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
      toast.show();

      <?php
        unset($_SESSION['toast_message'], $_SESSION['toast_type']);
      ?>
    <?php endif; ?>
  </script>

  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
