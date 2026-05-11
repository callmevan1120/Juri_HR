<?php

use App\Models\Payroll;
use App\Models\User;
use App\Services\Payroll\IndonesiaPayrollCalculator;
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
        ->and($result['net_salary'])->toBe(12159577.0);
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
