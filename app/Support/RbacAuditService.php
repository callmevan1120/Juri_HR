<?php

namespace App\Support;

use App\Models\Role;
use Illuminate\Support\Facades\Route;

class RbacAuditService
{
    /**
     * @return array<string, array<int, string>>
     */
    public function report(): array
    {
        return [
            'routes_without_permission' => $this->routesWithoutPermission(),
            'menu_entries_to_review' => $this->menuEntriesToReview(),
            'permissions_without_route' => $this->permissionsWithoutRoute(),
            'roles_without_permissions' => $this->rolesWithoutPermissions(),
            'policies_without_direct_test' => $this->policiesWithoutDirectTest(),
        ];
    }

    /**
     * @return list<string>
     */
    private function routesWithoutPermission(): array
    {
        $allowed = [
            'admin.dashboard',
            'admin.inbox',
            'admin.notifications',
            'admin.profile.show',
        ];

        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route): bool => str_starts_with((string) $route->getName(), 'admin.'))
            ->reject(fn ($route): bool => in_array((string) $route->getName(), $allowed, true))
            ->filter(function ($route): bool {
                $middleware = collect($route->gatherMiddleware());

                return ! $middleware->contains(fn (string $item): bool => str_starts_with($item, 'can:'));
            })
            ->map(fn ($route): string => (string) $route->getName().' ['.$route->uri().']')
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function menuEntriesToReview(): array
    {
        $path = resource_path('views/navigation-menu.blade.php');

        if (! is_file($path)) {
            return ['resources/views/navigation-menu.blade.php missing'];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];
        $findings = [];

        foreach ($lines as $index => $line) {
            if (! str_contains($line, "route('admin.")) {
                continue;
            }

            $nearby = implode(' ', array_slice($lines, max(0, $index - 3), 12));

            if (! str_contains($nearby, "'visible'") && ! str_contains($nearby, '$can(') && ! str_contains($nearby, 'allowsAdminPermission')) {
                $findings[] = 'line '.($index + 1).': '.trim($line);
            }
        }

        return $findings;
    }

    /**
     * @return list<string>
     */
    private function permissionsWithoutRoute(): array
    {
        $routeNames = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route): string => (string) $route->getName())
            ->filter()
            ->values();

        $registryIssues = collect(RbacRegistry::modules())
            ->flatMap(function (array $module, string $moduleKey) use ($routeNames): array {
                return collect($module['route_names'] ?? [])
                    ->filter(fn ($routeName): bool => is_string($routeName) && $routeName !== '')
                    ->reject(fn (string $routeName): bool => $routeNames->contains($routeName))
                    ->map(fn (string $routeName): string => $moduleKey.' references missing route '.$routeName)
                    ->values()
                    ->all();
            });

        try {
            $rolePermissions = Role::query()
                ->pluck('permissions')
                ->flatMap(fn ($permissions): array => is_array($permissions) ? $permissions : [])
                ->filter(fn ($permission): bool => is_string($permission) && str_starts_with($permission, 'admin.'))
                ->unique()
                ->values();
        } catch (\Throwable) {
            return ['Role permission scan unavailable because the database could not be read.'];
        }

        $registryPermissions = collect(RbacRegistry::permissionKeys());

        return $registryIssues
            ->merge($rolePermissions->diff($registryPermissions)->map(fn (string $permission): string => 'unknown role permission '.$permission))
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function rolesWithoutPermissions(): array
    {
        try {
            return Role::query()
                ->get(['name', 'permissions'])
                ->filter(fn (Role $role): bool => ! is_array($role->permissions) || $role->permissions === [])
                ->map(fn (Role $role): string => $role->name)
                ->values()
                ->all();
        } catch (\Throwable) {
            return ['Role mapping scan unavailable because the database could not be read.'];
        }
    }

    /**
     * @return list<string>
     */
    private function policiesWithoutDirectTest(): array
    {
        $policyFiles = glob(app_path('Policies/*Policy.php')) ?: [];
        $testFiles = glob(base_path('tests/Feature/*Policy*Test.php')) ?: [];
        $testHaystack = strtolower(implode(' ', array_map(
            fn (string $path): string => basename($path).' '.((string) file_get_contents($path)),
            $testFiles,
        )));

        return collect($policyFiles)
            ->map(fn (string $path): string => basename($path, '.php'))
            ->reject(function (string $policy) use ($testHaystack): bool {
                $policyName = strtolower($policy);
                $policySubject = strtolower(str_replace('Policy', '', $policy));

                return str_contains($testHaystack, $policyName)
                    || str_contains($testHaystack, $policySubject);
            })
            ->values()
            ->all();
    }
}
