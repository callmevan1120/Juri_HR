<?php

function paspapan_enterprise_runtime_key_missing(Throwable $throwable): bool
{
    return $throwable->getMessage() === 'Enterprise obfuscator key is missing.'
        || $throwable::class === 'App\\Exceptions\\EnterpriseObfuscatorKeyMissingException';
}

function paspapan_enterprise_runtime_http_response(): void
{
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    $wantsJson = str_contains($accept, 'application/json') || $requestedWith === 'xmlhttprequest';

    http_response_code(503);
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('Pragma: no-cache');
    header('X-Robots-Tag: noindex, nofollow');

    if ($wantsJson) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'message' => 'Enterprise runtime is not fully configured. Please contact the administrator.',
            'enterprise_runtime_locked' => true,
        ], JSON_THROW_ON_ERROR);

        return;
    }

    header('Content-Type: text/html; charset=UTF-8');
    echo <<<'HTML'
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enterprise Runtime Locked</title>
    <style>
        :root { color-scheme: light dark; }
        body {
            align-items: center;
            background: #0f172a;
            color: #e5e7eb;
            display: flex;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            justify-content: center;
            margin: 0;
            min-height: 100vh;
            padding: 24px;
        }
        main {
            background: rgba(15, 23, 42, .92);
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 18px;
            box-shadow: 0 24px 80px rgba(0, 0, 0, .28);
            max-width: 520px;
            padding: 28px;
        }
        h1 { font-size: 22px; line-height: 1.25; margin: 0 0 10px; }
        p { color: #cbd5e1; font-size: 15px; line-height: 1.7; margin: 0; }
    </style>
</head>
<body>
    <main>
        <h1>Enterprise runtime belum lengkap</h1>
        <p>Server ini belum memiliki deployment key yang diperlukan untuk menjalankan artifact Enterprise. Hubungi administrator untuk melengkapi konfigurasi runtime.</p>
    </main>
</body>
</html>
HTML;
}

function paspapan_enterprise_runtime_console_response(): void
{
    fwrite(STDERR, "Enterprise runtime is not fully configured.\n");
    fwrite(STDERR, "Set ENTERPRISE_OBFUSCATOR_KEY in the runtime environment with the same key used during enterprise build.\n");
}
