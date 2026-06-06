<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personal_access_tokens') || ! Schema::hasColumn('personal_access_tokens', 'tokenable_id')) {
            return;
        }

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE CHAR(26) USING tokenable_id::text'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE personal_access_tokens MODIFY tokenable_id CHAR(26) NOT NULL'),
            'sqlite' => null,
            default => null,
        };
    }

    public function down(): void
    {
        if (! Schema::hasTable('personal_access_tokens') || ! Schema::hasColumn('personal_access_tokens', 'tokenable_id')) {
            return;
        }

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE UUID USING tokenable_id::uuid'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE personal_access_tokens MODIFY tokenable_id CHAR(36) NOT NULL'),
            'sqlite' => null,
            default => null,
        };
    }
};
