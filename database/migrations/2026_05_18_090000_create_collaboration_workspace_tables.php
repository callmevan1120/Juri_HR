<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_threads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 32)->default('group');
            $table->string('title');
            $table->boolean('is_archived')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'type'], 'chat_threads_company_type_index');
            $table->index(['company_id', 'is_archived'], 'chat_threads_company_archived_index');
        });

        Schema::create('chat_thread_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chat_thread_id')->constrained('chat_threads')->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 32)->default('member');
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['chat_thread_id', 'user_id'], 'chat_thread_user_thread_user_unique');
            $table->index(['user_id', 'last_read_at'], 'chat_thread_user_user_read_index');
        });

        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chat_thread_id')->constrained('chat_threads')->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->string('attachment_disk')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime')->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['chat_thread_id', 'created_at'], 'chat_messages_thread_created_index');
        });

        Schema::create('cloud_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('chat_thread_id')->nullable()->constrained('chat_threads')->nullOnDelete();
            $table->foreignUlid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('visibility', 32)->default('private');
            $table->string('checksum')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'visibility'], 'cloud_files_company_visibility_index');
            $table->index(['owner_id', 'created_at'], 'cloud_files_owner_created_index');
        });

        Schema::create('online_meetings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('chat_thread_id')->nullable()->constrained('chat_threads')->nullOnDelete();
            $table->foreignUlid('host_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('provider', 40)->default('external');
            $table->string('meeting_url')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status', 32)->default('scheduled');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status', 'starts_at'], 'online_meetings_company_status_start_index');
            $table->index(['host_id', 'starts_at'], 'online_meetings_host_start_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_meetings');
        Schema::dropIfExists('cloud_files');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_thread_user');
        Schema::dropIfExists('chat_threads');
    }
};
