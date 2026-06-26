<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $permissions = [
        'admin.toko_pos.view',
        'admin.toko_pos.export',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $role = DB::table('roles')
            ->where('slug', 'demo_admin_readonly')
            ->first(['id', 'permissions']);

        if ($role === null) {
            return;
        }

        DB::table('roles')
            ->where('id', $role->id)
            ->update([
                'permissions' => json_encode($this->appendPermissions($role->permissions), JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $role = DB::table('roles')
            ->where('slug', 'demo_admin_readonly')
            ->first(['id', 'permissions']);

        if ($role === null) {
            return;
        }

        DB::table('roles')
            ->where('id', $role->id)
            ->update([
                'permissions' => json_encode($this->removePermissions($role->permissions), JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
    }

    /**
     * @return list<string>
     */
    private function appendPermissions(?string $encodedPermissions): array
    {
        return array_values(array_unique([
            ...$this->decodePermissions($encodedPermissions),
            ...$this->permissions,
        ]));
    }

    /**
     * @return list<string>
     */
    private function removePermissions(?string $encodedPermissions): array
    {
        return array_values(array_filter(
            $this->decodePermissions($encodedPermissions),
            fn (string $permission): bool => ! in_array($permission, $this->permissions, true),
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
