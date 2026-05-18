# Final Upgrade Review

Reviewed branch: `chore/major-upgrade-audit`

Latest evidence baseline: `37cf13b`.

Commit `37cf13b` is the UI/accessibility final pass for the user-facing native-app refresh. It covers the cleaner user dashboard, WFH modal form, scan/QR page polish, mobile bottom navigation, profile notification placement, dark-mode surfaces, native date/time field styling, salary visibility affordances, and ID/EN profile language toggle readiness.

Current working-tree follow-up adds the collaboration workspace foundation for the broader “1 platform” goal: company-scoped chat threads, user collaboration inbox, message history, private file upload/download, meeting link registry, optional VPS-only realtime broadcast hooks, RBAC/menu/policy coverage, fake/demo seeding, and regression tests.

Latest realtime follow-up hardens the broadcast runtime so local and production Reverb/Pusher modes only disable polling when the selected broadcast driver is actually configured with a client key. The browser Echo bootstrap now receives `/broadcasting/auth` and CSRF headers from the Laravel layout, while non-realtime environments stay on safe polling fallback.

Latest database follow-up makes PostgreSQL the local, CI, and VPS release baseline. `.env.example`, CI workflows, deployment docs, database config fallback, and portability smoke scripts now default to PostgreSQL 15+/16 while keeping MySQL/MariaDB as explicit compatibility paths.

Branch: `chore/major-upgrade-audit`

Suggested final PR/merge title: `chore(app): upgrade Laravel 13 and Livewire 4`

## Files Changed In Final Pass

The final reviewed code baseline includes the product/platform expansion, APK evidence commits, README/docs sync, the final salted enterprise obfuscator rerun, the enterprise boundary gate documentation sync, and the `37cf13b` UI/accessibility final pass.

The major stabilization commit is `9ab163e` (`129 files changed, 12889 insertions(+), 61 deletions(-)`). It expanded PasPapan into broader HR, operations, commercial, accounting, payroll, leave entitlement, WFH, custom form, command center, and multi-company foundations while keeping the Laravel 13, Livewire 4, Tailwind 4 upgrade gates green.

The follow-up evidence commits are `99cde52`, which updated APK smoke screenshots after running the physical device smoke suite, `0ed8b9f`, which refreshed release evidence docs, `c4d69fe`, which synced the README summary, `e5063be`, which refreshed salted enterprise artifacts, `df23827`, which documented the enterprise boundary gate in the reviewer evidence set, and `37cf13b`, which completed the user UI/accessibility final pass.

The final pass covered:

- `app/Actions`, `app/Http/Controllers`, `app/Http/Middleware`, `app/Http/Requests`, `app/Livewire`, `app/Policies`, `app/Providers`, `app/Services`, and `app/Support`
- `resources/views` Blade files for modern Livewire/Tailwind usage
- platform workspace migrations and models for company branches, operations, commercial, accounting, sales pipeline, custom forms, leave entitlement, vendor bills, payroll period/tax metadata, and accounting period closing
- report exports for accounting statements and payroll workbook/Coretax sheets
- admin/user Livewire pages for company, operations, commercial, accounting, command center, leave entitlement, custom forms, WFH, and operational tasks
- route/RBAC registration, policies, and direct policy coverage
- APK smoke screenshots in `screenshots/apk-device-smoke.png`, `screenshots/apk-attendance-e2e.png`, and `screenshots/apk-document-upload-e2e.png`
- salted enterprise obfuscated PHP artifacts generated from private `*.Source.php` mirrors
- `README.md`, `guides/final-upgrade-review.md`, `guides/reviewer-evidence.md`, `guides/operations.md`, and `RELEASE_CHECKLIST.md`
- `app/Livewire/User/HomeAttendanceStatus.php` now documents that the default morning shift fallback is intentionally global until the `shifts` table receives company/branch tenant columns.
- Current collaboration follow-up adds `ChatThread`, `ChatMessage`, `CloudFile`, `OnlineMeeting`, `CloudFilePolicy`, `DownloadCloudFileController`, `CollaborationWorkspaceUpdated`, `CollaborationRealtime`, `BroadcastRuntime`, `CollaborationWorkspaceService`, the `admin.collaboration` Livewire page, the user `collaboration` inbox with private attachment upload/download, `DemoCollaborationSeeder`, and `CollaborationWorkspaceTest`.

## Typo Audit

- The misspelled Livewire variant from the commit subject was not found in tracked source, docs, or release-note files after this pass.
- No tracked docs or release-note files contain the typo after the final evidence update.

## Tailwind 4 Verification

- `resources/css/app.css` starts with `@import "tailwindcss";` followed by `@import "flatpickr/dist/flatpickr.css";`.
- CSS-first directives include forms/typography plugins, Blade/compiled/Jetstream sources, and `../js/**/*.js` plus `../js/**/*.ts`.
- `@custom-variant dark (&:where(.dark, .dark *));` and `@theme` primary/brand tokens are present.
- No `@config` directive was found.
- No standalone Tailwind JavaScript config file is tracked; Tailwind 4 config stays CSS-first in `resources/css/app.css`.
- No legacy PostCSS Tailwind plugin path is tracked; Vite remains the Tailwind integration path.
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
- New or expanded platform coverage includes `AccountingWorkspaceTest`, `AdminCompanyManagerTest`, `CollaborationWorkspaceTest`, `CommandCenterTest`, `CommercialWorkspaceTest`, `CustomFormBuilderTest`, `LeaveEntitlementManagerTest`, `MyOperationalTasksTest`, `OperationalWorkspaceTest`, and `WorkFromHomeRequestFlowTest`.
- APK device evidence now covers launch/permission readiness, attendance check-in/photo/check-out, and document upload.

## Command Results

- `composer validate`: passed after `37cf13b`; `composer.json` is valid.
- `composer check-platform-reqs`: passed on PHP `8.3.31`.
- `composer check:database-portability`: passed after the PostgreSQL-first follow-up.
- `composer check:database-portability:sqlite`: passed after the PostgreSQL-first follow-up.
- `PASPAPAN_PG_USER=lutuk PASPAPAN_PG_ADMIN_DB=absensi composer check:database-portability:pgsql`: passed after the PostgreSQL-first follow-up; the command created and dropped a temporary `paspapan_pg_smoke_*` database.
- `php artisan about`: passed; Laravel `13.9.0`, Livewire `v4.3.0`, PHP `8.3.31`.
- `php artisan config:cache`: passed after the collaboration private upload/download follow-up.
- `php artisan route:cache`: passed after the collaboration private upload/download follow-up.
- `php artisan view:cache`: passed after the collaboration private upload/download follow-up; Blade templates cached successfully.
- `php artisan optimize:clear`: passed before Livewire-heavy test runs to avoid stale cached bootstrap state.
- `php artisan test`: passed after the broadcast runtime hardening follow-up; `534` tests and `10736` assertions in `41.33s`.
- `php artisan test tests/Feature/AnnouncementBroadcastTest.php tests/Feature/CollaborationWorkspaceTest.php`: passed after the broadcast runtime hardening follow-up; `17` tests and `69` assertions in `1.51s`.
- `php artisan test tests/Feature/CollaborationWorkspaceTest.php`: passed after adding VPS-only realtime broadcast hooks; `6` tests and `24` assertions in `1.10s`.
- `php artisan test tests/Feature/CollaborationWorkspaceTest.php tests/Feature/UserMenuSmokeTest.php`: passed after adding the user collaboration inbox private upload flow; `18` tests and `94` assertions in `2.13s`.
- `php secure_tools/build_enterprise.php`: passed in salted key mode with `ENTERPRISE_OBFUSCATOR_KEY` active; 39 enterprise artifacts were secured.
- `composer check:ui`: passed after the collaboration private upload/download follow-up; 238 Blade files and 88 Livewire files scanned, 53 exact legacy baseline warnings, `0` active warnings.
- `composer check:modern-stack`: passed after the collaboration follow-up; Laravel 13, Livewire 4, Tailwind 4, Capacitor 8, PHP 8.3+, Node 20+, and Bun 1.3.6+ baselines are clean.
- `composer check:enterprise-boundary`: passed after `37cf13b`; private `secure_tools/`, source mirrors, and generated enterprise build output remain outside the OSS review boundary.
- `./vendor/bin/pint --test`: passed after the collaboration private upload/download follow-up.
- `composer phpstan`: passed after the broadcast runtime hardening follow-up, no errors.
- `composer audit`: passed after `37cf13b`, no advisories.
- `bun install`: passed with Bun `1.3.12`; no dependency changes.
- `vendor/bin/pest`: passed after `37cf13b`; `524` tests and `10681` assertions in `39.70s`.
- `bun run build`: passed after the broadcast runtime hardening follow-up; Vite `7.3.2`, `359` modules transformed, no Tailwind/PostCSS warnings, built in `3.44s`.
- `bun run e2e:smoke`: first run failed with `ERR_CONNECTION_REFUSED` because the local server was not running; after starting `php artisan serve --host=127.0.0.1 --port=8000`, the rerun passed with public, admin, and user smoke all green (`3` tests in `4.9s`).
- `bun run apk:smoke`: passed on physical device `DQEQLFCEDEKFKFZ5`; launch, camera permission, GPS permission, barcode/photo readiness, and crash log were checked.
- `bun run apk:e2e:attendance`: passed on physical device; debug APK build, check-in, photo upload, and check-out succeeded.
- `bun run apk:e2e:document-upload`: passed on physical device; debug APK build, PDF push, document upload, and processed/uploaded status succeeded.
- `bun audit`: passed after `37cf13b`, no vulnerabilities.
- `php artisan rbac:audit`: passed after the collaboration private upload/download follow-up; routes, menus, permissions, roles, and direct policy coverage all `OK`.
- `php artisan queue:work database --once`: passed.
- `git diff --check`: passed after the collaboration private upload/download follow-up.

## Commands Not Run

- `composer install` was not rerun in this follow-up because this pass did not change dependencies and the requested command list did not include it.
- None for the requested `37cf13b` evidence refresh. All requested commands were rerun; `bun run e2e:smoke` required starting the local Laravel server before the successful rerun.

## Manual Smoke Status

Covered by automated tests, Playwright, or physical APK smoke in this pass:

- Login page, admin smoke, user smoke, RBAC audit, route cache, config cache, view cache, backup drill, private attachment access, payslip privacy, attendance API/photo validation, Dynamic QR/risk scoring, payroll/privacy, import/export jobs, and operational health.
- APK launch, camera/GPS permission readiness, barcode/photo readiness, attendance check-in/photo/check-out, document upload, and crash log.
- Commit `37cf13b` user UI/accessibility smoke:
  - user dashboard attendance panel: covered by `UserMenuSmokeTest`, Playwright `/home`, and `AttendanceFaceEnforcementTest` default morning shift coverage.
  - WFH create modal: covered by `WorkFromHomeRequestFlowTest`; `/wfh-requests` uses the modal create flow with native date/time fields.
  - scan attendance camera/QR: covered by Playwright `/scan`, `AttendanceMediaAndApiTest`, and previous APK attendance/photo evidence.
  - bottom nav mobile: covered by `UserMenuSmokeTest` simplified shared navigation assertion and the profile notification badge placement in the updated Blade.
  - dark mode: covered through Tailwind 4 dark variant build plus the user shell/profile surfaces in cached Blade/build output.
  - Flatpickr/native date/time field: WFH now uses native date/time fields; legacy Flatpickr remains built through the Tailwind/Vite entrypoint for pages that still need it.
  - salary mask show/hide: employee salary masking remains in the admin employee forms and import/export views; no regression was introduced by the user-page pass.
  - translation ID/EN toggle: profile page keeps the compact ID/EN language toggle and both `lang/id.json` and `lang/en.json` were updated with the new user-facing strings.

Still physical-device or staging only:

- Full staging backup/restore drill, production queue/scheduler heartbeat, production attachment storage fallback monitoring, and release-candidate install/update lifecycle across multiple Android versions.

## Merge Recommendation

Code review ready. Backend, frontend, browser smoke, RBAC audit, queue smoke, dependency audits, physical APK smoke, and salted enterprise obfuscator validation all passed in the final release-review sequence.
