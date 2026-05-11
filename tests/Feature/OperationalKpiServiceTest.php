<?php

use App\Models\Attendance;
use App\Models\CompanyAsset;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Reimbursement;
use App\Models\User;
use App\Support\OperationalKpiService;

test('operational kpi service summarizes attendance finance and asset metrics', function () {
    $admin = User::factory()->admin()->create(['provinsi_kode' => '31']);
    $employee = User::factory()->create(['hourly_rate' => 50000, 'provinsi_kode' => '31']);
    $secondEmployee = User::factory()->create(['hourly_rate' => 40000, 'provinsi_kode' => '31']);

    Attendance::create([
        'user_id' => $employee->id,
        'date' => '2026-06-01',
        'time_in' => '07:00:00',
        'status' => 'present',
        'approval_status' => Attendance::STATUS_APPROVED,
    ]);
    Attendance::create([
        'user_id' => $employee->id,
        'date' => '2026-06-02',
        'time_in' => '07:45:00',
        'status' => 'late',
        'approval_status' => Attendance::STATUS_APPROVED,
    ]);
    Attendance::create([
        'user_id' => $secondEmployee->id,
        'date' => '2026-06-01',
        'status' => 'excused',
        'approval_status' => Attendance::STATUS_APPROVED,
    ]);

    Overtime::create([
        'user_id' => $employee->id,
        'date' => '2026-06-02',
        'start_time' => '18:00:00',
        'end_time' => '20:00:00',
        'duration' => 120,
        'reason' => 'Month-end closing.',
        'status' => 'approved',
    ]);

    $reimbursement = Reimbursement::create([
        'user_id' => $employee->id,
        'date' => '2026-06-01',
        'type' => 'transport',
        'amount' => 150000,
        'description' => 'Client visit.',
        'status' => 'pending',
    ]);
    $reimbursement->forceFill(['created_at' => now()->subDays(4)])->save();

    Payroll::create([
        'user_id' => $employee->id,
        'month' => 5,
        'year' => 2026,
        'net_salary' => 5000000,
    ]);
    Payroll::create([
        'user_id' => $employee->id,
        'month' => 6,
        'year' => 2026,
        'net_salary' => 5500000,
    ]);

    CompanyAsset::create([
        'name' => 'Laptop',
        'type' => 'electronics',
        'user_id' => $employee->id,
        'date_assigned' => '2026-05-01',
        'return_date' => '2026-05-31',
        'status' => CompanyAsset::STATUS_ASSIGNED,
    ]);

    $summary = app(OperationalKpiService::class)->summary($admin, '2026-06-01', '2026-06-02');

    expect($summary['employee_count'])->toBe(2)
        ->and($summary['late_count'])->toBe(1)
        ->and($summary['late_rate'])->toBe(50.0)
        ->and($summary['absence_count'])->toBe(1)
        ->and($summary['absence_rate'])->toBe(25.0)
        ->and($summary['leave_liability_days'])->toBe(1)
        ->and($summary['overtime_minutes'])->toBe(120)
        ->and($summary['overtime_cost'])->toBe(100000.0)
        ->and($summary['reimbursement_pending_count'])->toBe(1)
        ->and($summary['reimbursement_oldest_pending_days'])->toBe(4)
        ->and($summary['payroll_variance_amount'])->toBe(500000.0)
        ->and($summary['payroll_variance_rate'])->toBe(10.0)
        ->and($summary['asset_overdue_count'])->toBe(1);
});
