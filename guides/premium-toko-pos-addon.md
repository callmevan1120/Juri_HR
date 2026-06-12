# Premium Toko POS Add-on

Last updated: 2026-06-09

## Final Readiness Statement

Premium Toko POS adalah add-on enterprise berbayar di dalam PasPapan, bukan aplikasi terpisah dan bukan fitur open-source. Modul ini menggantikan toko-pandan sebagai workflow retail harian, sementara PasPapan tetap menjadi core untuk login, RBAC, HRIS, database terpusat, dan finance/accounting.

Status saat ini: code baseline sudah siap untuk production UAT dan cutover sign-off. Yang belum boleh dianggap selesai adalah keputusan operasional produksi: training operator, owner/admin sign-off, role/menu final, dan kebijakan archive/read-only/redirect untuk toko-pandan.

## Product Direction

- Core system tetap PasPapan/absensi-gps-barcode.
- Toko/POS menjadi premium add-on enterprise dengan license boundary.
- HRIS tetap sumber data karyawan/payroll.
- Toko finance masuk ke PasPapan Accounting Workspace sebagai kontribusi sales, AR, purchase, AP, expense, dan jurnal Toko.
- UI harus clean, modern, ringan, dan lebih mudah dipakai dari toko-pandan.
- Migration page hanya alat transisi; route ini head/import-gated dan bisa dimatikan setelah cutover dengan `toko_pos.migration_enabled=false`.

## Premium Boundary

Source dan build enterprise:

- Source utama: `app/Livewire/Admin/TokoPosAddon.Source.php`
- Generated enterprise file: `app/Livewire/Admin/TokoPosAddon.php`
- Build command: `php secure_tools/build_enterprise.php`
- Boundary check: `composer check:enterprise-boundary`

Add-on wajib tetap melewati obfuscator/build enterprise dan license entitlement sebelum rilis.

## Core Finance And HRIS Integration

Toko/POS tidak berdiri sendiri.

- Karyawan di dashboard Toko adalah `Karyawan HRIS`, bukan master karyawan baru milik Toko.
- Gaji dan biaya operasional yang relevan dengan toko masuk sebagai operational expense/journal Toko.
- Sales, AR, purchases, AP, operational expenses, dan posted Toko journals ditampilkan di PasPapan Accounting Workspace sebagai `Toko Finance Contribution`.
- Laporan Toko tetap detail untuk operasional retail, sedangkan Accounting Workspace menjadi ringkasan lintas modul.

Integration contract:

- Login, session, role, permission, menu visibility, and company scope memakai core PasPapan.
- Toko tidak membuat auth, company, employee, payroll, atau finance ledger sendiri.
- Toko boleh punya workflow retail khusus seperti POS, barcode, stock movement, delivery letter, dan retail report.
- Setiap transaksi Toko yang berdampak uang harus masuk core finance/accounting sebagai invoice, vendor bill, journal, AR, AP, payment, refund, atau expense yang bisa dibaca Accounting Workspace.
- Cutover/import hanya alat transisi untuk mengisi data core PasPapan dari dump toko-pandan; setelah cutover, operasional harian berjalan dari data core yang sama.

## Company Scope And Admin Ownership

Data Toko/POS dipisah memakai `company_id`.

- Super Admin adalah head/global operator. Akun ini bisa melihat semua company dan memilih company aktif di Toko/POS.
- Admin company adalah penanggung jawab operasional company masing-masing. Akun ini hanya melihat dan mengelola data company yang terpasang di `users.company_id`.
- User/karyawan tetap berada di HRIS company masing-masing dan tidak otomatis mendapat akses admin Toko.
- Import/cutover/migration tidak diberikan ke admin company biasa; flow ini tetap untuk Super Admin/head-level karena bisa mengubah data historis besar.
- Accounting Workspace membaca Toko contribution sesuai scope company user: Super Admin bisa lintas company, admin company hanya company sendiri.
- Service mutation Toko wajib menolak create/payment/cancel lintas company walaupun request dikirim langsung tanpa UI.

Branch/store policy:

- `company_branches` tetap milik core Operations PasPapan.
- Branch yang hanya butuh HRIS/attendance/operations tidak perlu addon Toko dan tidak perlu permission `admin.toko_pos.*`.
- Baseline Toko sekarang company-scoped dan branch-aware. Transaksi utama Toko menyimpan `branch_id` opsional untuk invoice POS, purchase bill, stock movement, quotation, dan delivery letter.
- Head/Super Admin bisa melihat consolidated finance di Accounting Workspace, memilih company aktif, lalu memilih semua branch/store atau satu branch/store di Toko.
- Admin company hanya melihat report company sendiri. Jika admin hanya diberi tanggung jawab satu toko fisik, operasional Toko harus dijalankan dengan branch/store scope yang sesuai.
- Branch/store tanpa retail workflow tetap tidak perlu addon Toko; role dan permission Toko hanya diberikan ke user yang mengelola retail/POS.

## Current Routes

| Route | Role | Final Status |
| --- | --- | --- |
| `/admin/toko` | Dashboard toko | Cutover-ready baseline, owner report sign-off required |
| `/admin/toko/pos` | POS counter sale | Cutover-ready baseline, cashier UAT required |
| `/admin/toko/products` | Data barang, category, brand, barcode | Cutover-ready baseline |
| `/admin/toko/purchases` | Pembelian, hutang/AP, purchase list | Cutover-ready baseline, purchasing UAT required |
| `/admin/toko/customers` | Pelanggan, penawaran, pendapatan customer | Cutover-ready baseline |
| `/admin/toko/cash` | Operational expense and cash insight | Cutover-ready baseline |
| `/admin/toko/inventory` | Stock, stock movement, delivery letter | Cutover-ready baseline, warehouse UAT required |
| `/admin/toko/reports` | Reports and exports | Cutover-ready baseline, owner format sign-off required |
| `/admin/toko/migration` | Transitional import tooling | Head/import-gated, transition-only, disable after cutover |

## Legacy Menu Mapping

| toko-pandan menu | PasPapan target | Final Status |
| --- | --- | --- |
| Dashboard | `/admin/toko` | Mapped |
| Barang > Data Barang | `/admin/toko/products` | Mapped |
| Barang > Tambah Barang | `/admin/toko/products` product form | Mapped |
| Barang > Barcode | `/admin/toko/products` barcode tool | Mapped |
| Barang > Kategori | `/admin/toko/products` category settings | Mapped |
| Barang > Brand | `/admin/toko/products` brand settings | Mapped |
| Menu Pembelian > Buat Pembelian | `/admin/toko/purchases` purchase form | Mapped |
| Menu Pembelian > Data Transaksi | `/admin/toko/purchases` purchase table/detail | Mapped |
| Menu Pembelian > Hutang | `/admin/toko/purchases` AP panel | Mapped |
| Menu Pembelian > Rekap Pembelian | `/admin/toko/reports` and purchase recap | Mapped |
| Menu Penjualan > Trx Retail | `/admin/toko/pos` | Mapped |
| Menu Penjualan > Buat Invoice | `/admin/toko/pos` unpaid/invoice mode | Mapped |
| Menu Penjualan > Data Invoice | `/admin/toko/pos` sales list/detail | Mapped |
| Menu Penjualan > Daftar Trx Retail | `/admin/toko/pos` retail history | Mapped |
| Menu Penjualan > Rekening | `/admin/toko/cash` payment accounts | Mapped |
| Pelanggan > Data Pelanggan | `/admin/toko/customers` | Mapped |
| Pelanggan > Penawaran | `/admin/toko/customers` quotation panel | Mapped |
| Pelanggan > Pendapatan | `/admin/toko/customers` income report | Mapped |
| Pengeluaran Operasional > Tambah Trx | `/admin/toko/cash` expense form | Mapped |
| Pengeluaran Operasional > Data Operasional | `/admin/toko/cash` expense table | Mapped |
| Pengeluaran Operasional > Tipe Pengeluaran | `/admin/toko/cash` expense type settings | Mapped |
| Stok > Data Stok | `/admin/toko/inventory` stock dashboard/list | Mapped |
| Stok > Stok Masuk | `/admin/toko/inventory` stock-in | Mapped |
| Stok > Stok Keluar | `/admin/toko/inventory` stock-out | Mapped |
| Stok > Surat Jalan | `/admin/toko/inventory` delivery letters | Mapped |
| Stok > Mutasi Stok | `/admin/toko/inventory` movement audit | Mapped |
| Stok > Stok Barang Retur | `/admin/toko/inventory` returns | Mapped |
| Stok > Stok Menipis | `/admin/toko/products` low-stock workflow | Mapped |
| Stok > Penyesuaian Stok | `/admin/toko/inventory` stock opname | Mapped |
| Stok > Valuasi | `/admin/toko/reports` inventory valuation | Mapped |
| Laporan | `/admin/toko/reports` | Mapped |
| Supplier | `/admin/toko/purchases` vendor workspace | Mapped |
| Manajemen User | PasPapan users/RBAC | Mapped to core, production invite/sign-off required |
| Pengaturan | PasPapan settings/license/maintenance | Mapped to core, production decisions required |

## Implemented Baseline

- Centralized Toko data stored in PasPapan DB.
- Cutoff import cleans Toko/POS domain data before importing the frozen toko-pandan dump.
- Active product catalog reconciles to 690 legacy products from the accepted local dump.
- Stock totals, stock in/out, stock valuation, gross margin estimate, and sold omzet reconcile to the local frozen dump.
- Product workspace covers data barang, tambah/edit, barcode, kategori, brand, low stock, expired, stock card, import metadata, and DataTable-style search/pagination.
- POS covers barcode/search product picker, cart, nota generation, receipt preview, paid/unpaid sale, cash/transfer/QRIS/card/split tender, invoice mode, cancel/refund, stock reversal, payment metadata, retail history, invoice list/detail, and reprint.
- Purchase workspace covers Buat Pembelian, Data Transaksi, Hutang/AP, Rekap Pembelian, vendor, line items, due date, PO/faktur, extra cost, receiver audit, partial/full AP payment, cancel/reversal, and purchase history.
- Customer workspace covers customer data, member conversion, safe deactivate, quotation lifecycle, conversion to sale/invoice, AR drilldown, and customer income.
- Operational workspace covers expense entry, expense type settings, expense edit/void audit, payment accounts, cash/bank mapping, period filters, and export.
- Inventory workspace covers stock-in, stock-out, stock opname, movement audit, delivery letter, returns, low-stock/expired support, stock card, and valuation/report hooks.
- Reports cover sales, purchase, inventory valuation, operational expense, gross profit, profit/loss, AR/AP aging, product movement, customer income, supplier purchase, CSV exports, and dashboard drilldowns.
- Migration workspace stores reconciliation, monthly report reconciliation, cash/bank reconciliation, rollback targets, and cutover archive; access is head/import-gated.
- UI baseline uses compact dashboard sections, Indonesian number formatting, stable table controls, and icon actions where appropriate.

## Data Cutoff Baseline

Accepted local cutoff source:

- Dump: `/Users/lutuk/Project/learning/toko-pandan/database/toko.sql`
- Timestamp: `2026-06-09T00:50:52+07:00`
- Legacy rows: 11.990
- Mapped rows: 11.989
- Unmapped non-transactional row: `operasional_view`

Known reconciled local totals:

- Active products: 690
- Total stock: 18.693
- Stock out: 13.823
- Stock in: 17.209
- Low stock: 105
- Total estimasi modal: Rp599.092.044
- Total estimasi pemasukan: Rp764.537.500
- Total estimasi laba: Rp165.445.456
- Total omzet terjual: Rp680.723.500

If toko-pandan screenshots show different numbers, treat them as source-timestamp mismatch unless they come from the same frozen dump.

## Final Non-Code Gates

These are the only remaining blockers before legacy retirement:

1. Operator UAT for POS, purchasing, inventory, cash, customers, and reports.
2. Owner/admin sign-off on dashboard totals and report formats.
3. Production RBAC/menu decision, including confirming `/admin/toko/migration` is only visible to import-capable head users or disabled with `toko_pos.migration_enabled=false`.
4. Final archive/read-only/redirect policy for toko-pandan.
5. Final enterprise build/license check on the release artifact.

## Production UAT Pack

Use this pack to decide whether toko-pandan can be retired.

| Area | Tester | Required evidence | Pass criteria |
| --- | --- | --- | --- |
| Dashboard and reports | Owner/admin | Screenshot or exported report showing dashboard totals, sales, purchase, valuation, AR/AP, and profit/loss | Totals match the accepted frozen dump or approved reconciliation note |
| POS cashier | Cashier lead | Nota number, paid sale, unpaid invoice, split tender sale, receipt PDF, void/refund note | Cashier can finish daily sale flow without toko-pandan |
| Purchasing | Purchasing/admin gudang | Purchase bill, AP/hutang state, partial/full AP payment, cancel/reversal note | Goods receipt, supplier debt, payment, and cancellation flow are clear |
| Inventory | Warehouse/admin gudang | Stock in, stock out, stock opname, stock card, delivery letter PDF, movement audit | Every stock change is traceable and reversible according to policy |
| Customer and quotation | Sales/admin | Customer profile, member conversion, quotation, accepted/rejected status, invoice conversion | Customer lifecycle and quote-to-sale flow are complete |
| Cash and expenses | Finance/admin | Expense type, expense entry, edit/void audit, payment account, export | Expense data appears in Toko reports and Accounting Workspace |
| RBAC and branch/store | Head/admin | Login as Super Admin, company admin, and branch/store operator | Company admin cannot see other companies; normal admins cannot open migration |

UAT evidence format:

- Tester name:
- Role:
- Company:
- Branch/store:
- Date:
- Scenario:
- Evidence link/file:
- Result: Pass/Fail
- Notes:

## Sign-off Checklist

All items below need human approval before legacy retirement.

- `[ ]` Cashier lead accepts POS, receipt, split tender, and void/refund flow.
- `[ ]` Purchasing/admin gudang accepts purchase, AP, receiving, and cancellation flow.
- `[ ]` Warehouse/admin gudang accepts stock movement, stock card, return, and delivery letter flow.
- `[ ]` Finance/admin accepts cash, expense, AR/AP, and Accounting Workspace contribution.
- `[ ]` Owner/admin accepts dashboard totals, report totals, and data reconciliation.
- `[ ]` Head/admin accepts production RBAC and branch/store assignment.
- `[ ]` Owner selects toko-pandan retirement mode: archive, read-only, redirect, or temporary parallel hold.

## Operator Training Runbook

Run training by role, using production-like data after the frozen dump is accepted.

| Role | Training path | Must demonstrate |
| --- | --- | --- |
| Cashier | `/admin/toko/pos` | Search/scan product, cart, cash sale, unpaid invoice, split tender, receipt print/reprint, void/refund |
| Purchasing/admin gudang | `/admin/toko/purchases` | Create purchase, receive stock, set AP due date, pay partial/full AP, cancel purchase |
| Warehouse/admin gudang | `/admin/toko/inventory` | Stock in/out, opname, stock card, return, delivery letter print/reprint |
| Finance/admin | `/admin/toko/cash`, `/admin/toko/reports`, `/admin/accounting` | Expense type, expense entry, edit/void, AR/AP, report export, Accounting Workspace contribution |
| Owner/admin | `/admin/toko`, `/admin/toko/reports`, `/admin/accounting` | Dashboard review, drilldown reports, profit/loss, valuation, AR/AP, company/branch scope |
| Head admin | user/RBAC screens and `/admin/toko` | Role assignment, company scope, branch/store scope, migration access guard |

Training attendance format:

- Date:
- Trainer:
- Participant:
- Role:
- Company:
- Branch/store:
- Scenarios completed:
- Result: Pass/Needs follow-up
- Follow-up owner:

## Cutover Decision Log

Use this table for the final production meeting.

| Decision | Options | Selected | Owner | Evidence |
| --- | --- | --- | --- | --- |
| toko-pandan retirement mode | archive / read-only / redirect / temporary parallel hold |  | Owner |  |
| hard-delete policy | disabled safe-deactivate / enabled for super admin only |  | Owner + finance |  |
| stock adjustment approval | direct adjustment / approval required / finance review required |  | Owner + warehouse |  |
| report export scope | CSV only / CSV+PDF / CSV+PDF+XLSX |  | Owner |  |
| branch/store access | company-wide operator / branch-scoped operator |  | Head admin |  |
| migration workspace | enabled for head only / disabled after cutover |  | Head admin |  |

## Build And Verification Commands

Use after source changes:

```bash
php secure_tools/build_enterprise.php
php -l app/Livewire/Admin/TokoPosAddon.Source.php
php -l app/Livewire/Admin/TokoPosAddon.php
composer check:enterprise-boundary
php artisan test --filter=Toko
```

Use after route/menu/RBAC changes:

```bash
php artisan rbac:audit
```

## Cutover Rule

Do not retire toko-pandan until all are true:

- Final frozen dump has been imported and reconciled.
- Operators can run daily POS, purchasing, inventory, customer, supplier, cash, and report workflows without opening toko-pandan.
- Owner/admin accepts dashboard and report totals.
- Migration menu is hidden from normal operator roles and can be disabled after cutover.
- toko-pandan is archived, read-only, or redirected according to owner decision.
