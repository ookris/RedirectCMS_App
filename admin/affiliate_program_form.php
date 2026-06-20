<?php
  $pageTitle = ($mode === 'create' ? 'Nowy program afiliacyjny' : 'Edycja programu afiliacyjnego') . ' — RedirectCMS';
  $extraHead = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/mdbassit/Coloris@0.24.0/dist/coloris.min.css" />';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header bg-info text-white">
            <h2 class="mb-0"><?= $mode === 'create' ? 'Nowy program afiliacyjny' : 'Edycja programu afiliacyjnego' ?></h2>
          </div>
          <div class="card-body">
            <form method="post" action="<?= $mode === 'create' ? $basePath . '/admin/index.php?action=affiliate_program_new' : $basePath . '/admin/index.php?action=affiliate_program_edit&id=' . (int)$program['id'] ?>">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />

              <div class="mb-3">
                <label for="name" class="form-label">Nazwa programu *</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($program['name']) ?>" required />
              </div>

              <div class="mb-3">
                <label for="color" class="form-label">Kolor</label>
                <input type="text" class="form-control coloris-input" id="color" name="color" value="<?= htmlspecialchars($program['color'] ?? '#4CAF50') ?>" data-coloris required />
                <small class="form-text text-muted d-block mt-2">Kliknij aby otworzyć color picker, wpisz ręcznie lub użyj palet kolorów</small>
              </div>

              <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="<?= $basePath ?>/admin/index.php?action=affiliate_programs" class="btn btn-secondary">Anuluj</a>
                <button type="submit" class="btn btn-primary">Zapisz</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/gh/mdbassit/Coloris@0.24.0/dist/coloris.min.js"></script>
  <script>
    const colorInput = document.getElementById('color');

    // Inicjalizuj Coloris z opcjami
    Coloris({
      el: '.coloris-input',
      theme: 'light',
      margin: 8,
      borderRadius: 6,
      format: 'hex',
      closeButton: true,
      swatches: [
        '#005f73ff',
        '#0a9396ff',
        '#94d2bdff',
        '#e9d8a6ff',
        '#ee9b00ff',
        '#ca6702ff',
        '#bb3e03ff',
        '#ae2012ff',
        '#9b2226ff',
        '#ff595eff',
        '#ffca3aff',
        '#8ac926ff',
        '#1982c4ff',
        '#6a4c93ff',
        '#3d5a80ff',
        '#98c1d9ff',
        '#e0fbfcff',
        '#ee6c4dff',
        '#293241ff',
        '#064789ff',
        '#427aa1ff',
        '#ebf2faff',
        '#679436ff',
        '#a5be00ff'
      ]
    });

    // Walidacja koloru przy wpisywaniu
    colorInput.addEventListener('input', function(e) {
      let value = e.target.value.trim();

      // Dodaj # na poczatku jesli brakuje
      if (value && !value.startsWith('#')) {
        value = '#' + value;
        e.target.value = value;
      }

      // Waliduj format HEX
      if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
        e.target.setCustomValidity('');
      } else if (value === '') {
        e.target.setCustomValidity('');
      } else {
        e.target.setCustomValidity('Wprowadz poprawny kolor w formacie HEX (np. #ff0000)');
      }
    });

    // Normalizuj wartość przed wysłaniem formularza
    document.querySelector('form').addEventListener('submit', function(e) {
      let value = colorInput.value.trim().toUpperCase();
      if (value && !value.startsWith('#')) {
        value = '#' + value;
      }
      colorInput.value = value;
    });
  </script>
</body>
</html>
