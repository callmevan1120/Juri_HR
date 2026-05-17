<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreignId('project_id')
                ->nullable()
                ->after('quotation_id')
                ->constrained('projects')
                ->nullOnDelete();

            $table->index(['company_id', 'project_id'], 'invoices_company_project_index');
        });

        Schema::table('sales_opportunities', function (Blueprint $table): void {
            $table->foreignId('project_id')
                ->nullable()
                ->after('client_id')
                ->constrained('projects')
                ->nullOnDelete();

            $table->index(['company_id', 'project_id'], 'sales_opportunities_company_project_index');
        });
    }

    public function down(): void
    {
        Schema::table('sales_opportunities', function (Blueprint $table): void {
            $table->dropIndex('sales_opportunities_company_project_index');
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropIndex('invoices_company_project_index');
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
