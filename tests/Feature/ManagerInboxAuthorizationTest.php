<?php

use App\Livewire\Admin\ManagerInbox;
use App\Models\Attendance;
use App\Models\CashAdvance;
use App\Models\LeaveType;
use App\Models\Role;
use App\Models\User;
use App\Support\ManagerInboxService;
use Livewire\Livewire;

beforeEach(function () {
    $this->withoutVite();
    enableEnterpriseAttendanceForTests();
});

test('manager inbox only exposes tabs allowed by admin rbac permissions', function () {
    $admin = User::factory()->admin()->create();
    $role = Role::create([
        'name' => 'Leave Inbox Reviewer',
        'slug' => 'leave_inbox_reviewer',
        'description' => 'Can review leave requests from the manager inbox only.',
        'permissions' => [
            'admin.dashboard.view',
            'admin.leave_approvals.approve',
        ],
    ]);

    $admin->roles()->sync([$role->id]);

    Livewire::actingAs($admin->fresh())
        ->test(ManagerInbox::class)
        ->assertSee(__('Leaves'))
        ->assertDontSee(__('Cash Advances'))
        ->assertDontSee(__('Reimbursements'));

    expect(app(ManagerInboxService::class)->accessibleTabs($admin->fresh()))->toBe(['leaves']);
});

test('manager inbox is forbidden when admin has no reviewable modules', function () {
    $admin = User::factory()->admin()->create();
    $role = Role::create([
        'name' => 'Dashboard Only Inbox Regression',
        'slug' => 'dashboard_only_inbox_regression',
        'description' => 'Can access the admin dashboard only.',
        'permissions' => ['admin.dashboard.view'],
    ]);

    $admin->roles()->sync([$role->id]);

    $this->actingAs($admin->fresh())
        ->get(route('admin.inbox'))
        ->assertForbidden();
});

test('manager inbox rejects crafted tab changes outside admin rbac permissions', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create();
    $role = Role::create([
        'name' => 'Leave Only Crafted Inbox Regression',
        'slug' => 'leave_only_crafted_inbox_regression',
        'description' => 'Can review leaves, but not cash advances.',
        'permissions' => [
            'admin.dashboard.view',
            'admin.leave_approvals.approve',
        ],
    ]);

    $admin->roles()->sync([$role->id]);

    $advance = CashAdvance::create([
        'user_id' => $employee->id,
        'amount' => 200000,
        'purpose' => 'Travel advance',
        'payment_month' => (int) now()->month,
        'payment_year' => (int) now()->year,
        'status' => 'pending',
    ]);

    Livewire::actingAs($admin->fresh())
        ->test(ManagerInbox::class)
        ->set('activeTab', 'cash_advances')
        ->assertForbidden();

    expect($advance->fresh()->status)->toBe('pending');
});

test('manager inbox summarizes and filters overdue approvals', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->create();
    $leaveType = LeaveType::create([
        'code' => 'special_approval_test',
        'name' => 'Special Approval Test',
        'category' => LeaveType::CATEGORY_OTHER,
        'is_active' => true,
    ]);
    $role = Role::create([
        'name' => 'Leave Overdue Inbox Reviewer',
        'slug' => 'leave_overdue_inbox_reviewer',
        'description' => 'Can review overdue leave requests from the manager inbox.',
        'permissions' => [
            'admin.dashboard.view',
            'admin.leave_approvals.approve',
        ],
    ]);

    $admin->roles()->sync([$role->id]);

    $attendance = Attendance::create([
        'user_id' => $employee->id,
        'date' => now()->toDateString(),
        'status' => 'excused',
        'approval_status' => 'pending',
        'leave_type_id' => $leaveType->id,
    ]);
    $attendance->forceFill([
        'created_at' => now()->subDays(3),
        'updated_at' => now()->subDays(3),
    ])->save();

    Livewire::actingAs($admin->fresh())
        ->test(ManagerInbox::class)
        ->assertSee(__('Overdue'))
        ->assertSet('statusFilter', 'pending')
        ->call('setStatusFilter', 'overdue')
        ->assertSet('statusFilter', 'overdue')
        ->assertSee($employee->name);
});
