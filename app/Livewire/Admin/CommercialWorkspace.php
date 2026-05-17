<?php

namespace App\Livewire\Admin;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\SalesFollowUp;
use App\Models\SalesOpportunity;
use App\Models\StockMovement;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Support\CommercialWorkspaceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CommercialWorkspace extends Component
{
    use InteractsWithBanner;

    protected CommercialWorkspaceService $commerce;

    public string $activeTab = 'products';

    public string $search = '';

    public string $productCompanyId = '';

    public string $productName = '';

    public string $productSku = '';

    public string $productUnit = 'pcs';

    public string $productSellingPrice = '0';

    public string $productCostPrice = '0';

    public string $productReorderPoint = '0';

    public string $stockProductId = '';

    public string $stockType = StockMovement::TYPE_IN;

    public string $stockQuantity = '1';

    public string $stockUnitCost = '0';

    public string $stockNotes = '';

    public string $vendorCompanyId = '';

    public string $vendorName = '';

    public string $vendorContactName = '';

    public string $vendorEmail = '';

    public string $vendorPhone = '';

    public string $billVendorId = '';

    public string $billProductId = '';

    public string $billDescription = '';

    public string $billQuantity = '1';

    public string $billUnitCost = '0';

    public string $billTaxRate = '11';

    public string $billDueAt = '';

    public string $billNotes = '';

    public string $documentCompanyId = '';

    public string $documentClientId = '';

    public string $documentProjectId = '';

    public string $documentProductId = '';

    public string $documentDescription = '';

    public string $documentQuantity = '1';

    public string $documentUnitPrice = '0';

    public string $documentTaxRate = '11';

    public string $documentNotes = '';

    public string $opportunityCompanyId = '';

    public string $opportunityClientId = '';

    public string $opportunityProjectId = '';

    public string $opportunityTitle = '';

    public string $opportunityStage = SalesOpportunity::STAGE_LEAD;

    public string $opportunityExpectedValue = '0';

    public string $opportunityExpectedCloseAt = '';

    public string $opportunityNextFollowUpAt = '';

    public string $opportunitySource = '';

    public string $opportunityNotes = '';

    public function boot(CommercialWorkspaceService $commerce): void
    {
        Gate::authorize('viewCommercialWorkspace');

        $this->commerce = $commerce;
    }

    public function createProduct(): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $validated = $this->validate([
            'productCompanyId' => ['required', 'integer', Rule::exists('companies', 'id')],
            'productName' => ['required', 'string', 'max:180'],
            'productSku' => ['nullable', 'string', 'max:80'],
            'productUnit' => ['required', 'string', 'max:32'],
            'productSellingPrice' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'productCostPrice' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'productReorderPoint' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        $this->commerce->createProduct(auth()->user(), [
            'company_id' => (int) $validated['productCompanyId'],
            'name' => $validated['productName'],
            'sku' => $validated['productSku'] ?: null,
            'unit' => $validated['productUnit'],
            'selling_price' => $validated['productSellingPrice'],
            'cost_price' => $validated['productCostPrice'],
            'stock_tracking' => true,
            'reorder_point' => $validated['productReorderPoint'],
        ]);

        $this->reset(['productName', 'productSku', 'productSellingPrice', 'productCostPrice', 'productReorderPoint']);
        $this->productSellingPrice = '0';
        $this->productCostPrice = '0';
        $this->productReorderPoint = '0';
        $this->banner(__('Product created.'));
    }

    public function recordStockMovement(): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $validated = $this->validate([
            'stockProductId' => ['required', 'integer', Rule::exists('products', 'id')],
            'stockType' => ['required', Rule::in([StockMovement::TYPE_IN, StockMovement::TYPE_OUT, StockMovement::TYPE_ADJUSTMENT])],
            'stockQuantity' => ['required', 'numeric', 'min:0.001', 'max:999999999'],
            'stockUnitCost' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'stockNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::query()->findOrFail((int) $validated['stockProductId']);

        $this->commerce->recordStockMovement(auth()->user(), $product, [
            'type' => $validated['stockType'],
            'quantity' => $validated['stockQuantity'],
            'unit_cost' => $validated['stockUnitCost'] !== '' ? $validated['stockUnitCost'] : null,
            'notes' => $validated['stockNotes'] ?: null,
        ]);

        $this->reset(['stockQuantity', 'stockUnitCost', 'stockNotes']);
        $this->stockQuantity = '1';
        $this->stockUnitCost = '0';
        $this->banner(__('Stock movement recorded.'));
    }

    public function createVendor(): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $validated = $this->validate([
            'vendorCompanyId' => ['required', 'integer', Rule::exists('companies', 'id')],
            'vendorName' => ['required', 'string', 'max:180'],
            'vendorContactName' => ['nullable', 'string', 'max:180'],
            'vendorEmail' => ['nullable', 'email', 'max:180'],
            'vendorPhone' => ['nullable', 'string', 'max:80'],
        ]);

        $this->commerce->createVendor(auth()->user(), [
            'company_id' => (int) $validated['vendorCompanyId'],
            'name' => $validated['vendorName'],
            'contact_name' => $validated['vendorContactName'] ?: null,
            'email' => $validated['vendorEmail'] ?: null,
            'phone' => $validated['vendorPhone'] ?: null,
        ]);

        $this->reset(['vendorName', 'vendorContactName', 'vendorEmail', 'vendorPhone']);
        $this->banner(__('Vendor created.'));
    }

    public function createVendorBill(): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $validated = $this->validate([
            'billVendorId' => ['required', 'integer', Rule::exists('vendors', 'id')],
            'billProductId' => ['nullable', 'integer', Rule::exists('products', 'id')],
            'billDescription' => ['required', 'string', 'max:180'],
            'billQuantity' => ['required', 'numeric', 'min:0.001', 'max:999999999'],
            'billUnitCost' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'billTaxRate' => ['required', 'numeric', 'min:0', 'max:100'],
            'billDueAt' => ['nullable', 'date'],
            'billNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $vendor = Vendor::query()->findOrFail((int) $validated['billVendorId']);

        $this->commerce->createVendorBill(auth()->user(), [
            'company_id' => $vendor->company_id,
            'vendor_id' => $vendor->id,
            'due_at' => $validated['billDueAt'] ?: null,
            'notes' => $validated['billNotes'] ?: null,
        ], [[
            'product_id' => $validated['billProductId'] ?: null,
            'description' => $validated['billDescription'],
            'quantity' => $validated['billQuantity'],
            'unit_cost' => $validated['billUnitCost'],
            'tax_rate' => $validated['billTaxRate'],
        ]]);

        $this->resetVendorBillForm();
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

        $validated = $this->documentValidation();

        $this->commerce->createQuotation(auth()->user(), [
            'company_id' => (int) $validated['documentCompanyId'],
            'client_id' => $validated['documentClientId'] ?: null,
            'project_id' => $validated['documentProjectId'] ?: null,
            'notes' => $validated['documentNotes'] ?: null,
        ], [$this->documentItem($validated)]);

        $this->resetDocumentForm();
        $this->banner(__('Quotation created.'));
    }

    public function createInvoice(): void
    {
        Gate::authorize('manageCommercialWorkspace');

        $validated = $this->documentValidation();

        $this->commerce->createInvoice(auth()->user(), [
            'company_id' => (int) $validated['documentCompanyId'],
            'client_id' => $validated['documentClientId'] ?: null,
            'project_id' => $validated['documentProjectId'] ?: null,
            'notes' => $validated['documentNotes'] ?: null,
        ], [$this->documentItem($validated)]);

        $this->resetDocumentForm();
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

        $validated = $this->validate([
            'opportunityCompanyId' => ['required', 'integer', Rule::exists('companies', 'id')],
            'opportunityClientId' => ['nullable', 'integer', Rule::exists('clients', 'id')],
            'opportunityProjectId' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'opportunityTitle' => ['required', 'string', 'max:180'],
            'opportunityStage' => ['required', Rule::in($this->commerce->opportunityStages())],
            'opportunityExpectedValue' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'opportunityExpectedCloseAt' => ['nullable', 'date'],
            'opportunityNextFollowUpAt' => ['nullable', 'date'],
            'opportunitySource' => ['nullable', 'string', 'max:80'],
            'opportunityNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->commerce->createOpportunity(auth()->user(), [
            'company_id' => (int) $validated['opportunityCompanyId'],
            'client_id' => $validated['opportunityClientId'] ?: null,
            'project_id' => $validated['opportunityProjectId'] ?: null,
            'title' => $validated['opportunityTitle'],
            'stage' => $validated['opportunityStage'],
            'expected_value' => $validated['opportunityExpectedValue'],
            'expected_close_at' => $validated['opportunityExpectedCloseAt'] ?: null,
            'next_follow_up_at' => $validated['opportunityNextFollowUpAt'] ?: null,
            'source' => $validated['opportunitySource'] ?: null,
            'notes' => $validated['opportunityNotes'] ?: null,
            'follow_up_notes' => $validated['opportunityNotes'] ?: null,
        ]);

        $this->resetOpportunityForm();
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

        return view('livewire.admin.commercial-workspace', [
            'companies' => $companies,
            'products' => $products,
            'clients' => $clients,
            'projects' => $projects,
            'quotations' => $quotations,
            'invoices' => $invoices,
            'vendors' => $vendors,
            'vendorBills' => $vendorBills,
            'opportunities' => $opportunities,
            'opportunityStages' => $this->commerce->opportunityStages(),
            'salesSummary' => $this->commerce->salesSummaryForCompanies($companyIds),
            'canManage' => $user->can('manageCommercialWorkspace'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function documentValidation(): array
    {
        return $this->validate([
            'documentCompanyId' => ['required', 'integer', Rule::exists('companies', 'id')],
            'documentClientId' => ['nullable', 'integer', Rule::exists('clients', 'id')],
            'documentProjectId' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'documentProductId' => ['nullable', 'integer', Rule::exists('products', 'id')],
            'documentDescription' => ['required', 'string', 'max:180'],
            'documentQuantity' => ['required', 'numeric', 'min:0.001', 'max:999999999'],
            'documentUnitPrice' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'documentTaxRate' => ['required', 'numeric', 'min:0', 'max:100'],
            'documentNotes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function documentItem(array $validated): array
    {
        return [
            'product_id' => $validated['documentProductId'] ?: null,
            'description' => $validated['documentDescription'],
            'quantity' => $validated['documentQuantity'],
            'unit_price' => $validated['documentUnitPrice'],
            'tax_rate' => $validated['documentTaxRate'],
        ];
    }

    private function resetDocumentForm(): void
    {
        $this->reset(['documentProjectId', 'documentProductId', 'documentDescription', 'documentQuantity', 'documentUnitPrice', 'documentTaxRate', 'documentNotes']);
        $this->documentQuantity = '1';
        $this->documentUnitPrice = '0';
        $this->documentTaxRate = '11';
    }

    private function resetVendorBillForm(): void
    {
        $this->reset(['billProductId', 'billDescription', 'billQuantity', 'billUnitCost', 'billTaxRate', 'billDueAt', 'billNotes']);
        $this->billQuantity = '1';
        $this->billUnitCost = '0';
        $this->billTaxRate = '11';
    }

    private function resetOpportunityForm(): void
    {
        $this->reset([
            'opportunityClientId',
            'opportunityProjectId',
            'opportunityTitle',
            'opportunityExpectedValue',
            'opportunityExpectedCloseAt',
            'opportunityNextFollowUpAt',
            'opportunitySource',
            'opportunityNotes',
        ]);
        $this->opportunityStage = SalesOpportunity::STAGE_LEAD;
        $this->opportunityExpectedValue = '0';
    }
}
