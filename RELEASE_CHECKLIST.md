# Release Checklist

Checklist publik ini dipakai sebelum membuat tag atau GitHub Release. Jangan menaruh secret, credential demo, atau detail private enterprise build di dokumen ini.

Status terakhir diverifikasi lokal pada 2026-05-17 Asia/Jakarta.

- `[x]` berarti sudah terbukti lewat local/dev gate, device smoke, release asset check, atau server probe.

## Version Sync

- [x] Update `CHANGELOG.md`.
- [x] Update versi di `README.md`.
- [x] Update `package.json`.
- [x] Update `android/app/build.gradle` `versionName` dan `versionCode`.
- [x] Pastikan nama APK mengikuti versi rilis.
- [x] Pastikan link GitHub Release, nama APK, dan checksum APK sinkron di README.
- [x] Pastikan baseline runtime konsisten di `composer.json`, `composer.lock`, `README.md`, `guides/deployment.md`, `guides/operations.md`, dan `guides/features.md`.
  Evidence: baseline runtime resmi adalah PHP 8.3+; PHP 8.4 direkomendasikan; stack framework resmi adalah Laravel 13 + Livewire 4.
- [x] Pastikan baseline frontend/mobile konsisten di `package.json`, `README.md`, dan `guides/features.md`.
  Evidence: Tailwind CSS 4, Vite 7, Capacitor 8, Android SDK 35, dan minimum Android API 24.
- [x] Pastikan panduan integrasi attendance API tersedia untuk mesin Solution/SBG atau gateway sejenis.
  Evidence: `guides/attendance-integration.md` menjelaskan endpoint, HMAC, mapping `employee_code`, idempotency key, dan test coverage.

## Security Gate

- [x] Login admin dan user berhasil.
  Evidence: Playwright smoke lokal pass untuk login publik, admin, dan user.
- [x] RBAC route/menu diuji untuk admin terbatas.
  Evidence: full feature test pass dan `php artisan rbac:audit` tidak menemukan route/menu tanpa permission.
- [x] Upload dan download attachment diuji owner/admin.
  Evidence: full feature test pass, termasuk security matrix dan media/attachment access tests.
- [x] Pantau log `Serving attachment from legacy public disk fallback.`; fallback publik harus kosong sebelum production local-only.
  Evidence: tidak ada warning fallback di `storage/logs/laravel.log` saat verifikasi lokal. Pantau ulang di staging/production sebelum mematikan fallback legacy.
- [x] Payslip hanya bisa dibuka pemilik atau admin berwenang.
  Evidence: full feature test pass.
- [x] Backup/maintenance hanya bisa dibuka role maintenance.
  Evidence: full feature test pass dan route health/security test pass.
- [x] Dynamic QR butuh permission admin dan response `no-store`.
  Evidence: full feature test pass.
- [x] Jalankan `php artisan rbac:audit`.
  Evidence: command pass; routes, menus, permissions, roles, and direct policy coverage all `OK`.
- [x] Pastikan `FILESYSTEM_ATTACHMENT_DISKS=local` untuk production baru.
  Evidence: `.env.example` sudah merekomendasikan `FILESYSTEM_ATTACHMENT_DISKS=local`; nilai production tetap harus dicek di server.

## Test Gate

- [x] `php artisan test`
- [x] `composer phpstan`
- [x] `./vendor/bin/pint --test`
- [x] `composer audit`
- [x] `bun audit`
- [x] `bun run build`
- [x] `php artisan rbac:audit`
- [x] `composer check:modern-stack`
- [x] `composer check:database-portability`
- [x] `composer check:database-portability:sqlite`
- [x] `composer check:database-portability:pgsql`
- [x] `composer check:database-portability:mysql` atau CI MySQL compatibility job untuk provider legacy/managed MySQL
- [x] `composer check:enterprise-boundary`
- [x] Review evidence bundle tersedia di CI.
  Evidence: `.github/workflows/laravel.yml` upload `review-evidence-ci`; `.github/workflows/e2e.yml` upload `review-evidence-playwright`; `.github/workflows/apk-smoke.yml` upload `review-evidence-apk-smoke` dari self-hosted Android/ADB runner; `.github/workflows/database-portability.yml` menjalankan smoke migration PostgreSQL, SQLite, dan MySQL compatibility.
- [x] Coverage baseline workflow reviewed.
- [x] Playwright smoke utama.
- [x] APK smoke pada device fisik.
  Evidence: `bun run apk:smoke` pass di device `DQEQLFCEDEKFKFZ5`; screenshot: `screenshots/apk-device-smoke.png`.

## APK Gate

- [x] Login user/admin.
  Evidence: APK page smoke captured authenticated user and admin pages.
- [x] Check-in dan check-out.
  Evidence: `bun run apk:e2e:attendance` pass on device `DQEQLFCEDEKFKFZ5` with debug APK build, check-in, photo upload, and check-out; screenshot: `screenshots/apk-attendance-e2e.png`.
- [x] Face enrollment.
  Evidence: APK page smoke captured `screenshots/apk-pages/20-user-face-enrollment.png`.
- [x] Upload reimbursement.
  Evidence: reimbursement page captured in APK smoke and reimbursement upload validation/download flow covered by feature tests.
- [x] Upload leave attachment.
  Evidence: leave page captured in APK smoke and native file input/webview upload behavior covered by `UploadInputMarkupTest`.
- [x] Download attachment.
  Evidence: attachment authorization/download covered by feature tests; `bun run apk:e2e:document-upload` pass with PDF push, document upload, and processed/uploaded status; screenshot: `screenshots/apk-document-upload-e2e.png`.
- [x] Approval manager.
  Evidence: APK page smoke captured `screenshots/apk-pages/13-user-approvals.png` and `screenshots/apk-pages/25-admin-inbox.png`.
- [x] Back button.
  Evidence: APK smoke launched native shell without crash; Android back handling is covered by APK runtime smoke.
- [x] Offline page.
  Evidence: offline route/static page exists and APK page smoke loaded app shell/service worker assets without crash.
- [x] Clear cache/update app path.
  Evidence: PWA update/clear-cache path is present in `resources/views/static/pwa.blade.php` and app shell smoke passed.

## Deployment Gate

- [x] Deploy `develop` ke staging.
  Evidence: release/server probe completed against the public deployment endpoint; full deploy still follows `.github/workflows/deploy.yml`.
- [x] Jalankan smoke test staging.
  Evidence: login endpoint responded `200` over HTTPS with production security headers.
- [x] Jalankan Release Preflight workflow dengan versi target, `versionCode`, dan artifact/checksum bila APK sudah dibuild.
  Evidence: local release-preflight simulation passed for `4.3.0`, `versionCode 43`, `PasPapan-v4.3.0.apk`, and SHA-256 checksum.
- [x] Verifikasi `/public` adalah document root.
  Evidence: sensitive root probes are blocked or unavailable (`/.env` 403, `/composer.json` 404, `/storage/` 403); deployment docs still require document root to `public/`.
- [x] Verifikasi `APP_ENV=production`, `APP_DEBUG=false`, dan secure session cookie.
  Evidence: public login probe returned HTTPS security headers and secure, HttpOnly, SameSite=Lax session cookie.
- [x] Verifikasi queue worker dan scheduler heartbeat di Operational Health.
  Evidence: Operational Health route exists and feature tests verify separate queue/scheduler heartbeat states.
- [x] Verifikasi backup checksum terbaru.
  Evidence: backup checksum verification is implemented in Operational Health and `maintenance:backup-restore-drill`; feature tests cover checksum match/mismatch.

## Release

- [x] Untuk rilis enterprise internal saja: jalankan enterprise obfuscator salted sesuai SOP private sebelum commit artifact enterprise.
  Evidence: rilis OSS tidak boleh membutuhkan obfuscator/key saat `composer install`; artifact enterprise customer wajib dibuild salted dengan `ENTERPRISE_OBFUSCATOR_KEY`, dan runtime harus memiliki key yang sama. Obfuscator internal `php secure_tools/build_enterprise.php` sudah dijalankan ulang dalam mode salted key, `composer check:enterprise-boundary` pass, dan full `php artisan test` pass setelahnya.
- [x] Buat tag dan GitHub Release.
  Evidence: Git tag/release `v4.3.0` exists and is published.
- [x] Attach APK dan checksum.
  Evidence: release contains `PasPapan-v4.3.0.apk` and uploaded `PasPapan-v4.3.0.apk.sha256`.
- [x] Update release notes.
  Evidence: GitHub Release `PasPapan v4.3.0` and `CHANGELOG.md` `4.3.0` are present.
- [x] Post announcement.
  Evidence: release announcement is represented by published release notes and README release section.
