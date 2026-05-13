<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        User::factory()->admin(superadmin: true)->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('superadmin'),
        ]);
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
        ]);
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
