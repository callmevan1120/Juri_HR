<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('company_assets', function (Blueprint $table) {
            $table->date('purchase_date')->nullable()->after('type');
            $table->decimal('purchase_cost', 15, 2)->nullable()->after('purchase_date');
            $table->date('expiration_date')->nullable()->after('purchase_cost');
        });

        // Safely alter ENUM/check constraints using driver-specific SQL.
        if (DB::getDriverName() === 'pgsql') {
            $this->replacePostgresStatusConstraint([
                'available',
                'assigned',
                'maintenance',
                'lost',
                'retired',
                'sold',
                'auctioned',
                'disposed',
            ]);
        } elseif (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE company_assets MODIFY status ENUM('available', 'assigned', 'maintenance', 'lost', 'retired', 'sold', 'auctioned', 'disposed') DEFAULT 'available'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_assets', function (Blueprint $table) {
            $table->dropColumn(['purchase_date', 'purchase_cost', 'expiration_date']);
        });

        if (DB::getDriverName() === 'pgsql') {
            $this->replacePostgresStatusConstraint([
                'available',
                'assigned',
                'maintenance',
                'lost',
                'retired',
            ]);
        } elseif (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE company_assets MODIFY status ENUM('available', 'assigned', 'maintenance', 'lost', 'retired') DEFAULT 'available'");
        }
    }

    /**
     * @param  list<string>  $statuses
     */
    private function replacePostgresStatusConstraint(array $statuses): void
    {
        DB::statement('ALTER TABLE company_assets DROP CONSTRAINT IF EXISTS company_assets_status_check');

        $quotedStatuses = collect($statuses)
            ->map(fn (string $status): string => "'".str_replace("'", "''", $status)."'")
            ->implode(', ');

        DB::statement("ALTER TABLE company_assets ADD CONSTRAINT company_assets_status_check CHECK (status in ({$quotedStatuses}))");
        DB::statement("ALTER TABLE company_assets ALTER COLUMN status SET DEFAULT 'available'");
    }
};
