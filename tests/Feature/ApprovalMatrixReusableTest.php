<?php

use App\Models\ApprovalMatrixRule;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\User;
use App\Support\ApprovalMatrixService;

test('approval matrix resolves direct manager steps for overtime workflow', function () {
    $manager = User::factory()->create();
    $employee = User::factory()->create(['manager_id' => $manager->id]);
    $overtime = Overtime::create([
        'user_id' => $employee->id,
        'date' => now()->toDateString(),
        'start_time' => now()->setTime(18, 0),
        'end_time' => now()->setTime(21, 0),
        'duration' => 180,
        'reason' => 'Release support',
        'status' => 'pending',
    ]);

    ApprovalMatrixRule::create([
        'workflow' => ApprovalMatrixRule::WORKFLOW_OVERTIME,
        'name' => 'Long overtime needs manager',
        'is_active' => true,
        'priority' => 10,
        'conditions' => ['min_amount' => 120],
        'steps' => [
            ['key' => 'direct_manager', 'label' => 'Direct Manager', 'approver_type' => 'direct_manager'],
        ],
    ]);

    $matrix = app(ApprovalMatrixService::class);

    expect($matrix->matchingRule(ApprovalMatrixRule::WORKFLOW_OVERTIME, $overtime)?->name)->toBe('Long overtime needs manager')
        ->and($matrix->canActorApprove($manager, ApprovalMatrixRule::WORKFLOW_OVERTIME, $overtime))->toBeTrue();
});

test('approval matrix resolves permission steps for payroll sensitive workflow', function () {
    $payrollAdmin = User::factory()->admin()->create();
    $plainAdmin = User::factory()->admin()->create();

    Role::create([
        'name' => 'Payroll Sensitive Approver',
        'slug' => 'payroll_sensitive_approver',
        'description' => 'Can approve sensitive payroll actions.',
        'permissions' => ['admin.payrolls.approve_sensitive'],
    ])->users()->attach($payrollAdmin);

    $employee = User::factory()->create();
    $payroll = Payroll::create([
        'user_id' => $employee->id,
        'month' => now()->month,
        'year' => now()->year,
        'basic_salary' => 1000000,
        'allowances' => [],
        'deductions' => [],
        'overtime_pay' => 0,
        'net_salary' => 15000000,
        'status' => 'draft',
    ]);

    ApprovalMatrixRule::create([
        'workflow' => ApprovalMatrixRule::WORKFLOW_PAYROLL_SENSITIVE_ACTION,
        'name' => 'High payroll change approval',
        'is_active' => true,
        'priority' => 10,
        'conditions' => ['min_amount' => 10000000],
        'steps' => [
            [
                'key' => 'payroll_sensitive',
                'label' => 'Payroll Sensitive Approver',
                'approver_type' => 'permission',
                'permission' => 'admin.payrolls.approve_sensitive',
            ],
        ],
    ]);

    $matrix = app(ApprovalMatrixService::class);

    expect($matrix->matchingRule(ApprovalMatrixRule::WORKFLOW_PAYROLL_SENSITIVE_ACTION, $payroll)?->name)
        ->toBe('High payroll change approval')
        ->and($matrix->canActorApprove($payrollAdmin, ApprovalMatrixRule::WORKFLOW_PAYROLL_SENSITIVE_ACTION, $payroll))->toBeTrue()
        ->and($matrix->canActorApprove($plainAdmin, ApprovalMatrixRule::WORKFLOW_PAYROLL_SENSITIVE_ACTION, $payroll))->toBeFalse();
});
