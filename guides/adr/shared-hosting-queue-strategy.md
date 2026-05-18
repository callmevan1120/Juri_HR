# ADR: Legacy Cron-Only Queue Strategy

PasPapan is now VPS-first for production. This historical compatibility ADR remains for legacy cron-only installs that cannot run supervised workers.

Consequences:

- Queue heartbeat must prove jobs are processed, not only scheduled.
- Large import/export jobs need resumable status and operator runbooks.
- VPS deployments must use a supervised persistent worker for full production behavior.
