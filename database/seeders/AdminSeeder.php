<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if ($this->runningProduction() && ! $this->bootstrapAdminSeedingEnabled()) {
            $this->command?->warn('Skipping bootstrap admin accounts in production. Set BOOTSTRAP_ADMIN_SEEDING_ENABLED=true only for a controlled bootstrap.');

            return;
        }

        $this->seedAdminAccount([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('superadmin'),
        ], superadmin: true, roleSlug: 'super_admin');

        $this->seedAdminAccount([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
        ], superadmin: false, roleSlug: 'admin');
    }

    /**
     * Seed bootstrap admin accounts idempotently so local installs can repair
     * missing or stale credentials without failing on an existing email.
     *
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    private function seedAdminAccount(array $attributes, bool $superadmin, string $roleSlug): User
    {
        $group = $superadmin ? 'superadmin' : 'admin';
        $user = User::query()->where('email', $attributes['email'])->first();

        if ($user === null) {
            $user = User::factory()->admin(superadmin: $superadmin)->create($attributes);
        } else {
            $user->forceFill([
                'name' => $attributes['name'],
                'group' => $group,
                'password' => $attributes['password'],
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        }

        $this->assignDefaultRole($user, $roleSlug);

        return $user;
    }

    private function assignDefaultRole(User $user, string $roleSlug): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('role_user')) {
            return;
        }

        $roleId = Role::query()->where('slug', $roleSlug)->value('id');

        if ($roleId !== null) {
            $user->roles()->syncWithoutDetaching([$roleId]);
        }
    }

    private function bootstrapAdminSeedingEnabled(): bool
    {
        return filter_var(config('paspapan.bootstrap_admin_seeding_enabled', false), FILTER_VALIDATE_BOOL)
            || filter_var(config('paspapan.demo_seeding_enabled', false), FILTER_VALIDATE_BOOL);
    }

    private function runningProduction(): bool
    {
        return app()->environment('production') || config('app.env') === 'production';
    }
}
