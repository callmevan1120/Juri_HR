<?php

namespace App\Livewire\Admin;

use App\Models\AccountingAccount;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Support\AccountingWorkspaceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AccountingWorkspace extends Component
{
    use InteractsWithBanner;

    protected AccountingWorkspaceService $accounting;

    public string $activeTab = 'journals';

    public string $search = '';

    public string $accountCompanyId = '';

    public string $accountCode = '';

    public string $accountName = '';

    public string $accountType = AccountingAccount::TYPE_ASSET;

    public string $journalCompanyId = '';

    public string $journalDate = '';

    public string $journalDebitAccountId = '';

    public string $journalCreditAccountId = '';

    public string $journalAmount = '0';

    public string $journalReference = '';

    public string $journalDescription = '';

    public string $reportStartDate = '';

    public string $reportEndDate = '';

    public string $closingCompanyId = '';

    public string $closingStartDate = '';

    public string $closingEndDate = '';

    public string $closingNotes = '';

    public function boot(AccountingWorkspaceService $accounting): void
    {
        Gate::authorize('viewAccountingWorkspace');

        $this->accounting = $accounting;
    }

    public function mount(): void
    {
        $this->journalDate = now()->toDateString();
        $this->reportStartDate = now()->startOfMonth()->toDateString();
        $this->reportEndDate = now()->endOfMonth()->toDateString();
        $this->closingStartDate = now()->startOfMonth()->toDateString();
        $this->closingEndDate = now()->endOfMonth()->toDateString();
    }

    public function createAccount(): void
    {
        Gate::authorize('manageAccountingWorkspace');

        $validated = $this->validate([
            'accountCompanyId' => ['required', 'integer', Rule::exists('companies', 'id')],
            'accountCode' => ['required', 'string', 'max:32'],
            'accountName' => ['required', 'string', 'max:180'],
            'accountType' => ['required', Rule::in($this->accountTypes())],
        ]);

        $this->accounting->createAccount(auth()->user(), [
            'company_id' => (int) $validated['accountCompanyId'],
            'code' => $validated['accountCode'],
            'name' => $validated['accountName'],
            'type' => $validated['accountType'],
        ]);

        $this->reset(['accountCode', 'accountName']);
        $this->banner(__('Account created.'));
    }

    public function createJournal(): void
    {
        Gate::authorize('manageAccountingWorkspace');

        $validated = $this->validate([
            'journalCompanyId' => ['required', 'integer', Rule::exists('companies', 'id')],
            'journalDate' => ['required', 'date'],
            'journalDebitAccountId' => ['required', 'integer', Rule::exists('accounting_accounts', 'id')],
            'journalCreditAccountId' => ['required', 'integer', 'different:journalDebitAccountId', Rule::exists('accounting_accounts', 'id')],
            'journalAmount' => ['required', 'numeric', 'min:0.01', 'max:999999999999'],
            'journalReference' => ['nullable', 'string', 'max:120'],
            'journalDescription' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->accounting->createSimpleJournal(auth()->user(), [
            'company_id' => (int) $validated['journalCompanyId'],
            'entry_date' => $validated['journalDate'],
            'debit_account_id' => (int) $validated['journalDebitAccountId'],
            'credit_account_id' => (int) $validated['journalCreditAccountId'],
            'amount' => $validated['journalAmount'],
            'reference_number' => $validated['journalReference'] ?: null,
            'description' => $validated['journalDescription'] ?: null,
        ]);

        $this->reset(['journalDebitAccountId', 'journalCreditAccountId', 'journalAmount', 'journalReference', 'journalDescription']);
        $this->journalAmount = '0';
        $this->banner(__('Journal posted.'));
    }

    public function closeAccountingPeriod(): void
    {
        Gate::authorize('manageAccountingWorkspace');

        $validated = $this->validate([
            'closingCompanyId' => ['required', 'integer', Rule::exists('companies', 'id')],
            'closingStartDate' => ['required', 'date'],
            'closingEndDate' => ['required', 'date', 'after_or_equal:closingStartDate'],
            'closingNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->accounting->closePeriod(auth()->user(), [
            'company_id' => (int) $validated['closingCompanyId'],
            'period_start' => $validated['closingStartDate'],
            'period_end' => $validated['closingEndDate'],
            'notes' => $validated['closingNotes'] ?: null,
        ]);

        $this->reset('closingNotes');
        $this->banner(__('Accounting period closed.'));
    }

    public function reopenAccountingPeriod(int $closingId): void
    {
        Gate::authorize('manageAccountingWorkspace');

        $this->accounting->reopenPeriod(auth()->user(), $closingId);
        $this->banner(__('Accounting period reopened.'));
    }

    public function resetReportPeriod(): void
    {
        $this->reportStartDate = now()->startOfMonth()->toDateString();
        $this->reportEndDate = now()->endOfMonth()->toDateString();
    }

    public function render()
    {
        $user = auth()->user();
        $companyIds = $this->accounting
            ->scopeCompanies(Company::query(), $user)
            ->pluck('id')
            ->all();

        $companies = Company::query()
            ->whereIn('id', $companyIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $accounts = AccountingAccount::query()
            ->with('company:id,name')
            ->whereIn('company_id', $companyIds)
            ->when($this->search !== '', fn (Builder $query) => $query->where(function (Builder $nested): void {
                $nested
                    ->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%');
            }))
            ->orderBy('code')
            ->get();

        $journals = JournalEntry::query()
            ->with(['company:id,name', 'creator:id,name', 'lines.account'])
            ->whereIn('company_id', $companyIds)
            ->when($this->reportStartDate !== '', fn (Builder $query) => $query->whereDate('entry_date', '>=', $this->reportStartDate))
            ->when($this->reportEndDate !== '', fn (Builder $query) => $query->whereDate('entry_date', '<=', $this->reportEndDate))
            ->latest('entry_date')
            ->latest()
            ->get();

        return view('livewire.admin.accounting-workspace', [
            'companies' => $companies,
            'accounts' => $accounts,
            'journals' => $journals,
            'totals' => $this->accounting->totalsForCompanies($companyIds, $this->reportStartDate, $this->reportEndDate),
            'financialSummary' => $this->accounting->financialSummaryForCompanies($companyIds, $this->reportStartDate, $this->reportEndDate),
            'balanceSheet' => $this->accounting->balanceSheetForCompanies($companyIds, $this->reportStartDate, $this->reportEndDate),
            'taxSummary' => $this->accounting->taxSummaryForCompanies($companyIds, $this->reportStartDate, $this->reportEndDate),
            'accountBalances' => $this->accounting->accountBalancesForCompanies($companyIds, $this->reportStartDate, $this->reportEndDate),
            'receivablesAging' => $this->accounting->receivablesAgingForCompanies($companyIds, $this->reportEndDate),
            'payablesAging' => $this->accounting->payablesAgingForCompanies($companyIds, $this->reportEndDate),
            'cashflowSummary' => $this->accounting->cashflowSummaryForCompanies($companyIds, $this->reportStartDate, $this->reportEndDate),
            'ledgerLines' => $this->accounting->ledgerLinesForCompanies($companyIds, $this->reportStartDate, $this->reportEndDate),
            'periodClosings' => $this->accounting->periodClosingsForCompanies($companyIds),
            'accountTypes' => $this->accountTypes(),
            'canManage' => $user->can('manageAccountingWorkspace'),
        ]);
    }

    /**
     * @return list<string>
     */
    private function accountTypes(): array
    {
        return [
            AccountingAccount::TYPE_ASSET,
            AccountingAccount::TYPE_LIABILITY,
            AccountingAccount::TYPE_EQUITY,
            AccountingAccount::TYPE_REVENUE,
            AccountingAccount::TYPE_EXPENSE,
        ];
    }
}
