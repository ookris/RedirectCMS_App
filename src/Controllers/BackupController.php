<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../BackupService.php';
require_once __DIR__ . '/../AuditService.php';
require_once __DIR__ . '/../SettingsRepository.php';
require_once __DIR__ . '/../PseudoCron.php';

class BackupController extends BaseController
{
    public function backupGet(): void
    {
        Utils::requireLogin();
        $service = new BackupService($this->pdo, dirname(__DIR__, 2));
        $backups = $service->listBackups();

        $settingsRepo = new SettingsRepository($this->pdo);
        $autoEnabled  = (bool)$settingsRepo->get('backup_auto_enabled', false);
        $autoKeep     = max(1, min(30, (int)$settingsRepo->get('backup_auto_keep', 7)));

        $preferredHourRaw = $settingsRepo->get('backup_preferred_hour', '');
        $preferredHour    = ($preferredHourRaw !== '' && $preferredHourRaw !== null)
            ? max(0, min(23, (int)$preferredHourRaw))
            : null;

        $pseudoCron  = new PseudoCron($this->pdo);
        $allJobs     = $pseudoCron->getAllJobs();
        $autoJobArr  = array_values(array_filter($allJobs, fn($j) => $j['name'] === 'auto_backup'));
        $autoJob     = $autoJobArr[0] ?? null;
        $autoInterval = (int)($autoJob['interval_seconds'] ?? 86400);
        $autoJobId    = (int)($autoJob['id'] ?? 0);
        $autoNextRun  = $autoJob['next_run'] ?? null;

        $this->view('backup', [
            'backups'        => $backups,
            'csrf'           => Utils::csrfToken(),
            'autoEnabled'    => $autoEnabled,
            'autoKeep'       => $autoKeep,
            'autoInterval'   => $autoInterval,
            'autoJobId'      => $autoJobId,
            'autoNextRun'    => $autoNextRun,
            'preferredHour'  => $preferredHour,
        ]);
    }

    public function backupCreatePost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
            exit;
        }

        try {
            $service = new BackupService($this->pdo, dirname(__DIR__, 2));
            $filename = $service->createBackup();

            $audit = new AuditService($this->pdo);
            $audit->log('settings_change', null, null, 'Backup', null, ['action' => 'create', 'file' => $filename]);

            $_SESSION['toast_message'] = "Backup utworzony: $filename";
            $_SESSION['toast_type'] = 'success';
        } catch (\Throwable $e) {
            $_SESSION['toast_message'] = 'Błąd tworzenia backupu: ' . $e->getMessage();
            $_SESSION['toast_type'] = 'error';
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
        exit;
    }

    public function backupDownload(): void
    {
        Utils::requireLogin();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo 'Błędny CSRF token';
            return;
        }
        $file = basename((string)($_GET['file'] ?? ''));
        if (empty($file)) {
            header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
            exit;
        }

        try {
            $service = new BackupService($this->pdo, dirname(__DIR__, 2));
            $service->downloadBackup($file);
        } catch (\Throwable $e) {
            $_SESSION['toast_message'] = 'Błąd pobierania: ' . $e->getMessage();
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
            exit;
        }
    }

    public function backupRestorePost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
            exit;
        }

        $file = basename((string)($_GET['file'] ?? ''));
        if (empty($file)) {
            header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
            exit;
        }

        try {
            $service = new BackupService($this->pdo, dirname(__DIR__, 2));
            $result = $service->restoreBackup($file);

            $parts = [];
            if ($result['database']) $parts[] = 'baza danych';
            if ($result['uploads']) $parts[] = 'pliki';

            $audit = new AuditService($this->pdo);
            $audit->log('settings_change', null, null, 'Backup', null, ['action' => 'restore', 'file' => $file]);

            $_SESSION['toast_message'] = 'Przywrócono: ' . implode(', ', $parts);
            $_SESSION['toast_type'] = 'success';
        } catch (\Throwable $e) {
            $_SESSION['toast_message'] = 'Błąd przywracania: ' . $e->getMessage();
            $_SESSION['toast_type'] = 'error';
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
        exit;
    }

    public function backupRestoreUploadPost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
            exit;
        }

        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['toast_message'] = 'Błąd przesyłania pliku';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
            exit;
        }

        $ext = strtolower(pathinfo($_FILES['backup_file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            $_SESSION['toast_message'] = 'Dozwolone są tylko pliki .zip';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
            exit;
        }

        // Luźny pre-check magic bytes — odrzuca oczywiste non-ZIP przed przetwarzaniem.
        // Akceptuje dwie sygnatury nagłówkowe pliku ZIP:
        //   PK\x03\x04 — lokalny nagłówek pliku (standardowe archiwa niepuste)
        //   PK\x05\x06 — końcowy rekord katalogu centralnego (puste archiwa ZIP)
        // Uwaga: PK\x07\x08 to sygnatura data descriptor wewnątrz archiwum — nie pojawia się na początku pliku.
        // Ostateczna walidacja odbywa się przez ZipArchive::open() wewnątrz restoreFromPath().
        $tmpPath = $_FILES['backup_file']['tmp_name'];
        $handle = fopen($tmpPath, 'rb');
        $magicBytes = $handle !== false ? fread($handle, 4) : '';
        if ($handle !== false) {
            fclose($handle);
        }
        $validZipSignatures = ["PK\x03\x04", "PK\x05\x06"];
        if (!in_array(substr($magicBytes, 0, 4), $validZipSignatures, true)) {
            $_SESSION['toast_message'] = 'Przesłany plik nie jest prawidłowym archiwum ZIP';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
            exit;
        }

        // Przenieś plik do katalogu tymczasowego systemu — zostanie usunięty w finally
        $tmpZip = tempnam(sys_get_temp_dir(), 'rcms_restore_');
        try {
            if ($tmpZip === false || !move_uploaded_file($tmpPath, $tmpZip)) {
                throw new RuntimeException('Nie udało się zapisać przesłanego pliku');
            }

            $service = new BackupService($this->pdo, dirname(__DIR__, 2));
            $result = $service->restoreFromPath($tmpZip);

            $parts = [];
            if ($result['database']) $parts[] = 'baza danych';
            if ($result['uploads']) $parts[] = 'pliki';

            $_SESSION['toast_message'] = 'Przywrócono z przesłanego pliku: ' . implode(', ', $parts);
            $_SESSION['toast_type'] = 'success';
        } catch (\Throwable $e) {
            $_SESSION['toast_message'] = 'Błąd przywracania: ' . $e->getMessage();
            $_SESSION['toast_type'] = 'error';
        } finally {
            if ($tmpZip !== false && file_exists($tmpZip)) {
                @unlink($tmpZip);
            }
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
        exit;
    }

    public function backupDeletePost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
            exit;
        }

        $file = basename((string)($_GET['file'] ?? ''));
        $service = new BackupService($this->pdo, dirname(__DIR__, 2));

        if ($service->deleteBackup($file)) {
            $_SESSION['toast_message'] = "Usunięto backup: $file";
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Nie udało się usunąć backupu';
            $_SESSION['toast_type'] = 'error';
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
        exit;
    }

    public function backupAutoSettingsPost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
            exit;
        }

        $enabled = isset($_POST['backup_auto_enabled']);
        $keep    = max(1, min(30, (int)($_POST['backup_auto_keep'] ?? 7)));

        // Parse interval (value + unit) the same way as cron admin
        $intervalValue = max(1, min(365, (int)($_POST['auto_interval_value'] ?? 1)));
        $multipliers   = ['minutes' => 60, 'hours' => 3600, 'days' => 86400, 'weeks' => 604800, 'months' => 2592000];
        $intervalUnit  = $_POST['auto_interval_unit'] ?? 'days';
        if (!isset($multipliers[$intervalUnit])) {
            $intervalUnit = 'days';
        }
        $intervalSeconds = $intervalValue * $multipliers[$intervalUnit];

        $preferredHour = (isset($_POST['backup_preferred_hour']) && $_POST['backup_preferred_hour'] !== '')
            ? max(0, min(23, (int)$_POST['backup_preferred_hour']))
            : null;

        $settingsRepo = new SettingsRepository($this->pdo);
        $settingsRepo->set('backup_auto_enabled', $enabled ? '1' : '0');
        $settingsRepo->set('backup_auto_keep', $keep);
        $settingsRepo->set('backup_preferred_hour', $preferredHour !== null ? (string)$preferredHour : '');

        // Register (or update) the cron job — registerJob handles insert/update
        $pseudoCron = new PseudoCron($this->pdo);
        $pseudoCron->registerJob(
            'auto_backup',
            'Automatyczne tworzenie kopii zapasowych (SQL + pliki uploads)',
            $intervalSeconds,
            'CronTasks',
            'createAutoBackup',
            $enabled
        );

        // Recalculate next_run respecting the preferred hour
        if ($enabled) {
            $stmt = $this->pdo->prepare("SELECT id FROM cron_jobs WHERE name = 'auto_backup'");
            $stmt->execute();
            $cronJob = $stmt->fetch();
            if ($cronJob) {
                $nextRun = BackupService::calcNextRunWithPreferredHour($intervalSeconds, $preferredHour);
                $stmt = $this->pdo->prepare('UPDATE cron_jobs SET next_run = ? WHERE id = ?');
                $stmt->execute([$nextRun, (int)$cronJob['id']]);
            }
        }

        $_SESSION['toast_message'] = 'Ustawienia automatycznych kopii zapasowych zostały zapisane.';
        $_SESSION['toast_type'] = 'success';
        header('Location: ' . $this->basePath . '/admin/index.php?action=backup');
        exit;
    }
}
