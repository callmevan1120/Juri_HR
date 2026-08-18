# M4 — Self-Service (Izin/Cuti) and Announcements

**Plan:** `docs/plans/juri-hr-mvp.md`
**Depends on:** M0, M1, M2 (status integration), M3 (calendar surfaces the results)
**Exit criteria:** employees submit izin/cuti/sakit requests that HRD approves or rejects with a comment, remaining leave quota is visible and enforced, HRD can still record absences directly and in bulk, and announcements reach every employee in-app.

## Context

MVP approval is deliberately **single level: employee -> HRD**. The per-division layering (SPV first, then HRD) and the full approval matrix belong to the post-MVP roadmap; do not build them here.

One doctype `Absence` covers both directions:
- `source=employee` -> created as `pending`, needs HRD review
- `source=hrd` -> created as `approved` immediately (HRD recording on behalf of someone)

An approved absence overrides attendance status for the covered dates.

---

## M4.1 Absence backend

- [ ] Doctype `Absence`: `employee`, `type` (`izin`/`cuti`/`sakit`), `date_from`, `date_to`, `reason`, `attachment` (private, optional), `source` (`employee`/`hrd`), `status` (`pending`/`approved`/`rejected`), `reviewed_by`, `review_comment`, `reviewed_at`
- [ ] `juri_hr.absence.submit(type, date_from, date_to, reason, attachment?)`: employee-only, requires reason, `date_to >= date_from`, rejects overlap with an existing `pending`/`approved` absence, warns (does not block) when a date already has attendance
- [ ] `juri_hr.absence.review(absence, action, comment?)`: HRD-only; reject requires a comment; approve validates quota again for `cuti`
- [ ] `juri_hr.absence.bulk_upsert(rows)`: HRD Excel/CSV upload (`nip`, `type`, `date_from`, `date_to`, `reason`) with preview + transactional commit, created as `approved` with `source=hrd`
- [ ] Approved absences take precedence over attendance status in `my_summary`, `admin_daily`, `admin_monthly`
- [ ] Notifications to the employee on approve/reject; audit events on submit, review, bulk commit

**AC**
- Overlapping requests are impossible
- An approved cuti immediately shows on the employee calendar for every covered date

## M4.2 Leave balance

- [ ] `juri_hr.leave.balance(employee?, year?)` returning `{ quota, used, remaining }` where `quota` comes from `leave.annual_quota` (default 12) and `used` counts approved `cuti` days in the year
- [ ] Block `cuti` submissions and approvals that would exceed the remaining quota, with a clear message stating quota, used, and requested days
- [ ] `izin` and `sakit` are not quota-limited in the MVP
- [ ] Weekly off days and calendar holidays inside a range do **not** consume quota

**AC**
- Balance matches approved absence data
- Quota enforcement happens on the server, not only in the form

## M4.3 Employee self-service UI

- [ ] Request form: type, date range picker, reason, optional attachment; shows the computed number of counted days before submit
- [ ] "Pengajuan Saya" list: type, dates, status badge, HRD comment, attachment link
- [ ] Remaining cuti quota card (quota, used, remaining) on the request page and the employee home

**AC**
- The employee sees how many days a request will consume before submitting
- Rejection comments are visible without contacting HRD

## M4.4 Admin absence UI

- [ ] Queue page: pending first with employee, type, dates, reason, attachment; approve / reject (comment required on reject)
- [ ] Direct entry form for HRD (auto-approved) for a single employee
- [ ] Bulk upload page: template download, upload, preview with per-row errors, commit
- [ ] Filters: division, type, status, date range

**AC**
- HRD can clear the queue from one screen
- Bulk upload never partially commits

## M4.5 Announcements

- [ ] Doctype `Announcement`: `title`, `body` (rich text or markdown-lite), `priority` (`normal`/`high`), `publish_at`, `expires_at`, `is_active`
- [ ] Admin CRUD with preview; publishing (immediately or at `publish_at`) creates in-app notifications
- [ ] Employee surfaces: banner for `high` priority active announcements (dismissible), bell list for all active ones, detail view
- [ ] Audit event on create, publish, and deactivate

**AC**
- A high-priority announcement is impossible to miss on the employee home
- Expired announcements disappear without manual cleanup

## Notes

- No email or WhatsApp/Telegram delivery in the MVP — in-app only
- Dismissal tracking is per user; keep it simple (a child table or a small doctype), no read receipts reporting in the MVP
