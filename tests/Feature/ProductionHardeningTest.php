<?php

use App\Http\Middleware\EnsureSecurityHeaders;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\AttendanceSeeder;
use Database\Seeders\DemoAssetSeeder;
use Database\Seeders\FakeDataSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

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

test('bootstrap admin seeder can repair default admin credentials idempotently', function () {
    app(AdminSeeder::class)->run();
    app(AdminSeeder::class)->run();

    $superadmin = User::query()->where('email', 'superadmin@example.com')->firstOrFail();
    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

    expect(User::query()->where('email', 'superadmin@example.com')->count())->toBe(1)
        ->and(User::query()->where('email', 'admin@example.com')->count())->toBe(1)
        ->and($superadmin->group)->toBe('superadmin')
        ->and(Hash::check('superadmin', $superadmin->password))->toBeTrue()
        ->and($superadmin->roles()->where('slug', 'super_admin')->exists())->toBeTrue()
        ->and($admin->group)->toBe('admin')
        ->and(Hash::check('admin', $admin->password))->toBeTrue()
        ->and($admin->roles()->where('slug', 'admin')->exists())->toBeTrue();
});

test('setting seeder preserves saved enterprise license when env seed is blank', function () {
    Config::set('app.enterprise_license_key', null);

    Setting::query()->updateOrCreate(
        ['key' => 'enterprise_license_key'],
        [
            'value' => 'saved-enterprise-license',
            'group' => 'system',
            'type' => 'text',
            'description' => 'Legacy Enterprise License Key',
        ],
    );

    app(SettingSeeder::class)->run();

    $setting = Setting::query()->where('key', 'enterprise_license_key')->firstOrFail();

    expect($setting->value)->toBe('saved-enterprise-license')
        ->and($setting->group)->toBe('enterprise')
        ->and($setting->type)->toBe('textarea');
});

test('setting seeder preserves production-edited setting values', function () {
    Setting::query()->updateOrCreate(
        ['key' => 'app.company_name'],
        [
            'value' => 'PT Production Customer',
            'group' => 'identity',
            'type' => 'text',
            'description' => 'Old label',
        ],
    );

    app(SettingSeeder::class)->run();

    $setting = Setting::query()->where('key', 'app.company_name')->firstOrFail();

    expect($setting->value)->toBe('PT Production Customer')
        ->and($setting->group)->toBe('identity')
        ->and($setting->type)->toBe('text')
        ->and($setting->description)->toBe('Company Name for Reports');
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

test('local content security policy allows configured reverb websocket origin', function () {
    Config::set('app.env', 'local');
    Config::set('broadcasting.default', 'reverb');
    Config::set('broadcasting.connections.reverb.options.host', '127.0.0.1');
    Config::set('broadcasting.connections.reverb.options.port', 8081);

    app()->detectEnvironment(fn () => 'local');

    try {
        $response = app(EnsureSecurityHeaders::class)->handle(
            Request::create('http://127.0.0.1:8000/admin/dashboard', 'GET'),
            fn () => new Response('ok'),
        );
        $csp = $response->headers->get('Content-Security-Policy');

        expect(str_contains((string) $csp, 'ws://127.0.0.1:8081'))->toBeTrue()
            ->and(str_contains((string) $csp, 'wss://127.0.0.1:8081'))->toBeTrue()
            ->and(str_contains((string) $csp, 'ws://localhost:8081'))->toBeTrue()
            ->and(str_contains((string) $csp, 'wss://localhost:8081'))->toBeTrue();
    } finally {
        app()->detectEnvironment(fn () => 'testing');
        Config::set('app.env', 'testing');
    }
});

test('wilayah routes use dedicated throttle middleware', function () {
    $route = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($route) => $route->uri() === 'api/wilayah/provinces');

    expect($route?->gatherMiddleware())->toContain('throttle:wilayah');
});

test('missing enterprise obfuscator key fails closed instead of rendering a raw runtime exception', function () {
    Route::middleware('web')->get('/__test/enterprise-runtime-missing', function () {
        throw new RuntimeException('Enterprise obfuscator key is missing.');
    })->name('test.enterprise-runtime-missing');

    $superadmin = User::factory()->admin(true)->create();

    $this->actingAs($superadmin)
        ->get('/__test/enterprise-runtime-missing')
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('show-feature-lock', fn (array $payload): bool => $payload['title'] === __('Enterprise Runtime Locked'));
});

test('missing enterprise obfuscator key returns a locked json response', function () {
    Route::middleware('web')->get('/__test/enterprise-runtime-missing-json', function () {
        throw new RuntimeException('Enterprise obfuscator key is missing.');
    })->name('test.enterprise-runtime-missing-json');

    $superadmin = User::factory()->admin(true)->create();

    $this->actingAs($superadmin)
        ->getJson('/__test/enterprise-runtime-missing-json')
        ->assertStatus(423)
        ->assertJson([
            'feature_locked' => true,
            'enterprise_runtime_locked' => true,
        ]);
});

test('enterprise decryption failure fails closed instead of rendering a raw runtime exception', function () {
    Route::middleware('web')->get('/__test/enterprise-runtime-decryption-failed', function () {
        throw new RuntimeException('Enterprise source decryption failed.');
    })->name('test.enterprise-runtime-decryption-failed');

    $superadmin = User::factory()->admin(true)->create();

    $this->actingAs($superadmin)
        ->get('/__test/enterprise-runtime-decryption-failed')
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('show-feature-lock', fn (array $payload): bool => $payload['title'] === __('Enterprise Runtime Locked'));
});
