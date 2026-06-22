@if ($activePage === 'reports')
    <x-admin.panel class="border-0 shadow-sm bg-white dark:bg-slate-900">
        <div class="border-b border-slate-200/60/80 px-4 py-3.5 dark:border-slate-700/50/80">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Toko Reports') }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Sales, purchases, gross profit, stock card, low stock, and AR/AP aging.') }}</p>
                </div>
                @if ($canExport)
                    <div class="flex flex-wrap gap-2">
                        <x-actions.icon-button href="{{ route('admin.toko.exports.report-sales', $reportExportQuery) }}" label="{{ __('Sales CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                        <x-actions.icon-button href="{{ route('admin.toko.exports.report-purchases', $reportExportQuery) }}" label="{{ __('Purchase CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                        <x-actions.icon-button href="{{ route('admin.toko.exports.report-gross-profit', $reportExportQuery) }}" label="{{ __('Profit CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                        <x-actions.icon-button href="{{ route('admin.toko.exports.report-operational-expenses', $reportExportQuery) }}" label="{{ __('Operational CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                        <x-actions.icon-button href="{{ route('admin.toko.exports.report-inventory-valuation', $reportExportQuery) }}" label="{{ __('Inventory CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                        <x-actions.icon-button href="{{ route('admin.toko.exports.report-profit-loss', $reportExportQuery) }}" label="{{ __('P&L CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                        <x-actions.icon-button href="{{ route('admin.toko.exports.report-ar-aging') }}" label="{{ __('AR Aging CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                        <x-actions.icon-button href="{{ route('admin.toko.exports.report-ap-aging') }}" label="{{ __('AP Aging CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                    </div>
                @endif
            </div>
        </div>

        @if ($tokoReport)
            <div class="grid gap-2 border-b border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                <div>
                    <label for="toko-report-from" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Report Period') }}</label>
                    <input id="toko-report-from" type="date" wire:model.live="reportFromDate" class="min-h-9 w-full rounded-xl border-slate-200 dark:border-slate-700/50 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-white">
                </div>
                <div>
                    <label for="toko-report-to" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Until') }}</label>
                    <input id="toko-report-to" type="date" wire:model.live="reportToDate" class="min-h-9 w-full rounded-xl border-slate-200 dark:border-slate-700/50 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-white">
                </div>
                <div class="self-end rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm text-slate-600 dark:border-slate-700/50/80 dark:text-slate-300">
                    {{ ($reportPeriod['from'] ?? '') !== '' ? $reportPeriod['from'] : __('All start') }}
                    <span class="mx-1 text-slate-400">-</span>
                    {{ ($reportPeriod['to'] ?? '') !== '' ? $reportPeriod['to'] : __('All end') }}
                </div>
            </div>

            <div class="grid gap-2 border-b border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80 sm:grid-cols-3">
                @foreach ([
                    __('Sales') => $tokoReport['sales']['total'],
                    __('Purchases') => $tokoReport['purchases']['total'],
                    __('Gross Profit') => $tokoReport['gross_profit']['estimated'],
                    __('Stock Value') => $tokoReport['stock_valuation']['estimated'],
                    __('AR') => $tokoReport['aging']['accounts_receivable'],
                    __('AP') => $tokoReport['aging']['accounts_payable'],
                    __('Low Stock') => count($tokoReport['low_stock']),
                ] as $label => $value)
                    <div class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 dark:border-slate-700/50/80">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ $label }}</p>
                        <p class="mt-1 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber((float) $value) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-2 px-4 py-4 lg:grid-cols-2">
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Low Stock') }}</h3>
                    <div class="mt-2 space-y-2">
                        @forelse (array_slice($tokoReport['low_stock'], 0, 5) as $product)
                            <div class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm dark:border-slate-700/50/80">
                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $product['name'] }}</span>
                                <span class="text-slate-500 dark:text-slate-400 font-medium"> · {{ $idNumber($product['balance'], 3) }} / {{ $idNumber($product['reorder_point'], 3) }}</span>
                            </div>
                        @empty
                            <p class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm text-slate-500 dark:border-slate-700/50/80 dark:text-slate-400">{{ __('No low-stock products.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Stock Card') }}</h3>
                    <div class="mt-2 space-y-2">
                        @forelse (array_slice($tokoReport['stock_card'], 0, 5) as $movement)
                            <div class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm dark:border-slate-700/50/80">
                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $movement['product'] }}</span>
                                <span class="text-slate-500 dark:text-slate-400 font-medium"> · {{ $movement['type'] }} · {{ $idNumber($movement['quantity'], 3) }}</span>
                            </div>
                        @empty
                            <p class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm text-slate-500 dark:border-slate-700/50/80 dark:text-slate-400">{{ __('No stock movements yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Stock Adjustment Report') }}</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Stock opname adjustment history with previous, counted, and delta quantities.') }}</p>
                    </div>
                    <x-actions.icon-button href="{{ route('admin.toko.stock-adjustments.print') }}" target="_blank" label="{{ __('Print Adjustments') }}">
                        <x-heroicon-o-printer class="h-5 w-5" />
                    </x-actions.icon-button>
                </div>

                <div class="mt-3 overflow-x-auto rounded-2xl border border-slate-200/60 dark:border-slate-700/50 shadow-sm mt-4">
                    <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                        <thead class="bg-slate-50 dark:bg-slate-900">
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Date') }}</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Product') }}</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Reference') }}</th>
                                <th class="px-4 py-3.5 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Previous') }}</th>
                                <th class="px-4 py-3.5 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Counted') }}</th>
                                <th class="px-4 py-3.5 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Delta') }}</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($stockAdjustmentReportRows as $movement)
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $movement['date'] }}</td>
                                    <td class="px-4 py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $movement['product'] }}</td>
                                    <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $movement['reference'] }}</td>
                                    <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($movement['previous_quantity'], 3) }}</td>
                                    <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($movement['counted_quantity'], 3) }}</td>
                                    <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($movement['delta'], 3) }}</td>
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $movement['notes'] }}</td>
                                </tr>
                            @empty
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No stock adjustments yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border-t border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Purchase Recap Report') }}</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Legacy purchase recap with bill status, vendor, cancellation note, total, and line items.') }}</p>
                    </div>
                    @if ($canExport)
                        <x-actions.icon-button href="{{ route('admin.toko.exports.purchases') }}" label="{{ __('Purchase CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                    @endif
                </div>

                <div class="mt-3 overflow-x-auto rounded-2xl border border-slate-200/60 dark:border-slate-700/50 shadow-sm mt-4">
                    <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                        <thead class="bg-slate-50 dark:bg-slate-900">
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Bill') }}</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Vendor') }}</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Status') }}</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Note') }}</th>
                                <th class="px-4 py-3.5 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($purchaseBillRows as $bill)
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $bill['number'] }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $bill['issued_at'] ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $bill['vendor'] }}</td>
                                    <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $bill['status'] }}</td>
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $bill['cancel_reason'] ?: '-' }}</td>
                                    <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($bill['total']) }}</td>
                                </tr>
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <td colspan="5" class="bg-slate-50 px-4 py-3.5 dark:bg-slate-900">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Line Items') }}</p>
                                        <div class="mt-2 grid gap-2 lg:grid-cols-2">
                                            @foreach ($bill['items'] as $item)
                                                <div class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 text-xs dark:border-slate-700/50/80 dark:bg-slate-950">
                                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $item['description'] }}</p>
                                                    <p class="mt-1 text-slate-600 dark:text-slate-300">{{ $idNumber($item['quantity'], 3) }} x {{ $idNumber($item['unit_cost']) }} = {{ $idNumber($item['line_total']) }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No purchases yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border-t border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Product Movement Report') }}</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Period-aware stock movement audit by product, source, reference, quantity, and cost.') }}</p>
                    </div>
                    @if ($canExport)
                        <x-actions.icon-button href="{{ route('admin.toko.exports.report-product-movements', $reportExportQuery) }}" label="{{ __('Movement CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                    @endif
                </div>
                <div class="mt-3 overflow-x-auto rounded-2xl border border-slate-200/60 dark:border-slate-700/50 shadow-sm mt-4">
                    <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                        <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <th scope="col" class="px-4 py-3.5 text-left">{{ __('Date') }}</th>
                                <th scope="col" class="px-4 py-3.5 text-left">{{ __('Product') }}</th>
                                <th scope="col" class="px-4 py-3.5 text-left">{{ __('Type') }}</th>
                                <th scope="col" class="px-4 py-3.5 text-right">{{ __('Qty') }}</th>
                                <th scope="col" class="px-4 py-3.5 text-right">{{ __('Unit Cost') }}</th>
                                <th scope="col" class="px-4 py-3.5 text-left">{{ __('Ref / Notes') }}</th>
                                <th scope="col" class="px-4 py-3.5 text-left">{{ __('Source') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($productMovementReportRows as $row)
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $row['date'] }}</td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['product'] }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $row['sku'] }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="rounded-xl bg-slate-100 px-2 py-1 text-xs font-semibold uppercase text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $row['type'] }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($row['quantity'], 3) }}</td>
                                    <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($row['unit_cost']) }}</td>
                                    <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-300">
                                        {{ $row['reference'] }}<br>{{ $row['notes'] }}
                                    </td>
                                    <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ str_replace('_', ' ', Str::title($row['source'])) }}</td>
                                </tr>
                            @empty
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No movements recorded.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Showing :start to :end of :total product movements', ['start' => $idNumber($productMovementTableMeta['start']), 'end' => $idNumber($productMovementTableMeta['end']), 'total' => $idNumber($productMovementTableMeta['total'])]) }}</p>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="button" wire:click="previousProductMovementPage" @disabled($productMovementTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">{{ __('Previous') }}</button>
                        @php
                            $productMovementPageStart = max(1, $productMovementTableMeta['page'] - 2);
                            $productMovementPageEnd = min($productMovementTableMeta['pages'], $productMovementPageStart + 4);
                            $productMovementPageStart = max(1, $productMovementPageEnd - 4);
                        @endphp
                        @if ($productMovementPageStart > 1)
                            <button type="button" wire:click="gotoProductMovementPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                            @if ($productMovementPageStart > 2)
                                <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                            @endif
                        @endif
                        @for ($pageNumber = $productMovementPageStart; $pageNumber <= $productMovementPageEnd; $pageNumber++)
                            <button
                                type="button"
                                wire:key="toko-product-movement-page-{{ $pageNumber }}"
                                wire:click="gotoProductMovementPage({{ $pageNumber }})"
                                class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $productMovementTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-200/60/80 text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                            >
                                {{ $idNumber($pageNumber) }}
                            </button>
                        @endfor
                        @if ($productMovementPageEnd < $productMovementTableMeta['pages'])
                            @if ($productMovementPageEnd < $productMovementTableMeta['pages'] - 1)
                                <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                            @endif
                            <button type="button" wire:click="gotoProductMovementPage({{ $productMovementTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($productMovementTableMeta['pages']) }}</button>
                        @endif
                        <button type="button" wire:click="nextProductMovementPage" @disabled($productMovementTableMeta['page'] >= $productMovementTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">{{ __('Next') }}</button>
                    </div>
                </div>
            </div>

            <div class="grid gap-2 border-t border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80 lg:grid-cols-2">
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Sales By Product') }}</h3>
                    <div class="mt-2 space-y-2">
                        @forelse (array_slice($tokoReport['sales']['by_product'], 0, 5) as $row)
                            <div class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm dark:border-slate-700/50/80">
                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['product'] }}</span>
                                <span class="text-slate-500 dark:text-slate-400 font-medium"> · {{ $idNumber($row['quantity'], 3) }} · {{ $idNumber($row['total']) }}</span>
                            </div>
                        @empty
                            <p class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm text-slate-500 dark:border-slate-700/50/80 dark:text-slate-400">{{ __('No sales yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Sales By Customer') }}</h3>
                    <div class="mt-2 space-y-2">
                        @forelse (array_slice($tokoReport['sales']['by_customer'], 0, 5) as $row)
                            <div class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm dark:border-slate-700/50/80">
                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['customer'] }}</span>
                                <span class="text-slate-500 dark:text-slate-400 font-medium"> · {{ $idNumber($row['total']) }}</span>
                            </div>
                        @empty
                            <p class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm text-slate-500 dark:border-slate-700/50/80 dark:text-slate-400">{{ __('No customers yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid gap-2 border-t border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80 lg:grid-cols-3">
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Purchases By Date') }}</h3>
                    <div class="mt-2 space-y-2">
                        @forelse (array_slice($tokoReport['purchases']['by_date'], 0, 5) as $row)
                            <div class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm dark:border-slate-700/50/80">
                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['date'] }}</span>
                                <span class="text-slate-500 dark:text-slate-400 font-medium"> · {{ $idNumber($row['total']) }}</span>
                            </div>
                        @empty
                            <p class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm text-slate-500 dark:border-slate-700/50/80 dark:text-slate-400">{{ __('No purchases yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Purchases By Vendor') }}</h3>
                    <div class="mt-2 space-y-2">
                        @forelse (array_slice($tokoReport['purchases']['by_vendor'], 0, 5) as $row)
                            <div class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm dark:border-slate-700/50/80">
                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['vendor'] }}</span>
                                <span class="text-slate-500 dark:text-slate-400 font-medium"> · {{ $idNumber($row['total']) }}</span>
                            </div>
                        @empty
                            <p class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm text-slate-500 dark:border-slate-700/50/80 dark:text-slate-400">{{ __('No purchases yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Purchases By Product') }}</h3>
                    <div class="mt-2 space-y-2">
                        @forelse (array_slice($tokoReport['purchases']['by_product'], 0, 5) as $row)
                            <div class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm dark:border-slate-700/50/80">
                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['product'] }}</span>
                                <span class="text-slate-500 dark:text-slate-400 font-medium"> · {{ $idNumber($row['quantity'], 3) }} · {{ $idNumber($row['total']) }}</span>
                            </div>
                        @empty
                            <p class="rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm text-slate-500 dark:border-slate-700/50/80 dark:text-slate-400">{{ __('No purchases yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Operational Expense Report') }}</h3>
                    @if ($canExport)
                        <x-actions.icon-button href="{{ route('admin.toko.exports.report-operational-expenses', $reportExportQuery) }}" label="{{ __('Export CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                    @endif
                </div>
                <div class="mt-3 overflow-x-auto rounded-2xl border border-slate-200/60 dark:border-slate-700/50 shadow-sm mt-4">
                    <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                        <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <th scope="col" class="px-4 py-3.5 text-left">{{ __('Date') }}</th>
                                <th scope="col" class="px-4 py-3.5 text-left">{{ __('Type') }}</th>
                                <th scope="col" class="px-4 py-3.5 text-left">{{ __('Description') }}</th>
                                <th scope="col" class="px-4 py-3.5 text-left">{{ __('Payment') }}</th>
                                <th scope="col" class="px-4 py-3.5 text-right">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($operationalExpenseReportRows as $row)
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $row['date'] }}</td>
                                    <td class="px-4 py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['type'] }}</td>
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $row['description'] }}</td>
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $row['payment_method'] }} · {{ $row['bank_code'] }}</td>
                                    <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($row['amount']) }}</td>
                                </tr>
                            @empty
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No operational expenses yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Showing :start to :end of :total operational expenses', ['start' => $idNumber($operationalExpenseReportTableMeta['start']), 'end' => $idNumber($operationalExpenseReportTableMeta['end']), 'total' => $idNumber($operationalExpenseReportTableMeta['total'])]) }}</p>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="button" wire:click="previousOperationalExpenseReportPage" @disabled($operationalExpenseReportTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">{{ __('Previous') }}</button>
                        @php
                            $operationalExpensePageStart = max(1, $operationalExpenseReportTableMeta['page'] - 2);
                            $operationalExpensePageEnd = min($operationalExpenseReportTableMeta['pages'], $operationalExpensePageStart + 4);
                            $operationalExpensePageStart = max(1, $operationalExpensePageEnd - 4);
                        @endphp
                        @if ($operationalExpensePageStart > 1)
                            <button type="button" wire:click="gotoOperationalExpenseReportPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                            @if ($operationalExpensePageStart > 2)
                                <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                            @endif
                        @endif
                        @for ($pageNumber = $operationalExpensePageStart; $pageNumber <= $operationalExpensePageEnd; $pageNumber++)
                            <button
                                type="button"
                                wire:key="toko-operational-expense-page-{{ $pageNumber }}"
                                wire:click="gotoOperationalExpenseReportPage({{ $pageNumber }})"
                                class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $operationalExpenseReportTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-200/60/80 text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                            >
                                {{ $idNumber($pageNumber) }}
                            </button>
                        @endfor
                        @if ($operationalExpensePageEnd < $operationalExpenseReportTableMeta['pages'])
                            @if ($operationalExpensePageEnd < $operationalExpenseReportTableMeta['pages'] - 1)
                                <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                            @endif
                            <button type="button" wire:click="gotoOperationalExpenseReportPage({{ $operationalExpenseReportTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($operationalExpenseReportTableMeta['pages']) }}</button>
                        @endif
                        <button type="button" wire:click="nextOperationalExpenseReportPage" @disabled($operationalExpenseReportTableMeta['page'] >= $operationalExpenseReportTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">{{ __('Next') }}</button>
                    </div>
                </div>
            </div>
        @endif
    </x-admin.panel>
@endif
