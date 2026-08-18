# JuriHR

HR platform built as a **Vue 3 SPA (PWA)** on top of **Frappe HR v16** plus a minimal custom Frappe app `juri_hr`.

Current stage: **MVP implementation, phase M0 (foundation)**. See `docs/project_state.md` for live status.

## What the MVP does

- **Attendance** — employees check in and out from their phone inside a GPS geofence with a selfie. Office staff (`tetap`) follow one company-wide office-hours setting; outlet staff (`shift`) follow an imported monthly shift schedule. Late and early-leave are computed on the server.
- **Attendance review and correction** — HRD can reject a record with a mandatory comment; the employee responds with a correction request that HRD approves.
- **Employee calendar** — one monthly view showing status history (present, late, rejected, izin, cuti, sakit, absent, off, holiday) and the upcoming schedule, with per-day detail.
- **Izin / cuti self-service** — employees submit requests, HRD approves, annual leave quota is enforced server-side.
- **Payslip distribution** — HRD uploads one Excel per period using the provided template, reviews a preview, then publishes immediately or **schedules** publication so payslips appear when the salary lands. Each employee sees only their own payslip and can print it. No payslip emails are sent.
- **Activity log** — every important action is recorded with actor, subject, and a readable summary.
- **Announcements**, **employee directory**, **contract expiry visibility**, and an **HRD dashboard**.

Out of scope for the MVP (and tracked in the post-MVP backlog): payroll calculation, multi-level approval matrix, overtime, business trips, shift rotation, reimbursement/kasbon, chat, appraisal, native APK.

## Repository layout

```
frontend/            Vue 3 SPA (created in M0.1)
frappe/juri_hr/      Frappe custom app — the MVP backend
docs/plans/          approved plan
docs/tasks/          per-phase task breakdown (M0 … M7)
docs/reference/      legacy rewrite matrix and RBAC reference
docs/project_state.md  live progress summary
brand-assets/        logo and imagery reused from the legacy app
AGENTS.md            working rules for AI agents
PasPapan/            legacy Laravel app (git worktree on legacy-reference, gitignored)
```

The legacy app is checked out next to this project as a git worktree, so the old screens stay one folder away for visual reference while sharing a single git history:

```bash
git worktree add PasPapan legacy-reference   # already done locally
git worktree remove PasPapan                 # when it is no longer needed
```

## Getting started

Backend (Frappe HR + `juri_hr`) — full instructions in `docs/backend-setup.md` (written in M0.2):

```bash
bench init juri-bench --frappe-branch version-16
cd juri-bench
bench get-app hrms
bench new-site juri.localhost
bench --site juri.localhost install-app hrms
bench get-app juri_hr /path/to/this/repo/frappe/juri_hr
bench --site juri.localhost install-app juri_hr
bench --site juri.localhost enable-scheduler
bench start
```

Frontend:

```bash
cd frontend
bun install
cp .env.example .env      # set VITE_FRAPPE_BASE_URL, or leave empty for fixture mode
bun run dev
```

## Notes

- Timezone is `Asia/Jakarta`. All policy thresholds (grace period, geofence radius, leave quota, payslip deduction keywords, …) live in settings, never in code.
- HTTPS is required in production: browsers block camera and geolocation otherwise.
- The previous Laravel/Livewire application (PasPapan) is preserved on the **`legacy-reference`** branch and is used only as a visual reference while porting screens.

## History

JuriHR replaces PasPapan, a Laravel 13 + Livewire HRIS. The rewrite moves the backend to Frappe HR and the frontend to a Vue 3 SPA. The full long-term roadmap is summarised in `docs/reference/REWRITE-MATRIX.md`; the currently approved scope is `docs/plans/juri-hr-mvp.md`.
