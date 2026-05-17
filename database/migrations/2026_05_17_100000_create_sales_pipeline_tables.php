<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_opportunities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignUlid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('stage', 32)->default('lead');
            $table->decimal('expected_value', 14, 2)->default(0);
            $table->unsignedTinyInteger('probability')->default(25);
            $table->date('expected_close_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->string('source', 80)->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'stage'], 'sales_opportunities_company_stage_index');
            $table->index(['company_id', 'expected_close_at'], 'sales_opportunities_company_close_index');
        });

        Schema::create('sales_follow_ups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sales_opportunity_id')->constrained('sales_opportunities')->cascadeOnDelete();
            $table->foreignUlid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('due_at')->nullable();
            $table->string('status', 32)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_at'], 'sales_follow_ups_status_due_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_follow_ups');
        Schema::dropIfExists('sales_opportunities');
    }
};
