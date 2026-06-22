<?php

use App\Livewire\Admin\TokoPosAddon;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\Enterprise\LicenseGuard;
use App\Support\TokoPosSalesService;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function setTokoPosCounterSaleLicenseFeatures(array $features): void
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

test('toko pos service creates paid counter sale and stock out movement', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'Walk In',
        'code' => 'WALK-IN',
        'status' => Client::STATUS_ACTIVE,
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

    $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'client_id' => $client->id,
        'payment_status' => 'paid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
        ]],
    ]);

    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoice->number)->toStartWith('POS-')
        ->and($invoice->status)->toBe(Invoice::STATUS_PAID)
        ->and($invoice->grand_total)->toEqual('16000.00')
        ->and($invoice->metadata['source'])->toBe('toko_pos_counter_sale')
        ->and($invoice->items)->toHaveCount(1)
        ->and($invoice->items->first()->line_total)->toEqual('16000.00')
        ->and(StockMovement::query()->where('product_id', $product->id)->where('type', StockMovement::TYPE_OUT)->value('quantity'))->toEqual('2.000');
});

test('toko pos service creates unpaid counter sale without marking invoice paid', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
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

    $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 7500,
        ]],
    ]);

    expect($invoice->status)->toBe(Invoice::STATUS_SENT)
        ->and($invoice->grand_total)->toEqual('7500.00');
});

test('toko pos add-on can create counter sale from cart', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
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

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 10,
        'unit_cost' => 6500,
        'occurred_at' => now(),
        'metadata' => ['source' => 'opening'],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class)
        ->set('selectedProductId', (string) $product->id)
        ->set('saleQuantity', '2')
        ->call('addToSaleCart')
        ->set('saleTenderedAmount', '20000')
        ->call('createCounterSale');

    expect(Invoice::query()->where('company_id', $company->id)->where('status', Invoice::STATUS_PAID)->exists())->toBeTrue()
        ->and(StockMovement::query()->where('product_id', $product->id)->where('type', StockMovement::TYPE_OUT)->exists())->toBeTrue();
});

test('toko pos page exposes clear transaction menu sections', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-pos-menu',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->assertSee('Terminal POS')
        ->assertSee('Terminal POS')
        ->assertSee('Tools Admin')

        ->set('showPosBackOffice', true)

        ->assertSee('Invoice Payments')
        ->assertSee('Cancel Counter Sale')
        ->assertSee('Invoice');
});

test('toko pos page exposes legacy cashier scan and product detail fields', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-pos-legacy-flow',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU000007',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 8000,
        'cost_price' => 6500,
        'stock_tracking' => true,
        'metadata' => ['barcode' => '899777000007'],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->assertSee('Terminal POS')
        ->assertSee('Scan Barcode')
        ->assertSee('Subtotal')
        ->assertSee('Diskon')
        ->assertSee('Total Tagihan')
        ->assertSee('Selesaikan Transaksi')

        ->assertSee('Produk')
        ->assertSee('Harga')
        ->assertSee('Filter AC · SKU000007');
});

test('toko pos barcode scan adds matching product into sale cart', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-pos-barcode',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC Barcode',
        'sku' => 'SKU000008',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 12500,
        'cost_price' => 6500,
        'stock_tracking' => true,
        'metadata' => ['barcode' => '899777000008'],
    ]);
    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 9,
        'unit_cost' => 6500,
        'occurred_at' => now(),
        'metadata' => ['source' => 'opening_test'],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('saleBarcode', '899777000008')
        ->set('saleQuantity', '2')
        ->call('addScannedSaleBarcode')
        ->assertSet('saleBarcode', '')
        ->assertSee('Filter AC Barcode')
        ->assertSee('25.000')
        ->assertDontSee('25,000');
});

test('toko pos page shows modern checkout and receipt preview', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-modern-pos',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Cap AC Sigma 30+2 Uf',
        'sku' => 'SKU000015',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 45000,
        'cost_price' => 30000,
        'stock_tracking' => true,
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('selectedProductId', (string) $product->id)
        ->set('saleQuantity', '1')
        ->call('addToSaleCart')
        ->set('saleTenderedAmount', '50000')
        ->assertSee('Total Tagihan')
        ->assertSee('Subtotal')
        ->assertSee('Kembalian')
        ->assertSee('Diskon')
        ->assertSee('Selesaikan Transaksi')
        ->assertDontSee('Tidak')
        ->assertSee('Cap AC Sigma 30+2 Uf')
        ->assertSee('5.000')
        ->assertDontSee('5,000');
});

test('toko pos page behaves as a focused cashier terminal', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-focused-terminal',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->assertSee('Terminal POS')
        ->assertSee('Scan Barcode')
        ->assertSee('Tempo')
        ->assertSee('Selesaikan Transaksi')
        ->assertSee('F2 untuk fokus')
        ->assertDontSee('Invoice Payments')
        ->assertDontSee('Cancel Counter Sale')
        ->assertDontSee('Daftar Transaksi Ritel');
});

test('toko pos cashier mode avoids loading hidden back office datasets', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-pos-lean-render',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);

    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU000002',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 8000,
        'cost_price' => 6500,
        'stock_tracking' => true,
    ]);

    for ($i = 1; $i <= 5; $i++) {
        Invoice::query()->create([
            'company_id' => $company->id,
            'number' => 'POS-PERF-'.$i,
            'status' => Invoice::STATUS_PAID,
            'issued_at' => now()->subDays($i),
            'due_at' => now()->subDays($i),
            'subtotal' => 8000,
            'grand_total' => 8000,
            'metadata' => [
                'source' => 'toko_pos_counter_sale',
                'payments' => [[
                    'amount' => 8000,
                    'method' => 'Cash',
                    'paid_at' => now()->subDays($i)->toIso8601String(),
                ]],
            ],
        ]);
    }

    $queries = [];
    DB::listen(static function ($event) use (&$queries): void {
        $queries[] = $event->sql;
    });

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->assertSet('showPosBackOffice', false)
        ->assertDontSee('Invoice Payments');

    $joinedQueries = implode("\n", $queries);

    expect(count($queries))->toBeLessThan(45)
        ->and($joinedQueries)->not->toContain('import_export_runs')
        ->and($joinedQueries)->not->toContain('journal_entries');
});

test('toko pos paid cash checkout requires enough tendered amount before posting nota', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-cash-confirmation',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Cap AC Sigma 50 Uf',
        'sku' => 'SKU000050',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 60000,
        'cost_price' => 44000,
        'stock_tracking' => true,
    ]);

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 10,
        'unit_cost' => 6500,
        'occurred_at' => now(),
        'metadata' => ['source' => 'opening'],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('selectedProductId', (string) $product->id)
        ->set('saleQuantity', '1')
        ->call('addToSaleCart')
        ->set('salePaymentStatus', 'paid')
        ->set('salePaymentMethod', 'Cash')
        ->set('saleTenderedAmount', '50000')
        ->call('createCounterSale')
        ->assertDispatched('banner-message');

    expect(Invoice::query()->where('company_id', $company->id)->count())->toBe(0);
});

test('toko pos cashier supports split tender confirmation with payment line metadata', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-split-tender',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'AC Split Tender',
        'sku' => 'SKU-SPLIT-TENDER',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 100000,
        'cost_price' => 70000,
        'stock_tracking' => true,
    ]);

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 10,
        'unit_cost' => 6500,
        'occurred_at' => now(),
        'metadata' => ['source' => 'opening'],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('selectedProductId', (string) $product->id)
        ->set('saleQuantity', '1')
        ->call('addToSaleCart')
        ->set('saleTenderMethod', 'Cash')
        ->set('saleTenderAmount', '40000')
        ->call('addSaleTenderLine')
        ->set('saleTenderMethod', 'QRIS')
        ->set('saleTenderAmount', '30000')
        ->set('saleTenderReference', 'QR-123')
        ->call('addSaleTenderLine')
        ->call('createCounterSale')
        ->assertDispatched('banner-message');

    expect(Invoice::query()->where('company_id', $company->id)->count())->toBe(0);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('selectedProductId', (string) $product->id)
        ->set('saleQuantity', '1')
        ->call('addToSaleCart')
        ->set('saleTenderMethod', 'Cash')
        ->set('saleTenderAmount', '40000')
        ->call('addSaleTenderLine')
        ->set('saleTenderMethod', 'QRIS')
        ->set('saleTenderAmount', '30000')
        ->set('saleTenderReference', 'QR-123')
        ->call('addSaleTenderLine')
        ->set('saleTenderMethod', 'Transfer Bank')
        ->set('saleTenderAmount', '30000')
        ->set('saleTenderBankCode', 'BCA-001')
        ->set('saleTenderReference', 'TRF-123')
        ->call('addSaleTenderLine')
        ->call('createCounterSale')
        ->assertHasNoErrors();

    $invoice = Invoice::query()->where('company_id', $company->id)->latest('id')->firstOrFail();

    expect($invoice->status)->toBe(Invoice::STATUS_PAID)
        ->and($invoice->grand_total)->toEqual('100000.00')
        ->and($invoice->metadata['payment_method'])->toBe('Split Tender')
        ->and((float) $invoice->metadata['paid_total'])->toBe(100000.0)
        ->and((float) $invoice->metadata['remaining_total'])->toBe(0.0)
        ->and($invoice->metadata['payments'])->toHaveCount(3)
        ->and($invoice->metadata['payments'][0]['method'])->toBe('Cash')
        ->and((float) $invoice->metadata['payments'][0]['amount'])->toBe(40000.0)
        ->and($invoice->metadata['payments'][1]['method'])->toBe('QRIS')
        ->and($invoice->metadata['payments'][1]['reference'])->toBe('QR-123')
        ->and($invoice->metadata['payments'][2]['method'])->toBe('Transfer Bank')
        ->and($invoice->metadata['payments'][2]['bank_code'])->toBe('BCA-001');
});

test('toko pos add-on applies cashier discount additional charge and payment references', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU000006',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 10000,
        'cost_price' => 6500,
        'stock_tracking' => true,
    ]);

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 10,
        'unit_cost' => 6500,
        'occurred_at' => now(),
        'metadata' => ['source' => 'opening'],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('selectedProductId', (string) $product->id)
        ->set('saleQuantity', '2')
        ->call('addToSaleCart')
        ->set('saleDiscountAmount', '2000')
        ->set('saleAdditionalCharge', '500')
        ->set('salePaymentMethod', 'Transfer Bank')
        ->set('saleBankCode', 'BCA-001')
        ->call('createCounterSale')
        ->assertHasNoErrors();

    $invoice = Invoice::query()->where('company_id', $company->id)->latest('id')->firstOrFail();

    expect($invoice->status)->toBe(Invoice::STATUS_PAID)
        ->and($invoice->grand_total)->toEqual('18500.00')
        ->and((float) $invoice->metadata['discount_amount'])->toBe(2000.0)
        ->and((float) $invoice->metadata['additional_charge'])->toBe(500.0)
        ->and($invoice->metadata['payment_method'])->toBe('Transfer Bank')
        ->and($invoice->metadata['bank_code'])->toBe('BCA-001');
});

test('toko pos invoice pdf can be downloaded from toko route', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
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

    $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'paid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ]);

    $this->actingAs($actor)
        ->get(route('admin.toko.invoices.pdf', $invoice))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('toko pos add-on can record partial and final invoice payments with method metadata', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU000003',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 10000,
        'cost_price' => 6500,
        'stock_tracking' => true,
    ]);

    $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('selectedPaymentInvoiceId', (string) $invoice->id)
        ->set('invoicePaymentAmount', '4000')
        ->set('invoicePaymentMethod', 'Transfer Bank')
        ->set('invoicePaymentBankCode', 'BCA-001')
        ->set('invoicePaymentReference', 'TRX-1')
        ->call('recordInvoicePayment')
        ->assertHasNoErrors();

    $invoice = $invoice->fresh();

    expect($invoice->status)->toBe(Invoice::STATUS_SENT)
        ->and((float) $invoice->metadata['payments'][0]['amount'])->toBe(4000.0)
        ->and($invoice->metadata['payments'][0]['method'])->toBe('Transfer Bank')
        ->and((float) $invoice->metadata['paid_total'])->toBe(4000.0)
        ->and(JournalEntry::query()->where('source_type', 'toko_pos_invoice_payment')->count())->toBe(1);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('selectedPaymentInvoiceId', (string) $invoice->id)
        ->set('invoicePaymentAmount', '6000')
        ->set('invoicePaymentMethod', 'Cash')
        ->set('invoicePaymentReference', 'TRX-2')
        ->call('recordInvoicePayment')
        ->assertHasNoErrors();

    $invoice = $invoice->fresh();

    expect($invoice->status)->toBe(Invoice::STATUS_PAID)
        ->and((float) $invoice->metadata['paid_total'])->toBe(10000.0)
        ->and($invoice->metadata['payment_status'])->toBe('paid')
        ->and(JournalEntry::query()->where('source_type', 'toko_pos_invoice_payment')->count())->toBe(2)
        ->and((float) JournalEntry::query()->where('source_type', 'toko_pos_invoice_payment')->with('lines')->get()->flatMap->lines->sum('debit'))->toBe(10000.0)
        ->and((float) JournalEntry::query()->where('source_type', 'toko_pos_invoice_payment')->with('lines')->get()->flatMap->lines->sum('credit'))->toBe(10000.0);
});

test('toko pos add-on can cancel unpaid counter sale and reverse stock movement', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU000004',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 12000,
        'cost_price' => 7000,
        'stock_tracking' => true,
    ]);

    $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 3,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('selectedCancelInvoiceId', (string) $invoice->id)
        ->set('cancelInvoiceReason', 'Customer batal ambil barang')
        ->call('cancelCounterSale')
        ->assertHasNoErrors();

    $invoice = $invoice->fresh();

    expect($invoice->status)->toBe(Invoice::STATUS_CANCELLED)
        ->and($invoice->metadata['cancel_reason'])->toBe('Customer batal ambil barang')
        ->and(StockMovement::query()->where('product_id', $product->id)->where('type', StockMovement::TYPE_OUT)->sum('quantity'))->toEqual('3.000')
        ->and(StockMovement::query()->where('product_id', $product->id)->where('type', StockMovement::TYPE_IN)->where('metadata->source', 'toko_pos_counter_sale_cancel')->sum('quantity'))->toEqual('3.000');
});

test('toko pos add-on can refund paid counter sale and reverse stock movement', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Kompresor AC',
        'sku' => 'SKU000005',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 25000,
        'cost_price' => 17000,
        'stock_tracking' => true,
    ]);

    $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'paid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('selectedCancelInvoiceId', (string) $invoice->id)
        ->set('cancelInvoiceReason', 'Barang dikembalikan dan kas direfund')
        ->call('cancelCounterSale')
        ->assertHasNoErrors()
        ->assertSet('selectedCancelInvoiceId', '')
        ->assertSet('cancelInvoiceReason', '');

    $invoice = $invoice->fresh();
    $refundEntry = JournalEntry::query()
        ->where('source_type', 'toko_pos_invoice_refund')
        ->where('source_id', $invoice->id)
        ->with('lines')
        ->first();

    expect($invoice->status)->toBe(Invoice::STATUS_CANCELLED)
        ->and($invoice->metadata['payment_status'])->toBe('refunded')
        ->and($invoice->metadata['refund_journal_entry_id'])->toBe($refundEntry?->id)
        ->and(StockMovement::query()->where('product_id', $product->id)->where('type', StockMovement::TYPE_IN)->where('metadata->source', 'toko_pos_counter_sale_cancel')->sum('quantity'))->toEqual('2.000')
        ->and($refundEntry)->not->toBeNull()
        ->and((float) $refundEntry->lines->sum('debit'))->toBe(50000.0)
        ->and((float) $refundEntry->lines->sum('credit'))->toBe(50000.0);
});

test('toko pos page shows sales invoice list with payment and cancellation details', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU000007',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 15000,
        'cost_price' => 9000,
        'stock_tracking' => true,
    ]);

    $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ]);
    app(TokoPosSalesService::class)->recordInvoicePayment($actor, $invoice, [
        'amount' => 5000,
        'method' => 'Transfer Bank',
        'bank_code' => 'BCA-001',
        'reference' => 'PAY-001',
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('showPosBackOffice', true)
        ->assertSee('Invoice')
        ->assertSee($invoice->number)
        ->assertSee('Transfer')

        ->assertSee('partial');
});

test('toko sales invoice csv export includes payment and cancellation columns', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU000008',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 15000,
        'cost_price' => 9000,
        'stock_tracking' => true,
    ]);
    $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ]);
    app(TokoPosSalesService::class)->recordInvoicePayment($actor, $invoice, [
        'amount' => 5000,
        'method' => 'Transfer Bank',
        'bank_code' => 'BCA-001',
        'reference' => 'PAY-CSV',
    ]);

    $response = $this->actingAs($actor)->get(route('admin.toko.exports.sales'));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())->toContain('number,status,payment_status,total,payment_summary,cancel_reason')
        ->toContain($invoice->number)
        ->toContain('PAY-CSV');
});

test('toko pos page shows sales invoice line item detail', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Evaporator Detail POS',
        'sku' => 'SKU-LINE-SALE',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 21000,
        'cost_price' => 11000,
        'stock_tracking' => true,
    ]);

    app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('showPosBackOffice', true)
        ->assertSee('Produk')
        ->assertSee('Evaporator Detail POS')
        ->assertSee('2.000')
        ->assertSee('21.000')
        ->assertDontSee('21,000');
});

test('toko sales line csv export includes invoice item detail', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Kapasitor CSV Detail',
        'sku' => 'SKU-LINE-CSV-SALE',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 33000,
        'cost_price' => 14000,
        'stock_tracking' => true,
    ]);

    $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 4,
        ]],
    ]);

    $response = $this->actingAs($actor)->get(route('admin.toko.exports.sales-lines'));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())->toContain('invoice_number,status,description,quantity,unit_price,line_total')
        ->toContain($invoice->number)
        ->toContain('Kapasitor CSV Detail')
        ->toContain('4.000');
});

test('toko cash page shows invoice payment history', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Payment History Product',
        'sku' => 'SKU-PAY-HISTORY',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 20000,
        'cost_price' => 9000,
        'stock_tracking' => true,
    ]);
    $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ]);
    app(TokoPosSalesService::class)->recordInvoicePayment($actor, $invoice, [
        'amount' => 7500,
        'method' => 'QRIS',
        'bank_code' => 'QR-01',
        'reference' => 'PAY-HIST-1',
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'cash'])
        ->assertSee('Payment History')
        ->assertSee($invoice->number)
        ->assertSee('QRIS')
        ->assertSee('PAY-HIST-1');
});

test('toko payment history csv export includes invoice payment ledger', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Payment Export Product',
        'sku' => 'SKU-PAY-EXPORT',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 20000,
        'cost_price' => 9000,
        'stock_tracking' => true,
    ]);
    $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ]);
    app(TokoPosSalesService::class)->recordInvoicePayment($actor, $invoice, [
        'amount' => 7500,
        'method' => 'QRIS',
        'bank_code' => 'QR-01',
        'reference' => 'PAY-HIST-CSV',
    ]);

    $response = $this->actingAs($actor)->get(route('admin.toko.exports.payments'));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())->toContain('invoice_number,amount,method,bank_code,reference,paid_at')
        ->toContain($invoice->number)
        ->toContain('PAY-HIST-CSV');
});

test('toko cash payment history uses datatable pagination and search', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-payment-history-datatable',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Payment Datatable Product',
        'sku' => 'SKU-PAY-DT',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 20000,
        'cost_price' => 9000,
        'stock_tracking' => true,
    ]);

    foreach (range(1, 12) as $index) {
        $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
            'company_id' => $company->id,
            'payment_status' => 'unpaid',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
            ]],
        ]);
        app(TokoPosSalesService::class)->recordInvoicePayment($actor, $invoice, [
            'amount' => 1000 + $index,
            'method' => $index === 12 ? 'QRIS' : 'Cash',
            'bank_code' => $index === 12 ? 'QR-SPECIAL' : 'CASH',
            'reference' => 'PAY-DT-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
        ]);
    }

    $component = Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'cash'])
        ->assertSee('Payment History')
        ->assertSee('Showing 1 to 10 of 12 payment entries')
        ->assertSee('Next')
        ->call('nextPaymentHistoryPage')
        ->assertSee('Showing 11 to 12 of 12 payment entries')
        ->set('paymentHistorySearch', 'QR-SPECIAL')
        ->assertSee('Showing 1 to 1 of 1 payment entries')
        ->assertSee('PAY-DT-12')
        ->assertDontSee('Showing 1 to 10 of 12 payment entries');

    expect($component->get('paymentHistoryPage'))->toBe(1);
});

test('toko pos sales history uses datatable pagination and search', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-pos-sales-datatable',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'Sandy Teknik',
        'code' => 'SANDY',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Cap AC Sigma POS',
        'sku' => 'SKU-POS-DT',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 10000,
        'cost_price' => 7000,
        'stock_tracking' => true,
    ]);

    foreach (range(1, 12) as $index) {
        app(TokoPosSalesService::class)->createCounterSale($actor, [
            'company_id' => $company->id,
            'client_id' => $client->id,
            'payment_status' => $index === 12 ? 'unpaid' : 'paid',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => 10000 + $index,
            ]],
        ]);
    }

    $component = Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('showPosBackOffice', true)
        ->assertSee('Invoice')

        ->call('nextSalesPage')

        ->set('salesSearch', 'Sandy')

        ->set('salesSearch', 'unpaid')

        ->assertSee('sent');

    expect($component->get('salesPage'))->toBe(1);
});

test('toko pos sales history can open invoice detail drilldown', function (): void {
    setTokoPosCounterSaleLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-sales-detail',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'Sandy Teknik Detail',
        'code' => 'SANDY-DETAIL',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Cap AC Detail',
        'sku' => 'SKU-DETAIL',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 45000,
        'cost_price' => 28000,
        'stock_tracking' => true,
    ]);

    $invoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'client_id' => $client->id,
        'payment_status' => 'unpaid',
        'payment_method' => 'Transfer Bank',
        'bank_code' => 'BCA-001',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 45000,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('showPosBackOffice', true)
        ->call('viewSalesInvoiceDetail', $invoice->id)
        ->assertHasNoErrors()
        ->assertSee('Detail Transaksi')
        ->assertSee($invoice->number)
        ->assertSee('Sandy Teknik Detail')
        ->assertSee('Transfer')
        ->assertSee('BCA-001')
        ->assertSee('Cap AC Detail')
        ->assertSee('90.000')
        ->assertDontSee('90,000')
        ->call('clearSalesInvoiceDetail')
        ->assertDontSee('Detail Transaksi');
});
