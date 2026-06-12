<?php

use App\Support\RbacRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $adminPermissions = [
        'admin.toko_pos.view',
        'admin.toko_pos.manage',
        'admin.toko_pos.export',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $roles = DB::table('roles')
            ->whereIn('slug', ['super_admin', 'admin'])
            ->get(['id', 'slug', 'permissions']);

        foreach ($roles as $role) {
            $permissions = $role->slug === 'super_admin'
                ? RbacRegistry::permissionKeys()
                : $this->appendPermissions($role->permissions);

            DB::table('roles')
                ->where('id', $role->id)
                ->update([
                    'permissions' => json_encode($permissions, JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $roles = DB::table('roles')
            ->where('slug', 'admin')
            ->get(['id', 'permissions']);

        foreach ($roles as $role) {
            DB::table('roles')
                ->where('id', $role->id)
                ->update([
                    'permissions' => json_encode($this->removePermissions($role->permissions), JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
        }
    }

    private function appendPermissions(?string $encodedPermissions): array
    {
        return array_values(array_unique([
            ...$this->decodePermissions($encodedPermissions),
            ...$this->adminPermissions,
        ]));
    }

    private function removePermissions(?string $encodedPermissions): array
    {
        return array_values(array_filter(
            $this->decodePermissions($encodedPermissions),
            fn (string $permission): bool => ! in_array($permission, $this->adminPermissions, true),
        ));
    }

    /**
     * @return list<string>
     */
    private function decodePermissions(?string $encodedPermissions): array
    {
        if (! $encodedPermissions) {
            return [];
        }

        $permissions = json_decode($encodedPermissions, true);

        if (! is_array($permissions)) {
            return [];
        }

        return array_values(array_filter(
            $permissions,
            fn ($permission): bool => is_string($permission) && $permission !== '',
        ));
    }
};
