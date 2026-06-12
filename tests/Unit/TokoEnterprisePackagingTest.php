<?php

test('toko pos addon is included in enterprise obfuscator build list', function (): void {
    $buildScript = file_get_contents(base_path('secure_tools/build_enterprise.php'));

    expect($buildScript)
        ->toContain('../app/Livewire/Admin/TokoPosAddon')
        ->toContain('../app/Support/TokoLegacyImportPreviewService')
        ->toContain('../app/Support/TokoPosCutoverArchiveService')
        ->toContain('../app/Support/TokoPosCutoverReadinessService')
        ->toContain('../app/Support/TokoPosDeliveryLetterService')
        ->toContain('../app/Support/TokoPosInventoryAdjustmentService')
        ->toContain('../app/Support/TokoPosPurchaseService')
        ->toContain('../app/Support/TokoPosQuotationService')
        ->toContain('../app/Support/TokoPosReportService')
        ->toContain('../app/Support/TokoPosSalesService')
        ->toContain('../app/Http/Controllers/Admin/Toko/DownloadTokoDeliveryLetterPdfController')
        ->toContain('../app/Http/Controllers/Admin/Toko/DownloadTokoInvoicePdfController')
        ->toContain('../app/Http/Controllers/Admin/Toko/DownloadTokoQuotationPdfController')
        ->toContain('../app/Http/Controllers/Admin/Toko/PrintTokoStockAdjustmentReportController');
});

test('toko pos addon is included in license generator feature catalog', function (): void {
    $licenseGenerator = file_get_contents(base_path('secure_tools/license_generator.php'));

    expect($licenseGenerator)
        ->toContain("'toko_pos'")
        ->toContain('Toko / POS Add-on')
        ->toContain("'category' => 'addon'")
        ->toContain('--addons=a,b')
        ->toContain('--features=none')
        ->toContain("'addons' => \$addons");
});

test('toko pos addon is flagged as an enterprise addon module', function (): void {
    $module = config('rbac.modules.toko_pos_addon');

    expect($module)
        ->not->toBeNull()
        ->and($module['enterprise'] ?? false)->toBeTrue()
        ->and($module['addon'] ?? false)->toBeTrue()
        ->and($module['license_feature'] ?? null)->toBe('toko_pos')
        ->and($module['module_type'] ?? null)->toBe('addon');
});
