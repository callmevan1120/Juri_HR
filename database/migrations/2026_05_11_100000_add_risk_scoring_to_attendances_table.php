<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedTinyInteger('risk_score')->default(0)->after('suspicious_reason');
            $table->string('risk_level', 20)->default('low')->after('risk_score');
            $table->json('risk_factors')->nullable()->after('risk_level');
            $table->timestamp('risk_evaluated_at')->nullable()->after('risk_factors');

            $table->index(['risk_level', 'risk_score'], 'idx_attendances_risk_level_score');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_risk_level_score');
            $table->dropColumn([
                'risk_score',
                'risk_level',
                'risk_factors',
                'risk_evaluated_at',
            ]);
        });
    }
};
