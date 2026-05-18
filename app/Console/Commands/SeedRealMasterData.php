<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;

class SeedRealMasterData extends Command
{
    protected $signature = 'paspapan:seed-real';

    protected $description = 'Seed production-safe master data only. This command is idempotent; destructive refresh belongs in a separate approved runbook.';

    public function handle(): int
    {
        $this->info('Seeding production-safe master data...');
        $this->call('db:seed', [
            '--class' => DatabaseSeeder::class,
        ]);
        $this->info('Real master data seed completed.');

        return self::SUCCESS;
    }
}
