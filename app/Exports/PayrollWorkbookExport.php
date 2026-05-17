<?php

namespace App\Exports;

use App\Exports\Sheets\ArraySheet;
use App\Models\User;
use App\Support\CoretaxPayrollExportService;
use App\Support\PayrollPaymentInstructionService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PayrollWorkbookExport implements WithMultipleSheets
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        private readonly User $actor,
        private readonly array $filters = [],
    ) {}

    public function sheets(): array
    {
        $summary = new PayrollSummaryExport($this->actor, $this->filters);
        $payrolls = $summary->query()->get();
        $paymentRows = app(PayrollPaymentInstructionService::class)->rows($payrolls);
        $coretaxRows = app(CoretaxPayrollExportService::class)->rows($payrolls);

        return [
            $summary,
            new ArraySheet('Payment Instructions', [
                ['Payroll ID', 'Employee', 'NIP', 'Bank', 'Account Name', 'Account Number', 'Amount', 'Period', 'Reference'],
                ...array_map(fn (array $row): array => [
                    $row['payroll_id'],
                    $row['employee_name'],
                    $row['employee_nip'],
                    $row['bank_name'],
                    $row['bank_account_name'],
                    $row['bank_account_number'],
                    $row['amount'],
                    $row['period'],
                    $row['reference'],
                ], $paymentRows),
            ]),
            new ArraySheet('Coretax PPh21', [
                ['Payroll ID', 'Employee', 'NIP', 'PTKP', 'Period Type', 'Period Start', 'Period End', 'Gross Income', 'Taxable Income', 'Non Taxable Income', 'PPh 21', 'BPJS Employee', 'BPJS Employer'],
                ...array_map(fn (array $row): array => [
                    $row['payroll_id'],
                    $row['employee_name'],
                    $row['employee_nip'],
                    $row['ptkp_status'],
                    $row['period_type'],
                    $row['period_start'],
                    $row['period_end'],
                    $row['gross_income'],
                    $row['taxable_income'],
                    $row['non_taxable_income'],
                    $row['pph21'],
                    $row['bpjs_employee_total'],
                    $row['bpjs_employer_total'],
                ], $coretaxRows),
            ]),
        ];
    }
}
