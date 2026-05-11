<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_log_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type');
            $table->string('entity_id')->nullable();
            $table->string('field');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->json('metadata')->nullable();
            $table->string('integrity_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id'], 'idx_activity_log_details_entity');
            $table->index(['field', 'created_at'], 'idx_activity_log_details_field_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log_details');
    }
};
