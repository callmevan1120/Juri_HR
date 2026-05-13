<?php

use App\Livewire\Admin\ReimbursementManager;
use App\Livewire\User\Finance\TeamCashAdvanceManager;
use App\Livewire\User\TeamApprovals;
use App\Models\ApprovalMatrixRule;
use App\Models\CashAdvance;
use App\Models\Division;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\Reimbursement;
use App\Models\Role;
use App\Models\User;
use App\Notifications\CashAdvanceUpdated;
use App\Notifications\ReimbursementStatusUpdated;
use App\Support\CashAdvanceApprovalService;
use App\Support\ReimbursementApprovalService;
use App\Support\TeamApprovalQueryService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

function createApprovalHierarchy(string $divisionName = 'Operations'): array
{
    $division = Division::create(['name' => $divisionName]);

    $managerLevel = JobLevel::create(['name' => 'Manager', 'rank' => 2]);
    $staffLevel = JobLevel::create(['name' => 'Staff', 'rank' => 4]);

    $managerTitle = JobTitle::create([
        'name' => $divisionName.' Manager',
        'job_level_id' => $managerLevel->id,
        'division_id' => $division->id,
    ]);

    $staffTitle = JobTitle::create([
        'name' => $divisionName.' Staff',
        'job_level_id' => $staffLevel->id,
        'division_id' => $division->id,
    ]);

    $manager = User::factory()->create([
        'division_id' => $division->id,
        'job_title_id' => $managerTitle->id,
    ]);

    $employee = User::factory()->create([
        'division_id' => $division->id,
        'job_title_id' => $staffTitle->id,
    ]);

    return [$manager, $employee, $division, $managerTitle, $staffTitle];
}

function createFinanceHead(bool $admin = false): User
{
    $division = Division::create(['name' => 'Finance']);
    $level = JobLevel::create(['name' => 'Finance Head', 'rank' => 2]);
    $title = JobTitle::create([
        'name' => 'Finance Manager',
        'job_level_id' => $level->id,
        'division_id' => $division->id,
    ]);

    $factory = $admin ? User::factory()->admin() : User::factory();

    return $factory->create([
        'division_id' => $division->id,
        'job_title_id' => $title->id,
    ]);
}

test('supervisor approval forwards reimbursement to finance', function () {
    Notification::fake();

    [$manager, $employee] = createApprovalHierarchy();

    $reimbursement = Reimbursement::create([
        'user_id' => $employee->id,
        'date' => now()->toDateString(),
        'type' => 'Transport',
        'amount' => 150000,
        'description' => 'Airport pickup',
        'status' => 'pending',
    ]);

    $this->actingAs($manager);

    Livewire::test(TeamApprovals::class)
        ->set('activeTab', 'reimbursements')
        ->call('approveReimbursement', $reimbursement->id);

    $reimbursement->refresh();

    expect($reimbursement->status)->toBe('pending_finance')
        ->and($reimbursement->head_approved_by)->toBe($manager->id)
        ->and($reimbursement->head_approved_at)->not->toBeNull()
        ->and($reimbursement->finance_approved_by)->toBeNull();

    Notification::assertSentTo($employee, ReimbursementStatusUpdated::class);
});

test('team approval history keeps finance-forwarded requests visible to supervisors', function () {
    [$manager, $employee] = createApprovalHierarchy();

    $reimbursement = Reimbursement::create([
        'user_id' => $employee->id,
        'date' => now()->toDateString(),
        'type' => 'Meal',
        'amount' => 50000,
        'description' => 'Client lunch',
        'status' => 'pending_finance',
        'head_approved_by' => $manager->id,
        'head_approved_at' => now(),
    ]);

    $advance = CashAdvance::create([
        'user_id' => $employee->id,
        'amount' => 300000,
        'purpose' => 'Team transport advance',
        'payment_month' => (int) now()->month,
        'payment_year' => (int) now()->year,
        'status' => 'pending_finance',
        'head_approved_by' => $manager->id,
        'head_approved_at' => now(),
    ]);

    $service = app(TeamApprovalQueryService::class);

    $reimbursementHistory = collect($service->history($manager, 'reimbursements')->items());
    $cashAdvanceHistory = collect($service->history($manager, 'kasbons')->items());

    expect($reimbursementHistory->pluck('id'))->toContain($reimbursement->id)
        ->and($cashAdvanceHistory->pluck('id'))->toContain($advance->id);
});

test('supervisor approval forwards cash advance to finance', function () {
    Notification::fake();

    [$manager, $employee] = createApprovalHierarchy();

    $advance = CashAdvance::create([
        'user_id' => $employee->id,
        'amount' => 700000,
        'purpose' => 'Project field advance',
        'payment_month' => (int) now()->month,
        'payment_year' => (int) now()->year,
        'status' => 'pending',
    ]);

    $this->actingAs($manager);

    Livewire::test(TeamApprovals::class)
        ->set('activeTab', 'kasbons')
        ->call('approveKasbon', $advance->id);

    $advance->refresh();

    expect($advance->status)->toBe('pending_finance')
        ->and($advance->head_approved_by)->toBe($manager->id)
        ->and($advance->head_approved_at)->not->toBeNull()
        ->and($advance->approved_by)->toBeNull();

    Notification::assertSentTo($employee, CashAdvanceUpdated::class);
});

test('finance head can finalize pending finance reimbursements from manager queue', function () {
    Notification::fake();

    [, $employee] = createApprovalHierarchy();
    $financeHead = createFinanceHead(true);

    $reimbursement = Reimbursement::create([
        'user_id' => $employee->id,
        'date' => now()->toDateString(),
        'type' => 'Hotel',
        'amount' => 450000,
        'description' => 'Site visit stay',
        'status' => 'pending_finance',
    ]);

    $this->actingAs($financeHead);

    Livewire::test(ReimbursementManager::class)
        ->set('statusFilter', 'pending_finance')
        ->call('approve', $reimbursement->id);

    $reimbursement->refresh();

    expect($reimbursement->status)->toBe('approved')
        ->and($reimbursement->finance_approved_by)->toBe($financeHead->id)
        ->and($reimbursement->finance_approved_at)->not->toBeNull()
        ->and($reimbursement->approved_by)->toBe($financeHead->id);

    Notification::assertSentTo($employee, ReimbursementStatusUpdated::class);
});

test('approval matrix can require manager finance and hr role for high value reimbursement', function () {
    Notification::fake();

    [$manager, $employee] = createApprovalHierarchy();
    $financeHead = createFinanceHead();
    $hr = User::factory()->create();
    $hrRole = Role::create([
        'name' => 'HR Head Matrix',
        'slug' => 'hr_head_matrix',
        'description' => 'Can approve HR matrix steps.',
        'permissions' => [],
    ]);
    $hr->roles()->sync([$hrRole->id]);

    ApprovalMatrixRule::create([
        'workflow' => ApprovalMatrixRule::WORKFLOW_REIMBURSEMENT,
        'name' => 'High value reimbursement',
        'priority' => 100,
        'conditions' => ['min_amount' => 5000000],
        'steps' => [
            ['key' => 'direct_manager', 'label' => 'Direct Manager', 'approver_type' => 'direct_manager'],
            ['key' => 'finance_head', 'label' => 'Finance Head', 'approver_type' => 'finance_head'],
            ['key' => 'hr_head', 'label' => 'HR Head', 'approver_type' => 'role', 'role' => 'hr_head_matrix'],
        ],
    ]);

    $reimbursement = Reimbursement::create([
        'user_id' => $employee->id,
        'date' => now()->toDateString(),
        'type' => 'Travel',
        'amount' => 6000000,
        'description' => 'Out of town implementation.',
        'status' => 'pending',
    ]);

    app(ReimbursementApprovalService::class)->approve($reimbursement, $manager);
    $reimbursement->refresh();

    expect($reimbursement->status)->toBe('pending_finance')
        ->and($reimbursement->approval_current_step)->toBe('finance_head')
        ->and($reimbursement->head_approved_by)->toBe($manager->id);

    app(ReimbursementApprovalService::class)->approve($reimbursement, $financeHead);
    $reimbursement->refresh();

    expect($reimbursement->status)->toBe('pending_matrix')
        ->and($reimbursement->approval_current_step)->toBe('hr_head')
        ->and($reimbursement->finance_approved_by)->toBe($financeHead->id)
        ->and(Gate::forUser($hr)->allows('approve', $reimbursement))->toBeTrue();

    app(ReimbursementApprovalService::class)->approve($reimbursement, $hr);
    $reimbursement->refresh();

    expect($reimbursement->status)->toBe('approved')
        ->and($reimbursement->approval_current_step)->toBeNull()
        ->and($reimbursement->approved_by)->toBe($hr->id)
        ->and($reimbursement->approval_completed_steps)->toHaveCount(3);
});

test('team cash advance manager allows authorized supervisor to approve subordinate request', function () {
    enableEnterpriseAttendanceForTests();

    Notification::fake();

    [$manager, $employee] = createApprovalHierarchy();

    $advance = CashAdvance::create([
        'user_id' => $employee->id,
        'amount' => 450000,
        'purpose' => 'Site transport advance',
        'payment_month' => (int) now()->month,
        'payment_year' => (int) now()->year,
        'status' => 'pending',
    ]);

    $this->actingAs($manager);

    Livewire::test(TeamCashAdvanceManager::class)
        ->call('approve', $advance->id);

    $advance->refresh();

    expect($advance->status)->toBe('pending_finance')
        ->and($advance->head_approved_by)->toBe($manager->id)
        ->and($advance->head_approved_at)->not->toBeNull();

    Notification::assertSentTo($employee, CashAdvanceUpdated::class);
});

test('approval matrix can route cash advance through manager and finance', function () {
    enableEnterpriseAttendanceForTests();
    Notification::fake();

    [$manager, $employee] = createApprovalHierarchy();
    $financeHead = createFinanceHead();

    ApprovalMatrixRule::create([
        'workflow' => ApprovalMatrixRule::WORKFLOW_CASH_ADVANCE,
        'name' => 'Large kasbon',
        'priority' => 100,
        'conditions' => ['min_amount' => 1000000],
        'steps' => [
            ['key' => 'direct_manager', 'label' => 'Direct Manager', 'approver_type' => 'direct_manager'],
            ['key' => 'finance_head', 'label' => 'Finance Head', 'approver_type' => 'finance_head'],
        ],
    ]);

    $advance = CashAdvance::create([
        'user_id' => $employee->id,
        'amount' => 1500000,
        'purpose' => 'Emergency project float',
        'payment_month' => (int) now()->month,
        'payment_year' => (int) now()->year,
        'status' => 'pending',
    ]);

    app(CashAdvanceApprovalService::class)->approve($advance, $manager);
    $advance->refresh();

    expect($advance->status)->toBe('pending_finance')
        ->and($advance->approval_current_step)->toBe('finance_head')
        ->and($advance->head_approved_by)->toBe($manager->id);

    app(CashAdvanceApprovalService::class)->approve($advance, $financeHead);
    $advance->refresh();

    expect($advance->status)->toBe('approved')
        ->and($advance->approved_by)->toBe($financeHead->id)
        ->and($advance->finance_approved_by)->toBe($financeHead->id)
        ->and($advance->approval_completed_steps)->toHaveCount(2);
});

test('team cash advance manager forbids unrelated users from approving subordinate request', function () {
    enableEnterpriseAttendanceForTests();

    [, $employee] = createApprovalHierarchy();
    $unrelated = User::factory()->create([
        'division_id' => null,
        'job_title_id' => null,
    ]);

    $advance = CashAdvance::create([
        'user_id' => $employee->id,
        'amount' => 325000,
        'purpose' => 'Equipment pickup',
        'payment_month' => (int) now()->month,
        'payment_year' => (int) now()->year,
        'status' => 'pending',
    ]);

    $this->actingAs($unrelated);

    Livewire::test(TeamCashAdvanceManager::class)
        ->call('approve', $advance->id)
        ->assertForbidden();

    expect($advance->fresh()->status)->toBe('pending');
});
