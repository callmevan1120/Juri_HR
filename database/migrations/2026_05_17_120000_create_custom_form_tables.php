<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_form_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('title');
            $table->string('category', 80)->default('general');
            $table->text('description')->nullable();
            $table->json('fields');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'is_active'], 'custom_form_templates_company_active_index');
            $table->index(['company_id', 'category'], 'custom_form_templates_company_category_index');
        });

        Schema::create('custom_form_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('custom_form_template_id')->constrained('custom_form_templates')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUlid('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32)->default('submitted');
            $table->json('payload');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at'], 'custom_form_submissions_company_created_index');
            $table->index(['submitted_by', 'created_at'], 'custom_form_submissions_user_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_form_submissions');
        Schema::dropIfExists('custom_form_templates');
    }
};
