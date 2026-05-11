<?php

namespace App\Support;

use App\Models\Payroll;
use Illuminate\Database\Eloquent\Collection;

class PayrollPaymentInstructionService
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
                'bank_name' => $payroll->user?->bank_name,
                'bank_account_name' => $payroll->user?->bank_account_name,
                'bank_account_number' => $payroll->user?->bank_account_number,
                'amount' => (float) $payroll->net_salary,
                'period' => sprintf('%04d-%02d', (int) $payroll->year, (int) $payroll->month),
                'reference' => sprintf('PAY-%04d%02d-%s', (int) $payroll->year, (int) $payroll->month, $payroll->user?->nip ?? $payroll->user_id),
            ])
            ->values()
            ->all();
    }
}
