<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($errorTitle ?? 'Link niedostępny') ?> — RedirectCMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: radial-gradient(circle at 10% 20%, rgba(102,126,234,0.25), transparent 25%),
                  radial-gradient(circle at 80% 0%, rgba(118,75,162,0.25), transparent 30%),
                  #0b1021;
      color: #e9ecf5;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      padding: 16px;
    }
    .card-error {
      background: rgba(17, 24, 39, 0.8);
      border: 1px solid rgba(255,255,255,0.08);
      box-shadow: 0 20px 60px rgba(0,0,0,0.35);
      border-radius: 16px;
      padding: 32px;
      max-width: 720px;
      width: 100%;
      backdrop-filter: blur(10px);
    }
    .badge-pill {
      border-radius: 50px;
      padding: 6px 14px;
      font-size: 0.85rem;
      letter-spacing: 0.02em;
    }
    .badge-draft { background: rgba(245,158,11,0.2); color: #fbbf24; }
    .badge-trashed { background: rgba(239,68,68,0.2); color: #f87171; }
    .badge-scheduled { background: rgba(59,130,246,0.2); color: #60a5fa; }
    .badge-expired { background: rgba(107,114,128,0.2); color: #9ca3af; }
    .title {
      font-size: 2.4rem;
      font-weight: 700;
      color: #f8fafc;
      margin: 12px 0;
    }
    .lead {
      color: #cbd5e1;
      font-size: 1.05rem;
      line-height: 1.7;
    }
    .info-box {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 10px;
      padding: 16px;
      margin-top: 16px;
    }
    .info-box strong {
      color: #a5b4fc;
    }
    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 24px;
    }
    .btn-primary-gradient {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      color: #fff;
      padding: 10px 20px;
      border-radius: 10px;
      font-weight: 600;
      box-shadow: 0 10px 30px rgba(102,126,234,0.35);
    }
    .btn-primary-gradient:hover { opacity: 0.9; color: #fff; }
    .btn-ghost {
      border: 1px solid rgba(255,255,255,0.12);
      color: #e2e8f0;
      padding: 10px 20px;
      border-radius: 10px;
      background: rgba(255,255,255,0.03);
    }
    .btn-ghost:hover { border-color: rgba(255,255,255,0.25); color: #fff; }
    .icon-large {
      font-size: 3rem;
      margin-bottom: 8px;
    }
  </style>
</head>
<body>
  <div class="card-error">
    <?php
    $badgeClass = 'badge-draft';
    $icon = '';
    switch ($errorType ?? 'unavailable') {
        case 'draft':
            $badgeClass = 'badge-draft';
            $icon = '';
            break;
        case 'trashed':
            $badgeClass = 'badge-trashed';
            $icon = '';
            break;
        case 'scheduled':
            $badgeClass = 'badge-scheduled';
            $icon = '';
            break;
        case 'expired':
            $badgeClass = 'badge-expired';
            $icon = '';
            break;
    }
    ?>
    <div class="icon-large"><?= $icon ?></div>
    <div class="badge-pill <?= $badgeClass ?>"><?= htmlspecialchars($errorBadge ?? 'Link niedostępny') ?></div>
    <div class="title"><?= htmlspecialchars($errorTitle ?? 'Link jest niedostępny') ?></div>
    <p class="lead mb-3"><?= htmlspecialchars($errorMessage ?? 'Ten link jest obecnie niedostępny.') ?></p>

    <?php if (!empty($errorDetails)): ?>
    <div class="info-box">
      <?= $errorDetails ?>
    </div>
    <?php endif; ?>

    <div class="actions">
      <a class="btn btn-primary-gradient" href="<?= $basePath ?>/">Przejdź do strony głównej</a>
    </div>
  </div>
</body>
</html>
