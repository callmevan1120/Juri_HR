<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Queue::looping(function (): void {
            Cache::put('health:queue_heartbeat_at', now()->toIso8601String(), now()->addMinutes(10));
        });
    }
}
