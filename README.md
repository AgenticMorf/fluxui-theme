# FluxUI Theme

FluxUI theme and appearance settings with color swatch pickers for Laravel applications using [Livewire Flux](https://fluxui.dev).

## Installation

```bash
composer require christhompsontldr/fluxui-theme
```

Run the migration:

```bash
php artisan migrate
```

## Requirements

- Laravel 11+
- Livewire 3+
- Livewire Flux 2+
- Livewire Volt 1+

## Features

- **Theme mode**: Light, dark, or system
- **Accent color swatches**: 19 Flux accent colors
- **Base color swatches**: 10 Flux base colors (slate, gray, zinc, neutral, stone, mauve, olive, mist, taupe)
- User preferences stored in `appearance_preferences` JSON on User model

## Integration Guide

### 1. CSS Setup

Your app must import Flux CSS and ensure base colors (slate, gray, zinc, neutral, stone, mauve, olive, mist, taupe) are available. Flux provides these via its dist; base color switching works by remapping `--color-zinc-*` to the chosen base.

In `resources/css/app.css` (or equivalent):

```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';

/* Flux stubs for component class discovery */
@source '../../vendor/livewire/flux/stubs/**/*.blade.php';

@custom-variant dark (&:where(.dark, .dark *));
```

The package’s appearance component injects inline CSS when a non-zinc base is selected, redefining `--color-zinc-50` through `--color-zinc-950` to the chosen base (e.g. `--color-slate-50`, etc.). Flux’s `flux.css` must define those base color variables.

### 2. Blade: Layout Head

Include the appearance component **before** `@fluxAppearance` in your layout’s `<head>` so theme and base color are applied before Flux initializes:

```blade
{{-- In resources/views/partials/head.blade.php or your main layout <head> --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
<x-fluxui-theme::appearance />
@fluxAppearance
```

### 3. Blade: Accent Color Wrapper

Wrap your main app shell with `<flux:accent>` so accent colors apply. Pass the effective accent from `AppearanceService`:

```blade
@php
    $appearance = app(\AgenticMorf\FluxuiTheme\AppearanceService::class)->getEffective(auth()->user());
@endphp
<body>
    <flux:accent :color="$appearance['accent']">
        {{-- Your app content: sidebar, header, main, etc. --}}
    </flux:accent>
</body>
```

Use this in layouts that render authenticated UI (e.g. `components/layouts/app/sidebar.blade.php`, `header.blade.php`, or a shared app layout).

### 4. Blade: Settings Navigation

Add a link to the appearance settings page in your settings layout. The route name is configurable (default `appearance.edit`):

```blade
<flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
```

### 5. Settings Layout

The appearance page expects a settings layout component that provides `$heading`, `$subheading`, and a `$slot`. For example:

```blade
{{-- x-settings.layout --}}
<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            {{-- ... other nav items ... --}}
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>
    </div>
    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
        <div class="mt-5 w-full max-w-lg">{{ $slot }}</div>
    </div>
</div>
```

The Volt component optionally includes `partials.settings-heading` for a shared settings page header. Create it if you use a common settings layout:

```blade
{{-- resources/views/partials/settings-heading.blade.php --}}
<div class="relative mb-6 w-full">
    <flux:heading size="xl" level="1">{{ __('Settings') }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">{{ __('Manage your profile and account settings') }}</flux:subheading>
    <flux:separator variant="subtle" />
</div>
```

If the partial does not exist, it is skipped.

### 6. User Model

Add the cast for `appearance_preferences`:

```php
protected $casts = [
    'appearance_preferences' => 'array',
];
```

The migration adds the `appearance_preferences` column automatically when you run `php artisan migrate`.

## Configuration

Publish the config:

```bash
php artisan vendor:publish --tag=fluxui-theme-config
```

### App Defaults Resolver

To use app-level defaults (e.g. from a settings table), set `appearance_resolver` in `config/fluxui-theme.php`:

```php
'appearance_resolver' => [App\Services\MorfSettings::class, 'getAppearance'],
```

The resolver should return `['accent' => string, 'base' => string, 'theme' => string]`.

### Route

By default the appearance page is at `settings/appearance` with route name `appearance.edit`. Override in config:

```php
'route' => 'settings/appearance',
'route_name' => 'appearance.edit',
```

## Usage

Get effective appearance for the current user:

```php
$appearance = app(\AgenticMorf\FluxuiTheme\AppearanceService::class)->getEffective(auth()->user());
// ['accent' => 'blue', 'base' => 'zinc', 'theme' => 'system']
```

## Existing Migrations

If your app already adds `appearance_preferences` to the `users` table, remove that column from your migration to avoid a duplicate column error. This package’s migration handles it.

## License

MIT
