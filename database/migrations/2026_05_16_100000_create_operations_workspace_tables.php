<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_branches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('type', 40)->default('branch');
            $table->string('status', 32)->default('active');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('radius_meters')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code'], 'company_branches_company_code_unique');
            $table->index(['company_id', 'status'], 'company_branches_company_status_index');
        });

        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('status', 32)->default('active');
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code'], 'clients_company_code_unique');
            $table->index(['company_id', 'status'], 'clients_company_status_index');
        });

        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('company_branches')->nullOnDelete();
            $table->foreignUlid('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('status', 32)->default('active');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code'], 'projects_company_code_unique');
            $table->index(['company_id', 'status'], 'projects_company_status_index');
        });

        Schema::create('project_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUlid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('status', 32)->default('todo');
            $table->string('priority', 32)->default('normal');
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'project_tasks_company_status_index');
            $table->index(['assigned_to', 'status'], 'project_tasks_assignee_status_index');
        });

        Schema::create('project_task_checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_done')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_visit_evidences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('project_task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('visited_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('accuracy_meters')->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_disk')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('photo_original_name')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'visited_at'], 'project_visits_company_visited_index');
            $table->index(['user_id', 'visited_at'], 'project_visits_user_visited_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_visit_evidences');
        Schema::dropIfExists('project_task_checklist_items');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('company_branches');
    }
};
