<?php

use App\Livewire\Admin\TokoPosAddon;
use App\Models\Client;
use App\Models\Company;
use App\Models\DeliveryLetter;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\Enterprise\LicenseGuard;
use App\Support\TokoPosDeliveryLetterService;
use App\Support\TokoPosSalesService;
use Livewire\Livewire;

function setTokoPosDeliveryLetterLicenseFeatures(array $features): void
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

test('toko delivery letter service creates surat jalan from pos invoice once', function (): void {
    [$company, $actor, $invoice] = tokoDeliveryLetterFixture();

    $letter = app(TokoPosDeliveryLetterService::class)->createFromInvoice($actor, $invoice, [
        'destination' => 'Gudang Srengseng',
        'driver_name' => 'Admin',
        'vehicle_number' => 'B 1234 PTM',
    ]);
    $sameLetter = app(TokoPosDeliveryLetterService::class)->createFromInvoice($actor, $invoice, []);

    expect($letter)->toBeInstanceOf(DeliveryLetter::class)
        ->and($letter->company_id)->toBe($company->id)
        ->and($letter->invoice_id)->toBe($invoice->id)
        ->and($letter->number)->toStartWith('SJ-')
        ->and($letter->metadata['source'])->toBe('toko_pos_delivery_letter')
        ->and($letter->destination)->toBe('Gudang Srengseng')
        ->and($sameLetter->id)->toBe($letter->id);
});

test('toko add-on can create delivery letter from recent invoice', function (): void {
    setTokoPosDeliveryLetterLicenseFeatures(['toko_pos']);

    [$company, $actor, $invoice] = tokoDeliveryLetterFixture();

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class)
        ->call('createDeliveryLetterFromInvoice', $invoice->id);

    expect(DeliveryLetter::query()
        ->where('company_id', $company->id)
        ->where('invoice_id', $invoice->id)
        ->where('metadata->source', 'toko_pos_delivery_letter')
        ->exists())->toBeTrue();
});

test('toko delivery letter pdf can be downloaded from toko route', function (): void {
    setTokoPosDeliveryLetterLicenseFeatures(['toko_pos']);

    [$company, $actor, $invoice] = tokoDeliveryLetterFixture();
    $letter = app(TokoPosDeliveryLetterService::class)->createFromInvoice($actor, $invoice, []);

    $this->actingAs($actor)
        ->get(route('admin.toko.delivery-letters.pdf', $letter))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('toko delivery letters page shows surat jalan list and print action', function (): void {
    setTokoPosDeliveryLetterLicenseFeatures(['toko_pos']);

    [$company, $actor, $invoice] = tokoDeliveryLetterFixture();
    app(TokoPosDeliveryLetterService::class)->createFromInvoice($actor, $invoice, [
        'destination' => 'Gudang Srengseng',
        'driver_name' => 'Admin Gudang',
        'vehicle_number' => 'B 1234 PTM',
    ]);

    $this->actingAs($actor)
        ->get(route('admin.toko.delivery-letters'))
        ->assertOk()
        ->assertSee('Delivery Letter List')
        ->assertSee('Gudang Srengseng')
        ->assertSee('Admin Gudang')
        ->assertSee('B 1234 PTM')
        ->assertSee($invoice->number)
        ->assertSee('Print');
});

test('toko delivery letters page uses datatable pagination and search', function (): void {
    setTokoPosDeliveryLetterLicenseFeatures(['toko_pos']);

    [$company, $actor, $invoice] = tokoDeliveryLetterFixture();
    $product = Product::query()->where('company_id', $company->id)->firstOrFail();

    foreach (range(1, 12) as $index) {
        $saleInvoice = app(TokoPosSalesService::class)->createCounterSale($actor, [
            'company_id' => $company->id,
            'client_id' => $invoice->client_id,
            'payment_status' => 'paid',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
            ]],
        ]);

        app(TokoPosDeliveryLetterService::class)->createFromInvoice($actor, $saleInvoice, [
            'destination' => 'Gudang Datatable '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'driver_name' => 'Driver Datatable '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'vehicle_number' => 'B '.str_pad((string) $index, 4, '0', STR_PAD_LEFT).' PTM',
        ]);
    }

    $component = Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'delivery-letters'])
        ->assertSee('Show')
        ->assertSee('10')
        ->assertSee('entries')
        ->assertSee('Showing 1 to 10 of 12 delivery letter entries')
        ->assertSee('Gudang Datatable 12')
        ->assertSet('deliveryLetterPage', 1);

    $component
        ->call('nextDeliveryLetterPage')
        ->assertSee('Showing 11 to 12 of 12 delivery letter entries')
        ->assertSet('deliveryLetterPage', 2);

    $component
        ->set('deliveryLetterSearch', 'Gudang Datatable 07')
        ->assertSee('Showing 1 to 1 of 1 delivery letter entries')
        ->assertSee('Driver Datatable 07')
        ->assertSet('deliveryLetterPage', 1);
});

function tokoDeliveryLetterFixture(): array
{
    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-delivery',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'WARDI',
        'code' => 'CUST-SJ',
        'status' => Client::STATUS_ACTIVE,
        'contact_phone' => '085716004883',
        'address' => 'Srengseng',
    ]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter AC',
        'sku' => 'SKU-SJ',
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

    return [$company, $actor, $invoice];
}
