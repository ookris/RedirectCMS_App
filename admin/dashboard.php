<?php
  $pageTitle = 'Panel — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light dashboard-page">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <!-- Zawartość główna -->
  <div class="container py-5">    <!-- Toast Container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 2000">
      <div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body" id="toastMessage"></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>

    <!-- Baner wersji DEMO -->
    <?php if (($licenseStatus['state'] ?? '') === 'demo_active'): ?>
    <?php
        $_dDaysLeft = (int)($licenseStatus['demo_days_left'] ?? 0);
        $_dClass    = $_dDaysLeft <= 3 ? 'warning' : 'info';
        $_dExpires  = $licenseStatus['demo_expires_at'] ?? null;
        $_dExpFmt   = $_dExpires ? date('d.m.Y', strtotime($_dExpires . ' UTC')) : null;
        $_shopBuy   = defined('RCMS_SHOP_URL') ? RCMS_SHOP_URL . '/buy.php' : '';
    ?>
    <div class="alert alert-<?= $_dClass ?> alert-dismissible d-flex align-items-center gap-3 mb-4 shadow-sm" role="alert">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-gift-fill flex-shrink-0" viewBox="0 0 16 16">
        <path d="M3 2.5a2.5 2.5 0 0 1 5 0 2.5 2.5 0 0 1 5 0v.006c0 .07 0 .27-.038.494H15a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 1 14.5V7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h2.038A2.968 2.968 0 0 1 3 2.506V2.5z"/>
      </svg>
      <div class="flex-grow-1">
        <strong>Pracujesz na wersji DEMO RedirectCMS</strong>
        <?php if ($_dExpFmt !== null): ?>
          — ważna do <strong><?= htmlspecialchars($_dExpFmt) ?></strong>
          (pozostało <strong><?= $_dDaysLeft ?></strong> <?= $_dDaysLeft === 1 ? 'dzień' : 'dni' ?>)
        <?php endif; ?>
        <div class="mt-1 small">
          Przetestuj wszystkie funkcje i zdecyduj się na zakup pełnej licencji.
          <?php if ($_shopBuy !== ''): ?>
            &nbsp;<a href="<?= htmlspecialchars($_shopBuy) ?>" class="alert-link fw-semibold" target="_blank" rel="noopener">Kup licencję →</a>
          <?php endif; ?>
        </div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
    </div>
    <?php endif; ?>

    <!-- Baner nowej wersji -->
    <?php if (!empty($versionInfo['update_available'])): ?>
    <div class="alert alert-info alert-dismissible d-flex align-items-center gap-3 mb-4 shadow-sm" role="alert">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-arrow-up-circle-fill flex-shrink-0" viewBox="0 0 16 16">
        <path d="M16 8A8 8 0 1 0 0 8a8 8 0 0 0 16 0m-7.5 3.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707z"/>
      </svg>
      <div class="flex-grow-1">
        <strong>Dostępna nowa wersja RedirectCMS: <?= htmlspecialchars($versionInfo['latest_version'] ?? '') ?></strong>
        <?php if (!empty($versionInfo['latest_title'])): ?>
          &mdash; <?= htmlspecialchars($versionInfo['latest_title']) ?>
        <?php endif; ?>
        <?php if (!empty($versionInfo['published_at'])): ?>
          <small class="text-muted ms-2">(<?= htmlspecialchars($versionInfo['published_at']) ?>)</small>
        <?php endif; ?>
        <div class="mt-1 small">
          Zainstalowana: <strong>v<?= htmlspecialchars($versionInfo['current_version'] ?? '') ?></strong>
          <?php if (!empty($versionInfo['changelog_url'])): ?>
            &nbsp;&mdash;&nbsp;<a href="<?= htmlspecialchars($versionInfo['changelog_url']) ?>" target="_blank" rel="noopener noreferrer" class="alert-link">Zobacz changelog</a>
          <?php endif; ?>
        </div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
    </div>
    <?php endif; ?>

        <!-- Status systemu -->
    <?php if (!empty($systemStatus)): ?>
    <div class="row mb-5">
      <div class="col-12">
        <h3 class="mb-4">Status systemu</h3>
      </div>

    <!-- Widgety Dashboard -->
    <div class="row mb-5">
      <!-- Anomalies / Alerts -->
      <?php if (!empty($anomalies)): ?>
      <div class="col-12 mb-4">
        <?php foreach ($anomalies as $alert): ?>
        <div class="card shadow-sm border-<?= $alert['type'] === 'spike' ? 'success' : ($alert['type'] === 'drop' ? 'warning' : 'danger') ?> widget-card">
          <div class="card-header bg-<?= $alert['type'] === 'spike' ? 'success' : ($alert['type'] === 'drop' ? 'warning' : 'danger') ?>-subtle">
            <h5 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16">
                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
              </svg> 
              <span class="ms-2">Informacja</span>
                <span class="ms-2 badge bg-<?= $alert['type'] === 'spike' ? 'success' : ($alert['type'] === 'drop' ? 'warning' : 'danger') ?> me-2">
                <?= $alert['type'] === 'spike' ? 'Wzrost ruchu' : ($alert['type'] === 'drop' ? 'Spadek ruchu' : 'Alert') ?>
              </span>
              
              <?php endforeach; ?>
            </h5>
          </div>
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($alert['message']) ?></h5>
            <div class="mb-2">
              <div class="mt-1">
                Dzisiaj: <strong><?= number_format($alert['value']) ?></strong> |
                Średnia: <strong><?= number_format($alert['baseline'], 0) ?></strong>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Statystyki -->
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-info">
          <div class="card-header bg-info-subtle">
            <h5 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-graph-up" viewBox="0 0 16 16" style="margin-right: 8px;">
                <path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07"/>
              </svg>
              Statystyki
            </h5>
          </div>
          <div class="card-body">
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Łącznie linków:</span>
                <strong><?= number_format($systemStatus['statistics']['total_links'] ?? 0) ?></strong>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Wszystkie kliknięcia:</span>
                <strong><?= number_format($systemStatus['statistics']['total_clicks'] ?? 0) ?></strong>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Średnia na link:</span>
                <strong><?= number_format($systemStatus['statistics']['avg_clicks_per_link'] ?? 0, 1) ?></strong>
              </div>
            </div>
            <div class="mb-1 pt-2 border-top">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Kliknięcia dzisiaj:</span>
                <strong class="text-success"><?= number_format($systemStatus['statistics']['clicks_today'] ?? 0) ?></strong>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">→ Unikalni:</span>
                <span class="text-muted"><?= number_format($systemStatus['statistics']['unique_today'] ?? 0) ?></span>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">W tym tygodniu:</span>
                <strong class="text-info"><?= number_format($systemStatus['statistics']['clicks_this_week'] ?? 0) ?></strong>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">→ Unikalni:</span>
                <span class="text-muted"><?= number_format($systemStatus['statistics']['unique_this_week'] ?? 0) ?></span>
              </div>
            </div>
            <div class="mb-1 pt-2 border-top">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Nowe linki (miesiąc):</span>
                <strong><?= number_format($systemStatus['statistics']['links_this_month'] ?? 0) ?></strong>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Bez kliknięć:</span>
                <strong class="<?= ($systemStatus['statistics']['links_without_clicks'] ?? 0) > 0 ? 'text-warning' : '' ?>"><?= number_format($systemStatus['statistics']['links_without_clicks'] ?? 0) ?></strong>
              </div>
            </div>
            <div class="mb-1 pt-2 border-top">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Kategorie:</span>
                <strong><?= number_format($systemStatus['statistics']['categories_count'] ?? 0) ?></strong>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Programy afiliacyjne:</span>
                <strong><?= number_format($systemStatus['statistics']['affiliate_programs_count'] ?? 0) ?></strong>
              </div>
            </div>
            <?php if (!empty($systemStatus['statistics']['last_activity'])): ?>
            <div class="mt-2 pt-2 border-top">
              <div class="d-flex justify-content-between">
                <small class="text-muted">Ostatnia aktywność:</small>
                <small><?= date('Y-m-d H:i:s', strtotime($systemStatus['statistics']['last_activity'])) ?></small>
            </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <!-- Zasoby -->
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-info">
          <div class="card-header bg-info-subtle">
            <h5 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-hdd" viewBox="0 0 16 16" style="margin-right: 8px;">
                <path d="M4.5 11a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1M3 10.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                <path d="M16 11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V9.51c0-.418.105-.83.305-1.197l2.472-4.531A1.5 1.5 0 0 1 4.094 3h7.812a1.5 1.5 0 0 1 1.317.782l2.472 4.53c.2.368.305.78.305 1.198V11zM3.655 4.26 1.592 8.043C1.724 8.014 1.86 8 2 8h12c.14 0 .276.014.408.042L12.345 4.26a.5.5 0 0 0-.439-.26H4.094a.5.5 0 0 0-.44.26zM1 10v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1"/>
              </svg>
              Zasoby
            </h5>
          </div>
          <div class="card-body">
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Baza danych:</span>
                <strong><?= number_format($systemStatus['resources']['database_size_mb'] ?? 0, 2) ?> MB</strong>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">→ Uploads:</span>
                <span class="text-muted small"><?= htmlspecialchars($systemStatus['resources']['uploads_size_formatted'] ?? '0 B') ?></span>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">→ Logs:</span>
                <span class="text-muted small"><?= htmlspecialchars($systemStatus['resources']['logs_size_formatted'] ?? '0 B') ?></span>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">→ Backupy:</span>
                <span class="text-muted small"><?= htmlspecialchars($systemStatus['resources']['backups_size_formatted'] ?? '0 B') ?></span>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">→ Cache:</span>
                <span class="text-muted small"><?= htmlspecialchars($systemStatus['resources']['cache_size_formatted'] ?? '0 B') ?></span>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Pliki razem:</span>
                <strong><?= htmlspecialchars($systemStatus['resources']['total_size_formatted'] ?? '0 B') ?></strong>
              </div>
            </div>

            <?php if (!empty($diskQuotaData)): ?>
            <?php
              // Funkcja pomocnicza do formatowania wartości w MB na czytelną jednostkę
              function formatStorageValue(float $mb): string {
                if ($mb >= 1024) {
                  return number_format($mb / 1024, 2) . ' GB';
                }
                return number_format($mb, 0) . ' MB';
              }
            ?>
            <!-- Limity miejsca na dysku -->
            <div class="mt-3 pt-3 border-top">
              <h6 class="mb-3 text-muted small">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-speedometer2 me-1" viewBox="0 0 16 16">
                  <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4M3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707M2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10m9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5m.754-4.246a.39.39 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.39.39 0 0 0-.029-.518z"/><path fill-rule="evenodd" d="M0 10a8 8 0 1 1 16 0A8 8 0 0 1 0 10m8-7a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7"/>
                </svg>
                WYKORZYSTANIE LIMITÓW
              </h6>

              <?php if (isset($diskQuotaData['files'])): ?>
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <small class="text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-folder me-1" viewBox="0 0 16 16">
                      <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z"/>
                    </svg>
                    Pliki
                  </small>
                  <small><strong><?= formatStorageValue($diskQuotaData['files']['current_mb']) ?></strong> / <?= formatStorageValue($diskQuotaData['files']['limit_mb']) ?></small>
                </div>
                <div class="progress">
                  <div class="progress-bar bg-<?= $diskQuotaData['files']['color_class'] ?>" role="progressbar"
                       style="width: <?= $diskQuotaData['files']['percentage'] ?>%"
                       aria-valuenow="<?= $diskQuotaData['files']['percentage'] ?>"
                       aria-valuemin="0"
                       aria-valuemax="100">
                    <?= $diskQuotaData['files']['percentage'] ?>%
                  </div>
                </div>
              </div>
              <?php endif; ?>

              <?php if (isset($diskQuotaData['database'])): ?>
              <div class="mb-2">
                <div class="d-flex justify-content-between mb-1">
                  <small class="text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-database me-1" viewBox="0 0 16 16">
                      <path d="M4.318 2.687C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4c0-.374.356-.875 1.318-1.313M13 5.698V7c0 .374-.356.875-1.318 1.313C10.766 8.729 9.464 9 8 9s-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777A5 5 0 0 0 13 5.698M14 4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16s3.022-.289 4.096-.777C13.125 14.755 14 14.007 14 13zm-1 4.698V10c0 .374-.356.875-1.318 1.313C10.766 11.729 9.464 12 8 12s-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10s3.022-.289 4.096-.777A5 5 0 0 0 13 8.698m0 3V13c0 .374-.356.875-1.318 1.313C10.766 14.729 9.464 15 8 15s-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13s3.022-.289 4.096-.777c.324-.147.633-.323.904-.525"/>
                    </svg>
                    Baza danych
                  </small>
                  <small><strong><?= formatStorageValue($diskQuotaData['database']['current_mb']) ?></strong> / <?= formatStorageValue($diskQuotaData['database']['limit_mb']) ?></small>
                </div>
                <div class="progress">
                  <div class="progress-bar bg-<?= $diskQuotaData['database']['color_class'] ?>" role="progressbar"
                       style="width: <?= $diskQuotaData['database']['percentage'] ?>%"
                       aria-valuenow="<?= $diskQuotaData['database']['percentage'] ?>"
                       aria-valuemin="0"
                       aria-valuemax="100">
                    <?= $diskQuotaData['database']['percentage'] ?>%
                  </div>
                </div>
              </div>
              <small class="text-muted">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-gear me-1" viewBox="0 0 16 16">
                  <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                </svg>
                Ustaw limity w <a href="<?= $basePath ?>/admin/index.php?action=settings">Ustawieniach</a>
              </small>
              <?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <!-- Informacje techniczne -->
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-info">
          <div class="card-header bg-info-subtle">
            <h5 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16" style="margin-right: 8px;">
                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
              </svg>
              System
            </h5>
          </div>
          <div class="card-body">
            <div class="mb-1">
              <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted">RedirectCMS:</span>
                <span>
                  <strong>v<?= htmlspecialchars($versionInfo['current_version'] ?? '?') ?></strong>
                  <?php if (!empty($versionInfo['update_available'])): ?>
                    <?php if (!empty($versionInfo['changelog_url'])): ?>
                    <a href="<?= htmlspecialchars($versionInfo['changelog_url']) ?>" target="_blank" rel="noopener noreferrer"
                       class="badge bg-info-subtle border border-info text-info-emphasis ms-1 text-decoration-none" style="font-size:.72rem;">
                      v<?= htmlspecialchars($versionInfo['latest_version'] ?? '') ?> dostępna
                    </a>
                    <?php else: ?>
                    <span class="badge bg-info-subtle border border-info text-info-emphasis ms-1" style="font-size:.72rem;">
                      v<?= htmlspecialchars($versionInfo['latest_version'] ?? '') ?> dostępna
                    </span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="badge bg-success-subtle border border-success text-success-emphasis ms-1" style="font-size:.72rem;">Aktualna</span>
                  <?php endif; ?>
                </span>
              </div>
            </div>
            <div class="mb-1 pt-2 border-top">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Wersja PHP:</span>
                <strong><?= htmlspecialchars($systemStatus['technical']['php_version'] ?? 'N/A') ?></strong>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between align-items-start">
                <span class="text-muted">MySQL/MariaDB:</span>
                <strong class="text-end" style="max-width: 60%;"><?= htmlspecialchars($systemStatus['technical']['mysql_version'] ?? 'N/A') ?></strong>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Serwer WWW:</span>
                <strong class="text-end" style="max-width: 55%; font-size: 0.85em;"><?= htmlspecialchars(explode(' ', $systemStatus['technical']['web_server'] ?? 'N/A')[0]) ?></strong>
              </div>
            </div>
            <div class="mb-1 pt-2 border-top">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Memory limit:</span>
                <strong><?= htmlspecialchars($systemStatus['technical']['memory_limit'] ?? 'N/A') ?></strong>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Max upload:</span>
                <strong><?= htmlspecialchars($systemStatus['technical']['max_upload_size'] ?? 'N/A') ?></strong>
              </div>
            </div>
            <div class="mb-1 pt-2 border-top">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Cache geo:</span>
                <strong><?= number_format($systemStatus['technical']['geo_cache_records'] ?? 0) ?> rekordów</strong>
              </div>
            </div>
            <?php if (($systemStatus['technical']['trashed_links'] ?? 0) > 0): ?>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Linki w koszu:</span>
                <strong class="text-warning"><?= number_format($systemStatus['technical']['trashed_links']) ?></strong>
              </div>
            </div>
            <?php endif; ?>
            <?php if (($systemStatus['technical']['audit_log_count'] ?? 0) > 0): ?>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Log audytu:</span>
                <strong><?= number_format($systemStatus['technical']['audit_log_count']) ?> wpisów</strong>
              </div>
            </div>
            <?php endif; ?>
            <?php if (($systemStatus['technical']['cron_active_jobs'] ?? 0) > 0): ?>
            <div class="mb-1 pt-2 border-top">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Zadania cron:</span>
                <strong><?= number_format($systemStatus['technical']['cron_active_jobs']) ?> aktywnych</strong>
              </div>
            </div>
            <?php if (!empty($systemStatus['technical']['cron_last_run'])): ?>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">→ Ostatnie:</span>
                <span class="text-muted small"><?= date('d.m H:i', strtotime($systemStatus['technical']['cron_last_run'])) ?></span>
              </div>
            </div>
            <?php endif; ?>
            <?php if (($systemStatus['technical']['cron_errors_24h'] ?? 0) > 0): ?>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted small">→ Błędy (24h):</span>
                <strong class="text-danger small"><?= number_format($systemStatus['technical']['cron_errors_24h']) ?></strong>
              </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <div class="mb-1 pt-2 border-top">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Strefa serwera:</span>
                <strong style="font-size: 0.85em;"><?= htmlspecialchars($systemStatus['technical']['timezone'] ?? 'N/A') ?></strong>
              </div>
            </div>
            <div class="mb-1">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Strefa użytkownika:</span>
                <strong style="font-size: 0.85em;"><?= htmlspecialchars($systemStatus['technical']['user_timezone'] ?? 'Europe/Warsaw') ?></strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Wykres trendu kliknięć -->
    <?php if (!empty($chartData) && !empty($chartData['daily'])): ?>
    <div class="row mb-5">
      <div class="col-12">
        <div class="card shadow-sm border-info">
          <div class="card-header bg-info-subtle d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-graph-up-arrow" viewBox="0 0 16 16" style="margin-right: 8px;">
                <path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5"/>
              </svg>
              Trend kliknięć (ostatnie 30 dni)
            </h5>
            <a href="<?= $basePath ?>/admin/index.php?action=global_stats" class="btn btn-sm btn-light">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bar-chart-line" viewBox="0 0 16 16">
                <path d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1zm1 12h2V2h-2zm-3 0V7H7v7zm-5 0v-3H2v3z"/>
              </svg>
              Zobacz wszystkie wykresy
            </a>
          </div>
          <div class="card-body">
            <canvas id="dailyClicksChart" style="max-height: 400px;"></canvas>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

      <!-- Top Performers -->
      <?php if (!empty($topPerformers)): ?>
      <div class="col-12 mb-4">
        <div class="card shadow-sm border-info widget-card">
          <div class="card-header bg-info-subtle">
            <h5 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-circle" viewBox="0 0 16 16">
                <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0"/>
                <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z"/>
              </svg>
              Top linki (30 dni)
            </h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th style="width: 60px;">Lp.</th>
                    <th style="width: 700px;">Tytuł (alias, gdy brak)</th>
                    <th>Kategoria</th>
                    <th class="text-end" style="width: 100px;">Kliknięcia</th>
                    <th class="text-end" style="width: 100px;">Unikalni</th>
                    <th class="text-end" style="width: 100px;">Ostatnio</th>
                    <th class="text-end" style="width: 80px;">Akcje</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($topPerformers as $i => $link): ?>
                    <tr>
                      <td><span class="badge bg-primary"><?= $i + 1 ?></span></td>
                      <?php 
                        $title = isset($link['page_title']) ? trim((string)$link['page_title']) : '';
                      ?>
                      <td>
                        <?php if ($title !== ''): ?>
                          <span class="fw-semibold"><?= htmlspecialchars($title) ?></span>
                        <?php else: ?>
                          <code><?= htmlspecialchars($link['slug']) ?></code>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!empty($link['affiliate_program_name']) || !empty($link['category_name'])): ?>
                          <div class="d-flex flex-wrap gap-1">
                            <?php if (!empty($link['affiliate_program_name'])): ?>
                              <span class="badge" style="--badge-color: <?= htmlspecialchars($link['affiliate_program_color'] ?? '#4CAF50') ?>;">
                                Program: <?= htmlspecialchars($link['affiliate_program_name']) ?>
                              </span>
                            <?php endif; ?>
                            <?php if (!empty($link['category_name'])): ?>
                              <span class="badge" style="--badge-color: <?= htmlspecialchars($link['category_color'] ?? '#3A3F45') ?>;">
                                Kategoria: <?= htmlspecialchars($link['category_name']) ?>
                              </span>
                            <?php endif; ?>
                          </div>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-end"><strong><?= number_format($link['clicks']) ?></strong></td>
                      <td class="text-end"><strong><?= number_format($link['unique_visitors']) ?></strong></td>
                      <td class="text-end">
                        <?php if (!empty($link['last_click'])): ?>
                          <small><?= date('H:i', strtotime($link['last_click'])) ?></small>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">
                        <a href="<?= $basePath ?>/admin/index.php?action=stats&id=<?= (int)$link['id'] ?>" class="btn btn-sm btn-primary" title="Szczegółowe statystyki">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5v12h-2V2h2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-2zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3z"/>
                          </svg>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Recent Links -->
      <?php if (!empty($recentLinks)): ?>
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-info widget-card">
          <div class="card-header bg-info-subtle">
            <h5 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                <path d="M2.5.5A1.5 1.5 0 0 1 4 0h8a1.5 1.5 0 0 1 1.5 1.5v13A1.5 1.5 0 0 1 12 16H4a1.5 1.5 0 0 1-1.5-1.5v-13zM4 1a.5.5 0 0 0-.5.5v12a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5V1.5a.5.5 0 0 0-.5-.5H4z"/>
              </svg>
              Ostatnie linki
            </h5>
          </div>
          <div class="card-body" style="max-height: 400px; overflow-y: auto;">
            <?php foreach ($recentLinks as $i => $link): ?>
              <div class="mb-2 pb-2 <?= $i < count($recentLinks) - 1 ? 'border-bottom' : '' ?>">
                <?php $title = isset($link['page_title']) ? trim((string)$link['page_title']) : ''; ?>
                <div>
                  <?php if ($title !== ''): ?>
                    <strong><?= htmlspecialchars($title) ?></strong>
                    <div><small class="text-muted"><code><?= htmlspecialchars($link['slug']) ?></code></small></div>
                  <?php else: ?>
                    <strong><code><?= htmlspecialchars($link['slug']) ?></code></strong>
                  <?php endif; ?>
                  <?php if (!empty($link['affiliate_program_name']) || !empty($link['category_name'])): ?>
                    <div class="mt-1 d-flex flex-wrap gap-1">
                      <?php if (!empty($link['affiliate_program_name'])): ?>
                        <span class="badge" style="--badge-color: <?= htmlspecialchars($link['affiliate_program_color'] ?? '#4CAF50') ?>; font-size: 0.75rem;">
                          Program: <?= htmlspecialchars($link['affiliate_program_name']) ?>
                        </span>
                      <?php endif; ?>
                      <?php if (!empty($link['category_name'])): ?>
                        <span class="badge" style="--badge-color: <?= htmlspecialchars($link['category_color'] ?? '#3A3F45') ?>; font-size: 0.75rem;">
                          Kategoria: <?= htmlspecialchars($link['category_name']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </div>
                <small class="text-muted d-block mt-1">
                  <div>Dodany: <?= date('d.m.y H:i', strtotime($link['created_at'])) ?></div>
                  <div>Dzisiaj: <strong><?= number_format($link['today_clicks']) ?></strong> | Razem: <strong><?= number_format($link['total_clicks']) ?></strong></div>
                </small>
                <a href="<?= $basePath ?>/admin/index.php?action=edit&id=<?= (int)$link['id'] ?>" class="btn btn-sm btn-outline-primary mt-2 w-100">
                  Edytuj
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
    
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2>Ostatnie 10 linków</h2>
          <div class="d-flex gap-2">
            <a href="<?= $basePath ?>/admin/index.php?action=links" class="btn btn-outline-secondary">Wszystkie linki</a>
            <a href="<?= $basePath ?>/admin/index.php?action=new" class="btn btn-primary"<?= (($licenseStatus['state'] ?? '') === 'blocked') ? ' data-license-block="' . htmlspecialchars((string)($licenseStatus['message'] ?? 'Dodawanie linków jest zablokowane z powodu nieważnej licencji.'), ENT_QUOTES, 'UTF-8') . '"' : '' ?>>+ Nowy link</a>
          </div>
        </div>
        
        <!-- Wyszukiwarka i filtry -->
        <div class="card mb-3">
          <div class="card-body">
            <form method="get" action="<?= $basePath ?>/admin/index.php" class="row g-3">
              <input type="hidden" name="action" value="dashboard" />
              <div class="col-md-6">
                <label for="search" class="form-label">Szukaj</label>
                <div class="input-group">
                  <span class="input-group-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                  </span>
                  <input type="text" class="form-control" id="search" name="search" placeholder="Szukaj po aliasie, URL, kategorii lub programie..." value="<?= htmlspecialchars($searchQuery ?? '') ?>" />
                </div>
              </div>
              <div class="col-md-3">
                <label for="category_filter" class="form-label">Kategoria</label>
                <select class="form-select" id="category_filter" name="category_filter">
                  <option value="">Wszystkie kategorie</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= (isset($categoryFilter) && (int)$categoryFilter === (int)$cat['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cat['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label for="program_filter" class="form-label">Program afiliacyjny</label>
                <select class="form-select" id="program_filter" name="program_filter">
                  <option value="">Wszystkie programy</option>
                  <?php foreach ($affiliatePrograms as $prog): ?>
                    <option value="<?= (int)$prog['id'] ?>" <?= (isset($programFilter) && (int)$programFilter === (int)$prog['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($prog['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">Filtruj</button>
                <?php if (!empty($searchQuery) || !empty($categoryFilter) || !empty($programFilter)): ?>
                  <a href="<?= $basePath ?>/admin/index.php?action=dashboard" class="btn btn-outline-secondary">Wyczyść filtry</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>

        <?php if (empty($links)): ?>
          <div class="alert bg-info-subtle border border-info text-info-emphasis">
            <strong>Brak linków.</strong> <a href="<?= $basePath ?>/admin/index.php?action=new">Dodaj pierwszy link</a>.
          </div>
        <?php else: ?>
          <?php
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
          ?>

          <!-- Widok desktopowy - tabela (ukryty na mobile) -->
          <div class="table-responsive d-none d-md-block">
            <table class="table table-hover table-fixed">
              <thead class="table-info">
                <tr>
                  <?php
                    // Helper function dla sortowania
                    $currentSort = $sortBy ?? 'created_at';
                    $currentOrder = $sortOrder ?? 'DESC';
                    function sortUrl($column, $currentSort, $currentOrder, $basePath, $searchQuery, $categoryFilter, $programFilter) {
                      $newOrder = ($column === $currentSort && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
                      $params = ['action' => 'dashboard', 'sort' => $column, 'order' => $newOrder];
                      if (!empty($searchQuery)) {
                        $params['search'] = $searchQuery;
                      }
                      if (!empty($categoryFilter)) {
                        $params['category_filter'] = $categoryFilter;
                      }
                      if (!empty($programFilter)) {
                        $params['program_filter'] = $programFilter;
                      }
                      return $basePath . '/admin/index.php?' . http_build_query($params);
                    }
                    function sortIcon($column, $currentSort, $currentOrder) {
                      if ($column !== $currentSort) {
                        return '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16" style="opacity: 0.3;"><path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/></svg>';
                      }
                      if ($currentOrder === 'ASC') {
                        return '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="m7.247 4.86-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z"/></svg>';
                      } else {
                        return '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/></svg>';
                      }
                    }
                  ?>
                  <th style="width: 95px;">
                    <a href="<?= sortUrl('id', $currentSort, $currentOrder, $basePath, $searchQuery ?? '', $categoryFilter ?? null, $programFilter ?? null) ?>" class="text-white text-decoration-none">
                      ID / Obraz <?= sortIcon('id', $currentSort, $currentOrder) ?>
                    </a>
                  </th>
                  <th style="width: 500px;">
                    <a href="<?= sortUrl('slug', $currentSort, $currentOrder, $basePath, $searchQuery ?? '', $categoryFilter ?? null, $programFilter ?? null) ?>" class="text-white text-decoration-none">
                      Tytuł / alias <?= sortIcon('slug', $currentSort, $currentOrder) ?>
                    </a>
                  </th>
                  <th>
                    <a href="<?= sortUrl('target_url', $currentSort, $currentOrder, $basePath, $searchQuery ?? '', $categoryFilter ?? null, $programFilter ?? null) ?>" class="text-white text-decoration-none">
                      Docelowy URL <?= sortIcon('target_url', $currentSort, $currentOrder) ?>
                    </a>
                  </th>
                  <th style="width: 200px;">Akcje</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($links as $l):
                  $fullUrl = $protocol . '://' . $host . $basePath . '/' . htmlspecialchars($l['slug']);
                  $linkStats = $stats[(int)$l['id']] ?? ['total' => 0, 'today' => 0, 'week' => 0];
                  $targetDisplay = isset($l['target_url']) ? (string)$l['target_url'] : '';
                  $domain = $targetDisplay !== '' ? (parse_url($targetDisplay, PHP_URL_HOST) ?? '') : '';
                ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis"><?= (int)$l['id'] ?></span>
                        <?php if (!empty($l['og_image']) || !empty($l['og_image_thumb'])): ?>
                          <?php $thumbSrc = !empty($l['og_image_thumb']) ? $l['og_image_thumb'] : $l['og_image']; ?>
                          <img src="<?= htmlspecialchars($basePath . '/' . $thumbSrc) ?>" alt="Miniatura" class="thumbnail-preview thumbnail-preview-lg" title="<?= htmlspecialchars($l['og_image']) ?>" loading="lazy" width="45" height="45" />
                        <?php endif; ?>
                      </div>
                    </td>
                    <td>
                      <?php $title = isset($l['page_title']) ? trim((string)$l['page_title']) : ''; ?>
                      <?php if ($title !== ''): ?>
                        <div class="fw-semibold"><?= htmlspecialchars($title) ?></div>
                        <div><small class="text-muted"><code><?= htmlspecialchars($l['slug']) ?></code></small></div>
                      <?php else: ?>
                        <code><?= htmlspecialchars($l['slug']) ?></code>
                      <?php endif; ?>
                      <?php if (!empty($l['affiliate_program_name']) || !empty($l['category_name'])): ?>
                        <div class="mt-1 d-flex flex-wrap gap-1">
                          <?php if (!empty($l['affiliate_program_name'])): ?>
                            <span class="badge" style="--badge-color: <?= htmlspecialchars($l['affiliate_program_color'] ?? '#4CAF50') ?>;">
                              Program: <?= htmlspecialchars($l['affiliate_program_name']) ?>
                            </span>
                          <?php endif; ?>
                          <?php if (!empty($l['category_name'])): ?>
                            <span class="badge" style="--badge-color: <?= htmlspecialchars($l['category_color'] ?? '#3A3F45') ?>;">
                              Kategoria: <?= htmlspecialchars($l['category_name']) ?>
                            </span>
                          <?php endif; ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="text-break">
                        <small class="d-block"><?= htmlspecialchars(substr($targetDisplay, 0, 80)) ?></small>
                        <?php if (strlen($targetDisplay) > 80): ?>
                          <span>...</span>
                        <?php endif; ?>
                      </div>
                      <div class="mt-1 d-flex flex-wrap gap-2">
                        <span class="badge bg-secondary-subtle border border-secondary text-body-emphasis" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Liczba kliknięć dzisiaj">D: <?= $linkStats['today'] ?></span>
                        <span class="badge bg-secondary-subtle border border-secondary text-body-emphasis" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Liczba kliknięć w ciągu ostatnich 7 dni">7: <?= $linkStats['week'] ?></span>
                        <span class="badge bg-secondary-subtle border border-secondary text-body-emphasis" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Łączna liczba kliknięć">Ł: <?= $linkStats['total'] ?></span>
                        <span class="badge bg-warning-subtle border border-warning text-warning-emphasis" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Opóźnienie w sekundach">R: <?= (int)$l['delay_seconds'] ?>s</span>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex gap-1 flex-nowrap">
                      <button class="btn btn-sm btn-primary copy-btn" onclick="copyToClipboard('<?= $fullUrl ?>', this)" data-bs-toggle="tooltip" data-bs-title="Kopiuj pełny link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                          <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                        </svg>
                      </button>
                      <a href="<?= $basePath ?>/admin/index.php?action=stats&id=<?= (int)$l['id'] ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-title="Szczegółowe statystyki">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5v12h-2V2h2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-2zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3z"/>
                        </svg>
                      </a>
                      <a href="<?= $basePath ?>/admin/index.php?action=edit&id=<?= (int)$l['id'] ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-title="Edytuj link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                        </svg>
                      </a>
                      <form method="post" action="<?= $basePath ?>/admin/index.php?action=delete&id=<?= (int)$l['id'] ?>" class="d-inline">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Usunąć link?')" data-bs-toggle="tooltip" data-bs-title="Usuń link">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                          </svg>
                        </button>
                      </form>
                      <button class="btn btn-sm btn-success" onclick="showQRModal(<?= (int)$l['id'] ?>, '<?= htmlspecialchars(addslashes($l['slug'])) ?>')" data-bs-toggle="tooltip" data-bs-title="Pokaż kod QR">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-qr-code" viewBox="0 0 16 16">
                          <path d="M2 2h2v2H2z"/>
                          <path d="M6 0v6H0V0zM5 1H1v4h4zM4 12H2v2h2z"/>
                          <path d="M6 10v6H0v-6zm-5 1v4h4v-4zm11-9h2v2h-2z"/>
                          <path d="M10 0v6h6V0zm5 1v4h-4V1zM8 1V0h1v2H8v2H7V1zm0 5V4h1v2zM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8zm0 0v1H2V8H1v1H0V7h3v1zm10 1h-1V7h1zm-1 0h-1v2h2v-1h-1zm-4 0h2v1h-1v1h-1zm2 3v-1h-1v1h-1v1H9v1h3v-2zm0 0h3v1h-2v1h-1zm-4-1v1h1v-2H7v1z"/>
                          <path d="M7 12h1v3h4v1H7zm9 2v2h-3v-1h2v-1z"/>
                        </svg>
                      </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Widok mobilny - karty (ukryty na desktop) -->
          <div class="d-md-none">
            <?php foreach ($links as $l):
              $fullUrl = $protocol . '://' . $host . $basePath . '/' . htmlspecialchars($l['slug']);
              $linkStats = $stats[(int)$l['id']] ?? ['total' => 0, 'today' => 0, 'week' => 0];
              $targetDisplay = isset($l['target_url']) ? (string)$l['target_url'] : '';
              $title = isset($l['page_title']) ? trim((string)$l['page_title']) : '';
            ?>
              <div class="card mb-3 shadow-sm border-info">
                <div class="card-body">
                  <!-- Nagłówek z ID i obrazem -->
                  <div class="d-flex align-items-start gap-3 mb-2">
                    <div class="d-flex flex-column align-items-center gap-2">
                      <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">#<?= (int)$l['id'] ?></span>
                      <?php if (!empty($l['og_image']) || !empty($l['og_image_thumb'])): ?>
                        <?php $thumbSrc = !empty($l['og_image_thumb']) ? $l['og_image_thumb'] : $l['og_image']; ?>
                        <img src="<?= htmlspecialchars($basePath . '/' . $thumbSrc) ?>" alt="Miniatura" class="rounded" style="width: 60px; height: 60px; object-fit: cover;" loading="lazy" />
                      <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                      <?php if ($title !== ''): ?>
                        <h6 class="mb-1"><?= htmlspecialchars($title) ?></h6>
                        <small class="text-muted"><code><?= htmlspecialchars($l['slug']) ?></code></small>
                      <?php else: ?>
                        <h6 class="mb-1"><code><?= htmlspecialchars($l['slug']) ?></code></h6>
                      <?php endif; ?>
                      <?php if (!empty($l['affiliate_program_name']) || !empty($l['category_name'])): ?>
                        <div class="mt-2 d-flex flex-wrap gap-1">
                          <?php if (!empty($l['affiliate_program_name'])): ?>
                            <span class="badge" style="--badge-color: <?= htmlspecialchars($l['affiliate_program_color'] ?? '#4CAF50') ?>;">
                              Program: <?= htmlspecialchars($l['affiliate_program_name']) ?>
                            </span>
                          <?php endif; ?>
                          <?php if (!empty($l['category_name'])): ?>
                            <span class="badge" style="--badge-color: <?= htmlspecialchars($l['category_color'] ?? '#3A3F45') ?>;">
                              Kategoria: <?= htmlspecialchars($l['category_name']) ?>
                            </span>
                          <?php endif; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- URL docelowy -->
                  <div class="mb-2">
                    <small class="text-muted d-block mb-1">Docelowy URL:</small>
                    <small class="text-break"><?= htmlspecialchars(substr($targetDisplay, 0, 60)) ?><?= strlen($targetDisplay) > 60 ? '...' : '' ?></small>
                  </div>

                  <!-- Statystyki -->
                  <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-secondary-subtle border border-secondary text-body-emphasis">Dzisiaj: <?= $linkStats['today'] ?></span>
                    <span class="badge bg-secondary-subtle border border-secondary text-body-emphasis">7 dni: <?= $linkStats['week'] ?></span>
                    <span class="badge bg-secondary-subtle border border-secondary text-body-emphasis">Łącznie: <?= $linkStats['total'] ?></span>
                    <span class="badge bg-info-subtle border border-info text-info-emphasis">Opóźnienie: <?= (int)$l['delay_seconds'] ?>s</span>
                  </div>

                  <!-- Akcje -->
                  <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-primary flex-fill" onclick="copyToClipboard('<?= $fullUrl ?>', this)">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                        <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                      </svg> Kopiuj
                    </button>
                    <a href="<?= $basePath ?>/admin/index.php?action=edit&id=<?= (int)$l['id'] ?>" class="btn btn-sm btn-primary flex-fill">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                      </svg> Edytuj
                    </a>
                    <a href="<?= $basePath ?>/admin/index.php?action=stats&id=<?= (int)$l['id'] ?>" class="btn btn-sm btn-info flex-fill">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5v12h-2V2h2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-2zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3z"/>
                      </svg> Statystyki
                    </a>
                  </div>
                  <div class="d-flex gap-2 mt-2">
                    <button class="btn btn-sm btn-success flex-fill" onclick="showQRModal(<?= (int)$l['id'] ?>, '<?= htmlspecialchars(addslashes($l['slug'])) ?>')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-qr-code" viewBox="0 0 16 16">
                        <path d="M2 2h2v2H2z"/>
                        <path d="M6 0v6H0V0zM5 1H1v4h4zM4 12H2v2h2z"/>
                        <path d="M6 10v6H0v-6zm-5 1v4h4v-4zm11-9h2v2h-2z"/>
                        <path d="M10 0v6h6V0zm5 1v4h-4V1zM8 1V0h1v2H8v2H7V1zm0 5V4h1v2zM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8zm0 0v1H2V8H1v1H0V7h3v1zm10 1h-1V7h1zm-1 0h-1v2h2v-1h-1zm-4 0h2v1h-1v1h-1zm2 3v-1h-1v1h-1v1H9v1h3v-2zm0 0h3v1h-2v1h-1zm-4-1v1h1v-2H7v1z"/>
                        <path d="M7 12h1v3h4v1H7zm9 2v2h-3v-1h2v-1z"/>
                      </svg> QR
                    </button>
                    <form method="post" action="<?= $basePath ?>/admin/index.php?action=delete&id=<?= (int)$l['id'] ?>" class="flex-fill">
                      <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                      <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Usunąć link?')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                          <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                        </svg> Usuń
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
      
      const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
      toast.show();
      
      <?php 
        unset($_SESSION['toast_message'], $_SESSION['toast_type']);
      ?>
    <?php endif; ?>
    
    function copyToClipboard(text, button) {
      navigator.clipboard.writeText(text).then(function() {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>';
        button.classList.remove('btn-primary');
        button.classList.add('btn-success');
        
        setTimeout(function() {
          button.innerHTML = originalHTML;
          button.classList.remove('btn-success');
          button.classList.add('btn-primary');
        }, 2000);
      }).catch(function(err) {
        alert('Nie udało się skopiować linku');
        console.error('Błąd kopiowania:', err);
      });
    }

    // Funkcja do wyświetlenia modalnego QR code
    function showQRModal(linkId, slug) {
      fetch('<?= $basePath ?>/admin/index.php?action=qr_code&id=' + linkId + '&mode=display')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Utwórz modal dynamicznie
            let modalHTML = `
              <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="qrModalLabel">Kod QR: <code>${slug}</code></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                      <img src="${data.qr_url}" alt="QR Code" class="img-fluid" style="max-width: 600px; width: 100%; height: auto; border: 1px solid #D0D7DE; padding: 10px; border-radius: 4px;" />
                      <div class="mt-3">
                        <small class="text-muted d-block mb-2">URL: <code style="word-break: break-all;">${data.url}</code></small>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
                      <button type="button" class="btn btn-primary" onclick="printQR('${slug}')">Drukuj</button>
                      <a href="${data.qr_url}" class="btn btn-success" download="qr-${slug}.png">Pobierz PNG</a>
                    </div>
                  </div>
                </div>
              </div>
            `;
            
            // Usuń stary modal jeśli istnieje
            const oldModal = document.getElementById('qrModal');
            if (oldModal) {
              oldModal.remove();
            }
            
            // Dodaj nowy modal do body
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Pokaż modal
            const newModal = new bootstrap.Modal(document.getElementById('qrModal'));
            newModal.show();
            
            // Usuń modal z DOM po jego zamknięciu
            document.getElementById('qrModal').addEventListener('hidden.bs.modal', function() {
              this.remove();
            });
          } else {
            alert('Błąd: ' + data.error);
          }
        })
        .catch(error => {
          console.error('Błąd:', error);
          alert('Nie udało się załadować QR code');
        });
    }

    // Funkcja do drukowania QR code
    function printQR(slug) {
      const modal = document.getElementById('qrModal');
      const img = modal.querySelector('img');
      const printWindow = window.open('', '', 'height=400,width=600');
      printWindow.document.write('<img src="' + img.src + '" style="max-width: 100%;" />');
      printWindow.document.write('<p style="text-align: center; margin-top: 20px;">QR Code: ' + slug + '</p>');
      printWindow.document.close();
      printWindow.print();
    }

    // Inicjalizacja tooltipów Bootstrap 5
    const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    // Wykresy Chart.js
    <?php if (!empty($chartData)): ?>
    // 1. Wykres trendu dziennego (liniowy)
    <?php if (!empty($chartData['daily'])): ?>
    const dailyCtx = document.getElementById('dailyClicksChart');
    if (dailyCtx) {
      const dailyLabels = <?= json_encode(array_column($chartData['daily'], 'date')) ?>;
      const dailyCounts = <?= json_encode(array_column($chartData['daily'], 'count')) ?>;
      const dailyUnique = <?= json_encode(array_column($chartData['daily'], 'unique_count')) ?>;

      new Chart(dailyCtx, {
        type: 'line',
        data: {
          labels: dailyLabels,
          datasets: [
            {
              label: 'Wszystkie kliknięcia',
              data: dailyCounts,
              borderColor: 'rgb(13, 110, 253)',
              backgroundColor: 'rgba(13, 110, 253, 0.1)',
              tension: 0.3,
              fill: true
            },
            {
              label: 'Unikalni użytkownicy',
              data: dailyUnique,
              borderColor: 'rgb(25, 135, 84)',
              backgroundColor: 'rgba(25, 135, 84, 0.1)',
              tension: 0.3,
              fill: true
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              display: true,
              position: 'top'
            },
            tooltip: {
              mode: 'index',
              intersect: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            }
          }
        }
      });
    }
    <?php endif; ?>

    <?php endif; ?>
  </script>
</body>
</html>
