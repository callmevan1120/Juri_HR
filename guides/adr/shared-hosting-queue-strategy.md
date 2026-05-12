# ADR: Shared Hosting Queue Strategy

PasPapan supports shared hosting by allowing scheduled short-lived queue workers.

Consequences:

- Queue heartbeat must prove jobs are processed, not only scheduled.
- Large import/export jobs need resumable status and operator runbooks.
- VPS deployments should use a supervised persistent worker.
