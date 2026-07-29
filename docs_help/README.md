# 📚 Dokumentacja RedirectCMS

Witaj w dokumentacji technicznej RedirectCMS! Poniżej znajdziesz spis wszystkich dostępnych dokumentów wraz z krótkim opisem.

## 📖 Spis dokumentacji

### 🔧 Migracje i konfiguracja

- **Pełny schemat**: `../db/000_schema.sql` — zawiera wszystkie migracje (łącznie z trybem blog i motywami)
- **Przykładowa konfiguracja DB**: `../config/example_config.php`

Nie są potrzebne osobne pliki migracji krok-po-kroku; wystarczy załadować pełny schemat.

---

### 🎨 Szablony i motywy

#### [TEMPLATES.md](TEMPLATES.md)
**System szablonów bloga**

Opisuje, jak tworzyć własne motywy w katalogu `/templates/`, strukturę `theme.json`, wymagane pliki `home.php` i `post.php`, oraz mechanizm fallbacku.

---

### 🏷️ Organizacja treści

#### [CATEGORIES_AND_TAGS.md](CATEGORIES_AND_TAGS.md)
**Kategorie i tagi linków**

Kompleksowy przewodnik po systemie kategorii i tagów. Dowiesz się:
- Różnice między kategoriami a tagami
- Jak zarządzać kategoriami (z kolorami i opisami)
- Jak zarządzać tagami (z auto-tworzeniem)
- Przypisywanie kategorii i tagów do linków
- Filtrowanie po kategoriach/tagach w trybie blog
- API i przykłady kodu
- Najlepsze praktyki organizacji treści

---

### ⚡ Cache i wydajność

#### [README_CACHE.md](README_CACHE.md)
**Cache statystyk globalnych - Przewodnik**

Kompleksowy opis systemu cache dla statystyk globalnych. Dowiesz się:
- Jak działa automatyczny system cache (bez potrzeby konfiguracji crona)
- Domyślne zachowanie i TTL (30 minut)
- Jak ręcznie odświeżyć cache przez panel administracyjny
- Zaawansowane opcje: skrypt CLI do ręcznego odświeżania
- Informacje o wydajności i optymalizacji

---

### 🧪 Testowanie

#### [TESTING_CACHE.md](TESTING_CACHE.md)
**Testy działania systemu cache statystyk globalnych**

Scenariusze testowe dla systemu cache statystyk. Zawiera:
- Test pierwszego wejścia (brak cache) - weryfikacja loadera i generowania
- Test drugiego wejścia (cache aktualny) - sprawdzenie szybkości ładowania
- Test zmiany parametrów (dni, wykluczenie botów)
- Test wygaśnięcia cache (po przekroczeniu TTL 30 min)
- Test ręcznego odświeżania cache
- Weryfikacja wydajności dla różnych zakresów danych

---

## 🚀 Szybki start

Jeśli dopiero zaczynasz pracę z RedirectCMS, polecamy:

1. **Najpierw**: Przeczytaj główny [README.md](../README.md) w katalogu głównym projektu
2. **Jeśli aktualizujesz**: Zobacz [MIGRATION_006.md](MIGRATION_006.md) dla wdrożenia najnowszych funkcji
3. **Chcesz zrozumieć cache**: Sprawdź [README_CACHE.md](README_CACHE.md)
4. **Testujesz system**: Użyj scenariuszy z [TESTING_CACHE.md](TESTING_CACHE.md)

---

## 📝 Struktura projektu

```
RedirectCMS/
├── README.md              # Główna dokumentacja projektu
├── LICENSE                # Licencja non-commercial (CC BY-NC 4.0)
├── docs/                  # 📚 Katalog dokumentacji (tu jesteś!)
│   ├── README.md          # Ten plik - indeks dokumentacji
│   ├── README_CACHE.md    # System cache - przewodnik
│   ├── TESTING_CACHE.md   # Scenariusze testowe cache
│   └── TEMPLATES.md       # Tworzenie motywów bloga
├── admin/                 # Panel administracyjny
├── src/                   # Kod źródłowy PHP
├── views/                 # Widoki publiczne (home, redirect)
├── templates/             # Motywy bloga (home.php, post.php, theme.json)
├── assets/                # Pliki CSS
├── scripts/               # Skrypty CLI
├── db/                    # Pełny schemat SQL (000_schema.sql)
└── storage/               # Logi i cache
```

---

## 🔗 Przydatne linki

- **Główna dokumentacja**: [../README.md](../README.md)
- **Licencja projektu**: [../LICENSE](../LICENSE)
- **Schemat SQL**: `../db/000_schema.sql`
- **Skrypty maintenance**: `../scripts/`

---

## 💡 Wskazówki

- Wszystkie pliki markdown używają składni GitHub Flavored Markdown
- Polecenia SQL i bash są zawsze w blokach kodu z odpowiednim highlight
- Ikony emoji pomagają w szybkiej nawigacji
- Każdy dokument jest samodzielny - możesz czytać w dowolnej kolejności

---

## 📞 Wsparcie

Masz pytania? Sprawdź:
1. Dokumentację w tym katalogu
2. Komentarze w kodzie źródłowym (`src/`)
3. Instrukcje w pliku [LICENSE](../LICENSE) dotyczące licencji

---

**Ostatnia aktualizacja**: 9 stycznia 2026
