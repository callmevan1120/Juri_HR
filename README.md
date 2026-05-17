<div align="center">

<img src="./public/hero-banner.png" alt="PasPapan Hero" width="880">

# PasPapan

Platform kerja terpadu berbasis Laravel untuk HR, absensi aman, approval, payroll preparation, accounting foundation, CRM/operasional, reporting, aset, dan mobile workforce.

[![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire 4](https://img.shields.io/badge/Livewire-4-4E56A6?style=flat-square&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)

</div>

> Dokumentasi utama project ini memakai Bahasa Indonesia.

## Ringkasan

PasPapan adalah aplikasi workforce untuk organisasi yang membutuhkan absensi mobile, HR checklist onboarding/offboarding, workflow approval, persiapan payroll, import/export, reporting, accounting foundation, CRM/operasional, dan maintenance system dalam satu aplikasi Laravel deployable.

Fokus utama aplikasi:

- absensi aman dengan GPS, foto, Face ID, static barcode, dan Dynamic QR
- HR Checklist v2 untuk onboarding/offboarding, task dependency ringan, overdue indicator, attachment task, summary clearance, dan self-service HR Tasks
- attendance risk scoring untuk mock location, radius boundary, device context, barcode source, face verification, offline/cached location, dan jam di luar shift
- panel admin untuk karyawan, absensi, cuti, lembur, reimbursement, kasbon, aset, payroll, reports, settings, dan maintenance
- self-service karyawan untuk check-in/out, koreksi absensi, cuti, lembur, reimbursement, slip gaji, dokumen, jadwal, HR tasks, dan approval tim
- approval matrix reusable untuk reimbursement, kasbon, koreksi absensi, cuti, lembur, asset/document/payroll-sensitive workflow foundation
- multi-company dan company branch foundation untuk isolasi data perusahaan/cabang
- operations workspace untuk client, project, task, checklist, bukti kunjungan, dan bukti foto
- commercial workspace untuk client, product, stock movement, quotation, invoice, sales opportunity, follow-up, dan vendor bill foundation
- accounting workspace untuk chart of accounts, journal, AR/AP aging, cashflow, ledger detail, export Excel, dan closing period
- command center untuk pending approval, overdue HR task, attendance risk, payroll readiness, profile completeness, dan contract expiry
- custom form builder untuk request/form operasional yang bisa berubah tanpa migration baru
- WFH request dan leave entitlement untuk alokasi/expiry cuti
- payroll Indonesia foundation untuk period bulanan/mingguan/harian, BPJS, PPh21 TER/Coretax metadata, payment instruction, dan workbook export
- generic attendance integration API untuk mesin absensi/gateway seperti Solution atau SBG
- import/export background dengan progress run, ringkasan sukses/error, download hasil, dan cleanup otomatis
- operational health dashboard untuk queue, scheduler, backup, disk, database, cache/session/queue driver, app version, Reverb/polling, dan license status
- wrapper Android berbasis Capacitor untuk kebutuhan APK
- modul enterprise-gated untuk fitur lanjutan tertentu

Detail fitur lengkap ada di [guides/features.md](./guides/features.md). Panduan integrasi mesin absensi ada di [guides/attendance-integration.md](./guides/attendance-integration.md). Baseline stack modern ada di [guides/modern-stack.md](./guides/modern-stack.md). Coverage roadmap menuju 10/10 ada di [guides/roadmap-10-coverage.md](./guides/roadmap-10-coverage.md). Checklist rilis publik tersedia di [RELEASE_CHECKLIST.md](./RELEASE_CHECKLIST.md).
Evidence bundle untuk reviewer tersedia di [guides/reviewer-evidence.md](./guides/reviewer-evidence.md).

## Stack

- Laravel `13`
- PHP `8.3+` minimum; PHP `8.4` direkomendasikan
- Livewire `4`
- Tailwind CSS `4`
- Vite `7`
- Node.js `20+`
- Bun `1.3.6+`
- MySQL atau MariaDB
- Pest untuk test suite
- Capacitor `8`
- Android SDK `35` dengan minimum Android API `24`

Runtime default aplikasi database-centric:

- `DB_CONNECTION=mysql`
- `QUEUE_CONNECTION=database`
- `CACHE_STORE=database`
- `SESSION_DRIVER=database`
- `FILESYSTEM_DISK=local`
- `FILESYSTEM_ATTACHMENT_DISKS=local`
- realtime announcement hybrid: shared hosting memakai fallback polling ringan, VPS bisa memakai Reverb WebSocket
- timezone `Asia/Jakarta`
- locale `id`

Modul HR Checklist berjalan tanpa Redis, Horizon, atau Reverb sebagai baseline. Data checklist disimpan di database dan dapat dipakai di shared hosting selama migration, session, cache, dan queue database dasar tersedia.

Baseline runtime resmi PasPapan adalah PHP 8.3+. PHP 8.4 direkomendasikan untuk production baru karena dukungan platform lebih panjang dan performa runtime lebih segar. Stack framework resmi saat ini adalah Laravel 13 + Livewire 4. Konfigurasi MySQL/MariaDB memakai `Pdo\Mysql::ATTR_SSL_CA` ketika tersedia dengan fallback kompatibel untuk runtime PHP 8.3.

Vercel memakai runtime serverless, jadi default production-nya berbeda dari VPS/shared hosting:

- `SESSION_DRIVER=cookie`
- `CACHE_STORE=array`
- `QUEUE_CONNECTION=sync`
- `LOG_CHANNEL=stderr`
- `APP_STORAGE_PATH=/tmp/storage`
- `BROADCAST_CONNECTION=log`

Gunakan [`.env.vercel.example`](./.env.vercel.example) sebagai template environment Vercel. Jangan memakai `SESSION_DRIVER=database`, `CACHE_STORE=database`, atau `QUEUE_CONNECTION=database` di Vercel kecuali ada worker/cache eksternal yang memang sudah disiapkan.

## Rilis Terbaru

Rilis terbaru: [`v4.3.0`](https://github.com/RiprLutuk/PasPapan/releases/tag/v4.3.0)

- APK Android: [`PasPapan-v4.3.0.apk`](https://github.com/RiprLutuk/PasPapan/releases/download/v4.3.0/PasPapan-v4.3.0.apk)
- Checksum APK: [`PasPapan-v4.3.0.apk.sha256`](https://github.com/RiprLutuk/PasPapan/releases/download/v4.3.0/PasPapan-v4.3.0.apk.sha256)
- Changelog: [`CHANGELOG.md`](./CHANGELOG.md)
- ID aplikasi Android: `com.pandanteknik.paspapan`
- Versi Android: `4.3.0` (`versionCode 43`)

Sorotan `v4.3.0`:

- security matrix, multi-company isolation guard, private attachment policy, dan CI security scanning diperluas
- Playwright smoke workflow dan Android smoke/E2E tersedia untuk regresi utama
- screenshot katalog menu diperbarui menjadi 62 halaman untuk desktop dan APK
- source review enterprise dipisahkan dari artifact runtime obfuscated melalui `.gitattributes` dan panduan packaging

Sorotan branch `chore/major-upgrade-audit`:

- Laravel 13, Livewire 4, Tailwind CSS 4, Vite 7, PHP 8.3+, Node 20+, Bun 1.3.6+, dan Capacitor 8 sudah menjadi baseline modern stack.
- Foundation produk diperluas ke multi-company/branch, operations workspace, commercial/CRM workspace, accounting workspace, custom form builder, leave entitlement, WFH request, command center, dan payroll period/Coretax export.
- Evidence terakhir: `php artisan test` pass `505` tests / `10246` assertions setelah enterprise obfuscator salted dijalankan ulang, `composer check:enterprise-boundary`, `composer phpstan`, Pint, composer/bun audit, `bun run build`, `php artisan rbac:audit`, browser smoke, APK smoke, APK attendance E2E, dan APK document upload E2E pass.
- Commit evidence sebelumnya: `99cde52` untuk screenshot APK smoke, `0ed8b9f` untuk refresh dokumen release evidence, dan `c4d69fe` untuk sinkronisasi README.

## Enterprise Offline

Rilis ini memperkuat mode enterprise offline tanpa server lisensi:

- lisensi bertanda tangan mendukung allow-all atau daftar fitur spesifik
- gate enterprise mengecek fitur per modul, bukan hanya status lisensi global
- validasi lisensi memakai cache request dan cache aplikasi agar menu/gate tidak mem-parse lisensi berulang
- runtime enterprise sudah dioptimalkan agar proteksi offline tidak membuat halaman admin lambat
- komponen internal penerbitan lisensi tidak disertakan pada deployment klien

## HR Checklist

Modul `HR Checklists` membantu HR UMKM memastikan onboarding dan offboarding tidak bergantung pada ingatan manual.

- Admin/HR membuka `Master Data > HR Checklists` untuk membuat case onboarding atau offboarding.
- Template default dibuat otomatis untuk onboarding dan offboarding.
- Task dapat ditugaskan ke HR, karyawan, atau atasan langsung karyawan.
- Task menampilkan overdue indicator, dependency sederhana, dan attachment privat bila task membutuhkan bukti dokumen.
- Karyawan dan manager membuka `HR Tasks` dari quick action untuk menyelesaikan task mereka.
- Summary completion/clearance membantu HR melihat kesiapan onboarding/offboarding.
- RBAC memakai permission `admin.hr_checklists.view` dan `admin.hr_checklists.manage`.
- Semua label UI tersedia di `lang/id.json` dan `lang/en.json`.

## Quick Start

```bash
git clone https://github.com/RiprLutuk/PasPapan.git
cd PasPapan

php -v
node -v
bun --version
composer check-platform-reqs
composer install
bun install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

Jalankan aplikasi:

```bash
php artisan serve
bun run dev
```

Opsional untuk tes background job lokal:

```bash
php artisan queue:work database --queue=maintenance,default
```

## Environment Minimal

```dotenv
APP_NAME=PasPapan
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi
DB_USERNAME=your_user
DB_PASSWORD=your_password

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
BROADCAST_CONNECTION=log
ANNOUNCEMENT_REFRESH_MODE=auto
ANNOUNCEMENT_POLL_INTERVAL=60s
```

## Realtime Hybrid

PasPapan mendukung dua mode announcement/notification refresh:

- Shared hosting UMKM: gunakan `BROADCAST_CONNECTION=log` dengan `ANNOUNCEMENT_REFRESH_MODE=auto`. Aplikasi fallback ke polling ringan setiap `ANNOUNCEMENT_POLL_INTERVAL`.
- VPS: gunakan `BROADCAST_CONNECTION=reverb`. Aplikasi memakai Laravel Reverb + Echo sehingga announcement baru dikirim lewat WebSocket tanpa polling berkala.

Contoh VPS Reverb:

```dotenv
BROADCAST_CONNECTION=reverb
ANNOUNCEMENT_REFRESH_MODE=auto
REVERB_APP_ID=local-paspapan
REVERB_APP_KEY=change-me
REVERB_APP_SECRET=change-me-secret
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```

Di VPS jalankan proses long-running:

```bash
php artisan queue:work database --queue=maintenance,default --tries=3 --timeout=1800
php artisan reverb:start
```

## Deployment

Target produksi paling lengkap adalah VPS karena PasPapan memakai queue worker, scheduler, storage lokal, dan background job.

Panduan deployment dipisahkan di [guides/deployment.md](./guides/deployment.md):

- VPS dengan Nginx/Apache, Supervisor, dan cron
- shared hosting dengan cron fallback
- Vercel memakai [`vercel-community/php`](https://github.com/vercel-community/php)

Ringkasan target deploy:

| Target | Status | Catatan |
| --- | --- | --- |
| VPS | Production penuh | Target utama karena mendukung queue worker, scheduler, storage lokal/private, backup, Reverb, dan job background panjang. |
| Shared hosting | Production terbatas | Aman untuk instalasi kecil jika PHP 8.3+, cron, MySQL, dan document root `public/` tersedia; Reverb dan worker permanen biasanya tidak tersedia. |
| Vercel | Demo/staging/ringan | Aman untuk demo serverless; queue `sync`, storage `/tmp` ephemeral, tanpa scheduler/worker permanen, dan tidak cocok untuk backup/file-heavy production. |

File pendukung Vercel yang sudah tersedia:

- [`vercel.json`](./vercel.json)
- [`api/index.php`](./api/index.php)
- [`api/php.ini`](./api/php.ini)
- [`.env.vercel.example`](./.env.vercel.example)
- [`.vercelignore`](./.vercelignore)

Catatan Vercel: set semua environment variable lewat Vercel Dashboard atau CLI, lalu redeploy. Untuk TiDB/MySQL managed yang butuh TLS, isi `MYSQL_ATTR_SSL_CA` sesuai path CA runtime provider.

## Operasi

Panduan operasional ada di [guides/operations.md](./guides/operations.md):

- queue dan scheduler
- backup dan maintenance
- import/export run retention
- workflow update
- testing dan quality check
- Android build
- catatan produksi

Command yang paling sering dipakai:

```bash
php artisan queue:work database --queue=maintenance,default --tries=3 --timeout=1800
php artisan schedule:run
php artisan queue:failed
php artisan queue:retry all
php artisan queue:restart
```

## Testing

```bash
php artisan test --without-tty
composer check:ui
composer check:modern-stack
composer check:enterprise-boundary
./vendor/bin/pint --test
composer phpstan
composer audit
bun audit
bun run build
php artisan rbac:audit
```

Smoke dan screenshot tambahan:

```bash
bun run e2e:smoke
bun run screenshots:desktop
bun run screenshots:apk
bun run apk:smoke
bun run apk:e2e:attendance
bun run apk:e2e:document-upload
```

Screenshot katalog saat ini berisi 62 halaman menu/page untuk desktop dan APK. Manifest berada di:

- [`screenshots/desktop-pages/manifest.json`](./screenshots/desktop-pages/manifest.json)
- [`screenshots/apk-pages/manifest.json`](./screenshots/apk-pages/manifest.json)

Evidence APK terakhir:

- `screenshots/apk-device-smoke.png`
- `screenshots/apk-attendance-e2e.png`
- `screenshots/apk-document-upload-e2e.png`

## Demo

Gunakan platform di sandbox simulasi terbatas.

Link akses:

- Demo Vercel: [paspapan.vercel.app](https://paspapan.vercel.app)
- Demo produksi: [paspapan.pandanteknik.com](https://paspapan.pandanteknik.com)

Akun demo:
| Role | Email Login | Password |
| --- | --- | --- |
| Admin | `admin123@paspapan.com` | `12345678` |
| User | `user123@paspapan.com` | `12345678` |

Akun demo hanya boleh dipakai untuk environment lokal/staging atau demo publik yang sengaja disiapkan oleh operator. Kredensial di atas adalah kredensial demo dan tidak boleh digunakan ulang sebagai kredensial produksi. Demo Vercel berjalan di runtime serverless, sehingga fitur yang bergantung pada worker/background job panjang, storage lokal permanen, atau proses realtime long-running lebih cocok diuji di deployment VPS/shared hosting.

## Dukung Pengembangan

Kalau project ini membantu tim Anda dan Anda ingin mendukung pengembangannya, silakan scan QR GoPay berikut.

<div align="center">
  <img src="./screenshots/donation-qr.jpeg" alt="QR Dukungan GoPay" width="220">
  <p><strong>GoPay Support</strong></p>
</div>

## Kredit

[RiprLutuk](https://github.com/RiprLutuk).
