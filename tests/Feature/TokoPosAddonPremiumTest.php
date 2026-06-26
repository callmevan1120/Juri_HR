<?php

use App\Livewire\Admin\TokoPosAddon;
use App\Models\AccountingAccount;
use App\Models\Client;
use App\Models\Company;
use App\Models\CompanyBranch;
use App\Models\ImportExportRun;
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
use App\Support\TokoPosPurchaseService;
use App\Support\TokoPosReportService;
use App\Support\TokoPosSalesService;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    requireEnterpriseRuntimeSourceForTests('toko_pos');
});

function setTokoPosLicenseFeatures(array $features): void
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

function expectTokoTomSelectIds(string $html, array $ids): void
{
    foreach ($ids as $id) {
        expect($html)->toContain('id="'.$id.'"');
        expect($html)->toContain('tomSelectInput(');
    }
}

test('toko pos add-on route is locked without premium feature entitlement', function () {
    setTokoPosLicenseFeatures([]);

    $superadmin = User::factory()->admin(true)->create();

    $this->actingAs($superadmin)
        ->get(route('admin.toko'))
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('show-feature-lock');
});

test('toko pos add-on route opens with premium feature entitlement', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $superadmin = User::factory()->admin(true)->create();

    $this->actingAs($superadmin)
        ->get(route('admin.toko'))
        ->assertOk()
        ->assertSee(__('Transaction Command Center'))
        ->assertDontSee(__('Dry-run Master Import'))
        ->assertDontSee(__('Create Sale'))
        ->assertSee('feature: toko_pos')
        ->assertSee('module_type: addon')
        ->assertSee('license_feature: toko_pos')
        ->assertSee('data-toko-addon-flag="toko_pos"', false)
        ->assertSee('data-toko-nav-addon-flag="toko_pos"', false)
        ->assertSee('data-toko-nav-tree="toko_pos"', false)
        ->assertSee('x-data="{ treeExpanded: true }"', false)
        ->assertSee('@click.stop="treeExpanded = !treeExpanded"', false)
        ->assertSee('x-show="treeExpanded"', false)
        ->assertSee('data-toko-nav-link="admin.toko.pos"', false)
        ->assertSee('data-toko-nav-link="admin.toko.products"', false)
        ->assertSee('data-toko-nav-link="admin.toko.reports"', false);
});

test('toko admin workspaces use searchable tomselect dropdowns for operational choices', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-tomselect-workspaces',
        'status' => Company::STATUS_ACTIVE,
    ]);
    CompanyBranch::query()->create([
        'company_id' => $company->id,
        'name' => 'Toko Pusat',
        'code' => 'PST',
        'status' => 'active',
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);

    Setting::query()->updateOrCreate([
        'key' => 'toko_pos.expense_types',
    ], [
        'value' => json_encode([['name' => 'Operasional Toko', 'active' => true]]),
        'group' => 'toko_pos',
        'type' => 'json',
    ]);
    Setting::flushCache('toko_pos.expense_types');

    $posHtml = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->set('showPosBackOffice', true)
        ->html();
    expectTokoTomSelectIds($posHtml, [
        'toko-company-selector',
        'toko-branch-selector',
        'toko-pos-client',
        'toko-pos-payment-invoice',
        'toko-pos-cancel-invoice',
    ]);

    $productCreateHtml = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->call('setProductWorkspace', 'create')
        ->html();
    expectTokoTomSelectIds($productCreateHtml, [
        'toko-product-status',
    ]);

    $productBarcodeHtml = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->call('setProductWorkspace', 'barcode')
        ->html();
    expectTokoTomSelectIds($productBarcodeHtml, [
        'toko-barcode-product',
    ]);

    $customersHtml = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'customers'])
        ->html();
    expectTokoTomSelectIds($customersHtml, [
        'toko-customer-status',
    ]);

    $inventoryHtml = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'inventory'])
        ->html();
    expectTokoTomSelectIds($inventoryHtml, [
        'toko-inventory-return-product',
        'toko-inventory-return-type',
        'toko-inventory-manual-product',
        'toko-inventory-manual-type',
        'toko-inventory-cancel-movement',
        'toko-inventory-adjustment-product',
    ]);

    $quotationHtml = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'quotations'])
        ->html();
    expectTokoTomSelectIds($quotationHtml, [
        'toko-quotation-client',
        'toko-quotation-product',
    ]);

    $purchaseHtml = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'purchases'])
        ->html();
    expectTokoTomSelectIds($purchaseHtml, [
        'toko-purchase-vendor',
        'toko-purchase-product',
        'toko-purchase-vendor-bill-payment',
        'toko-purchase-cancel-vendor-bill',
    ]);

    $vendorsHtml = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'vendors'])
        ->html();
    expectTokoTomSelectIds($vendorsHtml, [
        'toko-vendor-status',
    ]);

    $cashHtml = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'cash'])
        ->html();
    expectTokoTomSelectIds($cashHtml, [
        'toko-operational-expense-type',
    ]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'migration'])
        ->assertSee(__('CSV Template Import'))
        ->assertDontSee('toko-dump-source');
});

test('toko migration workspace is head-level and hidden from company admins', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Migration Guard Company',
        'slug' => 'migration-guard-company',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $companyAdmin = User::factory()->admin(false)->create(['company_id' => $company->id]);

    $this->actingAs($companyAdmin)
        ->get(route('admin.toko.migration'))
        ->assertForbidden();

    Livewire::actingAs($companyAdmin)
        ->test(TokoPosAddon::class, ['page' => 'dashboard'])
        ->assertDontSee(route('admin.toko.migration'), false)
        ->assertDontSee(__('CSV Template Import'))
        ->assertDontSee(__('Template-based master data migration.'));
});

test('toko migration workspace uses csv templates instead of legacy dump mapping', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    Company::query()->create([
        'name' => 'CSV Migration Company',
        'slug' => 'csv-migration-company',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    $this->actingAs($superadmin)
        ->get(route('admin.toko.migration'))
        ->assertOk()
        ->assertSee(__('CSV Template Import'))
        ->assertSee(__('Template-based master data migration.'))
        ->assertSee('/admin/toko/import/templates/products.csv', false)
        ->assertSee('/admin/toko/import/templates/customers.csv', false)
        ->assertSee('/admin/toko/import/templates/vendors.csv', false)
        ->assertSee('/admin/toko/import', false)
        ->assertDontSee(__('Legacy dump source'))
        ->assertDontSee(__('Dry-run Master Import'))
        ->assertDontSee(__('Import Historical Documents'))
        ->assertDontSee(__('Archive Cutover Report'))
        ->assertDontSee(__('Mapped Rows'))
        ->assertDontSee(__('Unmapped Tables'))
        ->assertDontSee(__('Historical Reconciliation'));
});

test('toko csv import templates can be downloaded', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $superadmin = User::factory()->admin(true)->create();

    $response = $this->actingAs($superadmin)
        ->get(route('admin.toko.import-template', ['type' => 'customers']));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())
        ->toContain('code,name,phone,email,address,status')
        ->toContain('CUST-001');
});

test('toko csv imports scoped products customers and vendors into target company', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'CSV Target Company',
        'slug' => 'csv-target-company',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $otherCompany = Company::query()->create([
        'name' => 'Other CSV Company',
        'slug' => 'other-csv-company',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);

    $productFile = UploadedFile::fake()->createWithContent('products.csv', implode("\n", [
        'sku,name,unit,selling_price,cost_price,stock_tracking,reorder_point,status',
        'CSV-001,Produk CSV,pcs,12500,8000,yes,3,active',
    ]));

    $this->actingAs($superadmin)
        ->post(route('admin.toko.import'), [
            'import_type' => 'products',
            'import_file' => $productFile,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $customerFile = UploadedFile::fake()->createWithContent('customers.csv', implode("\n", [
        'code,name,phone,email,address,status',
        'CUST-CSV-001,Ayu CSV,08123456789,ayu@example.test,Jl CSV 1,active',
    ]));

    $this->actingAs($superadmin)
        ->post(route('admin.toko.import'), [
            'import_type' => 'customers',
            'import_file' => $customerFile,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $vendorFile = UploadedFile::fake()->createWithContent('vendors.csv', implode("\n", [
        'code,name,phone,email,address,tax_number,status',
        'VEND-CSV-001,Vendor CSV,08987654321,vendor@example.test,Jl Vendor CSV,01.234.567.8-999.000,active',
    ]));

    $this->actingAs($superadmin)
        ->post(route('admin.toko.import'), [
            'import_type' => 'vendors',
            'import_file' => $vendorFile,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Product::query()->where('company_id', $company->id)->where('sku', 'CSV-001')->exists())->toBeTrue()
        ->and(Product::query()->where('company_id', $otherCompany->id)->where('sku', 'CSV-001')->exists())->toBeFalse()
        ->and(Client::query()->where('company_id', $company->id)->where('code', 'CUST-CSV-001')->exists())->toBeTrue()
        ->and(Client::query()->where('company_id', $otherCompany->id)->where('code', 'CUST-CSV-001')->exists())->toBeFalse()
        ->and(Vendor::query()->where('company_id', $company->id)->where('metadata->legacy_code', 'VEND-CSV-001')->exists())->toBeTrue()
        ->and(Vendor::query()->where('company_id', $otherCompany->id)->where('metadata->legacy_code', 'VEND-CSV-001')->exists())->toBeFalse();
});

test('toko dashboard shows transaction cockpit and scoped recent activity', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'A Pandan Teknik',
        'slug' => 'pandan-dashboard',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $otherCompany = Company::query()->create([
        'name' => 'ZZ Other Store',
        'slug' => 'other-dashboard',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Dashboard Filter',
        'sku' => 'DASH-FILTER',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 12000,
        'cost_price' => 7000,
        'stock_tracking' => true,
    ]);
    $otherProduct = Product::query()->create([
        'company_id' => $otherCompany->id,
        'name' => 'Other Filter',
        'sku' => 'OTHER-FILTER',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 999000,
        'cost_price' => 1,
        'stock_tracking' => true,
    ]);
    $vendor = Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Dashboard Vendor',
        'status' => Vendor::STATUS_ACTIVE,
    ]);

    $invoice = app(TokoPosSalesService::class)->createCounterSale($superadmin, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
        ]],
    ]);
    app(TokoPosPurchaseService::class)->createPurchase($superadmin, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_cost' => 6500,
        ]],
    ]);
    app(TokoPosSalesService::class)->createCounterSale($superadmin, [
        'company_id' => $otherCompany->id,
        'payment_status' => 'paid',
        'items' => [[
            'product_id' => $otherProduct->id,
            'quantity' => 1,
        ]],
    ]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'dashboard'])
        ->assertSee('Transaction Command Center')
        ->assertSee('Insight Charts')
        ->assertSee('Sales Trend')
        ->assertSee('Purchase Trend')
        ->assertSee('Top Products')
        ->assertSee('data-toko-dashboard-charts', false)
        ->assertSee('data-toko-sales-chart', false)
        ->assertSee('data-toko-purchase-chart', false)
        ->assertSee('data-toko-products-chart', false)
        ->assertDontSee('Top Customers')
        ->assertSee('Risk Watch')
        ->assertSee('Today Sales')
        ->assertSee('Quick Actions')
        ->assertSee(route('admin.toko.pos'), false)
        ->assertSee(route('admin.toko.purchases'), false)
        ->assertSee($invoice->number)
        ->assertSee('Dashboard Vendor')
        ->assertSee('Rp24.000')
        ->assertDontSee('999,000')
        ->assertDontSee(__('Legacy Import Preview'))
        ->assertDontSee('Create Sale');
});

test('toko superadmin can switch company scope while company admin stays scoped', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'A Company Scope',
        'slug' => 'a-company-scope',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $otherCompany = Company::query()->create([
        'name' => 'B Company Scope',
        'slug' => 'b-company-scope',
        'status' => Company::STATUS_ACTIVE,
    ]);

    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Scoped Alpha Product',
        'sku' => 'SCOPED-ALPHA',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 12000,
        'cost_price' => 7000,
        'stock_tracking' => true,
    ]);
    Product::query()->create([
        'company_id' => $otherCompany->id,
        'name' => 'Scoped Beta Product',
        'sku' => 'SCOPED-BETA',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 15000,
        'cost_price' => 9000,
        'stock_tracking' => true,
    ]);

    $superadmin = User::factory()->admin(true)->create();
    $companyAdmin = User::factory()->admin(false)->create(['company_id' => $company->id]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->assertSet('selectedCompanyId', (string) $company->id)
        ->assertSee('Scoped Alpha Product')
        ->assertDontSee('Scoped Beta Product')
        ->set('selectedCompanyId', (string) $otherCompany->id)
        ->assertSee('Scoped Beta Product')
        ->assertDontSee('Scoped Alpha Product');

    Livewire::actingAs($companyAdmin)
        ->test(TokoPosAddon::class, ['page' => 'products'])
        ->assertSet('selectedCompanyId', (string) $company->id)
        ->set('selectedCompanyId', (string) $otherCompany->id)
        ->assertSet('selectedCompanyId', (string) $company->id)
        ->assertSee('Scoped Alpha Product')
        ->assertDontSee('Scoped Beta Product');
});

test('toko transaction services reject cross-company mutations', function (): void {
    setTokoPosLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Guarded Company A',
        'slug' => 'guarded-company-a',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $otherCompany = Company::query()->create([
        'name' => 'Guarded Company B',
        'slug' => 'guarded-company-b',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $otherProduct = Product::query()->create([
        'company_id' => $otherCompany->id,
        'name' => 'Cross Company Product',
        'sku' => 'CROSS-COMPANY-PRODUCT',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 25000,
        'cost_price' => 15000,
        'stock_tracking' => true,
    ]);
    $otherVendor = Vendor::query()->create([
        'company_id' => $otherCompany->id,
        'name' => 'Cross Company Vendor',
        'status' => Vendor::STATUS_ACTIVE,
    ]);

    $superadmin = User::factory()->admin(true)->create();
    $companyAdmin = User::factory()->admin(false)->create(['company_id' => $company->id]);
    $sales = app(TokoPosSalesService::class);
    $purchases = app(TokoPosPurchaseService::class);

    expect(fn () => $sales->createCounterSale($companyAdmin, [
        'company_id' => $otherCompany->id,
        'payment_status' => 'paid',
        'items' => [[
            'product_id' => $otherProduct->id,
            'quantity' => 1,
        ]],
    ]))->toThrow(HttpException::class);

    $otherInvoice = $sales->createCounterSale($superadmin, [
        'company_id' => $otherCompany->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $otherProduct->id,
            'quantity' => 1,
        ]],
    ]);

    expect(fn () => $sales->recordInvoicePayment($companyAdmin, $otherInvoice, [
        'amount' => 25000,
    ]))->toThrow(HttpException::class)
        ->and(fn () => $sales->cancelCounterSale($companyAdmin, $otherInvoice, 'wrong company'))
        ->toThrow(HttpException::class)
        ->and(fn () => $purchases->createPurchase($companyAdmin, [
            'company_id' => $otherCompany->id,
            'vendor_id' => $otherVendor->id,
            'items' => [[
                'product_id' => $otherProduct->id,
                'quantity' => 1,
                'unit_cost' => 15000,
            ]],
        ]))->toThrow(HttpException::class);

    $otherBill = $purchases->createPurchase($superadmin, [
        'company_id' => $otherCompany->id,
        'vendor_id' => $otherVendor->id,
        'items' => [[
            'product_id' => $otherProduct->id,
            'quantity' => 1,
            'unit_cost' => 15000,
        ]],
    ]);

    expect(fn () => $purchases->recordVendorBillPayment($companyAdmin, $otherBill))
        ->toThrow(HttpException::class)
        ->and(fn () => $purchases->cancelPurchase($companyAdmin, $otherBill, 'wrong company'))
        ->toThrow(HttpException::class)
        ->and(Invoice::query()->where('company_id', $company->id)->count())->toBe(0)
        ->and(VendorBill::query()->where('company_id', $company->id)->count())->toBe(0);
});

test('toko transactions and reports can be scoped by branch inside one company', function (): void {
    setTokoPosLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Branch Scope',
        'slug' => 'pandan-branch-scope',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $branchA = CompanyBranch::query()->create([
        'company_id' => $company->id,
        'name' => 'Toko Pusat',
        'code' => 'PUSAT',
        'type' => 'store',
        'status' => CompanyBranch::STATUS_ACTIVE,
    ]);
    $branchB = CompanyBranch::query()->create([
        'company_id' => $company->id,
        'name' => 'Toko Selatan',
        'code' => 'SELATAN',
        'type' => 'store',
        'status' => CompanyBranch::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    $vendor = Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Branch Vendor',
        'status' => Vendor::STATUS_ACTIVE,
    ]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Branch Product',
        'sku' => 'BRANCH-PRODUCT',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 10000,
        'cost_price' => 6000,
        'reorder_point' => 1,
        'stock_tracking' => true,
    ]);

    $sales = app(TokoPosSalesService::class);
    $purchases = app(TokoPosPurchaseService::class);

    $saleA = $sales->createCounterSale($actor, [
        'company_id' => $company->id,
        'branch_id' => $branchA->id,
        'payment_status' => 'paid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 2,
        ]],
    ]);
    $saleB = $sales->createCounterSale($actor, [
        'company_id' => $company->id,
        'branch_id' => $branchB->id,
        'payment_status' => 'paid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
        ]],
    ]);
    $billA = $purchases->createPurchase($actor, [
        'company_id' => $company->id,
        'branch_id' => $branchA->id,
        'vendor_id' => $vendor->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_cost' => 6000,
        ]],
    ]);

    $report = app(TokoPosReportService::class);
    $allSummary = $report->summary($company->id);
    $branchASummary = $report->summary($company->id, null, null, $branchA->id);
    $branchBSummary = $report->summary($company->id, null, null, $branchB->id);

    expect($saleA->branch_id)->toBe($branchA->id)
        ->and($saleB->branch_id)->toBe($branchB->id)
        ->and($billA->branch_id)->toBe($branchA->id)
        ->and((float) $allSummary['sales']['total'])->toBe(30000.0)
        ->and((float) $branchASummary['sales']['total'])->toBe(20000.0)
        ->and((float) $branchBSummary['sales']['total'])->toBe(10000.0)
        ->and((float) $branchASummary['purchases']['total'])->toBe(30000.0)
        ->and((float) $branchBSummary['purchases']['total'])->toBe(0.0)
        ->and(StockMovement::query()->where('branch_id', $branchA->id)->count())->toBe(2)
        ->and(StockMovement::query()->where('branch_id', $branchB->id)->count())->toBe(1);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'dashboard'])
        ->assertSee('Toko Pusat')
        ->assertSee('Toko Selatan')
        ->set('selectedBranchId', (string) $branchA->id)
        ->assertSee('Rp20.000')
        ->assertDontSee($saleB->number);
});

test('toko dashboard exposes richer legacy operational overview', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-rich-dashboard',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    User::factory()->create(['company_id' => $company->id]);
    $vendor = Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Sumber Teknik',
        'status' => Vendor::STATUS_ACTIVE,
    ]);
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Perak las HARIS',
        'sku' => 'PL-HARIS',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 15000,
        'cost_price' => 9000,
        'reorder_point' => 5,
        'stock_tracking' => true,
    ]);
    $lowStockProduct = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Filter Kosong Kecil',
        'sku' => 'FKK',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 5000,
        'cost_price' => 3000,
        'reorder_point' => 10,
        'stock_tracking' => true,
    ]);
    $placeholderProduct = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Legacy Placeholder Should Stay Hidden',
        'sku' => 'LEGACY-TOKO-OLD404',
        'status' => Product::STATUS_INACTIVE,
        'unit' => 'pcs',
        'selling_price' => 0,
        'cost_price' => 0,
        'reorder_point' => 0,
        'stock_tracking' => true,
        'metadata' => [
            'source' => 'legacy_toko_placeholder',
            'legacy_toko' => ['kode' => 'OLD404', 'missing_from_master_dump' => true],
        ],
    ]);
    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 20,
        'unit_cost' => 9000,
        'occurred_at' => now(),
        'metadata' => ['source' => 'opening_test'],
    ]);
    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $lowStockProduct->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 2,
        'unit_cost' => 3000,
        'occurred_at' => now(),
        'metadata' => ['source' => 'opening_test'],
    ]);
    StockMovement::query()->create([
        'company_id' => $company->id,
        'product_id' => $placeholderProduct->id,
        'user_id' => $actor->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 999,
        'unit_cost' => 0,
        'occurred_at' => now(),
        'metadata' => ['source' => 'legacy_toko_stock_in'],
    ]);
    app(TokoPosSalesService::class)->createCounterSale($actor, [
        'company_id' => $company->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 3,
        ]],
    ]);
    app(TokoPosPurchaseService::class)->createPurchase($actor, [
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'payment_status' => 'unpaid',
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 4,
            'unit_cost' => 9000,
        ]],
    ]);
    $expenseEntry = JournalEntry::query()->create([
        'company_id' => $company->id,
        'created_by' => $actor->id,
        'number' => 'TJ-OPER-001',
        'entry_date' => now()->toDateString(),
        'status' => JournalEntry::STATUS_POSTED,
        'source_type' => 'toko_pos_operational_expense',
        'description' => 'Biaya listrik toko',
        'metadata' => ['expense_type' => 'Listrik'],
    ]);
    $expenseAccount = AccountingAccount::query()->create([
        'company_id' => $company->id,
        'code' => '6100',
        'name' => 'Operational Expense',
        'type' => AccountingAccount::TYPE_EXPENSE,
        'normal_balance' => AccountingAccount::BALANCE_DEBIT,
        'is_active' => true,
    ]);
    JournalEntryLine::query()->create([
        'journal_entry_id' => $expenseEntry->id,
        'accounting_account_id' => $expenseAccount->id,
        'debit' => 5000,
        'credit' => 0,
        'memo' => 'Biaya listrik toko',
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'dashboard'])
        ->assertSee('Karyawan HRIS')
        ->assertSee('Supplier')
        ->assertSee('Barang')
        ->assertSee('SKU katalog toko aktif.')
        ->assertSee('2')
        ->assertSee('Barang Stok Minimum')
        ->assertSee('Stok Tersedia')
        ->assertSee('Barang Keluar')
        ->assertSee('Barang Masuk')
        ->assertDontSee('Jumlah Produk')
        ->assertSee('Total Estimasi Modal')
        ->assertSee('Total Estimasi Pemasukan')
        ->assertSee('Total Estimasi Laba')
        ->assertSee('Total Omzet')
        ->assertSee('Pendapatan Tahun Ini')
        ->assertSee('Laba Bersih')
        ->assertSee('Margin Kotor')
        ->assertSee('Barang dengan Stok paling banyak')
        ->assertDontSee('Legacy Placeholder Should Stay Hidden')
        ->assertSee('Barang Keluar Terbanyak')
        ->assertSee('Hutang dan Piutang')
        ->assertSee('Ringkasan')
        ->assertSee('Monthly Net Trend')
        ->assertSee('Income')
        ->assertSee('Cost')
        ->assertSee('Net')
        ->assertSee('Pendapatan Retail Vs Nota')
        ->assertSee('Pengeluaran')
        ->assertSee('Perak las HARIS')
        ->assertSee('Filter Kosong Kecil')
        ->assertSee('Rp40.000')
        ->assertDontSee('40,000')
        ->assertSee('data-toko-revenue-mix-chart', false)
        ->assertSee('data-toko-expense-chart', false);
});

test('toko dashboard uses legacy cutoff counters for imported toko data', function (): void {
    setTokoPosLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-legacy-dashboard-counters',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $actor = User::factory()->admin(true)->create(['company_id' => $company->id]);
    User::factory()->count(4)->create(['company_id' => $company->id]);
    Setting::updateOrCreate(
        ['key' => 'toko_pos.legacy_system_mapping'],
        [
            'value' => json_encode([
                'company_id' => $company->id,
                'users' => ['legacy_count' => 3],
            ]),
            'group' => 'toko_pos',
            'type' => 'json',
        ]
    );

    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Perak las HARIS',
        'sku' => 'SKU-HARIS',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 15000,
        'cost_price' => 9000,
        'reorder_point' => 5,
        'stock_tracking' => true,
        'metadata' => [
            'source' => 'legacy_toko_import',
            'legacy_toko' => [
                'sisa' => 965,
                'terjual' => 1565,
                'terbeli' => 2530,
            ],
        ],
    ]);
    Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Modern Test Product',
        'sku' => 'MODERN-TEST',
        'status' => Product::STATUS_ACTIVE,
        'unit' => 'pcs',
        'selling_price' => 999999,
        'cost_price' => 1,
        'reorder_point' => 0,
        'stock_tracking' => true,
    ]);
    Invoice::query()->create([
        'company_id' => $company->id,
        'number' => '100999',
        'status' => Invoice::STATUS_PAID,
        'issued_at' => now()->toDateString(),
        'due_date' => now()->toDateString(),
        'subtotal' => 123456,
        'tax_total' => 0,
        'grand_total' => 123456,
        'metadata' => ['source' => 'legacy_toko_sale'],
    ]);

    Livewire::actingAs($actor)
        ->test(TokoPosAddon::class, ['page' => 'dashboard'])
        ->assertSee('Karyawan HRIS')
        ->assertSee('Data orang dari HRIS; biaya gaji masuk finance Toko.')
        ->assertSee('3')
        ->assertSee('Barang')
        ->assertDontSee('Jumlah Produk')
        ->assertSee('1')
        ->assertSee('Stok Tersedia')
        ->assertSee('965')
        ->assertSee('Barang Keluar')
        ->assertSee('1.565')
        ->assertSee('Barang Masuk')
        ->assertSee('2.530')
        ->assertSee('Pendapatan Non Retail')
        ->assertSee('Rp123.456')
        ->assertDontSee('123,456')
        ->assertDontSee('Modern Test Product');
});

test('toko transaction pages do not show dashboard kpi cards', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-pos-focused',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->assertSee('Terminal POS')
        ->assertSee('Keranjang Transaksi')
        ->assertDontSee('Today Sales')
        ->assertDontSee('Insight Charts');
});

test('toko pos page renders when operational expense journals exist', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-pos-expense-safe',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);

    JournalEntry::query()->create([
        'company_id' => $company->id,
        'created_by' => $superadmin->id,
        'number' => 'TJ-OPER-POS-001',
        'entry_date' => now()->toDateString(),
        'status' => JournalEntry::STATUS_POSTED,
        'source_type' => 'toko_pos_operational_expense',
        'description' => 'Biaya operasional POS',
        'metadata' => ['expense_type' => 'Operasional Toko'],
    ]);

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'pos'])
        ->assertSee('Terminal POS')
        ->assertSee('Keranjang Transaksi');
});

test('toko dashboard renders insight charts before command center', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $company = Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-chart-order',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create(['company_id' => $company->id]);

    $html = Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'dashboard'])
        ->html();

    expect(strpos($html, 'Insight Charts'))->toBeLessThan(strpos($html, 'Transaction Command Center'));
});

test('toko pos add-on exposes prd submenu routes under premium entitlement', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $superadmin = User::factory()->admin(true)->create();

    foreach ([
        'admin.toko' => __('Toko Dashboard'),
        'admin.toko.pos' => __('Terminal POS'),
        'admin.toko.products' => __('Products'),
        'admin.toko.customers' => __('Customers'),
        'admin.toko.vendors' => __('Vendors'),
        'admin.toko.purchases' => __('Purchase Receiving'),
        'admin.toko.inventory' => __('Inventory'),
        'admin.toko.returns' => __('Returns'),
        'admin.toko.quotations' => __('Quotation Desk'),
        'admin.toko.delivery-letters' => __('Delivery Letters'),
        'admin.toko.cash' => __('Cash'),
        'admin.toko.reports' => __('Toko Reports'),
        'admin.toko.migration' => __('CSV Template Import'),
    ] as $routeName => $expectedHeading) {
        $this->actingAs($superadmin)
            ->get(route($routeName))
            ->assertOk()
            ->assertSee($expectedHeading)
            ->assertSee(route($routeName), false);
    }
});

test('toko migration workspace no longer exposes selectable sql dump source', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    $superadmin = User::factory()->admin(true)->create();

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class, ['page' => 'migration'])
        ->assertSee(__('CSV Template Import'))
        ->assertDontSee('toko.sql')
        ->assertDontSee('panh7986_toko.sql')
        ->assertDontSee('toko-dump-source');
});

test('toko pos add-on can run master import from selected dump', function () {
    setTokoPosLicenseFeatures(['toko_pos']);

    Company::query()->create([
        'name' => 'Pandan Teknik',
        'slug' => 'pandan-teknik',
        'status' => Company::STATUS_ACTIVE,
    ]);
    $superadmin = User::factory()->admin(true)->create();

    Livewire::actingAs($superadmin)
        ->test(TokoPosAddon::class)
        ->call('importMasterData');

    $run = ImportExportRun::query()
        ->where('resource', 'toko_pos_master')
        ->where('operation', 'import')
        ->latest('id')
        ->firstOrFail();

    expect($run->status)->toBe('completed')
        ->and($run->processed_rows)->toBeGreaterThan(0)
        ->and(Product::query()->count())->toBeGreaterThan(0);
});
