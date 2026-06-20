<?php
  $pageTitle = 'Import linków z CSV — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <!-- Zawartość główna -->
  <div class="container py-5">
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
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h2 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-earmark-arrow-up" viewBox="0 0 16 16" style="margin-right: 8px;">
                <path d="M8.5 11.5a.5.5 0 0 1-1 0V7.707L6.354 8.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 7.707z"/>
                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
              </svg>
              Import linków z CSV
            </h2>
          </div>
          <div class="card-body">
            <!-- Informacje o formacie -->
            <div class="alert bg-info-subtle border border-info text-info-emphasis" role="alert">
              <h5 class="alert-heading">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">
                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                  <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                </svg>
                Format pliku CSV
              </h5>
              <p class="mb-2"><strong>Separator:</strong> średnik (;) lub przecinek (,) — wykrywany automatycznie</p>
              <p class="mb-2"><strong>Wymagane kolumny (minimum):</strong> Alias + URL docelowy</p>
              <p class="mb-0">
                <strong>Wskazówka:</strong> Po wybraniu pliku zobaczysz podgląd danych i możliwość przypisania kolumn.
                Możesz też pobrać <a href="<?= $basePath ?>/admin/index.php?action=export_links" class="alert-link">szablon CSV</a>.
              </p>
            </div>

            <?php if (!empty($_SESSION['import_errors'])): ?>
              <div class="alert bg-warning-subtle border border-warning text-warning-emphasis alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">Szczegóły błędów importu:</h5>
                <ul class="mb-0" style="max-height: 300px; overflow-y: auto;">
                  <?php foreach ($_SESSION['import_errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                  <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
              </div>
              <?php unset($_SESSION['import_errors']); ?>
            <?php endif; ?>

            <!-- Krok 1: Wybór pliku -->
            <div id="step1">
              <h5 class="mb-3">Krok 1: Wybierz plik CSV</h5>
              <div class="mb-3">
                <input type="file" class="form-control" id="csv_file_picker" accept=".csv" />
                <div class="form-text">Maksymalny rozmiar pliku: <?= ini_get('upload_max_filesize') ?></div>
              </div>
              <div class="mb-3">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="has_header" checked />
                  <label class="form-check-label" for="has_header">Pierwszy wiersz zawiera nagłówki</label>
                </div>
              </div>
            </div>

            <!-- Krok 2: Podgląd i mapowanie kolumn -->
            <div id="step2" style="display: none;">
              <h5 class="mb-3">Krok 2: Przypisz kolumny</h5>
              <p class="text-muted mb-3">Przypisz kolumny z pliku CSV do pól w systemie. Kolumny <strong>Alias</strong> i <strong>URL docelowy</strong> są wymagane.</p>

              <div id="mappingRow" class="d-flex flex-wrap gap-2 mb-3"></div>

              <h6 class="mb-2">Podgląd danych (pierwsze 5 wierszy):</h6>
              <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered" id="previewTable">
                  <thead class="table-light" id="previewHead"></thead>
                  <tbody id="previewBody"></tbody>
                </table>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="update_existing" />
                    <label class="form-check-label" for="update_existing">Aktualizuj istniejące linki (wg aliasu)</label>
                  </div>
                </div>
              </div>

              <div id="mappingError" class="alert alert-danger" style="display: none;"></div>

              <!-- Formularz importu (ukryty, wypełniany przez JS) -->
              <form method="post" action="<?= $basePath ?>/admin/index.php?action=import_links" enctype="multipart/form-data" id="importForm">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                <input type="hidden" name="column_mapping" id="columnMappingInput" value="" />
                <input type="hidden" name="skip_first_row" id="skipFirstRowInput" value="1" />
                <input type="hidden" name="update_existing" id="updateExistingInput" value="0" />
                <input type="file" name="csv_file" id="csv_file_hidden" style="display: none;" />

                <div class="d-flex justify-content-between">
                  <button type="button" class="btn btn-secondary" onclick="resetImport()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                    </svg>
                    Wróć
                  </button>
                  <button type="submit" class="btn btn-primary" id="importBtn" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16">
                      <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                      <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/>
                    </svg>
                    Importuj <span id="rowCountBadge" class="badge bg-light text-primary ms-1"></span>
                  </button>
                </div>
              </form>
            </div>

            <!-- Przyciski kroku 1 -->
            <div id="step1Buttons">
              <div class="d-flex justify-content-end">
                <a href="<?= $basePath ?>/admin/index.php?action=export_links" class="btn btn-outline-primary">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                  </svg>
                  Pobierz szablon
                </a>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <script>
    // Toast notifications
    <?php if (!empty($_SESSION['toast_message'])): ?>
      const toastEl = document.getElementById('liveToast');
      const toastBody = document.getElementById('toastMessage');
      const toastMessage = <?= json_encode($_SESSION['toast_message']) ?>;
      const toastType = <?= json_encode($_SESSION['toast_type'] ?? 'info') ?>;

      toastBody.textContent = toastMessage;

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

    // CSV Import z podglądem i mapowaniem kolumn
    (function() {
      const FIELDS = [
        { key: '', label: '— Pomiń —' },
        { key: 'slug', label: 'Alias *', required: true },
        { key: 'target_url', label: 'URL docelowy *', required: true },
        { key: 'page_title', label: 'Tytuł strony' },
        { key: 'page_description', label: 'Opis strony' },
        { key: 'category', label: 'Kategoria (nazwa)' },
        { key: 'affiliate_program', label: 'Program afiliacyjny (nazwa)' },
        { key: 'delay_seconds', label: 'Opóźnienie (sekundy)' },
        { key: 'publish_at', label: 'Data publikacji' },
        { key: 'expires_at', label: 'Data wygaśnięcia' },
        { key: 'tags', label: 'Tagi (oddzielone przecinkami)' },
      ];

      // Auto-mapping: nagłówek CSV -> klucz pola
      const HEADER_MAP = {
        'id': '', 'slug': 'slug', 'tytuł strony': 'page_title', 'tytul strony': 'page_title',
        'page_title': 'page_title', 'url docelowy': 'target_url', 'target_url': 'target_url',
        'url': 'target_url', 'kategoria': 'category', 'category': 'category',
        'program afiliacyjny': 'affiliate_program', 'affiliate_program': 'affiliate_program',
        'opóźnienie (sekundy)': 'delay_seconds', 'opoznienie (sekundy)': 'delay_seconds',
        'delay_seconds': 'delay_seconds', 'delay': 'delay_seconds',
        'data utworzenia': '', 'created_at': '', 'data publikacji': 'publish_at',
        'publish_at': 'publish_at', 'data wygaśnięcia': 'expires_at', 'data wygasniecia': 'expires_at',
        'expires_at': 'expires_at', 'opis strony': 'page_description',
        'page_description': 'page_description', 'tagi': 'tags', 'tags': 'tags',
      };

      let parsedRows = [];
      let totalRows = 0;

      function parseCSV(text) {
        // Detect separator
        const firstLine = text.split('\n')[0] || '';
        const sep = (firstLine.split(';').length > firstLine.split(',').length) ? ';' : ',';

        const rows = [];
        let current = '';
        let inQuotes = false;

        for (let i = 0; i < text.length; i++) {
          const ch = text[i];
          if (inQuotes) {
            if (ch === '"' && text[i + 1] === '"') {
              current += '"';
              i++;
            } else if (ch === '"') {
              inQuotes = false;
            } else {
              current += ch;
            }
          } else {
            if (ch === '"') {
              inQuotes = true;
            } else if (ch === sep) {
              rows.length === 0 && rows.push([]);
              rows[rows.length - 1].push(current);
              current = '';
            } else if (ch === '\n' || (ch === '\r' && text[i + 1] === '\n')) {
              if (rows.length === 0) rows.push([]);
              rows[rows.length - 1].push(current);
              current = '';
              if (ch === '\r') i++;
              rows.push([]);
            } else {
              current += ch;
            }
          }
        }
        // Last field
        if (rows.length === 0) rows.push([]);
        rows[rows.length - 1].push(current);

        // Filter empty rows
        return rows.filter(r => r.some(c => c.trim() !== ''));
      }

      function escapeHtml(s) {
        const div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
      }

      function guessMapping(headers) {
        return headers.map(h => {
          const key = h.trim().toLowerCase();
          return HEADER_MAP[key] ?? '';
        });
      }

      function buildMappingDropdowns(colCount, headers) {
        const mapping = headers ? guessMapping(headers) : new Array(colCount).fill('');
        const container = document.getElementById('mappingRow');
        container.innerHTML = '';

        for (let i = 0; i < colCount; i++) {
          const wrapper = document.createElement('div');
          wrapper.className = 'flex-fill';
          wrapper.style.minWidth = '140px';

          const label = document.createElement('label');
          label.className = 'form-label small text-muted';
          label.textContent = headers ? headers[i] : `Kolumna ${i + 1}`;

          const select = document.createElement('select');
          select.className = 'form-select form-select-sm mapping-select';
          select.dataset.colIndex = i;

          FIELDS.forEach(f => {
            const opt = document.createElement('option');
            opt.value = f.key;
            opt.textContent = f.label;
            if (mapping[i] === f.key) opt.selected = true;
            select.appendChild(opt);
          });

          select.addEventListener('change', validateMapping);

          wrapper.appendChild(label);
          wrapper.appendChild(select);
          container.appendChild(wrapper);
        }

        validateMapping();
      }

      function validateMapping() {
        const selects = document.querySelectorAll('.mapping-select');
        const chosen = {};
        let hasSlug = false;
        let hasUrl = false;
        let duplicateError = '';

        selects.forEach(sel => {
          const val = sel.value;
          if (val === '') return;
          if (chosen[val]) {
            duplicateError = `Pole "${FIELDS.find(f => f.key === val).label}" jest przypisane do więcej niż jednej kolumny.`;
          }
          chosen[val] = true;
          if (val === 'slug') hasSlug = true;
          if (val === 'target_url') hasUrl = true;
        });

        const errorDiv = document.getElementById('mappingError');
        const importBtn = document.getElementById('importBtn');

        if (duplicateError) {
          errorDiv.textContent = duplicateError;
          errorDiv.style.display = 'block';
          importBtn.disabled = true;
          return;
        }

        if (!hasSlug || !hasUrl) {
          errorDiv.textContent = 'Musisz przypisać kolumny Alias i URL docelowy.';
          errorDiv.style.display = 'block';
          importBtn.disabled = true;
          return;
        }

        errorDiv.style.display = 'none';
        importBtn.disabled = false;

        // Build mapping JSON
        const mapping = {};
        selects.forEach(sel => {
          if (sel.value) {
            mapping[sel.dataset.colIndex] = sel.value;
          }
        });
        document.getElementById('columnMappingInput').value = JSON.stringify(mapping);
      }

      function renderPreview(rows, hasHeader) {
        const dataRows = hasHeader ? rows.slice(1) : rows;
        const headerRow = hasHeader ? rows[0] : null;
        const previewRows = dataRows.slice(0, 5);
        const colCount = rows[0] ? rows[0].length : 0;

        totalRows = dataRows.length;
        document.getElementById('rowCountBadge').textContent = totalRows + ' wierszy';

        // Build column mapping dropdowns
        buildMappingDropdowns(colCount, headerRow);

        // Preview table header
        const thead = document.getElementById('previewHead');
        let headHtml = '<tr>';
        for (let i = 0; i < colCount; i++) {
          headHtml += '<th class="small">' + (headerRow ? escapeHtml(headerRow[i]) : 'Kol. ' + (i + 1)) + '</th>';
        }
        headHtml += '</tr>';
        thead.innerHTML = headHtml;

        // Preview table body
        const tbody = document.getElementById('previewBody');
        let bodyHtml = '';
        previewRows.forEach(row => {
          bodyHtml += '<tr>';
          for (let i = 0; i < colCount; i++) {
            const val = row[i] || '';
            const display = val.length > 50 ? escapeHtml(val.substring(0, 50)) + '...' : escapeHtml(val);
            bodyHtml += '<td class="small text-nowrap">' + display + '</td>';
          }
          bodyHtml += '</tr>';
        });
        if (dataRows.length > 5) {
          bodyHtml += '<tr><td colspan="' + colCount + '" class="text-center text-muted small">... i jeszcze ' + (dataRows.length - 5) + ' wierszy</td></tr>';
        }
        tbody.innerHTML = bodyHtml;
      }

      // File picker handler
      document.getElementById('csv_file_picker').addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        // Copy file to hidden input
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('csv_file_hidden').files = dt.files;

        const reader = new FileReader();
        reader.onload = function(e) {
          const text = e.target.result;
          parsedRows = parseCSV(text);

          if (parsedRows.length < 2) {
            alert('Plik CSV jest pusty lub zawiera tylko nagłówki.');
            return;
          }

          const hasHeader = document.getElementById('has_header').checked;
          document.getElementById('skipFirstRowInput').value = hasHeader ? '1' : '0';

          renderPreview(parsedRows, hasHeader);

          document.getElementById('step1').style.display = 'none';
          document.getElementById('step1Buttons').style.display = 'none';
          document.getElementById('step2').style.display = 'block';
        };
        reader.readAsText(file, 'UTF-8');
      });

      // Re-render preview when header checkbox changes
      document.getElementById('has_header').addEventListener('change', function() {
        if (parsedRows.length > 0) {
          document.getElementById('skipFirstRowInput').value = this.checked ? '1' : '0';
          renderPreview(parsedRows, this.checked);
        }
      });

      // Sync update_existing checkbox
      document.getElementById('update_existing').addEventListener('change', function() {
        document.getElementById('updateExistingInput').value = this.checked ? '1' : '0';
      });

      // Reset to step 1
      window.resetImport = function() {
        document.getElementById('step1').style.display = 'block';
        document.getElementById('step1Buttons').style.display = 'flex';
        document.getElementById('step2').style.display = 'none';
        document.getElementById('csv_file_picker').value = '';
        parsedRows = [];
      };
    })();
  </script>
</body>
</html>
