<?php
  $pageTitle = 'Logowanie — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow" class="login-card">
      <div class="card-body p-5">
        <div class="text-center mb-4">
          <img src="<?= $basePath ?>/admin/static/img/logo_duze.png" alt="RedirectCMS" style="max-width: 280px; width: 100%; height: auto;" />
        </div>
        <h2 class="h5 text-center text-muted mb-4">Logowanie</h2>
        
        <?php if (!empty($licenseBlocked)): ?>
          <div class="alert alert-danger border-danger" role="alert">
            <strong>Dostęp zablokowany</strong><br>
            <?= htmlspecialchars($licenseMessage ?? 'Licencja RedirectCMS jest nieważna.') ?><br>
            <a href="https://redirectcms.pl/contact" class="alert-link" target="_blank" rel="noopener">Skontaktuj się z nami</a> w celu wyjaśnienia.
          </div>
        <?php elseif (!empty($error)): ?>
          <div class="alert bg-danger-subtle border border-danger text-danger-emphasis alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
          </div>
        <?php endif; ?>

        <?php if (empty($licenseBlocked)): ?>
        <form method="post" action="<?= $basePath ?>/admin/index.php?action=login">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
          
          <div class="mb-3">
            <label for="username" class="form-label">Nazwa użytkownika</label>
            <input type="text" class="form-control" id="username" name="username" required autofocus />
          </div>
          
          <div class="mb-3">
            <label for="password" class="form-label">Hasło</label>
            <input type="password" class="form-control" id="password" name="password" required />
          </div>

          <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" value="1" />
            <label class="form-check-label" for="remember_me">Zapamiętaj mnie przez 30 dni</label>
          </div>

          <button type="submit" class="btn btn-primary w-100">Zaloguj się</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  
  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
