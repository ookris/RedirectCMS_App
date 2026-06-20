-- ============================================================
-- RedirectCMS — schemat bazy danych (instalacja)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Table: affiliate_programs
DROP TABLE IF EXISTS `affiliate_programs`;
CREATE TABLE `affiliate_programs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT '#198754',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: audit_logs
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `action_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'login, logout, create, update, delete, restore, settings_change, bulk_action',
  `entity_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'link, category, tag, affiliate_program, settings, cron_job',
  `entity_id` int unsigned DEFAULT NULL COMMENT 'ID of affected entity (null for login/logout/bulk)',
  `entity_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Human-readable name (slug, category name, etc.)',
  `old_values` json DEFAULT NULL COMMENT 'Previous state of changed fields',
  `new_values` json DEFAULT NULL COMMENT 'New state of changed fields',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_action_type` (`action_type`),
  KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  KEY `idx_audit_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: campaigns
DROP TABLE IF EXISTS `campaigns`;
CREATE TABLE `campaigns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#2196F3',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: campaign_links
DROP TABLE IF EXISTS `campaign_links`;
CREATE TABLE `campaign_links` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int NOT NULL,
  `link_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `campaign_link_unique` (`campaign_id`,`link_id`),
  KEY `idx_campaign_id` (`campaign_id`),
  KEY `idx_link_id` (`link_id`),
  CONSTRAINT `fk_campaign_links_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_campaign_links_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: categories
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#6c757d',
  `icon_image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ścieżka do ikonki kategorii (800x800px)',
  `icon_image_thumb` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ścieżka do miniatury ikonki (200x200px)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cron_jobs
DROP TABLE IF EXISTS `cron_jobs`;
CREATE TABLE `cron_jobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `last_run` datetime DEFAULT NULL,
  `next_run` datetime DEFAULT NULL,
  `interval_seconds` int NOT NULL DEFAULT '3600',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `callback_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `callback_method` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_next_run` (`next_run`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Domyślne zadania cron (next_run = NOW() — uruchomią się przy pierwszym wywołaniu crona)
INSERT INTO `cron_jobs` (`name`, `description`, `last_run`, `next_run`, `interval_seconds`, `is_active`, `callback_class`, `callback_method`) VALUES
('clean_geo_cache',          'Czyszczenie starego cache geolokalizacji (starszego niż 30 dni)',                              NULL, NOW(), 86400,   1, 'CronTasks', 'cleanGeoCache'),
('refresh_global_stats',     'Odświeżanie cache statystyk globalnych dla różnych przedziałów czasowych',                    NULL, NOW(), 1800,    1, 'CronTasks', 'refreshGlobalStatsCache'),
('clean_cron_logs',          'Usuwanie logów cron starszych niż 30 dni',                                                    NULL, NOW(), 604800,  1, 'CronTasks', 'cleanCronLogs'),
('clean_old_events',         'Usuwanie wydarzeń (events) starszych niż 90 dni',                                             NULL, NOW(), 2592000, 1, 'CronTasks', 'cleanOldEvents'),
('clean_orphan_uploads',     'Usuwanie plików w uploads/ nieużywanych w bazie (og_image/link_images/branding)',             NULL, NOW(), 21600,   1, 'CronTasks', 'cleanOrphanUploads'),
('send_email_notifications', 'Wysyłanie powiadomień email z podsumowaniem statystyk top linków',                            NULL, NOW(), 3600,    1, 'CronTasks', 'sendEmailNotifications'),
('clean_trash',              'Automatyczne usuwanie linków z kosza starszych niż ustawiona liczba dni',                     NULL, NOW(),                                    86400,   1, 'CronTasks', 'cleanTrash'),
('check_broken_links',       'Sprawdzanie dostępności linków docelowych i wykrywanie błędnych',                             NULL, DATE_ADD(NOW(), INTERVAL  300 SECOND),  86400,   1, 'CronTasks', 'checkBrokenLinks'),
('clean_link_health_history','Usuwanie starej historii sprawdzeń linków (zachowuje ostatnie 10 na link)',                   NULL, DATE_ADD(NOW(), INTERVAL  600 SECOND),  604800,  1, 'CronTasks', 'cleanLinkHealthHistory'),
('clean_audit_logs',         'Usuwanie logów audytu starszych niż 90 dni',                                                  NULL, DATE_ADD(NOW(), INTERVAL  900 SECOND),  2592000, 1, 'CronTasks', 'cleanAuditLogs'),
('update_storage_sizes',     'Przelicza i aktualizuje rozmiary katalogów storage (uploads, logs, cache) w bazie danych',    NULL, DATE_ADD(NOW(), INTERVAL 1200 SECOND),  21600,   1, 'CronTasks', 'updateStorageSizes'),
('clean_notifications',      'Usuwanie powiadomień systemowych starszych niż 90 dni',                                       NULL, DATE_ADD(NOW(), INTERVAL 1500 SECOND),  7776000, 1, 'CronTasks', 'cleanNotifications'),
('auto_backup',              'Automatyczne tworzenie kopii zapasowych (SQL + pliki uploads)',                                NULL, DATE_ADD(NOW(), INTERVAL 1800 SECOND),  86400,   1, 'CronTasks', 'createAutoBackup'),
('clean_login_attempts',     'Usuwanie wygasłych rekordów prób logowania (starszych niż 24h)',                              NULL, DATE_ADD(NOW(), INTERVAL 2100 SECOND),  86400,   1, 'CronTasks', 'cleanLoginAttempts');

-- Table: cron_logs
DROP TABLE IF EXISTS `cron_logs`;
CREATE TABLE `cron_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `job_id` int NOT NULL,
  `started_at` datetime NOT NULL,
  `finished_at` datetime DEFAULT NULL,
  `status` enum('running','success','error') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'running',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `execution_time_ms` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_job_id` (`job_id`),
  KEY `idx_started_at` (`started_at`),
  CONSTRAINT `cron_logs_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `cron_jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: custom_pages
DROP TABLE IF EXISTS `custom_pages`;
CREATE TABLE `custom_pages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_html` mediumtext COLLATE utf8mb4_unicode_ci,
  `content_js` text COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('published','draft') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `show_in_nav` tinyint(1) NOT NULL DEFAULT '0',
  `use_theme` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: events
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `link_id` int unsigned NOT NULL,
  `event_type` enum('click','redirect') NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `referer` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `link_id` (`link_id`),
  CONSTRAINT `fk_events_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: geo_cache
DROP TABLE IF EXISTS `geo_cache`;
CREATE TABLE `geo_cache` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: global_stats_cache
DROP TABLE IF EXISTS `global_stats_cache`;
CREATE TABLE `global_stats_cache` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(64) NOT NULL COMMENT 'Klucz cache (np. global_stats_7d_nobots)',
  `data` json NOT NULL COMMENT 'Dane statystyk w formacie JSON',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cache_key` (`cache_key`),
  KEY `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cache dla statystyk globalnych';

-- Table: link_custom_fields
DROP TABLE IF EXISTS `link_custom_fields`;
CREATE TABLE `link_custom_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `link_id` int unsigned NOT NULL,
  `field_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `link_field_unique` (`link_id`,`field_key`),
  KEY `idx_link_id` (`link_id`),
  CONSTRAINT `fk_link_custom_fields_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: link_health_checks
DROP TABLE IF EXISTS `link_health_checks`;
CREATE TABLE `link_health_checks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `link_id` int unsigned NOT NULL,
  `http_status` int DEFAULT NULL COMMENT 'HTTP status code (200, 404, 500, etc.) or NULL for connection errors',
  `response_time_ms` int unsigned DEFAULT NULL COMMENT 'Response time in milliseconds',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Error description for failed checks',
  `final_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Final URL after redirects',
  `redirect_count` tinyint unsigned DEFAULT '0' COMMENT 'Number of redirects followed',
  `status` enum('healthy','broken','ignored','resolved') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'healthy',
  `checked_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_health_link_id` (`link_id`),
  KEY `idx_health_status` (`status`),
  KEY `idx_health_checked_at` (`checked_at`),
  CONSTRAINT `fk_health_check_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: link_images
DROP TABLE IF EXISTS `link_images`;
CREATE TABLE `link_images` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `link_id` int unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumb_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_link_images_link` (`link_id`),
  KEY `idx_link_images_primary` (`link_id`,`is_primary`),
  CONSTRAINT `fk_link_images_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: link_reactions
DROP TABLE IF EXISTS `link_reactions`;
CREATE TABLE `link_reactions` (
  `id`            int unsigned     NOT NULL AUTO_INCREMENT,
  `link_id`       int unsigned     NOT NULL,
  `reaction_type` enum('happy','love','laugh','surprised','cry','anger') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_hash`       varchar(64)      COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256(IP + salt)',
  `created_at`    timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_link_reactions_link`      (`link_id`),
  KEY `idx_link_reactions_ip_link`   (`link_id`, `ip_hash`, `created_at`),
  KEY `idx_link_reactions_link_type` (`link_id`, `reaction_type`),
  CONSTRAINT `fk_link_reactions_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: link_stats
DROP TABLE IF EXISTS `link_stats`;
CREATE TABLE `link_stats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `link_id` int unsigned NOT NULL,
  `visited_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `referer` text,
  `country_code` varchar(2) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `device_type` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `link_id` (`link_id`),
  KEY `idx_link_stats_visited_at` (`visited_at`),
  KEY `idx_link_stats_link_date` (`link_id`,`visited_at`),
  CONSTRAINT `fk_link_stats_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tags
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: link_tags
DROP TABLE IF EXISTS `link_tags`;
CREATE TABLE `link_tags` (
  `link_id` int unsigned NOT NULL,
  `tag_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`link_id`,`tag_id`),
  KEY `idx_link_id` (`link_id`),
  KEY `idx_tag_id` (`tag_id`),
  CONSTRAINT `fk_link_tags_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_link_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: links
DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) NOT NULL,
  `target_url` text NOT NULL,
  `delay_seconds` int unsigned NOT NULL DEFAULT '5',
  `page_title` varchar(255) DEFAULT NULL COMMENT 'Tytuł strony dla meta tagów',
  `page_description` text COMMENT 'Opis strony dla meta tagów',
  `og_image` varchar(500) DEFAULT NULL COMMENT 'Ścieżka do obrazka Open Graph',
  `og_image_thumb` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `affiliate_program_id` int DEFAULT NULL,
  `publish_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `fallback_url` text,
  `password_hash` varchar(255) DEFAULT NULL,
  `password_hint` varchar(255) DEFAULT NULL,
  `notes` text COMMENT 'Wewnętrzne notatki o linku',
  `status` enum('published','draft','trashed') NOT NULL DEFAULT 'published' COMMENT 'Status linku: published (aktywny), draft (szkic), trashed (w koszu)',
  `deleted_at` datetime DEFAULT NULL COMMENT 'Data przeniesienia do kosza (dla auto-usuwania)',
  `last_health_status` enum('healthy','broken','ignored','resolved') DEFAULT NULL COMMENT 'Ostatni status sprawdzenia linku',
  `last_health_check_at` datetime DEFAULT NULL COMMENT 'Data ostatniego sprawdzenia linku',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_links_category` (`category_id`),
  KEY `idx_links_publish_at` (`publish_at`),
  KEY `idx_links_expires_at` (`expires_at`),
  KEY `fk_links_affiliate_program` (`affiliate_program_id`),
  KEY `idx_links_status` (`status`),
  KEY `idx_links_deleted_at` (`deleted_at`),
  KEY `idx_links_health_status` (`last_health_status`),
  CONSTRAINT `fk_links_affiliate_program` FOREIGN KEY (`affiliate_program_id`) REFERENCES `affiliate_programs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_links_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: login_attempts
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `ip_hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256 adresu IP',
  `attempts` tinyint unsigned NOT NULL DEFAULT '0',
  `first_attempt_at` int unsigned NOT NULL,
  `blocked_until` int unsigned DEFAULT NULL,
  PRIMARY KEY (`ip_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rate limiting logowania per IP';

-- Table: notifications
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `component` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` enum('info','warning','error','critical') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'error',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `context` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_is_read` (`is_read`),
  KEY `idx_notifications_component` (`component`),
  KEY `idx_notifications_created_at` (`created_at`),
  KEY `idx_notifications_unread_date` (`is_read`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: settings
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `key` varchar(64) NOT NULL,
  `value` text NOT NULL,
  `description` text COMMENT 'Opis ustawienia',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Domyślne ustawienia aplikacji (bez danych osobowych i poufnych)
-- Dane logowania admina są wstawiane osobno przez install.php
INSERT INTO `settings` (`key`, `value`) VALUES
('timezone',                    'Europe/Warsaw'),
('pretty_urls',                 '1'),
('random_slug_length',          '9'),
('redirect_delay_seconds',      '2'),
('disable_social_redirect',     '0'),
('home_mode',                   'landing'),
('home_logo_text',              'RedirectCMS'),
('home_title',                  'RedirectCMS'),
('home_subtitle',               ''),
('home_description',            ''),
('home_section_title',          ''),
('home_section_text',           ''),
('home_cta_button_url',         ''),
('home_admin_button_text',      ''),
('home_footer',                 ''),
('home_header_code',            ''),
('home_footer_code',            ''),
('home_redirect_url',           ''),
('redirect_header_code',        ''),
('redirect_footer_code',        ''),
('blog_posts_per_page',         '10'),
('blog_description_length',     '200'),
('blog_show_images',            '1'),
('blog_theme',                  'default'),
('slider_count',                '3'),
('slider_type',                 'newest'),
('slider_days',                 '0'),
('branding_logo',               ''),
('branding_favicon',            ''),
('backup_auto_enabled',         '1'),
('backup_auto_keep',            '7'),
('backup_preferred_hour',       '1'),
('trash_mode',                  'auto_delete'),
('trash_auto_delete_days',      '30'),
('social_links',                '[]'),
('sidebar_widgets',             '[]'),
('notification_enabled',        '0'),
('notification_email',          ''),
('notification_frequency',      '1'),
('notification_time',           '10:00'),
('notification_top_links',      '5'),
('notification_custom_days',    '7'),
('smtp_enabled',                '0'),
('smtp_host',                   ''),
('smtp_port',                   '587'),
('smtp_username',               ''),
('smtp_password_encrypted',     ''),
('smtp_encryption',             'tls'),
('smtp_from_email',             ''),
('smtp_from_name',              'RedirectCMS'),
('debug_enabled',               '0'),
('debug_display_errors',        '0'),
('debug_log_errors',            '1'),
('debug_error_level',           'E_ALL'),
('disk_quota_database_mb',      '500'),
('disk_quota_files_mb',         '102400'),
('image_optimization_driver',   'gd'),
('image_optimization_settings', '{"gd":{"jpeg_quality":78,"png_compression":9},"imagick":{"jpeg_quality":80,"png_quality":85,"webp_quality":80},"exec_tools":{"jpegoptim_max":82,"pngquant_min":65,"pngquant_max":85,"optipng_level":2}}'),
('contact_page_enabled',        '0'),
('contact_page_intro',          ''),
('contact_page_email',          ''),
('custom_link_fields',          '[]'),
('cron_token',                  ''),
('theme_colors',                '{"primary":"#2196F3","header_bg":"#1565c0","header_text":"#ffffff","accent":"#ff9800","footer_bg":"#212121","footer_text":"#bdbdbd","body_bg":"#f5f5f5"}');

SET FOREIGN_KEY_CHECKS = 1;
