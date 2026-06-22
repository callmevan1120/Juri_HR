    @if ($activePage === 'dashboard')
        <x-admin.panel class="overflow-hidden">
            <div class="px-4 py-3.5 ">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold tracking-tight text-slate-900 dark:text-white">Ringkasan Toko</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Kondisi operasional, master, stok, dan laba rugi dalam satu tampilan.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 font-medium">{{ now()->format('d-m-Y H:i') }}</span>
                        <a href="{{ route('admin.toko.exports.sales') }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                            <x-heroicon-o-arrow-up-tray class="h-4 w-4" /> Export
                        </a>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-slate-200 dark:divide-slate-700">
                <section class="p-2">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Operasional Hari Ini</h3>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Finance</span>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
                        @foreach ($summary as $item)
                            <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-gradient-to-br from-slate-50 to-white p-3 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-300 dark:from-slate-900/80 dark:to-slate-950 dark:bg-slate-950/60">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ $item['label'] }}</p>
                                <p class="mt-1.5 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idMoney($item['value']) }}</p>
                                <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">{{ $item['caption'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                @if (($dashboardOverview['kpis'] ?? []) !== [])
                    <section class="p-2">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Master Toko</h3>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Data</span>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ($dashboardOverview['kpis'] as $item)
                                <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-gradient-to-br from-slate-50 to-white p-3 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-300 dark:from-slate-900/80 dark:to-slate-950 dark:bg-slate-950/60">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ $item['label'] }}</p>
                                    <p class="mt-1.5 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber($item['value']) }}</p>
                                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ $item['caption'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            @if (($dashboardOverview['stock_kpis'] ?? []) !== [] || ($dashboardOverview['profit_kpis'] ?? []) !== [])
                <div class="divide-y divide-slate-200 dark:divide-slate-700 ">
                    @if (($dashboardOverview['stock_kpis'] ?? []) !== [])
                        <section class="p-2">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Stok & Valuasi</h3>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200">Inventory</span>
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                                @foreach ($dashboardOverview['stock_kpis'] as $item)
                                    <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-gradient-to-br from-slate-50 to-white p-3 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-300 dark:from-slate-900/80 dark:to-slate-950 dark:bg-slate-950/60">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ $item['label'] }}</p>
                                        <p class="mt-1.5 text-base font-bold tracking-tight text-slate-900 dark:text-white">
                                            @if (str_contains(strtolower($item['label']), 'estimasi') || str_contains(strtolower($item['label']), 'omzet'))
                                                {{ $idMoney($item['value']) }}
                                            @else
                                                {{ $idNumber($item['value']) }}
                                            @endif
                                        </p>
                                        <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ $item['caption'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if (($dashboardOverview['profit_kpis'] ?? []) !== [])
                        <section class="p-2">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Laba/Rugi</h3>
                                <span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-950/50 dark:text-sky-200">Insight</span>
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                                @foreach ($dashboardOverview['profit_kpis'] as $item)
                                    <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-gradient-to-br from-slate-50 to-white p-3 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-300 dark:from-slate-900/80 dark:to-slate-950 dark:bg-slate-950/60">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ $item['label'] }}</p>
                                        <p class="mt-1.5 text-base font-bold tracking-tight text-slate-900 dark:text-white">
                                            {{ ($item['format'] ?? 'number') === 'percent' ? $idPercent($item['value']) : $idMoney($item['value']) }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ $item['caption'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            @endif
        </x-admin.panel>

            <div class="mt-3 grid gap-2 xl:grid-cols-2">
                <x-admin.panel class="border-0 shadow-sm  bg-white dark:bg-slate-900">
                    <div class="px-4 py-3.5 ">
                        <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">5 Barang dengan Stok paling banyak</h2>
                    </div>
                    <div class="overflow-x-auto rounded-2xl shadow-sm mt-4">
                        <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                            <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <th class="px-4 py-3 text-left">#</th>
                                    <th class="px-4 py-3 text-left">Barang</th>
                                    <th class="px-4 py-3 text-right">Stok</th>
                                    <th class="px-4 py-3 text-right">Persentase</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($dashboardOverview['top_stock'] as $index => $row)
                                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">{{ $row['name'] }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($row['balance'], 3) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="rounded-xl bg-emerald-50 px-2 py-1 text-xs font-bold tracking-wide text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">{{ $idPercent($row['percent']) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300"><td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No stock data yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.panel>

                <x-admin.panel class="border-0 shadow-sm  bg-white dark:bg-slate-900">
                    <div class="px-4 py-3.5 ">
                        <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">5 Barang Keluar Terbanyak</h2>
                    </div>
                    <div class="overflow-x-auto rounded-2xl shadow-sm mt-4">
                        <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                            <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <th class="px-4 py-3 text-left">#</th>
                                    <th class="px-4 py-3 text-left">Barang</th>
                                    <th class="px-4 py-3 text-right">Terjual</th>
                                    <th class="px-4 py-3 text-right">Persentase</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($dashboardOverview['top_outgoing'] as $index => $row)
                                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">{{ $row['name'] }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($row['quantity'], 3) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="rounded-xl bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-950/50 dark:text-sky-200">{{ $idPercent($row['percent']) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300"><td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No product sales yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.panel>
            </div>

            <div class="mt-3 grid gap-2 xl:grid-cols-2">
                <x-admin.panel class="border-0 shadow-sm  bg-white dark:bg-slate-900">
                    <div class="px-4 py-3.5 ">
                        <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Hutang dan Piutang (Rp)</h2>
                    </div>
                    <div class="grid gap-2 p-3 md:grid-cols-2">
                        <div>
                            <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Hutang</h3>
                            <div class="mt-3 space-y-3">
                                @foreach ($dashboardOverview['aging'] as $row)
                                    <div class="flex items-center justify-between gap-2 text-sm">
                                        <span class="text-slate-600 dark:text-slate-300">{{ $row['label'] }}</span>
                                        <span class="font-bold tracking-tight text-slate-900 dark:text-white">{{ $idMoney($row['ap']) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Piutang</h3>
                            <div class="mt-3 space-y-3">
                                @foreach ($dashboardOverview['aging'] as $row)
                                    <div class="flex items-center justify-between gap-2 text-sm">
                                        <span class="text-slate-600 dark:text-slate-300">{{ $row['label'] }}</span>
                                        <span class="font-bold tracking-tight text-slate-900 dark:text-white">{{ $idMoney($row['ar']) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </x-admin.panel>

                <x-admin.panel class="border-0 shadow-sm  bg-white dark:bg-slate-900">
                    <div class="px-4 py-3.5 ">
                        <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Ringkasan (Rp)</h2>
                    </div>
                    <div class="overflow-x-auto rounded-2xl shadow-sm mt-4">
                        <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                            <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <th class="px-4 py-3 text-left"></th>
                                    <th class="px-4 py-3 text-right">Bulan ini</th>
                                    <th class="px-4 py-3 text-right">Bulan lalu</th>
                                    <th class="px-4 py-3 text-right">Tahun ini</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach ($dashboardOverview['summary'] as $row)
                                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">{{ $row['label'] }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ $idMoney($row['current_month']) }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ $idMoney($row['last_month']) }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ $idMoney($row['current_year']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-admin.panel>
            </div>

            <div class="mt-3">
                <x-admin.panel class="border-0 shadow-sm  bg-white dark:bg-slate-900">
                    <div class="flex flex-col gap-1 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Monthly Net Trend</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Income, Cost, and Net movement for the last six months.</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto rounded-2xl shadow-sm mt-4">
                        <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                            <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <th class="px-4 py-3 text-left">Month</th>
                                    <th class="px-4 py-3 text-right">Income</th>
                                    <th class="px-4 py-3 text-right">Cost</th>
                                    <th class="px-4 py-3 text-right">Net</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse (($dashboardOverview['monthly_net_trend'] ?? []) as $row)
                                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">{{ $row['month'] }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ $idMoney($row['income']) }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">{{ $idMoney($row['cost']) }}</td>
                                        <td class="px-4 py-3 text-right font-bold tracking-tight text-slate-900 dark:text-white">{{ $idMoney($row['net']) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <x-actions.icon-button href="{{ $row['report_url'] }}" label="{{ __('Open report') }}">
                                                <x-heroicon-o-chart-bar-square class="h-5 w-5" />
                                            </x-actions.icon-button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300"><td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No monthly trend yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.panel>
            </div>
    @endif
    @if ($activePage === 'dashboard')
    @if ($tokoReport)
    <x-admin.panel class="border-0 shadow-sm  bg-white dark:bg-slate-900">
        <div class="px-4 py-3.5 ">
            <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Insight Charts') }}</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Sales, purchase, customer, product, and stock signals for replacing the legacy toko reports.') }}</p>
        </div>

        @php
            $salesTrendPreview = array_reverse(array_slice($tokoReport['sales']['by_date'], 0, 7));
            $purchaseTrendPreview = array_reverse(array_slice($tokoReport['purchases']['by_date'], 0, 7));
            $topProductsPreview = array_slice($tokoReport['sales']['by_product'], 0, 5);
            $tokoChartPayload = [
                'sales' => [
                    'labels' => array_map(fn ($row) => (string) $row['date'], $salesTrendPreview),
                    'values' => array_map(fn ($row) => (float) $row['total'], $salesTrendPreview),
                ],
                'purchases' => [
                    'labels' => array_map(fn ($row) => (string) $row['date'], $purchaseTrendPreview),
                    'values' => array_map(fn ($row) => (float) $row['total'], $purchaseTrendPreview),
                ],
                'products' => [
                    'labels' => array_map(fn ($row) => (string) $row['product'], $topProductsPreview),
                    'values' => array_map(fn ($row) => (float) $row['total'], $topProductsPreview),
                ],
                'revenueMix' => $dashboardOverview['revenue_mix'] ?? ['labels' => [], 'values' => []],
                'expenseMix' => $dashboardOverview['expense_mix'] ?? ['labels' => [], 'values' => []],
            ];
        @endphp

        <div
            class="grid gap-2 p-3 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,1fr)]"
            data-toko-dashboard-charts
            data-chart-payload='@json($tokoChartPayload)'
        >
            <div>
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Sales Trend') }}</h3>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $idMoney($tokoReport['sales']['total']) }}</span>
                </div>
                <div class="mt-3 h-40" wire:ignore>
                    <canvas data-toko-sales-chart role="img" aria-label="{{ __('Toko sales trend chart') }}"></canvas>
                </div>
                @if ($salesTrendPreview === [])
                    <div class="mt-2">
                        <p class="self-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No sales trend yet.') }}</p>
                    </div>
                @endif
            </div>

            <div>
                <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Purchase Trend') }}</h3>
                <div class="mt-3 h-40" wire:ignore>
                    <canvas data-toko-purchase-chart role="img" aria-label="{{ __('Toko purchase trend chart') }}"></canvas>
                </div>
                @if ($purchaseTrendPreview === [])
                    <div class="mt-2">
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No purchase trend yet.') }}</p>
                    </div>
                @endif
            </div>

            <div>
                <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Top Products') }}</h3>
                <div class="mt-3 h-40" wire:ignore>
                    <canvas data-toko-products-chart role="img" aria-label="{{ __('Toko top products chart') }}"></canvas>
                </div>
                @if ($topProductsPreview === [])
                    <div class="mt-2">
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No product sales yet.') }}</p>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-3">
                <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Risk Watch') }}</h3>
                <div class="mt-3 grid gap-2 sm:grid-cols-3">
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3.5 text-sm dark:bg-slate-900">
                        <span class="font-semibold text-slate-600 dark:text-slate-300">{{ __('AR') }}</span>
                        <span class="text-slate-950 dark:text-white">{{ $idMoney($tokoReport['aging']['accounts_receivable']) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3.5 text-sm dark:bg-slate-900">
                        <span class="font-semibold text-slate-600 dark:text-slate-300">{{ __('AP') }}</span>
                        <span class="text-slate-950 dark:text-white">{{ $idMoney($tokoReport['aging']['accounts_payable']) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3.5 text-sm dark:bg-slate-900">
                        <span class="font-semibold text-slate-600 dark:text-slate-300">{{ __('Low Stock') }}</span>
                        <span class="text-slate-950 dark:text-white">{{ $idNumber(count($tokoReport['low_stock'])) }}</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3 grid gap-2 lg:grid-cols-2">
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Pendapatan Retail Vs Nota</h3>
                    <div class="mt-2 grid gap-2 text-xs text-slate-600 dark:text-slate-300 sm:grid-cols-3">
                        @foreach (($dashboardOverview['revenue_mix']['labels'] ?? []) as $index => $label)
                            <div class="rounded-xl bg-slate-50 px-2 py-1 dark:bg-slate-900">
                                <span class="block font-semibold text-slate-800 dark:text-slate-100">{{ $label }}</span>
                                <span>{{ $idMoney(($dashboardOverview['revenue_mix']['values'][$index] ?? 0)) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 h-72" wire:ignore>
                        <canvas data-toko-revenue-mix-chart role="img" aria-label="{{ __('Toko revenue mix chart') }}"></canvas>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Pengeluaran</h3>
                    <div class="mt-3 h-72" wire:ignore>
                        <canvas data-toko-expense-chart role="img" aria-label="{{ __('Toko expense chart') }}"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </x-admin.panel>
    @endif

    <div class="grid gap-2 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
        <x-admin.panel class="border-0 shadow-sm  bg-white dark:bg-slate-900">
            <div class="flex flex-col gap-2 px-4 py-3.5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Transaction Command Center') }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Daily transaction monitor for POS, purchases, stock, cash, reports, and migration cutover.') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-actions.icon-button href="{{ route('admin.toko.pos') }}" variant="primary" label="{{ __('Open POS') }}">
                        <x-heroicon-o-banknotes class="h-5 w-5" />
                    </x-actions.icon-button>
                    <x-actions.icon-button href="{{ route('admin.toko.purchases') }}" label="{{ __('Purchases') }}">
                        <x-heroicon-o-document-text class="h-5 w-5" />
                    </x-actions.icon-button>
                    <x-actions.icon-button href="{{ route('admin.toko.reports') }}" label="{{ __('Reports') }}">
                        <x-heroicon-o-document-chart-bar class="h-5 w-5" />
                    </x-actions.icon-button>
                </div>
            </div>

            <div class="grid gap-2 px-4 py-4 lg:grid-cols-2">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Recent POS Invoices') }}</h3>
                        <x-actions.icon-button href="{{ route('admin.toko.pos') }}" label="{{ __('View POS') }}">
                            <x-heroicon-o-eye class="h-5 w-5" />
                        </x-actions.icon-button>
                    </div>
                    <div class="mt-3 space-y-2">
                        @forelse (array_slice($recentPosInvoices, 0, 5) as $invoice)
                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/40 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm ">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $invoice['number'] }}</span>
                                    <span class="text-slate-600 dark:text-slate-300">{{ $idMoney($invoice['total']) }}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $invoice['issued_at'] ?? '-' }} · {{ $invoice['status'] }}</p>
                            </div>
                        @empty
                            <p class="rounded-2xl bg-slate-50 dark:bg-slate-800/40 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm text-slate-500 dark:text-slate-400">{{ __('No POS invoices yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Recent Purchases') }}</h3>
                        <x-actions.icon-button href="{{ route('admin.toko.purchases') }}" label="{{ __('View Purchases') }}">
                            <x-heroicon-o-eye class="h-5 w-5" />
                        </x-actions.icon-button>
                    </div>
                    <div class="mt-3 space-y-2">
                        @forelse (array_slice($purchaseBillRows, 0, 5) as $bill)
                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/40 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm ">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $bill['number'] }}</span>
                                    <span class="text-slate-600 dark:text-slate-300">{{ $idMoney($bill['total']) }}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $bill['vendor'] }} · {{ $bill['status'] }}</p>
                            </div>
                        @empty
                            <p class="rounded-2xl bg-slate-50 dark:bg-slate-800/40 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm text-slate-500 dark:text-slate-400">{{ __('No purchases yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-admin.panel>

        <x-admin.panel class="border-0 shadow-sm  bg-white dark:bg-slate-900">
            <div class="px-4 py-3.5 ">
                <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Quick Actions') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Go straight to the correct transaction menu.') }}</p>
            </div>
            <div class="grid gap-2 p-3 sm:grid-cols-2">
                @php
                    $quickActions = [
                    ['label' => __('New Sale'), 'href' => route('admin.toko.pos'), 'caption' => __('POS cart, payment, invoice print.')],
                    ['label' => __('Receive Purchase'), 'href' => route('admin.toko.purchases'), 'caption' => __('Vendor bill and stock-in.')],
                    ['label' => __('Stock Movement'), 'href' => route('admin.toko.inventory'), 'caption' => __('Manual stock, returns, opname.')],
                    ['label' => __('Cash & Payments'), 'href' => route('admin.toko.cash'), 'caption' => __('Payment history and expenses.')],
                    ['label' => __('Reports'), 'href' => route('admin.toko.reports'), 'caption' => __('Sales, purchase, stock, AR/AP.')],
                    ];
                    $migrationNav = collect($tokoNavigation)->firstWhere('key', 'migration');
                    if ($migrationNav) {
                        $quickActions[] = ['label' => __('Migration'), 'href' => $migrationNav['href'], 'caption' => __('Import preview and cutover.')];
                    }
                @endphp

                @foreach ($quickActions as $action)
                    <a href="{{ $action['href'] }}" class="rounded-2xl bg-slate-50 dark:bg-slate-800/40 shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 text-sm hover:bg-slate-50 dark:hover:bg-slate-900">
                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $action['label'] }}</span>
                        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $action['caption'] }}</span>
                    </a>
                @endforeach
            </div>
        </x-admin.panel>
    </div>
    @endif