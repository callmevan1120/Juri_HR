# Reviewer Evidence Bundle

Evidence bundle adalah artifact CI yang berisi output command, Playwright report, dan APK smoke report agar reviewer tidak perlu percaya klaim checklist secara manual.

## CI Artifact

Workflow utama:

- `.github/workflows/laravel.yml` menghasilkan artifact `review-evidence-ci`.
- `.github/workflows/e2e.yml` menghasilkan artifact `review-evidence-playwright` dan `playwright-report`.
- `.github/workflows/apk-smoke.yml` menghasilkan artifact `review-evidence-apk-smoke` dari self-hosted runner Android/ADB.

Isi `review-evidence-ci`:

- `php-artisan-test.log`
- `composer-phpstan.log`
- `pint-test.log`
- `composer-audit.log`
- `bun-audit.log`
- `bun-run-build.log`
- `php-artisan-rbac-audit.log`
- `composer-check-ui.log`
- `composer-check-modern-stack.log`
- `composer-check-enterprise-boundary.log`
- `summary.md`

Isi `review-evidence-playwright`:

- `playwright-smoke.log`
- `playwright-summary.md`
- `test-results/` bila ada failure context
- `playwright-report/` pada artifact terpisah

Isi `review-evidence-apk-smoke`:

- `apk-smoke.log`
- `apk-device-smoke.png`
- `apk-smoke-summary.md`

## Critical Regression Scope

Feature/unit tests tetap menjadi bukti utama untuk domain kritikal:

- login dan active account guard
- RBAC route/menu dan `php artisan rbac:audit`
- absensi check-in/check-out, offline sync, risk scoring, dan Dynamic QR
- upload/download attachment private dan path traversal guard
- payslip privacy dan payroll-sensitive audit trail
- approval cuti, lembur, reimbursement, kasbon, attendance correction, shift swap, dan HR checklist task
- import/export background run
- backup/restore drill dan checksum
- multi-company isolation

Playwright smoke menambahkan bukti browser untuk halaman utama admin/user. APK smoke menambahkan bukti device untuk launch, permission kamera/GPS, barcode/photo readiness, screenshot, dan crash log.

## Local Reproduction

Untuk menghasilkan bundle lokal dengan struktur log yang sama:

```bash
bun run evidence:review
```

Output default berada di `storage/review-evidence/local/`. Playwright dan APK smoke dibuat opt-in karena membutuhkan server E2E atau device ADB:

```bash
RUN_E2E=1 bun run evidence:review
RUN_APK_SMOKE=1 bun run evidence:review
RUN_E2E=1 RUN_APK_SMOKE=1 bun run evidence:review
```

Command manual yang setara:

```bash
php artisan test --without-tty
composer phpstan
./vendor/bin/pint --test
composer audit
bun audit
bun run build
php artisan rbac:audit
composer check:modern-stack
bun run e2e:smoke
```

APK smoke lokal membutuhkan device ADB authorized:

```bash
bun run apk:smoke
bun run apk:e2e:attendance
bun run apk:e2e:document-upload
```

## Runtime Baseline

- PHP `8.3+`; PHP `8.4` direkomendasikan untuk production baru.
- Node.js `20+`.
- Bun `1.3.6+`.
- Java `21` untuk build Android.
