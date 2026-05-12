# Backup Restore Threat Model

## Assets

- Database dump.
- Application backup archive.
- Backup metadata and checksum.
- Restore confirmation and maintenance permission.

## Threats

- Restoring a tampered SQL file.
- Downloading backups without maintenance permission.
- Backup file exfiltration from public disk.
- Failed backup silently hiding operational risk.
- Low disk causing partial backups.

## Controls

- Database backups include an HMAC signature.
- Restore verifies signature and format before execution.
- Backup downloads/deletes require maintenance authorization.
- Health dashboard reports last failed backup, file presence, and checksum match.
- Runbook requires restore drill in a non-production environment before production restore.

## Drill

1. Create a fresh staging database.
2. Download the latest completed backup through the maintenance UI.
3. Verify checksum shown in Operational Health.
4. Run `php artisan maintenance:backup-restore-drill` to verify artifact presence and print the safe restore checklist.
5. Run smoke tests: login, admin dashboard, employee listing, attendance listing, payroll listing.
6. Record drill date, backup id, duration, and result in operations notes.
