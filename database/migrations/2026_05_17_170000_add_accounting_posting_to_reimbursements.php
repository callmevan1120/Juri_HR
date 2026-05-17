<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reimbursements', function (Blueprint $table): void {
            $table->foreignId('accounting_journal_entry_id')
                ->nullable()
                ->after('admin_note')
                ->constrained('journal_entries')
                ->nullOnDelete();
            $table->timestamp('accounting_posted_at')->nullable()->after('accounting_journal_entry_id');

            $table->index(['status', 'accounting_posted_at'], 'reimbursements_status_accounting_posted_index');
        });
    }

    public function down(): void
    {
        Schema::table('reimbursements', function (Blueprint $table): void {
            $table->dropIndex('reimbursements_status_accounting_posted_index');
            $table->dropConstrainedForeignId('accounting_journal_entry_id');
            $table->dropColumn('accounting_posted_at');
        });
    }
};
