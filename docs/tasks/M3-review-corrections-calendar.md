# M3 — Review, Corrections, Employee Calendar

**Plan:** `docs/plans/juri-hr-mvp.md`
**Depends on:** M0, M1, M2
**Exit criteria:** HRD can reject an attendance record with a mandatory comment, the employee can respond by submitting a correction that HRD approves, and every employee sees their month on a calendar with per-day detail.

## Context

Rejection and correction are one loop: HRD rejects a suspicious record (wrong location, wrong time, unclear selfie) with a comment; the day stops counting as present; the employee sees the comment, submits a correction with a reason and optional evidence; HRD approves and the day becomes valid again.

The employee calendar is the main screen employees will open daily. It serves both directions in time: past days show status, future days show the schedule.

---

## M3.1 Attendance review backend

- [ ] `juri_hr.attendance.review(checkin, action, comment)` where `action` is `reject` or `clear`
- [ ] Rejecting requires a non-empty comment; sets `custom_review_status=rejected`, `custom_review_comment`, `custom_reviewed_by`, `custom_reviewed_at`
- [ ] Rejected days are excluded from present/late counts and reported as status `rejected` everywhere (monitor, recap, calendar, dashboard)
- [ ] Permission: HRD/Admin roles only
- [ ] Notification to the employee containing the comment; audit event
- [ ] `clear` action reverts the record to `ok` (used when HRD changes their mind, and by an approved correction)

**AC**
- Recap and calendar counts change immediately after a rejection
- An employee cannot review their own attendance

## M3.2 Admin review UI

- [ ] Reject action on the daily monitor row and in the day detail drawer, with a required comment dialog
- [ ] Day detail drawer: resolved hours/shift, times, location + distance, selfie (authenticated), review status and comment history
- [ ] Filter for `rejected` records; badge count of rejected days in the current month

**AC**
- Rejecting is impossible without a comment
- HRD can see the evidence (selfie, distance, times) in the same place as the reject action

## M3.3 Attendance correction backend

- [ ] Doctype `Attendance Correction`: `employee`, `date`, `requested_time_in`, `requested_time_out`, `reason`, `attachment` (private), `status` (`pending`/`approved`/`rejected`), `reviewed_by`, `review_comment`, `reviewed_at`
- [ ] `juri_hr.correction.submit(date, time_in?, time_out?, reason, attachment?)`: employee-only; requires a reason; rejects duplicates for the same date while one is `pending`; allowed for a rejected day, a day with missing check-in/out, or a day with no record at all
- [ ] `juri_hr.correction.review(correction, action, comment?)`: HRD-only; on approve, apply the requested times to the existing `Employee Checkin` records (or create them when missing), recompute late/early-leave via the resolver, and clear any rejection; on reject, store the comment
- [ ] Notifications to the employee on both outcomes; audit events for submit, approve, reject

**AC**
- An approved correction makes the day count as present/late correctly (recomputed, not hardcoded)
- Only HRD can approve; employees can only submit for themselves

## M3.4 Employee correction UI

- [ ] Submit form: date (prefilled when opened from a specific day), requested check-in/check-out times, reason, optional attachment
- [ ] Entry points: the calendar day detail (M3.6), a rejected-day notification, and a standalone "Ajukan Koreksi" action
- [ ] "Koreksi Saya" list: date, requested times, status badge, HRD comment

**AC**
- The employee always knows why a day was rejected before submitting
- Status and HRD comment are visible without asking HRD

## M3.5 Admin correction queue

- [ ] Queue page: pending first, then history; filters by division and date range
- [ ] Row detail: current record vs requested times, reason, attachment preview, resolved work hours for context
- [ ] Approve / reject actions with optional (reject: required) comment

**AC**
- HRD can process a correction without leaving the queue
- Approvals recompute the day, verified against the monitor

## M3.6 Employee attendance calendar

- [ ] Extend `juri_hr.attendance.my_summary(month)` to return per-date: `status`, `early_leave` flag, resolved hours/shift, check-in/out times, location, distance, selfie url, review comment — and for **future dates** the scheduled hours/shift or off/holiday marker
- [ ] Monthly calendar page (employee): color + badge per date for `hadir`, `terlambat`, `pulang awal` (flag on top of hadir), `ditolak`, `izin`, `cuti`, `sakit`, `alpha`, `libur mingguan`, `libur kalender`
- [ ] Future dates show the schedule (hours or shift name) instead of a status
- [ ] Counters above the calendar: masuk, terlambat, pulang awal, izin, cuti, alpha; month navigation (previous/next, no future months beyond the schedule horizon)
- [ ] Tap a date -> detail sheet: work hours/shift, check-in/out times, location + distance, selfie, HRD comment when rejected, and an "Ajukan Koreksi" button wired to M3.4
- [ ] Mobile-first layout; usable one-handed; also renders on desktop

**AC**
- Counters reconcile exactly with the day cells
- Rejected days are not counted as present
- Off days, holidays, izin and cuti render with the correct color and label
- Future dates never show an attendance status

## M3.7 Admin monthly recap

- [ ] `juri_hr.attendance.admin_monthly(year, month, division?)`: per employee totals — masuk, terlambat, pulang awal, ditolak, izin, cuti, sakit, alpha, plus scheduled working days
- [ ] Admin page with division filter and CSV export
- [ ] Drill-down link from a row to that employee's day list for the month

**AC**
- Recap totals reconcile with the daily monitor for the same period
- CSV contains the same numbers as the screen
