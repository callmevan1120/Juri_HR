<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            $this->replacePostgresCheckConstraint([
                'present',
                'late',
                'leave',
                'excused',
                'sick',
                'absent',
                'rejected',
            ]);

            return;
        }

        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'late', 'leave', 'excused', 'sick', 'absent', 'rejected') DEFAULT 'absent'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            $this->replacePostgresCheckConstraint([
                'present',
                'late',
                'leave',
                'excused',
                'sick',
                'absent',
            ]);

            return;
        }

        // CAUTION: Reverting this might fail if there are 'rejected' values in the database.
        // We generally don't revert enum expansions in a way that truncates data, but for completeness:
        // DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'late', 'excused', 'sick', 'absent') DEFAULT 'absent'");

        // Safer to just leave it or handle specific revert logic if needed.
        // For now we will allow reverting to the original enum list.
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'late', 'leave', 'excused', 'sick', 'absent') DEFAULT 'absent'");
    }

    /**
     * @param  list<string>  $statuses
     */
    private function replacePostgresCheckConstraint(array $statuses): void
    {
        DB::statement('ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_status_check');

        $quotedStatuses = collect($statuses)
            ->map(fn (string $status): string => "'".str_replace("'", "''", $status)."'")
            ->implode(', ');

        DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_status_check CHECK (status in ({$quotedStatuses}))");
        DB::statement("ALTER TABLE attendances ALTER COLUMN status SET DEFAULT 'absent'");
    }
};
