<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status', 32)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            $table->index(['company_id', 'group'], 'users_company_group_index');
        });

        Schema::table('settings', function (Blueprint $table): void {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->cascadeOnDelete();
            $table->index(['company_id', 'key'], 'settings_company_key_index');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropIndex('settings_company_key_index');
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_company_group_index');
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::dropIfExists('companies');
    }
};
