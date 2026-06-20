<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../CategoryRepository.php';
require_once __DIR__ . '/../TagRepository.php';
require_once __DIR__ . '/../AffiliateProgramRepository.php';
require_once __DIR__ . '/../ImageUploader.php';

class TaxonomyController extends BaseController
{
    // ========== PROGRAMY AFILIACYJNE ==========

    public function affiliateProgramsGet(): void
    {
        Utils::requireLogin();
        $repo = new AffiliateProgramRepository($this->pdo);
        $programs = $repo->listAll();

        $this->view('affiliate_programs', [
            'programs' => $programs,
            'csrf' => Utils::csrfToken(),
            'success' => $_SESSION['affiliate_success'] ?? null,
            'error' => $_SESSION['affiliate_error'] ?? null,
        ]);
        unset($_SESSION['affiliate_success'], $_SESSION['affiliate_error']);
    }

    public function affiliateProgramNewGet(): void
    {
        Utils::requireLogin();
        $this->view('affiliate_program_form', [
            'csrf' => Utils::csrfToken(),
            'mode' => 'create',
            'program' => ['name' => '', 'color' => '#4CAF50'],
        ]);
    }

    public function affiliateProgramNewPost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $color = trim((string)($_POST['color'] ?? '#4CAF50'));

        if ($name === '') {
            Utils::startSession();
            $_SESSION['affiliate_error'] = 'Nazwa programu jest wymagana';
            header('Location: ' . $this->basePath . '/admin/index.php?action=affiliate_programs');
            return;
        }

        try {
            $repo = new AffiliateProgramRepository($this->pdo);

            $repo->create($name, $color);
            Utils::startSession();
            $_SESSION['affiliate_success'] = 'Program został utworzony';
        } catch (\Throwable $e) {
            Utils::startSession();
            $_SESSION['affiliate_error'] = $e->getMessage();
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=affiliate_programs');
    }

    public function affiliateProgramEditGet(int $id): void
    {
        Utils::requireLogin();
        $repo = new AffiliateProgramRepository($this->pdo);
        $program = $repo->getById($id);
        if (!$program) {
            http_response_code(404);
            echo 'Nie znaleziono programu';
            return;
        }

        $this->view('affiliate_program_form', [
            'csrf' => Utils::csrfToken(),
            'mode' => 'edit',
            'program' => $program,
        ]);
    }

    public function affiliateProgramEditPost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $color = trim((string)($_POST['color'] ?? '#4CAF50'));

        if ($name === '') {
            Utils::startSession();
            $_SESSION['affiliate_error'] = 'Nazwa programu jest wymagana';
            header('Location: ' . $this->basePath . '/admin/index.php?action=affiliate_programs');
            return;
        }

        try {
            $repo = new AffiliateProgramRepository($this->pdo);
            $repo->update($id, $name, $color);
            Utils::startSession();
            $_SESSION['affiliate_success'] = 'Program został zaktualizowany';
        } catch (\Throwable $e) {
            Utils::startSession();
            $_SESSION['affiliate_error'] = $e->getMessage();
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=affiliate_programs');
    }

    public function affiliateProgramDeletePost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $repo = new AffiliateProgramRepository($this->pdo);
        $repo->delete($id);

        Utils::startSession();
        $_SESSION['affiliate_success'] = 'Program został usunięty';
        header('Location: ' . $this->basePath . '/admin/index.php?action=affiliate_programs');
    }

    // ========== KATEGORIE ==========

    public function categoriesGet(): void
    {
        Utils::requireLogin();
        $repo = new CategoryRepository($this->pdo);
        $categories = $repo->list();
        $this->view('categories', [
            'categories' => $categories,
            'csrf' => Utils::csrfToken(),
            'success' => $_SESSION['category_success'] ?? null,
            'error' => $_SESSION['category_error'] ?? null,
        ]);
        unset($_SESSION['category_success'], $_SESSION['category_error']);
    }

    public function categoryNewGet(): void
    {
        Utils::requireLogin();
        $this->view('category_form', [
            'csrf' => Utils::csrfToken(),
            'mode' => 'create',
            'category' => ['name' => '', 'slug' => '', 'description' => '', 'color' => '#3A3F45'],
        ]);
    }

    public function categoryNewPost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $color = trim((string)($_POST['color'] ?? '#3A3F45'));

        if (empty($name)) {
            Utils::startSession();
            $_SESSION['category_error'] = 'Nazwa kategorii jest wymagana';
            header('Location: ' . $this->basePath . '/admin/index.php?action=categories');
            return;
        }

        if (empty($slug)) {
            $slug = Utils::sanitizeSlug($name, 100);
        } else {
            $slug = Utils::sanitizeSlug($slug, 100);
        }

        $repo = new CategoryRepository($this->pdo);
        if ($repo->slugExists($slug)) {
            Utils::startSession();
            $_SESSION['category_error'] = 'Alias już istnieje';
            header('Location: ' . $this->basePath . '/admin/index.php?action=categories');
            return;
        }

        $iconImage = null;
        $iconImageThumb = null;

        if (isset($_FILES['category_icon']) && $_FILES['category_icon']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $uploader = new ImageUploader(__DIR__ . '/../../uploads');
                $result = $uploader->uploadCategoryIcon($_FILES['category_icon']);
                $iconImage = $result['icon_path'];
                $iconImageThumb = $result['thumb_path'];
            } catch (\Exception $e) {
                Utils::startSession();
                $_SESSION['category_error'] = 'Błąd podczas uploadu ikony: ' . $e->getMessage();
                header('Location: ' . $this->basePath . '/admin/index.php?action=categories');
                return;
            }
        }

        $repo->create($name, $slug, $description ?: null, $color, $iconImage, $iconImageThumb);
        Utils::startSession();
        $_SESSION['category_success'] = 'Kategoria została utworzona';
        header('Location: ' . $this->basePath . '/admin/index.php?action=categories');
    }

    public function categoryEditGet(int $id): void
    {
        Utils::requireLogin();
        $repo = new CategoryRepository($this->pdo);
        $category = $repo->getById($id);
        if (!$category) {
            http_response_code(404);
            echo 'Nie znaleziono kategorii';
            return;
        }
        $this->view('category_form', [
            'csrf' => Utils::csrfToken(),
            'mode' => 'edit',
            'category' => $category,
        ]);
    }

    public function categoryEditPost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $color = trim((string)($_POST['color'] ?? '#3A3F45'));

        if (empty($name) || empty($slug)) {
            Utils::startSession();
            $_SESSION['category_error'] = 'Nazwa i alias są wymagane';
            header('Location: ' . $this->basePath . '/admin/index.php?action=categories');
            return;
        }

        try {
            $slug = Utils::sanitizeSlug($slug, 100);
        } catch (\InvalidArgumentException $e) {
            Utils::startSession();
            $_SESSION['category_error'] = $e->getMessage();
            header('Location: ' . $this->basePath . '/admin/index.php?action=categories');
            return;
        }

        $repo = new CategoryRepository($this->pdo);
        if ($repo->slugExists($slug, $id)) {
            Utils::startSession();
            $_SESSION['category_error'] = 'Alias już istnieje';
            header('Location: ' . $this->basePath . '/admin/index.php?action=categories');
            return;
        }

        $category = $repo->getById($id);
        $iconImage = $category['icon_image'] ?? null;
        $iconImageThumb = $category['icon_image_thumb'] ?? null;

        if (isset($_POST['delete_icon']) && $_POST['delete_icon'] === '1' && ($iconImage || $iconImageThumb)) {
            $uploader = new ImageUploader(__DIR__ . '/../../uploads');
            $uploader->deleteCategoryIcon($iconImage, $iconImageThumb);
            $iconImage = null;
            $iconImageThumb = null;
        }

        if (isset($_FILES['category_icon']) && $_FILES['category_icon']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $uploader = new ImageUploader(__DIR__ . '/../../uploads');

                if ($iconImage || $iconImageThumb) {
                    $uploader->deleteCategoryIcon($iconImage, $iconImageThumb);
                }

                $result = $uploader->uploadCategoryIcon($_FILES['category_icon']);
                $iconImage = $result['icon_path'];
                $iconImageThumb = $result['thumb_path'];
            } catch (\Exception $e) {
                Utils::startSession();
                $_SESSION['category_error'] = 'Błąd podczas uploadu ikony: ' . $e->getMessage();
                header('Location: ' . $this->basePath . '/admin/index.php?action=categories');
                return;
            }
        }

        $repo->update($id, $name, $slug, $description ?: null, $color, $iconImage, $iconImageThumb);
        Utils::startSession();
        $_SESSION['category_success'] = 'Kategoria została zaktualizowana';
        header('Location: ' . $this->basePath . '/admin/index.php?action=categories');
    }

    public function categoryDeletePost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $repo = new CategoryRepository($this->pdo);

        $category = $repo->getById($id);
        if ($category && ($category['icon_image'] ?? null || $category['icon_image_thumb'] ?? null)) {
            $uploader = new ImageUploader(__DIR__ . '/../../uploads');
            $uploader->deleteCategoryIcon($category['icon_image'] ?? null, $category['icon_image_thumb'] ?? null);
        }

        $repo->delete($id);
        Utils::startSession();
        $_SESSION['category_success'] = 'Kategoria została usunięta';
        header('Location: ' . $this->basePath . '/admin/index.php?action=categories');
    }

    // ========== TAGI ==========

    public function tagsGet(): void
    {
        Utils::requireLogin();
        $repo = new TagRepository($this->pdo);
        $tags = $repo->list();
        $this->view('tags', [
            'tags' => $tags,
            'csrf' => Utils::csrfToken(),
            'success' => $_SESSION['tag_success'] ?? null,
            'error' => $_SESSION['tag_error'] ?? null,
        ]);
        unset($_SESSION['tag_success'], $_SESSION['tag_error']);
    }

    public function tagNewGet(): void
    {
        Utils::requireLogin();
        $this->view('tag_form', [
            'csrf' => Utils::csrfToken(),
            'mode' => 'create',
            'tag' => ['name' => '', 'slug' => ''],
        ]);
    }

    public function tagNewPost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));

        if (empty($name)) {
            Utils::startSession();
            $_SESSION['tag_error'] = 'Nazwa tagu jest wymagana';
            header('Location: ' . $this->basePath . '/admin/index.php?action=tags');
            return;
        }

        if (empty($slug)) {
            $slug = Utils::sanitizeSlug($name, 100);
        } else {
            $slug = Utils::sanitizeSlug($slug, 100);
        }

        $repo = new TagRepository($this->pdo);
        if ($repo->slugExists($slug)) {
            Utils::startSession();
            $_SESSION['tag_error'] = 'Alias już istnieje';
            header('Location: ' . $this->basePath . '/admin/index.php?action=tags');
            return;
        }

        $repo->create($name, $slug);
        Utils::startSession();
        $_SESSION['tag_success'] = 'Tag został utworzony';
        header('Location: ' . $this->basePath . '/admin/index.php?action=tags');
    }

    public function tagEditGet(int $id): void
    {
        Utils::requireLogin();
        $repo = new TagRepository($this->pdo);
        $tag = $repo->getById($id);
        if (!$tag) {
            http_response_code(404);
            echo 'Nie znaleziono tagu';
            return;
        }
        $this->view('tag_form', [
            'csrf' => Utils::csrfToken(),
            'mode' => 'edit',
            'tag' => $tag,
        ]);
    }

    public function tagEditPost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));

        if (empty($name) || empty($slug)) {
            Utils::startSession();
            $_SESSION['tag_error'] = 'Nazwa i alias są wymagane';
            header('Location: ' . $this->basePath . '/admin/index.php?action=tags');
            return;
        }

        try {
            $slug = Utils::sanitizeSlug($slug, 100);
        } catch (\InvalidArgumentException $e) {
            Utils::startSession();
            $_SESSION['tag_error'] = $e->getMessage();
            header('Location: ' . $this->basePath . '/admin/index.php?action=tags');
            return;
        }

        $repo = new TagRepository($this->pdo);
        if ($repo->slugExists($slug, $id)) {
            Utils::startSession();
            $_SESSION['tag_error'] = 'Alias już istnieje';
            header('Location: ' . $this->basePath . '/admin/index.php?action=tags');
            return;
        }

        $repo->update($id, $name, $slug);
        Utils::startSession();
        $_SESSION['tag_success'] = 'Tag został zaktualizowany';
        header('Location: ' . $this->basePath . '/admin/index.php?action=tags');
    }

    public function tagDeletePost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $repo = new TagRepository($this->pdo);
        $repo->delete($id);
        Utils::startSession();
        $_SESSION['tag_success'] = 'Tag został usunięty';
        header('Location: ' . $this->basePath . '/admin/index.php?action=tags');
    }
}
