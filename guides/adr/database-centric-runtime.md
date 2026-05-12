# ADR: Database-Centric Runtime

PasPapan treats the relational database as the operational source of truth for HR, attendance, payroll, approvals, and audit state.

Consequences:

- Critical workflow state must be persisted, not only cached.
- Cache may accelerate views but must be recoverable from database rows.
- High-volume features need indexes and query tests before scale claims.
