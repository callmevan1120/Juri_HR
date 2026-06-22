<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\CashAdvance;
use App\Models\Company;
use App\Models\Holiday;
use App\Models\HrChecklistCase;
use App\Models\HrChecklistTask;
use App\Models\Invoice;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\ProjectTask;
use App\Models\Reimbursement;
use App\Models\SalesOpportunity;
use App\Models\User;
use App\Models\VendorBill;
use App\Models\WorkFromHomeRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdminDashboardQueryService
{
    /**
     * @return array<string, mixed>
     */
    public function build(User $admin, Carbon $selectedDate, string $search = ''): array
    {
        $selectedDateString = $selectedDate->toDateString();
        $today = now()->startOfDay();
        $managedUserIds = $this->managedUserIds($admin);
        $managedCompanyIds = $this->managedCompanyIds($admin, $managedUserIds);

        $pendingCounts = $this->pendingCounts($admin, $managedUserIds);

        $attendances = Attendance::query()
            ->managedBy($admin)
            ->with(['shift', 'user:id,name,nip'])
            ->where('date', $selectedDateString)
            ->get();
        $attendancesByUser = $attendances->keyBy('user_id');

        $employees = User::query()
            ->where('group', 'user')
            ->managedBy($admin)
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('name', 'like', '%'.$search.'%')
                        ->orWhere('nip', 'like', '%'.$search.'%');
                });
            })
            ->paginate(10, ['*'], 'employeesPage')
            ->through(function (User $user) use ($attendancesByUser) {
                return $user->setAttribute(
                    'attendance',
                    $attendancesByUser->get($user->id),
                );
            });

        $employeesCount = User::query()
            ->where('group', 'user')
            ->managedBy($admin)
            ->count();

        $attendanceSummary = $this->attendanceStatusSummary($admin, $selectedDateString);
        $presentCount = (int) ($attendanceSummary['present'] ?? 0);
        $lateCount = (int) ($attendanceSummary['late'] ?? 0);
        $excusedCount = (int) ($attendanceSummary['excused'] ?? 0);
        $sickCount = (int) ($attendanceSummary['sick'] ?? 0);

        $recentUserActivities = ActivityLog::query()
            ->with('user')
            ->whereHas('user', function (Builder $query) use ($admin) {
                $query->where('group', 'user')->managedBy($admin);
            })
            ->whereNotIn('action', ['Visited Page'])
            ->latest('created_at')
            ->take(6)
            ->get();

        $loggedInUserIdsOnSelectedDate = ActivityLog::query()
            ->where('action', 'Login Successful')
            ->whereBetween('created_at', [
                $selectedDate->copy()->startOfDay(),
                $selectedDate->copy()->endOfDay(),
            ])
            ->whereHas('user', function (Builder $query) use ($admin) {
                $query->where('group', 'user')->managedBy($admin);
            })
            ->distinct()
            ->pluck('user_id');

        $notLoggedInUsers = User::query()
            ->where('group', 'user')
            ->managedBy($admin)
            ->whereNotIn('id', $loggedInUserIdsOnSelectedDate)
            ->orderBy('name')
            ->paginate(10, ['id', 'name', 'nip'], 'notLoggedInPage');

        $notLoggedInUsersCount = $notLoggedInUsers->total();

        $overdueUsers = Attendance::query()
            ->managedBy($admin)
            ->with(['user', 'shift'])
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->where('date', $selectedDateString)
            ->orderByDesc('date')
            ->take(10)
            ->get()
            ->filter(function (Attendance $attendance) use ($selectedDate, $today) {
                if (! $attendance->shift) {
                    return false;
                }

                if ($selectedDate->lt($today)) {
                    return true;
                }

                if ($selectedDate->isSameDay($today)) {
                    return now()->format('H:i:s') > $attendance->shift->end_time;
                }

                return false;
            });

        return [
            'pendingLeavesCount' => $pendingCounts['leaves'],
            'pendingAttendanceCorrectionsCount' => $pendingCounts['attendance_corrections'],
            'pendingReimbursementsCount' => $pendingCounts['reimbursements'],
            'pendingOvertimesCount' => $pendingCounts['overtimes'],
            'pendingKasbonCount' => $pendingCounts['kasbon'],
            'missingFaceDataCount' => User::query()
                ->where('group', 'user')
                ->managedBy($admin)
                ->whereDoesntHave('faceDescriptor')
                ->count(),
            'activeHolidaysCount' => Holiday::query()->where('date', $selectedDateString)->count(),
            'attendances' => $attendances,
            'employees' => $employees,
            'employeesCount' => $employeesCount,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'excusedCount' => $excusedCount,
            'sickCount' => $sickCount,
            'recentUserActivities' => $recentUserActivities,
            'notLoggedInUsers' => $notLoggedInUsers,
            'notLoggedInUsersCount' => $notLoggedInUsersCount,
            'loggedInUsersCount' => max(0, $employeesCount - $notLoggedInUsersCount),
            'neverLoggedInCount' => User::query()
                ->where('group', 'user')
                ->managedBy($admin)
                ->whereDoesntHave('activityLogs', fn (Builder $query) => $query->where('action', 'Login Successful'))
                ->count(),
            'unreadNotificationsCount' => $admin->unreadNotifications()->count(),
            'unreadNotificationsPreview' => $admin->unreadNotifications()->latest()->take(5)->get(),
            'overdueUsers' => $overdueUsers,
            'calendarLeaves' => $this->calendarLeaves($admin, $selectedDate),
            'platformSignals' => $this->platformSignals($admin, $managedUserIds, $managedCompanyIds, $selectedDateString),
        ];
    }

    /**
     * @return array{present:int,late:int,excused:int,sick:int}
     */
    private function attendanceStatusSummary(User $admin, string $selectedDateString): array
    {
        $rows = Attendance::query()
            ->managedBy($admin)
            ->selectRaw("
                status,
                SUM(
                    CASE
                        WHEN status IN ('excused', 'sick') AND approval_status != ? THEN 0
                        ELSE 1
                    END
                ) as aggregate_count
            ", [Attendance::STATUS_APPROVED])
            ->where('date', $selectedDateString)
            ->whereIn('status', ['present', 'late', 'excused', 'sick'])
            ->groupBy('status')
            ->pluck('aggregate_count', 'status');

        return [
            'present' => (int) ($rows['present'] ?? 0),
            'late' => (int) ($rows['late'] ?? 0),
            'excused' => (int) ($rows['excused'] ?? 0),
            'sick' => (int) ($rows['sick'] ?? 0),
        ];
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public function chartData(User $admin, Carbon $selectedDate, string $chartFilter): array
    {
        $chartLabels = [];
        $chartPresent = [];
        $chartLate = [];
        $chartExcused = [];
        $chartSick = [];
        $chartAbsent = [];
        $startDate = $selectedDate->copy()->subDays($this->resolvedChartRangeDays($chartFilter));
        $endDate = $selectedDate->copy();
        $period = CarbonPeriod::create($startDate, $endDate);
        $employeesCount = User::query()
            ->where('group', 'user')
            ->managedBy($admin)
            ->count();

        $periodSummary = Attendance::query()
            ->managedBy($admin)
            ->selectRaw("
                date,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN status = 'excused' AND approval_status = ? THEN 1 ELSE 0 END) as excused_count,
                SUM(CASE WHEN status = 'sick' AND approval_status = ? THEN 1 ELSE 0 END) as sick_count
            ", [Attendance::STATUS_APPROVED, Attendance::STATUS_APPROVED])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('date')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString());

        foreach ($period as $date) {
            $dateKey = $date->toDateString();
            $daySummary = $periodSummary->get($dateKey);

            $chartLabels[] = $date->format('d M');
            $present = (int) ($daySummary->present_count ?? 0);
            $late = (int) ($daySummary->late_count ?? 0);
            $excused = (int) ($daySummary->excused_count ?? 0);
            $sick = (int) ($daySummary->sick_count ?? 0);

            $chartPresent[] = $present;
            $chartLate[] = $late;
            $chartExcused[] = $excused;
            $chartSick[] = $sick;
            $chartAbsent[] = max(0, $employeesCount - ($present + $late + $excused + $sick));
        }

        return [
            'labels' => $chartLabels,
            'present' => $chartPresent,
            'late' => $chartLate,
            'excused' => $chartExcused,
            'sick' => $chartSick,
            'absent' => $chartAbsent,
        ];
    }

    public function statDetail(User $admin, Carbon $selectedDate, string $type): Collection
    {
        $selectedDateString = $selectedDate->toDateString();

        if ($type === 'absent') {
            return User::query()
                ->where('group', 'user')
                ->managedBy($admin)
                ->whereDoesntHave('attendances', fn (Builder $query) => $query->where('date', $selectedDateString))
                ->get();
        }

        $query = Attendance::query()
            ->managedBy($admin)
            ->with(['user', 'shift'])
            ->where('date', $selectedDateString);

        if ($type === 'early_checkout') {
            return $query->get()->filter(function (Attendance $attendance) {
                if (! $attendance->time_out || ! $attendance->shift) {
                    return false;
                }

                return $attendance->time_out->format('H:i:s') < $attendance->shift->end_time;
            })->values();
        }

        if ($type === 'checked_in') {
            return $query->whereIn('status', ['present', 'late'])->get();
        }

        return $query->where('status', $type)->get();
    }

    /**
     * @return array{leaves:int,attendance_corrections:int,reimbursements:int,overtimes:int,kasbon:int}
     */
    private function pendingCounts(User $admin, Collection $managedUserIds): array
    {
        return [
            'leaves' => $admin->can('manageLeaveApprovals')
                ? $this->pendingManagedCount(Attendance::query()->where('approval_status', 'pending'), $admin, $managedUserIds)
                : 0,
            'attendance_corrections' => $admin->can('manageAttendanceCorrections')
                ? $this->pendingManagedCount(AttendanceCorrection::query()->where('status', 'pending'), $admin, $managedUserIds)
                : 0,
            'reimbursements' => $admin->allowsAdminPermission('admin.reimbursements.approve')
                ? $this->pendingManagedCount(Reimbursement::query()->where('status', 'pending'), $admin, $managedUserIds)
                : 0,
            'overtimes' => $admin->can('manageOvertime')
                ? $this->pendingManagedCount(Overtime::query()->where('status', 'pending'), $admin, $managedUserIds)
                : 0,
            'kasbon' => $admin->can('manageCashAdvances')
                ? $this->pendingManagedCount(CashAdvance::query()->where('status', 'pending'), $admin, $managedUserIds)
                : 0,
        ];
    }

    private function pendingManagedCount(Builder $query, User $admin, Collection $managedUserIds): int
    {
        if ($admin->hasGlobalAdminScope()) {
            return $query->count();
        }

        return $query->whereIn('user_id', $managedUserIds)->count();
    }

    /**
     * @return Collection<int, string>
     */
    private function managedUserIds(User $admin): Collection
    {
        if ($admin->group === 'user') {
            return $admin->subordinates->pluck('id');
        }

        return User::query()->managedBy($admin)->pluck('id');
    }

    /**
     * @param  Collection<int, int>  $managedUserIds
     * @return Collection<int, int>
     */
    private function managedCompanyIds(User $admin, Collection $managedUserIds): Collection
    {
        if ($admin->hasGlobalAdminScope()) {
            return Company::query()->pluck('id')->map(fn ($id): int => (int) $id);
        }

        return User::query()
            ->whereIn('id', $managedUserIds->concat([$admin->id])->unique()->values())
            ->whereNotNull('company_id')
            ->distinct()
            ->pluck('company_id')
            ->map(fn ($id): int => (int) $id);
    }

    /**
     * @param  Collection<int, int>  $managedUserIds
     * @param  Collection<int, int>  $managedCompanyIds
     * @return array<string, int>
     */
    private function platformSignals(User $admin, Collection $managedUserIds, Collection $managedCompanyIds, string $selectedDateString): array
    {
        $scopedUserQuery = function (Builder $query) use ($admin, $managedUserIds): Builder {
            if ($admin->hasGlobalAdminScope()) {
                return $query;
            }

            return $query->whereIn('user_id', $managedUserIds);
        };

        $scopedCompanyQuery = function (Builder $query) use ($managedCompanyIds): Builder {
            return $query->whereIn('company_id', $managedCompanyIds);
        };

        return [
            'pending_wfh' => $admin->allowsAdminPermission('admin.wfh_requests.manage')
                ? WorkFromHomeRequest::query()
                    ->where('status', WorkFromHomeRequest::STATUS_PENDING)
                    ->tap($scopedUserQuery)
                    ->count()
                : 0,
            'overdue_hr_tasks' => $admin->can('viewAny', HrChecklistCase::class)
                ? HrChecklistTask::query()
                    ->reminderReady()
                    ->whereHas('case.user', fn (Builder $query) => $query->managedBy($admin))
                    ->count()
                : 0,
            'high_risk_attendance' => Attendance::query()
                ->managedBy($admin)
                ->where('date', $selectedDateString)
                ->whereIn('risk_level', ['medium', 'high'])
                ->count(),
            'pending_payroll' => $admin->allowsAdminPermission('admin.payroll.view')
                ? Payroll::query()
                    ->where('status', 'pending')
                    ->whereHas('user', fn (Builder $query) => $query->managedBy($admin))
                    ->count()
                : 0,
            'open_invoices' => $admin->allowsAdminPermission('admin.commercial.view')
                ? Invoice::query()
                    ->where('status', '!=', Invoice::STATUS_PAID)
                    ->tap($scopedCompanyQuery)
                    ->count()
                : 0,
            'open_vendor_bills' => $admin->allowsAdminPermission('admin.commercial.view')
                ? VendorBill::query()
                    ->whereNotIn('status', [VendorBill::STATUS_PAID, VendorBill::STATUS_CANCELLED])
                    ->tap($scopedCompanyQuery)
                    ->count()
                : 0,
            'active_sales_opportunities' => $admin->allowsAdminPermission('admin.commercial.view')
                ? SalesOpportunity::query()
                    ->whereIn('stage', [
                        SalesOpportunity::STAGE_LEAD,
                        SalesOpportunity::STAGE_QUALIFIED,
                        SalesOpportunity::STAGE_PROPOSAL,
                    ])
                    ->tap($scopedCompanyQuery)
                    ->count()
                : 0,
            'overdue_project_tasks' => $admin->allowsAdminPermission('admin.operations.view')
                ? ProjectTask::query()
                    ->whereIn('status', [ProjectTask::STATUS_TODO, ProjectTask::STATUS_IN_PROGRESS])
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', now()->toDateString())
                    ->tap($scopedCompanyQuery)
                    ->count()
                : 0,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function calendarLeaves(User $admin, Carbon $selectedDate): Collection
    {
        $today = now()->startOfDay();
        $monthStart = $selectedDate->copy()->startOfMonth();
        $monthEnd = $selectedDate->copy()->endOfMonth();

        if ($monthEnd->lt($today)) {
            return collect();
        }

        $startDate = $monthStart->lt($today) ? $today : $monthStart;

        $rawLeaves = Attendance::query()
            ->managedBy($admin)
            ->with(['user', 'leaveType'])
            ->whereBetween('date', [$startDate->toDateString(), $monthEnd->toDateString()])
            ->whereIn('status', ['sick', 'excused'])
            ->where('approval_status', 'approved')
            ->orderBy('user_id')
            ->orderBy('date')
            ->get();

        $calendarLeaves = collect();

        if ($rawLeaves->isEmpty()) {
            return $calendarLeaves;
        }

        $grouped = $rawLeaves->groupBy(fn (Attendance $attendance) => $attendance->user_id.'-'.$attendance->status.'-'.($attendance->leave_type_id ?? 'legacy'));

        foreach ($grouped as $group) {
            $tempGroup = [];

            foreach ($group as $leave) {
                if ($tempGroup === []) {
                    $tempGroup[] = $leave;

                    continue;
                }

                $last = end($tempGroup);

                if ($last->date->diffInDays($leave->date) === 1) {
                    $tempGroup[] = $leave;

                    continue;
                }

                $calendarLeaves->push($this->formatLeaveGroup($tempGroup));
                $tempGroup = [$leave];
            }

            if ($tempGroup !== []) {
                $calendarLeaves->push($this->formatLeaveGroup($tempGroup));
            }
        }

        return $calendarLeaves;
    }

    /**
     * @param  array<int, Attendance>  $leaves
     * @return array<string, mixed>
     */
    private function formatLeaveGroup(array $leaves): array
    {
        $first = $leaves[0];
        $last = end($leaves);
        $count = count($leaves);
        $dateDisplay = $first->date->format('d M');

        if ($count > 1) {
            $dateDisplay .= ' - '.$last->date->format('d M Y');
            $dateDisplay .= ' ('.$count.' days)';
        } else {
            $dateDisplay = $first->date->format('d M Y');
        }

        return [
            'title' => $first->user->name,
            'date_display' => $dateDisplay,
            'start_date' => $first->date,
            'status' => $first->status,
            'leave_type' => $first->leaveType?->name,
        ];
    }

    private function resolvedChartRangeDays(string $chartFilter): int
    {
        return match ($chartFilter) {
            'week_2' => 13,
            'week_3' => 20,
            'month_1', 'month' => 29,
            'month_2' => 59,
            'month_3' => 89,
            default => 6,
        };
    }
}
