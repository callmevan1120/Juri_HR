# Architecture

PasPapan is a Laravel 11 and Livewire 3 HR operations app. The main runtime boundary is:

- `routes/web/*`: route entrypoints grouped by user, admin, system, payroll, and secure file flows.
- `app/Livewire/*`: UI state, validation, authorization calls, and component rendering.
- `app/Support`, `app/Services`, `app/Actions`, `app/Queries`: business logic, orchestration, query composition, and task-specific actions.
- `app/Policies` and `app/Providers/AuthServiceProvider.php`: authorization decisions.
- `database/migrations` and `database/seeders`: schema and idempotent master data.

Admin routes are split by domain under `routes/web/admin/*`. URL paths and route names must remain stable because menus, policies, tests, and bookmarked admin URLs depend on them.

Livewire components should not own heavy business behavior. Prefer:

- `Actions` for one-off command flows.
- `Services` for reusable domain behavior.
- `Queries` for paginated/eager-loaded read models.
- `Data` objects only when request/response structures become large enough to justify them.

Shared-hosting compatibility is a design constraint: database queue/cache/session are the baseline, and features should not require Redis, Horizon, Reverb, or long-running workers to render core pages.
