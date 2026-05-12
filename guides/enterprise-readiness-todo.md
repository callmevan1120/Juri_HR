# Enterprise Readiness TODO

This backlog tracks large work that should be implemented in dedicated slices with tests.

## E2E Smoke

- Playwright: login, admin dashboard, employee CRUD, attendance scan, leave approval, reimbursement approval, HR checklist, attachment download, payroll generate, payslip view, export report.
- Android/APK: camera, geolocation, barcode scanner, photo attendance, offline queue sync.

## Performance Baseline

- Seed 10k users and 100k attendance rows.
- Benchmark admin attendance listing, dashboard, payroll generation for 1k employees, and large import/export.
- Record target p95 and memory ceilings.

## Product Coverage

- HR lifecycle renewal workflow.
- Payroll variance report UI.
- Approval matrix for leave, overtime, attendance correction, asset, document request, and payroll-sensitive action.
- HR command center dashboard.

## Enterprise Controls

- SSO/OIDC foundation.
- Mandatory MFA for high-risk admin actions.
- Admin IP allowlist.
- Login audit and session/device management.
- Retention/anonymization workflows.
