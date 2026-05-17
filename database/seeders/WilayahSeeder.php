<?php

namespace Database\Seeders;

use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class WilayahSeeder extends Seeder
{
    private const COMPLETE_DATA_THRESHOLD = 80000;

    public function run(): void
    {
        if (! Schema::hasTable('wilayah')) {
            $this->command?->warn('Wilayah table does not exist yet. Skipping wilayah seed.');

            return;
        }

        $path = database_path('data/wilayah.sql.gz');

        if (! File::exists($path)) {
            $this->command?->error("Wilayah data file not found at {$path}");

            return;
        }

        $existingCount = Wilayah::query()->count();
        $refresh = filter_var(config('paspapan.wilayah_seed_refresh', false), FILTER_VALIDATE_BOOL);

        if ($existingCount >= self::COMPLETE_DATA_THRESHOLD && ! $refresh) {
            $this->command?->info("Wilayah data already looks complete ({$existingCount} rows). Set WILAYAH_SEED_REFRESH=true to refresh it.");

            return;
        }

        $this->command?->info('Extracting wilayah.sql.gz and importing wilayah master data...');

        $sql = gzdecode(File::get($path));

        if ($sql === false) {
            $this->command?->error('Failed to extract gzip file.');

            return;
        }

        if ($refresh) {
            Wilayah::query()->delete();
        }

        $imported = 0;

        foreach ($this->extractRows($sql) as $rows) {
            DB::table('wilayah')->upsert($rows, ['kode'], ['nama']);
            $imported += count($rows);
        }

        $total = Wilayah::query()->count();

        $this->command?->info("Wilayah table seeded successfully. Parsed {$imported} rows; table now has {$total} rows.");
    }

    /**
     * @return iterable<int, array<int, array{kode:string,nama:string}>>
     */
    private function extractRows(string $sql): iterable
    {
        preg_match_all('/INSERT INTO wilayah \(kode, nama\)\s*VALUES\s*(.*?);/s', $sql, $statements);

        $chunk = [];

        foreach ($statements[1] as $values) {
            preg_match_all("/\\('((?:[^']|'')*)','((?:[^']|'')*)'\\)/", (string) $values, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $chunk[] = [
                    'kode' => str_replace("''", "'", $match[1]),
                    'nama' => str_replace("''", "'", $match[2]),
                ];

                if (count($chunk) >= 1000) {
                    yield $chunk;
                    $chunk = [];
                }
            }
        }

        if ($chunk !== []) {
            yield $chunk;
        }
    }
}
