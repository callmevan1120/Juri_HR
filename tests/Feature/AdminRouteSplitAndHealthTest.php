<?php

use App\Jobs\RecordQueueHeartbeat;
use App\Models\Role;
use App\Models\SystemBackupRun;
use App\Models\User;
use App\Services\Enterprise\LicenseGuard;
use App\Support\OperationalHealthService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    if (! LicenseGuard::hasRuntimeObfuscatorKey()) {
        test()->markTestSkipped('Enterprise runtime obfuscator key is not available.');
    }
});

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
        'admin.toko' => 'admin/toko',
        'admin.toko.pos' => 'admin/toko/pos',
        'admin.toko.products' => 'admin/toko/products',
        'admin.toko.customers' => 'admin/toko/customers',
        'admin.toko.vendors' => 'admin/toko/vendors',
        'admin.toko.purchases' => 'admin/toko/purchases',
        'admin.toko.inventory' => 'admin/toko/inventory',
        'admin.toko.returns' => 'admin/toko/returns',
        'admin.toko.quotations' => 'admin/toko/quotations',
        'admin.toko.delivery-letters' => 'admin/toko/delivery-letters',
        'admin.toko.cash' => 'admin/toko/cash',
        'admin.toko.reports' => 'admin/toko/reports',
        'admin.toko.migration' => 'admin/toko/migration',
        'admin.toko.invoices.pdf' => 'admin/toko/invoices/{invoice}/pdf',
        'admin.toko.quotations.pdf' => 'admin/toko/quotations/{quotation}/pdf',
        'admin.toko.delivery-letters.pdf' => 'admin/toko/delivery-letters/{deliveryLetter}/pdf',
        'admin.toko.products.barcodes' => 'admin/toko/products/barcodes',
        'admin.settings' => 'admin/settings',
        'admin.operational-health' => 'admin/operational-health',
        'admin.roles.permissions' => 'admin/roles-permissions',
    ] as $name => $uri) {
        expect($routes->get($name)?->uri())->toBe($uri);
    }

    expect($routes->get('admin.attendances')?->gatherMiddleware())->toContain('can:viewAdminAny,App\\Models\\Attendance')
        ->and($routes->get('admin.hr-checklists')?->gatherMiddleware())->toContain('can:viewAny,App\\Models\\HrChecklistCase');
});

test('operational health page only allows maintenance viewers', function () {
    Storage::fake('local');
    Storage::disk('local')->put('maintenance-backups/database/health.sql', 'backup');
    SystemBackupRun::create([
        'type' => 'database',
        'status' => 'completed',
        'file_disk' => 'local',
        'file_path' => 'maintenance-backups/database/health.sql',
        'file_name' => 'health.sql',
        'size_bytes' => 6,
        'completed_at' => now(),
        'meta' => ['checksum_sha256' => hash('sha256', 'backup')],
    ]);
    Cache::put('health:queue_heartbeat_at', '2026-05-12T10:00:00+07:00');
    Cache::put('health:scheduler_heartbeat_at', '2026-05-12T10:01:00+07:00');

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
        ->assertSee(__('Operational Health'))
        ->assertSee(__('Subsystem Checks'))
        ->assertSee(__('Runtime Posture'))
        ->assertSee(__('HR Compliance Reminders'))
        ->assertSee(__('Probation'))
        ->assertSee(__('Contracts'))
        ->assertSee(__('Backup Integrity Detail'))
        ->assertSee(__('Checksum'))
        ->assertSee(__('OK'));

    $this->actingAs($healthAdmin)
        ->get(route('admin.system-maintenance'))
        ->assertOk()
        ->assertSee(route('admin.operational-health'), false);
});

test('operational health distinguishes scheduler heartbeat from queue heartbeat', function () {
    Cache::put('health:scheduler_heartbeat_at', now()->toIso8601String());
    Cache::forget('health:queue_heartbeat_at');

    $health = app(OperationalHealthService::class)->snapshot();
    $codes = collect($health['alerts'])->pluck('code');

    expect($codes)->toContain('queue_stale')
        ->and($codes)->not->toContain('scheduler_stale');

    (new RecordQueueHeartbeat)->handle();

    $health = app(OperationalHealthService::class)->snapshot();
    $codes = collect($health['alerts'])->pluck('code');

    expect(Cache::get('health:queue_heartbeat_at'))->not->toBeNull()
        ->and($codes)->not->toContain('queue_stale');
});

test('admin account dropdown and direct api token route stay inside admin profile', function () {
    enableJetstreamApiFeaturesForTests();

    $admin = User::factory()->admin()->create();

    $this
        ->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.profile.show').'#api', false)
        ->assertDontSee(route('api-tokens.index'), false);

    $this
        ->actingAs($admin)
        ->get(route('api-tokens.index'))
        ->assertRedirect(route('admin.profile.show').'#api');
});
