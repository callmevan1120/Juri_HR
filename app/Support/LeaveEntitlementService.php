<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LeaveEntitlementService
{
    public function __construct(private readonly LeaveCalculator $calculator) {}

    public function canAccessUser(User $actor, User $employee): bool
    {
        return $actor->isSuperadmin
            || $actor->company_id === null
            || (int) $actor->company_id === (int) $employee->company_id;
    }

    /**
     * @return array{
     *     leave_type_id: int|null,
     *     leave_type_name: string,
     *     year: int,
     *     allocated_days: float,
     *     carried_over_days: float,
     *     total_allocated: float,
     *     used_days: int,
     *     remaining_days: float,
     *     expires_at: string|null,
     *     is_expired: bool,
     *     uses_quota: bool
     * }
     */
    public function summaryFor(User $user, ?LeaveType $leaveType = null, ?int $year = null): array
    {
        $year ??= now()->year;
        $leaveType ??= $this->defaultAnnualLeaveType();
        $usesQuota = (bool) ($leaveType?->counts_against_quota ?? true);
        $entitlement = $leaveType
            ? LeaveEntitlement::query()
                ->where('user_id', $user->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('year', $year)
                ->first()
            : null;

        $allocatedDays = $entitlement
            ? (float) $entitlement->allocated_days
            : (float) Setting::getValue('leave.annual_quota', 12);
        $carriedOverDays = $entitlement ? (float) $entitlement->carried_over_days : 0.0;
        $totalAllocated = round($allocatedDays + $carriedOverDays, 2);
        $usedDays = $leaveType && $usesQuota ? $this->usedDays($user, $leaveType, $year) : 0;
        $expiresAt = $entitlement?->expires_at;
        $isExpired = $expiresAt !== null && $expiresAt->endOfDay()->isPast();

        return [
            'leave_type_id' => $leaveType?->id,
            'leave_type_name' => $leaveType?->name ?? __('Annual Leave'),
            'year' => $year,
            'allocated_days' => round($allocatedDays, 2),
            'carried_over_days' => round($carriedOverDays, 2),
            'total_allocated' => $totalAllocated,
            'used_days' => $usedDays,
            'remaining_days' => $usesQuota && ! $isExpired
                ? (float) $this->calculator->remainingAnnualQuota((int) floor($totalAllocated), $usedDays)
                : 0.0,
            'expires_at' => $expiresAt?->toDateString(),
            'is_expired' => $isExpired,
            'uses_quota' => $usesQuota,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function quotaSummariesFor(User $user, ?int $year = null): Collection
    {
        $year ??= now()->year;

        return LeaveType::query()
            ->active()
            ->where('counts_against_quota', true)
            ->ordered()
            ->get()
            ->map(fn (LeaveType $leaveType): array => $this->summaryFor($user, $leaveType, $year));
    }

    public function createOrUpdateAnnualEntitlement(
        User $user,
        int $year,
        float $allocatedDays,
        ?Carbon $expiresAt = null,
        float $carriedOverDays = 0,
        ?string $notes = null,
    ): LeaveEntitlement {
        $leaveType = $this->defaultAnnualLeaveType();

        abort_if($leaveType === null, 422, 'Annual leave type is not configured.');

        return LeaveEntitlement::query()->updateOrCreate([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'year' => $year,
        ], [
            'company_id' => $user->company_id,
            'allocated_days' => $allocatedDays,
            'carried_over_days' => $carriedOverDays,
            'expires_at' => $expiresAt?->toDateString(),
            'notes' => $notes,
        ]);
    }

    public function quotaErrorForRequest(
        User $user,
        string $status,
        ?LeaveType $leaveType,
        Carbon $fromDate,
        Carbon $toDate,
        int $requestedDays,
    ): ?string {
        $countsAgainstQuota = $leaveType?->counts_against_quota ?? $status === 'excused';

        if (! $countsAgainstQuota) {
            return null;
        }

        $summary = $this->summaryFor($user, $leaveType, $fromDate->year);

        if ($summary['expires_at'] !== null && $toDate->greaterThan(Carbon::parse($summary['expires_at'])->endOfDay())) {
            return __('Leave entitlement expired on :date.', [
                'date' => Carbon::parse($summary['expires_at'])->translatedFormat('d M Y'),
            ]);
        }

        if ($this->calculator->wouldExceedAnnualQuota($status, (int) floor($summary['total_allocated']), $summary['used_days'], $requestedDays, true)) {
            return __('Not enough remaining annual leave quota for this request.');
        }

        return null;
    }

    private function usedDays(User $user, LeaveType $leaveType, int $year): int
    {
        return Attendance::query()
            ->where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereIn('approval_status', [Attendance::STATUS_PENDING, Attendance::STATUS_APPROVED])
            ->where(function (Builder $query): void {
                $query->whereNull('status')
                    ->orWhereIn('status', Attendance::REQUEST_STATUSES);
            })
            ->where(function (Builder $query) use ($leaveType): void {
                $query->where('leave_type_id', $leaveType->id);

                if ($leaveType->code === 'annual_leave') {
                    $query->orWhere(function (Builder $legacyQuery): void {
                        $legacyQuery
                            ->whereNull('leave_type_id')
                            ->where('status', 'excused');
                    });
                }
            })
            ->count();
    }

    private function defaultAnnualLeaveType(): ?LeaveType
    {
        return LeaveType::query()
            ->active()
            ->where('counts_against_quota', true)
            ->ordered()
            ->first();
    }
}
