<?php
  $pageTitle = ($mode === 'create' ? 'Nowa kategoria' : 'Edycja kategorii') . ' — RedirectCMS';
  $extraHead = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/mdbassit/Coloris@latest/dist/coloris.min.css" />';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header bg-info text-white">
            <h2 class="mb-0"><?= $mode === 'create' ? 'Nowa kategoria' : 'Edycja kategorii' ?></h2>
          </div>
          <div class="card-body">
            <form method="post" enctype="multipart/form-data" action="<?= $mode === 'create' ? $basePath . '/admin/index.php?action=category_new' : $basePath . '/admin/index.php?action=category_edit&id=' . (int)$category['id'] ?>">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />

              <div class="mb-3">
                <label for="name" class="form-label">Nazwa *</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" required />
              </div>

              <div class="mb-3">
                <label for="slug" class="form-label">Alias</label>
                <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($category['slug']) ?>" placeholder="zostanie wygenerowany automatycznie jeśli puste" maxlength="100" />
                <small class="form-text text-muted">Max 100 znaków</small>
                <small class="form-text text-muted">Unikalna nazwa używana w URL (np. dla trybu blog)</small>
              </div>

              <div class="mb-3">
                <label for="description" class="form-label">Opis</label>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Opcjonalny opis kategorii"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
              </div>

              <div class="mb-3">
                <label for="color" class="form-label">Kolor</label>
                <input type="text" class="form-control coloris-input" id="color" name="color" value="<?= htmlspecialchars($category['color']) ?>" data-coloris required />
                <small class="form-text text-muted d-block mt-2">Kliknij aby otworzyć color picker, wpisz ręcznie lub użyj palet kolorów</small>
              </div>

              <div class="mb-3">
                <label for="category_icon" class="form-label">Ikona kategorii</label>
                <?php if ($mode === 'edit' && !empty($category['icon_image'])): ?>
                  <div class="mb-2">
                    <img src="<?= $basePath . '/' . htmlspecialchars($category['icon_image_thumb'] ?? $category['icon_image']) ?>" alt="Obecna ikona" class="img-thumbnail" style="max-width: 150px; max-height: 150px;" />
                    <div class="form-check mt-2">
                      <input type="checkbox" class="form-check-input" id="delete_icon" name="delete_icon" value="1" />
                      <label class="form-check-label" for="delete_icon">Usuń obecną ikonę</label>
                    </div>
                  </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="category_icon" name="category_icon" accept="image/jpeg,image/png,image/gif,image/webp" />
                <small class="form-text text-muted d-block mt-1">Maksymalny rozmiar: 1200x1200px. Zostanie przeskalowana do 800x800px (ikona) i 400x400px (miniatura). Formaty: JPEG, PNG, GIF, WebP.</small>
              </div>

              <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="<?= $basePath ?>/admin/index.php?action=categories" class="btn btn-secondary">Anuluj</a>
                <button type="submit" class="btn btn-primary">Zapisz</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/gh/mdbassit/Coloris@latest/dist/coloris.min.js"></script>
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
      
      // Dodaj # na początku jeśli brakuje
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
        e.target.setCustomValidity('Wprowadź poprawny kolor w formacie HEX (np. #ff0000)');
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
