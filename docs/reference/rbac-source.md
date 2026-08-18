# Legacy RBAC Source (reference)

Extracted from the legacy Laravel app `config/rbac.php` (branch `legacy-reference`, commit 6d7fa91).
Kept as the naming reference for JuriHR permissions. Not all of these are in the MVP scope —
see `docs/plans/juri-hr-mvp.md` for what is actually built.

Full original file: `docs/reference/rbac-source.php.txt` (833 lines).

## Sections

- `attendance` — attendance operations, approvals, schedules, reporting
- `finance` — payroll, reimbursements, cash advance workflows
- `master_data` — employee records, organization references, directories
- `operations` — client, project, task, checklist, field work
- `system` — settings, logs, import/export, access management

## Permission strings (83 total)

- `admin.accounting.manage`
- `admin.accounting.view`
- `admin.activity_logs.export`
- `admin.activity_logs.view`
- `admin.admin_accounts.manage`
- `admin.admin_accounts.superadmin_delete`
- `admin.admin_accounts.superadmin_manage`
- `admin.admin_accounts.superadmin_view`
- `admin.admin_accounts.view`
- `admin.analytics.view`
- `admin.announcements.manage`
- `admin.api_integrations.manage`
- `admin.appraisals.calibrate`
- `admin.appraisals.manage`
- `admin.appraisals.view`
- `admin.assets.view`
- `admin.attendance_corrections.approve`
- `admin.attendance_corrections.view`
- `admin.attendances.export`
- `admin.attendances.report`
- `admin.attendances.view`
- `admin.barcodes.manage`
- `admin.cash_advances.manage`
- `admin.collaboration.manage`
- `admin.collaboration.view`
- `admin.command_center.view`
- `admin.commercial.manage`
- `admin.commercial.view`
- `admin.companies.manage`
- `admin.custom_forms.manage`
- `admin.custom_forms.view`
- `admin.dashboard.view`
- `admin.divisions.manage`
- `admin.document_requests.fulfill`
- `admin.document_requests.generate`
- `admin.document_requests.request`
- `admin.document_requests.templates`
- `admin.document_requests.view`
- `admin.educations.manage`
- `admin.employees.approve_account_deletion`
- `admin.employees.manage`
- `admin.employees.manage_status`
- `admin.employees.view`
- `admin.holidays.manage`
- `admin.hr_checklists.manage`
- `admin.hr_checklists.view`
- `admin.import_export_attendances.export`
- `admin.import_export_attendances.import`
- `admin.import_export_attendances.view`
- `admin.import_export_users.export`
- `admin.import_export_users.import`
- `admin.import_export_users.view`
- `admin.job_titles.manage`
- `admin.kpi_settings.manage`
- `admin.leave_approvals.approve`
- `admin.leave_entitlements.manage`
- `admin.leave_types.manage`
- `admin.notifications.view`
- `admin.operations.manage`
- `admin.operations.view`
- `admin.overtime.manage`
- `admin.payroll.view`
- `admin.payroll_settings.manage`
- `admin.rbac.assign`
- `admin.rbac.manage`
- `admin.reimbursements.approve`
- `admin.reimbursements.view`
- `admin.reports.view`
- `admin.schedules.manage`
- `admin.scope.global`
- `admin.settings.license`
- `admin.settings.manage`
- `admin.settings.view`
- `admin.shift_swaps.approve`
- `admin.shifts.manage`
- `admin.system_maintenance.manage`
- `admin.system_maintenance.view`
- `admin.toko_pos.export`
- `admin.toko_pos.import`
- `admin.toko_pos.manage`
- `admin.toko_pos.view`
- `admin.user_sessions.manage`
- `admin.wfh_requests.manage`
