<?php

use App\Models\Role;

test('demo admin role includes toko pos permissions from migrations', function () {
    $role = Role::query()->where('slug', 'demo_admin_readonly')->firstOrFail();

    expect($role->permissions ?? [])->toContain(
        'admin.toko_pos.view',
        'admin.toko_pos.export',
    );
});

test('toko permission repair migration updates stale demo admin roles', function () {
    $role = Role::query()->where('slug', 'demo_admin_readonly')->firstOrFail();
    $role->forceFill([
        'permissions' => array_values(array_filter(
            $role->permissions ?? [],
            fn (string $permission): bool => ! str_starts_with($permission, 'admin.toko_pos.'),
        )),
    ])->save();

    $migrationPath = collect(glob(database_path('migrations/*_grant_toko_pos_permissions_to_demo_admin_role.php')) ?: [])
        ->first();

    expect($migrationPath)->not->toBeNull()
        ->and(is_file($migrationPath))->toBeTrue();

    $migration = require $migrationPath;
    $migration->up();

    $role->refresh();

    expect($role->permissions ?? [])->toContain(
        'admin.toko_pos.view',
        'admin.toko_pos.export',
    );
});
