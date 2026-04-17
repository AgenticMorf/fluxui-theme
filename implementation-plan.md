---
title: Implementation plan — Foundations
date: 2026-04-16
branch: feature/foundations-tests-ci
parent_brainstorm: brainstorming.md
---

# Implementation plan — Foundations

Detailed plan for the "Foundations — tests, CI, and quality gates" initiative. Maps 1:1 to the Linear parent + 8 sub-issues. Each sub-issue below is one PR.

## Goal

Give the package a real test suite, a matrix CI workflow, and standard quality gates (style, static analysis, mutation, coverage, dependency automation) — without changing runtime behavior or public API.

## Architecture of what's being added

```
.
├── phpunit.xml                           # Testbench config, repo root
├── pint.json                             # Laravel preset
├── phpstan.neon.dist                     # Larastan level 8, paths: [src]
├── tests/
│   ├── Pest.php                          # uses(TestCase::class) in Feature+Unit
│   ├── TestCase.php                      # extends Orchestra\Testbench, registers provider, users table
│   ├── Unit/
│   │   └── AppearanceServiceTest.php
│   └── Feature/
│       ├── AppearancePageTest.php        # Volt component
│       ├── AppearanceHeadComponentTest.php
│       ├── AppearanceRouteTest.php       # real HTTP round-trip
│       └── MigrationTest.php
└── .github/
    ├── workflows/
    │   └── tests.yml                     # matrix tests + pint + phpstan + mutation jobs
    └── dependabot.yml
```

`composer.json` test scripts rewritten to run locally without the `cd ../..` / `plugins/fluxui-theme/` prefix.

## Sub-issue 1 — Testbench harness

**Goal.** `composer test` runs on a fresh clone and exits 0, even with zero test bodies.

**Files to add.**
- `phpunit.xml` — minimal config pointing at `./tests/Unit` and `./tests/Feature`, bootstrap `vendor/autoload.php`, testsuite cache off.
- `tests/Pest.php` — `pest()->extends(Tests\TestCase::class)->in('Feature', 'Unit');`
- `tests/TestCase.php` — extends `Orchestra\Testbench\TestCase`:
  - `getPackageProviders()` returns `[FluxuiThemeServiceProvider::class]`.
  - `defineDatabaseMigrations()` loads the package migration + a helper `users` table (id, email, `appearance_preferences` JSON).
  - Publishes/configures an inline `User` model fixture with `$casts = ['appearance_preferences' => 'array']` and `$fillable = ['email', 'appearance_preferences']`.
  - Forces SQLite in-memory.
- `tests/Fixtures/User.php` — minimal Eloquent user that satisfies `Authenticatable` + has the JSON cast.

**Files to change.**
- `composer.json`
  - `scripts.test`: `vendor/bin/pest`
  - `scripts.test:coverage`: `XDEBUG_MODE=coverage vendor/bin/pest --coverage --min=90`
  - `scripts.test:type-coverage`: `vendor/bin/pest --type-coverage`
  - `scripts.test:mutation`: `XDEBUG_MODE=coverage vendor/bin/pest --mutate --everything --covered-only --class=AgenticMorf\\\\FluxuiTheme`
  - Drop the `bash -c 'cd ../..'` wrappers.

**Acceptance.** `composer install && composer test` green locally on PHP 8.4 × Laravel 12.

---

## Sub-issue 2 — `AppearanceService` unit tests

**Goal.** Pin down the service's contract. Aim for 100 % line coverage on `AppearanceService.php`.

**Cases (in `tests/Unit/AppearanceServiceTest.php`).**

- Constructor merges partial `$defaults` with built-ins `[accent:zinc, base:zinc, theme:system]`.
- `getEffective(null)` returns the merged defaults.
- `getEffective($user)` where `$user->appearance_preferences` is `null` → defaults.
- `getEffective($user)` where the array has only some keys → per-key fallback.
- `getEffective($user)` where all three keys are set → all three come from the user.
- `getAccentSwatchClass()` — parametrized over all 18 accent keys returns the mapped pair; unknown key returns `bg-zinc-600 dark:bg-zinc-400`.
- `getBaseSwatchClass()` — parametrized over all 9 base keys; unknown key returns `bg-zinc-500`.
- Service-provider binding: passing a callable `appearance_resolver` in config → service constructed with resolver return value (test via `app(AppearanceService::class)` after `config(['fluxui-theme.appearance_resolver' => fn () => [...]])` and re-booting the provider).

**Acceptance.** `AppearanceService` at 100 % line coverage in the coverage report.

---

## Sub-issue 3 — Volt settings page tests

**Goal.** Cover the user-facing persistence flow in `resources/views/livewire/settings/appearance.blade.php`.

**Cases (in `tests/Feature/AppearancePageTest.php`).**

- Uses `Livewire::test('settings.appearance')` (Volt component name) with a logged-in user fixture.
- Mount with no user prefs: `$theme === 'system'`, `$accent === ''`, `$base === ''`.
- Mount with `appearance_preferences = ['accent' => 'blue', 'base' => 'slate', 'theme' => 'dark']`: all three hydrated.
- `->set('theme', 'dark')` persists `['theme' => 'dark']` on the user.
- `->set('accent', 'emerald')` persists `['accent' => 'emerald']`.
- `->set('accent', '')` **unsets** the `accent` key (use `Arr::has`, not `===`).
- Same empty-string-unsets behavior for `base` and `theme`.
- Guest session: `persistPreference` no-ops (no exception, no write). Test by calling through a guest-auth helper, asserting no mutation.
- Route definition: `config('fluxui-theme.route_name')` resolves to the Volt route.

**Acceptance.** All `persistPreference` branches covered, including the empty-string-unsets case.

---

## Sub-issue 4 — Head component tests

**Goal.** Cover `resources/views/components/appearance.blade.php` rendering.

**Cases (in `tests/Feature/AppearanceHeadComponentTest.php`).**

- Rendering `<x-fluxui-theme::appearance />` as a guest emits the `localStorage.setItem('flux.appearance', 'system')` script (or whatever default).
- As an auth user with `theme: dark`, the script contains `'dark'`.
- With `base: zinc` (or no preference), **no** `<style>` tag is emitted.
- With `base: slate`, the rendered HTML contains `--color-zinc-50: var(--color-slate-50)` through `--color-zinc-950: var(--color-slate-950)`.
- `@js()` encoding: verify the theme string is JSON-encoded (i.e. wrapped in double quotes), guarding against XSS/breakage on odd values.

**Acceptance.** Head component fully covered; both branches of the `$base !== 'zinc'` conditional exercised.

---

## Sub-issue 5 — Migration tests

**Goal.** Prove the README's "running both is safe" guarantee and basic schema correctness.

**Cases (in `tests/Feature/MigrationTest.php`).**

- `Schema::hasColumn('users', 'appearance_preferences')` → `true` after `defineDatabaseMigrations()` runs.
- Column type is JSON.
- Re-running the migration is a no-op (no exception). Do this by calling `Artisan::call('migrate')` a second time and asserting success + column still present.
- Rolling back (`Artisan::call('migrate:rollback')`) removes the column.

**Acceptance.** Migration idempotency is covered in CI, not just documented.

---

## Sub-issue 5b — Route/HTTP feature test

(Merged into this same sub-issue since it's a few extra cases.)

**File.** `tests/Feature/AppearanceRouteTest.php`.

**Cases.**

- Unauthenticated GET of `appearance.edit` redirects to login.
- Authenticated GET returns 200 and contains the three swatch `role="radiogroup"` regions.
- The route is mounted at the configured path (`settings/appearance` default) and responds under a custom `config('fluxui-theme.route')` override.

**Acceptance.** A broken route or missing middleware surfaces as a CI failure, not as a prod regression.

---

## Sub-issue 6 — CI matrix workflow

**Goal.** Run the full test suite on every push and every PR, across supported PHP × Laravel versions.

**File.** `.github/workflows/tests.yml`.

**Triggers.** `push: branches: ['**']` + `pull_request: branches: [main]`.

**Concurrency.**
```yaml
concurrency:
  group: tests-${{ github.ref }}
  cancel-in-progress: true
```

**Matrix (7 cells).**
```yaml
strategy:
  fail-fast: false
  matrix:
    php: ['8.2', '8.3', '8.4']
    laravel: ['11.*', '12.*']
    deps: ['highest']
    include:
      - php: '8.2'
        laravel: '11.*'
        deps: 'lowest'
```

**Steps per cell.**
1. `actions/checkout@v4`.
2. `shivammathur/setup-php@v2` — `php-version: ${{ matrix.php }}`, `coverage: xdebug`, `tools: composer:v2`.
3. Cache composer dir keyed on `hashFiles('composer.json') + matrix`.
4. `composer require "laravel/framework:${{ matrix.laravel }}" "illuminate/support:${{ matrix.laravel }}" --no-update --no-interaction`.
5. `composer update --${{ matrix.deps == 'lowest' && 'prefer-lowest' || 'prefer-dist' }} --no-interaction`.
6. `composer test` (for the coverage cell: `composer test:coverage` instead).

**Coverage upload.** Only the `8.4 × 12 × highest` cell runs `test:coverage` and uploads a Codecov/Coveralls artifact. (Decision on provider: Codecov default; switch if you prefer.)

**Acceptance.** All 7 cells green on `main`. A failing PR is visibly blocked.

---

## Sub-issue 7 — Pint + Larastan

**Goal.** Catch style drift and static-analysis errors in CI.

**Files.**
- `pint.json` — Laravel preset:
  ```json
  { "preset": "laravel" }
  ```
  (Tweak rules only if the initial Pint pass against the current codebase shows something objectionable.)
- `phpstan.neon.dist`:
  ```neon
  includes:
    - vendor/larastan/larastan/extension.neon
  parameters:
    level: 8
    paths:
      - src
  ```
- Add `larastan/larastan: ^3.0` to `require-dev` in `composer.json`.

**Workflow jobs (added to `tests.yml` as sibling jobs, not matrixed).**
- `pint`: PHP 8.4, `vendor/bin/pint --test`.
- `phpstan`: PHP 8.4, `vendor/bin/phpstan analyse --no-progress`.

**One-time fixups to land in this PR.** Run Pint and Larastan locally; commit whatever adjustments the current codebase needs so CI goes green on merge (probably zero-to-few changes — the codebase is small and already clean-looking).

**Acceptance.** Both jobs green. A whitespace-only style violation in a follow-up PR visibly blocks merge.

---

## Sub-issue 8 — Mutation + coverage threshold + Dependabot

**Goal.** Light up the last three gates.

**Coverage threshold.** Update `scripts.test` (or the CI invocation for the coverage cell) to pass `--coverage --min=90`. Enforced in the single cell that uploads coverage, not across the matrix.

**Mutation.** Add a `mutation` job to `tests.yml`:
```yaml
mutation:
  runs-on: ubuntu-latest
  if: github.event_name == 'pull_request'
  continue-on-error: true    # non-blocking for first few runs
  steps:
    - uses: actions/checkout@v4
    - uses: shivammathur/setup-php@v2
      with: { php-version: '8.4', coverage: xdebug }
    - run: composer install --no-interaction --prefer-dist
    - run: composer test:mutation
```
Tighten the MSI threshold and remove `continue-on-error` in a follow-up PR once 2–3 runs establish a realistic baseline.

**Dependabot.** `.github/dependabot.yml`:
```yaml
version: 2
updates:
  - package-ecosystem: composer
    directory: /
    schedule:
      interval: weekly
    open-pull-requests-limit: 5
  - package-ecosystem: github-actions
    directory: /
    schedule:
      interval: weekly
```

**Acceptance.** PRs show a mutation check and a coverage check. Dependabot opens at least one update PR within a week of merging.

---

## Risks and open questions

- **Volt testing ergonomics.** The settings page is a Volt single-file component with `\Livewire\Volt\layout(...)` and inline mount. `Livewire::test('settings.appearance')` should work, but if the layout wrapper causes hydration issues we may need to bypass the layout in the test environment. Fallback: render directly via Blade + assert DOM.
- **Testbench's user fixture and the package migration.** The package adds `appearance_preferences` to the `users` table, but Testbench doesn't ship a `users` table. We'll need to run a minimal users-table migration in `TestCase::defineDatabaseMigrations()` *before* running the package migration, so the `hasColumn()` guard sees the table exist.
- **Mutation testing runtime.** Pest's `--mutate` can take 10–20× the base test suite time. If it balloons CI, move to a nightly schedule instead of per-PR.
- **Coverage target of 90 %.** Will be re-evaluated after sub-issues 2–5 land; may bump to 95 % if the suite is strong.

## Estimated effort

Rough T-shirt sizes for sequencing visibility only. Not commitments.

| Sub-issue | Size |
| --- | --- |
| 1. Testbench harness | M |
| 2. Service unit tests | S |
| 3. Volt page tests | M |
| 4. Head component tests | S |
| 5. Migration + route tests | S |
| 6. CI matrix workflow | M |
| 7. Pint + Larastan | S |
| 8. Mutation + coverage + Dependabot | S |
