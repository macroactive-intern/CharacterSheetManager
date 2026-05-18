# Character Sheet Manager

A Laravel 12 application for managing tabletop RPG character sheets. Users can create characters, track stats with a 100-point cap, and manage inventory items.

## Features

- Auth-gated character management (create, view, edit, delete)
- Nested stat tracking — unique names per character, 100-point total cap
- Item inventory with equipped/unequipped toggle
- Ownership enforced at every endpoint; cross-user access returns 403
- Shallow resource routing (`characters.stats`, `characters.items`)
- Mass assignment protection — `user_id` cannot be injected via the request body

## Requirements

- PHP 8.2+
- Node 18+
- Composer

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install && npm run build
```

## Running locally

```bash
php artisan serve
```

## Tests

```bash
php artisan test
```

40 tests, 97 assertions. Test suite uses Pest with the Laravel plugin.

## Stack

| Layer | Choice |
|---|---|
| Framework | Laravel 12 |
| Database | SQLite (dev/test), configurable via `DB_CONNECTION` |
| Auth | Laravel Breeze |
| Frontend | Blade + Tailwind CSS + Vite |
| Testing | Pest 3 + pest-plugin-laravel |
