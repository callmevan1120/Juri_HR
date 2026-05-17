# Multi-Company Isolation

Multi-company is currently guarded as a backend safety layer with a narrow admin UI for creating company, branch, store, or partner scopes. Broader tenant-specific settings should stay behind isolation tests for every sensitive module.

## Admin Entry Point

- Route: `admin.companies`
- Menu: Admin navigation > System > Companies
- Permission: `admin.companies.manage`
- Scope behavior: assign admins or employees to `users.company_id`; superadmins remain global.

## Enforced Scope

- Users are filtered by `User::managedBy()` when the actor has `company_id`.
- Policies deny cross-company access for:
  - Attendance
  - Payroll
  - Reimbursement
  - Kasbon
  - Assets
  - HR checklist cases/tasks

## Compatibility Rule

Legacy rows with `company_id = null` remain accessible to avoid breaking single-company installs. Once multi-company is enabled broadly, migrations/backfill must assign all users to a company before UI exposure.

## Isolation Test Proof

- `tests/Feature/SecurityIsolationMatrixTest.php`
- `tests/Feature/ProductFoundationServiceTest.php`
- `tests/Feature/AdminCompanyManagerTest.php`

## Backlog Before Broader UI Exposure

- Scope settings per company for tenant-specific branding/policies.
- Scope activity logs and audit exports.
- Scope all report/export jobs by company.
- Scope document templates and generated employee documents.
- Add database indexes for `company_id` paths used by high-volume reports.
