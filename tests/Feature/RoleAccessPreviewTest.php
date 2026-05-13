<?php

use App\Models\Role;
use App\Support\RoleAccessPreviewService;

test('role access preview lists modules available to a role', function () {
    $role = Role::create([
        'name' => 'HR Preview',
        'slug' => 'hr_preview',
        'permissions' => [
            'admin.employees.view',
            'admin.hr_checklists.view',
        ],
    ]);

    $preview = app(RoleAccessPreviewService::class)->forRole($role);
    $labels = collect($preview)->pluck('label');

    expect($labels)->toContain(__('Employees'))
        ->and($labels)->toContain(__('HR Checklists'));
});
