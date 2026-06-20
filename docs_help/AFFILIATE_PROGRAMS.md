# Programy afiliacyjne

Ten moduł pozwala definiować programy afiliacyjne i przypisywać je do linków. Dzięki temu możesz grupować i filtrować linki według programu.

## Migracja bazy

SQL: migrations/013_add_affiliate_programs.sql

- Tworzy tabelę `affiliate_programs` (id, name, created_at)
- Dodaje kolumnę `affiliate_program_id` do `links`
- Klucz obcy `ON DELETE SET NULL` – usunięcie programu automatycznie odpięte od linków

Uruchom migrację w swojej bazie:

```sql
-- przykład
SOURCE migrations/013_add_affiliate_programs.sql;
```

## Użycie w kodzie

`AffiliateProgramRepository` (src/AffiliateProgramRepository.php):

- `listAll(): array` – lista wszystkich programów
- `getById(int $id): ?array` – pojedynczy program
- `create(string $name): int` – dodaj nowy (walidacja unikalności)
- `update(int $id, string $name): void` – zmień nazwę (walidacja unikalności)
- `delete(int $id): void` – usuń program (powiązane linki zostają z `affiliate_program_id = NULL` dzięki FK)
- `existsByName(string $name, ?int $excludeId = null): bool` – sprawdź duplikaty

## Panel administracyjny

- Nawigacja: „Programy afiliacyjne”
- Lista, dodawanie, edycja, usuwanie programów
- Formularz linku ma pole „Program afiliacyjny (opcjonalnie)” z opcją „Brak programu”

## Przypisywanie do linków

- Tworzenie/edycja linku: wybierz program z listy
- W bazie `links.affiliate_program_id` wskazuje program lub `NULL`

## Zachowanie przy usuwaniu programu

- Klucz obcy ustawia `affiliate_program_id` na `NULL` dla powiązanych linków
- Linki pozostają w systemie bez przypisanego programu

## Przykładowe użycie w PHP

```php
$repo = new AffiliateProgramRepository($pdo);
$programId = $repo->create('Nowy Program');
$programs = $repo->listAll();
$repo->update($programId, 'Nowa nazwa');
$repo->delete($programId); // linki zostaną odpięte (NULL)
```
