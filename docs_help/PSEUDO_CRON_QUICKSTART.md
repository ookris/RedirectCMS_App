# 🚀 Quick Start: Pseudo-Cron

Ten przewodnik pomoże Ci szybko uruchomić system Pseudo-Cron w RedirectCMS.

## ⚡ 3 kroki do uruchomienia

### 1️⃣ Uruchom migrację SQL

```bash
mysql -u redirect_user -p redirect_cms < migrations/009_create_cron_jobs.sql
```

Lub w PHPMyAdmin:
- Zaznacz bazę `redirect_cms`
- Import → Wybierz `migrations/009_create_cron_jobs.sql`

### 2️⃣ Zarejestruj domyślne zadania

Wejdź do panelu admina:

```
https://twoja-domena.pl/admin/index.php?action=cron_jobs
```

Kliknij: **"Zarejestruj domyślne zadania"**

### 3️⃣ Gotowe! ✅

System już działa. Przy każdym wejściu na stronę zadania będą wykonywane automatycznie w tle.

## 🎯 Co dalej?

### Sprawdź czy działa
1. Przejdź do: Panel admin → **Cron**
2. Zobacz listę zarejestrowanych zadań
3. Odśwież stronę kilka razy
4. Sprawdź "Ostatnie wykonania" - powinny pojawić się logi

### Zarządzaj zadaniami
W panelu Cron możesz:
- ✅ **Włączać/wyłączać** zadania
- ▶️ **Wymusić natychmiastowe** wykonanie
- 📊 **Przeglądać logi** dla każdego zadania
- 🗑️ **Usuwać** niepotrzebne zadania

### Dostosuj do swoich potrzeb
Zobacz pełną dokumentację:
- [docs/PSEUDO_CRON.md](PSEUDO_CRON.md) - pełna dokumentacja
- Jak stworzyć własne zadania
- API klasy PseudoCron
- Rozwiązywanie problemów

## 💡 Domyślne zadania

Po rejestracji będziesz mieć:

| Zadanie | Interwał | Domyślny status |
|---------|----------|-----------------|
| Czyszczenie cache geo | 24 godziny | ✅ Włączone |
| Odświeżanie cache statystyk | 30 minut | ✅ Włączone |
| Czyszczenie logów cron | 7 dni | ✅ Włączone |
| Czyszczenie starych wydarzeń | 30 dni | ⏸️ Wyłączone |

## ❓ FAQ

**Q: Czy to wymaga prawdziwego crona?**
A: Nie! System działa przy każdym wejściu na stronę.

**Q: Czy to spowalnia stronę?**
A: Nie! Zadania wykonują się w tle, nie blokują użytkownika.

**Q: Co jeśli moja strona ma mało ruchu?**
A: Możesz dodać prawdziwy cron lub użyć zewnętrznego serwisu typu cron-job.org

**Q: Gdzie są logi?**
A: W bazie danych (tabela `cron_logs`) i w `storage/logs/cron.log`

## 🆘 Pomoc

Problem? Zobacz:
- [docs/PSEUDO_CRON.md](PSEUDO_CRON.md) - pełna dokumentacja
- Panel admin → Cron → Zobacz logi
- Sprawdź `storage/logs/cron.log`
