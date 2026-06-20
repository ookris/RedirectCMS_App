<?php
declare(strict_types=1);

// Ręczne załadowanie PHPMailer (bez Composer)
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailService
{
    private PrefixedPDO $pdo;
    private SettingsRepository $settings;

    public function __construct(PrefixedPDO $pdo)
    {
        $this->pdo = $pdo;
        $this->settings = new SettingsRepository($pdo);
    }

    /**
     * Wysyła email HTML przez SMTP
     *
     * @param string $to Email odbiorcy
     * @param string $subject Temat wiadomości
     * @param string $htmlBody Treść HTML
     * @param string|null $textBody Alternatywna treść tekstowa (opcjonalnie)
     * @throws Exception
     */
    public function sendEmail(string $to, string $subject, string $htmlBody, ?string $textBody = null): void
    {
        $config = $this->getSmtpConfig();

        if (!$config['enabled']) {
            throw new Exception('SMTP jest wyłączone w ustawieniach');
        }

        $mail = new PHPMailer(true);

        try {
            // Konfiguracja SMTP
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->Port = $config['port'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $this->decryptPassword($config['password_encrypted']);
            $mail->SMTPSecure = $config['encryption']; // 'tls' lub 'ssl'
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->XMailer = ' '; // Ukryj nagłówek X-Mailer (PHPMailer) — zmniejsza ryzyko odrzucenia przez filtry antyspamowe

            // Timeouts
            $mail->Timeout = 30;
            $mail->SMTPKeepAlive = false;

            // Nadawca
            $mail->setFrom($config['from_email'], $config['from_name']);

            // Odbiorca
            $mail->addAddress($to);

            // Treść
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            if ($textBody !== null) {
                $mail->AltBody = $textBody;
            } else {
                $mail->AltBody = strip_tags($htmlBody);
            }

            // Wyślij
            $mail->send();

            // Logowanie sukcesu
            $this->logEmailSent($to, $subject, true);

        } catch (PHPMailerException $e) {
            // ErrorInfo zawiera pełną odpowiedź serwera SMTP (np. kod 550 + treść odrzucenia)
            // getMessage() zwraca tylko krótki komunikat PHPMailera ("SMTP Error: data not accepted.")
            $fullError = $mail->ErrorInfo ?: $e->getMessage();

            // Logowanie błędu
            $this->logEmailSent($to, $subject, false, $fullError);

            // Utwórz powiadomienie systemowe
            try {
                require_once __DIR__ . '/NotificationRepository.php';
                $notifRepo = new NotificationRepository($this->pdo);
                $notifRepo->create(
                    'email',
                    "Błąd wysyłki email do: {$to}",
                    $fullError,
                    'error',
                    ['to' => $to, 'subject' => $subject]
                );
            } catch (Throwable $ignored) {
                // Celowo pomijamy — zapis powiadomienia o błędzie email jest best-effort;
                // pierwotny błąd zostanie rzucony ponownie poniżej.
            }

            throw new Exception('Nie udało się wysłać emaila: ' . $fullError);
        }
    }

    /**
     * Testuje połączenie SMTP bez wysyłania emaila
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection(): array
    {
        $config = $this->getSmtpConfig();

        if (!$config['enabled']) {
            return ['success' => false, 'message' => 'SMTP jest wyłączone'];
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->Port = $config['port'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $this->decryptPassword($config['password_encrypted']);
            $mail->SMTPSecure = $config['encryption'];
            $mail->Timeout = 10;

            // Próba połączenia
            if (!$mail->smtpConnect()) {
                return ['success' => false, 'message' => 'Nie można połączyć się z serwerem SMTP'];
            }

            $mail->smtpClose();

            return ['success' => true, 'message' => 'Połączenie SMTP działa poprawnie'];

        } catch (PHPMailerException $e) {
            return ['success' => false, 'message' => 'Błąd: ' . $e->getMessage()];
        }
    }

    /**
     * Pobiera konfigurację SMTP z bazy
     */
    private function getSmtpConfig(): array
    {
        return [
            'enabled' => (bool)$this->settings->get('smtp_enabled', false),
            'host' => (string)$this->settings->get('smtp_host', ''),
            'port' => (int)$this->settings->get('smtp_port', 587),
            'username' => (string)$this->settings->get('smtp_username', ''),
            'password_encrypted' => (string)$this->settings->get('smtp_password_encrypted', ''),
            'encryption' => (string)$this->settings->get('smtp_encryption', 'tls'),
            'from_email' => (string)$this->settings->get('smtp_from_email', ''),
            'from_name' => (string)$this->settings->get('smtp_from_name', 'RedirectCMS'),
        ];
    }

    /**
     * Szyfruje hasło SMTP algorytmem AES-256-CBC
     * KLUCZ SZYFROWANIA: config['encryption_key']
     */
    private function encryptPassword(string $password): string
    {
        $key = $this->getEncryptionKey();

        try {
            $iv = random_bytes(16);
        } catch (Exception $e) {
            throw new Exception('Nie można wygenerować losowego IV: ' . $e->getMessage());
        }

        $encrypted = openssl_encrypt($password, 'AES-256-CBC', $key, 0, $iv);

        if ($encrypted === false) {
            throw new Exception('Nie można zaszyfrować hasła SMTP');
        }

        // Zapisz IV + zaszyfrowane hasło jako base64
        return base64_encode($iv . $encrypted);
    }

    /**
     * Odszyfrowuje hasło SMTP
     */
    private function decryptPassword(string $encryptedPassword): string
    {
        if (empty($encryptedPassword)) {
            return '';
        }

        $key = $this->getEncryptionKey();
        $data = base64_decode($encryptedPassword);

        // Wyciągnij IV (pierwsze 16 bajtów)
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);

        if ($decrypted === false) {
            throw new Exception('Nie można odszyfrować hasła SMTP');
        }

        return $decrypted;
    }

    /**
     * Pobiera klucz szyfrowania z config.php
     */
    private function getEncryptionKey(): string
    {
        $configPath = __DIR__ . '/../config/config.php';

        if (file_exists($configPath)) {
            $config = require $configPath;

            // Preferuj dedykowany klucz
            if (!empty($config['encryption_key'])) {
                return hash('sha256', $config['encryption_key'], true);
            }

            // Fallback: użyj sekretu aplikacji
            if (!empty($config['app']['secret'])) {
                return hash('sha256', $config['app']['secret'] . ':smtp', true);
            }
        }

        // CRITICAL: W produkcji to nie powinno się zdarzyć
        throw new Exception('Brak klucza szyfrowania w config.php');
    }

    /**
     * Loguje wysłane emaile do pliku
     */
    private function logEmailSent(string $to, string $subject, bool $success, ?string $error = null): void
    {
        $status = $success ? 'SUCCESS' : 'FAILED';
        $message = sprintf(
            '[%s] EMAIL %s | to=%s | subject=%s',
            date('Y-m-d H:i:s'),
            $status,
            $to,
            $subject
        );

        if ($error !== null) {
            $message .= ' | error=' . $error;
        }

        Utils::appendLog(__DIR__ . '/../storage/logs/email.log', $message);
    }

    /**
     * Zapisuje hasło SMTP w zaszyfrowanej formie
     */
    public function saveSmtpPassword(string $plainPassword): void
    {
        $encrypted = $this->encryptPassword($plainPassword);
        $this->settings->set('smtp_password_encrypted', $encrypted);
    }
}
