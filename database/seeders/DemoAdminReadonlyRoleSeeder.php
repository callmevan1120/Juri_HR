<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Support\RbacRegistry;
use Database\Seeders\Concerns\GuardsDemoSeeding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoAdminReadonlyRoleSeeder extends Seeder
{
    use GuardsDemoSeeding;

    public function run(): void
    {
        if ($this->shouldSkipDemoSeeding() || ! $this->hasRequiredTables()) {
            return;
        }

        // Grant all permissions so the demo admin can see all menus.
        // Destructive actions are blocked inside the Livewire components using the is_demo guard.
        $permissions = array_values(RbacRegistry::permissionKeys());

        $role = Role::query()->updateOrCreate([
            'slug' => 'demo_admin_readonly',
        ], [
            'name' => 'Demo Admin',
            'description' => 'Near-superadmin access for demo. Excludes settings, RBAC, maintenance, and superadmin account management. Password changes blocked by is_demo guard.',
            'permissions' => $permissions,
            'is_system' => true,
            'is_super_admin' => false,
        ]);

        $demoAdmin = User::query()->updateOrCreate([
            'email' => 'admin123@paspapan.com',
        ], [
            'name' => 'Demo Admin',
            'nip' => '0000000000000001',
            'password' => Hash::make('12345678'),
            'group' => 'admin',
            'email_verified_at' => now(),
            'phone' => '081234567801',
            'gender' => 'male',
            'address' => 'Demo Address Admin',
            'company_id' => $this->defaultCompanyId(),
            'employment_status' => User::EMPLOYMENT_STATUS_ACTIVE,
        ]);

        $demoAdmin->roles()->sync([$role->id]);
    }

    private function hasRequiredTables(): bool
    {
        return Schema::hasTable('users')
            && Schema::hasTable('roles')
            && Schema::hasTable('role_user');
    }

    private function defaultCompanyId(): ?string
    {
        if (! Schema::hasTable('companies')) {
            return null;
        }

        return Company::query()->where('slug', 'paspapan-demo')->value('id')
            ?? Company::query()->orderBy('id')->value('id');
    }
}
