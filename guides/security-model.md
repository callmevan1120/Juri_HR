# Security Model

Sensitive data includes payroll amounts, salary, bank accounts, payslip passwords, enterprise license keys, backup artifacts, attendance location/photo data, role permissions, and HR documents.

Route protection is layered:

- Web routes require `auth:sanctum`, Jetstream session middleware, and `verified`.
- Admin routes add the `admin` middleware.
- Route-level `can:*` checks call gates or policies.
- `feature.lock:*` blocks enterprise modules when the license does not grant access.
- Secure attachment routes check ownership or admin policy before reading private files.

RBAC permissions live in `config/rbac.php` and are enforced through gates/policies. Avoid direct group checks for new features unless a legacy policy already requires it.

Audit strategy:

- General activity is written through `ActivityLog`.
- Sensitive model updates use field-level `ActivityLogDetail` with redaction/masking for secrets and bank data.
- Backup restore, role/permission, payroll, reimbursement, attendance correction, leave approval, settings, and asset assignment changes should remain audited.

Backup/restore risk:

- Backup artifacts can include database secrets and private attachments.
- Restore operations must require explicit maintenance authorization and signed backup validation.
- Error messages shown to users must not expose raw SQL, tokens, paths outside the backup disk, or license payloads.
