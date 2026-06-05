<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\Commercial\DocumentForm;
use App\Livewire\Forms\Commercial\OpportunityForm;
use App\Livewire\Forms\Commercial\ProductForm;
use App\Livewire\Forms\Commercial\StockMovementForm;
use App\Livewire\Forms\Commercial\VendorBillForm;
use App\Livewire\Forms\Commercial\VendorForm;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\SalesFollowUp;
use App\Models\SalesOpportunity;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Support\CommercialWorkspaceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class CommercialWorkspace extends Component
{
    use InteractsWithBanner;

    private const TABS = ['pipeline', 'products', 'stock', 'purchases', 'quotations', 'invoices'];

    protected CommercialWorkspaceService $commerce;

    #[Url(history: true)]
    public string $activeTab = 'products';

    public string $search = '';

    public ProductForm $productForm;
    public StockMovementForm $stockMovementForm;
    public VendorForm $vendorForm;
    public VendorBillForm $vendorBillForm;
    public DocumentForm $documentForm;
    public OpportunityForm $opportunityForm;

    public function boot(CommercialWorkspaceService $commerce): void
    {
        Gate::authorize('viewCommercialWorkspace');

        $this->commerce = $commerce;
    }

    public function mount(): void
    {
        $this->normalizeActiveTab();

        $companyId = $this->defaultCompanyId();

        if ($companyId === null) {
            return;
        }

        $this->productForm->companyId = $companyId;
        $this->vendorForm->companyId = $companyId;
        $this->documentForm->companyId = $companyId;
        $this->opportunityForm->companyId = $companyId;
    }

    public function updatedDocumentFormCompanyId(): void
    {
        $this->documentForm->reset(['clientId', 'projectId', 'productId']);
    }

    public function updatedOpportunityFormCompanyId(): void
    {
        $this->opportunityForm->reset(['clientId', 'projectId']);
    }

    public function updatedVendorBillFormVendorId(): void
    {
        $this->vendorBillForm->reset('productId');
    }

    public function updatedActiveTab(): void
    {
        $this->normalizeActiveTab();
    }

    public function createProduct(): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $validated = $this->productForm->validate();

        $this->commerce->createProduct(auth()->user(), [
            'company_id' => (int) $validated['companyId'],
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?: null,
            'unit' => $validated['unit'],
            'selling_price' => $validated['sellingPrice'],
            'cost_price' => $validated['costPrice'],
            'stock_tracking' => true,
            'reorder_point' => $validated['reorderPoint'],
        ]);

        $this->productForm->resetForm();
        $this->banner(__('Product created.'));
    }

    public function recordStockMovement(): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $validated = $this->stockMovementForm->validate();
        $product = Product::query()->findOrFail((int) $validated['productId']);

        $this->commerce->recordStockMovement(auth()->user(), $product, [
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'unit_cost' => $validated['unitCost'] !== '' ? $validated['unitCost'] : null,
            'notes' => $validated['notes'] ?: null,
        ]);

        $this->stockMovementForm->resetForm();
        $this->banner(__('Stock movement recorded.'));
    }

    public function createVendor(): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $validated = $this->vendorForm->validate();

        $this->commerce->createVendor(auth()->user(), [
            'company_id' => (int) $validated['companyId'],
            'name' => $validated['name'],
            'contact_name' => $validated['contactName'] ?: null,
            'email' => $validated['email'] ?: null,
            'phone' => $validated['phone'] ?: null,
        ]);

        $this->vendorForm->resetForm();
        $this->banner(__('Vendor created.'));
    }

    public function createVendorBill(): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $validated = $this->vendorBillForm->validate();
        $vendor = Vendor::query()->findOrFail((int) $validated['vendorId']);

        $this->commerce->createVendorBill(auth()->user(), [
            'company_id' => $vendor->company_id,
            'vendor_id' => $vendor->id,
            'due_at' => $validated['dueAt'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ], [[
            'product_id' => $validated['productId'] ?: null,
            'description' => $validated['description'],
            'quantity' => $validated['quantity'],
            'unit_cost' => $validated['unitCost'],
            'tax_rate' => $validated['taxRate'],
        ]]);

        $this->vendorBillForm->resetForm();
        $this->banner(__('Vendor bill posted to AP.'));
    }

    public function markVendorBillPaid(int $billId): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $bill = VendorBill::query()->findOrFail($billId);
        $this->commerce->markVendorBillPaid(auth()->user(), $bill);
        $this->banner(__('Vendor bill paid and posted to accounting.'));
    }

    public function createQuotation(): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $validated = $this->documentForm->validate();

        $this->commerce->createQuotation(auth()->user(), [
            'company_id' => (int) $validated['companyId'],
            'client_id' => $validated['clientId'] ?: null,
            'project_id' => $validated['projectId'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ], [[
            'product_id' => $validated['productId'] ?: null,
            'description' => $validated['description'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unitPrice'],
            'tax_rate' => $validated['taxRate'],
        ]]);

        $this->documentForm->resetForm();
        $this->banner(__('Quotation created.'));
    }

    public function createInvoice(): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $validated = $this->documentForm->validate();

        $this->commerce->createInvoice(auth()->user(), [
            'company_id' => (int) $validated['companyId'],
            'client_id' => $validated['clientId'] ?: null,
            'project_id' => $validated['projectId'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ], [[
            'product_id' => $validated['productId'] ?: null,
            'description' => $validated['description'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unitPrice'],
            'tax_rate' => $validated['taxRate'],
        ]]);

        $this->documentForm->resetForm();
        $this->banner(__('Invoice created.'));
    }

    public function markInvoicePaid(int $invoiceId): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $invoice = Invoice::query()->findOrFail($invoiceId);
        $this->commerce->markInvoicePaid(auth()->user(), $invoice);
        $this->banner(__('Invoice marked as paid and posted to accounting.'));
    }

    public function convertQuotationToInvoice(int $quotationId): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $quotation = Quotation::query()->findOrFail($quotationId);
        $this->commerce->convertQuotationToInvoice(auth()->user(), $quotation);

        $this->activeTab = 'invoices';
        $this->banner(__('Quotation converted to invoice.'));
    }

    public function createOpportunity(): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $validated = $this->opportunityForm->validate($this->opportunityForm->rules($this->commerce->opportunityStages()));

        $this->commerce->createOpportunity(auth()->user(), [
            'company_id' => (int) $validated['companyId'],
            'client_id' => $validated['clientId'] ?: null,
            'project_id' => $validated['projectId'] ?: null,
            'title' => $validated['title'],
            'stage' => $validated['stage'],
            'expected_value' => $validated['expectedValue'],
            'expected_close_at' => $validated['expectedCloseAt'] ?: null,
            'next_follow_up_at' => $validated['nextFollowUpAt'] ?: null,
            'source' => $validated['source'] ?: null,
            'notes' => $validated['notes'] ?: null,
            'follow_up_notes' => $validated['notes'] ?: null,
        ]);

        $this->opportunityForm->resetForm();
        $this->banner(__('Sales opportunity created.'));
    }

    public function moveOpportunityStage(int $opportunityId, string $stage): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $opportunity = SalesOpportunity::query()->findOrFail($opportunityId);
        $this->commerce->updateOpportunityStage(auth()->user(), $opportunity, $stage);
        $this->banner(__('Sales stage updated.'));
    }

    public function createQuotationFromOpportunity(int $opportunityId): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $opportunity = SalesOpportunity::query()->findOrFail($opportunityId);
        $this->commerce->createQuotationFromOpportunity(auth()->user(), $opportunity);

        $this->activeTab = 'quotations';
        $this->banner(__('Quotation created from opportunity.'));
    }

    public function completeFollowUp(int $followUpId): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $followUp = SalesFollowUp::query()->findOrFail($followUpId);
        $this->commerce->completeFollowUp(auth()->user(), $followUp);
        $this->banner(__('Follow-up marked as done.'));
    }

    public function render()
    {
        $user = auth()->user();
        $companyIds = $this->commerce
            ->scopeCompanies(Company::query(), $user)
            ->pluck('id')
            ->all();

        $companies = Company::query()
            ->whereIn('id', $companyIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $products = Product::query()
            ->with(['company:id,name', 'stockMovements'])
            ->whereIn('company_id', $companyIds)
            ->when($this->search !== '', fn (Builder $query) => $query->where(function (Builder $nested): void {
                $nested
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('sku', 'like', '%'.$this->search.'%');
            }))
            ->latest()
            ->get();

        $clients = Client::query()
            ->whereIn('company_id', $companyIds)
            ->orderBy('name')
            ->get(['id', 'company_id', 'name']);

        $projects = Project::query()
            ->whereIn('company_id', $companyIds)
            ->orderBy('name')
            ->get(['id', 'company_id', 'client_id', 'name']);

        $quotations = Quotation::query()
            ->with(['company:id,name', 'client:id,name', 'project:id,name', 'items'])
            ->whereIn('company_id', $companyIds)
            ->latest()
            ->get();

        $invoices = Invoice::query()
            ->with(['company:id,name', 'client:id,name', 'project:id,name', 'items'])
            ->whereIn('company_id', $companyIds)
            ->latest()
            ->get();

        $vendors = Vendor::query()
            ->whereIn('company_id', $companyIds)
            ->when($this->search !== '', fn (Builder $query) => $query->where(function (Builder $nested): void {
                $nested
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('contact_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            }))
            ->orderBy('name')
            ->get();

        $vendorBills = VendorBill::query()
            ->with(['company:id,name', 'vendor:id,name', 'items.product', 'accountingJournalEntry', 'paymentJournalEntry'])
            ->whereIn('company_id', $companyIds)
            ->latest()
            ->get();

        $opportunities = SalesOpportunity::query()
            ->with(['company:id,name', 'client:id,name', 'project:id,name', 'owner:id,name', 'followUps', 'quotations:id,sales_opportunity_id,number,status'])
            ->whereIn('company_id', $companyIds)
            ->when($this->search !== '', fn (Builder $query) => $query->where(function (Builder $nested): void {
                $nested
                    ->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('source', 'like', '%'.$this->search.'%')
                    ->orWhereHas('client', fn (Builder $clientQuery) => $clientQuery->where('name', 'like', '%'.$this->search.'%'));
            }))
            ->latest()
            ->get();

        $documentCompanyId = $this->scopedCompanyId($companyIds, $this->documentForm->companyId);
        $opportunityCompanyId = $this->scopedCompanyId($companyIds, $this->opportunityForm->companyId);
        $billVendorCompanyId = $this->selectedVendorCompanyId();

        return view('livewire.admin.commercial-workspace', [
            'companies' => $companies,
            'products' => $products,
            'clients' => $clients,
            'projects' => $projects,
            'documentClientOptions' => $documentCompanyId === null ? $clients : $clients->where('company_id', $documentCompanyId)->values(),
            'documentProjectOptions' => $documentCompanyId === null ? $projects : $projects->where('company_id', $documentCompanyId)->values(),
            'documentProductOptions' => $documentCompanyId === null ? $products : $products->where('company_id', $documentCompanyId)->values(),
            'opportunityClientOptions' => $opportunityCompanyId === null ? $clients : $clients->where('company_id', $opportunityCompanyId)->values(),
            'opportunityProjectOptions' => $opportunityCompanyId === null ? $projects : $projects->where('company_id', $opportunityCompanyId)->values(),
            'billProductOptions' => $billVendorCompanyId === null ? $products : $products->where('company_id', $billVendorCompanyId)->values(),
            'quotations' => $quotations,
            'invoices' => $invoices,
            'vendors' => $vendors,
            'vendorBills' => $vendorBills,
            'opportunities' => $opportunities,
            'opportunityStages' => $this->commerce->opportunityStages(),
            'salesSummary' => $this->commerce->salesSummaryForCompanies($companyIds),
            'collectionSummary' => $this->commerce->collectionSummaryForCompanies($companyIds),
            'canManage' => $user->can('manageCommercialWorkspace'),
        ]);
    }

    private function defaultCompanyId(): ?string
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $companyId = $this->commerce
            ->scopeCompanies(Company::query(), $user)
            ->orderBy('name')
            ->value('id');

        return $companyId === null ? null : (string) $companyId;
    }

    /**
     * @param  list<int|string>  $companyIds
     */
    private function scopedCompanyId(array $companyIds, string $companyId): ?int
    {
        if ($companyId === '') {
            return null;
        }

        $companyId = (int) $companyId;

        return in_array($companyId, array_map('intval', $companyIds), true) ? $companyId : null;
    }

    private function selectedVendorCompanyId(): ?int
    {
        if ($this->vendorBillForm->vendorId === '') {
            return null;
        }

        return Vendor::query()->whereKey($this->vendorBillForm->vendorId)->value('company_id');
    }

    private function normalizeActiveTab(): void
    {
        if (! in_array($this->activeTab, self::TABS, true)) {
            $this->activeTab = 'products';
        }
    }
}
