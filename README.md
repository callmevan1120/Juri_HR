<div align="center">

<img src="./public/hero-banner.png" alt="PasPapan Hero" width="880">

# PasPapan

Satu platform kerja untuk HR, absensi, payroll preparation, operasional, CRM, accounting foundation, kolaborasi tim, approval, reporting, dan mobile workforce.

[![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire 4](https://img.shields.io/badge/Livewire-4-4E56A6?style=flat-square&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Tailwind CSS 4](https://img.shields.io/badge/Tailwind-4-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)

</div>

> Dokumentasi utama PasPapan memakai Bahasa Indonesia supaya gampang dipakai tim lokal, vendor implementasi, HR, finance, dan engineer.

## Jalur Branch

| Branch | Untuk | Rekomendasi |
| --- | --- | --- |
| `main-vps` | VPS production penuh, PostgreSQL, queue worker, scheduler, private storage, Reverb, import/export panjang, Android/iOS wrapper | Gunakan ini untuk instalasi baru dan fitur lengkap |
| `main` | Track legacy/shared-hosting ringan dengan fitur lebih terbatas | Tetap tersedia untuk instalasi lama, tetapi upgrade ke `main-vps` disarankan |

Untuk free/community build yang paling aman, gunakan `main-vps` di VPS kecil atau local server dengan `BROADCAST_CONNECTION=log` dulu. Fitur enterprise tetap terkunci tanpa license, tetapi fitur open-source harus bisa install dan boot tanpa `ENTERPRISE_OBFUSCATOR_KEY`.

## Kenapa Pilih `main-vps`?

Kalau kebutuhanmu bukan cuma absensi sederhana, `main-vps` adalah versi yang paling masuk akal untuk dipakai serius. Branch ini dibuat untuk perusahaan Indonesia yang ingin punya sistem HRIS, absensi online, payroll preparation, reimbursement, kasbon, approval, operasional, CRM ringan, accounting foundation, dan laporan dalam satu aplikasi Laravel yang bisa dikontrol sendiri di VPS.

Kelebihan utama `main-vps`:

- **Lebih lengkap untuk operasional harian**: tidak hanya clock in/out, tapi juga cuti, izin, lembur, WFH, reimbursement, kasbon, dokumen karyawan, HR checklist, asset, KPI, project, task, client, quotation, invoice, dan laporan.
- **Lebih siap untuk multi cabang/multi perusahaan**: data company, branch, user, dokumen, attendance, finance, report, dan dashboard dijaga dengan company scope supaya tidak bocor antar perusahaan.
- **Lebih cocok untuk perusahaan lapangan**: absensi bisa memakai GPS, foto, barcode, Dynamic QR, Face ID, offline submission, dan risk scoring untuk bantu deteksi lokasi mencurigakan.
- **Lebih siap untuk HR dan finance Indonesia**: ada payroll period bulanan/mingguan/harian, metadata BPJS/PPh21/Coretax, THR/prorata foundation, payment instruction, payslip PDF, reimbursement, kasbon, dan aging/cashflow/journal foundation.
- **Lebih kuat untuk produksi**: default VPS memakai PostgreSQL, queue worker, scheduler, private storage, backup, Reverb/WebSocket optional, import/export background, audit log, RBAC, policy/gate, dan security checklist.
- **Lebih enak untuk vendor/implementor**: dokumentasi deploy, operations, database portability, security, release evidence, Android/iOS wrapper, dan command seed real/fake sudah dipisah.
- **Tetap bisa dipakai free/community**: fitur open-source bisa install dan boot tanpa enterprise key; fitur enterprise tetap terkunci rapi untuk deployment berlisensi.

Kata kunci yang cocok untuk menemukan PasPapan: aplikasi absensi karyawan, HRIS Indonesia, aplikasi payroll Laravel, sistem reimbursement karyawan, aplikasi kasbon karyawan, aplikasi cuti lembur WFH, absensi GPS dan foto, absensi QR code, aplikasi HR multi cabang, aplikasi operasional lapangan, aplikasi approval karyawan, aplikasi slip gaji PDF, aplikasi manajemen aset kantor, aplikasi CRM sederhana, aplikasi invoice dan quotation, aplikasi laporan Excel PDF, dan aplikasi workforce management Indonesia.

## Kenapa PasPapan?

PasPapan dibuat untuk perusahaan yang ingin aktivitas harian karyawan, HR, finance, dan operasional tidak tercecer di spreadsheet, chat, dan file manual.

Yang dikejar bukan sekadar "aplikasi absensi", tapi sistem kerja yang bisa dipakai setiap hari:

- HR bisa mengurus karyawan, kontrak, checklist onboarding/offboarding, dokumen, cuti, lembur, dan approval.
- Karyawan bisa clock in/out dari web/APK, mengajukan cuti, reimbursement, kasbon, WFH, koreksi absensi, dan melihat slip/dokumen.
- Manager bisa memantau approval, risiko absensi, pekerjaan tim, checklist, dan tugas operasional.
- Finance bisa menyiapkan payroll, payment instruction, reimbursement/kasbon, invoice, aging, dan laporan dasar.
- Operasional bisa mengelola client, project, tugas, checklist, bukti kunjungan, stock, quotation, invoice, dan follow-up sales.

## Cakupan Fitur

| Area | Yang Tersedia |
| --- | --- |
| HR & Employee | data karyawan, divisi/jabatan/level, direct manager, employee lifecycle, dokumen, HR checklist, clearance summary |
| Absensi Pintar | GPS, foto, Face ID, static barcode, Dynamic QR, offline/cached location, risk scoring, koreksi absensi |
| Approval | cuti, lembur, reimbursement, kasbon, attendance correction, shift swap, HR task, approval matrix foundation |
| Payroll | payroll period bulanan/mingguan/harian, komponen payroll, BPJS/PPh21/Coretax metadata, THR/prorata foundation, payment instruction, payslip PDF |
| Finance | reimbursement, expense, kasbon/simpan pinjam foundation, AR/AP aging, cashflow, journal, ledger, closing period, tax filing workflow |
| Operasional & CRM | branch, client, project, task, checklist, bukti kunjungan/foto, product, stock, quotation, invoice, vendor bill, sales tracking |
| Collaboration | thread personal/grup/proyek, private cloud file, secure download policy, meeting link, optional realtime via Reverb |
| Reporting | dashboard admin, KPI/analytics, Excel/PDF export, import/export background, operational health |
| Mobile | PWA, Android Capacitor APK, iOS Capacitor project dengan simulator preflight |
| Security | RBAC, policy/gate, private attachment, audit trail, multi-company isolation, enterprise feature lock |

Detail lengkap ada di [guides/features.md](./guides/features.md).

## Untuk Siapa?

- UMKM yang butuh absensi + HR + payroll preparation dalam satu sistem.
- Perusahaan lapangan yang butuh bukti lokasi, foto, QR, dan risk scoring.
- Konsultan/vendor yang mengelola banyak perusahaan/cabang.
- Tim operasional yang ingin client, project, task, checklist, invoice, dan laporan berada dalam sistem yang sama.
- Engineer Laravel yang butuh baseline aplikasi produksi dengan test, RBAC, queue, scheduler, dan deployment docs.

## Stack Resmi

- PHP `8.3+`; PHP `8.4` direkomendasikan
- Laravel `13`
- Livewire `4`
- Tailwind CSS `4` dengan CSS-first config di `resources/css/app.css`
- Vite `7`
- Node.js `20+`
- Bun `1.3.6+`
- PostgreSQL `15+` sebagai default local/VPS
- Queue/cache/session database-backed
- Capacitor `8` untuk Android dan iOS wrapper

Panduan stack ada di [guides/modern-stack.md](./guides/modern-stack.md).

## Target Deployment

| Target | Status | Keterangan |
| --- | --- | --- |
| VPS | Production penuh | Target utama: PostgreSQL, queue worker, scheduler, storage privat, backup, Reverb, import/export panjang |
| Vercel | Demo/staging ringan | Serverless, queue sync, storage `/tmp` sementara, tanpa worker/scheduler long-running |
| Shared hosting | Legacy/best-effort | Bisa untuk instalasi kecil tertentu, tetapi bukan baseline fitur penuh |

Panduan lengkap ada di [guides/deployment.md](./guides/deployment.md). Operasi harian ada di [guides/operations.md](./guides/operations.md).

## Quick Start

```bash
git clone https://github.com/RiprLutuk/PasPapan.git
cd PasPapan
git checkout main-vps

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

Worker lokal jika ingin menguji background job:

```bash
php artisan queue:work database --queue=maintenance,default --tries=3 --timeout=1800
```

## Environment Minimal

```dotenv
APP_NAME=PasPapan
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=absensi
DB_USERNAME=your_user
DB_PASSWORD=your_password
DB_SCHEMA=public
DB_SSLMODE=prefer

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
BROADCAST_CONNECTION=log
FILESYSTEM_ATTACHMENT_DISKS=local
```

## Seeder

Seeder dipisah agar aman untuk production dan tidak mencampur data contoh.

```bash
php artisan paspapan:seed-real
php artisan paspapan:seed-fake
```

- `paspapan:seed-real` untuk master data nyata: settings default, wilayah Indonesia, libur nasional, job level/title, pendidikan, divisi, shift, payroll component, KPI, template dokumen karyawan, company awal bila belum ada, dan COA per company.
- `paspapan:seed-real` tidak membuat data demo, produk demo, barcode lokasi demo, atau akun admin kecuali `BOOTSTRAP_ADMIN_SEEDING_ENABLED=true` diset secara sadar untuk bootstrap terkontrol.
- `paspapan:seed-fake` untuk lokal, QA, atau staging demo. Command ini selalu menjalankan master data real lebih dulu, lalu menambahkan akun/data/sample workflow demo.
- Jangan melakukan refresh/truncate database production kecuali operator sengaja menjalankan runbook restore/migration dengan backup.

## Realtime

Local/default bisa memakai log/polling:

```dotenv
BROADCAST_CONNECTION=log
ANNOUNCEMENT_REFRESH_MODE=auto
COLLABORATION_REALTIME_ENABLED=false
```

VPS production bisa memakai Reverb:

```bash
php -r 'echo "REVERB_APP_ID=paspapan-".bin2hex(random_bytes(6)).PHP_EOL; echo "REVERB_APP_KEY=".bin2hex(random_bytes(16)).PHP_EOL; echo "REVERB_APP_SECRET=".bin2hex(random_bytes(32)).PHP_EOL;'
php artisan reverb:start
php artisan queue:work database --queue=maintenance,default --tries=3 --timeout=1800
```

Detail ada di [guides/operations.md](./guides/operations.md).

## Quality Gate

Command yang paling sering dipakai sebelum merge/rilis:

```bash
php artisan test
composer check:ui
composer check:modern-stack
composer check:database-portability
composer check:enterprise-boundary
./vendor/bin/pint --test
composer phpstan
composer audit
bun audit
bun run build
php artisan rbac:audit
```

Smoke tambahan:

```bash
bun run e2e:smoke
bun run apk:smoke
bun run apk:e2e:attendance
bun run ios:preflight
```

Checklist rilis ada di [RELEASE_CHECKLIST.md](./RELEASE_CHECKLIST.md).

## Mobile

- Android APK memakai Capacitor.
- iOS project sudah tersedia untuk simulator preflight.
- Icon dan splash diambil dari `resources/icon.png` dan `resources/splash.png`.
- iOS belum diklaim App Store/TestFlight-ready sampai signing, provisioning, TestFlight upload, dan smoke test iPhone fisik selesai.

Panduan iOS ada di [guides/ios-release.md](./guides/ios-release.md).

## Dokumentasi

Mulai dari [guides/README.md](./guides/README.md) untuk mencari dokumen yang tepat.

Jalur cepat:

- [Fitur produk](./guides/features.md)
- [Deployment](./guides/deployment.md)
- [Operasi harian](./guides/operations.md)
- [Security model](./guides/security-model.md)
- [Database portability](./guides/database-portability.md)
- [Attendance integration API](./guides/attendance-integration.md)
- [Feature maturity](./guides/feature-maturity.md)
- [Reviewer evidence](./guides/reviewer-evidence.md)

## Rilis

Rilis APK publik terbaru tetap: [`v4.3.0`](https://github.com/RiprLutuk/PasPapan/releases/tag/v4.3.0)

Branch fitur terbaru dan paling lengkap: [`main-vps`](https://github.com/RiprLutuk/PasPapan/tree/main-vps)

- APK: [`PasPapan-v4.3.0.apk`](https://github.com/RiprLutuk/PasPapan/releases/download/v4.3.0/PasPapan-v4.3.0.apk)
- Checksum: [`PasPapan-v4.3.0.apk.sha256`](https://github.com/RiprLutuk/PasPapan/releases/download/v4.3.0/PasPapan-v4.3.0.apk.sha256)
- Changelog: [CHANGELOG.md](./CHANGELOG.md)

## Demo

Demo publik, jika aktif:

- [paspapan.vercel.app](https://paspapan.vercel.app)
- [paspapan.pandanteknik.com](https://paspapan.pandanteknik.com)

Kredensial demo tidak dipublikasikan di README. Operator dapat membuat akun demo khusus di local/staging. Jangan memakai akun demo atau password contoh untuk production.

## Enterprise

Repository open source harus bisa `composer install` dan boot tanpa `ENTERPRISE_OBFUSCATOR_KEY`.

Fitur enterprise yang dibuild sebagai artifact obfuscated salted dapat membutuhkan `ENTERPRISE_OBFUSCATOR_KEY` di runtime customer. `ENTERPRISE_LICENSE_KEY` hanya untuk validasi lisensi fitur, bukan secret obfuscator.

Panduan packaging ada di [guides/enterprise-packaging.md](./guides/enterprise-packaging.md).

## Kontribusi

Baca [CONTRIBUTING.md](./CONTRIBUTING.md), [SECURITY.md](./SECURITY.md), dan [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md).

## Dukung Pengembangan

Kalau project ini membantu tim Anda dan Anda ingin mendukung pengembangannya, silakan scan QR GoPay berikut.

<div align="center">
  <img src="./screenshots/donation-qr.jpeg" alt="QR Dukungan GoPay" width="220">
  <p><strong>GoPay Support</strong></p>
</div>

## Kredit

[RiprLutuk](https://github.com/RiprLutuk).
