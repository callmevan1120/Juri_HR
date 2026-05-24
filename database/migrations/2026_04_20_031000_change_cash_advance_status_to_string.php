<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_advances', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cash_advances DROP CONSTRAINT IF EXISTS cash_advances_status_check');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cash_advances DROP CONSTRAINT IF EXISTS cash_advances_status_check');
        }

        Schema::table('cash_advances', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending')->change();
        });
    }
};
