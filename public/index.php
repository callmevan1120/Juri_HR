<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require_once __DIR__.'/../bootstrap/enterprise-runtime.php';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

try {
    // Register the Composer autoloader...
    require __DIR__.'/../vendor/autoload.php';

    // Bootstrap Laravel and handle the request...
    (require_once __DIR__.'/../bootstrap/app.php')
        ->handleRequest(Request::capture());
} catch (Throwable $throwable) {
    if (paspapan_enterprise_runtime_key_missing($throwable)) {
        paspapan_enterprise_runtime_http_response();

        return;
    }

    throw $throwable;
}
