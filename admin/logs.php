<?php
  $pageTitle = 'Logi Systemowe — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-4">
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-file-text me-2" viewBox="0 0 16 16">
              <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5M5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1z"/>
              <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1"/>
            </svg>
            Logi Systemowe
          </h2>
        </div>

        <div class="card">
          <div class="card-header bg-white">
            <div class="row align-items-center">
              <div class="col-md-6">
                <label for="logFileSelect" class="form-label mb-2"><strong>Wybierz plik logu:</strong></label>
                <select class="form-select" id="logFileSelect">
                  <?php foreach ($availableLogs as $logKey => $logInfo): ?>
                    <option value="<?= htmlspecialchars($logKey) ?>" <?= $selectedLog === $logKey ? 'selected' : '' ?>>
                      <?= htmlspecialchars($logInfo['name']) ?>
                      <?php if ($logInfo['exists']): ?>
                        (<?= htmlspecialchars($logInfo['size']) ?>)
                      <?php else: ?>
                        (brak pliku)
                      <?php endif; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label for="linesCount" class="form-label mb-2"><strong>Liczba linii:</strong></label>
                <select class="form-select" id="linesCount">
                  <option value="50" <?= $lines === 50 ? 'selected' : '' ?>>50</option>
                  <option value="100" <?= $lines === 100 ? 'selected' : '' ?>>100</option>
                  <option value="200" <?= $lines === 200 ? 'selected' : '' ?>>200</option>
                  <option value="500" <?= $lines === 500 ? 'selected' : '' ?>>500</option>
                  <option value="1000" <?= $lines === 1000 ? 'selected' : '' ?>>1000</option>
                </select>
              </div>
              <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-primary w-100" id="refreshLogsBtn">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise me-1" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                    <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                  </svg>
                  Odśwież
                </button>
              </div>
            </div>
          </div>
          <div class="card-footer bg-white py-2">
            <small class="text-muted">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-info-circle me-1" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
              </svg>
              Ścieżka: <code><?= htmlspecialchars($logFilePath) ?></code>
              <?php if ($logFileExists): ?>
                | Rozmiar: <strong><?= htmlspecialchars($logFileSize) ?></strong>
                | Ostatnia modyfikacja: <strong><?= htmlspecialchars($logFileModified) ?></strong>
              <?php endif; ?>
            </small>
          </div>
          <div class="card-body p-0">
            <?php if (!empty($logContent)): ?>
              <div class="log-viewer" id="logContent">
                <pre class="log-pre mb-0"><?php foreach ($logContent as $line): ?><div class="log-line"><?= $line ?></div>
<?php endforeach; ?></pre>
              </div>
            <?php elseif (!$logFileExists): ?>
              <div class="alert bg-warning-subtle border border-warning text-warning-emphasis m-3 mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-exclamation-triangle me-2" viewBox="0 0 16 16">
                  <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                  <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                </svg>
                Plik logu nie istnieje. Logi zostaną utworzone po pierwszym zapisie.
              </div>
            <?php else: ?>
              <div class="alert bg-info-subtle border border-info text-info-emphasis m-3 mb-0">
                Plik logu jest pusty.
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>

  <style>
    .log-viewer {
      max-height: calc(100vh - 350px);
      min-height: 400px;
      overflow-y: auto;
      background: #1e1e1e;
      color: #d4d4d4;
      font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
      font-size: 13px;
      line-height: 0.8em;
    }

    .log-pre {
      padding: 1rem;
      margin: 0;
      white-space: pre-wrap;
      word-wrap: break-word;
    }

    .log-line {
      padding: 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.02);
      border-left: 3px solid #736f67;
      padding-left: 2px;
    }

    .log-line:hover {
      background: rgba(255, 255, 255, 0.05);
    }

    /* Kolorowanie logów */
    .log-line:has(.log-error) {
      background: rgba(220, 38, 38, 0.1);
      border-left: 3px solid #dc2626;
      padding-left: 2px;
    }

    .log-line:has(.log-warning) {
      background: rgba(251, 191, 36, 0.1);
      border-left: 3px solid #fbbf24;
      padding-left: 2px;
    }

    .log-line:has(.log-success) {
      background: rgba(34, 197, 94, 0.1);
      border-left: 3px solid #22c55e;
      padding-left: 2px;
    }

    .log-line:has(.log-info) {
      background: rgba(59, 130, 246, 0.1);
      border-left: 3px solid #3b82f6;
      padding-left: 2px;
    }

    .log-error { color: #f87171; font-weight: bold; }
    .log-warning { color: #fbbf24; font-weight: bold; }
    .log-success { color: #4ade80; font-weight: bold; }
    .log-info { color: #60a5fa; }
    .log-timestamp { color: #9ca3af; }
    .log-pipe { color: #6b7280; }
    .log-number { color: #a78bfa; }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const logFileSelect = document.getElementById('logFileSelect');
      const linesCount = document.getElementById('linesCount');
      const refreshBtn = document.getElementById('refreshLogsBtn');
      const basePath = '<?= $basePath ?>';

      function loadLogs() {
        const logFile = logFileSelect.value;
        const lines = linesCount.value;
        window.location.href = `${basePath}/admin/index.php?action=logs&log=${encodeURIComponent(logFile)}&lines=${lines}`;
      }

      refreshBtn.addEventListener('click', loadLogs);
      logFileSelect.addEventListener('change', loadLogs);
      linesCount.addEventListener('change', loadLogs);

      // Auto-scroll do końca
      const logViewer = document.querySelector('.log-viewer');
      if (logViewer) {
        logViewer.scrollTop = logViewer.scrollHeight;
      }
    });
  </script>
  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
