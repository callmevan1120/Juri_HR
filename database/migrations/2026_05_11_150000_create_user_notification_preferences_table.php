<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_key', 80);
            $table->json('channels');
            $table->boolean('digest_enabled')->default(false);
            $table->string('digest_frequency', 32)->nullable();
            $table->json('external_routes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'event_key'], 'user_notification_preferences_unique_event');
            $table->index(['event_key', 'digest_enabled'], 'user_notification_preferences_digest_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
