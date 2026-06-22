<?php

use App\Livewire\Admin\TokoPosAddon;
use App\Models\AccountingAccount;
use App\Models\Client;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Services\Enterprise\LicenseGuard;
use App\Support\TokoPosPurchaseService;
use App\Support\TokoPosSalesService;
use Livewire\Livewire;

function setTokoPosCatalogLicenseFeatures(array $features): void
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

test('toko catalog page can create and update product with legacy catalog metadata', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->set('productName', 'Pompa Celup 1 HP')
        ->set('productSku', 'PMP-001')
        ->set('productBarcode', '899700000001')
        ->set('productBrand', 'Shimizu')
        ->set('productCategory', 'Pompa')
        ->set('productUnit', 'unit')
        ->set('productColor', 'Biru')
        ->set('productSize', '1 HP')
        ->set('productLocation', 'Rak A1')
        ->set('productExpiredAt', '2027-12-31')
        ->set('productCostPrice', '750000')
        ->set('productSellingPrice', '950000')
        ->set('productReorderPoint', '3')
        ->call('saveCatalogProduct')
        ->assertHasNoErrors();

    $product = Product::query()->where('company_id', $company->id)->where('sku', 'PMP-001')->firstOrFail();

    expect($product->name)->toBe('Pompa Celup 1 HP')
        ->and($product->unit)->toBe('unit')
        ->and((float) $product->cost_price)->toBe(750000.0)
        ->and((float) $product->selling_price)->toBe(950000.0)
        ->and((float) $product->reorder_point)->toBe(3.0)
        ->and($product->metadata['barcode'])->toBe('899700000001')
        ->and($product->metadata['brand'])->toBe('Shimizu')
        ->and($product->metadata['category'])->toBe('Pompa')
        ->and($product->metadata['color'])->toBe('Biru')
        ->and($product->metadata['size'])->toBe('1 HP')
        ->and($product->metadata['location'])->toBe('Rak A1')
        ->and($product->metadata['expired_at'])->toBe('2027-12-31');

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->call('editCatalogProduct', $product->id)
        ->set('productSellingPrice', '975000')
        ->set('productBrand', 'Shimizu Pro')
        ->call('saveCatalogProduct')
        ->assertHasNoErrors();

    expect($product->fresh()->metadata['brand'])->toBe('Shimizu Pro')
        ->and((float) $product->fresh()->selling_price)->toBe(975000.0);
});

test('toko catalog barcode print route renders selected product barcode data', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Pompa Barcode',
        'sku' => 'BAR-001',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'cost_price' => 100000,
        'selling_price' => 150000,
        'stock_tracking' => true,
        'reorder_point' => 1,
        'metadata' => [
            'barcode' => '899700000099',
            'brand' => 'Pandan',
            'category' => 'Pompa',
        ],
    ]);

    $this->actingAs($superadmin)
        ->get(route('admin.toko.products.barcodes', ['products' => [$product->id]]))
        ->assertOk()
        ->assertSee('Pompa Barcode')
        ->assertSee('BAR-001')
        ->assertSee('899700000099');
});

test('toko catalog page exposes richer legacy product workbench', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-catalog-workbench',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();
    $today = now()->toDateString();
    $products = collect([
        [
            'name' => 'Cap AC Sigma 30+2 Uf',
            'sku' => 'CAP-030',
            'cost_price' => 28000,
            'selling_price' => 45000,
            'reorder_point' => 2,
            'quantity' => 5,
            'metadata' => ['barcode' => '899100000030', 'brand' => 'Sigma', 'category' => 'Kapasitor', 'location' => 'Rak A1'],
        ],
        [
            'name' => 'Filter Kosong Kecil',
            'sku' => 'FLT-001',
            'cost_price' => 3000,
            'selling_price' => 6000,
            'reorder_point' => 5,
            'quantity' => 2,
            'metadata' => ['barcode' => '899100000031', 'brand' => 'Pandan', 'category' => 'Filter', 'expired_at' => $today, 'location' => 'Rak B2'],
        ],
        [
            'name' => 'Pipa Hoda 1/4 - 3/8',
            'sku' => 'PIP-001',
            'cost_price' => 40000,
            'selling_price' => 70000,
            'reorder_point' => 1,
            'quantity' => 12,
            'metadata' => ['barcode' => '899100000032', 'brand' => 'Hoda', 'category' => 'Pipa', 'location' => 'Gudang'],
        ],
    ])->map(function (array $payload) use ($company, $superadmin): Product {
        $quantity = $payload['quantity'];
        unset($payload['quantity']);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'status' => Product::STATUS_ACTIVE,
            'unit' => 'pcs',
            'stock_tracking' => true,
            ...$payload,
        ]);
        StockMovement::query()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'user_id' => $superadmin->id,
            'type' => StockMovement::TYPE_IN,
            'quantity' => $quantity,
            'unit_cost' => $product->cost_price,
            'occurred_at' => now(),
            'metadata' => ['source' => 'opening_test'],
        ]);

        return $product;
    });

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->assertSee('Data Barang')
        ->assertSee('Tambah Barang')
        ->assertSee('Import Data')
        ->assertSee('Refresh')
        ->assertSee('Stok Limit')
        ->assertSee('Expired')
        ->assertSee('Barcode')
        ->assertSee('Kategori')
        ->assertSee('Brand')
        ->assertSee('Search')
        ->assertSee('Show')
        ->assertSee('entries')
        ->assertSee('Excel')
        ->assertSee('Cap AC Sigma 30+2 Uf')
        ->assertSee('28.000')
        ->assertSee('45.000')
        ->assertSee('5.000')
        ->assertSee('pcs')
        ->assertSee('1 stok limit')
        ->assertSee('1 expired')
        ->call('setProductCatalogFilter', 'low_stock')
        ->assertSee('Filter Kosong Kecil')
        ->assertDontSee('Cap AC Sigma 30+2 Uf')
        ->call('setProductCatalogFilter', 'expired')
        ->assertSee('Filter Kosong Kecil')
        ->assertDontSee('Pipa Hoda 1/4 - 3/8')
        ->call('setProductCatalogFilter', 'all')
        ->set('productSearch', 'hoda')
        ->assertSee('Pipa Hoda 1/4 - 3/8')
        ->assertDontSee('Cap AC Sigma 30+2 Uf')
        ->call('deactivateCatalogProduct', $products[2]->id)
        ->assertDispatched('banner-message');

    expect($products[2]->fresh()->status)->toBe(Product::STATUS_INACTIVE);
});

test('toko catalog low stock and expired workflows expose action plan and filtered export', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-catalog-stock-workflow',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);

    $lowStock = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Low Stock Capacitor',
        'sku' => 'LOW-001',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'cost_price' => 10000,
        'selling_price' => 15000,
        'reorder_point' => 5,
        'stock_tracking' => true,
        'metadata' => ['barcode' => '899200000001', 'brand' => 'Sigma', 'category' => 'Kapasitor', 'location' => 'Rak L'],
    ]);
    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $lowStock->id,
        'user_id' => $superadmin->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 2,
        'unit_cost' => 10000,
        'occurred_at' => now(),
        'metadata' => ['source' => 'opening_test'],
    ]);

    $expired = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Expired Cleaner',
        'sku' => 'EXP-001',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'botol',
        'cost_price' => 20000,
        'selling_price' => 30000,
        'reorder_point' => 1,
        'stock_tracking' => true,
        'metadata' => ['barcode' => '899200000002', 'brand' => 'Pandan', 'category' => 'Chemical', 'expired_at' => now()->toDateString(), 'location' => 'Rak E'],
    ]);
    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $expired->id,
        'user_id' => $superadmin->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 4,
        'unit_cost' => 20000,
        'occurred_at' => now(),
        'metadata' => ['source' => 'opening_test'],
    ]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->call('setProductCatalogFilter', 'low_stock')
        ->assertSee('Restock Plan')
        ->assertSee('Restock 3 pcs')
        ->call('setProductCatalogFilter', 'expired')
        ->assertSee('Expired Action')
        ->assertSee('Karantina');

    $lowStockExport = $this->actingAs($superadmin)->get(route('admin.toko.exports.products', ['filter' => 'low_stock']));
    $expiredExport = $this->actingAs($superadmin)->get(route('admin.toko.exports.products', ['filter' => 'expired']));

    $lowStockExport->assertOk();
    $expiredExport->assertOk();

    expect($lowStockExport->streamedContent())
        ->toContain('suggested_restock_quantity')
        ->toContain('LOW-001')
        ->toContain('3')
        ->not->toContain('EXP-001')
        ->and($expiredExport->streamedContent())
        ->toContain('expired_action')
        ->toContain('EXP-001')
        ->toContain('Karantina')
        ->not->toContain('LOW-001');
});

test('toko catalog table defaults to ten rows and keeps form submit buttons readable', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-catalog-datatable',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);

    foreach (range(1, 12) as $number) {
        Product::query()->create([
            'company_id' => $company->id,
            'name' => sprintf('Datatable Product %02d', $number),
            'sku' => sprintf('DT-%02d', $number),
            'status' => Product::STATUS_ACTIVE,
            'unit' => 'pcs',
            'selling_price' => 10000 + $number,
            'cost_price' => 5000,
            'stock_tracking' => true,
        ]);
    }

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->assertSee('Show')
        ->assertSee('10')
        ->assertSee('entries')
        ->assertSee('Showing 1 to 10 of 12 entries')
        ->assertSee('Previous')
        ->assertSee('Next')
        ->assertSee('Datatable Product 01')
        ->assertSee('Datatable Product 10')
        ->assertDontSee('Datatable Product 11')
        ->assertSee('Tambah Barang')
        ->call('setProductWorkspace', 'create')
        ->assertSeeHtml('data-form-action="catalog-product"')
        ->call('setProductWorkspace', 'catalog')
        ->call('nextProductPage')
        ->assertSee('Showing 11 to 12 of 12 entries')
        ->assertSee('Datatable Product 11')
        ->assertSee('Datatable Product 12')
        ->assertDontSee('Datatable Product 01')
        ->call('previousProductPage')
        ->assertSee('Showing 1 to 10 of 12 entries')
        ->assertSee('Datatable Product 01')
        ->assertDontSee('Datatable Product 11')
        ->call('gotoProductPage', 2)
        ->assertSee('Showing 11 to 12 of 12 entries')
        ->assertSee('Datatable Product 12')
        ->call('gotoProductPage', 99)
        ->assertSee('Showing 11 to 12 of 12 entries')
        ->call('gotoProductPage', 0)
        ->assertSee('Showing 1 to 10 of 12 entries');
});

test('toko catalog excludes inactive legacy history placeholders from operator product list', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-catalog-placeholder-filter',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);

    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Legacy Active Product',
        'sku' => 'SKU-ACTIVE',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 25000,
        'cost_price' => 15000,
        'stock_tracking' => true,
    ]);

    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Legacy Missing Product Placeholder',
        'sku' => 'LEGACY-TOKO-MISSING001',
        'status' => Product::STATUS_INACTIVE,
        'unit' => 'pcs',
        'selling_price' => 0,
        'cost_price' => 0,
        'stock_tracking' => true,
        'metadata' => [
            'source' => 'legacy_toko_placeholder',
            'legacy_toko' => [
                'kode' => 'MISSING001',
                'missing_from_master_dump' => true,
            ],
        ],
    ]);

    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Inactive Operator Product',
        'sku' => 'SKU-INACTIVE',
        'status' => Product::STATUS_INACTIVE,
        'unit' => 'pcs',
        'selling_price' => 12000,
        'cost_price' => 8000,
        'stock_tracking' => true,
    ]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->assertSee('Total Barang')
        ->assertSee('Showing 1 to 1 of 1 entries')
        ->assertSee('Legacy Active Product')
        ->assertDontSee('Legacy Missing Product Placeholder')
        ->assertDontSee('LEGACY-TOKO-MISSING001')
        ->assertDontSee('Inactive Operator Product')
        ->assertDontSee('SKU-INACTIVE');
});

test('toko customers page can convert customer status to berlangganan with audit metadata', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-customer-convert',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $customer = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'SANDY TEKNIK',
        'code' => '0004',
        'status' => Client::STATUS_ACTIVE,
        'metadata' => [
            'source' => 'legacy_toko_customer',
            'membership_status' => 'prospect',
        ],
    ]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'customers'])
        ->assertSee('Prospect')
        ->call('convertTokoCustomer', $customer->id)
        ->assertHasNoErrors()
        ->assertSee('Berlangganan');

    $customer = $customer->fresh();

    expect($customer->metadata['membership_status'])->toBe('berlangganan')
        ->and($customer->metadata['converted_to_member_at'])->not->toBeNull()
        ->and($customer->metadata['converted_to_member_by'])->toBe($superadmin->id);
});

test('toko catalog toolbar exposes working product export and print actions', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-catalog-actions',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);

    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Actionable Product',
        'sku' => 'ACT-001',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 25000,
        'cost_price' => 15000,
        'stock_tracking' => true,
        'metadata' => [
            'barcode' => '899700000111',
            'brand' => 'Pandan',
            'category' => 'Action',
        ],
    ]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->assertSeeHtml('href="'.route('admin.toko.exports.products').'"')
        ->assertSeeHtml('href="'.route('admin.toko.products.barcodes', ['products' => [$product->id]], false).'"')
        ->assertSeeHtml('aria-label="Excel"')
        ->assertSeeHtml('aria-label="Print"');

    $export = $this->actingAs($superadmin)->get('/admin/toko/exports/products.csv');

    $export->assertOk();

    $csv = $export->streamedContent();

    expect($csv)->toContain('sku,name,status,unit,brand,category,barcode,location,stock_balance,cost_price,selling_price,reorder_point,margin')
        ->and($csv)->toContain('ACT-001')
        ->and($csv)->toContain('Actionable Product');
});

test('toko catalog product row can open stock card drilldown', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-product-stock-card',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);

    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Stock Card Compressor',
        'sku' => 'SCC-001',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 85000,
        'cost_price' => 50000,
        'reorder_point' => 2,
        'stock_tracking' => true,
        'metadata' => [
            'barcode' => 'SCCBAR',
            'brand' => 'Pandan',
            'category' => 'Compressor',
            'location' => 'Rak Detail',
        ],
    ]);

    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $superadmin->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 8,
        'unit_cost' => 50000,
        'reference_number' => 'OPEN-SCC',
        'occurred_at' => now()->subDay(),
        'notes' => 'Opening stock',
        'metadata' => ['source' => 'opening_stock'],
    ]);
    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $superadmin->id,
        'type' => StockMovement::TYPE_OUT,
        'quantity' => 3,
        'unit_cost' => 50000,
        'reference_number' => 'POS-SCC',
        'occurred_at' => now(),
        'notes' => 'Counter sale',
        'metadata' => ['source' => 'toko_pos_counter_sale'],
    ]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->call('viewProductStockCard', $product->id)
        ->assertHasNoErrors()
        ->assertSee('Product Stock Card')
        ->assertSee('Stock Card Compressor')
        ->assertSee('SCC-001')
        ->assertSee('Rak Detail')
        ->assertSee('5.000')
        ->assertSee('35.000')
        ->assertSee('OPEN-SCC')
        ->assertSee('POS-SCC')
        ->assertSee('Counter sale')
        ->call('clearProductStockCard')
        ->assertDontSee('Product Stock Card');
});

test('toko products workspace exposes legacy barang tree with modern focused panels', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-barang-tree',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Barcode Tree Product',
        'sku' => 'TREE-001',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 18000,
        'cost_price' => 9000,
        'stock_tracking' => true,
        'metadata' => [
            'barcode' => 'TREE0001',
            'brand' => 'Legacy Brand',
            'category' => 'Legacy Category',
        ],
    ]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->assertSee('Data Barang')
        ->assertSee('Tambah Barang')
        ->assertSee('Barcode')
        ->assertSee('Kategori')
        ->assertSee('Brand')
        ->call('setProductWorkspace', 'create')
        ->assertSee('Form Barang')
        ->assertSee('Standard')
        ->assertSee('Advanced')
        ->call('setProductWorkspace', 'barcode')
        ->set('barcodeProductId', (string) $product->id)
        ->set('barcodePrintQuantity', '3')
        ->assertSee('Modul Cetak Barcode')
        ->assertSee('TREE0001')
        ->assertSeeHtml('products%5B0%5D='.$product->id)
        ->call('setProductWorkspace', 'categories')
        ->assertSee('Data Kategori')
        ->assertSee('Legacy Category')
        ->set('productCategoryName', 'Kategori Baru')
        ->call('saveProductCategory')
        ->assertHasNoErrors()
        ->assertSee('Kategori Baru')
        ->call('setProductWorkspace', 'brands')
        ->assertSee('Data Brand')
        ->assertSee('Legacy Brand')
        ->set('productBrandName', 'Brand Baru')
        ->call('saveProductBrand')
        ->assertHasNoErrors()
        ->assertSee('Brand Baru');

    expect(json_decode(Setting::getValue('toko_pos.product_categories', '[]'), true)[0]['name'])->toBe('Kategori Baru')
        ->and(json_decode(Setting::getValue('toko_pos.product_brands', '[]'), true)[0]['name'])->toBe('Brand Baru');
});

test('toko products workspace can remove manual category and brand rows', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-taxonomy-delete',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->call('setProductWorkspace', 'categories')
        ->set('productCategoryName', 'Kategori Hapus')
        ->call('saveProductCategory')
        ->assertSee('Kategori Hapus')
        ->call('deleteProductCategory', 'Kategori Hapus')
        ->assertHasNoErrors()
        ->assertDontSee('Kategori Hapus')
        ->call('setProductWorkspace', 'brands')
        ->set('productBrandName', 'Brand Hapus')
        ->call('saveProductBrand')
        ->assertSee('Brand Hapus')
        ->call('deleteProductBrand', 'Brand Hapus')
        ->assertHasNoErrors()
        ->assertDontSee('Brand Hapus');

    expect(json_encode(Setting::getValue('toko_pos.product_categories', '[]')))->not->toContain('Kategori Hapus')
        ->and(json_encode(Setting::getValue('toko_pos.product_brands', '[]')))->not->toContain('Brand Hapus');
});

test('toko customer page can create and update legacy customer profile', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'customers'])
        ->set('customerCode', 'CUST-001')
        ->set('customerName', 'PT Maju Pompa')
        ->set('customerPhone', '021123456')
        ->set('customerEmail', 'buyer@example.test')
        ->set('customerAddress', 'Jl. Merdeka 1')
        ->set('customerStatus', Client::STATUS_ACTIVE)
        ->call('saveTokoCustomer')
        ->assertHasNoErrors();

    $client = Client::query()->where('company_id', $company->id)->where('code', 'CUST-001')->firstOrFail();

    expect($client->name)->toBe('PT Maju Pompa')
        ->and($client->contact_phone)->toBe('021123456')
        ->and($client->contact_email)->toBe('buyer@example.test')
        ->and($client->address)->toBe('Jl. Merdeka 1')
        ->and($client->metadata['source'])->toBe('toko_pos_customer');

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'customers'])
        ->call('editTokoCustomer', $client->id)
        ->set('customerPhone', '021999999')
        ->call('saveTokoCustomer')
        ->assertHasNoErrors();

    expect($client->fresh()->contact_phone)->toBe('021999999');
});

test('toko customer page uses datatable pagination and search', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    foreach (range(1, 12) as $index) {
        Client::query()->create([
            'company_id' => $company->id,
            'code' => 'CUST-DT-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'name' => 'Customer Datatable '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'contact_phone' => '080000000'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'contact_email' => 'customer'.$index.'@example.test',
            'address' => 'Alamat Customer '.$index,
            'status' => Client::STATUS_ACTIVE,
        ]);
    }

    $component = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'customers'])
        ->assertSee('Show')
        ->assertSee('10')
        ->assertSee('entries')
        ->assertSee('Showing 1 to 10 of 12 customer entries')
        ->assertSee('Customer Datatable 12')
        ->assertSet('customerPage', 1);

    $component
        ->call('nextCustomerPage')
        ->assertSee('Showing 11 to 12 of 12 customer entries')
        ->assertSet('customerPage', 2);

    $component
        ->set('customerSearch', 'CUST-DT-07')
        ->assertSee('Showing 1 to 1 of 1 customer entries')
        ->assertSee('Customer Datatable 07')
        ->assertSet('customerPage', 1);
});

test('toko customer page shows sales history and ar summary', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'PT Income Customer',
        'code' => 'INC-001',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Income Product',
        'sku' => 'INC-PROD',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 12000,
        'cost_price' => 7000,
        'stock_tracking' => true,
    ]);

    app(TokoPosSalesService::class)->createCounterSale($superadmin, [
        'company_id' => $company->id,
        'client_id' => $client->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
        ]],
    ]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'customers'])
        ->assertSee('Customer Income')
        ->assertSee('PT Income Customer')
        ->assertSee('24.000')
        ->assertSee('AR');
});

test('toko customer page can safely deactivate legacy customer profile', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-customer-delete',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'Customer Legacy Delete',
        'code' => 'CUST-DEL',
        'status' => Client::STATUS_ACTIVE,
    ]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'customers'])
        ->assertSee('Customer Legacy Delete')
        ->call('deactivateTokoCustomer', $client->id)
        ->assertHasNoErrors()
        ->assertDispatched('banner-message')
        ->assertSee('inactive');

    expect($client->fresh()->status)->toBe(Client::STATUS_INACTIVE);
});

test('toko customer income csv export includes customer totals', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'PT Export Customer',
        'code' => 'EXP-001',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Export Income Product',
        'sku' => 'EXP-PROD',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 18000,
        'cost_price' => 9000,
        'stock_tracking' => true,
    ]);

    app(TokoPosSalesService::class)->createCounterSale($superadmin, [
        'company_id' => $company->id,
        'client_id' => $client->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ]);

    $response = $this->actingAs($superadmin)->get(route('admin.toko.exports.customer-income'));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())->toContain('customer,total,ar_total,invoice_count')
        ->toContain('PT Export Customer')
        ->toContain('18000');
});

test('toko vendor page can create and update legacy supplier profile', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'vendors'])
        ->set('vendorCode', 'SUP-001')
        ->set('vendorName', 'Supplier Pompa Jaya')
        ->set('vendorPhone', '021765432')
        ->set('vendorEmail', 'supplier@example.test')
        ->set('vendorAddress', 'Jl. Industri 9')
        ->set('vendorStatus', Vendor::STATUS_ACTIVE)
        ->call('saveTokoVendor')
        ->assertHasNoErrors();

    $vendor = Vendor::query()->where('company_id', $company->id)->where('metadata->legacy_code', 'SUP-001')->firstOrFail();

    expect($vendor->name)->toBe('Supplier Pompa Jaya')
        ->and($vendor->phone)->toBe('021765432')
        ->and($vendor->email)->toBe('supplier@example.test')
        ->and($vendor->address)->toBe('Jl. Industri 9')
        ->and($vendor->metadata['source'])->toBe('toko_pos_vendor');

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'vendors'])
        ->call('editTokoVendor', $vendor->id)
        ->set('vendorPhone', '021888888')
        ->call('saveTokoVendor')
        ->assertHasNoErrors();

    expect($vendor->fresh()->phone)->toBe('021888888');
});

test('toko vendor page uses datatable pagination and search', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    foreach (range(1, 12) as $index) {
        Vendor::query()->create([
            'company_id' => $company->id,
            'name' => 'Vendor Datatable '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'phone' => '081111111'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'email' => 'vendor'.$index.'@example.test',
            'address' => 'Alamat Vendor '.$index,
            'status' => Vendor::STATUS_ACTIVE,
            'metadata' => ['legacy_code' => 'SUP-DT-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT)],
        ]);
    }

    $component = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'vendors'])
        ->assertSee('Show')
        ->assertSee('10')
        ->assertSee('entries')
        ->assertSee('Showing 1 to 10 of 12 vendor entries')
        ->assertSee('Vendor Datatable 12')
        ->assertSet('vendorPage', 1);

    $component
        ->call('nextVendorPage')
        ->assertSee('Showing 11 to 12 of 12 vendor entries')
        ->assertSet('vendorPage', 2);

    $component
        ->set('vendorSearch', 'SUP-DT-07')
        ->assertSee('Showing 1 to 1 of 1 vendor entries')
        ->assertSee('Vendor Datatable 07')
        ->assertSet('vendorPage', 1);
});

test('toko vendor page can safely deactivate legacy supplier profile', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-vendor-delete',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();
    $vendor = Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Supplier Legacy Delete',
        'status' => Vendor::STATUS_ACTIVE,
        'metadata' => ['legacy_code' => 'SUP-DEL'],
    ]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'vendors'])
        ->assertSee('Supplier Legacy Delete')
        ->call('deactivateTokoVendor', $vendor->id)
        ->assertHasNoErrors()
        ->assertDispatched('banner-message')
        ->assertSee('inactive');

    expect($vendor->fresh()->status)->toBe(Vendor::STATUS_INACTIVE);
});

test('toko vendor page shows ap drilldown and purchase history', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-vendor-ap',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $vendor = Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Supplier AP Drilldown',
        'status' => Vendor::STATUS_ACTIVE,
        'metadata' => ['legacy_code' => 'SUP-AP'],
    ]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Kompresor AP',
        'sku' => 'AP-001',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'Unit',
        'selling_price' => 40000,
        'cost_price' => 30000,
        'stock_tracking' => true,
    ]);

    $service = app(TokoPosPurchaseService::class);
    $paidBill = $service->createPurchase($superadmin, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_cost' => 10000,
        ]],
    ]);
    $service->recordVendorBillPayment($superadmin, $paidBill);

    $openBill = $service->createPurchase($superadmin, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_cost' => 10000,
        ]],
    ]);
    $service->recordVendorBillPayment($superadmin, $openBill, 5000);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'vendors'])
        ->call('viewTokoVendorDetail', $vendor->id)
        ->assertHasNoErrors()
        ->assertSee('Vendor AP Summary')
        ->assertSee('Supplier AP Drilldown')
        ->assertSee('Total Purchases')
        ->assertSee('30.000')
        ->assertSee('Open AP')
        ->assertSee('15.000')
        ->assertSee('Paid Total')
        ->assertSee('15.000')
        ->assertSee('Recent Purchases')
        ->assertSee($paidBill->number)
        ->assertSee($openBill->number)
        ->assertSee(VendorBill::STATUS_PAID)
        ->assertSee(VendorBill::STATUS_POSTED)
        ->call('clearTokoVendorDetail')
        ->assertDontSee('Vendor AP Summary');
});

test('toko cash page can maintain legacy payment methods and bank accounts', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'cash'])
        ->set('paymentMethodName', 'Transfer Bank')
        ->call('savePaymentMethod')
        ->set('bankCode', 'BCA-001')
        ->set('bankName', 'BCA')
        ->set('bankAccountNumber', '1234567890')
        ->set('bankAccountName', 'PT Pandan Teknik')
        ->call('saveBankAccount')
        ->assertHasNoErrors()
        ->assertSee('Transfer Bank')
        ->assertSee('BCA-001')
        ->assertSee('1234567890');

    expect(json_decode(Setting::getValue('toko_pos.payment_methods', '[]'), true))
        ->toContain(['name' => 'Transfer Bank', 'active' => true])
        ->and(json_decode(Setting::getValue('toko_pos.bank_accounts', '[]'), true)[0])
        ->toMatchArray([
            'code' => 'BCA-001',
            'bank' => 'BCA',
            'number' => '1234567890',
            'name' => 'PT Pandan Teknik',
            'active' => true,
        ]);
});

test('toko cash page manages expense types and datatable operational expenses', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    $expenseAccount = AccountingAccount::query()->create([
        'company_id' => $company->id,
        'code' => '5400',
        'name' => 'Operating Expenses',
        'type' => AccountingAccount::TYPE_EXPENSE,
        'normal_balance' => AccountingAccount::BALANCE_DEBIT,
        'is_active' => true,
    ]);
    $cashAccount = AccountingAccount::query()->create([
        'company_id' => $company->id,
        'code' => '1100',
        'name' => 'Cash / Bank',
        'type' => AccountingAccount::TYPE_ASSET,
        'normal_balance' => AccountingAccount::BALANCE_DEBIT,
        'is_active' => true,
    ]);

    foreach (range(1, 12) as $index) {
        $entry = JournalEntry::query()->create([
            'company_id' => $company->id,
            'created_by' => $superadmin->id,
            'number' => 'OPEX-DT-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
            'entry_date' => now()->subDays($index)->toDateString(),
            'status' => JournalEntry::STATUS_POSTED,
            'source_type' => 'toko_pos_operational_expense',
            'reference_number' => 'OPEX-DT-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'description' => 'Expense Datatable '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'metadata' => [
                'source' => 'toko_pos_operational_expense',
                'expense_type' => 'Type Datatable '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'payment_method' => 'Cash',
                'bank_code' => 'CASH',
            ],
        ]);

        JournalEntryLine::query()->create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $expenseAccount->id,
            'debit' => 1000 + $index,
            'credit' => 0,
            'memo' => 'expense',
        ]);
        JournalEntryLine::query()->create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $cashAccount->id,
            'debit' => 0,
            'credit' => 1000 + $index,
            'memo' => 'cash',
        ]);
    }

    $component = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'cash'])
        ->set('expenseTypeName', 'Gaji Karyawan')
        ->call('saveExpenseType')
        ->assertHasNoErrors()
        ->assertSee('Gaji Karyawan')
        ->assertSee('Show')
        ->assertSee('10')
        ->assertSee('entries')
        ->assertSee('Showing 1 to 10 of 12 operational expense entries')
        ->assertSee('Expense Datatable 12')
        ->assertSet('operationalExpensePage', 1);

    $component
        ->call('nextOperationalExpensePage')
        ->assertSee('Showing 11 to 12 of 12 operational expense entries')
        ->assertSet('operationalExpensePage', 2);

    $component
        ->set('operationalExpenseSearch', 'OPEX-DT-07')
        ->assertSee('Showing 1 to 1 of 1 operational expense entries')
        ->assertSee('Expense Datatable 07')
        ->assertSet('operationalExpensePage', 1);

    expect(json_decode(Setting::getValue('toko_pos.expense_types', '[]'), true))
        ->toContain(['name' => 'Gaji Karyawan', 'active' => true]);
});

test('toko cash operational expense table filters by report period and exports same range', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-opex-period',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    $expenseAccount = AccountingAccount::query()->create([
        'company_id' => $company->id,
        'code' => '5400',
        'name' => 'Operating Expenses',
        'type' => AccountingAccount::TYPE_EXPENSE,
        'normal_balance' => AccountingAccount::BALANCE_DEBIT,
        'is_active' => true,
    ]);
    $cashAccount = AccountingAccount::query()->create([
        'company_id' => $company->id,
        'code' => '1100',
        'name' => 'Cash / Bank',
        'type' => AccountingAccount::TYPE_ASSET,
        'normal_balance' => AccountingAccount::BALANCE_DEBIT,
        'is_active' => true,
    ]);

    foreach ([
        ['date' => '2026-06-12', 'reference' => 'OPEX-JUN', 'description' => 'June listrik toko', 'amount' => 225000],
        ['date' => '2026-05-12', 'reference' => 'OPEX-MAY', 'description' => 'May listrik toko', 'amount' => 175000],
    ] as $row) {
        $entry = JournalEntry::query()->create([
            'company_id' => $company->id,
            'created_by' => $superadmin->id,
            'number' => $row['reference'],
            'entry_date' => $row['date'],
            'status' => JournalEntry::STATUS_POSTED,
            'source_type' => 'toko_pos_operational_expense',
            'reference_number' => $row['reference'],
            'description' => $row['description'],
            'metadata' => [
                'source' => 'toko_pos_operational_expense',
                'expense_type' => 'Listrik',
                'payment_method' => 'Cash',
                'bank_code' => 'CASH',
            ],
        ]);

        JournalEntryLine::query()->create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $expenseAccount->id,
            'debit' => $row['amount'],
            'credit' => 0,
            'memo' => 'expense',
        ]);
        JournalEntryLine::query()->create([
            'journal_entry_id' => $entry->id,
            'accounting_account_id' => $cashAccount->id,
            'debit' => 0,
            'credit' => $row['amount'],
            'memo' => 'cash',
        ]);
    }

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'cash'])
        ->set('operationalExpenseFromDate', '2026-06-01')
        ->set('operationalExpenseToDate', '2026-06-30')
        ->assertSee('June listrik toko')
        ->assertDontSee('May listrik toko')
        ->assertSee('Showing 1 to 1 of 1 operational expense entries')
        ->assertSee(route('admin.toko.exports.report-operational-expenses', [
            'from' => '2026-06-01',
            'to' => '2026-06-30',
        ]));
});

test('toko migration workspace can be hidden after cutover is completed', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    Setting::updateOrCreate(
        ['key' => 'toko_pos.migration_enabled'],
        ['value' => 'false', 'group' => 'toko_pos', 'type' => 'boolean']
    );
    Setting::flushCache('toko_pos.migration_enabled');

    $superadmin = User::factory()->admin(true)->create();

    $this->actingAs($superadmin)
        ->get(route('admin.toko'))
        ->assertOk()
        ->assertDontSee(route('admin.toko.migration'), false);

    $this->actingAs($superadmin)
        ->get(route('admin.toko.migration'))
        ->assertNotFound();
});

test('toko cash page can record operational expense into accounting journal', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'cash'])
        ->set('operationalExpenseType', 'Transport')
        ->set('operationalExpenseAmount', '125000')
        ->set('operationalExpensePaymentMethod', 'Cash')
        ->set('operationalExpenseBankCode', 'CASH')
        ->set('operationalExpenseDescription', 'Kirim barang ke customer')
        ->call('recordOperationalExpense')
        ->assertHasNoErrors()
        ->assertSee('Transport')
        ->assertSee('125.000');

    $journal = JournalEntry::query()
        ->where('company_id', $company->id)
        ->where('source_type', 'toko_pos_operational_expense')
        ->with('lines.account')
        ->firstOrFail();

    expect($journal->description)->toBe('Kirim barang ke customer')
        ->and($journal->metadata['expense_type'])->toBe('Transport')
        ->and($journal->metadata['payment_method'])->toBe('Cash')
        ->and($journal->lines->firstWhere('account.code', '5400'))->not->toBeNull()
        ->and($journal->lines->firstWhere('account.code', '1100'))->not->toBeNull()
        ->and((float) $journal->lines->sum('debit'))->toBe(125000.0)
        ->and((float) $journal->lines->sum('credit'))->toBe(125000.0);
});

test('toko cash page can edit and void operational expense with audit metadata', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-cash-expense-audit',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'cash'])
        ->set('operationalExpenseType', 'Listrik')
        ->set('operationalExpenseAmount', '100000')
        ->set('operationalExpensePaymentMethod', 'Cash')
        ->set('operationalExpenseBankCode', 'CASH')
        ->set('operationalExpenseDescription', 'Listrik toko')
        ->call('recordOperationalExpense')
        ->assertHasNoErrors();

    $journal = JournalEntry::query()
        ->where('company_id', $company->id)
        ->where('source_type', 'toko_pos_operational_expense')
        ->firstOrFail();

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'cash'])
        ->call('editOperationalExpense', $journal->id)
        ->assertSet('editingOperationalExpenseId', $journal->id)
        ->assertSet('operationalExpenseType', 'Listrik')
        ->set('operationalExpenseType', 'Pajak')
        ->set('operationalExpenseAmount', '150000')
        ->set('operationalExpensePaymentMethod', 'Transfer Bank')
        ->set('operationalExpenseBankCode', 'BCA-001')
        ->set('operationalExpenseDescription', 'Pajak toko revisi')
        ->call('recordOperationalExpense')
        ->assertHasNoErrors();

    $journal = $journal->fresh('lines');

    expect($journal->description)->toBe('Pajak toko revisi')
        ->and($journal->metadata['expense_type'])->toBe('Pajak')
        ->and($journal->metadata['payment_method'])->toBe('Transfer Bank')
        ->and($journal->metadata['edited_by'])->toBe($superadmin->id)
        ->and($journal->metadata['edited_at'])->not->toBeNull()
        ->and((float) $journal->lines->sum('debit'))->toBe(150000.0)
        ->and((float) $journal->lines->sum('credit'))->toBe(150000.0);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'cash'])
        ->call('voidOperationalExpense', $journal->id)
        ->assertHasNoErrors();

    $journal = $journal->fresh('lines');

    expect($journal->status)->toBe('void')
        ->and($journal->metadata['voided_by'])->toBe($superadmin->id)
        ->and($journal->metadata['voided_at'])->not->toBeNull()
        ->and((float) $journal->lines->sum('debit'))->toBe(0.0)
        ->and((float) $journal->lines->sum('credit'))->toBe(0.0);
});

test('toko reports page shows operational expense report and csv export', function () {
    setTokoPosCatalogLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik-operational-report',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'cash'])
        ->set('operationalExpenseType', 'Transport')
        ->set('operationalExpenseAmount', '125000')
        ->set('operationalExpensePaymentMethod', 'Cash')
        ->set('operationalExpenseBankCode', 'CASH')
        ->set('operationalExpenseDescription', 'Kirim barang ke customer')
        ->call('recordOperationalExpense')
        ->assertHasNoErrors();

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'reports'])
        ->assertSee('Operational Expense Report')
        ->assertSee('Transport')
        ->assertSee('125.000')
        ->assertSee(route('admin.toko.exports.report-operational-expenses'), false);

    $response = $this->actingAs($superadmin)->get(route('admin.toko.exports.report-operational-expenses'));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())->toContain('date,type,description,amount,payment_method,bank_code')
        ->toContain('Transport')
        ->toContain('Kirim barang ke customer')
        ->toContain('125000');
});
