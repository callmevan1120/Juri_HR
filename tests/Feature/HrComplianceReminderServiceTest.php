<?php

use App\Models\HrChecklistCase;
use App\Models\HrChecklistTask;
use App\Models\HrChecklistTemplate;
use App\Models\User;
use App\Support\HrComplianceReminderService;

test('hr compliance reminder summary tracks lifecycle and checklist risks', function () {
    User::factory()->create([
        'probation_ends_at' => now()->addDays(5)->toDateString(),
    ]);
    User::factory()->create([
        'contract_ends_at' => now()->addDays(10)->toDateString(),
    ]);
    User::factory()->create([
        'phone' => '',
        'division_id' => null,
        'job_title_id' => null,
    ]);
    User::factory()->create([
        'employment_status' => User::EMPLOYMENT_STATUS_RESIGNED,
        'account_auto_disable_at' => now()->subHour(),
    ]);
    $employee = User::factory()->create();
    $template = HrChecklistTemplate::create([
        'type' => 'onboarding',
        'name' => 'Compliance Test',
        'is_active' => true,
    ]);
    $case = HrChecklistCase::create([
        'template_id' => $template->id,
        'user_id' => $employee->id,
        'type' => 'onboarding',
        'status' => HrChecklistCase::STATUS_ACTIVE,
        'effective_date' => now()->toDateString(),
    ]);
    HrChecklistTask::create([
        'case_id' => $case->id,
        'title' => 'Upload signed policy',
        'assigned_to' => $employee->id,
        'status' => HrChecklistTask::STATUS_PENDING,
        'due_date' => now()->subDay()->toDateString(),
    ]);

    $summary = app(HrComplianceReminderService::class)->summary();

    expect($summary['probation_due'])->toBeGreaterThanOrEqual(1)
        ->and($summary['contract_due'])->toBeGreaterThanOrEqual(1)
        ->and($summary['incomplete_profiles'])->toBeGreaterThanOrEqual(1)
        ->and($summary['overdue_hr_tasks'])->toBeGreaterThanOrEqual(1)
        ->and($summary['auto_disable_due'])->toBeGreaterThanOrEqual(1);
});
