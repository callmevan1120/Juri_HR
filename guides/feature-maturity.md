# Feature Maturity Focus

PasPapan sudah memasuki fase pematangan produk. Prioritas kerja berikutnya bukan menambah modul baru, melainkan memperkuat logic, keamanan, data integrity, UX, testing, dan operasional dari fitur yang sudah ada.

## Skor Jujur Saat Ini

Baseline setelah hardening database, dashboard, mobile, dan dokumentasi terbaru: **sekitar 85/100 untuk keseluruhan visi 1 platform**.

Artinya:

- core HR, attendance, approval, RBAC/security, dan payroll dasar sudah layak dipakai dengan guard produksi yang cukup kuat;
- operasi, commercial/CRM, accounting, collaboration, dan APK sudah punya fondasi yang bisa diuji, tetapi sebagian masih berada di level release candidate/foundation;
- iOS delivery belum full release-ready;
- target “10/10” internal PasPapan dipatok sebagai skor audit minimal `95/100` plus tidak ada evidence yang hilang.

Jalankan audit lokal:

```bash
php artisan feature:maturity
php artisan feature:maturity --json
```

Definisi dan bobotnya ada di `config/feature_maturity.php`. Angka ini sengaja konservatif supaya dokumentasi tidak mengklaim sesuatu yang belum dibuktikan di test, staging, atau device. Follow-up terbaru menambahkan workflow tax filing draft/filed/paid untuk accounting, scoped message search untuk collaboration, AR collection summary untuk commercial, dan iOS preflight gate. Gap berkurang tanpa menutupi bahwa iOS delivery dan full finance sign-off masih perlu evidence nyata.

## Prinsip Kerja

- Jangan menambah menu/modul baru kecuali langsung menutup gap pada fitur yang sudah ada.
- Perubahan fitur harus backward-compatible dan tidak mengubah URL/route name tanpa alasan kuat.
- Logic bisnis berat tetap berada di `app/Actions`, `app/Services`, `app/Queries`, atau `app/Support`; Livewire fokus pada UI state, validasi, authorization, dan dispatch action.
- Setiap bug fix atau hardening penting idealnya punya test regression.
- Dokumentasi harus mengikuti behavior aktual, bukan aspirasi.

## Fokus Pematangan

### Attendance

- Pastikan check-in/check-out, Dynamic QR, GPS, foto, Face ID, mock-location signal, offline/cached location, dan risk scoring konsisten.
- Perkuat edge case shift malam, tidak ada shift, toleransi radius, QR expired/retry, dan device berubah.
- Bukti foto dan attachment attendance wajib lewat private storage dan policy.

### HR dan Employee Lifecycle

- Matangkan onboarding/offboarding checklist, task dependency, overdue reminder, attachment task, clearance summary, dan auto-disable account.
- Pastikan perubahan status karyawan tidak membuat akun, role, payroll, atau approval flow bocor lintas company.

### Approval dan Finance

- Approval matrix harus konsisten untuk cuti, lembur, reimbursement, kasbon, koreksi absensi, dan action sensitif payroll.
- Audit field-level harus mudah dibaca untuk perubahan status, nominal, bank, payroll, role, dan approval.
- Reimbursement/kasbon/payroll harus aman dari double approval, stale approval, dan akses lintas user/company.

### Payroll dan Accounting

- Payroll Indonesia yang sudah ada harus diperdalam melalui validasi komponen, prorata, THR, BPJS, PPh21/Coretax metadata, payment instruction, variance, dan payslip access control.
- Accounting foundation difokuskan pada posting yang benar, AR/AP aging, ledger export, closing period, tax filing draft/filed/paid, dan report yang bisa direkonsiliasi.

### Operations, Commercial, CRM, dan Collaboration

- Client, project, task, checklist, quotation, invoice, vendor bill, stock movement, sales opportunity, chat, cloud file, dan meeting link difokuskan pada flow input yang jelas, company scope, secure file delivery, scoped search, realtime hook, dan PDF/export yang konsisten.
- Hindari menambah submodul baru sebelum create/edit/detail/export/download pada modul existing stabil.

### UX dan Mobile

- User pages harus terasa native-app: sederhana, clean, tidak terlalu panjang, tidak ada elemen nempel, modal bisa diklik, tab tidak reset setelah reload, dan empty/error/loading state jelas.
- Admin pages harus konsisten font, spacing, input, TomSelect/Flatpickr styling, responsive table/card, dan SweetAlert structured feedback.

### Operations dan Release

- PostgreSQL menjadi baseline local/CI/VPS; MySQL/MariaDB hanya compatibility path.
- Queue, scheduler, Reverb, backup, import/export, health dashboard, dan evidence bundle harus bisa direproduksi di VPS.
- Seeder harus idempotent dan production-safe; jangan memakai refresh/drop kecuali operator sengaja menjalankan command destructive.

## Definition of Done

Sebuah pematangan fitur dianggap selesai jika:

- happy path dan edge case utama sudah dites;
- policy/RBAC dan company scope sudah jelas;
- upload/download memakai private storage atau route terproteksi;
- state reload, pagination, tab, filter, dan modal tidak merusak flow;
- PDF/export/import punya format stabil;
- docs dan release checklist sesuai behavior aktual;
- command validasi relevan sudah dijalankan dan hasilnya dicatat.
