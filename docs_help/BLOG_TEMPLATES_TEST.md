# Test Szablonów Bloga

## Instrukcja testowania

### 1. Ustawienie szablonów

Wejdź na `/admin/index.php?action=settings` i w sekcji "Ustawienia blogu":
- Ustaw `blog_theme` na kolejne wartości:
  - `minimal` - Minimalny szablon
  - `elegant` - Elegancki szablon z gradientami
  - `modern` - Nowoczesny szablon dark mode
  - `default` - Domyślny szablon (jeśli istnieje)

### 2. Przygotowanie postów

Upewnij się, że masz kilka postów:
- Dodaj linki z :
  - `page_title` i `page_description` (dla lepszego wyświetlenia)
  - Przypisz je do **różnych kategorii** (np. Tech, Design, Business)
  - Dodaj **tagi** do postów (np. #javascript, #css, #design)
  - Opcjonalnie: Dodaj `og_image` dla każdego wpisu
  - Ustaw `home_mode` na `blog` aby włączyć tryb bloga

### 3. Testowanie szablonów

Wejdź na `/` i sprawdź każdy szablon:

#### Minimalny (`minimal`)
- [ ] Czytelny tekst Georgia serif
- [ ] Sidebar z kategoriami (z liczbą postów)
- [ ] Sidebar z tagami
- [ ] Karty postów są klikalne
- [ ] Hover efekt na kartach
- [ ] Responsive na mobile (sidebar under posts)

#### Elegancki (`elegant`)
- [ ] Gradient header (667eea → 764ba2)
- [ ] CSS Grid layout 3-kolumnowy (desktop), 1-kolumnowy (mobile)
- [ ] Karty postów z cieniowaniem
- [ ] Hover: translate Y-8px + enlarged shadow
- [ ] Sticky sidebar
- [ ] Gradient separatory
- [ ] Zaokrąglone rogi (12px)

#### Nowoczesny (`modern`)
- [ ] Dark mode background (#0f1117)
- [ ] Neon accent colors (cyan #00d4ff, purple #6d28d9, orange #f97316)
- [ ] Radial gradient circles w tle headera
- [ ] Gradient text dla tytułów
- [ ] Border w place shadow (rgba colors)
- [ ] Tech-forward design language
- [ ] Przezroczyste panele

### 4. Testowanie kategorii i tagów

#### Na stronie głównej (home.php):
- Klikni na kategorię → URL zmienia się na `/?category=slug`
- Klikni na tag → URL zmienia się na `/?tag=slug`
- Lista postów filtruje się poprawnie
- Liczby postów są prawidłowe

### 5. Testowanie powiązanych wpisów

1. Otwórz dowolny wpis (np. `/blog/my-post`)
2. Sprawdź:
   - [ ] Wyświetla się sekcja "Powiązane wpisy"
   - [ ] Są wyświetlane wpisy z tej samej **kategorii** LUB te same **tagi**
   - [ ] Wyświetla się maximum 3 powiązane wpisy
   - [ ] Kliknięcie na powiązany wpis przenosi do niego
   - [ ] Sidebar pokazuje kategorie i tagi tego wpisu

### 6. Testowanie metadanych

#### Open Graph (dla mediów społeczniowych):
- Udostępnij link wpisu na Facebooku/Twitterze
- Sprawdź czy wyświetla się:
  - [ ] Tytuł wpisu (`og:title`)
  - [ ] Opis (`og:description`)
  - [ ] Obrazek (`og:image`)
  - [ ] URL (`og:url`)

### 7. Responsywność

Testuj każdy szablon na:
- [ ] Desktop (1920px) - sprawdź CSS Grid layout
- [ ] Tablet (768px) - sprawdź grid przejścia
- [ ] Mobile (375px) - sprawdź sidebar alignment
- [ ] Przesuwanie (scrolling) jest płynne
- [ ] Sticky sidebar pracuje poprawnie

### 8. Wydajność

- [ ] Brak console errors w DevTools
- [ ] CSS ładuje się poprawnie
- [ ] Obrazy ładują się z CDN (Bootstrap, flag-icons)
- [ ] Animacje są płynne (60fps)

## Checklist gotowości

- [ ] Wszystkie 3 szablony działają
- [ ] Filtrowanie po kategoriach/tagach działa
- [ ] Powiązane wpisy wyświetlają się
- [ ] Sidebar pokazuje kategorię i tagi
- [ ] Responsive design na wszystkich rozmiarach
- [ ] Open Graph metadata jest prawidłowe
- [ ] Brak errórów w konsoli przeglądarki
- [ ] Custom HTML kod z header/footer wykonuje się

## Troubleshooting

### Szablon nie wyświetla się
1. Sprawdź czy plik istnieje: `templates/{theme}/home.php`
2. Sprawdź czy `theme.json` ma prawidłową strukturę
3. Sprawdź ustawienie `blog_theme` w `settings`
4. Sprawdź czy `home_mode` = `blog`

### Powiązane wpisy się nie wyświetlają
1. Sprawdź czy wpisy mają przypisane kategorie lub tagi
2. Sprawdź czy są w tym samym zakresie dat (publish_at/expires_at)
3. Sprawdź czy metoda `LinkRepository::getRelatedPosts()` jest dostępna

### Sidebar nie wyświetla się
1. Sprawdź czy kategorie i tagi są przypisane do wpisów
2. Sprawdź czy `$allCategories` i `$allTags` są dostępne w szablonie
3. Sprawdź HTML strukturę w template (czy element `.sidebar` istnieje)

### Filtrowanie nie działa
1. Sprawdź czy URL zawiera `?category=` lub `?tag=`
2. Sprawdź czy category/tag slug jest prawidłowy
3. Sprawdź czy categoria/tag istnieje w bazie
4. Sprawdź czy wpisy są przypisane do kategorii/tagów
