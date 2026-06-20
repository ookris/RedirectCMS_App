<?php
$pageTitle = 'Optymalizacja obrazów — RedirectCMS';
require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
<?php require_once __DIR__ . '/static/navbar.php'; ?>


<div class="container py-4" style="max-width: 900px;">

  <h2 class="mb-1">Optymalizacja obrazów</h2>
  <p class="text-muted mb-4">Wybierz sterownik kompresji używany podczas regenerowania miniatur. Zmiany są stosowane przy kolejnym kliknięciu "Regeneruj miniatury" w zakładce <a href="?action=appearance">Wygląd</a>.</p>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle me-2" viewBox="0 0 16 16">
        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
        <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
      </svg>
      <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cpu me-2" viewBox="0 0 16 16">
        <path d="M5 0a.5.5 0 0 1 .5.5V2h1V.5a.5.5 0 0 1 1 0V2h1V.5a.5.5 0 0 1 1 0V2h1V.5a.5.5 0 0 1 1 0V2A2.5 2.5 0 0 1 14 4.5h1.5a.5.5 0 0 1 0 1H14v1h1.5a.5.5 0 0 1 0 1H14v1h1.5a.5.5 0 0 1 0 1H14v1h1.5a.5.5 0 0 1 0 1H14A2.5 2.5 0 0 1 11.5 14v1.5a.5.5 0 0 1-1 0V14h-1v1.5a.5.5 0 0 1-1 0V14h-1v1.5a.5.5 0 0 1-1 0V14h-1v1.5a.5.5 0 0 1-1 0V14A2.5 2.5 0 0 1 2 11.5H.5a.5.5 0 0 1 0-1H2v-1H.5a.5.5 0 0 1 0-1H2v-1H.5a.5.5 0 0 1 0-1H2v-1H.5a.5.5 0 0 1 0-1H2A2.5 2.5 0 0 1 4.5 2V.5A.5.5 0 0 1 5 0m-.5 3A1.5 1.5 0 0 0 3 4.5v7A1.5 1.5 0 0 0 4.5 13h7a1.5 1.5 0 0 0 1.5-1.5v-7A1.5 1.5 0 0 0 11.5 3zM5 6.5A1.5 1.5 0 0 1 6.5 5h3A1.5 1.5 0 0 1 11 6.5v3A1.5 1.5 0 0 1 9.5 11h-3A1.5 1.5 0 0 1 5 9.5zM6.5 6a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5z"/>
      </svg>
      Możliwości serwera
    </div>
    <div class="card-body">
      <?php
      $driverTooltips = [
          'gd'        => 'Zmniejsza jakość JPEG i zwiększa kompresję PNG względem wartości domyślnych. Wbudowany w PHP — zawsze dostępny.',
          'imagick'   => 'Usuwa metadane EXIF, generuje progresywne JPEG, lepiej kompresuje PNG i WebP.',
      ];
      $binaryTooltips = [
          'jpegoptim' => 'Bezstratna i stratna optymalizacja JPEG. Usuwa metadane EXIF, obsługuje progresywny zapis. Wymaga narzędzia jpegoptim zainstalowanego w systemie.',
          'pngquant'  => 'Stratna kompresja PNG przez redukcję palety kolorów. Duże oszczędności rozmiaru przy minimalnej utracie jakości. Wymaga narzędzia pngquant w systemie.',
          'optipng'   => 'Bezstratna reoptymalizacja PNG — lepsza kompresja bez utraty jakości. Wymaga narzędzia optipng zainstalowanego w systemie.',
      ];
      ?>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <?php foreach (['gd', 'imagick'] as $key): ?>
          <?php $cap = $capabilities[$key]; $available = !empty($cap['available']); ?>
          <span class="badge fs-6 fw-normal px-3 py-2 <?= $available ? 'bg-success-subtle border border-success text-success-emphasis' : 'bg-secondary-subtle border border-secondary text-secondary-emphasis' ?>"
                data-bs-toggle="tooltip" data-bs-placement="top"
                title="<?= htmlspecialchars($driverTooltips[$key] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($available): ?>
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check-lg me-1" viewBox="0 0 16 16">
                <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/>
              </svg>
            <?php else: ?>
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-x-lg me-1" viewBox="0 0 16 16">
                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
              </svg>
            <?php endif; ?>
            <?= htmlspecialchars($cap['label'], ENT_QUOTES, 'UTF-8') ?>
            <?php if ($key === $bestDriver && $available): ?>
              <span class="ms-1 opacity-75">(zalecany)</span>
            <?php endif; ?>
          </span>
        <?php endforeach; ?>

        <?php if (isset($capabilities['exec_tools']['binaries'])): ?>
          <span class="vr mx-1" style="align-self: stretch; opacity: .25;"></span>
          <span class="small text-muted">Narzędzia CLI:<?php if ($bestDriver === 'exec_tools' && !empty($capabilities['exec_tools']['available'])): ?> <span class="text-success">(zalecane)</span><?php endif; ?></span>
          <?php foreach ($capabilities['exec_tools']['binaries'] as $bin => $found): ?>
            <span class="badge fs-6 fw-normal px-3 py-2 <?= $found ? 'bg-success-subtle border border-success text-success-emphasis' : 'bg-secondary-subtle border border-secondary text-secondary-emphasis' ?>"
                  data-bs-toggle="tooltip" data-bs-placement="top"
                  title="<?= htmlspecialchars($binaryTooltips[$bin] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              <?php if ($found): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check-lg me-1" viewBox="0 0 16 16">
                  <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/>
                </svg>
              <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-x-lg me-1" viewBox="0 0 16 16">
                  <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                </svg>
              <?php endif; ?>
              <?= htmlspecialchars($bin, ENT_QUOTES, 'UTF-8') ?>
            </span>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header fw-semibold">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders me-2" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
      </svg>
      Ustawienia sterownika
    </div>
    <div class="card-body">
      <form method="POST" action="?action=image_optimizer">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

        <div class="mb-3">
          <label for="driverSelect" class="form-label fw-semibold">Sterownik optymalizacji</label>
          <select id="driverSelect" name="driver" class="form-select">
            <?php foreach ($capabilities as $key => $cap): ?>
              <?php if (empty($cap['available'])) continue; ?>
              <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                      <?= $currentDriver === $key ? 'selected' : '' ?>>
                <?= htmlspecialchars($cap['label'], ENT_QUOTES, 'UTF-8') ?>
                <?php if ($key === $bestDriver && $key !== 'none'): ?> — zalecany<?php endif; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div id="driverDescription" class="alert bg-success-subtle border border-success text-success-emphasis py-2 small mb-4">
          <?php foreach ($capabilities as $key => $cap): ?>
            <span class="driver-desc" data-driver="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                  style="display: <?= $currentDriver === $key ? 'block' : 'none' ?>">
              <?= htmlspecialchars($cap['description'], ENT_QUOTES, 'UTF-8') ?>
            </span>
          <?php endforeach; ?>
        </div>

        <?php
          // Helper: render a single range row
          // $id: input name/id, $label: display label, $val: current value, $min/$max: range, $unit: suffix string
          function renderRange(string $id, string $label, int $val, int $min, int $max, string $unit = ''): void { ?>
            <div class="mb-3">
              <label for="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" class="form-label d-flex justify-content-between">
                <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="text-body-secondary">
                  <strong id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>_val"><?= $val ?></strong><?= htmlspecialchars($unit, ENT_QUOTES, 'UTF-8') ?>
                  <span class="ms-2 text-muted small">(<?= $min ?>–<?= $max ?>)</span>
                </span>
              </label>
              <input type="range" class="form-range quality-range" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
                     name="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" min="<?= $min ?>" max="<?= $max ?>" value="<?= $val ?>">
            </div>
          <?php }
        ?>

        <!-- Per-driver quality settings (show/hide via JS) -->
        <div class="driver-settings-panel text-muted small mb-4 py-1"
             data-driver="none"
             style="display: <?= $currentDriver === 'none' ? 'block' : 'none' ?>">
          Brak dodatkowych ustawień — obrazy zapisywane są przez GD ze standardową jakością (JPEG 88%, PNG poziom&nbsp;8).
        </div>

        <div class="driver-settings-panel border rounded p-3 mb-4 bg-body-tertiary"
             data-driver="gd"
             style="display: <?= $currentDriver === 'gd' ? 'block' : 'none' ?>">
          <p class="fw-semibold mb-3 small text-muted text-uppercase letter-spacing-1">Ustawienia GD</p>
          <div class="row g-0">
            <div class="col-md-6 pe-md-3">
              <?php renderRange('gd_jpeg_quality',    'Jakość JPEG',          (int)($driverSettings['gd']['jpeg_quality']    ?? 78), 1, 100, '%') ?>
            </div>
            <div class="col-md-6 ps-md-3">
              <?php renderRange('gd_png_compression', 'Poziom kompresji PNG', (int)($driverSettings['gd']['png_compression'] ?? 9),  0, 9,   '/9') ?>
            </div>
          </div>
        </div>

        <div class="driver-settings-panel border rounded p-3 mb-4 bg-body-tertiary"
             data-driver="imagick"
             style="display: <?= $currentDriver === 'imagick' ? 'block' : 'none' ?>">
          <p class="fw-semibold mb-3 small text-muted text-uppercase letter-spacing-1">Ustawienia Imagick</p>
          <div class="row g-0">
            <div class="col-md-4 pe-md-3">
              <?php renderRange('imagick_jpeg_quality', 'Jakość JPEG', (int)($driverSettings['imagick']['jpeg_quality'] ?? 82), 1, 100, '%') ?>
            </div>
            <div class="col-md-4 px-md-3">
              <?php renderRange('imagick_png_quality',  'Jakość PNG',  (int)($driverSettings['imagick']['png_quality']  ?? 85), 1, 100, '%') ?>
            </div>
            <div class="col-md-4 ps-md-3">
              <?php renderRange('imagick_webp_quality', 'Jakość WebP', (int)($driverSettings['imagick']['webp_quality'] ?? 82), 1, 100, '%') ?>
            </div>
          </div>
        </div>

        <div class="driver-settings-panel border rounded p-3 mb-4 bg-body-tertiary"
             data-driver="exec_tools"
             style="display: <?= $currentDriver === 'exec_tools' ? 'block' : 'none' ?>">
          <p class="fw-semibold mb-3 small text-muted text-uppercase letter-spacing-1">Ustawienia narzędzi systemowych</p>
          <div class="row g-0">
            <div class="col-md-3 pe-md-3">
              <?php renderRange('exec_jpegoptim_max', 'jpegoptim —max',  (int)($driverSettings['exec_tools']['jpegoptim_max'] ?? 82), 1,  100, '%') ?>
            </div>
            <div class="col-md-3 px-md-2">
              <?php renderRange('exec_pngquant_min',  'pngquant min',    (int)($driverSettings['exec_tools']['pngquant_min']  ?? 65), 0,  100, '%') ?>
            </div>
            <div class="col-md-3 px-md-2">
              <?php renderRange('exec_pngquant_max',  'pngquant max',    (int)($driverSettings['exec_tools']['pngquant_max']  ?? 85), 0,  100, '%') ?>
            </div>
            <div class="col-md-3 ps-md-3">
              <?php renderRange('exec_optipng_level', 'optipng poziom',  (int)($driverSettings['exec_tools']['optipng_level'] ?? 2),  0,  7,   '') ?>
            </div>
          </div>
        </div>

        <?php if ($bestDriver !== 'none' && $currentDriver === 'none'): ?>
          <div class="alert bg-warning-subtle border border-warning text-warning-emphasis py-2 small mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-lightbulb me-1" viewBox="0 0 16 16">
              <path d="M2 6a6 6 0 1 1 10.174 4.31c-.203.196-.359.4-.453.619l-.762 1.769A.5.5 0 0 1 10.5 13a.5.5 0 0 1 0 1 .5.5 0 0 1 0 1l-.224.447a1 1 0 0 1-.894.553H6.618a1 1 0 0 1-.894-.553L5.5 15a.5.5 0 0 1 0-1 .5.5 0 0 1 0-1 .5.5 0 0 1-.46-.302l-.761-1.77a2 2 0 0 0-.453-.618A5.98 5.98 0 0 1 2 6m6-5a5 5 0 0 0-3.479 8.592c.263.254.514.564.676.941L5.83 12h4.342l.632-1.467c.162-.377.413-.687.676-.941A5 5 0 0 0 8 1"/>
            </svg>
            Zalecane: wybierz sterownik <strong><?= htmlspecialchars($capabilities[$bestDriver]['label'], ENT_QUOTES, 'UTF-8') ?></strong> dla lepszej kompresji.
          </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy me-2" viewBox="0 0 16 16">
            <path d="M11 2H9v3h2z"/>
            <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zm6 6.5v3h-6v-3a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 .5.5"/>
          </svg>
          Zapisz ustawienia
        </button>
      </form>
    </div>
  </div>


<?php if (!empty($imageSizes)): ?>
  <div class="card shadow-sm mt-4">
  <div class="card-header fw-semibold">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat me-2" viewBox="0 0 16 16">
      <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/>
      <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/>
    </svg>
    Wymusz ponowną optymalizację
  </div>
  <div class="card-body">
    <p class="text-muted small mb-3">
      Po zmianie ustawień jakości istniejące przycięte warianty nie są automatycznie aktualizowane (są pomijane, jeśli plik jest aktualny).
      Użyj tego przycisku, aby wymusić ponowne przetworzenie wszystkich obrazów z bieżącymi ustawieniami.
    </p>

    <p class="fw-semibold small mb-2">Wybierz rozmiary do przetworzenia:</p>
    <div class="d-flex flex-wrap gap-3 mb-3" id="sizeCheckboxes">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="sizeAll" checked>
        <label class="form-check-label fw-medium" for="sizeAll">Wszystkie</label>
      </div>
      <?php foreach ($imageSizes as $sizeKey => $size): ?>
      <div class="form-check size-check-item">
        <input class="form-check-input size-key-cb" type="checkbox"
               id="size_<?= htmlspecialchars($sizeKey, ENT_QUOTES, 'UTF-8') ?>"
               value="<?= htmlspecialchars($sizeKey, ENT_QUOTES, 'UTF-8') ?>"
               checked>
        <label class="form-check-label" for="size_<?= htmlspecialchars($sizeKey, ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($size['label'] ?? $sizeKey, ENT_QUOTES, 'UTF-8') ?>
          <span class="text-muted small">(<?= (int)$size['width'] ?>&times;<?= (int)$size['height'] ?>)</span>
        </label>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="d-flex align-items-center gap-3 mb-3">
      <button type="button" class="btn btn-warning" id="forceOptimizeBtn">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-arrow-repeat me-1" viewBox="0 0 16 16">
          <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/>
          <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/>
        </svg>
        Wymuś optymalizację
      </button>
      <span id="forceValidationMsg" class="small text-warning" style="display:none;">Wybierz co najmniej jeden rozmiar.</span>
    </div>

    <div id="forceProgressArea" style="display:none;">
      <div class="progress mb-2" style="height: 10px;">
        <div class="progress-bar progress-bar-striped progress-bar-animated"
             id="forceProgressBar" role="progressbar" style="width:100%"></div>
      </div>
      <p id="forceStatusText" class="small text-muted mb-2">Trwa przetwarzanie obrazów…</p>
      <div id="forceProgressStats" class="d-flex flex-wrap gap-3 small"></div>
    </div>
  </div>
  </div>
<?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Initialize Bootstrap tooltips
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

  const sel      = document.getElementById('driverSelect');
  const descs    = document.querySelectorAll('.driver-desc');
  const panels   = document.querySelectorAll('.driver-settings-panel');

  sel.addEventListener('change', () => {
    descs.forEach(d  => { d.style.display  = d.dataset.driver  === sel.value ? 'block' : 'none'; });
    panels.forEach(p => { p.style.display  = p.dataset.driver  === sel.value ? 'block' : 'none'; });
  });

  // Live value display for all range sliders
  document.querySelectorAll('.quality-range').forEach(input => {
    input.addEventListener('input', () => {
      const label = document.getElementById(input.id + '_val');
      if (label) label.textContent = input.value;
    });
  });

  // "Wszystkie" checkbox — master toggle
  const sizeAllCb   = document.getElementById('sizeAll');
  const sizeKeyCbs  = document.querySelectorAll('.size-key-cb');

  if (sizeAllCb) {
    sizeAllCb.addEventListener('change', () => {
      sizeKeyCbs.forEach(cb => { cb.checked = sizeAllCb.checked; });
    });
    sizeKeyCbs.forEach(cb => {
      cb.addEventListener('change', () => {
        sizeAllCb.checked = [...sizeKeyCbs].every(c => c.checked);
        sizeAllCb.indeterminate = !sizeAllCb.checked && [...sizeKeyCbs].some(c => c.checked);
      });
    });
  }

  // Force optimization button
  const forceBtn       = document.getElementById('forceOptimizeBtn');
  const forceProgArea  = document.getElementById('forceProgressArea');
  const forceProgBar   = document.getElementById('forceProgressBar');
  const forceProgStats = document.getElementById('forceProgressStats');
  const forceValMsg    = document.getElementById('forceValidationMsg');
  const btnOrigHTML    = forceBtn ? forceBtn.innerHTML : '';

  if (forceBtn) {
    forceBtn.addEventListener('click', async () => {
      const selected = [...sizeKeyCbs].filter(cb => cb.checked).map(cb => cb.value);
      if (selected.length === 0) {
        forceValMsg.style.display = 'inline';
        return;
      }
      forceValMsg.style.display = 'none';

      // Show animated progressbar
      const forceStatusText = document.getElementById('forceStatusText');
      forceProgArea.style.display = 'block';
      forceProgBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-warning';
      forceProgBar.style.width = '100%';
      forceStatusText.className = 'small text-muted mb-2';
      forceStatusText.textContent = 'Trwa przetwarzanie obrazów\u2026';
      forceProgStats.innerHTML = '';

      forceBtn.disabled = true;
      forceBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Przetwarzanie\u2026';

      try {
        const formData = new FormData();
        formData.append('csrf', '<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>');
        formData.append('force', '1');
        selected.forEach(k => formData.append('size_keys[]', k));

        const basePath = '<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>';
        const res  = await fetch(basePath + '/admin/index.php?action=regenerateImages', { method: 'POST', body: formData });
        const data = await res.json();

        // Stop animation, set final bar colour
        forceProgBar.classList.remove('progress-bar-striped', 'progress-bar-animated', 'bg-warning');

        if (data.success) {
          const total    = (data.processed ?? 0) + (data.skipped ?? 0) + (data.failed ?? 0);
          const hasError = (data.failed ?? 0) > 0;
          forceProgBar.classList.add(hasError ? 'bg-warning' : 'bg-success');
          forceStatusText.className = 'small fw-semibold ' + (hasError ? 'text-warning-emphasis' : 'text-success');
          forceStatusText.textContent = 'Gotowe!';
          forceProgStats.innerHTML =
            `<span class="text-muted">Łącznie: <strong class="text-body">${total}</strong></span>` +
            `<span class="text-success">Zoptymalizowano: <strong>${data.processed ?? 0}</strong></span>` +
            `<span class="text-secondary">Pominięte: <strong>${data.skipped ?? 0}</strong></span>` +
            ((data.failed ?? 0) > 0 ? `<span class="text-danger">Błędy: <strong>${data.failed}</strong></span>` : '');
        } else {
          forceProgBar.classList.add('bg-danger');
          forceStatusText.className = 'small text-danger fw-semibold mb-2';
          forceStatusText.textContent = data.message ?? 'Nieznany błąd';
          forceProgStats.innerHTML = '';
        }
      } catch (err) {
        forceProgBar.classList.remove('progress-bar-striped', 'progress-bar-animated', 'bg-warning');
        forceProgBar.classList.add('bg-danger');
        forceStatusText.className = 'small text-danger fw-semibold mb-2';
        forceStatusText.textContent = 'Błąd połączenia: ' + err.message;
        forceProgStats.innerHTML = '';
      } finally {
        forceBtn.disabled = false;
        forceBtn.innerHTML = btnOrigHTML;
      }
    });
  }
</script>
</body>
</html>
