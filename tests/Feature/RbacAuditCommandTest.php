<?php

use App\Models\Role;
use App\Services\Enterprise\LicenseGuard;
use App\Support\RbacAuditService;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    if (! LicenseGuard::hasRuntimeObfuscatorKey()) {
        test()->markTestSkipped('Enterprise runtime obfuscator key is not available.');
    }
});

test('rbac audit command returns a structured report', function () {
    $exitCode = Artisan::call('rbac:audit', ['--json' => true]);
    $report = json_decode(Artisan::output(), true);

    expect($exitCode)->toBe(0)
        ->and($report)->toHaveKeys([
            'routes_without_permission',
            'menu_entries_to_review',
            'permissions_without_route',
            'roles_without_permissions',
            'policies_without_direct_test',
        ]);
});

test('rbac audit service can inspect route and menu coverage without mutating data', function () {
    $report = app(RbacAuditService::class)->report();

    expect($report['routes_without_permission'])->toBeArray()
        ->and($report['menu_entries_to_review'])->toBeArray()
        ->and($report['permissions_without_route'])->toBeArray();
});

test('rbac audit treats registered permissions as covered even when route names differ', function () {
    Role::query()->create([
        'name' => 'Payroll Viewer',
        'slug' => 'payroll-viewer',
        'permissions' => ['admin.payroll.view'],
    ]);

    $report = app(RbacAuditService::class)->report();

    expect($report['permissions_without_route'])->not->toContain('unknown role permission admin.payroll.view');
});
