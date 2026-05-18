<?php

use App\Livewire\User\TeamApprovals;
use App\Livewire\User\WorkFromHomeRequestPage;
use App\Models\User;
use App\Models\WorkFromHomeRequest;
use App\Support\MultiCompanyService;
use App\Support\WorkFromHomeRequestService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

test('employee can submit work from home request and manager can approve it', function () {
    $manager = User::factory()->create();
    $employee = User::factory()->create(['manager_id' => $manager->id]);
    $company = app(MultiCompanyService::class)->createCompany('PT WFH Flow', $manager);
    app(MultiCompanyService::class)->assignUser($employee, $company);

    $this->actingAs($employee)
        ->get(route('wfh-requests'))
        ->assertOk();

    Livewire::actingAs($employee)
        ->test(WorkFromHomeRequestPage::class)
        ->set('date', now()->addDay()->toDateString())
        ->set('startTime', '09:00')
        ->set('endTime', '17:00')
        ->set('locationAddress', 'Home Office')
        ->set('reason', 'Need focused remote work for client documentation.')
        ->call('submit')
        ->assertHasNoErrors();

    $request = WorkFromHomeRequest::query()->firstOrFail();

    expect($request->user_id)->toBe($employee->id)
        ->and($request->company_id)->toBe($company->id)
        ->and($request->status)->toBe(WorkFromHomeRequest::STATUS_PENDING);

    $this->actingAs($manager)
        ->get(route('approvals').'?activeTab=wfh')
        ->assertOk();

    Livewire::actingAs($manager)
        ->test(TeamApprovals::class, ['activeTab' => 'wfh'])
        ->set('activeTab', 'wfh')
        ->assertSee('Home Office')
        ->call('approveWfh', $request->id);

    $request->refresh();

    expect($request->status)->toBe(WorkFromHomeRequest::STATUS_APPROVED)
        ->and($request->reviewed_by)->toBe($manager->id)
        ->and($request->reviewed_at)->not->toBeNull();
});

test('unrelated user cannot approve another employee wfh request', function () {
    $manager = User::factory()->create();
    $employee = User::factory()->create(['manager_id' => $manager->id]);
    $otherUser = User::factory()->create();
    User::factory()->create(['manager_id' => $otherUser->id]);
    $company = app(MultiCompanyService::class)->createCompany('PT WFH Guard', $manager);
    app(MultiCompanyService::class)->assignUser($employee, $company);

    $request = WorkFromHomeRequest::query()->create([
        'user_id' => $employee->id,
        'company_id' => $company->id,
        'date' => now()->addDay()->toDateString(),
        'reason' => 'Remote work guard test request.',
        'status' => WorkFromHomeRequest::STATUS_PENDING,
    ]);

    expect(Gate::forUser($otherUser)->denies('approve', $request))->toBeTrue();

    Livewire::actingAs($otherUser)
        ->test(TeamApprovals::class, ['activeTab' => 'wfh'])
        ->set('activeTab', 'wfh')
        ->call('approveWfh', $request->id);

    expect($request->fresh()->status)->toBe(WorkFromHomeRequest::STATUS_PENDING);
});

test('wfh approval service rejects stale reviewed request', function () {
    $manager = User::factory()->create();
    $employee = User::factory()->create(['manager_id' => $manager->id]);
    $company = app(MultiCompanyService::class)->createCompany('PT WFH Stale', $manager);
    app(MultiCompanyService::class)->assignUser($employee, $company);

    $request = WorkFromHomeRequest::query()->create([
        'user_id' => $employee->id,
        'company_id' => $company->id,
        'date' => now()->addDay()->toDateString(),
        'reason' => 'Already reviewed request.',
        'status' => WorkFromHomeRequest::STATUS_APPROVED,
        'reviewed_by' => $manager->id,
        'reviewed_at' => now(),
    ]);

    expect(fn () => app(WorkFromHomeRequestService::class)->approve($request, $manager))
        ->toThrow(AuthorizationException::class);

    expect($request->fresh()->status)->toBe(WorkFromHomeRequest::STATUS_APPROVED);
});
