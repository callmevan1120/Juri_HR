# Attendance Risk Scoring

Attendance risk scoring is a fraud triage signal, not an automatic disciplinary decision. Scores help HR/Admin prioritize review while keeping the original attendance status and approval workflow intact.

## Inputs

The production check-in/check-out flows call `AttendanceRiskScoringService`, which normalizes device, barcode, GPS, face verification, and offline context before delegating to `AttendanceRiskScorer`.

| Factor | Weight | Notes |
| --- | ---: | --- |
| `mock_location_detected` | 40 | Strong signal from native/mock-location detection. |
| `offline_submitted` | 25 | Covers offline queue and cached location submissions. |
| `cached_location_used` | 15 | Browser/PWA fallback used a recent cached GPS point because fresh GPS failed. |
| `gps_accuracy_too_perfect` | 20 | Accuracy below 5 meters can indicate emulator/spoof patterns. |
| `gps_zero_variance` | 20 | Repeated identical GPS variance is suspicious. |
| `near_attendance_radius` | 15 | Triggered when distance is 85%-100% of checkpoint radius. |
| `device_changed` | 15 | Includes missing device info. |
| `device_info_missing` | 10 | Device/API client explicitly reports missing device metadata. |
| `face_confidence_low` | 20 | Triggered below 0.65 or when face verification is failed/skipped. |
| `qr_token_retry` | 10-25 | Retry weight is capped at 25; static QR source counts as at least one retry. |
| `timestamp_invalid` / `timestamp_in_future` / `timestamp_anomaly` | 15-20 | Online submissions should be close to server time; offline submissions get a wider stale threshold. |
| `check_in_too_early` | 10 | More than 120 minutes before shift start. |
| `check_in_late` | 10 | Uses the attendance `late` status. |

## Platform Coverage

- Web/PWA uses browser geolocation and can mark cached location fallback when a recent known point is reused.
- Android can additionally submit the native mock-location signal from the dedicated plugin.
- iOS does not rely on mock-location APIs; it uses the shared backend risk context: GPS accuracy, cached/offline submission, timestamp drift, device-change metadata, face confidence, QR source, and shift timing.
- Device API and offline sync accept the same telemetry keys so native clients can converge on one backend scoring policy.

## Thresholds

| Score | Level | Operational action |
| ---: | --- | --- |
| 0-24 | Low | Normal monitoring. |
| 25-59 | Medium | Show risk badge/filter for admin review. |
| 60-100 | High | Mark `is_suspicious` and prioritize manual investigation. |

Scores are capped at 100. Check-out risk is merged with existing check-in risk so repeated weak signals can become high risk.

## False Positives

Common false positives include weak GPS indoors, new/replaced phones, emergency offline submissions, poor lighting during face verification, and checkpoints with too small a radius. Reviewers should compare photo, GPS map, device history, schedule, and manager notes before taking action.

## Review Guidance

Keep a human-in-the-loop review for medium/high risk rows. When a case is cleared, record the reason in the relevant correction/approval/audit workflow so future tuning can distinguish real fraud from environmental noise.
