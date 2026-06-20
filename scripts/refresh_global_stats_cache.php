#!/usr/bin/env php
<?php
/**
 * Skrypt do automatycznego odświeżania cache statystyk globalnych
 * 
 * Dodaj do crona:
 * */30 * * * * /usr/bin/php /ścieżka/do/projektu/scripts/refresh_global_stats_cache.php >> /ścieżka/do/projektu/storage/logs/stats_cache.log 2>&1
 * 
 * Lub co pół godziny:
 * 0,30 * * * * /usr/bin/php /ścieżka/do/projektu/scripts/refresh_global_stats_cache.php >> /ścieżka/do/projektu/storage/logs/stats_cache.log 2>&1
 */

declare(strict_types=1);

// Sprawdź czy skrypt jest uruchomiony z CLI
if (php_sapi_name() !== 'cli') {
    exit('Ten skrypt może być uruchomiony tylko z wiersza poleceń.' . PHP_EOL);
}

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/StatsRepository.php';

// Załaduj konfigurację
$configPath = __DIR__ . '/../config/config.php';
$config = require $configPath;

try {
    $db = new Database($config);
    $pdo = $db->pdo();
    $statsRepo = new StatsRepository($pdo);
    
    echo '[' . date('Y-m-d H:i:s') . '] Rozpoczęto odświeżanie cache statystyk globalnych...' . PHP_EOL;
    
    // Zdefiniuj kombinacje do przeliczenia
    $combinations = [
        ['days' => 7, 'excludeBots' => false],
        ['days' => 7, 'excludeBots' => true],
        ['days' => 30, 'excludeBots' => false],
        ['days' => 30, 'excludeBots' => true],
        ['days' => 90, 'excludeBots' => false],
        ['days' => 90, 'excludeBots' => true],
    ];
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($combinations as $combo) {
        $days = $combo['days'];
        $excludeBots = $combo['excludeBots'];
        $label = "{$days} dni" . ($excludeBots ? ' (bez botów)' : '');
        
        try {
            $startTime = microtime(true);
            $statsRepo->refreshGlobalStatsCache($days, $excludeBots);
            $duration = round(microtime(true) - $startTime, 2);
            
            echo '[' . date('Y-m-d H:i:s') . "] ✓ Odświeżono: {$label} ({$duration}s)" . PHP_EOL;
            $successCount++;
        } catch (Throwable $e) {
            echo '[' . date('Y-m-d H:i:s') . "] ✗ Błąd dla {$label}: {$e->getMessage()}" . PHP_EOL;
            $errorCount++;
        }
    }
    
    echo '[' . date('Y-m-d H:i:s') . "] Zakończono. Sukces: {$successCount}, Błędy: {$errorCount}" . PHP_EOL;
    echo str_repeat('-', 80) . PHP_EOL;
    
    exit($errorCount > 0 ? 1 : 0);
    
} catch (Throwable $e) {
    echo '[' . date('Y-m-d H:i:s') . '] BŁĄD KRYTYCZNY: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
