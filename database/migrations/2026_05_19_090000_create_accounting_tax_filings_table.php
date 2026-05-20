<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_tax_filings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('tax_type', 40)->default('ppn_output');
            $table->decimal('taxable_turnover', 16, 2)->default(0);
            $table->decimal('output_tax', 16, 2)->default(0);
            $table->decimal('input_tax', 16, 2)->default(0);
            $table->decimal('net_tax_payable', 16, 2)->default(0);
            $table->string('status', 32)->default('draft');
            $table->foreignUlid('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('prepared_at')->nullable();
            $table->foreignUlid('filed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('filed_at')->nullable();
            $table->foreignUlid('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->string('filing_reference')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'period_start', 'period_end', 'tax_type'], 'accounting_tax_filings_company_period_type_unique');
            $table->index(['company_id', 'status', 'period_end'], 'accounting_tax_filings_company_status_end_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_tax_filings');
    }
};
