# Enterprise Packaging

Open source installs must not require an enterprise obfuscator secret. `composer install`, `composer dump-autoload`, and `php artisan package:discover` must work with only `.env.example` values and no enterprise key. Community/runtime guard classes that are used by menus, policies, providers, or settings must stay readable and key-free.

The enterprise obfuscator is an internal release tool under `secure_tools/` and must not be moved into public scripts.

## Rules

- Keep `secure_tools/` ignored.
- Do not commit generated build/cache/vendor artifacts.
- Enterprise PHP files distributed to customers must be obfuscated.
- Source backups such as `*.Source.php` must stay ignored and private.
- The internal obfuscator key belongs only in trusted private build/runtime environments for enterprise artifacts. It must not be required for OSS installation or listed in public `.env.example`.
- Customer enterprise artifacts must be built in salted mode with `ENTERPRISE_OBFUSCATOR_KEY`. The same key must exist in the runtime environment so obfuscated PHP can decrypt itself. `ENTERPRISE_LICENSE_KEY` is only for license validation and must not be used as the obfuscation secret.
- Unsalted obfuscation is only allowed for explicit internal debugging with `ENTERPRISE_ALLOW_UNSALTED_OBFUSCATION=true`; never ship that artifact to customers.
- Obfuscated runtime files are marked as generated in `.gitattributes` so OSS review focuses on readable source, tests, docs, and public automation.
- Any private enterprise source change must be followed by the internal obfuscator before commit.

## OSS Review Boundary

Readable OSS code, tests, docs, migrations, workflows, and public helper scripts are reviewed normally.
Enterprise runtime files committed in obfuscated form are treated as release artifacts: reviewers verify that the internal build was run, the artifact list is expected, and no private `*.Source.php`, `secure_tools/`, or `enterprise_build/` content is included.
CI runs `composer check:enterprise-boundary` to prove the private obfuscator, source mirrors, and generated enterprise build directory are not part of the OSS review surface.

Do not move `secure_tools/build_enterprise.php` or private source mirrors into `scripts/`. If an OSS reviewer needs behavior assurance, add or update tests around the public boundary instead of exposing the private implementation.

## Release Flow

1. Run quality gates on readable source.
2. Set `ENTERPRISE_OBFUSCATOR_KEY` in the private build environment and run the internal enterprise obfuscator.
3. Run smoke tests in the private enterprise runtime environment.
4. Run `composer check:enterprise-boundary` before staging files.
5. Regenerate release evidence that is meant to be public, such as Playwright smoke output and screenshot manifests.
6. Commit only intended obfuscated enterprise runtime files, readable source changes, tests, docs, and public screenshots.
7. Never publish the obfuscator implementation.

For `v5.0.0`, the public evidence set includes:

- 62 desktop screenshots in `screenshots/desktop-pages/`
- 62 APK screenshots in `screenshots/apk-pages/`
- Playwright smoke workflow in `.github/workflows/e2e.yml`
- security scan workflow in `.github/workflows/security.yml`
- database portability workflow in `.github/workflows/database-portability.yml`

The private build tool remains `secure_tools/build_enterprise.php`. It must be run before commit/release when enterprise-owned files are touched, but it must not be copied to `scripts/` or exposed in release artifacts. The tool fails closed when `ENTERPRISE_OBFUSCATOR_KEY` is missing or shorter than 32 characters, unless `ENTERPRISE_ALLOW_UNSALTED_OBFUSCATION=true` is set for a local debug-only build.

Runtime is fail-closed only for enterprise artifacts: if `ENTERPRISE_LICENSE_KEY` exists but the matching `ENTERPRISE_OBFUSCATOR_KEY` is missing, open-source/community pages must still boot, while enterprise routes render the feature-lock flow and enterprise CLI work is skipped with a concise configuration message. Obfuscated files remain unreadable without the matching key. Do not enable `APP_DEBUG=true` on customer production servers.

The public repository must keep the open source path self-contained: users can install, migrate, seed non-demo production-safe data, and run community features without requesting an enterprise secret first.
