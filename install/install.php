<?php
/**
 * RedirectCMS - Instalator
 *
 * Ten plik przeprowadzi Cię przez proces instalacji aplikacji RedirectCMS.
 * Po zakończeniu instalacji plik config.php zostanie utworzony automatycznie.
 */

// Rozpocznij sesję dla tokena CSRF oraz przejścia do ekranu finish
session_start();

$step = $_GET['step'] ?? 'welcome';

// Sprawdź czy aplikacja nie jest już zainstalowana
// Pozwól wejść na krok "complete" zaraz po świeżej instalacji (gdy mamy hasło w sesji)
if (file_exists(__DIR__ . '/../config/config.php')) {
    $isFreshComplete = ($step === 'complete') && !empty($_SESSION['generated_admin_password']);
    if (!$isFreshComplete) {
        die('Aplikacja jest już zainstalowana. Jeśli chcesz zainstalować ponownie, usuń plik config/config.php');
    }
}

// Generuj token CSRF jeśli nie istnieje
if (empty($_SESSION['install_csrf_token'])) {
    $_SESSION['install_csrf_token'] = bin2hex(random_bytes(32));
}
$error = null;
$success = null;

// Krok 2: Testowanie połączenia z bazą danych
if ($step === 'test_connection' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Weryfikacja tokena CSRF
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['install_csrf_token'], $csrfToken)) {
        $error = 'Błąd weryfikacji CSRF. Odśwież stronę i spróbuj ponownie.';
    } else {
        $dbHost = trim($_POST['db_host'] ?? '');
        $dbName = trim($_POST['db_name'] ?? '');
        $dbUser = trim($_POST['db_user'] ?? '');
        $dbPass = $_POST['db_pass'] ?? '';

        if (!$dbHost || !$dbName || !$dbUser) {
            $error = 'Wszystkie pola (oprócz hasła) są wymagane.';
        } else {
            try {
                $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
                $pdo = new PDO($dsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                // Zapisz dane w sesji
                $_SESSION['install_db'] = [
                    'host' => $dbHost,
                    'name' => $dbName,
                    'user' => $dbUser,
                    'pass' => $dbPass,
                ];

                header('Location: install.php?step=install');
                exit;
            } catch (PDOException $e) {
                $error = 'Błąd połączenia z bazą danych: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}

// Krok 3: Instalacja bazy danych
if ($step === 'install') {
    if (!isset($_SESSION['install_db'])) {
        header('Location: install.php?step=database');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Weryfikacja tokena CSRF
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['install_csrf_token'], $csrfToken)) {
            $error = 'Błąd weryfikacji CSRF. Odśwież stronę i spróbuj ponownie.';
        } else {
            $db = $_SESSION['install_db'];
            $appEnv = $_POST['app_env'] ?? 'development';
            $sessionSecure = isset($_POST['session_secure']) ? 'true' : 'false';
            $tablePrefix = preg_replace('/[^a-z0-9_]/i', '', trim($_POST['table_prefix'] ?? ''));

            try {
                $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";
                $pdo = new PDO($dsn, $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);

                // Wczytaj i wykonaj schemat bazy danych z pliku db_schema.sql
                $schemaPath = __DIR__ . '/db_schema.sql';
                if (!file_exists($schemaPath)) {
                    throw new Exception('Nie znaleziono pliku db_schema.sql w katalogu install/');
                }

                $sql = file_get_contents($schemaPath);
                if ($sql === false) {
                    throw new Exception('Nie można wczytać pliku db_schema.sql');
                }

                // Usuń komentarze MySQL (zaczynające się od -- lub /* */)
                $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
                $sql = preg_replace('/\/\*.*?\*\//', '', $sql);

                // Usuń dyrektywy MySQL specyficzne dla phpMyAdmin
                $sql = preg_replace('/\/\*![0-9]{5}.*?\*\//', '', $sql);

                // Podziel na pojedyncze instrukcje (rozdzielone średnikami)
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function ($stmt) {
                        // Usuń puste instrukcje i specjalne komendy
                        $stmt = trim($stmt);
                        if (empty($stmt)) return false;
                        if (stripos($stmt, 'SET SQL_MODE') === 0) return false;
                        if (stripos($stmt, 'START TRANSACTION') === 0) return false;
                        if (stripos($stmt, 'COMMIT') === 0) return false;
                        if (stripos($stmt, 'SET time_zone') === 0) return false;
                        return true;
                    }
                );

                // Apply table prefix if set
                if (!empty($tablePrefix)) {
                    $tableNames = [
                        'affiliate_programs', 'audit_logs', 'campaigns', 'campaign_links', 'categories',
                        'cron_jobs', 'cron_logs', 'custom_pages', 'events', 'geo_cache', 'global_stats_cache',
                        'links', 'link_custom_fields', 'link_health_checks', 'link_images', 'link_reactions', 'link_stats',
                        'link_tags', 'login_attempts', 'notifications', 'settings', 'tags',
                    ];
                    foreach ($statements as &$stmt) {
                        foreach ($tableNames as $table) {
                            // Replace table names in various SQL contexts
                            $stmt = preg_replace('/`' . $table . '`/', '`' . $tablePrefix . $table . '`', $stmt);
                            $stmt = preg_replace('/REFERENCES `' . $tablePrefix . $tablePrefix . '/', 'REFERENCES `' . $tablePrefix, $stmt);
                        }
                    }
                    unset($stmt);
                }

                // Wykonaj schemat bez transakcji (DDL i tak wymuszają implicit commit w MySQL)
                try {
                    foreach ($statements as $statement) {
                        if (!empty($statement)) {
                            $pdo->exec($statement);
                        }
                    }
                } catch (Exception $e) {
                    throw new Exception('Błąd podczas tworzenia struktury bazy: ' . $e->getMessage());
                }

                // Wygeneruj losowe bezpieczne hasło administratora
                $adminPassword = bin2hex(random_bytes(8)); // 16 znaków hex
                $adminPasswordHash = password_hash($adminPassword, PASSWORD_DEFAULT);

                // Zapisz dane administratora do bazy danych
                $settingsTable = $tablePrefix . 'settings';
                $stmt = $pdo->prepare("INSERT INTO `{$settingsTable}` (`key`, `value`) VALUES ('admin_username', 'admin'), ('admin_password_hash', ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
                $stmt->execute([$adminPasswordHash]);

                // Zapisz hasło w sesji, aby pokazać je użytkownikowi na końcu instalacji
                $_SESSION['generated_admin_password'] = $adminPassword;

                // Utworz plik config.php
                $configContent = "<?php\n\n";
                $configContent .= "/**\n";
                $configContent .= " * Konfiguracja aplikacji RedirectCMS\n";
                $configContent .= " *\n";
                $configContent .= " * WAŻNE BEZPIECZEŃSTWO:\n";
                $configContent .= " * - Ten plik NIE JEST w repozytorium git (sprawdź .gitignore)\n";
                $configContent .= " * - Ustaw 'app' => 'env' na 'production' w środowisku produkcyjnym\n";
                $configContent .= " * - Ustaw 'session' => 'secure' na true gdy używasz HTTPS\n";
                $configContent .= " * - Zmień hasło do bazy danych na silne\n";
                $configContent .= " */\n\n";
                $configContent .= "return [\n";
                $configContent .= "    'db' => [\n";
                $configContent .= "        'host' => " . var_export($db['host'], true) . ",\n";
                $configContent .= "        'dbname' => " . var_export($db['name'], true) . ",\n";
                $configContent .= "        'user' => " . var_export($db['user'], true) . ",\n";
                $configContent .= "        'pass' => " . var_export($db['pass'], true) . ",\n";
                $configContent .= "        'table_prefix' => " . var_export($tablePrefix, true) . ",\n";
                $configContent .= "        'options' => [\n";
                $configContent .= "            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n";
                $configContent .= "            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n";
                $configContent .= "        ],\n";
                $configContent .= "    ],\n\n";
                $configContent .= "    // Ustawienia aplikacji\n";
                $configContent .= "    'app' => [\n";
                $configContent .= "        // Środowisko: 'development' lub 'production'\n";
                $configContent .= "        // W development błędy są wyświetlane, w production logowane do pliku\n";
                $configContent .= "        'env' => " . var_export($appEnv, true) . ",\n";
                $configContent .= "    ],\n\n";
                $configContent .= "    // Ustawienia sesji\n";
                $configContent .= "    'session' => [\n";
                $configContent .= "        // Ustaw na true gdy używasz HTTPS (wymagane w produkcji!)\n";
                $configContent .= "        'secure' => " . $sessionSecure . ",\n";
                $configContent .= "    ],\n";
                $configContent .= "];\n";

                $configPath = __DIR__ . '/../config/config.php';
                if (file_put_contents($configPath, $configContent) === false) {
                    throw new Exception('Nie udało się utworzyć pliku config.php. Sprawdź uprawnienia do zapisu.');
                }

                // Wyczyść dane instalacji z sesji
                unset($_SESSION['install_db']);

                header('Location: install.php?step=complete');
                exit;

            } catch (Exception $e) {
                $error = 'Błąd instalacji: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalator RedirectCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1E88E5 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .install-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 900px;
            width: 100%;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 18px;
            left: 12.5%;
            width: 75%;
            height: 3px;
            background: #D0D7DE;
            z-index: 0;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 8px 5px;
            position: relative;
            z-index: 1;
        }
        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #D0D7DE;
            color: #3A3F45;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .step.active .step-number {
            background: #1E88E5;
            color: white;
        }
        .step.completed .step-number {
            background: #4CAF50;
            color: white;
        }
        .step-label {
            display: block;
            font-size: 0.85rem;
            color: #3A3F45;
        }
        .step.active .step-label {
            color: #1E88E5;
            font-weight: 600;
        }
        .step.completed .step-label {
            color: #4CAF50;
        }
        .requirements-table td {
            padding: 8px 12px;
        }
        .requirements-table .status-ok {
            color: #4CAF50;
        }
        .requirements-table .status-error {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="install-card p-5">
        <?php
        // System requirements check
        $requirements = [
            'php_version' => [
                'name' => 'PHP',
                'required' => '8.1.0',
                'current' => PHP_VERSION,
                'ok' => version_compare(PHP_VERSION, '8.1.0', '>='),
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL',
                'required' => 'Wymagane',
                'current' => extension_loaded('pdo_mysql') ? 'Zainstalowane' : 'Brak',
                'ok' => extension_loaded('pdo_mysql'),
            ],
            'openssl' => [
                'name' => 'OpenSSL',
                'required' => 'Wymagane',
                'current' => extension_loaded('openssl') ? 'Zainstalowane' : 'Brak',
                'ok' => extension_loaded('openssl'),
            ],
            'json' => [
                'name' => 'JSON',
                'required' => 'Wymagane',
                'current' => extension_loaded('json') ? 'Zainstalowane' : 'Brak',
                'ok' => extension_loaded('json'),
            ],
            'gd' => [
                'name' => 'GD (obrazy)',
                'required' => 'Zalecane',
                'current' => extension_loaded('gd') ? 'Zainstalowane' : 'Brak',
                'ok' => extension_loaded('gd'),
                'warning' => true,
            ],
            'config_writable' => [
                'name' => 'Katalog config/',
                'required' => 'Zapisywalny',
                'current' => is_writable(__DIR__ . '/../config') ? 'OK' : 'Brak uprawnien',
                'ok' => is_writable(__DIR__ . '/../config'),
            ],
        ];
        $allRequirementsMet = true;
        foreach ($requirements as $req) {
            if (!$req['ok'] && empty($req['warning'])) {
                $allRequirementsMet = false;
            }
        }
        ?>

        <?php if ($step === 'welcome'): ?>
            <!-- KROK 1: Powitanie -->
            <div class="step-indicator">
                <div class="step active">
                    <span class="step-number">1</span>
                    <span class="step-label">Witaj</span>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span class="step-label">Baza danych</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-label">Instalacja</span>
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    <span class="step-label">Gotowe</span>
                </div>
            </div>

            <div class="text-center mb-4">
                <h1 class="h3">Witaj w RedirectCMS!</h1>
                <p class="text-muted">Kreator instalacji pomoże Ci skonfigurować aplikację.</p>
            </div>

            <!-- Wymagania systemowe -->
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Wymagania systemowe</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 requirements-table">
                        <thead class="table-light">
                            <tr>
                                <th>Komponent</th>
                                <th>Wymagane</th>
                                <th>Aktualne</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requirements as $key => $req): ?>
                            <tr>
                                <td><?= htmlspecialchars($req['name']) ?></td>
                                <td><?= htmlspecialchars($req['required']) ?></td>
                                <td><?= htmlspecialchars($req['current']) ?></td>
                                <td class="<?= $req['ok'] ? 'status-ok' : 'status-error' ?>">
                                    <?php if ($req['ok']): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M4.5 7.5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1z"/>
                                        </svg>                                    
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!$allRequirementsMet): ?>
            <div class="alert bg-danger-subtle border border-danger text-danger-emphasis">
                <strong>Nie wszystkie wymagania sa spelnione!</strong><br>
                Przed kontynuacja napraw powyzsze problemy.
            </div>
            <?php endif; ?>

            <div class="alert bg-info-subtle border border-info text-info-emphasis">
                <strong>Przed rozpoczeciem upewnij sie, ze:</strong>
                <ul class="mb-0 mt-2">
                    <li>Masz dostep do serwera MySQL 8.0+ lub MariaDB 10.5+</li>
                    <li>Utworzyłeś pustą bazę danych</li>
                    <li>Znasz dane dostępowe (host, nazwa bazy, użytkownik, hasło)</li>
                    <li>Katalog <code>config/</code> ma uprawnienia do zapisu</li>
                </ul>
            </div>

            <div class="text-center mt-4">
                <?php if ($allRequirementsMet): ?>
                    <a href="install.php?step=database" class="btn btn-primary btn-lg">Rozpocznij instalację</a>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg" disabled>Rozpocznij instalację</button>
                    <p class="text-muted mt-2 small">Spełnij wszystkie wymagania, aby kontynuować.</p>
                <?php endif; ?>
            </div>

        <?php elseif ($step === 'database'): ?>
            <!-- KROK 2: Konfiguracja bazy danych -->
            <div class="step-indicator">
                <div class="step completed">
                    <span class="step-number">1</span>
                    <span class="step-label">Witaj</span>
                </div>
                <div class="step active">
                    <span class="step-number">2</span>
                    <span class="step-label">Baza danych</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-label">Instalacja</span>
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    <span class="step-label">Gotowe</span>
                </div>
            </div>

            <h2 class="h4 mb-4">Konfiguracja bazy danych</h2>

            <?php if ($error): ?>
                <div class="alert bg-danger-subtle border border-danger text-danger-emphasis"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="install.php?step=test_connection">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['install_csrf_token']) ?>">
                <div class="mb-3">
                    <label class="form-label">Host bazy danych</label>
                    <input type="text" name="db_host" class="form-control" value="localhost" required>
                    <small class="text-muted">Zazwyczaj: localhost lub 127.0.0.1</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nazwa bazy danych</label>
                    <input type="text" name="db_name" class="form-control" value="redirect_cms" required>
                    <small class="text-muted">Baza musi już istnieć</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Użytkownik</label>
                    <input type="text" name="db_user" class="form-control" value="root" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Hasło</label>
                    <input type="password" name="db_pass" class="form-control">
                    <small class="text-muted">Zostaw puste jeśli nie ma hasła</small>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="install.php?step=welcome" class="btn btn-outline-secondary">Wstecz</a>
                    <button type="submit" class="btn btn-primary">Testuj połączenie</button>
                </div>
            </form>

        <?php elseif ($step === 'install'): ?>
            <!-- KROK 3: Instalacja -->
            <div class="step-indicator">
                <div class="step completed">
                    <span class="step-number">1</span>
                    <span class="step-label">Witaj</span>
                </div>
                <div class="step completed">
                    <span class="step-number">2</span>
                    <span class="step-label">Baza danych</span>
                </div>
                <div class="step active">
                    <span class="step-number">3</span>
                    <span class="step-label">Instalacja</span>
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    <span class="step-label">Gotowe</span>
                </div>
            </div>

            <h2 class="h4 mb-4">Ustawienia aplikacji</h2>

            <?php if ($error): ?>
                <div class="alert bg-danger-subtle border border-danger text-danger-emphasis"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="alert bg-success-subtle border border-success text-success-emphasis mb-4">
                Połączenie z bazą danych działa poprawnie!
            </div>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['install_csrf_token']) ?>">
                <div class="mb-3">
                    <label class="form-label">Środowisko aplikacji</label>
                    <select name="app_env" class="form-select" required>
                        <option value="development">Development (wyświetlanie błędów)</option>
                        <option value="production">Production (logowanie błędów do pliku)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="session_secure" id="sessionSecure">
                        <label class="form-check-label" for="sessionSecure">
                            Używam HTTPS (sesje będą bezpieczne)
                        </label>
                        <small class="d-block text-muted">Zaznacz tylko jeśli Twoja strona działa na HTTPS</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Prefix tabel w bazie danych</label>
                    <input type="text" name="table_prefix" class="form-control" value="rcms_" placeholder="np. rcms_">
                    <small class="text-muted">Opcjonalnie. Przydatne gdy w bazie są inne tabele. Dozwolone: a-z, 0-9, _</small>
                </div>

                <div class="alert bg-info-subtle border border-info text-info-emphasis">
                    <strong>Informacja:</strong> Po instalacji zostanie automatycznie wygenerowane bezpieczne hasło administratora, które zobaczysz na następnym ekranie.
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="install.php?step=database" class="btn btn-outline-secondary">Wstecz</a>
                    <button type="submit" class="btn btn-success">Zainstaluj bazę danych</button>
                </div>
            </form>

        <?php elseif ($step === 'complete'): ?>
            <!-- KROK 4: Zakończenie -->
            <div class="step-indicator">
                <div class="step completed">
                    <span class="step-number">1</span>
                    <span class="step-label">Witaj</span>
                </div>
                <div class="step completed">
                    <span class="step-number">2</span>
                    <span class="step-label">Baza danych</span>
                </div>
                <div class="step completed">
                    <span class="step-number">3</span>
                    <span class="step-label">Instalacja</span>
                </div>
                <div class="step completed">
                    <span class="step-number">4</span>
                    <span class="step-label">Gotowe</span>
                </div>
            </div>

            <div class="text-center">
                <div class="mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-check-circle text-success" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                    </svg>
                </div>

                <h2 class="h3 mb-3">Instalacja zakończona!</h2>
                <p class="text-muted mb-4">RedirectCMS został pomyślnie zainstalowany.</p>

                <?php if (!empty($_SESSION['generated_admin_password'])): ?>
                    <div class="alert bg-danger-subtle border border-danger text-danger-emphasis text-start">
                        <strong>WAŻNE - Dane logowania administratora:</strong><br>
                        Login: <code><strong>admin</strong></code><br>
                        Hasło: <code><strong><?= htmlspecialchars($_SESSION['generated_admin_password']) ?></strong></code><br><br>
                        <strong>ZAPISZ TO HASŁO W BEZPIECZNYM MIEJSCU!</strong><br>
                        Nie będzie można go ponownie wyświetlić. Możesz je później zmienić w ustawieniach panelu.
                    </div>
                <?php endif; ?>

                <div class="alert bg-info-subtle border border-info text-info-emphasis text-start">
                    <strong>Następne kroki:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Zaloguj się do panelu administratora używając powyższych danych</li>
                        <li>Zmień hasło na własne w ustawieniach (opcjonalnie)</li>
                        <li>Skonfiguruj aplikację w ustawieniach</li>
                        <li>Utwórz pierwszy skrócony link</li>
                    </ol>
                </div>

                <div class="alert bg-warning-subtle border border-warning text-warning-emphasis text-start">
                    <strong>Bezpieczeństwo:</strong><br>
                    Usuń katalog <code>install/</code> aby uniemożliwić ponowną instalację.
                </div>

                <div class="mt-4">
                    <a href="../admin/index.php?action=login" class="btn btn-primary btn-lg">Przejdź do panelu admin</a>
                    <a href="../index.php" class="btn btn-outline-secondary btn-lg">Przejdź do strony głównej</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
