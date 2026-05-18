# Roadmap 10/10 Coverage Notes

Dokumen ini mencatat area yang sudah punya implementasi/foundation dan area yang sengaja tidak dipaksakan sebagai rewrite besar.

Fase berikutnya adalah feature maturity, bukan ekspansi modul. Perubahan baru harus memperkuat logic, security, data integrity, UX, test coverage, atau operational readiness dari fitur yang sudah ada. Prinsip kerja lengkap ada di `guides/feature-maturity.md`.

## Covered

- Manager Inbox v2 foundation: pending summary, overdue summary, workflow badges, pending/overdue filter, quick approve/reject existing workflow, and managed-user/RBAC scope.
- Policy acknowledgement: high-priority announcements support mandatory acknowledgement and admin acknowledgement tracking.
- HR Compliance Reminder: probation due, contract due, incomplete employee profile, overdue HR tasks, and auto-disable due accounts are exposed through Operational Health.
- Operational Health v2: database latency, queue heartbeat, queue backlog, scheduler heartbeat, backup checksum, disk usage, import/export workload, runtime version, PHP/database version, table size summary, and license/feature locks.
- Attachment storage: production defaults to private local attachment lookup; public is documented as an explicit legacy fallback only.
- RBAC audit: `php artisan rbac:audit` reports route/menu/role/policy coverage.
- Role preview: Roles & Permissions shows a human-readable module/action preview for each role.
- Release hygiene: public release checklist, coverage baseline workflow, and release preflight workflow.
- Security scans: CodeQL PHP/JS, Semgrep, gitleaks, and TruffleHog are present in CI.
- Generic attendance integration: inbound HMAC API for Solution/SBG-style gateways with employee code mapping and idempotency.
- Offline attendance API scope: offline sync uses the dedicated `device:offline-attendance` Sanctum ability instead of sharing barcode scan scope.

## Proof Matrix

- Manager Inbox: `tests/Feature/ManagerInboxAuthorizationTest.php`
- Security matrix: `tests/Feature/SecurityMatrixTest.php`
- Multi-company isolation: `tests/Feature/SecurityIsolationMatrixTest.php`
- Operational Health: `tests/Feature/AdminRouteSplitAndHealthTest.php`
- Attendance integration API: `tests/Feature/AttendanceIntegrationApiTest.php`
- Offline attendance API scope: `tests/Feature/OfflineAttendanceSyncTest.php`
- Public release checklist: `RELEASE_CHECKLIST.md`
- Attendance integration guide: `guides/attendance-integration.md`

## Deferred For Explicit Product Pass

- Full superadmin impersonation needs a dedicated security design because it must block approval, payroll, backup, and destructive actions while impersonating.
- Mobile redesign for every list page should be handled page-by-page with screenshots because it changes user-facing layout.
- Full staging promotion automation should be wired to the real staging host/secrets rather than guessed in source.
- APK release automation should run on a runner with Android SDK signing secrets and should never store signing credentials in the repository.
- PostgreSQL-first local/VPS mode is now the default; remaining database work is keeping MySQL/MariaDB as compatibility paths without blocking the VPS release baseline.
