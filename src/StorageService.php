<?php

/**
 * Storage Service - centralized directory size calculation with database caching
 */
class StorageService
{
    private string $projectRoot;
    private string $uploadsPath;
    private string $logsPath;
    private string $cachePath;
    private string $backupsPath;
    private ?PrefixedPDO $pdo;

    public function __construct(?PrefixedPDO $pdo = null)
    {
        $this->projectRoot = __DIR__ . '/..';
        $this->uploadsPath = $this->projectRoot . '/uploads';
        $this->logsPath = $this->projectRoot . '/storage/logs';
        $this->cachePath = $this->projectRoot . '/storage/cache';
        $this->backupsPath = $this->projectRoot . '/storage/private_backups';
        $this->pdo = $pdo;

        // Ensure directories exist
        $this->ensureDirectoryExists($this->uploadsPath, 0755);
        $this->ensureDirectoryExists($this->logsPath);
        $this->ensureDirectoryExists($this->cachePath);
        $this->ensureDirectoryExists($this->backupsPath, 0750);
    }

    /**
     * Get statistics for a specific directory
     */
    public function getDirectoryStats(string $path): array
    {
        if (!is_dir($path)) {
            return [
                'total_size' => 0,
                'file_count' => 0,
                'dir_count' => 0,
                'size_formatted' => '0 B',
            ];
        }

        $totalSize = 0;
        $fileCount = 0;
        $dirCount = 0;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $totalSize += $fileInfo->getSize();
                    $fileCount++;
                } elseif ($fileInfo->isDir()) {
                    $dirCount++;
                }
            }
        } catch (Exception $e) {
            // Return empty stats on error
            return [
                'total_size' => 0,
                'file_count' => 0,
                'dir_count' => 0,
                'size_formatted' => '0 B',
            ];
        }

        return [
            'total_size' => $totalSize,
            'file_count' => $fileCount,
            'dir_count' => $dirCount,
            'size_formatted' => $this->formatSize($totalSize),
        ];
    }

    /**
     * Get statistics for all storage directories (uses database cache if available)
     */
    public function getAllStorageStats(): array
    {
        // Try to get from database cache first
        if ($this->pdo !== null) {
            $cachedStats = $this->getStatsFromDatabase();
            if ($cachedStats !== null) {
                return $cachedStats;
            }
        }

        // Fallback to live calculation if no cache or no database connection
        return $this->calculateStatsLive();
    }

    /**
     * Calculate storage statistics live (without cache)
     */
    private function calculateStatsLive(): array
    {
        $uploads = $this->getDirectoryStats($this->uploadsPath);
        $logs = $this->getDirectoryStats($this->logsPath);
        $cache = $this->getDirectoryStats($this->cachePath);
        $backups = $this->getDirectoryStats($this->backupsPath);

        $totalSize = $uploads['total_size'] + $logs['total_size'] + $cache['total_size'] + $backups['total_size'];

        return [
            'uploads' => $uploads,
            'logs' => $logs,
            'cache' => $cache,
            'backups' => $backups,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatSize($totalSize),
            'last_calculated' => time(),
        ];
    }

    /**
     * Recalculate and store directory sizes in database
     */
    public function recalculateAndStore(): array
    {
        if ($this->pdo === null) {
            throw new Exception('Database connection required for recalculation');
        }

        // Calculate fresh statistics
        $stats = $this->calculateStatsLive();

        // Store sizes in database
        $this->saveValueToDatabase('storage_uploads_size', $stats['uploads']['total_size']);
        $this->saveValueToDatabase('storage_logs_size', $stats['logs']['total_size']);
        $this->saveValueToDatabase('storage_cache_size', $stats['cache']['total_size']);
        $this->saveValueToDatabase('storage_backups_size', $stats['backups']['total_size']);

        // Store file counts in database (avoids directory walking on each request)
        $this->saveValueToDatabase('storage_uploads_files', $stats['uploads']['file_count']);
        $this->saveValueToDatabase('storage_logs_files', $stats['logs']['file_count']);
        $this->saveValueToDatabase('storage_cache_files', $stats['cache']['file_count']);
        $this->saveValueToDatabase('storage_backups_files', $stats['backups']['file_count']);

        $this->saveValueToDatabase('storage_last_calculated', (string)time());

        return $stats;
    }

    /**
     * Get statistics from database cache
     */
    private function getStatsFromDatabase(): ?array
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT `key`, value
                FROM settings
                WHERE `key` IN (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                'storage_uploads_size',
                'storage_logs_size',
                'storage_cache_size',
                'storage_backups_size',
                'storage_uploads_files',
                'storage_logs_files',
                'storage_cache_files',
                'storage_backups_files',
                'storage_last_calculated'
            ]);

            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['key']] = $row['value'];
            }

            // Check if we have all required values (sizes are required, counts are optional for backwards compatibility)
            if (!isset($settings['storage_uploads_size']) ||
                !isset($settings['storage_logs_size']) ||
                !isset($settings['storage_cache_size'])) {
                return null;
            }

            $uploadsSize = (int)$settings['storage_uploads_size'];
            $logsSize = (int)$settings['storage_logs_size'];
            $cacheSize = (int)$settings['storage_cache_size'];
            $backupsSize = isset($settings['storage_backups_size']) ? (int)$settings['storage_backups_size'] : 0;
            $totalSize = $uploadsSize + $logsSize + $cacheSize + $backupsSize;

            // Use cached file counts if available, otherwise show 0 (will be updated on next recalculation)
            $uploadsFiles = isset($settings['storage_uploads_files']) ? (int)$settings['storage_uploads_files'] : 0;
            $logsFiles = isset($settings['storage_logs_files']) ? (int)$settings['storage_logs_files'] : 0;
            $cacheFiles = isset($settings['storage_cache_files']) ? (int)$settings['storage_cache_files'] : 0;
            $backupsFiles = isset($settings['storage_backups_files']) ? (int)$settings['storage_backups_files'] : 0;

            return [
                'uploads' => [
                    'total_size' => $uploadsSize,
                    'size_formatted' => $this->formatSize($uploadsSize),
                    'file_count' => $uploadsFiles,
                    'dir_count' => 0, // Not cached, rarely needed
                ],
                'logs' => [
                    'total_size' => $logsSize,
                    'size_formatted' => $this->formatSize($logsSize),
                    'file_count' => $logsFiles,
                    'dir_count' => 0,
                ],
                'cache' => [
                    'total_size' => $cacheSize,
                    'size_formatted' => $this->formatSize($cacheSize),
                    'file_count' => $cacheFiles,
                    'dir_count' => 0,
                ],
                'backups' => [
                    'total_size' => $backupsSize,
                    'size_formatted' => $this->formatSize($backupsSize),
                    'file_count' => $backupsFiles,
                    'dir_count' => 0,
                ],
                'total_size' => $totalSize,
                'total_size_formatted' => $this->formatSize($totalSize),
                'last_calculated' => isset($settings['storage_last_calculated'])
                    ? (int)$settings['storage_last_calculated']
                    : null,
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Save a single size value to database
     */
    private function saveValueToDatabase(string $key, string $value): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO settings (`key`, value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE value = VALUES(value)
        ');
        $stmt->execute([$key, $value]);
    }

    /**
     * Get subdirectories with their sizes for a given directory
     */
    public function getSubdirectoryStats(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $subdirs = [];
        $iterator = new DirectoryIterator($path);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isDir()) {
                continue;
            }

            // Skip hidden directories
            $dirname = $fileInfo->getFilename();
            if (str_starts_with($dirname, '.')) {
                continue;
            }

            $subdirPath = $fileInfo->getPathname();
            $stats = $this->getDirectoryStats($subdirPath);

            $subdirs[] = [
                'name' => $dirname,
                'path' => $subdirPath,
                'file_count' => $stats['file_count'],
                'total_size' => $stats['total_size'],
                'size_formatted' => $stats['size_formatted'],
            ];
        }

        // Sort by size descending
        usort($subdirs, function ($a, $b) {
            return $b['total_size'] <=> $a['total_size'];
        });

        return $subdirs;
    }

    /**
     * Get uploads directory path
     */
    public function getUploadsPath(): string
    {
        return $this->uploadsPath;
    }

    /**
     * Get logs directory path
     */
    public function getLogsPath(): string
    {
        return $this->logsPath;
    }

    /**
     * Get cache directory path
     */
    public function getCachePath(): string
    {
        return $this->cachePath;
    }

    /**
     * Get backups directory path
     */
    public function getBackupsPath(): string
    {
        return $this->backupsPath;
    }

    /**
     * Format file size to human-readable format
     */
    public function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);
        return number_format($bytes / pow(1024, $power), 2, '.', ' ') . ' ' . $units[$power];
    }

    /**
     * Ensure directory exists, create if it doesn't
     */
    private function ensureDirectoryExists(string $path, int $mode = 0750): void
    {
        if (!is_dir($path)) {
            @mkdir($path, $mode, true);
        }
    }
}
