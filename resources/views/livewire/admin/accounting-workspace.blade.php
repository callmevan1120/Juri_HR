<x-admin.page-shell
    :title="__('Accounting Workspace')"
    :description="__('Maintain chart of accounts and balanced journal entries as the foundation for profit and balance reports.')"
    :show-description="true"
>
    <x-slot name="toolbar">
        <x-admin.page-tools grid-class="grid grid-cols-1 items-end gap-3 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <x-forms.label for="accounting-search" value="{{ __('Search accounts') }}" class="mb-1.5 block" />
                <x-forms.input id="accounting-search" type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search account code or name...') }}" />
            </div>
            <div class="lg:col-span-5">
                <div class="grid grid-cols-3 gap-2 rounded-xl bg-slate-100 p-1 text-sm font-semibold dark:bg-slate-800">
                    @foreach ([
                        'journals' => __('Journals'),
                        'accounts' => __('Accounts'),
                        'reports' => __('Reports'),
                    ] as $tab => $label)
                        <button
                            type="button"
                            wire:click="$set('activeTab', '{{ $tab }}')"
                            class="rounded-lg px-3 py-2 transition {{ $activeTab === $tab ? 'bg-white text-primary-700 shadow-sm dark:bg-slate-950 dark:text-primary-300' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </x-admin.page-tools>
    </x-slot>

    <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid grid-cols-1 items-end gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto]">
            <div>
                <x-forms.label for="accounting-report-start" value="{{ __('Report Start') }}" class="mb-1.5 block" />
                <x-forms.input id="accounting-report-start" type="date" wire:model.live="reportStartDate" />
            </div>
            <div>
                <x-forms.label for="accounting-report-end" value="{{ __('Report End') }}" class="mb-1.5 block" />
                <x-forms.input id="accounting-report-end" type="date" wire:model.live="reportEndDate" />
            </div>
            <x-actions.button type="button" wire:click="resetReportPeriod" variant="soft-secondary" class="justify-center">
                {{ __('This Month') }}
            </x-actions.button>
            <x-actions.button
                href="{{ route('admin.reports.accounting.export', ['start_date' => $reportStartDate, 'end_date' => $reportEndDate]) }}"
                variant="soft-primary"
                class="justify-center"
            >
                {{ __('Export Excel') }}
            </x-actions.button>
        </div>
    </div>

    <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3 xl:grid-cols-6 2xl:grid-cols-11">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Debit') }}</p>
            <p class="mt-2 text-xl font-bold text-slate-950 dark:text-white">Rp{{ number_format($totals['debit'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Credit') }}</p>
            <p class="mt-2 text-xl font-bold text-slate-950 dark:text-white">Rp{{ number_format($totals['credit'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Balance Check') }}</p>
            <p class="mt-2 text-xl font-bold {{ $totals['debit'] === $totals['credit'] ? 'text-emerald-600 dark:text-emerald-300' : 'text-amber-600 dark:text-amber-300' }}">
                {{ $totals['debit'] === $totals['credit'] ? __('Balanced') : __('Unbalanced') }}
            </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Revenue') }}</p>
            <p class="mt-2 text-xl font-bold text-slate-950 dark:text-white">Rp{{ number_format($financialSummary['revenue'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Expenses') }}</p>
            <p class="mt-2 text-xl font-bold text-slate-950 dark:text-white">Rp{{ number_format($financialSummary['expenses'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Net Income') }}</p>
            <p class="mt-2 text-xl font-bold {{ $financialSummary['net_income'] >= 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                Rp{{ number_format($financialSummary['net_income'], 0, ',', '.') }}
            </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Output Tax') }}</p>
            <p class="mt-2 text-xl font-bold text-slate-950 dark:text-white">Rp{{ number_format($taxSummary['issued_tax'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Unposted Tax') }}</p>
            <p class="mt-2 text-xl font-bold {{ $taxSummary['unposted_tax'] > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-300' }}">
                Rp{{ number_format($taxSummary['unposted_tax'], 0, ',', '.') }}
            </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Open AR') }}</p>
            <p class="mt-2 text-xl font-bold text-slate-950 dark:text-white">Rp{{ number_format($receivablesAging['total'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Open AP') }}</p>
            <p class="mt-2 text-xl font-bold text-slate-950 dark:text-white">Rp{{ number_format($payablesAging['total'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Net Cash') }}</p>
            <p class="mt-2 text-xl font-bold {{ $cashflowSummary['net_cash'] >= 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                Rp{{ number_format($cashflowSummary['net_cash'], 0, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-4">
            @if ($activeTab === 'journals')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Journal Entries') }}</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4">
                        @forelse ($journals as $journal)
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <h3 class="font-semibold text-slate-950 dark:text-white">{{ $journal->number }}</h3>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            {{ $journal->company?->name }} · {{ $journal->entry_date?->format('d M Y') }}
                                            @if ($journal->reference_number)
                                                · {{ $journal->reference_number }}
                                            @endif
                                        </p>
                                    </div>
                                    <x-admin.status-badge tone="success">{{ __(str($journal->status)->headline()->toString()) }}</x-admin.status-badge>
                                </div>
                                @if ($journal->description)
                                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ $journal->description }}</p>
                                @endif
                                <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800">
                                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950/50 dark:text-slate-400">
                                            <tr>
                                                <th class="px-3 py-2 text-left">{{ __('Account') }}</th>
                                                <th class="px-3 py-2 text-right">{{ __('Debit') }}</th>
                                                <th class="px-3 py-2 text-right">{{ __('Credit') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                            @foreach ($journal->lines as $line)
                                                <tr>
                                                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $line->account?->code }} · {{ $line->account?->name }}</td>
                                                    <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">Rp{{ number_format((float) $line->debit, 0, ',', '.') }}</td>
                                                    <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">Rp{{ number_format((float) $line->credit, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </article>
                        @empty
                            <x-admin.empty-state :title="__('No journals yet')" :description="__('Post a balanced journal from the form on the right.')" class="border-0 bg-transparent shadow-none" />
                        @endforelse
                    </div>
                </x-admin.panel>
            @elseif ($activeTab === 'accounts')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Chart of Accounts') }}</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2">
                        @forelse ($accounts as $account)
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-semibold text-slate-950 dark:text-white">{{ $account->code }} · {{ $account->name }}</h3>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $account->company?->name }}</p>
                                    </div>
                                    <x-admin.status-badge tone="primary">{{ __(str($account->type)->headline()->toString()) }}</x-admin.status-badge>
                                </div>
                                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ __('Normal balance: :balance', ['balance' => __(str($account->normal_balance)->headline()->toString())]) }}</p>
                            </article>
                        @empty
                            <x-admin.empty-state :title="__('No accounts yet')" :description="__('Create chart-of-account records from the form on the right.')" class="border-0 bg-transparent shadow-none" />
                        @endforelse
                    </div>
                </x-admin.panel>
            @else
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <x-admin.panel>
                        <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                            <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Profit & Loss') }}</h2>
                        </div>
                        <dl class="divide-y divide-slate-100 p-4 text-sm dark:divide-slate-800">
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-medium text-slate-600 dark:text-slate-300">{{ __('Revenue') }}</dt>
                                <dd class="font-bold text-slate-950 dark:text-white">Rp{{ number_format($financialSummary['revenue'], 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-medium text-slate-600 dark:text-slate-300">{{ __('Expenses') }}</dt>
                                <dd class="font-bold text-slate-950 dark:text-white">Rp{{ number_format($financialSummary['expenses'], 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-semibold text-slate-950 dark:text-white">{{ __('Net Income') }}</dt>
                                <dd class="font-black {{ $financialSummary['net_income'] >= 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                                    Rp{{ number_format($financialSummary['net_income'], 0, ',', '.') }}
                                </dd>
                            </div>
                        </dl>
                    </x-admin.panel>

                    <x-admin.panel>
                        <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                            <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Balance Sheet Snapshot') }}</h2>
                        </div>
                        <dl class="divide-y divide-slate-100 p-4 text-sm dark:divide-slate-800">
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-medium text-slate-600 dark:text-slate-300">{{ __('Assets') }}</dt>
                                <dd class="font-bold text-slate-950 dark:text-white">Rp{{ number_format($financialSummary['assets'], 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-medium text-slate-600 dark:text-slate-300">{{ __('Liabilities') }}</dt>
                                <dd class="font-bold text-slate-950 dark:text-white">Rp{{ number_format($financialSummary['liabilities'], 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-medium text-slate-600 dark:text-slate-300">{{ __('Equity') }}</dt>
                                <dd class="font-bold text-slate-950 dark:text-white">Rp{{ number_format($financialSummary['equity'], 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-medium text-slate-600 dark:text-slate-300">{{ __('Retained Earnings') }}</dt>
                                <dd class="font-bold text-slate-950 dark:text-white">Rp{{ number_format($balanceSheet['retained_earnings'], 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-medium text-slate-600 dark:text-slate-300">{{ __('Balance Check') }}</dt>
                                <dd class="font-bold {{ abs($balanceSheet['balance_check']) < 1 ? 'text-emerald-600 dark:text-emerald-300' : 'text-amber-600 dark:text-amber-300' }}">
                                    Rp{{ number_format($balanceSheet['balance_check'], 0, ',', '.') }}
                                </dd>
                            </div>
                        </dl>
                    </x-admin.panel>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <x-admin.panel>
                        <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                            <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('AR Aging') }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Open customer invoices as of the report end date.') }}</p>
                        </div>
                        <dl class="divide-y divide-slate-100 p-4 text-sm dark:divide-slate-800">
                            @foreach ([
                                'current' => __('Current'),
                                'days_1_30' => __('1-30 Days'),
                                'days_31_60' => __('31-60 Days'),
                                'days_61_90' => __('61-90 Days'),
                                'days_90_plus' => __('90+ Days'),
                            ] as $bucket => $label)
                                <div class="flex items-center justify-between py-2.5">
                                    <dt class="font-medium text-slate-600 dark:text-slate-300">{{ $label }}</dt>
                                    <dd class="font-bold text-slate-950 dark:text-white">Rp{{ number_format($receivablesAging[$bucket], 0, ',', '.') }}</dd>
                                </div>
                            @endforeach
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-semibold text-slate-950 dark:text-white">{{ __('Total') }}</dt>
                                <dd class="font-black text-slate-950 dark:text-white">Rp{{ number_format($receivablesAging['total'], 0, ',', '.') }}</dd>
                            </div>
                        </dl>
                    </x-admin.panel>

                    <x-admin.panel>
                        <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                            <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('AP Aging') }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Open vendor bills as of the report end date.') }}</p>
                        </div>
                        <dl class="divide-y divide-slate-100 p-4 text-sm dark:divide-slate-800">
                            @foreach ([
                                'current' => __('Current'),
                                'days_1_30' => __('1-30 Days'),
                                'days_31_60' => __('31-60 Days'),
                                'days_61_90' => __('61-90 Days'),
                                'days_90_plus' => __('90+ Days'),
                            ] as $bucket => $label)
                                <div class="flex items-center justify-between py-2.5">
                                    <dt class="font-medium text-slate-600 dark:text-slate-300">{{ $label }}</dt>
                                    <dd class="font-bold text-slate-950 dark:text-white">Rp{{ number_format($payablesAging[$bucket], 0, ',', '.') }}</dd>
                                </div>
                            @endforeach
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-semibold text-slate-950 dark:text-white">{{ __('Total') }}</dt>
                                <dd class="font-black text-slate-950 dark:text-white">Rp{{ number_format($payablesAging['total'], 0, ',', '.') }}</dd>
                            </div>
                        </dl>
                    </x-admin.panel>

                    <x-admin.panel>
                        <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                            <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Cashflow') }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Cash and bank movement posted through journal entries.') }}</p>
                        </div>
                        <dl class="divide-y divide-slate-100 p-4 text-sm dark:divide-slate-800">
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-medium text-slate-600 dark:text-slate-300">{{ __('Inflows') }}</dt>
                                <dd class="font-bold text-emerald-600 dark:text-emerald-300">Rp{{ number_format($cashflowSummary['inflows'], 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-medium text-slate-600 dark:text-slate-300">{{ __('Outflows') }}</dt>
                                <dd class="font-bold text-rose-600 dark:text-rose-300">Rp{{ number_format($cashflowSummary['outflows'], 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-semibold text-slate-950 dark:text-white">{{ __('Net Cash Movement') }}</dt>
                                <dd class="font-black {{ $cashflowSummary['net_cash'] >= 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                                    Rp{{ number_format($cashflowSummary['net_cash'], 0, ',', '.') }}
                                </dd>
                            </div>
                        </dl>
                    </x-admin.panel>
                </div>

                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Tax Summary') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Compare issued invoice tax against tax payable already posted to accounting.') }}</p>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-950/50">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Taxable Invoices') }}</p>
                            <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ number_format($taxSummary['taxable_invoice_count'], 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('from :count invoices', ['count' => number_format($taxSummary['invoice_count'], 0, ',', '.')]) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-950/50">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Issued Tax') }}</p>
                            <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Rp{{ number_format($taxSummary['issued_tax'], 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Invoice subtotal Rp:amount', ['amount' => number_format($taxSummary['issued_subtotal'], 0, ',', '.')]) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-950/50">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Posted Tax Payable') }}</p>
                            <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Rp{{ number_format($taxSummary['posted_tax_payable'], 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Paid tax Rp:amount', ['amount' => number_format($taxSummary['paid_tax'], 0, ',', '.')]) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-950/50">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Needs Posting') }}</p>
                            <p class="mt-2 text-2xl font-black {{ $taxSummary['unposted_tax'] > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-300' }}">
                                Rp{{ number_format($taxSummary['unposted_tax'], 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Output tax not yet posted.') }}</p>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 px-4 pb-4 dark:border-slate-800">
                        <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800">
                            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950/50 dark:text-slate-400">
                                    <tr>
                                        <th class="px-4 py-3 text-left">{{ __('Tax Rate') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Taxable Amount') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Tax Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse ($taxSummary['tax_rates'] as $taxRate)
                                        <tr>
                                            <td class="px-4 py-3 font-semibold text-slate-950 dark:text-white">{{ number_format($taxRate['rate'], 2, ',', '.') }}%</td>
                                            <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp{{ number_format($taxRate['taxable_amount'], 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-right font-bold text-slate-950 dark:text-white">Rp{{ number_format($taxRate['tax_amount'], 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No taxable invoice items found for this period.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </x-admin.panel>

                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Account Statement Breakdown') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Review debit, credit, and signed balance per account for the selected period.') }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950/50 dark:text-slate-400">
                                <tr>
                                    <th class="px-4 py-3 text-left">{{ __('Account') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Type') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Debit') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Credit') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Balance') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse ($accountBalances as $balance)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-slate-950 dark:text-white">{{ $balance['code'] }} · {{ $balance['name'] }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ __(str($balance['type'])->headline()->toString()) }}</td>
                                        <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp{{ number_format($balance['debit'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp{{ number_format($balance['credit'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right font-bold {{ $balance['balance'] >= 0 ? 'text-slate-950 dark:text-white' : 'text-rose-600 dark:text-rose-300' }}">
                                            Rp{{ number_format($balance['balance'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                            {{ __('No account movement found for this period.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.panel>

                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Ledger Detail') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Detailed journal lines for the selected period. Export Excel for the complete ledger.') }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950/50 dark:text-slate-400">
                                <tr>
                                    <th class="px-4 py-3 text-left">{{ __('Date') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Journal') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Account') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Memo') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Debit') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Credit') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse ($ledgerLines->take(25) as $line)
                                    <tr>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Carbon::parse($line['date'])->format('d M Y') }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-slate-950 dark:text-white">{{ $line['journal_number'] }}</div>
                                            @if ($line['reference_number'])
                                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $line['reference_number'] }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-slate-950 dark:text-white">{{ $line['account_code'] }} · {{ $line['account_name'] }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ __(str($line['account_type'])->headline()->toString()) }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $line['memo'] ?? $line['description'] ?? '-' }}</td>
                                        <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp{{ number_format($line['debit'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp{{ number_format($line['credit'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                            {{ __('No ledger lines found for this period.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.panel>
            @endif
        </div>

        <div class="space-y-4">
            @if ($canManage)
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Create Account') }}</h2>
                    </div>
                    <form wire:submit.prevent="createAccount" class="space-y-3 p-4">
                        <select wire:model.live="accountCompanyId" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="">{{ __('Company') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <x-forms.input-error for="accountCompanyId" />
                        <x-forms.input wire:model.live="accountCode" placeholder="{{ __('Account code') }}" />
                        <x-forms.input-error for="accountCode" />
                        <x-forms.input wire:model.live="accountName" placeholder="{{ __('Account name') }}" />
                        <x-forms.input-error for="accountName" />
                        <select wire:model.live="accountType" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            @foreach ($accountTypes as $type)
                                <option value="{{ $type }}">{{ __(str($type)->headline()->toString()) }}</option>
                            @endforeach
                        </select>
                        <x-actions.button type="submit" class="w-full">{{ __('Create Account') }}</x-actions.button>
                    </form>
                </x-admin.panel>

                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Post Journal') }}</h2>
                    </div>
                    <form wire:submit.prevent="createJournal" class="space-y-3 p-4">
                        <select wire:model.live="journalCompanyId" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="">{{ __('Company') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <x-forms.input-error for="journalCompanyId" />
                        <x-forms.input type="date" wire:model.live="journalDate" />
                        <select wire:model.live="journalDebitAccountId" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="">{{ __('Debit account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} · {{ $account->name }}</option>
                            @endforeach
                        </select>
                        <x-forms.input-error for="journalDebitAccountId" />
                        <select wire:model.live="journalCreditAccountId" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="">{{ __('Credit account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} · {{ $account->name }}</option>
                            @endforeach
                        </select>
                        <x-forms.input-error for="journalCreditAccountId" />
                        <x-forms.input type="number" min="0.01" step="0.01" wire:model.live="journalAmount" placeholder="{{ __('Amount') }}" />
                        <x-forms.input-error for="journalAmount" />
                        <x-forms.input wire:model.live="journalReference" placeholder="{{ __('Reference optional') }}" />
                        <x-forms.textarea wire:model.live="journalDescription" rows="2" placeholder="{{ __('Description') }}" />
                        <x-actions.button type="submit" variant="soft-primary" class="w-full">{{ __('Post Journal') }}</x-actions.button>
                    </form>
                </x-admin.panel>

                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Close Period') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Lock posted accounting periods so new journals cannot change approved reports.') }}</p>
                    </div>
                    <form wire:submit.prevent="closeAccountingPeriod" class="space-y-3 p-4">
                        <select wire:model.live="closingCompanyId" class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="">{{ __('Company') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <x-forms.input-error for="closingCompanyId" />
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div>
                                <x-forms.input type="date" wire:model.live="closingStartDate" />
                                <x-forms.input-error for="closingStartDate" />
                            </div>
                            <div>
                                <x-forms.input type="date" wire:model.live="closingEndDate" />
                                <x-forms.input-error for="closingEndDate" />
                            </div>
                        </div>
                        <x-forms.textarea wire:model.live="closingNotes" rows="2" placeholder="{{ __('Closing note optional') }}" />
                        <x-forms.input-error for="closingNotes" />
                        <x-actions.button type="submit" variant="soft-primary" class="w-full">{{ __('Close Period') }}</x-actions.button>
                    </form>
                </x-admin.panel>
            @else
                <x-admin.alert tone="info">
                    {{ __('You can view accounting records, but need manage permission to create accounts or journals.') }}
                </x-admin.alert>
            @endif

            <x-admin.panel>
                <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                    <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Period Locks') }}</h2>
                </div>
                <div class="space-y-3 p-4">
                    @forelse ($periodClosings as $closing)
                        <article class="rounded-xl border border-slate-200 bg-white p-3 text-sm dark:border-slate-800 dark:bg-slate-950/40">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-950 dark:text-white">{{ $closing->company?->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $closing->period_start?->format('d M Y') }} - {{ $closing->period_end?->format('d M Y') }}
                                    </p>
                                </div>
                                <x-admin.status-badge :tone="$closing->status === \App\Models\AccountingPeriodClosing::STATUS_CLOSED ? 'warning' : 'success'">
                                    {{ __(str($closing->status)->headline()->toString()) }}
                                </x-admin.status-badge>
                            </div>
                            @if ($closing->notes)
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $closing->notes }}</p>
                            @endif
                            @if ($canManage && $closing->status === \App\Models\AccountingPeriodClosing::STATUS_CLOSED)
                                <x-actions.button type="button" wire:click="reopenAccountingPeriod({{ $closing->id }})" variant="soft-secondary" class="mt-3 w-full justify-center">
                                    {{ __('Reopen Period') }}
                                </x-actions.button>
                            @endif
                        </article>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('No closed accounting periods yet.') }}</p>
                    @endforelse
                </div>
            </x-admin.panel>
        </div>
    </div>
</x-admin.page-shell>
