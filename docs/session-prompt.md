# Session Start Prompt

Copy the block below into a new session (or just tell the agent: "read `docs/session-prompt.md`").

Keep the **Current state** and **Next task** sections up to date whenever a task finishes — they are the only parts that go stale.

---

```
Working directory: D:\NEW JURIHR

Project: JuriHR — HR platform, Vue 3 SPA (PWA) + Frappe HR v16 + custom Frappe app `juri_hr`.
Replaces the legacy Laravel app PasPapan (kept at .\PasPapan as a read-only git worktree on
branch `legacy-reference` — visual reference only, never edit or commit from there).

Read these first, in order:
1. AGENTS.md                      — working rules (MUST follow)
2. docs/project_state.md          — current status, blockers, key decisions, next targets
3. docs/plans/juri-hr-mvp.md      — approved MVP plan
4. docs/tasks/M0-foundation.md    — current phase tasks

Current state: repo restructured and pushed (branch `main` -> origin callmevan1120/Juri_HR).
M0.1 done — `frontend/` scaffolded (Vite vue-ts, Tailwind 4, Pinia, Vue Router, ESLint/Prettier,
Vitest); `bun run build` and `bun run test` pass. Backend is skeleton only (frappe/juri_hr).

Next task — M0.2 (docs/tasks/M0-foundation.md):
- Frappe bench v16 + `bench get-app hrms` + create site + install frappe/hrms/juri_hr
- Enable scheduler and document heartbeat verification
- Module structure `Juri Hr` ready for new doctypes; hooks.py ready for scheduler events
- Create HRD/Admin + Employee test users; write docs/backend-setup.md (no secrets committed)

Reminders from AGENTS.md:
- Docs and code in English; UI strings and conversation with me in Indonesian
- Tick task checkboxes and update docs/project_state.md when a task is done (cap 500 lines)
- Light testing only (3 risky spots + one E2E) — no broad test suites
- Server-side validation for geofence / late / payslip ownership; policy values live in settings
- Commit only when I ask; confirm before destructive actions
```

---

## Phase quick reference

| Phase | Theme | Task file |
| --- | --- | --- |
| M0 | Foundation: repo, Frappe env, UI kit, API client, auth, layouts, PWA, CI | `docs/tasks/M0-foundation.md` |
| M1 | Employees, contracts, master data, directory | `docs/tasks/M1-employees.md` |
| M2 | Attendance core: locations, office hours, shift schedule, resolver, check-in/out | `docs/tasks/M2-attendance-core.md` |
| M3 | Review/reject, corrections, employee calendar, monthly recap | `docs/tasks/M3-review-corrections-calendar.md` |
| M4 | Izin/cuti self-service, leave balance, announcements | `docs/tasks/M4-self-service-announcements.md` |
| M5 | Payslip distributor with scheduled publishing | `docs/tasks/M5-payslip-distributor.md` |
| M6 | Activity log + HRD dashboard | `docs/tasks/M6-audit-dashboard.md` |
| M7 | Hardening, E2E, deployment, pilot | `docs/tasks/M7-hardening-golive.md` |
