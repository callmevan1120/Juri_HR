# JuriHR — Project State

> Living summary. Update after every finished task. Hard cap: 500 lines — when close, condense finished phases into one line each and move detail into `docs/archive/project_state-<date>.md`.

**Last updated:** 2026-08-18
**Plan:** `docs/plans/juri-hr-mvp.md`
**Tasks:** `docs/tasks/M0-foundation.md` … `docs/tasks/M7-hardening-golive.md`

---

## 1. Current status

| Field | Value |
| --- | --- |
| Stage | M0 in progress — M0.1 done (repo restructured, frontend scaffolded) |
| Active phase | M0 — Foundation |
| Active task | M0.2 — Frappe HR v16 bench + site + install `juri_hr` + scheduler + `docs/backend-setup.md` |
| Branch | `main` tracking `origin/main` (`callmevan1120/Juri_HR`) |
| Working directory | `D:\NEW JURIHR` (JuriHR). The legacy Laravel app is checked out at `D:\NEW JURIHR\PasPapan` as a git worktree on branch `legacy-reference` (gitignored) |
| Frontend | scaffolded: `frontend/` Vite + Vue 3 `vue-ts`, Tailwind CSS 4 (`@tailwindcss/vite`), Pinia, Vue Router, ESLint + Prettier, Vitest; `bun run build` and `bun run test` pass |
| Backend | `frappe/juri_hr` skeleton only (hooks.py, `api/ping.py`, one legacy doctype `izin_request`), not installed on a site |
| Legacy app | removed from `main`; preserved on `legacy-reference` @ `6d7fa91` (pushed to `origin`, checked out locally as a worktree) |
| Deployment | none |

**What JuriHR is:** a Vue 3 SPA (PWA) on top of Frappe HR v16 plus a small custom app `juri_hr`. It replaces the legacy Laravel PasPapan UI. MVP focus: employee attendance (office fixed hours + outlet shifts), attendance review/correction, izin/cuti self-service, and payslip distribution with scheduled publishing.

## 2. Completed features

No product features yet.

Completed work:

- Full exploration of the legacy PasPapan codebase (stack, routes, models, flows, settings, enterprise gating, obfuscated modules identified)
- Full-rewrite roadmap agreed (6 phases, ~29 weeks) and then deliberately reduced to an MVP because of limited available time
- MVP scope, data model, backend method list, and phase breakdown approved by the user
- Plan and task files written (`docs/plans/`, `docs/tasks/`), `AGENTS.md` rewritten for JuriHR — commit `52805c5`
- **M0.1 (partial)** — `legacy-reference` created and pushed to `origin` (`callmevan1120/Juri_HR`) at `6d7fa91`; Laravel app removed from the working branch (1388 files); brand images moved to `brand-assets/`; RBAC permissions extracted to `docs/reference/rbac-source.md`; README rewritten; `.gitignore` replaced with Node/Frappe rules; Laravel CI workflows removed — commit `a279cc2`
- **M0.1 (done)** — `frontend/` scaffolded with Vite `vue-ts` (Vue 3.5, Vite 8, TS 6), Tailwind CSS 4 via `@tailwindcss/vite`, Pinia, Vue Router 5, ESLint 10 flat config + Prettier, Vitest 4 with jsdom; `@` alias to `src/`; `frontend/.env.example` added; verified `bun run build`, `bun run test` (1 passed), `bun run lint` clean

## 3. In progress

**M0.2** — Frappe environment: bench init (v16), `bench get-app hrms`, create site, install `frappe` + `hrms` + `juri_hr`, enable scheduler, write `docs/backend-setup.md`, create HRD + Employee test users.

## 4. Blockers and open issues

| # | Item | Impact | Notes |
| --- | --- | --- | --- |
| 1 | No Frappe HR environment yet | Blocks every backend task (M1+) | M0.2 sets up bench + site + `juri_hr`; frontend can proceed in fixture mode meanwhile |
| 2 | ~~M0.1 deletes the Laravel app~~ | resolved | `legacy-reference` @ `6d7fa91` pushed and verified on `origin`; deletion done in `a279cc2` |
| 3 | HTTPS required for camera + geolocation | Attendance cannot be tested on a phone over plain HTTP | Local testing via `localhost` (allowed by browsers); production needs a real certificate (M7.4) |
| 4 | Scheduler dependency for scheduled payslip publishing | A dead scheduler silently delays payslips | Heartbeat warning + manual publish fallback (M5.3, M7.3) |
| 5 | Legacy enterprise components are obfuscated (`eval(gzinflate(base64_decode(...)))`) | Their exact business logic cannot be read | Not an MVP blocker; MVP payroll is distribution-only. Post-MVP payroll must be rebuilt from requirements, not ported |
| 6 | Employee NIP quality | Payslip matching and imports depend on unique NIPs | Import validates uniqueness; HRD must clean data before the pilot |

## 5. Key decisions

| # | Decision | Reason |
| --- | --- | --- |
| 1 | Rewrite the frontend as a Vue 3 SPA; **no Laravel anywhere** | User requirement; backend moves to Frappe HR |
| 2 | Backend is Frappe HR v16 + a minimal custom app `juri_hr` built in this repo | User builds the MVP backend themselves rather than waiting for another team |
| 3 | Ship an MVP first, not the full roadmap | Limited time; two pains dominate (payslip emailing, attendance visibility) |
| 4 | Payslip distribution only — no payroll calculation in the MVP | HRD already produces payslip figures elsewhere; JuriHR distributes them |
| 5 | System-provided Excel template, tolerant parser (`nip` + `net_salary` required; other columns become components) | Deterministic parsing without building a column-mapping UI |
| 6 | Deductions written as positive numbers; keyword-matched columns treated as deductions | Matches how HRD already writes spreadsheets |
| 7 | Recomputed net vs `net_salary` mismatch = warning, still publishable (HRD figure wins) | Catches typos without blocking distribution |
| 8 | Payslip publishing can be scheduled per batch (date + time, `Asia/Jakarta`); scheduled batches are invisible to employees | Payslips should appear when the salary lands |
| 9 | No payslip email delivery — in-app notification only | Email blasts were getting the account banned |
| 10 | Office staff (`tetap`) use one company-wide office-hours setting; **no shifts, no monthly input** | Their pattern is fixed (Mon–Thu 07:30–16:30, Fri 07:30–17:00, Sat/Sun off) |
| 11 | Outlet staff (`shift`) use a monthly imported shift schedule with per-day override | Outlet rosters change; HRD needs bulk input |
| 12 | One resolver (`juri_hr.shift.resolve`) with order: date override -> holiday list -> mode-specific -> fallback | Single source of truth for late/early-leave, calendar, recap |
| 13 | HRD can reject an attendance record with a **mandatory comment**; a rejected day does not count as present until a correction is approved | Requested by the user; keeps discipline data honest |
| 14 | Attendance corrections are the employee's path to fix a rejected or missing day; HRD approves | Avoids HRD editing records silently |
| 15 | MVP approval is single level (employee -> HRD) | Per-division SPV layering and the full approval matrix are post-MVP |
| 16 | Employee attendance calendar is one page for both history and upcoming schedule | Avoids a separate schedule page; it is the employee's daily screen |
| 17 | Activity log (`Audit Event`) is in the MVP and must not be skipped | User requirement; salary and discipline records need traceability |
| 18 | Contract data uses native Employee fields; MVP ships data + expiring list + "my contract" only | Renewal approval, H-7/H-3/H-1 reminders, and contract PDF are post-MVP |
| 19 | Web/PWA first; Capacitor APK post-MVP | Faster to ship; browser camera + GPS are sufficient |
| 20 | Testing is deliberately light: unit tests at 3 risky spots (resolver/geofence math, payslip parser, payslip access control) + one E2E happy path; everything else manual | User requirement to avoid over-engineering |
| 21 | All policy thresholds live in settings, never in code | HRD policy changes must not require a release |
| 22 | Legacy Laravel app preserved on `legacy-reference` as a visual reference | Porting screens 1:1 needs the original |
| 23 | All `.md` documentation in English; user communication in Indonesian | AI readability |
| 24 | Toko/POS, Commercial/Invoice, Accounting, Assets+QR excluded permanently | Out of the HR product scope |
| 25 | Future-ready but not executed: payslip password, Telegram/WhatsApp channels, operational health page, announcement targeting | Contract hooks kept; no implementation |
| 26 | Project repository moved to `callmevan1120/Juri_HR`; old `RiprLutuk/PasPapan` kept as remote `paspapan-upstream` | No write access to the original repo |
| 27 | The existing `callmevan1120/JuriHR` repo (Next.js + Prisma attempt) is not used for this MVP | Different stack; the approved plan is Vue 3 SPA + Frappe HR |
| 28 | Brand images kept in `brand-assets/` rather than deleted with `public/` | Reused by the new SPA (icons, logo, hero banner) |
| 29 | `AGENTS.md` is tracked in git (it was ignored in the legacy `.gitignore`) | Agent rules must travel with the repo |
| 30 | Local layout mirrors the remote split: JuriHR lives in `D:\NEW JURIHR`, the legacy Laravel app in `D:\NEW JURIHR\PasPapan` as a **git worktree** on `legacy-reference` | Visual reference stays one folder away without polluting the JuriHR tree or duplicating git history; `/PasPapan/` is gitignored |

## 6. Next targets

**Immediate (M0 — Foundation)**

1. **M0.2** Frappe HR v16 bench + site + install `juri_hr`, enable scheduler, write `docs/backend-setup.md`
2. **M0.3** Minimal UI kit + design tokens + status badge color map + `/__dev/ui`
3. **M0.4** API client (resource/method/files/fixtures) + auth store, Vitest for client behaviour
4. **M0.5** Router guards, `AdminLayout` / `UserLayout` / `GuestLayout`, theme toggle, PWA shell
5. **M0.6** CI workflow (lint, typecheck, unit tests, build); delete legacy Laravel workflows

**Then**

- **M1** employees + contracts + master data + directory
- **M2** attendance core (locations, office hours, shift schedule, resolver, check-in/out, daily monitor)
- **M3** review/reject with comment, corrections, employee calendar, monthly recap
- **M4** izin/cuti self-service + leave balance + announcements
- **M5** payslip distributor with scheduling
- **M6** activity log + HRD dashboard (the audit helper should land as early as possible)
- **M7** hardening, E2E, deployment, pilot

**Definition of phase done:** every task AC verified, the light tests for that phase passing, this file updated (status, completed features, decisions, blockers).

## 7. Post-MVP backlog (agreed, not scheduled)

Approval matrix with per-division layering (SPV -> HRD / direct HRD) · shift group & rotation per outlet (multi-shift, weekly pattern, PL->HRD approval, rest rules) · overtime planning/actual with SPV-only submission and anomaly detection · business trips with GPS verification and map monitoring · real payroll (omset/BEP incentive rules, PL/PIC team payslips, payslip password) · contract renewal approval + H-7/H-3/H-1 reminders + contract PDF · reimbursement and kasbon · collaboration/chat · appraisal/KPI · wider reports and analytics · import/export and backup UI · Capacitor APK · Telegram/WhatsApp notification channels.

Reference for the full rewrite mapping: `docs/reference/REWRITE-MATRIX.md`, `docs/reference/rbac-source.md`.
