# Tryb Blog - RedirectCMS

## Opis funkcji

Tryb blog pozwala na wyświetlanie wszystkich dostępnych w systemie linków jako wpisów blogowych na stronie głównej. Jest to alternatywa dla standardowego landing page'a lub przekierowania.

## Dwa typy linków

W trybie blog każdy link ma **dwa sposoby dostępu**:

1. **Link do wpisu bloga** (`/blog/slug`)
   - Wyświetla pełną stronę wpisu
   - Zawiera tytuł, obrazek, pełny opis
   - Meta tagi Open Graph dla mediów społecznościowych
   - Przycisk "Przejdź do docelowej strony"
   - Link bezpośredni do kopiowania
   - Breadcrumbs i przycisk powrotu

2. **Link bezpośredni** (`/slug`)
   - Klasyczne przekierowanie (jak dotychczas)
   - Działa ze wszystkimi istniejącymi funkcjami
   - Opóźnienie, statystyki, beacon tracking
   - Meta tagi OG dla social preview

**Przykład:**
- Wpis: `https://twoja-domena.pl/blog/tutorial-php` - pełna strona wpisu
- Bezpośredni: `https://twoja-domena.pl/tutorial-php` - przekierowanie

## Tryby strony głównej

System oferuje trzy tryby wyświetlania strony głównej:

### 1. Landing Page (domyślny)
- Statyczna strona informacyjna
- Pokazuje instrukcje użycia systemu
- Link do panelu administracyjnego

### 2. Redirect
- Automatyczne przekierowanie na wybrany URL
- Przydatne gdy chcesz przekierować wszystkich odwiedzających na główną stronę projektu

### 3. Blog (NOWE)
- Wyświetla wszystkie linki jako wpisy bloga
- Każdy wpis na liście zawiera:
  - Miniaturkę obrazka (og_image) - opcjonalnie
  - Tytuł (page_title) lub slug
  - Skrócony opis (page_description)
  - Datę utworzenia
  - Przycisk "Czytaj dalej" prowadzący do `/blog/slug`
- Pojedynczy wpis (`/blog/slug`) wyświetla:
  - Pełny obraz wyróżniający
  - Pełny tytuł i opis (bez skrócenia)
  - Link bezpośredni do kopiowania
  - Przycisk "Przejdź do docelowej strony"
  - Breadcrumbs i nawigację
- Klasyczny link (`/slug`) działa normalnie (przekierowanie)

## Konfiguracja

### Aktywacja trybu blog

1. Przejdź do panelu administracyjnego: `/admin/index.php`
2. Wybierz **Ustawienia** z menu
3. W sekcji **Konfiguracja strony głównej** wybierz **"Blog (lista linków jako wpisy)"**
4. Skonfiguruj dodatkowe opcje trybu blog (poniżej)
5. Zapisz zmiany

### Ustawienia trybu blog

Dostępne opcje konfiguracji:

- **Maksymalna długość opisu** (50-1000 znaków, domyślnie 200)
  - Określa ile znaków z `page_description` będzie wyświetlane na liście
  - Dłuższe opisy zostaną automatycznie skrócone z dodaniem "..."

- **Liczba wpisów na stronę** (1-50, domyślnie 10)
  - Ile linków wyświetlać na jednej stronie
  - Wpisy są sortowane od najnowszych (DESC)

- **Wyświetlaj miniaturki obrazków** (checkbox, domyślnie włączone)
  - Czy pokazywać obrazki `og_image` jako miniaturki wpisów
  - Jeśli link nie ma obrazka, wyświetlana jest placeholder z pierwszą literą tytułu

### Wspólne ustawienia

Niezależnie od trybu, możesz skonfigurować:
- **Tytuł strony głównej**
- **Podtytuł**
- **Meta Description** (SEO)
- **Stopkę** (HTML)
- **Dodatkowy kod w <head>** (np. Google Analytics)
- **Dodatkowy kod przed </body>** (np. skrypty)

## Przygotowanie linków do trybu blog

Aby linki wyglądały dobrze w trybie blog, uzupełnij dla każdego:

1. **page_title** (tytuł) - wyświetlany jako nagłówek wpisu
   - Jeśli puste, użyty zostanie slug
   
2. **page_description** (opis) - wyświetlany jako excerpt wpisu
   - Automatycznie skracany do ustawionej liczby znaków
   
3. **og_image** (obrazek) - wyświetlany jako miniatura
   - Opcjonalnie - jeśli brak, pokazywany jest placeholder
   - Rekomendowany rozmiar: 1200x630px lub większy
   - Formaty: JPG, PNG

## Wygląd trybu blog

Interfejs blog został zaprojektowany z myślą o:
- **Responsywności** - działa na urządzeniach mobilnych i desktopowych
- **Estetyce** - gradient header, karty z cieniami, animacje hover
- **UX** - przejrzysta struktura, łatwa nawigacja

## RSS i mapa strony

- Dostępne tylko, gdy w ustawieniach wybrany jest tryb **Blog**.
- RSS: `/rss.xml` (alias `/feed.xml`) — generowane dynamicznie z opublikowanych wpisów.
- Sitemap: `/sitemap.xml` — zawiera stronę główną bloga i wpisy `/blog/slug`.
- Uwzględnia tylko wpisy `published` w oknie publikacji (`publish_at <= now`, brak `expires_at` w przeszłości).
- URL-e respektują `basePath`, więc działają także, gdy aplikacja jest w podkatalogu.

### Layout

- **Header** - gradient z tytułem i podtytułem
- **Grid wpisów** - 3 kolumny na dużych ekranach, 2 na średnich, 1 na małych
- **Karty wpisów** - miniatura/placeholder, tytuł, opis, meta (data + przycisk)
- **Footer** - opcjonalna stopka HTML

## Migracja

Aby zastosować nowe ustawienia w bazie danych:

```bash
mysql -u [user] -p [database] < migrations/006_add_blog_mode.sql
```

Lub ręcznie wykonaj SQL z pliku `migrations/006_add_blog_mode.sql`.

## Przykład użycia

### Scenariusz: Blog techniczny z tutorialami

1. Ustaw tryb strony głównej na **Blog**
2. Dla każdego tutorialu utwórz link:
   - **slug**: `jak-zainstalowac-php`
   - **target_url**: `https://example.com/tutorial/php-installation`
   - **page_title**: `Jak zainstalować PHP 8.1 na Ubuntu`
   - **page_description**: `Kompletny przewodnik instalacji PHP 8.1 wraz z Composer i najpopularniejszymi rozszerzeniami na systemie Ubuntu 22.04 LTS`
   - **og_image**: Upload obrazka z logo PHP
3. Zapisz - wpis pojawi się na stronie głównej

### Scenariusz: Portfolio projektów

1. Ustaw tryb na **Blog** i tytuł "Moje Projekty"
2. Dla każdego projektu:
   - **slug**: nazwa projektu (np. `cms-system`)
   - **target_url**: link do live demo lub repo
   - **page_title**: Pełna nazwa projektu
   - **page_description**: Krótki opis funkcji i użytych technologii
   - **og_image**: Screenshot projektu
3. Strona główna stanie się portfolio

## Najlepsze praktyki

✅ **Zalecane:**
- Używaj spójnych długości opisów (około 150-200 znaków)
- Dodawaj obrazki w jednolitym stylu/rozmiarze
- Stosuj jasne, opisowe tytuły
- Uzupełniaj page_description nawet jeśli masz krótkie opisy

❌ **Unikaj:**
- Pozostawiania pustych page_title (będzie widoczny surowy slug)
- Mieszania wpisów z obrazkami i bez nich (wygląda niespójnie)
- Zbyt długich tytułów (powyżej 60 znaków)
- Duplikowania treści w title i description

## FAQ

**Q: Jaka jest różnica między `/blog/slug` a `/slug`?**  
A: `/blog/slug` wyświetla pełną stronę wpisu z opisem i przyciskiem do docelowej strony. `/slug` natychmiast przekierowuje na target_url (klasyczne działanie systemu).

**Q: Czy mogę udostępnić link do wpisu na social media?**  
A: Tak! Link `/blog/slug` ma pełne meta tagi Open Graph i będzie wyglądał dobrze na Facebooku, Twitterze, LinkedIn etc.

**Q: Czy link `/slug` nadal działa w trybie blog?**  
A: Tak! Oba linki działają równolegle. `/slug` przekierowuje jak zawsze, `/blog/slug` pokazuje stronę wpisu.

**Q: Czy mogę mieć paginację dla wpisów?**  
A: Obecnie system wyświetla N najnowszych wpisów (domyślnie 10). Paginacja nie jest jeszcze zaimplementowana, ale możesz zwiększyć liczbę wpisów na stronę w ustawieniach.

**Q: Czy wpisy są sortowane?**  
A: Tak, zawsze od najnowszych (według pola `created_at DESC`).

**Q: Co jeśli link nie ma page_title?**  
A: Zostanie użyty slug jako tytuł wpisu.

**Q: Co jeśli brak page_description?**  
A: Opis nie będzie wyświetlany, tylko tytuł i meta (data + przycisk).

**Q: Czy mogę ukryć niektóre linki na blogu?**  
A: Obecnie nie - wszystkie linki są wyświetlane. Funkcja filtrowania może zostać dodana w przyszłości.

**Q: Czy mogę zmienić wygląd bloga?**  
A: Tak, edytuj plik `views/home_blog.php` - zawiera inline style CSS i HTML template.

## Pliki związane z funkcją

- `migrations/006_add_blog_mode.sql` - migracja SQL
- `views/home_blog.php` - template listy wpisów bloga
- `views/blog_post.php` - template pojedynczego wpisu bloga
- `index.php` - routing i logika wyboru trybu (`/blog/slug` + `/slug`)
- `admin/settings.php` - formularz ustawień
- `src/AdminController.php` - obsługa zapisu ustawień
- `src/SettingsRepository.php` - dostęp do ustawień z bazy

## Changelog

### v1.1.0 (2026-01-09)
- Dodano tryb blog dla strony głównej
- Nowe ustawienia: home_mode, blog_description_length, blog_posts_per_page, blog_show_images
- Nowy widok: home_blog.php z responsywnym layoutem listy
- Nowy widok: blog_post.php dla pojedynczego wpisu
- Routing: `/blog/slug` wyświetla wpis, `/slug` przekierowuje (oba działają równolegle)
- Aktualizacja panelu administracyjnego: dynamiczne przełączanie opcji trybu
- Meta tagi Open Graph dla wpisów bloga
- Breadcrumbs i nawigacja między listą a wpisem
