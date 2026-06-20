<?php
  $pageTitle = 'Ustawienia — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <!-- Zawartość główna -->
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10">
        <div class="card shadow-sm">
          <div class="card-header bg-info text-white">
            <h2 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
              </svg>
              Ustawienia
            </h2>
          </div>
          <div class="card-body">
            <form method="post" action="<?= $basePath ?>/admin/index.php?action=settings" enctype="multipart/form-data" id="settingsForm">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
              <input type="hidden" name="active_tab" id="activeTabInput" value="general" />

              <!-- Nav tabs -->
              <ul class="nav nav-tabs mb-4 settings-nav" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z"/>
                    <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466"/>
                  </svg>
                    Ogólne
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="customfields-tab" data-bs-toggle="tab" data-bs-target="#customfields" type="button" role="tab" aria-controls="customfields" aria-selected="false">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list-check" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M10.854 6.146a.5.5 0 0 1 0 .708L8.207 9.5 7 8.293l-.854.853a.5.5 0 1 1-.708-.708l1.208-1.207a.5.5 0 0 1 .708 0l1.646 1.647 2.293-2.293a.5.5 0 0 1 .708 0"/>
                    <path d="M1.5 12a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1H2a.5.5 0 0 1-.5-.5m0-4A.5.5 0 0 1 2 7.5h2a.5.5 0 0 1 0 1H2a.5.5 0 0 1-.5-.5m0-4A.5.5 0 0 1 2 3.5h2a.5.5 0 0 1 0 1H2a.5.5 0 0 1-.5-.5M5 12a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 5 12m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 5 8m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 5 4"/>
                  </svg>
                    Pola linków
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="homepage-tab" data-bs-toggle="tab" data-bs-target="#homepage" type="button" role="tab" aria-controls="homepage" aria-selected="false">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                    <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
                  </svg>
                    Strona główna
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="scripts-tab" data-bs-toggle="tab" data-bs-target="#scripts" type="button" role="tab" aria-controls="scripts" aria-selected="false">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-braces-asterisk" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1.114 8.063V7.9c1.005-.102 1.497-.615 1.497-1.6V4.503c0-1.094.39-1.538 1.354-1.538h.273V2h-.376C2.25 2 1.49 2.759 1.49 4.352v1.524c0 1.094-.376 1.456-1.49 1.456v1.299c1.114 0 1.49.362 1.49 1.456v1.524c0 1.593.759 2.352 2.372 2.352h.376v-.964h-.273c-.964 0-1.354-.444-1.354-1.538V9.663c0-.984-.492-1.497-1.497-1.6M14.886 7.9v.164c-1.005.103-1.497.616-1.497 1.6v1.798c0 1.094-.39 1.538-1.354 1.538h-.273v.964h.376c1.613 0 2.372-.759 2.372-2.352v-1.524c0-1.094.376-1.456 1.49-1.456v-1.3c-1.114 0-1.49-.362-1.49-1.456V4.352C14.51 2.759 13.75 2 12.138 2h-.376v.964h.273c.964 0 1.354.444 1.354 1.538V6.3c0 .984.492 1.497 1.497 1.6M7.5 11.5V9.207l-1.621 1.621-.707-.707L6.792 8.5H4.5v-1h2.293L5.172 5.879l.707-.707L7.5 6.792V4.5h1v2.293l1.621-1.621.707.707L9.208 7.5H11.5v1H9.207l1.621 1.621-.707.707L8.5 9.208V11.5z"/>
                  </svg>
                    Skrypty & Kod
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16">
                    <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
                    <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                  </svg>
                    Bezpieczeństwo
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                  </svg>
                    Powiadomienia Email
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link<?= ($licenseStatus['state'] ?? 'active') !== 'active' ? ' text-danger fw-semibold' : '' ?>" id="license-tab" data-bs-toggle="tab" data-bs-target="#license" type="button" role="tab" aria-controls="license" aria-selected="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                      <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>
                      <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                    </svg>
                    Licencja
                  </button>
                </li>
              </ul>

              <!-- Tab content -->
              <div class="tab-content settings-tab-content" id="settingsTabContent">

                <!-- Tab: Ogólne -->
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                  <div class="mb-3">
                    <label for="delay" class="form-label">Opóźnienie przekierowania (sekundy)</label>
                    <input type="number" class="form-control" id="delay" min="0" name="delay" value="<?= (int)$delay ?>" required />
                    <div class="form-text">Ilość sekund przed automatycznym przekierowaniem użytkownika na docelowy adres</div>
                  </div>

                  <div class="mb-3">
                    <label for="length" class="form-label">Długość losowego aliasu</label>
                    <input type="number" class="form-control" id="length" min="4" max="64" name="length" value="<?= (int)$length ?>" required />
                    <div class="form-text">Liczba znaków dla automatycznie generowanych skróconych linków (4-64 znaki)</div>
                  </div>

                  <hr class="my-4" />
                  <h5 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-clock me-2" viewBox="0 0 16 16">
                      <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
                      <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/>
                    </svg>
                    Strefa czasowa
                  </h5>

                  <div class="mb-3">
                    <label for="timezone" class="form-label">Strefa czasowa systemu</label>
                    <select class="form-select" id="timezone" name="timezone">
                      <?php
                        $timezones = DateTimeZone::listIdentifiers();
                        $currentTimezone = $timezone ?? 'Europe/Warsaw';
                        foreach ($timezones as $tz):
                      ?>
                        <option value="<?= htmlspecialchars($tz) ?>" <?= $tz === $currentTimezone ? 'selected' : '' ?>>
                          <?= htmlspecialchars($tz) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <div class="form-text">Strefa czasowa używana do wysyłania powiadomień email i wyświetlania dat</div>
                  </div>

                  <div class="alert bg-info-subtle border border-info text-info-emphasis mb-3" role="alert">
                    <div class="row">
                      <div class="col-md-6">
                        <small class="d-block"><strong>Czas serwera:</strong> <?= htmlspecialchars($serverTime ?? date('Y-m-d H:i:s')) ?></small>
                        <small class="text-muted">(<?= htmlspecialchars($serverTimezone ?? date_default_timezone_get()) ?>)</small>
                      </div>
                      <div class="col-md-6">
                        <small class="d-block"><strong>Twoj czas:</strong> <span id="userTimeDisplay"><?= htmlspecialchars($userTime ?? $serverTime ?? date('Y-m-d H:i:s')) ?></span></small>
                        <small class="text-muted">(<span id="userTimezoneDisplay"><?= htmlspecialchars($timezone ?? 'Europe/Warsaw') ?></span>)</small>
                      </div>
                    </div>
                  </div>

                  <hr class="my-4" />
                  <h5 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16" style="margin-right: 8px;">
                      <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
                    </svg>
                    Kosz (Usuwanie linków)
                  </h5>

                  <div class="mb-3">
                    <label for="trash_mode" class="form-label">Tryb usuwania linków</label>
                    <select class="form-select" id="trash_mode" name="trash_mode" onchange="toggleTrashOptions()">
                      <option value="auto_delete" <?= ($trashMode ?? 'auto_delete') === 'auto_delete' ? 'selected' : '' ?>>
                        Usuń automatycznie po X dniach
                      </option>
                      <option value="keep_forever" <?= ($trashMode ?? 'auto_delete') === 'keep_forever' ? 'selected' : '' ?>>
                        Nie usuwaj automatycznie (pozostaw w koszu na zawsze)
                      </option>
                      <option value="hard_delete" <?= ($trashMode ?? 'auto_delete') === 'hard_delete' ? 'selected' : '' ?>>
                        Nie przenoś do kosza (usuń natychmiast)
                      </option>
                    </select>
                    <div class="form-text">Określ, co dzieje się z linkami po ich usunięciu</div>
                  </div>

                  <div class="mb-3" id="trash-auto-delete-options" style="<?= ($trashMode ?? 'auto_delete') === 'auto_delete' ? '' : 'display: none;' ?>">
                    <label for="trash_auto_delete_days" class="form-label">Usuń automatycznie po (dni)</label>
                    <input type="number" class="form-control" id="trash_auto_delete_days" name="trash_auto_delete_days"
                           value="<?= (int)($trashAutoDeleteDays ?? 30) ?>" min="1" max="365" />
                    <div class="form-text">Linki w koszu zostaną trwale usunięte po tej liczbie dni (1-365)</div>
                  </div>

                  <hr class="my-4" />
                  <h5 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-hdd me-2" viewBox="0 0 16 16">
                      <path d="M4.5 11a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1M3 10.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                      <path d="M16 11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V9.51c0-.418.105-.83.305-1.197l2.472-4.531A1.5 1.5 0 0 1 4.094 3h7.812a1.5 1.5 0 0 1 1.317.782l2.472 4.53c.2.368.305.78.305 1.198zM3.655 4.26 1.592 8.043C1.724 8.014 1.86 8 2 8h12c.14 0 .276.014.408.042L12.345 4.26a.5.5 0 0 0-.439-.26H4.094a.5.5 0 0 0-.44.26M1 10v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1"/>
                    </svg>
                    Limity miejsca na dysku
                  </h5>

                  <div class="alert bg-info-subtle border border-info text-info-emphasis">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle me-2" viewBox="0 0 16 16">
                      <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                      <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                    </svg>
                    <strong>Informacja:</strong> Wprowadź limity przyznane przez hosting, aby monitorować wykorzystanie przestrzeni. Sprawdź wartości w panelu hostingowym (cPanel/Plesk/DirectAdmin). Pozostaw puste, jeśli nie znasz limitów.
                  </div>

                  <?php
                    // Funkcja pomocnicza do konwersji MB na najlepszą jednostkę
                    function convertMbToDisplay(int $mb): array {
                      if ($mb <= 0) return ['value' => '', 'unit' => 'MB'];
                      if ($mb >= 1024 && $mb % 1024 === 0) {
                        return ['value' => $mb / 1024, 'unit' => 'GB'];
                      }
                      if ($mb < 1 && $mb > 0) {
                        return ['value' => $mb * 1024, 'unit' => 'KB'];
                      }
                      return ['value' => $mb, 'unit' => 'MB'];
                    }
                    $filesDisplay = convertMbToDisplay((int)($diskQuotaFilesMb ?? 0));
                    $dbDisplay = convertMbToDisplay((int)($diskQuotaDatabaseMb ?? 0));
                  ?>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="disk_quota_files_value" class="form-label">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-folder me-1" viewBox="0 0 16 16">
                          <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z"/>
                        </svg>
                        Limit przestrzeni dla plików
                      </label>
                      <div class="input-group">
                        <input type="number" class="form-control" id="disk_quota_files_value" name="disk_quota_files_value"
                               value="<?= $filesDisplay['value'] !== '' ? (float)$filesDisplay['value'] : '' ?>" min="0" step="any" placeholder="np. 10" />
                        <select class="form-select" id="disk_quota_files_unit" name="disk_quota_files_unit" style="max-width: 90px;">
                          <option value="MB" <?= $filesDisplay['unit'] === 'MB' ? 'selected' : '' ?>>MB</option>
                          <option value="GB" <?= $filesDisplay['unit'] === 'GB' ? 'selected' : '' ?>>GB</option>
                        </select>
                      </div>
                      <div class="form-text">Całkowity limit przestrzeni dla plików na serwerze (uploads, logs, cache)</div>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label for="disk_quota_database_value" class="form-label">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-database me-1" viewBox="0 0 16 16">
                          <path d="M4.318 2.687C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4c0-.374.356-.875 1.318-1.313M13 5.698V7c0 .374-.356.875-1.318 1.313C10.766 8.729 9.464 9 8 9s-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777A5 5 0 0 0 13 5.698M14 4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16s3.022-.289 4.096-.777C13.125 14.755 14 14.007 14 13zm-1 4.698V10c0 .374-.356.875-1.318 1.313C10.766 11.729 9.464 12 8 12s-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10s3.022-.289 4.096-.777A5 5 0 0 0 13 8.698m0 3V13c0 .374-.356.875-1.318 1.313C10.766 14.729 9.464 15 8 15s-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13s3.022-.289 4.096-.777c.324-.147.633-.323.904-.525"/>
                        </svg>
                        Limit przestrzeni dla bazy danych
                      </label>
                      <div class="input-group">
                        <input type="number" class="form-control" id="disk_quota_database_value" name="disk_quota_database_value"
                               value="<?= $dbDisplay['value'] !== '' ? (float)$dbDisplay['value'] : '' ?>" min="0" step="any" placeholder="np. 1" />
                        <select class="form-select" id="disk_quota_database_unit" name="disk_quota_database_unit" style="max-width: 90px;">
                          <option value="MB" <?= $dbDisplay['unit'] === 'MB' ? 'selected' : '' ?>>MB</option>
                          <option value="GB" <?= $dbDisplay['unit'] === 'GB' ? 'selected' : '' ?>>GB</option>
                        </select>
                      </div>
                      <div class="form-text">Limit przestrzeni dla bazy danych MySQL/MariaDB</div>
                    </div>
                  </div>

                  <hr class="my-4" />
                  <h5 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-link-45deg me-2" viewBox="0 0 16 16">
                      <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/>
                      <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/>
                    </svg>
                    Przyjazne adresy URL (mod_rewrite)
                  </h5>

                  <div class="mb-3">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="pretty_urls" name="pretty_urls" value="1" <?= !empty($prettyUrls) ? 'checked' : '' ?>>
                      <label class="form-check-label" for="pretty_urls">
                        <strong>Włącz przyjazne adresy URL</strong>
                      </label>
                    </div>
                    <div class="form-text ms-4 mt-1">
                      Po włączeniu linki do filtrowania bloga używają czytelnych adresów (np. <code>/category/slug</code> zamiast <code>/?category=slug</code>).
                      Wymaga aktywnego modułu <strong>mod_rewrite</strong> na serwerze Apache.
                    </div>
                  </div>

                  <div class="alert bg-info-subtle border border-info text-info-emphasis" id="htaccessInfo">
                    <div class="d-flex align-items-start gap-2 mb-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle mt-1 flex-shrink-0" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                      </svg>
                      <div>
                        <strong>Wymagana konfiguracja <code>.htaccess</code></strong><br>
                        <small>Upewnij się, że plik <code>.htaccess</code> w katalogu aplikacji zawiera poniższe reguły. Jeśli używasz RedirectCMS z instalatora, plik jest tworzony automatycznie.</small>
                      </div>
                    </div>
                    <pre class="bg-white border rounded p-3 mb-2 small font-monospace text-dark"><code>&lt;IfModule mod_rewrite.c&gt;
    RewriteEngine On

    # Nie przepisuj żądań do katalogu admin
    RewriteCond %{REQUEST_URI} ^/.*admin/ [NC]
    RewriteRule ^ - [L]

    # Nie przepisuj żądań do rzeczywistych plików
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^ - [L]

    # Nie przepisuj żądań do rzeczywistych katalogów
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # Wszystkie pozostałe żądania (slugi) kieruj do index.php
    RewriteRule ^(.*)$ index.php [QSA,L]
&lt;/IfModule&gt;</code></pre>
                    <small class="text-muted">
                      Sprawdź też, czy w konfiguracji Apache dla Twojej domeny jest włączone <code>AllowOverride All</code>.
                    </small>
                  </div>

                </div>

                <!-- Tab: Pola linków -->
                <div class="tab-pane fade" id="customfields" role="tabpanel" aria-labelledby="customfields-tab">
                  <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-3">
                    <div>
                      <h5 class="mb-1">Pola niestandardowe</h5>
                      <p class="text-muted mb-0">Dodaj własne atrybuty dla linków i wykorzystaj je w szablonach (np. tryb blog).</p>
                    </div>
                    <button type="button" class="btn btn-outline-primary" id="addCustomFieldRow">+ Dodaj pole</button>
                  </div>

                  <div class="table-responsive">
                    <table class="table align-middle" id="customFieldsTable">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 28%;">Etykieta</th>
                          <th style="width: 22%;">Klucz</th>
                          <th style="width: 15%;">Typ</th>
                          <th style="width: 20%;">Placeholder</th>
                          <th style="width: 10%;">Krok</th>
                          <th style="width: 5%;"></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (!empty($customFieldDefinitions)): ?>
                          <?php foreach ($customFieldDefinitions as $field): ?>
                            <tr>
                              <td><input type="text" name="custom_field_label[]" class="form-control" value="<?= htmlspecialchars($field['label'] ?? '') ?>" placeholder="np. Średnia ocena" required /></td>
                              <td><input type="text" name="custom_field_key[]" class="form-control" value="<?= htmlspecialchars($field['key'] ?? '') ?>" placeholder="np. avg_rating" required /></td>
                              <td>
                                <select name="custom_field_type[]" class="form-select">
                                  <option value="text" <?= ($field['type'] ?? 'text') === 'text' ? 'selected' : '' ?>>Tekst</option>
                                  <option value="number" <?= ($field['type'] ?? 'text') === 'number' ? 'selected' : '' ?>>Liczba</option>
                                </select>
                              </td>
                              <td><input type="text" name="custom_field_placeholder[]" class="form-control" value="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" placeholder="np. 199 PLN" /></td>
                              <td><input type="text" name="custom_field_step[]" class="form-control" value="<?= htmlspecialchars($field['step'] ?? '') ?>" placeholder="0.1" /></td>
                              <td class="text-end">
                                <button type="button" class="btn btn-link text-danger p-0 remove-custom-field" aria-label="Usuń">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M2.5 2.5a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-1 0v-10a.5.5 0 0 1 .5-.5m5 0a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-1 0v-10a.5.5 0 0 1 .5-.5m5 0a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-1 0v-10a.5.5 0 0 1 .5-.5"/>
                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                  </svg>
                                </button>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr>
                            <td><input type="text" name="custom_field_label[]" class="form-control" value="Średnia ocena" placeholder="np. Średnia ocena" required /></td>
                            <td><input type="text" name="custom_field_key[]" class="form-control" value="avg_rating" placeholder="np. avg_rating" required /></td>
                            <td>
                              <select name="custom_field_type[]" class="form-select">
                                <option value="text">Tekst</option>
                                <option value="number" selected>Liczba</option>
                              </select>
                            </td>
                            <td><input type="text" name="custom_field_placeholder[]" class="form-control" value="4.8" placeholder="np. 4.8" /></td>
                            <td><input type="text" name="custom_field_step[]" class="form-control" value="0.1" placeholder="0.1" /></td>
                            <td class="text-end">
                              <button type="button" class="btn btn-link text-danger p-0 remove-custom-field" aria-label="Usuń">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                  <path d="M2.5 2.5a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-1 0v-10a.5.5 0 0 1 .5-.5m5 0a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-1 0v-10a.5.5 0 0 1 .5-.5m5 0a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-1 0v-10a.5.5 0 0 1 .5-.5"/>
                                  <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                </svg>
                              </button>
                            </td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Tab: Strona główna -->
                <div class="tab-pane fade" id="homepage" role="tabpanel" aria-labelledby="homepage-tab">

                  <h5 class="mb-4">Konfiguracja strony głównej</h5>

                  <div class="mb-3">
                    <label for="home_mode" class="form-label">Tryb strony głównej</label>
                    <select class="form-select" id="home_mode" name="home_mode" onchange="toggleHomeModeOptions()">
                      <option value="landing" <?= ($homeMode ?? 'landing') === 'landing' ? 'selected' : '' ?>>Landing page (domyślny)</option>
                      <option value="redirect" <?= ($homeMode ?? 'landing') === 'redirect' ? 'selected' : '' ?>>Przekierowanie na inny URL</option>
                      <option value="blog" <?= ($homeMode ?? 'landing') === 'blog' ? 'selected' : '' ?>>Blog (lista linków jako wpisy)</option>
                    </select>
                    <div class="form-text">
                      <strong>Landing page:</strong> Standardowa strona informacyjna<br>
                      <strong>Przekierowanie:</strong> Automatyczne przekierowanie na wybrany URL<br>
                      <strong>Blog:</strong> Wyświetla wszystkie linki w formacie wpisów blogowych
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="home_title" class="form-label">Tytuł strony głównej</label>
                    <input type="text" class="form-control" id="home_title" name="home_title" value="<?= htmlspecialchars($homeTitle ?? 'RedirectCMS') ?>" placeholder="RedirectCMS" />
                  </div>

                  <div class="mb-3">
                    <label for="home_subtitle" class="form-label">Podtytuł</label>
                    <input type="text" class="form-control" id="home_subtitle" name="home_subtitle" value="<?= htmlspecialchars($homeSubtitle ?? 'Skrócone linki z historią') ?>" placeholder="Skrócone linki z historią" />
                  </div>

                  <div class="mb-3">
                    <label for="home_description" class="form-label">Meta Description (SEO)</label>
                    <input type="text" class="form-control" id="home_description" name="home_description" value="<?= htmlspecialchars($homeDescription ?? '') ?>" placeholder="Opis dla wyszukiwarek (max 160 znaków)" maxlength="160" />
                    <div class="form-text">Pojawiający się w wynikach wyszukiwania</div>
                  </div>

                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label">Logo (jpg/png)</label>
                        <?php if (!empty($brandingLogo)): ?>
                          <div class="mb-2 d-flex align-items-center gap-2">
                            <img src="<?= htmlspecialchars($basePath . '/' . ltrim($brandingLogo, '/')) ?>" alt="Aktualne logo" style="max-height: 60px; max-width: 200px; object-fit: contain; border: 1px solid #D0D7DE; padding: 6px; border-radius: 6px; background: #fff;" />
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="delete_logo" name="delete_logo" value="1" />
                              <label class="form-check-label" for="delete_logo">Usuń logo</label>
                            </div>
                          </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="branding_logo" id="branding_logo" accept=".jpg,.jpeg,.png" />
                        <div class="form-text">Rekomendacja: min. 80×80 px, proporcje poziome; format JPG/PNG.</div>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label">Favicon (jpg/png)</label>
                        <?php if (!empty($brandingFavicon)): ?>
                          <div class="mb-2 d-flex align-items-center gap-2">
                            <img src="<?= htmlspecialchars($basePath . '/' . ltrim($brandingFavicon, '/')) ?>" alt="Aktualna favicon" style="height: 40px; width: 40px; object-fit: contain; border: 1px solid #D0D7DE; padding: 6px; border-radius: 6px; background: #fff;" />
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="delete_favicon" name="delete_favicon" value="1" />
                              <label class="form-check-label" for="delete_favicon">Usuń favicon</label>
                            </div>
                          </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="branding_favicon" id="branding_favicon" accept=".jpg,.jpeg,.png" />
                        <div class="form-text">Rekomendacja: 32×32 px lub 64×64 px, format JPG/PNG.</div>
                      </div>
                    </div>

                  <!-- Opcje dla trybu Landing -->
                  <div id="landing-options" class="mode-options">
                    <h6 class="mb-3 text-primary">Ustawienia trybu Landing Page</h6>
                    <div class="mb-3">
                      <label for="home_section_title" class="form-label">Tytuł sekcji informacyjnej</label>
                      <input type="text" class="form-control" id="home_section_title" name="home_section_title" value="<?= htmlspecialchars($homeSectionTitle ?? 'Jak używać?') ?>" placeholder="Jak używać?" />
                    </div>

                    <div class="mb-3">
                      <label for="home_section_text" class="form-label">Tekst sekcji informacyjnej</label>
                      <textarea class="form-control" id="home_section_text" name="home_section_text" rows="3" placeholder="Aby skorzystać z systemu skróconych linków......"><?= htmlspecialchars($homeSectionText ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                      <label for="home_admin_button_text" class="form-label">Tekst przycisku na stronie głównej</label>
                      <input type="text" class="form-control" id="home_admin_button_text" name="home_admin_button_text" value="<?= htmlspecialchars($homeCtaButtonText ?? '') ?>" placeholder="np. Dowiedz się więcej" />
                      <div class="form-text">Pozostaw puste, aby nie wyświetlać przycisku.</div>
                    </div>

                    <div class="mb-3">
                      <label for="home_cta_button_url" class="form-label">Link przycisku na stronie głównej</label>
                      <input type="text" class="form-control" id="home_cta_button_url" name="home_cta_button_url" value="<?= htmlspecialchars($homeCtaButtonUrl ?? '') ?>" placeholder="https://example.com lub /strona" />
                    </div>
                  </div>

                  <!-- Opcje dla trybu Redirect -->
                  <div id="redirect-options" class="mode-options">
                    <h6 class="mb-3 text-primary">Ustawienia trybu Redirect</h6>
                    <div class="mb-3">
                      <label for="home_redirect_url" class="form-label">URL przekierowania</label>
                      <input type="url" class="form-control" id="home_redirect_url" name="home_redirect_url" value="<?= htmlspecialchars($homeRedirectUrl ?? '') ?>" placeholder="https://przyklad.com" />
                      <div class="form-text">Każdy wejście na główną stronę zostanie przekierowane na ten adres</div>
                    </div>
                  </div>

                  <!-- Opcje dla trybu Blog -->
                  <div id="blog-options" class="mode-options">
                    <div class="alert bg-info-subtle border border-info text-info-emphasis">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle me-2" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                      </svg>
                      Ustawienia wyglądu bloga (szablon, kolory, slider, sidebar) dostępne są w zakładce
                      <a href="<?= $basePath ?>/admin/index.php?action=appearance" class="alert-link">Wygląd</a>.
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="home_footer" class="form-label">Stopka strony głównej</label>
                    <textarea class="form-control" id="home_footer" name="home_footer" rows="4" placeholder="HTML dozwolony, np. &lt;p&gt;© 2024 Moja firma&lt;/p&gt;"><?= htmlspecialchars($homeFooter ?? '') ?></textarea>
                    <div class="form-text">Obsługuje HTML. Zostanie wyświetlona na dole strony głównej.</div>
                  </div>
                </div>

                <!-- Tab: Skrypty & Kod -->
                <div class="tab-pane fade" id="scripts" role="tabpanel" aria-labelledby="scripts-tab">

                  <h5 class="mb-4">Dodatkowe kody i skrypty</h5>

                  <h6 class="mb-3 mt-4">Strona główna</h6>

                  <div class="mb-3">
                    <label for="home_header_code" class="form-label">Kod w sekcji &lt;head&gt;</label>
                    <textarea class="form-control" id="home_header_code" name="home_header_code" rows="3" placeholder="Wklej kod np. Google Analytics czy Meta Pixel" spellcheck="false"><?= htmlspecialchars($homeHeaderCode ?? '') ?></textarea>
                    <div class="form-text">Wstawiany bez zmian w nagłówku strony głównej (Google Analytics, Meta Pixel, itp.)</div>
                  </div>

                  <div class="mb-4">
                    <label for="home_footer_code" class="form-label">Kod w stopce (przed &lt;/body&gt;)</label>
                    <textarea class="form-control" id="home_footer_code" name="home_footer_code" rows="3" placeholder="Skrypty ładujące się na końcu strony" spellcheck="false"><?= htmlspecialchars($homeFooterCode ?? '') ?></textarea>
                    <div class="form-text">Przydatne dla skryptów wymagających wstrzyknięcia przed zamknięciem znacznika body.</div>
                  </div>

                  <h6 class="mb-3 mt-4">Strona przekierowania</h6>

                  <div class="mb-3">
                    <label for="redirect_header_code" class="form-label">Kod w sekcji &lt;head&gt;</label>
                    <textarea class="form-control" id="redirect_header_code" name="redirect_header_code" rows="3" placeholder="Wklej kod np. Google Analytics czy Meta Pixel" spellcheck="false"><?= htmlspecialchars($redirectHeaderCode ?? '') ?></textarea>
                    <div class="form-text">Wstawiany bez zmian w nagłówku strony przekierowania (Google Analytics, Meta Pixel, itp.)</div>
                  </div>

                  <div class="mb-4">
                    <label for="redirect_footer_code" class="form-label">Kod w stopce (przed &lt;/body&gt;)</label>
                    <textarea class="form-control" id="redirect_footer_code" name="redirect_footer_code" rows="3" placeholder="Skrypty ładujące się na końcu strony przekierowania" spellcheck="false"><?= htmlspecialchars($redirectFooterCode ?? '') ?></textarea>
                    <div class="form-text">Przydatne dla skryptów wymagających wstrzyknięcia przed zamknięciem znacznika body.</div>
                  </div>

                  <h6 class="mb-3 mt-4">Boty społecznościowe</h6>

                  <div class="mb-3">
                    <div class="form-check form-check-lg">
                      <input class="form-check-input" type="checkbox" id="disable_social_redirect" name="disable_social_redirect" value="1" <?= !empty($disableSocialRedirect) ? 'checked' : '' ?> />
                      <label class="form-check-label" for="disable_social_redirect">
                        <strong>Wyłącz przekierowanie dla botów społecznościowych</strong>
                      </label>
                    </div>
                    <div class="form-text ms-4 mt-2">Dotyczy: Facebook, Twitter, LinkedIn, Slack, Telegram i innych botów. Włączenie powoduje, że preview boty widzą jedynie metadane Open Graph krótkiego linku i nie wykrywają docelowego URL.</div>
                  </div>
                </div>

                <!-- Tab: Bezpieczeństwo -->
                <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">

                  <h5 class="mb-4">Dane i hasło administratora</h5>

                  <div class="alert bg-info-subtle border border-info text-info-emphasis" role="alert">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                  </svg>
                    Używane do logowania do panelu administracyjnego
                  </div>

                  <div class="mb-3">
                    <label for="new_username" class="form-label">Nowa nazwa użytkownika</label>
                    <input type="text" class="form-control" id="new_username" name="new_username" placeholder="Pozostaw puste, aby nie zmieniać. Obecna: <?= htmlspecialchars($username) ?>" />

                    <?php if (isset($username_error)): ?>
                      <div class="alert bg-danger-subtle border border-danger text-danger-emphasis mt-2 mb-0"><?= htmlspecialchars($username_error) ?></div>
                    <?php endif; ?>
                    <?php if (isset($username_success)): ?>
                      <div class="alert bg-success-subtle border border-success text-success-emphasis mt-2 mb-0"><?= htmlspecialchars($username_success) ?></div>
                    <?php endif; ?>
                  </div>

                  <div class="mb-3">
                    <label for="new_password" class="form-label">Nowe hasło</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Pozostaw puste, aby nie zmieniać" />
                  </div>

                  <div class="mb-3">
                    <label for="new_password_confirm" class="form-label">Powtórz nowe hasło</label>
                    <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" placeholder="Pozostaw puste, aby nie zmieniać" />

                    <?php if (isset($password_error)): ?>
                      <div class="alert bg-danger-subtle border border-danger text-danger-emphasis mt-2 mb-0"><?= htmlspecialchars($password_error) ?></div>
                    <?php endif; ?>
                    <?php if (isset($password_success)): ?>
                      <div class="alert bg-success-subtle border border-success text-success-emphasis mt-2 mb-0"><?= htmlspecialchars($password_success) ?></div>
                    <?php endif; ?>
                  </div>

                  <hr class="my-4" />

                  <h5 class="mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                      <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
                    </svg>
                    Uwierzytelnianie dwuskładnikowe (2FA)
                  </h5>
                  <div class="d-flex align-items-center gap-3">
                    <?php if ($twoFactorEnabled): ?>
                      <span class="badge bg-success-subtle border border-success text-success-emphasis">Aktywne</span>
                      <span class="text-muted">2FA jest włączone dla Twojego konta.</span>
                    <?php else: ?>
                      <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">Wyłączone</span>
                      <span class="text-muted">Dodaj dodatkową warstwę zabezpieczeń.</span>
                    <?php endif; ?>
                    <a href="<?= $basePath ?>/admin/index.php?action=two_factor_setup" class="btn btn-sm btn-outline-primary ms-auto">
                      <?= $twoFactorEnabled ? 'Zarządzaj 2FA' : 'Włącz 2FA' ?>
                    </a>
                  </div>

                  <hr class="my-4" />

                  <h5 class="mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-bug me-2" viewBox="0 0 16 16">
                      <path d="M4.355.522a.5.5 0 0 1 .623.333l.291.956A5 5 0 0 1 8 1c1.007 0 1.946.298 2.731.811l.29-.956a.5.5 0 1 1 .957.29l-.41 1.352A5 5 0 0 1 13 6h.5a.5.5 0 0 0 .5-.5V5a.5.5 0 0 1 1 0v.5A1.5 1.5 0 0 1 13.5 7H13v1h1.5a.5.5 0 0 1 0 1H13v1h.5a1.5 1.5 0 0 1 1.5 1.5v.5a.5.5 0 1 1-1 0v-.5a.5.5 0 0 0-.5-.5H13a5 5 0 0 1-10 0h-.5a.5.5 0 0 0-.5.5v.5a.5.5 0 1 1-1 0v-.5A1.5 1.5 0 0 1 2.5 10H3V9H1.5a.5.5 0 0 1 0-1H3V7h-.5A1.5 1.5 0 0 1 1 5.5V5a.5.5 0 0 1 1 0v.5a.5.5 0 0 0 .5.5H3a5 5 0 0 1 1.54-3.622l-.41-1.352a.5.5 0 0 1 .333-.623M4 6a4 4 0 0 0-.354 7.978A5 5 0 0 1 4 13h8a5 5 0 0 1 .354.978A4 4 0 0 0 12 6z"/>
                    </svg>
                    Debugowanie i logi
                  </h5>

                  <div class="alert bg-warning-subtle border border-warning text-warning-emphasis" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle me-1" viewBox="0 0 16 16">
                      <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                    </svg>
                    <strong>Uwaga:</strong> Włączenie wyświetlania błędów na stronie może ujawnić wrażliwe informacje. Używaj tylko w środowisku deweloperskim!
                  </div>

                  <div class="mb-3">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="debug_enabled" name="debug_enabled" <?= !empty($debugEnabled) ? 'checked' : '' ?> />
                      <label class="form-check-label" for="debug_enabled">Włącz tryb debugowania</label>
                    </div>
                    <div class="form-text">Włącza zaawansowane logowanie błędów PHP</div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="debug_log_errors" name="debug_log_errors" <?= !empty($debugLogErrors) ? 'checked' : '' ?> />
                        <label class="form-check-label" for="debug_log_errors">Zapisuj bledy do pliku</label>
                      </div>
                      <div class="form-text">Bledy beda zapisywane do pliku logow</div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="debug_display_errors" name="debug_display_errors" <?= !empty($debugDisplayErrors) ? 'checked' : '' ?> />
                        <label class="form-check-label" for="debug_display_errors">Wyświetlaj błędy na stronie</label>
                      </div>
                      <div class="form-text text-danger">Tylko dla deweloperów! Nie włączaj na produkcji.</div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="debug_error_level" class="form-label">Poziom raportowania błędów</label>
                    <select class="form-select" id="debug_error_level" name="debug_error_level">
                      <option value="E_ALL" <?= ($debugErrorLevel ?? 'E_ALL') === 'E_ALL' ? 'selected' : '' ?>>E_ALL (wszystkie bledy)</option>
                      <option value="E_ALL_NO_DEPRECATED" <?= ($debugErrorLevel ?? 'E_ALL') === 'E_ALL_NO_DEPRECATED' ? 'selected' : '' ?>>E_ALL bez DEPRECATED</option>
                      <option value="E_ALL_NO_NOTICE" <?= ($debugErrorLevel ?? 'E_ALL') === 'E_ALL_NO_NOTICE' ? 'selected' : '' ?>>E_ALL bez NOTICE</option>
                      <option value="E_ERROR_WARNING" <?= ($debugErrorLevel ?? 'E_ALL') === 'E_ERROR_WARNING' ? 'selected' : '' ?>>Tylko ERROR i WARNING</option>
                      <option value="E_ERROR" <?= ($debugErrorLevel ?? 'E_ALL') === 'E_ERROR' ? 'selected' : '' ?>>Tylko ERROR (krytyczne)</option>
                    </select>
                    <div class="form-text">Określ, które typy błędów mają być raportowane</div>
                  </div>

                  <?php if (!empty($debugLogPath)): ?>
                  <div class="alert bg-secondary-subtle border border-secondary text-secondary-emphasis" role="alert">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                      <div>
                        <strong>Plik logow:</strong> <code><?= htmlspecialchars(basename($debugLogPath)) ?></code>
                        <span class="text-muted ms-2">(<?= htmlspecialchars($debugLogSizeFormatted ?? '0 B') ?>)</span>
                      </div>
                      <a href="<?= $basePath ?>/admin/index.php?action=logs&log=php_errors" class="btn btn-sm btn-outline-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-text me-1" viewBox="0 0 16 16">
                          <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5M5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1z"/>
                          <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1"/>
                        </svg>
                        Przegladaj logi
                      </a>
                    </div>
                  </div>
                  <?php endif; ?>

                </div>

                <!-- Tab: Powiadomienia Email -->
                <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">

                  <h5 class="mb-4">Konfiguracja SMTP</h5>

                  <div class="alert bg-info-subtle border border-info text-info-emphasis" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">
                      <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                      <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                    </svg>
                    Skonfiguruj serwer SMTP, aby otrzymywać automatyczne powiadomienia email z podsumowaniem statystyk.
                  </div>

                  <div class="mb-3">
                    <label for="smtp_host" class="form-label">Host SMTP</label>
                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($smtpHost ?? '') ?>" placeholder="smtp.gmail.com" />
                    <div class="form-text">Adres serwera SMTP (np. smtp.gmail.com, smtp.sendgrid.net)</div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="smtp_port" class="form-label">Port</label>
                      <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?= (int)($smtpPort ?? 587) ?>" placeholder="587" />
                      <div class="form-text">Zazwyczaj 587 (TLS) lub 465 (SSL)</div>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label for="smtp_encryption" class="form-label">Szyfrowanie</label>
                      <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                        <option value="tls" <?= ($smtpEncryption ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= ($smtpEncryption ?? 'tls') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                      </select>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="smtp_username" class="form-label">Użytkownik SMTP</label>
                    <input type="text" class="form-control" id="smtp_username" name="smtp_username" value="<?= htmlspecialchars($smtpUsername ?? '') ?>" placeholder="your-email@gmail.com" />
                    <div class="form-text">Nazwa użytkownika do autoryzacji SMTP</div>
                  </div>

                  <div class="mb-3">
                    <label for="smtp_password" class="form-label">Hasło SMTP</label>
                    <input type="password" class="form-control" id="smtp_password" name="smtp_password" placeholder="Pozostaw puste, aby nie zmieniać" autocomplete="new-password" />
                    <div class="form-text">Hasło jest przechowywane w zaszyfrowanej formie (AES-256)</div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="smtp_from_email" class="form-label">Email nadawcy</label>
                      <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" value="<?= htmlspecialchars($smtpFromEmail ?? '') ?>" placeholder="noreply@example.com" />
                    </div>

                    <div class="col-md-6 mb-3">
                      <label for="smtp_from_name" class="form-label">Nazwa nadawcy</label>
                      <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" value="<?= htmlspecialchars($smtpFromName ?? 'RedirectCMS') ?>" placeholder="RedirectCMS" />
                    </div>
                  </div>

                  <div class="mb-4">
                    <button type="button" class="btn btn-outline-primary" id="testSmtpButton">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16">
                        <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
                        <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                      </svg>
                      Testuj połączenie SMTP
                    </button>
                    <div id="smtpTestResult" class="mt-2"></div>
                  </div>

                  <hr class="my-4" />

                  <h5 class="mb-4">Ustawienia powiadomień</h5>

                  <div class="mb-3">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="notification_enabled" name="notification_enabled" value="1" <?= !empty($notificationEnabled) ? 'checked' : '' ?> />
                      <label class="form-check-label" for="notification_enabled">
                        <strong>Włącz automatyczne powiadomienia email</strong>
                      </label>
                    </div>
                    <div class="form-text">Otrzymuj regularne podsumowania statystyk na podany adres email</div>
                  </div>

                  <div class="mb-3">
                    <label for="notification_email" class="form-label">Adres email do powiadomień</label>
                    <input type="email" class="form-control" id="notification_email" name="notification_email" value="<?= htmlspecialchars($notificationEmail ?? '') ?>" placeholder="admin@example.com" />
                  </div>

                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label for="notification_frequency" class="form-label">Częstotliwość wysyłania</label>
                      <select class="form-select" id="notification_frequency" name="notification_frequency">
                        <option value="1" <?= ($notificationFrequency ?? '1') === '1' ? 'selected' : '' ?>>Codziennie</option>
                        <option value="3" <?= ($notificationFrequency ?? '1') === '3' ? 'selected' : '' ?>>Co 3 dni</option>
                        <option value="7" <?= ($notificationFrequency ?? '1') === '7' ? 'selected' : '' ?>>Co 7 dni</option>
                        <option value="14" <?= ($notificationFrequency ?? '1') === '14' ? 'selected' : '' ?>>Co 14 dni</option>
                        <option value="30" <?= ($notificationFrequency ?? '1') === '30' ? 'selected' : '' ?>>Co 30 dni</option>
                        <option value="custom" <?= ($notificationFrequency ?? '1') === 'custom' ? 'selected' : '' ?>>Własna częstotliwość</option>
                      </select>
                    </div>

                    <div class="col-md-4 mb-3" id="customDaysContainer" style="<?= ($notificationFrequency ?? '1') === 'custom' ? '' : 'display: none;' ?>">
                      <label for="notification_custom_days" class="form-label">Co ile dni</label>
                      <input type="number" class="form-control" id="notification_custom_days" name="notification_custom_days" value="<?= (int)($notificationCustomDays ?? 7) ?>" min="1" max="365" />
                      <div class="form-text">Od 1 do 365</div>
                    </div>

                    <div class="col-md-4 mb-3">
                      <label for="notification_time" class="form-label">Godzina wysyłania</label>
                      <input type="time" class="form-control" id="notification_time" name="notification_time" value="<?= htmlspecialchars($notificationTime ?? '09:00') ?>" />
                      <div class="form-text">Format 24-godzinny (w strefie czasowej ustawionej w zakładce Ogólne)</div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="notification_top_links" class="form-label">Liczba linków w podsumowaniu</label>
                      <input type="number" class="form-control" id="notification_top_links" name="notification_top_links" value="<?= (int)($notificationTopLinks ?? 10) ?>" min="1" max="50" />
                      <div class="form-text">Od 1 do 50</div>
                    </div>
                  </div>

                  <?php if (!empty($notificationLastSent)): ?>
                    <div class="alert bg-secondary-subtle border border-secondary text-secondary-emphasis" role="alert">
                      Ostatnie powiadomienie wysłano: <strong><?= htmlspecialchars($notificationLastSent) ?></strong>
                    </div>
                  <?php endif; ?>

                  <div class="mt-4">
                    <button type="button" class="btn btn-outline-success" id="testNotificationButton">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-check" viewBox="0 0 16 16">
                        <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/>
                        <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-1.993-1.679a.5.5 0 0 0-.686.172l-1.17 1.95-.547-.547a.5.5 0 0 0-.708.708l.774.773a.75.75 0 0 0 1.174-.144l1.335-2.226a.5.5 0 0 0-.172-.686"/>
                      </svg>
                      Wyślij testowe podsumowanie
                    </button>
                    <div id="notificationTestResult" class="mt-2"></div>
                    <div class="form-text">Kliknij aby wysłać przykładowe podsumowanie statystyk na podany adres email</div>
                  </div>

                </div>
              </div>


                <!-- Tab: Licencja -->
                <div class="tab-pane fade" id="license" role="tabpanel" aria-labelledby="license-tab">
                  <?php
                    $__ls    = $licenseStatus ?? ['state' => 'no_key', 'message' => '', 'since' => null];
                    $__state = $__ls['state'];
                    $__key   = defined('RCMS_LICENSE_KEY') ? RCMS_LICENSE_KEY : '';
                    $__hasKey = preg_match('/^[a-f0-9]{64}$/', $__key);
                    $__maskedKey = $__hasKey
                      ? (substr($__key, 0, 8) . str_repeat('•', 48) . substr($__key, -8))
                      : '';
                    $__supportUntil = is_string($__ls['support_until'] ?? null) ? $__ls['support_until'] : null;
                    $__supportActive = $__ls['support_active'] ?? null;
                    $__supportDaysLeft = isset($__ls['support_days_left']) ? (int)$__ls['support_days_left'] : null;
                    $__supportLabel = 'Brak danych';
                    $__supportBadgeClass = 'secondary';

                    if ($__supportUntil !== null) {
                      $supportTs = strtotime($__supportUntil . ' UTC');
                      if ($supportTs !== false) {
                        $formattedSupportDate = date('d.m.Y H:i', $supportTs);
                        if ($__supportActive === true) {
                          $__supportLabel = 'Aktywne do ' . $formattedSupportDate;
                          if ($__supportDaysLeft !== null) {
                            $__supportLabel .= ' (ok. ' . $__supportDaysLeft . ' dni)';
                          }
                          $__supportBadgeClass = 'success';
                        } elseif ($__supportActive === false) {
                          $__supportLabel = 'Wygasło ' . $formattedSupportDate;
                          $__supportBadgeClass = 'warning';
                        } else {
                          $__supportLabel = 'Do ' . $formattedSupportDate;
                          $__supportBadgeClass = 'secondary';
                        }
                      }
                    }

                    $__stateLabels = [
                        'active'          => ['success', 'Aktywna'],
                        'silent'          => ['warning', 'Weryfikacja nieudana (okres karencji)'],
                        'warning'         => ['warning', 'Weryfikacja nieudana – wymagana uwaga'],
                        'blocked'         => ['danger',  'Zablokowana – wymagana interwencja'],
                        'api_unreachable' => ['secondary','API niedostępne – ostatni znany stan'],
                        'no_key'          => ['danger',  'Brak klucza licencyjnego'],
                    ];
                    [$__badgeClass, $__stateLabel] = $__stateLabels[$__state] ?? ['secondary', $__state];
                  ?>

                  <h5 class="mb-4">Informacje o licencji</h5>
                  <p class="text-muted small mb-3">Status licencji i data wsparcia są cache'owane i odświeżane automatycznie nie częściej niż raz na 24 godziny.</p>

                  <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3 p-md-4">
                      <div class="row g-3">
                        <div class="col-12 col-md-6">
                          <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small mb-1">Stan licencji</div>
                            <div>
                              <span class="badge text-bg-<?= $__badgeClass ?> fs-6"><?= $__stateLabel ?></span>
                            </div>
                            <?php if (!empty($__ls['since'])): ?>
                              <div class="small text-muted mt-2">Problemy wykryte od: <?= date('d.m.Y H:i', (int)$__ls['since']) ?></div>
                            <?php endif; ?>
                          </div>
                        </div>

                        <div class="col-12 col-md-6">
                          <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small mb-1">Wsparcie techniczne</div>
                            <div>
                              <span class="badge text-bg-<?= $__supportBadgeClass ?> fs-6"><?= htmlspecialchars($__supportLabel) ?></span>
                            </div>
                          </div>
                        </div>

                        <div class="col-12">
                          <div class="border rounded-3 p-3 bg-light-subtle">
                            <div class="text-muted small mb-1">Klucz licencyjny</div>
                            <?php if ($__hasKey): ?>
                              <div class="bg-white border rounded-2 p-2">
                                <code id="licenseKeyValue" class="small d-block text-break" style="letter-spacing:.05em;" data-masked-key="<?= htmlspecialchars($__maskedKey, ENT_QUOTES, 'UTF-8') ?>" data-visible="false"><?= htmlspecialchars($__maskedKey, ENT_QUOTES, 'UTF-8') ?></code>
                              </div>
                              <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="toggleLicenseKeyBtn" aria-controls="licenseKeyValue" aria-expanded="false">Pokaż pełny klucz</button>
                              <div id="licenseKeyError" class="small text-danger mt-2 d-none" role="alert"></div>
                            <?php else: ?>
                              <span class="text-danger fw-semibold">Brak</span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <?php
                  // ── Opis stanu + instrukcja działania ────────────────────────────────
                  $__stateDescriptions = [
                      'active' => [
                          'class' => 'success',
                          'title' => 'Licencja aktywna',
                          'body'  => 'Klucz licencyjny został pozytywnie zweryfikowany. Aplikacja działa bez ograniczeń.',
                          'steps' => [],
                      ],
                      'silent' => [
                          'class' => 'warning',
                          'title' => 'Problemy z weryfikacją — okres ciszy (0–24h)',
                          'body'  => 'Ostatnia weryfikacja licencji zakończyła się niepowodzeniem. Aplikacja działa normalnie — masz do <strong>24 godzin</strong> na wyjaśnienie sytuacji zanim pojawią się ostrzeżenia.',
                          'steps' => [
                              'Sprawdź, czy serwer ma dostęp do internetu. Wejdź na stronę <code>redirectcms.pl</code>. Jeśli strona działa, serwer ma połączenie z internetem.',
                              'Upewnij się, że domena aplikacji nie zmieniła się od czasu zakupu.',
                              'Jeśli problem nie ustępuje, skontaktuj się z pomocą techniczną.',
                          ],
                      ],
                      'warning' => [
                          'class' => 'warning',
                          'title' => 'Problemy z weryfikacją — wymagana uwaga (24–48h)',
                          'body'  => 'Weryfikacja licencji jest nieudana od ponad <strong>24 godzin</strong>. Za mniej niż 24 godziny zostaną nałożone ograniczenia na dodawanie i edycję linków oraz dostęp do panelu.',
                          'steps' => [
                              'Sprawdź, czy serwer ma dostęp do internetu. Wejdź na stronę <code>redirectcms.pl</code>. Jeśli strona działa, serwer ma połączenie z internetem.',
                              'Upewnij się, że klucz licencyjny w pliku <code>config/license.php</code> jest poprawny.',
                              'Jeśli zmieniałeś domenę aplikacji, skontaktuj się z pomocą techniczną w celu przepisania licencji.',
                              'W nagłym przypadku użyj pliku recovery (pobierz z panelu klienta) aby ponownie zainstalować klucz.',
                          ],
                      ],
                      'blocked' => [
                          'class' => 'danger',
                          'title' => 'Licencja zablokowana (48h+)',
                          'body'  => 'Weryfikacja licencji jest nieudana od ponad <strong>48 godzin</strong>. Dostęp do panelu oraz możliwość dodawania i edycji linków są zablokowane.',
                          'steps' => [
                              'Pobierz plik recovery licencji z panelu klienta na stronie sprzedaży.',
                              'Umieść pobrany plik recovery licencji w głównym katalogu aplikacji (obok <code>index.php</code>).',
                              'Odśwież stronę — aplikacja automatycznie zainstaluje klucz i odblokuje dostęp.',
                              'Jeśli plik recovery nie pomaga, skontaktuj się z pomocą techniczną.',
                          ],
                      ],
                      'api_unreachable' => [
                          'class' => 'secondary',
                          'title' => 'API niedostępne — brak zmian stanu',
                          'body'  => 'Serwer weryfikacji licencji jest chwilowo niedostępny. <strong>Nie wpływa to na działanie aplikacji</strong> — weryfikacja zostanie ponowiona automatycznie po 24 godzinach.',
                          'steps' => [
                              'Brak wymaganych działań — jest to stan tymczasowy.',
                              'Jeśli problem utrzymuje się długo, sprawdź połączenie serwera z internetem lub skontaktuj się z pomocą techniczną.',
                          ],
                      ],
                      'no_key' => [
                          'class' => 'warning',
                          'title' => 'Brak klucza licencyjnego',
                          'body'  => 'Aplikacja nie posiada przypisanego klucza licencyjnego. Klucz jest nadawany automatycznie przy pobraniu aplikacji ze strony sprzedaży.',
                          'steps' => [
                              'Zaloguj się do <strong>panelu klienta</strong> na stronie sprzedaży.',
                              'Przejdź do szczegółów swojej transakcji i kliknij <em>Pobierz plik recovery licencji</em>.',
                              'Umieść pobrany plik recovery licencji w <strong>głównym katalogu aplikacji</strong> (obok <code>index.php</code>).',
                              'Odśwież stronę — aplikacja automatycznie wykryje, zainstaluje i usunie plik.',
                          ],
                      ],
                  ];
                  $__desc = $__stateDescriptions[$__state] ?? $__stateDescriptions['api_unreachable'];
                  ?>

                  <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3 p-md-4">
                      <div class="alert alert-<?= $__desc['class'] ?> mb-0" role="alert">
                        <div class="fw-semibold mb-2"><?= $__desc['title'] ?></div>
                        <div class="small"><?= $__desc['body'] ?></div>
                    <?php if (!empty($__desc['steps'])): ?>
                    <ol class="mb-0 mt-3 small">
                      <?php foreach ($__desc['steps'] as $__step): ?>
                        <li><?= $__step ?></li>
                      <?php endforeach; ?>
                    </ol>
                    <?php endif; ?>
                      </div>
                    </div>
                  </div>

                </div>

              <!-- End Tab content -->

              
              <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 pt-3 border-top">
                <a href="<?= $basePath ?>/admin/index.php" class="btn btn-secondary">Anuluj</a>
                <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <script>
    function toggleHomeModeOptions() {
      const mode = document.getElementById('home_mode').value;
      const landingOptions = document.getElementById('landing-options');
      const redirectOptions = document.getElementById('redirect-options');
      const blogOptions = document.getElementById('blog-options');

      // Ukryj wszystkie opcje
      [landingOptions, redirectOptions, blogOptions].forEach(el => el && el.classList.remove('active'));

      // Pokaż odpowiednie opcje
      if (mode === 'landing' && landingOptions) {
        landingOptions.classList.add('active');
      } else if (mode === 'redirect' && redirectOptions) {
        redirectOptions.classList.add('active');
      } else if (mode === 'blog' && blogOptions) {
        blogOptions.classList.add('active');
      }
    }

    function toggleTrashOptions() {
      const mode = document.getElementById('trash_mode').value;
      const daysContainer = document.getElementById('trash-auto-delete-options');
      daysContainer.style.display = mode === 'auto_delete' ? '' : 'none';
    }

    // Wywolaj na starcie aby ustawic prawidlowa widocznosc
    document.addEventListener('DOMContentLoaded', function() {
      toggleHomeModeOptions();
      toggleTrashOptions();

      // Obsługa zapamiętywania aktywnej zakładki
      const activeTabInput = document.getElementById('activeTabInput');
      const settingsTabs = document.getElementById('settingsTabs');

      // Sprawdź czy jest zapisana zakładka z poprzedniej sesji
      const savedTab = '<?= htmlspecialchars($activeTab ?? '') ?>';
      if (savedTab && savedTab !== '') {
        // Usuń active z wszystkich zakładek
        const allTabs = settingsTabs.querySelectorAll('.nav-link');
        const allPanes = document.querySelectorAll('.tab-pane');

        allTabs.forEach(tab => {
          tab.classList.remove('active');
          tab.setAttribute('aria-selected', 'false');
        });

        allPanes.forEach(pane => {
          pane.classList.remove('show', 'active');
        });

        // Aktywuj zapisaną zakładkę
        const savedTabButton = document.getElementById(savedTab + '-tab');
        const savedTabPane = document.getElementById(savedTab);

        if (savedTabButton && savedTabPane) {
          savedTabButton.classList.add('active');
          savedTabButton.setAttribute('aria-selected', 'true');
          savedTabPane.classList.add('show', 'active');
          activeTabInput.value = savedTab;
        }
      }

      // Nasłuchuj na zmiany zakładek i zapisuj aktywną
      if (settingsTabs) {
        settingsTabs.addEventListener('click', function(e) {
          const target = e.target.closest('.nav-link');
          if (target) {
            const tabId = target.getAttribute('aria-controls');
            if (tabId && activeTabInput) {
              activeTabInput.value = tabId;
            }
          }
        });
      }

      const licenseKeyValue = document.getElementById('licenseKeyValue');
      const toggleLicenseKeyBtn = document.getElementById('toggleLicenseKeyBtn');
      const licenseKeyError = document.getElementById('licenseKeyError');

      if (licenseKeyValue && toggleLicenseKeyBtn) {
        toggleLicenseKeyBtn.addEventListener('click', async function() {
          const isVisible = licenseKeyValue.dataset.visible === 'true';
          const maskedKey = licenseKeyValue.dataset.maskedKey || '';
          if (licenseKeyError) {
            licenseKeyError.classList.add('d-none');
            licenseKeyError.textContent = '';
          }

          if (isVisible) {
            licenseKeyValue.textContent = maskedKey;
            licenseKeyValue.dataset.visible = 'false';
            toggleLicenseKeyBtn.textContent = 'Pokaż pełny klucz';
            toggleLicenseKeyBtn.setAttribute('aria-expanded', 'false');
            return;
          }

          toggleLicenseKeyBtn.disabled = true;
          toggleLicenseKeyBtn.textContent = 'Pobieranie...';

          try {
            const csrfInput = document.querySelector('input[name="csrf"]');
            const csrf = csrfInput ? csrfInput.value : '';
            const formData = new FormData();
            formData.append('csrf', csrf);

            const basePath = <?= json_encode($basePath, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const response = await fetch(basePath + '/admin/index.php?action=reveal_license_key', {
              method: 'POST',
              body: formData,
              credentials: 'same-origin'
            });

            const contentType = (response.headers.get('content-type') || '').toLowerCase();
            const isJsonResponse = contentType.includes('application/json');
            const result = isJsonResponse ? await response.json() : null;

            if (!response.ok) {
              const fallbackMessage = 'Sesja wygasła lub serwer zwrócił nieprawidłową odpowiedź. Odśwież stronę i spróbuj ponownie.';
              throw new Error((result && typeof result.error === 'string' && result.error !== '') ? result.error : fallbackMessage);
            }

            if (!result || result.success !== true || typeof result.license_key !== 'string') {
              throw new Error((result && result.error) ? result.error : 'Nie udało się pobrać klucza.');
            }

            licenseKeyValue.textContent = result.license_key;
            licenseKeyValue.dataset.visible = 'true';
            toggleLicenseKeyBtn.textContent = 'Ukryj klucz';
            toggleLicenseKeyBtn.setAttribute('aria-expanded', 'true');
          } catch (error) {
            licenseKeyValue.textContent = maskedKey;
            licenseKeyValue.dataset.visible = 'false';
            if (licenseKeyError) {
              licenseKeyError.textContent = (error && error.message) ? error.message : 'Nie udało się pobrać klucza.';
              licenseKeyError.classList.remove('d-none');
            }
            toggleLicenseKeyBtn.textContent = 'Pokaż pełny klucz';
            toggleLicenseKeyBtn.setAttribute('aria-expanded', 'false');
          } finally {
            toggleLicenseKeyBtn.disabled = false;
          }
        });
      }

      const customFieldsTableBody = document.querySelector('#customFieldsTable tbody');
      const addCustomFieldButton = document.getElementById('addCustomFieldRow');

      const escapeValue = (value) => String(value ?? '').replace(/"/g, '&quot;');

      function addCustomFieldRow(label = '', key = '', type = 'text', placeholder = '', step = '') {
        if (!customFieldsTableBody) return;
        const row = document.createElement('tr');
        row.innerHTML = `
          <td><input type="text" name="custom_field_label[]" class="form-control" value="${escapeValue(label)}" placeholder="np. Średnia ocena" required /></td>
          <td><input type="text" name="custom_field_key[]" class="form-control" value="${escapeValue(key)}" placeholder="np. avg_rating" required /></td>
          <td>
            <select name="custom_field_type[]" class="form-select">
              <option value="text" ${type === 'text' ? 'selected' : ''}>Tekst</option>
              <option value="number" ${type === 'number' ? 'selected' : ''}>Liczba</option>
            </select>
          </td>
          <td><input type="text" name="custom_field_placeholder[]" class="form-control" value="${escapeValue(placeholder)}" placeholder="np. 199 PLN" /></td>
          <td><input type="text" name="custom_field_step[]" class="form-control" value="${escapeValue(step)}" placeholder="0.1" /></td>
          <td class="text-end">
            <button type="button" class="btn btn-link text-danger p-0 remove-custom-field" aria-label="Usuń">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 2.5a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-1 0v-10a.5.5 0 0 1 .5-.5m5 0a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-1 0v-10a.5.5 0 0 1 .5-.5m5 0a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-1 0v-10a.5.5 0 0 1 .5-.5"/>
                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
              </svg>
            </button>
          </td>
        `;
        customFieldsTableBody.appendChild(row);
      }

      if (addCustomFieldButton && customFieldsTableBody) {
        addCustomFieldButton.addEventListener('click', function() {
          addCustomFieldRow('', '', 'text', '', '');
        });

        customFieldsTableBody.addEventListener('click', function(event) {
          const removeBtn = event.target.closest('.remove-custom-field');
          if (removeBtn) {
            const row = removeBtn.closest('tr');
            if (row) {
              row.remove();
            }
          }
        });
      }

      // Test SMTP
      const testSmtpButton = document.getElementById('testSmtpButton');
      if (testSmtpButton) {
        testSmtpButton.addEventListener('click', async function() {
          const button = this;
          const resultDiv = document.getElementById('smtpTestResult');

          button.disabled = true;
          button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Testowanie...';
          resultDiv.innerHTML = '';

          try {
            const formData = new FormData();
            formData.append('csrf', document.querySelector('input[name="csrf"]').value);
            formData.append('smtp_host', document.getElementById('smtp_host').value);
            formData.append('smtp_port', document.getElementById('smtp_port').value);
            formData.append('smtp_username', document.getElementById('smtp_username').value);
            formData.append('smtp_password', document.getElementById('smtp_password').value);
            formData.append('smtp_encryption', document.getElementById('smtp_encryption').value);

            const basePath = '<?= $basePath ?>';
            const response = await fetch(basePath + '/admin/index.php?action=testSmtp', {
              method: 'POST',
              body: formData
            });

            const result = await response.json();

            resultDiv.innerHTML = `
              <div class="alert alert-${result.success ? 'success' : 'danger'} mb-0" role="alert">
                ${result.message}
              </div>
            `;
          } catch (error) {
            resultDiv.innerHTML = `
              <div class="alert bg-danger-subtle border border-danger text-danger-emphasis mb-0" role="alert">
                Błąd połączenia: ${error.message}
              </div>
            `;
          } finally {
            button.disabled = false;
            button.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16">
                <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
                <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
              </svg>
              Testuj połączenie SMTP
            `;
          }
        });
      }

      // Test Notification Button
      const testNotificationButton = document.getElementById('testNotificationButton');
      if (testNotificationButton) {
        testNotificationButton.addEventListener('click', async function() {
          const button = this;
          const resultDiv = document.getElementById('notificationTestResult');
          const emailInput = document.getElementById('notification_email');

          if (!emailInput.value || emailInput.value.trim() === '') {
            resultDiv.innerHTML = `
              <div class="alert bg-warning-subtle border border-warning text-warning-emphasis mb-0" role="alert">
                Proszę podać adres email do powiadomień
              </div>
            `;
            return;
          }

          button.disabled = true;
          button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Wysyłanie...';
          resultDiv.innerHTML = '';

          try {
            const formData = new FormData();
            formData.append('csrf', document.querySelector('input[name="csrf"]').value);
            formData.append('notification_email', emailInput.value);
            formData.append('notification_top_links', document.getElementById('notification_top_links').value);
            formData.append('notification_frequency', document.getElementById('notification_frequency').value);
            formData.append('notification_custom_days', document.getElementById('notification_custom_days').value);

            const basePath = '<?= $basePath ?>';
            const response = await fetch(basePath + '/admin/index.php?action=testNotification', {
              method: 'POST',
              body: formData
            });

            const result = await response.json();

            resultDiv.innerHTML = `
              <div class="alert alert-${result.success ? 'success' : 'danger'} mb-0" role="alert">
                ${result.message}
              </div>
            `;
          } catch (error) {
            resultDiv.innerHTML = `
              <div class="alert bg-danger-subtle border border-danger text-danger-emphasis mb-0" role="alert">
                Błąd: ${error.message}
              </div>
            `;
          } finally {
            button.disabled = false;
            button.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-check" viewBox="0 0 16 16">
                <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/>
                <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-1.993-1.679a.5.5 0 0 0-.686.172l-1.17 1.95-.547-.547a.5.5 0 0 0-.708.708l.774.773a.75.75 0 0 0 1.174-.144l1.335-2.226a.5.5 0 0 0-.172-.686"/>
              </svg>
              Wyślij testowe podsumowanie
            `;
          }
        });
      }

      // Toggle custom days input based on frequency selection
      const frequencySelect = document.getElementById('notification_frequency');
      const customDaysContainer = document.getElementById('customDaysContainer');

      if (frequencySelect && customDaysContainer) {
        frequencySelect.addEventListener('change', function() {
          if (this.value === 'custom') {
            customDaysContainer.style.display = '';
          } else {
            customDaysContainer.style.display = 'none';
          }
        });
      }


      // Aktualizuj wyświetlany czas przy zmianie strefy czasowej
      const timezoneSelect = document.getElementById('timezone');
      const userTimeDisplay = document.getElementById('userTimeDisplay');
      const userTimezoneDisplay = document.getElementById('userTimezoneDisplay');

      if (timezoneSelect && userTimeDisplay && userTimezoneDisplay) {
        timezoneSelect.addEventListener('change', function() {
          const selectedTz = this.value;
          userTimezoneDisplay.textContent = selectedTz;

          // Oblicz aktualny czas w wybranej strefie czasowej
          try {
            const now = new Date();
            const options = {
              timeZone: selectedTz,
              year: 'numeric',
              month: '2-digit',
              day: '2-digit',
              hour: '2-digit',
              minute: '2-digit',
              second: '2-digit',
              hour12: false
            };
            const formatter = new Intl.DateTimeFormat('sv-SE', options);
            const parts = formatter.formatToParts(now);

            let year, month, day, hour, minute, second;
            parts.forEach(part => {
              if (part.type === 'year') year = part.value;
              if (part.type === 'month') month = part.value;
              if (part.type === 'day') day = part.value;
              if (part.type === 'hour') hour = part.value;
              if (part.type === 'minute') minute = part.value;
              if (part.type === 'second') second = part.value;
            });

            userTimeDisplay.textContent = `${year}-${month}-${day} ${hour}:${minute}:${second}`;
          } catch (e) {
            console.error('Błąd konwersji strefy czasowej:', e);
          }
        });
      }
    });
  </script>
</body>
</html>
