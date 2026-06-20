<?php
declare(strict_types=1);

require_once __DIR__ . '/../Utils.php';
require_once __DIR__ . '/../ImageUploader.php';
require_once __DIR__ . '/../NotificationRepository.php';
require_once __DIR__ . '/../QRCodeGenerator.php';
require_once __DIR__ . '/../LinkRepository.php';
require_once __DIR__ . '/../CustomFieldService.php';
require_once __DIR__ . '/../VersionChecker.php';
require_once __DIR__ . '/../LicenseChecker.php';
require_once __DIR__ . '/../SettingsRepository.php';

class BaseController
{
    private ?int   $_unreadNotificationsCount = null;
    private ?array $_licenseStatus            = null;

    public function __construct(
        protected $pdo,
        protected array $config,
        protected string $basePath = '/'
    ) {}

    protected function view(string $template, array $vars = []): void
    {
        $basePath            = $this->basePath;
        $unreadNotifications = $this->getUnreadNotificationsCount();
        $licenseStatus       = $this->getLicenseStatus();
        extract($vars, EXTR_SKIP);
        include __DIR__ . '/../../admin/' . $template . '.php';
    }

    protected function getLicenseStatus(): array
    {
        if ($this->_licenseStatus === null) {
            try {
                $this->_licenseStatus = LicenseChecker::getStatus($this->pdo);
            } catch (\Throwable $e) {
                error_log('[BaseController] LicenseChecker error: ' . $e->getMessage());
                $this->_licenseStatus = ['state' => 'api_unreachable', 'message' => '', 'since' => null];
            }
        }
        return $this->_licenseStatus;
    }

    protected function isLicenseBlocked(): bool
    {
        return $this->getLicenseStatus()['state'] === 'blocked';
    }

    protected function getUnreadNotificationsCount(): int
    {
        if ($this->_unreadNotificationsCount === null) {
            try {
                $repo = new NotificationRepository($this->pdo);
                $this->_unreadNotificationsCount = $repo->countUnread();
            } catch (\Throwable $e) {
                $this->_unreadNotificationsCount = 0;
            }
        }
        return $this->_unreadNotificationsCount;
    }

    /**
     * Parsuje standardowe parametry listowania (sortowanie, wyszukiwanie, filtry) z $_GET.
     * Używane przez LinkController i StatsController.
     *
     * @return array{sortBy: string, sortOrder: string, searchQuery: string, categoryFilter: int|null, programFilter: int|null}
     */
    protected function parseListingParams(): array
    {
        return [
            'sortBy'         => trim((string)($_GET['sort'] ?? 'created_at')),
            'sortOrder'      => trim((string)($_GET['order'] ?? 'DESC')),
            'searchQuery'    => trim((string)($_GET['search'] ?? '')),
            'categoryFilter' => !empty($_GET['category_filter']) ? (int)$_GET['category_filter'] : null,
            'programFilter'  => !empty($_GET['program_filter'])  ? (int)$_GET['program_filter']  : null,
        ];
    }

    protected function normalizeDateTime(?string $value): ?string
    {
        $trimmed = trim((string)$value);
        if ($trimmed === '') {
            return null;
        }
        try {
            $dt = new \DateTimeImmutable($trimmed);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('Nieprawidłowa data lub godzina');
        }
    }

    protected function collectGalleryFiles(?array $input): array
    {
        if (empty($input) || !is_array($input) || !isset($input['name']) || !is_array($input['name'])) {
            return [];
        }

        $files = [];
        $count = count($input['name']);
        for ($i = 0; $i < $count; $i++) {
            $error = $input['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($error !== UPLOAD_ERR_OK) {
                throw new \InvalidArgumentException($this->describeUploadError($error));
            }

            $files[] = [
                'name' => $input['name'][$i] ?? '',
                'type' => $input['type'][$i] ?? '',
                'tmp_name' => $input['tmp_name'][$i] ?? '',
                'error' => $error,
                'size' => $input['size'][$i] ?? 0,
            ];
        }

        return $files;
    }

    protected function describeUploadError(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Wybrane zdjęcie przekracza dopuszczalny rozmiar.',
            UPLOAD_ERR_PARTIAL => 'Przesyłanie zdjęcia nie zostało ukończone. Spróbuj ponownie.',
            UPLOAD_ERR_NO_TMP_DIR => 'Brak katalogu tymczasowego na serwerze. Skontaktuj się z administratorem.',
            UPLOAD_ERR_CANT_WRITE => 'Nie udało się zapisać zdjęcia na dysku.',
            UPLOAD_ERR_EXTENSION => 'Przesyłanie zdjęcia zostało zablokowane przez rozszerzenie PHP.',
            default => 'Nie udało się przesłać zdjęcia (kod błędu: ' . $error . ').',
        };
    }

    protected function cleanupUploadedPaths(array $paths): void
    {
        if (empty($paths)) {
            return;
        }
        $uploader = new ImageUploader(__DIR__ . '/../../uploads');
        foreach ($paths as $path) {
            if (is_array($path)) {
                $main = $path['path'] ?? null;
                $thumb = $path['thumb_path'] ?? null;
                if (!empty($main)) {
                    $uploader->delete((string)$main);
                }
                if (!empty($thumb)) {
                    $uploader->delete((string)$thumb);
                }
                continue;
            }
            $uploader->delete((string)$path);
        }
    }

    protected function parsePreuploadedImages(array $raw): array
    {
        $result = [];

        foreach ($raw as $item) {
            $decoded = null;
            if (is_string($item)) {
                $decoded = json_decode($item, true);
            } elseif (is_array($item)) {
                $decoded = $item;
            }

            if (!is_array($decoded)) {
                continue;
            }

            $path = isset($decoded['path']) ? ltrim((string)$decoded['path'], '/') : '';
            if ($path === '' || strpos($path, 'uploads/') !== 0 || preg_match('/\.\.|[\x00\\\\]/', $path)) {
                continue;
            }

            $thumb = isset($decoded['thumb_path']) ? ltrim((string)$decoded['thumb_path'], '/') : '';
            if ($thumb !== '' && preg_match('/\.\.|[\x00\\\\]/', $thumb)) {
                continue;
            }
            $key = isset($decoded['key']) ? (string)$decoded['key'] : null;

            $result[] = [
                'path' => $path,
                'thumb_path' => $thumb !== '' ? $thumb : null,
                'key' => $key,
            ];
        }

        return $result;
    }

    protected function isUploadPathAllowed(string $path): bool
    {
        Utils::startSession();
        $whitelist = $_SESSION['upload_whitelist'] ?? [];
        if (is_array($whitelist) && in_array($path, $whitelist, true)) {
            return true;
        }

        $stmt = $this->pdo->prepare('SELECT 1 FROM link_images WHERE path = :p OR thumb_path = :p LIMIT 1');
        $stmt->execute([':p' => $path]);
        if ($stmt->fetchColumn()) {
            return true;
        }

        $stmt = $this->pdo->prepare('SELECT 1 FROM links WHERE og_image = :p OR og_image_thumb = :p LIMIT 1');
        $stmt->execute([':p' => $path]);
        return (bool)$stmt->fetchColumn();
    }

    protected function extractCustomFieldValues(array $definitions, array $submitted): array
    {
        $values = [];

        foreach ($definitions as $definition) {
            $key = (string)($definition['key'] ?? '');
            if ($key === '' || !array_key_exists($key, $submitted)) {
                continue;
            }

            $rawValue = trim((string)$submitted[$key]);
            if ($rawValue === '') {
                continue;
            }

            $type = (string)($definition['type'] ?? 'text');
            if ($type === 'number' && !is_numeric($rawValue)) {
                throw new \InvalidArgumentException('Pole "' . ($definition['label'] ?? $key) . '" musi być liczbą.');
            }

            $values[$key] = $rawValue;
        }

        return $values;
    }

    public function uploadImageAjax(): void
    {
        Utils::requireLogin();
        header('Content-Type: application/json');

        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Błędny CSRF token']);
            return;
        }

        if (!isset($_FILES['image'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nie przesłano pliku']);
            return;
        }

        if (
            !isset($_FILES['image']['tmp_name']) ||
            $_FILES['image']['tmp_name'] === '' ||
            !is_uploaded_file($_FILES['image']['tmp_name'])
        ) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nieprawidłowy plik uploadu']);
            return;
        }

        try {
            $uploader = new ImageUploader(__DIR__ . '/../../uploads');
            $result = $uploader->uploadWithThumbnail($_FILES['image']);

            $path = ltrim((string)($result['path'] ?? ''), '/');
            $thumbPath = isset($result['thumb_path']) ? ltrim((string)$result['thumb_path'], '/') : '';

            if ($path === '') {
                throw new \InvalidArgumentException('Nie udało się zapisać pliku');
            }

            Utils::startSession();
            if (!isset($_SESSION['upload_whitelist']) || !is_array($_SESSION['upload_whitelist'])) {
                $_SESSION['upload_whitelist'] = [];
            }
            $_SESSION['upload_whitelist'][] = $path;
            if ($thumbPath !== '') {
                $_SESSION['upload_whitelist'][] = $thumbPath;
            }
            $_SESSION['upload_whitelist'] = array_values(array_unique($_SESSION['upload_whitelist']));

            echo json_encode([
                'success' => true,
                'path' => $path,
                'thumb_path' => $thumbPath !== '' ? $thumbPath : null,
                'url' => $this->basePath . '/' . $path,
                'thumb_url' => $thumbPath !== '' ? $this->basePath . '/' . $thumbPath : null,
            ]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function deleteUploadedImageAjax(): void
    {
        Utils::requireLogin();
        header('Content-Type: application/json');

        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Błędny CSRF token']);
            return;
        }

        $path = isset($_POST['path']) ? ltrim((string)$_POST['path'], '/') : '';
        $thumbPath = isset($_POST['thumb_path']) ? ltrim((string)$_POST['thumb_path'], '/') : '';

        if ($path === '' || strpos($path, 'uploads/') !== 0 || preg_match('/\.\.|[\x00\\\\]/', $path)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nieprawidłowa ścieżka']);
            return;
        }

        $uploadsRoot = realpath(__DIR__ . '/../../uploads');
        if ($uploadsRoot === false || !is_dir($uploadsRoot)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Brak katalogu uploadów']);
            return;
        }

        $fullPath = realpath($uploadsRoot . '/' . substr($path, strlen('uploads/')));
        if ($fullPath === false || str_starts_with($fullPath, $uploadsRoot) === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Ścieżka spoza katalogu uploadów']);
            return;
        }

        if (!$this->isUploadPathAllowed($path)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Brak uprawnień do usunięcia pliku']);
            return;
        }

        $uploader = new ImageUploader(__DIR__ . '/../../uploads');
        $deletedMain = $uploader->delete($path);
        $deletedThumb = false;
        if (
            $thumbPath !== '' &&
            strpos($thumbPath, 'uploads/') === 0 &&
            !preg_match('/\.\.|[\x00\\\\]/', $thumbPath) &&
            $this->isUploadPathAllowed($thumbPath)
        ) {
            $deletedThumb = $uploader->delete($thumbPath);
        }

        echo json_encode([
            'success' => true,
            'deleted' => $deletedMain,
            'deleted_thumb' => $deletedThumb,
        ]);
    }

    public function generateQRCode(int $linkId, string $mode = 'display'): void
    {
        Utils::requireLogin();

        $linkRepo = new LinkRepository($this->pdo);
        $link = $linkRepo->getById($linkId);

        if (!$link) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Link nie istnieje']);
            return;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $fullUrl = $scheme . '://' . $host . ($this->basePath ? $this->basePath : '') . '/' . (string)$link['slug'];

        if ($mode === 'download') {
            $pngData = QRCodeGenerator::generatePNG($fullUrl);
            if (empty($pngData)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Nie udało się wygenerować QR code']);
                return;
            }

            $filename = 'qr-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $link['slug']) . '.png';
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($pngData));
            echo $pngData;
            return;
        }

        $qrFilePath = QRCodeGenerator::generateFile($fullUrl);
        header('Content-Type: application/json');
        if ($qrFilePath === null) {
            echo json_encode([
                'success' => false,
                'error' => 'Nie udało się wygenerować QR (brak danych)'
            ]);
            return;
        }

        $qrUrl = $this->basePath . '/' . $qrFilePath;

        echo json_encode([
            'success' => true,
            'qr_url' => $qrUrl,
            'slug' => htmlspecialchars($link['slug']),
            'url' => $fullUrl
        ]);
    }

    protected function colorizeLogLine(string $line): string
    {
        $line = preg_replace('/\[(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\]/', '<span class="log-timestamp">[$1]</span>', $line);
        $line = preg_replace('/(\s\|\s)/', '<span class="log-pipe">$1</span>', $line);
        $line = preg_replace('/\b(\d+)\b/', '<span class="log-number">$1</span>', $line);

        if (preg_match('/\b(error|failed|exception|fatal|failure)\b/i', $line)) {
            $line = preg_replace('/\b(error|failed|exception|fatal|failure)\b/i', '<span class="log-error">$1</span>', $line);
        } elseif (preg_match('/\b(warning|skipped|notice|deprecated)\b/i', $line)) {
            $line = preg_replace('/\b(warning|skipped|notice|deprecated)\b/i', '<span class="log-warning">$1</span>', $line);
        } elseif (preg_match('/\b(success|completed|sent|done|ok)\b/i', $line)) {
            $line = preg_replace('/\b(success|completed|sent|done|ok)\b/i', '<span class="log-success">$1</span>', $line);
        } elseif (preg_match('/\b(info|started|running|processing)\b/i', $line)) {
            $line = preg_replace('/\b(info|started|running|processing)\b/i', '<span class="log-info">$1</span>', $line);
        }

        return $line;
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    protected function getDirectorySize(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }

        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    protected function countFiles(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }

        return $count;
    }

    protected function convertToMb(float $value, string $unit): int
    {
        if ($value <= 0) {
            return 0;
        }

        return match (strtoupper($unit)) {
            'KB' => (int)round($value / 1024),
            'GB' => (int)round($value * 1024),
            default => (int)round($value),
        };
    }

    protected function deleteLinksWithImages(array $ids, LinkRepository $repo): void
    {
        $uploader = new ImageUploader(__DIR__ . '/../../uploads');
        foreach ($ids as $id) {
            $link = $repo->getById((int)$id);
            if (!$link) continue;

            if (!empty($link['og_image'])) {
                $uploader->delete($link['og_image']);
            }
            if (!empty($link['og_image_thumb'])) {
                $uploader->delete($link['og_image_thumb']);
            }

            $images = $repo->getImages((int)$id);
            foreach ($images as $img) {
                if (!empty($img['path'])) $uploader->delete($img['path']);
                if (!empty($img['thumb_path'])) $uploader->delete($img['thumb_path']);
            }
        }

        $repo->permanentDeleteBatch($ids);
    }
}
