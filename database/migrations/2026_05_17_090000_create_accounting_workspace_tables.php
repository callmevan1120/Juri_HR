<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name');
            $table->string('type', 32);
            $table->string('normal_balance', 16);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code'], 'accounting_accounts_company_code_unique');
            $table->index(['company_id', 'type'], 'accounting_accounts_company_type_index');
        });

        Schema::create('journal_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('number');
            $table->date('entry_date');
            $table->string('status', 32)->default('posted');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number'], 'journal_entries_company_number_unique');
            $table->index(['company_id', 'entry_date'], 'journal_entries_company_date_index');
            $table->index(['source_type', 'source_id'], 'journal_entries_source_index');
        });

        Schema::create('journal_entry_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('accounting_account_id')->constrained('accounting_accounts')->restrictOnDelete();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->text('memo')->nullable();
            $table->timestamps();

            $table->index('accounting_account_id', 'journal_entry_lines_account_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounting_accounts');
    }
};
