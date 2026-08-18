# M2 — Attendance Core

**Plan:** `docs/plans/juri-hr-mvp.md`
**Depends on:** M0, M1
**Exit criteria:** employees check in and out from their phone inside a geofence with a selfie; late and early-leave are computed from the correct work hours for both `tetap` and `shift` staff; HRD can see today's attendance.

## Context

Two populations, one resolver:

- **`tetap` (office)** — no shifts at all. Company standard office hours apply, e.g. Mon–Thu 07:30–16:30, Fri 07:30–17:00, Sat/Sun off. HRD configures this **once** in settings; there is no monthly input.
- **`shift` (outlet + outlet-operations division)** — monthly shift schedule imported from Excel, editable per day.

Weekly-off variety is covered: two days off (empty weekdays in office hours), one day off (single `OFF` per week in the schedule), or no fixed day off (all days scheduled; days off given as `OFF` on specific dates).

All distance and time math happens on the server. The client sends coordinates and accuracy; it never decides whether a check-in is valid or late.

---

## M2.1 Attendance locations

- [ ] Doctype `Attendance Location`: `label`, `latitude`, `longitude`, `radius_m` (default from `attendance.default_radius_m`), `is_active`
- [ ] Admin CRUD page with a map preview (Leaflet) or at minimum coordinate inputs plus a "use my current position" helper
- [ ] Validation: latitude/longitude ranges, `radius_m` between 20 and 5000

**AC**
- Only active locations are considered by check-in
- Coordinates can be captured from the browser to avoid manual typing errors

## M2.2 Company office hours settings

- [ ] Settings storage for `attendance.office_hours`: map of Monday..Sunday -> `{ start, end }` or `off`
- [ ] Settings page section "Jam Kerja Standar": seven rows (start, end, off toggle), plus `attendance.grace_period_minutes` and `attendance.early_leave_grace_minutes`
- [ ] Validation: when not off, both times required; `end` after `start` unless explicitly marked overnight (office hours do not support overnight in MVP)
- [ ] Every change writes an audit event (M6.1)

**AC**
- Configuring Mon–Thu 07:30–16:30, Fri 07:30–17:00, Sat/Sun off produces the correct resolve results
- Changing grace period changes late classification without any code change

## M2.3 Shift schedule (outlet staff)

- [ ] Doctype `Shift Schedule`: `employee`, `date`, `shift` (Link `Shift Type`), `is_off`; unique per employee+date
- [ ] `juri_hr.shift.schedule_import_preview(file_url, year, month)`: Excel where rows are employees (`nip`) and columns are days 1..31, cell value = shift code/name or `OFF` or empty (empty = no entry)
- [ ] Validation: unknown NIP, employee whose `custom_attendance_mode` is `tetap` (reject with a clear message), unknown shift code, duplicate rows, date outside the month
- [ ] `juri_hr.shift.schedule_import_commit(rows)`: transactional upsert; audit event
- [ ] `juri_hr.shift.set_day(employee, date, shift|off|clear)`: single-day edit, also usable as a **date override for `tetap` staff** (e.g. working on a holiday); audit event

**AC**
- Importing a month for outlet staff produces one row per employee per scheduled day
- Rows targeting `tetap` employees are rejected without partially committing

## M2.4 Work-hours resolver

- [ ] `juri_hr.shift.resolve(employee, date)` returning `{ source, status_hint, shift_name, start_time, end_time, is_off, is_holiday }` where `source` is one of `override`, `holiday`, `office_hours`, `schedule`, `fallback`, `none`
- [ ] Order: date override -> holiday list -> mode-specific (`office_hours` for `tetap`, `schedule` for `shift`) -> fallback for `shift` without a schedule per `attendance.shift_fallback` (nearest shift start to the given time) -> `none`
- [ ] Python unit tests (risky spot #1): fixed weekday, Friday different hours, Saturday off, calendar holiday, scheduled shift, `OFF` cell, unscheduled fallback, overnight shift, date override for a `tetap` employee

**AC**
- Every branch of the resolution order has a passing test
- Resolver is the single source of truth used by check-in, calendar, and recap

## M2.5 Check-in / check-out backend

- [ ] `juri_hr.attendance.checkin(latitude, longitude, accuracy_m, selfie_file_url?)`:
  - resolve nearest **active** `Attendance Location`, compute distance server-side (haversine)
  - reject when distance > `radius_m`, or `accuracy_m` > `attendance.max_accuracy_m`
  - require a selfie when `attendance.require_selfie` is on; store it as a private file
  - resolve work hours (M2.4); reject when `off`/`holiday`/`none` unless `attendance.allow_checkin_on_off_day`
  - compute `custom_is_late` and `custom_late_minutes` from `start_time` + grace
  - reject duplicate check-in for the same working day
  - create `Employee Checkin` (log_type IN) with all custom fields; audit event
- [ ] `juri_hr.attendance.checkout(...)`: same geofence/selfie rules, requires an existing check-in, computes `custom_early_leave_minutes` from `end_time` − early-leave grace, handles overnight shifts (checkout after midnight belongs to the previous working day)
- [ ] Error codes returned to the client: `OUT_OF_RADIUS`, `GPS_ACCURACY_LOW`, `SELFIE_REQUIRED`, `NO_WORK_HOURS`, `ALREADY_CHECKED_IN`, `NOT_CHECKED_IN`, `DAY_OFF`
- [ ] Python unit tests (risky spot #1 continued): radius boundary (inside/outside), late boundary at grace edge, early-leave boundary, overnight checkout, duplicate check-in

**AC**
- No client input can bypass geofence, late, or day-off rules
- Selfies are stored private and never exposed by public URL

## M2.6 Check-in screen (employee)

- [ ] Today's line: "Jam kerja hari ini 07:30–16:30" (`tetap`), "Shift hari ini: Pagi 07:00–15:00" (`shift`), or "Hari ini libur"
- [ ] Live position with distance to the nearest location and a clear in-range / out-of-range indicator
- [ ] Selfie capture (front camera) with retake, shown before submit
- [ ] One primary action that switches between "Check-in" and "Check-out" based on today's state; disabled with an explanation when not possible
- [ ] Failure messaging mapped from backend error codes, plus permission-denied guidance and an offline retry hint
- [ ] After success: today's card shows check-in/check-out times and late/early-leave badges

**AC**
- Works in mobile Chrome over HTTPS; every failure path shows a human-readable Indonesian message
- No silent failures when camera or geolocation permission is denied

## M2.7 Schedule administration UI

- [ ] Shift schedule import page: template download, upload, preview with per-row errors, commit
- [ ] Monthly roster grid for `shift` staff (employees x days) with per-day edit (assign shift, mark off, clear)
- [ ] Separate list of `tetap` staff showing the standard office hours in effect, with a per-date override action

**AC**
- A per-day correction immediately changes late/early-leave results for that date
- `tetap` staff never require schedule rows to be attendance-ready

## M2.8 Admin daily monitor

- [ ] `juri_hr.attendance.admin_daily(date, division?, mode?, status?)` returning one row per employee: mode, resolved hours/shift, check-in, check-out, late minutes, early-leave minutes, location, distance, selfie file url, review status
- [ ] Admin page: date picker, division/mode/status filters, table with selfie thumbnail (authenticated blob), CSV export
- [ ] Include employees with **no** attendance record for the day (status `absent`, or `off`/`holiday`/`izin`/`cuti` when applicable)

**AC**
- Selfie previews load only through authenticated requests
- The monitor shows who has not checked in, not just who has
