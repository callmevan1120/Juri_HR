# M5 — Payslip Distributor

**Plan:** `docs/plans/juri-hr-mvp.md`
**Depends on:** M0, M1 (employees with NIP)
**Exit criteria:** HRD uploads one Excel per period, reviews a preview, then publishes immediately or schedules publication; each employee sees only their own payslip and can print it. No payslip emails are sent.

## Context

This phase removes the pain of emailing payslips one by one (blasts get the mail account banned). JuriHR does **not** calculate payroll in the MVP — it distributes what HRD already produced elsewhere.

Format decision: the system provides the template (download -> fill -> upload). The parser stays tolerant — only `nip` and `net_salary` are required; every other column becomes a payslip component whose label is the column name. Free-form HRD formats with column mapping are post-MVP.

Scheduling matters because payslips should appear at the same time the salary lands in the bank account. Before its scheduled moment a batch must be completely invisible to employees.

---

## M5.1 Template and parser

- [ ] Doctype `Payslip Batch`: `period_year`, `period_month`, `status` (`draft`/`scheduled`/`published`/`cancelled`), `source_file` (private), `uploaded_by`, `scheduled_at`, `published_at`, `row_count`, `note`
- [ ] Doctype `Payslip Item`: `batch`, `employee`, `net_salary`, `earnings` (JSON label->amount), `deductions` (JSON label->amount), `computed_net`, `has_variance`, `note`
- [ ] Downloadable template (xlsx/csv): `nip`, `nama` (optional, informational), `net_salary`, then any number of component columns (e.g. `gaji_pokok`, `tunjangan_makan`, `potongan_bpjs`, `pph21`)
- [ ] `juri_hr.payslip.upload_preview(file_url, year, month)`:
  - match rows to employees by `nip`
  - treat all amounts as **positive**; classify a column as a deduction when its name matches `payslip.deduction_keywords` (default: `potongan`, `deduction`, `pph`, `bpjs`, `pinjaman`, `kasbon`, `telat`, `absen`), otherwise as earnings
  - recompute `earnings - deductions` and compare with `net_salary`; mismatch -> **warning** (`has_variance`), still publishable, HRD's `net_salary` wins
  - errors: unknown NIP, duplicate NIP in file, missing/invalid `net_salary`, non-numeric component value
  - return rows with per-row errors/warnings and totals; store nothing
- [ ] `juri_hr.payslip.upload_commit(...)`: create the batch as `draft` plus items, store the source file privately; replacing an existing draft for the same period is allowed
- [ ] Python unit tests (risky spot #2): deduction keyword classification, variance warning, error rows, duplicate NIP, decimal/thousand-separator parsing

**AC**
- A typo in a component never blocks distribution, but is visible as a variance warning
- Unknown NIP rows must be fixed before commit

## M5.2 Status flow, scheduling, access control

- [ ] `juri_hr.payslip.schedule(batch, scheduled_at)`: `draft` -> `scheduled`, timestamp stored in `Asia/Jakarta`, must be in the future
- [ ] `juri_hr.payslip.publish(batch)`: `draft`/`scheduled` -> `published`, sets `published_at`, creates one in-app notification per employee in the batch; idempotent (re-publish does nothing)
- [ ] `juri_hr.payslip.cancel(batch)`: allowed while `draft`/`scheduled`
- [ ] Scheduler hook (`hooks.py`, every 15 minutes or cron): publish due `scheduled` batches, log outcome
- [ ] `juri_hr.payslip.my_list()` / `juri_hr.payslip.get(item)`: return only the requesting employee's items from `published` batches; HRD may read any
- [ ] Audit events: upload, schedule, reschedule, publish (manual and scheduled), cancel
- [ ] Python unit tests (risky spot #3): employee A cannot read employee B's payslip; `scheduled` batch invisible to employees; publish is idempotent

**AC**
- Nothing in a `draft` or `scheduled` batch is reachable by employees through any endpoint
- A due batch publishes automatically and notifies employees

## M5.3 HRD payslip UI

- [ ] Upload page: period selector (year/month), template download, file upload
- [ ] Preview screen: totals (rows, sum of net), per-row errors and variance warnings, employee name resolved from NIP; commit as draft
- [ ] Publish options: "Publish sekarang" or "Jadwalkan" with a date-time picker (default time from `payslip.default_publish_time`)
- [ ] Batch list: period, status, row count, scheduled/published time, countdown for scheduled batches; actions reschedule, cancel, replace draft, manual publish
- [ ] Manual publish is always available as a fallback when the scheduler is not running

**AC**
- HRD can go from file to scheduled distribution without leaving the flow
- The countdown and status make the current state unambiguous

## M5.4 Employee payslip UI

- [ ] "Slip Gaji" list: period, net amount, published date — published batches only
- [ ] Detail view: earnings section, deductions section, net total, period, employee identity, company header
- [ ] Print stylesheet producing a clean A4 page (browser print / save as PDF), hiding navigation

**AC**
- Only the employee's own payslips are listed
- Printed output is readable and contains no UI chrome

## M5.5 Publish notification

- [ ] In-app notification with the period, linking directly to the payslip detail
- [ ] Bell badge count for unread payslip notifications
- [ ] Explicitly **no** email delivery

**AC**
- The employee learns about a new payslip without any email being sent
