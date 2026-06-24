<?php

use App\Livewire\Admin\TokoPosAddon;
use App\Models\AccountingAccount;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Services\Enterprise\LicenseGuard;
use App\Support\TokoPosInventoryAdjustmentService;
use App\Support\TokoPosPurchaseService;
use App\Support\TokoPosReportService;
use App\Support\TokoPosSalesService;
use Livewire\Livewire;

beforeEach(function () {
    if (! LicenseGuard::hasRuntimeObfuscatorKey('toko_pos')) {
        test()->markTestSkipped('Enterprise runtime obfuscator key is not available.');
    }
});

function setTokoPosInventoryLicenseFeatures(array $features): void
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

test('toko inventory service records sales return purchase return and stock opname', function (): void {
    [$company, $actor, $product] = tokoInventoryReportFixture();

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 10,
        'unit_cost' => 6500,
        'occurred_at' => now(),
        'metadata' => ['source' => 'opening_test'],
    ]);

    $service = app(TokoPosInventoryAdjustmentService::class);
    $salesReturn = $service->recordSalesReturn($actor, [
        'company_id' => $company->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'reference_number' => 'INV-RET',
    ]);
    $purchaseReturn = $service->recordPurchaseReturn($actor, [
        'company_id' => $company->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'reference_number' => 'BILL-RET',
    ]);
    $opname = $service->recordStockOpname($actor, [
        'company_id' => $company->id,
        'product_id' => $product->id,
        'counted_quantity' => 15,
    ]);

    expect($salesReturn->type)->toBe(StockMovement::TYPE_IN)
        ->and($salesReturn->metadata['source'])->toBe('toko_pos_sales_return')
        ->and($purchaseReturn->type)->toBe(StockMovement::TYPE_OUT)
        ->and($purchaseReturn->metadata['source'])->toBe('toko_pos_purchase_return')
        ->and($opname->type)->toBe(StockMovement::TYPE_ADJUSTMENT)
        ->and($opname->quantity)->toEqual('4.000')
        ->and($product->fresh()->stockBalance())->toBe(15.0);
});

test('toko stock balance ignores non impacting legacy audit movements', function (): void {
    [$company, $actor, $product] = tokoInventoryReportFixture();

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 10,
        'unit_cost' => 6000,
        'occurred_at' => now(),
        'metadata' => ['source' => 'legacy_toko_opening_stock'],
    ]);
    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_ADJUSTMENT,
        'quantity' => 999,
        'unit_cost' => 6000,
        'occurred_at' => now(),
        'metadata' => ['source' => 'legacy_toko_stock_adjustment', 'affects_stock' => false],
    ]);

    $report = app(TokoPosReportService::class)->summary($company->id);

    expect($product->fresh()->stockBalance())->toBe(10.0)
        ->and($report['stock_valuation']['estimated'])->toBe(60000.0)
        ->and($report['stock_card'])->toHaveCount(2);
});

test('toko stock valuation prefers legacy barang snapshot when available', function (): void {
    [$company, $actor, $product] = tokoInventoryReportFixture();

    $product->forceFill([
        'cost_price' => 9000,
        'selling_price' => 15000,
        'reorder_point' => 5,
        'metadata' => [
            'source' => 'legacy_toko_import',
            'legacy_toko' => [
                'sisa' => 12,
                'terjual' => 4,
                'terbeli' => 16,
            ],
        ],
    ])->save();

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 1,
        'unit_cost' => 9000,
        'occurred_at' => now(),
        'metadata' => ['source' => 'legacy_toko_opening_stock'],
    ]);

    $report = app(TokoPosReportService::class)->summary($company->id);

    expect($report['stock_valuation']['cost'])->toBe(108000.0)
        ->and($report['stock_valuation']['revenue'])->toBe(180000.0)
        ->and($report['stock_valuation']['profit'])->toBe(72000.0)
        ->and($report['stock_valuation']['sold_revenue'])->toBe(60000.0);
});

test('toko report service summarizes sales purchases profit stock card low stock and aging', function (): void {
    [$company, $actor, $product] = tokoInventoryReportFixture();
    $vendor = Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Digital Teknik',
        'status' => Vendor::STATUS_ACTIVE,
    ]);

    app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 10000,
        ]],
    ]);
    app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_cost' => 6000,
        ]],
    ]);

    $report = app(TokoPosReportService::class)->summary($company->id);

    expect($report['sales']['count'])->toBe(1)
        ->and($report['sales']['total'])->toBe(20000.0)
        ->and($report['sales']['by_date'])->toHaveCount(1)
        ->and($report['sales']['by_product'][0]['product'])->toBe('Filter AC')
        ->and($report['purchases']['count'])->toBe(1)
        ->and($report['purchases']['total'])->toBe(18000.0)
        ->and($report['purchases']['by_vendor'][0]['vendor'])->toBe('Digital Teknik')
        ->and($report['purchases']['by_product'][0]['product'])->toBe('Filter AC')
        ->and($report['gross_profit']['estimated'])->toBe(8000.0)
        ->and($report['stock_valuation']['estimated'])->toBe(6000.0)
        ->and($report['stock_valuation']['cost'])->toBe(6000.0)
        ->and($report['stock_valuation']['revenue'])->toBe(10000.0)
        ->and($report['stock_valuation']['profit'])->toBe(4000.0)
        ->and($report['low_stock'])->toHaveCount(1)
        ->and($report['stock_card'])->toHaveCount(2)
        ->and($report['aging']['accounts_receivable'])->toBe(20000.0)
        ->and($report['aging']['accounts_payable'])->toBe(18000.0);
});

test('toko report service includes imported legacy toko sales and purchases after cutover', function (): void {
    [$company, $actor, $product] = tokoInventoryReportFixture();
    $vendor = Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Digital Teknik',
        'status' => Vendor::STATUS_ACTIVE,
    ]);

    $legacySale = Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => null,
        'number' => '100001',
        'status' => Invoice::STATUS_PAID,
        'issued_at' => '2026-01-05',
        'due_date' => '2026-01-05',
        'subtotal' => 25000,
        'tax_total' => 0,
        'grand_total' => 25000,
        'metadata' => [
            'source' => 'legacy_toko_sale',
            'paid_total' => 25000,
        ],
    ]);
    $legacySale->items()->create([
        'product_id' => $product->id,
        'description' => 'Filter AC',
        'quantity' => 2,
        'unit_price' => 12500,
        'tax_rate' => 0,
        'line_total' => 25000,
    ]);

    $legacyPurchase = VendorBill::query()->create([
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'number' => 'PO0001',
        'status' => VendorBill::STATUS_POSTED,
        'issued_at' => '2026-01-06',
        'due_date' => '2026-01-20',
        'subtotal' => 30000,
        'tax_total' => 0,
        'grand_total' => 32500,
        'notes' => 'Legacy purchase',
        'metadata' => [
            'source' => 'legacy_toko_purchase',
            'payable' => ['amount' => 32500],
        ],
    ]);
    $legacyPurchase->items()->create([
        'product_id' => $product->id,
        'description' => 'Filter AC',
        'quantity' => 2,
        'unit_cost' => 15000,
        'tax_rate' => 0,
        'line_total' => 30000,
    ]);

    $report = app(TokoPosReportService::class)->summary($company->id, '2026-01-01', '2026-01-31');

    expect($report['sales']['count'])->toBe(1)
        ->and($report['sales']['total'])->toBe(25000.0)
        ->and($report['sales']['by_date'][0]['date'])->toBe('2026-01-05')
        ->and($report['sales']['by_product'][0]['total'])->toBe(25000.0)
        ->and($report['purchases']['count'])->toBe(1)
        ->and($report['purchases']['total'])->toBe(32500.0)
        ->and($report['purchases']['by_date'][0]['date'])->toBe('2026-01-06')
        ->and($report['purchases']['by_vendor'][0]['vendor'])->toBe('Digital Teknik')
        ->and($report['aging']['accounts_payable'])->toBe(32500.0);
});

test('toko dedicated report csv exports include sales purchase and gross profit summaries', function (): void {
    setTokoPosInventoryLicenseFeatures(['toko_pos']);

    [$company, $actor, $product] = tokoInventoryReportFixture();
    $vendor = Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Digital Teknik',
        'status' => Vendor::STATUS_ACTIVE,
    ]);

    app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 10000,
        ]],
    ]);
    app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_cost' => 6000,
        ]],
    ]);

    $sales = $this->actingAs($actor)->get(route('admin.toko.exports.report-sales'));
    $purchases = $this->actingAs($actor)->get(route('admin.toko.exports.report-purchases'));
    $grossProfit = $this->actingAs($actor)->get(route('admin.toko.exports.report-gross-profit'));

    $sales->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $purchases->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $grossProfit->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($sales->streamedContent())->toContain('section,name,quantity,total')->toContain('Filter AC')
        ->and($purchases->streamedContent())->toContain('Digital Teknik')->toContain('Filter AC')
        ->and($grossProfit->streamedContent())->toContain('metric,value')->toContain('estimated_gross_profit')->toContain('8000');
});

test('toko dedicated report csv exports include inventory pnl and ar ap aging parity', function (): void {
    setTokoPosInventoryLicenseFeatures(['toko_pos']);

    [$company, $actor, $product] = tokoInventoryReportFixture();
    $vendor = Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Digital Teknik',
        'status' => Vendor::STATUS_ACTIVE,
    ]);

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 7,
        'unit_cost' => 6000,
        'occurred_at' => '2026-06-01 09:00:00',
        'metadata' => ['source' => 'opening_test'],
    ]);

    $unpaidInvoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'due_date' => '2026-06-10',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 10000,
        ]],
    ]);
    $unpaidInvoice->forceFill(['issued_at' => '2026-06-08', 'due_at' => '2026-06-10'])->save();

    $paidInvoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'paid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 12000,
        ]],
    ]);
    $paidInvoice->forceFill(['issued_at' => '2026-06-09', 'due_at' => '2026-06-09'])->save();

    $unpaidBill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'payment_status' => 'unpaid',
        'due_at' => '2026-06-12',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_cost' => 6000,
        ]],
    ]);
    $unpaidBill->forceFill(['issued_at' => '2026-06-08', 'due_at' => '2026-06-12'])->save();

    $account = AccountingAccount::query()->create([
        'company_id' => $company->id,
        'code' => '6100',
        'name' => 'Operational Expense',
        'type' => AccountingAccount::TYPE_EXPENSE,
        'normal_balance' => AccountingAccount::BALANCE_DEBIT,
        'is_active' => true,
    ]);
    $entry = JournalEntry::query()->create([
        'company_id' => $company->id,
        'created_by' => $actor->id,
        'number' => 'OPEX-JUN',
        'entry_date' => '2026-06-08',
        'status' => JournalEntry::STATUS_POSTED,
        'source_type' => 'toko_pos_operational_expense',
        'description' => 'Listrik Juni',
        'metadata' => ['expense_type' => 'Listrik'],
    ]);
    JournalEntryLine::query()->create([
        'journal_entry_id' => $entry->id,
        'accounting_account_id' => $account->id,
        'debit' => 5000,
        'credit' => 0,
        'memo' => 'Listrik Juni',
    ]);

    $inventory = $this->actingAs($actor)->get(route('admin.toko.exports.report-inventory-valuation', ['from' => '2026-06-01', 'to' => '2026-06-30']));
    $profitLoss = $this->actingAs($actor)->get(route('admin.toko.exports.report-profit-loss', ['from' => '2026-06-01', 'to' => '2026-06-30']));
    $arAging = $this->actingAs($actor)->get(route('admin.toko.exports.report-ar-aging', ['as_of' => '2026-06-20']));
    $apAging = $this->actingAs($actor)->get(route('admin.toko.exports.report-ap-aging', ['as_of' => '2026-06-20']));

    $inventory->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $profitLoss->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $arAging->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $apAging->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($inventory->streamedContent())
        ->toContain('sku,product,stock_balance,cost_price,selling_price,stock_cost_value,stock_selling_value,estimated_margin_value')
        ->toContain('SKU-INV')
        ->toContain('42000')
        ->toContain('28000')
        ->and($profitLoss->streamedContent())
        ->toContain('metric,value')
        ->toContain('sales_total,32000')
        ->toContain('purchase_total,18000')
        ->toContain('operational_expenses,5000')
        ->toContain('net_income,9000')
        ->and($arAging->streamedContent())
        ->toContain('invoice,customer,due_date,days_overdue,bucket,total')
        ->toContain($unpaidInvoice->number)
        ->toContain('1-30')
        ->not->toContain($paidInvoice->number)
        ->and($apAging->streamedContent())
        ->toContain('bill,vendor,due_date,days_overdue,bucket,total')
        ->toContain($unpaidBill->number)
        ->toContain('1-30')
        ->toContain('Digital Teknik');
});

test('toko pos add-on can record stock opname adjustment', function (): void {
    setTokoPosInventoryLicenseFeatures(['toko_pos']);

    [$company, $actor, $product] = tokoInventoryReportFixture();

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 5,
        'unit_cost' => 6000,
        'occurred_at' => now(),
        'metadata' => ['source' => 'opening_test'],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class)
        ->set('selectedAdjustmentProductId', (string) $product->id)
        ->set('countedStockQuantity', '8')
        ->call('recordStockOpname');

    expect(StockMovement::query()
        ->where('company_id', $company->id)
        ->where('metadata->source', 'toko_pos_stock_opname')
        ->exists())->toBeTrue()
        ->and($product->fresh()->stockBalance())->toBe(8.0);
});

test('toko reports page shows stock adjustment report and printable page', function (): void {
    setTokoPosInventoryLicenseFeatures(['toko_pos']);

    [$company, $actor, $product] = tokoInventoryReportFixture();

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 5,
        'unit_cost' => 6000,
        'occurred_at' => now(),
        'metadata' => ['source' => 'opening_test'],
    ]);

    app(TokoPosInventoryAdjustmentService::class)->recordStockOpname($actor, [
        'company_id' => $company->id,
        'product_id' => $product->id,
        'counted_quantity' => 8,
        'notes' => 'Cycle count rak depan',
    ]);

    $this->actingAs($actor)
        ->get(route('admin.toko.reports'))
        ->assertOk()
        ->assertSee('Stock Adjustment Report')
        ->assertSee('Filter AC')
        ->assertSee('Cycle count rak depan')
        ->assertSee('Print Adjustments');

    $this->actingAs($actor)
        ->get(route('admin.toko.stock-adjustments.print'))
        ->assertOk()
        ->assertSee('Toko Stock Adjustment Report')
        ->assertSee('Filter AC')
        ->assertSee('8.000')
        ->assertSee('3.000')
        ->assertSee('Cycle count rak depan');
});

test('toko reports page shows product movement report and period aware csv export', function (): void {
    setTokoPosInventoryLicenseFeatures(['toko_pos']);

    [$company, $actor, $product] = tokoInventoryReportFixture();

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 7,
        'unit_cost' => 6500,
        'reference_number' => 'STK-JUN',
        'occurred_at' => '2026-06-12 09:00:00',
        'notes' => 'June stock in',
        'metadata' => ['source' => 'manual_stock_in'],
    ]);

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_OUT,
        'quantity' => 3,
        'unit_cost' => 6500,
        'reference_number' => 'STK-MAY',
        'occurred_at' => '2026-05-12 09:00:00',
        'notes' => 'May stock out',
        'metadata' => ['source' => 'manual_stock_out'],
    ]);

    $this->actingAs($actor)
        ->get(route('admin.toko.reports', ['from' => '2026-06-01', 'to' => '2026-06-30']))
        ->assertOk()
        ->assertSee('Product Movement Report')
        ->assertSee('STK-JUN')
        ->assertSee('June stock in')
        ->assertSee('report-product-movements.csv?from=2026-06-01&amp;to=2026-06-30', false);

    $response = $this->actingAs($actor)
        ->get(route('admin.toko.exports.report-product-movements', ['from' => '2026-06-01', 'to' => '2026-06-30']));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())
        ->toContain('date,product,sku,type,reference,quantity,unit_cost,source,notes')
        ->toContain('STK-JUN')
        ->toContain('June stock in')
        ->not->toContain('STK-MAY')
        ->not->toContain('May stock out');
});

test('toko reports page applies dashboard drilldown date range from query string', function (): void {
    setTokoPosInventoryLicenseFeatures(['toko_pos']);

    [$company, $actor, $product] = tokoInventoryReportFixture();

    $juneInvoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'paid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 20000,
        ]],
    ]);
    $mayInvoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'paid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 55000,
        ]],
    ]);

    $juneInvoice->forceFill(['issued_at' => '2026-06-08'])->save();
    $mayInvoice->forceFill(['issued_at' => '2026-05-08'])->save();

    $this->actingAs($actor)
        ->get(route('admin.toko.reports', ['from' => '2026-06-01', 'to' => '2026-06-30']))
        ->assertOk()
        ->assertSee('Report Period')
        ->assertSee('2026-06-01')
        ->assertSee('2026-06-30')
        ->assertSee('report-sales.csv?from=2026-06-01&amp;to=2026-06-30', false)
        ->assertSee('20.000')
        ->assertDontSee('75,000')
        ->assertDontSee('55,000');
});

test('toko report csv exports apply date range query string', function (): void {
    setTokoPosInventoryLicenseFeatures(['toko_pos']);

    [$company, $actor, $product] = tokoInventoryReportFixture();

    $juneInvoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'paid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 20000,
        ]],
    ]);
    $mayInvoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'paid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 55000,
        ]],
    ]);

    $juneInvoice->forceFill(['issued_at' => '2026-06-08'])->save();
    $mayInvoice->forceFill(['issued_at' => '2026-05-08'])->save();

    $response = $this->actingAs($actor)
        ->get(route('admin.toko.exports.report-sales', ['from' => '2026-06-01', 'to' => '2026-06-30']));

    $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())
        ->toContain('2026-06-08')
        ->toContain('20000')
        ->not->toContain('2026-05-08')
        ->not->toContain('55000');
});

test('toko operational expense csv export applies date range query string', function (): void {
    setTokoPosInventoryLicenseFeatures(['toko_pos']);

    [$company, $actor] = tokoInventoryReportFixture();
    $account = AccountingAccount::query()->create([
        'company_id' => $company->id,
        'code' => '6100',
        'name' => 'Operational Expense',
        'type' => AccountingAccount::TYPE_EXPENSE,
        'normal_balance' => AccountingAccount::BALANCE_DEBIT,
        'is_active' => true,
    ]);

    foreach ([
        ['number' => 'OPEX-JUN', 'date' => '2026-06-08', 'description' => 'Listrik Juni', 'amount' => 125000],
        ['number' => 'OPEX-MAY', 'date' => '2026-05-08', 'description' => 'Listrik Mei', 'amount' => 99000],
    ] as $row) {
        $entry = JournalEntry::query()->create([
            'company_id' => $company->id,
            'created_by' => $actor->id,
            'number' => $row['number'],
            'entry_date' => $row['date'],
            'status' => JournalEntry::STATUS_POSTED,
            'source_type' => 'toko_pos_operational_expense',
            'description' => $row['description'],
            'metadata' => ['expense_type' => 'Listrik'],
        ]);
        JournalEntryLine::query()->create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $account->id,
            'debit' => $row['amount'],
            'credit' => 0,
            'memo' => $row['description'],
        ]);
    }

    $response = $this->actingAs($actor)
        ->get(route('admin.toko.exports.report-operational-expenses', ['from' => '2026-06-01', 'to' => '2026-06-30']));

    $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())
        ->toContain('Listrik Juni')
        ->toContain('125000')
        ->not->toContain('Listrik Mei')
        ->not->toContain('99000');
});

test('toko pos add-on can record manual stock in and stock out documents', function (): void {
    setTokoPosInventoryLicenseFeatures(['toko_pos']);

    [$company, $actor, $product] = tokoInventoryReportFixture();

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'inventory'])
        ->set('selectedManualStockProductId', (string) $product->id)
        ->set('manualStockType', 'in')
        ->set('manualStockQuantity', '7')
        ->set('manualStockReferenceNumber', 'SM-001')
        ->set('manualStockNotes', 'Manual barang masuk')
        ->call('recordManualStockMovement')
        ->set('selectedManualStockProductId', (string) $product->id)
        ->set('manualStockType', 'out')
        ->set('manualStockQuantity', '2')
        ->set('manualStockReferenceNumber', 'SK-001')
        ->set('manualStockNotes', 'Manual barang keluar')
        ->call('recordManualStockMovement')
        ->assertHasNoErrors();

    expect(StockMovement::query()->where('metadata->source', 'toko_pos_manual_stock_in')->where('reference_number', 'SM-001')->sum('quantity'))->toEqual('7.000')
        ->and(StockMovement::query()->where('metadata->source', 'toko_pos_manual_stock_out')->where('reference_number', 'SK-001')->sum('quantity'))->toEqual('2.000')
        ->and($product->fresh()->stockBalance())->toBe(5.0);
});

test('toko inventory page exposes legacy stock workflow sections in one modern workspace', function (): void {
    setTokoPosInventoryLicenseFeatures(['toko_pos']);

    [$company, $actor] = tokoInventoryReportFixture();

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'inventory'])
        ->assertSee('Stok Masuk')
        ->assertSee('Stok Keluar')
        ->assertSee('Stok Sesuai')
        ->assertSee('Surat Jalan')
        ->assertSee('#toko-stock-in', false)
        ->assertSee('#toko-stock-out', false)
        ->assertSee('#toko-stock-opname', false)
        ->assertSee(route('admin.toko.delivery-letters', absolute: false), false);
});

test('toko inventory service cancels stock movement with reversal movement', function (): void {
    [$company, $actor, $product] = tokoInventoryReportFixture();

    $service = app(TokoPosInventoryAdjustmentService::class);
    $movement = $service->recordManualStockIn($actor, [
        'company_id' => $company->id,
        'product_id' => $product->id,
        'quantity' => 4,
        'reference_number' => 'SM-CANCEL',
    ]);

    $reversal = $service->cancelMovement($actor, $movement, 'Salah input stok');

    expect($movement->fresh()->metadata['canceled_at'])->not->toBeNull()
        ->and($movement->fresh()->metadata['canceled_by'])->toBe($actor->id)
        ->and($reversal->type)->toBe(StockMovement::TYPE_OUT)
        ->and($reversal->quantity)->toEqual('4.000')
        ->and($reversal->metadata['source'])->toBe('toko_pos_stock_cancellation')
        ->and($reversal->metadata['canceled_movement_id'])->toBe($movement->id)
        ->and($product->fresh()->stockBalance())->toBe(0.0);
});

test('toko pos add-on can cancel stock movement from inventory page', function (): void {
    setTokoPosInventoryLicenseFeatures(['toko_pos']);

    [$company, $actor, $product] = tokoInventoryReportFixture();
    $movement = app(TokoPosInventoryAdjustmentService::class)->recordManualStockIn($actor, [
        'company_id' => $company->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'reference_number' => 'SM-LW-CANCEL',
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'inventory'])
        ->assertSee('Stock Cancellation')
        ->set('selectedCancelStockMovementId', (string) $movement->id)
        ->set('cancelStockMovementReason', 'Retur dokumen stok')
        ->call('cancelStockMovement')
        ->assertHasNoErrors();

    expect(StockMovement::query()->where('metadata->source', 'toko_pos_stock_cancellation')->where('metadata->canceled_movement_id', $movement->id)->exists())->toBeTrue()
        ->and($product->fresh()->stockBalance())->toBe(0.0);
});

test('toko inventory movement list uses datatable pagination and search', function (): void {
    setTokoPosInventoryLicenseFeatures(['toko_pos']);

    [$company, $actor, $product] = tokoInventoryReportFixture();

    foreach (range(1, 12) as $index) {
        StockMovement::query()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'user_id' => $actor->id,
            'type' => $index === 12 ? StockMovement::TYPE_OUT : StockMovement::TYPE_IN,
            'quantity' => $index,
            'unit_cost' => 6000,
            'reference_number' => 'STK-DT-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'occurred_at' => now()->subMinutes($index),
            'notes' => $index === 12 ? 'Special keluar rak depan' : 'Manual stok masuk',
            'metadata' => ['source' => 'toko_pos_manual_stock_'.$index],
        ]);
    }

    $component = Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'inventory'])
        ->assertSee('Stock Movement List')
        ->assertSee('Showing 1 to 10 of 12 stock movement entries')
        ->assertSee('Next')
        ->call('nextInventoryMovementPage')
        ->assertSee('Showing 11 to 12 of 12 stock movement entries')
        ->assertSee('STK-DT-01')
        ->set('inventoryMovementSearch', 'special keluar')
        ->assertSee('Showing 1 to 1 of 1 stock movement entries')
        ->assertSee('STK-DT-12')
        ->assertDontSee('Showing 1 to 10 of 12 stock movement entries');

    expect($component->get('inventoryMovementPage'))->toBe(1);
});

function tokoInventoryReportFixture(): array
{
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-inventory',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU-INV',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 10000,
        'cost_price' => 6000,
        'stock_tracking' => true,
        'reorder_point' => 10,
    ]);

    return [$company, $actor, $product];
}
