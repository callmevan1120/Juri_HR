# JURI HR — Rewrite Matrix (Frontend Vue 3 → Frappe HR v16)

> Kontrak mapping antara halaman PasPapan (sumber: `app/Livewire`, `resources/views`, `routes/`) dan target backend Frappe HR v16. Dokumen ini dipakai sebagai acuan porting per modul: **port UI → panggil endpoint → buat DocType/method bila belum ada**.
>
> Keterangan target: **[core]** = doctype frappe, **[hr]** = doctype Frappe HR, **[custom]** = DocType di app `juri_hr`, **[method]** = whitelisted API method di `juri_hr`, **[erp/custom]** = ditangani custom (tidak memakai ERPNext).

## Stack & Aturan

- Frontend: Vue 3 + Vite + TS + Tailwind 4 + Pinia + Vue Router; folder `frontend/`.
- Semua data lewat `src/api/` client (REST Frappe). Tanpa Laravel, tanpa DB lokal.
- Auth: `POST /api/method/login` → `generate_keys` → `Authorization: token key:secret` di localStorage.
- RBAC: role Frappe (Role Profile meniru `config/rbac.php`).
- Base URL Frappe via `VITE_FRAPPE_BASE_URL`; mode dev bisa pakai fixture JSON bila server belum ada.

## Dihapus dari scope

Toko/POS, Komersial/Invoice (quotation, invoice, vendor bill, stock movement), Akuntansi (journal/ledger, COA, cashflow, AR/AP), Aset + QR tagging aset. → ERPNext **tidak dipasang**.

## Mapping Modul

### A. Auth & Profile
| Halaman PasPapan | Sumber | Target Frappe |
|---|---|---|
| Login/logout | Jetstream + `app/Actions/Fortify` | [method] `/api/method/login`, `frappe.auth.get_logged_user`, `generate_keys` |
| Profile user/admin | `profile/show.blade.php` | [hr] `Employee` + [core] `User` |
| Notifikasi prefs | `NotificationPreferencesForm` | [core] `Notification Settings` |
| Aktivitas | `ActivityLogViewer` | [core] `Activity Log` |
| Delete account | `RequestAccountDeletionForm` | [custom] method `juri_hr.account.disable` |

### B. Dashboard & Overview
| Halaman | Target Frappe |
|---|---|
| Admin dashboard (statistik real-time) | [method] `juri_hr.dashboard.admin_summary` (aggregate) + realtime |
| Command Center | [method] `juri_hr.dashboard.command_center` |
| Manager Inbox (pending approval per modul) | [method] `juri_hr.approvals.pending_count` |
| Home/status absen user | Employee Checkin terakhir + Shift Assignment hari ini |

### C. Absensi
| Halaman | Target Frappe |
|---|---|
| Scan (GPS + selfie face + QR + offline) | [method] `juri_hr.scan.checkin` / `juri_hr.scan.checkout` (validasi radius, QR token, face, anti-mock) |
| Dynamic QR / Barcode Locations | [custom] `Dynamic QR Token` + `Barcode Location` |
| Face enrollment | [custom] `Employee Face Descriptor` (face-api.js client-side; model port dari `public/models/`) |
| Anti-mock location | plugin Capacitor (client) + skor risiko di `juri_hr.scan.*` → [custom] `Attendance Scan Log` |
| Riwayat absensi user | [hr] `Attendance` + `Employee Checkin` (get_list) |
| Offline submission & sync | [custom] `Offline Checkin Queue` + [method] `juri_hr.scan.offline_sync` |
| Daily attendance admin | [hr] `Attendance` + [hr] `Employee Checkin` (list/filter/export) |
| Koreksi absensi | [custom] `Attendance Correction` (workflow approval) |
| Shift & Roster (kalender drag-drop) | [hr] `Shift Type`, `Shift Assignment` |
| Libur nasional | [core] `Holiday List` + fetch nasional via method |
| Pengumuman | [hr] `Announcement` + [custom] `Announcement View` (tracking dibaca) |

### D. Cuti (modul terpisah dari Izin)
| Halaman | Target Frappe |
|---|---|
| Jenis cuti | [hr] `Leave Type` |
| Jatah cuti | [hr] `Leave Allocation` + `Leave Ledger Entry` |
| Pengajuan/setujui cuti | [hr] `Leave Application` + Workflow (alur multi-level via `Approval Matrix` custom) |
| Kalkulasi sisa cuti | [hr] `Leave Ledger Entry` / `get_leave_balance` |

### E. Izin (modul terpisah dari Cuti — DocType custom)
| Halaman | Target Frappe |
|---|---|
| Jenis izin (sakit/pribadi/lainnya) | [custom] `Izin Type` (opsi; potong payroll: ya/tidak) |
| Pengajuan izin | [custom] `Izin Request` (tanpa jatah, workflow sendiri, approval matrix) |
| Persetujuan izin (admin/user manager) | [custom] `Izin Request` docstatus + Workflow |
| Laporan izin | [method] `juri_hr.izin.report` + export |

### F. Approval Harian Lainnya
| Halaman | Target Frappe |
|---|---|
| Lembur | [custom] `Overtime Request` (approval, kalkulasi jam) |
| WFH | [custom] `WFH Request` → flag `work_from_home` di [hr] `Attendance` |
| Shift swap | [custom] `Shift Swap Request` (pasangan, approve, swap Shift Assignment) |

### G. Payroll & Slip Gaji
| Halaman | Target Frappe |
|---|---|
| Komponen gaji | [hr] `Salary Component` (formula; BPJS, PPh21, THR prorata) |
| Struktur gaji | [hr] `Salary Structure` + `Salary Structure Assignment` |
| Proses payroll (one-click) | [hr] `Payroll Entry` (monthly/daily) + [method] `juri_hr.payroll.process` (THR prorata, potongan izin unpaid) |
| Slip gaji PDF | [hr] `Salary Slip` + **Print Format Jinja** `Juri Hr Payslip` (tiru `pdf/payslip.blade.php`) |
| Payment instruction | [method] `juri_hr.payroll.payment_instruction` (tanpa ERPNext) |
| Pengaturan payroll | [custom] `Payroll Settings` (namespace juri_hr) |

### H. Kasbon & Reimbursement
| Halaman | Target Frappe |
|---|---|
| Kasbon (ajukan, setujui, lunasi) | [hr] `Employee Advance` + [method] `juri_hr.advance.settle` (tanpa Payment Entry ERPNext) |
| Reimbursement + lampiran kuitansi | [hr] `Expense Claim` + `Expense Claim Type` (file via `/api/method/upload_file`) |
| Aging & rekap per divisi | [method] `juri_hr.claims.summary` |

### I. Appraisal & KPI
| Halaman | Target Frappe |
|---|---|
| Template appraisal/KPI | [hr] `Appraisal Template`, [custom] `KPI Template`/`KPI Group` |
| Penilaian & evaluasi | [hr] `Appraisal` + `Appraisal Evaluation` |

### J. Operasional Lapangan (dipertahankan)
| Halaman | Target Frappe |
|---|---|
| Client | [custom] `Field Client` (tanpa ERPNext Customer) |
| Project | [core] `Project` |
| Task / penugasan | [core] `Task` |
| Bukti kunjungan (GPS + foto) | [custom] `Visit Evidence` (file via `/api/method/upload_file`) |

### K. Kolaborasi
| Halaman | Target Frappe |
|---|---|
| Chat personal/grup/proyek | [custom] `Chat Thread` + `Chat Message` + realtime socket.io (E2EE opsional tahap akhir) |
| File sharing privat | [core] `File` (private folder) via `/api/method/upload_file` |
| Meeting links | [custom] `Meeting Link` (embedded pada thread) |

### L. System
| Halaman | Target Frappe |
|---|---|
| App settings | [custom] `Juri Hr Settings` (Single) — menggantikan tabel `settings` |
| Perusahaan/multi-cabang | [core] `Company` + permission rules (isolasi `company` per user) |
| RBAC | [core] `Role`, `Role Profile`, `Role Permission Manager` (port dari `config/rbac.php`) |
| Activity log | [core] `Activity Log` |
| Backup/restore | `bench backup` + [custom] UI `Backup Run` |
| Import/export | [core] `Data Import`, Report export |
| Notifikasi & announcement | [core] `Notification Log` + realtime |

## Pola Endpoint yang Dipakai

| Kebutuhan | Endpoint |
|---|---|
| List | `GET /api/resource/:doctype?fields=[...]&filters=[...]&limit_start=&limit_page_length=&order_by=` |
| Detail | `GET /api/resource/:doctype/:name` |
| Create/Update/Delete | `POST/PUT/DELETE /api/resource/:doctype[/:name]` |
| Action submit/cancel | `POST /api/resource/:doctype/:name?action=submit` (dsb.) |
| Method custom | `POST /api/method/juri_hr.xxx.yyy` |
| Upload file | `POST /api/method/upload_file` |
| Realtime | `socket.io` `/socket.io/` room per user + `frappe.publish_realtime` |
| Print format | `GET /api/method/frappe.utils.print_format.download_pdf?doctype=...&name=...&format=Juri Hr Payslip` |

## Status Tracking Modul

| Modul | Status | Catatan |
|---|---|---|
| A. Auth & Profile | Belum | Fase 2c (fondasi) |
| B. Dashboard | Belum | setelah fondasi |
| C. Absensi | Belum | prioritas modul #1 setelah fondasi |
| D. Cuti | Belum | |
| E. Izin | Belum | custom app |
| F. Approval harian | Belum | |
| G. Payroll | Belum | |
| H. Kasbon/Reimburse | Belum | |
| I. Appraisal | Belum | |
| J. Operasional | Belum | |
| K. Kolaborasi | Belum | |
| L. System | Belum | |
