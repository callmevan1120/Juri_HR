<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_from_home_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location_address')->nullable();
            $table->text('reason');
            $table->string('status', 32)->default('pending');
            $table->foreignUlid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'wfh_requests_company_status_index');
            $table->index(['user_id', 'date'], 'wfh_requests_user_date_index');
            $table->index(['reviewed_by', 'reviewed_at'], 'wfh_requests_reviewer_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_from_home_requests');
    }
};
