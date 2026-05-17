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
        $period = $this->resolvePeriod($options);
        $workDays = max(1, (int) ($options['work_days'] ?? $period['work_days']));
        $paidDays = max(0, min($workDays, (int) ($options['paid_days'] ?? $workDays)));
        $proratedSalary = round($baseSalary * ($paidDays / $workDays), 2);

        $fixedAllowanceItems = $options['fixed_allowances'] ?? [];
        $variableAllowanceItems = $options['variable_allowances'] ?? [];
        $fixedAllowances = $this->sumNamedAmounts($fixedAllowanceItems);
        $variableAllowances = $this->sumNamedAmounts($variableAllowanceItems);
        $overtimePay = (float) ($options['overtime_pay'] ?? 0);
        $thr = $this->calculateThr($baseSalary, (int) ($options['months_worked'] ?? 12), (bool) ($options['include_thr'] ?? false));

        $gross = round($proratedSalary + $fixedAllowances + $variableAllowances + $overtimePay + $thr, 2);
        $nonTaxableIncome = round($this->sumNamedAmounts($this->nonTaxableItems($fixedAllowanceItems)) + $this->sumNamedAmounts($this->nonTaxableItems($variableAllowanceItems)), 2);
        $taxableAllowances = round($fixedAllowances + $variableAllowances - $nonTaxableIncome, 2);
        $taxableIncome = round($proratedSalary + $taxableAllowances + $overtimePay + $thr, 2);
        $bpjsWageBase = round((float) ($options['bpjs_wage_base'] ?? $gross), 2);

        $bpjs = [
            'kesehatan_employee' => TaxCalculatorService::calculateBPJSKesehatan($bpjsWageBase, true),
            'kesehatan_employer' => TaxCalculatorService::calculateBPJSKesehatan($bpjsWageBase, false),
            'jht_employee' => TaxCalculatorService::calculateBPJSKetenagakerjaanJHT($bpjsWageBase, true),
            'jht_employer' => TaxCalculatorService::calculateBPJSKetenagakerjaanJHT($bpjsWageBase, false),
            'jp_employee' => TaxCalculatorService::calculateBPJSKetenagakerjaanJP($bpjsWageBase, true),
            'jp_employer' => TaxCalculatorService::calculateBPJSKetenagakerjaanJP($bpjsWageBase, false),
        ];

        $manualDeductions = $this->sumNamedAmounts($options['deductions'] ?? []);
        $ptkpStatus = (string) ($options['ptkp_status'] ?? $user->ptkp_status ?? 'TK/0');
        $pph21 = TaxCalculatorService::calculatePPh21TER($taxableIncome, $ptkpStatus);
        $bpjsEmployeeTotal = round($bpjs['kesehatan_employee'] + $bpjs['jht_employee'] + $bpjs['jp_employee'], 2);
        $bpjsEmployerTotal = round($bpjs['kesehatan_employer'] + $bpjs['jht_employer'] + $bpjs['jp_employer'], 2);
        $totalDeduction = round($manualDeductions + $bpjsEmployeeTotal + $pph21, 2);
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
                'ptkp_status' => $ptkpStatus,
                'taxable_income' => $taxableIncome,
                'non_taxable_income' => $nonTaxableIncome,
                'bpjs_wage_base' => $bpjsWageBase,
                'bpjs_employee_total' => $bpjsEmployeeTotal,
                'bpjs_employer_total' => $bpjsEmployerTotal,
                'employer_contribution_total' => $bpjsEmployerTotal,
                'work_days' => $workDays,
                'paid_days' => $paidDays,
                'months_worked' => (int) ($options['months_worked'] ?? 12),
                'period_type' => $period['period_type'],
                'period_start' => $period['period_start'],
                'period_end' => $period['period_end'],
                'period_sequence' => $period['period_sequence'],
                'coretax' => [
                    'employee_name' => $user->name,
                    'employee_nip' => $user->nip,
                    'period' => $period['period_start'].'/'.$period['period_end'],
                    'gross_income' => $gross,
                    'taxable_income' => $taxableIncome,
                    'pph21' => $pph21,
                ],
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

    /**
     * @param  array<string, mixed>  $options
     * @return array{period_type: string, period_start: string, period_end: string, period_sequence: int, work_days: int}
     */
    private function resolvePeriod(array $options): array
    {
        if (isset($options['period_start'], $options['period_end'])) {
            $start = (string) $options['period_start'];
            $end = (string) $options['period_end'];

            return [
                'period_type' => (string) ($options['period_type'] ?? 'custom'),
                'period_start' => $start,
                'period_end' => $end,
                'period_sequence' => (int) ($options['period_sequence'] ?? 1),
                'work_days' => (int) ($options['work_days'] ?? 30),
            ];
        }

        return app(PayrollPeriodService::class)->resolve(
            (string) ($options['period_type'] ?? 'monthly'),
            (int) ($options['year'] ?? now()->year),
            (int) ($options['month'] ?? now()->month),
            isset($options['period_sequence']) ? (int) $options['period_sequence'] : null,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function nonTaxableItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return collect($items)
            ->filter(fn ($item): bool => data_get($item, 'taxable', true) === false)
            ->values()
            ->all();
    }
}
