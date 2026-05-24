<div align="center">

<img src="./public/hero-banner.png" alt="PasPapan Hero" width="880">

# PasPapan

Sistem HRIS Indonesia self-hosted untuk absensi GPS/foto/QR, cuti-lembur-WFH, payroll dan slip gaji PDF, reimbursement, kasbon, approval, multi-cabang, laporan Excel/PDF, dan operasional lapangan.

[![Release](https://img.shields.io/badge/Release-v5.0.0-3f7d3a?style=flat-square)](https://github.com/RiprLutuk/PasPapan/releases/tag/v5.0.0)
[![Self Hosted](https://img.shields.io/badge/Self--Hosted-VPS%20Ready-2563eb?style=flat-square)](./guides/deployment.md)
[![Android APK](https://img.shields.io/badge/Android-APK-3f7d3a?style=flat-square)](https://github.com/RiprLutuk/PasPapan/releases/download/v5.0.0/PasPapan-v5.0.0.apk)
[![License](https://img.shields.io/badge/Open%20Source-Community-111827?style=flat-square)](#open-source--enterprise)

</div>

PasPapan dibuat untuk perusahaan yang ingin merapikan kerja harian HR, finance, manager, dan tim operasional dalam satu sistem yang bisa dikontrol sendiri. Cocok untuk tim yang selama ini masih memakai spreadsheet, chat manual, form terpisah, dan file penggajian yang tercecer.

Dengan PasPapan, karyawan bisa absen, mengajukan cuti/lembur/WFH, reimbursement, kasbon, dan melihat dokumen atau slip gaji. HR dan manager bisa memantau approval, data karyawan, checklist, risiko absensi, laporan, serta aktivitas tim tanpa pindah-pindah aplikasi.

## Yang Bisa Dikerjakan

| Area | Fitur Utama |
| --- | --- |
| HR & Karyawan | data karyawan, divisi/jabatan/level, direct manager, kontrak, probation, dokumen, HR checklist, onboarding/offboarding |
| Absensi Pintar | GPS, foto, barcode, Dynamic QR, Face ID, offline submission, risk scoring, koreksi presensi |
| Approval Harian | cuti, izin, lembur, WFH, reimbursement, kasbon, tukar shift, koreksi absensi, task HR |
| Payroll & Finance | periode payroll bulanan/mingguan/harian, komponen gaji, BPJS/PPh21/Coretax metadata, THR/prorata foundation, payment instruction, slip gaji PDF |
| Reimburse & Kasbon | pengajuan, approval, lampiran privat, status pembayaran, aging, dan laporan |
| Operasional | client, project, task, checklist, bukti kunjungan, foto lokasi, asset, QR asset, product, stock |
| Commercial & Accounting | quotation, invoice, vendor bill, journal, ledger, cashflow, AR/AP aging, closing period foundation |
| Laporan | dashboard admin, export Excel/PDF, import/export background, KPI, operational health |
| Mobile | PWA, Android APK, dan project iOS untuk build/smoke lanjutan |

Detail lengkap ada di [guides/features.md](./guides/features.md).

## Kenapa Dipakai

- **Satu sistem untuk kerja harian**: absensi, pengajuan, approval, persiapan payroll, dokumen, dan laporan berada di tempat yang sama.
- **Cocok untuk perusahaan Indonesia**: alur cuti, lembur, kasbon, reimbursement, slip gaji, BPJS/PPh21/Coretax metadata, dan kebutuhan multi-cabang disiapkan dari awal.
- **Siap untuk tim lapangan**: mendukung bukti lokasi, foto, QR, Face ID, dan penilaian risiko lokasi mencurigakan.
- **Data lebih aman**: attachment privat, RBAC, audit trail, policy/gate, dan isolasi multi-company membantu mencegah akses silang.
- **Bisa dikontrol sendiri**: cocok untuk VPS perusahaan, vendor implementasi, konsultan HR, atau tim IT internal yang ingin self-hosted.
- **Tetap ramah komunitas**: fitur open-source bisa install dan boot tanpa enterprise key.

## Cocok Untuk

- UMKM yang butuh aplikasi absensi karyawan dan pengajuan harian.
- Perusahaan multi-cabang yang perlu kontrol HR, approval, dan laporan.
- Tim lapangan yang butuh absensi GPS, foto, QR, dan bukti kunjungan.
- Finance yang ingin menyiapkan payroll, slip gaji PDF, reimbursement, kasbon, dan laporan dengan lebih rapi.
- Vendor atau konsultan yang ingin baseline HRIS Laravel yang bisa dikembangkan lebih jauh.

## Deployment

`main-vps` adalah jalur utama dan default untuk fitur lengkap. Target terbaiknya adalah VPS dengan database production, queue worker, scheduler, private storage, backup, dan optional realtime.

| Target | Rekomendasi |
| --- | --- |
| VPS | Jalur utama untuk production penuh |
| Shared hosting | Legacy/best-effort untuk instalasi ringan |
| Serverless/Vercel | Demo atau staging ringan, bukan production penuh |

Panduan lengkap ada di [guides/deployment.md](./guides/deployment.md) dan [guides/operations.md](./guides/operations.md).

## Mulai Cepat

```bash
git clone https://github.com/RiprLutuk/PasPapan.git
cd PasPapan

composer install
bun install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan paspapan:seed-real
php artisan storage:link
```

Jalankan lokal:

```bash
php artisan serve
bun run dev
```

Untuk data demo/staging:

```bash
php artisan paspapan:seed-fake
```

Catatan: `paspapan:seed-real` untuk master data production-ready. `paspapan:seed-fake` untuk demo/QA dan tidak disarankan untuk production.

## Rilis

Rilis mayor terbaru: [`v5.0.0`](https://github.com/RiprLutuk/PasPapan/releases/tag/v5.0.0)

- APK: [`PasPapan-v5.0.0.apk`](https://github.com/RiprLutuk/PasPapan/releases/download/v5.0.0/PasPapan-v5.0.0.apk)
- Checksum: [`PasPapan-v5.0.0.apk.sha256`](https://github.com/RiprLutuk/PasPapan/releases/download/v5.0.0/PasPapan-v5.0.0.apk.sha256)
- Changelog: [CHANGELOG.md](./CHANGELOG.md)

Branch default dan fitur paling lengkap: [`main-vps`](https://github.com/RiprLutuk/PasPapan/tree/main-vps)

## Dokumentasi

Mulai dari [guides/README.md](./guides/README.md) untuk mencari dokumen yang tepat.

Dokumen penting:

- [Fitur produk](./guides/features.md)
- [Deployment](./guides/deployment.md)
- [Operasi harian](./guides/operations.md)
- [Security model](./guides/security-model.md)
- [Database portability](./guides/database-portability.md)
- [Attendance integration API](./guides/attendance-integration.md)
- [Feature maturity](./guides/feature-maturity.md)
- [Reviewer evidence](./guides/reviewer-evidence.md)

## Open Source & Enterprise

Repository open source harus bisa `composer install` dan boot tanpa `ENTERPRISE_OBFUSCATOR_KEY`.

Fitur enterprise tertentu dapat dikunci dengan license dan artifact obfuscated salted. `ENTERPRISE_LICENSE_KEY` dipakai untuk validasi fitur, sedangkan `ENTERPRISE_OBFUSCATOR_KEY` hanya untuk runtime artifact enterprise yang memang dibuild dengan proteksi tersebut.

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
