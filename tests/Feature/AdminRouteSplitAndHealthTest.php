<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;

test('split admin route files preserve important route names and middleware', function () {
    $routes = collect(Route::getRoutes()->getRoutes())->keyBy(fn ($route) => $route->getName());

    foreach ([
        'admin.dashboard' => 'admin/dashboard',
        'admin.attendances' => 'admin/attendances',
        'admin.hr-checklists' => 'admin/hr-checklists',
        'admin.masters.division' => 'admin/masterdata/division',
        'admin.import-export.attendances' => 'admin/import-export/attendances',
        'admin.reports.index' => 'admin/reports',
        'admin.reimbursements' => 'admin/reimbursements',
        'admin.assets' => 'admin/assets',
        'admin.settings' => 'admin/settings',
        'admin.roles.permissions' => 'admin/roles-permissions',
    ] as $name => $uri) {
        expect($routes->get($name)?->uri())->toBe($uri);
    }

    expect($routes->get('admin.attendances')?->gatherMiddleware())->toContain('can:viewAdminAny,App\\Models\\Attendance')
        ->and($routes->get('admin.hr-checklists')?->gatherMiddleware())->toContain('can:viewAny,App\\Models\\HrChecklistCase');
});

test('operational health page only allows maintenance viewers', function () {
    $plainAdmin = User::factory()->admin()->create();
    $healthAdmin = User::factory()->admin()->create();
    $role = Role::create([
        'name' => 'Operational Health Viewer',
        'slug' => 'operational_health_viewer',
        'permissions' => ['admin.system_maintenance.view'],
    ]);
    $plainAdmin->roles()->detach();
    $healthAdmin->roles()->sync([$role->id]);

    $this->actingAs($plainAdmin)
        ->get(route('admin.operational-health'))
        ->assertForbidden();

    $this->actingAs($healthAdmin)
        ->get(route('admin.operational-health'))
        ->assertOk()
        ->assertSee(__('Operational Health'));
});
