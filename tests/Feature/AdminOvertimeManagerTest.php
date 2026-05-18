<?php

use App\Livewire\Admin\OvertimeManager;
use App\Models\Overtime;
use App\Models\User;
use App\Support\OvertimeApprovalService;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Livewire;

test('admin overtime manager renders existing overtime requests', function () {
    $admin = User::factory()->admin(true)->create();
    $employee = User::factory()->create(['name' => 'Existing Overtime Employee']);

    Overtime::create([
        'user_id' => $employee->id,
        'date' => now()->toDateString(),
        'start_time' => now()->setTime(18, 0),
        'end_time' => now()->setTime(20, 0),
        'duration' => 120,
        'reason' => 'Valid overtime',
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    Livewire::test(OvertimeManager::class)
        ->assertSee('Existing Overtime Employee');
});

test('overtime approval service rejects already reviewed requests', function () {
    $admin = User::factory()->admin(true)->create();
    $employee = User::factory()->create();

    $overtime = Overtime::create([
        'user_id' => $employee->id,
        'date' => now()->toDateString(),
        'start_time' => now()->setTime(18, 0),
        'end_time' => now()->setTime(20, 0),
        'duration' => 120,
        'reason' => 'Already approved overtime',
        'status' => 'approved',
        'approved_by' => $admin->id,
    ]);

    expect(fn () => app(OvertimeApprovalService::class)->approve($overtime, $admin))
        ->toThrow(AuthorizationException::class);

    expect($overtime->fresh()->status)->toBe('approved');
});
