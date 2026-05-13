<?php

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\AttendanceSeeder;
use Database\Seeders\DemoAssetSeeder;
use Database\Seeders\FakeDataSeeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

test('demo and bootstrap seeders skip known accounts in production by default', function () {
    putenv('DEMO_SEEDING_ENABLED=false');
    putenv('BOOTSTRAP_ADMIN_SEEDING_ENABLED=false');
    $_ENV['DEMO_SEEDING_ENABLED'] = 'false';
    $_ENV['BOOTSTRAP_ADMIN_SEEDING_ENABLED'] = 'false';
    $_SERVER['DEMO_SEEDING_ENABLED'] = 'false';
    $_SERVER['BOOTSTRAP_ADMIN_SEEDING_ENABLED'] = 'false';
    Config::set('paspapan.demo_seeding_enabled', false);
    Config::set('paspapan.bootstrap_admin_seeding_enabled', false);
    Config::set('app.env', 'production');

    app()->detectEnvironment(fn () => 'production');

    User::query()->whereIn('email', [
        'admin@example.com',
        'superadmin@example.com',
        'user123@paspapan.com',
    ])->delete();

    app(AdminSeeder::class)->run();
    app(FakeDataSeeder::class)->run();
    app(AttendanceSeeder::class)->run();
    app(DemoAssetSeeder::class)->run();

    expect(User::query()->whereIn('email', [
        'admin@example.com',
        'superadmin@example.com',
        'user123@paspapan.com',
    ])->exists())->toBeFalse();

    app()->detectEnvironment(fn () => 'testing');
    Config::set('app.env', 'testing');
});

test('repository and public htaccess block sensitive paths as defense in depth', function () {
    $rootHtaccess = file_get_contents(base_path('.htaccess'));
    $publicHtaccess = file_get_contents(public_path('.htaccess'));
    $vercel = file_get_contents(base_path('vercel.json'));

    expect($rootHtaccess)
        ->toContain('\.env')
        ->toContain('\.git')
        ->toContain('storage|vendor|database|bootstrap/cache')
        ->toContain('composer\.(json|lock)')
        ->toContain('\.(sql|zip|tar|gz|tgz|bak|old)$')
        ->and($publicHtaccess)->toContain('^temp(/|$)')
        ->and($vercel)->not->toContain('|temp)');
});

test('wilayah routes use dedicated throttle middleware', function () {
    $route = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($route) => $route->uri() === 'api/wilayah/provinces');

    expect($route?->gatherMiddleware())->toContain('throttle:wilayah');
});
