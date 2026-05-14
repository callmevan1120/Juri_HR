<?php

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

test('modern stack guard blocks stale framework and frontend markers', function () {
    $blockedMarkers = [
        'Laravel '.(10 + 1),
        'Laravel '.(10 + 2),
        'Livewire '.(1 + 2),
        'Tailwind '.(1 + 2),
    ];

    $allowedFiles = [
        'tests/Feature/ModernStackGuardTest.php',
        'scripts/check-modern-stack.php',
    ];

    foreach (trackedProjectFiles() as $file) {
        if (! shouldScanModernStackFile($file) || in_array($file, $allowedFiles, true)) {
            continue;
        }

        $contents = file_get_contents(base_path($file));

        foreach ($blockedMarkers as $marker) {
            expect($contents)->not->toContain($marker, "{$file} contains {$marker}");
        }
    }
});

test('full page livewire routes use livewire aliases instead of class route mounts', function () {
    foreach (trackedProjectFiles('routes/') as $file) {
        if (! str_ends_with($file, '.php')) {
            continue;
        }

        $contents = file_get_contents(base_path($file));

        expect($contents)->not->toContain('use App\\Livewire\\', "{$file} imports Livewire page classes")
            ->and($contents)->not->toMatch('/Route::livewire\(\s*[^,]+,\s*[^,)]+::class/s', "{$file} mounts Livewire with ::class");
    }
});

test('tailwind four css first setup has no legacy config files or directives', function () {
    $legacyConfigFiles = [
        'tailwind'.'.config.js',
        'tailwind'.'.config.cjs',
        'tailwind'.'.config.mjs',
        'postcss'.'.config.js',
        'postcss'.'.config.cjs',
        'postcss'.'.config.mjs',
    ];

    foreach ($legacyConfigFiles as $file) {
        expect(file_exists(base_path($file)))->toBeFalse("{$file} should not exist");
    }

    $css = file_get_contents(resource_path('css/app.css'));
    $package = json_decode(file_get_contents(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $devDependencies = $package['devDependencies'] ?? [];

    expect($css)
        ->toContain('@import "tailwindcss";')
        ->toContain('@theme')
        ->not->toMatch('/@tailwind\s+(base|components|utilities)\b/')
        ->and(array_key_exists('@tailwindcss/vite', $devDependencies))->toBeTrue()
        ->and(array_key_exists('post'.'css', $devDependencies))->toBeFalse()
        ->and(array_key_exists('auto'.'prefixer', $devDependencies))->toBeFalse();
});

test('laravel thirteen and livewire four upgrade configuration stays current', function () {
    $deprecatedMiddlewareMarkers = [
        'Verify'.'CsrfToken',
        'Validate'.'CsrfToken',
    ];

    foreach (trackedProjectFiles() as $file) {
        if (! shouldScanModernStackFile($file) || $file === 'tests/Feature/ModernStackGuardTest.php') {
            continue;
        }

        $contents = file_get_contents(base_path($file));

        foreach ($deprecatedMiddlewareMarkers as $marker) {
            expect($contents)->not->toContain($marker, "{$file} references deprecated CSRF middleware {$marker}");
        }
    }

    $vercelRoute = collect(Route::getRoutes())
        ->first(fn ($route): bool => $route->uri() === '__vercel-migrate' && in_array('POST', $route->methods(), true));

    $excludedMiddleware = $vercelRoute !== null && method_exists($vercelRoute, 'excludedMiddleware')
        ? $vercelRoute->excludedMiddleware()
        : [];

    expect(config('sanctum.middleware.validate_csrf_token'))->toBe(PreventRequestForgery::class)
        ->and($excludedMiddleware)->toContain(PreventRequestForgery::class)
        ->and(config('cache.serializable_classes'))->toBeFalse()
        ->and(config('livewire.component_layout'))->toBe('layouts::app')
        ->and(config('livewire.component_placeholder'))->toBeNull()
        ->and(config('livewire.smart_wire_keys'))->toBeTrue()
        ->and(config('livewire.csp_safe'))->toBeFalse()
        ->and(config('livewire.component_locations'))->toContain(resource_path('views/livewire'))
        ->and(config('livewire.component_namespaces'))->toHaveKey('layouts')
        ->and(config('livewire.make_command.type'))->toBe('sfc');

    $exampleEnv = file_get_contents(base_path('.env.example'));

    expect($exampleEnv)
        ->toContain('CACHE_PREFIX=paspapan_cache_')
        ->toContain('REDIS_PREFIX=paspapan_database_')
        ->toContain('SESSION_COOKIE=paspapan_session');
});

/**
 * @return list<string>
 */
function trackedProjectFiles(?string $prefix = null): array
{
    $output = shell_exec('git ls-files');
    $files = $output === null ? [] : array_values(array_filter(explode("\n", $output)));

    if ($prefix === null) {
        return $files;
    }

    return array_values(array_filter($files, fn (string $file): bool => Str::startsWith($file, $prefix)));
}

function shouldScanModernStackFile(string $file): bool
{
    if (in_array($file, ['README.md', 'RELEASE_CHECKLIST.md', 'composer.json', 'package.json', 'vite.config.js', 'capacitor.config.ts'], true)) {
        return true;
    }

    foreach (['.github/', 'app/', 'config/', 'database/', 'guides/', 'resources/css/', 'resources/views/', 'routes/', 'scripts/', 'tests/'] as $prefix) {
        if (Str::startsWith($file, $prefix)) {
            return true;
        }
    }

    return false;
}
