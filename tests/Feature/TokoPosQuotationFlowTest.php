<?php

use App\Livewire\Admin\TokoPosAddon;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Setting;
use App\Models\User;
use App\Services\Enterprise\LicenseGuard;
use App\Support\TokoPosQuotationService;
use Livewire\Livewire;

function setTokoPosQuotationLicenseFeatures(array $features): void
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

test('toko quotation service creates quotation and converts it to invoice', function (): void {
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'WARDI',
        'code' => 'CUST001',
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

    $quotation = app(TokoPosQuotationService::class)->createQuotation($actor, [
        'company_id' => $company->id,
        'client_id' => $client->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
        ]],
    ]);

    expect($quotation)->toBeInstanceOf(Quotation::class)
        ->and($quotation->grand_total)->toEqual('16000.00')
        ->and($quotation->metadata['source'])->toBe('toko_pos_quotation');

    $invoice = app(TokoPosQuotationService::class)->convertToInvoice($actor, $quotation);

    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoice->quotation_id)->toBe($quotation->id)
        ->and($invoice->grand_total)->toEqual('16000.00')
        ->and($invoice->metadata['source'])->toBe('quotation_conversion');
});

test('toko pos add-on can create quotation from quotation cart and convert it', function (): void {
    setTokoPosQuotationLicenseFeatures(['toko_pos']);

    [$company, $actor, $client, $product] = tokoQuotationFixture();

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class)
        ->set('selectedQuotationClientId', (string) $client->id)
        ->set('selectedQuotationProductId', (string) $product->id)
        ->set('quotationQuantity', '2')
        ->call('addToQuotationCart')
        ->call('createQuotation');

    $quotation = Quotation::query()
        ->where('company_id', $company->id)
        ->where('metadata->source', 'toko_pos_quotation')
        ->firstOrFail();

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class)
        ->call('convertQuotationToInvoice', $quotation->id);

    expect(Invoice::query()->where('quotation_id', $quotation->id)->exists())->toBeTrue();
});

test('toko quotation can be accepted or rejected before final conversion', function (): void {
    setTokoPosQuotationLicenseFeatures(['toko_pos']);

    [$company, $actor, $client, $product] = tokoQuotationFixture();

    $quotation = app(TokoPosQuotationService::class)->createQuotation($actor, [
        'company_id' => $company->id,
        'client_id' => $client->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'quotations'])
        ->assertSee('Recent Quotations')
        ->assertSee(__('Final'))
        ->call('markQuotationAccepted', $quotation->id);

    $quotation->refresh();

    expect($quotation->status)->toBe(Quotation::STATUS_ACCEPTED)
        ->and($quotation->metadata['accepted_by'])->toBe($actor->id);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'quotations'])
        ->call('convertQuotationToInvoice', $quotation->id);

    expect(Invoice::query()->where('quotation_id', $quotation->id)->count())->toBe(1);
});

test('rejected toko quotation cannot be converted to invoice', function (): void {
    setTokoPosQuotationLicenseFeatures(['toko_pos']);

    [$company, $actor, $client, $product] = tokoQuotationFixture();

    $quotation = app(TokoPosQuotationService::class)->createQuotation($actor, [
        'company_id' => $company->id,
        'client_id' => $client->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'quotations'])
        ->call('markQuotationRejected', $quotation->id)
        ->call('convertQuotationToInvoice', $quotation->id);

    $quotation->refresh();

    expect($quotation->status)->toBe(Quotation::STATUS_REJECTED)
        ->and($quotation->metadata['rejected_by'])->toBe($actor->id)
        ->and(Invoice::query()->where('quotation_id', $quotation->id)->exists())->toBeFalse();
});

test('toko quotation desk uses searchable ten row datatable pagination', function (): void {
    setTokoPosQuotationLicenseFeatures(['toko_pos']);

    [$company, $actor, $client] = tokoQuotationFixture();

    foreach (range(1, 12) as $number) {
        Quotation::query()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'number' => sprintf('QTN-DT-%02d', $number),
            'status' => Quotation::STATUS_SENT,
            'issued_at' => now()->subDays(12 - $number)->toDateString(),
            'valid_until' => now()->addDays(7)->toDateString(),
            'subtotal' => 10000 + $number,
            'tax_total' => 0,
            'grand_total' => 10000 + $number,
            'metadata' => ['source' => 'toko_pos_quotation'],
        ]);
    }

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'quotations'])
        ->assertSee('Data Penawaran')
        ->assertSee('Search')
        ->assertSee('Showing 1 to 10 of 12 quotation entries')
        ->assertSee('QTN-DT-12')
        ->assertDontSee('QTN-DT-01')
        ->call('nextQuotationPage')
        ->assertSee('Showing 11 to 12 of 12 quotation entries')
        ->assertSee('QTN-DT-02')
        ->assertSee('QTN-DT-01')
        ->assertSet('quotationPage', 2)
        ->set('quotationSearch', 'QTN-DT-07')
        ->assertSee('Showing 1 to 1 of 1 quotation entries')
        ->assertSee('QTN-DT-07')
        ->assertSet('quotationPage', 1);
});

test('toko pos quotation pdf can be downloaded from toko route', function (): void {
    setTokoPosQuotationLicenseFeatures(['toko_pos']);

    [$company, $actor, $client, $product] = tokoQuotationFixture();

    $quotation = app(TokoPosQuotationService::class)->createQuotation($actor, [
        'company_id' => $company->id,
        'client_id' => $client->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ]);

    $this->actingAs($actor)
        ->get(route('admin.toko.quotations.pdf', $quotation))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

function tokoQuotationFixture(): array
{
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-quote',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'WARDI',
        'code' => 'CUST-QTN',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU-QTN',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 8000,
        'cost_price' => 6500,
        'stock_tracking' => true,
    ]);

    return [$company, $actor, $client, $product];
}
