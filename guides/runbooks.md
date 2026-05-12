# Operational Runbooks

## Queue Down

1. Open Operational Health and check `queue_stale`.
2. Run `php artisan queue:failed`.
3. Restart the worker supervisor or scheduled worker.
4. Retry safe jobs with `php artisan queue:retry all`.
5. Recheck that queue heartbeat updates within 5 minutes.

## Scheduler Down

1. Open Operational Health and check `scheduler_stale`.
2. Confirm cron calls `php artisan schedule:run` every minute.
3. Check server logs for PHP path or permission errors.
4. Re-run `php artisan schedule:run` manually and verify heartbeat.

## Backup Failed

1. Check the latest failed backup run error.
2. Verify storage writable and disk free space.
3. Run a database backup manually from maintenance UI.
4. Verify checksum and record incident notes.

## Disk Full

1. Check `disk_low` alert and storage path.
2. Remove expired import/export runs.
3. Move old backups to external storage.
4. Re-run health check and backup.

## Failed Jobs Retry

1. List failed jobs.
2. Identify transient vs data errors.
3. Retry transient jobs.
4. Delete only jobs confirmed obsolete.

## Import/Export Stuck

1. Check queue heartbeat.
2. Inspect import/export run status.
3. Check failed jobs for the run id.
4. Retry or expire the run based on business owner approval.

## Attendance Risk Incident

1. Filter admin attendance by high risk.
2. Review risk factors, photo, GPS, and checkpoint.
3. Compare device/user history.
4. Decide correction, disciplinary follow-up, or false-positive tuning.
