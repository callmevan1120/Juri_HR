# Enterprise Packaging

The enterprise obfuscator is an internal release tool under `secure_tools/` and must not be moved into public scripts.

## Rules

- Keep `secure_tools/` ignored.
- Do not commit generated build/cache/vendor artifacts.
- Enterprise PHP files distributed to customers must be obfuscated.
- Source backups such as `*.Source.php` must stay ignored and private.
- `ENTERPRISE_OBFUSCATOR_KEY` must match between build and runtime for salted mode.

## Release Flow

1. Run quality gates on readable source.
2. Run the internal enterprise obfuscator.
3. Run smoke tests with the runtime obfuscator key.
4. Commit only intended obfuscated enterprise files and non-generated source changes.
5. Never publish the obfuscator implementation.
