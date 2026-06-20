<?php
  $pageTitle = 'Uwierzytelnianie dwuskładnikowe (2FA) — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-8">

        <?php if ($twoFactorEnabled): ?>
          <!-- 2FA jest włączone -->
          <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
              <h2 class="mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                  <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
                  <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                </svg>
                2FA jest aktywne
              </h2>
            </div>
            <div class="card-body">
              <div class="alert bg-success-subtle border border-success text-success-emphasis">
                Uwierzytelnianie dwuskładnikowe jest włączone dla Twojego konta. Przy każdym logowaniu wymagany jest kod z aplikacji.
              </div>

              <h5 class="mt-4 mb-3">Kody zapasowe</h5>
              <p class="text-muted">Użyj kodów zapasowych gdy nie masz dostępu do aplikacji. Każdy kod działa jednokrotnie.</p>

              <?php if (!empty($backupCodes)): ?>
                <div class="row row-cols-2 row-cols-md-5 g-2 mb-3">
                  <?php foreach ($backupCodes as $i => $code): ?>
                    <div class="col">
                      <code class="d-block text-center p-2 bg-light rounded border <?= $code['used'] ? 'text-decoration-line-through text-muted' : 'fw-bold' ?>">
                        <?= htmlspecialchars($code['code']) ?>
                      </code>
                    </div>
                  <?php endforeach; ?>
                </div>
                <p class="small text-muted">Pozostało kodów: <strong><?= $unusedBackupCount ?></strong> z <?= count($backupCodes) ?></p>
              <?php endif; ?>

              <hr />

              <div class="d-flex justify-content-end">
                <div class="d-flex gap-2">
                  <form method="post" action="<?= $basePath ?>/admin/index.php?action=two_factor_regenerate_backup">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Wygenerować nowe kody zapasowe? Stare kody przestaną działać.')">
                      Nowe kody zapasowe
                    </button>
                  </form>
                  <form method="post" action="<?= $basePath ?>/admin/index.php?action=two_factor_disable">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Czy na pewno chcesz wyłączyć 2FA?')">
                      Wyłącz 2FA
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>

        <?php else: ?>
          <!-- Konfiguracja 2FA -->
          <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
              <h2 class="mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                  <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
                </svg>
                Konfiguracja 2FA
              </h2>
            </div>
            <div class="card-body">
              <p>Dodaj dodatkową warstwę zabezpieczeń do konta administratora. Po włączeniu, logowanie będzie wymagało kodu z aplikacji TOTP.</p>

              <div class="alert bg-info-subtle border border-info text-info-emphasis">
                <strong>Jak to działa:</strong> Zeskanuj kod QR w aplikacji takiej jak Google Authenticator, Authy lub Microsoft Authenticator. Aplikacja będzie generować 6-cyfrowe kody ważne przez 30 sekund.
              </div>

              <h5 class="mt-4 mb-3">Krok 1: Zeskanuj kod QR</h5>
              <div class="text-center mb-3">
                <div id="qrcode" class="d-inline-block border rounded p-2" data-uri="<?= htmlspecialchars($otpUri) ?>"></div>
              </div>
              <p class="text-center text-muted small">Lub wpisz ręcznie klucz: <code class="user-select-all"><?= htmlspecialchars($secret) ?></code></p>

              <h5 class="mt-4 mb-3">Krok 2: Potwierdź kodem</h5>
              <form method="post" action="<?= $basePath ?>/admin/index.php?action=two_factor_enable">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                <input type="hidden" name="secret" value="<?= htmlspecialchars($secret) ?>" />

                <div class="row justify-content-center mb-3">
                  <div class="col-md-4">
                    <input type="text" class="form-control form-control-lg text-center" name="code" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autofocus autocomplete="one-time-code" inputmode="numeric" />
                  </div>
                </div>

                <?php if (!empty($error)): ?>
                  <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="d-flex justify-content-end">
                  <button type="submit" class="btn btn-primary">Włącz 2FA</button>
                </div>
              </form>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <?php if (!$twoFactorEnabled): ?>
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <script>
    var el = document.getElementById('qrcode');
    if (el) new QRCode(el, { text: el.dataset.uri, width: 200, height: 200, correctLevel: QRCode.CorrectLevel.L });
  </script>
  <?php endif; ?>
</body>
</html>
