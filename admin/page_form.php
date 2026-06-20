<?php
  $pageTitle = ($isEdit ? 'Edycja strony' : 'Nowa strona') . ' — RedirectCMS';
  $extraHead = '<link rel="stylesheet" href="https://unpkg.com/trix@2.1.6/dist/trix.css">';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-12 col-xl-10">

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <div class="card shadow-sm">
          <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h2 class="mb-0 fs-5">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-file-earmark-text me-2" viewBox="0 0 16 16">
                <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 0 1-1z"/>
              </svg>
              <?= $isEdit ? 'Edycja strony' : 'Nowa strona' ?>
            </h2>
            <div class="d-flex gap-2">
              <?php if ($isEdit): ?>
                <a href="<?= $basePath ?>/?page=<?= htmlspecialchars($page['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-light">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-box-arrow-up-right me-1" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
                    <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
                  </svg>
                  Podgląd
                </a>
              <?php endif; ?>
              <a href="<?= $basePath ?>/admin/index.php?action=pages" class="btn btn-sm btn-outline-light">Powrót</a>
            </div>
          </div>

          <div class="card-body">
            <form method="post"
                  action="<?= $basePath ?>/admin/index.php?action=<?= $isEdit ? 'page_edit&id=' . (int)$page['id'] : 'page_new' ?>"
                  id="pageForm">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

              <div class="row g-3">

                <!-- Tytuł -->
                <div class="col-12">
                  <label for="page_title" class="form-label fw-medium">Tytuł strony <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="page_title" name="title"
                         value="<?= htmlspecialchars($page['title']) ?>"
                         placeholder="np. O nas" maxlength="255" required>
                </div>

                <!-- Slug -->
                <div class="col-md-6">
                  <label for="page_slug" class="form-label fw-medium">Slug (URL) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text text-muted small">/?page=</span>
                    <input type="text" class="form-control font-monospace" id="page_slug" name="slug"
                           value="<?= htmlspecialchars($page['slug']) ?>"
                           placeholder="o-nas" maxlength="191" required
                           pattern="[a-z0-9\-]+" title="Tylko małe litery, cyfry i myślniki">
                  </div>
                  <div class="form-text">Używane w URL strony. Tylko małe litery, cyfry i myślniki.</div>
                </div>

                <!-- Status -->
                <div class="col-md-3">
                  <label for="page_status" class="form-label fw-medium">Status</label>
                  <select class="form-select" id="page_status" name="status">
                    <option value="draft" <?= ($page['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Szkic</option>
                    <option value="published" <?= ($page['status'] ?? '') === 'published' ? 'selected' : '' ?>>Opublikowana</option>
                  </select>
                </div>

                <!-- Opcje -->
                <div class="col-md-3 d-flex flex-column justify-content-end gap-2">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="show_in_nav" name="show_in_nav"
                           <?= !empty($page['show_in_nav']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="show_in_nav">Pokaż w nawigacji</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="use_theme" name="use_theme"
                           <?= !isset($page['use_theme']) || !empty($page['use_theme']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="use_theme">Użyj layoutu szablonu</label>
                  </div>
                </div>

                <!-- Treść (Trix) -->
                <div class="col-12">
                  <label class="form-label fw-medium">Treść strony</label>
                  <input type="hidden" id="content_html" name="content_html" value="<?= htmlspecialchars($page['content_html'] ?? '') ?>">
                  <trix-editor input="content_html" id="content_html_editor" placeholder="Wpisz treść strony..."></trix-editor>
                  <div class="form-text">Formatowanie tekstu, nagłówki, listy, linki i obrazki. Do zaawansowanego HTML/CSS użyj pola JavaScript poniżej.</div>
                </div>

                <!-- Własny JS -->
                <div class="col-12">
                  <label for="content_js" class="form-label fw-medium">
                    Własny JavaScript
                    <span class="badge bg-secondary fw-normal ms-1">opcjonalnie</span>
                  </label>
                  <textarea class="form-control font-monospace" id="content_js" name="content_js"
                            rows="6" spellcheck="false"
                            placeholder="// Opcjonalny kod JavaScript wykonyway na tej stronie&#10;// np. inicjalizacja mapy, formularza, animacji..."><?= htmlspecialchars($page['content_js'] ?? '') ?></textarea>
                  <div class="form-text text-warning-emphasis">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-exclamation-triangle me-1" viewBox="0 0 16 16">
                      <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                    </svg>
                    Kod JS jest wykonywany po stronie klienta — używaj ostrożnie i tylko z zaufanych źródeł.
                  </div>
                </div>

                <!-- SEO -->
                <div class="col-12">
                  <hr class="my-1">
                  <h6 class="text-muted mt-2 mb-3">SEO (opcjonalnie)</h6>
                </div>
                <div class="col-md-6">
                  <label for="meta_title" class="form-label">Meta tytuł</label>
                  <input type="text" class="form-control" id="meta_title" name="meta_title"
                         value="<?= htmlspecialchars($page['meta_title'] ?? '') ?>"
                         placeholder="Pozostaw puste — użyje tytułu strony" maxlength="255">
                </div>
                <div class="col-md-6">
                  <label for="meta_description" class="form-label">Meta opis</label>
                  <input type="text" class="form-control" id="meta_description" name="meta_description"
                         value="<?= htmlspecialchars($page['meta_description'] ?? '') ?>"
                         placeholder="Krótki opis dla wyszukiwarek" maxlength="500">
                </div>

              </div><!-- /row -->

              <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="<?= $basePath ?>/admin/index.php?action=pages" class="btn btn-secondary">Anuluj</a>
                <button type="submit" name="status_submit" value="draft" class="btn btn-outline-primary">Zapisz jako szkic</button>
                <button type="submit" name="status_submit" value="published" class="btn btn-primary">Opublikuj</button>
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

    // Auto-slug z tytułu (tylko przy tworzeniu nowej strony)
    <?php if (!$isEdit): ?>
    const titleInput = document.getElementById('page_title');
    const slugInput  = document.getElementById('page_slug');
    let slugManuallyEdited = <?= !empty($page['slug']) ? 'true' : 'false' ?>;

    slugInput.addEventListener('input', () => { slugManuallyEdited = true; });

    titleInput.addEventListener('input', function() {
      if (slugManuallyEdited) return;
      const slug = this.value
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/ą/g,'a').replace(/ć/g,'c').replace(/ę/g,'e')
        .replace(/ł/g,'l').replace(/ń/g,'n').replace(/ó/g,'o')
        .replace(/ś/g,'s').replace(/ź/g,'z').replace(/ż/g,'z')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
      slugInput.value = slug;
    });
    <?php endif; ?>

    // Status z przycisków submit (Zapisz jako szkic / Opublikuj)
    document.querySelectorAll('[name="status_submit"]').forEach(btn => {
      btn.addEventListener('click', function() {
        document.getElementById('page_status').value = this.value;
      });
    });

    // Upload obrazków przez Trix
    document.addEventListener('trix-attachment-add', function(event) {
      const attachment = event.attachment;
      if (!attachment || !attachment.file) return;

      const formData = new FormData();
      formData.append('csrf', csrfToken);
      formData.append('image', attachment.file);

      fetch(basePath + '/admin/index.php?action=upload_image_ajax', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          const url = data.url || (basePath + '/' + data.path);
          attachment.setAttributes({ url: url, href: url });
        } else {
          attachment.remove();
          alert('Nie udało się przesłać obrazka: ' + (data.error || 'Nieznany błąd'));
        }
      })
      .catch(err => {
        console.error('Upload Trix error', err);
        attachment.remove();
        alert('Nie udało się przesłać obrazka');
      });
    });
  </script>
</body>
</html>
