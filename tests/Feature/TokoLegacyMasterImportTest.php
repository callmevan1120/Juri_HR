<?php

use App\Models\Client;
use App\Models\Company;
use App\Models\ImportExportRun;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vendor;
use App\Support\TokoLegacyImportPreviewService;

test('toko master import creates master records and completed run', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);

    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Existing Kapasitor',
        'sku' => 'SKU000001',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
    ]);

    $path = writeTokoMasterImportDump();

    $run = app(TokoLegacyImportPreviewService::class)->importMasterData($actor, $path, (int) $company->id);

    expect($run)->toBeInstanceOf(ImportExportRun::class)
        ->and($run->resource)->toBe('toko_pos_master')
        ->and($run->operation)->toBe('import')
        ->and($run->status)->toBe('completed')
        ->and($run->processed_rows)->toBe(6)
        ->and($run->total_rows)->toBe(6)
        ->and($run->source_path)->toBe($path)
        ->and($run->meta['summary']['products']['created'])->toBe(2)
        ->and($run->meta['summary']['products']['updated_existing'])->toBe(1)
        ->and($run->meta['summary']['products']['skipped_existing'])->toBe(1)
        ->and($run->meta['summary']['products']['invalid'])->toBe(1)
        ->and($run->meta['summary']['opening_stock']['created'])->toBe(2)
        ->and($run->meta['summary']['opening_stock']['skipped_existing_product'])->toBe(1)
        ->and($run->meta['summary']['brands']['created'])->toBe(2)
        ->and($run->meta['summary']['categories']['created'])->toBe(1)
        ->and($run->meta['summary']['customers']['created'])->toBe(1)
        ->and($run->meta['summary']['vendors']['created'])->toBe(1);

    $product = Product::query()->where('company_id', $company->id)->where('sku', 'SKU000002')->firstOrFail();
    $existingProduct = Product::query()->where('company_id', $company->id)->where('sku', 'SKU000001')->firstOrFail();

    expect($product->name)->toBe('Filter AC')
        ->and($product->selling_price)->toEqual('8000.00')
        ->and($product->cost_price)->toEqual('6500.00')
        ->and($product->unit)->toBe('pcs')
        ->and($product->reorder_point)->toEqual('2.000')
        ->and($product->metadata['brand'])->toBe('Samsung')
        ->and($product->metadata['category'])->toBe('Sparepart AC')
        ->and($product->metadata['barcode'])->toBe('8992')
        ->and($product->metadata['location'])->toBe('Rak 2')
        ->and($product->metadata['legacy_toko']['kode'])->toBe('P002')
        ->and($product->metadata['legacy_toko']['brand'])->toBe('Samsung')
        ->and($existingProduct->selling_price)->toEqual('45000.00')
        ->and($existingProduct->cost_price)->toEqual('34000.00')
        ->and($existingProduct->reorder_point)->toEqual('1.000')
        ->and($existingProduct->metadata['brand'])->toBe('Sigma')
        ->and($existingProduct->metadata['category'])->toBe('Sparepart AC')
        ->and($existingProduct->metadata['barcode'])->toBe('8991')
        ->and(collect(json_decode(Setting::getValue('toko_pos.product_brands', '[]'), true))->pluck('name')->all())->toContain('Sigma', 'Samsung')
        ->and(collect(json_decode(Setting::getValue('toko_pos.product_categories', '[]'), true))->pluck('name')->all())->toContain('Sparepart AC')
        ->and(Client::query()->where('company_id', $company->id)->where('code', 'CUST001')->exists())->toBeTrue()
        ->and(Vendor::query()->where('company_id', $company->id)->where('name', 'Digital Teknik')->exists())->toBeTrue()
        ->and(StockMovement::query()->where('product_id', $product->id)->where('type', StockMovement::TYPE_IN)->value('quantity'))->toEqual('3.000')
        ->and(Product::query()->where('company_id', $company->id)->where('sku', 'SKU000004')->exists())->toBeTrue()
        ->and(Product::query()->where('company_id', $company->id)->where('sku', 'SKU000004')->firstOrFail()->stockBalance())->toBe(-2.0);

    unlink($path);
});

test('toko master import fails run when dump is unavailable', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);

    $run = app(TokoLegacyImportPreviewService::class)->importMasterData($actor, '/missing/toko.sql', (int) $company->id);

    expect($run->status)->toBe('failed')
        ->and($run->resource)->toBe('toko_pos_master')
        ->and($run->error_message)->toContain('Legacy SQL dump was not found.');
});

test('toko master import resolves product category and brand from legacy master code columns', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-relational-master',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);

    $dump = <<<'SQL'
INSERT INTO `kategori` (`kode`, `nama`) VALUES
('0003', 'Sparepart AC');
INSERT INTO `brand` (`kode`, `nama`) VALUES
('0010', 'Sigma');
INSERT INTO `barang` (`kode`, `sku`, `nama`, `idkategori`, `idbrand`, `hargabeli`, `hargajual`, `sisa`, `stokmin`, `satuan`) VALUES
('P010', 'SKU000010', 'Cap AC Sigma', '0003', '0010', 28000, 45000, 5, 1, 'pcs');
SQL;

    $path = tempnam(sys_get_temp_dir(), 'toko-master-relational-');
    file_put_contents($path, $dump);

    $run = app(TokoLegacyImportPreviewService::class)->importMasterData($actor, $path, (int) $company->id);

    $product = Product::query()->where('company_id', $company->id)->where('sku', 'SKU000010')->firstOrFail();

    expect($run->status)->toBe('completed')
        ->and($product->metadata['brand'])->toBe('Sigma')
        ->and($product->metadata['category'])->toBe('Sparepart AC')
        ->and(collect(json_decode(Setting::getValue('toko_pos.product_brands', '[]'), true))->pluck('name')->all())->toContain('Sigma')
        ->and(collect(json_decode(Setting::getValue('toko_pos.product_categories', '[]'), true))->pluck('name')->all())->toContain('Sparepart AC');

    unlink($path);
});

test('toko master import stores legacy system settings mapping and retirement decisions', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-legacy-settings',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);

    $dump = <<<'SQL'
INSERT INTO `backset` (`url`, `sessiontime`, `footer`, `themesback`, `responsive`, `namabisnis1`, `tipenota`, `l153n53`, `loginbg`) VALUES
('https://toko.pandanteknik.com', '30', 'Pandan Teknik Mandiri', 'dark', 'yes', 'Pandan Teknik', 'thermal', 'LEG-001', 'login.jpg');
INSERT INTO `data` (`nama`, `tagline`, `alamat`, `notelp`, `signature`, `avatar`, `no`) VALUES
('Pandan Teknik Mandiri', 'Teknik terpercaya', 'Jakarta Barat', '021', 'Admin', 'logo.png', 1);
INSERT INTO `barang_setting` (`view_sku`, `view_nama`, `view_hbeli`, `view_hjual`, `kode`) VALUES
('1', '1', '1', '1', 'BRGSET');
INSERT INTO `options` (`nama`, `tipe`, `no`) VALUES
('Cash', 'payment', 1);
INSERT INTO `pin` (`pin`, `ubah`) VALUES
('123456', 'yes');
INSERT INTO `info` (`nama`, `avatar`, `tanggal`, `isi`, `id`) VALUES
('Admin', 'admin.png', '2026-01-01', 'Stok opname akhir bulan', 1);
INSERT INTO `jabatan` (`kode`, `nama`, `no`) VALUES
('ADM', 'Admin Toko', 1);
INSERT INTO `chmenu` (`userjabatan`, `menu1`, `menu2`) VALUES
('ADM', '1', '1');
INSERT INTO `user` (`userna_me`, `pa_ssword`, `nama`, `alamat`, `nohp`, `tgllahir`, `tglaktif`, `jabatan`, `avatar`, `no`) VALUES
('admin', 'hash', 'Admin Toko', 'Jakarta', '0812', '1990-01-01', '2026-01-01', 'ADM', 'admin.png', 1);
SQL;

    $path = tempnam(sys_get_temp_dir(), 'toko-master-settings-');
    file_put_contents($path, $dump);

    $preview = app(TokoLegacyImportPreviewService::class)->preview($path, $company->id);
    $run = app(TokoLegacyImportPreviewService::class)->importMasterData($actor, $path, (int) $company->id);
    $mapping = json_decode(Setting::getValue('toko_pos.legacy_system_mapping', '{}'), true);

    expect($preview['tables']['backset']['mapped'])->toBeTrue()
        ->and($preview['tables']['pin']['mapped'])->toBeTrue()
        ->and($run->meta['summary']['settings']['mapped'])->toBe(9)
        ->and($mapping['company_id'])->toBe($company->id)
        ->and($mapping['identity']['business_name'])->toBe('Pandan Teknik Mandiri')
        ->and($mapping['receipt']['type'])->toBe('thermal')
        ->and($mapping['security']['legacy_pin_retired'])->toBeTrue()
        ->and($mapping['users']['legacy_count'])->toBe(1)
        ->and($mapping['roles']['legacy_count'])->toBe(1)
        ->and($mapping['announcements']['legacy_count'])->toBe(1);

    unlink($path);
});

function writeTokoMasterImportDump(): string
{
    $dump = <<<'SQL'
INSERT INTO `barang` (`kode`, `sku`, `nama`, `kategori`, `brand`, `hargabeli`, `hargajual`, `sisa`, `stokmin`, `satuan`, `barcode`, `lokasi`, `keterangan`) VALUES
('P001', 'SKU000001', 'Kapasitor AC', 'Sparepart AC', 'Sigma', 34000, 45000, 5, 1, 'pcs', '8991', 'Rak 1', 'existing'),
('P002', 'SKU000002', 'Filter AC', 'Sparepart AC', 'Samsung', 6500, 8000, 3, 2, 'pcs', '8992', 'Rak 2', 'new'),
('P004', 'SKU000004', 'Oversold Legacy', 'Sparepart AC', 'Sigma', 10000, 15000, -2, 1, 'pcs', '8994', 'Rak 4', 'negative stock'),
('P003', '', '', 'Sparepart AC', 'Samsung', 6500, 8000, -1, 2, 'pcs', '8993', 'Rak 3', 'invalid');
INSERT INTO `pelanggan` (`kode`, `tgldaftar`, `nama`, `alamat`, `nohp`, `email`, `idpelanggan`, `status`) VALUES
('CUST001', '2026-01-01', 'WARDI', 'Jakarta', '0812', 'wardi@example.test', 'L-001', 'Aktif');
INSERT INTO `supplier` (`kode`, `tgldaftar`, `nama`, `alamat`, `nohp`) VALUES
('SUP001', '2026-01-01', 'Digital Teknik', 'Jakarta', '021');
SQL;

    $path = tempnam(sys_get_temp_dir(), 'toko-master-import-');
    file_put_contents($path, $dump);

    return $path;
}
