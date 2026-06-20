# 🚀 Blog Szablony - Quick Start Guide

## W 5 minut do uruchomionego bloga!

### Krok 1: Włącz tryb blog
1. Wejdź na `/admin/index.php?action=settings`
2. W sekcji "Ustawienia strony głównej" -> "Tryb strony głównej"
3. Zmień na: **Blog**
4. Zapisz ustawienia

### Krok 2: Wybierz szablon
1. W tej samej sekcji -> "Szablon blogu"
2. Wybierz jeden z:
   - **Minimalny** - Czysty, elegancki design
   - **Elegancki** - Nowoczesny z gradientami
   - **Nowoczesny** - Dark mode z akcentami
3. Zapisz ustawienia

### Krok 3: Dodaj kategorie
1. Wejdź na `/admin/index.php?action=categories`
2. Kliknij "+ Nowa kategoria"
3. Dodaj:
   - **Nazwa**: Tech
   - **Slug**: tech
   - **Kolor**: Wybierz kolor (np. niebieski)

Powiedź się przy ~5 kategoriach.

### Krok 4: Dodaj tagi
1. Wejdź na `/admin/index.php?action=tags`
2. Kliknij "+ Nowy tag"
3. Dodaj kilka tagów:
   - JavaScript
   - CSS
   - Design
   - itp.

### Krok 5: Dodaj wpisy
1. Wejdź na `/admin/index.php?action=new`
2. Wypełnij:
   - **Slug**: my-first-post
   - **URL docelowy**: https://example.com (lub wpisz adres bloga)
   - **Tytuł strony**: "Mój Pierwszy Wpis"
   - **Opis strony**: "To jest mój pierwszy wpis na blogu..."
   - **Kategoria**: Wybierz kategorię (np. Tech)
   - **Tagi**: Dodaj tagi (np. #javascript, #tutorial)
   - **Obrazek**: Dodaj OG image (1200x630px)
3. Kliknij "Utwórz link"

Dodaj 5-10 wpisów dla pełnego doświadczenia.

### Krok 6: Odswiież i ciesz się blogiem!
1. Wejdź na `/`
2. Powinieneś zobaczyć:
   - **Blog home page** - Lista wpisów
   - **Sidebar** - Kategorie i tagi
   - **Wpisy** - Wyświetlone w wybranym szablonie

### Krok 7: Kliknij na wpis
1. Kliknij na dowolny wpis
2. Powinieneś zobaczyć:
   - **Tytuł wpisu** - Ze stylizacją z szablonu
   - **Opis** - Pełna treść
   - **Powiązane wpisy** - Automatycznie pobrane
   - **Sidebar** - Kategorie, tagi tego wpisu
   - **Metadata** - Data, kategoria

## 🎨 Przełączanie między szablonami

1. Wejdź na Settings (`/admin/index.php?action=settings`)
2. Zmień `blog_theme` na:
   - `minimal` → Minimalny
   - `elegant` → Elegancki
   - `modern` → Nowoczesny
3. Odśwież `/`

## 🔗 Testowanie filtrów

- **Filtr po kategorii**: Kliknij kategorię w sidebar → `/blog/?category=tech`
- **Filtr po tagu**: Kliknij tag w sidebar → `/blog/?tag=javascript`
- Powinieneś zobaczyć tylko wpisy z danej kategorii/tagu

## 📱 Testuj responsywność

- **Desktop** - Otwórz na komputerze (1920px)
- **Tablet** - Zmniejsz okno przeglądarki do 768px
- **Mobile** - Zmniejsz do 375px lub otwórz na telefonie

Każdy szablon powinien się prawidłowo dopasować.

## ⚠️ Troubleshooting

### Blog nie wyświetla się
- [ ] Sprawdź czy `home_mode = blog` (Settings)
- [ ] Sprawdź czy dodałeś co najmniej 1 wpis
- [ ] Odśwież cache przeglądarki (Ctrl+F5)

### Sidebar nie wyświetla się
- [ ] Dodaj kategorie do postów
- [ ] Dodaj tagi do postów
- [ ] Sprawdź czy template istnieje w `/templates/{theme}/`

### Powiązane wpisy się nie wyświetlają
- [ ] Dodaj wiele wpisów do tej samej kategorii
- [ ] LUB dodaj wiele tagów do wpisów
- [ ] Wpisy muszą respektować daty publikacji

### Obrazki się nie wyświetlają
- [ ] Sprawdź czy `blog_show_images = 1` (Settings)
- [ ] Dodaj OG image do wpisów (`og_image`)
- [ ] Sprawdź czy rozmiar pliku jest OK (<5MB)

## 🎯 Wskazówki

1. **OG Image** - Używaj 1200x630px dla najlepszych wyników
2. **Opis wpisu** - 150-160 znaków (Google meta opis)
3. **Slug** - Krótki, czytelny (np. `jak-zaczac-javascript`)
4. **Kategorie** - Ogranicz do 5-10 głównych
5. **Tagi** - Mogą być dowolne, ale spróbuj być spójny

## 📚 Pełna dokumentacja

- `templates/THEME_DEVELOPMENT.md` - Dokumentacja tworzenia szablonów
- `docs/BLOG_TEMPLATES_TEST.md` - Checklist testów
- `docs/BLOG_TEMPLATES_SUMMARY.md` - Podsumowanie
- `BLOG_TEMPLATES_CHANGELOG.md` - Co się zmieniło

## 🚀 Następnie

Możesz:
- [ ] Dodać custom HTML w nagłówku/stopce (Settings -> home_header_code)
- [ ] Zmienić tytuł bloga (home_title)
- [ ] Dostosować dłu opis skrótu (blog_description_length)
- [ ] Ukryć/pokazać obrazy (blog_show_images)

---

**Gotowe!** Twój blog powinien być już działający. Niech się Ci powiedzie! 🎉
