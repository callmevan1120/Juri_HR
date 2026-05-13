<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_attendance_events', function (Blueprint $table): void {
            $table->id();
            $table->string('source', 80)->default('generic');
            $table->string('idempotency_key', 160);
            $table->string('employee_code', 120)->index();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->string('event_type', 20)->index();
            $table->timestamp('occurred_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('device_id', 120)->nullable()->index();
            $table->string('status', 20)->default('accepted')->index();
            $table->json('normalized_payload')->nullable();
            $table->json('raw_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['source', 'idempotency_key'], 'integration_attendance_events_idempotency_unique');
            $table->index(['status', 'created_at'], 'integration_attendance_events_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_attendance_events');
    }
};
