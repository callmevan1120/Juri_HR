# Final Upgrade Review

Reviewed commit: `3f688db`

Branch: `chore/major-upgrade-audit`

Suggested final PR/merge title: `chore(app): upgrade Laravel 13 and Livewire 4`

## Files Changed In Final Pass

The reviewed refactor commit is `3f688db` (`89 files changed, 407 insertions(+), 163 deletions(-)`). It covered the final Laravel 13, Livewire 4, Tailwind 4, request-context, and view consistency pass across:

- `app/Actions`, `app/Http/Controllers`, `app/Http/Middleware`, `app/Http/Requests`, `app/Livewire`, `app/Policies`, `app/Providers`, `app/Services`, and `app/Support`
- `resources/views` Blade files for modern Livewire/Tailwind usage
- `tests/Feature/ModernStackGuardTest.php`
- `guides/final-upgrade-review.md` and `guides/reviewer-evidence.md`

This follow-up consistency pass changed:

- `app/Http/Controllers/System/LanguageController.php`
- `app/Http/Controllers/User/AttendanceController.php`
- `tests/e2e/main-smoke.spec.ts`
- `guides/final-upgrade-review.md`

## Typo Audit

- The misspelled Livewire variant from the commit subject was not found in tracked source, docs, or release-note files after this pass.
- The historical commit subject for `3f688db` still contains that typo in Git metadata; keep the PR/merge title as `chore(app): upgrade Laravel 13 and Livewire 4` unless the commit is intentionally amended before merge.

## Tailwind 4 Verification

- `resources/css/app.css` starts with `@import "tailwindcss";` followed by `@import "flatpickr/dist/flatpickr.css";`.
- CSS-first directives include forms/typography plugins, Blade/compiled/Jetstream sources, and `../js/**/*.js` plus `../js/**/*.ts`.
- `@custom-variant dark (&:where(.dark, .dark *));` and `@theme` primary/brand tokens are present.
- No `@config` directive was found.
- `tailwind.config.js` remains absent.
- `postcss.config.js` remains documented as autoprefixer-only.
- `vite.config.js` uses `@tailwindcss/vite`.
- `bun run build` passed without Tailwind/PostCSS warnings.

## Livewire 4 Verification

- `config/livewire.php` uses Livewire 4-style component discovery, layout, placeholder, smart wire keys, class path, namespace, and CSP-safe settings.
- `make_command` is class-based and generates tests by default.
- Temporary uploads are env-overridable via `LIVEWIRE_TEMPORARY_FILE_UPLOAD_*`.
- `.env.example` documents the Livewire upload variables.
- `.env.vercel.example` documents serverless upload/storage limitations.
- Modern stack guard coverage verifies no legacy `@livewire(`, `flex-shrink-0`, or `ring-opacity-` patterns in Blade source.

## Laravel 13 Compatibility Audit

- No stale `VerifyCsrfToken` or `ValidateCsrfToken` references were found.
- `bootstrap/app.php` uses `preventRequestForgery`; `__vercel-migrate` remains the intentional narrow exception.
- `config/cache.php` keeps `serializable_classes => false`.
- Cache payload review remains scalar/array-safe for settings, enterprise license state, heartbeat, OTP, dynamic barcode fingerprint, wilayah API, and attendance history.
- No real `->upsert` / `::upsert` compatibility risk was found.
- No `QueueBusy`, `JobAttempted`, or `exceptionOccurred` compatibility issue was found.
- Final grep audit refactored obvious request-context controller usage away from `Auth::user()` / `Auth::id()`. Remaining `Auth::*` and `app(...)` usages are deliberate in Livewire current-actor flows, Blade current-user rendering/ability checks, tests, model integration hooks, or static framework contexts.

## Tests Added Or Strengthened

- RBAC audit reports `Policies Without Direct Test: OK`.
- Direct policy mapping coverage is present in `tests/Feature/PolicyDirectCoverageTest.php`.
- Modern stack guard coverage is present in `tests/Feature/ModernStackGuardTest.php`.
- Playwright authenticated admin/user smoke defaults to the local E2E login token outside CI for deterministic menu/page coverage, while CI can still use explicit credentials or `E2E_LOGIN_TOKEN`.
- Backup restore drill remains covered by `tests/Feature/BackupSecurityHardeningTest.php`.

## Command Results

- `composer validate`: passed; `composer.json` is valid.
- `composer check-platform-reqs`: passed on PHP `8.3.31`.
- `php artisan about`: passed; Laravel `13.9.0`, Livewire `v4.3.0`, PHP `8.3.31`.
- `php artisan config:cache`: passed.
- `php artisan route:cache`: passed.
- `php artisan view:cache`: passed.
- `php artisan optimize:clear`: passed before test runs to avoid stale cached bootstrap state.
- `php artisan test`: passed, `446` tests and `9233` assertions.
- `vendor/bin/pest`: passed, `446` tests and `9233` assertions.
- `./vendor/bin/pint --test app/Http/Controllers/System/LanguageController.php app/Http/Controllers/User/AttendanceController.php`: passed.
- `composer phpstan`: passed, no errors.
- `composer audit`: passed, no advisories.
- `bun install`: passed with Bun `1.3.12`; no dependency changes.
- `bun run build`: passed; Vite `7.3.2`, `359` modules transformed, no Tailwind/PostCSS warnings.
- `bunx playwright test tests/e2e/main-smoke.spec.ts`: passed after starting `php artisan serve --host=127.0.0.1 --port=8000`; public, admin, and user smoke all passed (`3` tests). Earlier false starts were caused by no local server and macOS sandbox browser launch permissions, not app assertions.
- `php artisan rbac:audit`: passed; routes, menus, permissions, roles, and direct policy coverage all `OK`.
- `git diff --check`: passed.

## Commands Not Run

- `composer install` was not rerun in this follow-up because this pass did not change dependencies and the requested command list did not include it.
- Physical APK smoke was not run in this pass.

## Manual Smoke Status

Covered by automated tests or Playwright in this pass:

- Login page, admin smoke, user smoke, RBAC audit, route cache, config cache, view cache, backup drill, private attachment access, payslip privacy, attendance API/photo validation, Dynamic QR/risk scoring, payroll/privacy, import/export jobs, and operational health.

Still physical-device or staging only:

- APK camera permission prompt, GPS prompt, real barcode scan camera surface, Android file picker upload, WebView lifecycle under install/update, and full staging backup/restore drill.

## Merge Recommendation

Code review ready after the Android device smoke is either attached from a local authorized device or accepted as a release-gate artifact from the self-hosted APK CI runner.
