---
title: Usage
---

# Usage

## User Model

Add the cast for `appearance_preferences`:

```php
protected $casts = [
    'appearance_preferences' => 'array',
];
```

## CSS Setup

Your app must import Flux CSS. In `resources/css/app.css`:

```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';

@source '../../vendor/livewire/flux/stubs/**/*.blade.php';

@custom-variant dark (&:where(.dark, .dark *));
```

## Layout Head

Include the appearance component **before** `@fluxAppearance` in your layout `<head>`:

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
<x-fluxui-theme::appearance />
@fluxAppearance
```

## Accent Color Wrapper

Wrap your main app shell with `<flux:accent>`:

```blade
@php
    $appearance = app(\AgenticMorf\FluxuiTheme\AppearanceService::class)->getEffective(auth()->user());
@endphp
<body>
    <flux:accent :color="$appearance['accent']">
        {{-- Your app content --}}
    </flux:accent>
</body>
```

## Settings Navigation

Add a link to the appearance page:

```blade
<flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
```

## Settings Layout

The appearance page expects a settings layout with `$heading`, `$subheading`, and `$slot`. Optionally create `partials.settings-heading` for a shared settings header.

## Get Effective Appearance

```php
$appearance = app(\AgenticMorf\FluxuiTheme\AppearanceService::class)->getEffective(auth()->user());
// ['accent' => 'blue', 'base' => 'zinc', 'theme' => 'system']
```
