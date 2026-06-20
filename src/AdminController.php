<?php

require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/BackupController.php';
require_once __DIR__ . '/Controllers/CampaignController.php';
require_once __DIR__ . '/Controllers/CronController.php';
require_once __DIR__ . '/Controllers/TaxonomyController.php';
require_once __DIR__ . '/Controllers/SettingsController.php';
require_once __DIR__ . '/Controllers/StatsController.php';
require_once __DIR__ . '/Controllers/SystemController.php';
require_once __DIR__ . '/Controllers/LinkController.php';
require_once __DIR__ . '/Controllers/PageController.php';

class AdminController
{
    private ?AuthController $authController = null;
    private ?BackupController $backupController = null;
    private ?CampaignController $campaignController = null;
    private ?CronController $cronController = null;
    private ?TaxonomyController $taxonomyController = null;
    private ?SettingsController $settingsController = null;
    private ?StatsController $statsController = null;
    private ?SystemController $systemController = null;
    private ?LinkController $linkController = null;
    private ?PageController $pageController = null;

    public function __construct(
        private $pdo,
        private array $config,
        private string $basePath = '/'
    ) {}

    // ── Lazy-loading getters ──────────────────────────────────────

    private function auth(): AuthController
    {
        return $this->authController ??= new AuthController($this->pdo, $this->config, $this->basePath);
    }

    private function backup(): BackupController
    {
        return $this->backupController ??= new BackupController($this->pdo, $this->config, $this->basePath);
    }

    private function campaign(): CampaignController
    {
        return $this->campaignController ??= new CampaignController($this->pdo, $this->config, $this->basePath);
    }

    private function cron(): CronController
    {
        return $this->cronController ??= new CronController($this->pdo, $this->config, $this->basePath);
    }

    private function taxonomy(): TaxonomyController
    {
        return $this->taxonomyController ??= new TaxonomyController($this->pdo, $this->config, $this->basePath);
    }

    private function settings(): SettingsController
    {
        return $this->settingsController ??= new SettingsController($this->pdo, $this->config, $this->basePath);
    }

    private function statsCtrl(): StatsController
    {
        return $this->statsController ??= new StatsController($this->pdo, $this->config, $this->basePath);
    }

    private function system(): SystemController
    {
        return $this->systemController ??= new SystemController($this->pdo, $this->config, $this->basePath);
    }

    private function link(): LinkController
    {
        return $this->linkController ??= new LinkController($this->pdo, $this->config, $this->basePath);
    }

    private function page(): PageController
    {
        return $this->pageController ??= new PageController($this->pdo, $this->config, $this->basePath);
    }

    // ── Auth ──────────────────────────────────────────────────────

    public function loginGet(): void { $this->auth()->loginGet(); }
    public function loginPost(): void { $this->auth()->loginPost(); }
    public function logout(): void { $this->auth()->logout(); }
    public function twoFactorSetupGet(): void { $this->auth()->twoFactorSetupGet(); }
    public function twoFactorEnablePost(): void { $this->auth()->twoFactorEnablePost(); }
    public function twoFactorDisablePost(): void { $this->auth()->twoFactorDisablePost(); }
    public function twoFactorRegenerateBackupPost(): void { $this->auth()->twoFactorRegenerateBackupPost(); }
    public function twoFactorVerifyGet(): void { $this->auth()->twoFactorVerifyGet(); }
    public function twoFactorVerifyPost(): void { $this->auth()->twoFactorVerifyPost(); }

    // ── Backup ───────────────────────────────────────────────────

    public function backupGet(): void { $this->backup()->backupGet(); }
    public function backupCreatePost(): void { $this->backup()->backupCreatePost(); }
    public function backupDownload(): void { $this->backup()->backupDownload(); }
    public function backupRestorePost(): void { $this->backup()->backupRestorePost(); }
    public function backupRestoreUploadPost(): void { $this->backup()->backupRestoreUploadPost(); }
    public function backupDeletePost(): void { $this->backup()->backupDeletePost(); }
    public function backupAutoSettingsPost(): void { $this->backup()->backupAutoSettingsPost(); }

    // ── Campaign ─────────────────────────────────────────────────

    public function campaignsGet(): void { $this->campaign()->campaignsGet(); }
    public function campaignNewGet(): void { $this->campaign()->campaignNewGet(); }
    public function campaignNewPost(): void { $this->campaign()->campaignNewPost(); }
    public function campaignEditGet(int $id): void { $this->campaign()->campaignEditGet($id); }
    public function campaignEditPost(int $id): void { $this->campaign()->campaignEditPost($id); }
    public function campaignDetailGet(int $id): void { $this->campaign()->campaignDetailGet($id); }
    public function campaignDeletePost(int $id): void { $this->campaign()->campaignDeletePost($id); }

    // ── Cron ─────────────────────────────────────────────────────

    public function cronJobsGet(): void { $this->cron()->cronJobsGet(); }
    public function cronGenerateToken(): void { $this->cron()->cronGenerateToken(); }
    public function cronRegisterDefaults(): void { $this->cron()->cronRegisterDefaults(); }
    public function cronToggle(): void { $this->cron()->cronToggle(); }
    public function cronRunNow(): void { $this->cron()->cronRunNow(); }
    public function cronUpdateInterval(): void { $this->cron()->cronUpdateInterval(); }
    public function cronDelete(): void { $this->cron()->cronDelete(); }
    public function cronLogs(): void { $this->cron()->cronLogs(); }
    public function cronPseudoCronPost(): void { $this->cron()->cronPseudoCronPost(); }

    // ── Taxonomy (categories, tags, affiliate programs) ──────────

    public function affiliateProgramsGet(): void { $this->taxonomy()->affiliateProgramsGet(); }
    public function affiliateProgramNewGet(): void { $this->taxonomy()->affiliateProgramNewGet(); }
    public function affiliateProgramNewPost(): void { $this->taxonomy()->affiliateProgramNewPost(); }
    public function affiliateProgramEditGet(int $id): void { $this->taxonomy()->affiliateProgramEditGet($id); }
    public function affiliateProgramEditPost(int $id): void { $this->taxonomy()->affiliateProgramEditPost($id); }
    public function affiliateProgramDeletePost(int $id): void { $this->taxonomy()->affiliateProgramDeletePost($id); }
    public function categoriesGet(): void { $this->taxonomy()->categoriesGet(); }
    public function categoryNewGet(): void { $this->taxonomy()->categoryNewGet(); }
    public function categoryNewPost(): void { $this->taxonomy()->categoryNewPost(); }
    public function categoryEditGet(int $id): void { $this->taxonomy()->categoryEditGet($id); }
    public function categoryEditPost(int $id): void { $this->taxonomy()->categoryEditPost($id); }
    public function categoryDeletePost(int $id): void { $this->taxonomy()->categoryDeletePost($id); }
    public function tagsGet(): void { $this->taxonomy()->tagsGet(); }
    public function tagNewGet(): void { $this->taxonomy()->tagNewGet(); }
    public function tagNewPost(): void { $this->taxonomy()->tagNewPost(); }
    public function tagEditGet(int $id): void { $this->taxonomy()->tagEditGet($id); }
    public function tagEditPost(int $id): void { $this->taxonomy()->tagEditPost($id); }
    public function tagDeletePost(int $id): void { $this->taxonomy()->tagDeletePost($id); }

    // ── Settings ─────────────────────────────────────────────────

    public function settingsGet(): void { $this->settings()->settingsGet(); }
    public function settingsPost(): void { $this->settings()->settingsPost(); }
    public function testSmtpPost(): void { $this->settings()->testSmtpPost(); }
    public function testNotificationPost(): void { $this->settings()->testNotificationPost(); }
    public function regenerateImagesPost(): void { $this->settings()->regenerateImagesPost(); }
    public function appearanceGet(): void      { $this->settings()->appearanceGet(); }
    public function appearancePost(): void     { $this->settings()->appearancePost(); }
    public function imageOptimizerGet(): void  { $this->settings()->imageOptimizerGet(); }
    public function imageOptimizerPost(): void { $this->settings()->imageOptimizerPost(); }

    // ── Stats & Dashboard ────────────────────────────────────────

    public function dashboard(): void { $this->statsCtrl()->dashboard(); }
    public function stats(int $id): void { $this->statsCtrl()->stats($id); }
    public function exportStats(int $id): void { $this->statsCtrl()->exportStats($id); }
    public function geoCacheGet(): void { $this->statsCtrl()->geoCacheGet(); }
    public function geoCacheCleanup(): void { $this->statsCtrl()->geoCacheCleanup(); }
    public function globalStats(): void { $this->statsCtrl()->globalStats(); }
    public function refreshGlobalStatsCache(): void { $this->statsCtrl()->refreshGlobalStatsCache(); }

    // ── System (logs, resources, audit, notifications, etc.) ─────

    public function logsGet(): void { $this->system()->logsGet(); }
    public function resourcesGet(): void { $this->system()->resourcesGet(); }
    public function recalculateStoragePost(): void { $this->system()->recalculateStoragePost(); }
    public function auditLogsGet(): void { $this->system()->auditLogsGet(); }
    public function notificationsGet(): void { $this->system()->notificationsGet(); }
    public function notificationMarkRead(): void { $this->system()->notificationMarkRead(); }
    public function brokenLinksGet(): void { $this->system()->brokenLinksGet(); }
    public function brokenLinkAction(): void { $this->system()->brokenLinkAction(); }
    public function brokenLinksRunCheck(): void { $this->system()->brokenLinksRunCheck(); }
    public function fileManagerGet(): void { $this->system()->fileManagerGet(); }
    public function fileManagerDeletePost(): void { $this->system()->fileManagerDeletePost(); }
    public function fileDetailsGet(): void { $this->system()->fileDetailsGet(); }

    // ── Links ────────────────────────────────────────────────────

    public function links(): void { $this->link()->links(); }
    public function exportLinks(): void { $this->link()->exportLinks(); }
    public function importLinksGet(): void { $this->link()->importLinksGet(); }
    public function importLinksPost(): void { $this->link()->importLinksPost(); }
    public function bulkAction(): void { $this->link()->bulkAction(); }
    public function linkNewGet(): void { $this->link()->linkNewGet(); }
    public function linkNewPost(): void { $this->link()->linkNewPost(); }
    public function linkEditGet(int $id): void { $this->link()->linkEditGet($id); }
    public function linkEditPost(int $id): void { $this->link()->linkEditPost($id); }
    public function linkDeletePost(int $id): void { $this->link()->linkDeletePost($id); }
    public function linkRestorePost(int $id): void { $this->link()->linkRestorePost($id); }
    public function linkPermanentDeletePost(int $id): void { $this->link()->linkPermanentDeletePost($id); }
    public function emptyTrashPost(): void { $this->link()->emptyTrashPost(); }
    public function linkDuplicatePost(int $id): void { $this->link()->linkDuplicatePost($id); }

    // ── Shared (from BaseController, delegated via LinkController) ──

    public function uploadImageAjax(): void { $this->link()->uploadImageAjax(); }
    public function deleteUploadedImageAjax(): void { $this->link()->deleteUploadedImageAjax(); }
    public function generateQRCode(int $linkId, string $mode = 'display'): void { $this->link()->generateQRCode($linkId, $mode); }

    // ── Pages ────────────────────────────────────────────────────

    public function pagesGet(): void { $this->page()->pagesGet(); }
    public function pageNewGet(): void { $this->page()->pageNewGet(); }
    public function pageNewPost(): void { $this->page()->pageNewPost(); }
    public function pageEditGet(int $id): void { $this->page()->pageEditGet($id); }
    public function pageEditPost(int $id): void { $this->page()->pageEditPost($id); }
    public function pageDeletePost(int $id): void { $this->page()->pageDeletePost($id); }
}
