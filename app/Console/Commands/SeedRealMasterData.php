<?php

namespace App\Console\Commands;

use Database\Seeders\AdminSeeder;
use Database\Seeders\ProductionMasterDataSeeder;
use Illuminate\Console\Command;

class SeedRealMasterData extends Command
{
    protected $signature = 'paspapan:seed-real';

    protected $description = 'Seed production-safe master data only. This command is idempotent; destructive refresh belongs in a separate approved runbook.';

    public function handle(): int
    {
        $this->info('Seeding production-safe master data...');
        $this->call('db:seed', [
            '--class' => ProductionMasterDataSeeder::class,
        ]);

        if ($this->bootstrapAdminSeedingEnabled()) {
            $this->warn('BOOTSTRAP_ADMIN_SEEDING_ENABLED is enabled. Seeding bootstrap admin accounts for this controlled run.');
            $this->call('db:seed', [
                '--class' => AdminSeeder::class,
            ]);
        }

        $this->info('Real master data seed completed.');

        return self::SUCCESS;
    }

    private function bootstrapAdminSeedingEnabled(): bool
    {
        return filter_var(config('paspapan.bootstrap_admin_seeding_enabled', false), FILTER_VALIDATE_BOOL);
    }
}
