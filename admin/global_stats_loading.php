<?php
  $pageTitle = 'Generowanie statystyk — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light global-stats-loading">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-5">
    <div class="loading-container" id="loadingContainer">
      <div class="spinner-border spinner-large text-primary" role="status">
        <span class="visually-hidden">Ładowanie...</span>
      </div>
      <h3 class="status-message" id="statusMessage">
        Generowanie statystyk globalnych...
      </h3>
      <p class="text-muted mt-2" id="statusDetails">
        Cache <?= htmlspecialchars($reason) ?>. Proszę czekać, to może potrwać kilka sekund.
      </p>
      <div class="progress">
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
      </div>
    </div>

    <div class="error-message alert bg-danger-subtle border border-danger text-danger-emphasis d-none" id="errorMessage">
      <h5 class="alert-heading">Błąd generowania statystyk</h5>
      <p id="errorDetails"></p>
      <hr>
      <p class="mb-0">
        <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/index.php?action=global_stats&days=<?= (int)$days ?>&exclude_bots=<?= $excludeBots ? 1 : 0 ?>" class="btn btn-primary">
          Spróbuj ponownie
        </a>
      </p>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <script>
    // Automatyczne uruchomienie generowania cache
    (function() {
      const basePath = <?= json_encode($basePath, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
      const days = <?= json_encode($days, JSON_NUMERIC_CHECK) ?>;
      const excludeBots = <?= json_encode($excludeBots ? 1 : 0, JSON_NUMERIC_CHECK) ?>;
      const csrf = '<?= htmlspecialchars($csrf) ?>';
      
      const statusMessage = document.getElementById('statusMessage');
      const statusDetails = document.getElementById('statusDetails');
      const loadingContainer = document.getElementById('loadingContainer');
      const errorMessage = document.getElementById('errorMessage');
      const errorDetails = document.getElementById('errorDetails');
      
      // Rozpocznij generowanie
      const startTime = Date.now();
      
      fetch(basePath + '/admin/index.php?action=refresh_global_cache', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          csrf: csrf,
          days: days,
          exclude_bots: excludeBots
        })
      })
      .then(response => {
        if (!response.ok) {
          return response.json().then(data => {
            throw new Error(data.error || 'Nieznany błąd serwera');
          });
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          const duration = ((Date.now() - startTime) / 1000).toFixed(2);
          statusMessage.textContent = 'Statystyki wygenerowane pomyślnie!';
          statusDetails.textContent = `Czas generowania: ${duration}s`;
          
          // Przekieruj po krótkiej chwili
          setTimeout(() => {
            const url = basePath + '/admin/index.php?action=global_stats&days=' + days + '&exclude_bots=' + excludeBots;
            window.location.href = url;
          }, 500);
        } else {
          throw new Error(data.error || 'Nieznany błąd');
        }
      })
      .catch(error => {
        console.error('Błąd:', error);
        loadingContainer.classList.add('d-none');
        errorMessage.classList.remove('d-none');
        errorDetails.textContent = error.message || 'Wystąpił nieoczekiwany błąd podczas generowania statystyk.';
      });
    })();
  </script>
</body>
</html>
