<?php

use App\Models\Barcode;
use App\Models\Company;
use App\Models\Division;
use App\Models\JobTitle;
use App\Models\Shift;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\FakeDataSeeder;
use Illuminate\Support\Str;

test('database seeder is idempotent for real master data', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(Company::query()->where('slug', 'paspapan-demo')->count())->toBe(1)
        ->and(Barcode::query()->whereIn('value', [
            'PASPAPAN-HQ-ATTENDANCE',
            'PASPAPAN-WAREHOUSE-ATTENDANCE',
            'PASPAPAN-FIELD-ATTENDANCE',
        ])->count())->toBe(3)
        ->and(Shift::query()->whereIn('name', [
            'Shift Pagi',
            'Shift Sore',
            'Shift Malam',
        ])->count())->toBe(3)
        ->and(JobTitle::query()->whereIn('name', ['Head', 'Manager', 'Senior', 'Staff'])->whereNotNull('job_level_id')->count())->toBe(4);
});

test('fake employee seeder keeps one head and manager per division and fills employee fields', function () {
    $this->seed(DatabaseSeeder::class);

    $firstDivision = Division::query()->orderBy('name')->firstOrFail();
    $headTitle = JobTitle::query()->where('name', 'Head')->firstOrFail();
    $managerTitle = JobTitle::query()->where('name', 'Manager')->firstOrFail();
    $seniorTitle = JobTitle::query()->where('name', 'Senior')->firstOrFail();
    $staffTitle = JobTitle::query()->where('name', 'Staff')->firstOrFail();

    User::factory()->create([
        'email' => 'duplicate.head@example.com',
        'division_id' => $firstDivision->id,
        'job_title_id' => $headTitle->id,
        'group' => 'user',
    ]);
    User::factory()->create([
        'email' => 'duplicate.manager@example.com',
        'division_id' => $firstDivision->id,
        'job_title_id' => $managerTitle->id,
        'group' => 'user',
    ]);

    $this->seed(FakeDataSeeder::class);
    $this->seed(FakeDataSeeder::class);

    $seededEmails = ['user@example.com', 'user123@paspapan.com'];

    foreach (Division::query()->orderBy('name')->get() as $division) {
        $this->assertSame(1, User::query()
            ->where('group', 'user')
            ->where('division_id', $division->id)
            ->where('job_title_id', $headTitle->id)
            ->count());
        $this->assertSame(1, User::query()
            ->where('group', 'user')
            ->where('division_id', $division->id)
            ->where('job_title_id', $managerTitle->id)
            ->count());

        $divisionKey = Str::slug($division->name, '');
        array_push(
            $seededEmails,
            "head.{$divisionKey}@example.com",
            "manager.{$divisionKey}@example.com",
            "senior.{$divisionKey}@example.com",
            "staff.{$divisionKey}@example.com",
            "staff2.{$divisionKey}@example.com",
            "staff3.{$divisionKey}@example.com",
        );

        $head = User::query()
            ->where('division_id', $division->id)
            ->where('job_title_id', $headTitle->id)
            ->firstOrFail();
        $manager = User::query()
            ->where('division_id', $division->id)
            ->where('job_title_id', $managerTitle->id)
            ->firstOrFail();

        $this->assertNull($head->manager_id);
        $this->assertSame($head->id, $manager->manager_id);
    }

    $seededEmployees = User::query()
        ->whereIn('email', $seededEmails)
        ->get();

    $this->assertCount(count($seededEmails), $seededEmployees);

    foreach ($seededEmployees as $employee) {
        foreach ([
            'nip',
            'company_id',
            'name',
            'email',
            'phone',
            'gender',
            'birth_date',
            'birth_place',
            'address',
            'provinsi_kode',
            'kabupaten_kode',
            'kecamatan_kode',
            'kelurahan_kode',
            'education_id',
            'division_id',
            'job_title_id',
            'language',
            'basic_salary',
            'hourly_rate',
            'ptkp_status',
            'bank_name',
            'bank_account_name',
            'bank_account_number',
            'payslip_password',
            'payslip_password_set_at',
            'employment_status',
        ] as $field) {
            $this->assertNotEmpty($employee->{$field}, "Expected {$employee->email} {$field} to be filled.");
        }

        if ($employee->job_title_id === $headTitle->id) {
            $this->assertNull($employee->manager_id);
        } elseif (in_array($employee->job_title_id, [$managerTitle->id, $seniorTitle->id, $staffTitle->id], true)) {
            $this->assertNotEmpty($employee->manager_id, "Expected {$employee->email} manager_id to be filled.");
        }
    }
});
