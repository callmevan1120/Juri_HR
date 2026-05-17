<?php

use App\Exports\PayrollWorkbookExport;
use App\Models\Payroll;
use App\Models\User;
use App\Services\Payroll\IndonesiaPayrollCalculator;
use App\Services\Payroll\PayrollPeriodService;
use App\Support\CoretaxPayrollExportService;
use App\Support\PayrollPaymentInstructionService;

test('indonesia payroll calculator includes prorata thr bpjs and pph21 ter', function () {
    $user = User::factory()->create([
        'basic_salary' => 12000000,
        'ptkp_status' => 'TK/0',
    ]);

    $result = app(IndonesiaPayrollCalculator::class)->calculate($user, [
        'work_days' => 30,
        'paid_days' => 15,
        'fixed_allowances' => [
            ['name' => 'Transport', 'amount' => 500000],
        ],
        'variable_allowances' => [
            ['name' => 'Meal', 'amount' => 300000],
        ],
        'deductions' => [
            ['name' => 'Loan', 'amount' => 100000],
        ],
        'overtime_pay' => 200000,
        'include_thr' => true,
        'months_worked' => 6,
    ]);

    expect($result['basic_salary'])->toBe(6000000.0)
        ->and($result['allowances']['thr'])->toBe(6000000.0)
        ->and($result['gross_salary'])->toBe(13000000.0)
        ->and($result['deductions']['bpjs']['kesehatan_employee'])->toBe(120000.0)
        ->and($result['deductions']['bpjs']['jht_employee'])->toBe(260000.0)
        ->and($result['deductions']['bpjs']['jp_employee'])->toBe(100423.0)
        ->and($result['deductions']['pph21_ter'])->toBe(260000.0)
        ->and($result['net_salary'])->toBe(12159577.0)
        ->and($result['details']['taxable_income'])->toBe(13000000.0)
        ->and($result['details']['bpjs_employee_total'])->toBe(480423.0)
        ->and($result['details']['bpjs_employer_total'])->toBe(1161846.0);
});

test('payroll period service supports monthly weekly and daily payroll windows', function () {
    $service = app(PayrollPeriodService::class);

    expect($service->resolve('monthly', 2026, 5))->toMatchArray([
        'period_type' => 'monthly',
        'period_start' => '2026-05-01',
        'period_end' => '2026-05-31',
        'period_sequence' => 1,
        'work_days' => 31,
    ])
        ->and($service->resolve('weekly', 2026, 5, 2))->toMatchArray([
            'period_type' => 'weekly',
            'period_start' => '2026-05-08',
            'period_end' => '2026-05-14',
            'period_sequence' => 2,
            'work_days' => 7,
        ])
        ->and($service->resolve('daily', 2026, 5, 17))->toMatchArray([
            'period_type' => 'daily',
            'period_start' => '2026-05-17',
            'period_end' => '2026-05-17',
            'period_sequence' => 17,
            'work_days' => 1,
        ]);
});

test('indonesia payroll separates taxable and non taxable income for coretax rows', function () {
    $user = User::factory()->create([
        'nip' => 'TAX001',
        'basic_salary' => 10000000,
        'ptkp_status' => 'K/0',
    ]);

    $result = app(IndonesiaPayrollCalculator::class)->calculate($user, [
        'period_type' => 'weekly',
        'year' => 2026,
        'month' => 5,
        'period_sequence' => 1,
        'paid_days' => 7,
        'fixed_allowances' => [
            ['name' => 'Tunjangan Jabatan', 'amount' => 1000000, 'taxable' => true],
            ['name' => 'Uang Makan Reimburse', 'amount' => 500000, 'taxable' => false],
        ],
        'variable_allowances' => [
            ['name' => 'Insentif', 'amount' => 250000, 'taxable' => true],
        ],
        'bpjs_wage_base' => 10000000,
    ]);

    $payroll = Payroll::create([
        'user_id' => $user->id,
        'type' => 'regular',
        'period_type' => $result['details']['period_type'],
        'month' => 5,
        'year' => 2026,
        'period_start' => $result['details']['period_start'],
        'period_end' => $result['details']['period_end'],
        'period_sequence' => $result['details']['period_sequence'],
        'basic_salary' => $result['basic_salary'],
        'allowances' => $result['allowances'],
        'deductions' => $result['deductions'],
        'overtime_pay' => 0,
        'total_allowance' => $result['total_allowance'],
        'total_deduction' => $result['total_deduction'],
        'net_salary' => $result['net_salary'],
        'taxable_income' => $result['details']['taxable_income'],
        'non_taxable_income' => $result['details']['non_taxable_income'],
        'pph21_tax' => $result['deductions']['pph21_ter'],
        'bpjs_employee_total' => $result['details']['bpjs_employee_total'],
        'bpjs_employer_total' => $result['details']['bpjs_employer_total'],
        'employer_contribution_total' => $result['details']['employer_contribution_total'],
        'details' => $result['details'],
        'coretax_payload' => $result['details']['coretax'],
        'status' => 'published',
    ]);

    $rows = app(CoretaxPayrollExportService::class)->rows(Payroll::query()->whereKey($payroll->id)->get());

    expect($result['details']['period_type'])->toBe('weekly')
        ->and($result['details']['period_start'])->toBe('2026-05-01')
        ->and($result['details']['period_end'])->toBe('2026-05-07')
        ->and($result['details']['taxable_income'])->toBe(11250000.0)
        ->and($result['details']['non_taxable_income'])->toBe(500000.0)
        ->and($rows[0]['employee_nip'])->toBe('TAX001')
        ->and($rows[0]['period_type'])->toBe('weekly')
        ->and($rows[0]['taxable_income'])->toBe(11250000.0)
        ->and($rows[0]['non_taxable_income'])->toBe(500000.0)
        ->and($rows[0]['pph21'])->toBe($result['deductions']['pph21_ter']);
});

test('payroll payment instruction rows include employee bank destination and reference', function () {
    $user = User::factory()->create([
        'nip' => 'EMP001',
        'bank_name' => 'Bank Contoh',
        'bank_account_name' => 'Budi Payroll',
        'bank_account_number' => '1234567890',
    ]);

    $payroll = Payroll::create([
        'user_id' => $user->id,
        'month' => 5,
        'year' => 2026,
        'basic_salary' => 5000000,
        'net_salary' => 4750000,
        'status' => 'published',
    ]);

    $rows = app(PayrollPaymentInstructionService::class)->rows(Payroll::query()->whereKey($payroll->id)->get());

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['bank_name'])->toBe('Bank Contoh')
        ->and($rows[0]['bank_account_number'])->toBe('1234567890')
        ->and($rows[0]['amount'])->toBe(4750000.0)
        ->and($rows[0]['reference'])->toBe('PAY-202605-EMP001');
});

test('payroll workbook includes summary payment instruction and coretax sheets', function () {
    $admin = User::factory()->admin(true)->create();
    $employee = User::factory()->create([
        'nip' => 'EMP-CORETAX',
        'bank_name' => 'Bank Payroll',
        'bank_account_name' => 'Sari Payroll',
        'bank_account_number' => '9876543210',
        'ptkp_status' => 'TK/0',
    ]);

    Payroll::create([
        'user_id' => $employee->id,
        'type' => 'regular',
        'period_type' => 'monthly',
        'month' => 5,
        'year' => 2026,
        'period_start' => '2026-05-01',
        'period_end' => '2026-05-31',
        'period_sequence' => 1,
        'basic_salary' => 5000000,
        'allowances' => [],
        'deductions' => ['pph21_ter' => 25000],
        'overtime_pay' => 0,
        'total_allowance' => 0,
        'total_deduction' => 25000,
        'net_salary' => 4975000,
        'taxable_income' => 5000000,
        'non_taxable_income' => 0,
        'pph21_tax' => 25000,
        'bpjs_employee_total' => 200000,
        'bpjs_employer_total' => 500000,
        'employer_contribution_total' => 500000,
        'details' => [
            'coretax' => [
                'gross_income' => 5000000,
                'taxable_income' => 5000000,
                'pph21' => 25000,
            ],
        ],
        'status' => 'paid',
        'generated_by' => $admin->id,
        'paid_at' => now(),
    ]);

    $sheets = (new PayrollWorkbookExport($admin, [
        'month' => 5,
        'year' => 2026,
        'status' => 'paid',
    ]))->sheets();

    expect($sheets)->toHaveCount(3)
        ->and($sheets[0]->title())->toBe('Payroll Summary')
        ->and($sheets[1]->title())->toBe('Payment Instructions')
        ->and($sheets[2]->title())->toBe('Coretax PPh21')
        ->and($sheets[1]->array()[1][3])->toBe('Bank Payroll')
        ->and($sheets[2]->array()[1][8])->toBe(5000000.0);
});
