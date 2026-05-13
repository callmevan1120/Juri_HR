<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\EnsureActiveAccount;
use App\Http\Middleware\EnsureSecurityHeaders;
use App\Http\Middleware\LogUserActivity;
use App\Http\Middleware\RedirectLockedEnterpriseFeature;
use App\Http\Middleware\SetUserLocale;
use App\Http\Middleware\ThrottleRequestsByIP;
use App\Http\Middleware\UserMiddleware;
use App\Http\Middleware\VerifyAttendanceIntegrationSignature;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust Cloudflare proxies for HTTPS detection
        $middleware->trustProxies(at: '*');
        $middleware->redirectUsersTo(fn (Request $request) => $request->user()?->preferredHomeUrl() ?? '/');

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'user' => UserMiddleware::class,
            'ability' => CheckForAnyAbility::class,
            'abilities' => CheckAbilities::class,
            'feature.lock' => RedirectLockedEnterpriseFeature::class,
            'throttle.ip' => ThrottleRequestsByIP::class,
            'attendance.integration.signature' => VerifyAttendanceIntegrationSignature::class,
        ]);
        $middleware->preventRequestForgery(except: [
            '__vercel-migrate',
        ]);
        $middleware->web(append: [
            LogUserActivity::class,
            EnsureSecurityHeaders::class,
            CheckMaintenanceMode::class,
            SetUserLocale::class,
            EnsureActiveAccount::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthorizationException $e) {
            $request = request();
            $user = $request->user();

            Log::warning('AuthorizationException rendered.', [
                'path' => $request->path(),
                'route' => $request->route()?->getName(),
                'user_id' => $user?->id,
                'email' => $user?->email,
                'group' => $user?->group,
                'roles' => $user?->roles()->pluck('slug')->all() ?? [],
                'is_admin' => $user?->isAdmin,
                'can_access_admin_panel' => $user?->can('accessAdminPanel'),
                'can_view_admin_dashboard' => $user?->can('viewAdminDashboard'),
                'message' => $e->getMessage(),
            ]);

            return null;
        });

        $exceptions->render(function (HttpExceptionInterface $e) {
            $statusCode = $e->getStatusCode();

            if ($statusCode === 403) {
                $request = request();
                $user = $request->user();

                Log::warning('HTTP 403 rendered.', [
                    'path' => $request->path(),
                    'route' => $request->route()?->getName(),
                    'user_id' => $user?->id,
                    'email' => $user?->email,
                    'group' => $user?->group,
                    'roles' => $user?->roles()->pluck('slug')->all() ?? [],
                    'is_admin' => $user?->isAdmin,
                    'can_access_admin_panel' => $user?->can('accessAdminPanel'),
                    'can_view_admin_dashboard' => $user?->can('viewAdminDashboard'),
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                ]);
            }

            // Check if a specific view exists for this status code
            if (view()->exists("errors.{$statusCode}")) {
                return null; // Let Laravel handle usage of that view
            }

            // Fallback to 404 for any other HTTP error
            return response()->view('errors.404', [], 404);
        });
    })
    ->create();

if ($storagePath = env('APP_STORAGE_PATH')) {
    $app->useStoragePath($storagePath);

    foreach ([
        'app',
        'app/livewire-tmp',
        'app/import-export/uploads',
        'app/import-export/exports',
        'framework/cache/data',
        'framework/sessions',
        'framework/testing',
        'framework/views',
        'logs',
    ] as $directory) {
        $path = $storagePath.DIRECTORY_SEPARATOR.$directory;

        if (! is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }
}

return $app;
