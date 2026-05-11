<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_offline_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('client_uuid', 120);
            $table->foreignId('processed_attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->string('status', 30)->default('queued');
            $table->string('action', 30)->nullable();
            $table->string('barcode_data');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 10, 2)->nullable();
            $table->decimal('gps_variance', 12, 8)->nullable();
            $table->timestamp('captured_at');
            $table->timestamp('synced_at')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->string('risk_level', 20)->default('low');
            $table->json('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'client_uuid'], 'uniq_offline_attendance_client_item');
            $table->index(['status', 'synced_at'], 'idx_offline_attendance_status_synced');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_offline_submissions');
    }
};
