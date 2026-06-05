<?php

use App\Livewire\Admin\CommercialWorkspace;
use App\Models\AccountingAccount;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\SalesFollowUp;
use App\Models\SalesOpportunity;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Support\AccountingWorkspaceService;
use App\Support\CommercialWorkspaceService;
use App\Support\MultiCompanyService;
use Livewire\Livewire;

test('superadmin can manage commercial products stock quotations and invoices', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Commerce Platform');

    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'PT Buyer Utama',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $project = Project::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'name' => 'Commerce Implementation',
        'status' => Project::STATUS_ACTIVE,
    ]);

    $this->actingAs($superadmin);

    Livewire::test(CommercialWorkspace::class)
        ->set('productForm.companyId', (string) $company->id)
        ->set('productForm.name', 'Monthly Support Package')
        ->set('productForm.sku', 'support-001')
        ->set('productForm.unit', 'month')
        ->set('productForm.sellingPrice', '1500000')
        ->set('productForm.costPrice', '900000')
        ->set('productForm.reorderPoint', '10')
        ->call('createProduct')
        ->assertHasNoErrors();

    $product = Product::query()->where('sku', 'SUPPORT-001')->firstOrFail();

    Livewire::test(CommercialWorkspace::class)
        ->set('stockMovementForm.productId', (string) $product->id)
        ->set('stockMovementForm.type', StockMovement::TYPE_IN)
        ->set('stockMovementForm.quantity', '5')
        ->set('stockMovementForm.unitCost', '900000')
        ->set('stockMovementForm.notes', 'Opening balance')
        ->call('recordStockMovement')
        ->assertHasNoErrors();

    Livewire::test(CommercialWorkspace::class)
        ->set('documentForm.companyId', (string) $company->id)
        ->set('documentForm.clientId', (string) $client->id)
        ->set('documentForm.projectId', (string) $project->id)
        ->set('documentForm.productId', (string) $product->id)
        ->set('documentForm.description', 'Monthly support')
        ->set('documentForm.quantity', '2')
        ->set('documentForm.unitPrice', '1500000')
        ->set('documentForm.taxRate', '11')
        ->call('createQuotation')
        ->assertHasNoErrors()
        ->set('documentForm.companyId', (string) $company->id)
        ->set('documentForm.clientId', (string) $client->id)
        ->set('documentForm.projectId', (string) $project->id)
        ->set('documentForm.productId', (string) $product->id)
        ->set('documentForm.description', 'Monthly support')
        ->set('documentForm.quantity', '2')
        ->set('documentForm.unitPrice', '1500000')
        ->set('documentForm.taxRate', '11')
        ->call('createInvoice')
        ->assertHasNoErrors();

    $movement = StockMovement::query()->where('product_id', $product->id)->firstOrFail();
    $stockJournal = JournalEntry::query()
        ->where('source_type', StockMovement::class)
        ->where('source_id', $movement->id)
        ->firstOrFail();

    expect($product->company_id)->toBe($company->id)
        ->and($product->cost_price)->toBe('900000.00')
        ->and($product->reorder_point)->toBe('10.000')
        ->and(StockMovement::query()->where('product_id', $product->id)->count())->toBe(1)
        ->and($product->fresh()->load('stockMovements')->isLowStock())->toBeTrue()
        ->and($movement->metadata['accounting_journal_entry_id'])->toBe($stockJournal->id)
        ->and((float) JournalEntryLine::query()->where('journal_entry_id', $stockJournal->id)->sum('debit'))->toBe(4500000.0)
        ->and((float) JournalEntryLine::query()->where('journal_entry_id', $stockJournal->id)->sum('credit'))->toBe(4500000.0)
        ->and(Quotation::query()->where('company_id', $company->id)->firstOrFail()->grand_total)->toBe('3330000.00')
        ->and(Quotation::query()->where('company_id', $company->id)->firstOrFail()->project_id)->toBe($project->id)
        ->and(Invoice::query()->where('company_id', $company->id)->firstOrFail()->grand_total)->toBe('3330000.00')
        ->and(Invoice::query()->where('company_id', $company->id)->firstOrFail()->project_id)->toBe($project->id);
});

test('accepted quotations convert to invoices once and keep project context', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Quotation Convert');
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'PT Buyer Convert',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $project = Project::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'name' => 'Conversion Project',
        'status' => Project::STATUS_ACTIVE,
    ]);

    $this->actingAs($superadmin);

    Livewire::test(CommercialWorkspace::class)
        ->set('documentForm.companyId', (string) $company->id)
        ->set('documentForm.clientId', (string) $client->id)
        ->set('documentForm.projectId', (string) $project->id)
        ->set('documentForm.description', 'Implementation package')
        ->set('documentForm.quantity', '1')
        ->set('documentForm.unitPrice', '2500000')
        ->set('documentForm.taxRate', '11')
        ->call('createQuotation')
        ->assertHasNoErrors();

    $quotation = Quotation::query()->with('items')->firstOrFail();

    Livewire::test(CommercialWorkspace::class)
        ->call('convertQuotationToInvoice', $quotation->id)
        ->assertHasNoErrors()
        ->assertSet('activeTab', 'invoices')
        ->call('convertQuotationToInvoice', $quotation->id)
        ->assertHasNoErrors();

    $quotation->refresh();
    $invoice = Invoice::query()->where('quotation_id', $quotation->id)->firstOrFail();

    expect($quotation->status)->toBe(Quotation::STATUS_ACCEPTED)
        ->and($quotation->project_id)->toBe($project->id)
        ->and($quotation->metadata['converted_invoice_id'])->toBe($invoice->id)
        ->and($invoice->project_id)->toBe($project->id)
        ->and($invoice->client_id)->toBe($client->id)
        ->and($invoice->grand_total)->toBe('2775000.00')
        ->and($invoice->metadata['source'])->toBe('quotation_conversion')
        ->and(Invoice::query()->where('quotation_id', $quotation->id)->count())->toBe(1);
});

test('paid invoices are posted to accounting once', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Invoice Posting');

    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'PT Buyer Posting',
        'status' => Client::STATUS_ACTIVE,
    ]);

    $invoice = Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'number' => 'INV-POST-001',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => now()->toDateString(),
        'subtotal' => 1000000,
        'tax_total' => 110000,
        'grand_total' => 1110000,
    ]);

    $this->actingAs($superadmin);

    Livewire::test(CommercialWorkspace::class)
        ->call('markInvoicePaid', $invoice->id)
        ->assertHasNoErrors()
        ->call('markInvoicePaid', $invoice->id)
        ->assertHasNoErrors();

    $invoice->refresh();
    $journal = JournalEntry::query()
        ->where('source_type', Invoice::class)
        ->where('source_id', $invoice->id)
        ->firstOrFail();

    expect($invoice->status)->toBe(Invoice::STATUS_PAID)
        ->and($invoice->paid_at)->not->toBeNull()
        ->and($invoice->metadata['accounting_journal_entry_id'])->toBe($journal->id)
        ->and(JournalEntry::query()->where('source_type', Invoice::class)->where('source_id', $invoice->id)->count())->toBe(1)
        ->and(AccountingAccount::query()->where('company_id', $company->id)->whereIn('code', ['1100', '2100', '4100'])->count())->toBe(3)
        ->and((float) JournalEntryLine::query()->where('journal_entry_id', $journal->id)->sum('debit'))->toBe(1110000.0)
        ->and((float) JournalEntryLine::query()->where('journal_entry_id', $journal->id)->sum('credit'))->toBe(1110000.0);
});

test('stock out posts cost of goods sold once', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT COGS Posting');
    $product = Product::query()->create([
        'company_id' => $company->id,
        'name' => 'Hardware Kit',
        'unit' => 'pcs',
        'selling_price' => 500000,
        'cost_price' => 300000,
        'stock_tracking' => true,
        'reorder_point' => 2,
    ]);

    $this->actingAs($superadmin);

    Livewire::test(CommercialWorkspace::class)
        ->set('stockMovementForm.productId', (string) $product->id)
        ->set('stockMovementForm.type', StockMovement::TYPE_OUT)
        ->set('stockMovementForm.quantity', '2')
        ->set('stockMovementForm.unitCost', '300000')
        ->set('stockMovementForm.notes', 'Delivered to client')
        ->call('recordStockMovement')
        ->assertHasNoErrors();

    $movement = StockMovement::query()->where('product_id', $product->id)->firstOrFail();
    app(AccountingWorkspaceService::class)->postStockMovement($superadmin, $movement->fresh(['product']));

    $journal = JournalEntry::query()
        ->with('lines.account')
        ->where('source_type', StockMovement::class)
        ->where('source_id', $movement->id)
        ->firstOrFail();

    expect(JournalEntry::query()->where('source_type', StockMovement::class)->where('source_id', $movement->id)->count())->toBe(1)
        ->and((float) $journal->lines->sum('debit'))->toBe(600000.0)
        ->and((float) $journal->lines->sum('credit'))->toBe(600000.0)
        ->and($journal->lines->firstWhere('account.code', '5100'))->not->toBeNull()
        ->and($journal->lines->firstWhere('account.code', '1200'))->not->toBeNull()
        ->and($movement->fresh()->metadata['accounting_journal_entry_id'])->toBe($journal->id);
});

test('vendor bills post inventory AP and payment journals', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Vendor AP');

    $this->actingAs($superadmin);

    Livewire::test(CommercialWorkspace::class)
        ->set('vendorForm.companyId', (string) $company->id)
        ->set('vendorForm.name', 'PT Supplier Utama')
        ->set('vendorForm.contactName', 'Budi Supplier')
        ->set('vendorForm.email', 'supplier@example.com')
        ->call('createVendor')
        ->assertHasNoErrors()
        ->set('productForm.companyId', (string) $company->id)
        ->set('productForm.name', 'Inventory Pack')
        ->set('productForm.unit', 'pcs')
        ->set('productForm.sellingPrice', '250000')
        ->set('productForm.costPrice', '100000')
        ->set('productForm.reorderPoint', '5')
        ->call('createProduct')
        ->assertHasNoErrors();

    $vendor = Vendor::query()->firstOrFail();
    $product = Product::query()->firstOrFail();

    Livewire::test(CommercialWorkspace::class)
        ->set('activeTab', 'purchases')
        ->set('vendorBillForm.vendorId', (string) $vendor->id)
        ->set('vendorBillForm.productId', (string) $product->id)
        ->set('vendorBillForm.description', 'Opening stock purchase')
        ->set('vendorBillForm.quantity', '3')
        ->set('vendorBillForm.unitCost', '100000')
        ->set('vendorBillForm.taxRate', '11')
        ->call('createVendorBill')
        ->assertHasNoErrors()
        ->assertSee('PT Supplier Utama')
        ->call('markVendorBillPaid', VendorBill::query()->firstOrFail()->id)
        ->assertHasNoErrors();

    $bill = VendorBill::query()->with('items')->firstOrFail();
    $apJournal = JournalEntry::query()
        ->with('lines.account')
        ->where('source_type', VendorBill::class)
        ->where('source_id', $bill->id)
        ->whereJsonContains('metadata->source', 'vendor_bill')
        ->firstOrFail();
    $paymentJournal = JournalEntry::query()
        ->with('lines.account')
        ->where('source_type', VendorBill::class)
        ->where('source_id', $bill->id)
        ->whereJsonContains('metadata->source', 'vendor_bill_payment')
        ->firstOrFail();

    expect($bill->status)->toBe(VendorBill::STATUS_PAID)
        ->and($bill->grand_total)->toBe('333000.00')
        ->and($bill->accounting_journal_entry_id)->toBe($apJournal->id)
        ->and($bill->payment_journal_entry_id)->toBe($paymentJournal->id)
        ->and(StockMovement::query()->where('reference_type', VendorBill::class)->where('reference_number', $bill->number)->count())->toBe(1)
        ->and($product->fresh()->stockBalance())->toBe(3.0)
        ->and($apJournal->lines->firstWhere('account.code', '1200'))->not->toBeNull()
        ->and($apJournal->lines->firstWhere('account.code', '2300'))->not->toBeNull()
        ->and($paymentJournal->lines->firstWhere('account.code', '2300'))->not->toBeNull()
        ->and($paymentJournal->lines->firstWhere('account.code', '1100'))->not->toBeNull();
});

test('tenant scoped commercial admin cannot create product for another company', function () {
    $admin = User::factory()->admin()->create();
    $companyA = app(MultiCompanyService::class)->createCompany('PT Commerce A', $admin);
    $companyB = app(MultiCompanyService::class)->createCompany('PT Commerce B');

    $role = Role::query()->create([
        'name' => 'Commercial Manager',
        'slug' => 'commercial_manager',
        'permissions' => ['admin.commercial.view', 'admin.commercial.manage'],
    ]);
    $admin->roles()->sync([$role->id]);

    $this->actingAs($admin->fresh());

    Livewire::test(CommercialWorkspace::class)
        ->set('productForm.companyId', (string) $companyB->id)
        ->set('productForm.name', 'Cross tenant product')
        ->set('productForm.unit', 'pcs')
        ->set('productForm.sellingPrice', '10000')
        ->call('createProduct')
        ->assertForbidden();

    expect(Product::query()->where('company_id', $companyB->id)->exists())->toBeFalse()
        ->and($admin->fresh()->company_id)->toBe($companyA->id);
});

test('commercial pipeline tracks opportunities follow ups and stage summary', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Pipeline Platform');

    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'PT Prospect Utama',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $project = Project::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'name' => 'Pipeline Implementation',
        'status' => Project::STATUS_ACTIVE,
    ]);

    $this->actingAs($superadmin);

    Livewire::test(CommercialWorkspace::class)
        ->set('opportunityForm.companyId', (string) $company->id)
        ->set('opportunityForm.clientId', (string) $client->id)
        ->set('opportunityForm.projectId', (string) $project->id)
        ->set('opportunityForm.title', 'Implementation deal')
        ->set('opportunityForm.stage', SalesOpportunity::STAGE_QUALIFIED)
        ->set('opportunityForm.expectedValue', '12500000')
        ->set('opportunityForm.expectedCloseAt', now()->addDays(14)->toDateString())
        ->set('opportunityForm.nextFollowUpAt', now()->subDay()->toDateString())
        ->set('opportunityForm.source', 'Referral')
        ->set('opportunityForm.notes', 'Send proposal')
        ->call('createOpportunity')
        ->assertHasNoErrors();

    $opportunity = SalesOpportunity::query()->firstOrFail();

    Livewire::test(CommercialWorkspace::class)
        ->call('moveOpportunityStage', $opportunity->id, SalesOpportunity::STAGE_WON)
        ->assertHasNoErrors();

    $summary = app(CommercialWorkspaceService::class)->salesSummaryForCompanies([$company->id]);

    $lostOpportunity = app(CommercialWorkspaceService::class)->createOpportunity($superadmin, [
        'company_id' => $company->id,
        'title' => 'Lost comparison deal',
        'stage' => SalesOpportunity::STAGE_LOST,
        'expected_value' => 2500000,
    ]);

    expect($lostOpportunity->stage)->toBe(SalesOpportunity::STAGE_LOST)
        ->and($opportunity->refresh()->stage)->toBe(SalesOpportunity::STAGE_WON)
        ->and($opportunity->probability)->toBe(100)
        ->and($opportunity->project_id)->toBe($project->id)
        ->and(SalesFollowUp::query()->where('sales_opportunity_id', $opportunity->id)->count())->toBe(1)
        ->and($summary['won_value'])->toBe(12500000.0)
        ->and($summary['open_value'])->toBe(0.0)
        ->and($summary['overdue_follow_ups'])->toBe(0)
        ->and(app(CommercialWorkspaceService::class)->salesSummaryForCompanies([$company->id])['win_rate'])->toBe(50.0);
});

test('commercial collection summary tracks overdue and due soon receivables by company', function () {
    $this->travelTo(now()->setDate(2026, 5, 19)->startOfDay());

    $superadmin = User::factory()->admin(true)->create();
    $companyA = app(MultiCompanyService::class)->createCompany('PT Collection A');
    $companyB = app(MultiCompanyService::class)->createCompany('PT Collection B');
    $clientA = Client::query()->create([
        'company_id' => $companyA->id,
        'name' => 'Collection Buyer A',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $clientB = Client::query()->create([
        'company_id' => $companyB->id,
        'name' => 'Collection Buyer B',
        'status' => Client::STATUS_ACTIVE,
    ]);

    Invoice::query()->create([
        'company_id' => $companyA->id,
        'client_id' => $clientA->id,
        'number' => 'INV-COL-A-1',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => now()->subDays(20)->toDateString(),
        'due_at' => now()->subDay()->toDateString(),
        'subtotal' => 1000000,
        'tax_total' => 0,
        'grand_total' => 1000000,
    ]);
    Invoice::query()->create([
        'company_id' => $companyA->id,
        'client_id' => $clientA->id,
        'number' => 'INV-COL-A-2',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->addDays(3)->toDateString(),
        'subtotal' => 2000000,
        'tax_total' => 0,
        'grand_total' => 2000000,
    ]);
    Invoice::query()->create([
        'company_id' => $companyA->id,
        'client_id' => $clientA->id,
        'number' => 'INV-COL-A-3',
        'status' => Invoice::STATUS_PAID,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->toDateString(),
        'subtotal' => 3000000,
        'tax_total' => 0,
        'grand_total' => 3000000,
    ]);
    Invoice::query()->create([
        'company_id' => $companyB->id,
        'client_id' => $clientB->id,
        'number' => 'INV-COL-B-1',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => now()->subDays(20)->toDateString(),
        'due_at' => now()->subDay()->toDateString(),
        'subtotal' => 9000000,
        'tax_total' => 0,
        'grand_total' => 9000000,
    ]);

    $summary = app(CommercialWorkspaceService::class)->collectionSummaryForCompanies([$companyA->id]);

    expect($summary['open_total'])->toBe(3000000.0)
        ->and($summary['overdue_total'])->toBe(1000000.0)
        ->and($summary['overdue_count'])->toBe(1)
        ->and($summary['due_soon_total'])->toBe(2000000.0)
        ->and($summary['due_soon_count'])->toBe(1)
        ->and($summary['paid_total'])->toBe(3000000.0);

    $this->actingAs($superadmin);

    Livewire::test(CommercialWorkspace::class)
        ->assertViewHas('collectionSummary', fn (array $summary): bool => $summary['overdue_total'] === 10000000.0)
        ->set('documentForm.companyId', (string) $companyA->id)
        ->assertViewHas('collectionSummary', fn (array $summary): bool => $summary['due_soon_total'] === 2000000.0);
});

test('sales opportunities can create quotations once', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Opportunity Quote');
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'PT Quote Buyer',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $project = Project::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'name' => 'Quote Project',
        'status' => Project::STATUS_ACTIVE,
    ]);

    $this->actingAs($superadmin);

    Livewire::test(CommercialWorkspace::class)
        ->set('opportunityForm.companyId', (string) $company->id)
        ->set('opportunityForm.clientId', (string) $client->id)
        ->set('opportunityForm.projectId', (string) $project->id)
        ->set('opportunityForm.title', 'Quotation package')
        ->set('opportunityForm.stage', SalesOpportunity::STAGE_QUALIFIED)
        ->set('opportunityForm.expectedValue', '7500000')
        ->call('createOpportunity')
        ->assertHasNoErrors();

    $opportunity = SalesOpportunity::query()->firstOrFail();

    Livewire::test(CommercialWorkspace::class)
        ->set('activeTab', 'pipeline')
        ->assertSee('Quotation package')
        ->call('createQuotationFromOpportunity', $opportunity->id)
        ->assertHasNoErrors()
        ->assertSet('activeTab', 'quotations')
        ->call('createQuotationFromOpportunity', $opportunity->id)
        ->assertHasNoErrors();

    $quotation = Quotation::query()->where('sales_opportunity_id', $opportunity->id)->firstOrFail();

    expect($quotation->company_id)->toBe($company->id)
        ->and($quotation->client_id)->toBe($client->id)
        ->and($quotation->project_id)->toBe($project->id)
        ->and($quotation->grand_total)->toBe('7500000.00')
        ->and($quotation->metadata['source'])->toBe('sales_opportunity')
        ->and($opportunity->fresh()->stage)->toBe(SalesOpportunity::STAGE_PROPOSAL)
        ->and(Quotation::query()->where('sales_opportunity_id', $opportunity->id)->count())->toBe(1);
});

test('commercial follow ups can be completed from the workspace', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Follow Up Platform');

    $this->actingAs($superadmin);

    Livewire::test(CommercialWorkspace::class)
        ->set('opportunityForm.companyId', (string) $company->id)
        ->set('opportunityForm.title', 'Follow-up deal')
        ->set('opportunityForm.stage', SalesOpportunity::STAGE_PROPOSAL)
        ->set('opportunityForm.expectedValue', '5000000')
        ->set('opportunityForm.nextFollowUpAt', now()->subDay()->toDateString())
        ->set('opportunityForm.notes', 'Call prospect')
        ->call('createOpportunity')
        ->assertHasNoErrors();

    $opportunity = SalesOpportunity::query()->firstOrFail();
    $followUp = SalesFollowUp::query()->where('sales_opportunity_id', $opportunity->id)->firstOrFail();

    expect(app(CommercialWorkspaceService::class)->salesSummaryForCompanies([$company->id])['overdue_follow_ups'])->toBe(1);

    Livewire::test(CommercialWorkspace::class)
        ->set('activeTab', 'pipeline')
        ->assertSee('Call prospect')
        ->call('completeFollowUp', $followUp->id)
        ->assertHasNoErrors();

    expect($followUp->fresh()->status)->toBe(SalesFollowUp::STATUS_DONE)
        ->and($followUp->fresh()->completed_at)->not->toBeNull()
        ->and($opportunity->fresh()->next_follow_up_at)->toBeNull()
        ->and(app(CommercialWorkspaceService::class)->salesSummaryForCompanies([$company->id])['overdue_follow_ups'])->toBe(0);
});

test('tenant scoped commercial admin cannot create opportunity for another company client', function () {
    $admin = User::factory()->admin()->create();
    $companyA = app(MultiCompanyService::class)->createCompany('PT Pipeline A', $admin);
    $companyB = app(MultiCompanyService::class)->createCompany('PT Pipeline B');

    $clientB = Client::query()->create([
        'company_id' => $companyB->id,
        'name' => 'PT Other Prospect',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $projectB = Project::query()->create([
        'company_id' => $companyB->id,
        'client_id' => $clientB->id,
        'name' => 'Other tenant project',
        'status' => Project::STATUS_ACTIVE,
    ]);

    $role = Role::query()->create([
        'name' => 'Commercial Pipeline Manager',
        'slug' => 'commercial_pipeline_manager',
        'permissions' => ['admin.commercial.view', 'admin.commercial.manage'],
    ]);
    $admin->roles()->sync([$role->id]);

    $this->actingAs($admin->fresh());

    Livewire::test(CommercialWorkspace::class)
        ->set('opportunityForm.companyId', (string) $companyA->id)
        ->set('opportunityForm.projectId', (string) $projectB->id)
        ->set('opportunityForm.title', 'Cross tenant opportunity')
        ->set('opportunityForm.expectedValue', '1000000')
        ->call('createOpportunity')
        ->assertHasErrors('opportunityForm.projectId');

    expect(SalesOpportunity::query()->where('client_id', $clientB->id)->exists())->toBeFalse();
});

test('commercial forms scope selectable clients projects and products to selected company context', function () {
    $superadmin = User::factory()->admin(true)->create();
    $companyA = app(MultiCompanyService::class)->createCompany('PT Commercial Scope A');
    $companyB = app(MultiCompanyService::class)->createCompany('PT Commercial Scope B');
    $clientA = Client::query()->create([
        'company_id' => $companyA->id,
        'name' => 'Buyer Scope A',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $clientB = Client::query()->create([
        'company_id' => $companyB->id,
        'name' => 'Buyer Scope B',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $projectA = Project::query()->create([
        'company_id' => $companyA->id,
        'client_id' => $clientA->id,
        'name' => 'Commercial Project A',
        'status' => Project::STATUS_ACTIVE,
    ]);
    $projectB = Project::query()->create([
        'company_id' => $companyB->id,
        'client_id' => $clientB->id,
        'name' => 'Commercial Project B',
        'status' => Project::STATUS_ACTIVE,
    ]);
    $productA = Product::query()->create([
        'company_id' => $companyA->id,
        'name' => 'Scoped Product A',
        'unit' => 'pcs',
        'selling_price' => 100000,
        'cost_price' => 50000,
    ]);
    $productB = Product::query()->create([
        'company_id' => $companyB->id,
        'name' => 'Scoped Product B',
        'unit' => 'pcs',
        'selling_price' => 100000,
        'cost_price' => 50000,
    ]);
    $vendorA = Vendor::query()->create([
        'company_id' => $companyA->id,
        'name' => 'Vendor Scope A',
        'status' => Vendor::STATUS_ACTIVE,
    ]);

    $this->actingAs($superadmin);

    Livewire::test(CommercialWorkspace::class)
        ->set('activeTab', 'pipeline')
        ->set('opportunityForm.companyId', (string) $companyA->id)
        ->assertSee('Buyer Scope A')
        ->assertSee('Commercial Project A')
        ->assertDontSee('Buyer Scope B')
        ->assertDontSee('Commercial Project B')
        ->set('activeTab', 'quotations')
        ->set('documentForm.companyId', (string) $companyA->id)
        ->assertSee('Buyer Scope A')
        ->assertSee('Commercial Project A')
        ->assertSee('Scoped Product A')
        ->assertDontSee('Buyer Scope B')
        ->assertDontSee('Commercial Project B')
        ->assertDontSee('Scoped Product B')
        ->set('activeTab', 'purchases')
        ->set('vendorBillForm.vendorId', (string) $vendorA->id)
        ->assertSee('Scoped Product A')
        ->assertDontSee('Scoped Product B');

    expect($projectB->exists)->toBeTrue()
        ->and($productA->exists)->toBeTrue()
        ->and($productB->exists)->toBeTrue();
});

test('commercial workspace keeps selected tab from query string on reload', function () {
    $superadmin = User::factory()->admin(true)->create();

    $this->actingAs($superadmin);

    Livewire::withQueryParams(['activeTab' => 'purchases'])
        ->test(CommercialWorkspace::class)
        ->assertSet('activeTab', 'purchases');

    Livewire::withQueryParams(['activeTab' => 'bad-tab'])
        ->test(CommercialWorkspace::class)
        ->assertSet('activeTab', 'products');
});

test('commercial documents can be downloaded as scoped pdf files', function () {
    $superadmin = User::factory()->admin(true)->create();
    $companyA = app(MultiCompanyService::class)->createCompany('PT Commercial PDF A');
    $companyB = app(MultiCompanyService::class)->createCompany('PT Commercial PDF B');
    $client = Client::query()->create([
        'company_id' => $companyA->id,
        'name' => 'PDF Buyer',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $vendor = Vendor::query()->create([
        'company_id' => $companyA->id,
        'name' => 'PDF Vendor',
        'status' => Vendor::STATUS_ACTIVE,
    ]);
    $product = Product::query()->create([
        'company_id' => $companyA->id,
        'name' => 'PDF Product',
        'unit' => 'pcs',
        'selling_price' => 250000,
        'cost_price' => 150000,
    ]);

    $quotation = app(CommercialWorkspaceService::class)->createQuotation($superadmin, [
        'company_id' => $companyA->id,
        'client_id' => $client->id,
        'issued_at' => now()->toDateString(),
        'valid_until' => now()->addDays(7)->toDateString(),
        'notes' => 'Quotation PDF note',
    ], [[
        'product_id' => $product->id,
        'description' => 'PDF quotation line',
        'quantity' => 2,
        'unit_price' => 250000,
        'tax_rate' => 11,
    ]]);

    $invoice = app(CommercialWorkspaceService::class)->createInvoice($superadmin, [
        'company_id' => $companyA->id,
        'client_id' => $client->id,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->addDays(14)->toDateString(),
        'notes' => 'Invoice PDF note',
    ], [[
        'product_id' => $product->id,
        'description' => 'PDF invoice line',
        'quantity' => 1,
        'unit_price' => 250000,
        'tax_rate' => 11,
    ]]);

    $bill = app(CommercialWorkspaceService::class)->createVendorBill($superadmin, [
        'company_id' => $companyA->id,
        'vendor_id' => $vendor->id,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->addDays(14)->toDateString(),
        'notes' => 'Vendor bill PDF note',
    ], [[
        'product_id' => $product->id,
        'description' => 'PDF vendor bill line',
        'quantity' => 3,
        'unit_cost' => 150000,
        'tax_rate' => 11,
    ]]);

    foreach ([
        route('admin.commercial.quotations.pdf', $quotation),
        route('admin.commercial.invoices.pdf', $invoice),
        route('admin.commercial.vendor-bills.pdf', $bill),
    ] as $url) {
        $response = $this->actingAs($superadmin)->get($url);

        $response->assertOk();
        expect($response->headers->get('content-type'))->toContain('application/pdf')
            ->and($response->getContent())->toStartWith('%PDF');
    }

    $tenantAdmin = User::factory()->admin()->create(['company_id' => $companyB->id]);
    $role = Role::query()->create([
        'name' => 'Commercial PDF Viewer',
        'slug' => 'commercial_pdf_viewer',
        'permissions' => ['admin.commercial.view'],
    ]);
    $tenantAdmin->roles()->sync([$role->id]);

    $this->actingAs($tenantAdmin)
        ->get(route('admin.commercial.quotations.pdf', $quotation))
        ->assertForbidden();
});

test('commercial route requires explicit permission', function () {
    $admin = User::factory()->admin()->create();
    $admin->roles()->detach();

    $this->actingAs($admin)
        ->get(route('admin.commercial'))
        ->assertForbidden();

    $role = Role::query()->create([
        'name' => 'Commercial Viewer',
        'slug' => 'commercial_viewer',
        'permissions' => ['admin.commercial.view'],
    ]);
    $admin->roles()->sync([$role->id]);

    $this->actingAs($admin->fresh())
        ->get(route('admin.commercial'))
        ->assertOk();
});
