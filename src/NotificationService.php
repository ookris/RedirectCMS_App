<?php
declare(strict_types=1);

class NotificationService
{
    private PrefixedPDO $pdo;
    private SettingsRepository $settings;
    private StatsRepository $stats;
    private EmailService $email;

    public function __construct(PrefixedPDO $pdo)
    {
        $this->pdo = $pdo;
        $this->settings = new SettingsRepository($pdo);
        $this->stats = new StatsRepository($pdo);
        $this->email = new EmailService($pdo);
    }

    /**
     * Pobiera strefę czasową użytkownika
     *
     * @return DateTimeZone
     */
    private function getUserTimezone(): DateTimeZone
    {
        $tzString = (string)$this->settings->get('timezone', 'Europe/Warsaw');
        try {
            return new DateTimeZone($tzString);
        } catch (Exception $e) {
            return new DateTimeZone('Europe/Warsaw');
        }
    }

    /**
     * Sprawdza czy powiadomienie powinno zostać wysłane
     *
     * @return bool
     */
    public function shouldSendNotification(): bool
    {
        // Sprawdź czy powiadomienia są włączone
        $enabled = (bool)$this->settings->get('notification_enabled', false);
        if (!$enabled) {
            return false;
        }

        // Pobierz konfigurację
        $lastSent = $this->settings->get('notification_last_sent', null);
        $frequency = (string)$this->settings->get('notification_frequency', '1');
        $customDays = (int)$this->settings->get('notification_custom_days', 7);
        $notificationTime = (string)$this->settings->get('notification_time', '09:00');

        // Oblicz liczbę dni między wysyłkami
        $intervalDays = ($frequency === 'custom') ? $customDays : (int)$frequency;

        // Pobierz strefę czasową użytkownika
        $userTz = $this->getUserTimezone();
        $now = new DateTime('now', $userTz);

        if ($lastSent === null) {
            // Nigdy nie wysłano - sprawdź czy jest już odpowiednia godzina
            $todaySendTime = new DateTime('today ' . $notificationTime, $userTz);
            return $now >= $todaySendTime;
        }

        // Parsuj ostatnią datę wysłania w strefie użytkownika
        try {
            $lastSentDateTime = new DateTime($lastSent, $userTz);
        } catch (Exception $e) {
            $lastSentDateTime = new DateTime($lastSent);
            $lastSentDateTime->setTimezone($userTz);
        }

        // Oblicz następną datę wysyłania
        $nextSendDate = clone $lastSentDateTime;
        $nextSendDate->modify("+{$intervalDays} days");
        $nextSendDate->setTime(
            (int)substr($notificationTime, 0, 2),
            (int)substr($notificationTime, 3, 2),
            0
        );

        // Jeśli aktualny czas jest większy lub równy następnemu czasowi wysłania
        return $now >= $nextSendDate;
    }

    /**
     * Generuje i wysyła powiadomienie email
     *
     * @throws Exception
     */
    public function sendNotification(): void
    {
        // Pobierz konfigurację
        $recipientEmail = (string)$this->settings->get('notification_email', '');
        $topLinksCount = (int)$this->settings->get('notification_top_links', 10);
        $frequency = (string)$this->settings->get('notification_frequency', '1');
        $customDays = (int)$this->settings->get('notification_custom_days', 7);

        if (empty($recipientEmail)) {
            throw new Exception('Brak adresu email odbiorcy powiadomień');
        }

        // Oblicz zakres dat (liczba dni)
        $days = ($frequency === 'custom') ? $customDays : (int)$frequency;

        // Pobierz dane statystyk
        $topLinks = $this->stats->getTopPerformingLinks($topLinksCount, $days);
        $globalStats = $this->stats->getGlobalStats($days, true); // exclude bots

        // Generuj treść HTML
        $htmlBody = $this->generateHtmlEmail($topLinks, $globalStats, $days);

        // Temat emaila
        $period = $days === 1 ? 'dzisiaj' : "w ostatnich {$days} dniach";
        $subject = "RedirectCMS - Podsumowanie statystyk ($period)";

        // Generuj treść tekstową (AltBody)
        $textBody = $this->generateTextEmail($topLinks, $globalStats, $days);

        // Wyślij z granularną obsługą błędów:
        // - sukces: zapisz datę wysłania
        // - błąd quota/rate limit: też zapisz (uniknij zapętlonych prób co godzinę)
        // - inne błędy (sieć, konfiguracja): NIE zapisuj (umożliwi ponowną próbę)
        try {
            $this->email->sendEmail($recipientEmail, $subject, $htmlBody, $textBody);

            $userTz = $this->getUserTimezone();
            $now = new DateTime('now', $userTz);
            $this->settings->set('notification_last_sent', $now->format('Y-m-d H:i:s'));
        } catch (\Exception $e) {
            $msg = strtolower($e->getMessage());
            // Detect quota / throttling errors from various SMTP providers (case-insensitive via strtolower).
            // Matched → save timestamp to prevent an infinite hourly retry loop.
            // Not matched (network, config errors) → do NOT save, so next cron run retries.
            // Keywords: 'quota' (hMailServer, many providers), 'rate limit'/'rate_limit' (Gmail, G-Suite),
            // 'throttl*' (throttle/throttling/throttled), 'exceeded', 'too many', 'limit reached'.
            $isQuotaError = str_contains($msg, 'quota')
                || str_contains($msg, 'rate limit')
                || str_contains($msg, 'rate_limit')
                || str_contains($msg, 'throttl')
                || str_contains($msg, 'exceeded')
                || str_contains($msg, 'too many')
                || str_contains($msg, 'limit reached');
            if ($isQuotaError) {
                $userTz = $this->getUserTimezone();
                $now = new DateTime('now', $userTz);
                $this->settings->set('notification_last_sent', $now->format('Y-m-d H:i:s'));
            }
            throw $e;
        }
    }

    /**
     * Generuje HTML email z podsumowaniem statystyk
     *
     * @param array $topLinks Top linki z kliknięciami
     * @param array $globalStats Globalne statystyki
     * @param int $days Liczba dni do analizy
     * @return string HTML
     */
    public function generateHtmlEmail(array $topLinks, array $globalStats, int $days): string
    {
        // Użyj strefy czasowej użytkownika
        $userTz = $this->getUserTimezone();
        $now = new DateTime('now', $userTz);

        // Oblicz dokładny okres
        $endDate = $now->format('Y-m-d');
        $startDateTime = clone $now;
        $startDateTime->modify('-' . ($days - 1) . ' days');
        $startDate = $startDateTime->format('Y-m-d');

        // Tekst okresu z dokładnymi datami
        if ($days === 1) {
            $periodText = 'za dzień ' . $endDate;
            $periodSubtext = $endDate;
        } else {
            $periodText = "za okres {$days} dni";
            $periodSubtext = $startDate . ' — ' . $endDate;
        }

        $dateRange = $now->format('Y-m-d H:i') . ' (' . $userTz->getName() . ')';

        // Nagłówek
        $html = '<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podsumowanie statystyk RedirectCMS</title>
    <style>
        /* Reset CSS dla klientów email */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; }

        /* Podstawowe style */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f4; color: #333333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background-color: #0B2A4A; color: #ffffff; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 30px 20px; }
        .stats-box { background-color: #F5F7FA; border-left: 4px solid #1E88E5; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .stats-box h3 { margin: 0 0 10px 0; font-size: 16px; color: #1E88E5; }
        .stats-grid { display: table; width: 100%; }
        .stat-item { display: table-cell; padding: 10px; text-align: center; }
        .stat-value { font-size: 28px; font-weight: bold; color: #0B2A4A; display: block; }
        .stat-label { font-size: 12px; color: #3A3F45; text-transform: uppercase; }

        /* Tabela linków */
        .links-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .links-table thead { background-color: #F5F7FA; }
        .links-table th { padding: 12px; text-align: left; font-size: 13px; font-weight: 600; color: #3A3F45; border-bottom: 2px solid #D0D7DE; }
        .links-table td { padding: 12px; border-bottom: 1px solid #D0D7DE; font-size: 14px; }
        .links-table tr:hover { background-color: #F5F7FA; }
        .link-title { font-weight: 600; color: #0B2A4A; }
        .link-url { color: #3A3F45; font-size: 12px; word-break: break-all; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .badge-primary { background-color: #1E88E5; color: #ffffff; }
        .badge-success { background-color: #4CAF50; color: #ffffff; }

        .footer { background-color: #F5F7FA; padding: 20px; text-align: center; color: #3A3F45; font-size: 12px; }
        .footer a { color: #1E88E5; text-decoration: none; }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .content { padding: 20px 15px; }
            .stat-item { display: block; border-bottom: 1px solid #D0D7DE; }
            .stat-item:last-child { border-bottom: none; }
            .links-table th, .links-table td { padding: 8px; font-size: 12px; }
        }
    </style>
</head>
<body>
    <table class="container" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td class="header">
                <h1>RedirectCMS - Podsumowanie Statystyk</h1>
                <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">' . htmlspecialchars($periodText) . '</p>
                <p style="margin: 3px 0 0 0; font-size: 13px; opacity: 0.7;">' . htmlspecialchars($periodSubtext) . '</p>
            </td>
        </tr>
        <tr>
            <td class="content">';

        // Sekcja statystyk globalnych
        $html .= '
                <div class="stats-box">
                    <h3>Statystyki ' . htmlspecialchars($periodText) . '</h3>
                    <table class="stats-grid" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td class="stat-item">
                                <span class="stat-value">' . number_format($globalStats['total_clicks'] ?? 0, 0, ',', ' ') . '</span>
                                <span class="stat-label">Kliknięcia</span>
                            </td>
                            <td class="stat-item">
                                <span class="stat-value">' . number_format($globalStats['unique_total'] ?? 0, 0, ',', ' ') . '</span>
                                <span class="stat-label">Unikalni</span>
                            </td>
                        </tr>
                    </table>
                </div>';

        // Sekcja top linków
        if (!empty($topLinks)) {
            $html .= '
                <h2 style="margin-top: 30px; font-size: 18px; color: #0B2A4A;">Top ' . count($topLinks) . ' najlepszych link&oacute;w</h2>
                <table class="links-table" cellpadding="0" cellspacing="0" border="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Link</th>
                            <th style="text-align: center;">Kliknięcia</th>
                            <th style="text-align: center;">Unikalni</th>
                        </tr>
                    </thead>
                    <tbody>';

            $position = 1;
            foreach ($topLinks as $link) {
                $slug = htmlspecialchars($link['slug'] ?? '');
                $pageTitle = htmlspecialchars($link['page_title'] ?? $slug);
                $targetUrl = htmlspecialchars($link['target_url'] ?? '');
                $clicks = (int)($link['clicks'] ?? 0);
                $uniqueVisitors = (int)($link['unique_visitors'] ?? 0);

                // Badge dla miejsca na podium
                $badge = '';
                if ($position === 1) {
                    $badge = ' <span class="badge badge-success">#1</span>';
                } elseif ($position === 2) {
                    $badge = ' <span class="badge badge-primary">#2</span>';
                } elseif ($position === 3) {
                    $badge = ' <span class="badge badge-primary">#3</span>';
                }

                $html .= '
                        <tr>
                            <td style="font-weight: bold; color: #3A3F45;">' . $position . '</td>
                            <td>
                                <div class="link-title">/' . $slug . $badge . '</div>
                                <div class="link-url">' . $targetUrl . '</div>
                            </td>
                            <td style="text-align: center; font-weight: bold;">' . number_format($clicks, 0, ',', ' ') . '</td>
                            <td style="text-align: center;">' . number_format($uniqueVisitors, 0, ',', ' ') . '</td>
                        </tr>';

                $position++;
            }

            $html .= '
                    </tbody>
                </table>';
        } else {
            $html .= '<p style="color: #3A3F45; text-align: center; padding: 30px 0;">Brak danych w tym okresie.</p>';
        }

        // Stopka - bezpieczne budowanie URL
        // Preferuj kanoniczny URL z ustawień (bezpieczne)
        $siteUrl = (string)$this->settings->get('site_url', '');

        if (empty($siteUrl)) {
            // Fallback: użyj Utils::getBasePath() jeśli dostępne
            $basePath = method_exists('Utils', 'getBasePath') ? Utils::getBasePath() : '';

            // Bezpieczne określenie protokołu
            $protocol = 'https'; // Domyślnie HTTPS dla bezpieczeństwa
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                $protocol = 'https';
            } elseif (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 80) {
                $protocol = 'http';
            }

            // Bezpieczny fallback dla hosta (nie używaj HTTP_HOST bezpośrednio)
            $host = 'localhost';
            if (isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) {
                // SERVER_NAME jest bezpieczniejsze niż HTTP_HOST
                $host = $_SERVER['SERVER_NAME'];
            }

            $adminUrl = $protocol . '://' . $host . rtrim($basePath, '/') . '/admin/';
        } else {
            // Użyj kanonicznego URL z ustawień
            $adminUrl = rtrim($siteUrl, '/') . '/admin/';
        }

        $html .= '
            </td>
        </tr>
        <tr>
            <td class="footer">
                <p style="margin: 0 0 10px 0;">To automatyczne powiadomienie z systemu RedirectCMS</p>
                <p style="margin: 0;"><a href="' . htmlspecialchars($adminUrl) . '">Przejdź do panelu administracyjnego</a></p>
                <p style="margin: 10px 0 0 0; font-size: 11px; color: #adb5bd;">Wygenerowano: ' . $dateRange . '</p>
            </td>
        </tr>
    </table>
</body>
</html>';

        return $html;
    }

    /**
     * Generuje tekstową wersję emaila (AltBody)
     *
     * @param array $topLinks Top linki z kliknięciami
     * @param array $globalStats Globalne statystyki
     * @param int $days Liczba dni do analizy
     * @return string Czytelny tekst
     */
    private function generateTextEmail(array $topLinks, array $globalStats, int $days): string
    {
        $userTz = $this->getUserTimezone();
        $now = new DateTime('now', $userTz);

        $endDate = $now->format('Y-m-d');
        $startDateTime = clone $now;
        $startDateTime->modify('-' . ($days - 1) . ' days');
        $startDate = $startDateTime->format('Y-m-d');

        if ($days === 1) {
            $periodText = 'za dzień ' . $endDate;
        } else {
            $periodText = "za okres {$days} dni ({$startDate} - {$endDate})";
        }

        $lines = [];
        $lines[] = 'RedirectCMS - Podsumowanie Statystyk';
        $lines[] = $periodText;
        $lines[] = str_repeat('-', 40);
        $lines[] = '';
        $lines[] = 'STATYSTYKI GLOBALNE';
        $lines[] = '  Kliknięcia: ' . number_format($globalStats['total_clicks'] ?? 0, 0, ',', ' ');
        $lines[] = '  Unikalni:   ' . number_format($globalStats['unique_total'] ?? 0, 0, ',', ' ');
        $lines[] = '';

        if (!empty($topLinks)) {
            $lines[] = 'TOP ' . count($topLinks) . ' NAJLEPSZYCH LINKÓW';
            $lines[] = str_repeat('-', 40);

            $position = 1;
            foreach ($topLinks as $link) {
                $slug = $link['slug'] ?? '';
                $clicks = (int)($link['clicks'] ?? 0);
                $unique = (int)($link['unique_visitors'] ?? 0);
                $targetUrl = $link['target_url'] ?? '';

                $medal = $position <= 3 ? " [#{$position}]" : '';
                $lines[] = sprintf(
                    '%d. /%s%s - Kliknięcia: %s, Unikalni: %s',
                    $position,
                    $slug,
                    $medal,
                    number_format($clicks, 0, ',', ' '),
                    number_format($unique, 0, ',', ' ')
                );
                if (!empty($targetUrl)) {
                    $lines[] = '   -> ' . $targetUrl;
                }

                $position++;
            }
        } else {
            $lines[] = 'Brak danych w tym okresie.';
        }

        $lines[] = '';
        $lines[] = str_repeat('-', 40);
        $lines[] = 'To automatyczne powiadomienie z systemu RedirectCMS';
        $lines[] = 'Wygenerowano: ' . $now->format('Y-m-d H:i') . ' (' . $userTz->getName() . ')';

        return implode("\r\n", $lines);
    }
}
