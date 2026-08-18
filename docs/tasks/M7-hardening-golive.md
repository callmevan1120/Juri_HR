# M7 — Hardening and Go-Live

**Plan:** `docs/plans/juri-hr-mvp.md`
**Depends on:** M0–M6
**Exit criteria:** JuriHR runs in production over HTTPS with a pilot group, the one end-to-end path is automated and passing, backups are verified, and HRD plus employees have a short usage guide.

## Context

The MVP touches salary data, GPS positions, and selfies. Before any pilot, access control and private file handling must be verified deliberately rather than assumed. Deployment needs HTTPS because the browser blocks camera and geolocation otherwise.

---

## M7.1 Security pass

- [ ] Verify every private file path: selfies, request attachments, payslip source files — no public URL access, only authenticated fetches
- [ ] Re-check payslip access control: employee A vs employee B, employee vs HRD, draft/scheduled invisibility (extend the M5.2 tests if any gap appears)
- [ ] Confirm server-side authority for geofence distance, late/early-leave, work-hours resolution, quota enforcement, and approval permissions; remove any place where the client decides
- [ ] Rate limits on login and check-in endpoints
- [ ] Audit that no endpoint returns another employee's data by only filtering in the frontend
- [ ] Confirm secrets are not committed (`.env`, API keys, credentials); document required environment variables instead

**AC**
- Cross-account access attempts fail on the server for every private resource
- No client-side-only filtering remains for sensitive lists

## M7.2 End-to-end happy path

- [ ] One Playwright spec covering: HRD imports employees -> sets office hours -> imports an outlet shift schedule -> employee checks in late and checks out early -> HRD rejects one attendance with a comment -> employee submits a correction -> HRD approves it -> employee requests cuti -> HRD approves -> HRD uploads and schedules a payslip batch -> batch auto-publishes -> employee sees the payslip and the calendar -> activity log shows the trail
- [ ] Runs against a seeded test site (or fixture mode where a real backend is unavailable) and is wired into CI

**AC**
- The spec passes reliably and covers both roles
- A regression in any core flow fails CI

## M7.3 Operational readiness

- [ ] Scheduler heartbeat check surfaced in the admin UI, with a visible warning when the scheduler is stale plus the manual publish fallback
- [ ] Frappe scheduled backup enabled; perform one restore test and document it in `docs/backend-setup.md`
- [ ] Error logging reviewed: failed check-ins, failed scheduled publishes, import failures are all traceable

**AC**
- If the scheduler dies, HRD is warned and can still publish manually
- A restore has actually been performed once, not just configured

## M7.4 Deployment

- [ ] Frappe HR + `juri_hr` deployed on a VPS with HTTPS (valid certificate)
- [ ] Frontend build served on the same domain (preferred, avoids CORS) or on Vercel with CORS and cookie/token settings configured
- [ ] Environment documentation: `VITE_FRAPPE_BASE_URL`, site config, backup schedule, timezone `Asia/Jakarta`
- [ ] Smoke check on a real phone: install PWA, check in, view calendar, open a payslip

**AC**
- Camera and geolocation work on a real device over HTTPS
- A fresh deployment can be reproduced from the documentation

## M7.5 Pilot rollout

- [ ] Pick one office division (`tetap`) and one outlet (`shift`) for the pilot
- [ ] Run one attendance week and one payroll period fully inside JuriHR: no payslip emails, attendance recorded daily
- [ ] Short Indonesian usage guide (`docs/guides/`): HRD (import, schedule, review, publish) and employee (check-in, calendar, request, payslip)
- [ ] Collect issues into a follow-up list; decide what blocks wider rollout versus what waits for the post-MVP roadmap

**AC**
- The pilot period completes with payslips distributed only through JuriHR
- Issues are recorded with a clear blocker/non-blocker decision
