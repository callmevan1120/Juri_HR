<?php

use App\Models\AccountingAccount;
use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\CashAdvance;
use App\Models\Client;
use App\Models\Company;
use App\Models\CompanyBranch;
use App\Models\CustomFormSubmission;
use App\Models\CustomFormTemplate;
use App\Models\Division;
use App\Models\Invoice;
use App\Models\JobTitle;
use App\Models\JournalEntry;
use App\Models\LeaveEntitlement;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Quotation;
use App\Models\Reimbursement;
use App\Models\Role;
use App\Models\SalesOpportunity;
use App\Models\Shift;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\Wilayah;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\FakeDataSeeder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

test('database seeder is idempotent for real master data', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(Company::query()->where('metadata->source', 'real_master_data')->count())->toBe(1)
        ->and(Company::query()->where('slug', 'paspapan-demo')->exists())->toBeFalse()
        ->and(Barcode::query()->whereIn('value', [
            'PASPAPAN-HQ-ATTENDANCE',
            'PASPAPAN-WAREHOUSE-ATTENDANCE',
            'PASPAPAN-FIELD-ATTENDANCE',
        ])->exists())->toBeFalse()
        ->and(Shift::query()->whereIn('name', [
            'Shift Pagi',
            'Shift Sore',
            'Shift Malam',
        ])->count())->toBe(3)
        ->and(Wilayah::query()->count())->toBeGreaterThan(80000)
        ->and(Wilayah::query()->whereRaw('LENGTH(kode) = 2')->count())->toBeGreaterThan(30)
        ->and(Wilayah::query()->where('kode', '11')->value('nama'))->toBe('Aceh')
        ->and(AccountingAccount::query()->where('code', '1100')->exists())->toBeTrue()
        ->and(JobTitle::query()->whereIn('name', ['Head', 'Manager', 'Senior', 'Officer', 'Staff'])->whereNotNull('job_level_id')->count())->toBe(5);
});

test('fake employee seeder keeps one head and manager per division and fills employee fields', function () {
    $this->seed(DatabaseSeeder::class);

    $firstDivision = Division::query()->orderBy('name')->firstOrFail();
    $headTitle = JobTitle::query()->where('name', 'Head')->firstOrFail();
    $managerTitle = JobTitle::query()->where('name', 'Manager')->firstOrFail();
    $seniorTitle = JobTitle::query()->where('name', 'Senior')->firstOrFail();
    $officerTitle = JobTitle::query()->where('name', 'Officer')->firstOrFail();

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
            "officer.{$divisionKey}@example.com",
            "officer2.{$divisionKey}@example.com",
            "officer3.{$divisionKey}@example.com",
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
        } elseif (in_array($employee->job_title_id, [$managerTitle->id, $seniorTitle->id, $officerTitle->id], true)) {
            $this->assertNotEmpty($employee->manager_id, "Expected {$employee->email} manager_id to be filled.");
        }
    }

    expect(CompanyBranch::query()->where('code', 'JKT-HQ')->exists())->toBeTrue()
        ->and(Client::query()->where('code', 'CL-ACME')->exists())->toBeTrue()
        ->and(Project::query()->where('code', 'PRJ-OPS-001')->exists())->toBeTrue()
        ->and(ProjectTask::query()->where('title', 'Survey lokasi outlet pertama')->exists())->toBeTrue()
        ->and(Product::query()->where('sku', 'DEVICE-BUNDLE-001')->exists())->toBeTrue()
        ->and(Vendor::query()->where('name', 'PT Supplier Teknologi Nusantara')->exists())->toBeTrue()
        ->and(Quotation::query()->where('number', 'QTN-DEMO-001')->exists())->toBeTrue()
        ->and(Invoice::query()->where('number', 'INV-DEMO-001')->exists())->toBeTrue()
        ->and(VendorBill::query()->where('number', 'BILL-DEMO-001')->exists())->toBeTrue()
        ->and(SalesOpportunity::query()->where('title', 'ACME Device Rollout Q2')->exists())->toBeTrue()
        ->and(JournalEntry::query()->count())->toBeGreaterThan(0)
        ->and(CustomFormTemplate::query()->where('title', 'Bukti Kunjungan Lokasi')->exists())->toBeTrue()
        ->and(CustomFormSubmission::query()->exists())->toBeTrue()
        ->and(Reimbursement::query()->where('description', 'Demo pending taxi reimbursement for client meeting.')->exists())->toBeTrue()
        ->and(CashAdvance::query()->where('purpose', 'Demo travel advance for client visit.')->exists())->toBeTrue()
        ->and(Overtime::query()->where('reason', 'Demo urgent client rollout support.')->exists())->toBeTrue()
        ->and(Payroll::query()->where('period_type', 'monthly')->exists())->toBeTrue()
        ->and(LeaveEntitlement::query()->exists())->toBeTrue()
        ->and(Attendance::query()->where('approval_status', Attendance::STATUS_PENDING)->whereNotNull('leave_type_id')->exists())->toBeTrue();

    $demoAdmin = User::query()->where('email', 'admin123@paspapan.com')->firstOrFail();
    $demoRole = Role::query()->where('slug', 'demo_admin_readonly')->firstOrFail();
    $demoPermissions = $demoRole->permissions ?? [];

    expect($demoAdmin->roles()->pluck('slug')->all())->toBe(['demo_admin_readonly'])
        ->and($demoPermissions)->toContain('admin.dashboard.view')
        ->and($demoPermissions)->toContain('admin.employees.view')
        ->and($demoPermissions)->toContain('admin.commercial.view')
        ->and($demoPermissions)->toContain('admin.employees.manage')
        ->and(Gate::forUser($demoAdmin)->allows('viewEmployees'))->toBeTrue()
        ->and(Gate::forUser($demoAdmin)->allows('manageRbac'))->toBeTrue()
        ->and(Gate::forUser($demoAdmin)->allows('manageUserRecord', [User::query()->where('group', 'user')->first(), 'user']))->toBeTrue();
});

test('paspapan seeding commands keep real and fake data separated', function () {
    $this->artisan('paspapan:seed-real')
        ->assertSuccessful();

    expect(Company::query()->where('metadata->source', 'real_master_data')->exists())->toBeTrue()
        ->and(Company::query()->where('slug', 'paspapan-demo')->exists())->toBeFalse()
        ->and(Product::query()->where('sku', 'DEVICE-BUNDLE-001')->exists())->toBeFalse();

    $this->artisan('paspapan:seed-fake')
        ->assertSuccessful();

    expect(Wilayah::query()->count())->toBeGreaterThan(80000)
        ->and(JobTitle::query()->whereIn('name', ['Head', 'Manager', 'Senior', 'Officer', 'Staff'])->whereNotNull('job_level_id')->count())->toBe(5)
        ->and(Barcode::query()->whereIn('value', [
            'PASPAPAN-HQ-ATTENDANCE',
            'PASPAPAN-WAREHOUSE-ATTENDANCE',
            'PASPAPAN-FIELD-ATTENDANCE',
        ])->count())->toBe(3)
        ->and(Product::query()->where('sku', 'DEVICE-BUNDLE-001')->exists())->toBeTrue()
        ->and(CustomFormTemplate::query()->where('title', 'Bukti Kunjungan Lokasi')->exists())->toBeTrue();
});

test('real master seeding command keeps destructive refresh outside routine automation', function () {
    $this->artisan('paspapan:seed-real --help')
        ->expectsOutputToContain('Seed production-safe master data only. This command is idempotent; destructive refresh belongs in a separate approved runbook.')
        ->doesntExpectOutputToContain('refresh-wilayah')
        ->assertSuccessful();
});
