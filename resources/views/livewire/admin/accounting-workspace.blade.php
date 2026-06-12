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
                <div class="grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1 text-xs font-semibold dark:bg-slate-800 sm:grid-cols-4 sm:text-sm">
                    @foreach ([
                        'journals' => __('Journals'),
                        'accounts' => __('Accounts'),
                        'reports' => __('Reports'),
                        'tax' => __('Tax'),
                    ] as $tab => $label)
                        <button
                            type="button"
                            wire:click="$set('activeTab', '{{ $tab }}')"
                            class="rounded-lg px-2.5 py-2 transition sm:px-3 {{ $activeTab === $tab ? 'bg-white text-primary-700 shadow-sm dark:bg-slate-950 dark:text-primary-300' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}"
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
            <p class="mt-2 text-lg font-bold text-slate-950 dark:text-white sm:text-xl">Rp{{ number_format($totals['debit'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Credit') }}</p>
            <p class="mt-2 text-lg font-bold text-slate-950 dark:text-white sm:text-xl">Rp{{ number_format($totals['credit'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Balance Check') }}</p>
            <p class="mt-2 text-lg font-bold sm:text-xl {{ $totals['debit'] === $totals['credit'] ? 'text-emerald-600 dark:text-emerald-300' : 'text-amber-600 dark:text-amber-300' }}">
                {{ $totals['debit'] === $totals['credit'] ? __('Balanced') : __('Unbalanced') }}
            </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Revenue') }}</p>
            <p class="mt-2 text-lg font-bold text-slate-950 dark:text-white sm:text-xl">Rp{{ number_format($financialSummary['revenue'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Expenses') }}</p>
            <p class="mt-2 text-lg font-bold text-slate-950 dark:text-white sm:text-xl">Rp{{ number_format($financialSummary['expenses'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Net Income') }}</p>
            <p class="mt-2 text-lg font-bold sm:text-xl {{ $financialSummary['net_income'] >= 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                Rp{{ number_format($financialSummary['net_income'], 0, ',', '.') }}
            </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Output Tax') }}</p>
            <p class="mt-2 text-lg font-bold text-slate-950 dark:text-white sm:text-xl">Rp{{ number_format($taxSummary['issued_tax'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Unposted Tax') }}</p>
            <p class="mt-2 text-lg font-bold sm:text-xl {{ $taxSummary['unposted_tax'] > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-300' }}">
                Rp{{ number_format($taxSummary['unposted_tax'], 0, ',', '.') }}
            </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Open AR') }}</p>
            <p class="mt-2 text-lg font-bold text-slate-950 dark:text-white sm:text-xl">Rp{{ number_format($receivablesAging['total'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Open AP') }}</p>
            <p class="mt-2 text-lg font-bold text-slate-950 dark:text-white sm:text-xl">Rp{{ number_format($payablesAging['total'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Net Cash') }}</p>
            <p class="mt-2 text-lg font-bold sm:text-xl {{ $cashflowSummary['net_cash'] >= 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}">
                Rp{{ number_format($cashflowSummary['net_cash'], 0, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="mb-4 rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-1 border-b border-slate-200 px-4 py-3 dark:border-slate-800 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Toko Finance Contribution') }}</h2>
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Toko/POS add-on transactions are included in the global accounting totals for this report period.') }}</p>
            </div>
            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200">{{ __('Integrated') }}</span>
        </div>
        <div class="grid gap-2 p-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6">
            @foreach ([
                ['label' => __('Toko Sales'), 'value' => 'Rp'.number_format($tokoContribution['sales_total'], 0, ',', '.'), 'caption' => __('Paid, open, quotation-converted, and legacy store invoices.')],
                ['label' => __('Toko Open AR'), 'value' => 'Rp'.number_format($tokoContribution['open_ar'], 0, ',', '.'), 'caption' => __('Outstanding customer invoices from Toko/POS.')],
                ['label' => __('Toko Purchases'), 'value' => 'Rp'.number_format($tokoContribution['purchase_total'], 0, ',', '.'), 'caption' => __('Posted vendor bills from Toko purchasing.')],
                ['label' => __('Toko Open AP'), 'value' => 'Rp'.number_format($tokoContribution['open_ap'], 0, ',', '.'), 'caption' => __('Outstanding vendor bills from Toko purchasing.')],
                ['label' => __('Toko Expenses'), 'value' => 'Rp'.number_format($tokoContribution['operational_expenses'], 0, ',', '.'), 'caption' => __('Operational expense journals posted from Toko cash.')],
                ['label' => __('Toko Journals'), 'value' => number_format($tokoContribution['posted_journals'], 0, ',', '.'), 'caption' => __('Posted Toko-specific accounting journals.')],
            ] as $metric)
                <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-700 dark:bg-slate-950/60">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</p>
                    <p class="mt-1.5 text-lg font-semibold text-slate-950 dark:text-white">{{ $metric['value'] }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">{{ $metric['caption'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="order-2 space-y-4 xl:order-1">
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
                            <x-admin.empty-state :title="__('No journals yet')" :description="__('Post a balanced journal from the active action panel.')" class="border-0 bg-transparent shadow-none" />
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
                            <x-admin.empty-state :title="__('No accounts yet')" :description="__('Create chart-of-account records from the active action panel.')" class="border-0 bg-transparent shadow-none" />
                        @endforelse
                    </div>
                </x-admin.panel>
            @elseif ($activeTab === 'reports')
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
                            <p class="mt-2 text-xl font-bold text-slate-950 dark:text-white">{{ number_format($taxSummary['taxable_invoice_count'], 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('from :count invoices', ['count' => number_format($taxSummary['invoice_count'], 0, ',', '.')]) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-950/50">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Issued Tax') }}</p>
                            <p class="mt-2 text-xl font-bold text-slate-950 dark:text-white">Rp{{ number_format($taxSummary['issued_tax'], 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Invoice subtotal Rp:amount', ['amount' => number_format($taxSummary['issued_subtotal'], 0, ',', '.')]) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-950/50">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Posted Tax Payable') }}</p>
                            <p class="mt-2 text-xl font-bold text-slate-950 dark:text-white">Rp{{ number_format($taxSummary['posted_tax_payable'], 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Paid tax Rp:amount', ['amount' => number_format($taxSummary['paid_tax'], 0, ',', '.')]) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-950/50">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Needs Posting') }}</p>
                            <p class="mt-2 text-xl font-bold {{ $taxSummary['unposted_tax'] > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-300' }}">
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
                    <div class="grid grid-cols-1 gap-3 p-4 md:hidden">
                        @forelse ($accountBalances as $balance)
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950/40">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-950 dark:text-white">{{ $balance['code'] }} · {{ $balance['name'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __(str($balance['type'])->headline()->toString()) }}</p>
                                    </div>
                                    <p class="shrink-0 text-right text-sm font-bold {{ $balance['balance'] >= 0 ? 'text-slate-950 dark:text-white' : 'text-rose-600 dark:text-rose-300' }}">
                                        Rp{{ number_format($balance['balance'], 0, ',', '.') }}
                                    </p>
                                </div>
                                <dl class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-900">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Debit') }}</dt>
                                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">Rp{{ number_format($balance['debit'], 0, ',', '.') }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-900">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Credit') }}</dt>
                                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">Rp{{ number_format($balance['credit'], 0, ',', '.') }}</dd>
                                    </div>
                                </dl>
                            </article>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                {{ __('No account movement found for this period.') }}
                            </p>
                        @endforelse
                    </div>
                    <div class="hidden md:block">
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
                    <div class="grid grid-cols-1 gap-3 p-4 md:hidden">
                        @forelse ($ledgerLines->take(25) as $line)
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950/40">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-950 dark:text-white">{{ $line['journal_number'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            {{ \Illuminate\Support\Carbon::parse($line['date'])->format('d M Y') }}
                                            @if ($line['reference_number'])
                                                · {{ $line['reference_number'] }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-900">
                                    <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ $line['account_code'] }} · {{ $line['account_name'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __(str($line['account_type'])->headline()->toString()) }}</p>
                                    @if ($line['memo'] ?? $line['description'] ?? null)
                                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $line['memo'] ?? $line['description'] }}</p>
                                    @endif
                                </div>
                                <dl class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-900">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Debit') }}</dt>
                                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">Rp{{ number_format($line['debit'], 0, ',', '.') }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-900">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Credit') }}</dt>
                                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">Rp{{ number_format($line['credit'], 0, ',', '.') }}</dd>
                                    </div>
                                </dl>
                            </article>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                {{ __('No ledger lines found for this period.') }}
                            </p>
                        @endforelse
                    </div>
                    <div class="hidden md:block">
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
            @else
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <x-admin.panel>
                        <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                            <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Tax Filing Readiness') }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Prepare monthly output tax drafts from invoice tax summaries before filing or payment.') }}</p>
                        </div>
                        <dl class="divide-y divide-slate-100 p-4 text-sm dark:divide-slate-800">
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-medium text-slate-600 dark:text-slate-300">{{ __('Issued Tax') }}</dt>
                                <dd class="font-bold text-slate-950 dark:text-white">Rp{{ number_format($taxSummary['issued_tax'], 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-medium text-slate-600 dark:text-slate-300">{{ __('Posted Tax Payable') }}</dt>
                                <dd class="font-bold text-slate-950 dark:text-white">Rp{{ number_format($taxSummary['posted_tax_payable'], 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="font-semibold text-slate-950 dark:text-white">{{ __('Needs Posting') }}</dt>
                                <dd class="font-black {{ $taxSummary['unposted_tax'] > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-300' }}">
                                    Rp{{ number_format($taxSummary['unposted_tax'], 0, ',', '.') }}
                                </dd>
                            </div>
                        </dl>
                    </x-admin.panel>

                    <x-admin.panel>
                        <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                            <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Tax Rate Breakdown') }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Taxable base grouped by invoice line tax rate.') }}</p>
                        </div>
                        <div class="space-y-3 p-4">
                            @forelse ($taxSummary['tax_rates'] as $taxRate)
                                <div class="rounded-xl border border-slate-200 bg-white p-3 text-sm dark:border-slate-800 dark:bg-slate-950/40">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="font-semibold text-slate-950 dark:text-white">{{ number_format($taxRate['rate'], 2, ',', '.') }}%</p>
                                        <p class="font-bold text-slate-950 dark:text-white">Rp{{ number_format($taxRate['tax_amount'], 0, ',', '.') }}</p>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Taxable amount Rp:amount', ['amount' => number_format($taxRate['taxable_amount'], 0, ',', '.')]) }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('No taxable invoice items found for this period.') }}</p>
                            @endforelse
                        </div>
                    </x-admin.panel>
                </div>

                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Tax Filing Workflow') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Track draft, filed, and paid tax filings without mixing records across companies.') }}</p>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2">
                        @forelse ($taxFilings as $filing)
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-semibold text-slate-950 dark:text-white">{{ $filing->company?->name }}</h3>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            {{ $filing->period_start?->format('d M Y') }} - {{ $filing->period_end?->format('d M Y') }}
                                        </p>
                                    </div>
                                    <x-admin.status-badge :tone="match ($filing->status) {
                                        \App\Models\AccountingTaxFiling::STATUS_PAID => 'success',
                                        \App\Models\AccountingTaxFiling::STATUS_FILED => 'primary',
                                        default => 'warning',
                                    }">
                                        {{ __(str($filing->status)->headline()->toString()) }}
                                    </x-admin.status-badge>
                                </div>
                                <dl class="mt-4 grid grid-cols-2 gap-2 text-sm">
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/50">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Output Tax') }}</dt>
                                        <dd class="mt-1 font-bold text-slate-950 dark:text-white">Rp{{ number_format($filing->output_tax, 0, ',', '.') }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/50">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Net Payable') }}</dt>
                                        <dd class="mt-1 font-bold text-slate-950 dark:text-white">Rp{{ number_format($filing->net_tax_payable, 0, ',', '.') }}</dd>
                                    </div>
                                </dl>
                                @if ($filing->filing_reference || $filing->payment_reference)
                                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $filing->filing_reference ? __('Filing: :ref', ['ref' => $filing->filing_reference]) : '' }}
                                        {{ $filing->payment_reference ? ' · '.__('Payment: :ref', ['ref' => $filing->payment_reference]) : '' }}
                                    </p>
                                @endif
                                @if ($canManage && $filing->status !== \App\Models\AccountingTaxFiling::STATUS_PAID)
                                    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                        <x-actions.button type="button" wire:click="markTaxFilingFiled({{ $filing->id }})" variant="soft-secondary" class="justify-center">
                                            {{ __('Mark Filed') }}
                                        </x-actions.button>
                                        <x-actions.button type="button" wire:click="markTaxFilingPaid({{ $filing->id }})" variant="soft-primary" class="justify-center">
                                            {{ __('Mark Paid') }}
                                        </x-actions.button>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <x-admin.empty-state :title="__('No tax filings yet')" :description="__('Prepare a filing draft from the action panel once invoice tax summaries are ready.')" class="border-0 bg-transparent shadow-none md:col-span-2" />
                        @endforelse
                    </div>
                </x-admin.panel>
            @endif
        </div>

        <div class="order-1 space-y-4 xl:order-2">
            @if ($canManage)
                <x-admin.panel class="border-primary-200 bg-primary-50/60 dark:border-primary-900/60 dark:bg-primary-950/20">
                    <div class="space-y-1 p-3.5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-primary-800 dark:text-primary-200">{{ __('Quick action') }}</p>
                        <p class="text-sm leading-5 text-primary-700 dark:text-primary-100">
                            {{ __('The accounting form follows the selected tab so posting, setup, and closing stay separated.') }}
                        </p>
                    </div>
                </x-admin.panel>

                @if ($activeTab === 'accounts')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Create Account') }}</h2>
                        <p class="mt-1 text-sm leading-5 text-slate-500 dark:text-slate-400">{{ __('Create one chart-of-account record with company, code, name, and type.') }}</p>
                    </div>
                    <form wire:submit.prevent="createAccount" class="space-y-4 p-4">
                        <div class="space-y-1.5">
                            <x-forms.label for="account-company" value="{{ __('Company') }}" />
                            <x-forms.select id="account-company" wire:model.live="accountCompanyId" class="w-full" placeholder="{{ __('Choose company') }}">
                                <option value="">{{ __('Choose company') }}</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </x-forms.select>
                            <x-forms.input-error for="accountCompanyId" />
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <x-forms.label for="account-code" value="{{ __('Account code') }}" />
                                <x-forms.input id="account-code" wire:model.live="accountCode" placeholder="{{ __('e.g. 1100') }}" />
                                <x-forms.input-error for="accountCode" />
                            </div>
                            <div class="space-y-1.5">
                                <x-forms.label for="account-type" value="{{ __('Account type') }}" />
                                <x-forms.select id="account-type" wire:model.live="accountType" class="w-full" placeholder="{{ __('Account type') }}">
                                    @foreach ($accountTypes as $type)
                                        <option value="{{ $type }}">{{ __(str($type)->headline()->toString()) }}</option>
                                    @endforeach
                                </x-forms.select>
                                <x-forms.input-error for="accountType" />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <x-forms.label for="account-name" value="{{ __('Account name') }}" />
                            <x-forms.input id="account-name" wire:model.live="accountName" placeholder="{{ __('e.g. Cash / Bank') }}" />
                            <x-forms.input-error for="accountName" />
                        </div>
                        <x-actions.button type="submit" class="w-full">{{ __('Create Account') }}</x-actions.button>
                    </form>
                </x-admin.panel>

                @elseif ($activeTab === 'journals')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Post Journal') }}</h2>
                        <p class="mt-1 text-sm leading-5 text-slate-500 dark:text-slate-400">{{ __('Post a balanced debit and credit journal with reference and description.') }}</p>
                    </div>
                    <form wire:submit.prevent="createJournal" class="space-y-4 p-4">
                        <div class="space-y-1.5">
                            <x-forms.label for="journal-company" value="{{ __('Company') }}" />
                            <x-forms.select id="journal-company" wire:model.live="journalCompanyId" class="w-full" placeholder="{{ __('Choose company') }}">
                                <option value="">{{ __('Choose company') }}</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </x-forms.select>
                            <x-forms.input-error for="journalCompanyId" />
                        </div>

                        <div class="space-y-1.5">
                            <x-forms.label for="journal-date" value="{{ __('Journal date') }}" />
                            <x-forms.input id="journal-date" type="date" wire:model.live="journalDate" />
                            <x-forms.input-error for="journalDate" />
                        </div>

                        <div class="space-y-1.5">
                            <x-forms.label for="journal-debit-account" value="{{ __('Debit account') }}" />
                            <x-forms.select id="journal-debit-account" wire:model.live="journalDebitAccountId" class="w-full" placeholder="{{ __('Choose debit account') }}">
                                <option value="">{{ __('Choose debit account') }}</option>
                                @foreach ($journalAccountOptions as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} · {{ $account->name }}</option>
                                @endforeach
                            </x-forms.select>
                            <x-forms.input-error for="journalDebitAccountId" />
                        </div>

                        <div class="space-y-1.5">
                            <x-forms.label for="journal-credit-account" value="{{ __('Credit account') }}" />
                            <x-forms.select id="journal-credit-account" wire:model.live="journalCreditAccountId" class="w-full" placeholder="{{ __('Choose credit account') }}">
                                <option value="">{{ __('Choose credit account') }}</option>
                                @foreach ($journalAccountOptions as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} · {{ $account->name }}</option>
                                @endforeach
                            </x-forms.select>
                            <x-forms.input-error for="journalCreditAccountId" />
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <x-forms.label for="journal-amount" value="{{ __('Amount') }}" />
                                <x-forms.input id="journal-amount" type="number" min="0.01" step="0.01" wire:model.live="journalAmount" placeholder="0" />
                                <x-forms.input-error for="journalAmount" />
                            </div>
                            <div class="space-y-1.5">
                                <x-forms.label for="journal-reference" value="{{ __('Reference') }}" />
                                <x-forms.input id="journal-reference" wire:model.live="journalReference" placeholder="{{ __('Optional') }}" />
                                <x-forms.input-error for="journalReference" />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <x-forms.label for="journal-description" value="{{ __('Description') }}" />
                            <x-forms.textarea id="journal-description" wire:model.live="journalDescription" rows="3" placeholder="{{ __('Transaction memo, source document, or approval note.') }}" />
                            <x-forms.input-error for="journalDescription" />
                        </div>
                        <x-actions.button type="submit" variant="soft-primary" class="w-full">{{ __('Post Journal') }}</x-actions.button>
                    </form>
                </x-admin.panel>

                @elseif ($activeTab === 'reports')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Close Period') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Lock posted accounting periods so new journals cannot change approved reports.') }}</p>
                    </div>
                    <form wire:submit.prevent="closeAccountingPeriod" class="space-y-4 p-4">
                        <div class="space-y-1.5">
                            <x-forms.label for="closing-company" value="{{ __('Company') }}" />
                            <x-forms.select id="closing-company" wire:model.live="closingCompanyId" class="w-full" placeholder="{{ __('Choose company') }}">
                                <option value="">{{ __('Choose company') }}</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </x-forms.select>
                            <x-forms.input-error for="closingCompanyId" />
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <x-forms.label for="closing-start-date" value="{{ __('Period start') }}" />
                                <x-forms.input id="closing-start-date" type="date" wire:model.live="closingStartDate" />
                                <x-forms.input-error for="closingStartDate" />
                            </div>
                            <div class="space-y-1.5">
                                <x-forms.label for="closing-end-date" value="{{ __('Period end') }}" />
                                <x-forms.input id="closing-end-date" type="date" wire:model.live="closingEndDate" />
                                <x-forms.input-error for="closingEndDate" />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <x-forms.label for="closing-notes" value="{{ __('Closing note') }}" />
                            <x-forms.textarea id="closing-notes" wire:model.live="closingNotes" rows="3" placeholder="{{ __('Optional approval, report, or handover note.') }}" />
                            <x-forms.input-error for="closingNotes" />
                        </div>
                        <x-actions.button type="submit" variant="soft-primary" class="w-full">{{ __('Close Period') }}</x-actions.button>
                    </form>
                </x-admin.panel>
                @elseif ($activeTab === 'tax')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Prepare Tax Filing') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Generate a controlled tax filing draft from the selected company and period.') }}</p>
                    </div>
                    <form wire:submit.prevent="prepareTaxFiling" class="space-y-4 p-4">
                        <div class="space-y-1.5">
                            <x-forms.label for="tax-company" value="{{ __('Company') }}" />
                            <x-forms.select id="tax-company" wire:model.live="taxCompanyId" class="w-full" placeholder="{{ __('Choose company') }}">
                                <option value="">{{ __('Choose company') }}</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </x-forms.select>
                            <x-forms.input-error for="taxCompanyId" />
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <x-forms.label for="tax-start-date" value="{{ __('Period start') }}" />
                                <x-forms.input id="tax-start-date" type="date" wire:model.live="taxStartDate" />
                                <x-forms.input-error for="taxStartDate" />
                            </div>
                            <div class="space-y-1.5">
                                <x-forms.label for="tax-end-date" value="{{ __('Period end') }}" />
                                <x-forms.input id="tax-end-date" type="date" wire:model.live="taxEndDate" />
                                <x-forms.input-error for="taxEndDate" />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <x-forms.label for="tax-input-tax" value="{{ __('Input tax credit') }}" />
                            <x-forms.input id="tax-input-tax" type="number" min="0" step="0.01" wire:model.live="taxInputTax" placeholder="0" />
                            <x-forms.input-error for="taxInputTax" />
                        </div>

                        <div class="space-y-1.5">
                            <x-forms.label for="tax-filing-reference" value="{{ __('Filing reference') }}" />
                            <x-forms.input id="tax-filing-reference" wire:model.live="taxFilingReference" placeholder="{{ __('Optional e-Faktur/Coretax reference') }}" />
                            <x-forms.input-error for="taxFilingReference" />
                        </div>

                        <div class="space-y-1.5">
                            <x-forms.label for="tax-payment-reference" value="{{ __('Payment reference') }}" />
                            <x-forms.input id="tax-payment-reference" wire:model.live="taxPaymentReference" placeholder="{{ __('Optional payment reference') }}" />
                            <x-forms.input-error for="taxPaymentReference" />
                        </div>

                        <div class="space-y-1.5">
                            <x-forms.label for="tax-notes" value="{{ __('Tax note') }}" />
                            <x-forms.textarea id="tax-notes" wire:model.live="taxNotes" rows="3" placeholder="{{ __('Filing note, approval number, or reconciliation remark.') }}" />
                            <x-forms.input-error for="taxNotes" />
                        </div>
                        <x-actions.button type="submit" variant="soft-primary" class="w-full">{{ __('Prepare Draft') }}</x-actions.button>
                    </form>
                </x-admin.panel>
                @endif
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
