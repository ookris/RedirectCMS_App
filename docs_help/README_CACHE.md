# Cache statystyk globalnych

## 🎯 Jak to działa?

System cache działa **automatycznie bez konfiguracji crona**! 

Przy wejściu na stronę statystyk globalnych:
1. ✅ Sprawdza czy cache istnieje i jest aktualny (TTL 30 min)
2. 🔄 Jeśli cache wygasł — wyświetla loader i generuje statystyki w tle
3. ⚡ Automatycznie przekierowuje na stronę z świeżymi danymi
4. 🚀 Kolejne wejścia (w ciągu 30 min) są błyskawiczne dzięki cache

## 📋 Domyślne zachowanie

**Nie wymaga żadnej konfiguracji crona!**

Statystyki są automatycznie generowane:
- Przy pierwszym wejściu na stronę statystyk globalnych
- Gdy poprzednia cache wygaśnie (TTL 30 minut)

Użytkownik widzi przyjazny loader podczas generowania danych.

## 🔧 Informacje techniczne

### Gdzie są dane?

- **Tabela**: `global_stats_cache` — przechowuje przeliczone statystyki
- **TTL**: 30 minut — po tym czasie cache jest uznawany za przestarzały
- **Kombinacje**: System cache obsługuje 6 kombinacji (7/30/90 dni, z/bez botów)

### Jak pracuje?

1. Użytkownik wchodzi na `/admin/index.php?action=global_stats`
2. System sprawdza czy cache dla danego wariantu istnieje i czy jest aktualny
3. Jeśli cache brakuje/wygasł:
   - Wyświetla loader AJAX
   - W tle regeneruje statystyki (może trwać kilka sekund)
   - Strona automatycznie odświeża się z nowymi danymi
4. Jeśli cache aktualny — dane wyświetlane natychmiast

### Ręczne odświeżenie

W panelu admin dostępny jest przycisk "Odśwież cache statystyk" który pozwala natychmiast wymusić regenerację.

## 📊 Weryfikacja działania

1. Otwórz panel administracyjny: `/admin/index.php?action=global_stats`
2. Przy pierwszym wejściu zobaczysz loader "Generowanie statystyk..."
3. Po wygenerowaniu strona się odświeży automatycznie
4. Kolejne wejścia (w ciągu 30 min) będą natychmiastowe
5. Przycisk "Odśwież cache" pozwala ręcznie wymusić regenerację

## 🚀 Czyszczenie cache geolokalizacji

Oddzielnie (!) od cache statystyk globalnych, aplikacja zbiera cache geolokalizacji w tabeli `geo_cache`.
Ten cache **wymaga automatycznego czyszczenia** poprzez cron:

```bash
0 2 * * * cd /var/www/html/RedirectCMS && php scripts/clean_geo_cache.php 30 >> storage/logs/geo_cache.log 2>&1
```

Szczegóły: [clean_geo_cache.php](clean_geo_cache.php)
