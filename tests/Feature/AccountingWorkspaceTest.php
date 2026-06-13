<?php

use App\Exports\AccountingStatementsExport;
use App\Livewire\Admin\AccountingWorkspace;
use App\Models\AccountingAccount;
use App\Models\AccountingPeriodClosing;
use App\Models\AccountingTaxFiling;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Support\AccountingWorkspaceService;
use App\Support\MultiCompanyService;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('superadmin can create accounts and balanced journal entries', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Accounting Platform');

    $this->actingAs($superadmin);

    Livewire::test(AccountingWorkspace::class)
        ->set('accountCompanyId', (string) $company->id)
        ->set('accountCode', '1010')
        ->set('accountName', 'Cash Bank')
        ->set('accountType', AccountingAccount::TYPE_ASSET)
        ->call('createAccount')
        ->assertHasNoErrors()
        ->set('accountCompanyId', (string) $company->id)
        ->set('accountCode', '4010')
        ->set('accountName', 'Service Revenue')
        ->set('accountType', AccountingAccount::TYPE_REVENUE)
        ->call('createAccount')
        ->assertHasNoErrors();

    $cash = AccountingAccount::query()->where('code', '1010')->firstOrFail();
    $revenue = AccountingAccount::query()->where('code', '4010')->firstOrFail();

    Livewire::test(AccountingWorkspace::class)
        ->set('journalCompanyId', (string) $company->id)
        ->set('journalDate', now()->toDateString())
        ->set('journalDebitAccountId', (string) $cash->id)
        ->set('journalCreditAccountId', (string) $revenue->id)
        ->set('journalAmount', '2500000')
        ->set('journalReference', 'INV-TEST')
        ->set('journalDescription', 'Invoice posting')
        ->call('createJournal')
        ->assertHasNoErrors();

    $journal = JournalEntry::query()->with('lines')->firstOrFail();
    $summary = app(AccountingWorkspaceService::class)->financialSummaryForCompanies([$company->id]);

    expect($cash->normal_balance)->toBe(AccountingAccount::BALANCE_DEBIT)
        ->and($revenue->normal_balance)->toBe(AccountingAccount::BALANCE_CREDIT)
        ->and($journal->company_id)->toBe($company->id)
        ->and((float) $journal->lines->sum('debit'))->toBe(2500000.0)
        ->and((float) $journal->lines->sum('credit'))->toBe(2500000.0)
        ->and($summary['assets'])->toBe(2500000.0)
        ->and($summary['revenue'])->toBe(2500000.0)
        ->and($summary['net_income'])->toBe(2500000.0);
});

test('tenant scoped accounting admin cannot create account for another company', function () {
    $admin = User::factory()->admin()->create();
    $companyA = app(MultiCompanyService::class)->createCompany('PT Accounting A', $admin);
    $companyB = app(MultiCompanyService::class)->createCompany('PT Accounting B');

    $role = Role::query()->create([
        'name' => 'Accounting Manager',
        'slug' => 'accounting_manager',
        'permissions' => ['admin.accounting.view', 'admin.accounting.manage'],
    ]);
    $admin->roles()->sync([$role->id]);

    $this->actingAs($admin->fresh());

    Livewire::test(AccountingWorkspace::class)
        ->set('accountCompanyId', (string) $companyB->id)
        ->set('accountCode', '9999')
        ->set('accountName', 'Cross tenant account')
        ->set('accountType', AccountingAccount::TYPE_ASSET)
        ->call('createAccount')
        ->assertForbidden();

    expect(AccountingAccount::query()->where('company_id', $companyB->id)->exists())->toBeFalse()
        ->and($admin->fresh()->company_id)->toBe($companyA->id);
});

test('accounting reports can be filtered by period', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Accounting Period');
    $service = app(AccountingWorkspaceService::class);

    $cash = $service->createAccount($superadmin, [
        'company_id' => $company->id,
        'code' => '1010',
        'name' => 'Cash',
        'type' => AccountingAccount::TYPE_ASSET,
    ]);
    $revenue = $service->createAccount($superadmin, [
        'company_id' => $company->id,
        'code' => '4010',
        'name' => 'Revenue',
        'type' => AccountingAccount::TYPE_REVENUE,
    ]);

    $service->createSimpleJournal($superadmin, [
        'company_id' => $company->id,
        'entry_date' => now()->toDateString(),
        'debit_account_id' => $cash->id,
        'credit_account_id' => $revenue->id,
        'amount' => 1000000,
    ]);
    $service->createSimpleJournal($superadmin, [
        'company_id' => $company->id,
        'entry_date' => now()->subMonthsNoOverflow(2)->toDateString(),
        'debit_account_id' => $cash->id,
        'credit_account_id' => $revenue->id,
        'amount' => 3000000,
    ]);

    $start = now()->startOfMonth()->toDateString();
    $end = now()->endOfMonth()->toDateString();
    $summary = $service->financialSummaryForCompanies([$company->id], $start, $end);
    $totals = $service->totalsForCompanies([$company->id], $start, $end);
    $balances = $service->accountBalancesForCompanies([$company->id], $start, $end)->keyBy('code');

    expect($summary['revenue'])->toBe(1000000.0)
        ->and($summary['net_income'])->toBe(1000000.0)
        ->and($totals['debit'])->toBe(1000000.0)
        ->and($totals['credit'])->toBe(1000000.0)
        ->and($balances['1010']['balance'])->toBe(1000000.0)
        ->and($balances['4010']['balance'])->toBe(1000000.0);

    $this->actingAs($superadmin);

    Livewire::test(AccountingWorkspace::class)
        ->set('activeTab', 'reports')
        ->set('reportStartDate', $start)
        ->set('reportEndDate', $end)
        ->assertViewHas('journals', fn ($journals): bool => $journals->count() === 1)
        ->assertSee(__('Account Statement Breakdown'));
});

test('accounting reports summarize invoice output tax and posted tax payable', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Tax Report');
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'PT Tax Buyer',
        'status' => Client::STATUS_ACTIVE,
    ]);

    $postedInvoice = Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'number' => 'INV-TAX-001',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => now()->toDateString(),
        'subtotal' => 1000000,
        'tax_total' => 110000,
        'grand_total' => 1110000,
    ]);
    InvoiceItem::query()->create([
        'invoice_id' => $postedInvoice->id,
        'description' => 'Taxable service',
        'quantity' => 1,
        'unit_price' => 1000000,
        'tax_rate' => 11,
        'line_total' => 1000000,
    ]);

    $unpostedInvoice = Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'number' => 'INV-TAX-002',
        'status' => Invoice::STATUS_DRAFT,
        'issued_at' => now()->toDateString(),
        'subtotal' => 500000,
        'tax_total' => 55000,
        'grand_total' => 555000,
    ]);
    InvoiceItem::query()->create([
        'invoice_id' => $unpostedInvoice->id,
        'description' => 'Taxable addon',
        'quantity' => 1,
        'unit_price' => 500000,
        'tax_rate' => 11,
        'line_total' => 500000,
    ]);

    app(AccountingWorkspaceService::class)->postInvoicePayment($superadmin, $postedInvoice);

    $taxSummary = app(AccountingWorkspaceService::class)->taxSummaryForCompanies([
        $company->id,
    ], now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString());
    $balanceSheet = app(AccountingWorkspaceService::class)->balanceSheetForCompanies([
        $company->id,
    ], now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString());

    expect($taxSummary['invoice_count'])->toBe(2)
        ->and($taxSummary['taxable_invoice_count'])->toBe(2)
        ->and($taxSummary['issued_subtotal'])->toBe(1500000.0)
        ->and($taxSummary['issued_tax'])->toBe(165000.0)
        ->and($taxSummary['posted_tax_payable'])->toBe(110000.0)
        ->and($taxSummary['unposted_tax'])->toBe(55000.0)
        ->and($taxSummary['tax_rates']->first())->toMatchArray([
            'rate' => 11.0,
            'taxable_amount' => 1500000.0,
            'tax_amount' => 165000.0,
        ])
        ->and($balanceSheet['balance_check'])->toBe(0.0);

    $this->actingAs($superadmin);

    Livewire::test(AccountingWorkspace::class)
        ->set('activeTab', 'reports')
        ->assertViewHas('taxSummary', fn (array $summary): bool => $summary['unposted_tax'] === 55000.0)
        ->assertSee(__('Tax Summary'))
        ->assertSee(__('Needs Posting'));
});

test('accounting reports summarize aging cashflow and ledger detail', function () {
    $this->travelTo(Carbon::parse('2026-05-17 10:00:00'));

    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Accounting Operations');
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'PT Ledger Buyer',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $vendor = Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'PT Ledger Vendor',
        'status' => Vendor::STATUS_ACTIVE,
    ]);

    Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'number' => 'INV-AGING-001',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => now()->subDays(15)->toDateString(),
        'due_at' => now()->subDays(10)->toDateString(),
        'subtotal' => 1000000,
        'tax_total' => 0,
        'grand_total' => 1000000,
    ]);
    Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'number' => 'INV-AGING-002',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => now()->subDays(50)->toDateString(),
        'due_at' => now()->subDays(45)->toDateString(),
        'subtotal' => 2000000,
        'tax_total' => 0,
        'grand_total' => 2000000,
    ]);
    $paidInvoice = Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'number' => 'INV-CASH-001',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->toDateString(),
        'subtotal' => 750000,
        'tax_total' => 0,
        'grand_total' => 750000,
    ]);

    VendorBill::query()->create([
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'number' => 'BILL-AGING-001',
        'status' => VendorBill::STATUS_POSTED,
        'issued_at' => now()->subDays(25)->toDateString(),
        'due_at' => now()->subDays(20)->toDateString(),
        'subtotal' => 500000,
        'tax_total' => 0,
        'grand_total' => 500000,
    ]);
    $paidBill = VendorBill::query()->create([
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'number' => 'BILL-CASH-001',
        'status' => VendorBill::STATUS_POSTED,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->toDateString(),
        'subtotal' => 300000,
        'tax_total' => 0,
        'grand_total' => 300000,
    ]);

    $service = app(AccountingWorkspaceService::class);
    $service->postInvoicePayment($superadmin, $paidInvoice);
    $service->postVendorBillPayment($superadmin, $paidBill);

    $arAging = $service->receivablesAgingForCompanies([$company->id], now()->toDateString());
    $apAging = $service->payablesAgingForCompanies([$company->id], now()->toDateString());
    $cashflow = $service->cashflowSummaryForCompanies([$company->id], now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString());
    $ledgerLines = $service->ledgerLinesForCompanies([$company->id], now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString());

    expect($arAging['days_1_30'])->toBe(1000000.0)
        ->and($arAging['days_31_60'])->toBe(2000000.0)
        ->and($arAging['total'])->toBe(3000000.0)
        ->and($arAging['count'])->toBe(2)
        ->and($apAging['days_1_30'])->toBe(500000.0)
        ->and($apAging['total'])->toBe(500000.0)
        ->and($apAging['count'])->toBe(1)
        ->and($cashflow['inflows'])->toBe(750000.0)
        ->and($cashflow['outflows'])->toBe(300000.0)
        ->and($cashflow['net_cash'])->toBe(450000.0)
        ->and($ledgerLines->where('account_code', '1100')->count())->toBe(2);

    $this->actingAs($superadmin);

    Livewire::test(AccountingWorkspace::class)
        ->set('activeTab', 'reports')
        ->assertViewHas('receivablesAging', fn (array $aging): bool => $aging['total'] === 3000000.0)
        ->assertViewHas('payablesAging', fn (array $aging): bool => $aging['total'] === 500000.0)
        ->assertViewHas('cashflowSummary', fn (array $summary): bool => $summary['net_cash'] === 450000.0)
        ->assertSee(__('AR Aging'))
        ->assertSee(__('AP Aging'))
        ->assertSee(__('Ledger Detail'));

    $export = new AccountingStatementsExport($superadmin, [
        'start_date' => now()->startOfMonth()->toDateString(),
        'end_date' => now()->endOfMonth()->toDateString(),
    ]);

    expect($export->sheets())->toHaveCount(8);
});

test('accounting workspace surfaces toko contribution inside global finance', function (): void {
    $this->travelTo(Carbon::parse('2026-06-09 09:00:00'));

    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Toko Finance Integrated');
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'Walk-in',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $vendor = Vendor::query()->create([
        'company_id' => $company->id,
        'name' => 'Pandan Supplier',
        'status' => Vendor::STATUS_ACTIVE,
    ]);

    $paidInvoice = Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'number' => 'POS-20260609-0001',
        'status' => Invoice::STATUS_PAID,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->toDateString(),
        'subtotal' => 150000,
        'tax_total' => 0,
        'grand_total' => 150000,
        'metadata' => ['source' => 'toko_pos_counter_sale'],
    ]);
    Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'number' => 'POS-20260609-0002',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->addDays(7)->toDateString(),
        'subtotal' => 50000,
        'tax_total' => 0,
        'grand_total' => 50000,
        'metadata' => ['source' => 'legacy_toko_sale'],
    ]);
    Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'number' => '00519',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->addDays(7)->toDateString(),
        'subtotal' => 90000,
        'tax_total' => 0,
        'grand_total' => 90000,
        'metadata' => ['source' => 'legacy_toko_retail_sale'],
    ]);
    VendorBill::query()->create([
        'company_id' => $company->id,
        'vendor_id' => $vendor->id,
        'number' => 'PO-TOKO-001',
        'status' => VendorBill::STATUS_POSTED,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->addDays(14)->toDateString(),
        'subtotal' => 70000,
        'tax_total' => 0,
        'grand_total' => 70000,
        'metadata' => ['source' => 'toko_pos_purchase'],
    ]);

    $service = app(AccountingWorkspaceService::class);
    $service->postInvoicePayment($superadmin, $paidInvoice);

    $cash = $service->createAccount($superadmin, [
        'company_id' => $company->id,
        'code' => '1101',
        'name' => 'Toko Cash',
        'type' => AccountingAccount::TYPE_ASSET,
    ]);
    $expense = $service->createAccount($superadmin, [
        'company_id' => $company->id,
        'code' => '6101',
        'name' => 'Toko Expense',
        'type' => AccountingAccount::TYPE_EXPENSE,
    ]);
    $expenseJournal = JournalEntry::query()->create([
        'company_id' => $company->id,
        'created_by' => $superadmin->id,
        'number' => 'TJ-EXP-001',
        'entry_date' => now()->toDateString(),
        'status' => JournalEntry::STATUS_POSTED,
        'source_type' => 'toko_pos_operational_expense',
        'reference_number' => 'OP-001',
        'description' => 'Toko expense',
        'metadata' => ['source' => 'toko_pos_operational_expense', 'expense_type' => 'Gaji Karyawan'],
    ]);
    JournalEntryLine::query()->create([
        'journal_entry_id' => $expenseJournal->id,
        'accounting_account_id' => $expense->id,
        'debit' => 10000,
        'credit' => 0,
        'memo' => 'Toko salary expense',
    ]);
    JournalEntryLine::query()->create([
        'journal_entry_id' => $expenseJournal->id,
        'accounting_account_id' => $cash->id,
        'debit' => 0,
        'credit' => 10000,
        'memo' => 'Toko salary payment',
    ]);

    $contribution = $service->tokoContributionForCompanies([
        $company->id,
    ], now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString());

    expect($contribution['sales_total'])->toBe(290000.0)
        ->and($contribution['open_ar'])->toBe(140000.0)
        ->and($contribution['purchase_total'])->toBe(70000.0)
        ->and($contribution['open_ap'])->toBe(70000.0)
        ->and($contribution['operational_expenses'])->toBe(10000.0)
        ->and($contribution['posted_journals'])->toBe(2);

    $this->actingAs($superadmin);

    Livewire::test(AccountingWorkspace::class)
        ->assertViewHas('tokoContribution', fn (array $summary): bool => $summary['sales_total'] === 290000.0)
        ->assertSee(__('Toko Finance Contribution'))
        ->assertSee('Rp290.000')
        ->assertSee('Rp70.000')
        ->assertSee(__('Toko/POS add-on transactions are included in the global accounting totals for this report period.'));
});

test('closed accounting periods block new postings until reopened', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Closed Ledger');
    $service = app(AccountingWorkspaceService::class);

    $cash = $service->createAccount($superadmin, [
        'company_id' => $company->id,
        'code' => '1010',
        'name' => 'Cash',
        'type' => AccountingAccount::TYPE_ASSET,
    ]);
    $revenue = $service->createAccount($superadmin, [
        'company_id' => $company->id,
        'code' => '4010',
        'name' => 'Revenue',
        'type' => AccountingAccount::TYPE_REVENUE,
    ]);

    $closing = $service->closePeriod($superadmin, [
        'company_id' => $company->id,
        'period_start' => '2026-05-01',
        'period_end' => '2026-05-31',
        'notes' => 'Monthly close',
    ]);

    expect($closing->status)->toBe(AccountingPeriodClosing::STATUS_CLOSED)
        ->and($service->isPeriodClosed($company->id, '2026-05-17'))->toBeTrue()
        ->and(fn () => $service->createSimpleJournal($superadmin, [
            'company_id' => $company->id,
            'entry_date' => '2026-05-17',
            'debit_account_id' => $cash->id,
            'credit_account_id' => $revenue->id,
            'amount' => 1000000,
        ]))->toThrow(HttpException::class, 'Accounting period is closed.');

    $service->reopenPeriod($superadmin, $closing);

    $journal = $service->createSimpleJournal($superadmin, [
        'company_id' => $company->id,
        'entry_date' => '2026-05-17',
        'debit_account_id' => $cash->id,
        'credit_account_id' => $revenue->id,
        'amount' => 1000000,
    ]);

    expect($closing->refresh()->status)->toBe(AccountingPeriodClosing::STATUS_REOPENED)
        ->and($journal->reference_number)->toBeNull();
});

test('accounting workspace can close and reopen periods from UI state', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT UI Period Close');

    $this->actingAs($superadmin);

    Livewire::test(AccountingWorkspace::class)
        ->set('closingCompanyId', (string) $company->id)
        ->set('closingStartDate', '2026-05-01')
        ->set('closingEndDate', '2026-05-31')
        ->set('closingNotes', 'Close from UI')
        ->call('closeAccountingPeriod')
        ->assertHasNoErrors()
        ->assertViewHas('periodClosings', fn ($closings): bool => $closings->contains('notes', 'Close from UI'))
        ->call('reopenAccountingPeriod', AccountingPeriodClosing::query()->firstOrFail()->id)
        ->assertHasNoErrors()
        ->assertViewHas('periodClosings', fn ($closings): bool => $closings->contains('status', AccountingPeriodClosing::STATUS_REOPENED));
});

test('accounting tax filing workflow prepares files and pays company scoped output tax', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Tax Filing Workflow');
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'PT Filing Buyer',
        'status' => Client::STATUS_ACTIVE,
    ]);

    $invoice = Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'number' => 'INV-FILING-001',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => '2026-05-10',
        'subtotal' => 2000000,
        'tax_total' => 220000,
        'grand_total' => 2220000,
    ]);
    InvoiceItem::query()->create([
        'invoice_id' => $invoice->id,
        'description' => 'Taxable implementation',
        'quantity' => 1,
        'unit_price' => 2000000,
        'tax_rate' => 11,
        'line_total' => 2000000,
    ]);

    $service = app(AccountingWorkspaceService::class);
    $filing = $service->prepareTaxFiling($superadmin, [
        'company_id' => $company->id,
        'period_start' => '2026-05-01',
        'period_end' => '2026-05-31',
        'input_tax' => 50000,
        'notes' => 'Initial reconciliation',
    ]);

    expect($filing->status)->toBe(AccountingTaxFiling::STATUS_DRAFT)
        ->and($filing->taxable_turnover)->toBe(2000000.0)
        ->and($filing->output_tax)->toBe(220000.0)
        ->and($filing->input_tax)->toBe(50000.0)
        ->and($filing->net_tax_payable)->toBe(170000.0)
        ->and($filing->metadata['taxable_invoice_count'])->toBe(1);

    $service->markTaxFilingFiled($superadmin, $filing, [
        'filing_reference' => 'CORETAX-FILE-001',
    ]);

    $paid = $service->markTaxFilingPaid($superadmin, $filing->refresh(), [
        'payment_reference' => 'NTPN-001',
    ]);

    expect($paid->status)->toBe(AccountingTaxFiling::STATUS_PAID)
        ->and($paid->filing_reference)->toBe('CORETAX-FILE-001')
        ->and($paid->payment_reference)->toBe('NTPN-001')
        ->and($paid->paid_by)->toBe($superadmin->id);
});

test('accounting tax filing workflow is available from UI state', function () {
    $superadmin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT UI Tax Filing');
    $client = Client::query()->create([
        'company_id' => $company->id,
        'name' => 'PT UI Tax Buyer',
        'status' => Client::STATUS_ACTIVE,
    ]);
    $invoice = Invoice::query()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'number' => 'INV-UI-TAX-001',
        'status' => Invoice::STATUS_SENT,
        'issued_at' => '2026-05-10',
        'subtotal' => 1000000,
        'tax_total' => 110000,
        'grand_total' => 1110000,
    ]);
    InvoiceItem::query()->create([
        'invoice_id' => $invoice->id,
        'description' => 'UI taxable service',
        'quantity' => 1,
        'unit_price' => 1000000,
        'tax_rate' => 11,
        'line_total' => 1000000,
    ]);

    $this->actingAs($superadmin);

    Livewire::test(AccountingWorkspace::class)
        ->set('activeTab', 'tax')
        ->set('taxCompanyId', (string) $company->id)
        ->set('taxStartDate', '2026-05-01')
        ->set('taxEndDate', '2026-05-31')
        ->set('taxInputTax', '10000')
        ->call('prepareTaxFiling')
        ->assertHasNoErrors()
        ->assertViewHas('taxFilings', fn ($filings): bool => $filings->contains('net_tax_payable', 100000.0))
        ->set('taxFilingReference', 'CORETAX-UI-001')
        ->call('markTaxFilingFiled', AccountingTaxFiling::query()->firstOrFail()->id)
        ->assertHasNoErrors()
        ->set('taxPaymentReference', 'NTPN-UI-001')
        ->call('markTaxFilingPaid', AccountingTaxFiling::query()->firstOrFail()->id)
        ->assertHasNoErrors()
        ->assertViewHas('taxFilings', fn ($filings): bool => $filings->contains('status', AccountingTaxFiling::STATUS_PAID));
});

test('accounting journal form scopes accounts to selected company', function () {
    $superadmin = User::factory()->admin(true)->create();
    $companyA = app(MultiCompanyService::class)->createCompany('PT Account Scope A');
    $companyB = app(MultiCompanyService::class)->createCompany('PT Account Scope B');
    $cashA = AccountingAccount::query()->create([
        'company_id' => $companyA->id,
        'code' => '1110',
        'name' => 'Scoped Cash A',
        'type' => AccountingAccount::TYPE_ASSET,
        'normal_balance' => AccountingAccount::BALANCE_DEBIT,
        'is_active' => true,
    ]);
    $cashB = AccountingAccount::query()->create([
        'company_id' => $companyB->id,
        'code' => '2220',
        'name' => 'Scoped Cash B',
        'type' => AccountingAccount::TYPE_ASSET,
        'normal_balance' => AccountingAccount::BALANCE_DEBIT,
        'is_active' => true,
    ]);

    $this->actingAs($superadmin);

    Livewire::test(AccountingWorkspace::class)
        ->set('activeTab', 'journals')
        ->set('journalCompanyId', (string) $companyA->id)
        ->assertSee('Scoped Cash A')
        ->assertDontSee('Scoped Cash B');

    expect($cashA->exists)->toBeTrue()
        ->and($cashB->exists)->toBeTrue();
});

test('accounting workspace keeps selected tab from query string on reload', function () {
    $superadmin = User::factory()->admin(true)->create();

    $this->actingAs($superadmin);

    Livewire::withQueryParams(['activeTab' => 'accounts'])
        ->test(AccountingWorkspace::class)
        ->assertSet('activeTab', 'accounts');

    Livewire::withQueryParams(['activeTab' => 'bad-tab'])
        ->test(AccountingWorkspace::class)
        ->assertSet('activeTab', 'journals');
});

test('accounting route requires explicit permission', function () {
    $admin = User::factory()->admin()->create();
    $admin->roles()->detach();

    $this->actingAs($admin)
        ->get(route('admin.accounting'))
        ->assertForbidden();

    $role = Role::query()->create([
        'name' => 'Accounting Viewer',
        'slug' => 'accounting_viewer',
        'permissions' => ['admin.accounting.view'],
    ]);
    $admin->roles()->sync([$role->id]);

    $this->actingAs($admin->fresh())
        ->get(route('admin.accounting'))
        ->assertOk();
});

test('accounting statements can be exported by authorized admin', function () {
    Excel::fake();
    $this->travelTo(Carbon::parse('2026-05-17 10:00:00'));

    $admin = User::factory()->admin()->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Accounting Export', $admin);
    $role = Role::query()->create([
        'name' => 'Accounting Exporter',
        'slug' => 'accounting_exporter',
        'permissions' => ['admin.accounting.view'],
    ]);
    $admin->roles()->sync([$role->id]);

    $service = app(AccountingWorkspaceService::class);
    $cash = $service->createAccount($admin, [
        'company_id' => $company->id,
        'code' => '1010',
        'name' => 'Cash',
        'type' => AccountingAccount::TYPE_ASSET,
    ]);
    $revenue = $service->createAccount($admin, [
        'company_id' => $company->id,
        'code' => '4010',
        'name' => 'Revenue',
        'type' => AccountingAccount::TYPE_REVENUE,
    ]);
    $service->createSimpleJournal($admin, [
        'company_id' => $company->id,
        'entry_date' => now()->toDateString(),
        'debit_account_id' => $cash->id,
        'credit_account_id' => $revenue->id,
        'amount' => 1250000,
    ]);

    $this->actingAs($admin->fresh())
        ->get(route('admin.reports.accounting.export', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]))
        ->assertOk();

    Excel::assertDownloaded('accounting-statements-20260517-100000.xlsx');
});
