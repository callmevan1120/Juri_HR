    @if ($activePage === 'cash')
        <x-admin.panel x-data="{ showPaymentMethodForm: false, showBankAccountForm: false, showExpenseTypeForm: false, showExpenseForm: false }" x-init="$watch('$wire.editingOperationalExpenseId', value => { if(value) showExpenseForm = true })">
            <div class="border-b border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80">
                <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Cash') }}</h2>
            </div>

            <div class="grid gap-4 border-b border-slate-200/60/80 px-4 py-6 dark:border-slate-700/50/80 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-4 dark:border-slate-700/50/80 bg-slate-50/50 dark:bg-slate-900/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Paid Sales') }}</p>
                    <p class="mt-1 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idMoney((float) ($tokoReport['sales']['total'] ?? 0)) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-4 dark:border-slate-700/50/80 bg-slate-50/50 dark:bg-slate-900/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Purchases') }}</p>
                    <p class="mt-1 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idMoney((float) ($tokoReport['purchases']['total'] ?? 0)) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-4 dark:border-slate-700/50/80 bg-slate-50/50 dark:bg-slate-900/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Net') }}</p>
                    <p class="mt-1 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idMoney((float) (($tokoReport['sales']['total'] ?? 0) - ($tokoReport['purchases']['total'] ?? 0))) }}</p>
                </div>
            </div>

            <div class="grid gap-2 px-4 py-4 lg:grid-cols-2">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Payment Methods') }}</h3>
                        <x-actions.icon-button @click="showPaymentMethodForm = true; $wire.resetPaymentMethodForm()" variant="success" label="{{ __('Tambah Method') }}">
                            <x-heroicon-m-plus class="h-4 w-4" />
                        </x-actions.icon-button>
                    </div>
                    <div class="space-y-2">
                        @forelse ($paymentMethods as $method)
                            <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $method['name'] }}</div>
                        @empty
                            <p class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm text-slate-500 dark:text-slate-400">{{ __('No payment methods yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Bank Accounts') }}</h3>
                        <x-actions.icon-button @click="showBankAccountForm = true; $wire.resetBankAccountForm()" variant="success" label="{{ __('Tambah Bank Account') }}">
                            <x-heroicon-m-plus class="h-4 w-4" />
                        </x-actions.icon-button>
                    </div>
                    <div class="space-y-2">
                        @forelse ($bankAccounts as $account)
                            <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm ">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $account['code'] }} · {{ $account['bank'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $account['number'] }} · {{ $account['name'] }}</p>
                            </div>
                        @empty
                            <p class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm text-slate-500 dark:text-slate-400">{{ __('No bank accounts yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="border-b border-slate-200/60/80 px-4 py-6 dark:border-slate-700/50/80">
                <div class="mb-3 flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Operational Expenses') }}</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Tambah Trx, Data Operasional, and Tipe Pengeluaran in one clean cash workspace.') }}</p>
                    </div>
                    <div class="flex gap-2">
                        <x-actions.button @click="showExpenseTypeForm = true; $wire.resetExpenseTypeForm()" variant="secondary">
                            <x-heroicon-m-tag class="mr-1 -ml-1 h-4 w-4" />
                            {{ __('Tipe Pengeluaran') }}
                        </x-actions.button>
                        <x-actions.button @click="showExpenseForm = true; $wire.resetOperationalExpenseForm()" variant="primary">
                            <x-heroicon-m-plus class="mr-1 -ml-1 h-4 w-4" />
                            {{ __('Tambah Trx') }}
                        </x-actions.button>
                    </div>
                </div>

                

                @if ($expenseTypes !== [])
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($expenseTypes as $type)
                            <span class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $type['name'] }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="mt-3 flex flex-col gap-2 pt-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative w-full sm:w-64">
                            <x-heroicon-m-magnifying-glass class="absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                            <input id="toko-operational-expense-search" type="search" wire:model.live.debounce.250ms="operationalExpenseSearch" placeholder="{{ __('Search expenses...') }}" class="min-h-9 w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 pl-10 pr-4 py-2.5 text-sm transition-all focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-slate-700/50 dark:bg-slate-900/50 dark:focus:border-primary-500 dark:focus:bg-slate-900">
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <input type="date" wire:model.live="operationalExpenseFromDate" aria-label="{{ __('From date') }}" class="min-h-9 rounded-xl border border-slate-200/60 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-white">
                        <input type="date" wire:model.live="operationalExpenseToDate" aria-label="{{ __('To date') }}" class="min-h-9 rounded-xl border border-slate-200/60 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-white">
                        @if ($canExport)
                            <x-actions.icon-button href="{{ route('admin.toko.exports.report-operational-expenses', $operationalExpenseExportQuery) }}" label="{{ __('Export CSV') }}">
                                <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                            </x-actions.icon-button>
                        @endif
                    </div>
                </div>

                <div class="mt-3 overflow-x-auto rounded-2xl shadow-sm mt-4">
                    <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                        <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <th scope="col" class="px-4 py-3 text-left">{{ __('Code') }}</th>
                                <th scope="col" class="px-4 py-3 text-left">{{ __('Type') }}</th>
                                <th scope="col" class="px-4 py-3 text-left">{{ __('Description') }}</th>
                                <th scope="col" class="px-4 py-3 text-left">{{ __('Payment') }}</th>
                                <th scope="col" class="px-4 py-3 text-right">{{ __('Amount') }}</th>
                                <th scope="col" class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($operationalExpenseRows as $expense)
                                <tr wire:key="toko-operational-expense-row-{{ $expense['id'] }}">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $expense['reference'] ?: '-' }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $expense['date'] }} · {{ $expense['status'] }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $expense['type'] }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $expense['description'] }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $expense['payment_method'] ?: '-' }} · {{ $expense['bank_code'] ?: '-' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($expense['amount']) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <x-actions.icon-button wire:click="editOperationalExpense({{ $expense['id'] }})" label="{{ __('Edit') }}">
                                            <x-heroicon-m-pencil-square class="h-5 w-5" />
                                        </x-actions.icon-button>
                                        <x-actions.icon-button wire:click="voidOperationalExpense({{ $expense['id'] }})" wire:confirm="{{ __('Void this operational expense?') }}" variant="danger" label="{{ __('Void') }}">
                                            <x-heroicon-o-no-symbol class="h-5 w-5" />
                                        </x-actions.icon-button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300"><td colspan="6" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400 font-medium">{{ __('No operational expenses yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 flex flex-col gap-2 pt-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Showing :start to :end of :total expenses', ['start' => $idNumber($operationalExpenseTableMeta['start']), 'end' => $idNumber($operationalExpenseTableMeta['end']), 'total' => $idNumber($operationalExpenseTableMeta['total'])]) }}</p>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="button" wire:click="previousOperationalExpensePage" @disabled($operationalExpenseTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">{{ __('Previous') }}</button>
                        @php
                            $operationalExpensePageStart = max(1, $operationalExpenseTableMeta['page'] - 2);
                            $operationalExpensePageEnd = min($operationalExpenseTableMeta['pages'], $operationalExpensePageStart + 4);
                            $operationalExpensePageStart = max(1, $operationalExpensePageEnd - 4);
                        @endphp
                        @if ($operationalExpensePageStart > 1)
                            <button type="button" wire:click="gotoOperationalExpensePage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                            @if ($operationalExpensePageStart > 2)
                                <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                            @endif
                        @endif
                        @for ($pageNumber = $operationalExpensePageStart; $pageNumber <= $operationalExpensePageEnd; $pageNumber++)
                            <button
                                type="button"
                                wire:key="toko-operational-expense-page-{{ $pageNumber }}"
                                wire:click="gotoOperationalExpensePage({{ $pageNumber }})"
                                class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $operationalExpenseTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-200/60/80 text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                            >
                                {{ $idNumber($pageNumber) }}
                            </button>
                        @endfor
                        @if ($operationalExpensePageEnd < $operationalExpenseTableMeta['pages'])
                            @if ($operationalExpensePageEnd < $operationalExpenseTableMeta['pages'] - 1)
                                <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                            @endif
                            <button type="button" wire:click="gotoOperationalExpensePage({{ $operationalExpenseTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($operationalExpenseTableMeta['pages']) }}</button>
                        @endif
                        <button type="button" wire:click="nextOperationalExpensePage" @disabled($operationalExpenseTableMeta['page'] >= $operationalExpenseTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">{{ __('Next') }}</button>
                    </div>
                </div>
            </div>

            <div class="px-4 py-4 ">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Payment History') }}</h3>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div class="relative w-full sm:w-64">
                            <x-heroicon-m-magnifying-glass class="absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                            <input id="toko-payment-history-search" type="search" wire:model.live.debounce.250ms="paymentHistorySearch" placeholder="{{ __('Search payment history...') }}" class="min-h-9 w-full rounded-2xl border border-slate-200/60 bg-slate-50/50 pl-10 pr-4 py-2.5 text-sm transition-all focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-slate-700/50 dark:bg-slate-900/50 dark:focus:border-primary-500 dark:focus:bg-slate-900">
                        </div>
                        @if ($canExport)
                            <x-actions.icon-button href="{{ route('admin.toko.exports.payments') }}" label="{{ __('Export CSV') }}">
                                <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                            </x-actions.icon-button>
                        @endif
                    </div>
                </div>
                <div class="mt-3 overflow-x-auto rounded-2xl shadow-sm mt-4">
                    <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                        <thead class="bg-slate-50 dark:bg-slate-900">
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Invoice') }}</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Method') }}</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Reference') }}</th>
                                <th class="px-4 py-3.5 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($paymentHistoryRows as $payment)
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <td class="px-4 py-3.5 text-slate-900 dark:text-slate-100">{{ $payment['invoice_number'] }}</td>
                                    <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $payment['method'] ?: '-' }} · {{ $payment['bank_code'] ?: '-' }}</td>
                                    <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $payment['reference'] ?: '-' }}</td>
                                    <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($payment['amount']) }}</td>
                                </tr>
                            @empty
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <td colspan="4" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No invoice payments yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 flex flex-col gap-2 pt-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Showing :start to :end of :total payments', ['start' => $idNumber($paymentHistoryTableMeta['start']), 'end' => $idNumber($paymentHistoryTableMeta['end']), 'total' => $idNumber($paymentHistoryTableMeta['total'])]) }}</p>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="button" wire:click="previousPaymentHistoryPage" @disabled($paymentHistoryTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">{{ __('Previous') }}</button>
                        @php
                            $paymentHistoryPageStart = max(1, $paymentHistoryTableMeta['page'] - 2);
                            $paymentHistoryPageEnd = min($paymentHistoryTableMeta['pages'], $paymentHistoryPageStart + 4);
                            $paymentHistoryPageStart = max(1, $paymentHistoryPageEnd - 4);
                        @endphp
                        @if ($paymentHistoryPageStart > 1)
                            <button type="button" wire:click="gotoPaymentHistoryPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                            @if ($paymentHistoryPageStart > 2)
                                <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                            @endif
                        @endif
                        @for ($pageNumber = $paymentHistoryPageStart; $pageNumber <= $paymentHistoryPageEnd; $pageNumber++)
                            <button
                                type="button"
                                wire:key="toko-payment-history-page-{{ $pageNumber }}"
                                wire:click="gotoPaymentHistoryPage({{ $pageNumber }})"
                                class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $paymentHistoryTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-200/60/80 text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                            >
                                {{ $idNumber($pageNumber) }}
                            </button>
                        @endfor
                        @if ($paymentHistoryPageEnd < $paymentHistoryTableMeta['pages'])
                            @if ($paymentHistoryPageEnd < $paymentHistoryTableMeta['pages'] - 1)
                                <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                            @endif
                            <button type="button" wire:click="gotoPaymentHistoryPage({{ $paymentHistoryTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($paymentHistoryTableMeta['pages']) }}</button>
                        @endif
                        <button type="button" wire:click="nextPaymentHistoryPage" @disabled($paymentHistoryTableMeta['page'] >= $paymentHistoryTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">{{ __('Next') }}</button>
                    </div>
                </div>
            </div>
        
            <!-- Payment Method Modal -->
            <div x-show="showPaymentMethodForm" style="display: none" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                <div @click.away="showPaymentMethodForm = false; $wire.resetPaymentMethodForm()" class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Tambah Payment Method') }}</h3>
                        <button @click="showPaymentMethodForm = false; $wire.resetPaymentMethodForm()" class="text-slate-400 hover:text-slate-500">
                            <x-heroicon-m-x-mark class="h-6 w-6" />
                        </button>
                    </div>
                    <div class="px-6 py-4">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Method name') }}</label>
                        <input type="text" wire:model="paymentMethodName" placeholder="{{ __('Method name') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/50">
                        <button type="button" @click="showPaymentMethodForm = false; $wire.resetPaymentMethodForm()" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Batal') }}</button>
                        <x-actions.button wire:click="savePaymentMethod" @click="showPaymentMethodForm = false" variant="primary">{{ __('Simpan') }}</x-actions.button>
                    </div>
                </div>
            </div>

            <!-- Bank Account Modal -->
            <div x-show="showBankAccountForm" style="display: none" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                <div @click.away="showBankAccountForm = false; $wire.resetBankAccountForm()" class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Tambah Bank Account') }}</h3>
                        <button @click="showBankAccountForm = false; $wire.resetBankAccountForm()" class="text-slate-400 hover:text-slate-500">
                            <x-heroicon-m-x-mark class="h-6 w-6" />
                        </button>
                    </div>
                    <div class="grid gap-4 px-6 py-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Code') }}</label>
                            <input type="text" wire:model="bankCode" placeholder="{{ __('Code') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Bank') }}</label>
                            <input type="text" wire:model="bankName" placeholder="{{ __('Bank') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Account number') }}</label>
                            <input type="text" wire:model="bankAccountNumber" placeholder="{{ __('Account number') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Account name') }}</label>
                            <input type="text" wire:model="bankAccountName" placeholder="{{ __('Account name') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/50">
                        <button type="button" @click="showBankAccountForm = false; $wire.resetBankAccountForm()" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Batal') }}</button>
                        <x-actions.button wire:click="saveBankAccount" @click="showBankAccountForm = false" variant="primary">{{ __('Simpan') }}</x-actions.button>
                    </div>
                </div>
            </div>

            <!-- Expense Type Modal -->
            <div x-show="showExpenseTypeForm" style="display: none" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                <div @click.away="showExpenseTypeForm = false; $wire.resetExpenseTypeForm()" class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Tambah Tipe Pengeluaran') }}</h3>
                        <button @click="showExpenseTypeForm = false; $wire.resetExpenseTypeForm()" class="text-slate-400 hover:text-slate-500">
                            <x-heroicon-m-x-mark class="h-6 w-6" />
                        </button>
                    </div>
                    <div class="px-6 py-4">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Tipe Pengeluaran') }}</label>
                        <input type="text" wire:model="expenseTypeName" placeholder="{{ __('Tipe Pengeluaran') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/50">
                        <button type="button" @click="showExpenseTypeForm = false; $wire.resetExpenseTypeForm()" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Batal') }}</button>
                        <x-actions.button wire:click="saveExpenseType" @click="showExpenseTypeForm = false" variant="primary">{{ __('Simpan') }}</x-actions.button>
                    </div>
                </div>
            </div>

            <!-- Operational Expense Modal -->
            <div x-show="showExpenseForm" style="display: none" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                <div @click.away="showExpenseForm = false; $wire.resetOperationalExpenseForm()" class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $editingOperationalExpenseId ? __('Update Expense') : __('Tambah Trx') }}</h3>
                        <button @click="showExpenseForm = false; $wire.resetOperationalExpenseForm()" class="text-slate-400 hover:text-slate-500">
                            <x-heroicon-m-x-mark class="h-6 w-6" />
                        </button>
                    </div>
                    <div class="grid gap-4 px-6 py-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Expense type') }}</label>
                            @if ($expenseTypes !== [])
                                <x-forms.tom-select id="toko-operational-expense-type" wire:model="operationalExpenseType" placeholder="{{ __('Expense type') }}" dropdown-direction="down">
                                    <option value="">{{ __('Expense type') }}</option>
                                    @foreach ($expenseTypes as $type)
                                        <option value="{{ $type['name'] }}">{{ $type['name'] }}</option>
                                    @endforeach
                                </x-forms.tom-select>
                            @else
                                <input type="text" wire:model="operationalExpenseType" placeholder="{{ __('Expense type') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                            @endif
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Amount') }}</label>
                            <input type="number" min="0.01" step="0.01" wire:model="operationalExpenseAmount" placeholder="{{ __('Amount') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Payment method') }}</label>
                            <input type="text" wire:model="operationalExpensePaymentMethod" placeholder="{{ __('Payment method') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Bank code') }}</label>
                            <input type="text" wire:model="operationalExpenseBankCode" placeholder="{{ __('Bank code') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Description') }}</label>
                            <textarea wire:model="operationalExpenseDescription" placeholder="{{ __('Description') }}" class="w-full min-h-20 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white"></textarea>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/50">
                        <button type="button" @click="showExpenseForm = false; $wire.resetOperationalExpenseForm()" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Batal') }}</button>
                        <x-actions.button wire:click="recordOperationalExpense" @click="showExpenseForm = false" variant="primary">{{ $editingOperationalExpenseId ? __('Update') : __('Simpan') }}</x-actions.button>
                    </div>
                </div>
            </div>
        </x-admin.panel>
    @endif