# Lock Files Directory

Ten katalog przechowuje pliki blokad używane przez system Pseudo-Cron.

## Jak to działa?

Gdy zadanie cron jest wykonywane, tworzony jest plik blokady (np. `cron_1.lock`), aby zapobiec wielokrotnemu uruchomieniu tego samego zadania.

- Blokada jest tworzona przed wykonaniem zadania
- Blokada jest usuwana po zakończeniu zadania
- Jeśli blokada jest starsza niż 5 minut, jest automatycznie usuwana (zakładamy, że proces padł)

## Rozwiązywanie problemów

Jeśli zadanie się "zawiesiło" i nie chce się uruchomić ponownie, możesz ręcznie usunąć plik blokady:

```bash
rm storage/locks/cron_*.lock
```

## .gitignore

Pliki blokad (*.lock) są ignorowane przez git - nie powinny być commitowane do repozytorium.
