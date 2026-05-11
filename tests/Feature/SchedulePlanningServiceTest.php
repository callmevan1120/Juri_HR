<?php

use App\Models\Division;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\User;
use App\Support\SchedulePlanningService;

test('schedule planning bulk assigns users across a date range', function () {
    $division = Division::create(['name' => 'Field Operations']);
    $users = User::factory()->count(2)->create(['division_id' => $division->id]);
    $shift = Shift::create(['name' => 'Morning', 'start_time' => '07:00', 'end_time' => '15:00']);

    $result = app(SchedulePlanningService::class)->bulkAssign(
        $users->pluck('id')->all(),
        $shift->id,
        '2026-06-01',
        '2026-06-03',
    );

    expect($result['assigned_count'])->toBe(6)
        ->and($result['skipped_count'])->toBe(0)
        ->and(Schedule::query()->where('shift_id', $shift->id)->count())->toBe(6);
});

test('schedule planning detects existing schedules and shift swap conflicts', function () {
    $employee = User::factory()->create();
    $morning = Shift::create(['name' => 'Morning', 'start_time' => '07:00', 'end_time' => '15:00']);
    $afternoon = Shift::create(['name' => 'Afternoon', 'start_time' => '15:00', 'end_time' => '23:00']);

    Schedule::create([
        'user_id' => $employee->id,
        'shift_id' => $morning->id,
        'date' => '2026-06-02',
    ]);

    ShiftSwapRequest::create([
        'user_id' => $employee->id,
        'schedule_date' => '2026-06-03',
        'requested_shift_id' => $afternoon->id,
        'reason' => 'Need afternoon coverage.',
        'status' => ShiftSwapRequest::STATUS_PENDING,
    ]);

    $conflicts = app(SchedulePlanningService::class)->detectConflicts(
        [$employee->id],
        '2026-06-01',
        '2026-06-03',
    );

    expect($conflicts)->toHaveCount(2)
        ->and($conflicts[0]['type'])->toBe('existing_schedule')
        ->and($conflicts[0]['date'])->toBe('2026-06-02')
        ->and($conflicts[1]['type'])->toBe('shift_swap_request')
        ->and($conflicts[1]['date'])->toBe('2026-06-03');

    $result = app(SchedulePlanningService::class)->bulkAssign(
        [$employee->id],
        $afternoon->id,
        '2026-06-01',
        '2026-06-03',
    );

    expect($result['assigned_count'])->toBe(1)
        ->and($result['skipped_count'])->toBe(2)
        ->and(Schedule::query()->where('user_id', $employee->id)->count())->toBe(2);
});

test('schedule planning summarizes capacity by division and shift', function () {
    $operations = Division::create(['name' => 'Operations']);
    $finance = Division::create(['name' => 'Finance']);
    $morning = Shift::create(['name' => 'Morning', 'start_time' => '07:00', 'end_time' => '15:00']);
    $night = Shift::create(['name' => 'Night', 'start_time' => '23:00', 'end_time' => '07:00']);
    $operationsUsers = User::factory()->count(3)->create(['division_id' => $operations->id]);
    $financeUser = User::factory()->create(['division_id' => $finance->id]);

    Schedule::create(['user_id' => $operationsUsers[0]->id, 'shift_id' => $morning->id, 'date' => '2026-06-04']);
    Schedule::create(['user_id' => $operationsUsers[1]->id, 'shift_id' => $morning->id, 'date' => '2026-06-04']);
    Schedule::create(['user_id' => $operationsUsers[2]->id, 'shift_id' => $night->id, 'date' => '2026-06-04', 'is_off' => true]);
    Schedule::create(['user_id' => $financeUser->id, 'shift_id' => $morning->id, 'date' => '2026-06-04']);

    $capacity = app(SchedulePlanningService::class)->capacityByDivision($operations->id, '2026-06-04');

    expect($capacity)->toHaveCount(2)
        ->and($capacity[0]['shift_name'])->toBe('Morning')
        ->and($capacity[0]['scheduled_count'])->toBe(2)
        ->and($capacity[1]['shift_name'])->toBe('Night')
        ->and($capacity[1]['scheduled_count'])->toBe(1)
        ->and($capacity[1]['off_count'])->toBe(1);
});
