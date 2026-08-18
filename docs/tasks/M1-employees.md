# M1 — Employees, Contracts, Master Data

**Plan:** `docs/plans/juri-hr-mvp.md`
**Depends on:** M0
**Exit criteria:** HRD can import employees from Excel (including contract data and attendance mode), every imported employee can log in, master data needed by attendance exists, and contract expiry is visible to both HRD and the employee.

## Context

Employees are the anchor for everything else: attendance mode decides how work hours resolve (M2), NIP is the matching key for payslip uploads (M5), and contract fields come from native Frappe HR `Employee` fields — no custom contract doctype in the MVP.

`custom_attendance_mode` (`tetap` | `shift`) is a new custom field on `Employee`:
- `tetap` — office staff, uses company standard office hours, no schedule input at all
- `shift` — outlet staff and outlet-operations division, uses the monthly shift schedule

---

## M1.1 Employee import backend

- [ ] Add custom field `custom_attendance_mode` (Select: `tetap`, `shift`, default `tetap`) to `Employee` via fixtures in `juri_hr`
- [ ] `juri_hr.employees.import_preview(file_url)`: parse xlsx/csv with columns `nip, nama, email, divisi, jabatan, mode_absensi, tipe_kontrak, tanggal_masuk, tanggal_berakhir_kontrak`
- [ ] Validation per row: required `nip`, `nama`, `email`; email format; unique `nip` and email (within file and against existing records); `mode_absensi` in (`tetap`, `shift`); dates parseable; `tanggal_berakhir_kontrak` not before `tanggal_masuk`; unknown division/job title flagged (create-on-commit option or error, pick create-on-commit and report it)
- [ ] Return `{ rows: [{ row_number, data, errors[], warnings[] }], summary: { total, valid, invalid } }` and store nothing
- [ ] `juri_hr.employees.import_commit(rows)`: inside a transaction create/update `Employee`, create `User` with the Employee role, map `mode_absensi` -> `custom_attendance_mode`, map contract fields to `employment_type`, `date_of_joining`, `contract_end_date`; abort the whole commit if any row fails
- [ ] Emit one audit event summarising the import (count created/updated) — see M6.1; if M6.1 is not implemented yet, add a TODO with the exact call site
- [ ] Python unit test: a file with one invalid row commits nothing

**AC**
- Valid file creates Employee + User records that can log in
- Invalid rows are reported with reasons and nothing is partially committed

## M1.2 Employee import UI

- [ ] Page `Karyawan > Import`: download template button (generated client-side or served static), file picker, upload
- [ ] Preview table: row number, key fields, per-row error and warning messages, summary counters, commit button disabled while any error exists
- [ ] After commit: result summary (created, updated) and link to the employee list

**AC**
- HRD can complete an import without reading logs; every rejection reason is visible in the table

## M1.3 Employee management and directory

- [ ] Admin employee list: search by name/NIP, filter by division and attendance mode, paginated `DataTable`
- [ ] Admin employee detail/edit: name, NIP, email, division, job title, **attendance mode toggle**, employment type, join date, contract end date, status
- [ ] Employee directory (visible to all roles): name, division, job title, work email/phone — read-only, no salary or personal document data

**AC**
- Editing attendance mode immediately changes which schedule source applies in M2
- Directory exposes no sensitive fields

## M1.4 Master data

- [ ] Divisions CRUD (list, create, edit, deactivate)
- [ ] Job titles CRUD
- [ ] `Shift Type` CRUD (name, start time, end time; overnight supported when end <= start) — clearly labelled as applying to `shift` mode staff only
- [ ] Holiday list selection: choose the active `Holiday List` used for `holiday` status, with a link to manage it in Frappe desk

**AC**
- Shift types created here are selectable in the M2 schedule import
- The active holiday list is readable by `juri_hr.shift.resolve`

## M1.5 Contract visibility

- [ ] `juri_hr.contracts.expiring(window_days)`: employees with `contract_end_date` within the window (default from `contract.expiring_window_days`, fallback 30), ordered by date, including days remaining
- [ ] Admin page "Kontrak Segera Habis": list (employee, division, type, end date, days remaining), CSV export
- [ ] Employee "Kontrak Saya" card on their home/profile: employment type, join date, contract end date, days remaining (or "tanpa batas waktu" for permanent)

**AC**
- List matches `contract_end_date` data
- An employee sees only their own contract information

## Notes

- No contract renewal workflow, no reminder scheduler, no contract PDF in the MVP — those are post-MVP roadmap items
- Keep the import template column names in Indonesian (HRD-facing) while code and docs stay English
