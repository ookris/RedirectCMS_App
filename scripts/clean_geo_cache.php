#!/usr/bin/env php
<?php
declare(strict_types=1);

// CLI: Clean geo_cache entries older than N days or clear all
// Usage:
//   php scripts/clean_geo_cache.php [days]
//   php scripts/clean_geo_cache.php --clear-all

// Tylko CLI — ten skrypt czyści dane bez uwierzytelniania, więc nie może być
// wywoływany przez HTTP (patrz refresh_global_stats_cache.php dla tego samego wzorca).
if (php_sapi_name() !== 'cli') {
    exit('Ten skrypt może być uruchomiony tylko z wiersza poleceń.' . PHP_EOL);
}

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/StatsRepository.php';
require_once __DIR__ . '/../src/Utils.php';

// Load config
$configPath = __DIR__ . '/../config/config.php';
$config = require $configPath;

// Connect DB
try {
    $db = new Database($config);
    $pdo = $db->pdo();
} catch (Throwable $e) {
    fwrite(STDERR, "DB connection error: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

$repo = new StatsRepository($pdo);

$arg = $argv[1] ?? null;
if ($arg === '--clear-all') {
    $deleted = $repo->clearAllGeoCache();
    $msg = sprintf('[%s] CLI | clear_all | deleted=%d', date('Y-m-d H:i:s'), $deleted);
    echo $msg . PHP_EOL;
    Utils::appendLog(__DIR__ . '/../storage/logs/geo_cache.log', $msg);
    exit(0);
}

$days = $arg !== null ? max(30, (int)$arg) : 60; // default 60, min 30
$deleted = $repo->cleanOldGeoCache($days);
$msg = sprintf('[%s] CLI | clean_old days=%d | deleted=%d', date('Y-m-d H:i:s'), $days, $deleted);
echo $msg . PHP_EOL;
Utils::appendLog(__DIR__ . '/../storage/logs/geo_cache.log', $msg);
