<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('status', 32)->default('active');
            $table->string('unit', 32)->default('pcs');
            $table->decimal('selling_price', 14, 2)->default(0);
            $table->decimal('cost_price', 14, 2)->default(0);
            $table->boolean('stock_tracking')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'sku'], 'products_company_sku_unique');
            $table->index(['company_id', 'status'], 'products_company_status_index');
        });

        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 32);
            $table->decimal('quantity', 14, 3);
            $table->decimal('unit_cost', 14, 2)->nullable();
            $table->string('reference_type')->nullable();
            $table->string('reference_number')->nullable();
            $table->timestamp('occurred_at');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'occurred_at'], 'stock_movements_company_occurred_index');
            $table->index(['product_id', 'occurred_at'], 'stock_movements_product_occurred_index');
        });

        Schema::create('quotations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('number');
            $table->string('status', 32)->default('draft');
            $table->date('issued_at')->nullable();
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number'], 'quotations_company_number_unique');
            $table->index(['company_id', 'status'], 'quotations_company_status_index');
        });

        Schema::create('quotation_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 14, 3)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->string('number');
            $table->string('status', 32)->default('draft');
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number'], 'invoices_company_number_unique');
            $table->index(['company_id', 'status'], 'invoices_company_status_index');
        });

        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 14, 3)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
    }
};
