# Character Sheet Manager Audit

## Scope

Built a Laravel Breeze + Blade character sheet manager with authenticated CRUD for characters and nested stat/item management. The implementation focuses on ownership checks, relationship-based writes, and server-side validation so users cannot submit or spoof `user_id` or `character_id`.

## What Was Added

- Installed Breeze Blade auth and built frontend assets.
- Added `Character`, `Stat`, and `Item` models, migrations, factories, controllers, and relationships.
- Added `CharacterPolicy` ownership checks.
- Added authenticated character, stat, and item routes.
- Added Blade screens for listing, creating, editing, viewing, and managing character sheets.
- Added feature tests covering auth redirects, ownership isolation, nested record ownership, stat caps, duplicate stat names, item toggle, cascade deletes, and factories.

## Security Decisions

- Character creation uses `auth()->user()->characters()->create(...)`.
- Stat and item creation use `$character->stats()->create(...)` and `$character->items()->create(...)`.
- Forms do not submit `user_id` or `character_id`.
- Character access is authorized through `CharacterPolicy`.
- Nested stat and item actions authorize the parent character first.
- Stat/item update and delete actions verify the child belongs to the route parent and abort with `404` if not.

## Validation Decisions

- Character `level` is capped server-side at `max:100` to match the UI input constraint.
- Stat values are capped at `max:100` and the total stat budget is capped at `100`.
- Stat names are unique per character through both validation logic and a database unique constraint.
- Item checkbox values use validated nested input and default to `false` when unchecked.

## Review Fixes Applied

- Replaced flat multi-form field names on `show.blade.php` with namespaced inputs:
  - `stat[name]`, `stat[value]`
  - `stats[id][name]`, `stats[id][value]`
  - `item[name]`, `item[type]`, `item[equipped]`
  - `items[id][name]`, `items[id][type]`, `items[id][equipped]`
- Updated controllers and tests to match the nested request structure.
- Moved validation error output next to the relevant form fields.
- Added `viewAny` authorization to `CharacterController@index`.
- Removed unused generated policy methods for `restore` and `forceDelete`.
- Added delete confirmations for destructive character, stat, and item deletes.
- Added model factories for `Character`, `Stat`, and `Item`.
- Removed `fake()->unique()` from `StatFactory` to avoid exhausting a small value pool.

## Concurrency And Integrity

Stat total validation and stat-name uniqueness are checked inside database transactions. The parent character row is loaded with `lockForUpdate()` before checking and writing. The database unique constraint remains the final source of truth for duplicate stat names.

Unique constraint violations are converted into validation errors instead of unhandled server errors. The handler covers:

- SQLite/MySQL SQLSTATE `23000`
- PostgreSQL SQLSTATE `23505`
- SQLite message `UNIQUE constraint failed`
- MySQL message `Duplicate entry`

## Database Integrity

- `characters.user_id` cascades on user delete.
- `stats.character_id` cascades on character delete.
- `items.character_id` cascades on character delete.
- `stats` has a unique index on `character_id` and `name`.

## Verification

Latest verification commands run:

```text
php artisan test --filter=CharacterSheetManagerTest
9 passed (35 assertions)

php artisan test
34 passed (97 assertions)

vendor\bin\pint --dirty
passed

npm.cmd run build
built successfully
```

## Known Notes

- Tests use SQLite, where `lockForUpdate()` does not enforce row locks. The transaction and database constraint paths are still covered functionally, but true concurrent locking behavior should be verified against the production database engine if this app moves beyond local coursework/demo use.
- `CharacterPolicy::viewAny` and `create` currently allow all authenticated users. They are intentionally present so future account-tier, suspension, or character-limit rules have a clear policy location.
