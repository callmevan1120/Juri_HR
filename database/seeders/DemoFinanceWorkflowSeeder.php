<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\CashAdvance;
use App\Models\Company;
use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Reimbursement;
use App\Models\User;
use App\Support\AccountingWorkspaceService;
use Database\Seeders\Concerns\GuardsDemoSeeding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoFinanceWorkflowSeeder extends Seeder
{
    use GuardsDemoSeeding;

    public function run(): void
    {
        if ($this->shouldSkipDemoSeeding() || ! $this->hasRequiredTables()) {
            return;
        }

        $company = $this->company();
        $actor = $this->actor();
        $employee = $this->employee($company);

        if (! $employee) {
            $this->command?->warn('No employee found for demo finance workflows. Run FakeDataSeeder first.');

            return;
        }

        $this->seedLeaveData($company, $employee, $actor);
        $this->seedOvertime($employee, $actor);
        $this->seedCashAdvances($employee, $actor);
        $this->seedReimbursements($employee, $actor);
        $this->seedPayroll($employee, $actor);
    }

    private function hasRequiredTables(): bool
    {
        return collect([
            'companies',
            'users',
            'leave_types',
            'leave_entitlements',
            'attendances',
            'overtimes',
            'cash_advances',
            'reimbursements',
            'payrolls',
        ])->every(fn (string $table): bool => Schema::hasTable($table));
    }

    private function company(): Company
    {
        return Company::query()->firstOrCreate([
            'slug' => 'paspapan-demo',
        ], [
            'name' => 'PasPapan Demo',
            'status' => Company::STATUS_ACTIVE,
            'metadata' => ['seeded' => true],
        ]);
    }

    private function actor(): ?User
    {
        return User::query()->whereIn('group', ['superadmin', 'admin'])->first()
            ?? User::query()->first();
    }

    private function employee(Company $company): ?User
    {
        return User::query()
            ->where('company_id', $company->id)
            ->where('group', 'user')
            ->orderBy('email')
            ->first();
    }

    private function seedLeaveData(Company $company, User $employee, ?User $actor): void
    {
        $annual = LeaveType::query()->where('code', 'annual_leave')->first();
        $sick = LeaveType::query()->where('code', 'sick_leave')->first();

        if ($annual) {
            LeaveEntitlement::query()->updateOrCreate([
                'user_id' => $employee->id,
                'leave_type_id' => $annual->id,
                'year' => now()->year,
            ], [
                'company_id' => $company->id,
                'allocated_days' => 12,
                'carried_over_days' => 2,
                'expires_at' => now()->endOfYear()->toDateString(),
                'notes' => 'Demo annual leave entitlement.',
                'metadata' => ['seeded' => true],
            ]);

            Attendance::query()->updateOrCreate([
                'user_id' => $employee->id,
                'date' => now()->addDays(4)->toDateString(),
                'leave_type_id' => $annual->id,
            ], [
                'status' => 'excused',
                'note' => 'Demo pending annual leave request.',
                'approval_status' => Attendance::STATUS_PENDING,
            ]);
        }

        if ($sick) {
            Attendance::query()->updateOrCreate([
                'user_id' => $employee->id,
                'date' => now()->subDays(5)->toDateString(),
                'leave_type_id' => $sick->id,
            ], [
                'status' => 'sick',
                'note' => 'Demo approved sick leave.',
                'approval_status' => Attendance::STATUS_APPROVED,
                'approved_by' => $actor?->id,
                'approved_at' => now()->subDays(4),
            ]);
        }
    }

    private function seedOvertime(User $employee, ?User $actor): void
    {
        Overtime::query()->updateOrCreate([
            'user_id' => $employee->id,
            'date' => now()->subDay()->toDateString(),
            'reason' => 'Demo urgent client rollout support.',
        ], [
            'start_time' => now()->subDay()->setTime(18, 0),
            'end_time' => now()->subDay()->setTime(20, 30),
            'duration' => 150,
            'status' => 'pending',
            'approved_by' => null,
            'rejection_reason' => null,
        ]);

        Overtime::query()->updateOrCreate([
            'user_id' => $employee->id,
            'date' => now()->subDays(8)->toDateString(),
            'reason' => 'Demo month-end closing support.',
        ], [
            'start_time' => now()->subDays(8)->setTime(18, 30),
            'end_time' => now()->subDays(8)->setTime(21, 0),
            'duration' => 150,
            'status' => 'approved',
            'approved_by' => $actor?->id,
            'rejection_reason' => null,
        ]);
    }

    private function seedCashAdvances(User $employee, ?User $actor): void
    {
        CashAdvance::query()->updateOrCreate([
            'user_id' => $employee->id,
            'purpose' => 'Demo travel advance for client visit.',
            'payment_month' => now()->month,
            'payment_year' => now()->year,
        ], [
            'amount' => 1500000,
            'status' => 'pending',
        ]);

        CashAdvance::query()->updateOrCreate([
            'user_id' => $employee->id,
            'purpose' => 'Demo approved equipment purchase advance.',
            'payment_month' => now()->subMonth()->month,
            'payment_year' => now()->subMonth()->year,
        ], [
            'amount' => 750000,
            'status' => 'approved',
            'approved_by' => $actor?->id,
            'approved_at' => now()->subWeeks(2),
            'finance_approved_by' => $actor?->id,
            'finance_approved_at' => now()->subWeeks(2),
        ]);
    }

    private function seedReimbursements(User $employee, ?User $actor): void
    {
        Reimbursement::query()->updateOrCreate([
            'user_id' => $employee->id,
            'date' => now()->subDays(2)->toDateString(),
            'type' => 'Transport',
            'description' => 'Demo pending taxi reimbursement for client meeting.',
        ], [
            'amount' => 185000,
            'status' => 'pending',
        ]);

        $approved = Reimbursement::query()->updateOrCreate([
            'user_id' => $employee->id,
            'date' => now()->subDays(10)->toDateString(),
            'type' => 'Operational',
            'description' => 'Demo approved field supplies reimbursement.',
        ], [
            'amount' => 420000,
            'status' => 'approved',
            'approved_by' => $actor?->id,
            'finance_approved_by' => $actor?->id,
            'finance_approved_at' => now()->subDays(9),
        ]);

        if ($actor) {
            app(AccountingWorkspaceService::class)->postReimbursement($actor, $approved->fresh(['user']));
        }
    }

    private function seedPayroll(User $employee, ?User $actor): void
    {
        $basicSalary = (float) ($employee->basic_salary ?: 5000000);
        $allowances = [
            ['name' => 'Tunjangan Makan', 'amount' => 750000],
            ['name' => 'Tunjangan Transport', 'amount' => 500000],
        ];
        $deductions = [
            ['name' => 'BPJS Karyawan', 'amount' => 200000],
            ['name' => 'PPh 21 TER', 'amount' => 150000],
        ];

        Payroll::query()->updateOrCreate([
            'user_id' => $employee->id,
            'type' => 'regular',
            'period_type' => 'monthly',
            'month' => now()->month,
            'year' => now()->year,
        ], [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'period_sequence' => 1,
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'overtime_pay' => 250000,
            'total_allowance' => 1250000,
            'total_deduction' => 350000,
            'net_salary' => $basicSalary + 1250000 + 250000 - 350000,
            'taxable_income' => $basicSalary + 1250000 + 250000,
            'non_taxable_income' => 0,
            'pph21_tax' => 150000,
            'bpjs_employee_total' => 200000,
            'bpjs_employer_total' => 250000,
            'employer_contribution_total' => 250000,
            'details' => ['seeded' => true, 'source' => 'demo_finance_workflow'],
            'coretax_payload' => ['status' => 'demo'],
            'status' => 'draft',
            'generated_by' => $actor?->id,
        ]);
    }
}
