---
title: Foundations — tests, CI, and quality gates
date: 2026-04-16
status: design approved, pending implementation
branch: feature/foundations-tests-ci
---

# Brainstorming: Foundations — tests, CI, and quality gates

## Context

`agenticmorf/fluxui-theme` is a young Laravel package (9 commits on `main` at the time of writing) wrapping Livewire Flux's appearance controls: light/dark/system theme, 18 accent colors, 9 base colors. It was just migrated into the AgenticMorf namespace.

The core surface is small and well-scoped:

- `AppearanceService` — pure PHP, holds color constants and helpers, merges config defaults with per-user preferences.
- `resources/views/livewire/settings/appearance.blade.php` — Volt settings page with swatch pickers; persists into `users.appearance_preferences`.
- `resources/views/components/appearance.blade.php` — head component that seeds `localStorage['flux.appearance']` and injects the base-color CSS remap before `@fluxAppearance` runs.
- `2026_03_15_000000_add_appearance_preferences_to_users_table.php` — idempotent column migration.

## Discovery — gaps found

During exploration we found several opportunities worth considering. The most impactful for a young package are the foundational ones:

1. **No `tests/` directory.** `composer.json` *defines* test scripts and requires `pestphp/pest`, `orchestra/testbench`, and `orchestra/pest-plugin-testbench` as dev deps — but no tests exist and the scripts can't even run as written.
2. **Composer test scripts assume a plugin layout.** They `cd ../..` and reference `plugins/fluxui-theme/phpunit.xml`, suggesting the package was expected to live inside a host app. That conflicts with the Testbench deps, which are for standalone package testing.
3. **No CI for tests.** Only `.github/workflows/docs.yml` exists (GitHub Pages deploy).
4. **No lint / static analysis.** A commit as recent as `8aeca8e` ("Drop invalid `@see` from AppearanceService docblock") would have been caught by PHPStan.
5. **No dependency automation.** The package will sit idle between feature bursts; upstream Laravel and action versions will drift silently.

Other opportunities (extensibility for custom brand colors, a11y preferences like high-contrast / reduced-motion, typography & density axes, events on appearance change, named theme presets) were considered and deferred. They all benefit from having a test safety net in place first, so Foundations goes first.

## Options considered

Each decision was presented with alternatives; the selected option is marked (**✓**).

### Direction of the initiative

| Option | Description |
| --- | --- |
| **A ✓** | **Foundations** — tests + CI + quality gates |
| B | Extensibility — consumer-app custom colors and named presets |
| C | Accessibility preferences — high-contrast, reduced-motion, text size |
| D | Typography & density |
| E | Developer ergonomics — events, traits, CSS generator, publishable stubs |
| F | Combination / other |

### Test harness shape

| Option | Description |
| --- | --- |
| **A ✓** | **Standalone via Testbench** — the conventional pattern for public Composer packages; the Testbench deps are already in `composer.json` |
| B | Plugin-within-app (keep the current `cd ../..` scripts) |
| C | Both |

### Test coverage scope

| Option | Description |
| --- | --- |
| A | `AppearanceService` only |
| B | Service + Volt settings page |
| C | Service + Volt + head component + migration |
| **D ✓** | **All of C + feature/HTTP test that exercises the real settings route** |

### CI matrix

| Option | Description |
| --- | --- |
| A | PHP 8.4 × Laravel 12 only |
| B | {8.2, 8.3, 8.4} × Laravel 12 |
| C | {8.2, 8.3, 8.4} × {Laravel 11, Laravel 12} |
| **D ✓** | **C + one lowest-deps cell (`composer update --prefer-lowest`)** |

### Additional quality gates

| Option | Description |
| --- | --- |
| A | Laravel Pint |
| B | Larastan / PHPStan |
| C | Mutation testing (wire up existing `test:mutation` script) |
| D | Coverage threshold enforcement |
| E | Dependabot |
| **F ✓** | **All of A–E** |

### Linear shape

| Option | Description |
| --- | --- |
| A | One umbrella issue with a checklist |
| **B ✓** | **One parent issue + 8 sub-issues (one per deliverable)** |
| C | Flat sibling issues, no parent |
| D | Three coarse-grained issues |

### Sequencing of sub-issues

| Option | Description |
| --- | --- |
| **1 ✓** | **Bottom-up — harness → tests → CI → gates** |
| 2 | Top-down — empty-test CI skeleton first |
| 3 | Parallel tracks |

## Decisions summary

- **Direction:** Foundations — tests, CI, quality gates.
- **Harness:** Standalone Testbench. Rewrite `composer.json` test scripts to drop the plugin-layout assumptions.
- **Coverage:** Service + Volt settings page + head component + migration + a feature/HTTP test against the settings route.
- **CI matrix:** 7 cells — {8.2, 8.3, 8.4} × {L11, L12} highest-deps + one lowest-deps cell (8.2 × L11).
- **Quality gates:** Pint, Larastan (level 8), mutation testing (non-blocking), 90 % global coverage threshold, Dependabot for composer + github-actions.
- **Linear structure:** Parent issue "Foundations" with 8 sub-issues in AgenticMorf / fluxui-theme.
- **Sequencing:** Bottom-up. Each sub-issue is a PR-sized chunk that merges green before the next starts.

## Non-goals for this initiative

- New features (custom brand colors, presets, a11y, typography) — explicitly deferred.
- Moving docs tooling, publishing pipeline, or release automation.
- Windows / macOS CI runners.
- Changing the public API of `AppearanceService` or any blade components.
