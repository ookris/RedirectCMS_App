<?php
declare(strict_types=1);

/**
 * LicenseInstaller — wykrywa plik license*.php w głównym katalogu i instaluje klucz.
 *
 * Flow:
 *   1. Skanuje główny katalog aplikacji w poszukiwaniu pliku license.php lub license_*.php
 *   2. Parsuje plik jako tekst i wyciąga stałą RCMS_LICENSE_KEY (bez include)
 *   3. Waliduje format klucza (64 znaki hex)
 *   4. Zapisuje klucz do App/config/license.php (nadpisuje istniejący)
 *   5. Usuwa plik z głównego katalogu
 *
 * Wywołanie: LicenseInstaller::runIfNeeded(__DIR__)
 * (w głównym index.php lub na dashboardzie admina)
 */
class LicenseInstaller
{
    private const LICENSE_FILE  = __DIR__ . '/../config/license.php';
    private const INSTALL_GLOB  = 'license*.php';
    private const KEY_PATTERN   = '/^[a-f0-9]{64}$/';

    /**
     * Skanuje $appRoot w poszukiwaniu pliku license*.php i instaluje klucz jeśli znaleziony.
     * Bezpieczna do wywołania przy każdym requeście — kończy się natychmiast gdy brak pliku.
     * Nadpisuje istniejący klucz licencyjny — umieszczenie pliku w katalogu głównym
     * jest atomową operacją wymuszającą ponowną instalację licencji.
     *
     * @return bool  true jeśli klucz został zainstalowany, false gdy brak pliku lub błąd
     */
    public static function runIfNeeded(string $appRoot): bool
    {
        $appRoot     = rtrim($appRoot, '/\\');
        $installFile = self::findInstallFile($appRoot);

        if ($installFile === null) {
            return false;
        }
        $installed   = false;

        try {
            self::install($installFile);
            $installed = true;
        } catch (Throwable $e) {
            error_log('[LicenseInstaller] Failed to install license from ' . basename($installFile) . ': ' . $e->getMessage());
            error_log('[LicenseInstaller] Plik zachowany — napraw problem i spróbuj ponownie.');
        }

        // Usuń plik z głównego katalogu tylko po pomyślnej instalacji
        if ($installed && is_file($installFile)) {
            @unlink($installFile);
        }

        return $installed;
    }

    /**
     * Szuka pliku instalacyjnego w $appRoot.
     * Priorytet: license.php > license_<cyfry>.php (pierwszy pasujący).
     * Celowo wąski wzorzec — unika złapania np. license-old.php czy license_backup.php.
     */
    private static function findInstallFile(string $appRoot): ?string
    {
        // 1. Dokładna nazwa license.php
        $exact = $appRoot . '/license.php';
        if (is_file($exact)) {
            return $exact;
        }

        // 2. license_<cyfry>.php (pliki recovery z panelu klienta)
        $candidates = glob($appRoot . '/license_*.php');
        if (!empty($candidates)) {
            foreach ($candidates as $f) {
                // Akceptuj tylko license_<same cyfry>.php
                if (is_file($f) && preg_match('/^license_\d+\.php$/', basename($f))) {
                    return $f;
                }
            }
        }

        return null;
    }

    private static function install(string $installFile): void
    {
        $key = self::extractKey($installFile);

        if ($key === null) {
            throw new RuntimeException('No valid RCMS_LICENSE_KEY found in ' . basename($installFile));
        }

        self::writeLicenseFile($key);
    }

    /**
     * Wyciąga wartość RCMS_LICENSE_KEY z pliku instalacyjnego.
     * Używa parsowania tekstu zamiast include, aby nie wykonywać arbitralnego kodu.
     */
    private static function extractKey(string $installFile): ?string
    {
        $content = @file_get_contents($installFile);
        if ($content === false || $content === '') {
            return null;
        }

        // Szukaj: define('RCMS_LICENSE_KEY', '...');
        if (!preg_match("/define\s*\(\s*'RCMS_LICENSE_KEY'\s*,\s*'([^']+)'\s*\)/", $content, $m)) {
            return null;
        }

        // Walidacja formatu klucza
        if (!preg_match(self::KEY_PATTERN, $m[1])) {
            return null;
        }

        return $m[1];
    }

    private static function writeLicenseFile(string $key): void
    {
        $licenseDir = dirname(self::LICENSE_FILE);

        if (!is_dir($licenseDir)) {
            if (!mkdir($licenseDir, 0750, true)) {
                throw new RuntimeException('Cannot create config directory: ' . $licenseDir);
            }
        }

        $content = "<?php\ndeclare(strict_types=1);\n\n"
            . "// Klucz licencyjny RedirectCMS — zainstalowany automatycznie.\n"
            . "// Nie modyfikuj ręcznie. Jeden klucz jest ważny dla jednej domeny.\n\n"
            . "define('RCMS_LICENSE_KEY', '" . str_replace("'", "\\'", $key) . "');\n";

        if (file_put_contents(self::LICENSE_FILE, $content, LOCK_EX) === false) {
            throw new RuntimeException('Cannot write license file: ' . self::LICENSE_FILE);
        }
    }
}
