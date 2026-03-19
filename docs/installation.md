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

```bash
php artisan migrate
```

The migration adds the `appearance_preferences` column to the `users` table. If your app already adds this column, remove it from your migration to avoid a duplicate column error.
