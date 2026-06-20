<?php
declare(strict_types=1);

/**
 * Klasa do konfiguracji debugowania PHP na podstawie ustawień z bazy danych
 */
class DebugConfig
{
    private static bool $initialized = false;
    private static string $logPath;

    /**
     * Inicjalizuje konfigurację debugowania
     *
     * @param PDO $pdo Połączenie z bazą danych
     * @param string $logDir Ścieżka do katalogu logów
     */
    public static function init($pdo, string $logDir): void
    {
        if (self::$initialized) {
            return;
        }

        self::$logPath = rtrim($logDir, '/') . '/php_errors.log';

        // Upewnij się, że katalog logów istnieje
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        try {
            $settings = new SettingsRepository($pdo);

            $debugEnabled = (bool)$settings->get('debug_enabled', false);
            $logErrors = (bool)$settings->get('debug_log_errors', true);
            $displayErrors = (bool)$settings->get('debug_display_errors', false);
            $errorLevel = (string)$settings->get('debug_error_level', 'E_ALL');

            self::configure($debugEnabled, $logErrors, $displayErrors, $errorLevel);

        } catch (Throwable $e) {
            // W przypadku błędu bazy danych, użyj bezpiecznych domyślnych wartości
            self::configure(false, true, false, 'E_ALL');
        }

        self::$initialized = true;
    }

    /**
     * Konfiguruje PHP error reporting
     */
    private static function configure(bool $debugEnabled, bool $logErrors, bool $displayErrors, string $errorLevel): void
    {
        // Ustaw poziom raportowania błędów
        $level = self::parseErrorLevel($errorLevel);
        error_reporting($level);

        if ($debugEnabled) {
            // Logowanie błędów do pliku
            ini_set('log_errors', $logErrors ? '1' : '0');
            if ($logErrors) {
                ini_set('error_log', self::$logPath);
            }

            // Wyświetlanie błędów (tylko jeśli włączone - ostrożnie w produkcji!)
            ini_set('display_errors', $displayErrors ? '1' : '0');
            ini_set('display_startup_errors', $displayErrors ? '1' : '0');
        } else {
            // Debugowanie wyłączone - bezpieczne ustawienia produkcyjne
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            // Zawsze loguj błędy do pliku dla bezpieczeństwa
            ini_set('log_errors', '1');
            ini_set('error_log', self::$logPath);
        }
    }

    /**
     * Parsuje string poziomu błędów na wartość int
     */
    private static function parseErrorLevel(string $level): int
    {
        $levels = [
            'E_ALL' => E_ALL,
            'E_ERROR' => E_ERROR,
            'E_WARNING' => E_WARNING,
            'E_PARSE' => E_PARSE,
            'E_NOTICE' => E_NOTICE,
            'E_STRICT' => E_STRICT,
            'E_DEPRECATED' => E_DEPRECATED,
            'E_ALL_NO_DEPRECATED' => E_ALL & ~E_DEPRECATED,
            'E_ALL_NO_NOTICE' => E_ALL & ~E_NOTICE,
            'E_ERROR_WARNING' => E_ERROR | E_WARNING | E_PARSE,
        ];

        return $levels[$level] ?? E_ALL;
    }

    /**
     * Zwraca ścieżkę do pliku logów
     */
    public static function getLogPath(): string
    {
        return self::$logPath ?? '';
    }

    /**
     * Pobiera ostatnie linie z pliku logów
     *
     * @param int $lines Liczba linii do pobrania
     * @return array Tablica linii logów
     */
    public static function getRecentLogs(int $lines = 100): array
    {
        $logPath = self::$logPath ?? '';
        if (!$logPath || !file_exists($logPath)) {
            return [];
        }

        $file = new SplFileObject($logPath, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        if ($totalLines === 0) {
            return [];
        }

        $startLine = max(0, $totalLines - $lines);
        $logs = [];

        $file->seek($startLine);
        while (!$file->eof()) {
            $line = $file->fgets();
            if (trim($line) !== '') {
                $logs[] = $line;
            }
        }

        return array_reverse($logs);
    }

    /**
     * Czyści plik logów
     *
     * @return bool
     */
    public static function clearLogs(): bool
    {
        $logPath = self::$logPath ?? '';
        if (!$logPath) {
            return false;
        }

        return @file_put_contents($logPath, '') !== false;
    }

    /**
     * Zwraca rozmiar pliku logów w bajtach
     */
    public static function getLogSize(): int
    {
        $logPath = self::$logPath ?? '';
        if (!$logPath || !file_exists($logPath)) {
            return 0;
        }
        return (int)filesize($logPath);
    }

    /**
     * Formatuje rozmiar pliku do czytelnej postaci
     */
    public static function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
