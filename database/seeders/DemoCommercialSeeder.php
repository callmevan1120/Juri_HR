<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesFollowUp;
use App\Models\SalesOpportunity;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorBillItem;
use App\Support\AccountingWorkspaceService;
use Database\Seeders\Concerns\GuardsDemoSeeding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoCommercialSeeder extends Seeder
{
    use GuardsDemoSeeding;

    public function run(): void
    {
        if ($this->shouldSkipDemoSeeding() || ! $this->hasRequiredTables()) {
            return;
        }

        $company = $this->company();
        $actor = $this->actor();
        $client = $this->client($company);
        $project = $this->project($company, $client);
        $product = $this->product($company);
        $vendor = $this->vendor($company);

        $movement = StockMovement::query()->updateOrCreate([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_IN,
            'reference_number' => 'OPENING-STOCK-001',
        ], [
            'user_id' => $actor?->id,
            'quantity' => 25,
            'unit_cost' => 750000,
            'occurred_at' => now()->subDays(12),
            'notes' => 'Opening stock for commercial demo.',
            'metadata' => ['seeded' => true],
        ]);

        if ($actor) {
            app(AccountingWorkspaceService::class)->postStockMovement($actor, $movement->fresh(['product']));
        }

        $opportunity = SalesOpportunity::query()->updateOrCreate([
            'company_id' => $company->id,
            'title' => 'ACME Device Rollout Q2',
        ], [
            'client_id' => $client->id,
            'project_id' => $project?->id,
            'owner_id' => $actor?->id,
            'stage' => SalesOpportunity::STAGE_PROPOSAL,
            'expected_value' => 24000000,
            'probability' => 65,
            'expected_close_at' => now()->addDays(21)->toDateString(),
            'next_follow_up_at' => now()->addDays(2),
            'source' => 'Referral',
            'notes' => 'Need final commercial proposal and payment term confirmation.',
            'metadata' => ['seeded' => true],
        ]);

        SalesFollowUp::query()->updateOrCreate([
            'sales_opportunity_id' => $opportunity->id,
            'notes' => 'Follow up procurement approval and technical rollout window.',
        ], [
            'assigned_to' => $actor?->id,
            'due_at' => now()->addDays(2),
            'status' => SalesFollowUp::STATUS_PENDING,
            'metadata' => ['seeded' => true],
        ]);

        $quotation = $this->quotation($company, $client, $project, $opportunity, $product);
        $invoice = $this->invoice($company, $client, $project, $quotation, $product);
        $bill = $this->vendorBill($company, $vendor, $product);

        if ($actor) {
            app(AccountingWorkspaceService::class)->postInvoicePayment($actor, $invoice->fresh(['client']));
            app(AccountingWorkspaceService::class)->postVendorBill($actor, $bill->fresh(['vendor', 'items.product']));
        }
    }

    private function hasRequiredTables(): bool
    {
        return collect([
            'companies',
            'clients',
            'projects',
            'products',
            'stock_movements',
            'vendors',
            'vendor_bills',
            'quotations',
            'invoices',
            'sales_opportunities',
            'sales_follow_ups',
            'accounting_accounts',
            'journal_entries',
        ])->every(fn (string $table): bool => Schema::hasTable($table));
    }

    private function company(): Company
    {
        return Company::query()->firstOrCreate([
            'slug' => 'paspapan-demo',
        ], [
            'name' => 'PasPapan Demo',
            'status' => Company::STATUS_ACTIVE,
            'metadata' => ['seeded' => true],
        ]);
    }

    private function actor(): ?User
    {
        return User::query()->whereIn('group', ['superadmin', 'admin'])->first()
            ?? User::query()->first();
    }

    private function client(Company $company): Client
    {
        return Client::query()->updateOrCreate([
            'company_id' => $company->id,
            'code' => 'CL-ACME',
        ], [
            'name' => 'PT Acme Nusantara',
            'status' => Client::STATUS_ACTIVE,
            'contact_name' => 'Rani Procurement',
            'contact_email' => 'procurement@acme.test',
            'contact_phone' => '081234560001',
            'address' => 'Kawasan Bisnis TB Simatupang, Jakarta',
            'metadata' => ['seeded' => true],
        ]);
    }

    private function project(Company $company, Client $client): ?Project
    {
        if (! Schema::hasTable('projects')) {
            return null;
        }

        return Project::query()->updateOrCreate([
            'company_id' => $company->id,
            'code' => 'PRJ-OPS-001',
        ], [
            'client_id' => $client->id,
            'name' => 'ACME Site Rollout',
            'status' => Project::STATUS_ACTIVE,
            'starts_at' => now()->subWeeks(2)->toDateString(),
            'ends_at' => now()->addWeeks(6)->toDateString(),
            'description' => 'Commercially linked rollout project.',
            'metadata' => ['seeded' => true],
        ]);
    }

    private function product(Company $company): Product
    {
        return Product::query()->updateOrCreate([
            'company_id' => $company->id,
            'sku' => 'DEVICE-BUNDLE-001',
        ], [
            'name' => 'Smart Attendance Device Bundle',
            'status' => Product::STATUS_ACTIVE,
            'unit' => 'paket',
            'selling_price' => 2500000,
            'cost_price' => 750000,
            'stock_tracking' => true,
            'reorder_point' => 5,
            'metadata' => ['seeded' => true],
        ]);
    }

    private function vendor(Company $company): Vendor
    {
        return Vendor::query()->updateOrCreate([
            'company_id' => $company->id,
            'name' => 'PT Supplier Teknologi Nusantara',
        ], [
            'status' => Vendor::STATUS_ACTIVE,
            'contact_name' => 'Dimas Supplier',
            'email' => 'dimas@supplier.test',
            'phone' => '081234560099',
            'tax_number' => '09.123.456.7-001.000',
            'address' => 'Jl. Industri No. 88, Bekasi',
            'metadata' => ['seeded' => true],
        ]);
    }

    private function quotation(
        Company $company,
        Client $client,
        ?Project $project,
        SalesOpportunity $opportunity,
        Product $product,
    ): Quotation {
        $subtotal = 6 * 2500000;
        $tax = $subtotal * 0.11;

        $quotation = Quotation::query()->updateOrCreate([
            'company_id' => $company->id,
            'number' => 'QTN-DEMO-001',
        ], [
            'client_id' => $client->id,
            'project_id' => $project?->id,
            'sales_opportunity_id' => $opportunity->id,
            'status' => Quotation::STATUS_SENT,
            'issued_at' => now()->subDays(3)->toDateString(),
            'valid_until' => now()->addDays(11)->toDateString(),
            'subtotal' => $subtotal,
            'tax_total' => $tax,
            'grand_total' => $subtotal + $tax,
            'notes' => 'Demo quotation generated from seeded commercial pipeline.',
            'metadata' => ['seeded' => true],
        ]);

        $quotation->items()->delete();
        QuotationItem::query()->create([
            'quotation_id' => $quotation->id,
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity' => 6,
            'unit_price' => 2500000,
            'tax_rate' => 11,
            'line_total' => $subtotal + $tax,
        ]);

        return $quotation->fresh(['items']);
    }

    private function invoice(Company $company, Client $client, ?Project $project, Quotation $quotation, Product $product): Invoice
    {
        $subtotal = 3 * 2500000;
        $tax = $subtotal * 0.11;

        $invoice = Invoice::query()->updateOrCreate([
            'company_id' => $company->id,
            'number' => 'INV-DEMO-001',
        ], [
            'client_id' => $client->id,
            'quotation_id' => $quotation->id,
            'project_id' => $project?->id,
            'status' => Invoice::STATUS_PAID,
            'issued_at' => now()->subDays(2)->toDateString(),
            'due_at' => now()->addDays(12)->toDateString(),
            'paid_at' => now()->subDay(),
            'subtotal' => $subtotal,
            'tax_total' => $tax,
            'grand_total' => $subtotal + $tax,
            'notes' => 'Demo paid invoice for accounting and PDF review.',
            'metadata' => ['seeded' => true],
        ]);

        $invoice->items()->delete();
        InvoiceItem::query()->create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity' => 3,
            'unit_price' => 2500000,
            'tax_rate' => 11,
            'line_total' => $subtotal + $tax,
        ]);

        return $invoice->fresh(['items']);
    }

    private function vendorBill(Company $company, Vendor $vendor, Product $product): VendorBill
    {
        $subtotal = 10 * 750000;
        $tax = $subtotal * 0.11;

        $bill = VendorBill::query()->updateOrCreate([
            'company_id' => $company->id,
            'number' => 'BILL-DEMO-001',
        ], [
            'vendor_id' => $vendor->id,
            'status' => VendorBill::STATUS_POSTED,
            'issued_at' => now()->subDays(7)->toDateString(),
            'due_at' => now()->addDays(7)->toDateString(),
            'subtotal' => $subtotal,
            'tax_total' => $tax,
            'grand_total' => $subtotal + $tax,
            'notes' => 'Demo vendor bill for stock and AP review.',
            'metadata' => ['seeded' => true],
        ]);

        $bill->items()->delete();
        VendorBillItem::query()->create([
            'vendor_bill_id' => $bill->id,
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity' => 10,
            'unit_cost' => 750000,
            'tax_rate' => 11,
            'line_subtotal' => $subtotal,
            'line_total' => $subtotal + $tax,
        ]);

        return $bill->fresh(['items.product']);
    }
}
