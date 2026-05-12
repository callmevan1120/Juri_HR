# ADR: Multi-Company Scope Strategy

Multi-company support starts as a backend isolation guard before UI exposure.

Consequences:

- `company_id` on users gates sensitive resources through policies and queries.
- Legacy single-company rows with null company remain backward-compatible.
- UI exposure requires broader tests for settings, audit, reports, and exports.
