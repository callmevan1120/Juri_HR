<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_document_templates', function (Blueprint $table): void {
            $table->boolean('is_marketplace')->default(false)->after('is_active');
            $table->string('marketplace_slug')->nullable()->after('is_marketplace')->unique();
            $table->string('marketplace_category', 64)->nullable()->after('marketplace_slug');
            $table->json('marketplace_tags')->nullable()->after('marketplace_category');
            $table->foreignId('source_template_id')->nullable()->after('marketplace_tags')->constrained('employee_document_templates')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->after('source_template_id');
            $table->index(['is_marketplace', 'marketplace_category'], 'employee_doc_templates_marketplace_index');
        });
    }

    public function down(): void
    {
        Schema::table('employee_document_templates', function (Blueprint $table): void {
            $table->dropIndex('employee_doc_templates_marketplace_index');
            $table->dropConstrainedForeignId('source_template_id');
            $table->dropUnique(['marketplace_slug']);
            $table->dropColumn([
                'is_marketplace',
                'marketplace_slug',
                'marketplace_category',
                'marketplace_tags',
                'published_at',
            ]);
        });
    }
};
