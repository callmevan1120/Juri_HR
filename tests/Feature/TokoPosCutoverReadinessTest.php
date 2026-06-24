<?php

use App\Models\Client;
use App\Models\Company;
use App\Models\ImportExportRun;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Services\Enterprise\LicenseGuard;
use App\Support\TokoLegacyImportPreviewService;
use App\Support\TokoPosCutoverReadinessService;

beforeEach(function () {
    if (! LicenseGuard::hasRuntimeObfuscatorKey('toko_pos')) {
        test()->markTestSkipped('Enterprise runtime obfuscator key is not available.');
    }
});

test('toko cutover readiness compares legacy dump counts with target database', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-cutover',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'BRG001',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 10000,
        'cost_price' => 6000,
        'stock_tracking' => true,
        'metadata' => ['legacy_toko' => ['kode' => 'BRG001']],
    ]);
    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Legacy audit placeholder',
        'sku' => 'LEGACY-TOKO-999',
        'status' => Product::STATUS_INACTIVE,
        'unit' => 'pcs',
        'selling_price' => 0,
        'cost_price' => 0,
        'stock_tracking' => true,
        'metadata' => ['legacy_toko' => ['kode' => '999']],
    ]);
    Client::query()->create([
        'company_id' => $company->id,
        'name' => 'WARDI',
        'code' => '0001',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $path = base_path('storage/framework/testing/toko-cutover.sql');
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, <<<'SQL'
INSERT INTO `barang` (`no`, `kode`, `sku`, `nama`, `kategori`, `brand`, `barcode`, `hargabeli`, `hargajual`, `terjual`, `terbeli`, `sisa`, `retur`, `stokmin`, `ukuran`, `warna`, `expired`, `satuan`, `lokasi`, `keterangan`, `avatar`) VALUES
(1, 'BRG001', 'BRG001', 'Filter AC', '', '', '', 6000, 10000, 0, 0, 3, 0, 1, '', '', '', 'pcs', '', '', '');
INSERT INTO `pelanggan` (`kode`, `tgldaftar`, `nama`, `alamat`, `nohp`, `email`, `idpelanggan`, `status`, `no`) VALUES
('0001', '2026-01-01', 'WARDI', 'Srengseng', '08', '', '', 'Aktif', 1);
INSERT INTO `quotation` (`nota`, `nomor`, `tgl`, `due`, `pelanggan`, `modal`, `total`, `diskon`, `potongan`, `biayatambahan`, `status`, `oleh`, `notainvoice`, `keterangan`, `no`) VALUES
('Q0001', 'QTN-LEG-001', '2026-01-02', '2026-01-16', '0001', 6000, 20000, 0, 0, 0, 'Open', 'admin', '', 'Legacy quote', 1);
INSERT INTO `quotation_list` (`nota`, `kode`, `nama`, `harga`, `jumlah`, `hargaakhir`, `modal`, `conv`, `no`) VALUES
('Q0001', 'BRG001', 'Filter AC', 10000, 2, 20000, 6000, 0, 1);
INSERT INTO `retur` (`nota`, `tanggal`, `dana`, `status`, `petugas`, `no`) VALUES
('R0001', '2026-01-03', 10000, 'Retur', 'admin', 1);
INSERT INTO `dataretur` (`nota`, `kode`, `nama`, `jumlah`, `harga`, `hargaakhir`, `no`) VALUES
('R0001', 'BRG001', 'Filter AC', 1, 10000, 10000, 1);
INSERT INTO `surat` (`nota`, `nosurat`, `tanggal`, `kode_pelanggan`, `tujuan`, `notelp`, `alamat`, `driver`, `nohp`, `nopol`, `oleh`, `no`) VALUES
('S0001', 'SR-LEG-001', '2026-01-04', '0001', 'Gudang Srengseng', '08', 'Srengseng', 'Admin', '', '', 'Admin', 1);
SQL);

    app(TokoLegacyImportPreviewService::class)->importHistoricalDocuments($actor, $path, $company->id);

    $readiness = app(TokoPosCutoverReadinessService::class)->snapshot($path, $company->id);

    expect($readiness['checks']['products']['legacy'])->toBe(1)
        ->and($readiness['checks']['products']['target'])->toBe(1)
        ->and($readiness['checks']['quotations']['target'])->toBe(1)
        ->and($readiness['checks']['returns']['target'])->toBe(1)
        ->and($readiness['checks']['delivery_letters']['target'])->toBe(1)
        ->and($readiness['ready'])->toBeTrue();
});

test('toko cutover readiness blocks cutoff when historical transaction buckets are still missing', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-cutover-history-gap',
        'status' => Company::STATUS_ACTIVE,
    ]);

    $path = base_path('storage/framework/testing/toko-cutover-history-gap.sql');
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, <<<'SQL'
INSERT INTO `barang` (`no`, `kode`, `sku`, `nama`, `kategori`, `brand`, `barcode`, `hargabeli`, `hargajual`, `terjual`, `terbeli`, `sisa`, `retur`, `stokmin`, `ukuran`, `warna`, `expired`, `satuan`, `lokasi`, `keterangan`, `avatar`) VALUES
(1, 'BRG001', 'BRG001', 'Filter AC', '', '', '', 6000, 10000, 0, 0, 3, 0, 1, '', '', '', 'pcs', '', '', '');
INSERT INTO `sale` (`nota`, `tgl`, `pelanggan`, `total`, `status`, `oleh`, `no`) VALUES
('100001', '2026-01-05', '0001', 25000, 'dibayar', 'admin', 1);
INSERT INTO `beli` (`nota`, `tgl`, `supplier`, `total`, `status`, `oleh`, `no`) VALUES
('PO0001', '2026-01-06', 'Digital Teknik', 15000, 'hutang', 'admin', 1);
INSERT INTO `operasional` (`kode`, `nama`, `tipe`, `tanggal`, `biaya`, `keterangan`, `no`) VALUES
('000001', 'bayar listrik', 'Listrik', '2026-01-07', 100000, 'listrik toko', 1);
INSERT INTO `stok_masuk_daftar` (`nota`, `kode`, `nama`, `jumlah`, `harga`, `no`) VALUES
('SM0001', 'BRG001', 'Filter AC', 4, 6000, 1);
SQL);

    $readiness = app(TokoPosCutoverReadinessService::class)->snapshot($path, $company->id);

    expect($readiness['ready'])->toBeFalse()
        ->and($readiness['checks']['sales']['legacy'])->toBe(1)
        ->and($readiness['checks']['sales']['target'])->toBe(0)
        ->and($readiness['checks']['purchases']['legacy'])->toBe(1)
        ->and($readiness['checks']['operational_expenses']['legacy'])->toBe(1)
        ->and($readiness['checks']['stock_movements']['legacy'])->toBe(1)
        ->and($readiness['checks']['stock_movements']['ready'])->toBeFalse();
});

test('toko cutover readiness blocks cutoff when latest reconciliation has total gaps', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-cutover-reconciliation-gap',
        'status' => Company::STATUS_ACTIVE,
    ]);

    Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => null,
        'quotation_id' => null,
        'number' => '100001',
        'status' => Invoice::STATUS_PAID,
        'issued_at' => '2026-01-05',
        'due_at' => null,
        'paid_at' => '2026-01-05',
        'subtotal' => 25000,
        'tax_total' => 0,
        'grand_total' => 25000,
        'metadata' => ['source' => 'legacy_toko_sale', 'legacy_toko' => ['nota' => '100001']],
    ]);

    $path = base_path('storage/framework/testing/toko-cutover-reconciliation-gap.sql');
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, <<<'SQL'
INSERT INTO `sale` (`nota`, `tgl`, `pelanggan`, `total`, `status`, `oleh`, `no`) VALUES
('100001', '2026-01-05', '0001', 25000, 'dibayar', 'admin', 1);
SQL);

    ImportExportRun::query()->create([
        'resource' => 'toko_pos_history',
        'operation' => 'import',
        'status' => 'completed',
        'source_path' => $path,
        'processed_rows' => 1,
        'total_rows' => 1,
        'meta' => [
            'company_id' => $company->id,
            'reconciliation' => [
                'sales' => [
                    'legacy_count' => 1,
                    'target_count' => 1,
                    'count_gap' => 0,
                    'legacy_total' => 25000,
                    'target_total' => 20000,
                    'total_gap' => 5000,
                    'matched' => false,
                ],
            ],
        ],
        'completed_at' => now(),
    ]);

    $readiness = app(TokoPosCutoverReadinessService::class)->snapshot($path, $company->id);

    expect($readiness['checks']['sales']['ready'])->toBeTrue()
        ->and($readiness['reconciliation']['ready'])->toBeFalse()
        ->and($readiness['reconciliation']['checks']['sales']['matched'])->toBeFalse()
        ->and($readiness['ready'])->toBeFalse();
});

test('toko cutover readiness blocks cutoff when latest monthly report reconciliation has gaps', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-cutover-monthly-gap',
        'status' => Company::STATUS_ACTIVE,
    ]);

    Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => null,
        'quotation_id' => null,
        'number' => '100001',
        'status' => Invoice::STATUS_PAID,
        'issued_at' => '2026-01-05',
        'due_at' => null,
        'paid_at' => '2026-01-05',
        'subtotal' => 25000,
        'tax_total' => 0,
        'grand_total' => 25000,
        'metadata' => ['source' => 'legacy_toko_sale', 'legacy_toko' => ['nota' => '100001']],
    ]);

    $path = base_path('storage/framework/testing/toko-cutover-monthly-gap.sql');
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, <<<'SQL'
INSERT INTO `sale` (`nota`, `tgl`, `pelanggan`, `total`, `status`, `oleh`, `no`) VALUES
('100001', '2026-01-05', '0001', 25000, 'dibayar', 'admin', 1);
SQL);

    ImportExportRun::query()->create([
        'resource' => 'toko_pos_history',
        'operation' => 'import',
        'status' => 'completed',
        'source_path' => $path,
        'processed_rows' => 1,
        'total_rows' => 1,
        'meta' => [
            'company_id' => $company->id,
            'reconciliation' => [
                'sales' => [
                    'legacy_count' => 1,
                    'target_count' => 1,
                    'count_gap' => 0,
                    'legacy_total' => 25000,
                    'target_total' => 25000,
                    'total_gap' => 0,
                    'matched' => true,
                ],
            ],
            'monthly_reconciliation' => [
                '2026-01' => [
                    'legacy' => ['sales' => 25000, 'purchases' => 0, 'operational_expenses' => 0, 'net_income' => 25000],
                    'target' => ['sales' => 20000, 'purchases' => 0, 'operational_expenses' => 0, 'net_income' => 20000],
                    'gaps' => ['sales' => 5000, 'purchases' => 0, 'operational_expenses' => 0, 'net_income' => 5000],
                    'matched' => false,
                ],
            ],
        ],
        'completed_at' => now(),
    ]);

    $readiness = app(TokoPosCutoverReadinessService::class)->snapshot($path, $company->id);

    expect($readiness['checks']['sales']['ready'])->toBeTrue()
        ->and($readiness['reconciliation']['checks']['sales']['matched'])->toBeTrue()
        ->and($readiness['reconciliation']['monthly']['2026-01']['matched'])->toBeFalse()
        ->and($readiness['reconciliation']['ready'])->toBeFalse()
        ->and($readiness['ready'])->toBeFalse();
});

test('toko cutover readiness blocks cutoff when latest cash bank reconciliation has gaps', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-cutover-cash-bank-gap',
        'status' => Company::STATUS_ACTIVE,
    ]);

    Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => null,
        'quotation_id' => null,
        'number' => '100001',
        'status' => Invoice::STATUS_PAID,
        'issued_at' => '2026-01-05',
        'due_at' => null,
        'paid_at' => '2026-01-05',
        'subtotal' => 25000,
        'tax_total' => 0,
        'grand_total' => 25000,
        'metadata' => ['source' => 'legacy_toko_sale', 'legacy_toko' => ['nota' => '100001']],
    ]);

    $path = base_path('storage/framework/testing/toko-cutover-cash-bank-gap.sql');
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, <<<'SQL'
INSERT INTO `sale` (`nota`, `tgl`, `pelanggan`, `total`, `status`, `oleh`, `no`) VALUES
('100001', '2026-01-05', '0001', 25000, 'dibayar', 'admin', 1);
SQL);

    ImportExportRun::query()->create([
        'resource' => 'toko_pos_history',
        'operation' => 'import',
        'status' => 'completed',
        'source_path' => $path,
        'processed_rows' => 1,
        'total_rows' => 1,
        'meta' => [
            'company_id' => $company->id,
            'reconciliation' => [
                'sales' => [
                    'legacy_count' => 1,
                    'target_count' => 1,
                    'count_gap' => 0,
                    'legacy_total' => 25000,
                    'target_total' => 25000,
                    'total_gap' => 0,
                    'matched' => true,
                ],
            ],
            'cash_bank_reconciliation' => [
                'sales_payments' => [
                    'CASH' => [
                        'legacy_total' => 25000,
                        'target_total' => 20000,
                        'gap' => 5000,
                        'matched' => false,
                    ],
                ],
            ],
        ],
        'completed_at' => now(),
    ]);

    $readiness = app(TokoPosCutoverReadinessService::class)->snapshot($path, $company->id);

    expect($readiness['checks']['sales']['ready'])->toBeTrue()
        ->and($readiness['reconciliation']['cash_bank']['sales_payments']['CASH']['matched'])->toBeFalse()
        ->and($readiness['reconciliation']['ready'])->toBeFalse()
        ->and($readiness['ready'])->toBeFalse();
});
