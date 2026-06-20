<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= !empty($page_title) ? htmlspecialchars($page_title) : 'Przekierowanie…' ?></title>
  
  <?php if (!empty($page_description_plain ?? $page_description)): ?>
  <meta name="description" content="<?= htmlspecialchars($page_description_plain ?? strip_tags((string)$page_description)) ?>" />
  <?php endif; ?>
  
  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?= htmlspecialchars($share_url ?? '') ?>" />
  <meta property="og:title" content="<?= !empty($page_title) ? htmlspecialchars($page_title) : 'Przekierowanie' ?>" />
  <?php if (!empty($page_description_plain ?? $page_description)): ?>
  <meta property="og:description" content="<?= htmlspecialchars($page_description_plain ?? strip_tags((string)$page_description)) ?>" />
  <?php endif; ?>
  <?php if (!empty($og_image_url ?? '')): ?>
  <meta property="og:image" content="<?= htmlspecialchars($og_image_url) ?>" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <?php endif; ?>
  
  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image" />
  <meta property="twitter:url" content="<?= htmlspecialchars($share_url ?? '') ?>" />
  <meta property="twitter:title" content="<?= !empty($page_title) ? htmlspecialchars($page_title) : 'Przekierowanie' ?>" />
  <?php if (!empty($page_description_plain ?? $page_description)): ?>
  <meta property="twitter:description" content="<?= htmlspecialchars($page_description_plain ?? strip_tags((string)$page_description)) ?>" />
  <?php endif; ?>
  <?php if (!empty($og_image_url ?? '')): ?>
  <meta property="twitter:image" content="<?= htmlspecialchars($og_image_url) ?>" />
  <?php endif; ?>
  
  <?php 
    $refreshDelay = max(0, (int)$delay_seconds);
    $hasPassword = !empty($password_hash ?? null);
    $passwordGranted = $hasPassword ? ($password_granted ?? false) : true;
  ?>
  <?php if (empty($isCrawler) && $passwordGranted): ?>
  <meta http-equiv="refresh" content="<?= $refreshDelay ?>; url=<?= htmlspecialchars($target_url) ?>" />
  <?php endif; ?>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: system-ui, -apple-system, sans-serif;
        padding: 1rem;
    }
    .redirect-card {
        max-width: 1100px;
        width: 100%;
      margin: 0 auto;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      animation: fadeIn 0.5s ease-in;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .card-img-left {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 0.375rem 0 0 0.375rem;
        min-height: 400px;
    }
    .progress {
      height: 8px;
    }
    .countdown {
      font-size: 2rem;
      font-weight: 700;
      color: #667eea;
    }
      @media (max-width: 768px) {
        .card-img-left {
          min-height: 250px;
        }
      }
  </style>
  <?php if (!empty($redirectHeaderCode ?? '')) { echo $redirectHeaderCode; } ?>
</head>
<body>
  <div class="container">
    <div class="card redirect-card">
      <div class="row g-0">
        <?php if (!empty($og_image)): ?>
        <div class="col-md-5 d-none d-md-block">
          <img src="<?= htmlspecialchars($basePath . '/' . $og_image) ?>" class="card-img-left h-100" alt="<?= htmlspecialchars($page_title ?? 'Obraz') ?>" />
        </div>
        <?php endif; ?>
        <div class="<?= !empty($og_image) ? 'col-md-7' : 'col-12' ?>">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="text-primary mb-3" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
              </svg>
              <h4 class="card-title mb-2">Przekierowanie</h4>
              <?php if (!empty($page_title)): ?>
              <h5 class="text-muted"><?= htmlspecialchars($page_title) ?></h5>
              <?php endif; ?>
            </div>

            <?php if (!empty($page_description)): ?>
            <div class="card-text text-muted mb-3" style="word-wrap: break-word;"><?= Utils::sanitizeHtml($page_description) ?></div>
            <?php endif; ?>

            <?php if (!$passwordGranted): ?>
              <!-- Password form -->
              <div class="text-center my-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="text-warning mb-3" viewBox="0 0 16 16">
                  <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/>
                </svg>
                <h5 class="mb-3">Ten link jest chroniony hasłem</h5>
                <?php if (!empty($password_hint ?? null)): ?>
                  <p class="text-muted mb-3"><strong>Podpowiedź:</strong> <?= htmlspecialchars($password_hint) ?></p>
                <?php endif; ?>
              </div>
              
              <?php if (!empty($password_error ?? null)): ?>
                <div class="alert bg-danger-subtle border border-danger text-danger-emphasis" role="alert">
                  <?= htmlspecialchars($password_error) ?>
                </div>
              <?php endif; ?>

              <form method="post" action="">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf_token ?? '') ?>" />
                <input type="hidden" name="verify_password" value="1" />
                <div class="mb-3">
                  <label for="password_input" class="form-label">Wprowadź hasło:</label>
                  <input type="password" class="form-control form-control-lg" id="password_input" name="password" required autofocus />
                </div>
                <div class="d-grid">
                  <button type="submit" class="btn btn-primary btn-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                      <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2M3.5 9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-.5.5H4a.5.5 0 0 1-.5-.5z"/>
                    </svg>
                    Odblokuj
                  </button>
                </div>
              </form>
            <?php elseif (empty($isCrawler)): ?>
              <div class="text-center my-4">
                <div class="countdown mb-2"><span id="counter"><?= (int)$delay_seconds ?></span>s</div>
                <p class="text-muted small mb-3">Za chwilę nastąpi automatyczne przekierowanie</p>
                <div class="progress mb-3">
                  <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
                </div>
              </div>

              <div class="d-grid gap-2">
                <a href="<?= htmlspecialchars($target_url) ?>" class="btn btn-primary btn-lg" id="directLink">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                  </svg>
                  Przejdź teraz
                </a>
              </div>
            <?php else: ?>
              <p class="text-muted text-center">Podgląd karty udostępnienia</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if (empty($isCrawler)): ?>
  <script>
    (function(){
      var delay = <?= (int)$delay_seconds ?>;
      var totalDelay = delay;
      var slug = <?= json_encode($slug) ?>;
      var target = <?= json_encode($target_url) ?>;
      var counter = document.getElementById('counter');
      var progressBar = document.getElementById('progressBar');
      var eventToken = <?= json_encode($eventToken) ?>;
      var eventUrl = <?= json_encode(($basePath ? $basePath : '') . '/__event') ?>;
      
      function tick(){
        if (totalDelay <= 0) {
          // Brak odliczania przy natychmiastowym przekierowaniu (meta refresh=0)
          counter && (counter.textContent = '0');
          progressBar && (progressBar.style.width = '0%');
          return;
        }
        delay--;
        if (counter) counter.textContent = Math.max(delay, 0);
        var progress = totalDelay > 0 ? ((totalDelay - Math.max(delay,0)) / totalDelay) * 100 : 100;
        if (progressBar) progressBar.style.width = (100 - Math.min(progress, 100)) + '%';
        if (delay > 0) setTimeout(tick, 1000);
      }
      
      // Zgłoś kliknięcie wejścia na stronę
      try {
        navigator.sendBeacon(eventUrl, new Blob([JSON.stringify({slug: slug, type: 'click', referer: document.referrer, token: eventToken})], {type: 'application/json'}));
      } catch(e) {}

      // Zgłoś event "redirect" tuż przed meta-redirectem
      var delayMs = Math.max(0, totalDelay * 1000);
      var sendRedirect = function(){
        try {
          navigator.sendBeacon(eventUrl, new Blob([JSON.stringify({slug: slug, type: 'redirect', referer: document.referrer, token: eventToken})], {type: 'application/json'}));
        } catch(e) {}
      };
      if (delayMs > 0) {
        setTimeout(sendRedirect, Math.max(0, delayMs - 150));
      } else {
        // Przy 0s meta refresh przeglądarka może przełączyć stronę bardzo szybko;
        // wyślij beacon ASAP, jeśli się uda.
        setTimeout(sendRedirect, 0);
      }

      // Uruchom odliczanie tylko dla dodatnich opóźnień
      if (totalDelay > 0) {
        setTimeout(tick, 1000);
      } else {
        // Uporządkuj UI dla natychmiastowego przekierowania
        if (counter) counter.textContent = '0';
        if (progressBar) progressBar.style.width = '0%';
      }
    })();
  </script>
  <?php endif; ?>
  <?php if (!empty($redirectFooterCode ?? '')) { echo $redirectFooterCode; } ?>
</body>
</html>
