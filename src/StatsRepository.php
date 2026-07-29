<?php
declare(strict_types=1);

class StatsRepository
{
    private PrefixedPDO $pdo;

    /** @var int Cache TTL dla statystyk globalnych (w sekundach) */
    private const GLOBAL_STATS_CACHE_TTL = 1800; // 30 minut

    public function __construct(PrefixedPDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Zapisz kliknięcie linku
     */
    public function recordClick(int $linkId, string $ipAddress, string $userAgent, ?string $referer = null): void
    {
        // Anonimizuj IP dla RODO/prywatności
        $ipAddress = Utils::anonymizeIp($ipAddress);
        
        $deviceType = $this->detectDeviceType($userAgent);
        
        // Pobierz geolokalizację (z cache lub API)
        $geo = $this->getGeoData($ipAddress);
        $countryCode = $geo['country_code'] ?? null;
        $city = $geo['city'] ?? null;
        
        $stmt = $this->pdo->prepare('
            INSERT INTO link_stats (link_id, ip_address, user_agent, referer, country_code, city, device_type)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $linkId,
            $ipAddress,
            $userAgent,
            $referer,
            $countryCode,
            $city,
            $deviceType
        ]);
    }

    /**
     * Pobierz dane geo (z cache w bazie lub API)
     */
    private function getGeoData(string $ip): ?array
    {
        // Sprawdź cache (TTL 30 dni)
        $stmt = $this->pdo->prepare('
            SELECT country_code, city, updated_at
            FROM geo_cache
            WHERE ip_address = ?
        ');
        $stmt->execute([$ip]);
        $cached = $stmt->fetch();
        
        if ($cached) {
            $age = time() - strtotime($cached['updated_at']);
            $ttl = 30 * 24 * 3600; // 30 dni
            
            if ($age < $ttl) {
                return [
                    'country_code' => $cached['country_code'],
                    'city' => $cached['city']
                ];
            }
        }
        
        // Cache miss lub wygasł - pobierz z API
        $geoData = Utils::getGeoLocation($ip);
        
        if ($geoData !== null) {
            // Zapisz/zaktualizuj cache
            $stmt = $this->pdo->prepare('
                INSERT INTO geo_cache (ip_address, country_code, city, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    country_code = VALUES(country_code),
                    city = VALUES(city),
                    updated_at = NOW()
            ');
            $stmt->execute([
                $ip,
                $geoData['country_code'],
                $geoData['city']
            ]);
        }
        
        return $geoData;
    }
    
    /**
     * Pobierz statystyki dla konkretnego linku
     */
    public function getLinkStats(int $linkId, int $days = 30, bool $excludeBots = false): array
    {
        return [
            'today' => $this->getClickCount($linkId, 'today', $excludeBots),
            'last_7_days' => $this->getClickCount($linkId, '7 days', $excludeBots),
            'last_30_days' => $this->getClickCount($linkId, '30 days', $excludeBots),
            'total' => $this->getClickCount($linkId, 'all', $excludeBots),
            'previous_today' => $this->getPreviousPeriodCount($linkId, 1, $excludeBots),
            'previous_7_days' => $this->getPreviousPeriodCount($linkId, 7, $excludeBots),
            'previous_30_days' => $this->getPreviousPeriodCount($linkId, 30, $excludeBots),
            'unique_today' => $this->getUniqueCount($linkId, 'today', $excludeBots),
            'unique_last_7_days' => $this->getUniqueCount($linkId, '7 days', $excludeBots),
            'unique_last_30_days' => $this->getUniqueCount($linkId, '30 days', $excludeBots),
            'unique_total' => $this->getUniqueCount($linkId, 'all', $excludeBots),
            'by_device' => $this->getClicksByDevice($linkId, $excludeBots),
            'by_referer' => $this->getTopReferers($linkId, 10, $excludeBots),
            'by_referer_domain' => $this->getReferersByDomain($linkId, $days, $excludeBots, 10),
            'by_browser' => $this->getClicksByBrowser($linkId, $days, $excludeBots),
            'by_country' => $this->getClicksByCountry($linkId, $days, $excludeBots, 10),
            'by_city' => $this->getClicksByCity($linkId, $days, $excludeBots, 10),
            'hourly_distribution' => $this->getHourlyDistribution($linkId, $days, $excludeBots),
            'daily_chart' => $this->getDailyChart($linkId, $days, $excludeBots),
            'heatmap' => $this->getHeatmapData($linkId, $days, $excludeBots),
        ];
    }
    
    /**
     * Pobierz liczbę kliknięć dla okresu
     */
    private function getClickCount(int $linkId, string $period, bool $excludeBots = false): int
    {
        $where = 'link_id = ?';
        $params = [$linkId];
        
        if ($period !== 'all') {
            match($period) {
                'today' => $where .= ' AND DATE(visited_at) = CURDATE()',
                '7 days' => $where .= ' AND visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
                '30 days' => $where .= ' AND visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
                default => null,
            };
        }
        if ($excludeBots) {
            $where .= " AND device_type <> 'bot'";
        }
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM link_stats WHERE $where");
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    }

    /**
     * Liczba kliknięć dla poprzedniego okresu (do porównania)
     */
    private function getPreviousPeriodCount(int $linkId, int $days, bool $excludeBots = false): int
    {
        // Dla okresu N dni, oblicz poprzednie N dni (od -2N do -N)
        $endDate = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d 23:59:59');
        $startDate = (new DateTimeImmutable('now'))
            ->modify('-' . ($days * 2) . ' days')
            ->format('Y-m-d 00:00:00');
        
        $where = 'link_id = ? AND visited_at >= ? AND visited_at <= ?';
        $params = [$linkId, $startDate, $endDate];
        
        if ($excludeBots) {
            $where .= " AND device_type <> 'bot'";
        }
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM link_stats WHERE $where");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Liczba unikalnych odwiedzających (po IP) dla okresu
     */
    private function getUniqueCount(int $linkId, string $period, bool $excludeBots = false): int
    {
        $where = 'link_id = ?';
        $params = [$linkId];
        if ($period !== 'all') {
            match($period) {
                'today' => $where .= ' AND DATE(visited_at) = CURDATE()',
                '7 days' => $where .= ' AND visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
                '30 days' => $where .= ' AND visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
                default => null,
            };
        }
        if ($excludeBots) {
            $where .= " AND device_type <> 'bot'";
        }
        $stmt = $this->pdo->prepare("SELECT COUNT(DISTINCT ip_address) FROM link_stats WHERE $where");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Pobierz statystyki według typu urządzenia
     */
    private function getClicksByDevice(int $linkId, bool $excludeBots = false): array
    {
        $sql = '
            SELECT device_type, COUNT(*) as count
            FROM link_stats
            WHERE link_id = ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY device_type
            ORDER BY count DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$linkId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Top domeny źródłowe (referery grupowane po hoście)
     */
    private function getReferersByDomain(int $linkId, int $days = 30, bool $excludeBots = false, int $limit = 10): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        
        $sql = '
            SELECT referer
            FROM link_stats
            WHERE link_id = ? AND visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$linkId, $from]);
        $rows = $stmt->fetchAll();

        $buckets = [];
        foreach ($rows as $row) {
            $ref = (string)$row['referer'];
            $domain = $this->extractDomain($ref);
            $buckets[$domain] = ($buckets[$domain] ?? 0) + 1;
        }
        arsort($buckets);
        
        $result = [];
        $count = 0;
        foreach ($buckets as $domain => $clicks) {
            if ($count >= $limit) break;
            $result[] = ['domain' => $domain, 'count' => $clicks];
            $count++;
        }
        return $result;
    }

    /**
     * Podział kliknięć według kraju
     */
    private function getClicksByCountry(int $linkId, int $days = 30, bool $excludeBots = false, int $limit = 10): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        $limit = max(1, (int)$limit);
        
        $sql = '
            SELECT 
                COALESCE(country_code, "UNKNOWN") as country,
                COUNT(*) as count
            FROM link_stats
            WHERE link_id = ? AND visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY country_code
            ORDER BY count DESC
            LIMIT ' . $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$linkId, $from]);
        
        return $stmt->fetchAll();
    }

    /**
     * Podział kliknięć według miasta
     */
    private function getClicksByCity(int $linkId, int $days = 30, bool $excludeBots = false, int $limit = 10): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        $limit = max(1, (int)$limit);
        
        $sql = '
            SELECT 
                COALESCE(city, "UNKNOWN") as city,
                country_code,
                COUNT(*) as count
            FROM link_stats
            WHERE link_id = ? AND visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY city, country_code
            ORDER BY count DESC
            LIMIT ' . $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$linkId, $from]);
        
        return $stmt->fetchAll();
    }

    /**
     * Podział kliknięć według przeglądarki (na bazie UA)
     */
    private function getClicksByBrowser(int $linkId, int $days = 30, bool $excludeBots = false): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        $sql = '
            SELECT user_agent
            FROM link_stats
            WHERE link_id = ? AND visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$linkId, $from]);
        $rows = $stmt->fetchAll();

        $buckets = [];
        foreach ($rows as $row) {
            $browser = $this->detectBrowser((string)$row['user_agent']);
            $buckets[$browser] = ($buckets[$browser] ?? 0) + 1;
        }
        arsort($buckets);
        $result = [];
        foreach ($buckets as $name => $count) {
            $result[] = ['browser' => $name, 'count' => $count];
        }
        return $result;
    }
    
    /**
     * Pobierz top źródła ruchu (referery)
     */
    private function getTopReferers(int $linkId, int $limit = 10, bool $excludeBots = false): array
    {
        // MySQL nie pozwala na bindowanie parametru w LIMIT w trybie bez emulacji
        $limit = max(1, (int)$limit);
        $sql = '
            SELECT 
                CASE 
                    WHEN referer IS NULL OR referer = "" THEN "Bezpośredni"
                    ELSE referer 
                END as referer,
                COUNT(*) as count
            FROM link_stats
            WHERE link_id = ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY referer
            ORDER BY count DESC
            LIMIT ' . $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$linkId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Pobierz rozkład kliknięć według godzin
     */
    private function getHourlyDistribution(int $linkId, int $days = 30, bool $excludeBots = false): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        $sql = '
            SELECT 
                HOUR(visited_at) as hour,
                COUNT(*) as count
            FROM link_stats
            WHERE link_id = ? AND visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY HOUR(visited_at)
            ORDER BY hour';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$linkId, $from]);
        
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int)$row['hour']] = (int)$row['count'];
        }
        
        // Wypełnij brakujące godziny zerami
        for ($i = 0; $i < 24; $i++) {
            if (!isset($result[$i])) {
                $result[$i] = 0;
            }
        }
        ksort($result);
        
        return $result;
    }
    
    /**
     * Pobierz dane dla wykresu dziennego (ostatnie N dni)
     */
    private function getDailyChart(int $linkId, int $days = 30, bool $excludeBots = false): array
    {
        // Nie bindować w INTERVAL – wylicz datę graniczną w PHP
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        $sql = '
            SELECT 
                DATE(visited_at) as date,
                COUNT(*) as count
            FROM link_stats
            WHERE link_id = ? AND visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY DATE(visited_at)
            ORDER BY date';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$linkId, $from]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Pobierz szybkie statystyki dla wszystkich linków (do wyświetlenia na liście)
     */
    public function getQuickStats(): array
    {
        $stmt = $this->pdo->query('
            SELECT 
                link_id,
                COUNT(*) as total_clicks,
                SUM(CASE WHEN visited_at >= CURDATE() THEN 1 ELSE 0 END) as today_clicks,
                SUM(CASE WHEN visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_clicks
            FROM link_stats
            GROUP BY link_id
        ');
        
        $stats = [];
        foreach ($stmt->fetchAll() as $row) {
            $stats[(int)$row['link_id']] = [
                'total' => (int)$row['total_clicks'],
                'today' => (int)$row['today_clicks'],
                'week' => (int)$row['week_clicks'],
            ];
        }
        
        return $stats;
    }
    
    /**
     * Wykryj typ urządzenia na podstawie User Agent
     */
    private function detectDeviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);
        
        // Boty
        if (preg_match('/bot|crawl|spider|slurp|mediapartners/i', $userAgent)) {
            return 'bot';
        }
        
        // Mobile
        if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
            return 'mobile';
        }
        
        // Tablet
        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    /**
     * Wykryj podstawową przeglądarkę na podstawie User Agent
     */
    private function detectBrowser(string $userAgent): string
    {
        $ua = strtolower($userAgent);
        // Kolejność ma znaczenie (Edge/Opera/Chrome/Safari/Firefox/IE)
        if (preg_match('/edg\//', $ua)) {
            return 'Edge';
        }
        if (preg_match('/opr\//', $ua)) {
            return 'Opera';
        }
        if (preg_match('/chrome\//', $ua) && !preg_match('/edg\//', $ua) && !preg_match('/opr\//', $ua)) {
            return 'Chrome';
        }
        if (preg_match('/safari\//', $ua) && !preg_match('/chrome\//', $ua)) {
            return 'Safari';
        }
        if (preg_match('/firefox\//', $ua)) {
            return 'Firefox';
        }
        if (preg_match('/msie|trident\//', $ua)) {
            return 'IE';
        }
        return 'Inne';
    }

    /**
     * Wyciągnij domenę/host z URL referera
     */
    private function extractDomain(string $referer): string
    {
        if (empty($referer)) {
            return 'Bezpośredni';
        }
        
        $parsed = parse_url($referer);
        if (isset($parsed['host'])) {
            return strtolower($parsed['host']);
        }
        
        return 'Inne';
    }

    /**
     * Pobierz dane heatmapy: dzień tygodnia × godzina
     * Zwraca tablicę [dayOfWeek][hour] => count
     * Dane są pobierane z bieżącego tygodnia (poniedziałek - niedziela)
     */
    private function getHeatmapData(int $linkId, int $days = 30, bool $excludeBots = false): array
    {
        // Oblicz początek bieżącego tygodnia (poniedziałek 00:00:00)
        $now = new DateTimeImmutable('now');
        $dayOfWeek = (int)$now->format('N'); // 1 (poniedziałek) do 7 (niedziela)
        $from = $now
            ->modify('-' . ($dayOfWeek - 1) . ' days')
            ->setTime(0, 0, 0)
            ->format('Y-m-d H:i:s');
        
        $sql = '
            SELECT 
                DAYOFWEEK(visited_at) as day_of_week,
                HOUR(visited_at) as hour,
                COUNT(*) as count
            FROM link_stats
            WHERE link_id = ? AND visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY DAYOFWEEK(visited_at), HOUR(visited_at)
            ORDER BY day_of_week, hour';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$linkId, $from]);
        
        // Inicjalizuj pustą siatkę 7 dni × 24 godziny
        $heatmap = [];
        for ($day = 1; $day <= 7; $day++) {
            for ($hour = 0; $hour < 24; $hour++) {
                $heatmap[$day][$hour] = 0;
            }
        }
        
        // Wypełnij danymi
        foreach ($stmt->fetchAll() as $row) {
            $day = (int)$row['day_of_week']; // 1=niedziela, 2=pn, ..., 7=sob
            $hour = (int)$row['hour'];
            $heatmap[$day][$hour] = (int)$row['count'];
        }
        
        return $heatmap;
    }

    /**
     * Statystyki cache geolokalizacji
     */
    public function getGeoCacheStats(): array
    {
        // Całkowita liczba wpisów
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM geo_cache');
        $totalEntries = (int)$stmt->fetchColumn();
        
        // Najstarszy i najnowszy wpis
        $stmt = $this->pdo->query('
            SELECT 
                MIN(updated_at) as oldest,
                MAX(updated_at) as newest
            FROM geo_cache
        ');
        $ages = $stmt->fetch();
        
        // Wpisy starsze niż 30 dni (wygasłe)
        $stmt = $this->pdo->query('
            SELECT COUNT(*) 
            FROM geo_cache 
            WHERE updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ');
        $expiredEntries = (int)$stmt->fetchColumn();
        
        // Rozmiar tabeli w bazie danych
        $tableSize = 0;
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    ROUND((data_length + index_length) / 1024, 2) as size_kb
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE() 
                AND table_name = 'geo_cache'
            ");
            $result = $stmt->fetch();
            $tableSize = $result['size_kb'] ?? 0;
        } catch (Exception $e) {
            // Jeśli nie można pobrać rozmiaru, zwróć 0
        }
        
        return [
            'total_entries' => $totalEntries,
            'expired_entries' => $expiredEntries,
            'oldest_entry' => $ages['oldest'] ?? null,
            'newest_entry' => $ages['newest'] ?? null,
            'table_size_kb' => $tableSize,
        ];
    }

    /**
     * Wyczyść stare wpisy cache (starsze niż X dni)
     */
    public function cleanOldGeoCache(int $days = 60): int
    {
        $stmt = $this->pdo->prepare('
            DELETE FROM geo_cache 
            WHERE updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ');
        $stmt->execute([$days]);
        return $stmt->rowCount();
    }

    /**
     * Wyczyść cały cache geolokalizacji
     */
    public function clearAllGeoCache(): int
    {
        $stmt = $this->pdo->query('DELETE FROM geo_cache');
        return $stmt->rowCount();
    }

    /**
     * Pobierz status systemu
     */
    public function getSystemStatus(): array
    {
        // Statystyki systemu
        $totalLinks = $this->pdo->query('SELECT COUNT(*) FROM links')->fetchColumn();
        $totalClicks = $this->pdo->query('SELECT COUNT(*) FROM link_stats')->fetchColumn();
        $clicksToday = $this->pdo->query("SELECT COUNT(*) FROM link_stats WHERE DATE(visited_at) = CURDATE()")->fetchColumn();
        $clicksThisWeek = $this->pdo->query("SELECT COUNT(*) FROM link_stats WHERE YEARWEEK(visited_at, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn();
        $linksThisMonth = $this->pdo->query("SELECT COUNT(*) FROM links WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn();

        // Unikalni użytkownicy
        $uniqueToday = $this->pdo->query("SELECT COUNT(DISTINCT ip_address) FROM link_stats WHERE DATE(visited_at) = CURDATE()")->fetchColumn();
        $uniqueThisWeek = $this->pdo->query("SELECT COUNT(DISTINCT ip_address) FROM link_stats WHERE YEARWEEK(visited_at, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn();

        // Linki bez kliknięć (nieaktywne)
        $linksWithoutClicks = $this->pdo->query("SELECT COUNT(*) FROM links l WHERE NOT EXISTS (SELECT 1 FROM link_stats ls WHERE ls.link_id = l.id)")->fetchColumn();

        // Liczba kategorii i programów afiliacyjnych
        $categoriesCount = $this->pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
        $affiliateProgramsCount = $this->pdo->query('SELECT COUNT(*) FROM affiliate_programs')->fetchColumn();

        // Ostatnia aktywność
        $lastActivity = $this->pdo->query("SELECT MAX(visited_at) FROM link_stats")->fetchColumn();

        // Rozmiar bazy danych
        $dbName = $this->pdo->query("SELECT DATABASE()")->fetchColumn();
        $stmt = $this->pdo->prepare("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
            FROM information_schema.TABLES
            WHERE table_schema = ?
        ");
        $stmt->execute([$dbName]);
        $dbSize = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cache geolokalizacji
        $geoCacheCount = $this->pdo->query('SELECT COUNT(*) FROM geo_cache')->fetchColumn();

        // Linki w koszu
        $trashedLinks = 0;
        try {
            $trashedLinks = $this->pdo->query("SELECT COUNT(*) FROM links WHERE status = 'trashed'")->fetchColumn();
        } catch (Throwable $e) {
            // Ignoruj jeśli kolumna nie istnieje
        }

        // Wpisy w logu audytu
        $auditLogCount = 0;
        try {
            $auditLogCount = $this->pdo->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn();
        } catch (Throwable $e) {
            // Ignoruj jeśli tabela nie istnieje
        }

        // Statystyki cron
        $cronLastRun = null;
        $cronErrors24h = 0;
        $cronActiveJobs = 0;
        try {
            // Ostatnie wykonanie dowolnego zadania
            $cronLastRun = $this->pdo->query("SELECT MAX(started_at) FROM cron_logs")->fetchColumn();
            // Błędy z ostatnich 24h
            $cronErrors24h = $this->pdo->query("SELECT COUNT(*) FROM cron_logs WHERE status = 'error' AND started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
            // Liczba aktywnych zadań
            $cronActiveJobs = $this->pdo->query("SELECT COUNT(*) FROM cron_jobs WHERE is_active = 1")->fetchColumn();
        } catch (Throwable $e) {
            // Ignoruj jeśli tabele nie istnieją
        }

        // Wersje i info o systemie
        $phpVersion = phpversion();
        $mysqlVersion = $this->pdo->query('SELECT VERSION()')->fetchColumn();
        $memoryLimit = ini_get('memory_limit') ?: '128M';
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');
        $maxUploadSize = min(
            $this->convertToBytes($uploadMaxFilesize !== false && $uploadMaxFilesize !== '' ? $uploadMaxFilesize : '0'),
            $this->convertToBytes($postMaxSize !== false && $postMaxSize !== '' ? $postMaxSize : '0')
        );
        $webServer = $_SERVER['SERVER_SOFTWARE'] ?? 'N/A';

        // Użyj StorageService do spójnych obliczeń rozmiarów katalogów (z cache w bazie)
        require_once __DIR__ . '/StorageService.php';
        require_once __DIR__ . '/SettingsRepository.php';
        $storageService = new StorageService($this->pdo);
        $allStats = $storageService->getAllStorageStats();

        // Rozmiary projektu (cache TTL 10 min dla ograniczenia kosztu rekursywnego liczenia)
        $projectRoot = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
        $projectSizeBytes = $this->getDirectorySizeCached($projectRoot, 'project_root', 600);
        $projectSizeMb = round($projectSizeBytes / 1024 / 1024, 2);

        // Oblicz średnią kliknięć na link
        $avgClicksPerLink = $totalLinks > 0 ? round((int)$totalClicks / (int)$totalLinks, 1) : 0;

        return [
            'statistics' => [
                'total_links' => (int)$totalLinks,
                'total_clicks' => (int)$totalClicks,
                'clicks_today' => (int)$clicksToday,
                'clicks_this_week' => (int)$clicksThisWeek,
                'links_this_month' => (int)$linksThisMonth,
                'unique_today' => (int)$uniqueToday,
                'unique_this_week' => (int)$uniqueThisWeek,
                'avg_clicks_per_link' => $avgClicksPerLink,
                'links_without_clicks' => (int)$linksWithoutClicks,
                'categories_count' => (int)$categoriesCount,
                'affiliate_programs_count' => (int)$affiliateProgramsCount,
                'last_activity' => $lastActivity,
            ],
            'resources' => [
                'database_size_mb' => (float)($dbSize['size_mb'] ?? 0),
                'uploads_size' => $allStats['uploads']['total_size'],
                'uploads_size_formatted' => $allStats['uploads']['size_formatted'],
                'logs_size' => $allStats['logs']['total_size'],
                'logs_size_formatted' => $allStats['logs']['size_formatted'],
                'cache_size' => $allStats['cache']['total_size'],
                'cache_size_formatted' => $allStats['cache']['size_formatted'],
                'backups_size' => $allStats['backups']['total_size'] ?? 0,
                'backups_size_formatted' => $allStats['backups']['size_formatted'] ?? '0 B',
                'total_size' => $allStats['total_size'],
                'total_size_formatted' => $allStats['total_size_formatted'],
                'project_size_mb' => $projectSizeMb,
            ],
            'technical' => [
                'php_version' => $phpVersion,
                'mysql_version' => $mysqlVersion,
                'geo_cache_records' => (int)$geoCacheCount,
                'memory_limit' => $memoryLimit,
                'max_upload_size' => $this->formatBytes($maxUploadSize),
                'web_server' => $webServer,
                'trashed_links' => (int)$trashedLinks,
                'audit_log_count' => (int)$auditLogCount,
                'cron_last_run' => $cronLastRun,
                'cron_errors_24h' => (int)$cronErrors24h,
                'cron_active_jobs' => (int)$cronActiveJobs,
                'timezone' => date_default_timezone_get(),
                'user_timezone' => (new SettingsRepository($this->pdo))->get('timezone', 'Europe/Warsaw'),
            ],
        ];
    }

    /**
     * Konwertuj string rozmiaru (np. "128M") na bajty
     */
    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || strlen($value) < 1) {
            return 0;
        }

        $num = (int)$value;
        if ($num <= 0) {
            return 0;
        }

        $last = strtolower($value[strlen($value) - 1]);

        switch ($last) {
            case 'g':
                $num *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $num *= 1024 * 1024;
                break;
            case 'k':
                $num *= 1024;
                break;
        }

        return $num;
    }

    /**
     * Formatuj bajty na czytelny string
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 1) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 0) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Oblicz rozmiar katalogu (rekurencyjnie)
     */
    private function getDirectorySize(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }
        
        $size = 0;
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
            ),
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        
        foreach ($items as $item) {
            if ($item->isFile()) {
                $size += $item->getSize();
            }
        }
        
        return $size;
    }

    /**
     * Cached directory size (bytes). Uses simple JSON cache under storage/cache.
     */
    private function getDirectorySizeCached(string $path, string $cacheKey, int $ttlSeconds = 600): int
    {
        $cacheFile = __DIR__ . '/../storage/cache/system_sizes.json';

        // Load cache
        $cache = [];
        if (is_file($cacheFile)) {
            $json = @file_get_contents($cacheFile);
            $decoded = json_decode((string)$json, true);
            if (is_array($decoded)) {
                $cache = $decoded;
            }
        }

        $now = time();
        if (isset($cache[$cacheKey]['ts'], $cache[$cacheKey]['size']) && ($now - (int)$cache[$cacheKey]['ts'] < $ttlSeconds)) {
            return (int)$cache[$cacheKey]['size'];
        }

        $size = $this->getDirectorySize($path);

        // Persist cache
        $cache[$cacheKey] = ['ts' => $now, 'size' => $size];
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents($cacheFile, json_encode($cache, JSON_PRETTY_PRINT));

        return $size;
    }

    /**
     * Pobierz globalne statystyki dla wszystkich linków (z cache)
     */
    public function getGlobalStats(int $days = 30, bool $excludeBots = false): array
    {
        $cacheKey = $this->getGlobalStatsCacheKey($days, $excludeBots);
        
        // Sprawdź cache
        $cached = $this->getGlobalStatsCache($cacheKey, self::GLOBAL_STATS_CACHE_TTL);
        if ($cached !== null) {
            return $cached;
        }
        
        // Cache miss - wygeneruj statystyki
        $stats = $this->generateGlobalStats($days, $excludeBots);
        
        // Zapisz do cache
        $this->setGlobalStatsCache($cacheKey, $stats);
        
        return $stats;
    }

    /**
     * Generuj globalne statystyki bez cache
     */
    private function generateGlobalStats(int $days, bool $excludeBots): array
    {
        return [
            'total_clicks' => $this->getGlobalClickCount('all', $excludeBots),
            'today' => $this->getGlobalClickCount('today', $excludeBots),
            'last_7_days' => $this->getGlobalClickCount('7 days', $excludeBots),
            'last_30_days' => $this->getGlobalClickCount('30 days', $excludeBots),
            'unique_today' => $this->getGlobalUniqueCount('today', $excludeBots),
            'unique_last_7_days' => $this->getGlobalUniqueCount('7 days', $excludeBots),
            'unique_last_30_days' => $this->getGlobalUniqueCount('30 days', $excludeBots),
            'unique_total' => $this->getGlobalUniqueCount('all', $excludeBots),
            'top_links' => $this->getTopLinks($days, $excludeBots, 10),
            'by_device' => $this->getGlobalClicksByDevice($days, $excludeBots),
            'by_browser' => $this->getGlobalClicksByBrowser($days, $excludeBots),
            'by_country' => $this->getGlobalClicksByCountry($days, $excludeBots, 10),
            'by_city' => $this->getGlobalClicksByCity($days, $excludeBots, 10),
            'by_referer_domain' => $this->getGlobalReferersByDomain($days, $excludeBots, 10),
            'hourly_distribution' => $this->getGlobalHourlyDistribution($days, $excludeBots),
            'daily_chart' => $this->getGlobalDailyChart($days, $excludeBots),
            'heatmap' => $this->getGlobalHeatmapData($days, $excludeBots),
        ];
    }

    /**
     * Ręczne odświeżenie cache statystyk globalnych
     */
    public function refreshGlobalStatsCache(int $days = 30, bool $excludeBots = false): array
    {
        $cacheKey = $this->getGlobalStatsCacheKey($days, $excludeBots);
        $stats = $this->generateGlobalStats($days, $excludeBots);
        $this->setGlobalStatsCache($cacheKey, $stats);
        return $stats;
    }

    /**
     * Pobierz klucz cache dla statystyk globalnych
     */
    public function getGlobalStatsCacheKey(int $days, bool $excludeBots): string
    {
        return 'global_stats_' . $days . 'd' . ($excludeBots ? '_nobots' : '');
    }

    /**
     * Pobierz cache statystyk globalnych
     */
    private function getGlobalStatsCache(string $cacheKey, int $ttlSeconds): ?array
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT data, updated_at
                FROM global_stats_cache
                WHERE cache_key = ?
            ');
            $stmt->execute([$cacheKey]);
            $row = $stmt->fetch();
            
            if (!$row) {
                return null;
            }
            
            // Sprawdź czy nie wygasło
            $age = time() - strtotime($row['updated_at']);
            if ($age >= $ttlSeconds) {
                return null;
            }
            
            $data = json_decode((string)($row['data'] ?? ''), true);
            return is_array($data) ? $data : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Zapisz cache statystyk globalnych
     */
    private function setGlobalStatsCache(string $cacheKey, array $stats): void
    {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO global_stats_cache (cache_key, data, updated_at)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    data = VALUES(data),
                    updated_at = NOW()
            ');
            $stmt->execute([$cacheKey, json_encode($stats, JSON_UNESCAPED_UNICODE)]);
        } catch (Throwable $e) {
            // Ignoruj błędy cache - nie przerywaj działania aplikacji
        }
    }

    /**
     * Wyczyść cały cache statystyk globalnych
     */
    public function clearGlobalStatsCache(): int
    {
        $stmt = $this->pdo->query('DELETE FROM global_stats_cache');
        return $stmt->rowCount();
    }

    /**
     * Pobierz informacje o cache
     */
    public function getGlobalStatsCacheInfo(): array
    {
        $stmt = $this->pdo->query('
            SELECT 
                cache_key,
                updated_at,
                TIMESTAMPDIFF(SECOND, updated_at, NOW()) as age_seconds
            FROM global_stats_cache
            ORDER BY updated_at DESC
        ');
        return $stmt->fetchAll();
    }

    /**
     * Sprawdź status cache (czy istnieje i czy jest aktualny)
     */
    public function checkGlobalStatsCacheStatus(string $cacheKey, ?int $ttlSeconds = null): array
    {
        if ($ttlSeconds === null) {
            $ttlSeconds = self::GLOBAL_STATS_CACHE_TTL;
        }
        
        try {
            $stmt = $this->pdo->prepare('
                SELECT 
                    updated_at,
                    TIMESTAMPDIFF(SECOND, updated_at, NOW()) as age_seconds
                FROM global_stats_cache
                WHERE cache_key = ?
            ');
            $stmt->execute([$cacheKey]);
            $row = $stmt->fetch();
            
            if (!$row) {
                return [
                    'exists' => false,
                    'expired' => true,
                    'age_seconds' => null,
                    'updated_at' => null,
                ];
            }
            
            $ageSeconds = (int)$row['age_seconds'];
            $expired = $ageSeconds >= $ttlSeconds;
            
            return [
                'exists' => true,
                'expired' => $expired,
                'age_seconds' => $ageSeconds,
                'updated_at' => $row['updated_at'],
            ];
        } catch (Throwable $e) {
            return [
                'exists' => false,
                'expired' => true,
                'age_seconds' => null,
                'updated_at' => null,
            ];
        }
    }

    /**
     * Globalna liczba kliknięć dla okresu
     */
    private function getGlobalClickCount(string $period, bool $excludeBots = false): int
    {
        $where = '1=1';
        $params = [];
        
        if ($period !== 'all') {
            match($period) {
                'today' => $where .= ' AND DATE(visited_at) = CURDATE()',
                '7 days' => $where .= ' AND visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
                '30 days' => $where .= ' AND visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
                default => null,
            };
        }
        if ($excludeBots) {
            $where .= " AND device_type <> 'bot'";
        }
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM link_stats WHERE $where");
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    }

    /**
     * Globalna liczba unikalnych IP dla okresu
     */
    private function getGlobalUniqueCount(string $period, bool $excludeBots = false): int
    {
        $where = '1=1';
        $params = [];
        if ($period !== 'all') {
            match($period) {
                'today' => $where .= ' AND DATE(visited_at) = CURDATE()',
                '7 days' => $where .= ' AND visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
                '30 days' => $where .= ' AND visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
                default => null,
            };
        }
        if ($excludeBots) {
            $where .= " AND device_type <> 'bot'";
        }
        $stmt = $this->pdo->prepare("SELECT COUNT(DISTINCT ip_address) FROM link_stats WHERE $where");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Top linki według liczby kliknięć
     */
    private function getTopLinks(int $days, bool $excludeBots, int $limit): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        $limit = max(1, (int)$limit);
        
        $sql = '
            SELECT 
                l.id,
                l.slug,
                l.page_title,
                l.target_url,
                COUNT(*) as clicks
            FROM link_stats ls
            JOIN links l ON ls.link_id = l.id
            WHERE ls.visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND ls.device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY l.id, l.slug, l.page_title, l.target_url
            ORDER BY clicks DESC
            LIMIT ' . $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $from, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Globalne statystyki według urządzeń
     */
    private function getGlobalClicksByDevice(int $days, bool $excludeBots): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        
        $sql = '
            SELECT device_type, COUNT(*) as count
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY device_type
            ORDER BY count DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$from]);
        
        return $stmt->fetchAll();
    }

    /**
     * Globalne statystyki według przeglądarek
     * 
     * UWAGA: Dla dużych zbiorów danych to może być nieoptymalne (pobiera wszystkie UA do PHP).
     * Rozważ: 1) kolumnę 'browser' w link_stats, 2) agregację SQL, 3) pagination/limit.
     */
    private function getGlobalClicksByBrowser(int $days, bool $excludeBots): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        $sql = '
            SELECT user_agent
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$from]);
        $rows = $stmt->fetchAll();

        $buckets = [];
        foreach ($rows as $row) {
            $browser = $this->detectBrowser((string)$row['user_agent']);
            $buckets[$browser] = ($buckets[$browser] ?? 0) + 1;
        }
        arsort($buckets);
        $result = [];
        foreach ($buckets as $name => $count) {
            $result[] = ['browser' => $name, 'count' => $count];
        }
        return $result;
    }

    /**
     * Globalne statystyki według krajów
     */
    private function getGlobalClicksByCountry(int $days, bool $excludeBots, int $limit): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        $limit = max(1, (int)$limit);
        
        $sql = '
            SELECT 
                COALESCE(country_code, "UNKNOWN") as country,
                COUNT(*) as count
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY country_code
            ORDER BY count DESC
            LIMIT ' . $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $from, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Globalne statystyki według miast
     */
    private function getGlobalClicksByCity(int $days, bool $excludeBots, int $limit): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        $limit = max(1, (int)$limit);
        
        $sql = '
            SELECT 
                COALESCE(city, "UNKNOWN") as city,
                country_code,
                COUNT(*) as count
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY city, country_code
            ORDER BY count DESC
            LIMIT ' . $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $from, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Globalne domeny źródłowe (referery)
     * 
     * UWAGA: Dla dużych zbiorów danych to może być nieoptymalne (pobiera wszystkie referery do PHP).
     * Rozważ: 1) kolumnę 'referer_domain' w link_stats, 2) SQL SUBSTRING_INDEX, 3) pagination.
     */
    private function getGlobalReferersByDomain(int $days, bool $excludeBots, int $limit): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        
        $sql = '
            SELECT referer
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$from]);
        $rows = $stmt->fetchAll();

        $buckets = [];
        foreach ($rows as $row) {
            $ref = (string)$row['referer'];
            $domain = $this->extractDomain($ref);
            $buckets[$domain] = ($buckets[$domain] ?? 0) + 1;
        }
        arsort($buckets);
        
        $result = [];
        $count = 0;
        foreach ($buckets as $domain => $clicks) {
            if ($count >= $limit) break;
            $result[] = ['domain' => $domain, 'count' => $clicks];
            $count++;
        }
        return $result;
    }

    /**
     * Globalny rozkład godzinowy
     */
    private function getGlobalHourlyDistribution(int $days, bool $excludeBots): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        $sql = '
            SELECT 
                HOUR(visited_at) as hour,
                COUNT(*) as count
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY HOUR(visited_at)
            ORDER BY hour';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$from]);
        
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int)$row['hour']] = (int)$row['count'];
        }
        
        // Wypełnij brakujące godziny zerami
        for ($i = 0; $i < 24; $i++) {
            if (!isset($result[$i])) {
                $result[$i] = 0;
            }
        }
        ksort($result);
        
        return $result;
    }

    /**
     * Globalny wykres dzienny
     */
    private function getGlobalDailyChart(int $days, bool $excludeBots): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');
        $sql = '
            SELECT 
                DATE(visited_at) as date,
                COUNT(*) as count
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY DATE(visited_at)
            ORDER BY date';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$from]);
        
        return $stmt->fetchAll();
    }

    /**
     * Pobierz globalne dane heatmapy: dzień tygodnia × godzina
     */
    private function getGlobalHeatmapData(int $days, bool $excludeBots): array
    {
        // Oblicz początek bieżącego tygodnia (poniedziałek 00:00:00)
        $now = new DateTimeImmutable('now');
        $dayOfWeek = (int)$now->format('N'); // 1 (poniedziałek) do 7 (niedziela)
        $from = $now
            ->modify('-' . ($dayOfWeek - 1) . ' days')
            ->setTime(0, 0, 0)
            ->format('Y-m-d H:i:s');
        
        $sql = '
            SELECT 
                DAYOFWEEK(visited_at) as day_of_week,
                HOUR(visited_at) as hour,
                COUNT(*) as count
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $sql .= " AND device_type <> 'bot'";
        }
        $sql .= '
            GROUP BY DAYOFWEEK(visited_at), HOUR(visited_at)
            ORDER BY day_of_week, hour';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$from]);
        
        // Inicjalizuj pustą siatkę 7 dni × 24 godziny
        $heatmap = [];
        for ($day = 1; $day <= 7; $day++) {
            for ($hour = 0; $hour < 24; $hour++) {
                $heatmap[$day][$hour] = 0;
            }
        }
        
        // Wypełnij danymi
        foreach ($stmt->fetchAll() as $row) {
            $day = (int)$row['day_of_week']; // 1=niedziela, 2=pn, ..., 7=sob
            $hour = (int)$row['hour'];
            $heatmap[$day][$hour] = (int)$row['count'];
        }
        
        return $heatmap;
    }

    /**
     * Pobierz ostatnio dodane/edytowane linki
     */
    public function getRecentLinks(int $limit = 5): array
    {
        $limit = max(1, (int)$limit);
        $stmt = $this->pdo->prepare('
            SELECT
                l.id,
                l.slug,
                l.page_title,
                l.target_url,
                l.created_at,
                l.updated_at,
                c.name as category_name,
                c.color as category_color,
                ap.name as affiliate_program_name,
                ap.color as affiliate_program_color,
                (SELECT COUNT(*) FROM link_stats WHERE link_id = l.id) as total_clicks,
                (SELECT COUNT(*) FROM link_stats WHERE link_id = l.id AND visited_at >= CURDATE()) as today_clicks
            FROM links l
            LEFT JOIN categories c ON l.category_id = c.id
            LEFT JOIN affiliate_programs ap ON l.affiliate_program_id = ap.id
            WHERE l.status = \'published\'
              AND (l.publish_at IS NULL OR l.publish_at <= NOW())
              AND (l.expires_at IS NULL OR l.expires_at > NOW())
            ORDER BY COALESCE(l.updated_at, l.created_at) DESC
            LIMIT ' . $limit . '
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Pobierz top performujące linki (z największą liczbą kliknięć)
     */
    public function getTopPerformingLinks(int $limit = 5, int $days = 30): array
    {
        $days = max(1, (int)$days);
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');

        $limit = max(1, (int)$limit);
        $stmt = $this->pdo->prepare('
            SELECT
                l.id,
                l.slug,
                l.page_title,
                l.target_url,
                c.name as category_name,
                c.color as category_color,
                ap.name as affiliate_program_name,
                ap.color as affiliate_program_color,
                COUNT(ls.id) as clicks,
                COUNT(DISTINCT ls.ip_address) as unique_visitors,
                MAX(ls.visited_at) as last_click
            FROM links l
            LEFT JOIN categories c ON l.category_id = c.id
            LEFT JOIN affiliate_programs ap ON l.affiliate_program_id = ap.id
            LEFT JOIN link_stats ls ON l.id = ls.link_id AND ls.visited_at >= ?
            WHERE l.status = \'published\'
              AND (l.publish_at IS NULL OR l.publish_at <= NOW())
              AND (l.expires_at IS NULL OR l.expires_at > NOW())
            GROUP BY l.id, l.slug, l.page_title, l.target_url, c.name, c.color, ap.name, ap.color
            HAVING clicks > 0
            ORDER BY clicks DESC
            LIMIT ' . $limit . '
        ');
        $stmt->bindValue(1, $from, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Wykryj anomalie w ruchu (spike/spadek)
     */
    public function detectAnomalies(): array
    {
        // Oblicz średnią dzienną z ostatnich 7 dni (poza dzisiaj)
        $stmt = $this->pdo->query('
            SELECT 
                AVG(daily_count) as avg_clicks,
                STDDEV(daily_count) as stddev_clicks
            FROM (
                SELECT COUNT(*) as daily_count
                FROM link_stats
                WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                AND visited_at < CURDATE()
                GROUP BY DATE(visited_at)
            ) daily_stats
        ');
        $baselineStats = $stmt->fetch();
        
        // Pobierz kliknięcia dzisiaj
        $todayStmt = $this->pdo->query('
            SELECT COUNT(*) FROM link_stats WHERE DATE(visited_at) = CURDATE()
        ');
        $todayClicks = (int)$todayStmt->fetchColumn();
        
        $anomalies = [];
        
        if ($baselineStats && $baselineStats['avg_clicks'] > 0) {
            $avgClicks = (float)$baselineStats['avg_clicks'];
            $stddev = (float)($baselineStats['stddev_clicks'] ?? 0);
            
            // Jeśli dzisiaj powyżej 2 sigma - spike
            if ($todayClicks > $avgClicks + (2 * $stddev)) {
                $percent = round((($todayClicks - $avgClicks) / $avgClicks) * 100, 1);
                $anomalies[] = [
                    'type' => 'spike',
                    'message' => "Pik ruchu! Dzisiaj +{$percent}% więcej kliknięć niż zwykle.",
                    'value' => $todayClicks,
                    'baseline' => $avgClicks,
                ];
            }
            // Jeśli dzisiaj poniżej średniej - spadek
            elseif ($todayClicks < $avgClicks * 0.5 && $todayClicks > 0) {
                $percent = round((($avgClicks - $todayClicks) / $avgClicks) * 100, 1);
                $anomalies[] = [
                    'type' => 'drop',
                    'message' => "Spadek ruchu. Dzisiaj -{$percent}% mniej kliknięć niż zwykle.",
                    'value' => $todayClicks,
                    'baseline' => $avgClicks,
                ];
            }
            // Jeśli dzisiaj zupełnie brak ruchu
            elseif ($todayClicks === 0 && $avgClicks > 0) {
                $anomalies[] = [
                    'type' => 'no_traffic',
                    'message' => "Brak ruchu dzisiaj! Przeciętnie: " . round($avgClicks) . " kliknięć/dzień.",
                    'value' => 0,
                    'baseline' => $avgClicks,
                ];
            }
        }
        
        return $anomalies;
    }

    /**
     * Pobierz linki z anomaliami (np. chronione hasłem ale bez kliknięć)
     */
    public function getUnusedLinks(int $limit = 5): array
    {
        // Linki starsze niż 7 dni bez żadnych kliknięć
        $limit = max(1, (int)$limit);
        $stmt = $this->pdo->prepare('
            SELECT
                l.id,
                l.slug,
                l.target_url,
                l.created_at,
                c.name as category_name,
                c.color as category_color
            FROM links l
            LEFT JOIN categories c ON l.category_id = c.id
            WHERE l.created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND l.id NOT IN (SELECT DISTINCT link_id FROM link_stats)
            ORDER BY l.created_at DESC
            LIMIT ' . $limit . '
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Pobierz dane dla wykresów na dashboardzie
     */
    public function getDashboardChartData(int $days = 30, bool $excludeBots = false): array
    {
        $days = max(1, min(90, (int)$days)); // Ograniczamy do 90 dni
        $from = (new DateTimeImmutable('now'))
            ->modify('-' . $days . ' days')
            ->format('Y-m-d H:i:s');

        // Wykres dzienny - kliknięcia w czasie
        $dailyQuery = '
            SELECT
                DATE(visited_at) as date,
                COUNT(*) as count,
                COUNT(DISTINCT ip_address) as unique_count
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $dailyQuery .= " AND device_type <> 'bot'";
        }
        $dailyQuery .= '
            GROUP BY DATE(visited_at)
            ORDER BY date';

        $stmt = $this->pdo->prepare($dailyQuery);
        $stmt->execute([$from]);
        $dailyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Rozkład według urządzeń
        $deviceQuery = '
            SELECT device_type, COUNT(*) as count
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $deviceQuery .= " AND device_type <> 'bot'";
        }
        $deviceQuery .= '
            GROUP BY device_type
            ORDER BY count DESC';

        $stmt = $this->pdo->prepare($deviceQuery);
        $stmt->execute([$from]);
        $deviceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Rozkład godzinowy
        $hourlyQuery = '
            SELECT
                HOUR(visited_at) as hour,
                COUNT(*) as count
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $hourlyQuery .= " AND device_type <> 'bot'";
        }
        $hourlyQuery .= '
            GROUP BY HOUR(visited_at)
            ORDER BY hour';

        $stmt = $this->pdo->prepare($hourlyQuery);
        $stmt->execute([$from]);
        $hourlyRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Wypełnij brakujące godziny
        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyData[$i] = 0;
        }
        foreach ($hourlyRaw as $row) {
            $hourlyData[(int)$row['hour']] = (int)$row['count'];
        }

        // Top 10 krajów
        $countryQuery = '
            SELECT
                COALESCE(country_code, "UNKNOWN") as country,
                COUNT(*) as count
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $countryQuery .= " AND device_type <> 'bot'";
        }
        $countryQuery .= '
            GROUP BY country_code
            ORDER BY count DESC
            LIMIT 10';

        $stmt = $this->pdo->prepare($countryQuery);
        $stmt->execute([$from]);
        $countryData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top 10 przeglądarek
        $browserQuery = '
            SELECT user_agent
            FROM link_stats
            WHERE visited_at >= ?';
        if ($excludeBots) {
            $browserQuery .= " AND device_type <> 'bot'";
        }

        $stmt = $this->pdo->prepare($browserQuery);
        $stmt->execute([$from]);
        $userAgents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $browserBuckets = [];
        foreach ($userAgents as $row) {
            $browser = $this->detectBrowser((string)$row['user_agent']);
            $browserBuckets[$browser] = ($browserBuckets[$browser] ?? 0) + 1;
        }
        arsort($browserBuckets);

        $browserData = [];
        $count = 0;
        foreach ($browserBuckets as $browser => $clicks) {
            if ($count >= 10) break;
            $browserData[] = ['browser' => $browser, 'count' => $clicks];
            $count++;
        }

        return [
            'daily' => $dailyData,
            'devices' => $deviceData,
            'hourly' => $hourlyData,
            'countries' => $countryData,
            'browsers' => $browserData,
        ];
    }

    /**
     * Pobierz dodatkowe informacje dla programów afiliacyjnych (do wykresów)
     */
    public function getAffiliateProgramData(int $limit = 10): array
    {
        $limit = max(1, (int)$limit);
        $stmt = $this->pdo->prepare('
            SELECT
                ap.id,
                ap.name,
                COUNT(DISTINCT l.id) as link_count,
                COUNT(ls.id) as click_count,
                COUNT(DISTINCT ls.ip_address) as unique_visitors
            FROM affiliate_programs ap
            LEFT JOIN links l ON ap.id = l.affiliate_program_id
            LEFT JOIN link_stats ls ON l.id = ls.link_id
            GROUP BY ap.id, ap.name
            HAVING click_count > 0
            ORDER BY click_count DESC
            LIMIT ' . $limit . '
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get database size in bytes
     */
    public function getDatabaseSize(): int
    {
        try {
            $stmt = $this->pdo->query('SELECT DATABASE() as db_name');
            $dbInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dbInfo || !$dbInfo['db_name']) {
                return 0;
            }

            $dbName = $dbInfo['db_name'];

            $stmt = $this->pdo->prepare('
                SELECT SUM(data_length + index_length) as size
                FROM information_schema.TABLES
                WHERE table_schema = :db_name
            ');
            $stmt->execute([':db_name' => $dbName]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['size'] ?? 0);
        } catch (Exception $e) {
            error_log('Error getting database size: ' . $e->getMessage());
            return 0;
        }
    }
}
