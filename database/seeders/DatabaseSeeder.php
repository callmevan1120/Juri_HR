<?php

namespace Database\Seeders;

use App\Models\Barcode;
use App\Models\Company;
use App\Models\Division;
use App\Models\Education;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\Shift;
use Database\Factories\DivisionFactory;
use Database\Factories\EducationFactory;
use Database\Factories\JobTitleFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SettingSeeder::class,
            HolidaySeeder::class,
            JobLevelSeeder::class,
            PayrollComponentSeeder::class,
            KpiSeeder::class,
            EmployeeDocumentTemplateSeeder::class,
        ]);

        foreach (DivisionFactory::$divisions as $value) {
            Division::query()->firstOrCreate(['name' => $value]);
        }

        foreach (EducationFactory::$educations as $value) {
            Education::query()->firstOrCreate(['name' => $value]);
        }

        $jobLevelIds = JobLevel::query()->pluck('id', 'name')->all();
        $jobLevelRanks = JobLevel::query()->pluck('rank', 'name')->all();

        foreach (JobTitleFactory::$jobTitles as $value) {
            JobTitle::query()->updateOrCreate([
                'name' => $value,
            ], [
                'job_level_id' => $jobLevelIds[$value] ?? null,
                'level' => $jobLevelRanks[$value] ?? 4,
            ]);
        }

        $this->seedDefaultCompany();
        $this->seedDefaultBarcodes();
        $this->seedDefaultShifts();
    }

    private function seedDefaultCompany(): void
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        Company::query()->updateOrCreate([
            'slug' => 'paspapan-demo',
        ], [
            'name' => 'PasPapan Demo',
            'status' => Company::STATUS_ACTIVE,
            'metadata' => [
                'segment' => 'Head Office',
                'seeded' => true,
            ],
        ]);
    }

    private function seedDefaultBarcodes(): void
    {
        foreach ([
            [
                'name' => 'Kantor Pusat',
                'value' => 'PASPAPAN-HQ-ATTENDANCE',
                'secret_key' => hash('sha256', 'PASPAPAN-HQ-ATTENDANCE'),
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius' => 75,
                'dynamic_enabled' => true,
                'dynamic_ttl_seconds' => 60,
            ],
            [
                'name' => 'Gudang Operasional',
                'value' => 'PASPAPAN-WAREHOUSE-ATTENDANCE',
                'secret_key' => hash('sha256', 'PASPAPAN-WAREHOUSE-ATTENDANCE'),
                'latitude' => -6.238270,
                'longitude' => 106.975570,
                'radius' => 100,
                'dynamic_enabled' => true,
                'dynamic_ttl_seconds' => 60,
            ],
            [
                'name' => 'Area Lapangan',
                'value' => 'PASPAPAN-FIELD-ATTENDANCE',
                'secret_key' => hash('sha256', 'PASPAPAN-FIELD-ATTENDANCE'),
                'latitude' => -6.302445,
                'longitude' => 106.895155,
                'radius' => 150,
                'dynamic_enabled' => true,
                'dynamic_ttl_seconds' => 60,
            ],
        ] as $barcode) {
            Barcode::query()->updateOrCreate([
                'value' => $barcode['value'],
            ], $barcode);
        }
    }

    private function seedDefaultShifts(): void
    {
        foreach ([
            ['name' => 'Shift Pagi', 'start_time' => '07:00', 'end_time' => '15:00'],
            ['name' => 'Shift Sore', 'start_time' => '15:00', 'end_time' => '23:00'],
            ['name' => 'Shift Malam', 'start_time' => '23:00', 'end_time' => '07:00'],
        ] as $shift) {
            Shift::query()->updateOrCreate([
                'name' => $shift['name'],
            ], $shift);
        }
    }
}
