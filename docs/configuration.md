---
title: Configuration
---

# Configuration

Publish the config:

```bash
php artisan vendor:publish --tag=fluxui-theme-config
```

Edit `config/fluxui-theme.php`:

- **route** — Appearance page path (default: `settings/appearance`)
- **route_name** — Route name (default: `appearance.edit`)
- **layout** — Livewire layout (default: `components.layouts.app.sidebar`)
- **defaults** — Default accent, base, theme when no user preference
- **appearance_resolver** — Optional callback for app-level defaults (e.g. from a settings table). Signature: `fn(): array` returning `['accent' => string, 'base' => string, 'theme' => string]`
