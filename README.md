# RedirectCMS

**RedirectCMS** to system zarządzania skróconymi linkami z panelem administracyjnym, zaawansowaną analityką, obsługą kampanii afiliacyjnych i modułem CMS. Napisany w czystym PHP — bez zbędnych frameworków, łatwy w instalacji na dowolnym hostingu z PHP i MySQL.

---

## Funkcje

### Zarządzanie linkami
- Tworzenie skróconych linków z własnym slugiem lub automatycznie generowanym
- Opóźnienie przekierowania (konfigurowalne globalnie i per-link)
- Planowanie publikacji i automatyczne wygasanie linków
- Kosz z możliwością przywrócenia usuniętych linków
- Generowanie kodów QR dla każdego linku
- UTM Builder — kreator parametrów kampanii UTM
- Niestandardowe pola linku (cena, ocena, kod rabatowy i inne)
- Obrazek OG i meta description dla SEO

### Organizacja treści
- **Kategorie** z kolorami i ikonami
- **Tagi** — wielokrotne tagowanie linków
- **Kampanie** — grupowanie linków z łącznymi statystykami
- **Programy afiliacyjne** — etykiety z kolorami dla sieci partnerskich
- **Strony CMS** — statyczne strony HTML z edytorem WYSIWYG (Trix)

### Analityka
- Kliknięcia dzisiaj / 7 / 30 / 90 dni z trendem
- Unikalni użytkownicy (po IP)
- Rozkład według godzin — wykres szczytowych pór ruchu
- Źródła ruchu (referers) z procentami
- Przeglądarki i typy urządzeń
- Geolokalizacja kliknięć (kraj, region, miasto) via ip-api.com
- Filtrowanie botów i crawlerów
- Cache globalnych statystyk dla wydajności
- Eksport danych statystycznych

### Import / Eksport
- Import linków z pliku CSV (separator `;`) z podglądem przed importem
- Aktualizacja istniejących linków przez import
- Eksport linków wraz z tagami i kategoriami

### Bezpieczeństwo
- Uwierzytelnianie dwuskładnikowe **TOTP (2FA)** — zgodne z Google Authenticator, Authy, 1Password
- 10 jednorazowych kodów zapasowych
- Rate limiting logowania
- Audit Log — rejestruje wszystkie akcje (CREATE / UPDATE / DELETE / LOGIN / SETTINGS)
- Logi systemowe i logi błędów SMTP
- Anonimizacja adresów IP w bazie danych

### Wygląd i motywy
- System szablonów oparty o `theme.json` (paleta kolorów, rozmiary miniatur, obsługiwane widgety)
- CSS custom properties (`--theme-*`) dla motywów
- Logo i favicon
- Konfigurowalny sidebar z widgetami (popularne linki, losowe linki, chmura tagów)
- Strona kontaktowa z formularzem i rate limitingiem

### Administracja
- **Backup** — tworzenie i przywracanie kopii zapasowych bazy danych (ZIP z SQL), automatyczny backup przez cron
- **Sprawdzanie linków** — automatyczna weryfikacja dostępności adresów URL (kody HTTP 404, 500 itp.)
- **Menedżer plików** — przeglądanie i zarządzanie plikami w katalogu `uploads/`
- **Optymalizacja obrazów** — obsługa GD, ImageMagick (WebP, progresywne JPEG) i narzędzi CLI (jpegoptim, pngquant, optipng)
- **Cache Geo** — zarządzanie cache geolokalizacji, automatyczne czyszczenie wpisów starszych niż 30 dni
- **System powiadomień** — centrum alertów o błędach cron, martwych linkach, problemach SMTP (poziomy: Info / Ostrzeżenie / Błąd / Krytyczny)
- **Zadania Cron** — zarządzanie harmonogramem przez panel (tryb HTTP i CLI), logi wykonań
- **Zasoby** — monitoring użycia dysku i bazy danych z limitami

---

## Wymagania systemowe

| Komponent | Wersja minimalna |
|-----------|-----------------|
| PHP | 8.1+ |
| MySQL / MariaDB | 5.7+ / 10.2+ |
| Serwer WWW | Apache (mod_rewrite) lub Nginx |
| Rozszerzenia PHP | PDO, pdo_mysql, json, mbstring, curl |
| Uprawnienia zapisu | `config/`, `logs/`, `uploads/`, `storage/` |

---

## Instalacja

### Automatyczna (zalecana)

1. Wgraj pliki aplikacji na serwer
2. Utwórz pustą bazę danych MySQL/MariaDB
3. Otwórz kreator instalacji w przeglądarce:
   ```
   https://twoja-domena.pl/install/install.php
   ```
4. Postępuj zgodnie z instrukcjami — instalator skonfiguruje bazę danych i wygeneruje plik `config/config.php`

### Ręczna

```bash
# 1. Skopiuj przykładową konfigurację
cp config/example_config.php config/config.php

# 2. Uzupełnij dane dostępowe w config/config.php (host, dbname, user, pass, encryption_key)

# 3. Zaimportuj schemat bazy danych
mysql -u użytkownik -p nazwa_bazy < install/db_schema.sql

# 4. Nadaj uprawnienia do zapisu
chmod 755 uploads/ storage/ logs/ config/
```

### Po instalacji

```bash
# WAŻNE: usuń katalog instalatora po zakończeniu
rm -rf install/
```

Zaloguj się do panelu administracyjnego:
```
https://twoja-domena.pl/admin/
```

Domyślny login: `admin` — hasło wyświetlane na końcu procesu instalacji.

---

## Konfiguracja Cron

Aby zadania automatyczne działały poprawnie, dodaj wpis do crontab serwera.

**Tryb HTTP (curl):**
```bash
*/5 * * * * curl -s "https://twoja-domena.pl/cron_runner.php?token=TWOJ_TOKEN" > /dev/null 2>&1
```

**Tryb CLI (PHP):**
```bash
*/5 * * * * php /ścieżka/do/aplikacji/cron_runner.php > /dev/null 2>&1
```

Token generowany jest automatycznie w panelu: `Konfiguracja → Cron`.

### Domyślne zadania cron

| Zadanie | Interwał | Opis |
|---------|----------|------|
| `clean_geo_cache` | 24h | Usuwa wpisy geolokalizacji starsze niż 30 dni |
| `refresh_global_stats` | 30 min | Odświeża cache globalnych statystyk |
| `auto_backup` | 24h | Automatyczna kopia zapasowa bazy danych |
| `check_broken_links` | Konfigurowalny | Weryfikacja dostępności adresów URL |

---

## Konfiguracja

Plik `config/config.php` (nie jest wersjonowany w git):

```php
return [
    'db' => [
        'host'   => 'localhost',
        'dbname' => 'redirect_cms',
        'user'   => 'root',
        'pass'   => 'silne_haslo',
    ],
    'app' => [
        'env' => 'production',   // 'development' wyświetla błędy; 'production' loguje je
    ],
    'session' => [
        'secure' => true,        // true gdy używasz HTTPS
    ],
    // Klucz szyfrowania dla haseł SMTP (64-znakowy hex)
    // Generowanie: php -r "echo bin2hex(openssl_random_pseudo_bytes(32));"
    'encryption_key' => 'WYGENERUJ_WLASNY_KLUCZ',
];
```

---

## Import CSV

Plik CSV musi używać separatora `;`. Wymagane kolumny: `slug`, `target_url`.

```csv
slug;target_url;title;description;category;affiliate_program;tags
promo2024;https://example.com/promo;Promocja 2024;Opis promocji;Oferty;Amazon;rabat,lato
```

---

## Struktura projektu

```
RedirectCMS/
├── admin/          # Panel administracyjny (widoki)
├── assets/         # CSS i JavaScript
├── config/         # Konfiguracja (config.php nie jest w git)
├── docs/           # Dokumentacja HTML
├── install/        # Instalator i schemat SQL
├── logs/           # Logi aplikacji
├── scripts/        # Skrypty CLI (clean_geo_cache, refresh_stats)
├── src/            # Klasy PHP (logika biznesowa)
├── storage/        # Pliki tymczasowe
├── templates/      # Szablony motywów
├── uploads/        # Przesłane pliki (obrazy, QR, miniatury)
├── views/          # Widoki publiczne (home, redirect, 404)
├── cron_runner.php # Punkt wejścia dla zadań cron
└── index.php       # Router główny
```

---

## Licencja

RedirectCMS jest udostępniany na licencji **Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)**.

**Dozwolone:** użytek prywatny, edukacja, organizacje non-profit, modyfikacje, udostępnianie z zachowaniem licencji.

**Zabronione:** użytek komercyjny, generowanie przychodów, świadczenie płatnych usług opartych na RedirectCMS.

Pełna treść licencji: [LICENSE](LICENSE) · [CC BY-NC 4.0](https://creativecommons.org/licenses/by-nc/4.0/legalcode.pl)

---

© 2025–2026 RedirectCMS · Krzysztof Janiczek
