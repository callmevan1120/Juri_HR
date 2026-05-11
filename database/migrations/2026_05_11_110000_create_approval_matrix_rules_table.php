<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_matrix_rules', function (Blueprint $table) {
            $table->id();
            $table->string('workflow', 80);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->json('conditions')->nullable();
            $table->json('steps');
            $table->timestamps();

            $table->index(['workflow', 'is_active', 'priority'], 'idx_approval_matrix_workflow_active');
        });

        foreach (['reimbursements', 'cash_advances'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('approval_matrix_rule_id')->nullable()->after('status')->constrained('approval_matrix_rules')->nullOnDelete();
                $table->json('approval_steps')->nullable()->after('approval_matrix_rule_id');
                $table->string('approval_current_step', 80)->nullable()->after('approval_steps');
                $table->json('approval_completed_steps')->nullable()->after('approval_current_step');
            });
        }
    }

    public function down(): void
    {
        foreach (['reimbursements', 'cash_advances'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('approval_matrix_rule_id');
                $table->dropColumn([
                    'approval_steps',
                    'approval_current_step',
                    'approval_completed_steps',
                ]);
            });
        }

        Schema::dropIfExists('approval_matrix_rules');
    }
};
