<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ptkp_status', 10)->default('TK/0')->after('hourly_rate');
            $table->string('bank_name')->nullable()->after('ptkp_status');
            $table->string('bank_account_name')->nullable()->after('bank_name');
            $table->string('bank_account_number')->nullable()->after('bank_account_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ptkp_status',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
            ]);
        });
    }
};
