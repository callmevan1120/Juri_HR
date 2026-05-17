<?php

use App\Support\RbacRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('roles') || ! Schema::hasTable('role_user')) {
            return;
        }

        $now = now();
        $roleId = DB::table('roles')->where('slug', 'demo_admin_readonly')->value('id');

        if ($roleId === null) {
            $roleId = (string) Str::ulid();

            DB::table('roles')->insert([
                'id' => $roleId,
                'slug' => 'demo_admin_readonly',
                'name' => 'Demo Admin Read Only',
                'description' => 'Read-only demo role with broad admin visibility and no destructive actions.',
                'permissions' => json_encode(RbacRegistry::readOnlyPermissionKeys(), JSON_THROW_ON_ERROR),
                'is_system' => true,
                'is_super_admin' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('roles')->where('id', $roleId)->update([
                'name' => 'Demo Admin Read Only',
                'description' => 'Read-only demo role with broad admin visibility and no destructive actions.',
                'permissions' => json_encode(RbacRegistry::readOnlyPermissionKeys(), JSON_THROW_ON_ERROR),
                'is_system' => true,
                'is_super_admin' => false,
                'updated_at' => $now,
            ]);
        }

        $userId = DB::table('users')->where('email', 'admin123@paspapan.com')->value('id');

        if ($userId === null) {
            return;
        }

        DB::table('role_user')->where('user_id', $userId)->delete();
        DB::table('role_user')->insert([
            'role_id' => $roleId,
            'user_id' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('roles') || ! Schema::hasTable('role_user')) {
            return;
        }

        $roleId = DB::table('roles')->where('slug', 'demo_admin_readonly')->value('id');

        if ($roleId !== null) {
            DB::table('role_user')->where('role_id', $roleId)->delete();
            DB::table('roles')->where('id', $roleId)->delete();
        }
    }
};
