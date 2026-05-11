# Fitur PasPapan

Dokumen ini merangkum cakupan produk dan catatan teknis fitur. README utama sengaja dibuat ringkas; detail produk ditempatkan di sini.

## Operasi Admin

Area admin mencakup:

- dashboard dan notifikasi
- direktori karyawan
- atasan langsung karyawan untuk routing approval, notifikasi, dan reporting tim
- data absensi dan reporting
- pusat laporan HR operasional di `System > Reports`
- approval cuti
- master jenis cuti di `Master Data > Leave Types`
- approval koreksi absensi
- approval tukar/perubahan shift di `Attendance > Shift Swap Approvals`
- manajemen lembur
- kalender libur
- shift dan jadwal kerja
- barcode dan Dynamic QR
- reimbursement
- payroll dan payroll settings
- kasbon
- dokumen karyawan
- checklist onboarding/offboarding di `Master Data > HR Checklists`
- lifecycle akun karyawan
- role-based access control untuk menu dan aksi admin
- import/export data user, absensi, dan activity log
- export report absensi PDF/XLSX via background job
- template import user dan absensi sesuai schema terbaru
- KPI settings
- analytics dashboard
- activity log
- pengumuman
- system maintenance, cache operations, backup center, restore center, dan cleanup tools

## Self-Service Karyawan

Sisi karyawan mencakup:

- status absensi di beranda
- scan check-in dan check-out
- riwayat absensi
- koreksi absensi dengan review supervisor dan admin
- koreksi check-in dan check-out dalam satu pengajuan, termasuk shift malam atau check-out setelah tengah malam
- pengajuan cuti
- pengajuan lembur
- reimbursement
- kasbon dan pantauan kasbon tim sesuai izin
- jadwal shift
- HR tasks untuk checklist onboarding/offboarding pribadi atau bawahan langsung
- pengajuan swap/perubahan shift
- dokumen karyawan
- approval tim dan riwayat approval
- slip gaji
- Face ID enrollment
- aset pribadi dan pengembalian aset dengan OTP
- permintaan penghapusan akun
- penilaian performa
- notifikasi

## Absensi dan Lokasi

Workflow absensi mendukung:

- static barcode untuk deployment konvensional
- Dynamic QR dengan signed rotating token
- validasi latest-token dan konsumsi token sekali pakai
- scanner browser via camera APIs
- native Android scanner bridge saat berjalan di shell Capacitor
- GPS browser dan Capacitor Geolocation
- cached-location recovery
- preview peta dan handoff Google Maps
- bukti foto absensi
- Face ID verification jika diaktifkan
- anti-mock-location jika runtime Android menyediakan status tersebut
- secure attachment route untuk file sensitif

## Face ID

Face ID berjalan di browser saat enrollment dan capture absensi:

- memakai `face-api.js`
- TinyFaceDetector dan 68-point landmarks
- movement-based liveness check
- descriptor numerik, bukan raw selfie image
- descriptor 129-value baru dan legacy 128-value tetap diterima untuk kompatibilitas

Setting utama di UI adalah `attendance.require_face_verification`. Key lama `attendance.require_face_enrollment` masih didukung secara internal untuk backward compatibility.

## Dynamic QR

Dynamic QR dirancang untuk menghindari reuse QR statis:

- token memiliki payload bertanda tangan
- token punya issue time, expiry, dan nonce
- hanya token terbaru untuk barcode tersebut yang diterima
- token expired ditolak tanpa grace window
- token dikonsumsi setelah scan dynamic sukses
- cache store wajib berfungsi karena latest-token validation memakai cache

## Cuti dan Approval

`admin/leaves` menampilkan pengajuan cuti dari semua approval status secara default, dengan filter approval status dan request type. Pengajuan yang ditolak tetap mempertahankan tipe request asli di `status`, sedangkan keputusan review disimpan di `approval_status`.

Jenis cuti dikelola dari `Master Data > Leave Types`. Setiap jenis cuti dapat diberi kategori tahunan, sakit, atau khusus; dapat diwajibkan lampiran; dapat diaktifkan/nonaktifkan; dan hanya kategori yang ditandai memakai kuota yang mengurangi kuota cuti tahunan.

## Koreksi Absensi dan Jadwal

Koreksi absensi dipakai saat user perlu memperbaiki jam masuk, jam keluar, atau shift pada tanggal tertentu.

- jika user memiliki supervisor, request masuk ke review supervisor lebih dulu
- jika tidak ada supervisor, request langsung menunggu review admin
- form menerima nilai tanggal dan jam penuh
- satu pengajuan dapat memperbaiki check-in dan check-out sekaligus
- shift malam, shift yang berakhir tepat tengah malam, dan check-out tanggal berikutnya didukung
- snapshot jam aktual ditampilkan sebagai pembanding saat tersedia

Pengajuan tukar/perubahan shift bisa diajukan untuk tanggal yang sudah punya jadwal maupun tanggal kosong. Untuk tanggal kosong, jadwal baru dibuat atau diperbarui saat request disetujui.

## Struktur Atasan

Data karyawan mendukung `Direct Manager` eksplisit dari form create/edit employee. Field ini menjadi sumber utama untuk supervisor, bawahan, notifikasi approval, halaman approval, dan Team Kasbon.

Untuk kompatibilitas data lama, sistem masih memakai fallback divisi dan job level saat `Direct Manager` belum diisi. Form employee menolak assignment ke diri sendiri atau rantai atasan yang melingkar.

## HR Checklist Onboarding dan Offboarding

`Master Data > HR Checklists` menyediakan workflow ringan untuk HR UMKM:

- HR/admin membuat case onboarding atau offboarding untuk satu karyawan dengan tanggal efektif.
- Template default onboarding dan offboarding dibuat otomatis saat modul pertama kali dibuka.
- Template item dapat memakai default assignee HR, karyawan, atau direct manager.
- Task menyimpan due date, assignee, status, catatan, penyelesai, dan waktu selesai.
- Case menampilkan progress dari total task dan otomatis menjadi `completed` saat semua task ditutup sebagai `done` atau `skipped`.
- Case dapat dibatalkan oleh role yang memiliki permission manage.

Halaman karyawan `HR Tasks` menampilkan task yang ditugaskan ke user login. User dapat menandai task miliknya sebagai pending, done, skipped, atau blocked. Admin/HR tetap dapat mengubah task dari halaman admin sesuai policy.

Authorization:

- route admin `admin.hr-checklists` memakai `HrChecklistCasePolicy@viewAny`
- action admin memakai gate `viewHrChecklists` dan `manageHrChecklists`
- route user `hr-tasks` memakai `HrChecklistTaskPolicy@viewAny`
- task hanya dapat diubah oleh admin/HR dengan manage permission atau user yang menjadi assignee task tersebut

Modul ini shared-hosting friendly karena tidak membutuhkan Redis, Horizon, Reverb, queue worker long-running, atau WebSocket sebagai baseline.

## Import, Export, dan Report Background

Import/export admin memakai model run yang bisa dipantau dari UI:

- export user
- export absensi
- export activity log
- import user
- import absensi
- export report absensi PDF/XLSX

Run list menampilkan status `queued`, `running`, `completed`, atau `failed`, progress row, file hasil, tombol download, dan ringkasan error.

Import user:

- bisa membuat user baru
- bisa memperbarui user berdasarkan `id`, email, atau NIP
- manager bisa dicocokkan lewat NIP atau email
- konflik NIP/email/phone dicatat sebagai error row
- row valid lain tetap bisa diproses

Template user mencakup field schema terbaru seperti status karyawan, bahasa, manager, kode wilayah, email verified, dan created timestamp. Template absensi memakai contoh datetime lengkap untuk `time_in` dan `time_out`, termasuk shift malam lintas tanggal.

Run terminal yang lebih lama dari 24 jam disembunyikan dari daftar terbaru dan dipangkas otomatis oleh scheduler.

## Modul Enterprise

Repository ini memuat modul dan penguncian enterprise untuk:

- payroll management lanjutan
- analytics dan reporting lanjutan
- appraisal berbasis KPI
- lifecycle aset perusahaan
- import/export
- RBAC menu dan izin aksi admin
- dokumen karyawan
- checklist onboarding/offboarding
- kasbon dan approval finance
- backup automation
- validasi lisensi enterprise dan hardware fingerprint

License enterprise berjalan offline dengan payload bertanda tangan. Lisensi bisa memberi semua fitur (`*`) atau daftar fitur spesifik. Gate aplikasi mengecek fitur per modul sehingga lisensi valid yang hanya berisi `payroll` tidak otomatis membuka `audit`, `analytics`, `system_backup`, atau modul enterprise lain.

Runtime license memakai cache validasi dan cache feature map. Hasil validasi dipakai ulang dalam satu request dan disimpan pendek di cache aplikasi supaya menu, policy, gate, dan service binding tidak memverifikasi signature berulang. Proteksi enterprise offline tetap aktif, tetapi runtime sudah dioptimalkan agar halaman admin tidak perlu melakukan validasi berat berulang. Komponen internal penerbitan lisensi tidak menjadi bagian deployment klien.

Generate fingerprint hardware server:

```bash
php artisan enterprise:hwid
```

## Roadmap Fase dan Task

Roadmap ini disusun mobile-first, shared-hosting first, dan tetap mengikuti RBAC/policy yang sudah ada.

Status implementasi fondasi:

- Fase 1: audit trail detail dan risk scoring absensi sudah memiliki model, service, migration, dan feature test.
- Fase 2: approval matrix reimbursement/kasbon sudah memiliki rule engine, status bertahap, dan feature test.
- Fase 3: offline attendance sync terbatas sudah memiliki API endpoint, persistence queue, idempotency, dan risk flag.
- Fase 4: kalkulator payroll Indonesia dan payment instruction sudah tersedia sebagai service teruji.
- Fase 5: lifecycle, shift planning, notification preference, webhook/API integration, dan KPI service sudah tersedia sebagai foundation backend.
- Fase 6: multi-company ringan, marketplace template HR, dan issue labels komunitas sudah tersedia sebagai foundation produk.

### Fase 1 - Trust, Audit, dan Risiko Absensi

- Audit trail detail per entity: rekam perubahan field-level untuk salary, payroll, role/permission, koreksi absensi, dan approval cuti.
- Risk scoring absensi: skor risiko untuk mock location, jarak mendekati radius, device berubah, face confidence rendah, waktu check-in tidak wajar, offline submission, dan retry QR token.
- Tampilan audit/risk di admin: kartu ringkas mobile-first, filter entity, aktor, tanggal, dan tingkat risiko.
- Export audit/risk: tetap memakai background job database queue supaya aman untuk data besar.

### Fase 2 - Approval Matrix Configurable

- Rule approval bertingkat berdasarkan divisi, role, nominal, lokasi, dan policy.
- Approval chain untuk reimbursement, kasbon, koreksi absensi, cuti, lembur, dan payroll-sensitive actions.
- Fallback approver, delegation, SLA reminder, dan audit keputusan approval.
- RBAC baru harus diselaraskan dengan `config/rbac.php`, gate/policy, menu visibility, dan test authorization.

### Fase 3 - Field Operations Offline

- Offline queue terbatas untuk absensi lapangan: GPS, foto, timestamp lokal, barcode/QR payload, dan device context.
- Sync saat online dengan flag `offline submitted`, waktu submit asli, waktu sync, dan risk score tambahan.
- Conflict handling untuk token QR expired, lokasi berubah jauh, atau foto/GPS tidak lengkap.
- Validasi APK/mobile browser wajib sebelum rilis.

### Fase 4 - Payroll Indonesia Lanjutan

- PPh 21 TER, BPJS TK/Kesehatan, THR, prorata, potongan, tunjangan tetap/tidak tetap.
- Payroll component yang bisa dipetakan ke taxable/non-taxable dan recurring/one-time.
- Export bank/payment instruction untuk payroll disbursement.
- Audit field-level untuk setiap perubahan nominal payroll dan komponen payroll.

### Fase 5 - Employee Lifecycle dan Operasional

- Probation, contract end reminder, renewal workflow, resignation, exit interview, asset return checklist, dan auto-disable account.
- Shift planning visual calendar dengan drag-and-drop roster, conflict detection, kapasitas lokasi/divisi, dan bulk assign.
- Notification preference per user/admin untuk in-app, email, WhatsApp/webhook, Telegram, dan digest.
- Webhook/API integration untuk accounting/payroll eksternal, Slack/Telegram/WhatsApp gateway, Google Calendar, dan SSO.
- Dashboard KPI operasional: late rate, absence rate, overtime cost, leave liability, reimbursement aging, payroll variance, dan asset overdue.

### Fase 6 - Produk dan Komunitas

- Multi-company atau multi-tenant ringan untuk konsultan/vendor yang mengelola banyak klien.
- Marketplace template dokumen HR: kontrak, surat tugas, surat cuti, paklaring, warning letter, dan onboarding checklist.
- Public roadmap dan issue labels untuk kontribusi komunitas repository public.

## Performa Admin

Beberapa halaman yang rawan lambat pada data besar memakai query ter-paginate dan eager loading terarah:

- data absensi admin hanya memuat attendance untuk user yang tampil pada halaman aktif
- report absensi membatch record attendance sesuai user dan rentang tanggal
- ringkasan employee status memakai aggregate query
- activity log memakai range waktu agar indeks `created_at` bisa dipakai
- dashboard admin menghindari `whereIn` besar untuk scope global
- approval cuti dan shift swap memakai pagination query dan indeks tambahan

## Tech Scan

Backend:

- Laravel `11`
- PHP `8.2+`
- Livewire `3`
- Jetstream, Fortify, dan Sanctum
- MySQL atau MariaDB
- queue, cache, notification, dan session berbasis database sebagai default

Frontend:

- Tailwind CSS `3.4`
- Vite `7`
- Alpine via Blade dan Livewire screens
- Tom Select
- Chart.js
- Leaflet dan marker clustering
- SweetAlert2
- Heroicons via Blade UI Kit

Tool dokumen dan data:

- `maatwebsite/excel`
- `barryvdh/laravel-dompdf`
- `endroid/qr-code`
- `intervention/image`
- `ballen/distical`

Testing dan tooling:

- Pest `4`
- Laravel Pint
- Bun
- feature tests untuk attendance enforcement, Dynamic QR, backup jobs, maintenance security, leave approval, media access, queued report export, import/export retention, template import, overtime manager, HR checklist, dan koreksi absensi lintas hari
