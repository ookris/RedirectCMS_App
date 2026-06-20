# Tworzenie szablonów w RedirectCMS

Kompletny przewodnik dla twórców szablonów (motywów) — opisuje każdą możliwość systemu szablonów.

---

## Spis treści

1. [Struktura katalogu szablonu](#1-struktura-katalogu-szablonu)
2. [Plik theme.json](#2-plik-themejson)
3. [Pliki szablonu PHP](#3-pliki-szablonu-php)
4. [Zmienne PHP dostępne w szablonach](#4-zmienne-php-dostępne-w-szablonach)
5. [System kolorów — CSS Custom Properties](#5-system-kolorów--css-custom-properties)
6. [Rozmiary obrazków (image_sizes)](#6-rozmiary-obrazków-image_sizes)
7. [Funkcje opcjonalne (supports)](#7-funkcje-opcjonalne-supports)
8. [Sidebar i widgety](#8-sidebar-i-widgety)
9. [Lightbox — galeria ze zdjęciami](#9-lightbox--galeria-ze-zdjęciami)
10. [System ocen czytelników (reakcje)](#10-system-ocen-czytelników-reakcje)
11. [Nawigacja, strony custom i kontakt](#11-nawigacja-strony-custom-i-kontakt)
12. [Budowanie URL](#12-budowanie-url)
13. [Partiale i pliki pomocnicze](#13-partiale-i-pliki-pomocnicze)
14. [Mechanizm fallback](#14-mechanizm-fallback)
15. [Tworzenie nowego szablonu — krok po kroku](#15-tworzenie-nowego-szablonu--krok-po-kroku)
16. [Dobre praktyki i bezpieczeństwo](#16-dobre-praktyki-i-bezpieczeństwo)

---

## 1. Struktura katalogu szablonu

Każdy szablon to **oddzielny podkatalog** w `App/templates/`:

```
App/src/
└── PostReactions.php     ← Współdzielony komponent ocen czytelników (dołączany przez wszystkie szablony)

App/assets/post_react/
├── happy.svg             ← Ikony SVG reakcji (inline w PostReactions.php)
├── love.svg
├── laugh.svg
├── surprised.svg
├── cry.svg
├── anger.svg
└── post_react.css        ← Style widgetu (ładowane automatycznie przez PostReactions.php)

App/templates/
├── default/              ← Szablon domyślny (fallback)
│   ├── theme.json        ← WYMAGANE: metadane i konfiguracja
│   ├── home.php          ← WYMAGANE: strona główna / lista wpisów
│   ├── post.php          ← WYMAGANE: pojedynczy wpis
│   ├── page.php          ← opcjonalne: strona statyczna (Custom Page)
│   ├── contact.php       ← opcjonalne: strona kontaktowa
│   └── _sidebar.php      ← opcjonalne: partial sidebara
│
└── MojSzablon/           ← Twój szablon (nazwa = folder)
    ├── theme.json
    ├── home.php
    ├── post.php
    ├── page.php          ← jeśli supports contact_page
    ├── contact.php       ← jeśli supports contact_page
    └── _sidebar.php      ← jeśli supports sidebar
```

**Wymagane minimum:** `theme.json` + `home.php` + `post.php`

Nazwa folderu musi zawierać wyłącznie znaki `[a-zA-Z0-9_-]`.

---

## 2. Plik theme.json

Plik JSON z metadanymi i konfiguracją szablonu. Musi istnieć i zawierać pole `name`.

### Pełny przykład

```json
{
  "name": "Mój Szablon",
  "author": "Jan Kowalski",
  "version": "1.0.0",
  "description": "Opis szablonu widoczny w panelu admina",
  "published_date": "2026-03-01",
  "email": "autor@example.com",

  "supports": ["sidebar", "contact_page", "gallery", "lightbox"],

  "colors": [
    {"key": "primary",     "label": "Kolor główny (przyciski, linki)",  "default": "#7952b3"},
    {"key": "header_bg",   "label": "Tło nagłówka",                     "default": "#6f42c1"},
    {"key": "header_text", "label": "Tekst nagłówka",                   "default": "#ffffff"},
    {"key": "accent",      "label": "Akcent (tagi, odznaki)",           "default": "#e83e8c"},
    {"key": "footer_bg",   "label": "Tło stopki",                       "default": "#343a40"},
    {"key": "footer_text", "label": "Tekst stopki",                     "default": "#adb5bd"},
    {"key": "body_bg",     "label": "Tło strony",                       "default": "#f8f9fa"}
  ],

  "image_sizes": {
    "featured":      {"width": 1200, "height": 630,  "crop": true,  "label": "Obraz wyróżniający"},
    "post":          {"width": 800,  "height": 450,  "crop": true,  "label": "Obraz wpisu"},
    "thumbnail":     {"width": 300,  "height": 200,  "crop": true,  "label": "Miniatura sidebar"},
    "gallery":       {"width": 1200, "height": 800,  "crop": false, "label": "Obraz galerii (pełny)"},
    "gallery_thumb": {"width": 300,  "height": 200,  "crop": true,  "label": "Miniatura galerii"}
  }
}
```

### Pola theme.json

| Pole             | Wymagane | Typ    | Opis |
|------------------|----------|--------|------|
| `name`           | TAK      | string | Nazwa wyświetlana w panelu admina |
| `author`         | nie      | string | Autor szablonu |
| `version`        | nie      | string | Wersja (np. `"1.0.0"`) |
| `description`    | nie      | string | Krótki opis |
| `published_date` | nie      | string | Data publikacji `YYYY-MM-DD` |
| `email`          | nie      | string | Kontakt do autora |
| `supports`       | nie      | array  | Lista obsługiwanych funkcji (patrz rozdział 7) |
| `colors`         | nie      | array  | Definicje kolorów konfigurowalnych w adminie |
| `image_sizes`    | nie      | object | Rozmiary miniatur generowanych przez ImageCropService |

---

## 3. Pliki szablonu PHP

### home.php — strona główna / lista wpisów

Wyświetla listę wpisów bloga. Plik wywoływany dla URL `/?` oraz `/?page=N` i przy filtrowaniu kategorią/tagiem.

### post.php — pojedynczy wpis

Wyświetla szczegóły wpisu: tytuł, treść, obrazek, tagi, kategorię, galerię, wpisy powiązane.

### page.php — strona statyczna (Custom Page)

Wyświetla treść strony statycznej tworzonej w panelu admina (edytor Trix). URL: `/?page=slug`.

### contact.php — strona kontaktowa

Formularz kontaktowy z CSRF, rate limitingiem i walidacją. URL: `/?page=contact`.
Wymagane jeśli szablon deklaruje `"contact_page"` w `supports`.

### _sidebar.php — partial sidebara

Plik dołączany przez `require __DIR__ . '/_sidebar.php'` w obrębie `home.php` i `post.php`.
Renderuje widgety na podstawie tablicy `$sidebarData`.

---

## 4. Zmienne PHP dostępne w szablonach

Wszystkie zmienne są przekazywane przez mechanizm `extract()` — gotowe do użycia bez prefiksu `$this->`.

### Zmienne globalne (wszystkie widoki)

| Zmienna           | Typ     | Opis |
|-------------------|---------|------|
| `$basePath`       | string  | Ścieżka bazowa aplikacji, np. `""` lub `"/blog"` |
| `$homeTitle`      | string  | Tytuł bloga z ustawień |
| `$homeSubtitle`   | string  | Podtytuł bloga |
| `$homeFooter`     | string  | HTML stopki (raw — może zawierać tagi) |
| `$homeHeaderCode` | string  | Custom HTML wstrzykiwany do `<head>` |
| `$homeFooterCode` | string  | Custom HTML przed `</body>` |
| `$themeCss`       | string  | Blok `<style>:root{--theme-*}</style>` z kolorami szablonu |
| `$brandingLogo`   | string  | Ścieżka do logo (relatywna), np. `"uploads/logo.png"` |
| `$navPages`       | array   | Strony do wyświetlenia w nawigacji (patrz niżej) |
| `$contactEnabled` | bool    | Czy strona kontaktowa jest włączona |
| `$socialLinks`    | array   | Linki social media: `['facebook' => 'https://...', ...]` |
| `$sidebarData`    | array   | Dane widgetów sidebara (pusta tablica gdy brak) |
| `$prettyUrls`     | bool    | Czy włączone przyjazne URL (`/category/slug` vs `/?category=slug`) |
| `$lightboxEnabled`| bool    | Czy szablon obsługuje lightbox |

**`$navPages`** — każdy element:
```php
[
  'id'    => int,
  'title' => string,   // tytuł linku w menu
  'slug'  => string,   // slug strony
]
```

**`$socialLinks`** — obsługiwane klucze sieci: `facebook`, `instagram`, `twitter`, `youtube`, `linkedin`, `tiktok`, `pinterest`, `custom_url`

---

### Zmienne w home.php

| Zmienna              | Typ     | Opis |
|----------------------|---------|------|
| `$blogPosts`         | array   | Lista wpisów bieżącej strony (patrz struktura niżej) |
| `$allCategories`     | array   | Wszystkie kategorie z liczbą wpisów |
| `$allTags`           | array   | Wszystkie tagi z liczbą wpisów |
| `$homeMetaDescription` | string | Meta opis strony głównej |
| `$activeCategory`    | array\|null | Aktywna kategoria filtru (lub `null`) |
| `$activeTag`         | array\|null | Aktywny tag filtru (lub `null`) |
| `$currentPage`       | int     | Numer bieżącej strony paginacji (1-based) |
| `$totalPages`        | int     | Całkowita liczba stron paginacji |
| `$blogDescLength`    | int     | Maksymalna długość skrótu opisu (znaki) |
| `$blogShowImages`    | bool    | Czy wyświetlać obrazki OG |

**Pojedynczy wpis (`$blogPosts[n]`):**
```php
[
  'id'               => int,
  'slug'             => string,      // np. "moj-wpis"
  'page_title'       => string,      // tytuł wpisu
  'page_description' => string,      // HTML treści wpisu
  'og_image'         => string,      // ścieżka obrazka OG, np. "uploads/img.jpg"
  'created_at'       => string,      // datetime "YYYY-MM-DD HH:MM:SS"
  'click_count'      => int,         // liczba odsłon/kliknięć
  'category_name'    => string|null, // nazwa kategorii
  'category_slug'    => string|null,
  'category_color'   => string|null, // kolor hex, np. "#e74c3c"
  'tags'             => array,       // [{id, name, slug}, ...]
]
```

**`$allCategories[n]` i `$allTags[n]`:**
```php
// Kategoria
[
  'id'         => int,
  'name'       => string,
  'slug'       => string,
  'color'      => string,   // hex
  'post_count' => int,
]

// Tag
[
  'id'         => int,
  'name'       => string,
  'slug'       => string,
  'post_count' => int,
]
```

---

### Zmienne w post.php

| Zmienna           | Typ    | Opis |
|-------------------|--------|------|
| `$link`           | array  | Pełne dane wpisu (patrz niżej) |
| `$postTitle`      | string | Tytuł wpisu (do `<title>` i `<h1>`) |
| `$postDescription`| string | HTML treści wpisu (może zawierać tagi — użyj `Utils::sanitizeHtml()`) |
| `$postCreatedAt`  | string | Data wpisu `YYYY-MM-DD HH:MM:SS` |
| `$ogImageUrl`     | string | Pełny URL obrazka OG |
| `$shareUrl`       | string | Kanoniczny URL wpisu (do Open Graph) |
| `$relatedPosts`     | array   | Max. 3 wpisy powiązane (ta sama kategoria lub wspólne tagi) |
| `$galleryImages`    | array   | Obrazy galerii wpisu (patrz niżej) |
| `$allCategories`    | array   | Wszystkie kategorie (do sidebara) |
| `$allTags`          | array   | Wszystkie tagi (do sidebara) |
| `$directLinkUrl`    | string  | Pełny URL przekierowania przez router CMS (`$baseUrl . '/' . $slug`) — użyj tego w przycisku "Sprawdź ofertę" |
| `$reactionCounts`   | array   | Liczniki reakcji: `['happy'=>n, 'love'=>n, 'laugh'=>n, 'surprised'=>n, 'cry'=>n, 'anger'=>n]` |
| `$myReaction`       | ?string | Typ reakcji oddanej przez bieżącego użytkownika w ciągu ostatnich 24h, lub `null` |

**`$link` (pełna struktura):**
```php
[
  'id'               => int,
  'slug'             => string,
  'page_title'       => string,
  'page_description' => string,      // HTML
  'og_image'         => string,      // ścieżka relative
  'target_url'       => string,      // docelowy URL przekierowania (pole DB)
  'created_at'       => string,
  'click_count'      => int,
  'category'         => [            // null jeśli brak
      'id'    => int,
      'name'  => string,
      'slug'  => string,
      'color' => string,
  ],
  'tags'             => [            // pusta tablica jeśli brak
      ['id' => int, 'name' => string, 'slug' => string],
  ],
  'gallery'          => array,       // obrazy galerii (raw z DB)
]
```

**`$galleryImages[n]`:**
```php
[
  'path' => string,   // ścieżka relatywna do pliku oryginalnego
  'url'  => string,   // URL do croppowanej miniatury (jeśli wygenerowana) lub pusty string
]
```

**`$relatedPosts[n]`** — ta sama struktura co `$blogPosts[n]` (home.php).

---

### Zmienne w page.php

| Zmienna              | Typ    | Opis |
|----------------------|--------|------|
| `$pageTitle`         | string | Tytuł strony |
| `$pageHtml`          | string | HTML treści (z edytora Trix — zaufany, można wyświetlić raw) |
| `$pageMetaTitle`     | string | Meta tytuł SEO (jeśli różny od `$pageTitle`) |
| `$pageMetaDescription` | string | Meta opis SEO |
| `$pageJs`            | string | Opcjonalny JavaScript do wstrzyknięcia (raw) |
| `$currentSlug`       | string | Slug bieżącej strony (do oznaczenia aktywnego linku nav) |

---

### Zmienne w contact.php

| Zmienna         | Typ    | Opis |
|-----------------|--------|------|
| `$contactIntro` | string | Tekst wstępny formularza (ustawienia → Wygląd) |
| `$contactCsrf`  | string | Token CSRF — **musi** być wstrzyknięty jako `<input type="hidden" name="csrf_contact">` |
| `$formSuccess`  | string | Komunikat sukcesu po wysłaniu (pusty jeśli brak) |
| `$formError`    | string | Komunikat błędu walidacji (pusty jeśli brak) |
| `$formValues`   | array  | Poprzednie wartości pól: `['name' => ..., 'email' => ..., 'message' => ...]` |

> **Ważne:** Formularz musi wysyłać `POST` na `/?page=contact` i zawierać ukryte pole `csrf_contact`.
> Walidacja i wysyłka e-mail są obsługiwane przez `EmailService` — szablon obsługuje tylko prezentację.

---

## 5. System kolorów — CSS Custom Properties

### Jak działa

1. Definiujesz kolory w `theme.json` → sekcja `colors`
2. Admin może je zmienić przez panel → Ustawienia → Wygląd
3. System generuje blok CSS z właściwościami custom i przekazuje go jako `$themeCss`
4. Wstrzykujesz `$themeCss` w `<head>` — **przed własnym `<style>`**
5. Używasz właściwości `var(--theme-klucz)` w CSS szablonu

### Format definicji koloru

```json
{"key": "primary", "label": "Kolor główny", "default": "#7952b3"}
```

- `key` — identyfikator (tylko `[a-z0-9_]`), staje się `--theme-{key}`
- `label` — opis widoczny w panelu admina
- `default` — wartość domyślna (hex, `rgb()`, `rgba()`, `hsl()`, lub named CSS color)

### Wygenerowany CSS

```html
<style>
:root {
    --theme-primary: #7952b3;
    --theme-header-bg: #6f42c1;
    --theme-header-text: #ffffff;
    --theme-accent: #e83e8c;
    --theme-footer-bg: #343a40;
    --theme-footer-text: #adb5bd;
    --theme-body-bg: #f8f9fa;
}
</style>
```

### Użycie w szablonie

```php
// W <head>, przed własnym <style>:
<?php echo $themeCss ?? ''; ?>

// W CSS:
<style>
  body { background: var(--theme-body-bg, #f8f9fa); }
  .site-header { background: var(--theme-header-bg, #333); color: var(--theme-header-text, #fff); }
  a { color: var(--theme-primary, #7952b3); }
  .btn-primary { background: var(--theme-primary, #7952b3); }
  .tag-badge { background: var(--theme-accent, #e83e8c); }
  .site-footer { background: var(--theme-footer-bg, #343a40); color: var(--theme-footer-text, #adb5bd); }
</style>
```

> Zawsze podawaj wartość fallback w `var()` — szablon działa poprawnie nawet bez skonfigurowanych kolorów.

### Zalecane konwencje nazewnictwa kluczy

| Klucz           | Przeznaczenie |
|-----------------|---------------|
| `primary`       | Główny kolor marki, przyciski, linki, akcenty |
| `header_bg`     | Tło nagłówka |
| `header_text`   | Kolor tekstu w nagłówku |
| `accent`        | Akcent drugorzędny (tagi, hover, odznaki) |
| `footer_bg`     | Tło stopki |
| `footer_text`   | Kolor tekstu w stopce |
| `body_bg`       | Tło strony |

Możesz definiować dowolną liczbę własnych kluczy.

---

## 6. Rozmiary obrazków (image_sizes)

System ImageCropService automatycznie generuje miniatury podczas przesyłania obrazków.
Miniaturki zapisywane są w `uploads/cropped/{sizeKey}/`.

### Definicja w theme.json

```json
"image_sizes": {
  "featured":      {"width": 1200, "height": 630,  "crop": true,  "label": "Obraz wyróżniający"},
  "post":          {"width": 800,  "height": 450,  "crop": true,  "label": "Obraz wpisu"},
  "thumbnail":     {"width": 300,  "height": 200,  "crop": true,  "label": "Miniatura"},
  "gallery":       {"width": 1200, "height": 800,  "crop": false, "label": "Galeria — pełny rozmiar"},
  "gallery_thumb": {"width": 300,  "height": 200,  "crop": true,  "label": "Miniatura galerii"}
}
```

| Parametr | Typ  | Opis |
|----------|------|------|
| `width`  | int  | Szerokość w pikselach (min. 1) |
| `height` | int  | Wysokość w pikselach (min. 1) |
| `crop`   | bool | `true` = center-crop, `false` = fit (zachowuje proporcje) |
| `label`  | string | Opis w panelu admina |

### Korzystanie z miniatur w szablonie

```php
// Oryginalny obrazek OG wpisu:
$ogSrc = $basePath . '/' . ltrim($post['og_image'], '/');

// Miniatura center-crop (jeśli wygenerowana przez ImageCropService):
$thumbSrc = $basePath . '/uploads/cropped/thumbnail/' . basename($post['og_image']);

// Bezpieczne wyświetlanie (fallback na oryginał):
$src = file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/cropped/thumbnail/' . basename($post['og_image']))
    ? $basePath . '/uploads/cropped/thumbnail/' . basename($post['og_image'])
    : $basePath . '/' . ltrim($post['og_image'], '/');
```

> Miniaturki są regenerowane przyciskiem **"Regeneruj miniatury"** w Ustawienia → Wygląd.

### Obrazki w galerii ($galleryImages)

Pola `path` i `url` w elementach `$galleryImages` mają różne formaty — traktuj je inaczej:

```php
foreach ($galleryImages as $_gi) {
    // path — surowa ścieżka z bazy (np. "uploads/img.jpg") — wymaga $basePath
    $_full  = htmlspecialchars($basePath . '/' . ltrim($_gi['path'], '/'), ENT_QUOTES);

    // url — absolutny URL obrazu złożony przez system jako $baseUrl . '/' . ltrim(path, '/')
    //        Jest to pełny URL z domeną (https://example.com/uploads/img.jpg), NIE thumbnail
    //        Użyj go bezpośrednio jako URL obrazu w src/href — nie doklejaj $basePath
    $_imageUrl = !empty($_gi['url']) ? htmlspecialchars($_gi['url'], ENT_QUOTES) : $_full;
}
```

| Pole   | Przykładowa wartość                                      | Format         | Użycie |
|--------|----------------------------------------------------------|----------------|--------|
| `path` | `uploads/2026/02/image.jpg`                              | ścieżka rel.   | `$basePath . '/' . ltrim($path, '/')` |
| `url`  | `https://example.com/uploads/2026/02/image.jpg`          | absolutny URL  | bezpośrednio w `src`/`href` — **nie doklejaj** `$basePath` |

> `url` to ten sam obraz co `path`, tylko w innym formacie. Żadne z tych pól nie jest miniaturą — thumbnail z ImageCropService jest dostępny jako osobna ścieżka w `uploads/cropped/{sizeKey}/`.

---

## 7. Funkcje opcjonalne (supports)

Klucze tablicy `supports` w theme.json aktywują funkcje systemu.

### Dostępne wartości

| Wartość          | Efekt |
|------------------|-------|
| `"sidebar"`      | System wczytuje widgety sidebara i przekazuje `$sidebarData`; template powinien renderować `_sidebar.php` |
| `"contact_page"` | Strona kontaktowa `/?page=contact` używa pliku `contact.php` z szablonu |
| `"gallery"`      | Wpisy mogą mieć galerię obrazów; `$galleryImages` jest przekazywana do `post.php` |
| `"lightbox"`     | Lightbox2 jest ładowany (CSS + JS z CDN); zmienna `$lightboxEnabled = true` |

### Sprawdzanie supports w szablonie

```php
// $lightboxEnabled jest już ustawione przez system — nie musisz sprawdzać theme.json
<?php if (!empty($lightboxEnabled)): ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" />
<?php endif; ?>

// $sidebarData jest pusta tablicą gdy sidebar nie jest obsługiwany lub nie ma widgetów
<?php if (!empty($sidebarData)): ?>
  <?php require __DIR__ . '/_sidebar.php'; ?>
<?php endif; ?>
```

---

## 8. Sidebar i widgety

Sidebar jest renderowany przez `_sidebar.php` przy użyciu danych z `$sidebarData`.

### Struktura $sidebarData

```php
$sidebarData = [
    [
        'type'  => 'popular_posts',   // typ widgetu
        'title' => 'Popularne wpisy', // tytuł (może być pusty)
        'data'  => [...],             // dane zależne od typu
    ],
    // ...kolejne widgety
];
```

### Typy widgetów i ich dane

#### `popular_posts` — najczęściej odwiedzane wpisy
```php
$widget['data'] = [
    ['page_title' => string, 'slug' => string, 'click_count' => int],
    // ...
]
```

#### `random_posts` — losowe wpisy
```php
$widget['data'] = [
    ['page_title' => string, 'slug' => string],
    // ...
]
```

#### `tag_cloud` — chmura tagów
```php
$widget['data'] = [
    ['name' => string, 'slug' => string],
    // ...
]
```

#### `categories` — lista kategorii
```php
$widget['data'] = [
    ['name' => string, 'slug' => string, 'color' => string, 'post_count' => int],
    // ...
]
```

#### `social_links` — ikony social media
```php
$widget['data'] = [
    'facebook'  => 'https://facebook.com/...',
    'instagram' => 'https://instagram.com/...',
    'twitter'   => '',   // pusty = nie wyświetlaj
    // ...klucze: facebook, instagram, twitter, youtube, linkedin, tiktok, pinterest, custom_url
]
```

#### `custom_html` — własny HTML
```php
$widget['data'] = '<p>Dowolny HTML</p>';  // string, wyświetlaj bezpośrednio przez echo
```

### Przykładowy rendering _sidebar.php

```php
<?php foreach ($sidebarData as $_sw): ?>
<aside class="sidebar-widget mb-4">
  <?php if (!empty($_sw['title'])): ?>
    <h3 class="widget-title"><?= htmlspecialchars($_sw['title']) ?></h3>
  <?php endif; ?>

  <?php if ($_sw['type'] === 'popular_posts' || $_sw['type'] === 'random_posts'): ?>
    <ul>
      <?php foreach ($_sw['data'] as $_p): ?>
        <li>
          <a href="<?= $basePath ?>/<?= htmlspecialchars($_p['slug']) ?>">
            <?= htmlspecialchars($_p['page_title']) ?>
          </a>
          <?php if ($_sw['type'] === 'popular_posts' && !empty($_p['click_count'])): ?>
            <small>(<?= (int)$_p['click_count'] ?> odsłon)</small>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>

  <?php elseif ($_sw['type'] === 'tag_cloud'): ?>
    <?php foreach ($_sw['data'] as $_t): ?>
      <a href="<?= $basePath ?>/?tag=<?= urlencode($_t['slug']) ?>" class="tag-pill">
        #<?= htmlspecialchars($_t['name']) ?>
      </a>
    <?php endforeach; ?>

  <?php elseif ($_sw['type'] === 'categories'): ?>
    <ul>
      <?php foreach ($_sw['data'] as $_c): ?>
        <li>
          <a href="<?= $basePath ?>/?category=<?= urlencode($_c['slug']) ?>">
            <?= htmlspecialchars($_c['name']) ?>
          </a>
          (<?= (int)$_c['post_count'] ?>)
        </li>
      <?php endforeach; ?>
    </ul>

  <?php elseif ($_sw['type'] === 'social_links'): ?>
    <?php foreach ($_sw['data'] as $_net => $_url): ?>
      <?php if (!empty($_url)): ?>
        <a href="<?= htmlspecialchars($_url) ?>" target="_blank" rel="noopener">
          <?= htmlspecialchars(ucfirst($_net)) ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>

  <?php elseif ($_sw['type'] === 'custom_html'): ?>
    <?= $_sw['data'] ?>

  <?php endif; ?>
</aside>
<?php endforeach; ?>
```

---

## 9. Lightbox — galeria ze zdjęciami

Jeśli szablon deklaruje `"lightbox"` w `supports`, system ładuje bibliotekę Lightbox2 v2.11.4 z CDN.

### Ładowanie CSS i JS (w post.php)

```php
<!-- W <head>: -->
<?php if (!empty($lightboxEnabled)): ?>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" />
<?php endif; ?>

<!-- Przed </body>: -->
<?php if (!empty($lightboxEnabled)): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<?php endif; ?>
```

> **Ważne:** Lightbox2 v2.11.4 wymaga jQuery. Bez jQuery skrypt ładuje się bez błędów, ale kliknięcia w obrazki otwierają je bezpośrednio w przeglądarce zamiast w overlaycie. Zawsze ładuj jQuery tuż przed lightbox.min.js.

### Renderowanie galerii

```php
<?php if (!empty($galleryImages)): ?>
  <?php if (!empty($lightboxEnabled)): ?>
    <!-- Lightbox gallery -->
    <div class="gallery-grid">
      <?php foreach ($galleryImages as $_img): ?>
        <?php $imgUrl = !empty($_img['url']) ? $_img['url'] : $basePath . '/' . ltrim($_img['path'], '/'); ?>
        <a href="<?= htmlspecialchars($imgUrl) ?>"
           data-lightbox="post-gallery"
           data-title="<?= htmlspecialchars($postTitle) ?>">
          <img src="<?= htmlspecialchars($imgUrl) ?>"
               alt="<?= htmlspecialchars($postTitle) ?>"
               loading="lazy" />
        </a>
      <?php endforeach; ?>
    </div>

  <?php else: ?>
    <!-- Prosta galeria bez lightbox -->
    <?php $firstImg = $galleryImages[0]; $firstUrl = !empty($firstImg['url']) ? $firstImg['url'] : $basePath . '/' . ltrim($firstImg['path'], '/'); ?>
    <img id="galleryMain" src="<?= htmlspecialchars($firstUrl) ?>" alt="<?= htmlspecialchars($postTitle) ?>" />
    <?php foreach ($galleryImages as $_i => $_img): ?>
      <?php $tUrl = !empty($_img['url']) ? $_img['url'] : $basePath . '/' . ltrim($_img['path'], '/'); ?>
      <img src="<?= htmlspecialchars($tUrl) ?>" loading="lazy"
           onclick="document.getElementById('galleryMain').src=this.src"
           style="cursor:pointer; width:80px; height:60px; object-fit:cover" />
    <?php endforeach; ?>
  <?php endif; ?>
<?php endif; ?>
```

### Przycisk "Sprawdź ofertę"

Przycisk pojawia się tylko dla wpisów z wypełnionym polem `target_url`. Link **musi** używać `$directLinkUrl` (przez router CMS), żeby kliknięcie zostało zliczone.

```php
<?php if (!empty($link['target_url'])): ?>
  <a href="<?= htmlspecialchars($directLinkUrl, ENT_QUOTES) ?>">
    Sprawdź ofertę
  </a>
<?php endif; ?>
```

| Zmienna | Wartość | Zlicza kliknięcia |
|---|---|---|
| `$directLinkUrl` | `https://example.com/slug` (przez router CMS) | **TAK** |
| `$link['target_url']` | `https://zewnetrzna-strona.pl/` (bezpośredni) | NIE |

> Nie dodawaj `target="_blank"` ani `rel="noopener"` — link przechodzi przez CMS, który sam obsługuje przekierowanie.

---

## 10. System ocen czytelników (reakcje)

Każdy wpis w trybie bloga może być oceniony przez czytelników za pomocą sześciu reakcji emoji. System działa w oparciu o wspólny partial `src/PostReactions.php` dołączany przez każdy szablon `post.php`.

### Jak to działa

| Aspekt | Szczegół |
|---|---|
| Limit głosowania | 1 głos / IP / post / 24 godziny |
| Toggle | Kliknięcie tej samej reakcji **cofa** głos |
| Zmiana | Kliknięcie innej reakcji **zamienia** głos (bez podwójnego liczenia) |
| Stan po odświeżeniu | Poprawny — serwer sprawdza bieżący głos i przekazuje `$myReaction` do szablonu |
| Aktualizacja UI | Optimistic — licznik zmienia się natychmiast; cofa się jeśli serwer zwróci błąd |
| Prywatność IP | Adresy IP są solone i hashowane SHA-256, nigdy nie trafiają do bazy w oryginale |

### Dostępne typy reakcji

| Klucz | Emoji | Etykieta |
|---|---|---|
| `happy` | 😊 | Super |
| `love` | ❤️ | Kocham to |
| `laugh` | 😂 | Śmieszne |
| `surprised` | 😮 | Wow |
| `cry` | 😢 | Smutne |
| `anger` | 😠 | Wkurzone |

### Dodanie widgetu do szablonu (zalecane)

Wstaw jeden `require` bezpośrednio **przed** przyciskiem CTA ("Przejdź do oferty"):

```php
<!-- Reakcje czytelników -->
<?php require __DIR__ . '/../../src/PostReactions.php'; ?>

<!-- CTA -->
<?php if (!empty($link['target_url'])): ?>
  <a href="<?= htmlspecialchars($directLinkUrl) ?>">Przejdź do oferty</a>
<?php endif; ?>
```

Partial jest **samowystarczalny** — nie wymaga żadnych zmian w szablonie poza tym jednym `require`. Automatycznie obsługuje:

| Co | Jak |
|---|---|
| Style CSS | `<link>` do `assets/post_react/post_react.css` emitowany raz dzięki fladze `static $_rxCssLoaded` |
| Ikony SVG | Inline w pliku — brak dodatkowych żądań HTTP |
| JavaScript | Inline `<script>` z logiką fetch |
| Stan przy ładowaniu | `$myReaction` przekazywany server-side — poprawny stan po odświeżeniu bez cookie |

> **Uwaga dla twórców szablonów:** Nie musisz dodawać żadnego `<link>` ani `<script>` do `<head>` szablonu.
> CSS zostanie załadowany automatycznie przy pierwszym renderowaniu widgetu na stronie.

### Endpoint AJAX

```
POST /__react
Content-Type: application/json

Żądanie:  { "link_id": 42, "reaction_type": "happy" }
Odpowiedź: { "ok": true, "my_reaction": "happy", "counts": { "happy": 5, "love": 2, ... } }

Toggle off: { "ok": true, "my_reaction": null, "counts": { ... } }
```

### Własna integracja (bez partiala)

Jeśli chcesz zintegrować reakcje z własnym designem zamiast używać gotowego partiala, korzystasz bezpośrednio ze zmiennych PHP:

```php
<div class="moje-reakcje" id="reactions-<?= (int)$link['id'] ?>">
  <?php foreach (\ReactionRepository::VALID_TYPES as $type): ?>
    <button type="button"
            data-reaction="<?= $type ?>"
            data-link-id="<?= (int)$link['id'] ?>"
            class="<?= $myReaction === $type ? 'active' : '' ?>">
      <!-- własna ikona -->
      <span class="count"><?= (int)$reactionCounts[$type] ?></span>
    </button>
  <?php endforeach; ?>
</div>

<script>
document.querySelectorAll('[data-reaction]').forEach(btn => {
  btn.addEventListener('click', () => {
    fetch('<?= $basePath ?>/__react', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        link_id: parseInt(btn.dataset.linkId),
        reaction_type: btn.dataset.reaction
      })
    })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        // zaktualizuj UI na podstawie data.counts i data.my_reaction
      }
    });
  });
});
</script>
```

### Pliki zasobów

```
App/assets/post_react/
├── post_react.css   ← Style widgetu (.post-reactions*)
├── happy.svg        ← Ikony używane w panelu admina (lista linków)
├── love.svg
├── laugh.svg
├── surprised.svg
├── cry.svg
└── anger.svg
```

**`post_react.css`** jest ładowany przez `PostReactions.php` automatycznie przy pierwszym renderowaniu:

```php
<?php static $_rxCssLoaded = false; if (!$_rxCssLoaded): $_rxCssLoaded = true; ?>
<link rel="stylesheet" href="<?= $_rxBasePath ?>/assets/post_react/post_react.css">
<?php endif; ?>
```

Flaga `static $_rxCssLoaded` działa w obrębie jednego żądania HTTP — gwarantuje, że `<link>` zostanie wyemitowany **dokładnie raz**, nawet jeśli `PostReactions.php` zostałby dołączony wielokrotnie.

Ikony SVG w widgecie są osadzone **inline** bezpośrednio w `PostReactions.php` — nie generują osobnych żądań HTTP. Pliki `.svg` w `assets/post_react/` służą wyłącznie panelowi admina (lista linków — miniaturki z licznikami głosów).

### Tabela bazy danych

```sql
CREATE TABLE `link_reactions` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `link_id`       INT UNSIGNED NOT NULL,
  `reaction_type` ENUM('happy','love','laugh','surprised','cry','anger') NOT NULL,
  `ip_hash`       VARCHAR(64)  NOT NULL COMMENT 'SHA-256(IP + sól)',
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ...
  CONSTRAINT `fk_link_reactions_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE
);
```

Tabela tworzona jest automatycznie przez `ReactionRepository::ensureTable()` przy pierwszym użyciu. Dla nowych instalacji jest uwzględniona w `App/install/db_schema.sql`.

### Panel admina

Na liście linków (`admin/?action=links`) pod istniejącymi statystykami kliknięć (D/7/Ł) wyświetlane są miniaturki SVG z licznikami głosów — widoczne tylko dla postów które mają przynajmniej jeden głos.

---

## 11. Nawigacja, strony custom i kontakt

### Nawigacja — $navPages

Strony wyróżnione jako "pokazuj w nawigacji" w panelu admina:

```php
<nav>
  <a href="<?= $basePath ?>/">Blog</a>
  <?php foreach ($navPages as $_np): ?>
    <a href="<?= $basePath ?>/?page=<?= htmlspecialchars($_np['slug']) ?>"
       class="<?= $_np['slug'] === ($currentSlug ?? '') ? 'active' : '' ?>">
      <?= htmlspecialchars($_np['title']) ?>
    </a>
  <?php endforeach; ?>
  <?php if (!empty($contactEnabled)): ?>
    <a href="<?= $basePath ?>/?page=contact">Kontakt</a>
  <?php endif; ?>
</nav>
```

### Custom Pages

Wyświetlane przez `page.php`. Zawartość HTML z edytora Trix przekazywana jako `$pageHtml` — można wyświetlić bezpośrednio przez `echo $pageHtml`.

### Strona kontaktowa

Formularz musi zawierać:
1. Ukryty token CSRF: `<input type="hidden" name="csrf_contact" value="<?= htmlspecialchars($contactCsrf) ?>">`
2. Pole `name` (opcjonalne)
3. Pole `email` (wymagane)
4. Pole `message` (wymagane, max 1000 znaków)
5. Akcja formularza: `<?= $basePath ?>/?page=contact`

---

## 12. Budowanie URL

### Przyjazne URL a query string

Zmienna `$prettyUrls` (bool) decyduje o formacie linków:

```php
// Kategoria
$categoryUrl = !empty($prettyUrls)
    ? $basePath . '/category/' . urlencode($slug)
    : $basePath . '/?category=' . urlencode($slug);

// Tag
$tagUrl = !empty($prettyUrls)
    ? $basePath . '/tag/' . urlencode($slug)
    : $basePath . '/?tag=' . urlencode($slug);

// Wpis bloga (widok posta — szablon post.php)
$postUrl = $basePath . '/blog/' . urlencode($slug);

// Wpis bloga — przycisk "Sprawdź ofertę" (przechodzi przez router CMS, zlicza kliknięcia)
// Warunek: !empty($link['target_url']) — pole DB z docelowym URL
// Href:    $directLinkUrl — router CMS (zlicza kliknięcia), NIE $link['target_url'] (pomija licznik)
$offerUrl = $directLinkUrl; // == $baseUrl . '/' . $slug

// Custom Page
$pageUrl = $basePath . '/?page=' . urlencode($slug);

// Kontakt
$contactUrl = $basePath . '/?page=contact';

// Strona główna
$homeUrl = $basePath . '/';

// Paginacja bloga (parametr `p`, NIE `page` — `page` jest zarezerwowane dla routingu stron)
$paginationUrl = $basePath . '/?p=' . $pageNumber;
// Z filtrem kategorii (wariant z parametrem `category`):
$paginationUrlWithCategory = $basePath . '/?category=' . urlencode($categorySlug) . '&p=' . $pageNumber;

// Obrazek (ścieżka relatywna z bazy)
$imgSrc = $basePath . '/' . ltrim($post['og_image'], '/');

// Logo
$logoSrc = $basePath . '/' . ltrim($brandingLogo, '/');
```

---

## 13. Partiale i pliki pomocnicze

Szablon `RedirectCMS` korzysta ze wspólnych partiali dołączanych przez `require`:

```php
// Partiale wewnątrz katalogu szablonu (np. templates/MojSzablon/)
require __DIR__ . '/_head_css.php';        // <link> do Bootstrap + themeCss + własne style
require __DIR__ . '/_navbar.php';          // navbar z hamburgerem i wyszukiwarką
require __DIR__ . '/_footer.php';          // 4-kolumnowa stopka
require __DIR__ . '/_sidebar.php';         // widgety sidebara

// Komponenty współdzielone (App/src/) — dołączane z poziomu szablonu
require __DIR__ . '/../../src/PostReactions.php'; // widget ocen czytelników — używaj w post.php
```

Możesz tworzyć własne partiale w katalogu szablonu i dołączać je przez `require __DIR__ . '/_moj-partial.php'`.

### Konwencja nazewnictwa

- Partiale (nie renderowane samodzielnie) — nazwa zaczyna się od `_` (np. `_header.php`, `_sidebar.php`)
- Główne widoki — bez prefiksu (np. `home.php`, `post.php`)

---

## 14. Mechanizm fallback

Jeśli szablon nie posiada danego pliku, system próbuje załadować go z szablonu `default`:

1. `templates/{aktywny_szablon}/post.php` → jeśli nie istnieje:
2. `templates/default/post.php` → użyty jako fallback

Szablon musi być **kompletny** (mieć `home.php` i `post.php`) aby był uznany za ważny.
Jeśli wybrany szablon nie istnieje lub jest niekompletny — system automatycznie wraca do `default`.

---

## 15. Tworzenie nowego szablonu — krok po kroku

### Krok 1: Utwórz folder

```
App/templates/MojNowySzablon/
```

### Krok 2: Utwórz theme.json

```json
{
  "name": "Mój Nowy Szablon",
  "author": "Twoje Imię",
  "version": "1.0.0",
  "description": "Opis szablonu",
  "supports": ["sidebar", "contact_page", "gallery", "lightbox"],
  "colors": [
    {"key": "primary",     "label": "Kolor główny",  "default": "#3490dc"},
    {"key": "header_bg",   "label": "Tło nagłówka",  "default": "#1a202c"},
    {"key": "header_text", "label": "Tekst nagłówka","default": "#ffffff"},
    {"key": "footer_bg",   "label": "Tło stopki",    "default": "#1a202c"},
    {"key": "footer_text", "label": "Tekst stopki",  "default": "#a0aec0"},
    {"key": "body_bg",     "label": "Tło strony",    "default": "#f7fafc"}
  ],
  "image_sizes": {
    "featured":  {"width": 1200, "height": 630, "crop": true, "label": "Obraz wyróżniający"},
    "post":      {"width": 800,  "height": 450, "crop": true, "label": "Obraz wpisu"},
    "gallery":   {"width": 1200, "height": 800, "crop": false,"label": "Galeria"},
    "gallery_thumb": {"width": 300, "height": 200, "crop": true, "label": "Miniatura galerii"}
  }
}
```

### Krok 3: Utwórz home.php (minimum)

```php
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($homeMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars(strip_tags($homeMetaDescription)) ?>">
  <?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    body { background: var(--theme-body-bg, #f7fafc); }
    /* Twój CSS tutaj */
  </style>
</head>
<body>

<header style="background: var(--theme-header-bg, #1a202c); color: var(--theme-header-text, #fff); padding: 1rem 0;">
  <div class="container d-flex align-items-center gap-3">
    <?php if (!empty($brandingLogo)): ?>
      <a href="<?= $basePath ?>/">
        <img src="<?= htmlspecialchars($basePath . '/' . ltrim($brandingLogo, '/')) ?>"
             alt="<?= htmlspecialchars($homeTitle) ?>" style="height:40px">
      </a>
    <?php endif; ?>
    <a href="<?= $basePath ?>/" style="color:inherit; text-decoration:none; font-size:1.5rem; font-weight:700">
      <?= htmlspecialchars($homeTitle) ?>
    </a>
  </div>
</header>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <?php foreach ($blogPosts as $_post): ?>
        <?php
          $hasImg = !empty($_post['og_image']) && !empty($blogShowImages);
          $desc   = mb_substr(strip_tags($_post['page_description'] ?? ''), 0, $blogDescLength);
        ?>
        <article class="card mb-4">
          <?php if ($hasImg): ?>
            <img src="<?= htmlspecialchars($basePath . '/' . ltrim($_post['og_image'], '/')) ?>"
                 class="card-img-top" style="height:200px; object-fit:cover" loading="lazy">
          <?php endif; ?>
          <div class="card-body">
            <h2 class="card-title h5">
              <a href="<?= $basePath ?>/blog/<?= htmlspecialchars($_post['slug']) ?>">
                <?= htmlspecialchars($_post['page_title'] ?? $_post['slug']) ?>
              </a>
            </h2>
            <?php if (!empty($desc)): ?>
              <p class="card-text text-muted small"><?= htmlspecialchars($desc) ?>…</p>
            <?php endif; ?>
            <div class="d-flex align-items-center justify-content-between">
              <small class="text-muted"><?= substr($_post['created_at'] ?? '', 0, 10) ?></small>
              <a href="<?= $basePath ?>/blog/<?= htmlspecialchars($_post['slug']) ?>"
                 class="btn btn-sm btn-primary">Czytaj więcej</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>

      <!-- Paginacja -->
      <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <nav><ul class="pagination">
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
              <a class="page-link" href="<?= $basePath ?>/?p=<?= $p ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
        </ul></nav>
      <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <?php if (!empty($sidebarData)): ?>
        <?php require __DIR__ . '/_sidebar.php'; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<footer style="background: var(--theme-footer-bg, #1a202c); color: var(--theme-footer-text, #a0aec0); padding: 2rem 0; margin-top: 3rem;">
  <div class="container text-center">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>
    <?php if (!empty($homeFooter)) echo $homeFooter; ?>
  </div>
</footer>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

### Krok 4: Utwórz post.php (minimum)

```php
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($postTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags($postDescription ?? ''), 0, 160)) ?>">
  <meta property="og:type"        content="article">
  <meta property="og:title"       content="<?= htmlspecialchars($postTitle) ?>">
  <meta property="og:url"         content="<?= htmlspecialchars($shareUrl) ?>">
  <?php if (!empty($ogImageUrl)): ?>
    <meta property="og:image"     content="<?= htmlspecialchars($ogImageUrl) ?>">
  <?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <?php if (!empty($lightboxEnabled)): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
  <?php endif; ?>
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
</head>
<body>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <article>
        <h1><?= htmlspecialchars($postTitle) ?></h1>
        <div class="text-muted small mb-3"><?= substr($postCreatedAt ?? '', 0, 10) ?></div>

        <?php if (!empty($ogImageUrl)): ?>
          <img src="<?= htmlspecialchars($ogImageUrl) ?>" class="img-fluid mb-3">
        <?php endif; ?>

        <!-- Treść wpisu — używaj Utils::sanitizeHtml() dla bezpieczeństwa -->
        <div class="post-content">
          <?= Utils::sanitizeHtml($postDescription ?? '') ?>
        </div>

        <!-- Galeria -->
        <?php if (!empty($galleryImages)): ?>
          <div class="row g-2 mt-3">
            <?php foreach ($galleryImages as $_img): ?>
              <?php $imgUrl = !empty($_img['url']) ? $_img['url'] : $basePath . '/' . ltrim($_img['path'], '/'); ?>
              <?php if (!empty($lightboxEnabled)): ?>
                <div class="col-4">
                  <a href="<?= htmlspecialchars($imgUrl) ?>" data-lightbox="gallery">
                    <img src="<?= htmlspecialchars($imgUrl) ?>" class="img-fluid" loading="lazy">
                  </a>
                </div>
              <?php else: ?>
                <div class="col-4">
                  <img src="<?= htmlspecialchars($imgUrl) ?>" class="img-fluid" loading="lazy">
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Reakcje czytelników -->
        <?php require __DIR__ . '/../../src/PostReactions.php'; ?>

        <div class="mt-4">
          <?php if (!empty($link['target_url'])): ?>
            <a href="<?= htmlspecialchars($directLinkUrl) ?>" class="btn btn-primary">Przejdź do oferty</a>
          <?php endif; ?>
          <a href="<?= $basePath ?>/" class="btn btn-outline-secondary">&larr; Wróć</a>
        </div>
      </article>
    </div>

    <div class="col-lg-4">
      <?php if (!empty($sidebarData)): ?>
        <?php require __DIR__ . '/_sidebar.php'; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($lightboxEnabled)): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<?php endif; ?>
</body>
</html>
```

### Krok 5: Aktywuj w panelu admina

Admin → Ustawienia → Wygląd → pole **"Szablon bloga"** → wpisz nazwę folderu → Zapisz.

---

## 16. Dobre praktyki i bezpieczeństwo

### Escapowanie danych wyjściowych

```php
// Zawsze escapuj dane z bazy przed wyświetleniem:
echo htmlspecialchars($post['page_title']);

// Do treści HTML (trusted HTML z edytora):
echo Utils::sanitizeHtml($postDescription);  // Filtruje niebezpieczne tagi/atrybuty

// NIE używaj echo bezpośrednio na danych z bazy bez escapowania!
// echo $post['page_title'];  ← NIEBEZPIECZNE
```

### URL-e

```php
// Zawsze używaj urlencode() dla parametrów w URL:
$url = $basePath . '/?category=' . urlencode($categorySlug);

// Zawsze używaj htmlspecialchars() dla atrybutów HTML:
<a href="<?= htmlspecialchars($url) ?>">...</a>
```

### Sprawdzanie isset/empty

```php
// Zawsze sprawdzaj czy zmienna istnieje przed użyciem:
<?php if (!empty($brandingLogo)): ?>
  <img src="...">
<?php endif; ?>

// Null coalescing operator:
$title = $post['page_title'] ?? $post['slug'] ?? '';
```

### Responsywność

- Używaj Bootstrap 5.3.8 (już załadowany przez `$homeHeaderCode` lub własny link)
- Testuj na mobile (375px), tablet (768px) i desktop (1440px)
- Sidebar powinien przesuwać się pod treść na mobile (`col-lg-4`)

### Open Graph i SEO

Każdy widok powinien mieć pełny zestaw meta tagów:
```php
<!-- Podstawowe -->
<title><?= htmlspecialchars($postTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags($postDescription), 0, 160)) ?>">

<!-- Open Graph -->
<meta property="og:type"        content="article">
<meta property="og:title"       content="<?= htmlspecialchars($postTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars(mb_substr(strip_tags($postDescription), 0, 200)) ?>">
<meta property="og:url"         content="<?= htmlspecialchars($shareUrl) ?>">
<?php if (!empty($ogImageUrl)): ?>
  <meta property="og:image"     content="<?= htmlspecialchars($ogImageUrl) ?>">
  <meta property="og:image:width"  content="1200">
  <meta property="og:image:height" content="630">
<?php endif; ?>
<?php if (!empty($postCreatedAt)): ?>
  <meta property="article:published_time" content="<?= htmlspecialchars($postCreatedAt) ?>">
<?php endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= htmlspecialchars($postTitle) ?>">
<?php if (!empty($ogImageUrl)): ?>
  <meta name="twitter:image"     content="<?= htmlspecialchars($ogImageUrl) ?>">
<?php endif; ?>
```

### Kolejność ładowania CSS

```html
<!-- 1. Bootstrap (CDN) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- 2. Lightbox (jeśli supports lightbox) -->
<?php if (!empty($lightboxEnabled)): ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
<?php endif; ?>
<!-- 3. Kolory szablonu (CSS custom properties) -->
<?php echo $themeCss ?? ''; ?>
<!-- 4. Custom header code z ustawień admina -->
<?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
<!-- 5. Twój własny CSS (może nadpisywać powyższe) -->
<style>
  /* Twój CSS */
</style>
```

---

*Dokumentacja RedirectCMS — system szablonów v1.0 — marzec 2026*
