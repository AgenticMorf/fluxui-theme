<!--
Ready-to-paste Linear issue content for the Foundations initiative.
Target: AgenticMorf team / fluxui-theme project.

HOW TO ASSOCIATE THE BRANCH
---------------------------
Linear auto-links a branch when the branch name contains the issue identifier
(e.g. MORF-42). Our current branch `feature/foundations-tests-ci` does NOT
follow that convention, so pick one of:

  A. After creating the parent issue, click the "Copy git branch name"
     button in Linear's issue sidebar. It suggests a name like
     `chris/morf-42-foundations-tests-ci-and-quality-gates`. Rename the
     local branch to match:
        git branch -m feature/foundations-tests-ci chris/morf-42-foundations...
        git push origin -u chris/morf-42-foundations...
        git push origin --delete feature/foundations-tests-ci  (optional)

  B. Leave the branch name as-is and manually paste the branch URL
     into the parent issue's description (under "Branch"):
        https://github.com/AgenticMorf/fluxui-theme/tree/feature/foundations-tests-ci
     This does not create a live link in Linear's sidebar, but it is visible
     in the description.

  C. When you open the first PR, include `Fixes MORF-42` (or the real id)
     in the PR body. GitHub's Linear integration will link the PR to the
     issue — that's the strongest association.

Recommend A for the parent issue and C for each sub-issue's PR.
-->

# Parent issue

**Title**
```
Foundations — tests, CI, and quality gates
```

**Description (paste as Linear markdown)**
```markdown
Young package (~9 commits) has no test suite, no CI for tests, and no quality
gates. `composer.json` defines test scripts that can't even run. This
initiative lands the foundation so every subsequent feature (custom brand
colors, a11y, typography, presets) can be built on safe ground.

## Scope

- Standalone Testbench test harness (replaces the broken `cd ../..` plugin-path scripts)
- Tests for: `AppearanceService` (100 % line coverage), Volt settings page,
  head component, migration idempotency, and the HTTP route
- GitHub Actions matrix: {PHP 8.2, 8.3, 8.4} × {Laravel 11, 12} + one
  lowest-deps cell (PHP 8.2 × Laravel 11)
- Laravel Pint, Larastan level 8, Pest mutation testing (non-blocking),
  90 % coverage threshold, Dependabot for composer + github-actions

## Non-goals

- New features (deferred: custom brand colors, a11y prefs, typography, presets, events)
- Windows / macOS CI runners
- Changing public API of `AppearanceService` or blade components

## Design docs

On branch `feature/foundations-tests-ci`:
- `brainstorming.md` — options considered and decisions
- `implementation-plan.md` — detailed plan, 1:1 with the sub-issues below

## Branch

`feature/foundations-tests-ci`
(Rename to `chris/<this-issue-id>-foundations-...` to get Linear's auto-link.)

## Sub-issues

1. Testbench harness
2. AppearanceService unit tests
3. Volt settings page tests
4. Head component tests
5. Migration + route tests
6. CI matrix workflow
7. Pint + Larastan
8. Mutation + coverage threshold + Dependabot

Merge order is dependency-driven, top to bottom.
```

---

# Sub-issue 1 — Testbench harness

**Title**
```
Sub 1: Testbench harness
```

**Description**
```markdown
Stand up a standalone Pest + Testbench test harness at the repo root so
`composer test` works on a fresh clone.

## Files to add
- `phpunit.xml` — points at `./tests/Unit` and `./tests/Feature`, SQLite in-memory
- `tests/Pest.php` — `pest()->extends(Tests\TestCase::class)->in('Feature', 'Unit')`
- `tests/TestCase.php` — extends `Orchestra\Testbench\TestCase`:
  - `getPackageProviders()` returns `[FluxuiThemeServiceProvider::class]`
  - `defineDatabaseMigrations()` creates minimal `users` table then loads the package migration
  - Forces SQLite in-memory
- `tests/Fixtures/User.php` — minimal Eloquent user: `Authenticatable`,
  `$fillable = ['email', 'appearance_preferences']`,
  `$casts = ['appearance_preferences' => 'array']`

## composer.json changes
- `scripts.test`: `vendor/bin/pest`
- `scripts.test:coverage`: `XDEBUG_MODE=coverage vendor/bin/pest --coverage --min=90`
- `scripts.test:type-coverage`: `vendor/bin/pest --type-coverage`
- `scripts.test:mutation`: `XDEBUG_MODE=coverage vendor/bin/pest --mutate --everything --covered-only --class=AgenticMorf\\\\FluxuiTheme`
- Drop all `bash -c 'cd ../..'` wrappers

## Acceptance
`composer install && composer test` green on PHP 8.4 × Laravel 12 with zero
tests written (suite exits 0).
```

---

# Sub-issue 2 — AppearanceService unit tests

**Title**
```
Sub 2: AppearanceService unit tests
```

**Description**
```markdown
Unit-test the pure-PHP `AppearanceService` in `tests/Unit/AppearanceServiceTest.php`.

## Cases
- Constructor merges partial `$defaults` with built-ins `[accent:zinc, base:zinc, theme:system]`
- `getEffective(null)` returns merged defaults
- `getEffective($user)` with `appearance_preferences = null` → defaults
- Partial prefs → per-key fallback
- All three keys set → all three come from the user
- `getAccentSwatchClass()` — parametrized over all 18 accent keys; unknown key → `bg-zinc-600 dark:bg-zinc-400`
- `getBaseSwatchClass()` — parametrized over all 9 base keys; unknown key → `bg-zinc-500`
- Provider binding: callable `appearance_resolver` in config → service constructed from resolver return value

## Acceptance
`AppearanceService` at 100 % line coverage.

## Depends on
Sub 1 (harness).
```

---

# Sub-issue 3 — Volt settings page tests

**Title**
```
Sub 3: Volt settings page tests
```

**Description**
```markdown
Test `resources/views/livewire/settings/appearance.blade.php` in
`tests/Feature/AppearancePageTest.php`.

## Cases
- `Livewire::test('settings.appearance')` with logged-in user fixture
- Mount with no prefs: `$theme === 'system'`, `$accent === ''`, `$base === ''`
- Mount with `['accent' => 'blue', 'base' => 'slate', 'theme' => 'dark']` → all three hydrated
- `->set('theme', 'dark')` persists to user prefs
- `->set('accent', 'emerald')` persists
- `->set('accent', '')` **unsets** the `accent` key (assert with `Arr::has`, not `===`)
- Same empty-string-unsets behavior for `base` and `theme`
- Guest session: `persistPreference` no-ops (no write, no exception)
- Route definition: `config('fluxui-theme.route_name')` resolves to the Volt route

## Acceptance
All `persistPreference` branches covered, including empty-string-unsets.

## Depends on
Sub 1, Sub 2.
```

---

# Sub-issue 4 — Head component tests

**Title**
```
Sub 4: Head component tests
```

**Description**
```markdown
Test `resources/views/components/appearance.blade.php` in
`tests/Feature/AppearanceHeadComponentTest.php`.

## Cases
- Guest render emits `localStorage.setItem('flux.appearance', 'system')`
- Auth user with `theme: dark` → script contains `'dark'`
- `base: zinc` (or no preference) → **no** `<style>` tag emitted
- `base: slate` → rendered HTML contains `--color-zinc-50: var(--color-slate-50)` through `--color-zinc-950: var(--color-slate-950)`
- `@js()` encoding: theme string is JSON-encoded (wrapped in double quotes), guards against XSS on odd input

## Acceptance
Head component fully covered; both branches of the `$base !== 'zinc'` conditional exercised.

## Depends on
Sub 1.
```

---

# Sub-issue 5 — Migration + route tests

**Title**
```
Sub 5: Migration + route tests
```

**Description**
```markdown
## Migration — `tests/Feature/MigrationTest.php`
- `Schema::hasColumn('users', 'appearance_preferences')` → true after migrations run
- Column type is JSON
- Re-running the migration is a no-op (proves the README's "running both is safe" claim)
- `migrate:rollback` removes the column

## Route — `tests/Feature/AppearanceRouteTest.php`
- Unauthenticated GET of `appearance.edit` → redirect to login
- Authenticated GET → 200 and three `role="radiogroup"` regions in the HTML
- Works under a custom `config('fluxui-theme.route')` override

## Acceptance
Migration idempotency covered in CI, not just documented. Broken route or
missing middleware surfaces as CI failure.

## Depends on
Sub 1.
```

---

# Sub-issue 6 — CI matrix workflow

**Title**
```
Sub 6: CI matrix workflow
```

**Description**
```markdown
Add `.github/workflows/tests.yml`.

## Triggers
- `push: branches: ['**']`
- `pull_request: branches: [main]`

## Concurrency
```yaml
concurrency:
  group: tests-${{ github.ref }}
  cancel-in-progress: true
```

## Matrix (7 cells)
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

## Steps per cell
1. `actions/checkout@v4`
2. `shivammathur/setup-php@v2` (coverage: xdebug, tools: composer:v2)
3. Cache composer dir keyed on `hashFiles('composer.json')` + matrix
4. `composer require "laravel/framework:${{ matrix.laravel }}" "illuminate/support:${{ matrix.laravel }}" --no-update --no-interaction`
5. `composer update --${{ matrix.deps == 'lowest' && 'prefer-lowest' || 'prefer-dist' }} --no-interaction`
6. `composer test` (coverage cell: `composer test:coverage` instead)

## Coverage upload
Only the `8.4 × 12 × highest` cell uploads to Codecov.

## Acceptance
All 7 cells green on `main`; a failing PR is visibly blocked.

## Depends on
Subs 1–5.
```

---

# Sub-issue 7 — Pint + Larastan

**Title**
```
Sub 7: Pint + Larastan
```

**Description**
```markdown
Catch style drift and static-analysis errors in CI.

## Files
- `pint.json`:
  ```json
  { "preset": "laravel" }
  ```
- `phpstan.neon.dist`:
  ```neon
  includes:
    - vendor/larastan/larastan/extension.neon
  parameters:
    level: 8
    paths:
      - src
  ```
- `composer.json` require-dev: add `larastan/larastan: ^3.0`

## Workflow jobs (added to `tests.yml`, not matrixed)
- `pint`: PHP 8.4, `vendor/bin/pint --test`
- `phpstan`: PHP 8.4, `vendor/bin/phpstan analyse --no-progress`

## Fixup pass
Run Pint and Larastan locally before this PR; commit any adjustments the
current codebase needs so the initial green is real.

## Acceptance
Both jobs green on `main`; a trivial style violation in a follow-up PR visibly blocks merge.

## Depends on
Sub 6.
```

---

# Sub-issue 8 — Mutation + coverage threshold + Dependabot

**Title**
```
Sub 8: Mutation + coverage threshold + Dependabot
```

**Description**
```markdown
Light up the last three quality gates.

## Coverage threshold
`--coverage --min=90` already wired into `composer test:coverage` in Sub 1.
Switch the coverage cell in `tests.yml` to call `composer test:coverage`
instead of `composer test`, so the threshold actually gates merge.

## Mutation job in `tests.yml`
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
Tighten the MSI threshold and drop `continue-on-error` in a follow-up once
2–3 runs establish a realistic baseline.

## `.github/dependabot.yml`
```yaml
version: 2
updates:
  - package-ecosystem: composer
    directory: /
    schedule: { interval: weekly }
    open-pull-requests-limit: 5
  - package-ecosystem: github-actions
    directory: /
    schedule: { interval: weekly }
```

## Acceptance
PRs show a mutation check and a coverage check. Dependabot opens at least
one update PR within a week.

## Depends on
Sub 7.
```
