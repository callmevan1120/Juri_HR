<?php

namespace App\Console\Commands;

use Database\Seeders\FakeDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class SeedFakeDemoData extends Command
{
    protected $signature = 'paspapan:seed-fake {--force : Allow fake/demo data seeding in production-like environments}';

    protected $description = 'Seed real master data first, then fake/demo data for local, QA, and staging environments.';

    public function handle(): int
    {
        if ($this->isProductionLike() && ! $this->option('force')) {
            $this->warn('Refusing to seed fake data in production. Re-run with --force only for controlled staging/demo environments.');

            return self::FAILURE;
        }

        if ($this->option('force')) {
            Config::set('paspapan.demo_seeding_enabled', true);
        }

        $this->info('Seeding real master data and fake/demo data...');
        $this->call('db:seed', [
            '--class' => FakeDataSeeder::class,
        ]);
        $this->info('Fake/demo data seed completed.');

        return self::SUCCESS;
    }

    private function isProductionLike(): bool
    {
        return app()->environment('production') || config('app.env') === 'production';
    }
}
