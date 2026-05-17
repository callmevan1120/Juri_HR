<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_entitlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('allocated_days', 8, 2)->default(0);
            $table->decimal('carried_over_days', 8, 2)->default(0);
            $table->date('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'leave_type_id', 'year'], 'leave_entitlements_user_type_year_unique');
            $table->index(['company_id', 'year'], 'leave_entitlements_company_year_index');
            $table->index(['leave_type_id', 'expires_at'], 'leave_entitlements_type_expiry_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_entitlements');
    }
};
