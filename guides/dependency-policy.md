# Dependency Policy

Dependency changes should be conservative because PasPapan targets shared-hosting deployments.

Rules:

- Prefer Laravel, Livewire, and existing support classes before adding packages.
- Do not add services that require Redis, Horizon, WebSocket workers, or native binaries as baseline requirements.
- Keep `composer audit` and frontend audit output reviewed before release.
- Package overrides in `composer.json` or `package.json` must be tied to a concrete security or compatibility reason.
- Build output, cache, `vendor`, and `node_modules` must not be committed.
- For frontend assets, run `bun run build` after UI-impacting changes.

If a package is needed only for optional enterprise or native APK behavior, keep the runtime path guarded so the web app still works without that integration enabled.
