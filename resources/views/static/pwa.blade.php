<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#f5faf4">
    <title>{{ config('app.name') }}</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png">
    <style>
        :root {
            color-scheme: light dark;
            --bg: #f5faf4;
            --surface: rgba(255, 255, 255, 0.92);
            --border: rgba(87, 148, 74, 0.18);
            --text: #13251a;
            --muted: #5c715f;
            --primary: #57944a;
            --primary-strong: #44733a;
            --accent: rgba(87, 148, 74, 0.12);
            --track: rgba(87, 148, 74, 0.16);
            --shadow: 0 28px 64px -38px rgba(34, 64, 41, 0.42);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #07110c;
                --surface: rgba(15, 23, 42, 0.92);
                --border: rgba(132, 193, 120, 0.18);
                --text: #f4f8f3;
                --muted: #c6d6c4;
                --primary: #84c178;
                --primary-strong: #6aa35f;
                --accent: rgba(132, 193, 120, 0.1);
                --track: rgba(132, 193, 120, 0.18);
                --shadow: 0 28px 64px -40px rgba(0, 0, 0, 0.78);
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 20% 10%, var(--accent), transparent 34%),
                radial-gradient(circle at 90% 85%, rgba(87, 148, 74, 0.08), transparent 32%),
                var(--bg);
            transition: opacity 0.24s ease;
        }

        body.loaded {
            opacity: 0;
        }

        main {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: calc(1.75rem + env(safe-area-inset-top)) 1rem calc(1.75rem + env(safe-area-inset-bottom));
        }

        .shell {
            width: min(100%, 28rem);
            display: grid;
            gap: 1rem;
        }

        .panel {
            border: 1px solid var(--border);
            border-radius: 2rem;
            background: var(--surface);
            box-shadow: var(--shadow);
            padding: 1.25rem;
            backdrop-filter: blur(16px);
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .brand-lockup {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            min-width: 0;
        }

        .brand img {
            width: 4rem;
            height: 4rem;
            border-radius: 1.25rem;
            object-fit: cover;
            box-shadow: 0 16px 36px -22px rgba(68, 115, 58, 0.9);
        }

        .brand h1 {
            margin: 0;
            font-size: 1.55rem;
            line-height: 1.1;
            letter-spacing: -0.035em;
        }

        .brand p {
            margin: 0.25rem 0 0;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.45;
        }

        .status-pill {
            flex: 0 0 auto;
            min-width: 3rem;
            height: 3rem;
            border-radius: 999px;
            display: inline-grid;
            place-items: center;
            border: 1px solid var(--border);
            background: var(--track);
        }

        .pulse {
            width: 0.8rem;
            height: 0.8rem;
            border-radius: 999px;
            background: var(--primary);
            box-shadow: 0 0 0 0 rgba(87, 148, 74, 0.34);
            animation: pulse 1.35s ease-out infinite;
        }

        .progress {
            margin-top: 1.25rem;
        }

        .progress-track {
            height: 0.45rem;
            border-radius: 999px;
            background: var(--track);
            overflow: hidden;
        }

        .progress-bar {
            width: 42%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--primary-strong), var(--primary));
            animation: slide 1.15s ease-in-out infinite;
        }

        .status-text {
            margin: 0.875rem 0 0;
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.55;
        }

        .summary {
            display: grid;
            gap: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 1.5rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.34);
        }

        .summary-row {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            color: var(--muted);
            font-size: 0.925rem;
            line-height: 1.45;
        }

        .summary-row::before {
            content: "";
            flex: 0 0 auto;
            width: 0.625rem;
            height: 0.625rem;
            margin-top: 0.35rem;
            border-radius: 999px;
            background: var(--primary);
            box-shadow: 0 0 0 0.35rem var(--track);
        }

        @keyframes pulse {
            70% {
                box-shadow: 0 0 0 0.8rem rgba(87, 148, 74, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(87, 148, 74, 0);
            }
        }

        @keyframes slide {
            0% {
                transform: translateX(-75%);
            }

            50% {
                transform: translateX(85%);
            }

            100% {
                transform: translateX(235%);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <main>
        <div class="shell">
            <section class="panel" aria-labelledby="pwa-title">
                <div class="brand">
                    <div class="brand-lockup">
                        <img src="{{ asset('images/icons/icon-192x192.png') }}" alt="{{ config('app.name') }}">
                        <div>
                            <h1 id="pwa-title">{{ config('app.name') }}</h1>
                            <p>{{ __('Workforce operating system') }}</p>
                        </div>
                    </div>
                    <span class="status-pill" aria-hidden="true">
                        <span class="pulse"></span>
                    </span>
                </div>

                <div class="progress" aria-live="polite">
                    <div class="progress-track" aria-hidden="true">
                        <div class="progress-bar"></div>
                    </div>
                    <p class="status-text" id="status">{{ __('Preparing offline support...') }}</p>
                </div>
            </section>

            <section class="summary" aria-label="{{ __('App readiness') }}">
                <div class="summary-row">{{ __('Attendance, approvals, and payroll pages are being prepared.') }}</div>
                <div class="summary-row">{{ __('You will be redirected automatically when the app is ready.') }}</div>
            </section>
        </div>
    </main>

    <script>
        const statusEl = document.getElementById('status');
        let redirectTimer;

        function setStatus(message) {
            if (statusEl) {
                statusEl.textContent = message;
            }
        }

        function redirectToLogin() {
            setStatus(@js(__('Ready! Opening application...')));
            document.body.classList.add('loaded');

            setTimeout(() => {
                window.location.href = '/login';
            }, 240);
        }

        if ('serviceWorker' in navigator) {
            setStatus(@js(__('Registering service worker...')));

            const url = new URL(window.location.href);
            const isNativeApp = !!(window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform());

            const resetPromise = isNativeApp || url.searchParams.get('reset-sw') === '1'
                ? navigator.serviceWorker.getRegistrations()
                    .then((registrations) => Promise.all(registrations.map((registration) => registration.unregister())))
                    .then(() => 'caches' in window ? caches.keys().then((cacheNames) => Promise.all(cacheNames.map((cacheName) => caches.delete(cacheName)))) : null)
                    .then(() => {
                        if (isNativeApp) {
                            setStatus(@js(__('Native mode active.')));
                            return Promise.reject(new Error('SW disabled on native runtime'));
                        }

                        url.searchParams.delete('reset-sw');
                        window.location.replace(url.toString());

                        return Promise.reject(new Error('SW reset in progress'));
                    })
                : Promise.resolve();

            resetPromise.then(() => navigator.serviceWorker.register('/sw.js', {
                    updateViaCache: 'none',
                }))
                .then((registration) => registration.update().then(() => registration))
                .then((registration) => {
                    if (registration.waiting) {
                        registration.waiting.postMessage({ type: 'SKIP_WAITING' });
                    }

                    setStatus(@js(__('Service worker active!')));
                    redirectTimer = setTimeout(redirectToLogin, 800);
                })
                .catch((err) => {
                    if (err?.message === 'SW reset in progress') {
                        return;
                    }

                    console.error('SW registration failed:', err);
                    setStatus(@js(__('Failed to register service worker')));
                    redirectTimer = setTimeout(redirectToLogin, 1200);
                });
        } else {
            setStatus(@js(__('Browser does not support PWA')));
            redirectTimer = setTimeout(redirectToLogin, 900);
        }

        window.addEventListener('beforeunload', () => {
            if (redirectTimer) {
                clearTimeout(redirectTimer);
            }
        });
    </script>
</body>
</html>
