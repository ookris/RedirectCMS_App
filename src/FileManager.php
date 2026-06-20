<?php

/**
 * File Manager - zarządzanie plikami w katalogu uploads lub storage
 */
class FileManager
{
    private $pdo;
    private string $uploadsRoot;
    private string $rootDir;
    private ?array $referencedPathsCache = null;
    private const ALLOWED_ROOTS = ['uploads', 'storage'];

    public function __construct($pdo, string $rootDir = 'uploads')
    {
        if (!in_array($rootDir, self::ALLOWED_ROOTS, true)) {
            throw new InvalidArgumentException("Niedozwolony katalog: {$rootDir}");
        }

        $this->pdo = $pdo;
        $this->rootDir = $rootDir;
        $basePath = __DIR__ . '/../' . $rootDir;
        $realPath = realpath($basePath);

        $this->uploadsRoot = $realPath !== false ? $realPath : $basePath;

        if (!is_dir($this->uploadsRoot)) {
            @mkdir($this->uploadsRoot, 0755, true);
            $realPath = realpath($basePath);
            if ($realPath !== false) {
                $this->uploadsRoot = $realPath;
            }
        }
    }

    /**
     * Pobiera listę plików i folderów z podanej ścieżki
     */
    public function listFiles(string $relativePath = ''): array
    {
        $safePath = $this->sanitizePath($relativePath);
        $fullPath = $this->uploadsRoot . '/' . $safePath;

        if (!is_dir($fullPath) || !$this->isPathSafe($fullPath)) {
            return [];
        }

        $items = [];
        $iterator = new DirectoryIterator($fullPath);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            // Pomijaj pliki ukryte (zaczynające się od kropki)
            $filename = $fileInfo->getFilename();
            if (str_starts_with($filename, '.')) {
                continue;
            }

            $relativeName = ($safePath ? $safePath . '/' : '') . $filename;

            $items[] = [
                'name' => $filename,
                'path' => $relativeName,
                'type' => $fileInfo->isDir() ? 'directory' : 'file',
                'size' => $fileInfo->isFile() ? $fileInfo->getSize() : null,
                'modified' => $fileInfo->getMTime(),
                'extension' => $fileInfo->isFile() ? strtolower($fileInfo->getExtension()) : null,
            ];
        }

        // Sortuj: foldery pierwsze, potem alfabetycznie
        usort($items, function ($a, $b) {
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'directory' ? -1 : 1;
            }
            return strcasecmp($a['name'], $b['name']);
        });

        return $items;
    }

    /**
     * Sprawdza czy plik jest referencjonowany w bazie danych
     */
    public function isFileReferenced(string $relativePath): bool
    {
        if ($this->rootDir !== 'uploads') return false;

        if ($this->referencedPathsCache === null) {
            $this->referencedPathsCache = $this->collectReferencedPaths();
        }

        $normalizedPath = 'uploads/' . ltrim($relativePath, '/');
        return in_array($normalizedPath, $this->referencedPathsCache, true);
    }

    /**
     * Zwraca informacje gdzie plik jest używany
     */
    public function getFileUsage(string $relativePath): array
    {
        if ($this->rootDir !== 'uploads') return [];

        $normalizedPath = 'uploads/' . ltrim($relativePath, '/');
        $usage = [];

        // Sprawdź w linkach
        $stmt = $this->pdo->prepare('SELECT id, slug FROM links WHERE og_image = :path OR og_image_thumb = :path LIMIT 5');
        $stmt->execute([':path' => $normalizedPath]);
        $links = $stmt->fetchAll();
        if ($links) {
            $usage['links'] = $links;
        }

        // Sprawdź w galeriach
        $stmt = $this->pdo->prepare('SELECT link_id FROM link_images WHERE path = :path OR thumb_path = :path LIMIT 5');
        $stmt->execute([':path' => $normalizedPath]);
        $galleries = $stmt->fetchAll();
        if ($galleries) {
            $usage['galleries'] = $galleries;
        }

        // Sprawdź w kategoriach (tylko categories ma kolumny icon_image)
        $stmt = $this->pdo->prepare('SELECT id, name FROM categories WHERE icon_image = :path OR icon_image_thumb = :path LIMIT 5');
        $stmt->execute([':path' => $normalizedPath]);
        $categories = $stmt->fetchAll();
        if ($categories) {
            $usage['categories'] = $categories;
        }

        // Sprawdź w brandingu
        $stmt = $this->pdo->prepare('SELECT `key`, value FROM settings WHERE `key` IN ("branding_logo", "branding_favicon") AND value = :path');
        $stmt->execute([':path' => $normalizedPath]);
        $branding = $stmt->fetchAll();
        if ($branding) {
            $usage['branding'] = $branding;
        }

        return $usage;
    }

    /**
     * Usuwa plik (tylko jeśli nie jest referencjonowany)
     */
    public function deleteFile(string $relativePath): bool
    {
        $safePath = $this->sanitizePath($relativePath);
        $fullPath = $this->uploadsRoot . '/' . $safePath;

        if (!$this->isPathSafe($fullPath) || !is_file($fullPath)) {
            return false;
        }

        // Nie usuwaj plików, które są w bazie
        if ($this->isFileReferenced($safePath)) {
            return false;
        }

        return @unlink($fullPath);
    }

    /**
     * Pobiera statystyki dysku
     */
    public function getDiskStats(): array
    {
        $totalSize = 0;
        $fileCount = 0;
        $dirCount = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->uploadsRoot, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $totalSize += $fileInfo->getSize();
                $fileCount++;
            } elseif ($fileInfo->isDir()) {
                $dirCount++;
            }
        }

        return [
            'total_size' => $totalSize,
            'file_count' => $fileCount,
            'dir_count' => $dirCount,
        ];
    }

    /**
     * Zbiera wszystkie ścieżki referencjonowane w bazie
     */
    private function collectReferencedPaths(): array
    {
        $paths = [];

        // Links
        $stmt = $this->pdo->query('SELECT og_image, og_image_thumb FROM links');
        foreach ($stmt->fetchAll() as $row) {
            if ($row['og_image']) $paths[] = $row['og_image'];
            if ($row['og_image_thumb']) $paths[] = $row['og_image_thumb'];
        }

        // Galerie
        $stmt = $this->pdo->query('SELECT path, thumb_path FROM link_images');
        foreach ($stmt->fetchAll() as $row) {
            if ($row['path']) $paths[] = $row['path'];
            if ($row['thumb_path']) $paths[] = $row['thumb_path'];
        }

        // Kategorie (tylko categories ma kolumny icon_image)
        $stmt = $this->pdo->query('SELECT icon_image, icon_image_thumb FROM categories');
        foreach ($stmt->fetchAll() as $row) {
            if ($row['icon_image']) $paths[] = $row['icon_image'];
            if ($row['icon_image_thumb']) $paths[] = $row['icon_image_thumb'];
        }

        // Branding
        $stmt = $this->pdo->prepare('SELECT value FROM settings WHERE `key` IN ("branding_logo", "branding_favicon")');
        $stmt->execute();
        foreach ($stmt->fetchAll() as $row) {
            if ($row['value']) $paths[] = $row['value'];
        }

        return array_unique($paths);
    }

    /**
     * Sanityzuje ścieżkę (usuwa .. i inne niebezpieczne elementy)
     */
    private function sanitizePath(string $path): string
    {
        $path = str_replace(['..', '\\'], ['', '/'], $path);
        $path = trim($path, '/');
        return $path;
    }

    /**
     * Sprawdza czy ścieżka jest bezpieczna (czy jest w uploads/)
     */
    private function isPathSafe(string $fullPath): bool
    {
        $realPath = realpath($fullPath);
        if ($realPath === false) {
            return false;
        }
        return strpos($realPath, $this->uploadsRoot) === 0;
    }

    /**
     * Formatuje rozmiar pliku
     */
    public static function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return number_format($bytes / pow(1024, $power), 2, '.', ' ') . ' ' . $units[$power];
    }
}
