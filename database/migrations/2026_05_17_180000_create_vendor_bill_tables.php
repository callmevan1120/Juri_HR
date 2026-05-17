<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('status', 32)->default('active');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('tax_number')->nullable();
            $table->text('address')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'name'], 'vendors_company_name_unique');
            $table->index(['company_id', 'status'], 'vendors_company_status_index');
        });

        Schema::create('vendor_bills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('number');
            $table->string('status', 32)->default('posted');
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->foreignId('accounting_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('payment_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number'], 'vendor_bills_company_number_unique');
            $table->index(['company_id', 'status', 'due_at'], 'vendor_bills_company_status_due_index');
        });

        Schema::create('vendor_bill_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_bill_id')->constrained('vendor_bills')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 14, 3)->default(1);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('line_subtotal', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_bill_items');
        Schema::dropIfExists('vendor_bills');
        Schema::dropIfExists('vendors');
    }
};
