<?php

use App\Services\Enterprise\LicenseGuard;

it('requires addon salt when checking toko runtime obfuscator key in testing', function (): void {
    putenv('TEST_ENTERPRISE_OBFUSCATOR_KEY='.str_repeat('k', 32));
    putenv('TEST_ENTERPRISE_ADDON_SALT_TOKO_POS');
    unset($_ENV['TEST_ENTERPRISE_ADDON_SALT_TOKO_POS'], $_SERVER['TEST_ENTERPRISE_ADDON_SALT_TOKO_POS']);

    expect(LicenseGuard::hasRuntimeObfuscatorKey('toko_pos'))->toBeFalse()
        ->and(LicenseGuard::hasRuntimeObfuscatorKey())->toBeTrue();

    putenv('TEST_ENTERPRISE_ADDON_SALT_TOKO_POS='.str_repeat('s', 32));
    $_ENV['TEST_ENTERPRISE_ADDON_SALT_TOKO_POS'] = str_repeat('s', 32);

    expect(LicenseGuard::hasRuntimeObfuscatorKey('toko_pos'))->toBeTrue();

    putenv('TEST_ENTERPRISE_OBFUSCATOR_KEY');
    putenv('TEST_ENTERPRISE_ADDON_SALT_TOKO_POS');
    unset($_ENV['TEST_ENTERPRISE_OBFUSCATOR_KEY'], $_SERVER['TEST_ENTERPRISE_OBFUSCATOR_KEY'], $_ENV['TEST_ENTERPRISE_ADDON_SALT_TOKO_POS'], $_SERVER['TEST_ENTERPRISE_ADDON_SALT_TOKO_POS']);
});
