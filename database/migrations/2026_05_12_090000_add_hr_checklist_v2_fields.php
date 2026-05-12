<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_checklist_templates', function (Blueprint $table): void {
            $table->foreignId('division_id')->nullable()->after('description')->constrained('divisions')->nullOnDelete();
            $table->foreignId('job_title_id')->nullable()->after('division_id')->constrained('job_titles')->nullOnDelete();
            $table->index(['type', 'division_id', 'job_title_id', 'is_active'], 'hr_templates_scope_index');
        });

        Schema::table('hr_checklist_tasks', function (Blueprint $table): void {
            $table->foreignId('depends_on_task_id')->nullable()->after('template_item_id')->constrained('hr_checklist_tasks')->nullOnDelete();
            $table->string('attachment_path')->nullable()->after('notes');
            $table->string('attachment_original_name')->nullable()->after('attachment_path');
            $table->timestamp('attachment_uploaded_at')->nullable()->after('attachment_original_name');
            $table->index(['status', 'due_date'], 'hr_tasks_reminder_index');
        });
    }

    public function down(): void
    {
        Schema::table('hr_checklist_tasks', function (Blueprint $table): void {
            $table->dropIndex('hr_tasks_reminder_index');
            $table->dropConstrainedForeignId('depends_on_task_id');
            $table->dropColumn([
                'attachment_path',
                'attachment_original_name',
                'attachment_uploaded_at',
            ]);
        });

        Schema::table('hr_checklist_templates', function (Blueprint $table): void {
            $table->dropIndex('hr_templates_scope_index');
            $table->dropConstrainedForeignId('division_id');
            $table->dropConstrainedForeignId('job_title_id');
        });
    }
};
