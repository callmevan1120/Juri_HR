<?php

namespace App\Exports;

use App\Exports\Sheets\ArraySheet;
use App\Models\Company;
use App\Models\User;
use App\Support\AccountingWorkspaceService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AccountingStatementsExport implements WithMultipleSheets
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
        $accounting = app(AccountingWorkspaceService::class);
        $companyIds = $accounting
            ->scopeCompanies(Company::query(), $this->actor)
            ->pluck('id')
            ->all();
        $startDate = $this->filters['start_date'] ?? null;
        $endDate = $this->filters['end_date'] ?? null;
        $summary = $accounting->financialSummaryForCompanies($companyIds, $startDate, $endDate);
        $balanceSheet = $accounting->balanceSheetForCompanies($companyIds, $startDate, $endDate);
        $taxSummary = $accounting->taxSummaryForCompanies($companyIds, $startDate, $endDate);
        $accountBalances = $accounting->accountBalancesForCompanies($companyIds, $startDate, $endDate);
        $receivablesAging = $accounting->receivablesAgingForCompanies($companyIds, $endDate);
        $payablesAging = $accounting->payablesAgingForCompanies($companyIds, $endDate);
        $cashflowSummary = $accounting->cashflowSummaryForCompanies($companyIds, $startDate, $endDate);
        $ledgerLines = $accounting->ledgerLinesForCompanies($companyIds, $startDate, $endDate);

        return [
            new ArraySheet('Profit Loss', [
                ['Period Start', $startDate],
                ['Period End', $endDate],
                ['Metric', 'Amount'],
                ['Revenue', $summary['revenue']],
                ['Expenses', $summary['expenses']],
                ['Net Income', $summary['net_income']],
            ]),
            new ArraySheet('Balance Sheet', [
                ['Period Start', $startDate],
                ['Period End', $endDate],
                ['Metric', 'Amount'],
                ['Assets', $balanceSheet['assets']],
                ['Liabilities', $balanceSheet['liabilities']],
                ['Equity', $balanceSheet['equity']],
                ['Retained Earnings', $balanceSheet['retained_earnings']],
                ['Equity With Income', $balanceSheet['equity_with_income']],
                ['Balance Check', $balanceSheet['balance_check']],
                ['Working Capital', $balanceSheet['working_capital']],
            ]),
            new ArraySheet('Tax Summary', [
                ['Period Start', $startDate],
                ['Period End', $endDate],
                ['Metric', 'Value'],
                ['Invoice Count', $taxSummary['invoice_count']],
                ['Taxable Invoice Count', $taxSummary['taxable_invoice_count']],
                ['Issued Subtotal', $taxSummary['issued_subtotal']],
                ['Issued Tax', $taxSummary['issued_tax']],
                ['Issued Total', $taxSummary['issued_total']],
                ['Paid Tax', $taxSummary['paid_tax']],
                ['Posted Tax Payable', $taxSummary['posted_tax_payable']],
                ['Unposted Tax', $taxSummary['unposted_tax']],
                [],
                ['Tax Rate', 'Taxable Amount', 'Tax Amount'],
                ...$taxSummary['tax_rates']->map(fn (array $rate): array => [
                    $rate['rate'],
                    $rate['taxable_amount'],
                    $rate['tax_amount'],
                ])->all(),
            ]),
            new ArraySheet('Account Balances', [
                ['Code', 'Name', 'Type', 'Debit', 'Credit', 'Balance'],
                ...$accountBalances->map(fn (array $balance): array => [
                    $balance['code'],
                    $balance['name'],
                    $balance['type'],
                    $balance['debit'],
                    $balance['credit'],
                    $balance['balance'],
                ])->all(),
            ]),
            new ArraySheet('AR Aging', [
                ['As Of', $endDate],
                ['Bucket', 'Amount'],
                ['Current', $receivablesAging['current']],
                ['1-30 Days', $receivablesAging['days_1_30']],
                ['31-60 Days', $receivablesAging['days_31_60']],
                ['61-90 Days', $receivablesAging['days_61_90']],
                ['90+ Days', $receivablesAging['days_90_plus']],
                ['Total', $receivablesAging['total']],
                ['Open Invoices', $receivablesAging['count']],
            ]),
            new ArraySheet('AP Aging', [
                ['As Of', $endDate],
                ['Bucket', 'Amount'],
                ['Current', $payablesAging['current']],
                ['1-30 Days', $payablesAging['days_1_30']],
                ['31-60 Days', $payablesAging['days_31_60']],
                ['61-90 Days', $payablesAging['days_61_90']],
                ['90+ Days', $payablesAging['days_90_plus']],
                ['Total', $payablesAging['total']],
                ['Open Vendor Bills', $payablesAging['count']],
            ]),
            new ArraySheet('Cashflow', [
                ['Period Start', $startDate],
                ['Period End', $endDate],
                ['Metric', 'Amount'],
                ['Cash Inflows', $cashflowSummary['inflows']],
                ['Cash Outflows', $cashflowSummary['outflows']],
                ['Net Cash Movement', $cashflowSummary['net_cash']],
            ]),
            new ArraySheet('Ledger Detail', [
                ['Date', 'Journal Number', 'Reference', 'Company', 'Account Code', 'Account Name', 'Type', 'Description', 'Memo', 'Debit', 'Credit'],
                ...$ledgerLines->map(fn (array $line): array => [
                    $line['date'],
                    $line['journal_number'],
                    $line['reference_number'],
                    $line['company'],
                    $line['account_code'],
                    $line['account_name'],
                    $line['account_type'],
                    $line['description'],
                    $line['memo'],
                    $line['debit'],
                    $line['credit'],
                ])->all(),
            ]),
        ];
    }
}
