# Release Checklist

## Enterprise key rotation

- Generate and store a new `ENTERPRISE_OBFUSCATOR_KEY` in the secret manager.
- Generate and store a new `ENTERPRISE_ADDON_SALT_TOKO_POS` in the secret manager for the Toko/POS add-on.
- Keep both values at least 32 characters long.
- Do not paste either secret into tickets, chat, commits, screenshots, or CI logs.

## Rebuild enterprise artifacts

- Update local `.env` with the new `ENTERPRISE_OBFUSCATOR_KEY`.
- Update local `.env` with the new `ENTERPRISE_ADDON_SALT_TOKO_POS` when building Toko/POS artifacts.
- Run `php secure_tools/build_enterprise.php` to regenerate secured enterprise artifacts.
- Confirm the build output marks Toko/POS files with `toko_pos add-on salt`.

## Deploy runtime configuration

- Set `ENTERPRISE_OBFUSCATOR_KEY` on every enterprise runtime environment.
- Set `ENTERPRISE_ADDON_SALT_TOKO_POS` on every runtime that serves the Toko/POS add-on.
- Deploy the newly rebuilt enterprise artifacts together with the new runtime secrets.
- Avoid partial rollout: old artifacts with new secrets, or new artifacts with old secrets, will fail to decrypt.

## Post-deploy verification

- Run `php artisan optimize:clear` on the target server.
- Open an enterprise page that uses core secured artifacts.
- Open a Toko/POS page to confirm add-on artifacts decrypt successfully.
- Verify non-enterprise/community pages still boot without enterprise secrets where expected.
- Run the relevant smoke tests for the deployed edition.

## Rollback note

- If rollback is required, revert the artifact bundle and the matching runtime secrets as one pair.
- Do not mix a previous artifact bundle with the rotated secrets.
