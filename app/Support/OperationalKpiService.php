<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\CompanyAsset;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Reimbursement;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;

class OperationalKpiService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(User $actor, string|\DateTimeInterface $startDate, string|\DateTimeInterface $endDate): array
    {
        $start = CarbonImmutable::parse($startDate)->startOfDay();
        $end = CarbonImmutable::parse($endDate)->startOfDay();

        if ($end->lessThan($start)) {
            [$start, $end] = [$end, $start];
        }

        $employeeCount = User::query()
            ->where('group', 'user')
            ->managedBy($actor)
            ->count();

        $workDays = collect(CarbonPeriod::create($start, $end))->count();
        $attendanceSummary = $this->attendanceSummary($actor, $start, $end);
        $expectedAttendances = $employeeCount * $workDays;
        $coveredAttendances = $attendanceSummary['present'] + $attendanceSummary['late'] + $attendanceSummary['approved_leave'];
        $absentCount = max(0, $expectedAttendances - $coveredAttendances);
        $overtimeCost = $this->overtimeCost($actor, $start, $end);
        $reimbursementAging = $this->reimbursementAging($actor);
        $payrollVariance = $this->payrollVariance($actor, $end);

        return [
            'employee_count' => $employeeCount,
            'period_days' => $workDays,
            'late_count' => $attendanceSummary['late'],
            'late_rate' => $this->rate($attendanceSummary['late'], max(1, $attendanceSummary['present'] + $attendanceSummary['late'])),
            'absence_count' => $absentCount,
            'absence_rate' => $this->rate($absentCount, max(1, $expectedAttendances)),
            'approved_leave_count' => $attendanceSummary['approved_leave'],
            'leave_liability_days' => $attendanceSummary['approved_leave'],
            'overtime_minutes' => $overtimeCost['minutes'],
            'overtime_cost' => $overtimeCost['cost'],
            'reimbursement_pending_count' => $reimbursementAging['pending_count'],
            'reimbursement_oldest_pending_days' => $reimbursementAging['oldest_pending_days'],
            'reimbursement_average_pending_days' => $reimbursementAging['average_pending_days'],
            'payroll_current_net' => $payrollVariance['current_net'],
            'payroll_previous_net' => $payrollVariance['previous_net'],
            'payroll_variance_amount' => $payrollVariance['amount'],
            'payroll_variance_rate' => $payrollVariance['rate'],
            'asset_overdue_count' => $this->assetOverdueCount($actor, $end),
        ];
    }

    /**
     * @return array{present:int,late:int,approved_leave:int}
     */
    protected function attendanceSummary(User $actor, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $rows = Attendance::query()
            ->managedBy($actor)
            ->selectRaw("
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN status IN ('excused', 'sick') AND approval_status = ? THEN 1 ELSE 0 END) as approved_leave_count
            ", [Attendance::STATUS_APPROVED])
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->first();

        return [
            'present' => (int) ($rows->present_count ?? 0),
            'late' => (int) ($rows->late_count ?? 0),
            'approved_leave' => (int) ($rows->approved_leave_count ?? 0),
        ];
    }

    /**
     * @return array{minutes:int,cost:float}
     */
    protected function overtimeCost(User $actor, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $overtimes = Overtime::query()
            ->with('user:id,hourly_rate')
            ->whereHas('user', fn (Builder $query) => $query->managedBy($actor))
            ->where('status', 'approved')
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->get();

        return [
            'minutes' => (int) $overtimes->sum('duration'),
            'cost' => round((float) $overtimes->sum(fn (Overtime $overtime): float => ((float) ($overtime->user?->hourly_rate ?? 0)) * ((int) $overtime->duration / 60)), 2),
        ];
    }

    /**
     * @return array{pending_count:int,oldest_pending_days:int,average_pending_days:float}
     */
    protected function reimbursementAging(User $actor): array
    {
        $pending = Reimbursement::query()
            ->whereHas('user', fn (Builder $query) => $query->managedBy($actor))
            ->whereIn('status', ['pending', 'pending_finance', 'pending_matrix'])
            ->get(['id', 'created_at']);

        $ages = $pending->map(fn (Reimbursement $reimbursement): int => (int) $reimbursement->created_at->diffInDays(now()));

        return [
            'pending_count' => $pending->count(),
            'oldest_pending_days' => (int) ($ages->max() ?? 0),
            'average_pending_days' => round((float) ($ages->avg() ?? 0), 2),
        ];
    }

    /**
     * @return array{current_net:float,previous_net:float,amount:float,rate:float}
     */
    protected function payrollVariance(User $actor, CarbonImmutable $periodDate): array
    {
        $current = $this->payrollNetFor($actor, (int) $periodDate->month, (int) $periodDate->year);
        $previousDate = $periodDate->subMonthNoOverflow();
        $previous = $this->payrollNetFor($actor, (int) $previousDate->month, (int) $previousDate->year);

        return [
            'current_net' => $current,
            'previous_net' => $previous,
            'amount' => round($current - $previous, 2),
            'rate' => $this->rate($current - $previous, max(1, $previous)),
        ];
    }

    protected function payrollNetFor(User $actor, int $month, int $year): float
    {
        return round((float) Payroll::query()
            ->whereHas('user', fn (Builder $query) => $query->managedBy($actor))
            ->where('month', $month)
            ->where('year', $year)
            ->sum('net_salary'), 2);
    }

    protected function assetOverdueCount(User $actor, CarbonImmutable $asOf): int
    {
        return CompanyAsset::query()
            ->whereHas('user', fn (Builder $query) => $query->managedBy($actor))
            ->where('status', CompanyAsset::STATUS_ASSIGNED)
            ->whereNotNull('return_date')
            ->whereDate('return_date', '<', $asOf->toDateString())
            ->count();
    }

    protected function rate(float|int $value, float|int $base): float
    {
        return round(((float) $value / max(1, (float) $base)) * 100, 2);
    }
}
