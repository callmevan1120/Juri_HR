<?php

use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use App\Models\Vendor;
use App\Support\TokoLegacyImportPreviewService;

test('toko legacy preview detects collisions against existing company data', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);

    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Existing Kapasitor',
        'sku' => 'SKU000001',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
    ]);

    Client::query()->create([
        'company_id' => $company->id,
        'name' => 'Existing Wardi',
        'code' => 'CUST001',
        'status' => Client::STATUS_ACTIVE,
    ]);

    Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Digital Teknik',
        'status' => Vendor::STATUS_ACTIVE,
    ]);

    $path = writeTokoDryRunDump();

    $preview = app(TokoLegacyImportPreviewService::class)->preview($path, (int) $company->id);

    expect($preview['collisions']['products']['count'])->toBe(1)
        ->and($preview['collisions']['products']['keys'])->toContain('SKU000001')
        ->and($preview['collisions']['customers']['count'])->toBe(1)
        ->and($preview['collisions']['customers']['keys'])->toContain('CUST001')
        ->and($preview['collisions']['vendors']['count'])->toBe(1)
        ->and($preview['collisions']['vendors']['keys'])->toContain('Digital Teknik')
        ->and($preview['issues'])->toContain('products: SKU SKU000001 already exists in target company.')
        ->and($preview['issues'])->toContain('customers: code CUST001 already exists in target company.')
        ->and($preview['issues'])->toContain('vendors: name Digital Teknik already exists in target company.');

    unlink($path);
});

test('toko legacy preview builds dry-run master import actions', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);

    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Existing Kapasitor',
        'sku' => 'SKU000001',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
    ]);

    $path = writeTokoDryRunDump();

    $preview = app(TokoLegacyImportPreviewService::class)->preview($path, (int) $company->id);

    expect($preview['dry_run']['company_id'])->toBe((int) $company->id)
        ->and($preview['dry_run']['products']['create'])->toBe(2)
        ->and($preview['dry_run']['products']['skip_existing'])->toBe(1)
        ->and($preview['dry_run']['products']['invalid'])->toBe(1)
        ->and($preview['dry_run']['customers']['create'])->toBe(2)
        ->and($preview['dry_run']['vendors']['create'])->toBe(2)
        ->and($preview['dry_run']['opening_stock']['create'])->toBe(2)
        ->and($preview['dry_run']['opening_stock']['skip_existing_product'])->toBe(1)
        ->and($preview['dry_run']['opening_stock']['invalid'])->toBe(1);

    unlink($path);
});

function writeTokoDryRunDump(): string
{
    $dump = <<<'SQL'
INSERT INTO `barang` (`kode`, `sku`, `nama`, `kategori`, `brand`, `hargabeli`, `hargajual`, `sisa`, `stokmin`, `satuan`, `barcode`) VALUES
('P001', 'SKU000001', 'Kapasitor AC', 'Sparepart AC', 'Sigma', 34000, 45000, 5, 1, 'pcs', '8991'),
('P002', 'SKU000002', 'Filter AC', 'Sparepart AC', 'Samsung', 6500, 8000, 3, 2, 'pcs', '8992'),
('P004', 'SKU000004', 'Oversold Legacy', 'Sparepart AC', 'Sigma', 10000, 15000, -2, 1, 'pcs', '8994'),
('P003', '', '', 'Sparepart AC', 'Samsung', 6500, 8000, -1, 2, 'pcs', '8993');
INSERT INTO `pelanggan` (`kode`, `nama`, `alamat`, `nohp`, `email`) VALUES
('CUST001', 'WARDI', 'Jakarta', '0812', ''),
('CUST002', 'BUDI', 'Depok', '', '');
INSERT INTO `supplier` (`kode`, `nama`, `alamat`, `nohp`) VALUES
('SUP001', 'Digital Teknik', 'Jakarta', '021'),
('SUP002', 'Tanjung Jaya Teknik', 'Tangerang', '');
SQL;

    $path = tempnam(sys_get_temp_dir(), 'toko-dry-run-');
    file_put_contents($path, $dump);

    return $path;
}
