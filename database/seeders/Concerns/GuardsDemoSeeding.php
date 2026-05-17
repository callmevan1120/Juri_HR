<?php

namespace Database\Seeders\Concerns;

trait GuardsDemoSeeding
{
    protected function shouldSkipDemoSeeding(): bool
    {
        if (! $this->runningProduction()) {
            return false;
        }

        if ($this->demoSeedingEnabled()) {
            return false;
        }

        $this->command?->warn('Skipping fake/demo data in production. Set DEMO_SEEDING_ENABLED=true only for staging/demo.');

        return true;
    }

    protected function demoSeedingEnabled(): bool
    {
        return filter_var(config('paspapan.demo_seeding_enabled', false), FILTER_VALIDATE_BOOL);
    }

    protected function runningProduction(): bool
    {
        return app()->environment('production') || config('app.env') === 'production';
    }
}
