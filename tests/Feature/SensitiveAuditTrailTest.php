<?php

use App\Contracts\AuditServiceInterface;
use App\Models\ActivityLog;
use App\Models\ActivityLogDetail;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

function bindPersistentAuditRecorder(): void
{
    app()->instance(AuditServiceInterface::class, new class implements AuditServiceInterface
    {
        public function record(string $action, ?string $description = null)
        {
            return ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => request()->ip(),
            ]);
        }
    });
}

test('sensitive employee salary changes create field level audit details', function () {
    bindPersistentAuditRecorder();

    $actor = User::factory()->admin(true)->create();
    $employee = User::factory()->create([
        'basic_salary' => 5000000,
        'hourly_rate' => 25000,
    ]);

    $this->actingAs($actor);

    $employee->update([
        'basic_salary' => 6500000,
        'hourly_rate' => 32000,
    ]);

    $details = ActivityLogDetail::query()
        ->where('entity_type', User::class)
        ->where('entity_id', $employee->id)
        ->orderBy('field')
        ->get();

    expect($details)->toHaveCount(2)
        ->and($details->pluck('field')->all())->toBe(['basic_salary', 'hourly_rate'])
        ->and($details->every->hasValidIntegrityHash())->toBeTrue();

    $salaryDetail = $details->firstWhere('field', 'basic_salary');

    expect((string) data_get($salaryDetail->old_value, 'value'))->toContain('5000000')
        ->and((string) data_get($salaryDetail->new_value, 'value'))->toContain('6500000')
        ->and($salaryDetail->activityLog->user_id)->toBe($actor->id)
        ->and($salaryDetail->activityLog->action)->toBe('Sensitive Field Changed');
});

test('payroll amount and status changes create field level audit details', function () {
    bindPersistentAuditRecorder();

    $actor = User::factory()->admin(true)->create();
    $employee = User::factory()->create();
    $payroll = Payroll::create([
        'user_id' => $employee->id,
        'month' => 5,
        'year' => 2026,
        'basic_salary' => 5000000,
        'net_salary' => 5000000,
        'status' => 'draft',
        'generated_by' => $actor->id,
    ]);

    $this->actingAs($actor);

    $payroll->update([
        'net_salary' => 5750000,
        'status' => 'published',
    ]);

    $details = ActivityLogDetail::query()
        ->where('entity_type', Payroll::class)
        ->where('entity_id', (string) $payroll->id)
        ->orderBy('field')
        ->get();

    expect($details)->toHaveCount(2)
        ->and($details->pluck('field')->all())->toBe(['net_salary', 'status'])
        ->and((string) data_get($details->firstWhere('field', 'net_salary')->new_value, 'value'))->toContain('5750000')
        ->and(data_get($details->firstWhere('field', 'status')->old_value, 'value'))->toBe('draft')
        ->and(data_get($details->firstWhere('field', 'status')->new_value, 'value'))->toBe('published');
});

test('role permission changes create field level audit details', function () {
    bindPersistentAuditRecorder();

    $actor = User::factory()->admin(true)->create();
    $role = Role::create([
        'name' => 'Audit Test Role',
        'slug' => 'audit_test_role',
        'description' => 'Initial permission set.',
        'permissions' => ['admin.dashboard.view'],
    ]);

    $this->actingAs($actor);

    $role->update([
        'permissions' => ['admin.dashboard.view', 'admin.activity_logs.view'],
    ]);

    $detail = ActivityLogDetail::query()
        ->where('entity_type', Role::class)
        ->where('entity_id', $role->id)
        ->where('field', 'permissions')
        ->firstOrFail();

    expect(data_get($detail->old_value, 'value'))->toBe(['admin.dashboard.view'])
        ->and(data_get($detail->new_value, 'value'))->toBe(['admin.dashboard.view', 'admin.activity_logs.view'])
        ->and($detail->hasValidIntegrityHash())->toBeTrue();
});

test('sensitive secret fields are redacted in audit details', function () {
    bindPersistentAuditRecorder();

    $actor = User::factory()->admin(true)->create();
    $employee = User::factory()->create();

    $this->actingAs($actor);

    $employee->forceFill([
        'payslip_password' => 'encrypted-secret',
        'payslip_password_set_at' => now(),
    ])->save();

    $passwordDetail = ActivityLogDetail::query()
        ->where('entity_type', User::class)
        ->where('entity_id', $employee->id)
        ->where('field', 'payslip_password')
        ->firstOrFail();

    expect(data_get($passwordDetail->old_value, 'redacted'))->toBeTrue()
        ->and(data_get($passwordDetail->new_value, 'redacted'))->toBeTrue()
        ->and($passwordDetail->new_value)->not->toHaveKey('value');
});

test('activity log details are append only and tampering is detectable', function () {
    bindPersistentAuditRecorder();

    $actor = User::factory()->admin(true)->create();
    $employee = User::factory()->create(['basic_salary' => 5000000]);

    $this->actingAs($actor);

    $employee->update(['basic_salary' => 6000000]);

    $detail = ActivityLogDetail::query()->where('field', 'basic_salary')->firstOrFail();

    expect($detail->hasValidIntegrityHash())->toBeTrue();

    expect(fn () => $detail->forceFill(['field' => 'hourly_rate'])->save())
        ->toThrow(AuthorizationException::class, 'Activity log details are append-only and cannot be modified.');

    expect(fn () => $detail->delete())
        ->toThrow(AuthorizationException::class, 'Activity log details are append-only and cannot be deleted.');

    DB::table('activity_log_details')
        ->where('id', $detail->id)
        ->update(['new_value' => json_encode(['value' => 1], JSON_THROW_ON_ERROR)]);

    expect($detail->refresh()->hasValidIntegrityHash())->toBeFalse();
});
