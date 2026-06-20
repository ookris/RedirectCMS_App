<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../PseudoCron.php';
require_once __DIR__ . '/../CronTasks.php';
require_once __DIR__ . '/../SettingsRepository.php';

class CronController extends BaseController
{
    public function cronJobsGet(): void
    {
        Utils::requireLogin();
        $pseudoCron = new PseudoCron($this->pdo);
        $jobs = $pseudoCron->getAllJobs();

        $stmt = $this->pdo->query('
            SELECT l.*, j.name as job_name
            FROM cron_logs l
            JOIN cron_jobs j ON l.job_id = j.id
            ORDER BY l.started_at DESC
            LIMIT 20
        ');
        $recentLogs = $stmt->fetchAll();

        $settingsRepo = new SettingsRepository($this->pdo);
        $cronToken = (string)$settingsRepo->get('cron_token', '');
        $lastCronRun = $settingsRepo->get('cron_last_call', '') ?: null;
        $pseudoCronEnabled = (string)$settingsRepo->get('pseudo_cron_enabled', '1') !== '0';

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $cronRunnerUrl = $scheme . '://' . $host . $this->basePath . '/cron';
        $cronRunnerPath = realpath(__DIR__ . '/../../cron_runner.php');

        $this->view('cron', [
            'csrf'              => Utils::csrfToken(),
            'jobs'              => $jobs,
            'recentLogs'        => $recentLogs,
            'lastCronRun'       => $lastCronRun,
            'cronToken'         => $cronToken,
            'cronRunnerUrl'     => $cronRunnerUrl,
            'cronRunnerPath'    => $cronRunnerPath,
            'pseudoCronEnabled' => $pseudoCronEnabled,
        ]);
    }

    public function cronGenerateToken(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $newToken = bin2hex(random_bytes(16));

        $settingsRepo = new SettingsRepository($this->pdo);
        $settingsRepo->set('cron_token', $newToken);

        Utils::startSession();
        $_SESSION['cron_success'] = 'Wygenerowano nowy token dostępu do cron';
        header('Location: ' . $this->basePath . '/admin/index.php?action=cron_jobs');
    }

    public function cronRegisterDefaults(): void
    {
        Utils::requireLogin();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST', true, 405);
            http_response_code(405);
            echo 'Metoda niedozwolona (użyj POST)';
            return;
        }

        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        try {
            $pseudoCron = new PseudoCron($this->pdo);

            $pseudoCron->registerJob('clean_geo_cache', 'Czyszczenie starego cache geolokalizacji (starszego niż 30 dni)', 86400, 'CronTasks', 'cleanGeoCache', true);
            $pseudoCron->registerJob('refresh_global_stats', 'Odświeżanie cache statystyk globalnych dla różnych przedziałów czasowych', 1800, 'CronTasks', 'refreshGlobalStatsCache', true);
            $pseudoCron->registerJob('clean_cron_logs', 'Usuwanie logów cron starszych niż 30 dni', 604800, 'CronTasks', 'cleanCronLogs', true);
            $pseudoCron->registerJob('clean_old_events', 'Usuwanie wydarzeń (events) starszych niż 90 dni', 2592000, 'CronTasks', 'cleanOldEvents', true);
            $pseudoCron->registerJob('clean_orphan_uploads', 'Usuwanie plików w uploads/ nieużywanych w bazie (og_image/link_images/branding)', 21600, 'CronTasks', 'cleanOrphanUploads', true);
            $pseudoCron->registerJob('refresh_license_support_cache', 'Odświeżanie cache statusu licencji i daty wsparcia (API, co 24h)', 86400, 'CronTasks', 'refreshLicenseSupportCache', true);
            $pseudoCron->registerJob('send_email_notifications', 'Wysyłanie powiadomień email z podsumowaniem statystyk top linków', 3600, 'CronTasks', 'sendEmailNotifications', true);
            $pseudoCron->registerJob('clean_trash', 'Automatyczne usuwanie linków z kosza starszych niz ustawiona liczba dni', 86400, 'CronTasks', 'cleanTrash', true);
            $pseudoCron->registerJob('check_broken_links', 'Sprawdzanie dostępności linków docelowych i wykrywanie błędnych', 86400, 'CronTasks', 'checkBrokenLinks', true);
            $pseudoCron->registerJob('clean_link_health_history', 'Usuwanie starej historii sprawdzeń linków (zachowuje ostatnie 10 na link)', 604800, 'CronTasks', 'cleanLinkHealthHistory', true);
            $pseudoCron->registerJob('clean_audit_logs', 'Usuwanie logow audytu starszych niz 90 dni', 2592000, 'CronTasks', 'cleanAuditLogs', true);
            $pseudoCron->registerJob('auto_backup', 'Automatyczne tworzenie kopii zapasowych (SQL + pliki uploads)', 86400, 'CronTasks', 'createAutoBackup', false);

            Utils::startSession();
            $_SESSION['cron_success'] = 'Zarejestrowano domyslne zadania cron';
        } catch (\Throwable $e) {
            Utils::startSession();
            $_SESSION['cron_error'] = 'Błąd rejestracji: ' . $e->getMessage();
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=cron_jobs');
    }

    public function cronToggle(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $jobId = (int)($_POST['job_id'] ?? 0);
        $isActive = (int)($_POST['is_active'] ?? 0) === 1;

        $pseudoCron = new PseudoCron($this->pdo);
        $pseudoCron->toggleJob($jobId, $isActive);

        Utils::startSession();
        $_SESSION['cron_success'] = $isActive ? 'Zadanie włączone' : 'Zadanie wyłączone';
        header('Location: ' . $this->basePath . '/admin/index.php?action=cron_jobs');
    }

    public function cronRunNow(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $jobId = (int)($_POST['job_id'] ?? 0);

        $pseudoCron = new PseudoCron($this->pdo);

        Utils::startSession();
        try {
            $result = $pseudoCron->runJobNow($jobId);
            if ($result === 'success') {
                $_SESSION['cron_success'] = 'Zadanie wykonane pomyślnie';
            } else {
                $_SESSION['cron_error'] = $result;
            }
        } catch (\Throwable $e) {
            $_SESSION['cron_error'] = 'Błąd wykonania: ' . $e->getMessage();
        }
        header('Location: ' . $this->basePath . '/admin/index.php?action=cron_jobs');
    }

    public function cronUpdateInterval(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $jobId = (int)($_POST['job_id'] ?? 0);
        $intervalSeconds = (int)($_POST['interval_seconds'] ?? 0);

        if ($intervalSeconds < 60 || $intervalSeconds > 31536000) {
            Utils::startSession();
            $_SESSION['cron_error'] = 'Interwał musi być między 1 minutą a 365 dniami';
            header('Location: ' . $this->basePath . '/admin/index.php?action=cron_jobs');
            return;
        }

        try {
            $pseudoCron = new PseudoCron($this->pdo);
            $pseudoCron->updateInterval($jobId, $intervalSeconds);

            Utils::startSession();
            $_SESSION['cron_success'] = 'Interwał zadania został zmieniony';
        } catch (\Throwable $e) {
            Utils::startSession();
            $_SESSION['cron_error'] = 'Błąd zmiany interwału: ' . $e->getMessage();
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=cron_jobs');
    }

    public function cronDelete(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $jobId = (int)($_POST['job_id'] ?? 0);

        $pseudoCron = new PseudoCron($this->pdo);
        $pseudoCron->deleteJob($jobId);

        Utils::startSession();
        $_SESSION['cron_success'] = 'Zadanie zostało usunięte';
        header('Location: ' . $this->basePath . '/admin/index.php?action=cron_jobs');
    }

    public function cronLogs(): void
    {
        Utils::requireLogin();
        $jobId = (int)($_GET['job_id'] ?? 0);

        if ($jobId <= 0) {
            http_response_code(400);
            echo 'Nieprawidłowe ID zadania';
            return;
        }

        $stmt = $this->pdo->prepare('SELECT * FROM cron_jobs WHERE id = ?');
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();

        if (!$job) {
            http_response_code(404);
            echo 'Nie znaleziono zadania';
            return;
        }

        $pseudoCron = new PseudoCron($this->pdo);
        $logs = $pseudoCron->getJobLogs($jobId, 100);

        $this->view('cron_logs', [
            'job' => $job,
            'logs' => $logs,
        ]);
    }

    public function cronPseudoCronPost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $enabled = isset($_POST['pseudo_cron_enabled']) ? '1' : '0';
        $settingsRepo = new SettingsRepository($this->pdo);
        $settingsRepo->set('pseudo_cron_enabled', $enabled);

        Utils::startSession();
        $_SESSION['cron_success'] = $enabled === '1'
            ? 'Wbudowany pseudo-cron został włączony.'
            : 'Wbudowany pseudo-cron został wyłączony.';
        header('Location: ' . $this->basePath . '/admin/index.php?action=cron_jobs');
        exit;
    }
}
