# Attendance Threat Model

Attendance fraud scenarios:

- Mock location or developer-mode location injection.
- Static QR reuse or screenshot sharing.
- Dynamic QR replay, retry, or stale token use.
- Check-in near the outer GPS radius.
- Cached/offline location submitted later.
- Device changes or missing device context.
- Face verification skipped, failed, or low confidence.
- Check-in/check-out outside normal shift windows.

Controls:

- Dynamic QR tokens are signed, short-lived, latest-token validated, and consumed after successful scan.
- GPS distance is checked against barcode radius.
- Risk scoring records factors such as mock location, offline submission, device change, static QR usage, near-radius distance, and face confidence.
- Admin attendance views should surface suspicious status without exposing unnecessary raw device metadata.

Offline mode is intentionally limited. It captures local timestamp, GPS, optional photo, and barcode payload, then syncs with an `offline_submitted` risk factor. Server time remains the trusted sync time.
