<?php
declare(strict_types=1);

/**
 * Serwis backupu i przywracania bazy danych RedirectCMS.
 * Backup: eksport SQL (INSERT INTO) + ZIP plików uploads.
 * Restore: import SQL + rozpakowanie plików.
 */
class BackupService
{
    public const MAX_ENTRY_BYTES   = 104_857_600; // 100 MB per file
    public const MAX_ENTRIES       = 20_000;      // cap on number of entries

    private PrefixedPDO $pdo;
    private string $backupDir;
    private string $appDir;
    private string $legacyBackupDir;

    public function __construct(PrefixedPDO $pdo, string $appDir)
    {
        $this->pdo = $pdo;
        $this->appDir = rtrim($appDir, '/');
        // Move backups to a non-public path.
        $this->backupDir = $this->appDir . '/storage/private_backups';
        $this->legacyBackupDir = $this->appDir . '/storage/backups';

        $this->ensureDirectory($this->backupDir);
        $this->ensureProtectionFile($this->backupDir);
    }

    /**
     * Create a full backup (database SQL + uploads ZIP).
     * $isAuto = true: filename prefixed with 'auto_backup_', limit enforced from settings.
     * Returns the filename of the created backup.
     */
    public function createBackup(bool $isAuto = false): string
    {
        $timestamp = date('Y-m-d_H-i-s');
        $prefix    = $isAuto ? 'auto_backup_' : 'backup_';
        $filename  = "{$prefix}{$timestamp}.zip";
        $filepath  = $this->backupDir . '/' . $filename;

        $zip = new ZipArchive();
        if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Nie można utworzyć pliku ZIP');
        }

        // Export database to SQL
        $sqlContent = $this->exportDatabaseSql();
        $zip->addFromString('database.sql', $sqlContent);

        // Add uploads directory
        $uploadsDir = $this->appDir . '/uploads';
        if (is_dir($uploadsDir)) {
            $this->addDirectoryToZip($zip, $uploadsDir, 'uploads');
        }

        // Config excluded from backup — contains DB credentials and secrets

        $zip->close();

        if ($isAuto) {
            // Read keep-count from settings; fall back to 7
            require_once __DIR__ . '/SettingsRepository.php';
            $repo = new SettingsRepository($this->pdo);
            $keep = max(1, min(30, (int)$repo->get('backup_auto_keep', 7)));
            $this->enforceBackupLimitForPrefix('auto_backup_', $keep);
        } else {
            $this->enforceBackupLimit(10);
        }

        return $filename;
    }

    /**
     * Export all database tables as SQL INSERT statements.
     * Uses a temp file to avoid accumulating the entire dump in memory.
     */
    private function exportDatabaseSql(): string
    {
        $tmpFile = tmpfile();
        if ($tmpFile === false) {
            throw new RuntimeException('Nie można utworzyć pliku tymczasowego');
        }

        $write = function(string $data) use ($tmpFile): void {
            fwrite($tmpFile, $data);
        };

        $write("-- RedirectCMS Backup\n");
        $write("-- Date: " . date('Y-m-d H:i:s') . "\n");
        $write("-- Version: PHP " . PHP_VERSION . "\n\n");
        $write("SET NAMES utf8mb4;\n");
        $write("SET FOREIGN_KEY_CHECKS = 0;\n\n");

        $tables = $this->pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $createStmt = $this->pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
            $write("-- Table: {$table}\n");
            $write("DROP TABLE IF EXISTS `{$table}`;\n");
            $write($createStmt['Create Table'] . ";\n\n");

            $stmt = $this->pdo->query("SELECT * FROM `{$table}`");
            $firstRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($firstRow !== false) {
                $columns = array_keys($firstRow);
                $columnList = implode('`, `', $columns);

                $chunk = [];
                $formatRow = function(array $row): string {
                    $rowValues = [];
                    foreach ($row as $value) {
                        $rowValues[] = ($value === null) ? 'NULL' : $this->pdo->quote((string)$value);
                    }
                    return '(' . implode(', ', $rowValues) . ')';
                };

                $chunk[] = $formatRow($firstRow);
                $rowCount = 1;

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $chunk[] = $formatRow($row);
                    $rowCount++;
                    if ($rowCount % 100 === 0) {
                        $write("INSERT INTO `{$table}` (`{$columnList}`) VALUES\n");
                        $write(implode(",\n", $chunk) . ";\n");
                        $chunk = [];
                    }
                }

                if (!empty($chunk)) {
                    $write("INSERT INTO `{$table}` (`{$columnList}`) VALUES\n");
                    $write(implode(",\n", $chunk) . ";\n");
                }
                $write("\n");
            }
        }

        $write("SET FOREIGN_KEY_CHECKS = 1;\n");

        rewind($tmpFile);
        $result = stream_get_contents($tmpFile);
        fclose($tmpFile);
        return $result;
    }

    /**
     * Recursively add a directory to a ZIP archive.
     */
    private function addDirectoryToZip(ZipArchive $zip, string $realPath, string $zipPath): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($realPath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipPath . '/' . substr($filePath, strlen($realPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Restore from a backup ZIP file (by filename within backup directory).
     */
    public function restoreBackup(string $filename): array
    {
        $filepath = $this->requireBackupPath($filename);
        return $this->restoreFromPath($filepath);
    }

    /**
     * Restore from a backup ZIP at an arbitrary absolute path.
     * Used when restoring from an uploaded file stored in a temp location.
     */
    public function restoreFromPath(string $fullPath): array
    {
        if (!file_exists($fullPath)) {
            throw new RuntimeException('Plik backupu nie istnieje');
        }

        $zip = new ZipArchive();
        if ($zip->open($fullPath) !== true) {
            throw new RuntimeException('Nie można otworzyć pliku ZIP');
        }

        if ($zip->numFiles > self::MAX_ENTRIES) {
            $zip->close();
            throw new RuntimeException('Archiwum zawiera zbyt wiele plików');
        }

        $result = ['database' => false, 'uploads' => false, 'config' => false];

        // Guard database.sql size to prevent OOM on crafted archives
        $statSql = $zip->statName('database.sql');
        if ($statSql !== false) {
            $sqlSize = (int)($statSql['size'] ?? 0);
            if ($sqlSize > self::MAX_ENTRY_BYTES) {
                $zip->close();
                throw new RuntimeException('Plik database.sql jest zbyt duży');
            }
        }

        // First pass: validate uploads entries before touching database or filesystem
        $validatedUploads = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!str_starts_with($name, 'uploads/')) {
                continue;
            }

            // Block path traversal (.. sequences, null bytes, backslashes)
            if (preg_match('/\.{2}|[\x00\\\\]/', $name)) {
                $zip->close();
                throw new RuntimeException('Archiwum zawiera niedozwolone ścieżki');
            }

            $normalized = ltrim($name, '/');
            if ($this->isBlockedUploadPath($normalized)) {
                $zip->close();
                throw new RuntimeException('Archiwum zawiera pliki ukryte lub konfiguracyjne w uploads');
            }

            if ($this->isDangerousExtension($normalized)) {
                $zip->close();
                throw new RuntimeException('Archiwum zawiera pliki o niedozwolonym rozszerzeniu');
            }

            $stat = $zip->statIndex($i);
            $size = $stat !== false ? (int)($stat['size'] ?? 0) : 0;
            if ($size > self::MAX_ENTRY_BYTES) {
                $zip->close();
                throw new RuntimeException('Archiwum zawiera zbyt duży plik');
            }

            $validatedUploads[] = [
                'name'   => $normalized,
                'is_dir' => str_ends_with($name, '/'),
            ];
        }

        // Restore database after full validation
        $sqlContent = $zip->getFromName('database.sql');
        if ($sqlContent !== false) {
            $this->importSql($sqlContent);
            $result['database'] = true;
        }

        // Restore uploads using validated entries
        $baseDir = realpath($this->appDir);
        $uploadedAny = false;
        foreach ($validatedUploads as $entry) {
            $name = $entry['name'];
            $targetPath = $this->appDir . '/' . $name;

            if ($entry['is_dir']) {
                if (!is_dir($targetPath)) {
                    @mkdir($targetPath, 0755, true);
                }
                continue;
            }

            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }
            $realDir = realpath($targetDir);
            if ($realDir === false || ($baseDir !== false && !str_starts_with($realDir, $baseDir))) {
                continue;
            }

            $stream = $zip->getStream($name);
            if ($stream === false) {
                continue;
            }
            $outFile = @fopen($targetPath, 'wb');
            if ($outFile === false) {
                fclose($stream);
                continue;
            }
            stream_copy_to_stream($stream, $outFile);
            fclose($stream);
            fclose($outFile);
            $uploadedAny = true;
        }
        $result['uploads'] = $uploadedAny;

        $zip->close();
        return $result;
    }

    /**
     * Split a SQL dump into individual statements, respecting quoted string
     * literals. A naive split on ";\n" breaks if a text column contains that
     * exact byte sequence (e.g. "Price: 10;\nFree shipping") — the INSERT would
     * be cut mid-statement and the whole restore would fail on ordinary user
     * content, not just malicious input.
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $len = strlen($sql);
        $inSingle = false;
        $inDouble = false;
        $inBacktick = false;

        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            $current .= $ch;

            if ($inSingle || $inDouble) {
                if ($ch === '\\' && $i + 1 < $len) {
                    $current .= $sql[++$i];
                    continue;
                }
                if (($inSingle && $ch === "'") || ($inDouble && $ch === '"')) {
                    $inSingle = false;
                    $inDouble = false;
                }
                continue;
            }
            if ($inBacktick) {
                if ($ch === '`') {
                    $inBacktick = false;
                }
                continue;
            }

            if ($ch === "'") { $inSingle = true; continue; }
            if ($ch === '"') { $inDouble = true; continue; }
            if ($ch === '`') { $inBacktick = true; continue; }

            if ($ch === ';') {
                $statements[] = $current;
                $current = '';
            }
        }

        if (trim($current) !== '') {
            $statements[] = $current;
        }

        return $statements;
    }

    /**
     * Import SQL statements from backup.
     */
    private function importSql(string $sql): void
    {
        // Remove comments and split into statements
        $sql = preg_replace('/^--.*$/m', '', $sql);
        $statements = $this->splitSqlStatements($sql);

        $this->pdo->beginTransaction();
        try {
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                $stmtNoSemicolon = rtrim($stmt, "; \t\n\r");
                if ($stmt === '' || $stmtNoSemicolon === 'SET FOREIGN_KEY_CHECKS = 0' || $stmtNoSemicolon === 'SET FOREIGN_KEY_CHECKS = 1') {
                    continue;
                }
                $this->pdo->exec($stmt);
            }

            $this->pdo->commit();
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Backup restore SQL error (rolled back): " . $e->getMessage());
            throw $e;
        } finally {
            try {
                $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            } catch (PDOException $e) {
                error_log("Failed to re-enable FOREIGN_KEY_CHECKS: " . $e->getMessage());
            }
        }
    }

    /**
     * List all available backups (manual + auto), newest first.
     */
    public function listBackups(): array
    {
        $filesMap = [];

        $dirs = [$this->backupDir, $this->legacyBackupDir];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $manual = glob($dir . '/backup_*.zip') ?: [];
            $auto   = glob($dir . '/auto_backup_*.zip') ?: [];
            foreach (array_merge($manual, $auto) as $file) {
                $name = basename($file);
                // Prefer the first occurrence (new path wins because it is first in $dirs)
                if (!isset($filesMap[$name])) {
                    $filesMap[$name] = $file;
                }
            }
        }

        if (empty($filesMap)) {
            return [];
        }

        // Sort by mtime descending (newest first)
        uasort($filesMap, fn($a, $b) => filemtime($b) <=> filemtime($a));

        $backups = [];
        foreach ($filesMap as $name => $file) {
            $backups[] = [
                'filename'       => $name,
                'type'           => str_starts_with($name, 'auto_backup_') ? 'auto' : 'manual',
                'size'           => filesize($file),
                'size_formatted' => $this->formatSize(filesize($file)),
                'created_at'     => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        return $backups;
    }

    /**
     * Delete a backup file (manual or auto).
     */
    public function deleteBackup(string $filename): bool
    {
        $filepath = $this->resolveBackupPath($filename);
        return $filepath !== null && @unlink($filepath);
    }

    /**
     * Download a backup file (sends headers and streams content).
     */
    public function downloadBackup(string $filename): void
    {
        $filepath = $this->requireBackupPath($filename);

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($filepath);
        exit;
    }

    /**
     * Keep only the N most recent manual backups (backup_*.zip).
     */
    private function enforceBackupLimit(int $maxBackups): void
    {
        $this->enforceBackupLimitForPrefix('backup_', $maxBackups);
    }

    /**
     * Keep only the N most recent backups matching a given filename prefix.
     */
    private function enforceBackupLimitForPrefix(string $prefix, int $maxBackups): void
    {
        $files = glob($this->backupDir . '/' . $prefix . '*.zip');
        if ($files === false || count($files) <= $maxBackups) {
            return;
        }

        usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a)); // newest first
        $toDelete = array_slice($files, $maxBackups);

        foreach ($toDelete as $file) {
            @unlink($file);
        }
    }

    /**
     * Calculate the next run timestamp respecting a preferred hour of day.
     * Returns the first occurrence of $preferredHour (0-23) that is at least
     * $intervalSeconds from now. If $preferredHour is null, returns now + interval.
     */
    public static function calcNextRunWithPreferredHour(int $intervalSeconds, ?int $preferredHour): string
    {
        $earliest = time() + $intervalSeconds;
        if ($preferredHour === null) {
            return date('Y-m-d H:i:s', $earliest);
        }
        $candidate = mktime(
            $preferredHour, 0, 0,
            (int)date('n', $earliest),
            (int)date('j', $earliest),
            (int)date('Y', $earliest)
        );
        if ($candidate < $earliest) {
            $candidate = mktime(
                $preferredHour, 0, 0,
                (int)date('n', $earliest),
                (int)date('j', $earliest) + 1,
                (int)date('Y', $earliest)
            );
        }
        return date('Y-m-d H:i:s', $candidate);
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $size = (float)$bytes;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    private function ensureDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
    }

    private function ensureProtectionFile(string $dir): void
    {
        $htaccess = rtrim($dir, '/') . '/.htaccess';
        $content = "Require all denied\nOptions -Indexes\n";
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, $content);
        }
    }

    private function isDangerousExtension(string $path): bool
    {
        $ext = strtolower((string)pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === '') {
            return false;
        }
        $blocked = [
            'php','phar','phtml','php3','php4','php5','phps',
            'cgi','pl','py','rb','jsp','asp','aspx',
            'exe','dll','so','bat','cmd','com','scr','ps1','sh','bash','fish','zsh',
            'js','mjs','ts'
        ];
        return in_array($ext, $blocked, true);
    }

    private function isBlockedUploadPath(string $path): bool
    {
        $blocked = ['.htaccess', '.user.ini'];
        $segments = explode('/', rtrim($path, '/'));

        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }

            if ($segment[0] === '.') {
                return true;
            }

            if (in_array(strtolower($segment), $blocked, true)) {
                return true;
            }
        }

        return false;
    }

    private function resolveBackupPath(string $filename): ?string
    {
        $base = basename($filename);
        if (!$this->isAllowedBackupName($base)) {
            return null;
        }

        $candidate = $this->backupDir . '/' . $base;
        if (is_file($candidate)) {
            return $candidate;
        }

        $legacy = $this->legacyBackupDir . '/' . $base;
        return is_file($legacy) ? $legacy : null;
    }

    private function requireBackupPath(string $filename): string
    {
        $path = $this->resolveBackupPath($filename);
        if ($path === null) {
            throw new RuntimeException('Plik backupu nie istnieje lub nazwa jest niedozwolona');
        }
        return $path;
    }

    private function isAllowedBackupName(string $base): bool
    {
        $allowedPrefix = str_starts_with($base, 'backup_') || str_starts_with($base, 'auto_backup_');
        return $allowedPrefix && (bool)preg_match('/^[A-Za-z0-9_.-]+$/', $base);
    }
}
