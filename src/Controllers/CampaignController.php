<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../CampaignRepository.php';
require_once __DIR__ . '/../LinkRepository.php';
require_once __DIR__ . '/../StatsRepository.php';
require_once __DIR__ . '/../AuditService.php';

class CampaignController extends BaseController
{
    public function campaignsGet(): void
    {
        Utils::requireLogin();
        $repo = new CampaignRepository($this->pdo);
        $campaigns = $repo->listAll();

        foreach ($campaigns as &$campaign) {
            $campaign['stats'] = $repo->getCampaignStats((int)$campaign['id']);
        }
        unset($campaign);

        $this->view('campaigns', ['campaigns' => $campaigns]);
    }

    public function campaignNewGet(): void
    {
        Utils::requireLogin();
        $linkRepo = new LinkRepository($this->pdo);
        $allLinks = $linkRepo->list(1000, 0, null, null, 'slug', 'ASC', '', false, false, null, 'published');

        $this->view('campaign_form', [
            'campaign' => ['id' => null, 'name' => '', 'color' => '#2196F3', 'description' => ''],
            'campaignLinks' => [],
            'allLinks' => $allLinks,
            'isEdit' => false,
        ]);
    }

    public function campaignNewPost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=campaigns');
            exit;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $color = trim((string)($_POST['color'] ?? '#2196F3'));
        $description = trim((string)($_POST['description'] ?? ''));
        $linkIds = array_filter(array_map('intval', $_POST['link_ids'] ?? []));

        try {
            $repo = new CampaignRepository($this->pdo);

            $id = $repo->create($name, $color, $description ?: null);

            if (!empty($linkIds)) {
                $repo->setLinksForCampaign($id, $linkIds);
            }

            $audit = new AuditService($this->pdo);
            $audit->log('create', 'campaign', $id, $name);

            $_SESSION['toast_message'] = "Kampania \"$name\" została utworzona";
            $_SESSION['toast_type'] = 'success';
            header('Location: ' . $this->basePath . '/admin/index.php?action=campaign_detail&id=' . $id);
        } catch (\InvalidArgumentException $e) {
            $_SESSION['toast_message'] = $e->getMessage();
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=campaign_new');
        }
        exit;
    }

    public function campaignEditGet(int $id): void
    {
        Utils::requireLogin();
        $repo = new CampaignRepository($this->pdo);
        $campaign = $repo->getById($id);

        if (!$campaign) {
            $_SESSION['toast_message'] = 'Kampania nie istnieje';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=campaigns');
            exit;
        }

        $campaignLinks = $repo->getLinksForCampaign($id);
        $linkRepo = new LinkRepository($this->pdo);
        $allLinks = $linkRepo->list(1000, 0, null, null, 'slug', 'ASC', '', false, false, null, 'published');

        $this->view('campaign_form', [
            'campaign' => $campaign,
            'campaignLinks' => $campaignLinks,
            'allLinks' => $allLinks,
            'isEdit' => true,
        ]);
    }

    public function campaignEditPost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=campaigns');
            exit;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $color = trim((string)($_POST['color'] ?? '#2196F3'));
        $description = trim((string)($_POST['description'] ?? ''));
        $linkIds = array_filter(array_map('intval', $_POST['link_ids'] ?? []));

        try {
            $repo = new CampaignRepository($this->pdo);
            $repo->update($id, $name, $color, $description ?: null);
            $repo->setLinksForCampaign($id, $linkIds);

            $audit = new AuditService($this->pdo);
            $audit->log('update', 'campaign', $id, $name);

            $_SESSION['toast_message'] = "Kampania \"$name\" została zaktualizowana";
            $_SESSION['toast_type'] = 'success';
            header('Location: ' . $this->basePath . '/admin/index.php?action=campaign_detail&id=' . $id);
        } catch (\InvalidArgumentException $e) {
            $_SESSION['toast_message'] = $e->getMessage();
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=campaign_edit&id=' . $id);
        }
        exit;
    }

    public function campaignDetailGet(int $id): void
    {
        Utils::requireLogin();
        $repo = new CampaignRepository($this->pdo);
        $campaign = $repo->getById($id);

        if (!$campaign) {
            $_SESSION['toast_message'] = 'Kampania nie istnieje';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=campaigns');
            exit;
        }

        $links = $repo->getLinksForCampaign($id);
        $stats = $repo->getCampaignStats($id);

        $linkStats = [];
        try {
            $statsRepo = new StatsRepository($this->pdo);
            $linkStats = $statsRepo->getQuickStats();
        } catch (\Throwable $e) {}

        $this->view('campaign_detail', [
            'campaign' => $campaign,
            'links' => $links,
            'stats' => $stats,
            'linkStats' => $linkStats,
        ]);
    }

    public function campaignDeletePost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=campaigns');
            exit;
        }

        $repo = new CampaignRepository($this->pdo);
        $campaign = $repo->getById($id);
        $repo->delete($id);

        $audit = new AuditService($this->pdo);
        $audit->log('delete', 'campaign', $id, $campaign['name'] ?? '');

        $_SESSION['toast_message'] = 'Kampania została usunięta';
        $_SESSION['toast_type'] = 'success';
        header('Location: ' . $this->basePath . '/admin/index.php?action=campaigns');
        exit;
    }
}
