<?php
declare(strict_types=1);

/**
 * PseudoCron - system kolejki zadań wykonywanych przy każdym wejściu na stronę
 * 
 * Pozwala na wykonywanie zadań cyklicznych bez dostępu do prawdziwego crona.
 * Zadania wykonywane są asynchronicznie w tle, nie blokując użytkownika.
 */
class PseudoCron
{
    private PrefixedPDO $pdo;
    private string $lockDir;
    
    // Maksymalny czas wykonania pojedynczego zadania (sekundy)
    private const MAX_EXECUTION_TIME = 30;
    
    // Maksymalny wiek blokady zadania (sekundy) - po tym czasie uznajemy że proces padł
    private const LOCK_MAX_AGE = 300;
    
    public function __construct(PrefixedPDO $pdo, string $lockDir = null)
    {
        $this->pdo = $pdo;
        $this->lockDir = $lockDir ?? __DIR__ . '/../storage/locks';
        
        // Upewnij się że katalog locks istnieje
        if (!is_dir($this->lockDir)) {
            @mkdir($this->lockDir, 0775, true);
        }
    }
    
    /**
     * Główna metoda wywoływana przy każdym wejściu na stronę
     * Sprawdza czy są zadania do wykonania i uruchamia je w tle
     */
    public function tick(): void
    {
        // Sprawdź czy są zadania do wykonania
        $jobs = $this->getJobsToRun();
        
        if (empty($jobs)) {
            return;
        }
        
        // Uruchom zadania asynchronicznie
        foreach ($jobs as $job) {
            $this->runJobAsync($job);
        }
    }
    
    /**
     * Pobierz zadania, które powinny być teraz wykonane
     */
    private function getJobsToRun(): array
    {
        $now = date('Y-m-d H:i:s');
        
        $stmt = $this->pdo->prepare('
            SELECT * FROM cron_jobs
            WHERE is_active = 1
            AND (next_run IS NULL OR next_run <= ?)
            ORDER BY next_run ASC
            LIMIT 5
        ');
        $stmt->execute([$now]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Uruchom zadanie asynchronicznie (w tle, bez blokowania użytkownika)
     */
    private function runJobAsync(array $job): void
    {
        $jobId = (int)$job['id'];
        $lockFile = $this->lockDir . '/cron_' . $jobId . '.lock';
        
        // Sprawdź czy zadanie nie jest już wykonywane
        if ($this->isLocked($lockFile)) {
            return;
        }
        
        // Zaktualizuj next_run natychmiast, aby inne procesy nie próbowały uruchomić tego zadania
        $nextRun = date('Y-m-d H:i:s', time() + (int)$job['interval_seconds']);
        $stmt = $this->pdo->prepare('UPDATE cron_jobs SET next_run = ? WHERE id = ?');
        $stmt->execute([$nextRun, $jobId]);
        
        // Uruchom zadanie w tle
        $this->executeJobInBackground($job, $lockFile);
    }
    
    /**
     * Sprawdź czy zadanie jest zablokowane (wykonywane)
     */
    private function isLocked(string $lockFile): bool
    {
        if (!file_exists($lockFile)) {
            return false;
        }
        
        // Sprawdź wiek blokady
        $age = time() - filemtime($lockFile);
        if ($age > self::LOCK_MAX_AGE) {
            // Blokada jest za stara, usuń ją (proces prawdopodobnie padł)
            @unlink($lockFile);
            return false;
        }
        
        return true;
    }
    
    /**
     * Wykonaj zadanie w tle (bez blokowania request użytkownika)
     */
    private function executeJobInBackground(array $job, string $lockFile): void
    {
        // Utwórz blokadę
        @file_put_contents($lockFile, getmypid());
        
        // Wykonaj zadanie w osobnym procesie
        $jobId = (int)$job['id'];
        $callbackClass = $job['callback_class'];
        $callbackMethod = $job['callback_method'];
        
        // Log rozpoczęcia
        $logId = $this->logStart($jobId);
        
        try {
            // Wywołaj callback
            if (class_exists($callbackClass)) {
                $instance = new $callbackClass($this->pdo);
                if (method_exists($instance, $callbackMethod)) {
                    $startTime = microtime(true);
                    $instance->$callbackMethod();
                    $executionTime = (int)((microtime(true) - $startTime) * 1000);
                    
                    // Log sukcesu
                    $this->logFinish($logId, 'success', null, $executionTime);
                } else {
                    throw new Exception("Method {$callbackMethod} not found in {$callbackClass}");
                }
            } else {
                throw new Exception("Class {$callbackClass} not found");
            }
            
            // Zaktualizuj last_run
            $stmt = $this->pdo->prepare('UPDATE cron_jobs SET last_run = NOW() WHERE id = ?');
            $stmt->execute([$jobId]);
            
        } catch (Throwable $e) {
            // Log błędu
            $this->logFinish($logId, 'error', $e->getMessage());

            // Loguj do pliku
            $logMessage = sprintf(
                '[%s] CRON ERROR | job=%s | error=%s',
                date('Y-m-d H:i:s'),
                $job['name'],
                $e->getMessage()
            );
            Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);

            // Utwórz powiadomienie systemowe
            try {
                require_once __DIR__ . '/NotificationRepository.php';
                $notifRepo = new NotificationRepository($this->pdo);
                $notifRepo->create(
                    'cron',
                    "Błąd zadania cron: {$job['name']}",
                    $e->getMessage(),
                    'error',
                    ['job_id' => $jobId, 'job_name' => $job['name']]
                );
            } catch (Throwable $ignored) {
                // Celowo pomijamy — tworzenie powiadomienia jest operacją best-effort;
                // nie możemy pozwolić, by błąd notyfikacji zamaskował pierwotny wyjątek crona.
            }
        } finally {
            // Usuń blokadę
            @unlink($lockFile);
        }
    }

    /**
     * Zaloguj rozpoczęcie zadania
     */
    private function logStart(int $jobId): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO cron_logs (job_id, started_at, status)
            VALUES (?, NOW(), "running")
        ');
        $stmt->execute([$jobId]);
        return (int)$this->pdo->lastInsertId();
    }
    
    /**
     * Zaloguj zakończenie zadania
     */
    private function logFinish(int $logId, string $status, ?string $errorMessage = null, ?int $executionTimeMs = null): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE cron_logs
            SET finished_at = NOW(),
                status = ?,
                error_message = ?,
                execution_time_ms = ?
            WHERE id = ?
        ');
        $stmt->execute([$status, $errorMessage, $executionTimeMs, $logId]);
    }
    
    /**
     * Zarejestruj nowe zadanie cron
     */
    public function registerJob(
        string $name,
        string $description,
        int $intervalSeconds,
        string $callbackClass,
        string $callbackMethod,
        bool $isActive = true
    ): void
    {
        // Sprawdź czy zadanie już istnieje
        $stmt = $this->pdo->prepare('SELECT id FROM cron_jobs WHERE name = ?');
        $stmt->execute([$name]);
        
        if ($stmt->fetch()) {
            // Zaktualizuj istniejące
            $stmt = $this->pdo->prepare('
                UPDATE cron_jobs
                SET description = ?,
                    interval_seconds = ?,
                    callback_class = ?,
                    callback_method = ?,
                    is_active = ?
                WHERE name = ?
            ');
            $stmt->execute([
                $description,
                $intervalSeconds,
                $callbackClass,
                $callbackMethod,
                $isActive ? 1 : 0,
                $name
            ]);
        } else {
            // Dodaj nowe
            $nextRun = date('Y-m-d H:i:s', time() + $intervalSeconds);
            $stmt = $this->pdo->prepare('
                INSERT INTO cron_jobs (name, description, next_run, interval_seconds, callback_class, callback_method, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $name,
                $description,
                $nextRun,
                $intervalSeconds,
                $callbackClass,
                $callbackMethod,
                $isActive ? 1 : 0
            ]);
        }
    }
    
    /**
     * Pobierz wszystkie zadania
     */
    public function getAllJobs(): array
    {
        $stmt = $this->pdo->query('
            SELECT 
                j.*,
                (SELECT COUNT(*) FROM cron_logs WHERE job_id = j.id) as total_runs,
                (SELECT COUNT(*) FROM cron_logs WHERE job_id = j.id AND status = "error") as error_count,
                (SELECT AVG(execution_time_ms) FROM cron_logs WHERE job_id = j.id AND status = "success") as avg_execution_time
            FROM cron_jobs j
            ORDER BY name ASC
        ');
        return $stmt->fetchAll();
    }
    
    /**
     * Pobierz logi dla zadania
     */
    public function getJobLogs(int $jobId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM cron_logs
            WHERE job_id = ?
            ORDER BY started_at DESC
            LIMIT ?
        ');
        $stmt->bindValue(1, $jobId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Włącz/wyłącz zadanie
     */
    public function toggleJob(int $jobId, bool $isActive): void
    {
        $stmt = $this->pdo->prepare('UPDATE cron_jobs SET is_active = ? WHERE id = ?');
        $stmt->execute([$isActive ? 1 : 0, $jobId]);
    }
    
    /**
     * Usuń zadanie
     */
    public function deleteJob(int $jobId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM cron_jobs WHERE id = ?');
        $stmt->execute([$jobId]);
    }
    
    /**
     * Zmień interwał zadania
     */
    public function updateInterval(int $jobId, int $intervalSeconds): void
    {
        // Pobierz aktualne dane zadania
        $stmt = $this->pdo->prepare('SELECT last_run FROM cron_jobs WHERE id = ?');
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();

        if (!$job) {
            throw new Exception('Zadanie nie istnieje');
        }

        // Przelicz next_run na podstawie last_run + nowy interwał
        if (!empty($job['last_run'])) {
            $nextRun = date('Y-m-d H:i:s', strtotime($job['last_run']) + $intervalSeconds);
            // Jeśli wyliczony next_run jest w przeszłości, ustaw na teraz
            if (strtotime($nextRun) < time()) {
                $nextRun = date('Y-m-d H:i:s');
            }
        } else {
            $nextRun = date('Y-m-d H:i:s', time() + $intervalSeconds);
        }

        $stmt = $this->pdo->prepare('UPDATE cron_jobs SET interval_seconds = ?, next_run = ? WHERE id = ?');
        $stmt->execute([$intervalSeconds, $nextRun, $jobId]);
    }

    /**
     * Wymuś natychmiastowe wykonanie zadania (synchronicznie)
     */
    public function runJobNow(int $jobId): string
    {
        $stmt = $this->pdo->prepare('SELECT * FROM cron_jobs WHERE id = ?');
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();

        if (!$job) {
            return 'Zadanie nie istnieje';
        }

        $lockFile = $this->lockDir . '/cron_' . $jobId . '.lock';

        if ($this->isLocked($lockFile)) {
            return 'Zadanie jest już w trakcie wykonywania';
        }

        try {
            $this->executeJobSync($job);
        } catch (\Throwable $e) {
            return 'Błąd podczas wykonywania zadania: ' . $e->getMessage();
        }
        return 'success';
    }
    
    /**
     * Wyczyść stare logi (starsze niż X dni)
     */
    public function cleanOldLogs(int $days = 30): int
    {
        $stmt = $this->pdo->prepare('
            DELETE FROM cron_logs
            WHERE started_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ');
        $stmt->execute([$days]);
        return $stmt->rowCount();
    }

    /**
     * Uruchom wszystkie zadania (dla wywołania z crontaba)
     *
     * @param bool $force Jeśli true, uruchom wszystkie aktywne zadania niezależnie od next_run
     * @return array Tablica wyników [nazwa_zadania => status]
     */
    public function tickAll(bool $force = false): array
    {
        $results = [];

        if ($force) {
            // Pobierz wszystkie aktywne zadania
            $stmt = $this->pdo->query('
                SELECT * FROM cron_jobs
                WHERE is_active = 1
                ORDER BY name ASC
            ');
        } else {
            // Pobierz tylko zadania do wykonania (bez limitu)
            $now = date('Y-m-d H:i:s');
            $stmt = $this->pdo->prepare('
                SELECT * FROM cron_jobs
                WHERE is_active = 1
                AND (next_run IS NULL OR next_run <= ?)
                ORDER BY next_run ASC
            ');
            $stmt->execute([$now]);
        }

        $jobs = $stmt->fetchAll();

        foreach ($jobs as $job) {
            $jobName = $job['name'];
            $lockFile = $this->lockDir . '/cron_' . (int)$job['id'] . '.lock';

            // Sprawdź blokadę
            if ($this->isLocked($lockFile)) {
                $results[$jobName] = 'LOCKED';
                continue;
            }

            try {
                // Wykonaj zadanie synchronicznie
                $this->executeJobSync($job);
                $results[$jobName] = 'SUCCESS';
            } catch (Throwable $e) {
                $results[$jobName] = 'ERROR: ' . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Uruchom konkretne zadanie po nazwie
     *
     * @param string $name Nazwa zadania
     * @param bool $force Jeśli true, uruchom nawet jeśli nie nadszedł czas
     * @return bool|string true = sukces, false = nie znaleziono/zablokowane, string = wiadomość
     */
    public function runJobByName(string $name, bool $force = false): bool|string
    {
        $stmt = $this->pdo->prepare('SELECT * FROM cron_jobs WHERE name = ?');
        $stmt->execute([$name]);
        $job = $stmt->fetch();

        if (!$job) {
            return "Zadanie '{$name}' nie istnieje";
        }

        if (!$job['is_active']) {
            return "Zadanie '{$name}' jest wyłączone";
        }

        // Sprawdź czy nadszedł czas (chyba że force)
        if (!$force && $job['next_run'] !== null) {
            $nextRun = strtotime($job['next_run']);
            if ($nextRun > time()) {
                return "Zadanie '{$name}' zaplanowane na " . $job['next_run'];
            }
        }

        $lockFile = $this->lockDir . '/cron_' . (int)$job['id'] . '.lock';

        if ($this->isLocked($lockFile)) {
            return false;
        }

        try {
            $this->executeJobSync($job);
            return true;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Wykonaj zadanie synchronicznie (dla CLI/HTTP cron runner)
     */
    private function executeJobSync(array $job): void
    {
        $jobId = (int)$job['id'];
        $lockFile = $this->lockDir . '/cron_' . $jobId . '.lock';

        // Utwórz blokadę
        @file_put_contents($lockFile, getmypid());

        // Zaktualizuj next_run
        $nextRun = date('Y-m-d H:i:s', time() + (int)$job['interval_seconds']);
        $stmt = $this->pdo->prepare('UPDATE cron_jobs SET next_run = ? WHERE id = ?');
        $stmt->execute([$nextRun, $jobId]);

        $callbackClass = $job['callback_class'];
        $callbackMethod = $job['callback_method'];

        // Log rozpoczęcia
        $logId = $this->logStart($jobId);

        try {
            if (!class_exists($callbackClass)) {
                throw new Exception("Class {$callbackClass} not found");
            }

            $instance = new $callbackClass($this->pdo);

            if (!method_exists($instance, $callbackMethod)) {
                throw new Exception("Method {$callbackMethod} not found in {$callbackClass}");
            }

            $startTime = microtime(true);
            $instance->$callbackMethod();
            $executionTime = (int)((microtime(true) - $startTime) * 1000);

            // Log sukcesu
            $this->logFinish($logId, 'success', null, $executionTime);

            // Zaktualizuj last_run
            $stmt = $this->pdo->prepare('UPDATE cron_jobs SET last_run = NOW() WHERE id = ?');
            $stmt->execute([$jobId]);

        } catch (Throwable $e) {
            // Log błędu
            $this->logFinish($logId, 'error', $e->getMessage());

            // Loguj do pliku
            $logMessage = sprintf(
                '[%s] CRON ERROR | job=%s | error=%s',
                date('Y-m-d H:i:s'),
                $job['name'],
                $e->getMessage()
            );
            Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);

            // Utwórz powiadomienie systemowe
            try {
                require_once __DIR__ . '/NotificationRepository.php';
                $notifRepo = new NotificationRepository($this->pdo);
                $notifRepo->create(
                    'cron',
                    "Błąd zadania cron: {$job['name']}",
                    $e->getMessage(),
                    'error',
                    ['job_id' => $jobId, 'job_name' => $job['name']]
                );
            } catch (Throwable $ignored) {
                // Celowo pomijamy — tworzenie powiadomienia jest best-effort;
                // pierwotny wyjątek zostanie rzucony ponownie poniżej.
            }

            throw $e;
        } finally {
            // Usuń blokadę
            @unlink($lockFile);
        }
    }
}
