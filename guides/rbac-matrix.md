# RBAC Matrix

The source of truth for permissions is `config/rbac.php`.

Common admin domains:

- Dashboard: `admin.dashboard.view`
- Attendance: attendance, correction, schedule, leave, shift swap, overtime permissions
- Finance/payroll: reimbursement, cash advance, payroll, payroll settings
- HR: employee documents, HR checklists, employees
- System: settings, import/export, reports, audit logs, maintenance, RBAC

Authorization flow:

1. Route middleware checks authentication and admin access.
2. Route `can:*` middleware calls a gate or policy.
3. Policy/gate checks role permissions via `User::allowsAdminPermission`.
4. Feature lock middleware checks enterprise license state for locked modules.
5. Component/action methods repeat authorization for mutating operations.

When adding a new admin page, define the route, permission key in `config/rbac.php` when needed, gate/policy coverage, menu visibility, and route authorization tests.
