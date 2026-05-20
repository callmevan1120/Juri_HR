<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Keep the test runner isolated from any cached local/production config.
     *
     * The release checklist intentionally validates config:cache/route:cache, but
     * PHPUnit must still honor phpunit.xml afterward so tests never use a cached
     * DB_CONNECTION or other machine-local runtime value.
     */
    public function createApplication()
    {
        $basePath = Application::inferBasePath();

        foreach ([
            'config.php',
            'routes-v7.php',
            'events.php',
        ] as $cacheFile) {
            $path = $basePath.'/bootstrap/cache/'.$cacheFile;

            if (is_file($path)) {
                @unlink($path);
            }
        }

        $app = require $basePath.'/bootstrap/app.php';

        $this->traitsUsedByTest = class_uses_recursive(static::class);

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
