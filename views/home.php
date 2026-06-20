<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($homeTitle) ?> — RedirectCMS</title>
  <?php if (!empty($brandingFavicon ?? '')): ?>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($basePath . '/' . ltrim($brandingFavicon, '/')) ?>" />
  <?php endif; ?>
  <?php if (!empty($homeMetaDescription)): ?>
  <meta name="description" content="<?= htmlspecialchars($homeMetaDescription) ?>" />
  <?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #ffffff;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      color: #212529;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      transition: background-color 0.25s ease, color 0.25s ease;
    }

    body.dark {
      background-color: #0f172a;
      color: #e2e8f0;
    }
    
    .home-container {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    
    .home-header {
      background-color: #f8f9fa;
      border-bottom: 1px solid #e9ecef;
      padding: 48px 0;
      text-align: center;
      position: relative;
    }

    body.dark .home-header {
      background: linear-gradient(135deg, rgba(102,126,234,0.12), rgba(118,75,162,0.18));
      border-color: rgba(255,255,255,0.08);
    }
    
    .home-header h1 {
      font-size: 2.5rem;
      font-weight: 600;
      margin-bottom: 8px;
      color: #212529;
    }

    .brand-logo {
      max-height: 64px;
      max-width: 220px;
      object-fit: contain;
      margin-bottom: 12px;
    }
    
    .home-header .subtitle {
      font-size: 1.1rem;
      color: #6c757d;
      margin: 0;
      font-weight: 400;
    }
    
    .home-content {
      flex: 1;
      padding: 48px 0;
    }
    
    .info-section {
      background-color: #ffffff;
      padding: 0;
    }

    body.dark .info-section {
      background: transparent;
    }
    
    .info-section h2 {
      color: #212529;
      font-weight: 600;
      margin-bottom: 20px;
      font-size: 1.8rem;
    }

    body.dark .info-section h2 {
      color: #e2e8f0;
    }
    
    .info-section p {
      color: #495057;
      font-size: 1rem;
      line-height: 1.6;
      margin-bottom: 16px;
    }

    body.dark .info-section p {
      color: #cbd5e1;
    }
    
    
    .action-box {
      background-color: #f8f9fa;
      padding: 20px;
      border-radius: 6px;
      margin-top: 24px;
      text-align: center;
    }

    body.dark .action-box {
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.08);
    }
    
    .action-box p {
      margin: 0 0 12px 0;
      color: #495057;
    }
    
    .home-footer {
      background-color: #f8f9fa;
      border-top: 1px solid #e9ecef;
      padding: 24px 0;
      text-align: center;
      margin-top: auto;
      font-size: 0.95rem;
      color: #6c757d;
    }

    body.dark .home-footer {
      background: rgba(255,255,255,0.03);
      border-color: rgba(255,255,255,0.08);
      color: #cbd5e1;
    }
    
    .home-footer a {
      color: #0d6efd;
      text-decoration: none;
    }
    
    .home-footer a:hover {
      text-decoration: underline;
    }

    /* Dark mode toggle */
    .theme-toggle {
      position: absolute;
      top: 16px;
      right: 16px;
      background: #212529;
      color: #fff;
      border: none;
      border-radius: 999px;
      padding: 10px 14px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    body.dark .theme-toggle {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      box-shadow: 0 10px 30px rgba(118,75,162,0.25);
    }

    /* Responsive tweaks */
    @media (max-width: 768px) {
      .home-header { padding: 36px 0; }
      .home-header h1 { font-size: 2rem; }
      .home-content { padding: 32px 0; }
      .info-section h2 { font-size: 1.5rem; }
      .info-section p { font-size: 0.98rem; }
      .theme-toggle { top: 12px; right: 12px; padding: 8px 12px; }
    }
  </style>
  <?php if (!empty($homeHeaderCode ?? '')) { echo $homeHeaderCode; } ?>
</head>
<body>
  <div class="home-container">
    <header class="home-header">
      <button class="theme-toggle" id="themeToggle" type="button" aria-label="Przełącz tryb">
        <span id="themeIcon">🌞</span>
        <span id="themeLabel">Jasny</span>
      </button>
      <div class="container">
        <?php if (!empty($brandingLogo ?? '')): ?>
          <div class="d-flex justify-content-center">
            <img src="<?= htmlspecialchars($basePath . '/' . ltrim($brandingLogo, '/')) ?>" alt="Logo" class="brand-logo" />
          </div>
        <?php endif; ?>
        <h1><?= htmlspecialchars($homeTitle) ?></h1>
        <p class="subtitle"><?= htmlspecialchars($homeSubtitle) ?></p>
      </div>
    </header>
    
    <main class="home-content">
      <div class="container">
        <section class="info-section">
          <h2><?= htmlspecialchars($homeSectionTitle) ?></h2>
          <p><?= htmlspecialchars($homeSectionText) ?></p>
          
          <?php
            $safeCtaUrl = (preg_match('/^(https?:\/\/|\/(?![\/\\\\]))/i', (string)$homeCtaButtonUrl))
              ? $homeCtaButtonUrl
              : null;
          ?>
          <?php if (!empty($homeCtaButtonText) && $safeCtaUrl !== null): ?>
            <div class="action-box">
              <a href="<?= htmlspecialchars($safeCtaUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-lg">
                <?= htmlspecialchars($homeCtaButtonText, ENT_QUOTES, 'UTF-8') ?>
              </a>
            </div>
          <?php endif; ?>
        </section>
      </div>
    </main>
    
    <?php if (!empty($homeFooter)): ?>
    <footer class="home-footer">
      <div class="container">
        <?= $homeFooter; ?>
      </div>
    </footer>
    <?php endif; ?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function() {
      const body = document.body;
      const btn = document.getElementById('themeToggle');
      const icon = document.getElementById('themeIcon');
      const label = document.getElementById('themeLabel');
      const stored = localStorage.getItem('rcms_theme');
      if (stored === 'dark') {
        body.classList.add('dark');
        icon.textContent = '🌙';
        label.textContent = 'Ciemny';
      }
      btn.addEventListener('click', () => {
        const isDark = body.classList.toggle('dark');
        if (isDark) {
          icon.textContent = '🌙';
          label.textContent = 'Ciemny';
          localStorage.setItem('rcms_theme', 'dark');
        } else {
          icon.textContent = '🌞';
          label.textContent = 'Jasny';
          localStorage.setItem('rcms_theme', 'light');
        }
      });
    })();
  </script>
  <?php if (!empty($homeFooterCode ?? '')) { echo $homeFooterCode; } ?>
</body>
</html>
