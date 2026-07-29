# Test działania systemu cache statystyk globalnych

## Scenariusze testowe

### Test 1: Pierwsze wejście (brak cache)
1. Uruchom migrację: `mysql -u root -p redirect_cms < migrations/006_add_global_stats_cache.sql`
2. Otwórz: `/admin/index.php?action=global_stats`
3. **Oczekiwany rezultat**: 
   - Wyświetli się loader "Generowanie statystyk globalnych..."
   - Po kilku sekundach nastąpi automatyczne przekierowanie
   - Zobaczysz pełne statystyki z heatmapą

### Test 2: Drugie wejście (cache aktualny)
1. Odśwież stronę statystyk globalnych
2. **Oczekiwany rezultat**: 
   - Strona ładuje się natychmiastowo (bez loadera)
   - Statystyki są wyświetlane z cache
   - W prawym górnym rogu widoczna info o cache (np. "5 min temu")

### Test 3: Zmiana parametrów
1. Zmień zakres z "30 dni" na "7 dni"
2. **Oczekiwany rezultat**: 
   - Ponieważ to nowa kombinacja parametrów - pokaże się loader
   - Wygeneruje nowy cache dla 7 dni
   - Statystyki będą dla wybranego zakresu

### Test 4: Ręczne odświeżanie
1. Na stronie statystyk kliknij przycisk "Odśwież cache"
2. **Oczekiwany rezultat**: 
   - Pokaże się loader
   - Cache zostanie przeliczony
   - Pojawi się komunikat sukcesu z czasem generowania

### Test 5: Wygaśnięcie cache (TTL)
1. Otwórz statystyki globalne
2. Poczekaj ponad 30 minut (lub zmień TTL w kodzie dla testu)
3. Odśwież stronę
4. **Oczekiwany rezultat**: 
   - Wykryje przestarzały cache
   - Pokaże loader i wygeneruje nowe statystyki
   - Przekieruje na świeże dane

### Test 6: Wykluczanie botów
1. Zaznacz checkbox "Wyklucz boty"
2. **Oczekiwany rezultat**: 
   - Jeśli to nowa kombinacja - loader i generowanie
   - Statystyki bez ruchu botów
   - Osobny wpis w cache (global_stats_30d_nobots)

### Test 7: Heatmapa
1. Otwórz statystyki globalne
2. Przewiń do sekcji "Heatmapa aktywności"
3. **Oczekiwany rezultat**: 
   - Tabela 7 dni × 24 godziny
   - Komórki z liczbami i kolorowym tłem (im więcej kliknięć, tym ciemniejszy kolor)
   - Tooltip przy najechaniu pokazuje szczegóły

## Weryfikacja w bazie danych

```sql
-- Sprawdź zawartość cache
SELECT 
    cache_key, 
    updated_at,
    TIMESTAMPDIFF(MINUTE, updated_at, NOW()) as minutes_old
FROM global_stats_cache
ORDER BY updated_at DESC;
```

## Logi

Jeśli skonfigurowałeś cron, sprawdź logi:

```bash
tail -f storage/logs/stats_cache.log
```

## Rozwiązywanie problemów

### Problem: Loader pokazuje się w kółko
- Sprawdź logi błędów PHP
- Upewnij się że tabela `global_stats_cache` istnieje
- Sprawdź uprawnienia do zapisu dla użytkownika bazy danych

### Problem: Błąd "Błędny CSRF token"
- Wyczyść cache przeglądarki i cookies
- Sprawdź czy sesje PHP działają poprawnie

### Problem: Bardzo długie generowanie (>10 sekund)
- Sprawdź czy masz dużo danych w tabeli `link_stats`
- Rozważ dodanie indeksów na kolumny używane w zapytaniach
- Możesz zmniejszyć zakres dni (90→30→7)

## Sukces!

Jeśli wszystkie testy przeszły pomyślnie, system działa poprawnie! 🎉

Cache znacznie przyspieszy ładowanie statystyk globalnych i zmniejszy obciążenie serwera.
