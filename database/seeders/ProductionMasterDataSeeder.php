<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Division;
use App\Models\Education;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\Setting;
use App\Models\Shift;
use Database\Factories\DivisionFactory;
use Database\Factories\EducationFactory;
use Database\Factories\JobTitleFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductionMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            WilayahSeeder::class,
            HolidaySeeder::class,
            JobLevelSeeder::class,
            PayrollComponentSeeder::class,
            KpiSeeder::class,
            EmployeeDocumentTemplateSeeder::class,
        ]);

        $this->seedOrganizationMasterData();
        $this->seedPrimaryCompanyIfNeeded();
        $this->call(AccountingMasterDataSeeder::class);
        $this->seedDefaultShifts();
    }

    private function seedOrganizationMasterData(): void
    {
        foreach (DivisionFactory::$divisions as $name) {
            Division::query()->firstOrCreate(['name' => $name]);
        }

        foreach (EducationFactory::$educations as $name) {
            Education::query()->firstOrCreate(['name' => $name]);
        }

        $jobLevelIds = JobLevel::query()->pluck('id', 'name')->all();
        $jobLevelRanks = JobLevel::query()->pluck('rank', 'name')->all();

        foreach (JobTitleFactory::$jobTitles as $name) {
            JobTitle::query()->updateOrCreate([
                'name' => $name,
            ], [
                'job_level_id' => $jobLevelIds[$name] ?? null,
                'level' => $jobLevelRanks[$name] ?? 4,
            ]);
        }
    }

    private function seedPrimaryCompanyIfNeeded(): void
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        $hasProductionCompany = Company::query()
            ->where('slug', '!=', 'paspapan-demo')
            ->exists();

        if ($hasProductionCompany) {
            return;
        }

        $companyName = trim((string) Setting::getValue('app.company_name', 'PT. PasPapan Indonesia'));
        $companyName = $companyName !== '' ? $companyName : 'PT. PasPapan Indonesia';
        $slug = Str::slug($companyName) ?: 'primary-company';
        $slug = $slug === 'paspapan-demo' ? 'primary-company' : $slug;

        Company::query()->firstOrCreate([
            'slug' => $slug,
        ], [
            'name' => $companyName,
            'status' => Company::STATUS_ACTIVE,
            'metadata' => [
                'seeded' => true,
                'source' => 'real_master_data',
                'note' => 'Initial company placeholder. Rename it from Admin Settings after production onboarding.',
            ],
        ]);
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
