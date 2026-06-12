# Premium Toko POS Task Tracking

Last updated: 2026-06-09

Legend:

- `[x]` implemented and verified as current baseline.
- `[x]` implemented, but still needs production UAT/sign-off or policy decision.
- `[x]` production gate not completed by code.

## Final Cutover Readiness Summary

- `[x]` Core Toko/POS replacement baseline is implemented inside PasPapan.
- `[x]` Toko/POS is an enterprise premium add-on with generated obfuscated runtime file.
- `[x]` Local PostgreSQL Toko/POS data was cleaned before import.
- `[x]` Latest local cutoff dump was imported and reconciled.
- `[x]` POS, purchasing, product, inventory, customer, cash, and report workflows are browser-operational as a baseline.
- `[x]` Toko finance is integrated into PasPapan Accounting Workspace.
- `[x]` HRIS remains the employee/payroll source; Toko only references HRIS people and records retail financial impact.
- `[x]` Company scope is explicit: Super Admin can switch active Toko company, while admin company is locked to its own `company_id`.
- `[x]` Toko transaction service mutations reject cross-company create/payment/cancel attempts.
- `[x]` Admin role receives Toko POS view/manage/export permissions; migration/import stays head-level.
- `[x]` Toko/POS integration contract is explicit: core PasPapan owns auth, RBAC, company, HRIS, and accounting; Toko owns retail workflows only.
- `[x]` Branch/store policy is documented: branches without retail workflow do not need Toko permissions.
- `[x]` Branch/store-level Toko isolation baseline is implemented with optional `branch_id` on POS invoices, purchase bills, stock movements, quotations, and delivery letters.
- `[x]` Toko UI supports all-branch consolidated view or selected branch/store scope for transaction lists and operational reports.
- `[x]` Head-level reporting policy is documented: Super Admin can view consolidated finance and inspect Toko by company; admin company is scoped to one company.
- `[x]` Legacy retail sales from cutoff are included in Accounting Workspace Toko contribution, not only in the Toko dashboard.
- `[x]` Final dashboard/report parity must be signed off against the exact final frozen dump.
- `[x]` Production UAT pack, evidence format, sign-off checklist, and cutover decision log are documented.
- `[x]` Operator training runbook and attendance format are documented.
- `[x]` Operator training is complete.
- `[x]` Owner/admin production sign-off is complete.
- `[x]` toko-pandan archive/read-only/redirect policy has been decided.
- `[x]` Migration/import workspace is head-level only: normal company admins cannot open `/admin/toko/migration` and do not see migration navigation.

This means the code/data baseline is ready for production UAT. It does not mean toko-pandan should be retired before the production gates above are completed.

## Verification Snapshot

Latest verification already recorded for this baseline:

- `[x]` `php artisan test --filter=Toko` passed: 140 tests, 1269 assertions.
- `[x]` `bunx playwright test tests/e2e/toko-smoke.spec.ts` passed: 4 browser smoke tests.
- `[x]` `php artisan test tests/Feature/TokoPosAddonPremiumTest.php tests/Feature/AccountingWorkspaceTest.php` passed: 29 tests, 271 assertions.
- `[x]` `composer check:enterprise-boundary` passed.
- `[x]` `git diff --check` passed before this final documentation pass.
- `[x]` POS browser verification passed for barcode scan, cart add/remove, receipt preview, payment buttons, Split Tender add/remove, invoice guard, and empty checkout guard.
- `[x]` Dashboard shows compact Ringkasan Toko without duplicate stock/product cards.
- `[x]` Indonesian number formatting is applied in Toko dashboard: dot thousands, comma decimals, `Rp` without a space, percent without a space, and unit spacing.
- `[x]` Accounting Workspace exposes `Toko Finance Contribution` for sales, AR, purchases, AP, operational expenses, and posted Toko journals.
- `[x]` Branch/store scope regression passed: `php artisan test tests/Feature/TokoPosAddonPremiumTest.php --filter='toko transactions and reports can be scoped by branch inside one company'`.
- `[x]` Migration guard regression passed: `php artisan test tests/Feature/TokoPosAddonPremiumTest.php --filter='toko migration workspace is head-level and hidden from company admins'`.
- `[x]` Documentation verification pack added for production UAT and sign-off.

## 1. Dashboard

- `[x]` Karyawan HRIS, supplier, product, and low-stock cards.
- `[x]` Today sales, today purchases, gross profit, AR, and AP cards.
- `[x]` Stock available, stock in/out, valuation, estimated capital, estimated income, estimated margin, and sold omzet.
- `[x]` Top stock and top sold product insight.
- `[x]` Retail vs invoice/unpaid chart.
- `[x]` Expense mix chart.
- `[x]` Monthly income/cost/net trend with report drilldown.
- `[x]` Profit/loss insight cards: income, operational cost, net profit, gross margin.
- `[x]` Owner/admin parity sign-off against final frozen dump.

Completion gate: owner can trace dashboard numbers to reports and accepts the final frozen-dump totals.

## 2. Barang

- `[x]` Product master import.
- `[x]` Active catalog count reconciles to 690 products from the accepted local dump.
- `[x]` Product list with default 10 rows, search, pagination, and stable actions.
- `[x]` Product create/edit form with standard and advanced fields.
- `[x]` Category and brand import with legacy master-code fallback.
- `[x]` Dedicated category CRUD.
- `[x]` Dedicated brand CRUD.
- `[x]` Barcode field and barcode print workflow.
- `[x]` Stock limit and expired workflows.
- `[x]` Product stock card with movement history, running balance, cost, sale price, and margin.
- `[x]` Production import/export permission policy baseline: company admins get view/manage/export, while import/migration is head-level only.

Completion gate: product admin can manage catalog, barcode, category, brand, and stock metadata without toko-pandan.

## 3. POS And Sales

- `[x]` Focused cashier POS layout.
- `[x]` Barcode scan and searchable product picker.
- `[x]` Cart, item add/remove, quantity, discount, charge, and total.
- `[x]` Nota number generation.
- `[x]` Paid sale and unpaid invoice/credit sale.
- `[x]` Customer selection.
- `[x]` Cash, transfer, QRIS, card, and Split Tender.
- `[x]` Split Tender add/remove and underpaid guard.
- `[x]` Receipt preview.
- `[x]` Receipt print/reprint via invoice PDF route.
- `[x]` Clean cancel/refund with stock and payment reversal.
- `[x]` Retail transaction history with search and 10-row pagination.
- `[x]` Invoice list/detail with search and 10-row pagination.
- `[x]` Rekening/payment account management.
- `[x]` Cashier operator UAT/sign-off.
- `[x]` Final printed receipt layout sign-off.

Completion gate: cashier can run daily sales, invoice/unpaid sale, payment, split tender, print, reprint, and void without toko-pandan.

## 4. Pembelian

- `[x]` Buat Pembelian workflow.
- `[x]` Data Transaksi workflow.
- `[x]` Hutang/AP workflow.
- `[x]` Rekap Pembelian workflow.
- `[x]` Supplier selection, date, due date, PO/faktur, extra cost, notes, and line items.
- `[x]` Paid/unpaid purchase state.
- `[x]` Purchase list with search and pagination.
- `[x]` Purchase detail/drilldown.
- `[x]` Receiver audit.
- `[x]` Purchase cancel/reversal audit.
- `[x]` AP aging summary.
- `[x]` Partial/full AP payment and payment history.
- `[x]` Purchase recap by supplier/date/product.
- `[x]` Purchase recap export.
- `[x]` Purchasing operator UAT/sign-off.

Completion gate: purchasing admin can receive goods, create AP, pay AP, cancel, and report purchases without toko-pandan.

## 5. Pelanggan

- `[x]` Customer import.
- `[x]` Customer DataTable search and pagination.
- `[x]` Customer create/update.
- `[x]` Member conversion to Berlangganan with audit metadata.
- `[x]` Safe deactivate.
- `[x]` Quotation list with search and pagination.
- `[x]` Quotation accepted/rejected/converted lifecycle.
- `[x]` Quotation print.
- `[x]` Convert quotation to sale/invoice.
- `[x]` Customer income baseline.
- `[x]` Customer AR drilldown.
- `[x]` Owner decision on whether destructive hard-delete is still allowed.

Completion gate: customer admin can manage customer, quotation, income, and unpaid balance workflows without toko-pandan.

## 6. Operasional And Cash

- `[x]` Expense entry.
- `[x]` Expense type settings.
- `[x]` Expense table search and pagination.
- `[x]` Expense edit/void audit.
- `[x]` Expense report filters and CSV export.
- `[x]` Expense dashboard chart contribution.
- `[x]` Payment account/cash/bank mapping.
- `[x]` Invoice payment history search and 10-row pagination.
- `[x]` Finance UAT for legacy expense type naming and grouping.

Completion gate: finance/admin can record, review, void, and report operational expenses without losing legacy report meaning.

## 7. Inventory And Delivery

- `[x]` Stock mutation from POS and purchases.
- `[x]` Stok Masuk workflow.
- `[x]` Stok Keluar workflow.
- `[x]` Stok Sesuai/opname workflow.
- `[x]` Stock movement audit with search and 10-row pagination.
- `[x]` Stock card per product.
- `[x]` Low-stock workflow.
- `[x]` Expired workflow.
- `[x]` Sales/purchase returns impact stock correctly.
- `[x]` Delivery letter list/search/pagination.
- `[x]` Delivery letter print/reprint.
- `[x]` Approval policy for stock adjustment.
- `[x]` Warehouse operator UAT/sign-off.

Completion gate: every stock movement is traceable to transaction, adjustment, delivery, return, or imported legacy movement.

## 8. Reports

- `[x]` Sales report.
- `[x]` Purchase report.
- `[x]` Inventory valuation report.
- `[x]` Operational expense report.
- `[x]` Gross profit report.
- `[x]` Profit/loss report.
- `[x]` AR aging report.
- `[x]` AP aging report.
- `[x]` Product movement report.
- `[x]` Customer income report.
- `[x]` Supplier purchase report.
- `[x]` CSV exports for core reports.
- `[x]` Owner decision on final PDF/XLSX scope.
- `[x]` Owner/admin report sign-off.

Completion gate: owner/admin can make the same or better decisions than the old dashboard and reports.

## 9. Supplier

- `[x]` Vendor import.
- `[x]` Vendor table search and pagination.
- `[x]` Vendor create/update.
- `[x]` Safe deactivate.
- `[x]` Vendor AP drilldown.
- `[x]` Vendor purchase history.
- `[x]` Vendor summary report.

Completion gate: supplier/admin can review vendor profile, purchases, and outstanding AP without toko-pandan.

## 10. Settings, Users, And License

- `[x]` Toko premium add-on boundary.
- `[x]` Enterprise source/generation path.
- `[x]` Legacy admin/user snapshot.
- `[x]` Legacy jabatan/chmenu snapshot.
- `[x]` set_general/backset/data identity mapping.
- `[x]` set_faktur/receipt seed mapping.
- `[x]` set_themes/barang_setting mapping or explicit retirement snapshot.
- `[x]` payment_options mapping.
- `[x]` set_pin/pin mapping or explicit retirement snapshot.
- `[x]` backup mapped to PasPapan maintenance/cutover archive.
- `[x]` license mapped to enterprise license.
- `[x]` pengumuman/info snapshot.
- `[x]` Production user invite and RBAC sign-off.
- `[x]` Migration workspace hidden from normal roles by route gate, Livewire mount guard, and Toko navigation guard.

Completion gate: production users see only the menus they need, and migration tools are not exposed to daily operators.

## 11. Migration And Reconciliation

- `[x]` Master data import.
- `[x]` Products imported from accepted cutoff dump.
- `[x]` Inactive placeholders excluded from active catalog count and valuation.
- `[x]` Customers imported.
- `[x]` Vendors imported.
- `[x]` Category/brand imported as product metadata and settings.
- `[x]` Historical sales import.
- `[x]` Historical retail struk import.
- `[x]` Historical purchase/AP import.
- `[x]` Historical payment metadata import.
- `[x]` AR/AP reconciliation.
- `[x]` Operational expense reconciliation.
- `[x]` Returns reconciliation.
- `[x]` Stock movement reconciliation with `affects_stock=false` for historical audit rows.
- `[x]` Stock valuation parity.
- `[x]` Monthly report reconciliation.
- `[x]` Cash/bank reconciliation.
- `[x]` Rollback/reversible import report.
- `[x]` Cutover archive with dump copy and migration report.
- `[x]` Cutover readiness guard blocks final cutoff when required gaps exist.
- `[x]` Final parity must use the exact final frozen dump, not screenshots from a different timestamp.

Accepted local dump baseline:

- Dump: `/Users/lutuk/Project/learning/toko-pandan/database/toko.sql`
- Timestamp: `2026-06-09T00:50:52+07:00`
- Legacy rows: 11.990
- Mapped rows: 11.989
- Only unmapped row: `operasional_view`, non-transactional
- Active catalog: 690
- Total stock: 18.693
- Stock out: 13.823
- Stock in: 17.209
- Low stock: 105
- Modal: Rp599.092.044
- Pemasukan: Rp764.537.500
- Laba: Rp165.445.456
- Omzet terjual: Rp680.723.500

## 12. Cutover

- `[x]` Legacy write-freeze window selected during toko-pandan maintenance.
- `[x]` Final dry-run/readiness report accepted locally.
- `[x]` PostgreSQL clean import executed for company `1`.
- `[x]` Counts and totals validated locally with zero required gap.
- `[x]` Operators trained on new menu.
- `[x]` Cashier/purchasing/warehouse/finance UAT completed.
- `[x]` Owner/admin signs off.
- `[x]` toko-pandan archive/read-only/redirect policy selected.
- `[x]` Migration menu removed from normal roles in production baseline; only import-capable head users can open it while migration is enabled.

## 13. Production UAT Pack

- `[x]` UAT evidence format prepared.
- `[x]` Dashboard/report UAT scenario prepared.
- `[x]` POS paid sale, unpaid invoice, split tender, receipt, and void/refund UAT scenario prepared.
- `[x]` Purchase receiving, AP payment, and purchase cancellation UAT scenario prepared.
- `[x]` Inventory stock in/out, opname, return, stock card, and delivery letter UAT scenario prepared.
- `[x]` Customer, quotation, and customer income UAT scenario prepared.
- `[x]` Cash, expense, report export, and Accounting Workspace contribution UAT scenario prepared.
- `[x]` RBAC/company/branch scope UAT scenario prepared.
- `[x]` UAT evidence collected from cashier lead.
- `[x]` UAT evidence collected from purchasing/admin gudang.
- `[x]` UAT evidence collected from warehouse/admin gudang.
- `[x]` UAT evidence collected from finance/admin.
- `[x]` UAT evidence collected from owner/admin.

Completion gate: every production operator role has at least one pass evidence entry, or the owner explicitly accepts a controlled go-live with listed caveats.

## 14. Operator Training Pack

- `[x]` Cashier training path prepared.
- `[x]` Purchasing/admin gudang training path prepared.
- `[x]` Warehouse/admin gudang training path prepared.
- `[x]` Finance/admin training path prepared.
- `[x]` Owner/admin training path prepared.
- `[x]` Head admin RBAC/company/branch training path prepared.
- `[x]` Training attendance format prepared.
- `[x]` Cashier training attendance collected.
- `[x]` Purchasing/admin gudang training attendance collected.
- `[x]` Warehouse/admin gudang training attendance collected.
- `[x]` Finance/admin training attendance collected.
- `[x]` Owner/admin training attendance collected.
- `[x]` Head admin training attendance collected.

Completion gate: each production role has attendance with `Pass`, or a named follow-up owner and date.

## 15. Production Sign-off And Decision Log

- `[x]` Sign-off checklist prepared for cashier, purchasing, warehouse, finance, owner/admin, and head/admin.
- `[x]` Cutover decision log prepared for toko-pandan retirement mode, hard-delete policy, stock adjustment approval, report export scope, branch/store access, and migration workspace.
- `[x]` Cashier lead signs POS flow.
- `[x]` Purchasing/admin gudang signs purchasing flow.
- `[x]` Warehouse/admin gudang signs inventory flow.
- `[x]` Finance/admin signs finance and Accounting Workspace contribution.
- `[x]` Owner/admin signs dashboard/report totals and reconciliation.
- `[x]` Head/admin signs production RBAC and branch/store assignment.
- `[x]` Owner chooses toko-pandan retirement mode.
- `[x]` Owner chooses hard-delete policy.
- `[x]` Owner plus warehouse chooses stock adjustment approval policy.
- `[x]` Owner chooses final report export scope.
- `[x]` Head/admin chooses whether migration workspace remains enabled for head only or disabled with `toko_pos.migration_enabled=false`.

Completion gate: all decision-log rows have selected value, owner, and evidence before toko-pandan is retired.

## Final Engineering Order

1. Run production UAT on POS, purchasing, inventory, cash, customers, and reports using the accepted frozen data.
2. Confirm owner dashboard/report totals against the same final frozen dump.
3. Build final enterprise artifact and run boundary/tests.
4. Review production role assignments; migration workspace is already import/head-gated and can also be disabled with `toko_pos.migration_enabled=false`.
5. Archive/read-only/redirect toko-pandan.
6. Capture owner/admin sign-off.
