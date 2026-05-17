<?php

use App\Livewire\Admin\LeaveEntitlementManager;
use App\Models\LeaveEntitlement;
use App\Models\Role;
use App\Models\User;
use App\Support\MultiCompanyService;
use Livewire\Livewire;

test('superadmin can assign annual leave entitlement with expiry', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Leave Entitlement');
    $employee = User::factory()->create([
        'company_id' => $company->id,
    ]);

    $this->actingAs($superadmin);

    Livewire::test(LeaveEntitlementManager::class)
        ->set('userId', $employee->id)
        ->set('year', now()->year)
        ->set('allocatedDays', '12')
        ->set('carriedOverDays', '2')
        ->set('expiresAt', now()->endOfYear()->toDateString())
        ->set('notes', 'Annual entitlement')
        ->call('save')
        ->assertHasNoErrors();

    $entitlement = LeaveEntitlement::query()->where('user_id', $employee->id)->firstOrFail();

    expect($entitlement->company_id)->toBe($company->id)
        ->and($entitlement->allocated_days)->toBe('12.00')
        ->and($entitlement->carried_over_days)->toBe('2.00')
        ->and($entitlement->expires_at?->toDateString())->toBe(now()->endOfYear()->toDateString());
});

test('tenant scoped admin cannot assign entitlement to another company employee', function () {
    $admin = User::factory()->admin()->create();
    $companyA = app(MultiCompanyService::class)->createCompany('PT Leave A', $admin);
    $companyB = app(MultiCompanyService::class)->createCompany('PT Leave B');
    $employeeB = User::factory()->create([
        'company_id' => $companyB->id,
    ]);

    $role = Role::query()->create([
        'name' => 'Leave Entitlement Manager',
        'slug' => 'leave_entitlement_manager',
        'permissions' => ['admin.leave_entitlements.manage'],
    ]);
    $admin->roles()->sync([$role->id]);

    $this->actingAs($admin->fresh());

    Livewire::test(LeaveEntitlementManager::class)
        ->set('userId', $employeeB->id)
        ->set('year', now()->year)
        ->set('allocatedDays', '12')
        ->set('expiresAt', now()->endOfYear()->toDateString())
        ->call('save')
        ->assertForbidden();

    expect(LeaveEntitlement::query()->where('user_id', $employeeB->id)->exists())->toBeFalse()
        ->and($admin->fresh()->company_id)->toBe($companyA->id);
});

test('leave entitlement route requires explicit permission', function () {
    $admin = User::factory()->admin()->create();
    $admin->roles()->detach();

    $this->actingAs($admin)
        ->get(route('admin.masters.leave-entitlements'))
        ->assertForbidden();

    $role = Role::query()->create([
        'name' => 'Leave Entitlement Viewer',
        'slug' => 'leave_entitlement_viewer',
        'permissions' => ['admin.leave_entitlements.manage'],
    ]);
    $admin->roles()->sync([$role->id]);

    $this->actingAs($admin->fresh())
        ->get(route('admin.masters.leave-entitlements'))
        ->assertOk();
});
