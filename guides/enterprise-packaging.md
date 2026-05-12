# Enterprise Packaging

The enterprise obfuscator is an internal release tool under `secure_tools/` and must not be moved into public scripts.

## Rules

- Keep `secure_tools/` ignored.
- Do not commit generated build/cache/vendor artifacts.
- Enterprise PHP files distributed to customers must be obfuscated.
- Source backups such as `*.Source.php` must stay ignored and private.
- `ENTERPRISE_OBFUSCATOR_KEY` must match between build and runtime for salted mode.
- Obfuscated runtime files are marked as generated in `.gitattributes` so OSS review focuses on readable source, tests, docs, and public automation.
- Any private enterprise source change must be followed by the internal obfuscator before commit.

## OSS Review Boundary

Readable OSS code, tests, docs, migrations, workflows, and public helper scripts are reviewed normally.
Enterprise runtime files committed in obfuscated form are treated as release artifacts: reviewers verify that the internal build was run, the artifact list is expected, and no private `*.Source.php`, `secure_tools/`, or `enterprise_build/` content is included.

Do not move `secure_tools/build_enterprise.php` or private source mirrors into `scripts/`. If an OSS reviewer needs behavior assurance, add or update tests around the public boundary instead of exposing the private implementation.

## Release Flow

1. Run quality gates on readable source.
2. Run the internal enterprise obfuscator.
3. Run smoke tests with the runtime obfuscator key.
4. Regenerate release evidence that is meant to be public, such as Playwright smoke output and screenshot manifests.
5. Commit only intended obfuscated enterprise runtime files, readable source changes, tests, docs, and public screenshots.
6. Never publish the obfuscator implementation.

For `v4.3.0`, the public evidence set includes:

- 62 desktop screenshots in `screenshots/desktop-pages/`
- 62 APK screenshots in `screenshots/apk-pages/`
- Playwright smoke workflow in `.github/workflows/e2e.yml`
- security scan workflow in `.github/workflows/security.yml`

The private build tool remains `secure_tools/build_enterprise.php`. It must be run before commit/release when enterprise-owned files are touched, but it must not be copied to `scripts/` or exposed in release artifacts.
