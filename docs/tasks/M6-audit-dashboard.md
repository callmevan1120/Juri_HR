# M6 — Activity Log and HRD Dashboard

**Plan:** `docs/plans/juri-hr-mvp.md`
**Depends on:** M0 (helper can land early), consumed by M1–M5
**Exit criteria:** every important action is traceable to an actor with a subject and summary, HRD can search that history, employees can see their own activity, and HRD has one screen showing today's state and pending work.

## Context

The activity log is a hard requirement, not a nice-to-have: attendance rejections, corrections, absence approvals, and payslip publishing all change money- or discipline-related records, so who did what must be answerable later.

Frappe already records document versions and login activity. `Audit Event` sits on top of that as a **domain-level** log: readable summaries in business terms rather than raw field diffs.

Implementation note: build the helper first (M6.1) and call it from the phases as they are implemented. If M6.1 lands after a phase, that phase's task file carries a TODO listing its call sites — resolve those TODOs here.

---

## M6.1 Audit event backend

- [ ] Doctype `Audit Event`: `timestamp`, `user`, `module` (e.g. `employee`, `attendance`, `schedule`, `absence`, `correction`, `payslip`, `announcement`, `settings`, `auth`), `action` (e.g. `import_commit`, `checkin`, `review_reject`, `approve`, `publish`, `update`), `subject_doctype`, `subject_name`, `summary` (human-readable Indonesian sentence), `meta` (JSON)
- [ ] Helper `juri_hr.audit.record(module, action, subject=None, summary="", meta=None)` — never raises into the caller (log failures instead)
- [ ] Wire calls into: login (success and failure), employee import commit, employee edit, settings/office-hours change, shift schedule import, per-day schedule edit, check-in, check-out, attendance reject/clear, correction submit/approve/reject, absence submit/approve/reject/bulk commit, announcement create/publish/deactivate, payslip upload/schedule/reschedule/publish/cancel
- [ ] `juri_hr.audit.list(filters)`: user, module, action, date range, subject; paginated, HRD-only
- [ ] `juri_hr.audit.mine()`: current user's own events

**AC**
- Each action in the list above produces exactly one entry with actor, subject, and a readable summary
- A failure inside audit recording never breaks the business action

## M6.2 Admin activity log page

- [ ] Page "Aktivitas": filters (user, module, action, date range, free-text on summary), paginated table (time, user, module, action, summary)
- [ ] Detail drawer: full meta JSON, subject link opening the related record (attendance day, correction, absence, payslip batch)
- [ ] CSV export of the filtered result

**AC**
- An entry can be traced from the log to the record it changed
- Filters make a single employee's history retrievable in a few clicks

## M6.3 Employee activity page

- [ ] "Aktivitas Saya": own login events, check-in/out, submitted requests and their outcomes, payslip views
- [ ] Read-only, no other users' data

**AC**
- An employee can reconstruct their own history without HRD help

## M6.4 HRD dashboard

- [ ] Today card set: hadir, terlambat, pulang awal, ditolak, izin, cuti, belum absen (from `admin_daily` for the current date)
- [ ] Pending work: correction queue count, absence queue count — each linking to its queue
- [ ] Contracts expiring within the configured window (count + link)
- [ ] Latest payslip batch status (period, status, scheduled/published time, countdown)
- [ ] Recent activity: last 10 audit events

**AC**
- The numbers match the underlying pages they link to
- The dashboard is the natural landing page for HRD after login
