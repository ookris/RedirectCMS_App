<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../PageRepository.php';
require_once __DIR__ . '/../AuditService.php';

class PageController extends BaseController
{
    private function repo(): PageRepository
    {
        $repo = new PageRepository($this->pdo);
        $repo->ensureTable();
        return $repo;
    }

    public function pagesGet(): void
    {
        Utils::requireLogin();
        $pages = $this->repo()->listAll();
        $this->view('pages', [
            'pages'   => $pages,
            'success' => $_SESSION['toast_message'] ?? null,
            'toast_type' => $_SESSION['toast_type'] ?? 'success',
        ]);
        unset($_SESSION['toast_message'], $_SESSION['toast_type']);
    }

    public function pageNewGet(): void
    {
        Utils::requireLogin();
        $this->view('page_form', [
            'page'   => ['id' => null, 'title' => '', 'slug' => '', 'content_html' => '',
                         'content_js' => '', 'meta_title' => '', 'meta_description' => '',
                         'status' => 'draft', 'show_in_nav' => 0, 'use_theme' => 1],
            'isEdit' => false,
            'csrf'   => Utils::csrfToken(),
            'error'  => $_SESSION['page_error'] ?? null,
        ]);
        unset($_SESSION['page_error']);
    }

    public function pageNewPost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=pages');
            exit;
        }

        $repo = $this->repo();

        try {
            [$title, $slug, $html, $js, $mt, $md, $status, $nav, $theme] = $this->extractFormData($repo);

            $id = $repo->create($title, $slug, $html, $js, $mt ?: null, $md ?: null, $status, $nav, $theme);

            $audit = new AuditService($this->pdo);
            $audit->log('create', 'page', $id, $title);

            $_SESSION['toast_message'] = "Strona \"$title\" została utworzona";
            $_SESSION['toast_type'] = 'success';
            header('Location: ' . $this->basePath . '/admin/index.php?action=page_edit&id=' . $id);
        } catch (\InvalidArgumentException $e) {
            $_SESSION['page_error'] = $e->getMessage();
            header('Location: ' . $this->basePath . '/admin/index.php?action=page_new');
        }
        exit;
    }

    public function pageEditGet(int $id): void
    {
        Utils::requireLogin();
        $page = $this->repo()->getById($id);
        if (!$page) {
            http_response_code(404);
            echo 'Strona nie istnieje';
            return;
        }
        $this->view('page_form', [
            'page'   => $page,
            'isEdit' => true,
            'csrf'   => Utils::csrfToken(),
            'error'  => $_SESSION['page_error'] ?? null,
        ]);
        unset($_SESSION['page_error']);
    }

    public function pageEditPost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=pages');
            exit;
        }

        $repo = $this->repo();
        $existing = $repo->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo 'Strona nie istnieje';
            return;
        }

        try {
            [$title, $slug, $html, $js, $mt, $md, $status, $nav, $theme] = $this->extractFormData($repo, $id);

            $repo->update($id, $title, $slug, $html, $js, $mt ?: null, $md ?: null, $status, $nav, $theme);

            $audit = new AuditService($this->pdo);
            $audit->log('update', 'page', $id, $title);

            $_SESSION['toast_message'] = "Strona \"$title\" została zaktualizowana";
            $_SESSION['toast_type'] = 'success';
            header('Location: ' . $this->basePath . '/admin/index.php?action=page_edit&id=' . $id);
        } catch (\InvalidArgumentException $e) {
            $_SESSION['page_error'] = $e->getMessage();
            header('Location: ' . $this->basePath . '/admin/index.php?action=page_edit&id=' . $id);
        }
        exit;
    }

    public function pageDeletePost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=pages');
            exit;
        }

        $repo = $this->repo();
        $page = $repo->getById($id);
        if ($page) {
            $repo->delete($id);
            $audit = new AuditService($this->pdo);
            $audit->log('delete', 'page', $id, $page['title']);
            $_SESSION['toast_message'] = "Strona \"{$page['title']}\" została usunięta";
            $_SESSION['toast_type'] = 'success';
        }
        header('Location: ' . $this->basePath . '/admin/index.php?action=pages');
        exit;
    }

    /** @return array{string, string, string, string, string, string, string, bool, bool} */
    private function extractFormData(PageRepository $repo, int $excludeId = 0): array
    {
        $title = trim((string)($_POST['title'] ?? ''));
        if ($title === '') {
            throw new \InvalidArgumentException('Tytuł strony jest wymagany');
        }
        if (mb_strlen($title) > 255) {
            throw new \InvalidArgumentException('Tytuł może mieć maksymalnie 255 znaków');
        }

        $slug = trim((string)($_POST['slug'] ?? ''));
        $slug = preg_replace('/[^a-z0-9-]/', '', strtolower(str_replace(' ', '-', $slug)));
        $slug = trim($slug, '-');

        if ($slug === '') {
            throw new \InvalidArgumentException('Slug jest wymagany');
        }
        if (mb_strlen($slug) > 191) {
            throw new \InvalidArgumentException('Slug może mieć maksymalnie 191 znaków');
        }
        if ($repo->isReservedSlug($slug)) {
            throw new \InvalidArgumentException("Slug \"$slug\" jest zarezerwowany przez system");
        }
        if ($repo->slugExists($slug, $excludeId)) {
            throw new \InvalidArgumentException("Strona o slug \"$slug\" już istnieje");
        }

        $html  = (string)($_POST['content_html'] ?? '');
        $js    = trim((string)($_POST['content_js'] ?? ''));
        $mt    = trim((string)($_POST['meta_title'] ?? ''));
        $md    = trim((string)($_POST['meta_description'] ?? ''));

        $status = (string)($_POST['status'] ?? 'draft');
        if (!in_array($status, ['published', 'draft'], true)) {
            $status = 'draft';
        }

        $nav   = !empty($_POST['show_in_nav']);
        $theme = !isset($_POST['use_theme']) || !empty($_POST['use_theme']);

        return [$title, $slug, $html, $js, $mt, $md, $status, $nav, $theme];
    }
}
