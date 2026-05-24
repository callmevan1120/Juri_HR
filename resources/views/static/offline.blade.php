<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f5faf4">
    <title>{{ __('Offline') }} | {{ config('app.name') }}</title>
    <style>
        :root {
            color-scheme: light dark;
            --bg: #f5faf4;
            --surface: rgba(255, 255, 255, 0.94);
            --border: rgba(87, 148, 74, 0.18);
            --text: #163020;
            --muted: #536b5b;
            --primary: #57944a;
            --primary-strong: #44733a;
            --accent: rgba(87, 148, 74, 0.1);
            --shadow: 0 24px 56px -34px rgba(34, 64, 41, 0.35);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #07110c;
                --surface: rgba(15, 23, 42, 0.94);
                --border: rgba(132, 193, 120, 0.18);
                --text: #f3f7f2;
                --muted: #c5d6c3;
                --primary: #84c178;
                --primary-strong: #6aa35f;
                --accent: rgba(132, 193, 120, 0.1);
                --shadow: 0 24px 56px -36px rgba(0, 0, 0, 0.72);
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
                radial-gradient(circle at top left, var(--accent), transparent 36%),
                radial-gradient(circle at bottom right, rgba(87, 148, 74, 0.08), transparent 30%),
                var(--bg);
        }

        main {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: calc(1.5rem + env(safe-area-inset-top)) 1rem calc(1.5rem + env(safe-area-inset-bottom));
        }

        .panel {
            width: min(100%, 28rem);
            border: 1px solid var(--border);
            border-radius: 1.75rem;
            background: var(--surface);
            box-shadow: var(--shadow);
            padding: 1.25rem;
            backdrop-filter: blur(16px);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            margin-bottom: 1.5rem;
        }

        .brand img {
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 1rem;
            object-fit: cover;
            box-shadow: 0 14px 30px -18px rgba(68, 115, 58, 0.8);
        }

        .brand strong {
            display: block;
            font-size: 1rem;
        }

        .brand span {
            display: block;
            margin-top: 0.125rem;
            color: var(--muted);
            font-size: 0.875rem;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.85rem, 8vw, 2.45rem);
            line-height: 1.08;
            letter-spacing: -0.035em;
        }

        p {
            margin: 0.875rem 0 0;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.65;
        }

        .steps {
            display: grid;
            gap: 0.625rem;
            margin-top: 1.25rem;
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 1.25rem;
            background: var(--accent);
        }

        .step {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--muted);
            font-size: 0.925rem;
            line-height: 1.45;
        }

        .dot {
            flex: 0 0 auto;
            width: 0.625rem;
            height: 0.625rem;
            border-radius: 999px;
            background: var(--primary);
            box-shadow: 0 0 0 0.375rem rgba(87, 148, 74, 0.12);
        }

        .actions {
            display: grid;
            gap: 0.75rem;
            margin-top: 1.25rem;
        }

        a,
        button {
            -webkit-tap-highlight-color: transparent;
        }

        .button,
        .button-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 3.125rem;
            border-radius: 1rem;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            font-weight: 800;
            text-decoration: none;
        }

        .button {
            border: 0;
            background: linear-gradient(180deg, var(--primary), var(--primary-strong));
            color: #fff;
        }

        .button-secondary {
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text);
        }

        .button:focus-visible,
        .button-secondary:focus-visible {
            outline: 3px solid rgba(87, 148, 74, 0.28);
            outline-offset: 3px;
        }

        @media (min-width: 640px) {
            .panel {
                padding: 1.5rem;
            }

            .actions {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <main>
        <section class="panel" aria-labelledby="offline-title">
            <div class="brand">
                <img src="{{ asset('images/icons/icon-192x192.png') }}" alt="{{ config('app.name') }}">
                <div>
                    <strong>{{ config('app.name') }}</strong>
                    <span>{{ __('Offline mode') }}</span>
                </div>
            </div>

            <h1 id="offline-title">{{ __('You are offline') }}</h1>
            <p>{{ __('PasPapan cannot reach the server right now. You can retry when the connection is back.') }}</p>

            <div class="steps" aria-label="{{ __('What to check') }}">
                <div class="step"><span class="dot" aria-hidden="true"></span>{{ __('Check your mobile data or Wi-Fi connection.') }}</div>
                <div class="step"><span class="dot" aria-hidden="true"></span>{{ __('If you are in the app, reopen it after the connection is stable.') }}</div>
            </div>

            <div class="actions">
                <button class="button" type="button" onclick="window.location.reload()">{{ __('Try again') }}</button>
                <a class="button-secondary" href="{{ url('/') }}">{{ __('Back to Home') }}</a>
            </div>
        </section>
    </main>
</body>
</html>
