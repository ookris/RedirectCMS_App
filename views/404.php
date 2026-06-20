<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Nie znaleziono strony — RedirectCMS</title>
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
    .card-404 {
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
      background: rgba(255,255,255,0.08);
      color: #a5b4fc;
      font-size: 0.85rem;
      letter-spacing: 0.02em;
    }
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
    .pill-stack {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 14px;
    }
    .pill {
      padding: 4px 10px;
      border-radius: 999px;
      background: rgba(255,255,255,0.07);
      color: #cbd5e1;
      font-size: 0.85rem;
    }
  </style>
</head>
<body>
  <div class="card-404">
    <div class="badge-pill">404 • Nie znaleziono</div>
    <div class="title">Ups, ta strona nie istnieje</div>
    <p class="lead mb-3">Szukany adres jest nieprawidłowy lub wygasł. Wróć na stronę główną albo sprawdź dostępne linki w panelu.</p>
    <div class="pill-stack">
      <span class="pill">Brak dopasowania</span>
      <span class="pill">Sprawdź pisownię</span>
      <span class="pill">Wróć do startu</span>
    </div>
    <div class="actions">
      <a class="btn btn-primary-gradient" href="<?= $basePath ?>/">Przejdź do strony głównej</a>
    </div>
  </div>
</body>
</html>
