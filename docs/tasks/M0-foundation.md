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

- [ ] Document and execute local setup in `docs/backend-setup.md`: bench init (Frappe v16), `bench get-app hrms`, create site, install `frappe`, `hrms`, then `juri_hr` from `frappe/juri_hr`
- [ ] Enable the scheduler (`bench --site <site> enable-scheduler`) and record how to verify the heartbeat
- [ ] Ensure `juri_hr` has a proper module structure for new doctypes (module `Juri Hr`), and `hooks.py` is ready for scheduler events
- [ ] Create an HRD/Admin test user and one Employee test user; document credentials handling (never commit secrets)

**AC**
- Site serves over HTTP locally; `bench --site <site> list-apps` shows `juri_hr`
- Scheduler heartbeat observable; instructions reproducible from a clean machine

## M0.3 Minimal UI kit

- [ ] Port design tokens into `frontend/src/styles/app.css`: primary + brand color scales, dark variant, base focus rings, card and rounded conventions, WCAG touch target helper (min 2.75rem)
- [ ] Build components in `frontend/src/components/ui/`: `AppButton`, `AppInput`, `AppSelect`, `AppTextarea`, `AppCheckbox`, `AppModal`, `AppBadge`, `DataTable` (sortable + paginated), `PageHeader`, `AppToast` (+ toast store), `AppEmptyState`, `AppSpinner`
- [ ] Add a status badge helper mapping daily statuses to colors: `present` green, `late` amber, `rejected` red, `absent` red-muted, `izin` blue, `cuti` cyan, `sakit` violet, `off`/`holiday` gray
- [ ] Add a dev-only route `/__dev/ui` rendering every component and status badge in light and dark mode

**AC**
- All components render correctly in light and dark
- Status colors come from one shared map (no duplicated color logic)

## M0.4 API client and auth store

- [ ] `frontend/src/api/client.ts`: base URL from env, `Authorization: token key:secret` injection, JSON handling, normalized error shape (`{ status, code, message, details }`) parsing Frappe `exc_type` and `_server_messages`, timeout/abort
- [ ] `frontend/src/api/resource.ts`: `listResource`, `getResource`, `createResource`, `updateResource`, `deleteResource` with `fields`, `filters`, `limit_start`, `limit_page_length`, `order_by`
- [ ] `frontend/src/api/method.ts`: `callMethod(name, params)`
- [ ] `frontend/src/api/files.ts`: `uploadPrivateFile(file, { doctype, docname })` via `/api/method/upload_file` with `is_private=1`, and `fetchPrivateBlob(fileUrl)` for authenticated downloads/previews
- [ ] `frontend/src/api/fixtures.ts`: interceptor that resolves requests from `frontend/src/api/fixtures/*.json` when `VITE_USE_FIXTURES=true` or the base URL is empty; supports basic filter + pagination simulation
- [ ] `frontend/src/stores/auth.ts`: `login(email, password)` -> `POST /api/method/login` then `generate_keys` (or an equivalent `juri_hr` helper) -> persist token; `logout()`; `loadSession()`; expose `user`, `roles`, `isHrd`
- [ ] Vitest: token injection, error normalization, fixture fallback, 401 clearing the session

**AC**
- Real login against the local Frappe site works and persists across reload
- With `VITE_USE_FIXTURES=true` the app runs without any backend

## M0.5 Roles, layouts, PWA

- [ ] Router with route meta `{ requiresAuth, roles, layout }` and guards: unauthenticated -> `/login`, wrong role -> `/403`
- [ ] `AdminLayout`: sidebar (Dashboard, Karyawan, Absensi, Jadwal, Pengajuan, Slip Gaji, Pengumuman, Aktivitas, Pengaturan), topbar with notification bell and user menu
- [ ] `UserLayout`: mobile-first topbar + bottom navigation (Home, Kalender, Pengajuan, Slip Gaji, Profil), safe-area padding
- [ ] `GuestLayout` for login
- [ ] Dark/light theme toggle persisted in localStorage
- [ ] PWA: `manifest.json` (name JuriHR, standalone, icons), service worker precaching the app shell and an offline page, bypassing `/api/` requests; online/offline indicator
- [ ] Placeholder pages for every nav entry so navigation is testable

**AC**
- HRD lands on the admin dashboard, employee on their home
- App installs on Android Chrome and shows the offline page when offline

## M0.6 CI

- [ ] GitHub Actions workflow: install (bun), lint, typecheck, Vitest, build for `frontend/`
- [ ] Separate job (or step) running Python unit tests for `frappe/juri_hr` where they exist (skip gracefully while none exist)
- [ ] Remove or rewrite legacy workflows that reference Laravel/FTP deploys

**AC**
- CI green on push; no workflow references deleted Laravel paths
