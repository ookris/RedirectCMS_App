# System Szablonów Bloga

RedirectCMS pozwala na łatwe dostosowywanie wyglądu bloga poprzez użycie szablonów (themes). Każdy szablon ma własny design i strukturę HTML.

## Struktura katalogów

```
/templates/
├── default/                    # Domyślny szablon
│   ├── theme.json             # Metadane szablonu
│   ├── home.php               # Szablon strony głównej bloga
│   └── post.php               # Szablon pojedynczego wpisu
├── twoj-szablon/              # Twój custom szablon
│   ├── theme.json
│   ├── home.php
│   └── post.php
```

## Plik metadanych szablonu (`theme.json`)

Każdy szablon MUSI zawierać plik `theme.json` z następującymi polami:

```json
{
  "name": "Nazwa Szablonu",
  "author": "Imię Autora",
  "version": "1.0.0",
  "description": "Krótki opis tego szablonu",
  "published_date": "2026-01-09",
  "email": "author@example.com"
}
```

### Pola:
- **name** _(wymagane)_ - Nazwa szablonu wyświetlana w ustawieniach
- **author** _(opcjonalne)_ - Autor szablonu (domyślnie: "Nieznany autor")
- **version** _(opcjonalne)_ - Wersja szablonu (domyślnie: "1.0.0")
- **description** _(opcjonalne)_ - Opis szablonu
- **published_date** _(opcjonalne)_ - Data publikacji (format: YYYY-MM-DD)
- **email** _(opcjonalne)_ - Email autora dla kontaktu

## Szablony

### home.php - Strona główna bloga

Szablon wyświetlany na stronie głównej gdy tryb = "Blog". Dostępne zmienne:

```php
$basePath              // Ścieżka bazowa aplikacji
$homeTitle             // Tytuł strony głównej
$homeSubtitle          // Podtytuł
$homeMetaDescription   // Meta description (SEO)
$homeHeaderCode        // Dodatkowy kod w <head>
$homeFooterCode        // Dodatkowy kod przed </body>
$homeFooter            // Zawartość stopki (HTML)

$blogPosts             // Array z wpisami - każdy zawiera:
                       //   - 'slug'          - krótki link
                       //   - 'page_title'    - tytuł wpisu
                       //   - 'page_description' - opis
                       //   - 'og_image'      - ścieżka do obrazu
                       //   - 'created_at'    - data utworzenia
                       
$blogDescLength        // Maksymalna długość opisu (znaki)
$blogShowImages        // Boolean - czy pokazywać obrazy
```

**Przykład:**
```php
<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($homeTitle) ?></title>
  <?php if ($homeHeaderCode) echo $homeHeaderCode; ?>
</head>
<body>
  <h1><?= htmlspecialchars($homeTitle) ?></h1>
  
  <div class="posts">
    <?php foreach ($blogPosts as $post): ?>
      <article>
        <h2><?= htmlspecialchars($post['page_title'] ?? $post['slug']) ?></h2>
        <p><?= htmlspecialchars($post['page_description'] ?? '') ?></p>
        <a href="<?= $basePath ?>/blog/<?= htmlspecialchars($post['slug']) ?>">
          Czytaj więcej
        </a>
      </article>
    <?php endforeach; ?>
  </div>
  
  <?php if ($homeFooter) echo $homeFooter; ?>
  <?php if ($homeFooterCode) echo $homeFooterCode; ?>
</body>
</html>
```

### post.php - Strona pojedynczego wpisu

Szablon dla szczegółów wpisu (dostępny pod `/blog/{slug}`). Dostępne zmienne:

```php
$basePath              // Ścieżka bazowa aplikacji
$homeTitle             // Tytuł witryny
$homeFooter            // Zawartość stopki (HTML)
$homeHeaderCode        // Dodatkowy kod w <head>
$homeFooterCode        // Dodatkowy kod przed </body>

$link                  // Obiekt wpisu:
                       //   - 'id'               - ID linku
                       //   - 'slug'             - krótki link
                       //   - 'target_url'       - docelowy URL
                       //   - 'page_title'       - tytuł
                       //   - 'page_description' - opis/treść
                       //   - 'og_image'         - obrazek
                       //   - 'created_at'       - data

$postTitle             // Tytuł wpisu
$postDescription       // Opis/treść wpisu
$shareUrl              // URL do udostępnienia wpisu
$directLinkUrl         // Krótki link (skrócony URL)
$ogImageUrl            // Pełny URL do obrazu (z domeną)

// Reakcje (oceny czytelników)
$reactionCounts        // Array<string,int> — liczniki per typ reakcji:
                       //   ['happy'=>3, 'love'=>1, 'laugh'=>0, 'surprised'=>2, 'cry'=>0, 'anger'=>0]
$myReaction            // ?string — typ reakcji oddanej przez bieżącego użytkownika
                       //   np. 'happy' lub null (jeśli nie głosował / minęło 24h)
```

**Przykład:**
```php
<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($postTitle) ?> — <?= htmlspecialchars($homeTitle) ?></title>
  
  <!-- Open Graph -->
  <meta property="og:title" content="<?= htmlspecialchars($postTitle) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($postDescription) ?>" />
  <meta property="og:url" content="<?= htmlspecialchars($shareUrl) ?>" />
  <?php if ($ogImageUrl): ?>
    <meta property="og:image" content="<?= htmlspecialchars($ogImageUrl) ?>" />
  <?php endif; ?>
  
  <?php if ($homeHeaderCode) echo $homeHeaderCode; ?>
</head>
<body>
  <article>
    <?php if ($link['og_image']): ?>
      <img src="<?= htmlspecialchars($basePath . '/' . $link['og_image']) ?>" alt="<?= htmlspecialchars($postTitle) ?>" />
    <?php endif; ?>
    
    <h1><?= htmlspecialchars($postTitle) ?></h1>
    <time><?= date('d.m.Y', strtotime($link['created_at'])) ?></time>
    
    <?php if ($postDescription): ?>
      <div><?= $postDescription ?></div>
    <?php endif; ?>
    
    <a href="<?= htmlspecialchars($link['target_url']) ?>" target="_blank">
      Przejdź do oferty
    </a>
  </article>
  
  <?php if ($homeFooter) echo $homeFooter; ?>
  <?php if ($homeFooterCode) echo $homeFooterCode; ?>
</body>
</html>
```

## Tworzenie szablonu niestandardowego

### Krok 1: Utwórz folder szablonu

```bash
mkdir -p /templates/moj-szablon
```

### Krok 2: Utwórz `theme.json`

```json
{
  "name": "Mój Szablon",
  "author": "Twoje Imię",
  "version": "1.0.0",
  "description": "Customowy szablon bloga z moim designem",
  "published_date": "2026-01-09",
  "email": "twoj@email.com"
}
```

### Krok 3: Utwórz `home.php`

Szablon dla strony głównej bloga. Możesz skopiować domyślny i go zmodyfikować:

```php
<!-- templates/moj-szablon/home.php -->
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title><?= htmlspecialchars($homeTitle) ?></title>
  <style>
    /* Twoje CSS tutaj */
  </style>
</head>
<body>
  <h1><?= htmlspecialchars($homeTitle) ?></h1>
  
  <?php foreach ($blogPosts as $post): ?>
    <article>
      <h2><?= htmlspecialchars($post['page_title'] ?? $post['slug']) ?></h2>
      <p><?= htmlspecialchars($post['page_description'] ?? '') ?></p>
      <a href="<?= $basePath ?>/blog/<?= htmlspecialchars($post['slug']) ?>">Więcej</a>
    </article>
  <?php endforeach; ?>
</body>
</html>
```

### Krok 4: Utwórz `post.php`

Szablon dla pojedynczego wpisu:

```php
<!-- templates/moj-szablon/post.php -->
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title><?= htmlspecialchars($postTitle) ?></title>
  <meta property="og:title" content="<?= htmlspecialchars($postTitle) ?>" />
  <meta property="og:url" content="<?= htmlspecialchars($shareUrl) ?>" />
</head>
<body>
  <h1><?= htmlspecialchars($postTitle) ?></h1>
  <div><?= $postDescription ?></div>

  <!-- Widget ocen czytelników (opcjonalnie) -->
  <?php require __DIR__ . '/../../src/PostReactions.php'; ?>

  <a href="<?= htmlspecialchars($directLinkUrl) ?>">Przejdź do oferty</a>
</body>
</html>
```

> **Uwaga:** `require __DIR__ . '/../../src/PostReactions.php'` zakłada, że Twój szablon
> znajduje się bezpośrednio w katalogu `templates/moj-szablon/`. Jeśli struktura
> katalogów jest inna, dostosuj ścieżkę odpowiednio.

### Krok 5: Wybierz szablon w ustawieniach

1. Przejdź do **Panel → Ustawienia**
2. Zmień "Tryb strony głównej" na **"Blog"**
3. W sekcji "Blog" wybierz "Szablon bloga"
4. Twój szablon powinien się pojawić na liście
5. Kliknij "Zapisz zmiany"

## Mechanizm fallback

Jeśli wybrany szablon:
- Nie istnieje
- Nie ma wymaganych plików (`home.php` lub `post.php`)
- Ma błędy w `theme.json`

...system automatycznie przełączy się na domyślny szablon (`default`).

## Walidacja szablonów

Szablon jest uważany za **kompletny** gdy:
- ✅ Folder istnieje w `/templates/`
- ✅ Zawiera plik `theme.json` z polem "name"
- ✅ Zawiera plik `home.php`
- ✅ Zawiera plik `post.php`

## System ocen czytelników (reakcje)

Każdy szablon `post.php` może wyświetlać widget z sześcioma reakcjami emoji (😊 ❤️ 😂 😮 😢 😠), dzięki którym czytelnicy mogą ocenić wpis.

### Jak to działa

- Czytelnik może oddać **jeden głos per post w ciągu 24 godzin** (identyfikacja po adresie IP)
- Kliknięcie tej samej reakcji **cofa głos** (toggle)
- Kliknięcie innej reakcji **zmienia głos** (bez podwójnego głosowania)
- Stan jest zapisywany w bazie danych — odświeżenie strony nie kasuje wyboru
- Liczniki aktualizują się **natychmiast** przez AJAX (endpoint `/__react`)

### Jak dodać widget do szablonu

Wstaw jeden `require` **przed** przyciskiem przekierowania do oferty:

```php
<!-- Reakcje czytelników -->
<?php require __DIR__ . '/../../src/PostReactions.php'; ?>

<!-- CTA -->
<a href="<?= htmlspecialchars($directLinkUrl) ?>">Przejdź do oferty</a>
```

Partial `PostReactions.php` (w katalogu `src/`) sam obsługuje:
- Renderowanie SVG ikon
- Style CSS (scoped do `.post-reactions`)
- Logikę JavaScript (fetch do `/__react`)
- Podświetlenie bieżącej reakcji użytkownika

### Dostępne zmienne reakcji w `post.php`

| Zmienna | Typ | Opis |
|---|---|---|
| `$reactionCounts` | `array<string,int>` | Liczniki: `['happy'=>3, 'love'=>1, ...]` |
| `$myReaction` | `?string` | Bieżąca reakcja użytkownika lub `null` |

Typy reakcji: `happy`, `love`, `laugh`, `surprised`, `cry`, `anger`.

### Własna prezentacja (bez partiala)

Jeśli chcesz zintegrować reakcje z własnym designem zamiast używać gotowego partiala:

```php
<div class="moje-reakcje">
  <?php foreach (['happy','love','laugh','surprised','cry','anger'] as $type): ?>
    <button data-reaction="<?= $type ?>" data-link-id="<?= (int)$link['id'] ?>"
            class="<?= $myReaction === $type ? 'aktywna' : '' ?>">
      <?= $reactionCounts[$type] ?>
    </button>
  <?php endforeach; ?>
</div>
```

Wyślij POST na `/__react` z JSON `{"link_id": <id>, "reaction_type": "<typ>"}`.
Odpowiedź: `{"ok": true, "my_reaction": "happy", "counts": {"happy": 4, ...}}`.

### Tabela bazy danych

Reakcje przechowywane są w tabeli `link_reactions`:

```sql
link_id       INT    -- ID posta
reaction_type ENUM   -- 'happy'|'love'|'laugh'|'surprised'|'cry'|'anger'
ip_hash       CHAR   -- SHA-256(IP + sól) — bez przechowywania surowego IP
created_at    TIMESTAMP
```

---

## Praktyczne porady

1. **Bezpieczeństwo HTML**: Zawsze używaj `htmlspecialchars()` dla danych użytkownika
2. **Linki bezwzględne**: Zawsze używaj `$basePath` w linkach (wspiera subfoldery)
3. **SEO**: Pamiętaj o Open Graph w szablonie `post.php`
4. **Responsywność**: Używaj CSS media queries dla mobile
5. **Kopii zapasowe**: Zanim zmienisz szablon, zrób kopię obecnego
6. **Reakcje**: Zawsze dodawaj `PostReactions.php` przed CTA — to standardowy element UX wszystkich wbudowanych szablonów

## Przykład zaawansowanego szablonu

Możesz pobrać więcej szablonów z repozytorium RedirectCMS lub tworzyć własne.
Domyślny szablon znajduje się w `/templates/default/` i jest dobrym punktem wyjścia.
