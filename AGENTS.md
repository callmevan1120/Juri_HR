# AGENTS.md — JuriHR

Instructions for AI agents working in this repository. Read this before making any change.

## What this project is

JuriHR is an HR platform being rebuilt as a **Vue 3 SPA (PWA)** on top of **Frappe HR v16** plus a minimal custom Frappe app `juri_hr` (Python) that lives in `frappe/juri_hr`.

It replaces a legacy Laravel/Livewire app (PasPapan), which is preserved on the branch `legacy-reference` and used only as a **visual reference** when porting screens.

Local layout: JuriHR is the repository root (`D:\NEW JURIHR`). The legacy app is checked out beside it at `D:\NEW JURIHR\PasPapan` as a git worktree on `legacy-reference` — read it for reference, never edit or build inside it, and never commit from it.

Current focus is the MVP: employee attendance (fixed office hours for office staff, shifts for outlet staff), attendance review and correction, izin/cuti self-service, and payslip distribution with scheduled publishing.

## Read these first, in this order

1. `docs/project_state.md` — current status, what is done, blockers, decisions, next targets
2. `docs/plans/juri-hr-mvp.md` — the approved MVP plan (goal, scope, data model, constraints, phases)
3. `docs/tasks/M<N>-*.md` — the task file for the phase you are working on

Reference material (full rewrite scope, legacy RBAC): `docs/reference/`.

## Mandatory working rules

### Documentation maintenance

- **Update `docs/project_state.md` after every finished task.** Adjust: current status, completed features, in progress, blockers, key decisions (if a new one was made), next targets.
- Keep `docs/project_state.md` at **500 lines maximum**. When approaching the limit, condense finished phases into one line each and move the detail to `docs/archive/project_state-<YYYY-MM-DD>.md`.
- Tick task checkboxes in `docs/tasks/*.md` as work completes. Do not silently skip a task — if it is dropped, record the reason in `docs/project_state.md`.
- When a new decision is made with the user, append it to the decisions table in `docs/project_state.md` with its reason.

### Language

- All `.md` documentation, code, comments, and commit messages: **English**
- All UI strings and communication with the user: **Indonesian**

### Scope discipline

- The out-of-scope list in `docs/plans/juri-hr-mvp.md` is binding. New feature ideas go to the post-MVP backlog in `docs/project_state.md`, not into the current phase.
- No unrequested abstractions, no speculative configuration, no scaffolding "for later". Prefer the smallest change that works.

### Security rules (non-negotiable)

- Validate on the **server**: geofence distance, late/early-leave math, work-hours resolution, payslip ownership, approval permissions, leave quota. The client never decides these.
- Private files (`is_private=1`): selfies, request attachments, payslip source files. Never expose them by public URL; fetch through authenticated requests.
- Payslips are readable by their owner and HRD only. Batches in `draft` or `scheduled` status must be unreachable by employees through any endpoint.
- Never filter sensitive lists only in the frontend.
- No payslip email blasting — in-app notifications only.
- Never commit secrets. Document required environment variables instead.

### Policy values belong in settings

Thresholds and rules must be readable from settings, never hardcoded: `attendance.office_hours`, `attendance.grace_period_minutes`, `attendance.early_leave_grace_minutes`, `attendance.require_selfie`, `attendance.default_radius_m`, `attendance.max_accuracy_m`, `attendance.shift_fallback`, `attendance.allow_checkin_on_off_day`, `leave.annual_quota`, `contract.expiring_window_days`, `payslip.deduction_keywords`, `payslip.default_publish_time`. Timezone is `Asia/Jakarta`.

### Testing (intentionally light)

Do not add broad test suites. Only:

1. Python unit tests for the work-hours resolver and geofence/late/early-leave math
2. Python unit tests for the payslip parser
3. Python unit tests for payslip access control
4. One Playwright end-to-end happy path (M7.2)
5. Vitest for API client/auth helpers and formatters

Everything else is verified manually: mobile Chrome (GPS, camera, permission denial), both roles, light and dark mode.

### Verification before claiming completion

- Run the relevant build/test command and report the actual result. Never claim success without evidence.
- Frontend: `cd frontend && bun run build` (plus `bun run test` when tests exist for the change)
- Backend: run the phase's Python unit tests where they exist
- If verification cannot be run (missing environment), say so explicitly and why.

### Destructive actions

- M0.1 deletes the legacy Laravel app. Before deleting anything: create `legacy-reference`, push it, verify it exists on the remote, and ask the user to confirm.
- Never force-push, reset --hard, or delete branches without explicit user approval.
- Only commit when the user asks. Stage specific files, never `git add .` blindly.

## Repository layout

```
frontend/                 Vue 3 SPA (created in M0.1)
frappe/juri_hr/           Frappe custom app (MVP backend)
brand-assets/             logo, icons, hero banner reused from the legacy app
docs/plans/               approved plans
docs/tasks/               per-phase task breakdowns
docs/reference/           legacy rewrite matrix, legacy RBAC source
docs/project_state.md     living progress summary (max 500 lines)
docs/backend-setup.md     Frappe environment setup (created in M0.2)
PasPapan/                 legacy Laravel app (git worktree, gitignored, read-only reference)
```

## Conventions

- Frontend: Vue 3 `<script setup>` + TypeScript, Pinia stores, Tailwind CSS 4 utility classes, components in `frontend/src/components/ui/`, one module folder per feature under `frontend/src/modules/`
- Reuse the design tokens ported from the legacy app (primary green, brand cyan) and the shared status color map — never duplicate status colors
- Backend: whitelisted methods namespaced `juri_hr.<module>.<action>`; doctypes in the `Juri Hr` module; return normalized error codes the frontend can map to Indonesian messages
- Every domain-changing action calls the audit helper `juri_hr.audit.record(...)`
