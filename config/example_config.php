<?php

/**
 * Konfiguracja aplikacji RedirectCMS
 *
 * WAŻNE:
 * - Ten plik jest PRZYKŁADOWĄ konfiguracją i JEST w repozytorium git
 * - Właściwą konfigurację zapisz w pliku config.php (nie wersjonuj go w gicie)
 * - Instalator automatycznie generuje config.php – możesz go później edytować ręcznie
 * - Ustaw 'app' => 'env' na 'production' w środowisku produkcyjnym
 * - Ustaw 'session' => 'secure' na true gdy używasz HTTPS
 * - Zmień hasło do bazy danych na silne
 */

return [
    'db' => [
        'host' => 'localhost',
        'dbname' => 'redirect_cms',
        'user' => 'root',
        'pass' => '',  // ZMIEŃ NA WŁASNE HASŁO!
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ],

    // Ustawienia aplikacji
    'app' => [
        // Środowisko: 'development' lub 'production'
        // W development błędy są wyświetlane, w production logowane do pliku
        'env' => 'development',
    ],

    // Ustawienia sesji
    'session' => [
        // Ustaw na true gdy używasz HTTPS (wymagane w produkcji!)
        'secure' => false,
    ],

    // Klucz szyfrowania dla haseł SMTP (wygeneruj unikalny 64-znakowy hex)
    // WAŻNE: Wygeneruj własny klucz i NIGDY nie wersjonuj go w git!
    // Generowanie: php -r "echo bin2hex(openssl_random_pseudo_bytes(32)) . PHP_EOL;"
    'encryption_key' => 'REPLACE_ME_WITH_64_CHAR_HEX_KEY_EXAMPLE_ONLY',
];
