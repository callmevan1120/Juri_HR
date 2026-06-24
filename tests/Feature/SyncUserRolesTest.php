<?php

use App\Actions\Hr\SyncUserRoles;
use App\Models\Role;
use App\Models\User;
use App\Support\RbacRegistry;
use Illuminate\Auth\Access\AuthorizationException;

function syncUserRoles(): SyncUserRoles
{
    return app(SyncUserRoles::class);
}

function makeAdminRole(string $slug = 'settings_only'): Role
{
    return Role::create([
        'name' => 'Settings Only',
        'slug' => $slug,
        'description' => 'Can only access admin settings.',
        'permissions' => ['admin.settings.view'],
    ]);
}

test('a superadmin can assign a role and the resolved state is returned', function () {
    $actor = User::factory()->admin(true)->create();
    $subject = User::factory()->admin()->create();
    $role = makeAdminRole();

    $result = syncUserRoles()->handle($subject, $actor, [$role->id], []);

    expect($result['changed'])->toBeTrue();
    expect($result['role_ids'])->toBe([$role->id]);
    expect($result['original_role_ids'])->toBe([$role->id]);
    expect($subject->fresh()->roles()->pluck('roles.id')->all())->toBe([$role->id]);
});

test('an unchanged role selection is a no-op', function () {
    $actor = User::factory()->admin(true)->create();
    $subject = User::factory()->admin()->create();
    $role = makeAdminRole();
    $subject->roles()->sync([$role->id]);

    $result = syncUserRoles()->handle($subject, $actor, [$role->id], [$role->id]);

    expect($result['changed'])->toBeFalse();
    expect($result['role_ids'])->toBe([$role->id]);
});

test('an invalid role id is rejected', function () {
    $actor = User::factory()->admin(true)->create();
    $subject = User::factory()->admin()->create();

    expect(fn () => syncUserRoles()->handle($subject, $actor, ['non-existent-id'], []))
        ->toThrow(AuthorizationException::class, __('One or more selected roles are invalid.'));
});

test('an actor without assign permission cannot assign roles', function () {
    $actor = User::factory()->create(); // plain employee, no admin permissions
    $subject = User::factory()->admin()->create();
    $role = makeAdminRole();

    expect(fn () => syncUserRoles()->handle($subject, $actor, [$role->id], []))
        ->toThrow(AuthorizationException::class, __('You do not have permission to assign roles.'));
});

test('an actor cannot change their own role assignment', function () {
    $actor = User::factory()->admin(true)->create();
    $role = makeAdminRole();

    expect(fn () => syncUserRoles()->handle($actor, $actor, [$role->id], []))
        ->toThrow(AuthorizationException::class, __('You cannot change your own role assignment.'));
});

test('assigning a full-admin role requires super admin management permission', function () {
    $assignerRole = Role::create([
        'name' => 'Role Assigner',
        'slug' => 'role_assigner',
        'description' => 'Can assign roles but not manage super admins.',
        'permissions' => ['admin.rbac.assign'],
    ]);
    $actor = User::factory()->admin()->create();
    $actor->roles()->sync([$assignerRole->id]);

    $subject = User::factory()->admin()->create();
    $fullAdminRole = Role::create([
        'name' => 'Full Admin',
        'slug' => 'full_admin',
        'description' => 'Grants every permission.',
        'permissions' => RbacRegistry::permissionKeys(),
    ]);

    expect(fn () => syncUserRoles()->handle($subject, $actor, [$fullAdminRole->id], []))
        ->toThrow(AuthorizationException::class, __('You do not have permission to assign the Super Admin role.'));
});

test('assigning a full-admin role promotes the subject group to superadmin', function () {
    $actor = User::factory()->admin(true)->create();
    $subject = User::factory()->admin()->create();
    $fullAdminRole = Role::create([
        'name' => 'Full Admin',
        'slug' => 'full_admin',
        'description' => 'Grants every permission.',
        'permissions' => RbacRegistry::permissionKeys(),
    ]);

    $result = syncUserRoles()->handle($subject, $actor, [$fullAdminRole->id], []);

    expect($result['changed'])->toBeTrue();
    expect($result['group'])->toBe('superadmin');
    expect($subject->fresh()->group)->toBe('superadmin');
});

test('an admin with no explicit role receives the default admin role', function () {
    $actor = User::factory()->admin(true)->create();
    $subject = User::factory()->admin()->create();
    $defaultAdminRole = Role::query()->where('slug', 'admin')->firstOrFail();

    $result = syncUserRoles()->handle($subject, $actor, [], []);

    expect($result['changed'])->toBeTrue();
    expect($result['role_ids'])->toBe([$defaultAdminRole->id]);
    expect($subject->fresh()->roles()->pluck('roles.id')->all())->toBe([$defaultAdminRole->id]);
});
