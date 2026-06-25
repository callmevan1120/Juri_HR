<?php

use App\Services\Enterprise\LicenseGuard;

it('requires addon salt when checking toko runtime obfuscator key in testing', function (): void {
    $previousKey = getenv('TEST_ENTERPRISE_OBFUSCATOR_KEY');
    $previousKeyEnv = $_ENV['TEST_ENTERPRISE_OBFUSCATOR_KEY'] ?? null;
    $previousKeyServer = $_SERVER['TEST_ENTERPRISE_OBFUSCATOR_KEY'] ?? null;
    $previousSalt = getenv('TEST_ENTERPRISE_ADDON_SALT_TOKO_POS');
    $previousSaltEnv = $_ENV['TEST_ENTERPRISE_ADDON_SALT_TOKO_POS'] ?? null;
    $previousSaltServer = $_SERVER['TEST_ENTERPRISE_ADDON_SALT_TOKO_POS'] ?? null;

    try {
        putenv('TEST_ENTERPRISE_OBFUSCATOR_KEY='.str_repeat('k', 32));
        $_ENV['TEST_ENTERPRISE_OBFUSCATOR_KEY'] = str_repeat('k', 32);
        $_SERVER['TEST_ENTERPRISE_OBFUSCATOR_KEY'] = str_repeat('k', 32);
        putenv('TEST_ENTERPRISE_ADDON_SALT_TOKO_POS');
        unset($_ENV['TEST_ENTERPRISE_ADDON_SALT_TOKO_POS'], $_SERVER['TEST_ENTERPRISE_ADDON_SALT_TOKO_POS']);

        expect(LicenseGuard::hasRuntimeObfuscatorKey('toko_pos'))->toBeFalse()
            ->and(LicenseGuard::hasRuntimeObfuscatorKey())->toBeTrue();

        putenv('TEST_ENTERPRISE_ADDON_SALT_TOKO_POS='.str_repeat('s', 32));
        $_ENV['TEST_ENTERPRISE_ADDON_SALT_TOKO_POS'] = str_repeat('s', 32);
        $_SERVER['TEST_ENTERPRISE_ADDON_SALT_TOKO_POS'] = str_repeat('s', 32);

        expect(LicenseGuard::hasRuntimeObfuscatorKey('toko_pos'))->toBeTrue();
    } finally {
        restoreRuntimeEnv('TEST_ENTERPRISE_OBFUSCATOR_KEY', $previousKey, $previousKeyEnv, $previousKeyServer);
        restoreRuntimeEnv('TEST_ENTERPRISE_ADDON_SALT_TOKO_POS', $previousSalt, $previousSaltEnv, $previousSaltServer);
    }
});

function restoreRuntimeEnv(string $name, string|false $processValue, ?string $envValue, ?string $serverValue): void
{
    if ($processValue === false) {
        putenv($name);
    } else {
        putenv($name.'='.$processValue);
    }

    if ($envValue === null) {
        unset($_ENV[$name]);
    } else {
        $_ENV[$name] = $envValue;
    }

    if ($serverValue === null) {
        unset($_SERVER[$name]);
    } else {
        $_SERVER[$name] = $serverValue;
    }
}
