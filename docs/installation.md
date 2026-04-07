---
title: Installation
---

# Installation

## Requirements

- PHP ^8.2
- Laravel ^11.0 or ^12.0
- Livewire ^3.0 or ^4.0
- livewire/flux ^2.0
- livewire/volt ^1.0

## Composer

```bash
composer require agenticmorf/fluxui-theme
```

[Packagist](https://packagist.org/packages/agenticmorf/fluxui-theme)

## Migration

After `composer require`, run:

```bash
php artisan migrate
```

The package registers a migration that adds the nullable JSON `appearance_preferences` column on `users` (via `loadMigrationsFrom`). The migration skips the column if it already exists.

### Column still missing?

If you see `SQLSTATE[42703]: Undefined column ... appearance_preferences`, the package migration has not been applied yet. Run `php artisan migrate` on the environment that serves the app (including production and Docker).

If your deploy pipeline only runs migrations from `database/migrations` and never loads vendor migrations, add your own migration for `appearance_preferences` or adjust the pipeline so `php artisan migrate` runs the full migrator (including package paths).
