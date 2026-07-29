<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../SettingsRepository.php';
require_once __DIR__ . '/../TwoFactorAuth.php';
require_once __DIR__ . '/../AuditService.php';
require_once __DIR__ . '/../LoginRateLimiter.php';

class AuthController extends BaseController
{
    private const REMEMBER_ME_COOKIE = 'remember_token';
    private const REMEMBER_ME_DAYS = 30;

    private function rateLimiter(): LoginRateLimiter
    {
        return new LoginRateLimiter($this->pdo);
    }

    private function setRememberMeToken(SettingsRepository $repo): void
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $token = $selector . ':' . $validator;

        $expiry = time() + (self::REMEMBER_ME_DAYS * 24 * 60 * 60);
        $repo->set('remember_me_selector', $selector);
        $repo->set('remember_me_validator_hash', password_hash($validator, PASSWORD_DEFAULT));
        $repo->set('remember_me_expiry', $expiry);

        $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        setcookie(
            self::REMEMBER_ME_COOKIE,
            $token,
            [
                'expires' => $expiry,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    private function clearRememberMeToken(): void
    {
        $repo = new SettingsRepository($this->pdo);
        $repo->delete('remember_me_selector');
        $repo->delete('remember_me_validator_hash');
        $repo->delete('remember_me_expiry');

        if (isset($_COOKIE[self::REMEMBER_ME_COOKIE])) {
            setcookie(
                self::REMEMBER_ME_COOKIE,
                '',
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'domain' => '',
                    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        }
    }

    private function validatePasswordStrength(string $password): ?string
    {
        if (strlen($password) < 12) {
            return 'Hasło musi mieć minimum 12 znaków';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'Hasło musi zawierać małą literę';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Hasło musi zawierać wielką literę';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'Hasło musi zawierać cyfrę';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return 'Hasło musi zawierać znak specjalny';
        }
        return null;
    }

    public function loginGet(): void
    {
        $licStatus = $this->getLicenseStatus();
        $this->view('login', [
            'csrf'           => Utils::csrfToken(),
            'error'          => null,
            'licenseBlocked' => $licStatus['state'] === 'blocked',
            'licenseMessage' => $licStatus['message'],
        ]);
    }

    public function loginPost(): void
    {
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        // Blokada logowania gdy licencja nieważna 48h+
        $licStatus = $this->getLicenseStatus();
        if ($licStatus['state'] === 'blocked') {
            $this->view('login', [
                'csrf'           => Utils::csrfToken(),
                'error'          => null,
                'licenseBlocked' => true,
                'licenseMessage' => $licStatus['message'],
            ]);
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if ($error = $this->rateLimiter()->check($ip)) {
            $this->view('login', ['csrf' => Utils::csrfToken(), 'error' => $error]);
            return;
        }

        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (!$username || !$password) {
            $this->view('login', ['csrf' => Utils::csrfToken(), 'error' => 'Wprowadź użytkownika i hasło']);
            return;
        }

        $repo = new SettingsRepository($this->pdo);
        $storedUsername = $repo->get('admin_username', null);
        $storedHash = $repo->get('admin_password_hash', null);

        if (!$storedUsername || !$storedHash) {
            // Brak skonfigurowanych danych logowania — instalacja nie została ukończona.
            // NIE tworzymy domyślnych danych admin/admin: znane credentials to wektor ataku.
            $this->view('login', ['csrf' => Utils::csrfToken(), 'error' => 'Dane logowania nie są skonfigurowane. Uruchom instalację.']);
            return;
        }

        if ($username === $storedUsername && password_verify($password, $storedHash)) {
            Utils::startSession();
            $this->rateLimiter()->reset($ip);
            session_regenerate_id(true);

            $twoFactorSecret = $repo->get('two_factor_secret', '');
            if (!empty($twoFactorSecret)) {
                $_SESSION['2fa_pending'] = true;
                $_SESSION['2fa_remember_me'] = !empty($_POST['remember_me']);
                header('Location: ' . $this->basePath . '/admin/index.php?action=two_factor_verify');
                exit;
            }

            $_SESSION['admin_logged'] = true;

            if ($username === 'admin' && password_verify('admin', $storedHash)) {
                $_SESSION['must_change_password'] = true;
                $_SESSION['password_warning'] = 'UWAGA: Używasz domyślnego hasła admin/admin. Ze względów bezpieczeństwa MUSISZ je zmienić!';
            }

            if (!empty($_POST['remember_me'])) {
                $this->setRememberMeToken($repo);
            }

            $audit = new AuditService($this->pdo);
            $audit->logLogin($username);

            header('Location: ' . $this->basePath . '/admin/index.php');
        } else {
            $this->rateLimiter()->record($ip);

            $audit = new AuditService($this->pdo);
            $audit->logFailedLogin($username);

            $this->view('login', ['csrf' => Utils::csrfToken(), 'error' => 'Nieprawidłowe dane logowania']);
        }
    }

    public function logout(): void
    {
        Utils::startSession();

        $audit = new AuditService($this->pdo);
        $audit->logLogout();

        $this->clearRememberMeToken();

        $_SESSION = [];
        session_destroy();
        header('Location: ' . $this->basePath . '/admin/index.php?action=login');
    }

    // ── 2FA Methods ──────────────────────────────────────────────

    public function twoFactorSetupGet(): void
    {
        Utils::requireLogin();
        $repo = new SettingsRepository($this->pdo);
        $twoFactorEnabled = !empty($repo->get('two_factor_secret', ''));

        if ($twoFactorEnabled) {
            $backupCodes = $repo->get('two_factor_backup_codes', []);
            if (is_string($backupCodes)) $backupCodes = json_decode($backupCodes, true) ?: [];
            $unusedBackupCount = count(array_filter($backupCodes, fn($c) => !$c['used']));

            $this->view('two_factor_setup', [
                'twoFactorEnabled' => true,
                'backupCodes' => $backupCodes,
                'unusedBackupCount' => $unusedBackupCount,
            ]);
        } else {
            $secret = TwoFactorAuth::generateSecret();
            $username = $repo->get('admin_username', 'admin');
            $otpUri = TwoFactorAuth::getOtpAuthUri($secret, $username);

            $this->view('two_factor_setup', [
                'twoFactorEnabled' => false,
                'secret' => $secret,
                'otpUri' => $otpUri,
                'error' => null,
            ]);
        }
    }

    public function twoFactorEnablePost(): void
    {
        Utils::requireLogin();

        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=two_factor_setup');
            exit;
        }

        $secret = trim((string)($_POST['secret'] ?? ''));
        $code = trim((string)($_POST['code'] ?? ''));

        if (empty($secret) || !TwoFactorAuth::verifyCode($secret, $code)) {
            $repo = new SettingsRepository($this->pdo);
            $username = $repo->get('admin_username', 'admin');
            $otpUri = TwoFactorAuth::getOtpAuthUri($secret, $username);

            $this->view('two_factor_setup', [
                'twoFactorEnabled' => false,
                'secret' => $secret,
                'otpUri' => $otpUri,
                'error' => 'Nieprawidłowy kod. Sprawdź czy czas w telefonie jest zsynchronizowany.',
            ]);
            return;
        }

        $repo = new SettingsRepository($this->pdo);
        $repo->set('two_factor_secret', $secret);

        $rawCodes = TwoFactorAuth::generateBackupCodes();
        $backupCodes = array_map(fn($c) => ['code' => $c, 'used' => false], $rawCodes);
        $repo->set('two_factor_backup_codes', json_encode($backupCodes));

        $audit = new AuditService($this->pdo);
        $audit->log('settings_change', null, null, '2FA', null, ['enabled' => true]);

        $_SESSION['toast_message'] = '2FA zostało włączone. Zapisz kody zapasowe w bezpiecznym miejscu!';
        $_SESSION['toast_type'] = 'success';
        header('Location: ' . $this->basePath . '/admin/index.php?action=two_factor_setup');
        exit;
    }

    public function twoFactorDisablePost(): void
    {
        Utils::requireLogin();

        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=two_factor_setup');
            exit;
        }

        $repo = new SettingsRepository($this->pdo);
        $repo->delete('two_factor_secret');
        $repo->delete('two_factor_backup_codes');

        $audit = new AuditService($this->pdo);
        $audit->log('settings_change', null, null, '2FA', null, ['enabled' => false]);

        $_SESSION['toast_message'] = '2FA zostało wyłączone.';
        $_SESSION['toast_type'] = 'success';
        header('Location: ' . $this->basePath . '/admin/index.php?action=settings');
        exit;
    }

    public function twoFactorRegenerateBackupPost(): void
    {
        Utils::requireLogin();

        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=two_factor_setup');
            exit;
        }

        $rawCodes = TwoFactorAuth::generateBackupCodes();
        $backupCodes = array_map(fn($c) => ['code' => $c, 'used' => false], $rawCodes);

        $repo = new SettingsRepository($this->pdo);
        $repo->set('two_factor_backup_codes', json_encode($backupCodes));

        $_SESSION['toast_message'] = 'Wygenerowano nowe kody zapasowe. Zapisz je w bezpiecznym miejscu!';
        $_SESSION['toast_type'] = 'success';
        header('Location: ' . $this->basePath . '/admin/index.php?action=two_factor_setup');
        exit;
    }

    public function twoFactorVerifyGet(): void
    {
        Utils::startSession();

        if (empty($_SESSION['2fa_pending'])) {
            header('Location: ' . $this->basePath . '/admin/index.php?action=login');
            exit;
        }

        $this->view('two_factor_verify', ['error' => null]);
    }

    public function twoFactorVerifyPost(): void
    {
        Utils::startSession();

        if (empty($_SESSION['2fa_pending'])) {
            header('Location: ' . $this->basePath . '/admin/index.php?action=login');
            exit;
        }

        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $this->view('two_factor_verify', ['error' => 'Błąd weryfikacji CSRF']);
            return;
        }

        // Osobny licznik prób niż logowanie hasłem (limiter loginu jest resetowany
        // zaraz po podaniu poprawnego hasła, więc bez tego nic by tu nie ograniczało
        // liczby prób odgadnięcia kodu 2FA).
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateLimitKey = '2fa:' . $ip;
        if ($error = $this->rateLimiter()->check($rateLimitKey)) {
            $this->view('two_factor_verify', ['error' => $error]);
            return;
        }

        $code = trim((string)($_POST['code'] ?? ''));
        $repo = new SettingsRepository($this->pdo);
        $secret = $repo->get('two_factor_secret', '');

        $verified = false;

        if (strlen($code) === 6 && ctype_digit($code)) {
            $verified = TwoFactorAuth::verifyCode($secret, $code);
        }

        if (!$verified) {
            $backupCodes = $repo->get('two_factor_backup_codes', []);
            if (is_string($backupCodes)) $backupCodes = json_decode($backupCodes, true) ?: [];
            $codeUpper = strtoupper($code);

            foreach ($backupCodes as &$bc) {
                if (!$bc['used'] && strtoupper($bc['code']) === $codeUpper) {
                    $bc['used'] = true;
                    $verified = true;
                    $repo->set('two_factor_backup_codes', json_encode($backupCodes));
                    break;
                }
            }
            unset($bc);
        }

        if (!$verified) {
            $this->rateLimiter()->record($rateLimitKey);
            $this->view('two_factor_verify', ['error' => 'Nieprawidłowy kod. Spróbuj ponownie.']);
            return;
        }

        $this->rateLimiter()->reset($rateLimitKey);
        unset($_SESSION['2fa_pending']);
        $_SESSION['admin_logged'] = true;

        if (!empty($_SESSION['2fa_remember_me'])) {
            $this->setRememberMeToken($repo);
            unset($_SESSION['2fa_remember_me']);
        }

        $storedHash = $repo->get('admin_password_hash', '');
        if (password_verify('admin', $storedHash)) {
            $_SESSION['must_change_password'] = true;
            $_SESSION['password_warning'] = 'UWAGA: Używasz domyślnego hasła admin/admin. Ze względów bezpieczeństwa MUSISZ je zmienić!';
        }

        $audit = new AuditService($this->pdo);
        $audit->logLogin($repo->get('admin_username', 'admin'));

        header('Location: ' . $this->basePath . '/admin/index.php');
    }
}
