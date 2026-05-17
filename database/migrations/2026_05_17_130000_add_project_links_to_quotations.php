<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            $table->foreignId('project_id')
                ->nullable()
                ->after('client_id')
                ->constrained('projects')
                ->nullOnDelete();

            $table->index(['company_id', 'project_id'], 'quotations_company_project_index');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table): void {
            $table->dropIndex('quotations_company_project_index');
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
