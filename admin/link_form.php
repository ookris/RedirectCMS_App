<?php
  $pageTitle = ($mode === 'create' ? 'Nowy link' : 'Edycja linku') . ' — RedirectCMS';
  $extraHead = '<link rel="stylesheet" href="https://unpkg.com/trix@2.1.6/dist/trix.css">';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <!-- Zawartość główna -->
  <div class="container py-4">
    <!-- Toast Container -->
    <div class="position-fixed bottom-0 end-0 p-3 z-toast">
      <div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body" id="toastMessage"></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>
    
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10">
        <div class="card border-info shadow-sm">
          <div class="card-header bg-info-subtle d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><?= $mode === 'create' ? 'Nowy link' : 'Edycja linku' ?></h2>
            <?php if ($mode === 'edit'):
              $linkStatus = $link['status'] ?? 'published';
              $statusBadge = '';
              if ($linkStatus === 'trashed') {
                $statusBadge = '<span class="badge bg-danger fs-6">W koszu</span>';
              } elseif ($linkStatus === 'draft') {
                $statusBadge = '<span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis fs-6">Szkic</span>';
              } else {
                $publishAtTs = !empty($link['publish_at']) ? strtotime((string)$link['publish_at']) : null;
                $expiresAtTs = !empty($link['expires_at']) ? strtotime((string)$link['expires_at']) : null;
                $now = time();
                if ($publishAtTs && $publishAtTs > $now) {
                  $statusBadge = '<span class="badge bg-warning-subtle border border-warning text-warning-emphasis text-dark fs-6">Zaplanowany</span>';
                } elseif ($expiresAtTs && $expiresAtTs <= $now) {
                  $statusBadge = '<span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis fs-6">Wygasły</span>';
                } else {
                  $statusBadge = '<span class="badge bg-success-subtle border border-success text-success-emphasis fs-6">Opublikowany</span>';
                }
              }
              echo $statusBadge;
            endif; ?>
          </div>
          <div class="card-body">
            <?php if (!empty($error)): ?>
              <div class="alert bg-danger-subtle border border-danger text-danger-emphasis alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
              </div>
            <?php endif; ?>

            <form method="post" action="<?= $mode === 'create' ? $basePath . '/admin/index.php?action=new' : $basePath . '/admin/index.php?action=edit&id=' . (int)$link['id'] ?>" enctype="multipart/form-data">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
              <?php 
                $customFields = $customFieldDefinitions ?? [];
                $customFieldValues = $customFieldValues ?? [];
              ?>

              <?php if ($mode === 'edit'):
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                $host = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost', ENT_QUOTES, 'UTF-8');
                $redirectUrl = $protocol . '://' . $host . $basePath . '/' . htmlspecialchars($link['slug']);
                $blogUrl = $protocol . '://' . $host . $basePath . '/blog/' . htmlspecialchars($link['slug']);
              ?>
                <div class="card border-success mb-4">
                  <div class="card-header bg-success-subtle d-flex justify-content-between align-items-center">
                    <span>Linki</span>
                    <button type="button" class="btn btn-sm btn-success" id="slugEditToggle">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                      </svg>
                      Edytuj
                    </button>
                  </div>
                  <div class="card-body">
                    <!-- Edycja aliasu (ukryta domyślnie) -->
                    <div id="slugEditForm" class="mb-3" style="display: none;">
                      <label class="form-label fw-semibold">Zmień alias</label>
                      <div class="input-group input-group-sm">
                        <input type="text" class="form-control form-control-sm" id="slugEditInput" value="<?= htmlspecialchars($link['slug']) ?>" maxlength="190" />
                        <button type="button" class="btn btn-sm btn-success" id="slugSaveBtn">Zapisz</button>
                        <button type="button" class="btn btn-sm btn-outline-dark" id="slugCancelBtn">
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-backspace-reverse-fill" viewBox="0 0 16 16">
                            <path d="M0 3a2 2 0 0 1 2-2h7.08a2 2 0 0 1 1.519.698l4.843 5.651a1 1 0 0 1 0 1.302L10.6 14.3a2 2 0 0 1-1.52.7H2a2 2 0 0 1-2-2zm9.854 2.854a.5.5 0 0 0-.708-.708L7 7.293 4.854 5.146a.5.5 0 1 0-.708.708L6.293 8l-2.147 2.146a.5.5 0 0 0 .708.708L7 8.707l2.146 2.147a.5.5 0 0 0 .708-.708L7.707 8z"/>
                          </svg>
                          Anuluj
                        </button>
                      </div>
                      <div id="slugEditError" class="text-danger small mt-1" style="display: none;"></div>
                      <div class="text-warning small mt-1">Uwaga: zmiana aliasu spowoduje zmianę adresów URL. Istniejące kody QR, linki zewnętrzne i statystyki mogą przestać działać poprawnie.</div>
                    </div>

                    <!-- Link z przekierowaniem -->
                    <div class="mb-3">
                      <h5 class="card-title">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
                        <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
                      </svg>
                      Link z przekierowaniem
                      </h5>
                      <div class="input-group input-group-sm">
                        <input class="form-control form-control-sm border border-success" type="text" id="redirectUrlInput" value="<?= $redirectUrl ?>" readonly>
                        <button type="button" class="btn btn-sm btn-success copy-link-btn" data-target="redirectUrlInput" data-bs-toggle="tooltip" data-bs-title="Kopiuj do schowka">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                            <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                          </svg>
                        </button>
                      </div>
                    </div>

                    <!-- Link do wpisu na blogu -->
                    <div class="mb-0">
                      <h5 class="card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-heading" viewBox="0 0 16 16">
                          <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                          <path d="M3 8.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m0-5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5z"/>
                        </svg>
                      Link do wpisu na blogu</h5>
                      <div class="input-group input-group-sm">
                        <input class="form-control form-control-sm border border-success" type="text" id="blogUrlInput" value="<?= $blogUrl ?>" readonly>
                        <button type="button" class="btn btn-sm btn-success copy-link-btn" data-target="blogUrlInput" data-bs-toggle="tooltip" data-bs-title="Kopiuj do schowka">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                            <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>

              <div class="card mb-4" style="border-secondary">
                <div class="card-header bg-light">
                  <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-link" viewBox="0 0 16 16">
                      <path d="M6.354 5.5H4a3 3 0 0 0 0 6h3a3 3 0 0 0 2.83-4H9q-.13 0-.25.031A2 2 0 0 1 7 10.5H4a2 2 0 1 1 0-4h1.535c.218-.376.495-.714.82-1z"/>
                      <path d="M9 5.5a3 3 0 0 0-2.83 4h1.098A2 2 0 0 1 9 6.5h3a2 2 0 1 1 0 4h-1.535a4 4 0 0 1-.82 1H12a3 3 0 1 0 0-6z"/>
                    </svg>
                    Parametry linka
                  </h4>
                </div>
                <div class="card-body">
                  <?php if ($mode === 'create'): ?>
                    <div class="mb-3">
                      <label for="slug" class="form-label">Alias</label>
                      <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($link['slug']) ?>" placeholder="np. promo-2025" maxlength="190" />
                      <small class="form-text text-muted">Max 190 znaków. Dozwolone: litery, cyfry, myślnik, podkreślenie.</small>
                    </div>
                    <div class="mb-3">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="use_random" id="use_random">
                        <label class="form-check-label" for="use_random">
                          Użyj losowego aliasu
                          <span class="text-muted small">(długość: <?= (int)$random_length ?>)</span>
                        </label>
                      </div>
                    </div>
                  <?php endif; ?>

                  <div class="mb-3">
                    <label for="target_url" class="form-label">Docelowy URL</label>
                    <input type="url" class="form-control" id="target_url" name="target_url" value="<?= htmlspecialchars($link['target_url']) ?>" required />
                  </div>

                  <div class="mb-3">
                    <label for="delay_seconds" class="form-label">Opóźnienie (sekundy)</label>
                    <input type="number" class="form-control" id="delay_seconds" min="0" name="delay_seconds" value="<?= (int)$link['delay_seconds'] ?>" required />
                  </div>
                </div>
              </div>

                <?php
                  $publishValue = '';
                  if (!empty($link['publish_at'])) {
                    $ts = strtotime((string)$link['publish_at']);
                    if ($ts !== false) {
                      $publishValue = date('Y-m-d\\TH:i', $ts);
                    }
                  }
                  $expiresValue = '';
                  if (!empty($link['expires_at'])) {
                    $ts = strtotime((string)$link['expires_at']);
                    if ($ts !== false) {
                      $expiresValue = date('Y-m-d\\TH:i', $ts);
                    }
                  }
                ?>

              <div class="card mb-4" style="border-secondary">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                  <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-calendar-plus" viewBox="0 0 16 16">
                      <path d="M8 7a.5.5 0 0 1 .5.5V9H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7"/>
                      <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                    </svg>
                    Publikacja (opcjonalnie)
                  </h4>
                  <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#publikacjaSection" aria-expanded="false">
                    Rozwiń
                  </button>
                </div>
                <div class="collapse" id="publikacjaSection">
                <div class="card-body">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="publish_at" class="form-label">Data startu (opcjonalnie)</label>
                    <input type="datetime-local" class="form-control" id="publish_at" name="publish_at" value="<?= htmlspecialchars($publishValue) ?>" />
                    <small class="form-text text-muted">Pozostaw puste, aby publikować od razu.</small>
                  </div>
                  <div class="col-md-6">
                    <label for="expires_at" class="form-label">Data wygaśnięcia (opcjonalnie)</label>
                    <input type="datetime-local" class="form-control" id="expires_at" name="expires_at" value="<?= htmlspecialchars($expiresValue) ?>" />
                    <small class="form-text text-muted">Po tej dacie link przestanie działać.</small>
                  </div>
                </div>

                <!-- URL zapasowy po wygaśnięciu -->
                <div class="row mt-3" id="fallback-url-row" style="<?= empty($expiresValue) ? 'display:none;' : '' ?>">
                  <div class="col-12">
                    <label for="fallback_url" class="form-label">URL zapasowy po wygaśnięciu (opcjonalnie)</label>
                    <input type="url" class="form-control" id="fallback_url" name="fallback_url" value="<?= htmlspecialchars($link['fallback_url'] ?? '') ?>" placeholder="https://example.com/alternatywna-strona" />
                    <small class="form-text text-muted">Po wygaśnięciu linku użytkownik zostanie przekierowany na ten adres zamiast widzieć stronę błędu.</small>
                  </div>
                </div>
                <script>
                  document.getElementById('expires_at').addEventListener('change', function() {
                    document.getElementById('fallback-url-row').style.display = this.value ? '' : 'none';
                  });
                </script>
                </div>
                </div>
              </div>

              <div class="card mb-4" style="border-secondary">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                  <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-shield-lock" viewBox="0 0 16 16">
                      <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
                      <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415"/>
                    </svg>
                    Ochrona hasłem (opcjonalnie)
                  </h4>
                  <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#ochronaHaslemSection" aria-expanded="false">
                    Rozwiń
                  </button>
                </div>
                <div class="collapse" id="ochronaHaslemSection">
                <div class="card-body">

              <?php if ($mode === 'edit' && !empty($link['password_hash'])): ?>
                <div class="alert bg-info-subtle border border-info text-info-emphasis d-flex align-items-center justify-content-between">
                  <span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                      <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/>
                    </svg>
                    Ten link jest chroniony hasłem
                  </span>
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="remove_password" id="remove_password" value="1" />
                    <label class="form-check-label" for="remove_password">Usuń hasło</label>
                  </div>
                </div>
              <?php endif; ?>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label for="password" class="form-label">Hasło dostępu<?= $mode === 'edit' ? ' (zostaw puste aby nie zmieniać)' : '' ?></label>
                  <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" autocomplete="new-password" />
                  <small class="form-text text-muted">Jeśli ustawione, użytkownicy muszą podać hasło przed przekierowaniem</small>
                </div>
                <div class="col-md-6">
                  <label for="password_hint" class="form-label">Podpowiedź (opcjonalnie)</label>
                  <input type="text" class="form-control" id="password_hint" name="password_hint" value="<?= htmlspecialchars($link['password_hint'] ?? '') ?>" placeholder="np. Nazwa produktu" maxlength="255" />
                  <small class="form-text text-muted">Wyświetlana użytkownikowi przy formularzu hasła</small>
                </div>
              </div>
                </div>
                </div>
              </div>

              <div class="card mb-4" style="border-secondary">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                  <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16">
                      <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/>
                      <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/>
                    </svg>
                    UTM Builder (opcjonalnie)
                  </h4>
                  <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#utmBuilderSection" aria-expanded="false">
                    Rozwiń
                  </button>
                </div>
                <div class="collapse" id="utmBuilderSection">
                <div class="card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label for="utm_source" class="form-label">utm_source</label>
                      <input type="text" class="form-control form-control-sm utm-field" id="utm_source" placeholder="np. facebook, newsletter, google" />
                      <small class="form-text text-muted">Źródło ruchu</small>
                    </div>
                    <div class="col-md-6">
                      <label for="utm_medium" class="form-label">utm_medium</label>
                      <input type="text" class="form-control form-control-sm utm-field" id="utm_medium" placeholder="np. cpc, email, social, banner" />
                      <small class="form-text text-muted">Medium kampanii</small>
                    </div>
                    <div class="col-md-6">
                      <label for="utm_campaign" class="form-label">utm_campaign</label>
                      <input type="text" class="form-control form-control-sm utm-field" id="utm_campaign" placeholder="np. spring_sale, black_friday" />
                      <small class="form-text text-muted">Nazwa kampanii</small>
                    </div>
                    <div class="col-md-6">
                      <label for="utm_term" class="form-label">utm_term</label>
                      <input type="text" class="form-control form-control-sm utm-field" id="utm_term" placeholder="np. buty+sportowe" />
                      <small class="form-text text-muted">Słowa kluczowe (opcjonalnie)</small>
                    </div>
                    <div class="col-md-6">
                      <label for="utm_content" class="form-label">utm_content</label>
                      <input type="text" class="form-control form-control-sm utm-field" id="utm_content" placeholder="np. header_link, sidebar_banner" />
                      <small class="form-text text-muted">Wariant reklamy (opcjonalnie)</small>
                    </div>
                  </div>
                  <div id="utmPreview" class="mt-3" style="display:none;">
                    <label class="form-label fw-semibold">Podgląd docelowego URL z parametrami UTM:</label>
                    <div class="input-group input-group-sm">
                      <input type="text" class="form-control form-control-sm bg-white" id="utmPreviewUrl" readonly />
                      <button type="button" class="btn btn-sm btn-success" id="utmCopyBtn" title="Kopiuj URL z UTM">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                          <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                        </svg>
                      </button>
                      <button type="button" class="btn btn-sm btn-primary" id="utmApplyBtn" title="Zastosuj UTM do docelowego URL">Zastosuj do URL</button>
                    </div>
                    <small class="form-text text-muted">Kliknij "Zastosuj do URL" aby dodać parametry UTM do docelowego adresu URL.</small>
                  </div>
                </div>
                </div>
              </div>

              <div class="card mb-4" style="border-secondary">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                  <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-journal-text" viewBox="0 0 16 16">
                      <path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                      <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>
                      <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/>
                    </svg>
                    Notatki wewnętrzne (opcjonalnie)
                  </h4>
                  <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#notatkiSection" aria-expanded="false">
                    Rozwiń
                  </button>
                </div>
                <div class="collapse" id="notatkiSection">
                  <div class="card-body">
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Wewnętrzne notatki o tym linku, np. cel kampanii, uwagi dla zespołu..."><?= htmlspecialchars($link['notes'] ?? '') ?></textarea>
                    <small class="form-text text-muted">Te notatki są widoczne tylko w panelu administracyjnym i nie są wyświetlane publicznie</small>
                  </div>
                </div>
              </div>

              <div class="card mb-4" style="border-secondary">
                <div class="card-header bg-light">
                  <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-bookmark-plus" viewBox="0 0 16 16">
                      <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1z"/>
                      <path d="M8 4a.5.5 0 0 1 .5.5V6H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V7H6a.5.5 0 0 1 0-1h1.5V4.5A.5.5 0 0 1 8 4"/>
                    </svg>
                    Opis i meta tagi dla mediów społecznościowych
                  </h4>
                </div>
                <div class="card-body">

              <div class="mb-3">
                <label for="page_title" class="form-label">Tytuł strony (opcjonalnie)</label>
                <input type="text" class="form-control" id="page_title" name="page_title" value="<?= htmlspecialchars($link['page_title'] ?? '') ?>" placeholder="np. Super promocja 2025" maxlength="255" />
                <small class="form-text text-muted">Zostanie wyświetlony jako tytuł przy udostępnianiu na Facebooku/LinkedIn</small>
              </div>

              <div class="mb-3">
                <label for="page_description" class="form-label">Opis strony (opcjonalnie)</label>
                <input type="hidden" id="page_description" name="page_description" value="<?= htmlspecialchars($link['page_description'] ?? '') ?>">
                <trix-editor id="page_description_editor" input="page_description" placeholder="np. Skorzystaj z wyjątkowej oferty..."></trix-editor>
                <small class="form-text text-muted d-block mt-2">Możesz formatować tekst (bold, italic, itp.). Zostanie wyświetlony przy udostępnianiu</small>
              </div>

              <div class="mb-3">
                <?php
                  $existingImages = isset($link['images']) && is_array($link['images']) ? $link['images'] : [];
                  $hasPrimaryImage = false;
                  foreach ($existingImages as $image) {
                      if (!empty($image['is_primary'])) {
                          $hasPrimaryImage = true;
                          break;
                      }
                  }
                ?>
                <h4 class="mb-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-images" viewBox="0 0 16 16">
                    <path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                    <path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z"/>
                  </svg>
                  Galeria obrazów</h4>
                <p class="text-muted">Dodaj wiele zdjęć produktu. Jedno z nich oznacz jako główne (og:image).</p>

                <?php if (!empty($existingImages)): ?>
                  <div class="row g-3 mb-3">
                    <?php foreach ($existingImages as $image): ?>
                      <div class="col-md-4">
                        <div class="card h-100 shadow-sm">
                          <?php $thumbSrc = !empty($image['thumb_path']) ? $image['thumb_path'] : $image['path']; ?>
                          <img src="<?= htmlspecialchars($basePath . '/' . $thumbSrc) ?>" alt="Podgląd" class="card-img-top" class="card-img-fixed" loading="lazy" />
                          <div class="card-body">
                            <div class="form-check mb-2">
                              <input class="form-check-input" type="radio" name="primary_image" id="primary_<?= (int)$image['id'] ?>" value="<?= (int)$image['id'] ?>" <?= !empty($image['is_primary']) ? 'checked' : '' ?> />
                              <label class="form-check-label" for="primary_<?= (int)$image['id'] ?>">Ustaw jako główne</label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" name="remove_images[]" id="remove_<?= (int)$image['id'] ?>" value="<?= (int)$image['id'] ?>" />
                              <label class="form-check-label" for="remove_<?= (int)$image['id'] ?>">Usuń to zdjęcie</label>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <div class="form-check mb-3">
                  <input class="form-check-input" type="radio" name="primary_image" id="primary_new_upload" value="new" <?= (!$hasPrimaryImage && empty($existingImages)) ? 'checked' : '' ?> />
                  <label class="form-check-label" for="primary_new_upload">Ustaw pierwsze nowe zdjęcie jako główne</label>
                  <small class="form-text text-muted d-block">Wybierz tę opcję, jeśli chcesz, aby pierwszy nowo wgrany plik został zapisany jako og:image.</small>
                </div>

                <div class="mb-3" id="newImagesWrapper" style="display: none;">
                  <h6 class="mb-2">Nowo przesłane (zostaną zapisane po zapisaniu linku)</h6>
                  <div class="row g-3" id="newImagesContainer"></div>
                </div>

                <div id="preuploaded-inputs"></div>

                <div class="mb-3">
                  <label for="gallery_images" class="form-label">Dodaj nowe zdjęcia</label>
                  <input type="file" class="form-control" id="gallery_images" name="gallery_images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple />
                  <small class="form-text text-muted">Obsługiwane: JPG, PNG, GIF, WebP. Max 5MB/szt. Wybrane pliki wysyłają się automatycznie; możesz je usunąć lub ustawić jako główne przed zapisaniem formularza.</small>
                </div>
              </div>
                </div>
              </div>

              <div class="card mb-4" style="border-secondary">
                <div class="card-header bg-light">
                  <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                      <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                    </svg>
                    Kategorie i tagi
                  </h4>
                </div>
                <div class="card-body">

              <div class="mb-3">
                <label for="category_id" class="form-label">Kategoria (opcjonalnie)</label>
                <select class="form-select" id="category_id" name="category_id">
                  <option value="">-- Brak kategorii --</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= (isset($link['category_id']) && (int)$link['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cat['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">
                  <a href="<?= $basePath ?>/admin/index.php?action=categories" target="_blank">Zarządzaj kategoriami</a>
                </small>
              </div>

              <div class="mb-3">
                <label for="affiliate_program_id" class="form-label">Program afiliacyjny (opcjonalnie)</label>
                <select class="form-select" id="affiliate_program_id" name="affiliate_program_id">
                  <option value="">-- Brak programu --</option>
                  <?php foreach ($affiliatePrograms as $program): ?>
                    <option value="<?= (int)$program['id'] ?>" <?= (isset($link['affiliate_program_id']) && (int)$link['affiliate_program_id'] === (int)$program['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($program['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">
                  <a href="<?= $basePath ?>/admin/index.php?action=affiliate_programs" target="_blank">Zarządzaj programami</a>
                </small>
              </div>

              <div class="mb-3">
                <label for="tags" class="form-label">Tagi (opcjonalnie)</label>
                <div class="position-relative">
                  <input type="text" class="form-control" id="tags" name="tags" 
                         value="<?= isset($link['tags']) && is_array($link['tags']) ? htmlspecialchars(implode(', ', array_column($link['tags'], 'name'))) : '' ?>" 
                         placeholder="np. promocja, zima, rabat"
                         autocomplete="off" />
                  <div id="tags-suggestions" class="list-group position-absolute w-100" class="suggestions-dropdown"></div>
                </div>
                <small class="form-text text-muted">
                  Oddziel przecinkami. Tagi zostaną utworzone automatycznie jeśli nie istnieją.
                  <a href="<?= $basePath ?>/admin/index.php?action=tags" target="_blank">Zarządzaj tagami</a>
                </small>
                
                <?php if (isset($top_tags) && !empty($top_tags)): ?>
                  <div class="mt-3">
                    <small class="text-muted">Top 50 najpopularniejszych tagów:</small>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                      <?php foreach ($top_tags as $tag): ?>
                        <span class="badge bg-primary tag-suggestion" data-tag="<?= htmlspecialchars($tag['name'], ENT_QUOTES) ?>">
                          + <?= htmlspecialchars($tag['name']) ?> <span class="opacity-75">(<?= (int)$tag['usage_count'] ?>)</span>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
                </div>
              </div>

              <?php if (!empty($customFields)): ?>
              <div class="card mb-4" style="border-secondary">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                  <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-square" viewBox="0 0 16 16">
                      <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                      <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                    </svg>
                    Dodatkowe pola (opcjonalnie)
                  </h4>
                  <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#dodatkowePolaSection" aria-expanded="false">
                    Rozwiń
                  </button>
                </div>
                <div class="collapse" id="dodatkowePolaSection">
                <div class="card-body">
                  <p class="text-muted">Wartości widoczne w szablonach (np. tryb blog) i eksportach.</p>
                  <div class="row g-3">
                    <?php foreach ($customFields as $field):
                      $fieldKey = (string)($field['key'] ?? '');
                      if ($fieldKey === '') { continue; }
                      $fieldLabel = (string)($field['label'] ?? ucfirst($fieldKey));
                      $fieldType = (string)($field['type'] ?? 'text');
                      $fieldPlaceholder = (string)($field['placeholder'] ?? '');
                      $fieldStep = (string)($field['step'] ?? '');
                      $currentValue = (string)($customFieldValues[$fieldKey] ?? '');
                      $stepAttr = ($fieldType === 'number' && $fieldStep !== '') ? 'step="' . htmlspecialchars($fieldStep) . '"' : '';
                    ?>
                      <div class="col-md-6">
                        <label class="form-label" for="cf-<?= htmlspecialchars($fieldKey) ?>"><?= htmlspecialchars($fieldLabel) ?></label>
                        <input
                          type="<?= $fieldType === 'number' ? 'number' : 'text' ?>"
                          class="form-control"
                          id="cf-<?= htmlspecialchars($fieldKey) ?>"
                          name="custom_fields[<?= htmlspecialchars($fieldKey) ?>]"
                          value="<?= htmlspecialchars($currentValue) ?>"
                          placeholder="<?= htmlspecialchars($fieldPlaceholder) ?>"
                          <?= $stepAttr ?>
                        />
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                </div>
              </div>
              <?php endif; ?>

              <?php if ($mode === 'edit'): ?>
              <div class="card mb-4" style="border-secondary">
                <div class="card-header bg-light">
                  <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-qr-code" viewBox="0 0 16 16">
                    <path d="M2 2h2v2H2z"/>
                    <path d="M6 0v6H0V0zM5 1H1v4h4zM4 12H2v2h2z"/>
                    <path d="M6 10v6H0v-6zm-5 1v4h4v-4zm11-9h2v2h-2z"/>
                    <path d="M10 0v6h6V0zm5 1v4h-4V1zM8 1V0h1v2H8v2H7V1zm0 5V4h1v2zM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8zm0 0v1H2V8H1v1H0V7h3v1zm10 1h-1V7h1zm-1 0h-1v2h2v-1h-1zm-4 0h2v1h-1v1h-1zm2 3v-1h-1v1h-1v1H9v1h3v-2zm0 0h3v1h-2v1h-1zm-4-1v1h1v-2H7v1z"/>
                    <path d="M7 12h1v3h4v1H7zm9 2v2h-3v-1h2v-1z"/>
                    </svg>
                    Kod QR
                  </h4>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted mb-3">Kod QR dla tego linku:</p>
                    <div id="qr-container" class="mb-3">
                      <button class="btn btn-outline-primary btn-sm" type="button" id="qrGenerateBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-qr-code" viewBox="0 0 16 16">
                          <path d="M2 2h2v2H2z"/>
                          <path d="M6 0v6H0V0zM5 1H1v4h4zM4 12H2v2h2z"/>
                          <path d="M6 10v6H0v-6zm-5 1v4h4v-4zm11-9h2v2h-2z"/>
                          <path d="M10 0v6h6V0zm5 1v4h-4V1zM8 1V0h1v2H8v2H7V1zm0 5V4h1v2zM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8zm0 0v1H2V8H1v1H0V7h3v1zm10 1h-1V7h1zm-1 0h-1v2h2v-1h-1zm-4 0h2v1h-1v1h-1zm2 3v-1h-1v1h-1v1H9v1h3v-2zm0 0h3v1h-2v1h-1zm-4-1v1h1v-2H7v1z"/>
                          <path d="M7 12h1v3h4v1H7zm9 2v2h-3v-1h2v-1z"/>
                        </svg>
                        Pokaż kod QR
                      </button>
                    </div>
                    <div id="qr-preview" style="display: none; text-align: center;">
                      <img id="qr-image" src="" alt="QR Code" style="max-width: 300px; border: 2px solid #D0D7DE; padding: 10px; border-radius: 6px;" />
                      <div class="mt-3 d-flex justify-content-center gap-2">
                        <a id="qr-download" href="#" class="btn btn-sm btn-success" download>
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 4px;">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                          </svg>
                          Pobierz
                        </a>
                        <button type="button" class="btn btn-sm btn-primary" id="qrPrintBtn">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 4px;">
                            <path d="M2.5 1A1.5 1.5 0 0 0 1 2.5v3h14v-3A1.5 1.5 0 0 0 13.5 1h-11zM1 5.5v6A1.5 1.5 0 0 0 2.5 13h11a1.5 1.5 0 0 0 1.5-1.5v-6h1v6A2.5 2.5 0 0 1 13.5 14h-11A2.5 2.5 0 0 1 0 11.5v-6h1z"/>
                            <path d="M4 8.5a.5.5 0 0 1 1 0v3a.5.5 0 0 1-1 0v-3zm2 0a.5.5 0 0 1 1 0v3a.5.5 0 0 1-1 0v-3zm2 0a.5.5 0 0 1 1 0v3a.5.5 0 0 1-1 0v-3z"/>
                          </svg>
                          Drukuj
                        </button>
                      </div>
                    </div>
                </div>
              </div>
              <?php endif; ?>

              <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="<?= $basePath ?>/admin/index.php?action=links" class="btn btn-secondary">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-backspace-reverse-fill" viewBox="0 0 16 16">
                    <path d="M0 3a2 2 0 0 1 2-2h7.08a2 2 0 0 1 1.519.698l4.843 5.651a1 1 0 0 1 0 1.302L10.6 14.3a2 2 0 0 1-1.52.7H2a2 2 0 0 1-2-2zm9.854 2.854a.5.5 0 0 0-.708-.708L7 7.293 4.854 5.146a.5.5 0 1 0-.708.708L6.293 8l-2.147 2.146a.5.5 0 0 0 .708.708L7 8.707l2.146 2.147a.5.5 0 0 0 .708-.708L7.707 8z"/>
                  </svg>
                  Anuluj
                </a>
                <?php
                  $currentStatus = $link['status'] ?? 'published';
                  $isTrashed = ($currentStatus === 'trashed');
                  $isDraft = ($currentStatus === 'draft');
                ?>
                <?php if ($mode === 'create'): ?>
                  <!-- Nowy link: mozliwosc zapisania jako szkic lub opublikowania -->
                  <button type="submit" name="save_as_draft" value="1" class="btn btn-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z"/>
                      <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                    </svg>
                    Zapisz jako szkic
                  </button>
                  <button type="submit" class="btn btn-success">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16">
                    <path d="M11 2H9v3h2z"/>
                    <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/>
                  </svg>
                    Opublikuj
                  </button>
                <?php elseif ($isDraft): ?>
                  <!-- Szkic: mozliwosc zapisania jako szkic lub opublikowania -->
                  <button type="submit" name="save_as_draft" value="1" class="btn btn-warning">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                    <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z"/>
                    <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                  </svg>
                    Zapisz szkic
                  </button>
                  <button type="submit" class="btn btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16">
                      <path d="M11 2H9v3h2z"/>
                      <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/>
                    </svg>
                    Opublikuj
                  </button>
                <?php elseif ($isTrashed): ?>
                  <!-- Link w koszu: zapisz i przywróć -->
                  <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16">
                      <path d="M11 2H9v3h2z"/>
                      <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/>
                    </svg>
                    Zapisz zmiany
                  </button>
                <?php else: ?>
                  <!-- Opublikowany link: standardowy zapis -->
                  <button type="submit" class="btn btn-success">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16">
                    <path d="M11 2H9v3h2z"/>
                    <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/>
                  </svg>
                    Zapisz zmiany
                  </button>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <script src="https://unpkg.com/trix@2.1.6/dist/trix.umd.min.js"></script>
  <script>
    const basePath = '<?= $basePath ?>';
    const csrfToken = document.querySelector('input[name="csrf"]').value;

    // Toggle button text for collapsible sections
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function(btn) {
      const targetId = btn.getAttribute('data-bs-target');
      const target = document.querySelector(targetId);
      if (target) {
        target.addEventListener('shown.bs.collapse', function() {
          btn.textContent = 'Zwiń';
        });
        target.addEventListener('hidden.bs.collapse', function() {
          btn.textContent = 'Rozwiń';
        });
      }
    });
    const preuploadedState = [];
    const newImagesContainer = document.getElementById('newImagesContainer');
    const newImagesWrapper = document.getElementById('newImagesWrapper');
    const preuploadedInputs = document.getElementById('preuploaded-inputs');
    const galleryInput = document.getElementById('gallery_images');
    let searchTimeout = null;

    function createClientKey() {
      return 'tmp-' + Date.now() + '-' + Math.random().toString(16).slice(2, 8);
    }

    function ensurePrimarySelection() {
      const selected = document.querySelector('input[name="primary_image"]:checked');
      if (selected) {
        return;
      }
      const first = document.querySelector('input[name="primary_image"]');
      if (!first) {
        return;
      }
      first.checked = true;
    }

    function addPreuploadedInput(meta) {
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'preuploaded_images[]';
      hidden.dataset.key = meta.key;
      hidden.value = JSON.stringify(meta);
      preuploadedInputs.appendChild(hidden);
    }

    function renderNewImage(meta, previewUrl) {
      if (!newImagesContainer || !newImagesWrapper) {
        return;
      }
      newImagesWrapper.style.display = 'block';
      const col = document.createElement('div');
      col.className = 'col-md-4';
      col.dataset.key = meta.key;
      const radioId = 'primary_' + meta.key;
      col.innerHTML = `
        <div class="card h-100 shadow-sm">
          <img src="${previewUrl}" alt="Podgląd" class="card-img-top" class="card-img-fixed" loading="lazy" />
          <div class="card-body">
            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="primary_image" id="${radioId}" value="${meta.key}">
              <label class="form-check-label" for="${radioId}">Ustaw jako główne</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-new-image" data-key="${meta.key}">Usuń</button>
          </div>
        </div>
      `;
      newImagesContainer.appendChild(col);

      const radio = col.querySelector('input[type="radio"]');
      radio.addEventListener('change', ensurePrimarySelection);
      const removeBtn = col.querySelector('.remove-new-image');
      removeBtn.addEventListener('click', () => removePreuploaded(meta.key));

      // Automatycznie wybierz pierwsze dostępne zdjęcie
      ensurePrimarySelection();
    }

    async function removePreuploaded(key) {
      const idx = preuploadedState.findIndex(item => item.key === key);
      if (idx === -1) {
        return;
      }

      const meta = preuploadedState[idx];
      preuploadedState.splice(idx, 1);

      const card = newImagesContainer.querySelector('[data-key="' + key + '"]');
      if (card) {
        card.remove();
      }

      const hidden = preuploadedInputs.querySelector('input[data-key="' + key + '"]');
      if (hidden) {
        hidden.remove();
      }

      ensurePrimarySelection();

      if (preuploadedState.length === 0 && newImagesWrapper) {
        newImagesWrapper.style.display = 'none';
      }

      try {
        const fd = new FormData();
        fd.append('csrf', csrfToken);
        fd.append('path', meta.path);
        if (meta.thumb_path) {
          fd.append('thumb_path', meta.thumb_path);
        }
        await fetch(basePath + '/admin/index.php?action=delete_uploaded_image', {
          method: 'POST',
          body: fd
        });
      } catch (err) {
        console.error('Błąd usuwania pliku', err);
      }
    }

    async function uploadSingleFile(file) {
      const fd = new FormData();
      fd.append('csrf', csrfToken);
      fd.append('image', file);

      try {
        const response = await fetch(basePath + '/admin/index.php?action=upload_image_ajax', {
          method: 'POST',
          body: fd
        });
        if (!response.ok) {
          const text = await response.text();
          throw new Error(text || 'Błąd serwera podczas uploadu');
        }

        let data;
        try {
          data = await response.json();
        } catch (parseErr) {
          throw new Error('Nieprawidłowa odpowiedź serwera');
        }
        if (!data.success) {
          alert(data.error || 'Nie udało się przesłać pliku');
          return;
        }

        const key = createClientKey();
        const meta = {
          path: data.path,
          thumb_path: data.thumb_path,
          key: key
        };
        preuploadedState.push(meta);
        addPreuploadedInput(meta);
        renderNewImage(meta, data.thumb_url || data.url);
      } catch (err) {
        console.error('Upload error', err);
        alert(err?.message || 'Nie udało się przesłać pliku');
      }
    }

    async function uploadFiles(files) {
      for (const file of files) {
        await uploadSingleFile(file);
      }
    }

    if (galleryInput) {
      galleryInput.addEventListener('change', (event) => {
        const files = Array.from(event.target.files || []);
        if (files.length === 0) {
          return;
        }
        uploadFiles(files);
        galleryInput.value = '';
      });
    }

    document.addEventListener('trix-attachment-add', function(event) {
      const attachment = event.attachment;
      if (!attachment || !attachment.file) {
        return;
      }

      const fd = new FormData();
      fd.append('csrf', csrfToken);
      fd.append('image', attachment.file);

      fetch(basePath + '/admin/index.php?action=upload_image_ajax', {
        method: 'POST',
        body: fd
      })
        .then(response => response.json())
        .then(data => {
          if (!data.success) {
            attachment.remove();
            alert(data.error || 'Nie udało się przesłać obrazka do opisu');
            return;
          }
          const url = data.url || (basePath + '/' + data.path);
          attachment.setAttributes({ url: url, href: url });
        })
        .catch(err => {
          console.error('Upload Trix error', err);
          attachment.remove();
          alert('Nie udało się przesłać obrazka do opisu');
        });
    });
    
    function addTag(tagName) {
      const tagsInput = document.getElementById('tags');
      const currentTags = tagsInput.value.trim();
      
      // Sprawdź czy tag już nie istnieje
      const tagsArray = currentTags.split(',').map(t => t.trim().toLowerCase()).filter(t => t !== '');
      if (tagsArray.includes(tagName.toLowerCase())) {
        return; // Tag już dodany
      }
      
      // Dodaj tag
      if (currentTags === '') {
        tagsInput.value = tagName;
      } else {
        tagsInput.value = currentTags + ', ' + tagName;
      }
      
      // Zamknij sugestie
      document.getElementById('tags-suggestions').style.display = 'none';
      tagsInput.focus();
    }
    
    // Autouzupełnianie tagów
    const tagsInput = document.getElementById('tags');
    const suggestionsList = document.getElementById('tags-suggestions');

    function escapeHtml(str) {
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }
    
    tagsInput.addEventListener('input', function(e) {
      const value = e.target.value;
      const lastCommaIndex = value.lastIndexOf(',');
      const currentTag = lastCommaIndex >= 0 ? value.substring(lastCommaIndex + 1).trim() : value.trim();
      
      // Wyszukuj tylko jeśli mamy przynajmniej 2 znaki
      if (currentTag.length < 2) {
        suggestionsList.style.display = 'none';
        return;
      }
      
      // Debounce - czekaj 300ms przed wysłaniem zapytania
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        fetch(basePath + '/admin/index.php?action=search_tags&q=' + encodeURIComponent(currentTag))
          .then(response => response.json())
          .then(tags => {
            if (tags.length === 0) {
              suggestionsList.innerHTML = '<div class="list-group-item text-muted"><small>Brak pasujących tagów - zostanie utworzony nowy</small></div>';
              suggestionsList.style.display = 'block';
              return;
            }
            
            // Pobierz już dodane tagi
            const existingTags = value.split(',').map(t => t.trim().toLowerCase()).filter(t => t !== '');
            
            suggestionsList.innerHTML = tags
              .filter(tag => !existingTags.includes(tag.name.toLowerCase()))
              .map(tag => {
                const safeName = escapeHtml(tag.name);
                const usageInfo = tag.usage_count > 0 ? ` <span class="text-muted">(${tag.usage_count}×)</span>` : '';
                return `<a href="#" class="list-group-item list-group-item-action" data-tag-name="${safeName}">
                  ${safeName}${usageInfo}
                </a>`;
              })
              .join('');
            
            if (suggestionsList.innerHTML.trim() === '') {
              suggestionsList.style.display = 'none';
            } else {
              suggestionsList.style.display = 'block';
              
              // Obsługa kliknięcia na sugestię
              suggestionsList.querySelectorAll('a').forEach(item => {
                item.addEventListener('click', function(e) {
                  e.preventDefault();
                  const tagName = this.getAttribute('data-tag-name');
                  
                  // Zamień obecny tag na wybrany
                  const beforeLastComma = lastCommaIndex >= 0 ? value.substring(0, lastCommaIndex + 1) + ' ' : '';
                  tagsInput.value = beforeLastComma + tagName;
                  
                  suggestionsList.style.display = 'none';
                  tagsInput.focus();
                });
              });
            }
          })
          .catch(err => {
            console.error('Błąd wyszukiwania tagów:', err);
            suggestionsList.style.display = 'none';
          });
      }, 300);
    });
    
    // Zamknij sugestie po kliknięciu poza polem
    document.addEventListener('click', function(e) {
      if (e.target !== tagsInput && !suggestionsList.contains(e.target)) {
        suggestionsList.style.display = 'none';
      }
    });
    
    // Obsługa klawiatury (Escape - zamknij sugestie)
    tagsInput.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        suggestionsList.style.display = 'none';
      }
    });

    const linkId = <?= (int)($link['id'] ?? 0) ?>;

    function toggleSlugEdit() {
      const form = document.getElementById('slugEditForm');
      if (!form) return;
      const visible = form.style.display !== 'none';
      form.style.display = visible ? 'none' : 'block';
      if (!visible) {
        document.getElementById('slugEditInput').focus();
        document.getElementById('slugEditError').style.display = 'none';
      }
    }

    function saveSlugEdit() {
      const input = document.getElementById('slugEditInput');
      const errorEl = document.getElementById('slugEditError');
      const saveBtn = document.getElementById('slugSaveBtn');
      const newSlug = input.value.trim();

      if (!newSlug) {
        errorEl.textContent = 'Alias nie może być pusty';
        errorEl.style.display = 'block';
        return;
      }

      saveBtn.disabled = true;
      saveBtn.textContent = 'Zapisywanie...';
      errorEl.style.display = 'none';

      const fd = new FormData();
      fd.append('csrf', csrfToken);
      fd.append('id', linkId);
      fd.append('slug', newSlug);

      fetch(basePath + '/admin/index.php?action=update_slug', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          saveBtn.disabled = false;
          saveBtn.textContent = 'Zapisz';

          if (!data.success) {
            errorEl.textContent = data.error || 'Nie udało się zapisać';
            errorEl.style.display = 'block';
            return;
          }

          // Aktualizuj wyświetlane URL-e
          const redirectInput = document.getElementById('redirectUrlInput');
          const blogInput = document.getElementById('blogUrlInput');
          if (redirectInput) redirectInput.value = data.redirect_url;
          if (blogInput) blogInput.value = data.blog_url;

          // Ukryj formularz edycji
          document.getElementById('slugEditForm').style.display = 'none';

          // Resetuj QR code (wymaga ponownego wygenerowania z nowym aliasem)
          const qrPreview = document.getElementById('qr-preview');
          const qrContainer = document.getElementById('qr-container');
          if (qrPreview) qrPreview.style.display = 'none';
          if (qrContainer) qrContainer.style.display = 'block';

          // Toast
          const toastEl = document.getElementById('liveToast');
          const toastBody = document.getElementById('toastMessage');
          if (toastEl && toastBody) {
            toastEl.className = 'toast align-items-center border-0 bg-success text-white';
            toastBody.textContent = 'Alias zmieniony na: ' + data.slug;
            new bootstrap.Toast(toastEl, { delay: 3000 }).show();
          }
        })
        .catch(err => {
          saveBtn.disabled = false;
          saveBtn.textContent = 'Zapisz';
          errorEl.textContent = 'Błąd połączenia';
          errorEl.style.display = 'block';
        });
    }

    function copyLinkUrl(url, button) {
      navigator.clipboard.writeText(url).then(function() {
        const originalHTML = button.innerHTML;
        const originalClass = button.className;
        
        button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>';
        button.classList.remove('btn-info');
        button.classList.add('btn-success');
        
        setTimeout(function() {
          button.innerHTML = originalHTML;
          button.className = originalClass;
        }, 2000);
      }).catch(function(err) {
        alert('Nie udało się skopiować linku');
        console.error('Błąd kopiowania:', err);
      });
    }

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

    // Funkcje QR code
    function generateQRPreview(linkId) {
      fetch(basePath + '/admin/index.php?action=qr_code&id=' + linkId + '&mode=display')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('qr-image').src = data.qr_url;
            document.getElementById('qr-download').href = data.qr_url;
            document.getElementById('qr-download').download = 'qr-' + data.slug + '.png';
            document.getElementById('qr-preview').style.display = 'block';
            document.getElementById('qr-container').style.display = 'none';
          } else {
            alert('Błąd: ' + data.error);
          }
        })
        .catch(error => {
          console.error('Błąd:', error);
          alert('Nie udało się załadować QR code');
        });
    }

    function printQRPreview() {
      const img = document.getElementById('qr-image');
      const printWindow = window.open('', '', 'height=400,width=600');
      printWindow.document.write('<img src="' + img.src + '" style="max-width: 100%;" />');
      printWindow.document.write('<p style="text-align: center; margin-top: 20px;">Kod QR</p>');
      printWindow.document.close();
      printWindow.print();
    }

    ensurePrimarySelection();

    // Podpięcie zdarzeń (zamiast inline onclick)
    const slugEditToggle = document.getElementById('slugEditToggle');
    if (slugEditToggle) slugEditToggle.addEventListener('click', toggleSlugEdit);

    const slugSaveBtn = document.getElementById('slugSaveBtn');
    if (slugSaveBtn) slugSaveBtn.addEventListener('click', saveSlugEdit);

    const slugCancelBtn = document.getElementById('slugCancelBtn');
    if (slugCancelBtn) slugCancelBtn.addEventListener('click', toggleSlugEdit);

    document.querySelectorAll('.copy-link-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        if (input) copyLinkUrl(input.value, this);
      });
    });

    document.querySelectorAll('.tag-suggestion[data-tag]').forEach(function(el) {
      el.addEventListener('click', function() {
        addTag(this.getAttribute('data-tag'));
      });
    });

    const qrGenerateBtn = document.getElementById('qrGenerateBtn');
    if (qrGenerateBtn) qrGenerateBtn.addEventListener('click', function() { generateQRPreview(linkId); });

    const qrPrintBtn = document.getElementById('qrPrintBtn');
    if (qrPrintBtn) qrPrintBtn.addEventListener('click', printQRPreview);

    // Inicjalizacja tooltipów Bootstrap 5
    const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    // UTM Builder
    (function() {
      const utmFields = document.querySelectorAll('.utm-field');
      const utmPreview = document.getElementById('utmPreview');
      const utmPreviewUrl = document.getElementById('utmPreviewUrl');
      const targetUrlInput = document.getElementById('target_url');
      const utmCopyBtn = document.getElementById('utmCopyBtn');
      const utmApplyBtn = document.getElementById('utmApplyBtn');

      function buildUtmUrl() {
        const baseUrl = targetUrlInput ? targetUrlInput.value.trim() : '';
        if (!baseUrl) return '';

        const params = new URLSearchParams();
        utmFields.forEach(function(field) {
          const val = field.value.trim();
          if (val) params.set(field.id, val);
        });

        const paramStr = params.toString();
        if (!paramStr) return '';

        const separator = baseUrl.includes('?') ? '&' : '?';
        return baseUrl + separator + paramStr;
      }

      function updatePreview() {
        const url = buildUtmUrl();
        if (url) {
          utmPreview.style.display = '';
          utmPreviewUrl.value = url;
        } else {
          utmPreview.style.display = 'none';
        }
      }

      utmFields.forEach(function(field) {
        field.addEventListener('input', updatePreview);
      });
      if (targetUrlInput) {
        targetUrlInput.addEventListener('input', updatePreview);
      }

      if (utmCopyBtn) {
        utmCopyBtn.addEventListener('click', function() {
          const url = utmPreviewUrl.value;
          if (!url) return;
          navigator.clipboard.writeText(url).then(function() {
            utmCopyBtn.classList.remove('btn-success');
            utmCopyBtn.classList.add('btn-primary');
            setTimeout(function() {
              utmCopyBtn.classList.remove('btn-primary');
              utmCopyBtn.classList.add('btn-success');
            }, 1500);
          });
        });
      }

      if (utmApplyBtn) {
        utmApplyBtn.addEventListener('click', function() {
          const url = buildUtmUrl();
          if (url && targetUrlInput) {
            targetUrlInput.value = url;
            utmFields.forEach(function(f) { f.value = ''; });
            updatePreview();
          }
        });
      }
    })();
  </script>
</body>
</html>
