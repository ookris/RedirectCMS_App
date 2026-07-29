<?php

require_once __DIR__ . '/PrefixedPDO.php';

class Database
{
    private PDO $rawPdo;
    private PrefixedPDO $pdo;
    private static ?string $tablePrefix = null;
    private static array $tableNames = [
        'links', 'link_tags', 'link_custom_fields', 'link_images', 'link_stats',
        'link_health_checks', 'categories', 'tags', 'affiliate_programs', 'settings',
        'events', 'audit_logs', 'cron_jobs', 'cron_logs', 'geo_cache', 'global_stats_cache',
        'notifications', 'campaigns', 'campaign_links', 'login_attempts',
        'custom_pages', 'link_reactions'
    ];

    public function __construct(array $config)
    {
        $db = $config['db'];
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $db['host'], $db['dbname']);
        $this->rawPdo = new PDO($dsn, $db['user'], $db['pass'], $db['options'] ?? []);

        // Store table prefix
        self::$tablePrefix = $config['db']['table_prefix'] ?? '';

        // Wrap PDO with prefix support
        $this->pdo = new PrefixedPDO($this->rawPdo);
    }

    /**
     * Get PDO instance wrapped with prefix support
     * All queries will automatically have table prefixes applied
     * @return PrefixedPDO|PDO
     */
    public function pdo()
    {
        return $this->pdo;
    }

    /**
     * Get raw PDO instance without prefix wrapping (use sparingly)
     */
    public function rawPdo(): PDO
    {
        return $this->rawPdo;
    }

    /**
     * Apply table prefix to SQL query
     * Replaces all known table names with prefixed versions
     */
    public static function applyPrefix(string $sql): string
    {
        $prefix = self::getPrefix();
        if ($prefix === '') {
            return $sql;
        }

        foreach (self::$tableNames as $table) {
            // Match table names in various SQL contexts (with word boundaries)
            $patterns = [
                "/\bFROM\s+`?{$table}`?(\s|$)/i",
                "/\bINTO\s+`?{$table}`?(\s|\()/i",
                "/\bUPDATE\s+`?{$table}`?\s+SET\b/i",
                "/\bJOIN\s+`?{$table}`?(\s)/i",
                "/\bTABLE\s+`?{$table}`?(\s|\()/i",
            ];
            $replacements = [
                "FROM `{$prefix}{$table}`$1",
                "INTO `{$prefix}{$table}`$1",
                "UPDATE `{$prefix}{$table}` SET",
                "JOIN `{$prefix}{$table}`$1",
                "TABLE `{$prefix}{$table}`$1",
            ];
            $sql = preg_replace($patterns, $replacements, $sql);
        }

        return $sql;
    }

    /**
     * Get table name with prefix
     * Usage: Database::table('links') returns 'prefix_links' or 'links' if no prefix
     */
    public static function table(string $name): string
    {
        if (self::$tablePrefix === null) {
            // Try to load from config if not initialized
            $configPath = __DIR__ . '/../config/config.php';
            if (file_exists($configPath)) {
                $config = require $configPath;
                self::$tablePrefix = $config['db']['table_prefix'] ?? '';
            } else {
                self::$tablePrefix = '';
            }
        }

        return self::$tablePrefix . $name;
    }

    /**
     * Get current table prefix
     */
    public static function getPrefix(): string
    {
        if (self::$tablePrefix === null) {
            self::table(''); // Initialize
        }
        return self::$tablePrefix ?? '';
    }

    /**
     * Set table prefix (used during installation)
     */
    public static function setPrefix(string $prefix): void
    {
        self::$tablePrefix = $prefix;
    }
}
