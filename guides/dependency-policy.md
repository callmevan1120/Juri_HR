# Dependency Policy

Dependency changes should be conservative because PasPapan targets VPS production with a PostgreSQL-first database, while still keeping legacy compatibility paths installable.

Rules:

- Prefer Laravel, Livewire, and existing support classes before adding packages.
- Do not add Redis, Horizon, or native binaries as hard requirements for core pages. WebSocket/Reverb may be used for VPS-first realtime features when polling or non-realtime fallback is documented.
- Keep `composer audit` and frontend audit output reviewed before release.
- Package overrides in `composer.json` or `package.json` must be tied to a concrete security or compatibility reason.
- Build output, cache, `vendor`, and `node_modules` must not be committed.
- For frontend assets, run `bun run build` after UI-impacting changes.

If a package is needed only for optional enterprise or native APK behavior, keep the runtime path guarded so the web app still works without that integration enabled.
