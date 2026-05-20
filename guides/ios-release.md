# iOS Release Readiness

PasPapan now ships the reviewed Capacitor `ios/` project and iOS can be compiled on a macOS release machine. It is **build-ready for simulator**, but not full App Store/TestFlight release-ready until signing and physical iPhone smoke pass.

Use this before claiming iOS support in a production release:

```bash
bun install
bun run build
bun run ios:assets
bun run ios:preflight
bun run ios:screenshot
```

On macOS with Xcode:

```bash
bunx cap sync ios
open ios/App/App.xcodeproj
```

Release gates:

- Xcode project generated, reviewed, and synced with Capacitor.
- Icons and splash screens regenerated from `resources/icon.png` and `resources/splash.png` via `bun run ios:assets`.
- iOS plugin set stays SPM-clean: camera, geolocation, browser, app, and splash screen. The legacy community barcode plugin remains Android-only because it does not ship a Capacitor 8 `Package.swift`; iOS QR scanning uses the WebView/HTML5 scanner path.
- Simulator build passes with `CODE_SIGNING_ALLOWED=NO`.
- Simulator screenshot evidence is captured in `screenshots/ios-simulator/` with `bun run ios:screenshot`.
- App identifier, icons, splash screen, permissions, and ATS/CSP reviewed.
- Camera, GPS, file upload, Dynamic QR, login, and logout smoke-tested on a physical iPhone.
- TestFlight build uploaded with version matching `package.json`, README, changelog, and release notes.
- Production URL and Reverb/broadcast configuration verified for the target environment.

Until those gates pass, the maturity audit intentionally keeps iOS below release-ready.
