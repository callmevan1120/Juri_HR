<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_period_closings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 32)->default('closed');
            $table->foreignUlid('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->foreignUlid('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reopened_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'period_start', 'period_end'], 'accounting_period_closings_company_period_unique');
            $table->index(['company_id', 'status', 'period_end'], 'accounting_period_closings_company_status_end_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_period_closings');
    }
};
