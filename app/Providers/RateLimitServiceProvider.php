<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('global', function (Request $request) {
            $limit = (int) Setting::getValue('security.rate_limit_global', 1000);

            return Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $limit = (int) Setting::getValue('security.rate_limit_login', 5);

            return Limit::perMinute($limit)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            $limit = (int) Setting::getValue('security.rate_limit_api', 60);

            return Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('wilayah', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));
        RateLimiter::for('attendance-integrations', function (Request $request) {
            $apiKey = (string) $request->header('X-PasPapan-Api-Key', '');
            $fingerprint = $apiKey !== '' ? hash('sha256', $apiKey) : $request->ip();

            return Limit::perMinute(120)->by($fingerprint);
        });
    }
}
