<?php

use App\Providers\AuditServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\EnterpriseServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\JetstreamServiceProvider;
use App\Providers\RateLimitServiceProvider;
use App\Providers\SettingsServiceProvider;

return [
    EnterpriseServiceProvider::class,
    SettingsServiceProvider::class,
    AuditServiceProvider::class,
    RateLimitServiceProvider::class,
    AuthServiceProvider::class,
    FortifyServiceProvider::class,
    JetstreamServiceProvider::class,
];
