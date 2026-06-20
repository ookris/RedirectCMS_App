<?php
  $pageTitle = 'Zadania Cron — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container-xxl py-5">
    <!-- Toast Container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 2000">
      <div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body" id="toastMessage"></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>
            <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="currentColor" class="bi bi-list-check" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3.854 2.146a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 3.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 7.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/>
            </svg>
          Zadania Cron
        </h2>
          <div>
            <form method="post" action="<?= $basePath ?>/admin/index.php?action=cron_register_defaults" class="d-inline" data-confirm="Zarejestrować domyślne zadania cron?">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>" />
              <button type="submit" class="btn btn-success me-2">
                Zarejestruj domyślne zadania
              </button>
            </form>
          </div>
        </div>

        <!-- Pseudo-cron toggle -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">Wbudowany pseudo-cron</h5>
          </div>
          <div class="card-body">
            <p class="text-muted mb-3">
              Wbudowany pseudo-cron uruchamia zadania automatycznie przy każdym wejściu użytkownika na stronę frontendową.
            </p>
            <form method="post" action="<?= $basePath ?>/admin/index.php?action=cron_pseudo_toggle" class="d-flex align-items-center gap-3 flex-wrap">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>" />
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" name="pseudo_cron_enabled" id="pseudo_cron_enabled"
                       <?= $pseudoCronEnabled ? 'checked' : '' ?> onchange="this.form.submit()">
                <label class="form-check-label" for="pseudo_cron_enabled">
                  <?= $pseudoCronEnabled ? 'Włączony' : 'Wyłączony' ?>
                </label>
              </div>
            </form>
            <?php if ($pseudoCronEnabled): ?>
              <div class="alert bg-warning-subtle border border-warning text-warning-emphasis mt-3 mb-0 small">
                <strong>Wskazówka:</strong> Jeśli używasz zewnętrznego crontaba (patrz niżej), wyłącz wbudowany pseudo-cron, aby uniknąć konfliktu z podwójnym wykonywaniem zadań.
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- External Cron Runner -->
        <div class="card mb-4">
          <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Zewnętrzny Cron Runner</h5>
            <span class="badge bg-light text-primary">Zalecane dla produkcji</span>
          </div>
          <div class="card-body">
            <p class="text-muted mb-3">
              Dla lepszej niezawodności możesz skonfigurować prawdziwy cron (crontab) do wywoływania zadań.
              Obsługiwane są dwa tryby: HTTP (curl/wget) oraz CLI (php).
            </p>

            <?php if (empty($cronToken)): ?>
              <!-- Brak tokena - pokaż przycisk generowania -->
              <div class="alert bg-warning-subtle border border-warning text-warning-emphasis">
                <strong>Token nie jest skonfigurowany.</strong>
                Wygeneruj token, aby umożliwić zewnętrzne wywoływanie crona przez HTTP.
              </div>
              <form method="post" action="<?= $basePath ?>/admin/index.php?action=cron_generate_token" class="mb-3">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
                <button type="submit" class="btn btn-primary">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key me-1" viewBox="0 0 16 16">
                    <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z"/>
                    <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                  </svg>
                  Wygeneruj token
                </button>
              </form>
            <?php else: ?>
              <!-- Token istnieje - pokaż instrukcje -->
              <div class="row">
                <div class="col-lg-6 mb-3">
                  <h6 class="fw-bold">Tryb HTTP (curl/wget)</h6>
                  <p class="small text-muted">Idealny gdy masz dostęp do zewnętrznego crona (np. cron-job.org)</p>
                  <div class="alert alert-warning py-1 px-2 small mb-2">
                    <strong>Uwaga:</strong> Token w URL może trafiać do logów serwera i nagłówka <code>Referer</code>. Jeśli serwer loguje requesty, preferuj przekazywanie tokenu przez nagłówek HTTP:
                    <code>-H "X-Cron-Token: <?= htmlspecialchars(substr($cronToken, 0, 6)) ?>..."</code>
                  </div>
                  <div class="input-group mb-2">
                    <span class="input-group-text">URL</span>
                    <input type="text" class="form-control font-monospace" readonly
                           value="<?= htmlspecialchars($cronRunnerUrl) ?>?token=<?= htmlspecialchars($cronToken) ?>"
                           id="cronHttpUrl" />
                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('cronHttpUrl')">
                      Kopiuj
                    </button>
                  </div>
                  <div class="bg-dark border border-secondary text-light p-2 rounded small font-monospace">
                    # crontab - co 5 minut (z tokenem w nagłówku — bezpieczniejsze)<br>
                    */5 * * * * curl -s -H "X-Cron-Token: <?= htmlspecialchars($cronToken) ?>" "<?= htmlspecialchars($cronRunnerUrl) ?>" > /dev/null
                  </div>
                </div>

                <div class="col-lg-6 mb-3">
                  <h6 class="fw-bold">Tryb CLI (php)</h6>
                  <p class="small text-muted">Zalecany gdy masz dostęp SSH do serwera</p>
                  <div class="input-group mb-2">
                    <span class="input-group-text">Ścieżka</span>
                    <input type="text" class="form-control font-monospace" readonly
                           value="<?= htmlspecialchars($cronRunnerPath ?: '/path/to/App/cron_runner.php') ?>"
                           id="cronCliPath" />
                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('cronCliPath')">
                      Kopiuj
                    </button>
                  </div>
                  <div class="bg-dark border border-secondary text-light p-2 rounded small font-monospace">
                    # crontab - co 5 minut<br>
                    */5 * * * * /usr/bin/php <?= htmlspecialchars($cronRunnerPath ?: '/path/to/App/cron_runner.php') ?> >> /var/log/cron.log 2>&1
                  </div>
                </div>
              </div>

              <hr class="my-3">

              <div class="row">
                <div class="col-md-6">
                  <h6 class="fw-bold">Opcje CLI</h6>
                  <ul class="small mb-0">
                    <li><code>--help</code> - wyświetl pomoc</li>
                    <li><code>--list</code> - lista wszystkich zadań</li>
                    <li><code>--force</code> - uruchom wszystkie aktywne zadania (ignoruj harmonogram)</li>
                    <li><code>--job=nazwa</code> - uruchom tylko konkretne zadanie</li>
                  </ul>
                </div>
                <div class="col-md-6">
                  <h6 class="fw-bold">Zarządzanie tokenem</h6>
                  <p class="small text-muted mb-2">
                    Token: <code><?= htmlspecialchars(substr($cronToken, 0, 8)) ?>...</code>
                  </p>
                  <form method="post" action="<?= $basePath ?>/admin/index.php?action=cron_generate_token" data-confirm="Uwaga: Po wygenerowaniu nowego tokenu stary przestanie działać. Kontynuować?">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
                    <button type="submit" class="btn btn-sm btn-outline-warning">
                      Wygeneruj nowy token
                    </button>
                  </form>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Status crona -->
        <div class="card mb-4">
          <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <?php $lastRunTs = $lastCronRun ? strtotime($lastCronRun) : false; ?>
                <?php if ($lastRunTs !== false): ?>
                  <?php
                    $secondsAgo = time() - $lastRunTs;
                    if ($secondsAgo < 600) {
                      $statusColor = 'success';
                      $statusIcon = 'check-circle-fill';
                      $statusText = 'Cron działa prawidłowo';
                    } elseif ($secondsAgo < 3600) {
                      $statusColor = 'warning';
                      $statusIcon = 'exclamation-triangle-fill';
                      $statusText = 'Cron opóźniony';
                    } else {
                      $statusColor = 'danger';
                      $statusIcon = 'x-circle-fill';
                      $statusText = 'Cron nieaktywny';
                    }

                    if ($secondsAgo < 60) {
                      $agoText = 'przed chwilą';
                    } elseif ($secondsAgo < 3600) {
                      $agoText = floor($secondsAgo / 60) . ' min temu';
                    } elseif ($secondsAgo < 86400) {
                      $agoText = floor($secondsAgo / 3600) . ' godz. temu';
                    } else {
                      $agoText = floor($secondsAgo / 86400) . ' dni temu';
                    }
                  ?>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-<?= $statusIcon ?> text-<?= $statusColor ?> me-3" viewBox="0 0 16 16">
                    <?php if ($statusIcon === 'check-circle-fill'): ?>
                      <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    <?php elseif ($statusIcon === 'exclamation-triangle-fill'): ?>
                      <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                    <?php else: ?>
                      <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                    <?php endif; ?>
                  </svg>
                  <div>
                    <strong class="text-<?= $statusColor ?>"><?= $statusText ?></strong>
                    <br>
                    <small class="text-muted">
                      Ostatnie wykonanie: <?= date('d.m.Y H:i:s', $lastRunTs) ?> (<?= $agoText ?>)
                    </small>
                  </div>
                <?php else: ?>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-question-circle-fill text-secondary me-3" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286m1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94"/>
                  </svg>
                  <div>
                    <strong class="text-secondary">Brak danych</strong>
                    <br>
                    <small class="text-muted">Cron nie był jeszcze uruchomiony</small>
                  </div>
                <?php endif; ?>
              </div>
              <a href="<?= $basePath ?>/admin/index.php?action=logs&log=cron" class="btn btn-sm btn-outline-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-clock-history me-1" viewBox="0 0 16 16">
                  <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-1.398 1.8a7 7 0 0 0 .573-.606l.768.645a8 8 0 0 1-.39.46z"/>
                  <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
                  <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
                </svg>
                Historia
              </a>
            </div>
          </div>
        </div>

        <!-- Zadania -->
        <div class="card mb-4">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0">Zarejestrowane zadania</h5>
          </div>
          <div class="card-body">
            <?php if (empty($jobs)): ?>
              <div class="text-muted mb-0 d-flex align-items-center gap-2 flex-wrap">
                <span>Brak zarejestrowanych zadań.</span>
                <form method="post" action="<?= $basePath ?>/admin/index.php?action=cron_register_defaults" class="d-inline-block" data-confirm="Zarejestrować domyślne zadania cron?">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>" />
                  <button type="submit" class="btn btn-link p-0 align-baseline">
                    Zarejestruj domyślne zadania
                  </button>
                </form>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th style="width: 50px;">Status</th>
                      <th>Nazwa</th>
                      <th>Opis</th>
                      <th style="width: 120px;">Interwał</th>
                      <th style="width: 150px;">Ostatnie wykonanie</th>
                      <th style="width: 150px;">Następne wykonanie</th>
                      <th style="width: 100px;">Statystyki</th>
                      <th style="width: 200px;">Akcje</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($jobs as $job): 
                      $isActive = (int)$job['is_active'] === 1;
                      $totalRuns = (int)($job['total_runs'] ?? 0);
                      $errorCount = (int)($job['error_count'] ?? 0);
                      $avgTime = isset($job['avg_execution_time']) ? round((float)$job['avg_execution_time']) : null;
                    ?>
                      <tr>
                        <td>
                          <span class="job-status-badge <?= $isActive ? 'job-active' : 'job-inactive' ?>" 
                                title="<?= $isActive ? 'Aktywne' : 'Nieaktywne' ?>"></span>
                        </td>
                        <td><strong><?= htmlspecialchars($job['name']) ?></strong></td>
                        <td><small class="text-muted"><?= htmlspecialchars($job['description'] ?? '') ?></small></td>
                        <td>
                          <?php
                            $interval = (int)$job['interval_seconds'];
                            if ($interval >= 86400) {
                              $intervalLabel = round($interval / 86400) . ' dni';
                            } elseif ($interval >= 3600) {
                              $intervalLabel = round($interval / 3600) . ' godz';
                            } elseif ($interval >= 60) {
                              $intervalLabel = round($interval / 60) . ' min';
                            } else {
                              $intervalLabel = $interval . ' sek';
                            }
                          ?>
                          <button type="button" class="btn btn-sm btn-outline-secondary"
                                  data-bs-toggle="modal" data-bs-target="#intervalModal"
                                  data-job-id="<?= (int)$job['id'] ?>"
                                  data-job-name="<?= htmlspecialchars($job['name'], ENT_QUOTES, 'UTF-8') ?>"
                                  data-interval="<?= $interval ?>"
                                  title="Zmień interwał">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil me-1" viewBox="0 0 16 16">
                              <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                            </svg>
                            <?= $intervalLabel ?>
                          </button>
                        </td>
                        <td>
                          <?php if (!empty($job['last_run'])): ?>
                            <small><?= date('d.m.y H:i', strtotime($job['last_run'])) ?></small>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if (!empty($job['next_run'])): 
                            $nextRunTime = strtotime($job['next_run']);
                            $isPast = $nextRunTime <= time();
                          ?>
                            <small class="<?= $isPast ? 'text-warning' : '' ?>">
                              <?= date('d.m.y H:i', $nextRunTime) ?>
                            </small>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <small>
                            Wykonań: <strong><?= $totalRuns ?></strong><br>
                            Błędów: <strong class="<?= $errorCount > 0 ? 'text-danger' : '' ?>"><?= $errorCount ?></strong><br>
                            <?php if ($avgTime !== null): ?>
                              Śr. czas: <strong><?= $avgTime ?>ms</strong>
                            <?php endif; ?>
                          </small>
                        </td>
                        <td>
                          <div class="btn-group btn-group-sm" role="group">
                            <form method="post" action="<?= $basePath ?>/admin/index.php?action=cron_toggle" class="d-inline me-1">
                              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
                              <input type="hidden" name="job_id" value="<?= (int)$job['id'] ?>" />
                              <input type="hidden" name="is_active" value="<?= $isActive ? '0' : '1' ?>" />
                              <button type="submit" class="btn btn-sm btn-<?= $isActive ? 'warning' : 'success' ?>" title="<?= $isActive ? 'Wyłącz zadanie' : 'Włącz zadanie' ?>" data-bs-toggle="tooltip">
                                <?php if ($isActive): ?>
                                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-stop-fill" viewBox="0 0 16 16">
                                    <path d="M5 3.5h6A1.5 1.5 0 0 1 12.5 5v6a1.5 1.5 0 0 1-1.5 1.5H5A1.5 1.5 0 0 1 3.5 11V5A1.5 1.5 0 0 1 5 3.5"/>
                                  </svg>
                                <?php else: ?>
                                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393"/>
                                  </svg>
                                <?php endif; ?>
                              </button>
                            </form>
                            
                            <form method="post" action="<?= $basePath ?>/admin/index.php?action=cron_run_now" class="d-inline me-1">
                              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
                              <input type="hidden" name="job_id" value="<?= (int)$job['id'] ?>" />
                              <button type="submit" class="btn btn-sm btn-primary" title="Uruchom zadanie natychmiast" data-bs-toggle="tooltip">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                  <path d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445"/>
                                </svg>
                              </button>
                            </form>
                            
                            <a href="<?= $basePath ?>/admin/index.php?action=cron_logs&job_id=<?= (int)$job['id'] ?>" class="btn btn-sm btn-info me-1" title="Zobacz historię wykonań" data-bs-toggle="tooltip">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
                              </svg>
                            </a>
                            
                            <form method="post" action="<?= $basePath ?>/admin/index.php?action=cron_delete" class="d-inline me-1" data-confirm="Czy na pewno usunąć to zadanie?">
                              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
                              <input type="hidden" name="job_id" value="<?= (int)$job['id'] ?>" />
                              <button type="submit" class="btn btn-sm btn-danger" title="Usuń zadanie" data-bs-toggle="tooltip">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
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

        <!-- Modal zmiany interwału -->
        <div class="modal fade" id="intervalModal" tabindex="-1" aria-labelledby="intervalModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-sm">
            <div class="modal-content">
              <form method="post" action="<?= $basePath ?>/admin/index.php?action=cron_update_interval">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
                <input type="hidden" name="job_id" id="intervalJobId" value="" />
                <div class="modal-header">
                  <h6 class="modal-title" id="intervalModalLabel">Zmień interwał</h6>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <p class="small text-muted mb-2">Zadanie: <strong id="intervalJobName"></strong></p>
                  <label class="form-label">Interwał</label>
                  <div class="input-group">
                    <input type="number" class="form-control" id="intervalValue" min="1" max="365" step="1" placeholder="np. 30" required>
                    <select class="form-select" id="intervalUnit" style="max-width: 140px;">
                      <option value="60">minut</option>
                      <option value="3600">godzin</option>
                      <option value="86400">dni</option>
                      <option value="604800">tygodni</option>
                      <option value="2592000">miesięcy</option>
                    </select>
                  </div>
                  <div class="form-text" id="intervalPreview"></div>
                  <input type="hidden" name="interval_seconds" id="intervalSecondsInput" value="" />
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Anuluj</button>
                  <button type="submit" class="btn btn-primary btn-sm">Zapisz</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Ostatnie logi -->
        <?php if (!empty($recentLogs)): ?>
        <div class="card">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0">Ostatnie wykonania</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Zadanie</th>
                    <th>Start</th>
                    <th>Koniec</th>
                    <th>Status</th>
                    <th>Czas</th>
                    <th>Błąd</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recentLogs as $log): 
                    $status = $log['status'];
                    $statusClass = match($status) {
                      'success' => 'log-status-success',
                      'error' => 'log-status-error',
                      'running' => 'log-status-running',
                      default => ''
                    };
                  ?>
                    <tr>
                      <td><strong><?= htmlspecialchars($log['job_name'] ?? 'N/A') ?></strong></td>
                      <td><small><?= date('d.m.y H:i:s', strtotime($log['started_at'])) ?></small></td>
                      <td>
                        <?php if (!empty($log['finished_at'])): ?>
                          <small><?= date('d.m.y H:i:s', strtotime($log['finished_at'])) ?></small>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php $badgeColor = $status === 'success' ? 'success' : ($status === 'error' ? 'danger' : 'warning'); ?>
                        <span class="badge bg-<?= $badgeColor ?>-subtle border border-<?= $badgeColor ?> text-<?= $badgeColor ?>-emphasis">
                          <?= ucfirst($status) ?>
                        </span>
                      </td>
                      <td>
                        <?php if ($log['execution_time_ms']): ?>
                          <small><?= number_format($log['execution_time_ms']) ?>ms</small>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!empty($log['error_message'])): ?>
                          <small class="text-danger" title="<?= htmlspecialchars($log['error_message']) ?>">
                            <?= htmlspecialchars(substr($log['error_message'], 0, 50)) ?>...
                          </small>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <script>
    <?php if (!empty($_SESSION['cron_success'])): ?>
      const toastEl = document.getElementById('liveToast');
      const toastBody = document.getElementById('toastMessage');
      toastBody.textContent = <?= json_encode($_SESSION['cron_success']) ?>;
      toastEl.classList.add('bg-success', 'text-white');
      const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
      toast.show();
      <?php unset($_SESSION['cron_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['cron_error'])): ?>
      const toastEl = document.getElementById('liveToast');
      const toastBody = document.getElementById('toastMessage');
      toastBody.textContent = <?= json_encode($_SESSION['cron_error']) ?>;
      toastEl.classList.add('bg-danger', 'text-white');
      const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
      toast.show();
      <?php unset($_SESSION['cron_error']); ?>
    <?php endif; ?>

    // Obsługa potwierdzeń akcji – przeniesiona z inline handlerów atrybutów na JS
    document.querySelectorAll('form[data-confirm]').forEach(function(form) {
      form.addEventListener('submit', function(event) {
        const message = form.getAttribute('data-confirm');
        if (message && !window.confirm(message)) {
          event.preventDefault();
          event.stopPropagation();
        }
      });
    });

    // Modal interwału - wypełnij danymi z klikniętego przycisku
    const intervalModal = document.getElementById('intervalModal');
    const intervalValue = document.getElementById('intervalValue');
    const intervalUnit = document.getElementById('intervalUnit');

    if (intervalModal) {
      intervalModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const jobId = button.getAttribute('data-job-id');
        const jobName = button.getAttribute('data-job-name');
        const totalSeconds = parseInt(button.getAttribute('data-interval'));

        document.getElementById('intervalJobId').value = jobId;
        document.getElementById('intervalJobName').textContent = jobName;

        // Rozłóż sekundy na najlepszą jednostkę
        const units = [
          { value: 2592000, key: '2592000' },  // miesiące (30 dni)
          { value: 604800, key: '604800' },     // tygodnie
          { value: 86400, key: '86400' },       // dni
          { value: 3600, key: '3600' },         // godziny
          { value: 60, key: '60' }              // minuty
        ];

        let bestUnit = '60';
        let bestValue = Math.round(totalSeconds / 60);

        for (const unit of units) {
          if (totalSeconds >= unit.value && totalSeconds % unit.value === 0) {
            bestUnit = unit.key;
            bestValue = totalSeconds / unit.value;
            break;
          }
        }

        intervalUnit.value = bestUnit;
        intervalValue.value = bestValue;
        updateIntervalInput();
      });
    }

    function updateIntervalInput() {
      const value = parseInt(intervalValue.value) || 0;
      const unitSeconds = parseInt(intervalUnit.value) || 60;
      const totalSeconds = value * unitSeconds;

      document.getElementById('intervalSecondsInput').value = totalSeconds;

      // Podgląd w czytelnym formacie
      const preview = document.getElementById('intervalPreview');
      if (value > 0) {
        const unitLabels = { '60': 'min', '3600': 'godz', '86400': 'dni', '604800': 'tyg', '2592000': 'mies' };
        preview.textContent = 'Interwał: ' + value + ' ' + (unitLabels[intervalUnit.value] || '') + ' (' + totalSeconds.toLocaleString() + ' sek)';
      } else {
        preview.textContent = '';
      }
    }

    if (intervalValue) {
      intervalValue.addEventListener('input', updateIntervalInput);
    }
    if (intervalUnit) {
      intervalUnit.addEventListener('change', updateIntervalInput);
    }

    // Funkcja kopiowania do schowka
    function copyToClipboard(inputId) {
      const input = document.getElementById(inputId);
      input.select();
      input.setSelectionRange(0, 99999); // Dla urządzeń mobilnych

      navigator.clipboard.writeText(input.value).then(function() {
        // Pokaż toast z potwierdzeniem
        const toastEl = document.getElementById('liveToast');
        const toastBody = document.getElementById('toastMessage');
        toastBody.textContent = 'Skopiowano do schowka';
        toastEl.classList.remove('bg-danger');
        toastEl.classList.add('bg-success', 'text-white');
        const toast = new bootstrap.Toast(toastEl, { delay: 2000 });
        toast.show();
      }).catch(function(err) {
        console.error('Błąd kopiowania: ', err);
      });
    }
  </script>
</body>
</html>
