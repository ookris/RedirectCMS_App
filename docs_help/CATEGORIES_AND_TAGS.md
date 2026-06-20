# Kategorie i tagi linków

System kategorii i tagów w RedirectCMS pozwala na lepszą organizację linków oraz filtrowanie ich w trybie blog.

## Spis treści
- [Czym są kategorie i tagi?](#czym-są-kategorie-i-tagi)
- [Migracja bazy danych](#migracja-bazy-danych)
- [Zarządzanie kategoriami](#zarządzanie-kategoriami)
- [Zarządzanie tagami](#zarządzanie-tagami)
- [Przypisywanie do linków](#przypisywanie-do-linków)
- [Filtrowanie w trybie blog](#filtrowanie-w-trybie-blog)
- [API](#api)

## Czym są kategorie i tagi?

### Kategorie
- **Relacja**: Jeden link = jedna kategoria (lub brak)
- **Zastosowanie**: Główna klasyfikacja linków (np. "Promocje", "Artykuły", "Produkty")
- **Właściwości**: Nazwa, slug, opis, kolor (do wizualizacji)
- **Wyświetlanie**: Badge z kolorowym tłem przy linkach

### Tagi
- **Relacja**: Jeden link = wiele tagów (many-to-many)
- **Zastosowanie**: Szczegółowe oznaczanie tematyki (np. "zima", "rabat", "nowość")
- **Właściwości**: Nazwa, slug
- **Wyświetlanie**: Małe odznaki przy linkach
- **Auto-tworzenie**: Tagi są tworzone automatycznie przy zapisie linku jeśli nie istnieją

## Migracja bazy danych

```bash
# Uruchom migrację 007
mysql -u [user] -p [database] < migrations/007_add_categories_and_tags.sql
```

Migracja tworzy następujące tabele:
- `categories` - tabela kategorii
- `tags` - tabela tagów
- `link_tags` - tabela powiązań linków z tagami (many-to-many)
- Dodaje kolumnę `category_id` do tabeli `links`

## Zarządzanie kategoriami

### Panel administracyjny
1. Przejdź do **Kategorie** w menu nawigacji
2. Kliknij **+ Nowa kategoria**

### Właściwości kategorii
- **Nazwa** (wymagana) - wyświetlana nazwa kategorii
- **Slug** (opcjonalny) - unikalny identyfikator URL, generowany automatycznie z nazwy jeśli pusty
- **Opis** (opcjonalny) - krótki opis kategorii
- **Kolor** (wymagany) - kolor HEX używany do wizualizacji (#6c757d domyślnie)

### Użycie w kodzie
```php
$categoryRepo = new CategoryRepository($pdo);

// Pobierz wszystkie kategorie
$categories = $categoryRepo->list();

// Pobierz kategorię po ID
$category = $categoryRepo->getById(1);

// Pobierz kategorię po slug
$category = $categoryRepo->getBySlug('promocje');

// Utwórz kategorię
$id = $categoryRepo->create('Promocje', 'promocje', 'Promocje i rabaty', '#ff6b6b');

// Zaktualizuj kategorię
$categoryRepo->update(1, 'Promocje 2025', 'promocje-2025', 'Nowy opis', '#ff6b6b');

// Usuń kategorię
$categoryRepo->delete(1);
```

## Zarządzanie tagami

### Panel administracyjny
1. Przejdź do **Tagi** w menu nawigacji
2. Kliknij **+ Nowy tag**

### Właściwości tagu
- **Nazwa** (wymagana) - wyświetlana nazwa tagu
- **Slug** (opcjonalny) - unikalny identyfikator URL, generowany automatycznie z nazwy jeśli pusty

### Użycie w kodzie
```php
$tagRepo = new TagRepository($pdo);

// Pobierz wszystkie tagi
$tags = $tagRepo->list();

// Pobierz tag po ID
$tag = $tagRepo->getById(1);

// Pobierz tag po slug
$tag = $tagRepo->getBySlug('zima');

// Utwórz tag
$id = $tagRepo->create('Zima', 'zima');

// Zaktualizuj tag
$tagRepo->update(1, 'Zima 2025', 'zima-2025');

// Usuń tag
$tagRepo->delete(1);

// Znajdź lub utwórz tag (auto-create)
$tagId = $tagRepo->findOrCreate('nowość'); // Zwróci ID istniejącego lub nowego tagu

// Przypisz tagi do linku
$tagRepo->setTagsForLink(5, [1, 2, 3]); // Link ID 5 -> tagi 1, 2, 3

// Pobierz tagi dla linku
$tags = $tagRepo->getTagsForLink(5);
```

## Przypisywanie do linków

### W formularzu linku

#### Kategoria
- Wybierz z listy rozwijanej
- Można wybrać tylko jedną kategorię lub zostawić puste

#### Tagi
- Wpisz nazwy tagów oddzielone przecinkami: `promocja, zima, rabat`
- Tagi są tworzone automatycznie jeśli nie istnieją
- Kliknij istniejący tag aby szybko go dodać

### Kod PHP
```php
$linkRepo = new LinkRepository($pdo);

// Utwórz link z kategorią
$linkId = $linkRepo->create(
    'promo-2025',
    'https://example.com',
    5,
    'Super promocja',
    'Opis promocji',
    null,
    1  // ID kategorii
);

// Dodaj tagi do linku
$tagRepo = new TagRepository($pdo);
$tagIds = [];
foreach (['promocja', 'zima', 'rabat'] as $tagName) {
    $tagIds[] = $tagRepo->findOrCreate($tagName);
}
$tagRepo->setTagsForLink($linkId, $tagIds);

// Pobierz link z relacjami
$link = $linkRepo->getByIdWithRelations($linkId);
// Zwraca:
// - $link['category'] - dane kategorii lub null
// - $link['tags'] - tablica tagów
```

## Filtrowanie w trybie blog

### Automatyczne filtrowanie
Gdy strona główna jest w trybie "blog", użytkownicy mogą filtrować wpisy:
- **Po kategorii**: `/?category=promocje`
- **Po tagu**: `/?tag=zima`

### Menu filtrów
Szablon bloga automatycznie wyświetla:
- Listę wszystkich kategorii (z kolorami)
- Listę wszystkich tagów
- Podświetlenie aktywnego filtra

### Kod PHP
```php
$linkRepo = new LinkRepository($pdo);

// Pobierz linki z konkretnej kategorii
$categoryRepo = new CategoryRepository($pdo);
$category = $categoryRepo->getBySlug('promocje');
$links = $linkRepo->list(10, 0, $category['id']);

// Pobierz linki z konkretnym tagiem
$tagRepo = new TagRepository($pdo);
$tag = $tagRepo->getBySlug('zima');
$links = $linkRepo->list(10, 0, null, $tag['id']);

// Paginacja
$links = $linkRepo->list(
    10,     // limit (ilość na stronę)
    0,      // offset (0 = pierwsza strona, 10 = druga strona, itd.)
    1,      // categoryId (opcjonalnie)
    null    // tagId (opcjonalnie)
);
```

## API

### CategoryRepository

```php
class CategoryRepository
{
    // Pobierz listę kategorii z liczbą przypisanych linków
    public function list(int $limit = 100, int $offset = 0): array;
    
    // Pobierz kategorię po ID
    public function getById(int $id): ?array;
    
    // Pobierz kategorię po slug
    public function getBySlug(string $slug): ?array;
    
    // Utwórz nową kategorię
    public function create(string $name, string $slug, ?string $description = null, string $color = '#6c757d'): int;
    
    // Zaktualizuj kategorię
    public function update(int $id, string $name, string $slug, ?string $description = null, string $color = '#6c757d'): void;
    
    // Usuń kategorię
    public function delete(int $id): void;
    
    // Sprawdź czy slug istnieje
    public function slugExists(string $slug, ?int $excludeId = null): bool;
}
```

### TagRepository

```php
class TagRepository
{
    // Pobierz listę tagów z liczbą przypisanych linków
    public function list(int $limit = 100, int $offset = 0): array;
    
    // Pobierz tag po ID
    public function getById(int $id): ?array;
    
    // Pobierz tag po slug
    public function getBySlug(string $slug): ?array;
    
    // Utwórz nowy tag
    public function create(string $name, string $slug): int;
    
    // Zaktualizuj tag
    public function update(int $id, string $name, string $slug): void;
    
    // Usuń tag
    public function delete(int $id): void;
    
    // Sprawdź czy slug istnieje
    public function slugExists(string $slug, ?int $excludeId = null): bool;
    
    // Pobierz tagi dla linku
    public function getTagsForLink(int $linkId): array;
    
    // Przypisz tagi do linku (nadpisuje istniejące)
    public function setTagsForLink(int $linkId, array $tagIds): void;
    
    // Znajdź lub utwórz tag po nazwie
    public function findOrCreate(string $name): int;
}
```

### LinkRepository (rozszerzenia)

```php
class LinkRepository
{
    // Pobierz linki z opcjonalnym filtrowaniem po kategorii i tagu
    public function list(int $limit = 100, int $offset = 0, ?int $categoryId = null, ?int $tagId = null): array;
    
    // Pobierz link z pełnymi relacjami (kategoria + tagi)
    public function getByIdWithRelations(int $id): ?array;
    
    // Pobierz tagi dla linku
    public function getTagsForLink(int $linkId): array;
}
```

## Przykłady użycia

### Tworzenie struktury kategorii i tagów

```php
// Utwórz kategorie
$categoryRepo = new CategoryRepository($pdo);
$promoId = $categoryRepo->create('Promocje', 'promocje', 'Promocje i rabaty', '#ff6b6b');
$articleId = $categoryRepo->create('Artykuły', 'artykuly', 'Artykuły blogowe', '#4ecdc4');
$productId = $categoryRepo->create('Produkty', 'produkty', 'Strony produktów', '#95e1d3');

// Utwórz tagi
$tagRepo = new TagRepository($pdo);
$tagRepo->create('Zima', 'zima');
$tagRepo->create('Lato', 'lato');
$tagRepo->create('Nowość', 'nowosc');
$tagRepo->create('Rabat', 'rabat');
```

### Tworzenie linku z kategorią i tagami

```php
$linkRepo = new LinkRepository($pdo);
$tagRepo = new TagRepository($pdo);

// Utwórz link
$linkId = $linkRepo->create(
    'super-promo',
    'https://example.com/promo',
    5,
    'Super promocja zimowa',
    'Nie przegap naszej zimowej oferty!',
    '/uploads/2025/01/promo.jpg',
    $promoId  // Kategoria: Promocje
);

// Dodaj tagi
$tagIds = [
    $tagRepo->findOrCreate('Zima'),
    $tagRepo->findOrCreate('Rabat'),
    $tagRepo->findOrCreate('Nowość')
];
$tagRepo->setTagsForLink($linkId, $tagIds);
```

### Wyświetlanie linków z kategoriami i tagami

```php
$linkRepo = new LinkRepository($pdo);
$links = $linkRepo->list(10, 0);

foreach ($links as $link) {
    echo "Link: " . $link['slug'] . "\n";
    
    // Wyświetl kategorię (jeśli jest)
    if (!empty($link['category_name'])) {
        echo "Kategoria: " . $link['category_name'] . " (" . $link['category_color'] . ")\n";
    }
    
    // Pobierz i wyświetl tagi
    $tags = $linkRepo->getTagsForLink($link['id']);
    if (!empty($tags)) {
        echo "Tagi: " . implode(', ', array_column($tags, 'name')) . "\n";
    }
    
    echo "\n";
}
```

## Najlepsze praktyki

1. **Nazwy kategorii**: Używaj krótkich, opisowych nazw (max 2-3 słowa)
2. **Kolory kategorii**: Wybieraj kontrastowe kolory dla lepszej czytelności
3. **Tagi**: Używaj małych liter, krótkich słów, spójnej konwencji nazewnictwa
4. **Slug**: Zostaw puste aby wygenerować automatycznie z nazwy
5. **Ograniczenie tagów**: Nie przypisuj więcej niż 5-7 tagów do jednego linku
6. **Konsystencja**: Przed dodaniem nowego tagu sprawdź czy podobny już nie istnieje
7. **Kategorie vs Tagi**: Kategorie dla szerokich grup, tagi dla szczegółów

## Integracja z istniejącym kodem

System kategorii i tagów jest w pełni kompatybilny z istniejącą funkcjonalnością:
- ✅ Tryb landing (strona główna)
- ✅ Tryb redirect (przekierowanie na inny URL)
- ✅ Tryb blog (lista wpisów)
- ✅ Statystyki linków
- ✅ Open Graph metadata
- ✅ Geolokalizacja

## Troubleshooting

### Kategoria/tag nie wyświetla się
- Sprawdź czy link ma przypisaną kategorię/tagi w bazie danych
- Upewnij się że szablon bloga został zaktualizowany
- Wyczyść cache przeglądarki

### Błąd przy tworzeniu kategorii/tagu
- Sprawdź czy slug nie jest już użyty
- Upewnij się że nazwa nie jest pusta
- Sprawdź czy kolor ma prawidłowy format HEX (#RRGGBB)

### Filtrowanie nie działa w trybie blog
- Sprawdź czy strona główna jest w trybie "blog" (Ustawienia → Tryb strony głównej)
- Upewnij się że kategoria/tag istnieje w bazie
- Sprawdź parametry URL: `?category=slug` lub `?tag=slug`

### Auto-tworzenie tagów nie działa
- Sprawdź czy używasz metody `TagRepository::findOrCreate()` zamiast `create()`
- Upewnij się że nazwa tagu nie jest pusta
- Zweryfikuj format danych wejściowych (string, nie array)
