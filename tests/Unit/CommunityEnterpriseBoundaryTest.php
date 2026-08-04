<?php

test('community routes do not eagerly load secured enterprise controllers', function (): void {
    $settingsRoutes = file_get_contents(base_path('routes/web/admin/settings.php'));

    expect($settingsRoutes)
        ->toContain('EnterpriseRuntime::sourceAvailable(')
        ->toContain('probeClass: OperationalHealthController::class')
        ->toContain('LockedEnterpriseRouteController::class')
        ->toContain("Route::get('/operational-health', \$operationalHealthAction)")
        ->not->toContain("Route::get('/operational-health', OperationalHealthController::class)");
});

test('community example environment does not advertise private enterprise runtime secrets', function (): void {
    $exampleEnvironment = file_get_contents(base_path('.env.example'));

    expect($exampleEnvironment)
        ->not->toContain('ENTERPRISE_OBFUSCATOR_KEY')
        ->not->toContain('ENTERPRISE_ADDON_SALT_');
});
