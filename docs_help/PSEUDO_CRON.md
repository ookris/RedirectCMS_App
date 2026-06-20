# Pseudo-Cron System

## 📋 Opis

Pseudo-Cron to system kolejki zadań, który wykonuje zadania cykliczne przy każdym wejściu na stronę (publiczną lub panel admina), bez potrzeby konfigurowania prawdziwego crona na serwerze. Jest to idealne rozwiązanie dla hostingów współdzielonych, które nie oferują dostępu do crona.

## ⚙️ Jak to działa?

1. **Automatyczne uruchamianie**: Przy każdym wejściu na stronę (`index.php`) system sprawdza, czy są zadania do wykonania
2. **Wykonanie w tle**: Zadania są wykonywane asynchronicznie, nie blokując użytkownika
3. **Blokady**: System używa plików blokad (`storage/locks/`), aby zapobiec wielokrotnemu uruchomieniu tego samego zadania
4. **Logowanie**: Wszystkie wykonania są logowane do bazy danych z informacją o statusie, czasie wykonania i błędach

## 📁 Struktura

### Pliki

- `src/PseudoCron.php` - główna klasa zarządzająca kolejką zadań
- `src/CronTasks.php` - klasa z callbackami dla zadań
- `migrations/009_create_cron_jobs.sql` - migracja SQL tworząca tabele `cron_jobs` i `cron_logs`
- `storage/locks/` - katalog na pliki blokad (automatycznie tworzony)

### Tabele w bazie danych

#### `cron_jobs`
Przechowuje zarejestrowane zadania cykliczne.

| Kolumna | Typ | Opis |
|---------|-----|------|
| `id` | INT | ID zadania |
| `name` | VARCHAR(100) | Unikalna nazwa zadania |
| `description` | TEXT | Opis zadania |
| `last_run` | DATETIME | Data ostatniego wykonania |
| `next_run` | DATETIME | Data następnego wykonania |
| `interval_seconds` | INT | Interwał w sekundach |
| `is_active` | TINYINT(1) | Czy zadanie jest aktywne (1/0) |
| `callback_class` | VARCHAR(255) | Nazwa klasy z callbackiem |
| `callback_method` | VARCHAR(100) | Nazwa metody do wywołania |

#### `cron_logs`
Przechowuje historię wykonań zadań.

| Kolumna | Typ | Opis |
|---------|-----|------|
| `id` | INT | ID logu |
| `job_id` | INT | ID zadania |
| `started_at` | DATETIME | Czas rozpoczęcia |
| `finished_at` | DATETIME | Czas zakończenia |
| `status` | ENUM | Status: 'running', 'success', 'error' |
| `error_message` | TEXT | Komunikat błędu (jeśli wystąpił) |
| `execution_time_ms` | INT | Czas wykonania w milisekundach |

## 🚀 Instalacja

### 1. Uruchom migrację SQL

```bash
mysql -u user -p database < migrations/009_create_cron_jobs.sql
```

### 2. Zarejestruj domyślne zadania

W panelu administracyjnym:
1. Przejdź do **Cron** (w menu nawigacji)
2. Kliknij **"Zarejestruj domyślne zadania"**

Lub ręcznie w kodzie:
```php
$pseudoCron = new PseudoCron($pdo);

// Czyszczenie cache geo (co 24h)
$pseudoCron->registerJob(
    'clean_geo_cache',
    'Czyszczenie starego cache geolokalizacji (starszego niż 30 dni)',
    86400, // 24 godziny
    'CronTasks',
    'cleanGeoCache',
    true
);

// Odświeżanie cache statystyk (co 30 min)
$pseudoCron->registerJob(
    'refresh_global_stats',
    'Odświeżanie cache statystyk globalnych',
    1800, // 30 minut
    'CronTasks',
    'refreshGlobalStatsCache',
    true
);
```

## 📝 Domyślne zadania

System rejestruje następujące domyślne zadania:

| Zadanie | Interwał | Opis |
|---------|----------|------|
| `clean_geo_cache` | 24h | Usuwa wpisy cache geolokalizacji starsze niż 30 dni |
| `refresh_global_stats` | 30 min | Odświeża cache statystyk globalnych dla różnych przedziałów czasowych |
| `clean_cron_logs` | 7 dni | Usuwa logi cron starsze niż 30 dni |
| `clean_old_events` | 30 dni | Usuwa wydarzenia (events) starsze niż 90 dni (domyślnie wyłączone) |

## 🛠️ Tworzenie własnych zadań

### 1. Dodaj metodę do klasy `CronTasks`

```php
// src/CronTasks.php
class CronTasks
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Twoje zadanie
     * Wykonywane: Co X sekund/minut/godzin
     */
    public function mojeZadanie(): void
    {
        // Twoja logika
        $result = someOperation();
        
        // Opcjonalnie: loguj do pliku
        $logMessage = sprintf(
            '[%s] CRON | moje_zadanie | result=%s',
            date('Y-m-d H:i:s'),
            $result
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
    }
}
```

### 2. Zarejestruj zadanie

```php
$pseudoCron = new PseudoCron($pdo);

$pseudoCron->registerJob(
    'moje_zadanie',                    // Unikalna nazwa
    'Opis mojego zadania',             // Opis
    3600,                               // Interwał w sekundach (1 godzina)
    'CronTasks',                        // Klasa
    'mojeZadanie',                      // Metoda
    true                                // Aktywne od razu
);
```

## 💻 Panel administracyjny

W panelu admina (`/admin/index.php?action=cron_jobs`) możesz:

- ✅ Przeglądać wszystkie zarejestrowane zadania
- ⏸️ Włączać/wyłączać zadania
- ▶️ Wymusić natychmiastowe wykonanie zadania
- 📊 Przeglądać historię wykonań (logi)
- 🗑️ Usuwać zadania
- 📈 Monitorować statystyki (liczba wykonań, błędów, średni czas)

## 🔧 API klasy PseudoCron

### Główne metody

#### `tick(): void`
Sprawdza i wykonuje zadania. Wywoływane automatycznie w `index.php`.

```php
$pseudoCron = new PseudoCron($pdo);
$pseudoCron->tick();
```

#### `registerJob(): void`
Rejestruje nowe zadanie lub aktualizuje istniejące.

```php
$pseudoCron->registerJob(
    string $name,              // Unikalna nazwa
    string $description,       // Opis
    int $intervalSeconds,      // Interwał w sekundach
    string $callbackClass,     // Nazwa klasy
    string $callbackMethod,    // Nazwa metody
    bool $isActive = true      // Czy aktywne
);
```

#### `getAllJobs(): array`
Zwraca wszystkie zadania ze statystykami.

```php
$jobs = $pseudoCron->getAllJobs();
// Zwraca: id, name, description, last_run, next_run, interval_seconds, 
//         is_active, total_runs, error_count, avg_execution_time
```

#### `getJobLogs(int $jobId, int $limit = 50): array`
Zwraca logi dla zadania.

```php
$logs = $pseudoCron->getJobLogs($jobId, 100);
```

#### `toggleJob(int $jobId, bool $isActive): void`
Włącza/wyłącza zadanie.

```php
$pseudoCron->toggleJob($jobId, false); // Wyłącz
```

#### `runJobNow(int $jobId): void`
Ustawia `next_run` na teraz - zadanie wykona się przy następnym wejściu na stronę.

```php
$pseudoCron->runJobNow($jobId);
```

#### `deleteJob(int $jobId): void`
Usuwa zadanie (wraz z logami).

```php
$pseudoCron->deleteJob($jobId);
```

#### `cleanOldLogs(int $days = 30): int`
Usuwa logi starsze niż X dni. Zwraca liczbę usuniętych rekordów.

```php
$deleted = $pseudoCron->cleanOldLogs(30);
```

## 🔒 Mechanizm blokad

System używa plików blokad w `storage/locks/`, aby zapobiec wielokrotnemu uruchomieniu tego samego zadania:

1. Przed wykonaniem zadania tworzony jest plik `cron_{job_id}.lock`
2. Jeśli plik istnieje i jest młodszy niż 5 minut, zadanie nie jest uruchamiane
3. Po zakończeniu zadania plik blokady jest usuwany
4. Jeśli blokada jest starsza niż 5 minut, jest automatycznie usuwana (zakładamy, że proces padł)

## ⚠️ Ważne uwagi

### Wydajność
- Zadania wykonują się **przy każdym wejściu na stronę**, ale nie blokują użytkownika
- System sprawdza tylko czy są zadania do wykonania (szybkie zapytanie SQL)
- Faktyczne wykonanie zadania odbywa się "w tle" (w tym samym procesie PHP, ale po wysłaniu odpowiedzi do użytkownika)

### Ograniczenia
- To **nie jest prawdziwy cron** - zadania wykonują się tylko gdy ktoś wchodzi na stronę
- Jeśli strona nie ma ruchu, zadania się nie wykonają
- Dla stron z bardzo małym ruchem rozważ użycie prawdziwego crona lub zewnętrznego serwisu (np. cron-job.org)

### Alternatywa: Prawdziwy cron
Jeśli masz dostęp do crona, możesz utworzyć zadanie wywołujące endpoint:

```bash
# Crontab - uruchamiaj co minutę
* * * * * curl -s https://twoja-domena.pl/cron_trigger.php > /dev/null 2>&1
```

```php
// cron_trigger.php
<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/PseudoCron.php';
require_once __DIR__ . '/src/CronTasks.php';

$config = require __DIR__ . '/config/config.php';
$db = new Database($config);
$pdo = $db->pdo();

$pseudoCron = new PseudoCron($pdo);
$pseudoCron->tick();

echo "OK\n";
```

## 📊 Monitorowanie

### Logi plików
Zadania mogą logować do plików w `storage/logs/`:
- `storage/logs/cron.log` - ogólne logi systemu cron
- `storage/logs/geo_cache.log` - logi zadania czyszczenia cache geo

### Logi w bazie danych
Wszystkie wykonania są zapisywane w tabeli `cron_logs` z:
- Czasem rozpoczęcia i zakończenia
- Statusem (success/error/running)
- Komunikatem błędu (jeśli wystąpił)
- Czasem wykonania w milisekundach

## 🐛 Debugowanie

### Problem: Zadania się nie wykonują
1. Sprawdź czy zadanie jest aktywne w panelu admina
2. Sprawdź czy `next_run` jest w przeszłości
3. Sprawdź logi w `storage/logs/cron.log`
4. Sprawdź logi błędów PHP (`error_log`)

### Problem: Zadanie wykonuje się zbyt często
1. Sprawdź interwał zadania (`interval_seconds`)
2. Sprawdź czy nie ma wielokrotnych wpisów tego samego zadania

### Problem: Blokada nie znika
Usuń ręcznie plik blokady:
```bash
rm storage/locks/cron_*.lock
```

## 📚 Przykłady użycia

### Przykład 1: Wysyłanie newslettera co tydzień

```php
// src/CronTasks.php
public function sendWeeklyNewsletter(): void
{
    $subscribers = $this->getActiveSubscribers();
    $sent = 0;
    
    foreach ($subscribers as $subscriber) {
        if ($this->sendEmail($subscriber['email'], 'Tygodniowy newsletter')) {
            $sent++;
        }
    }
    
    Utils::appendLog(__DIR__ . '/../storage/logs/newsletter.log', 
        "Sent newsletter to {$sent} subscribers");
}

// Rejestracja
$pseudoCron->registerJob(
    'weekly_newsletter',
    'Wysyłanie tygodniowego newslettera',
    604800, // 7 dni
    'CronTasks',
    'sendWeeklyNewsletter',
    true
);
```

### Przykład 2: Backup bazy danych co 24h

```php
public function backupDatabase(): void
{
    $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $path = __DIR__ . '/../storage/backups/' . $filename;
    
    $dbConfig = require __DIR__ . '/../config/config.php';
    $host = $dbConfig['db']['host'];
    $dbname = $dbConfig['db']['dbname'];
    $user = $dbConfig['db']['user'];
    $pass = $dbConfig['db']['pass'];
    
    $command = "mysqldump -h {$host} -u {$user} -p{$pass} {$dbname} > {$path}";
    exec($command, $output, $result);
    
    if ($result === 0) {
        Utils::appendLog(__DIR__ . '/../storage/logs/backup.log', 
            "Backup created: {$filename}");
    } else {
        throw new Exception("Backup failed: " . implode("\n", $output));
    }
}
```

## 🔄 Aktualizacja z wersji bez pseudo-crona

Jeśli aktualizujesz z wcześniejszej wersji:

1. Uruchom migrację `009_create_cron_jobs.sql`
2. Kod w `index.php` został już zaktualizowany - pseudo-cron uruchamia się automatycznie
3. Przejdź do panelu admina → Cron → Zarejestruj domyślne zadania
4. Sprawdź czy wszystko działa poprawnie

## 📞 Wsparcie

W razie problemów:
1. Sprawdź logi w `storage/logs/cron.log`
2. Sprawdź panel admina → Cron → Historia wykonań
3. Sprawdź dokumentację projektu w `docs/`
