<?php

namespace App\Support;

use App\Models\Schedule;
use App\Models\ShiftSwapRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SchedulePlanningService
{
    /**
     * @param  array<int, string|int>  $userIds
     * @return array{assigned_count: int, skipped_count: int, conflicts: array<int, array<string, mixed>>}
     */
    public function bulkAssign(
        array $userIds,
        int $shiftId,
        string|\DateTimeInterface $startDate,
        string|\DateTimeInterface $endDate,
        bool $isOff = false,
        bool $overwrite = false,
    ): array {
        $userIds = $this->normalizeUserIds($userIds);
        $dates = $this->datesBetween($startDate, $endDate);
        $conflicts = $overwrite ? [] : $this->detectConflicts($userIds, $startDate, $endDate);
        $conflictKeys = collect($conflicts)
            ->mapWithKeys(fn (array $conflict): array => [$this->conflictKey($conflict['user_id'], $conflict['date']) => true]);

        $assigned = 0;

        DB::transaction(function () use ($userIds, $dates, $shiftId, $isOff, $overwrite, $conflictKeys, &$assigned): void {
            foreach ($userIds as $userId) {
                foreach ($dates as $date) {
                    if (! $overwrite && $conflictKeys->has($this->conflictKey($userId, $date))) {
                        continue;
                    }

                    Schedule::query()->updateOrCreate(
                        ['user_id' => $userId, 'date' => $date],
                        ['shift_id' => $shiftId, 'is_off' => $isOff],
                    );

                    $assigned++;
                }
            }
        });

        return [
            'assigned_count' => $assigned,
            'skipped_count' => count($conflicts),
            'conflicts' => $conflicts,
        ];
    }

    /**
     * @param  array<int, string|int>  $userIds
     * @return array<int, array<string, mixed>>
     */
    public function detectConflicts(
        array $userIds,
        string|\DateTimeInterface $startDate,
        string|\DateTimeInterface $endDate,
    ): array {
        $userIds = $this->normalizeUserIds($userIds);
        $dates = $this->datesBetween($startDate, $endDate);

        if ($userIds === [] || $dates === []) {
            return [];
        }

        $conflicts = collect();

        Schedule::query()
            ->with('shift:id,name')
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [reset($dates), end($dates)])
            ->get()
            ->each(function (Schedule $schedule) use ($conflicts): void {
                $conflicts->push([
                    'type' => 'existing_schedule',
                    'user_id' => $schedule->user_id,
                    'date' => $schedule->date?->toDateString(),
                    'schedule_id' => $schedule->id,
                    'shift_id' => $schedule->shift_id,
                    'shift_name' => $schedule->shift?->name,
                ]);
            });

        ShiftSwapRequest::query()
            ->with(['requestedShift:id,name', 'schedule:id,date'])
            ->whereIn('user_id', $userIds)
            ->whereIn('status', [ShiftSwapRequest::STATUS_PENDING, ShiftSwapRequest::STATUS_APPROVED])
            ->get()
            ->filter(function (ShiftSwapRequest $request) use ($dates): bool {
                $date = $request->effectiveScheduleDate()?->toDateString();

                return $date !== null && in_array($date, $dates, true);
            })
            ->each(function (ShiftSwapRequest $request) use ($conflicts): void {
                $conflicts->push([
                    'type' => 'shift_swap_request',
                    'user_id' => $request->user_id,
                    'date' => $request->effectiveScheduleDate()?->toDateString(),
                    'shift_swap_request_id' => $request->id,
                    'status' => $request->status,
                    'requested_shift_id' => $request->requested_shift_id,
                    'requested_shift_name' => $request->requestedShift?->name,
                ]);
            });

        return $conflicts
            ->filter(fn (array $conflict): bool => filled($conflict['date'] ?? null))
            ->sortBy(fn (array $conflict): string => ($conflict['date'] ?? '').'|'.($conflict['user_id'] ?? '').'|'.($conflict['type'] ?? ''))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function capacityByDivision(int $divisionId, string|\DateTimeInterface $date): array
    {
        $userIds = User::query()
            ->where('division_id', $divisionId)
            ->pluck('id')
            ->all();

        return $this->capacityByShift($date, $userIds);
    }

    /**
     * @param  array<int, string|int>|null  $userIds
     * @return array<int, array<string, mixed>>
     */
    public function capacityByShift(string|\DateTimeInterface $date, ?array $userIds = null): array
    {
        $date = $this->toDate($date);

        /** @var EloquentCollection<int, Schedule> $schedules */
        $schedules = Schedule::query()
            ->with(['shift:id,name,start_time,end_time', 'user:id,name,division_id'])
            ->whereDate('date', $date)
            ->when($userIds !== null, fn ($query) => $query->whereIn('user_id', $this->normalizeUserIds($userIds)))
            ->get();

        return $schedules
            ->groupBy('shift_id')
            ->map(function (Collection $items, int|string $shiftId): array {
                /** @var Schedule|null $first */
                $first = $items->first();

                return [
                    'shift_id' => (int) $shiftId,
                    'shift_name' => $first?->shift?->name,
                    'scheduled_count' => $items->count(),
                    'off_count' => $items->where('is_off', true)->count(),
                    'division_ids' => $items
                        ->pluck('user.division_id')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all(),
                ];
            })
            ->sortBy('shift_name')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string|int>  $userIds
     * @return array<int, string|int>
     */
    protected function normalizeUserIds(array $userIds): array
    {
        return collect($userIds)
            ->filter(fn ($userId): bool => filled($userId))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function datesBetween(string|\DateTimeInterface $startDate, string|\DateTimeInterface $endDate): array
    {
        $start = CarbonImmutable::parse($startDate)->startOfDay();
        $end = CarbonImmutable::parse($endDate)->startOfDay();

        if ($end->lessThan($start)) {
            [$start, $end] = [$end, $start];
        }

        return collect(CarbonPeriod::create($start, $end))
            ->map(fn ($date): string => CarbonImmutable::parse($date)->toDateString())
            ->values()
            ->all();
    }

    protected function toDate(string|\DateTimeInterface $date): string
    {
        return CarbonImmutable::parse($date)->toDateString();
    }

    protected function conflictKey(string|int $userId, string $date): string
    {
        return $userId.'|'.$date;
    }
}
