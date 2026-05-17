<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            $table->foreignId('sales_opportunity_id')
                ->nullable()
                ->after('project_id')
                ->constrained('sales_opportunities')
                ->nullOnDelete();

            $table->index(['company_id', 'sales_opportunity_id'], 'quotations_company_opportunity_index');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            $table->dropIndex('quotations_company_opportunity_index');
            $table->dropConstrainedForeignId('sales_opportunity_id');
        });
    }
};
