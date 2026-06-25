<?php

use App\Http\Controllers\System\LockedEnterpriseRouteController;
use App\Support\EnterpriseRuntime;
use Illuminate\Support\Facades\Route;

if (! function_exists('enterprise_livewire_route')) {
    function enterprise_livewire_route(string $uri, string $component, ?string $addon = null)
    {
        return EnterpriseRuntime::sourceAvailable($addon)
            ? Route::livewire($uri, $component)
            : Route::get($uri, LockedEnterpriseRouteController::class);
    }
}
