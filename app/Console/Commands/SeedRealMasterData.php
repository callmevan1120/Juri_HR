<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class SeedRealMasterData extends Command
{
    protected $signature = 'paspapan:seed-real {--refresh-wilayah : Refresh the full wilayah dataset from database/data/wilayah.sql.gz}';

    protected $description = 'Seed production-safe master data only.';

    public function handle(): int
    {
        if ($this->option('refresh-wilayah')) {
            Config::set('paspapan.wilayah_seed_refresh', true);
        }

        $this->info('Seeding production-safe master data...');
        $this->call('db:seed', [
            '--class' => DatabaseSeeder::class,
        ]);
        $this->info('Real master data seed completed.');

        return self::SUCCESS;
    }
}
