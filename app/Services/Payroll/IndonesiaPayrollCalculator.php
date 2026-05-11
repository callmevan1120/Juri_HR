<?php

namespace App\Services\Payroll;

use App\Models\User;

class IndonesiaPayrollCalculator
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function calculate(User $user, array $options = []): array
    {
        $baseSalary = (float) ($options['basic_salary'] ?? $user->basic_salary ?? 0);
        $workDays = max(1, (int) ($options['work_days'] ?? 30));
        $paidDays = max(0, min($workDays, (int) ($options['paid_days'] ?? $workDays)));
        $proratedSalary = round($baseSalary * ($paidDays / $workDays), 2);

        $fixedAllowances = $this->sumNamedAmounts($options['fixed_allowances'] ?? []);
        $variableAllowances = $this->sumNamedAmounts($options['variable_allowances'] ?? []);
        $overtimePay = (float) ($options['overtime_pay'] ?? 0);
        $thr = $this->calculateThr($baseSalary, (int) ($options['months_worked'] ?? 12), (bool) ($options['include_thr'] ?? false));

        $gross = round($proratedSalary + $fixedAllowances + $variableAllowances + $overtimePay + $thr, 2);

        $bpjs = [
            'kesehatan_employee' => TaxCalculatorService::calculateBPJSKesehatan($gross, true),
            'kesehatan_employer' => TaxCalculatorService::calculateBPJSKesehatan($gross, false),
            'jht_employee' => TaxCalculatorService::calculateBPJSKetenagakerjaanJHT($gross, true),
            'jht_employer' => TaxCalculatorService::calculateBPJSKetenagakerjaanJHT($gross, false),
            'jp_employee' => TaxCalculatorService::calculateBPJSKetenagakerjaanJP($gross, true),
            'jp_employer' => TaxCalculatorService::calculateBPJSKetenagakerjaanJP($gross, false),
        ];

        $manualDeductions = $this->sumNamedAmounts($options['deductions'] ?? []);
        $pph21 = TaxCalculatorService::calculatePPh21TER($gross, (string) ($options['ptkp_status'] ?? $user->ptkp_status ?? 'TK/0'));
        $totalDeduction = round($manualDeductions + $bpjs['kesehatan_employee'] + $bpjs['jht_employee'] + $bpjs['jp_employee'] + $pph21, 2);
        $netSalary = round($gross - $totalDeduction, 2);

        return [
            'basic_salary' => $proratedSalary,
            'gross_salary' => $gross,
            'total_allowance' => round($fixedAllowances + $variableAllowances + $overtimePay + $thr, 2),
            'total_deduction' => $totalDeduction,
            'net_salary' => $netSalary,
            'allowances' => [
                'fixed' => $options['fixed_allowances'] ?? [],
                'variable' => $options['variable_allowances'] ?? [],
                'overtime_pay' => $overtimePay,
                'thr' => $thr,
            ],
            'deductions' => [
                'manual' => $options['deductions'] ?? [],
                'bpjs' => $bpjs,
                'pph21_ter' => $pph21,
            ],
            'details' => [
                'country' => 'ID',
                'tax_method' => 'PPh21 TER',
                'ptkp_status' => (string) ($options['ptkp_status'] ?? $user->ptkp_status ?? 'TK/0'),
                'work_days' => $workDays,
                'paid_days' => $paidDays,
                'months_worked' => (int) ($options['months_worked'] ?? 12),
            ],
        ];
    }

    public function calculateThr(float $monthlySalary, int $monthsWorked, bool $includeThr = true): float
    {
        if (! $includeThr) {
            return 0.0;
        }

        return round($monthlySalary * min(max($monthsWorked, 0), 12) / 12, 2);
    }

    private function sumNamedAmounts(mixed $items): float
    {
        if (! is_array($items)) {
            return 0.0;
        }

        return round(collect($items)->sum(fn ($item): float => (float) data_get($item, 'amount', 0)), 2);
    }
}
