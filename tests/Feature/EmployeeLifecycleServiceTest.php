<?php

use App\Models\HrChecklistCase;
use App\Models\User;
use App\Support\EmployeeLifecycleService;

test('employee lifecycle finds probation and contract reminders', function () {
    $probation = User::factory()->create([
        'probation_ends_at' => now()->addDays(7)->toDateString(),
    ]);
    $contract = User::factory()->create([
        'contract_ends_at' => now()->addDays(20)->toDateString(),
    ]);
    User::factory()->create([
        'contract_ends_at' => now()->addDays(60)->toDateString(),
    ]);

    $service = app(EmployeeLifecycleService::class);

    expect($service->probationEndingWithin(14)->pluck('id')->all())->toContain($probation->id)
        ->and($service->contractEndingWithin(30)->pluck('id')->all())->toContain($contract->id);
});

test('employee lifecycle resignation creates offboarding case and schedules auto disable', function () {
    $actor = User::factory()->admin()->create();
    $employee = User::factory()->create();

    $updated = app(EmployeeLifecycleService::class)->submitResignation(
        employee: $employee,
        actor: $actor,
        effectiveDate: '2026-05-20',
        reason: 'Accepted another opportunity.',
    );

    expect($updated->employment_status)->toBe(User::EMPLOYMENT_STATUS_RESIGNED)
        ->and($updated->resigned_at?->toDateString())->toBe('2026-05-20')
        ->and($updated->account_auto_disable_at?->toDateString())->toBe('2026-05-20')
        ->and($updated->resignation_reason)->toBe('Accepted another opportunity.')
        ->and(HrChecklistCase::query()->where('user_id', $employee->id)->where('type', 'offboarding')->exists())->toBeTrue();
});

test('employee lifecycle can complete exit interview and auto disable due resigned accounts', function () {
    $employee = User::factory()->create([
        'employment_status' => User::EMPLOYMENT_STATUS_RESIGNED,
        'account_auto_disable_at' => now()->subDay(),
    ]);

    $service = app(EmployeeLifecycleService::class);
    $service->completeExitInterview($employee);

    expect($employee->refresh()->exit_interview_completed_at)->not->toBeNull();

    expect($service->disableDueAccounts())->toBe(1)
        ->and($employee->refresh()->employment_status)->toBe(User::EMPLOYMENT_STATUS_INACTIVE);
});
