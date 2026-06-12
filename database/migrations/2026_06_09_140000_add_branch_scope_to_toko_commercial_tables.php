<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained('company_branches')->nullOnDelete();
            $table->index(['company_id', 'branch_id', 'occurred_at'], 'stock_movements_company_branch_occurred_index');
        });

        Schema::table('quotations', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained('company_branches')->nullOnDelete();
            $table->index(['company_id', 'branch_id', 'status'], 'quotations_company_branch_status_index');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained('company_branches')->nullOnDelete();
            $table->index(['company_id', 'branch_id', 'status'], 'invoices_company_branch_status_index');
        });

        Schema::table('vendor_bills', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained('company_branches')->nullOnDelete();
            $table->index(['company_id', 'branch_id', 'status'], 'vendor_bills_company_branch_status_index');
        });

        Schema::table('delivery_letters', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained('company_branches')->nullOnDelete();
            $table->index(['company_id', 'branch_id', 'issued_at'], 'delivery_letters_company_branch_issued_index');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_letters', function (Blueprint $table): void {
            $table->dropIndex('delivery_letters_company_branch_issued_index');
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('vendor_bills', function (Blueprint $table): void {
            $table->dropIndex('vendor_bills_company_branch_status_index');
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropIndex('invoices_company_branch_status_index');
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('quotations', function (Blueprint $table): void {
            $table->dropIndex('quotations_company_branch_status_index');
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->dropIndex('stock_movements_company_branch_occurred_index');
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
