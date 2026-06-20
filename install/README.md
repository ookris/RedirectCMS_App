# Instalator RedirectCMS

Instalator RedirectCMS pozwala na łatwą i szybką instalację aplikacji na serwerze.

## Automatyczna instalacja (ZALECANA)

1. Wgraj wszystkie pliki aplikacji na serwer
2. Utwórz pustą bazę danych MySQL/MariaDB
3. Otwórz w przeglądarce: `http://twoja-domena.pl/install/install.php`
4. Postępuj zgodnie z instrukcjami kreatora instalacji

Instalator automatycznie:
- Przetestuje połączenie z bazą danych
- Utworzy wszystkie wymagane tabele
- Wygeneruje plik `config/config.php` z Twoimi danymi
- Wygeneruje bezpieczne hasło administratora

Po instalacji skonfigurujesz zadania cron i ustawienia aplikacji samodzielnie (np. w panelu administratora i konfiguracji serwera).

## Ręczna instalacja

Jeśli wolisz instalację ręczną:

1. Skopiuj `config/example_config.php` do `config/config.php`
2. Edytuj `config/config.php` i ustaw dane dostępowe do bazy danych
3. Zaimportuj plik `install/db_schema.sql` do swojej bazy danych:
   ```bash
   mysql -u użytkownik -p nazwa_bazy < install/db_schema.sql
   ```
4. Ustaw hasło administratora w tabeli settings (lub użyj automatycznego instalatora)
5. Ustaw uprawnienia do zapisu dla katalogów `storage/` i `uploads/`

## Tworzenie struktury katalogów (WAŻNE!)

Jeśli katalogi dla uploadów nie zostały utworzone automatycznie lub masz problemy z przesyłaniem plików:

**Opcja 1: Automatyczne tworzenie (zalecane)**
```
http://twoja-domena.pl/App/install/create_upload_dirs.php
```
Lub przez CLI:
```bash
php App/install/create_upload_dirs.php
```

**Opcja 2: Ręczne tworzenie**
```bash
mkdir -p uploads/category-icon
chmod 755 uploads/
chmod 755 uploads/category-icon/
```

Sprawdź czy katalogi mają odpowiednie uprawnienia (755 lub 775) i czy użytkownik PHP (www-data/apache) ma prawo do zapisu.

## Naprawa bezwzględnych ścieżek (dla istniejących instalacji)

Jeśli aktualizujesz istniejącą instalację i obrazki przestały się wyświetlać (ścieżki w bazie danych wyglądają jak `/home/user/www/uploads/...` zamiast `uploads/...`), uruchom skrypt naprawczy:

**Przez przeglądarkę:**
```
http://twoja-domena.pl/App/install/fix_absolute_paths.php
```

**Przez CLI:**
```bash
php App/install/fix_absolute_paths.php
```

Skrypt automatycznie przekonwertuje wszystkie bezwzględne ścieżki na relatywne w tabelach:
- `links` (image, thumbnail, qr_code)
- `categories` (icon_image, icon_image_thumb)
- `settings` (logo, favicon)

## Po instalacji

1. **Zaloguj się do panelu admin** używając danych wygenerowanych podczas instalacji:
   - Login: `admin`
   - Hasło: (hasło wyświetlone na końcu instalacji - zapisz je w bezpiecznym miejscu!)

2. **USUŃ katalog `install/`** aby zapobiec ponownej instalacji:
   ```bash
   rm -rf install/
   ```

3. Skonfiguruj ustawienia w panelu administratora

4. W środowisku produkcyjnym ustaw w `config/config.php`:
   - `'env' => 'production'`
   - `'secure' => true` (jeśli używasz HTTPS)

## Wymagania systemowe

- PHP 8.1 lub nowszy
- MySQL 5.7+ / MariaDB 10.2+
- PDO PHP Extension
- Apache/Nginx z mod_rewrite
- Uprawnienia do zapisu: `config/`, `logs/`, `uploads/`

## Bezpieczeństwo

Po instalacji plik `config/config.php` NIE JEST dodawany do repozytorium git (chroni Twoje dane dostępowe).

Jeśli masz problemy z instalacją, sprawdź:
- Logi błędów PHP
- Uprawnienia do katalogów
- Połączenie z bazą danych
- Wersję PHP i MySQL

## Pomoc

W razie problemów z instalacją, sprawdź dokumentację projektu lub zgłoś issue na GitHub.
