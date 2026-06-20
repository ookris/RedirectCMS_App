<?php
  $pageTitle = 'Weryfikacja 2FA — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-8 col-md-5">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white text-center">
            <h3 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
              </svg>
              Weryfikacja 2FA
            </h3>
          </div>
          <div class="card-body">
            <p class="text-muted text-center mb-4">Wpisz 6-cyfrowy kod z aplikacji uwierzytelniającej.</p>

            <form method="post" action="<?= $basePath ?>/admin/index.php?action=two_factor_verify">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />

              <div class="mb-3">
                <input type="text" class="form-control form-control-lg text-center" name="code" placeholder="000000" maxlength="8" required autofocus autocomplete="one-time-code" inputmode="numeric" />
                <div class="form-text text-center">Lub wpisz kod zapasowy</div>
              </div>

              <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
              <?php endif; ?>

              <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Weryfikuj</button>
              </div>
            </form>

            <hr />
            <div class="text-center">
              <a href="<?= $basePath ?>/admin/index.php?action=logout" class="text-muted small">Wyloguj się</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
