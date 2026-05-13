<?php

namespace App\Support;

use App\Models\HrChecklistTask;
use App\Models\User;

class HrComplianceReminderService
{
    /**
     * @return array{probation_due:int,contract_due:int,incomplete_profiles:int,overdue_hr_tasks:int,auto_disable_due:int}
     */
    public function summary(int $probationDays = 14, int $contractDays = 30): array
    {
        return [
            'probation_due' => User::query()
                ->where('employment_status', User::EMPLOYMENT_STATUS_ACTIVE)
                ->whereNotNull('probation_ends_at')
                ->whereBetween('probation_ends_at', [now()->toDateString(), now()->addDays($probationDays)->toDateString()])
                ->count(),
            'contract_due' => User::query()
                ->where('employment_status', User::EMPLOYMENT_STATUS_ACTIVE)
                ->whereNotNull('contract_ends_at')
                ->whereBetween('contract_ends_at', [now()->toDateString(), now()->addDays($contractDays)->toDateString()])
                ->count(),
            'incomplete_profiles' => User::query()
                ->where('employment_status', User::EMPLOYMENT_STATUS_ACTIVE)
                ->where(function ($query): void {
                    $query
                        ->whereNull('nip')
                        ->orWhere('nip', '')
                        ->orWhereNull('phone')
                        ->orWhere('phone', '')
                        ->orWhereNull('division_id')
                        ->orWhereNull('job_title_id');
                })
                ->count(),
            'overdue_hr_tasks' => HrChecklistTask::query()->reminderReady()->count(),
            'auto_disable_due' => User::query()
                ->whereNotNull('account_auto_disable_at')
                ->where('account_auto_disable_at', '<=', now())
                ->where('employment_status', '!=', User::EMPLOYMENT_STATUS_INACTIVE)
                ->count(),
        ];
    }
}
