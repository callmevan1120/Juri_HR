# File Upload Security

PasPapan upload policy uses allowlists, private storage, and random storage names.

## Rules

- Allow extensions only by category:
  - document: `jpg`, `jpeg`, `png`, `pdf`
  - image: `jpg`, `jpeg`, `png`
  - spreadsheet: `csv`, `xls`, `xlsx`, `ods`
- Validate server-detected MIME type.
- Reject dangerous double extensions such as `proof.php.pdf`.
- Enforce max size per category.
- Store user attachments on private disk.
- Never trust original filename for storage path.
- Always serve files through a controller + policy.

## Current Coverage

- Leave request attachments.
- Reimbursement attachments.
- Import spreadsheet uploads.
- Profile/device image uploads.
- HR checklist task attachment download policy.

## Backlog

- Add malware scanning hook for enterprise deployments.
- Add object-storage signed download adapter.
- Add retention cleanup per attachment category.
