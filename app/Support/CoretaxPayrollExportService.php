<?php

namespace App\Support;

use App\Models\Payroll;
use Illuminate\Database\Eloquent\Collection;

class CoretaxPayrollExportService
{
    /**
     * @param  Collection<int, Payroll>  $payrolls
     * @return list<array<string, mixed>>
     */
    public function rows(Collection $payrolls): array
    {
        return $payrolls
            ->loadMissing('user')
            ->map(fn (Payroll $payroll): array => [
                'payroll_id' => $payroll->id,
                'employee_name' => $payroll->user?->name,
                'employee_nip' => $payroll->user?->nip,
                'ptkp_status' => $payroll->user?->ptkp_status ?? data_get($payroll->details, 'ptkp_status', 'TK/0'),
                'period_type' => $payroll->period_type ?? 'monthly',
                'period_start' => $payroll->period_start?->toDateString() ?? sprintf('%04d-%02d-01', (int) $payroll->year, (int) $payroll->month),
                'period_end' => $payroll->period_end?->toDateString(),
                'gross_income' => round((float) data_get($payroll->details, 'coretax.gross_income', $payroll->basic_salary + $payroll->total_allowance + $payroll->overtime_pay), 2),
                'taxable_income' => round((float) ($payroll->taxable_income ?: data_get($payroll->details, 'taxable_income', 0)), 2),
                'non_taxable_income' => round((float) ($payroll->non_taxable_income ?: data_get($payroll->details, 'non_taxable_income', 0)), 2),
                'pph21' => round((float) ($payroll->pph21_tax ?: data_get($payroll->deductions, 'pph21_ter', data_get($payroll->details, 'coretax.pph21', 0))), 2),
                'bpjs_employee_total' => round((float) ($payroll->bpjs_employee_total ?: data_get($payroll->details, 'bpjs_employee_total', 0)), 2),
                'bpjs_employer_total' => round((float) ($payroll->bpjs_employer_total ?: data_get($payroll->details, 'bpjs_employer_total', 0)), 2),
            ])
            ->values()
            ->all();
    }
}
