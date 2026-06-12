<?php

use App\Livewire\Admin\TokoPosAddon;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Services\Enterprise\LicenseGuard;
use App\Support\TokoPosPurchaseService;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

function setTokoPosPurchaseLicenseFeatures(array $features): void
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

test('toko purchase service creates vendor bill and stock in movement', function (): void {
    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();

    $bill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 4,
            'unit_cost' => 6500,
        ]],
    ]);

    expect($bill)->toBeInstanceOf(VendorBill::class)
        ->and($bill->status)->toBe(VendorBill::STATUS_POSTED)
        ->and($bill->grand_total)->toEqual('26000.00')
        ->and($bill->metadata['source'])->toBe('toko_pos_purchase')
        ->and($bill->items)->toHaveCount(1)
        ->and(StockMovement::query()->where('product_id', $product->id)->where('type', StockMovement::TYPE_IN)->value('quantity'))->toEqual('4.000');
});

test('toko pos add-on can create purchase from purchase cart', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class)
        ->set('selectedPurchaseVendorId', (string) $vendor->id)
        ->set('selectedPurchaseProductId', (string) $product->id)
        ->set('purchaseQuantity', '3')
        ->set('purchaseUnitCost', '7000')
        ->call('addToPurchaseCart')
        ->call('createPurchase');

    expect(VendorBill::query()->where('company_id', $company->id)->exists())->toBeTrue()
        ->and(StockMovement::query()->where('product_id', $product->id)->where('type', StockMovement::TYPE_IN)->exists())->toBeTrue();
});

test('toko pos add-on stores legacy purchase header fields and extra cost', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->set('selectedPurchaseVendorId', (string) $vendor->id)
        ->set('selectedPurchaseProductId', (string) $product->id)
        ->set('purchaseQuantity', '2')
        ->set('purchaseUnitCost', '10000')
        ->set('purchaseDueAt', '2026-06-30')
        ->set('purchasePoNumber', 'PO-PTM-777')
        ->set('purchaseExtraCost', '2500')
        ->set('purchaseReceiverName', 'Admin Gudang')
        ->set('purchaseNotes', 'Barang diterima sebagian')
        ->call('addToPurchaseCart')
        ->call('createPurchase')
        ->assertHasNoErrors();

    $bill = VendorBill::query()
        ->where('company_id', $company->id)
        ->where('metadata->source', 'toko_pos_purchase')
        ->latest('id')
        ->firstOrFail();

    expect($bill->due_at?->toDateString())->toBe('2026-06-30')
        ->and($bill->notes)->toBe('Barang diterima sebagian')
        ->and((float) $bill->grand_total)->toBe(22500.0)
        ->and($bill->metadata['po_number'])->toBe('PO-PTM-777')
        ->and((float) $bill->metadata['extra_cost'])->toBe(2500.0)
        ->and($bill->metadata['receiver_name'])->toBe('Admin Gudang')
        ->and($bill->items()->whereNull('product_id')->where('description', 'Biaya lain')->exists())->toBeTrue()
        ->and(StockMovement::query()->where('product_id', $product->id)->where('type', StockMovement::TYPE_IN)->sum('quantity'))->toEqual('2.000');

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->call('viewPurchaseBillDetail', $bill->id)
        ->assertSee('PO-PTM-777')
        ->assertSee('Admin Gudang')
        ->assertSee('Biaya lain')
        ->assertSee('2.500')
        ->assertSee('Barang diterima sebagian');
});

test('toko purchases page exposes legacy purchase workflow sections in one modern workspace', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor] = tokoPurchaseFixture();

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->assertSee('Buat Pembelian')
        ->assertSee('Data Transaksi')
        ->assertSee('Hutang')
        ->assertSee('Rekap Pembelian')
        ->assertSee('#toko-purchase-create', false)
        ->assertSee('#toko-purchase-transactions', false)
        ->assertSee('#toko-purchase-ap', false)
        ->assertSee('#toko-purchase-recap', false);
});

test('toko pos add-on can pay posted vendor bill from purchase page', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();
    $bill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_cost' => 5000,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->set('selectedVendorBillPaymentId', (string) $bill->id)
        ->call('payVendorBill')
        ->assertHasNoErrors();

    $bill = $bill->fresh();

    expect($bill->status)->toBe(VendorBill::STATUS_PAID)
        ->and($bill->paid_at)->not->toBeNull()
        ->and($bill->payment_journal_entry_id)->not->toBeNull();
});

test('toko pos add-on can record partial vendor bill payments before final payment', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();
    $bill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_cost' => 10000,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->set('selectedVendorBillPaymentId', (string) $bill->id)
        ->set('vendorBillPaymentAmount', '20000')
        ->call('payVendorBill')
        ->assertHasNoErrors()
        ->assertSee('Vendor Payment History')
        ->assertSee('20.000');

    $bill = $bill->fresh();

    expect($bill->status)->toBe(VendorBill::STATUS_POSTED)
        ->and((float) $bill->metadata['paid_total'])->toBe(20000.0)
        ->and((float) $bill->metadata['balance_due'])->toBe(30000.0)
        ->and($bill->payment_journal_entry_id)->not->toBeNull()
        ->and(JournalEntry::query()->where('source_type', 'toko_pos_purchase_payment')->where('source_id', $bill->id)->count())->toBe(1);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->set('selectedVendorBillPaymentId', (string) $bill->id)
        ->set('vendorBillPaymentAmount', '30000')
        ->call('payVendorBill')
        ->assertHasNoErrors();

    $bill = $bill->fresh();

    expect($bill->status)->toBe(VendorBill::STATUS_PAID)
        ->and((float) $bill->metadata['paid_total'])->toBe(50000.0)
        ->and((float) $bill->metadata['balance_due'])->toBe(0.0)
        ->and($bill->paid_at)->not->toBeNull()
        ->and(JournalEntry::query()->where('source_type', 'toko_pos_purchase_payment')->where('source_id', $bill->id)->count())->toBe(2);
});

test('toko pos add-on can cancel posted purchase and reverse stock movement', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();
    $bill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_cost' => 6000,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->set('selectedCancelVendorBillId', (string) $bill->id)
        ->set('cancelPurchaseReason', 'Supplier salah kirim barang')
        ->call('cancelPurchase')
        ->assertHasNoErrors();

    $bill = $bill->fresh();

    expect($bill->status)->toBe(VendorBill::STATUS_CANCELLED)
        ->and($bill->metadata['cancel_reason'])->toBe('Supplier salah kirim barang')
        ->and(StockMovement::query()->where('product_id', $product->id)->where('type', StockMovement::TYPE_IN)->sum('quantity'))->toEqual('5.000')
        ->and(StockMovement::query()->where('product_id', $product->id)->where('type', StockMovement::TYPE_OUT)->where('metadata->source', 'toko_pos_purchase_cancel')->sum('quantity'))->toEqual('5.000');
});

test('toko pos add-on can refund paid purchase and reverse ap payment', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();
    $bill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_cost' => 5000,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->set('selectedVendorBillPaymentId', (string) $bill->id)
        ->call('payVendorBill')
        ->set('selectedCancelVendorBillId', (string) $bill->id)
        ->set('cancelPurchaseReason', 'Supplier refund pembayaran')
        ->call('cancelPurchase')
        ->assertHasNoErrors();

    $bill = $bill->fresh();
    $refundEntry = JournalEntry::query()
        ->where('source_type', 'toko_pos_purchase_refund')
        ->where('source_id', $bill->id)
        ->with('lines.account')
        ->first();

    expect($bill->status)->toBe(VendorBill::STATUS_CANCELLED)
        ->and($bill->metadata['payment_status'])->toBe('refunded')
        ->and($bill->metadata['refund_journal_entry_id'])->toBe($refundEntry?->id)
        ->and(StockMovement::query()->where('product_id', $product->id)->where('type', StockMovement::TYPE_OUT)->where('metadata->source', 'toko_pos_purchase_cancel')->sum('quantity'))->toEqual('2.000')
        ->and($refundEntry)->not->toBeNull()
        ->and((float) $refundEntry->lines->sum('debit'))->toBe(10000.0)
        ->and((float) $refundEntry->lines->sum('credit'))->toBe(10000.0)
        ->and($refundEntry->lines->firstWhere('account.code', '1100'))->not->toBeNull()
        ->and($refundEntry->lines->firstWhere('account.code', '2300'))->not->toBeNull();
});

test('toko purchases page shows purchase list with status and cancellation details', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();
    $bill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_cost' => 5000,
        ]],
    ]);
    app(TokoPosPurchaseService::class)->cancelPurchase($actor, $bill, 'Supplier salah kirim barang');

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->assertSee('Purchase List')
        ->assertSee($bill->number)
        ->assertSee($vendor->name)
        ->assertSee('cancelled')
        ->assertSee('Supplier salah kirim barang');
});

test('toko purchases page uses datatable pagination and search', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();
    $bills = collect();

    foreach (range(1, 12) as $index) {
        $rowVendor = Vendor::query()->create([
            'company_id' => $company->id,
            'name' => 'Vendor Datatable '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'status' => Vendor::STATUS_ACTIVE,
        ]);

        $bills->push(app(TokoPosPurchaseService::class)->createPurchase($actor, [
            'company_id' => $company->id,
            'vendor_id' => $rowVendor->id,
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_cost' => 1000 + $index,
            ]],
        ]));
    }

    $component = Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->assertSee('Show')
        ->assertSee('10')
        ->assertSee('entries')
        ->assertSee('Showing 1 to 10 of 12 purchase entries')
        ->assertSee('Vendor Datatable 12')
        ->assertSee('Vendor Datatable 03')
        ->assertSet('purchasePage', 1);

    $component
        ->call('nextPurchasePage')
        ->assertSee('Showing 11 to 12 of 12 purchase entries')
        ->assertSee('Vendor Datatable 02')
        ->assertSee('Vendor Datatable 01')
        ->assertSet('purchasePage', 2);

    $component
        ->set('purchaseSearch', $bills[6]->number)
        ->assertSee('Showing 1 to 1 of 1 purchase entries')
        ->assertSee('Vendor Datatable 07')
        ->assertSet('purchasePage', 1);
});

test('toko purchases page can open purchase bill detail drilldown', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();
    $product->forceFill(['name' => 'Kondensor Detail Beli'])->save();

    $bill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_cost' => 12500,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->call('viewPurchaseBillDetail', $bill->id)
        ->assertHasNoErrors()
        ->assertSee('Purchase Detail')
        ->assertSee($bill->number)
        ->assertSee($vendor->name)
        ->assertSee('posted')
        ->assertSee('Kondensor Detail Beli')
        ->assertSee('37.500')
        ->call('clearPurchaseBillDetail')
        ->assertDontSee('Purchase Detail');
});

test('toko purchases page shows ap aging buckets for unpaid vendor bills', function (): void {
    Carbon::setTestNow('2026-06-08 10:00:00');
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();

    $overdue = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_cost' => 10000,
        ]],
    ]);
    $overdue->forceFill(['due_at' => Carbon::parse('2026-06-05')])->save();

    $dueSoon = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_cost' => 20000,
        ]],
    ]);
    $dueSoon->forceFill(['due_at' => Carbon::parse('2026-06-12')])->save();

    $notYetDue = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_cost' => 30000,
        ]],
    ]);
    $notYetDue->forceFill(['due_at' => Carbon::parse('2026-06-30')])->save();

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->assertSee('AP Aging')
        ->assertSee('Overdue')
        ->assertSee('10.000')
        ->assertSee('Due This Week')
        ->assertSee('20.000')
        ->assertSee('Not Yet Due')
        ->assertSee('30.000')
        ->assertSee('Total AP')
        ->assertSee('60.000');

    Carbon::setTestNow();
});

test('toko purchases page shows vendor payment history from paid bills', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();
    $bill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_cost' => 15000,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->set('selectedVendorBillPaymentId', (string) $bill->id)
        ->call('payVendorBill')
        ->assertHasNoErrors()
        ->assertSee('Vendor Payment History')
        ->assertSee($bill->number)
        ->assertSee($vendor->name)
        ->assertSee('30.000');
});

test('toko reports page shows purchase recap report', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();
    $bill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_cost' => 12500,
        ]],
    ]);

    $this->actingAs($actor)
        ->get(route('admin.toko.reports'))
        ->assertOk()
        ->assertSee('Purchase Recap Report')
        ->assertSee($bill->number)
        ->assertSee($vendor->name)
        ->assertSee('Filter AC')
        ->assertSee('62.500')
        ->assertSee('Purchases By Date')
        ->assertSee('Purchases By Vendor')
        ->assertSee('Purchases By Product')
        ->assertSee('Purchase CSV');
});

test('toko purchase csv export includes vendor bill status and cancellation columns', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();
    $bill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_cost' => 5000,
        ]],
    ]);
    app(TokoPosPurchaseService::class)->cancelPurchase($actor, $bill, 'Supplier salah kirim barang');

    $response = $this->actingAs($actor)->get(route('admin.toko.exports.purchases'));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())->toContain('number,vendor,status,total,cancel_reason')
        ->toContain($bill->number)
        ->toContain($vendor->name)
        ->toContain('Supplier salah kirim barang');
});

test('toko purchases page shows purchase line item detail', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();
    $product->forceFill(['name' => 'Kondensor Detail Beli'])->save();

    app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_cost' => 12500,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->assertSee('Line Items')
        ->assertSee('Kondensor Detail Beli')
        ->assertSee('3')
        ->assertSee('12.500');
});

test('toko purchase line csv export includes bill item detail', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();
    $product->forceFill(['name' => 'Relay CSV Detail'])->save();

    $bill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 6,
            'unit_cost' => 17500,
        ]],
    ]);

    $response = $this->actingAs($actor)->get(route('admin.toko.exports.purchase-lines'));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())->toContain('bill_number,vendor,status,description,quantity,unit_cost,line_total')
        ->toContain($bill->number)
        ->toContain('Relay CSV Detail')
        ->toContain('6.000');
});

test('toko purchase receipt pdf can be downloaded from toko route and linked from purchase list', function (): void {
    setTokoPosPurchaseLicenseFeatures(['toko_pos']);

    [$company, $actor, $vendor, $product] = tokoPurchaseFixture();

    $bill = app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_cost' => 9000,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->assertSee(route('admin.toko.purchases.pdf', $bill, false), false);

    $this->actingAs($actor)
        ->get(route('admin.toko.purchases.pdf', $bill))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

function tokoPurchaseFixture(): array
{
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $vendor = Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Digital Teknik',
        'status' => Vendor::STATUS_ACTIVE,
    ]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU000002',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 8000,
        'cost_price' => 6500,
        'stock_tracking' => true,
    ]);

    return [$company, $actor, $vendor, $product];
}
