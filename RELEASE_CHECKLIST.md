# Release Checklist

Checklist publik ini dipakai sebelum membuat tag atau GitHub Release. Jangan menaruh secret, credential demo, atau detail private enterprise build di dokumen ini.

## Version Sync

- [ ] Update `CHANGELOG.md`.
- [ ] Update versi di `README.md`.
- [ ] Update `package.json`.
- [ ] Update `android/app/build.gradle` `versionName` dan `versionCode`.
- [ ] Pastikan nama APK mengikuti versi rilis.

## Security Gate

- [ ] Login admin dan user berhasil.
- [ ] RBAC route/menu diuji untuk admin terbatas.
- [ ] Upload dan download attachment diuji owner/admin.
- [ ] Payslip hanya bisa dibuka pemilik atau admin berwenang.
- [ ] Backup/maintenance hanya bisa dibuka role maintenance.
- [ ] Dynamic QR butuh permission admin dan response `no-store`.
- [ ] Jalankan `php artisan rbac:audit`.
- [ ] Pastikan `FILESYSTEM_ATTACHMENT_DISKS=local` untuk production baru.

## Test Gate

- [ ] `php artisan test --without-tty`
- [ ] `composer phpstan`
- [ ] `./vendor/bin/pint --test`
- [ ] `composer audit`
- [ ] `bun audit`
- [ ] `bun run build`
- [ ] `php artisan rbac:audit`
- [ ] Coverage baseline workflow reviewed.
- [ ] Playwright smoke utama.
- [ ] APK smoke pada device fisik.

## APK Gate

- [ ] Login user/admin.
- [ ] Check-in dan check-out.
- [ ] Face enrollment.
- [ ] Upload reimbursement.
- [ ] Upload leave attachment.
- [ ] Download attachment.
- [ ] Approval manager.
- [ ] Back button.
- [ ] Offline page.
- [ ] Clear cache/update app path.

## Deployment Gate

- [ ] Deploy `develop` ke staging.
- [ ] Jalankan smoke test staging.
- [ ] Jalankan Release Preflight workflow dengan versi target.
- [ ] Verifikasi `/public` adalah document root.
- [ ] Verifikasi `APP_ENV=production`, `APP_DEBUG=false`, dan secure session cookie.
- [ ] Verifikasi queue worker dan scheduler heartbeat di Operational Health.
- [ ] Verifikasi backup checksum terbaru.

## Release

- [ ] Jalankan enterprise obfuscator sesuai SOP private sebelum commit rilis.
- [ ] Buat tag dan GitHub Release.
- [ ] Attach APK dan checksum.
- [ ] Update release notes.
- [ ] Post announcement.
