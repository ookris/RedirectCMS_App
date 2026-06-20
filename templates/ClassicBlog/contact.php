<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kontakt — <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="description" content="Skontaktuj się z nami">
  <?php require __DIR__ . '/_head_css.php'; ?>
</head>
<body>

<?php require __DIR__ . '/_navbar.php'; ?>

<!-- BREADCRUMB BAR -->
<div class="post-breadcrumb-bar">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $basePath ?>/">Strona główna</a></li>
        <li class="breadcrumb-item active" aria-current="page">Kontakt</li>
      </ol>
    </nav>
  </div>
</div>

<!-- FORMULARZ KONTAKTOWY -->
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-7">

      <div class="contact-card">
        <h1 class="h2 mb-2" style="font-family:var(--ff-serif)">Kontakt</h1>
        <?php if (!empty($contactIntro)): ?>
          <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($contactIntro)) ?></p>
        <?php endif; ?>

        <?php if (!empty($formSuccess)): ?>
          <div class="alert alert-success d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
              <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
            </svg>
            <?= htmlspecialchars($formSuccess) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($formError)): ?>
          <div class="alert alert-danger d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
              <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
            </svg>
            <?= htmlspecialchars($formError) ?>
          </div>
        <?php endif; ?>

        <?php if (empty($formSuccess)): ?>
        <form method="post" action="<?= $basePath ?>/?page=contact" class="mt-4" novalidate>
          <input type="hidden" name="csrf_contact" value="<?= htmlspecialchars($contactCsrf) ?>">

          <div class="mb-3">
            <label for="c_name" class="form-label fw-600 small">
              Imię <span class="text-muted fw-normal">(opcjonalnie)</span>
            </label>
            <input type="text" id="c_name" name="name" class="form-control"
                   value="<?= htmlspecialchars($formValues['name'] ?? '') ?>"
                   maxlength="50" placeholder="Twoje imię">
          </div>

          <div class="mb-3">
            <label for="c_email" class="form-label fw-600 small">
              Adres e-mail <span class="text-danger">*</span>
            </label>
            <input type="email" id="c_email" name="email" class="form-control" required
                   value="<?= htmlspecialchars($formValues['email'] ?? '') ?>"
                   maxlength="254" placeholder="twoj@email.pl">
          </div>

          <div class="mb-4">
            <label for="c_msg" class="form-label fw-600 small">
              Wiadomość <span class="text-danger">*</span>
            </label>
            <textarea id="c_msg" name="message" class="form-control"
                      rows="7" maxlength="1000" required
                      placeholder="Treść wiadomości…"><?= htmlspecialchars($formValues['message'] ?? '') ?></textarea>
            <div class="form-text text-end small">
              <span id="charCount">0</span>/1000 znaków
            </div>
          </div>

          <button type="submit" class="btn-contact">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="me-2" viewBox="0 0 16 16">
              <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"/>
            </svg>
            Wyślij wiadomość
          </button>
        </form>
        <?php endif; ?>
      </div>

      <div class="mt-4">
        <a href="<?= $basePath ?>/" class="post-back-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
          </svg>
          Strona główna
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  (function() {
    var msg = document.getElementById('c_msg');
    var cnt = document.getElementById('charCount');
    if (msg && cnt) {
      var update = function() { cnt.textContent = msg.value.length; };
      msg.addEventListener('input', update);
      update();
    }
  })();
</script>
<?php require __DIR__ . '/_footer.php'; ?>
