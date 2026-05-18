<?php

use Illuminate\Support\Str;

test('database configuration exposes mysql mariadb and postgres runtime knobs', function () {
    $databaseConfig = file_get_contents(config_path('database.php'));
    $envExample = file_get_contents(base_path('.env.example'));

    expect(str_contains($databaseConfig, "'mysql'"))->toBeTrue()
        ->and(str_contains($databaseConfig, "'mariadb'"))->toBeTrue()
        ->and(str_contains($databaseConfig, "'pgsql'"))->toBeTrue()
        ->and(str_contains($databaseConfig, "env('DB_SCHEMA', 'public')"))->toBeTrue()
        ->and(str_contains($databaseConfig, "env('DB_SSLMODE', 'prefer')"))->toBeTrue()
        ->and(str_contains($envExample, 'DB_CONNECTION=pgsql'))->toBeTrue()
        ->and(str_contains($envExample, 'DB_SCHEMA=public'))->toBeTrue()
        ->and(str_contains($envExample, 'DB_SSLMODE=prefer'))->toBeTrue();
});

test('known mysql-only migration statements have postgres or sqlite guards', function () {
    $migrationFiles = collect(glob(database_path('migrations/*.php')) ?: [])
        ->filter(fn (string $path): bool => str_contains(file_get_contents($path), 'DB::statement'))
        ->values();

    $unguarded = $migrationFiles
        ->filter(function (string $path): bool {
            $contents = file_get_contents($path);
            $hasMysqlOnlyStatement = Str::contains($contents, [
                ' MODIFY ',
                ' MODIFY COLUMN ',
                'DROP FOREIGN KEY',
                'ADD CONSTRAINT `',
                'DATE_ADD(',
            ]);

            if (! $hasMysqlOnlyStatement) {
                return false;
            }

            return ! Str::contains($contents, [
                "DB::getDriverName() === 'pgsql'",
                "DB::getDriverName() !== 'sqlite'",
                "DB::getDriverName() === 'sqlite'",
                "in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)",
            ]);
        })
        ->map(fn (string $path): string => str_replace(base_path().DIRECTORY_SEPARATOR, '', $path))
        ->values()
        ->all();

    expect($unguarded)->toBe([]);
});

test('database portability documentation is linked from public docs', function () {
    expect(str_contains(file_get_contents(base_path('README.md')), './guides/database-portability.md'))->toBeTrue()
        ->and(str_contains(file_get_contents(base_path('guides/deployment.md')), 'PostgreSQL'))->toBeTrue();
});
