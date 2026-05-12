<?php

use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Reimbursement;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SystemBackupRun;
use App\Models\User;
use App\Services\Enterprise\LicenseGuard;
use App\Support\SecureUploadPolicy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

test('idor matrix denies another user attendance media', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $attendance = Attendance::create([
        'user_id' => $owner->id,
        'date' => now()->toDateString(),
        'status' => 'present',
        'approval_status' => Attendance::STATUS_APPROVED,
    ]);

    $this->actingAs($attacker)
        ->get(route('attendance.photo', [$attendance, 'in']))
        ->assertForbidden();
});

test('attachment matrix rejects unsafe names and denies cross user reimbursement downloads', function () {
    Storage::fake('local');

    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    Storage::disk('local')->put('reimbursements/private.pdf', 'private');

    $reimbursement = Reimbursement::create([
        'user_id' => $owner->id,
        'date' => now()->toDateString(),
        'type' => 'medical',
        'amount' => 100000,
        'description' => 'Private claim',
        'attachment' => 'reimbursements/private.pdf',
        'status' => 'pending',
    ]);

    $file = UploadedFile::fake()->create('claim.php.pdf', 64, 'application/pdf');
    $rules = app(SecureUploadPolicy::class)->rules('document');

    expect(validator(['attachment' => $file], ['attachment' => ['required', ...$rules]])->fails())->toBeTrue();

    $this->actingAs($attacker)
        ->get(route('reimbursement.attachment.download', $reimbursement))
        ->assertForbidden();
});

test('payslip and payroll privacy matrix denies other users and unauthorized admins', function () {
    enableEnterpriseAttendanceForTests();

    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $plainAdmin = User::factory()->admin()->create();

    $payroll = Payroll::create([
        'user_id' => $owner->id,
        'month' => now()->month,
        'year' => now()->year,
        'basic_salary' => 1000000,
        'allowances' => [],
        'deductions' => [],
        'overtime_pay' => 0,
        'net_salary' => 1000000,
        'status' => 'paid',
    ]);

    expect(Gate::forUser($owner)->allows('download', $payroll))->toBeTrue()
        ->and(Gate::forUser($attacker)->denies('download', $payroll))->toBeTrue()
        ->and(Gate::forUser($plainAdmin)->denies('view', $payroll))->toBeTrue();
});

test('backup access matrix requires explicit maintenance management', function () {
    enableEnterpriseAttendanceForTests();

    $viewer = User::factory()->admin()->create();
    $manager = User::factory()->admin()->create();

    Role::create([
        'name' => 'Security Matrix Maintenance Viewer',
        'slug' => 'security_matrix_maintenance_viewer',
        'description' => 'Can view maintenance.',
        'permissions' => ['admin.system_maintenance.view'],
    ])->users()->attach($viewer);
    Role::create([
        'name' => 'Security Matrix Maintenance Manager',
        'slug' => 'security_matrix_maintenance_manager',
        'description' => 'Can manage maintenance.',
        'permissions' => ['admin.system_maintenance.manage'],
    ])->users()->attach($manager);

    $backup = SystemBackupRun::create([
        'type' => 'database',
        'status' => 'queued',
        'requested_by_user_id' => $manager->id,
        'queue' => 'maintenance',
        'file_disk' => 'local',
    ]);
    $backup->update([
        'status' => 'completed',
        'file_path' => 'backups/security-matrix.sql',
        'file_name' => 'security-matrix.sql',
        'completed_at' => now(),
    ]);

    expect(Gate::forUser($viewer)->denies('download', $backup))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('download', $backup))->toBeTrue();
});

test('debug route matrix keeps diagnostic endpoints hidden in production', function () {
    Config::set('app.debug', false);
    app()->detectEnvironment(fn () => 'production');

    $this->actingAs(User::factory()->create())
        ->get('/__auth-debug')
        ->assertNotFound();

    app()->detectEnvironment(fn () => 'testing');
});

test('enterprise gate matrix locks licensed modules without feature entitlement', function () {
    Setting::updateOrCreate(
        ['key' => 'enterprise_license_key'],
        ['value' => makeEnterpriseTestLicense(['features' => []]), 'group' => 'enterprise', 'type' => 'textarea']
    );
    Setting::flushCache('enterprise_license_key');
    LicenseGuard::clearLicenseCache();

    $superadmin = User::factory()->admin(true)->create();

    $this->actingAs($superadmin)
        ->get(route('admin.payrolls'))
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('show-feature-lock');
});
