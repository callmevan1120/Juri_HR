<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_endpoints', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('provider', 64)->default('custom');
            $table->json('event_keys');
            $table->string('url', 2048);
            $table->text('secret')->nullable();
            $table->json('headers')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamps();

            $table->index(['provider', 'is_active'], 'integration_endpoints_provider_active_index');
        });

        Schema::create('integration_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('integration_endpoint_id')->constrained('integration_endpoints')->cascadeOnDelete();
            $table->string('event_key', 100);
            $table->json('payload');
            $table->string('status', 32)->default('pending');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->string('signature')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['event_key', 'status'], 'integration_deliveries_event_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_deliveries');
        Schema::dropIfExists('integration_endpoints');
    }
};
