<?php

namespace App\Support;

use App\Models\HrChecklistTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeLifecycleService
{
    public function __construct(
        private readonly HrChecklistService $hrChecklists,
    ) {}

    /**
     * @return Collection<int, User>
     */
    public function probationEndingWithin(int $days = 14): Collection
    {
        return $this->endingWithin('probation_ends_at', $days);
    }

    /**
     * @return Collection<int, User>
     */
    public function contractEndingWithin(int $days = 30): Collection
    {
        return $this->endingWithin('contract_ends_at', $days);
    }

    public function submitResignation(User $employee, User $actor, Carbon|string $effectiveDate, ?string $reason = null): User
    {
        $effectiveDate = Carbon::parse($effectiveDate)->startOfDay();

        return DB::transaction(function () use ($employee, $actor, $effectiveDate, $reason): User {
            $employee->forceFill([
                'employment_status' => User::EMPLOYMENT_STATUS_RESIGNED,
                'resignation_submitted_at' => now(),
                'resigned_at' => $effectiveDate,
                'resignation_reason' => filled($reason) ? trim((string) $reason) : null,
                'account_auto_disable_at' => $effectiveDate->copy()->endOfDay(),
            ])->save();

            $this->createOffboardingCase($employee, $actor, $effectiveDate);

            return $employee->refresh();
        });
    }

    public function completeExitInterview(User $employee): User
    {
        $employee->forceFill([
            'exit_interview_completed_at' => now(),
        ])->save();

        return $employee->refresh();
    }

    public function disableDueAccounts(?Carbon $now = null): int
    {
        $now ??= now();

        return User::query()
            ->whereNotNull('account_auto_disable_at')
            ->where('account_auto_disable_at', '<=', $now)
            ->where('employment_status', '!=', User::EMPLOYMENT_STATUS_INACTIVE)
            ->update([
                'employment_status' => User::EMPLOYMENT_STATUS_INACTIVE,
                'updated_at' => now(),
            ]);
    }

    /**
     * @return Collection<int, User>
     */
    private function endingWithin(string $column, int $days): Collection
    {
        return User::query()
            ->where('employment_status', User::EMPLOYMENT_STATUS_ACTIVE)
            ->whereNotNull($column)
            ->whereBetween($column, [now()->toDateString(), now()->addDays($days)->toDateString()])
            ->orderBy($column)
            ->get();
    }

    private function createOffboardingCase(User $employee, User $actor, Carbon $effectiveDate): void
    {
        $this->hrChecklists->ensureDefaultTemplates();

        $template = HrChecklistTemplate::query()
            ->where('type', HrChecklistTemplate::TYPE_OFFBOARDING)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if ($template === null) {
            return;
        }

        $existing = $employee->hrChecklistCases()
            ->where('type', HrChecklistTemplate::TYPE_OFFBOARDING)
            ->where('effective_date', $effectiveDate->toDateString())
            ->exists();

        if (! $existing) {
            $this->hrChecklists->createCase($employee, $template, $actor, $effectiveDate);
        }
    }
}
