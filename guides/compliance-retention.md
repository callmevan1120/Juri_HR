# Compliance And Retention

## Personal Data

- Personal data export should include profile, attendance, leave, reimbursements, kasbon, assets, documents, and audit references.
- Deletion/anonymization must preserve statutory payroll/audit records while removing unnecessary personal identifiers.

## Audit Retention

- Field-level audit logs for sensitive changes should be retained according to company policy and legal requirements.
- Audit logs must be append-only and tamper-evident.

## Backup Retention

- Keep only the minimum operational backup history needed for recovery.
- Store backups on private storage and validate checksum during drills.
- Delete expired backups through audited maintenance flows.

## Payroll Retention

- Payroll records, tax calculations, BPJS, THR, payment instructions, and payslips should follow Indonesian statutory retention requirements.
- Payslip access must stay employee/admin scoped and never expose other employees.

## Attachment Retention

- Attendance photos, leave attachments, reimbursement attachments, HR checklist attachments, and employee documents should have category retention windows.
- Expired attachments should be deleted by audited maintenance jobs.
