<?php

use App\Livewire\Admin\TokoPosAddon;
use App\Models\Client;
use App\Models\Company;
use App\Models\DeliveryLetter;
use App\Models\ImportExportRun;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Services\Enterprise\LicenseGuard;
use App\Support\TokoLegacyImportPreviewService;
use App\Support\TokoPosReportService;
use Livewire\Livewire;

beforeEach(function (): void {
    if (! LicenseGuard::hasRuntimeObfuscatorKey('toko_pos')) {
        $this->markTestSkipped('Enterprise runtime obfuscator key is not available.');
    }
});

function setTokoLegacyHistoryLicenseFeatures(array $features): void
{
    Setting::updateOrCreate(
        ['key' => 'app.company_name'],
        ['value' => 'PT. PasPapan Indonesia', 'group' => 'identity', 'type' => 'text']
    );
    Setting::updateOrCreate(
        ['key' => 'app.support_contact'],
        ['value' => 'https://t.me/RiprLutuk', 'group' => 'identity', 'type' => 'text']
    );
    Setting::updateOrCreate(
        ['key' => 'enterprise_license_key'],
        [
            'value' => makeEnterpriseTestLicense(['features' => $features]),
            'group' => 'enterprise',
            'type' => 'textarea',
        ]
    );

    Setting::flushCache('app.company_name');
    Setting::flushCache('app.support_contact');
    Setting::flushCache('enterprise_license_key');
    LicenseGuard::clearLicenseCache();
}

test('toko historical import creates quotations returns and delivery letters idempotently', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-history',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    Client::query()->create([
        'company_id' => $company->id,
        'name' => 'WARDI',
        'code' => '0001',
        'status' => Client::STATUS_ACTIVE,
        'contact_phone' => '085716004883',
        'address' => 'Srengseng',
    ]);
    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU-HIST',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 10000,
        'cost_price' => 6000,
        'stock_tracking' => true,
        'metadata' => ['legacy_toko' => ['kode' => 'BRG001']],
    ]);
    Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Digital Teknik',
        'status' => Vendor::STATUS_ACTIVE,
        'metadata' => ['legacy_toko' => ['kode' => 'SUP001']],
    ]);
    $path = base_path('storage/framework/testing/toko-history.sql');
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, <<<'SQL'
INSERT INTO `quotation` (`nota`, `nomor`, `tgl`, `due`, `pelanggan`, `modal`, `total`, `diskon`, `potongan`, `biayatambahan`, `status`, `oleh`, `notainvoice`, `keterangan`, `no`) VALUES
('Q0001', 'QTN-LEG-001', '2026-01-02', '2026-01-16', '0001', 6000, 20000, 0, 0, 0, 'Open', 'admin', '', 'Legacy quote', 1);
INSERT INTO `quotation_list` (`nota`, `kode`, `nama`, `harga`, `jumlah`, `hargaakhir`, `modal`, `conv`, `no`) VALUES
('Q0001', 'BRG001', 'Filter AC', 10000, 2, 20000, 6000, 0, 1);
INSERT INTO `retur` (`nota`, `tanggal`, `dana`, `status`, `petugas`, `no`) VALUES
('R0001', '2026-01-03', 10000, 'Retur', 'admin', 1);
INSERT INTO `dataretur` (`nota`, `kode`, `nama`, `jumlah`, `harga`, `hargaakhir`, `no`) VALUES
('R0001', 'BRG001', 'Filter AC', 1, 10000, 10000, 1);
INSERT INTO `surat` (`nota`, `nosurat`, `tanggal`, `kode_pelanggan`, `tujuan`, `notelp`, `alamat`, `driver`, `nohp`, `nopol`, `oleh`, `no`) VALUES
('S0001', 'SR-LEG-001', '2026-01-04', '0001', 'Gudang Srengseng', '085716004883', 'Srengseng', 'Admin', '0812', 'B 1234 PTM', 'Admin', 1);
INSERT INTO `sale` (`nota`, `tgl`, `pelanggan`, `total`, `status`, `oleh`, `no`) VALUES
('100001', '2026-01-05', '0001', 25000, 'dibayar', 'admin', 1);
INSERT INTO `invoicejual` (`nota`, `kode`, `nama`, `harga`, `jumlah`, `hargaakhir`, `modal`, `no`) VALUES
('100001', 'BRG001', 'Filter AC', 12500, 2, 25000, 6000, 1);
INSERT INTO `bayar` (`nota`, `tanggal`, `jumlah`, `tipe`, `rekening`, `keterangan`, `no`) VALUES
('100001', '2026-01-05', 25000, 'Cash', 'CASH', 'Lunas kasir', 1);
INSERT INTO `buy` (`nota`, `tanggal`, `tglsale`, `biaya`, `total`, `supplier`, `kasir`, `keterangan`, `no`, `status`, `diterima`, `nopo`) VALUES
('PO0001', '2026-01-06', '2026-01-20', 2500, 32500, 'SUP001', 'admin', 'Legacy purchase', 1, 'hutang', 'Admin Gudang', 'PO-LEG-001');
INSERT INTO `invoicebeli` (`nota`, `kode`, `nama`, `harga`, `jumlah`, `terima`, `hargaakhir`, `no`) VALUES
('PO0001', 'BRG001', 'Filter AC', 15000, 2, 2, 30000, 1);
INSERT INTO `hutang` (`nota`, `kreditur`, `tgl`, `due`, `hutang`, `keterangan`, `status`, `no`) VALUES
('PO0001', 'SUP001', '2026-01-06', '2026-01-20', 32500, 'Tempo supplier', 'hutang', 1);
INSERT INTO `operasional_tipe` (`Kode`, `nama`, `no`) VALUES
('0001', 'Listrik', 1);
INSERT INTO `operasional` (`kode`, `nama`, `tanggal`, `biaya`, `keterangan`, `kasir`, `tipe`, `no`) VALUES
('OP0001', 'Bayar listrik toko', '2026-01-07', 125000, 'Token listrik', 'admin', 'Listrik', 1);
INSERT INTO `stok_masuk` (`nota`, `cabang`, `tgl`, `supplier`, `userid`, `no`) VALUES
('SM0001', '01', '2026-01-08', 'SUP001', '1', 1);
INSERT INTO `stok_masuk_daftar` (`nota`, `kode_barang`, `nama`, `jumlah`, `no`) VALUES
('SM0001', 'BRG001', 'Filter AC', 4, 1);
INSERT INTO `stok_keluar` (`nota`, `cabang`, `tgl`, `pelanggan`, `userid`, `keterangan`, `no`) VALUES
('SK0001', '01', '2026-01-09', 'WARDI', '1', 'Kirim barang', 1);
INSERT INTO `stok_keluar_daftar` (`nota`, `kode_barang`, `nama`, `jumlah`, `no`) VALUES
('SK0001', 'BRG001', 'Filter AC', 2, 1);
INSERT INTO `stok_sesuai` (`nota`, `tgl`, `oleh`, `keterangan`, `no`) VALUES
('SS0001', '2026-01-10', 'Admin', 'Opname rak', 1);
INSERT INTO `stok_sesuai_daftar` (`nota`, `kode_brg`, `nama`, `sebelum`, `sesudah`, `selisih`, `catatan`, `no`) VALUES
('SS0001', 'BRG001', 'Filter AC', 10, 7, -3, 'Rusak', 1);
SQL);

    $service = app(TokoLegacyImportPreviewService::class);
    $run = $service->importHistoricalDocuments($actor, $path, $company->id);
    $secondRun = $service->importHistoricalDocuments($actor, $path, $company->id);

    expect($run->status)->toBe('completed')
        ->and($run->resource)->toBe('toko_pos_history')
        ->and($run->meta['summary']['quotations']['created'])->toBe(1)
        ->and($run->meta['summary']['returns']['created'])->toBe(1)
        ->and($run->meta['summary']['delivery_letters']['created'])->toBe(1)
        ->and($run->meta['summary']['sales']['created'])->toBe(1)
        ->and($run->meta['summary']['purchases']['created'])->toBe(1)
        ->and($run->meta['summary']['operational_expenses']['created'])->toBe(1)
        ->and($run->meta['summary']['stock_movements']['created'])->toBe(3)
        ->and($run->meta['reconciliation']['sales']['matched'])->toBeTrue()
        ->and((float) $run->meta['reconciliation']['sales']['legacy_total'])->toBe(25000.0)
        ->and((float) $run->meta['reconciliation']['sales']['target_total'])->toBe(25000.0)
        ->and($run->meta['reconciliation']['purchases']['matched'])->toBeTrue()
        ->and((float) $run->meta['reconciliation']['purchases']['legacy_total'])->toBe(32500.0)
        ->and((float) $run->meta['reconciliation']['operational_expenses']['target_total'])->toBe(125000.0)
        ->and($run->meta['reconciliation']['stock_movements']['legacy_count'])->toBe(3)
        ->and($run->meta['reconciliation']['stock_movements']['target_count'])->toBe(3)
        ->and($run->meta['monthly_reconciliation']['2026-01']['matched'])->toBeTrue()
        ->and((float) $run->meta['monthly_reconciliation']['2026-01']['legacy']['sales'])->toBe(25000.0)
        ->and((float) $run->meta['monthly_reconciliation']['2026-01']['target']['purchases'])->toBe(32500.0)
        ->and((float) $run->meta['monthly_reconciliation']['2026-01']['legacy']['operational_expenses'])->toBe(125000.0)
        ->and((float) $run->meta['monthly_reconciliation']['2026-01']['target']['net_income'])->toBe(-132500.0)
        ->and($run->meta['rollback_report']['reversible'])->toBeTrue()
        ->and($run->meta['rollback_report']['targets']['invoices']['count'])->toBe(1)
        ->and($run->meta['rollback_report']['targets']['vendor_bills']['count'])->toBe(1)
        ->and($run->meta['rollback_report']['targets']['journal_entries']['count'])->toBe(1)
        ->and($run->meta['rollback_report']['targets']['stock_movements']['count'])->toBe(4)
        ->and($run->meta['rollback_report']['targets']['delivery_letters']['count'])->toBe(1)
        ->and($run->meta['cash_bank_reconciliation']['sales_payments']['CASH']['matched'])->toBeTrue()
        ->and((float) $run->meta['cash_bank_reconciliation']['sales_payments']['CASH']['legacy_total'])->toBe(25000.0)
        ->and((float) $run->meta['cash_bank_reconciliation']['sales_payments']['CASH']['target_total'])->toBe(25000.0)
        ->and($secondRun->meta['summary']['quotations']['skipped_existing'])->toBe(1)
        ->and($secondRun->meta['summary']['sales']['skipped_existing'])->toBe(1)
        ->and($secondRun->meta['summary']['purchases']['skipped_existing'])->toBe(1)
        ->and($secondRun->meta['summary']['operational_expenses']['skipped_existing'])->toBe(1)
        ->and($secondRun->meta['summary']['stock_movements']['skipped_existing'])->toBe(3)
        ->and(Quotation::query()->where('company_id', $company->id)->where('number', 'QTN-LEG-001')->count())->toBe(1)
        ->and(Invoice::query()->where('company_id', $company->id)->where('number', '100001')->where('metadata->source', 'legacy_toko_sale')->count())->toBe(1)
        ->and(Invoice::query()->where('company_id', $company->id)->where('number', '100001')->firstOrFail()->items)->toHaveCount(1)
        ->and((float) Invoice::query()->where('company_id', $company->id)->where('number', '100001')->firstOrFail()->metadata['payments'][0]['amount'])->toBe(25000.0)
        ->and(VendorBill::query()->where('company_id', $company->id)->where('number', 'PO0001')->where('metadata->source', 'legacy_toko_purchase')->count())->toBe(1)
        ->and(VendorBill::query()->where('company_id', $company->id)->where('number', 'PO0001')->firstOrFail()->items)->toHaveCount(1)
        ->and(VendorBill::query()->where('company_id', $company->id)->where('number', 'PO0001')->firstOrFail()->status)->toBe(VendorBill::STATUS_POSTED)
        ->and((float) VendorBill::query()->where('company_id', $company->id)->where('number', 'PO0001')->firstOrFail()->metadata['payable']['amount'])->toBe(32500.0)
        ->and(JournalEntry::query()->where('company_id', $company->id)->where('source_type', 'toko_pos_operational_expense')->where('reference_number', 'OP0001')->count())->toBe(1)
        ->and(JournalEntry::query()->where('company_id', $company->id)->where('source_type', 'toko_pos_operational_expense')->where('reference_number', 'OP0001')->firstOrFail()->metadata['expense_type'])->toBe('Listrik')
        ->and((float) JournalEntry::query()->where('company_id', $company->id)->where('source_type', 'toko_pos_operational_expense')->where('reference_number', 'OP0001')->firstOrFail()->lines()->sum('debit'))->toBe(125000.0)
        ->and(StockMovement::query()->where('company_id', $company->id)->whereIn('metadata->source', ['legacy_toko_stock_in', 'legacy_toko_stock_out', 'legacy_toko_stock_adjustment'])->count())->toBe(3)
        ->and((float) StockMovement::query()->where('company_id', $company->id)->where('metadata->source', 'legacy_toko_stock_adjustment')->firstOrFail()->quantity)->toBe(3.0)
        ->and(StockMovement::query()->where('company_id', $company->id)->where('metadata->source', 'legacy_toko_return')->count())->toBe(1)
        ->and(DeliveryLetter::query()->where('company_id', $company->id)->where('number', 'SR-LEG-001')->count())->toBe(1);
});

test('toko historical import preserves purchase headers without item rows as summary bills', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik Summary',
        'slug' => 'pandan-teknik-summary-purchase',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Legacy Supplier',
        'status' => Vendor::STATUS_ACTIVE,
        'metadata' => ['legacy_toko' => ['kode' => 'SUP001']],
    ]);
    $path = base_path('storage/framework/testing/toko-summary-purchase.sql');
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, <<<'SQL'
INSERT INTO `buy` (`nota`, `tanggal`, `tglsale`, `biaya`, `total`, `supplier`, `kasir`, `keterangan`, `no`, `status`, `diterima`, `nopo`) VALUES
('PO-SUM-001', '2026-01-06', '2026-01-20', 5000, 125000, 'SUP001', 'admin', 'Header only', 1, 'dibayar', 'Admin Gudang', 'PO-SUM-001');
SQL);

    $run = app(TokoLegacyImportPreviewService::class)->importHistoricalDocuments($actor, $path, $company->id);
    $bill = VendorBill::query()->where('company_id', $company->id)->where('number', 'PO-SUM-001')->firstOrFail();

    expect($run->status)->toBe('completed')
        ->and($run->meta['summary']['purchases']['created'])->toBe(1)
        ->and($run->meta['summary']['purchases']['invalid'])->toBe(0)
        ->and($run->meta['reconciliation']['purchases']['matched'])->toBeTrue()
        ->and((float) $run->meta['reconciliation']['purchases']['legacy_total'])->toBe(125000.0)
        ->and((float) $run->meta['reconciliation']['purchases']['target_total'])->toBe(125000.0)
        ->and($bill->items)->toHaveCount(1)
        ->and($bill->items->first()->product_id)->toBeNull()
        ->and($bill->items->first()->description)->toBe('Legacy purchase summary - detail items missing in dump')
        ->and((float) $bill->items->first()->line_total)->toBe(120000.0)
        ->and($bill->metadata['payable'])->toBeNull();
});

test('toko historical import maps production sale tglsale and hydrates existing legacy invoice dates', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik Sale Date',
        'slug' => 'pandan-teknik-sale-date',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU-HIST-DATE',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 10000,
        'cost_price' => 6000,
        'stock_tracking' => true,
        'metadata' => ['legacy_toko' => ['kode' => 'BRG001']],
    ]);
    Invoice::query()->create([
        'company_id' => $company->id,
        'number' => '100001',
        'status' => Invoice::STATUS_PAID,
        'issued_at' => null,
        'due_date' => null,
        'subtotal' => 0,
        'tax_total' => 0,
        'grand_total' => 25000,
        'metadata' => ['source' => 'legacy_toko_sale', 'legacy_toko' => ['nota' => '100001']],
    ])->items()->create([
        'product_id' => $product->id,
        'description' => 'Filter AC',
        'quantity' => 2,
        'unit_price' => 12500,
        'tax_rate' => 0,
        'line_total' => 25000,
    ]);

    $path = base_path('storage/framework/testing/toko-history-sale-date.sql');
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, <<<'SQL'
INSERT INTO `sale` (`nota`, `nomor`, `tglsale`, `duedate`, `total`, `pelanggan`, `kasir`, `keterangan`, `no`, `status`) VALUES
('100001', 'INV100001', '2026-01-29', '2026-02-05', 25000, '0001', 'admin', '', 1, 'dibayar');
INSERT INTO `invoicejual` (`nota`, `kode`, `nama`, `harga`, `jumlah`, `hargaakhir`, `modal`, `no`) VALUES
('100001', 'BRG001', 'Filter AC', 12500, 2, 25000, 6000, 1);
INSERT INTO `bayar` (`nota`, `tanggal`, `jumlah`, `tipe`, `rekening`, `keterangan`, `no`) VALUES
('100001', '2026-01-29', 25000, 'Cash', 'CASH', 'Lunas kasir', 1);
SQL);

    $run = app(TokoLegacyImportPreviewService::class)->importHistoricalDocuments($actor, $path, $company->id);
    $invoice = Invoice::query()->where('company_id', $company->id)->where('number', '100001')->firstOrFail();

    expect($run->meta['summary']['sales']['skipped_existing'])->toBe(1)
        ->and($invoice->issued_at?->toDateString())->toBe('2026-01-29')
        ->and($invoice->due_at?->toDateString())->toBe('2026-02-05')
        ->and($invoice->paid_at?->toDateString())->toBe('2026-01-29')
        ->and((float) $invoice->metadata['paid_total'])->toBe(25000.0);

    unlink($path);
});

test('toko historical import migrates legacy retail struk from mutasi and transaksimasuk', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik Retail',
        'slug' => 'pandan-teknik-retail-history',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Perak las HARIS',
        'sku' => 'SKU-HARIS',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 45000,
        'cost_price' => 28000,
        'stock_tracking' => true,
        'metadata' => ['legacy_toko' => ['kode' => '15']],
    ]);

    $path = base_path('storage/framework/testing/toko-history-retail.sql');
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, <<<'SQL'
INSERT INTO `mutasi` (`namauser`, `tgl`, `jam`, `kodebarang`, `sisa`, `jumlah`, `kegiatan`, `keterangan`, `no`, `status`) VALUES
('kasir', '2026-06-08', '03:44', '15', 36, -2, 'menjual barang memakai struk', '00519', 1, 'berhasil');
INSERT INTO `transaksimasuk` (`nota`, `kode`, `nama`, `harga`, `hargabeli`, `jumlah`, `hargaakhir`, `hargabeliakhir`, `retur`, `no`) VALUES
('00519', '15', 'Perak las HARIS', 45000, 28000, 2, 90000, 56000, 'NO', 1);
SQL);

    $run = app(TokoLegacyImportPreviewService::class)->importHistoricalDocuments($actor, $path, $company->id);
    $invoice = Invoice::query()->where('company_id', $company->id)->where('number', '00519')->firstOrFail();
    $report = app(TokoPosReportService::class)->summary($company->id, '2026-06-01', '2026-06-30');

    expect($run->meta['summary']['retail_sales']['created'])->toBe(1)
        ->and($invoice->metadata['source'])->toBe('legacy_toko_retail_sale')
        ->and($invoice->issued_at?->toDateString())->toBe('2026-06-08')
        ->and((float) $invoice->grand_total)->toBe(90000.0)
        ->and($invoice->items)->toHaveCount(1)
        ->and((float) $invoice->items->first()->quantity)->toBe(2.0)
        ->and($report['sales']['total'])->toBe(90000.0)
        ->and($report['gross_profit']['estimated'])->toBe(34000.0);

    unlink($path);
});

test('toko historical import keeps stock and return rows for legacy products missing from master dump', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik Placeholder',
        'slug' => 'pandan-teknik-placeholder-product',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $path = base_path('storage/framework/testing/toko-placeholder-product.sql');
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, <<<'SQL'
INSERT INTO `retur` (`nota`, `tanggal`, `dana`, `status`, `petugas`, `no`) VALUES
('R-MISS-001', '2026-01-03', 10000, 'Retur', 'admin', 1);
INSERT INTO `dataretur` (`nota`, `kode`, `nama`, `jumlah`, `harga`, `hargaakhir`, `no`) VALUES
('R-MISS-001', 'OLD404', 'Barang Lama Hilang', 2, 10000, 20000, 1);
INSERT INTO `stok_masuk` (`nota`, `cabang`, `tgl`, `supplier`, `userid`, `no`) VALUES
('SM-MISS-001', '01', '2026-01-08', 'SUP001', '1', 1);
INSERT INTO `stok_masuk_daftar` (`nota`, `kode_barang`, `nama`, `jumlah`, `no`) VALUES
('SM-MISS-001', 'OLD404', 'Barang Lama Hilang', 3, 1);
SQL);

    $run = app(TokoLegacyImportPreviewService::class)->importHistoricalDocuments($actor, $path, $company->id);
    $placeholder = Product::query()->where('company_id', $company->id)->where('sku', 'LEGACY-TOKO-OLD404')->firstOrFail();

    expect($run->status)->toBe('completed')
        ->and($run->meta['summary']['returns']['created'])->toBe(1)
        ->and($run->meta['summary']['returns']['invalid'])->toBe(0)
        ->and($run->meta['summary']['stock_movements']['created'])->toBe(1)
        ->and($run->meta['summary']['stock_movements']['invalid'])->toBe(0)
        ->and($run->meta['reconciliation']['returns']['matched'])->toBeTrue()
        ->and($run->meta['reconciliation']['stock_movements']['matched'])->toBeTrue()
        ->and($placeholder->status)->toBe(Product::STATUS_INACTIVE)
        ->and($placeholder->metadata['source'])->toBe('legacy_toko_placeholder')
        ->and($placeholder->metadata['legacy_toko']['missing_from_master_dump'])->toBeTrue()
        ->and(StockMovement::query()->where('company_id', $company->id)->where('product_id', $placeholder->id)->count())->toBe(2);
});

test('toko add-on can run historical import from selected dump', function (): void {
    setTokoLegacyHistoryLicenseFeatures(['toko_pos']);

    [$company, $actor] = tokoHistoryFixtureWithExistingMasters();
    $path = base_path('../toko-pandan/database/toko.sql');

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'migration'])
        ->call('importHistoricalDocuments')
        ->assertSee('Historical Reconciliation')
        ->assertSee('Monthly Report Reconciliation')
        ->assertSee('Cash/Bank Reconciliation')
        ->assertSee('Sales')
        ->assertSee('Operational Expenses');

    expect(ImportExportRun::query()
        ->where('resource', 'toko_pos_history')
        ->where('source_path', $path)
        ->where('status', 'completed')
        ->exists())->toBeTrue();
});

function tokoHistoryFixtureWithExistingMasters(): array
{
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-history-livewire',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    Client::query()->create([
        'company_id' => $company->id,
        'name' => 'WARDI',
        'code' => '0001',
        'status' => Client::STATUS_ACTIVE,
    ]);

    return [$company, $actor];
}
