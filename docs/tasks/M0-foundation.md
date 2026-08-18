# M0 â€” Foundation

**Plan:** `docs/plans/juri-hr-mvp.md`
**Depends on:** nothing
**Exit criteria:** repo contains no Laravel code on `main` (preserved on `legacy-reference`), a Frappe HR site runs with `juri_hr` installed, the Vue SPA logs into that site with role-based layouts, and CI is green.

## Context

The repository currently holds the legacy Laravel/Livewire app (PasPapan). The MVP replaces it with a Vue 3 SPA plus a minimal Frappe custom app. The legacy code is still valuable as a **visual reference** while porting screens, so it must be preserved on a branch before deletion.

`frappe/juri_hr` already exists as a skeleton (hooks.py, one doctype) and **stays on `main`** â€” it is the MVP backend.

Design tokens to reuse (from the legacy `resources/css/app.css`): primary green scale around `#6ab45b`, brand cyan scale around `#06b6d4`, card/rounded/dark-mode conventions.

---

## M0.1 Repo restructure and legacy branch

**DESTRUCTIVE â€” confirm with the user before deleting anything.**

- [x] Verify the working tree is clean except intended changes (`git status`)
- [x] Create branch `legacy-reference` from the current commit and push it to the remote; verify it exists remotely
- [x] On `main` (or the working branch), delete Laravel artifacts: `app/`, `resources/`, `routes/`, `config/`, `database/`, `bootstrap/`, `storage/`, `lang/`, `public/` (keep reusable brand images: hero banner, icons, logo), `tests/`, `api/`, `composer.json`, `composer.lock`, `artisan`, `server.php`, `phpunit.xml`, `phpstan.neon.dist`, `pint.json`, `vercel.json`, `.vercelignore`, `capacitor.config.ts`, `capacitor-www/`, `android/`, `ios/`, `stubs/`, `update.sh`
- [x] Keep: `frappe/juri_hr/`, `.github/` (rewrite workflows later), `.agents/`, `docs/`, `screenshots/`, `README.md`, `LICENSE`, `SECURITY.md`, `CODE_OF_CONDUCT.md`, `CONTRIBUTING.md`
- [x] Move `REWRITE-MATRIX.md` to `docs/reference/REWRITE-MATRIX.md`
- [x] Copy the legacy RBAC definition to `docs/reference/rbac-source.md` (permission strings and sections, for later phases)
- [x] Rewrite `README.md` for JuriHR: what it is, stack, how to run frontend + backend, link to `docs/plans/juri-hr-mvp.md`
- [x] Scaffold the frontend: `frontend/` via Vite (`vue-ts`), add Pinia, Vue Router, Tailwind CSS 4 (`@tailwindcss/vite`), ESLint + Prettier, Vitest
- [x] Add `frontend/.env.example` with `VITE_FRAPPE_BASE_URL=`, `VITE_USE_FIXTURES=true`, `VITE_APP_NAME=JuriHR`

**AC**
- `legacy-reference` exists remotely and still boots the old app (`composer install && php artisan serve`)
- No `.php` files remain outside `frappe/`
- `cd frontend && bun install && bun run build` succeeds

## M0.2 Frappe environment and app install

**BLOCKED** — this machine has neither WSL2 nor Docker; installing either needs administrator rights and a reboot. M0.3–M0.6 proceed in fixture mode meanwhile.

- [ ] Document and execute local setup in `docs/backend-setup.md`: bench init (Frappe v16), `bench get-app hrms`, create site, install `frappe`, `hrms`, then `juri_hr` from `frappe/juri_hr`
- [ ] Enable the scheduler (`bench --site <site> enable-scheduler`) and record how to verify the heartbeat
- [ ] Ensure `juri_hr` has a proper module structure for new doctypes (module `Juri Hr`), and `hooks.py` is ready for scheduler events
- [ ] Create an HRD/Admin test user and one Employee test user; document credentials handling (never commit secrets)

**AC**
- Site serves over HTTP locally; `bench --site <site> list-apps` shows `juri_hr`
- Scheduler heartbeat observable; instructions reproducible from a clean machine

## M0.3 Minimal UI kit

- [x] Port design tokens into `frontend/src/styles/app.css`: primary + brand color scales, dark variant, base focus rings, card and rounded conventions, WCAG touch target helper (min 2.75rem)
- [x] Build components in `frontend/src/components/ui/`: `AppButton`, `AppInput`, `AppSelect`, `AppTextarea`, `AppCheckbox`, `AppModal`, `AppBadge`, `DataTable` (sortable + paginated), `PageHeader`, `AppToast` (+ toast store), `AppEmptyState`, `AppSpinner`
- [x] Add a status badge helper mapping daily statuses to colors: `present` green, `late` amber, `rejected` red, `absent` red-muted, `izin` blue, `cuti` cyan, `sakit` violet, `off`/`holiday` gray
- [x] Add a dev-only route `/__dev/ui` rendering every component and status badge in light and dark mode

**AC**
- All components render correctly in light and dark
- Status colors come from one shared map (no duplicated color logic)

## M0.4 API client and auth store

- [x] `frontend/src/api/client.ts`: base URL from env, `Authorization: token key:secret` injection, JSON handling, normalized error shape (`{ status, code, message, details }`) parsing Frappe `exc_type` and `_server_messages`, timeout/abort
- [x] `frontend/src/api/resource.ts`: `listResource`, `getResource`, `createResource`, `updateResource`, `deleteResource` with `fields`, `filters`, `limit_start`, `limit_page_length`, `order_by`
- [x] `frontend/src/api/method.ts`: `callMethod(name, params)`
- [x] `frontend/src/api/files.ts`: `uploadPrivateFile(file, { doctype, docname })` via `/api/method/upload_file` with `is_private=1`, and `fetchPrivateBlob(fileUrl)` for authenticated downloads/previews
- [x] `frontend/src/api/fixtures.ts`: interceptor that resolves requests from `frontend/src/api/fixtures/*.json` when `VITE_USE_FIXTURES=true` or the base URL is empty; supports basic filter + pagination simulation
- [x] `frontend/src/stores/auth.ts`: `login(email, password)` -> `POST /api/method/login` then `generate_keys` (or an equivalent `juri_hr` helper) -> persist token; `logout()`; `loadSession()`; expose `user`, `roles`, `isHrd`
- [x] Vitest: token injection, error normalization, fixture fallback, 401 clearing the session

**AC**
- Real login against the local Frappe site works and persists across reload — **pending M0.2** (no Frappe environment on this machine yet)
- With `VITE_USE_FIXTURES=true` the app runs without any backend — verified by `src/api/client.spec.ts`

## M0.5 Roles, layouts, PWA

- [x] Router with route meta `{ requiresAuth, roles, layout }` and guards: unauthenticated -> `/login`, wrong role -> `/403`
- [x] `AdminLayout`: sidebar (Dashboard, Karyawan, Absensi, Jadwal, Pengajuan, Slip Gaji, Pengumuman, Aktivitas, Pengaturan), topbar with notification bell and user menu
- [x] `UserLayout`: mobile-first topbar + bottom navigation (Home, Kalender, Pengajuan, Slip Gaji, Profil), safe-area padding
- [x] `GuestLayout` for login
- [x] Dark/light theme toggle persisted in localStorage
- [x] PWA: `manifest.json` (name JuriHR, standalone, icons), service worker precaching the app shell and an offline page, bypassing `/api/` requests; online/offline indicator
- [x] Placeholder pages for every nav entry so navigation is testable

**AC**
- HRD lands on the admin dashboard, employee on their home — verified in fixture mode (`hrd@…` -> `/admin`, other emails -> `/`) and covered by `src/router/guards.spec.ts`
- App installs on Android Chrome and shows the offline page when offline — manifest, service worker and `offline.html` all served by `bun run preview`; installing on a real device is a manual check

**Manual test in fixture mode:** set `VITE_USE_FIXTURES=true`, then log in with any password. An email starting with `hrd` (e.g. `hrd@example.com`) grants the HRD roles; any other email (e.g. `budi@example.com`) logs in as a plain employee.

## M0.6 CI

- [x] GitHub Actions workflow: install (bun), lint, typecheck, Vitest, build for `frontend/`
- [x] Separate job (or step) running Python unit tests for `frappe/juri_hr` where they exist (skip gracefully while none exist)
- [x] Remove or rewrite legacy workflows that reference Laravel/FTP deploys

**AC**
- CI green on push; no workflow references deleted Laravel paths — **verified: the run on `6dc34d2` passed both the `frontend` and `backend` jobs**

**Notes**
- `.github/workflows/ci.yml` has two jobs: `frontend` (bun 1.3.14, `--frozen-lockfile`, lint, Prettier check, typecheck, Vitest, build) and `backend` (byte-compile `frappe/juri_hr`, then run pytest only if a `test_*.py` exists)
- No Laravel workflow existed on `main` to delete — those were already removed in M0.1; the leftover `php artisan migrate` line in `.github/PULL_REQUEST_TEMPLATE.md` was replaced with the JuriHR verification checklist. The `legacy-reference` branch still carries the old `Vercel Production` / `Deploy` workflows and they fail there on every push; that branch is a read-only reference, so they are left alone (disable them in the repository Actions settings if the red runs are distracting)
- `bun run lint` no longer auto-fixes (CI must fail on violations); use `bun run lint:fix` locally
