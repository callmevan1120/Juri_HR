# JuriHR MVP — Execution Plan

> **For agentic workers:** implement task-by-task from `docs/tasks/M*.md`. Update `docs/project_state.md` after every finished task. Testing is intentionally light (see Testing). Communicate with the user in Indonesian; keep all docs in English.

**Status:** approved, not started
**Last updated:** 2026-08-18

---

## 1. Goal

1. HRD stops emailing payslips one by one (email blasts keep getting banned). JuriHR becomes the distributor, and publishing can be **scheduled** so payslips appear together with the salary transfer.
2. Employees see and manage their own attendance — present / late / izin / cuti — on a monthly calendar. Office staff use fixed company work hours; outlet staff use shifts.
3. Every important action is auditable.

Attendance is the core feature; payslip distribution is the second pillar.

## 2. Product & Naming

- Product name: **JuriHR** (page titles, PWA name, docs)
- Frappe custom app id: `juri_hr` (already scaffolded at `frappe/juri_hr`)
- Legacy Laravel app (PasPapan) is preserved on branch `legacy-reference` as a visual reference

## 3. Architecture

```
frontend/ (Vue 3 SPA, PWA, mobile-first)
    |
    |  REST: /api/resource/*, /api/method/juri_hr.*
    v
Frappe HR v16  +  custom app juri_hr (Python, this repo)
```

- Auth: `POST /api/method/login` -> `generate_keys` -> `Authorization: token key:secret` persisted in localStorage
- All security-relevant logic is **server-side**: geofence distance, late/early-leave math, work-hours resolution, payslip ownership, approval permissions
- Fixture mode: when `VITE_FRAPPE_BASE_URL` is empty (or `VITE_USE_FIXTURES=true`), the API client serves JSON fixtures so frontend work can run ahead of backend endpoints
- Scheduled payslip publishing uses the Frappe scheduler

## 4. Tech Stack

| Layer | Choice |
| --- | --- |
| Frontend | Vue 3 (`<script setup>`), TypeScript, Vite, Tailwind CSS 4, Pinia, Vue Router |
| Backend | Frappe HR v16 + custom app `juri_hr` (Python) |
| Device | Browser geolocation + camera (PWA). No Capacitor in MVP |
| Payslip output | Print stylesheet (browser print / save as PDF) |
| Tests | Python unit tests (risky spots), Vitest (client helpers), one Playwright E2E |

## 5. Scope

### In scope

- Employee import from Excel (contract fields + attendance mode), employee directory
- Company standard office hours (settings), outlet shift schedule import + per-day override
- Attendance locations (geofence), check-in/out with GPS + selfie
- Late detection and early-leave detection
- HRD attendance review: reject with mandatory comment
- Attendance correction request -> HRD approve/reject
- Employee izin/cuti/sakit request -> HRD approve/reject, leave balance
- HRD direct absence entry + bulk upload
- Announcements (banner + bell)
- Employee attendance calendar (status history + upcoming schedule + per-day detail)
- Admin daily monitor, monthly recap, CSV exports
- Payslip distributor: system template -> upload -> preview -> publish now or scheduled -> employee view + print
- Contract data (native Employee fields) + "expiring soon" list + "my contract"
- Activity log (admin view + employee "my activity")
- HRD dashboard
- In-app notifications, roles HRD/Admin + Employee, PWA installable

### Out of scope (post-MVP)

Payroll calculation, omset/BEP incentive rules, approval matrix and multi-level layering (SPV -> HRD), overtime planning/actual + anomaly detection, business trips, shift group & rotation per outlet, face recognition, dynamic QR, offline attendance queue, contract renewal approval + H-7/H-3/H-1 reminders + contract PDF, PL/PIC team payslips, payslip password, reimbursement/kasbon, collaboration/chat, appraisal/KPI, wider reports, APK/Capacitor, Telegram/WhatsApp channels, backup UI.

Excluded permanently from the rewrite: Toko/POS, Commercial/Invoice, Accounting, Assets + QR tagging.

## 6. Global Constraints

- Docs in English; UI and user communication in Indonesian
- Server-side validation for geofence distance, late/early-leave, work-hours resolution, payslip ownership, approval permissions
- Private files (`is_private=1`): selfies, request attachments, payslip source file. Payslips readable by owner + HRD only
- No payslip email blasting — in-app notification only
- Policy values live in settings, never in code:
  - `attendance.office_hours` (7-day map of start/end or off)
  - `attendance.grace_period_minutes`, `attendance.early_leave_grace_minutes`
  - `attendance.require_selfie`, `attendance.default_radius_m`, `attendance.max_accuracy_m`
  - `attendance.shift_fallback`, `attendance.allow_checkin_on_off_day`
  - `leave.annual_quota`, `contract.expiring_window_days`
  - `payslip.deduction_keywords`, `payslip.default_publish_time`
  - Timezone `Asia/Jakarta`
- Check-in requires connectivity (offline queue is post-MVP) — show explicit retry messaging
- HTTPS mandatory (camera + geolocation)

## 7. Data Model

| Doctype | Notes |
| --- | --- |
| native `Employee` + custom fields | contract via native `employment_type`, `date_of_joining`, `contract_end_date`; new `custom_attendance_mode` (`tetap` \| `shift`, default `tetap`) |
| native `Shift Type` | shift definitions (start/end, overnight) — `shift` mode staff only |
| native `Holiday List` | calendar holidays -> status `holiday` |
| `Shift Schedule` | employee, date, shift (Link Shift Type), is_off — outlet roster **and** date override for anyone |
| `Attendance Location` | label, latitude, longitude, radius_m, is_active |
| native `Employee Checkin` + custom fields | log_type IN/OUT, time, `custom_location`, `custom_latitude`, `custom_longitude`, `custom_accuracy_m`, `custom_distance_m`, `custom_selfie` (private), `custom_shift`, `custom_is_late`, `custom_late_minutes`, `custom_early_leave_minutes`, `custom_review_status` (`ok` \| `rejected`), `custom_review_comment`, `custom_reviewed_by`, `custom_reviewed_at` |
| `Absence` | employee, type (`izin` \| `cuti` \| `sakit`), date_from, date_to, reason, attachment, source (`hrd` \| `employee`), status (`pending` \| `approved` \| `rejected`), reviewed_by, review_comment |
| `Attendance Correction` | employee, date, requested_time_in, requested_time_out, reason, attachment, status (`pending` \| `approved` \| `rejected`), reviewed_by, review_comment |
| `Announcement` | title, body, priority (`normal` \| `high`), publish_at, expires_at, is_active |
| `Payslip Batch` | period (year+month), status (`draft` \| `scheduled` \| `published` \| `cancelled`), source_file (private), uploaded_by, scheduled_at, published_at, row_count |
| `Payslip Item` | batch, employee, net_salary, earnings (JSON label->amount), deductions (JSON label->amount), computed_net, has_variance |
| `Audit Event` | timestamp, user, module, action, subject (doctype + name), summary, meta (JSON) |

### Work-hours resolution

`juri_hr.shift.resolve(employee, date)` evaluates in order:

1. Date override in `Shift Schedule`
2. `Holiday List` -> `holiday`
3. `tetap`: company office hours for that weekday (empty = `off`) · `shift`: that date's schedule row (`OFF` = `off`)
4. `shift` with no schedule: fallback per `attendance.shift_fallback` (nearest shift start to check-in time) or "no shift"

### Daily status values

`present` \| `late` \| `rejected` \| `izin` \| `cuti` \| `sakit` \| `absent` \| `off` \| `holiday`, plus an `early_leave` flag.

Rejected days do **not** count as present until a correction is approved.

### Backend methods

```
juri_hr.employees.import_preview / commit
juri_hr.shift.schedule_import_preview / commit
juri_hr.shift.resolve
juri_hr.attendance.checkin / checkout / review
juri_hr.attendance.my_summary(month)
juri_hr.attendance.admin_daily / admin_monthly
juri_hr.correction.submit / review
juri_hr.absence.submit / review / bulk_upsert
juri_hr.leave.balance
juri_hr.payslip.upload_preview / schedule / publish / cancel / my_list / get
juri_hr.contracts.expiring
juri_hr.audit.list
juri_hr.settings.get / update
```

## 8. Phases

Each phase ends with a working, reviewable deliverable. Detailed tasks live in `docs/tasks/`.

| Phase | Theme | Task file |
| --- | --- | --- |
| M0 | Foundation: repo restructure, Frappe env, UI kit, API client, auth, layouts, PWA, CI | `docs/tasks/M0-foundation.md` |
| M1 | Employees, contracts, master data, directory | `docs/tasks/M1-employees.md` |
| M2 | Attendance core: locations, office hours, shift schedule, resolve, check-in/out, monitor | `docs/tasks/M2-attendance-core.md` |
| M3 | Review, corrections, employee calendar, monthly recap | `docs/tasks/M3-review-corrections-calendar.md` |
| M4 | Self-service izin/cuti, leave balance, announcements | `docs/tasks/M4-self-service-announcements.md` |
| M5 | Payslip distributor with scheduling | `docs/tasks/M5-payslip-distributor.md` |
| M6 | Activity log + HRD dashboard | `docs/tasks/M6-audit-dashboard.md` |
| M7 | Hardening, E2E, deployment, pilot | `docs/tasks/M7-hardening-golive.md` |

Dependency order is strictly M0 -> M1 -> M2 -> M3 -> M4 -> M5 -> M6 -> M7, except:

- M5 (payslip) only depends on M0 + M1 and may be pulled forward if payslip distribution becomes urgent
- M6.1 (audit helper) should ideally land early; the helper is small and other phases call it

## 9. Testing (intentionally light)

- Python unit tests at 3 risky spots only:
  1. Work-hours resolve + geofence/late/early-leave math (M2.4, M2.5)
  2. Payslip parser (M5.1)
  3. Payslip access control (M5.2)
- Vitest for API client/auth helpers and date/status formatters
- One Playwright E2E happy path (M7.2)
- Everything else manual: mobile Chrome (GPS, camera, permission denial), both roles, light/dark
- Phase exit = works + task ACs verified + `docs/project_state.md` updated

## 10. Risks

| Risk | Mitigation |
| --- | --- |
| Deleting Laravel is irreversible | Push `legacy-reference` first, verify remotely, then restructure `main`; ask user to confirm |
| Frappe scheduler down -> scheduled payslips never publish | Heartbeat check + manual publish fallback always available in UI (M7.3) |
| Browser GPS/camera blocked without HTTPS | HTTPS mandatory in deployment docs; check-in screen explains permission failures |
| Excel formats vary between HRD spreadsheets | System-provided template + tolerant parser (only `nip` + `net_salary` required, other columns become components) |
| Payslip privacy leak | Ownership enforced server-side + explicit cross-account test |
| Scope creep back toward the full roadmap | Out-of-scope list in this plan is binding; new asks go to the post-MVP roadmap |

## 11. After MVP

Continue with the agreed full roadmap: approval matrix layering (per-division SPV -> HRD / direct HRD), shift group & rotation per outlet, overtime planning/actual + anomaly detection, business trips, real payroll (omset/BEP rules, PL/PIC payslips, payslip password), contract renewal approval + H-7/H-3/H-1 reminders + contract PDF, reimbursement/kasbon, collaboration, wider reports, APK.

MVP structures (`Shift Schedule`, `Absence`, `Payslip Batch`, office-hours settings, `Audit Event`) are designed to be superseded by those modules without data loss.

Reference material for the full rewrite scope: `docs/reference/REWRITE-MATRIX.md`, `docs/reference/rbac-source.md`.
