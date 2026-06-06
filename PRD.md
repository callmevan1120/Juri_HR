# PRD: iOS Native Scanner Validation Fix

## Problem

iOS native build passes and the default app launch renders the styled home page correctly, but the scanner validation entry is not reliable yet. When the iOS simulator app is launched with `CAP_SERVER_URL` pointing directly to the E2E login URL for `/scan`, the captured screen opens in Safari with an unstyled `127.0.0.1` page instead of rendering the scanner inside the Capacitor WebView.

## Evidence

- iOS build/preflight passed with `BUILD SUCCEEDED`.
- Default native launch renders correctly:
  - `screenshots/ios-native-test/default-launch.png`
- Scanner direct-launch path renders incorrectly:
  - `screenshots/ios-native-test/scan-native.png`
- Android native scanner is not showing this issue after `adb reverse tcp:8000 tcp:8000` and `adb reverse tcp:5173 tcp:5173`.

## Expected Behavior

Launching the iOS native app into the scanner flow should:

- Stay inside the Capacitor app WebView, not Safari.
- Render Tailwind/Vite styles correctly.
- Show the `/scan` screen with the native scanner layout.
- Preserve safe-area spacing for Dynamic Island devices.
- Keep scanner card, shift selector, camera frame, and location card aligned without overlap.

## Current Behavior

The default iOS native home screen is styled and usable, but the direct scanner entry shows:

- Safari browser chrome/address bar.
- Raw/unstyled HTML.
- Large unstyled logo.
- No usable scanner card.

## Acceptance Criteria

1. `bun run ios:preflight` completes successfully.
2. `bun run ios:screenshot` with default config captures a styled native app screen.
3. A scanner-specific native screenshot can be captured from iPhone simulator without opening Safari.
4. The scanner screenshot shows:
   - title/header rendered with app styling,
   - `QR Absen Masuk`,
   - shift selector,
   - camera/scanner frame,
   - location section,
   - no raw link styling,
   - no Safari address bar.
5. The implementation supports local development with:
   - Laravel at `http://127.0.0.1:8000`,
   - Vite dev assets from `public/hot` when `bun run dev:local` is active.

## Suggested Investigation

- Verify whether Capacitor iOS supports `server.url` values with path/query parameters or only origins.
- If only origins are supported, update the iOS scanner test harness to launch the app at the origin and navigate internally to `/__e2e-login?...&to=/scan` after WebView load.
- Confirm Vite dev asset URLs are reachable from iOS simulator during native WebView rendering.
- Confirm any external navigation handling does not route local app URLs to Safari.

## Verification Commands

```bash
bun run ios:preflight
IOS_SCREENSHOT_DIR=screenshots/ios-native-test IOS_SCREENSHOT_NAME=default-launch.png bun run ios:screenshot
```

For scanner validation, use the fixed harness to produce:

```bash
screenshots/ios-native-test/scan-native.png
```
