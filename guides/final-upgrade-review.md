# Final Upgrade Review

Tanggal review: 24 Mei 2026

Branch sumber: `chore/major-upgrade-audit`
Target release branch: `main-vps`
Target release version: `v5.0.0`
Baseline sebelum pass ini: `a2a3559`
Release-prep commit awal: `3a6bd56`

## Ringkasan

Pass ini menyiapkan PasPapan sebagai track produksi VPS penuh. `main-vps` menjadi branch rekomendasi untuk instalasi baru dengan PostgreSQL, queue worker, scheduler, private storage, Reverb, import/export, Android/iOS wrapper, dan modul HR/finance/ops/commercial/collaboration lengkap. Branch `main` tetap dipertahankan sebagai jalur legacy/shared-hosting ringan dan akan diberi notice agar pengguna diarahkan naik ke `main-vps`.

## Perubahan Final

- Menambahkan dokumentasi branch `main-vps` di README, pusat dokumen, deployment, operations, dan changelog.
- Menetapkan metadata rilis mayor `v5.0.0` untuk package, Android, iOS, README, changelog, dan release checklist.
- Membuat `update.sh` branch-aware: default `main-vps`, tetap bisa `main` jika eksplisit untuk legacy.
- Menambahkan `PASPAPAN_RELEASE_BRANCH=main-vps` ke `.env.example`.
- Memastikan script smoke yang direferensikan fresh clone/CI ikut tracked, bukan lagi tertahan `.gitignore`.
- Memperluas database portability smoke ke approval workflow dan attendance media/API selain WFH dan payroll.
- Memperbaiki migration cash advance status untuk PostgreSQL dengan drop constraint enum legacy sebelum kolom menjadi string.
- Menjalankan ulang enterprise obfuscator salted runtime artifact.

## Evidence Commands

| Command | Result |
| --- | --- |
| `composer validate` | PASS |
| `bun install` | PASS, no changes |
| `bun run build` | PASS |
| Release metadata sync (`package.json`, README, changelog, Android, iOS) | PASS, `5.0.0` / `versionCode 50` / `CURRENT_PROJECT_VERSION 50` |
| `composer check-platform-reqs` | PASS, PHP 8.3.31 dan extension required terpenuhi |
| `composer check:modern-stack` | PASS |
| `composer check:ui` | PASS, 0 active warning, 31 baseline legacy |
| `composer check:enterprise-boundary` | PASS |
| `composer check:database-portability` | PASS |
| `composer check:database-portability:sqlite` | PASS, 69 tests / 353 assertions |
| `composer check:database-portability:pgsql` | PASS, 69 tests / 353 assertions |
| `composer check:database-portability:mysql` | NOT RUN TO COMPLETION, local MySQL rejected `root@localhost` with `ERROR 1698 (28000)` |
| `php artisan config:cache` | PASS |
| `php artisan route:cache` | PASS |
| `php artisan view:cache` | PASS |
| `php artisan test` | PASS, 568 tests / 11094 assertions |
| `vendor/bin/pest` | PASS, 568 tests / 11094 assertions |
| `composer phpstan` | PASS |
| `composer audit` | PASS, no advisories |
| `bun audit` | PASS, no vulnerabilities |
| `bun run e2e:smoke` | PASS, 3 Playwright tests |
| `php artisan rbac:audit` | PASS, all sections OK |
| `./vendor/bin/pint --test --dirty` | PASS |
| `php secure_tools/build_enterprise.php` | PASS, 39 enterprise files secured with salted runtime key |
| `git diff --check` | PASS |
| `gh workflow run database-portability.yml --ref main-vps` | TRIGGERED: https://github.com/RiprLutuk/PasPapan/actions/runs/26355693441 |

## Security Notes

- Open-source/community install still boots without `ENTERPRISE_OBFUSCATOR_KEY`; only salted enterprise artifacts require the runtime key.
- `secure_tools/`, `enterprise_build/`, and `*.Source.php` remain untracked.
- Multi-company isolation is covered by feature tests for user, attendance, document, HR checklist, import/export, report, and dashboard scopes.
- Database portability is now validated by SQLite and PostgreSQL locally. MySQL smoke needs valid local MySQL credentials or CI secrets to complete.
- GitHub workflow dispatch untuk database portability berhasil dibuat, tetapi run `26355693441` selesai `failure` tanpa step log yang bisa diambil (`BlobNotFound`). Local SQLite/PostgreSQL smoke sudah hijau; CI run ini perlu rerun/inspect dari GitHub UI.

## Demo / Free Build

Belum ada public demo host yang ditetapkan. Untuk free/community review, gunakan `main-vps` di local/VPS kecil dengan:

```bash
composer install
bun install
php artisan migrate
php artisan paspapan:seed-real
php artisan serve
```

Gunakan `php artisan paspapan:seed-fake` hanya untuk staging/demo, bukan production.

## Merge Recommendation

Layak dipublish ke `main-vps` setelah commit final. Setelah itu, branch `main` cukup diberi notice bahwa fitur penuh sekarang ada di `main-vps`.
