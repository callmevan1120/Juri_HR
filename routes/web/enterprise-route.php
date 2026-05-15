<?php

use App\Http\Controllers\System\LockedEnterpriseRouteController;
use App\Services\Enterprise\LicenseGuard;
use Illuminate\Support\Facades\Route;

if (! function_exists('enterprise_livewire_route')) {
    function enterprise_livewire_route(string $uri, string $component)
    {
        return LicenseGuard::hasRuntimeObfuscatorKey()
            ? Route::livewire($uri, $component)
            : Route::get($uri, LockedEnterpriseRouteController::class);
    }
}
