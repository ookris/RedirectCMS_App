<?php
/**
 * Cron Runner - endpoint do uruchamiania zadań cron
 *
 * Może być wywoływany:
 * 1. Z wiersza poleceń: php cron_runner.php [--force] [--job=nazwa]
 * 2. Przez HTTP: https://example.com/cron_runner.php?token=TWOJ_TOKEN
 */
// Przykłady użycia w crontab:
// - Co minutę:  * * * * * /usr/bin/php /path/to/App/cron_runner.php >> /var/log/redirectcms_cron.log 2>&1
// - Co 5 minut: */5 * * * * curl -s "https://example.com/cron_runner.php?token=TWOJ_TOKEN" > /dev/null

declare(strict_types=1);

// Sprawdź czy wywołanie z CLI
$isCli = php_sapi_name() === 'cli';

// Ustaw odpowiedni content-type dla HTTP
if (!$isCli) {
    header('Content-Type: text/plain; charset=utf-8');
}

// Helper do logowania
function cronLog(string $message, bool $isCli = false): void
{
    $timestamp = date('Y-m-d H:i:s');
    $line = "[{$timestamp}] {$message}" . ($isCli ? "\n" : "<br>");
    echo $line;

    // Flush output dla HTTP
    if (!$isCli && ob_get_level() > 0) {
        ob_flush();
    }
    flush();
}

// Sprawdź czy aplikacja jest zainstalowana
$configPath = __DIR__ . '/config/config.php';
if (!file_exists($configPath)) {
    cronLog('ERROR: Aplikacja nie jest zainstalowana', $isCli);
    exit(1);
}

// Załaduj wymagane pliki
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Utils.php';
require_once __DIR__ . '/src/SettingsRepository.php';
require_once __DIR__ . '/src/PseudoCron.php';
require_once __DIR__ . '/src/CronTasks.php';

// Załaduj konfigurację
$config = require $configPath;

try {
    $db = new Database($config);
    $pdo = $db->pdo();
} catch (Throwable $e) {
    cronLog('ERROR: Błąd połączenia z bazą danych: ' . $e->getMessage(), $isCli);
    exit(1);
}

$settingsRepo = new SettingsRepository($pdo);

// Parsuj argumenty CLI lub parametry HTTP
$forceRun = false;
$specificJob = null;

if ($isCli) {
    // Parsowanie argumentów CLI
    $options = getopt('', ['force', 'job:', 'help', 'list']);

    if (isset($options['help'])) {
        echo "Użycie: php cron_runner.php [opcje]\n";
        echo "\n";
        echo "Opcje:\n";
        echo "  --help       Wyświetl tę pomoc\n";
        echo "  --list       Lista wszystkich zadań cron\n";
        echo "  --force      Uruchom zadania natychmiast (ignoruj next_run)\n";
        echo "  --job=nazwa  Uruchom tylko konkretne zadanie (po nazwie)\n";
        echo "\n";
        echo "Przykłady:\n";
        echo "  php cron_runner.php                    # Uruchom zaplanowane zadania\n";
        echo "  php cron_runner.php --force            # Uruchom wszystkie aktywne zadania\n";
        echo "  php cron_runner.php --job=clean_trash  # Uruchom tylko zadanie clean_trash\n";
        echo "\n";
        exit(0);
    }

    if (isset($options['list'])) {
        $pseudoCron = new PseudoCron($pdo);
        $jobs = $pseudoCron->getAllJobs();

        echo "Lista zadań cron:\n";
        echo str_repeat('-', 80) . "\n";
        printf("%-25s %-10s %-12s %-20s\n", "Nazwa", "Status", "Interwał", "Następne uruchomienie");
        echo str_repeat('-', 80) . "\n";

        foreach ($jobs as $job) {
            $status = $job['is_active'] ? 'aktywne' : 'wyłączone';
            $interval = Utils::formatDuration((int)$job['interval_seconds']);
            $nextRun = $job['next_run'] ?? 'nie ustawiono';
            printf("%-25s %-10s %-12s %-20s\n", $job['name'], $status, $interval, $nextRun);
        }

        echo str_repeat('-', 80) . "\n";
        exit(0);
    }

    $forceRun = isset($options['force']);
    $specificJob = $options['job'] ?? null;

} else {
    // Tryb HTTP - wymagany token autoryzacyjny
    $cronToken = (string)$settingsRepo->get('cron_token', '');

    if ($cronToken === '') {
        cronLog('ERROR: Token cron nie jest skonfigurowany. Ustaw go w panelu administracyjnym.', $isCli);
        http_response_code(503);
        exit(1);
    }

    $providedToken = $_GET['token'] ?? $_SERVER['HTTP_X_CRON_TOKEN'] ?? '';

    if (!hash_equals($cronToken, $providedToken)) {
        cronLog('ERROR: Nieprawidłowy token autoryzacyjny', $isCli);
        http_response_code(403);
        exit(1);
    }

    // Opcjonalne parametry HTTP
    $forceRun = isset($_GET['force']) && $_GET['force'] === '1';
    $specificJob = isset($_GET['job']) ? (string)$_GET['job'] : null;
}

// Zapisz czas ostatniego wywołania cron runnera
$settingsRepo->set('cron_last_call', date('Y-m-d H:i:s'));

// Loguj rozpoczęcie
cronLog("=== Cron Runner Start ===", $isCli);
cronLog("Mode: " . ($isCli ? 'CLI' : 'HTTP'), $isCli);
if ($forceRun) {
    cronLog("Force mode: ON", $isCli);
}
if ($specificJob) {
    cronLog("Specific job: {$specificJob}", $isCli);
}

try {
    $pseudoCron = new PseudoCron($pdo);

    if ($specificJob !== null) {
        // Uruchom konkretne zadanie
        $result = $pseudoCron->runJobByName($specificJob, $forceRun);
        if ($result === true) {
            cronLog("SUCCESS: Zadanie '{$specificJob}' wykonane pomyślnie", $isCli);
        } elseif ($result === false) {
            cronLog("SKIP: Zadanie '{$specificJob}' jest aktualnie zablokowane lub nie istnieje", $isCli);
        } else {
            cronLog("INFO: Zadanie '{$specificJob}' - {$result}", $isCli);
        }
    } else {
        // Uruchom wszystkie zadania
        $results = $pseudoCron->tickAll($forceRun);

        if (empty($results)) {
            cronLog("INFO: Brak zadań do wykonania", $isCli);
        } else {
            foreach ($results as $jobName => $status) {
                cronLog("{$status}: {$jobName}", $isCli);
            }
        }
    }

    cronLog("=== Cron Runner End ===", $isCli);
    exit(0);

} catch (Throwable $e) {
    cronLog("ERROR: " . $e->getMessage(), $isCli);
    cronLog("Trace: " . $e->getTraceAsString(), $isCli);
    exit(1);
}
