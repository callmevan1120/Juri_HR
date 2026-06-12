# PRD: Premium Toko POS Replacement

Last updated: 2026-06-09

## Final Status

Premium Toko POS is the PasPapan enterprise add-on that replaces toko-pandan for Pandan Teknik retail operations. The implementation baseline is cutover-ready for production UAT: the main legacy workflows are mapped, local cutoff data is reconciled, POS/purchase/inventory/report flows are implemented, and Toko finance now contributes to the PasPapan Accounting Workspace.

This PRD is not claiming operator rollout is complete. Remaining work is operational sign-off: cashier/purchasing/warehouse/finance UAT, owner report approval, production RBAC/menu lock, and toko-pandan archive policy.

## Summary

Build Premium Toko POS inside PasPapan so Pandan Teknik can stop using toko-pandan. The new system keeps the retail workflow from toko-pandan, runs on one centralized PasPapan database, uses PasPapan login/RBAC/license, and provides a cleaner daily interface for retail operators.

## Problem

toko-pandan already has many working flows: dashboard, products, barcode, category, brand, purchasing, sales, invoices, customers, quotations, operational expenses, stock, reports, supplier, users, and settings. The replacement must preserve those operational meanings without copying the old UI one-to-one. If a flow exists only as a placeholder, the migration is not acceptable.

## Goals

- Replace toko-pandan feature-by-feature with a better PasPapan implementation.
- Keep one centralized DB, login, RBAC, and enterprise license boundary.
- Make Toko/POS a premium add-on, not open source.
- Make POS a focused cashier workspace with nota, payment, split tender, receipt, and safe void/refund.
- Make purchase, inventory, customer, cash, and report workflows clear enough to operate without toko-pandan.
- Make tables lightweight: 10-row default, search, pagination, clear empty state, stable actions.
- Use Indonesian number formats consistently: `Rp100.000`, `99,50%`, `10 kg`.
- Feed Toko sales, AR, AP, purchases, expenses, and journals into PasPapan Accounting Workspace.
- Keep HRIS as the employee/payroll source while Toko consumes relevant finance impact.
- Provide migration reconciliation and rollback evidence for safe cutover.

## Non Goals

- Do not keep toko-pandan as a permanent dependency.
- Do not expose migration pages to day-to-day users after cutover.
- Do not clone the old visual design.
- Do not double count stock, sales, purchases, or accounting during migration.
- Do not create separate employee/payroll ownership inside Toko.

## Users

- Owner/admin: monitors dashboard, profit, stock value, AR/AP, and reports.
- Kasir: runs POS, scans/searches products, receives payment, prints nota, voids safely.
- Purchasing/admin gudang: receives purchases, tracks supplier AP, adjusts stock.
- Finance/admin: records expenses, payment accounts, AR/AP, and monthly summaries.
- Manager: checks product movement, low stock, sales trends, and operational cost.

## Company Scope Model

- Every Toko/POS operational record belongs to a company through `company_id`.
- Super Admin is the global head role and can choose which company scope to inspect or operate.
- Admin users are company operators; they are scoped to their own `users.company_id`.
- Admin users get Toko POS view/manage/export permissions, but not migration/import permissions by default.
- Migration/import/cutover remains Super Admin/head-level because it can overwrite large historical datasets.
- PasPapan Accounting Workspace aggregates or scopes Toko contribution using the same company visibility rule.
- Toko/POS services must reject cross-company create, payment, cancellation, and stock mutation attempts even if the UI is bypassed.

## Branch, Store, And Entitlement Policy

- Branch/store data remains a core PasPapan operations concept through `company_branches`.
- Not every company branch needs the Toko/POS add-on. Branches that only use HRIS, attendance, projects, field work, or operations should not receive Toko/POS permissions.
- Toko/POS access is controlled by enterprise feature `toko_pos` plus RBAC permissions: `admin.toko_pos.view`, `admin.toko_pos.manage`, `admin.toko_pos.export`, and head-only `admin.toko_pos.import`.
- Current baseline is company-scoped and branch-aware. `branch_id` is available on POS invoices, purchase bills, stock movements, quotations, and delivery letters.
- Branch/store selector supports consolidated company view or one branch/store view for Toko operational reports and transaction lists.
- A branch/store cashier role can be assigned by granting only the needed Toko permissions and operating inside the relevant company plus branch/store scope.

## Reporting Visibility

- Company admin sees Toko and finance reports only for their own `company_id`.
- Super Admin/head can see consolidated Accounting Workspace reports across companies and can inspect Toko reports per selected company.
- Head-level reporting supports consolidated group totals, company-by-company drilldown, and branch/store-level Toko drilldown where branch scope is selected.
- Accounting Workspace remains company-level finance by default; branch/store analysis is handled in the Toko operational reports unless finance branch reporting is explicitly enabled later.

## Core Integration Contract

- Toko/POS is a PasPapan retail domain module, not a sidecar application.
- Authentication, RBAC, company scope, HRIS employee data, accounting accounts, invoices, vendor bills, journals, payments, and reports stay owned by PasPapan core.
- Toko/POS owns retail workflows only: POS, product retail metadata, barcode, stock movement, delivery letters, retail purchases, retail customers, operational expenses, and retail reports.
- Every money-impacting Toko/POS transaction must be visible in PasPapan Accounting Workspace.
- Legacy toko-pandan import is a one-time transition path into the shared PasPapan database, not a permanent bridge.

## UX Principles

- Keep menu meaning familiar, but make screens cleaner and faster.
- POS must prioritize cashier flow over dashboard/admin information.
- Icon-only buttons are preferred for table/action controls with tooltips.
- Form submit/cancel/reset buttons may keep text labels for clarity.
- No broken buttons or dead UI.
- Every list with real volume uses search and pagination.
- Reports must explain numbers clearly enough for owner decisions.

## Target Menu And Acceptance

### Dashboard

Required:

- Karyawan HRIS, supplier, product, low-stock cards.
- Today sales, today purchases, gross profit, AR, AP.
- Stock available, stock in/out, valuation, estimated margin, sold omzet.
- Top stock, top sold products.
- Retail vs invoice/unpaid chart.
- Expense mix chart.
- Monthly income/cost/net summary.
- AR/AP aging and profit/loss insight.

Status: cutover-ready baseline. Remaining gate is owner/admin sign-off against the accepted final dump and preferred report presentation.

### Barang

Required:

- Data Barang, Tambah Barang, Barcode, Kategori, Brand.
- Product CRUD with standard and advanced fields.
- SKU/barcode, category, brand, unit, stock, low stock, expired, rack/location, cost, selling price, margin.
- Product stock card and movement history.
- Import/export, search, pagination, and stable actions.

Status: cutover-ready baseline. Active product count and valuation reconcile to the local frozen dump.

### Menu Pembelian

Required:

- Buat Pembelian, Data Transaksi, Hutang, Rekap Pembelian.
- Supplier, date, due date, PO/faktur, extra cost, notes, line items.
- Paid/hutang status.
- Detail, cancel/reversal audit, AP aging, partial/full payment history.
- Purchase recap by supplier/date/product and export.

Status: cutover-ready baseline. Remaining gate is purchasing operator UAT.

### Menu Penjualan

Required:

- Trx Retail, Buat Invoice, Data Invoice, Daftar Trx Retail, Rekening.
- Full cashier POS with barcode/search, cart, nota, receipt preview, print/reprint.
- Cash, transfer, QRIS, card, and split tender.
- Paid sale and unpaid invoice/credit sale.
- Customer selection, discount, charge, due days, bank/account.
- Void/cancel/refund that reverses stock, payment, and transaction state.

Status: cutover-ready baseline. Remaining gate is cashier UAT and final printed receipt sign-off.

### Pelanggan

Required:

- Customer CRUD, member/subscriber status, safe deactivate.
- Penawaran/quotation lifecycle, print, convert to sale/invoice.
- Pendapatan/customer income by period.
- AR drilldown.

Status: cutover-ready baseline. Hard-delete remains intentionally avoided unless owner requests legacy destructive behavior.

### Pengeluaran Operasional

Required:

- Tambah Trx, Data Operasional, Tipe Pengeluaran.
- Expense type, amount, date, notes, edit/void audit.
- Payment account/cash/bank mapping.
- Expense report and dashboard chart contribution.

Status: cutover-ready baseline. Expense type decisions are mapped from legacy and should be reviewed during finance UAT.

### Stok

Required:

- Data Stok, Stok Masuk, Stok Keluar, Surat Jalan, Mutasi Stok, Retur, Stok Menipis, Penyesuaian Stok, Valuasi.
- Stock mutation audit by transaction/source.
- Manual stock-in/out, opname/adjustment, stock card, low-stock, expired, returns.
- Delivery letter create/list/print/reprint.

Status: cutover-ready baseline. Remaining gate is warehouse UAT and approval-policy decision for adjustments.

### Laporan

Required:

- Sales report.
- Purchase report.
- Inventory valuation.
- Operational expense report.
- Profit/loss report.
- AR/AP aging.
- Product movement report.
- Customer income report.
- Supplier purchase report.
- Useful CSV/PDF/XLSX exports.

Status: cutover-ready baseline for data flow and CSV exports. Remaining gate is owner sign-off for final PDF/XLSX format scope.

### Supplier

Required:

- Vendor CRUD.
- Vendor purchase history.
- AP/hutang integration.
- Supplier summary.

Status: cutover-ready baseline.

### Manajemen User

Required:

- Legacy admin/jabatan/chmenu mapped to PasPapan users, roles, permissions, and menu access.
- Enterprise add-on permission boundary remains active.

Status: mapped to PasPapan core. Remaining gate is production invite/RBAC sign-off.

### Pengaturan

Required:

- Legacy settings, numbering, payment options, pin, backup/archive, license, and announcements mapped to PasPapan settings/license/maintenance where relevant.

Status: migration snapshot complete. Remaining gate is production decision for announcement publishing and retired legacy settings.

## Data Migration Requirements

Cutover sequence:

1. Confirm final frozen toko-pandan dump.
2. Clean Toko/POS domain data in PostgreSQL.
3. Import master data.
4. Import historical transactions.
5. Reconcile counts and totals.
6. Archive dump and rollback report.
7. Run UAT on imported data.
8. Confirm migration menu is head/import-gated and disable it after cutover when no longer needed.
9. Archive/read-only/redirect toko-pandan.

Must reconcile:

- Products count and stock value.
- Categories and brands.
- Customers and suppliers.
- Sales totals and item totals.
- Purchase totals and stock receiving.
- AR and AP.
- Operational expenses.
- Returns.
- Cash/bank/payment accounts.
- Report totals by month.
- Stock movements without double-applying stock against the cutoff snapshot.

## Accepted Local Cutoff Baseline

Source dump:

- `/Users/lutuk/Project/learning/toko-pandan/database/toko.sql`
- Timestamp: `2026-06-09T00:50:52+07:00`

Reconciled local totals:

- Active products: 690
- Total stock: 18.693
- Stock out: 13.823
- Stock in: 17.209
- Low stock: 105
- Total estimasi modal: Rp599.092.044
- Total estimasi pemasukan: Rp764.537.500
- Total estimasi laba: Rp165.445.456
- Total omzet terjual: Rp680.723.500

Screenshots/live toko-pandan pages from a different timestamp are not reconciliation evidence. If production data changes, import and reconcile the new final frozen dump.

## DataTable Standard

Every admin/toko list with operational volume must provide:

- Default page size: 10.
- Search box.
- Pagination.
- Total record count.
- Empty state.
- Stable action column.
- Icon buttons for table actions.
- Text buttons allowed for form submit/cancel/reset.
- No broken buttons.

## Acceptance Criteria

Legacy can be retired only when:

- Every legacy menu has a mapped PasPapan workflow.
- Every mapped workflow has create/read/update/delete or approved read-only behavior.
- POS cashier can complete, print, void, and review a sale without toko-pandan.
- Purchasing can receive, cancel, pay hutang, and report purchases without toko-pandan.
- Inventory can trace stock movement without toko-pandan.
- Finance can see Toko contribution inside PasPapan Accounting Workspace.
- Owner/admin can make the same or better decisions than old dashboard/reports.
- Migration reconciliation is reviewed and accepted.
- Enterprise build and tests pass.
- Operator training and production RBAC/menu decisions are complete.

## Production UAT Protocol

UAT must use the accepted frozen dump, not screenshots or live toko-pandan pages from a different timestamp.

### UAT Evidence Requirements

Every UAT item must record:

- Tester name and role.
- Company and branch/store scope.
- Date and environment.
- Scenario executed.
- Transaction number, report export, screenshot, or PDF evidence.
- Pass/fail result.
- Follow-up note when failed or accepted with caveat.

### Required UAT Scenarios

| Area | Scenario | Expected result |
| --- | --- | --- |
| Dashboard | Compare active products, stock, valuation, omzet, profit/loss, AR/AP, top stock, top sold, and charts | Owner can trace each number to reports or accepted reconciliation |
| POS paid sale | Scan/search product, add to cart, apply discount/charge, pay cash/transfer/QRIS/card, print nota | Invoice paid, stock decreases, payment recorded, receipt printable |
| POS unpaid invoice | Create invoice/credit sale with customer and due days | Invoice remains AR until paid and appears in AR report |
| Split tender | Add multiple payment rows, block underpayment, complete exact/over payment | Payment lines are stored and total is correct |
| Void/refund | Cancel unpaid sale and refund paid sale | Invoice status changes, stock/payment reversal is traceable |
| Purchasing | Create purchase, receive stock, set due date/PO/faktur, pay full/partial AP | Stock increases, AP status and payment history are correct |
| Purchase cancellation | Cancel paid/unpaid purchase | Stock/AP/payment reversal is traceable |
| Inventory | Manual stock in/out, opname, return, stock card, delivery letter | Movement audit links to source and branch/store scope where selected |
| Customer/quotation | Create customer, convert status, create quotation, accept/reject, convert to invoice | Customer and quotation lifecycle works without toko-pandan |
| Cash/expense | Create expense type, record expense, edit/void, export report | Expense appears in report and Accounting Workspace contribution |
| Reports | Export sales, purchases, valuation, product movement, AR/AP, profit/loss | Report totals match dashboard and accepted reconciliation |
| RBAC/company/branch | Login as Super Admin, company admin, and normal operator | Scope is correct; normal company admin cannot open migration |

### Human Sign-off Rules

- Cashier lead signs POS only after paid sale, unpaid invoice, split tender, print/reprint, and void/refund pass.
- Purchasing/admin gudang signs purchasing only after receiving, AP payment, and cancellation pass.
- Warehouse/admin gudang signs inventory only after stock movement, stock card, return, delivery letter, and branch/store scope pass.
- Finance/admin signs finance only after expenses, AR/AP, reports, and Accounting Workspace contribution pass.
- Owner/admin signs final cutover only after dashboard/report totals and migration reconciliation are accepted.
- Head/admin signs RBAC only after role assignment and migration visibility are confirmed in production.

## Operator Training Protocol

Training must be role-based and evidence-based. A user is considered trained only when attendance records include participant name, role, company, branch/store where relevant, scenarios completed, result, and follow-up owner if any scenario needs retry.

Required training paths:

- Cashier: POS search/scan, cart, paid sale, unpaid invoice, split tender, receipt print/reprint, void/refund.
- Purchasing/admin gudang: purchase receiving, AP due date, partial/full payment, purchase cancellation.
- Warehouse/admin gudang: stock in/out, opname, stock card, return, delivery letter.
- Finance/admin: expense type, expense entry, edit/void, AR/AP, report export, Accounting Workspace contribution.
- Owner/admin: dashboard, report drilldown, profit/loss, valuation, AR/AP, company/branch scope.
- Head admin: production RBAC, company scope, branch/store scope, migration access guard.

## Final Production Gates

1. Cashier, purchasing, warehouse, finance, and owner UAT.
2. Owner/admin approval for dashboard/report totals.
3. Release build with enterprise obfuscation and license entitlement.
4. Production RBAC/menu lock, especially confirming migration is import/head-only or disabled.
5. toko-pandan archive/read-only/redirect policy.
