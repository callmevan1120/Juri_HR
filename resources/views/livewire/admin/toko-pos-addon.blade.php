<x-admin.page-shell
    :title="$pageTitle"
    :description="__('Premium store, POS, inventory, purchase, sales, invoice, and legacy migration workspace.')"
    :show-description="true"
    data-toko-addon-flag="toko_pos"
    data-toko-nav-addon-flag="toko_pos"
>
    @php
        $idNumber = fn ($value, int $decimals = 0, bool $trimZeros = true) => \App\Helpers::formatNumberId($value, $decimals, $trimZeros);
        $idMoney = fn ($value, int $decimals = 0) => \App\Helpers::formatRupiah($value, $decimals);
        $idPercent = fn ($value, int $decimals = 2, bool $trimZeros = true) => \App\Helpers::formatPercentId($value, $decimals, $trimZeros);
        $idUnit = fn ($value, string $unit, int $decimals = 3) => \App\Helpers::formatUnitId($value, $unit, $decimals);
    @endphp

    <div data-toko-addon-flag="toko_pos" data-toko-nav-addon-flag="toko_pos" class="hidden"></div>

        <x-slot name="toolbar">
        <x-admin.page-tools grid-class="grid grid-cols-1 items-end gap-3 lg:grid-cols-12">
            <div class="lg:col-span-4 flex flex-col sm:flex-row gap-2">
                @if (($companyOptions ?? []) !== [])
                    <div class="w-full sm:w-1/2">
                        <label class="sr-only" for="toko-company-selector">{{ __('Company') }}</label>
                        <x-forms.tom-select id="toko-company-selector" wire:model.live="selectedCompanyId" placeholder="{{ __('Company') }}" :disabled="count($companyOptions) === 1" dropdown-direction="down">
                            @foreach ($companyOptions as $companyOption)
                                <option value="{{ $companyOption['id'] }}">{{ $companyOption['name'] }}</option>
                            @endforeach
                        </x-forms.tom-select>
                    </div>
                @endif
                @if (($branchOptions ?? []) !== [])
                    <div class="w-full sm:w-1/2">
                        <label class="sr-only" for="toko-branch-selector">{{ __('Branch / Store') }}</label>
                        <x-forms.tom-select id="toko-branch-selector" wire:model.live="selectedBranchId" placeholder="{{ __('Semua branch/store') }}" dropdown-direction="down">
                            <option value="">{{ __('Semua branch/store') }}</option>
                            @foreach ($branchOptions as $branchOption)
                                <option value="{{ $branchOption['id'] }}">{{ $branchOption['label'] }}</option>
                            @endforeach
                        </x-forms.tom-select>
                    </div>
                @endif
            </div>
            <div class="lg:col-span-8 flex justify-end">
                <div class="flex w-full items-center gap-1 overflow-x-auto rounded-xl bg-slate-100 p-1 text-sm font-semibold dark:bg-slate-800 shadow-inner">
                    @foreach ($tokoNavigation as $nav)
                        <a href="{{ $nav['href'] }}" @class([
                            'shrink-0 rounded-lg px-3 py-1.5 transition-all duration-200 text-center flex-1 min-w-[max-content]',
                            'bg-white text-primary-700 shadow shadow-primary-500/10 ring-1 ring-slate-200/50 dark:bg-slate-900 dark:text-primary-300 dark:ring-slate-700' => $nav['active'],
                            'text-slate-500 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50' => ! $nav['active'],
                        ])>
                            {{ $nav['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </x-admin.page-tools>
    </x-slot>

    @if ($activePage === 'dashboard')
        <x-admin.panel class="overflow-hidden">
            <div class="border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">Ringkasan Toko</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Kondisi operasional, master, stok, dan laba rugi dalam satu tampilan.</p>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ now()->format('d-m-Y H:i') }}</span>
                </div>
            </div>

            <div class="divide-y divide-slate-200 dark:divide-slate-700">
                <section class="p-2">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Operasional Hari Ini</h3>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Finance</span>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
                        @foreach ($summary as $item)
                            <div class="rounded-xl border border-slate-100/80 bg-gradient-to-br from-slate-50 to-white p-3 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 dark:from-slate-900/80 dark:to-slate-950 dark:border-slate-800/80 dark:bg-slate-950/60">
                                <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                                <p class="mt-1.5 text-base font-semibold text-slate-950 dark:text-white">{{ $idMoney($item['value']) }}</p>
                                <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">{{ $item['caption'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                @if (($dashboardOverview['kpis'] ?? []) !== [])
                    <section class="p-2">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Master Toko</h3>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Data</span>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ($dashboardOverview['kpis'] as $item)
                                <div class="rounded-xl border border-slate-100/80 bg-gradient-to-br from-slate-50 to-white p-3 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 dark:from-slate-900/80 dark:to-slate-950 dark:border-slate-800/80 dark:bg-slate-950/60">
                                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                                    <p class="mt-1.5 text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($item['value']) }}</p>
                                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ $item['caption'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            @if (($dashboardOverview['stock_kpis'] ?? []) !== [] || ($dashboardOverview['profit_kpis'] ?? []) !== [])
                <div class="divide-y divide-slate-200 border-t border-slate-100/80 dark:divide-slate-700 dark:border-slate-800/80">
                    @if (($dashboardOverview['stock_kpis'] ?? []) !== [])
                        <section class="p-2">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Stok & Valuasi</h3>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200">Inventory</span>
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                                @foreach ($dashboardOverview['stock_kpis'] as $item)
                                    <div class="rounded-xl border border-slate-100/80 bg-gradient-to-br from-slate-50 to-white p-3 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 dark:from-slate-900/80 dark:to-slate-950 dark:border-slate-800/80 dark:bg-slate-950/60">
                                        <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                                        <p class="mt-1.5 text-base font-semibold text-slate-950 dark:text-white">
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
                                <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Laba/Rugi</h3>
                                <span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-950/50 dark:text-sky-200">Insight</span>
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                                @foreach ($dashboardOverview['profit_kpis'] as $item)
                                    <div class="rounded-xl border border-slate-100/80 bg-gradient-to-br from-slate-50 to-white p-3 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 dark:from-slate-900/80 dark:to-slate-950 dark:border-slate-800/80 dark:bg-slate-950/60">
                                        <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                                        <p class="mt-1.5 text-base font-semibold text-slate-950 dark:text-white">
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
                <x-admin.panel>
                    <div class="border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                        <h2 class="text-sm font-semibold text-slate-950 dark:text-white">5 Barang dengan Stok paling banyak</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-1.5 text-left">#</th>
                                    <th class="px-3 py-1.5 text-left">Barang</th>
                                    <th class="px-3 py-1.5 text-right">Stok</th>
                                    <th class="px-3 py-1.5 text-right">Persentase</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($dashboardOverview['top_stock'] as $index => $row)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $index + 1 }}</td>
                                        <td class="px-3 py-1.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['name'] }}</td>
                                        <td class="px-3 py-1.5 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($row['balance'], 3) }}</td>
                                        <td class="px-3 py-1.5 text-right">
                                            <span class="rounded-xl bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200">{{ $idPercent($row['percent']) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200"><td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No stock data yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.panel>

                <x-admin.panel>
                    <div class="border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                        <h2 class="text-sm font-semibold text-slate-950 dark:text-white">5 Barang Keluar Terbanyak</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-1.5 text-left">#</th>
                                    <th class="px-3 py-1.5 text-left">Barang</th>
                                    <th class="px-3 py-1.5 text-right">Terjual</th>
                                    <th class="px-3 py-1.5 text-right">Persentase</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($dashboardOverview['top_outgoing'] as $index => $row)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $index + 1 }}</td>
                                        <td class="px-3 py-1.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['name'] }}</td>
                                        <td class="px-3 py-1.5 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($row['quantity'], 3) }}</td>
                                        <td class="px-3 py-1.5 text-right">
                                            <span class="rounded-xl bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-950/50 dark:text-sky-200">{{ $idPercent($row['percent']) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200"><td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No product sales yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.panel>
            </div>

            <div class="mt-3 grid gap-2 xl:grid-cols-2">
                <x-admin.panel>
                    <div class="border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                        <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Hutang dan Piutang (Rp)</h2>
                    </div>
                    <div class="grid gap-2 p-3 md:grid-cols-2">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Hutang</h3>
                            <div class="mt-3 space-y-3">
                                @foreach ($dashboardOverview['aging'] as $row)
                                    <div class="flex items-center justify-between gap-2 text-sm">
                                        <span class="text-slate-600 dark:text-slate-300">{{ $row['label'] }}</span>
                                        <span class="font-semibold text-slate-950 dark:text-white">{{ $idMoney($row['ap']) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Piutang</h3>
                            <div class="mt-3 space-y-3">
                                @foreach ($dashboardOverview['aging'] as $row)
                                    <div class="flex items-center justify-between gap-2 text-sm">
                                        <span class="text-slate-600 dark:text-slate-300">{{ $row['label'] }}</span>
                                        <span class="font-semibold text-slate-950 dark:text-white">{{ $idMoney($row['ar']) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </x-admin.panel>

                <x-admin.panel>
                    <div class="border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                        <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Ringkasan (Rp)</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-1.5 text-left"></th>
                                    <th class="px-3 py-1.5 text-right">Bulan ini</th>
                                    <th class="px-3 py-1.5 text-right">Bulan lalu</th>
                                    <th class="px-3 py-1.5 text-right">Tahun ini</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach ($dashboardOverview['summary'] as $row)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-1.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['label'] }}</td>
                                        <td class="px-3 py-1.5 text-right text-slate-700 dark:text-slate-200">{{ $idMoney($row['current_month']) }}</td>
                                        <td class="px-3 py-1.5 text-right text-slate-700 dark:text-slate-200">{{ $idMoney($row['last_month']) }}</td>
                                        <td class="px-3 py-1.5 text-right text-slate-700 dark:text-slate-200">{{ $idMoney($row['current_year']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-admin.panel>
            </div>

            <div class="mt-3">
                <x-admin.panel>
                    <div class="flex flex-col gap-1 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Monthly Net Trend</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Income, Cost, and Net movement for the last six months.</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-1.5 text-left">Month</th>
                                    <th class="px-3 py-1.5 text-right">Income</th>
                                    <th class="px-3 py-1.5 text-right">Cost</th>
                                    <th class="px-3 py-1.5 text-right">Net</th>
                                    <th class="px-3 py-1.5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse (($dashboardOverview['monthly_net_trend'] ?? []) as $row)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-1.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['month'] }}</td>
                                        <td class="px-3 py-1.5 text-right text-slate-700 dark:text-slate-200">{{ $idMoney($row['income']) }}</td>
                                        <td class="px-3 py-1.5 text-right text-slate-700 dark:text-slate-200">{{ $idMoney($row['cost']) }}</td>
                                        <td class="px-3 py-1.5 text-right font-semibold text-slate-950 dark:text-white">{{ $idMoney($row['net']) }}</td>
                                        <td class="px-3 py-1.5 text-right">
                                            <x-actions.icon-button href="{{ $row['report_url'] }}" label="{{ __('Open report') }}">
                                                <x-heroicon-o-chart-bar-square class="h-5 w-5" />
                                            </x-actions.icon-button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200"><td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No monthly trend yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.panel>
            </div>
    @endif

    @if ($activePage === 'products')
        <x-admin.panel>
            <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Data Barang</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Catalog, SKU, barcode, brand, category, price, reorder point, and legacy product attributes.') }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-actions.icon-button wire:click="setProductWorkspace('create')" variant="success" label="Tambah Barang">
                        <x-heroicon-m-plus class="h-5 w-5" />
                    </x-actions.icon-button>
                    @if (collect($tokoNavigation)->firstWhere('key', 'migration'))
                        <x-actions.icon-button href="{{ route('admin.toko.migration') }}" variant="primary" label="Import Data">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                    @else
                        <span aria-label="Import Data" title="Import Data" class="wcag-touch-target inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-700 opacity-60 dark:bg-primary-950/30 dark:text-primary-200">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </span>
                    @endif
                    <x-actions.icon-button wire:click="$refresh" label="Refresh">
                        <x-heroicon-m-arrow-path class="h-5 w-5" />
                    </x-actions.icon-button>
                    <x-actions.icon-button wire:click="setProductCatalogFilter('low_stock')" variant="warning" label="Stok Limit">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
                    </x-actions.icon-button>
                    <x-actions.icon-button wire:click="setProductCatalogFilter('expired')" variant="warning" label="Expired">
                        <x-heroicon-o-clock class="h-5 w-5" />
                    </x-actions.icon-button>
                    <x-actions.icon-button href="{{ route('admin.toko.products.barcodes', ['products' => collect($productRows)->pluck('id')->take(24)->all()]) }}" target="_blank" label="Barcode">
                        <x-heroicon-o-qr-code class="h-5 w-5" />
                    </x-actions.icon-button>
                </div>
            </div>

            <div class="grid gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($productWorkspaceTabs as $tab)
                    <button
                        type="button"
                        wire:click="setProductWorkspace('{{ $tab['key'] }}')"
                        aria-label="{{ $tab['label'] }}"
                        aria-pressed="{{ $productWorkspace === $tab['key'] ? 'true' : 'false' }}"
                        class="min-h-12 rounded-xl border px-3 py-2 text-left transition {{ $productWorkspace === $tab['key'] ? 'border-primary-500 bg-primary-50 text-primary-800 dark:border-primary-500 dark:bg-primary-950/30 dark:text-primary-100' : 'border-slate-100/80 bg-white text-slate-700 hover:border-primary-300 dark:border-slate-800/80 dark:bg-slate-950 dark:text-slate-200 dark:hover:border-primary-600' }}"
                    >
                        <span class="block text-sm font-semibold">{{ $tab['label'] }}</span>
                        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $tab['caption'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 sm:grid-cols-2 xl:grid-cols-6">
                @foreach ([
                    ['label' => 'Total Barang', 'value' => $productCatalogSummary['total']],
                    ['label' => 'Aktif', 'value' => $productCatalogSummary['active']],
                    ['label' => 'Stok Limit', 'value' => $productCatalogSummary['low_stock'], 'suffix' => 'stok limit'],
                    ['label' => 'Expired', 'value' => $productCatalogSummary['expired'], 'suffix' => 'expired'],
                    ['label' => 'Brand', 'value' => $productCatalogSummary['brands']],
                    ['label' => 'Kategori', 'value' => $productCatalogSummary['categories']],
                ] as $metric)
                    <div class="rounded-xl border border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                        <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($metric['value']) }}@if (isset($metric['suffix'])) <span class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $metric['suffix'] }}</span>@endif</p>
                        @if (isset($metric['suffix']))
                            <p class="sr-only">{{ $idNumber($metric['value']) }} {{ $metric['suffix'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($productWorkspace === 'create')
                <div class="border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                    <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Form Barang</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Standard fields stay fast for cashier backoffice; advanced fields keep legacy detail complete.') }}</p>
                        </div>
                        <button type="button" wire:click="resetCatalogProductForm" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl border border-slate-100/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">
                            <x-heroicon-o-arrow-path class="h-5 w-5" />
                            <span>{{ __('Reset') }}</span>
                        </button>
                    </div>

                    <div class="border-b border-slate-100/80 dark:border-slate-800/80">
                        <div class="inline-flex min-h-9 items-center border-b-2 border-primary-500 px-3 text-sm font-semibold text-primary-700 dark:text-primary-200">Standard</div>
                        <div class="inline-flex min-h-9 items-center px-3 text-sm font-semibold text-slate-500 dark:text-slate-400">Advanced</div>
                    </div>

                    <div class="mt-3 grid gap-2 lg:grid-cols-4">
                        <input type="text" wire:model="productName" placeholder="{{ __('Product name') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <input type="text" wire:model="productSku" placeholder="{{ __('SKU') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <input type="text" wire:model="productBarcode" placeholder="{{ __('Barcode') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <x-forms.tom-select id="toko-product-status" wire:model="productStatus" placeholder="{{ __('Status') }}" dropdown-direction="down">
                            <option value="active">{{ __('Active') }}</option>
                            <option value="inactive">{{ __('Inactive') }}</option>
                        </x-forms.tom-select>

                        <input list="toko-brand-options" type="text" wire:model="productBrand" placeholder="{{ __('Brand') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <datalist id="toko-brand-options">
                            @foreach ($productBrandRows as $brand)
                                <option value="{{ $brand['name'] }}"></option>
                            @endforeach
                        </datalist>
                        <input list="toko-category-options" type="text" wire:model="productCategory" placeholder="{{ __('Category') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <datalist id="toko-category-options">
                            @foreach ($productCategoryRows as $category)
                                <option value="{{ $category['name'] }}"></option>
                            @endforeach
                        </datalist>
                        <input type="text" wire:model="productUnit" placeholder="{{ __('Unit') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <input type="text" wire:model="productLocation" placeholder="{{ __('Location') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">

                        <input type="text" wire:model="productColor" placeholder="{{ __('Color') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <input type="text" wire:model="productSize" placeholder="{{ __('Size') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <input type="date" wire:model="productExpiredAt" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <input type="number" min="0" step="0.001" wire:model="productReorderPoint" placeholder="{{ __('Reorder point') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">

                        <input type="number" min="0" step="0.01" wire:model="productCostPrice" placeholder="{{ __('Cost price') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <input type="number" min="0" step="0.01" wire:model="productSellingPrice" placeholder="{{ __('Selling price') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <button
                            type="button"
                            wire:click="saveCatalogProduct"
                            data-form-action="catalog-product"
                            class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 lg:col-span-2"
                        >
                            <x-heroicon-m-check class="h-5 w-5" />
                            <span>{{ $editingProductId ? __('Update Product') : 'Tambah Barang' }}</span>
                        </button>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="border-b border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">
                    {{ $errors->first() }}
                </div>
            @endif

            @if ($productWorkspace === 'catalog')
            <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2 text-sm">
	                    <span class="text-slate-600 dark:text-slate-300">Show</span>
	                    <span class="rounded-xl border border-slate-100/80 px-3 py-1.5 text-slate-700 dark:border-slate-800/80 dark:text-slate-200">10</span>
	                    <span class="text-slate-600 dark:text-slate-300">entries</span>
                    <x-actions.icon-button wire:click="setProductCatalogFilter('all')" variant="{{ $productCatalogFilter === 'all' ? 'primary' : 'neutral' }}" label="Semua">
                        <x-heroicon-o-table-cells class="h-5 w-5" />
                    </x-actions.icon-button>
                    <x-actions.icon-button wire:click="setProductCatalogFilter('low_stock')" variant="{{ $productCatalogFilter === 'low_stock' ? 'warning' : 'neutral' }}" label="Stok Limit">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
                    </x-actions.icon-button>
                    <x-actions.icon-button wire:click="setProductCatalogFilter('expired')" variant="{{ $productCatalogFilter === 'expired' ? 'warning' : 'neutral' }}" label="Expired">
                        <x-heroicon-o-clock class="h-5 w-5" />
                    </x-actions.icon-button>
                </div>
                <div class="flex items-center gap-2">
                    <label for="toko-product-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                    <input id="toko-product-search" type="search" wire:model.live.debounce.250ms="productSearch" class="min-h-9 w-64 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                </div>
            </div>

            @if ($productStockCardDetail)
                <div class="border-b border-slate-100/80 bg-slate-50 px-4 py-4 dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Product Stock Card') }}</p>
                            <h3 class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $productStockCardDetail['name'] }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $productStockCardDetail['sku'] ?? '-' }} · {{ $productStockCardDetail['brand'] ?: '-' }} · {{ $productStockCardDetail['category'] ?: '-' }} · {{ $productStockCardDetail['location'] ?: '-' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-actions.icon-button href="{{ route('admin.toko.products.barcodes', ['products' => [$productStockCardDetail['id']]]) }}" target="_blank" label="{{ __('Barcode') }}">
                                <x-heroicon-o-qr-code class="h-5 w-5" />
                            </x-actions.icon-button>
                            <x-actions.icon-button wire:click="clearProductStockCard" label="{{ __('Close') }}">
                                <x-heroicon-m-x-mark class="h-5 w-5" />
                            </x-actions.icon-button>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-2 md:grid-cols-4">
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Current Stock') }}</p>
                            <p class="mt-1 font-semibold text-slate-950 dark:text-white">{{ $idNumber($productStockCardDetail['stock_balance'], 3) }} {{ $productStockCardDetail['unit'] }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Cost') }}</p>
                            <p class="mt-1 font-semibold text-slate-950 dark:text-white">{{ $idNumber($productStockCardDetail['cost_price']) }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Sale Price') }}</p>
                            <p class="mt-1 font-semibold text-slate-950 dark:text-white">{{ $idNumber($productStockCardDetail['selling_price']) }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Margin') }}</p>
                            <p class="mt-1 font-semibold text-slate-950 dark:text-white">{{ $idNumber($productStockCardDetail['margin']) }}</p>
                        </div>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-white text-xs font-semibold uppercase text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('Type') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('Reference') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Qty') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Balance') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Unit Cost') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('Source') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($productStockCardDetail['movements'] as $movement)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $movement['date'] }}</td>
                                        <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $movement['type'] }}</td>
                                        <td class="px-3 py-2 font-mono text-xs text-slate-700 dark:text-slate-200">{{ $movement['reference'] }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($movement['quantity'], 3) }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($movement['balance'], 3) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($movement['unit_cost']) }}</td>
                                        <td class="px-3 py-2 text-xs text-slate-600 dark:text-slate-300">{{ $movement['source'] }}</td>
                                        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $movement['notes'] }}</td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td colspan="8" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No stock movements yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                            <th scope="col" class="px-3 py-1.5 text-left">Action</th>
                            <th scope="col" class="px-3 py-1.5 text-left">Nama Barang</th>
                            <th scope="col" class="px-3 py-1.5 text-right">Harga Beli</th>
                            <th scope="col" class="px-3 py-1.5 text-right">Harga Jual</th>
                            <th scope="col" class="px-3 py-1.5 text-right">Stok</th>
                            <th scope="col" class="px-3 py-1.5 text-left">satuan</th>
                            <th scope="col" class="px-3 py-1.5 text-left">{{ __('Brand') }}</th>
                            <th scope="col" class="px-3 py-1.5 text-left">Kategori</th>
                            <th scope="col" class="px-3 py-1.5 text-left">{{ __('Barcode') }}</th>
                            <th scope="col" class="px-3 py-1.5 text-left">{{ __('Location') }}</th>
                            <th scope="col" class="px-3 py-1.5 text-left">{{ __('Workflow') }}</th>
                            <th scope="col" class="px-3 py-1.5 text-right">Margin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($productRows as $product)
                            <tr wire:key="toko-product-row-{{ $product['id'] }}">
                                <td class="px-3 py-1.5">
                                    <div class="flex gap-1">
                                        <x-actions.icon-button wire:click="editCatalogProduct({{ $product['id'] }})" variant="success" label="{{ __('Edit') }}">
                                            <x-heroicon-m-pencil-square class="h-5 w-5" />
                                        </x-actions.icon-button>
                                        <x-actions.icon-button wire:click="deactivateCatalogProduct({{ $product['id'] }})" variant="danger" label="Nonaktif">
                                            <x-heroicon-m-trash class="h-5 w-5" />
                                        </x-actions.icon-button>
                                        <x-actions.icon-button href="{{ $product['print_url'] }}" target="_blank" variant="primary" label="Barcode">
                                            <x-heroicon-o-qr-code class="h-5 w-5" />
                                        </x-actions.icon-button>
                                        <x-actions.icon-button wire:click="viewProductStockCard({{ $product['id'] }})" label="{{ __('Detail') }}">
                                            <x-heroicon-o-eye class="h-5 w-5" />
                                        </x-actions.icon-button>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $product['name'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $product['sku'] ?? '-' }} · {{ $product['status'] }}@if ($product['is_low_stock']) · Stok Limit @endif @if ($product['is_expired']) · Expired @endif</p>
                                </td>
                                <td class="px-3 py-1.5 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($product['cost_price']) }}</td>
                                <td class="px-3 py-1.5 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($product['selling_price']) }}</td>
                                <td class="px-3 py-1.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($product['stock_balance'], 3) }}</td>
                                <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $product['unit'] }}</td>
                                <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $product['brand'] ?: '-' }}</td>
                                <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $product['category'] ?: '-' }}</td>
                                <td class="px-3 py-1.5 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $product['barcode'] ?: '-' }}</td>
                                <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $product['location'] ?: '-' }}</td>
                                <td class="px-3 py-1.5 text-xs text-slate-600 dark:text-slate-300">
                                    @if ($product['is_low_stock'])
                                        <p class="font-semibold text-amber-700 dark:text-amber-200">Restock Plan</p>
                                        <p>{{ __('Restock') }} {{ $idNumber($product['suggested_restock_quantity'], 3) }} {{ $product['unit'] }}</p>
                                    @endif
                                    @if ($product['is_expired'])
                                        <p class="font-semibold text-rose-700 dark:text-rose-200">Expired Action</p>
                                        <p>{{ $product['expired_action'] }}</p>
                                    @endif
                                    @if (! $product['is_low_stock'] && ! $product['is_expired'])
                                        <span>-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-1.5 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($product['margin']) }}</td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td colspan="12" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">{{ __('No products yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-2 border-t border-slate-100/80 px-3 py-2 dark:border-slate-800/80 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($productTableMeta['start']) }} to {{ $idNumber($productTableMeta['end']) }} of {{ $idNumber($productTableMeta['total']) }} entries</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="button" wire:click="previousProductPage" @disabled($productTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                    @php
                        $productPageStart = max(1, $productTableMeta['page'] - 2);
                        $productPageEnd = min($productTableMeta['pages'], $productPageStart + 4);
                        $productPageStart = max(1, $productPageEnd - 4);
                    @endphp
                    @if ($productPageStart > 1)
                        <button type="button" wire:click="gotoProductPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                    @endif
                    @for ($pageNumber = $productPageStart; $pageNumber <= $productPageEnd; $pageNumber++)
                        <button
                            type="button"
                            wire:key="toko-product-page-{{ $pageNumber }}"
                            wire:click="gotoProductPage({{ $pageNumber }})"
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $productTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-100/80 text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                        >
                            {{ $idNumber($pageNumber) }}
                        </button>
                    @endfor
                    @if ($productPageEnd < $productTableMeta['pages'])
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                        <button type="button" wire:click="gotoProductPage({{ $productTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($productTableMeta['pages']) }}</button>
                    @endif
                    <button type="button" wire:click="nextProductPage" @disabled($productTableMeta['page'] >= $productTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                    <a href="{{ route('admin.toko.exports.products', $productCatalogFilter === 'all' ? [] : ['filter' => $productCatalogFilter]) }}" aria-label="Excel" title="Excel" class="wcag-touch-target inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">
                        <x-heroicon-o-table-cells class="h-5 w-5" />
                    </a>
                    @if (count($productRows) > 0)
                        <a href="{{ route('admin.toko.products.barcodes', ['products' => collect($productRows)->pluck('id')->take(24)->all()]) }}" target="_blank" aria-label="Print" title="Print" class="wcag-touch-target inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100/80 text-slate-700 dark:border-slate-800/80 dark:text-slate-200">
                            <x-heroicon-o-printer class="h-5 w-5" />
                        </a>
                    @else
                        <span aria-label="Print" title="Print" class="wcag-touch-target inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100/80 text-slate-400 opacity-60 dark:border-slate-800/80 dark:text-slate-500">
                            <x-heroicon-o-printer class="h-5 w-5" />
                        </span>
                    @endif
                </div>
            </div>
            @endif

            @if ($productWorkspace === 'barcode')
                <div class="grid gap-2 px-4 py-4 lg:grid-cols-[minmax(0,1fr)_380px]">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Modul Cetak Barcode</h3>
                        <div class="mt-3 grid gap-2 lg:grid-cols-[minmax(0,1fr)_160px]">
                            <x-forms.tom-select
                                id="toko-barcode-product"
                                wire:model.live="barcodeProductId"
                                placeholder="{{ __('Pilih Barang') }}"
                                :options="$productOptions"
                                dropdown-direction="down"
                            >
                                <option value="">{{ __('Pilih Barang') }}</option>
                                @foreach ($productOptions as $option)
                                    <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </x-forms.tom-select>
                            <input type="number" min="1" max="15" wire:model.live="barcodePrintQuantity" placeholder="{{ __('Jumlah Print') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        </div>
                        @if ($barcodeProductPreview)
                            <div class="mt-3 overflow-hidden rounded-xl border border-slate-100/80 dark:border-slate-800/80">
                                <div class="grid gap-2 p-3 text-sm sm:grid-cols-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Nama Barang</p>
                                        <p class="mt-1 font-semibold text-slate-950 dark:text-white">{{ $barcodeProductPreview['name'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">SKU</p>
                                        <p class="mt-1 font-mono text-slate-700 dark:text-slate-200">{{ $barcodeProductPreview['sku'] ?: '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Barcode</p>
                                        <p class="mt-1 font-mono text-slate-700 dark:text-slate-200">{{ $barcodeProductPreview['barcode'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Jumlah</p>
                                        <p class="mt-1 font-semibold text-slate-950 dark:text-white">{{ $barcodeProductPreview['quantity'] }}</p>
                                    </div>
                                </div>
                                <div class="border-t border-slate-100/80 p-3 dark:border-slate-800/80">
                                    <a href="{{ $barcodeProductPreview['print_url'] }}" target="_blank" aria-label="Print" title="Print" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-4 text-sm font-semibold text-white hover:bg-primary-700">
                                        <x-heroicon-o-printer class="h-5 w-5" />
                                        <span>Print</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="rounded-xl border border-dashed border-slate-300 p-3 dark:border-slate-800/80">
                        <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Label Preview') }}</p>
                        <div class="mt-3 rounded-xl bg-white p-3 text-center text-slate-950 shadow-sm dark:bg-slate-950 dark:text-white">
                            <p class="text-xs">{{ $barcodeProductPreview['name'] ?? 'Pilih barang' }}</p>
                            <p class="mt-3 font-mono text-base tracking-widest">{{ $barcodeProductPreview['barcode'] ?? '000000000000' }}</p>
                            <p class="mt-2 text-xs text-slate-500">{{ $barcodeProductPreview['sku'] ?? 'SKU' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (in_array($productWorkspace, ['categories', 'brands'], true))
                @php
                    $taxonomyRows = $productWorkspace === 'brands' ? $productBrandRows : $productCategoryRows;
                    $taxonomyInput = $productWorkspace === 'brands' ? 'productBrandName' : 'productCategoryName';
                    $taxonomyAction = $productWorkspace === 'brands' ? 'saveProductBrand' : 'saveProductCategory';
                    $taxonomyDeleteAction = $productWorkspace === 'brands' ? 'deleteProductBrand' : 'deleteProductCategory';
                    $taxonomyTitle = $productWorkspace === 'brands' ? 'Data Brand' : 'Data Kategori';
                    $taxonomyPlaceholder = $productWorkspace === 'brands' ? 'Nama Brand' : 'Nama Kategori';
                @endphp
                <div class="px-4 py-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ $taxonomyTitle }}</h3>
                        <div class="flex gap-2">
                            <input type="text" wire:model="{{ $taxonomyInput }}" placeholder="{{ $taxonomyPlaceholder }}" class="min-h-9 w-64 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                            <button type="button" wire:click="{{ $taxonomyAction }}" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 text-sm font-semibold text-white hover:bg-primary-700">
                                <x-heroicon-m-plus class="h-5 w-5" />
                                <span>Tambah</span>
                            </button>
                        </div>
                    </div>
                    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-100/80 dark:border-slate-800/80">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-1.5 text-left">Kode</th>
                                    <th class="px-3 py-1.5 text-left">Nama</th>
                                    <th class="px-3 py-1.5 text-right">Barang</th>
                                    <th class="px-3 py-1.5 text-left">Source</th>
                                    <th class="px-3 py-1.5 text-right">Opsi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($taxonomyRows as $row)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-1.5 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $row['code'] }}</td>
                                        <td class="px-3 py-1.5 font-semibold text-slate-950 dark:text-white">{{ $row['name'] }}</td>
                                        <td class="px-3 py-1.5 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($row['products_count']) }}</td>
                                        <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $row['source'] }}</td>
                                        <td class="px-3 py-1.5 text-right">
                                            @if ($row['source'] === 'setting')
                                                <x-actions.icon-button wire:click="{{ $taxonomyDeleteAction }}(@js($row['name']))" wire:confirm="{{ __('Delete this row?') }}" variant="danger" label="{{ __('Delete') }}">
                                                    <x-heroicon-m-trash class="h-5 w-5" />
                                                </x-actions.icon-button>
                                            @else
                                                <span class="text-xs text-slate-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">{{ __('No data yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </x-admin.panel>
    @endif

    @if ($activePage === 'customers')
        <x-admin.panel>
            <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Customers') }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Customer code, contact, address, status, and AR-ready profile data.') }}</p>
                </div>
                @if ($editingCustomerId)
                    <x-actions.icon-button wire:click="resetTokoCustomerForm" label="{{ __('New Customer') }}">
                        <x-heroicon-m-plus class="h-5 w-5" />
                    </x-actions.icon-button>
                @endif
            </div>

            <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 lg:grid-cols-3">
                <input type="text" wire:model="customerCode" placeholder="{{ __('Code') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <input type="text" wire:model="customerName" placeholder="{{ __('Customer name') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <x-forms.tom-select id="toko-customer-status" wire:model="customerStatus" placeholder="{{ __('Status') }}" dropdown-direction="down">
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </x-forms.tom-select>
                <input type="text" wire:model="customerPhone" placeholder="{{ __('Phone') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <input type="email" wire:model="customerEmail" placeholder="{{ __('Email') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <button type="button" wire:click="saveTokoCustomer" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    <x-heroicon-m-check class="h-5 w-5" />
                    <span>{{ $editingCustomerId ? __('Update Customer') : __('Save Customer') }}</span>
                </button>
                <textarea wire:model="customerAddress" placeholder="{{ __('Address') }}" class="min-h-20 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white lg:col-span-3"></textarea>
            </div>

            <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-slate-600 dark:text-slate-300">Show</span>
                    <span class="rounded-xl border border-slate-100/80 px-3 py-1.5 text-slate-700 dark:border-slate-800/80 dark:text-slate-200">10</span>
                    <span class="text-slate-600 dark:text-slate-300">entries</span>
                </div>
                <div class="flex items-center gap-2">
                    <label for="toko-customer-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                    <input id="toko-customer-search" type="search" wire:model.live.debounce.250ms="customerSearch" class="min-h-9 w-64 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                            <th scope="col" class="px-3 py-1.5 text-left">{{ __('Customer') }}</th>
                            <th scope="col" class="px-3 py-1.5 text-left">{{ __('Contact') }}</th>
                            <th scope="col" class="px-3 py-1.5 text-left">{{ __('Address') }}</th>
                            <th scope="col" class="px-3 py-1.5 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($customerRows as $customer)
                            <tr wire:key="toko-customer-row-{{ $customer['id'] }}">
                                <td class="px-3 py-1.5">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $customer['name'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $customer['code'] ?? '-' }} · {{ $customer['status'] }}</p>
                                    <span class="mt-1 inline-flex rounded-xl bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200">{{ $customer['membership_status'] }}</span>
                                </td>
                                <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $customer['phone'] ?: '-' }}<br>{{ $customer['email'] ?: '-' }}</td>
                                <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $customer['address'] ?: '-' }}</td>
                                <td class="px-3 py-1.5 text-right">
                                    <x-actions.icon-button wire:click="editTokoCustomer({{ $customer['id'] }})" label="{{ __('Edit') }}">
                                        <x-heroicon-m-pencil-square class="h-5 w-5" />
                                    </x-actions.icon-button>
                                    <x-actions.icon-button wire:click="convertTokoCustomer({{ $customer['id'] }})" variant="warning" label="{{ __('Convert') }}">
                                        <x-heroicon-o-arrow-path-rounded-square class="h-5 w-5" />
                                    </x-actions.icon-button>
                                    <x-actions.icon-button wire:click="deactivateTokoCustomer({{ $customer['id'] }})" wire:confirm="{{ __('Deactivate this customer?') }}" variant="danger" label="{{ __('Deactivate') }}">
                                        <x-heroicon-m-trash class="h-5 w-5" />
                                    </x-actions.icon-button>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200"><td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">{{ __('No customers yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-2 border-t border-slate-100/80 px-3 py-2 dark:border-slate-800/80 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($customerTableMeta['start']) }} to {{ $idNumber($customerTableMeta['end']) }} of {{ $idNumber($customerTableMeta['total']) }} customer entries</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="button" wire:click="previousCustomerPage" @disabled($customerTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                    @php
                        $customerPageStart = max(1, $customerTableMeta['page'] - 2);
                        $customerPageEnd = min($customerTableMeta['pages'], $customerPageStart + 4);
                        $customerPageStart = max(1, $customerPageEnd - 4);
                    @endphp
                    @if ($customerPageStart > 1)
                        <button type="button" wire:click="gotoCustomerPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                    @endif
                    @for ($pageNumber = $customerPageStart; $pageNumber <= $customerPageEnd; $pageNumber++)
                        <button
                            type="button"
                            wire:key="toko-customer-page-{{ $pageNumber }}"
                            wire:click="gotoCustomerPage({{ $pageNumber }})"
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $customerTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-100/80 text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                        >
                            {{ $idNumber($pageNumber) }}
                        </button>
                    @endfor
                    @if ($customerPageEnd < $customerTableMeta['pages'])
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                        <button type="button" wire:click="gotoCustomerPage({{ $customerTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($customerTableMeta['pages']) }}</button>
                    @endif
                    <button type="button" wire:click="nextCustomerPage" @disabled($customerTableMeta['page'] >= $customerTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                </div>
            </div>

            <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Customer Income') }}</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Sales history and outstanding AR summarized by customer.') }}</p>
                    </div>
                    @if ($canExport)
                        <x-actions.icon-button href="{{ route('admin.toko.exports.customer-income') }}" label="{{ __('Export CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                    @endif
                </div>

                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <th scope="col" class="px-3 py-1.5 text-left">{{ __('Customer') }}</th>
                                <th scope="col" class="px-3 py-1.5 text-right">{{ __('Invoices') }}</th>
                                <th scope="col" class="px-3 py-1.5 text-right">{{ __('Total') }}</th>
                                <th scope="col" class="px-3 py-1.5 text-right">{{ __('AR') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($customerIncomeRows as $row)
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <td class="px-3 py-1.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['customer'] }}</td>
                                    <td class="px-3 py-1.5 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($row['invoice_count']) }}</td>
                                    <td class="px-3 py-1.5 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($row['total']) }}</td>
                                    <td class="px-3 py-1.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($row['ar_total']) }}</td>
                                </tr>
                            @empty
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200"><td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">{{ __('No customer income yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-admin.panel>
    @endif

    @if ($activePage === 'vendors')
        <x-admin.panel>
            <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Vendors') }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Supplier code, contact, address, status, and AP-ready profile data.') }}</p>
                </div>
                @if ($editingVendorId)
                    <x-actions.icon-button wire:click="resetTokoVendorForm" label="{{ __('New Vendor') }}">
                        <x-heroicon-m-plus class="h-5 w-5" />
                    </x-actions.icon-button>
                @endif
            </div>

            <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 lg:grid-cols-3">
                <input type="text" wire:model="vendorCode" placeholder="{{ __('Code') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <input type="text" wire:model="vendorName" placeholder="{{ __('Vendor name') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <x-forms.tom-select
                    id="toko-vendor-status"
                    wire:model="vendorStatus"
                    placeholder="{{ __('Status') }}"
                    dropdown-direction="down"
                >
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </x-forms.tom-select>
                <input type="text" wire:model="vendorPhone" placeholder="{{ __('Phone') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <input type="email" wire:model="vendorEmail" placeholder="{{ __('Email') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <button type="button" wire:click="saveTokoVendor" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    <x-heroicon-m-check class="h-5 w-5" />
                    <span>{{ $editingVendorId ? __('Update Vendor') : __('Save Vendor') }}</span>
                </button>
                <textarea wire:model="vendorAddress" placeholder="{{ __('Address') }}" class="min-h-20 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white lg:col-span-3"></textarea>
            </div>

            <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-slate-600 dark:text-slate-300">Show</span>
                    <span class="rounded-xl border border-slate-100/80 px-3 py-1.5 text-slate-700 dark:border-slate-800/80 dark:text-slate-200">10</span>
                    <span class="text-slate-600 dark:text-slate-300">entries</span>
                </div>
                <div class="flex items-center gap-2">
                    <label for="toko-vendor-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                    <input id="toko-vendor-search" type="search" wire:model.live.debounce.250ms="vendorSearch" class="min-h-9 w-64 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                </div>
            </div>

            @if ($vendorApDetail)
                <div class="border-b border-slate-100/80 bg-slate-50 px-4 py-4 dark:border-slate-800/80 dark:bg-slate-900/50">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Vendor AP Summary') }}</p>
                            <h3 class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $vendorApDetail['name'] }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $vendorApDetail['code'] ?: '-' }} · {{ $idNumber($vendorApDetail['bill_count']) }} {{ __('bills') }}</p>
                        </div>
                        <x-actions.icon-button wire:click="clearTokoVendorDetail" label="{{ __('Close') }}">
                            <x-heroicon-m-x-mark class="h-5 w-5" />
                        </x-actions.icon-button>
                    </div>
                    <div class="mt-3 grid gap-2 md:grid-cols-3">
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Total Purchases') }}</p>
                            <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($vendorApDetail['total_purchases']) }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Open AP') }}</p>
                            <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($vendorApDetail['open_ap']) }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Paid Total') }}</p>
                            <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($vendorApDetail['paid_total']) }}</p>
                        </div>
                    </div>
                    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-100/80 bg-white dark:border-slate-800/80 dark:bg-slate-950">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-2 text-left">{{ __('Recent Purchases') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('Due') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Total') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Paid') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Balance') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($vendorApDetail['rows'] as $bill)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-2">
                                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $bill['number'] }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $bill['issued_at'] ?? '-' }}</p>
                                        </td>
                                        <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $bill['status'] }}</td>
                                        <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $bill['due_at'] ?? '-' }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($bill['total']) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($bill['paid_total']) }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($bill['balance_due']) }}</td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td colspan="6" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No purchases yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                            <th scope="col" class="px-3 py-1.5 text-left">{{ __('Vendor') }}</th>
                            <th scope="col" class="px-3 py-1.5 text-left">{{ __('Contact') }}</th>
                            <th scope="col" class="px-3 py-1.5 text-left">{{ __('Address') }}</th>
                            <th scope="col" class="px-3 py-1.5 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($vendorRows as $vendor)
                            <tr wire:key="toko-vendor-row-{{ $vendor['id'] }}">
                                <td class="px-3 py-1.5">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $vendor['name'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $vendor['code'] ?: '-' }} · {{ $vendor['status'] }}</p>
                                </td>
                                <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $vendor['phone'] ?: '-' }}<br>{{ $vendor['email'] ?: '-' }}</td>
                                <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $vendor['address'] ?: '-' }}</td>
                                <td class="px-3 py-1.5 text-right">
                                    <x-actions.icon-button wire:click="viewTokoVendorDetail({{ $vendor['id'] }})" label="{{ __('Detail') }}">
                                        <x-heroicon-o-eye class="h-5 w-5" />
                                    </x-actions.icon-button>
                                    <x-actions.icon-button wire:click="editTokoVendor({{ $vendor['id'] }})" label="{{ __('Edit') }}">
                                        <x-heroicon-m-pencil-square class="h-5 w-5" />
                                    </x-actions.icon-button>
                                    <x-actions.icon-button wire:click="deactivateTokoVendor({{ $vendor['id'] }})" wire:confirm="{{ __('Deactivate this vendor?') }}" variant="danger" label="{{ __('Deactivate') }}">
                                        <x-heroicon-m-trash class="h-5 w-5" />
                                    </x-actions.icon-button>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200"><td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">{{ __('No vendors yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-2 border-t border-slate-100/80 px-3 py-2 dark:border-slate-800/80 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($vendorTableMeta['start']) }} to {{ $idNumber($vendorTableMeta['end']) }} of {{ $idNumber($vendorTableMeta['total']) }} vendor entries</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="button" wire:click="previousVendorPage" @disabled($vendorTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                    @php
                        $vendorPageStart = max(1, $vendorTableMeta['page'] - 2);
                        $vendorPageEnd = min($vendorTableMeta['pages'], $vendorPageStart + 4);
                        $vendorPageStart = max(1, $vendorPageEnd - 4);
                    @endphp
                    @if ($vendorPageStart > 1)
                        <button type="button" wire:click="gotoVendorPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                    @endif
                    @for ($pageNumber = $vendorPageStart; $pageNumber <= $vendorPageEnd; $pageNumber++)
                        <button
                            type="button"
                            wire:key="toko-vendor-page-{{ $pageNumber }}"
                            wire:click="gotoVendorPage({{ $pageNumber }})"
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $vendorTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-100/80 text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                        >
                            {{ $idNumber($pageNumber) }}
                        </button>
                    @endfor
                    @if ($vendorPageEnd < $vendorTableMeta['pages'])
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                        <button type="button" wire:click="gotoVendorPage({{ $vendorTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($vendorTableMeta['pages']) }}</button>
                    @endif
                    <button type="button" wire:click="nextVendorPage" @disabled($vendorTableMeta['page'] >= $vendorTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                </div>
            </div>
        </x-admin.panel>
    @endif

    @if ($activePage === 'cash')
        <x-admin.panel>
            <div class="border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Cash') }}</h2>
            </div>

            <div class="grid gap-2 px-4 py-4 md:grid-cols-3">
                <div class="rounded-xl border border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Paid Sales') }}</p>
                    <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idMoney((float) ($tokoReport['sales']['total'] ?? 0)) }}</p>
                </div>
                <div class="rounded-xl border border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Purchases') }}</p>
                    <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idMoney((float) ($tokoReport['purchases']['total'] ?? 0)) }}</p>
                </div>
                <div class="rounded-xl border border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Net') }}</p>
                    <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idMoney((float) (($tokoReport['sales']['total'] ?? 0) - ($tokoReport['purchases']['total'] ?? 0))) }}</p>
                </div>
            </div>

            <div class="grid gap-2 border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80 lg:grid-cols-2">
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Payment Methods') }}</h3>
                    <div class="flex gap-2">
                        <input type="text" wire:model="paymentMethodName" placeholder="{{ __('Method name') }}" class="min-h-9 flex-1 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <button type="button" wire:click="savePaymentMethod" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                            <x-heroicon-m-check class="h-5 w-5" />
                            <span>{{ __('Save') }}</span>
                        </button>
                    </div>
                    <div class="space-y-2">
                        @forelse ($paymentMethods as $method)
                            <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm font-semibold text-slate-900 dark:border-slate-800/80 dark:text-slate-100">{{ $method['name'] }}</div>
                        @empty
                            <p class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm text-slate-500 dark:border-slate-800/80 dark:text-slate-400">{{ __('No payment methods yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Bank Accounts') }}</h3>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <input type="text" wire:model="bankCode" placeholder="{{ __('Code') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <input type="text" wire:model="bankName" placeholder="{{ __('Bank') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <input type="text" wire:model="bankAccountNumber" placeholder="{{ __('Account number') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <input type="text" wire:model="bankAccountName" placeholder="{{ __('Account name') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <div class="sm:col-span-2">
                            <button type="button" wire:click="saveBankAccount" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                                <x-heroicon-m-check class="h-5 w-5" />
                                <span>{{ __('Save Bank Account') }}</span>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-2">
                        @forelse ($bankAccounts as $account)
                            <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $account['code'] }} · {{ $account['bank'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $account['number'] }} · {{ $account['name'] }}</p>
                            </div>
                        @empty
                            <p class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm text-slate-500 dark:border-slate-800/80 dark:text-slate-400">{{ __('No bank accounts yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                <div class="mb-3 flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Operational Expenses') }}</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Tambah Trx, Data Operasional, and Tipe Pengeluaran in one clean cash workspace.') }}</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input type="text" wire:model="expenseTypeName" placeholder="{{ __('Tipe Pengeluaran') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <button type="button" wire:click="saveExpenseType" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                            <x-heroicon-m-check class="h-5 w-5" />
                            <span>{{ __('Save Type') }}</span>
                        </button>
                    </div>
                </div>

                <div class="grid gap-2 lg:grid-cols-[minmax(0,1fr)_10rem_minmax(0,12rem)_minmax(0,10rem)]">
                    @if ($expenseTypes !== [])
                        <x-forms.tom-select
                            id="toko-operational-expense-type"
                            wire:model="operationalExpenseType"
                            placeholder="{{ __('Expense type') }}"
                            dropdown-direction="down"
                        >
                            <option value="">{{ __('Expense type') }}</option>
                            @foreach ($expenseTypes as $type)
                                <option value="{{ $type['name'] }}">{{ $type['name'] }}</option>
                            @endforeach
                        </x-forms.tom-select>
                    @else
                        <input type="text" wire:model="operationalExpenseType" placeholder="{{ __('Expense type') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    @endif
                    <input type="number" min="0.01" step="0.01" wire:model="operationalExpenseAmount" placeholder="{{ __('Amount') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    <input type="text" wire:model="operationalExpensePaymentMethod" placeholder="{{ __('Payment method') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    <input type="text" wire:model="operationalExpenseBankCode" placeholder="{{ __('Bank code') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    <textarea wire:model="operationalExpenseDescription" placeholder="{{ __('Description') }}" class="min-h-20 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white lg:col-span-3"></textarea>
                    <button type="button" wire:click="recordOperationalExpense" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                        <x-heroicon-m-check class="h-5 w-5" />
                        <span>{{ $editingOperationalExpenseId ? __('Update Expense') : __('Record Expense') }}</span>
                    </button>
                </div>

                @if ($expenseTypes !== [])
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($expenseTypes as $type)
                            <span class="rounded-xl border border-slate-100/80 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:border-slate-800/80 dark:text-slate-200">{{ $type['name'] }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="mt-3 flex flex-col gap-2 border-t border-slate-100/80 pt-3 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="text-slate-600 dark:text-slate-300">Show</span>
                        <span class="rounded-xl border border-slate-100/80 px-3 py-1.5 text-slate-700 dark:border-slate-800/80 dark:text-slate-200">10</span>
                        <span class="text-slate-600 dark:text-slate-300">entries</span>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <input type="date" wire:model.live="operationalExpenseFromDate" aria-label="{{ __('Operational expense from date') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <input type="date" wire:model.live="operationalExpenseToDate" aria-label="{{ __('Operational expense to date') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <label for="toko-operational-expense-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                        <input id="toko-operational-expense-search" type="search" wire:model.live.debounce.250ms="operationalExpenseSearch" class="min-h-9 w-64 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        @if ($canExport)
                            <x-actions.icon-button href="{{ route('admin.toko.exports.report-operational-expenses', $operationalExpenseExportQuery) }}" label="{{ __('Export CSV') }}">
                                <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                            </x-actions.icon-button>
                        @endif
                    </div>
                </div>

                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <th scope="col" class="px-3 py-1.5 text-left">{{ __('Code') }}</th>
                                <th scope="col" class="px-3 py-1.5 text-left">{{ __('Type') }}</th>
                                <th scope="col" class="px-3 py-1.5 text-left">{{ __('Description') }}</th>
                                <th scope="col" class="px-3 py-1.5 text-left">{{ __('Payment') }}</th>
                                <th scope="col" class="px-3 py-1.5 text-right">{{ __('Amount') }}</th>
                                <th scope="col" class="px-3 py-1.5 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($operationalExpenseRows as $expense)
                                <tr wire:key="toko-operational-expense-row-{{ $expense['id'] }}">
                                    <td class="px-3 py-1.5">
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $expense['reference'] ?: '-' }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $expense['date'] }} · {{ $expense['status'] }}</p>
                                    </td>
                                    <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $expense['type'] }}</td>
                                    <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $expense['description'] }}</td>
                                    <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">{{ $expense['payment_method'] ?: '-' }} · {{ $expense['bank_code'] ?: '-' }}</td>
                                    <td class="px-3 py-1.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($expense['amount']) }}</td>
                                    <td class="px-3 py-1.5 text-right">
                                        <x-actions.icon-button wire:click="editOperationalExpense({{ $expense['id'] }})" label="{{ __('Edit') }}">
                                            <x-heroicon-m-pencil-square class="h-5 w-5" />
                                        </x-actions.icon-button>
                                        <x-actions.icon-button wire:click="voidOperationalExpense({{ $expense['id'] }})" wire:confirm="{{ __('Void this operational expense?') }}" variant="danger" label="{{ __('Void') }}">
                                            <x-heroicon-o-no-symbol class="h-5 w-5" />
                                        </x-actions.icon-button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200"><td colspan="6" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">{{ __('No operational expenses yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 flex flex-col gap-2 border-t border-slate-100/80 pt-3 dark:border-slate-800/80 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($operationalExpenseTableMeta['start']) }} to {{ $idNumber($operationalExpenseTableMeta['end']) }} of {{ $idNumber($operationalExpenseTableMeta['total']) }} operational expense entries</p>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="button" wire:click="previousOperationalExpensePage" @disabled($operationalExpenseTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                        @php
                            $operationalExpensePageStart = max(1, $operationalExpenseTableMeta['page'] - 2);
                            $operationalExpensePageEnd = min($operationalExpenseTableMeta['pages'], $operationalExpensePageStart + 4);
                            $operationalExpensePageStart = max(1, $operationalExpensePageEnd - 4);
                        @endphp
                        @if ($operationalExpensePageStart > 1)
                            <button type="button" wire:click="gotoOperationalExpensePage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                            <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                        @endif
                        @for ($pageNumber = $operationalExpensePageStart; $pageNumber <= $operationalExpensePageEnd; $pageNumber++)
                            <button
                                type="button"
                                wire:key="toko-operational-expense-page-{{ $pageNumber }}"
                                wire:click="gotoOperationalExpensePage({{ $pageNumber }})"
                                class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $operationalExpenseTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-100/80 text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                            >
                                {{ $idNumber($pageNumber) }}
                            </button>
                        @endfor
                        @if ($operationalExpensePageEnd < $operationalExpenseTableMeta['pages'])
                            <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                            <button type="button" wire:click="gotoOperationalExpensePage({{ $operationalExpenseTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($operationalExpenseTableMeta['pages']) }}</button>
                        @endif
                        <button type="button" wire:click="nextOperationalExpensePage" @disabled($operationalExpenseTableMeta['page'] >= $operationalExpenseTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Payment History') }}</h3>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <label for="toko-payment-history-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                        <input id="toko-payment-history-search" type="search" wire:model.live.debounce.250ms="paymentHistorySearch" class="min-h-9 w-64 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        @if ($canExport)
                            <x-actions.icon-button href="{{ route('admin.toko.exports.payments') }}" label="{{ __('Export CSV') }}">
                                <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                            </x-actions.icon-button>
                        @endif
                    </div>
                </div>
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900">
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Invoice') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Method') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Reference') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($paymentHistoryRows as $payment)
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <td class="px-3 py-2 text-slate-900 dark:text-slate-100">{{ $payment['invoice_number'] }}</td>
                                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $payment['method'] ?: '-' }} · {{ $payment['bank_code'] ?: '-' }}</td>
                                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $payment['reference'] ?: '-' }}</td>
                                    <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($payment['amount']) }}</td>
                                </tr>
                            @empty
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <td colspan="4" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No invoice payments yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 flex flex-col gap-2 border-t border-slate-100/80 pt-3 dark:border-slate-800/80 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($paymentHistoryTableMeta['start']) }} to {{ $idNumber($paymentHistoryTableMeta['end']) }} of {{ $idNumber($paymentHistoryTableMeta['total']) }} payment entries</p>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="button" wire:click="previousPaymentHistoryPage" @disabled($paymentHistoryTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                        @php
                            $paymentHistoryPageStart = max(1, $paymentHistoryTableMeta['page'] - 2);
                            $paymentHistoryPageEnd = min($paymentHistoryTableMeta['pages'], $paymentHistoryPageStart + 4);
                            $paymentHistoryPageStart = max(1, $paymentHistoryPageEnd - 4);
                        @endphp
                        @if ($paymentHistoryPageStart > 1)
                            <button type="button" wire:click="gotoPaymentHistoryPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                            <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                        @endif
                        @for ($pageNumber = $paymentHistoryPageStart; $pageNumber <= $paymentHistoryPageEnd; $pageNumber++)
                            <button
                                type="button"
                                wire:key="toko-payment-history-page-{{ $pageNumber }}"
                                wire:click="gotoPaymentHistoryPage({{ $pageNumber }})"
                                class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $paymentHistoryTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-100/80 text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                            >
                                {{ $idNumber($pageNumber) }}
                            </button>
                        @endfor
                        @if ($paymentHistoryPageEnd < $paymentHistoryTableMeta['pages'])
                            <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                            <button type="button" wire:click="gotoPaymentHistoryPage({{ $paymentHistoryTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($paymentHistoryTableMeta['pages']) }}</button>
                        @endif
                        <button type="button" wire:click="nextPaymentHistoryPage" @disabled($paymentHistoryTableMeta['page'] >= $paymentHistoryTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                    </div>
                </div>
            </div>
        </x-admin.panel>
    @endif

    @if ($activePage === 'dashboard')
    @if ($tokoReport)
    <x-admin.panel>
        <div class="border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
            <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Insight Charts') }}</h2>
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
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Sales Trend') }}</h3>
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $idMoney($tokoReport['sales']['total']) }}</span>
                </div>
                <div class="mt-3 h-40" wire:ignore>
                    <canvas data-toko-sales-chart role="img" aria-label="{{ __('Toko sales trend chart') }}"></canvas>
                </div>
                @if ($salesTrendPreview === [])
                    <div class="mt-2">
                        <p class="self-center text-sm text-slate-500 dark:text-slate-400">{{ __('No sales trend yet.') }}</p>
                    </div>
                @endif
            </div>

            <div>
                <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Purchase Trend') }}</h3>
                <div class="mt-3 h-40" wire:ignore>
                    <canvas data-toko-purchase-chart role="img" aria-label="{{ __('Toko purchase trend chart') }}"></canvas>
                </div>
                @if ($purchaseTrendPreview === [])
                    <div class="mt-2">
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('No purchase trend yet.') }}</p>
                    </div>
                @endif
            </div>

            <div>
                <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Top Products') }}</h3>
                <div class="mt-3 h-40" wire:ignore>
                    <canvas data-toko-products-chart role="img" aria-label="{{ __('Toko top products chart') }}"></canvas>
                </div>
                @if ($topProductsPreview === [])
                    <div class="mt-2">
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('No product sales yet.') }}</p>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-3">
                <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Risk Watch') }}</h3>
                <div class="mt-3 grid gap-2 sm:grid-cols-3">
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm dark:bg-slate-900">
                        <span class="font-semibold text-slate-600 dark:text-slate-300">{{ __('AR') }}</span>
                        <span class="text-slate-950 dark:text-white">{{ $idMoney($tokoReport['aging']['accounts_receivable']) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm dark:bg-slate-900">
                        <span class="font-semibold text-slate-600 dark:text-slate-300">{{ __('AP') }}</span>
                        <span class="text-slate-950 dark:text-white">{{ $idMoney($tokoReport['aging']['accounts_payable']) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm dark:bg-slate-900">
                        <span class="font-semibold text-slate-600 dark:text-slate-300">{{ __('Low Stock') }}</span>
                        <span class="text-slate-950 dark:text-white">{{ $idNumber(count($tokoReport['low_stock'])) }}</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3 grid gap-2 lg:grid-cols-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Pendapatan Retail Vs Nota</h3>
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
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Pengeluaran</h3>
                    <div class="mt-3 h-72" wire:ignore>
                        <canvas data-toko-expense-chart role="img" aria-label="{{ __('Toko expense chart') }}"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </x-admin.panel>
    @endif

    <div class="grid gap-2 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
        <x-admin.panel>
            <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Transaction Command Center') }}</h2>
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
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Recent POS Invoices') }}</h3>
                        <x-actions.icon-button href="{{ route('admin.toko.pos') }}" label="{{ __('View POS') }}">
                            <x-heroicon-o-eye class="h-5 w-5" />
                        </x-actions.icon-button>
                    </div>
                    <div class="mt-3 space-y-2">
                        @forelse (array_slice($recentPosInvoices, 0, 5) as $invoice)
                            <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $invoice['number'] }}</span>
                                    <span class="text-slate-600 dark:text-slate-300">{{ $idMoney($invoice['total']) }}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $invoice['issued_at'] ?? '-' }} · {{ $invoice['status'] }}</p>
                            </div>
                        @empty
                            <p class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm text-slate-500 dark:border-slate-800/80 dark:text-slate-400">{{ __('No POS invoices yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Recent Purchases') }}</h3>
                        <x-actions.icon-button href="{{ route('admin.toko.purchases') }}" label="{{ __('View Purchases') }}">
                            <x-heroicon-o-eye class="h-5 w-5" />
                        </x-actions.icon-button>
                    </div>
                    <div class="mt-3 space-y-2">
                        @forelse (array_slice($purchaseBillRows, 0, 5) as $bill)
                            <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $bill['number'] }}</span>
                                    <span class="text-slate-600 dark:text-slate-300">{{ $idMoney($bill['total']) }}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $bill['vendor'] }} · {{ $bill['status'] }}</p>
                            </div>
                        @empty
                            <p class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm text-slate-500 dark:border-slate-800/80 dark:text-slate-400">{{ __('No purchases yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-admin.panel>

        <x-admin.panel>
            <div class="border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Quick Actions') }}</h2>
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
                    <a href="{{ $action['href'] }}" class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm hover:bg-slate-50 dark:border-slate-800/80 dark:hover:bg-slate-900">
                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $action['label'] }}</span>
                        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $action['caption'] }}</span>
                    </a>
                @endforeach
            </div>
        </x-admin.panel>
    </div>
    @endif

        @if ($activePage === 'pos')
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start" x-data="{ barcodeFocus: true }" @keydown.window="if($event.key === 'F2') { $refs.barcodeInput.focus(); $event.preventDefault(); }">
        <!-- LEFT COLUMN (Products & Cart) -->
        <div class="flex-1 space-y-4">
            
            <!-- HEADER & PRODUCT SELECTION -->
            <x-admin.panel class="p-4 shadow-sm border-0 ring-1 ring-slate-200/50 dark:ring-slate-800/50">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                                <x-heroicon-s-shopping-bag class="h-5 w-5" />
                            </span>
                            {{ __('Terminal POS') }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('Tekan F2 untuk fokus ke barcode scanner.') }}</p>
                    </div>
                    <div class="flex gap-2">
                        <x-actions.icon-button wire:click="$refresh" label="Refresh" class="bg-white hover:bg-slate-50">
                            <x-heroicon-m-arrow-path class="h-4 w-4" />
                        </x-actions.icon-button>
                        <x-actions.icon-button wire:click="$toggle('showPosBackOffice')" variant="{{ $showPosBackOffice ? 'primary' : 'neutral' }}" label="Tools Admin" class="{{ $showPosBackOffice ? '' : 'bg-white hover:bg-slate-50' }}">
                            <x-heroicon-o-clipboard-document-list class="h-4 w-4" />
                        </x-actions.icon-button>
                    </div>
                </div>

                <div x-data="{
                        open: false,
                        search: @entangle('saleBarcode').live,
                        options: @js($productOptions),
                        highlightedIndex: -1,
                        get filteredOptions() {
                            if (!this.search) return [];
                            const q = this.search.toLowerCase();
                            return this.options.filter(o => o.name.toLowerCase().includes(q)).slice(0, 15);
                        },
                        selectOption(id) {
                            $wire.set('selectedProductId', id);
                            this.search = '';
                            this.open = false;
                            this.highlightedIndex = -1;
                        },
                        onKeyDown(e) {
                            if (!this.open && this.search && this.search.length > 0) {
                                this.open = true;
                            }
                            const opts = this.filteredOptions;
                            if (e.key === 'ArrowDown') {
                                e.preventDefault();
                                if (this.highlightedIndex < opts.length - 1) this.highlightedIndex++;
                                this.scrollToHighlighted();
                            } else if (e.key === 'ArrowUp') {
                                e.preventDefault();
                                if (this.highlightedIndex > 0) this.highlightedIndex--;
                                this.scrollToHighlighted();
                            } else if (e.key === 'Enter') {
                                if (this.open && this.highlightedIndex >= 0 && opts[this.highlightedIndex]) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    this.selectOption(opts[this.highlightedIndex].id);
                                } else {
                                    this.open = false;
                                }
                            } else if (e.key === 'Escape') {
                                this.open = false;
                            }
                        },
                        scrollToHighlighted() {
                            this.$nextTick(() => {
                                const activeEl = this.$refs.dropdown?.querySelector('[data-index=\'' + this.highlightedIndex + '\']');
                                if (activeEl) {
                                    activeEl.scrollIntoView({ block: 'nearest' });
                                }
                            });
                        }
                    }" 
                    class="relative w-full"
                    @click.away="open = false"
                >
                    <div class="flex items-center bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500 transition-all">
                        <div class="pointer-events-none flex items-center pl-4">
                            <x-heroicon-m-magnifying-glass class="h-5 w-5 text-slate-400" x-show="!search" />
                            <x-heroicon-m-qr-code class="h-5 w-5 text-primary-500" x-show="search" x-cloak />
                        </div>
                        <input
                            x-ref="barcodeInput"
                            type="text"
                            wire:model.live="saleBarcode"
                            wire:keydown.enter="addScannedSaleBarcode"
                            @keydown="onKeyDown($event)"
                            @focus="open = true"
                            @input="open = true; highlightedIndex = -1"
                            placeholder="{{ __('Ketik nama produk atau Scan Barcode (F2)...') }}"
                            class="w-full border-0 bg-transparent py-3 pl-3 pr-4 text-sm focus:ring-0 dark:text-white"
                            autofocus
                        >
                        <!-- Clear button -->
                        <button type="button" x-show="search" @click="search = ''; open = false; $refs.barcodeInput.focus()" class="pr-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300" x-cloak>
                            <x-heroicon-m-x-mark class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Dropdown -->
                    <div 
                        x-ref="dropdown"
                        x-show="open && search && search.length > 0" 
                        x-transition.opacity.duration.150ms 
                        style="display: none;" 
                        class="absolute z-50 w-full mt-1.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-xl overflow-hidden"
                    >
                        <div class="max-h-60 overflow-y-auto p-1">
                            <template x-for="(opt, index) in filteredOptions" :key="opt.id">
                                <div 
                                    @click="selectOption(opt.id)" 
                                    @mouseenter="highlightedIndex = index"
                                    :data-index="index"
                                    :class="{'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300': highlightedIndex === index, 'text-slate-700 dark:text-slate-200': highlightedIndex !== index}"
                                    class="px-3 py-2.5 text-sm cursor-pointer rounded-lg transition-colors flex items-center justify-between group"
                                >
                                    <span x-text="opt.name" class="truncate pr-4"></span>
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-primary-500 opacity-0 group-hover:opacity-100" x-show="highlightedIndex === index">{{ __('Pilih') }}</span>
                                </div>
                            </template>
                            <template x-if="filteredOptions.length === 0">
                                <div class="px-4 py-4 text-sm text-slate-500 dark:text-slate-400 text-center flex flex-col items-center justify-center gap-2">
                                    <x-heroicon-o-magnifying-glass class="h-6 w-6 text-slate-300 dark:text-slate-600" />
                                    <span>{{ __('Produk tidak ditemukan, tekan Enter jika ini adalah Barcode.') }}</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                @if($selectedProductId || $saleBarcode)
                <div class="mt-4 rounded-xl border border-primary-200/60 bg-primary-50/50 p-4 dark:border-primary-900/40 dark:bg-primary-900/20 shadow-sm transition-all">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <div class="sm:col-span-5">
                            <label class="block text-xs font-semibold text-primary-700 dark:text-primary-300 mb-1">{{ __('Produk Terpilih') }}</label>
                            <input type="text" readonly value="{{ $saleProductPreview['name'] ?? '' }}" class="w-full rounded-lg border-0 bg-white/80 py-2 text-sm shadow-sm ring-1 ring-inset ring-primary-200/50 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-primary-800/50">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-primary-700 dark:text-primary-300 mb-1">{{ __('Sisa Stok') }}</label>
                            <input type="text" readonly value="{{ isset($saleProductPreview) ? $idNumber($saleProductPreview['stock'], 3) : '' }}" class="w-full rounded-lg border-0 bg-slate-100/80 py-2 text-center text-sm shadow-sm ring-1 ring-inset ring-slate-200/50 dark:bg-slate-800/60 dark:text-slate-300">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-primary-700 dark:text-primary-300 mb-1">{{ __('Qty') }}</label>
                            <input type="number" min="0.001" step="0.001" wire:model.live="saleQuantity" wire:keydown.enter="addToSaleCart" class="w-full rounded-lg border-0 py-2 text-center text-sm shadow-sm ring-1 ring-inset ring-primary-300 focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-white dark:ring-primary-700">
                        </div>
                        <div class="sm:col-span-3">
                            <button type="button" wire:click="addToSaleCart" class="w-full flex items-center justify-center gap-1.5 rounded-lg bg-primary-600 hover:bg-primary-500 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5">
                                <x-heroicon-m-plus class="h-4 w-4" /> Tambah
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </x-admin.panel>

            <!-- CART TABLE -->
            <x-admin.panel class="overflow-hidden border-0 ring-1 ring-slate-200/50 dark:ring-slate-800/50 shadow-sm">
                <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Keranjang Transaksi') }}</h3>
                </div>
                <div class="overflow-x-auto min-h-[300px]">
                    <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50/50 dark:bg-slate-900/50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">No</th>
                                <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Produk') }}</th>
                                <th scope="col" class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Harga') }}</th>
                                <th scope="col" class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Qty') }}</th>
                                <th scope="col" class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Subtotal') }}</th>
                                <th scope="col" class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-slate-500"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-950">
                            @forelse ($saleCart as $index => $item)
                                <tr class="group hover:bg-slate-50/80 dark:hover:bg-slate-900/50 transition-colors">
                                    <td class="px-4 py-3 text-slate-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ $item['name'] }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $item['sku'] ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($item['unit_price']) }}</td>
                                    <td class="px-4 py-3 text-center font-medium text-slate-700 dark:text-slate-200">
                                        <div class="inline-flex items-center justify-center px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-md">
                                            {{ $idNumber($item['quantity'], 3) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-slate-100">{{ $idNumber($item['line_total']) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" wire:click="removeSaleCartItem({{ $index }})" class="text-rose-400 hover:text-rose-600 transition-colors" title="Hapus">
                                            <x-heroicon-m-x-circle class="h-5 w-5 mx-auto" />
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-16 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 mb-3">
                                            <x-heroicon-o-shopping-bag class="h-6 w-6 text-slate-400" />
                                        </div>
                                        <p class="text-sm font-medium text-slate-900 dark:text-slate-200">Keranjang masih kosong</p>
                                        <p class="text-xs text-slate-500 mt-1">Mulai scan barcode atau cari produk.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.panel>
        </div>

        <!-- RIGHT COLUMN (Checkout) -->
        <div class="w-full shrink-0 lg:w-96 space-y-4">
            
            <x-admin.panel class="p-4 border-0 ring-1 ring-slate-200/50 dark:ring-slate-800/50 shadow-sm bg-gradient-to-b from-slate-50/50 to-white dark:from-slate-900/50 dark:to-slate-950">
                <!-- CUSTOMER & INVOICE NO -->
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100 dark:border-slate-800 gap-2">
                    <div class="flex-1">
                        <label class="sr-only" for="toko-pos-client">{{ __('Customer') }}</label>
                        <x-forms.tom-select id="toko-pos-client" wire:model="selectedClientId" placeholder="{{ __('Pelanggan Umum (Walk-in)') }}" :options="$clientOptions" dropdown-direction="down">
                            <option value="">{{ __('Pelanggan Umum (Walk-in)') }}</option>
                            @foreach ($clientOptions as $client)
                                <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
                            @endforeach
                        </x-forms.tom-select>
                    </div>
                    <button type="button" wire:click="$set('showingQuickCustomerModal', true)" class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors tooltip" data-tippy-content="{{ __('Tambah Pelanggan Baru') }}">
                        <x-heroicon-m-user-plus class="h-5 w-5" />
                    </button>
                </div>

                <!-- GRAND TOTAL DISPLAY -->
                <div class="rounded-2xl bg-slate-900 p-5 text-center shadow-inner relative overflow-hidden dark:bg-slate-950">
                    <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-white/5 blur-2xl"></div>
                    <div class="absolute -left-4 -bottom-4 h-16 w-16 rounded-full bg-primary-500/10 blur-2xl"></div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-1 relative z-10">{{ __('Total Tagihan') }}</p>
                    <p class="text-3xl sm:text-4xl font-black text-white relative z-10 tracking-tight">{{ $idMoney($salePayableTotal) }}</p>
                    <p class="text-[10px] text-slate-500 mt-2 relative z-10">{{ $nextSaleDraftNumber }}</p>
                </div>

                <!-- ORDER SUMMARY -->
                <div class="mt-5 space-y-2.5 text-sm px-1">
                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-semibold text-slate-900 dark:text-slate-200">{{ $idNumber($saleCartTotal) }}</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <div>
                            <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Diskon (-)</label>
                            <input type="number" min="0" step="0.01" wire:model.live.debounce.500ms="saleDiscountAmount" class="w-full rounded-lg border-slate-200 py-1.5 text-sm shadow-sm focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Charge (+)</label>
                            <input type="number" min="0" step="0.01" wire:model.live.debounce.500ms="saleAdditionalCharge" class="w-full rounded-lg border-slate-200 py-1.5 text-sm shadow-sm focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900">
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-100 pt-3 mt-3 dark:border-slate-800">
                        <span class="font-semibold text-slate-900 dark:text-white">Jumlah Bayar</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $idNumber($saleTenderTotal) }}</span>
                    </div>
                </div>

                <!-- PAYMENT METHODS -->
                <div class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-800">
                    <p class="mb-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Pilih Pembayaran') }}</p>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" wire:click="setSalePaymentMode('cash')" class="flex flex-col items-center justify-center gap-1 rounded-xl py-2 transition-all {{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('cash') ? 'bg-primary-50 border border-primary-200 text-primary-700 shadow-sm dark:bg-primary-900/30 dark:border-primary-800 dark:text-primary-300' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <x-heroicon-o-banknotes class="h-5 w-5" />
                            <span class="text-[10px] font-bold tracking-wide">Tunai</span>
                        </button>
                        <button type="button" wire:click="setSalePaymentMode('qris')" class="flex flex-col items-center justify-center gap-1 rounded-xl py-2 transition-all {{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('qris') ? 'bg-primary-50 border border-primary-200 text-primary-700 shadow-sm dark:bg-primary-900/30 dark:border-primary-800 dark:text-primary-300' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <x-heroicon-o-qr-code class="h-5 w-5" />
                            <span class="text-[10px] font-bold tracking-wide">QRIS</span>
                        </button>
                        <button type="button" wire:click="setSalePaymentMode('debit')" class="flex flex-col items-center justify-center gap-1 rounded-xl py-2 transition-all {{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('debit') ? 'bg-primary-50 border border-primary-200 text-primary-700 shadow-sm dark:bg-primary-900/30 dark:border-primary-800 dark:text-primary-300' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <x-heroicon-o-credit-card class="h-5 w-5" />
                            <span class="text-[10px] font-bold tracking-wide">Debit</span>
                        </button>
                        <button type="button" wire:click="setSalePaymentMode('transfer')" class="flex flex-col items-center justify-center gap-1 rounded-xl py-2 transition-all {{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('transfer') ? 'bg-primary-50 border border-primary-200 text-primary-700 shadow-sm dark:bg-primary-900/30 dark:border-primary-800 dark:text-primary-300' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <x-heroicon-o-arrows-right-left class="h-5 w-5" />
                            <span class="text-[10px] font-bold tracking-wide">Transfer</span>
                        </button>
                        <button type="button" wire:click="setSalePaymentMode('split')" class="flex flex-col items-center justify-center gap-1 rounded-xl py-2 transition-all {{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('split') ? 'bg-primary-50 border border-primary-200 text-primary-700 shadow-sm dark:bg-primary-900/30 dark:border-primary-800 dark:text-primary-300' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <x-heroicon-o-rectangle-stack class="h-5 w-5" />
                            <span class="text-[10px] font-bold tracking-wide">Split</span>
                        </button>
                        <button type="button" wire:click="setSalePaymentMode('unpaid')" class="flex flex-col items-center justify-center gap-1 rounded-xl py-2 transition-all {{ $salePaymentStatus === 'unpaid' ? 'bg-amber-50 border border-amber-200 text-amber-700 shadow-sm dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-300' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <x-heroicon-o-clock class="h-5 w-5" />
                            <span class="text-[10px] font-bold tracking-wide">Tempo</span>
                        </button>
                    </div>
                </div>

                <!-- TENDER INPUT (Dynamic) -->
                @if ($salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('cash'))
                    <div class="mt-4 p-3 rounded-xl bg-slate-100/50 dark:bg-slate-900/50 border border-slate-200/50 dark:border-slate-800">
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1.5">{{ __('Uang Diterima') }}</label>
                        <div x-data="{ 
                            display: '',
                            format(val) {
                                let num = String(val).replace(/\D/g, '');
                                return num ? Number(num).toLocaleString('id-ID') : '0';
                            }
                        }" x-init="display = format($wire.saleTenderedAmount); $watch('$wire.saleTenderedAmount', val => display = format(val))">
                            <input type="hidden" wire:model.live.debounce.500ms="saleTenderedAmount" x-ref="hiddenVal">
                            <input type="text" inputmode="numeric"
                                :value="display"
                                @input="display = format($event.target.value); $refs.hiddenVal.value = String($event.target.value).replace(/\D/g, '') || '0'; $refs.hiddenVal.dispatchEvent(new Event('input', { bubbles: true }))"
                                class="w-full rounded-lg border-slate-300 text-lg font-bold shadow-sm focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-950">
                        </div>
                        
                        <div class="flex justify-between items-center mt-3 pt-3 border-t border-slate-200 dark:border-slate-700/50">
                            <span class="text-sm text-slate-500 font-medium">Kembalian</span>
                            <span class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $idNumber($saleChangeDue) }}</span>
                        </div>
                    </div>
                @elseif ($salePaymentStatus === 'paid' && !str($salePaymentMethod)->lower()->contains('split'))
                    <div class="mt-4 p-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800/50 text-center">
                        <div class="mb-1 flex justify-center text-primary-500 dark:text-primary-400">
                            @if(str($salePaymentMethod)->lower()->contains('qris'))
                                <x-heroicon-s-qr-code class="h-6 w-6" />
                            @elseif(str($salePaymentMethod)->lower()->contains('debit'))
                                <x-heroicon-s-credit-card class="h-6 w-6" />
                            @else
                                <x-heroicon-s-arrows-right-left class="h-6 w-6" />
                            @endif
                        </div>
                        <p class="text-xs font-semibold text-primary-700 dark:text-primary-300">{{ $salePaymentMethod }}</p>
                        <p class="text-[10px] text-primary-600/70 dark:text-primary-400/70 mt-0.5">Sistem akan mencatat lunas sesuai total tagihan</p>
                    </div>
                @endif

                @if (str($salePaymentMethod)->lower()->contains('split'))
                    <div class="mt-4 p-3 rounded-xl bg-slate-100/50 dark:bg-slate-900/50 border border-slate-200/50 dark:border-slate-800">
                        <p class="text-[11px] font-bold uppercase text-slate-500 mb-2">{{ __('Daftar Split Tender') }}</p>
                        <div class="space-y-2 mb-3">
                            <x-forms.tom-select id="toko-pos-tender-method" wire:model="saleTenderMethod" placeholder="{{ __('Pilih Metode') }}" dropdown-direction="down">
                                <option value="Cash">Cash</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="QRIS">QRIS</option>
                                <option value="Card">Card</option>
                            </x-forms.tom-select>
                            <div x-data="{ 
                                display: '',
                                format(val) {
                                    let num = String(val).replace(/\D/g, '');
                                    return num ? Number(num).toLocaleString('id-ID') : '';
                                }
                            }" x-init="display = format($wire.saleTenderAmount); $watch('$wire.saleTenderAmount', val => display = format(val))">
                                <input type="hidden" wire:model="saleTenderAmount" x-ref="hiddenVal">
                                <input type="text" inputmode="numeric" placeholder="{{ __('Nominal') }}"
                                    :value="display"
                                    @input="display = format($event.target.value); $refs.hiddenVal.value = String($event.target.value).replace(/\D/g, ''); $refs.hiddenVal.dispatchEvent(new Event('input', { bubbles: true }))"
                                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-950">
                            </div>
                            <div class="flex gap-2">
                                <input type="text" wire:model="saleTenderBankCode" placeholder="{{ __('Bank') }}" class="w-1/2 rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-950">
                                <input type="text" wire:model="saleTenderReference" placeholder="{{ __('Ref') }}" class="w-1/2 rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-950">
                            </div>
                            <button type="button" wire:click="addSaleTenderLine" class="w-full rounded-lg bg-slate-800 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">
                                Tambah Split
                            </button>
                        </div>
                        
                        @if ($saleTenderLines !== [])
                            <div class="space-y-2 border-t border-slate-200 pt-2 dark:border-slate-700">
                                @foreach ($saleTenderLines as $index => $line)
                                    <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-2 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm">
                                        <div>
                                            <p class="text-xs font-bold">{{ $line['method'] }}</p>
                                            <p class="text-[10px] text-slate-500">{{ $line['bank_code'] }} {{ $line['reference'] }}</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold">{{ $idNumber((float) $line['amount']) }}</span>
                                            <button type="button" wire:click="removeSaleTenderLine({{ $index }})" class="text-rose-500"><x-heroicon-m-x-circle class="w-4 h-4" /></button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-between items-center mt-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                                <span class="text-xs text-slate-500 font-medium">Total Split / Kembali</span>
                                <span class="text-sm font-bold {{ $saleChangeDue > 0 ? 'text-amber-600' : 'text-slate-900 dark:text-white' }}">{{ $idNumber($saleTenderTotal) }} / {{ $idNumber($saleChangeDue) }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($salePaymentStatus === 'unpaid')
                    <div class="mt-4 p-3 rounded-xl bg-amber-50/50 dark:bg-amber-900/10 border border-amber-200/50 dark:border-amber-800">
                        <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1.5">{{ __('Jatuh Tempo (Hari)') }}</label>
                        <input type="number" min="0" max="365" step="1" wire:model="saleDueDays" class="w-full rounded-lg border-amber-300 focus:ring-amber-500 dark:border-amber-700 dark:bg-slate-950">
                    </div>
                @endif

                <!-- CHECKOUT BUTTON -->
                <div class="mt-6">
                    <button type="button" wire:click="createCounterSale" wire:loading.attr="disabled" class="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 p-4 text-base font-bold text-white shadow-lg transition-all hover:scale-[1.02] hover:shadow-emerald-500/30 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:hover:scale-100">
                        <div class="absolute inset-0 bg-white/20 translate-y-full transition-transform group-hover:translate-y-0"></div>
                        <x-heroicon-s-check-circle class="h-6 w-6 relative z-10" />
                        <span class="relative z-10">{{ __('Selesaikan Transaksi') }}</span>
                    </button>
                </div>
            </x-admin.panel>
        </div>
    </div>
    @if ($showPosBackOffice)
    <x-admin.panel class="mt-4">
        <div class="px-4 py-4">
            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Invoice Payments') }}</h3>
            <div class="mt-3 grid gap-2 lg:grid-cols-[minmax(0,1fr)_9rem_minmax(0,11rem)_minmax(0,9rem)_minmax(0,9rem)_auto]">
                <x-forms.tom-select
                    id="toko-pos-payment-invoice"
                    wire:model="selectedPaymentInvoiceId"
                    placeholder="{{ __('Invoice') }}"
                    dropdown-direction="down"
                >
                    <option value="">{{ __('Invoice') }}</option>
                    @foreach ($paymentInvoiceOptions as $invoice)
                        <option value="{{ $invoice['id'] }}">{{ $invoice['label'] }}</option>
                    @endforeach
                </x-forms.tom-select>
                <input type="number" min="0.01" step="0.01" wire:model="invoicePaymentAmount" placeholder="{{ __('Amount') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <input type="text" wire:model="invoicePaymentMethod" placeholder="{{ __('Method') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <input type="text" wire:model="invoicePaymentBankCode" placeholder="{{ __('Bank') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <input type="text" wire:model="invoicePaymentReference" placeholder="{{ __('Reference') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <button type="button" wire:click="recordInvoicePayment" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    <x-heroicon-m-check class="h-5 w-5" />
                    <span>{{ __('Record') }}</span>
                </button>
            </div>
        </div>

        <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Cancel Counter Sale') }}</h3>
            <div class="mt-3 grid gap-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.5fr)_auto]">
                <x-forms.tom-select
                    id="toko-pos-cancel-invoice"
                    wire:model="selectedCancelInvoiceId"
                    placeholder="{{ __('Invoice') }}"
                    dropdown-direction="down"
                >
                    <option value="">{{ __('Invoice') }}</option>
                    @foreach ($cancelInvoiceOptions as $invoice)
                        <option value="{{ $invoice['id'] }}">{{ $invoice['label'] }}</option>
                    @endforeach
                </x-forms.tom-select>
                <textarea wire:model="cancelInvoiceReason" rows="1" placeholder="{{ __('Reason') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"></textarea>
                <button type="button" wire:click="cancelCounterSale" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                    <x-heroicon-m-x-mark class="h-5 w-5" />
                    <span>{{ __('Cancel') }}</span>
                </button>
            </div>
        </div>
    </x-admin.panel>
    @endif

    @if ($recentPosInvoices !== [])
    <x-admin.panel class="mt-4 overflow-hidden border-0 ring-1 ring-emerald-500/30 dark:ring-emerald-500/20 shadow-md">
        <div class="bg-emerald-50/50 dark:bg-emerald-900/10 px-4 py-4">
            <h3 class="text-sm font-semibold text-emerald-900 dark:text-emerald-400 flex items-center gap-2">
                <x-heroicon-s-clock class="w-5 h-5" />
                {{ __('Transaksi POS Terbaru (Nota & Surat Jalan)') }}
            </h3>
            <div class="mt-3 grid gap-3 lg:grid-cols-2">
                @foreach ($recentPosInvoices as $invoice)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-emerald-100/80 bg-white px-4 py-3 shadow-sm transition-all hover:shadow-md dark:border-emerald-800/40 dark:bg-slate-900/50">
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">{{ $invoice['number'] }}</p>
                            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                                <span class="{{ $invoice['status'] === 'paid' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">{{ strtoupper($invoice['status']) }}</span>
                                <span class="mx-1">•</span>
                                {{ $invoice['issued_at'] ?? '-' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 dark:text-white mr-2">{{ $idNumber($invoice['total']) }}</span>
                            <x-actions.icon-button href="{{ $invoice['print_url'] }}" target="_blank" variant="primary" label="{{ __('Cetak Nota (A4)') }}">
                                <x-heroicon-s-printer class="h-4 w-4" />
                            </x-actions.icon-button>
                            <x-actions.icon-button href="{{ $invoice['thermal_print_url'] }}" target="_blank" variant="neutral" label="{{ __('Cetak Struk (Thermal)') }}">
                                <x-heroicon-s-ticket class="h-4 w-4" />
                            </x-actions.icon-button>
                            @if ($invoice['has_delivery_letter'])
                                <x-actions.icon-button href="{{ $invoice['delivery_letter_url'] }}" target="_blank" label="{{ __('Cetak SJ') }}" class="bg-white hover:bg-slate-50 text-slate-600 border-slate-200">
                                    <x-heroicon-o-document-text class="h-4 w-4" />
                                </x-actions.icon-button>
                            @else
                                <x-actions.icon-button wire:click="createDeliveryLetterFromInvoice({{ $invoice['id'] }})" label="{{ __('Buat SJ') }}" class="bg-white hover:bg-slate-50 text-slate-600 border-slate-200">
                                    <x-heroicon-o-document-plus class="h-4 w-4" />
                                </x-actions.icon-button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-admin.panel>
    @endif

    <x-admin.panel class="mt-4 border-0 ring-1 ring-slate-200/50 dark:ring-slate-800/50 shadow-sm">
        <div class="border-b border-slate-100 px-4 py-4 dark:border-slate-800">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Retail Transaction List') }}</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Semua histori transaksi retail POS.') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-heroicon-m-magnifying-glass class="h-4 w-4 text-slate-400" />
                        </div>
                        <input id="toko-sales-search" type="search" wire:model.live.debounce.250ms="salesSearch" placeholder="Cari transaksi..." class="min-h-9 w-64 rounded-xl border-slate-300 pl-9 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-900 dark:text-white">
                    </div>
                    @if ($canExport)
                        <x-actions.icon-button href="{{ route('admin.toko.exports.sales') }}" label="{{ __('Export CSV') }}" class="bg-white hover:bg-slate-50 text-slate-600">
                            <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                        </x-actions.icon-button>
                    @endif
                </div>
            </div>
            
            @if ($salesInvoiceDetail)
                <div class="mt-4 rounded-xl border border-primary-200 bg-primary-50/70 p-4 shadow-sm dark:border-primary-900/50 dark:bg-primary-900/20">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400">{{ __('Detail Transaksi') }}</p>
                            <h4 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $salesInvoiceDetail['number'] }}</h4>
                            <p class="mt-1 text-sm font-medium text-slate-600 dark:text-slate-300">
                                {{ $salesInvoiceDetail['customer'] }} <span class="mx-1 text-slate-300">•</span> {{ $salesInvoiceDetail['issued_at'] ?? '-' }} <span class="mx-1 text-slate-300">•</span> {{ $salesInvoiceDetail['status'] }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-actions.icon-button href="{{ $salesInvoiceDetail['print_url'] }}" target="_blank" variant="primary" label="{{ __('Cetak Nota') }}">
                                <x-heroicon-s-printer class="h-4 w-4" />
                            </x-actions.icon-button>
                            <x-actions.icon-button href="{{ $salesInvoiceDetail['thermal_print_url'] }}" target="_blank" variant="neutral" label="{{ __('Cetak Struk (Thermal)') }}">
                                <x-heroicon-o-ticket class="h-4 w-4" />
                            </x-actions.icon-button>
                            <x-actions.icon-button wire:click="clearSalesInvoiceDetail" label="{{ __('Tutup') }}" class="bg-white hover:bg-slate-50 text-slate-600">
                                <x-heroicon-m-x-mark class="h-4 w-4" />
                            </x-actions.icon-button>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 grid-cols-2 md:grid-cols-4">
                        <div class="rounded-lg border border-white/60 bg-white/60 px-3 py-2 shadow-sm dark:border-slate-800/60 dark:bg-slate-900/40">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Payment') }}</p>
                            <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">{{ $salesInvoiceDetail['payment_summary'] }}</p>
                        </div>
                        <div class="rounded-lg border border-white/60 bg-white/60 px-3 py-2 shadow-sm dark:border-slate-800/60 dark:bg-slate-900/40">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Due Date') }}</p>
                            <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">{{ $salesInvoiceDetail['due_at'] ?? '-' }}</p>
                        </div>
                        <div class="rounded-lg border border-white/60 bg-white/60 px-3 py-2 shadow-sm dark:border-slate-800/60 dark:bg-slate-900/40">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Cancel Note') }}</p>
                            <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">{{ $salesInvoiceDetail['cancel_reason'] ?: '-' }}</p>
                        </div>
                        <div class="rounded-lg border border-primary-200/60 bg-primary-100/50 px-3 py-2 text-right shadow-sm dark:border-primary-800/60 dark:bg-primary-900/40">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400">{{ __('Total') }}</p>
                            <p class="mt-0.5 text-base font-black text-slate-900 dark:text-white">{{ $idNumber($salesInvoiceDetail['total']) }}</p>
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200/50 bg-white shadow-sm dark:border-slate-800/50 dark:bg-slate-950">
                        <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900/80 dark:text-slate-400">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">{{ __('Item') }}</th>
                                    <th class="px-4 py-2.5 text-center">{{ __('Qty') }}</th>
                                    <th class="px-4 py-2.5 text-right">{{ __('Unit Price') }}</th>
                                    <th class="px-4 py-2.5 text-right">{{ __('Line Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 bg-white dark:divide-slate-800/50 dark:bg-slate-950">
                                @foreach ($salesInvoiceDetail['items'] as $item)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/50">
                                        <td class="px-4 py-2 text-slate-900 dark:text-slate-100">
                                            <span class="font-medium">{{ $item['description'] ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-center text-slate-600 dark:text-slate-300">{{ $idNumber($item['quantity'], 3) }}</td>
                                        <td class="px-4 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($item['unit_price']) }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($item['line_total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800/80">
                <thead class="bg-slate-50/50 dark:bg-slate-900/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left">{{ __('Invoice') }}</th>
                        <th scope="col" class="px-4 py-3 text-left">{{ __('Customer') }}</th>
                        <th scope="col" class="px-4 py-3 text-left">{{ __('Date') }}</th>
                        <th scope="col" class="px-4 py-3 text-left">{{ __('Status') }}</th>
                        <th scope="col" class="px-4 py-3 text-right">{{ __('Total') }}</th>
                        <th scope="col" class="px-4 py-3 text-center"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800/80 dark:bg-slate-950">
                    @forelse ($salesInvoiceRows as $sale)
                        <tr class="group hover:bg-slate-50/50 transition-colors dark:hover:bg-slate-900/50">
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-primary-600 dark:text-primary-400">{{ $sale['number'] }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-300">{{ $sale['customer'] }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500 dark:text-slate-400">{{ $sale['issued_at'] ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 w-fit">{{ $sale['status'] }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $sale['payment_status'] === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }} w-fit">{{ $sale['payment_status'] }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-bold text-slate-900 dark:text-white">{{ $idNumber($sale['total']) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <x-actions.icon-button wire:click="viewSalesInvoiceDetail({{ $sale['id'] }})" label="{{ __('View') }}" class="bg-white hover:bg-slate-50 text-slate-500">
                                    <x-heroicon-m-eye class="h-4 w-4" />
                                </x-actions.icon-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No sales found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="border-t border-slate-100/80 px-4 py-3 dark:border-slate-800/80 bg-slate-50/30 dark:bg-slate-900/30">
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>{{ __('Page :page of :pages', ['page' => $salesTableMeta['page'], 'pages' => $salesTableMeta['pages']]) }}</span>
                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="prevSalesPage" @disabled($salesTableMeta['page'] <= 1) class="inline-flex min-h-8 items-center justify-center rounded-lg border border-slate-200 px-3 font-semibold text-slate-700 hover:bg-white disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Prev</button>
                        <button type="button" wire:click="nextSalesPage" @disabled($salesTableMeta['page'] >= $salesTableMeta['pages']) class="inline-flex min-h-8 items-center justify-center rounded-lg border border-slate-200 px-3 font-semibold text-slate-700 hover:bg-white disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </x-admin.panel>
    @endif

    @if ($activePage === 'delivery-letters')
    <x-admin.panel>
        <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Delivery Letter List') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Surat jalan list, invoice source, destination, driver, vehicle, and print action.') }}</p>
            </div>
            <x-actions.icon-button href="{{ route('admin.toko.pos') }}" variant="primary" label="{{ __('Create From POS Invoice') }}">
                <x-heroicon-m-plus class="h-5 w-5" />
            </x-actions.icon-button>
        </div>

        <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300">Show</span>
                <span class="rounded-xl border border-slate-100/80 px-3 py-1.5 text-slate-700 dark:border-slate-800/80 dark:text-slate-200">10</span>
                <span class="text-slate-600 dark:text-slate-300">entries</span>
            </div>
            <div class="flex items-center gap-2">
                <label for="toko-delivery-letter-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                <input id="toko-delivery-letter-search" type="search" wire:model.live.debounce.250ms="deliveryLetterSearch" class="min-h-9 w-64 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
            </div>
        </div>

        <div class="overflow-x-auto p-3">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-900">
                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Surat Jalan') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Invoice') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Destination') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Driver') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Vehicle') }}</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($deliveryLetterRows as $letter)
                        <tr wire:key="toko-delivery-letter-row-{{ $letter['id'] }}">
                            <td class="px-3 py-2">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $letter['number'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $letter['issued_at'] ?? '-' }} · {{ $letter['status'] }}</p>
                            </td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $letter['invoice_number'] }}</td>
                            <td class="px-3 py-2">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $letter['destination'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $letter['customer'] }}</p>
                            </td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $letter['driver_name'] }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $letter['vehicle_number'] }}</td>
                            <td class="px-3 py-2 text-right">
                                <x-actions.icon-button href="{{ $letter['print_url'] }}" target="_blank" label="{{ __('Print') }}">
                                    <x-heroicon-o-printer class="h-5 w-5" />
                                </x-actions.icon-button>
                            </td>
                        </tr>
                    @empty
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                            <td colspan="6" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No delivery letters yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col gap-2 border-t border-slate-100/80 px-3 py-2 dark:border-slate-800/80 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($deliveryLetterTableMeta['start']) }} to {{ $idNumber($deliveryLetterTableMeta['end']) }} of {{ $idNumber($deliveryLetterTableMeta['total']) }} delivery letter entries</p>
            <div class="flex flex-wrap justify-end gap-2">
                <button type="button" wire:click="previousDeliveryLetterPage" @disabled($deliveryLetterTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                @php
                    $deliveryLetterPageStart = max(1, $deliveryLetterTableMeta['page'] - 2);
                    $deliveryLetterPageEnd = min($deliveryLetterTableMeta['pages'], $deliveryLetterPageStart + 4);
                    $deliveryLetterPageStart = max(1, $deliveryLetterPageEnd - 4);
                @endphp
                @if ($deliveryLetterPageStart > 1)
                    <button type="button" wire:click="gotoDeliveryLetterPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                    <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                @endif
                @for ($pageNumber = $deliveryLetterPageStart; $pageNumber <= $deliveryLetterPageEnd; $pageNumber++)
                    <button
                        type="button"
                        wire:key="toko-delivery-letter-page-{{ $pageNumber }}"
                        wire:click="gotoDeliveryLetterPage({{ $pageNumber }})"
                        class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $deliveryLetterTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-100/80 text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                    >
                        {{ $idNumber($pageNumber) }}
                    </button>
                @endfor
                @if ($deliveryLetterPageEnd < $deliveryLetterTableMeta['pages'])
                    <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                    <button type="button" wire:click="gotoDeliveryLetterPage({{ $deliveryLetterTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($deliveryLetterTableMeta['pages']) }}</button>
                @endif
                <button type="button" wire:click="nextDeliveryLetterPage" @disabled($deliveryLetterTableMeta['page'] >= $deliveryLetterTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
            </div>
        </div>
    </x-admin.panel>
    @endif

    @if (in_array($activePage, ['inventory', 'returns', 'reports'], true))
    <div class="grid gap-2 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <x-admin.panel>
            <div class="border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Inventory Movements') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Record manual stock documents, returns, and counted stock adjustments.') }}</p>
            </div>

            <div class="flex flex-wrap gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                <a href="#toko-stock-in" class="inline-flex min-h-9 items-center gap-2 rounded-xl border border-slate-100/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">
                    <x-heroicon-o-arrow-down-tray class="h-5 w-5" />
                    <span>{{ __('Stok Masuk') }}</span>
                </a>
                <a href="#toko-stock-out" class="inline-flex min-h-9 items-center gap-2 rounded-xl border border-slate-100/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">
                    <x-heroicon-o-arrow-up-tray class="h-5 w-5" />
                    <span>{{ __('Stok Keluar') }}</span>
                </a>
                <a href="#toko-stock-opname" class="inline-flex min-h-9 items-center gap-2 rounded-xl border border-slate-100/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">
                    <x-heroicon-o-adjustments-horizontal class="h-5 w-5" />
                    <span>{{ __('Stok Sesuai') }}</span>
                </a>
                <a href="{{ route('admin.toko.delivery-letters') }}" class="inline-flex min-h-9 items-center gap-2 rounded-xl border border-slate-100/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">
                    <x-heroicon-o-truck class="h-5 w-5" />
                    <span>{{ __('Surat Jalan') }}</span>
                </a>
            </div>

            <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 lg:grid-cols-[minmax(0,1fr)_8rem_minmax(0,10rem)_auto]">
                <x-forms.tom-select
                    id="toko-inventory-return-product"
                    wire:model="selectedReturnProductId"
                    placeholder="{{ __('Return product') }}"
                    :options="$productOptions"
                    dropdown-direction="down"
                >
                    <option value="">{{ __('Return product') }}</option>
                    @foreach ($productOptions as $product)
                        <option value="{{ $product['id'] }}">{{ $product['label'] }}</option>
                    @endforeach
                </x-forms.tom-select>

                <input
                    type="number"
                    min="0.001"
                    step="0.001"
                    wire:model="returnQuantity"
                    class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
                >

                <x-forms.tom-select
                    id="toko-inventory-return-type"
                    wire:model="returnType"
                    placeholder="{{ __('Return type') }}"
                    dropdown-direction="down"
                >
                    <option value="sales">{{ __('Sales') }}</option>
                    <option value="purchase">{{ __('Purchase') }}</option>
                </x-forms.tom-select>

                <button type="button" wire:click="recordInventoryReturn" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-800/80 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900">
                    <x-heroicon-m-arrow-uturn-left class="h-5 w-5" />
                    <span>{{ __('Record Return') }}</span>
                </button>
            </div>

            <div id="toko-stock-in" class="relative grid scroll-mt-24 gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 lg:grid-cols-[minmax(0,1fr)_8rem_8rem_minmax(0,9rem)_minmax(0,1fr)_auto]">
                <span id="toko-stock-out" class="absolute -top-24"></span>
                <x-forms.tom-select
                    id="toko-inventory-manual-product"
                    wire:model="selectedManualStockProductId"
                    placeholder="{{ __('Manual product') }}"
                    :options="$productOptions"
                    dropdown-direction="down"
                >
                    <option value="">{{ __('Manual product') }}</option>
                    @foreach ($productOptions as $product)
                        <option value="{{ $product['id'] }}">{{ $product['label'] }}</option>
                    @endforeach
                </x-forms.tom-select>

                <x-forms.tom-select
                    id="toko-inventory-manual-type"
                    wire:model="manualStockType"
                    placeholder="{{ __('Movement type') }}"
                    dropdown-direction="down"
                >
                    <option value="in">{{ __('In') }}</option>
                    <option value="out">{{ __('Out') }}</option>
                </x-forms.tom-select>

                <input
                    type="number"
                    min="0.001"
                    step="0.001"
                    wire:model="manualStockQuantity"
                    class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
                >

                <input
                    type="text"
                    wire:model="manualStockReferenceNumber"
                    placeholder="{{ __('Reference') }}"
                    class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
                >

                <input
                    type="text"
                    wire:model="manualStockNotes"
                    placeholder="{{ __('Notes') }}"
                    class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
                >

                <button type="button" wire:click="recordManualStockMovement" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-800/80 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900">
                    <x-heroicon-m-check class="h-5 w-5" />
                    <span>{{ __('Record Stock') }}</span>
                </button>
            </div>

            <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Stock Cancellation') }}</label>
                    <x-forms.tom-select
                        id="toko-inventory-cancel-movement"
                        wire:model="selectedCancelStockMovementId"
                        placeholder="{{ __('Movement') }}"
                        dropdown-direction="down"
                    >
                        <option value="">{{ __('Movement') }}</option>
                        @foreach ($cancelStockMovementOptions as $movement)
                            <option value="{{ $movement['id'] }}">{{ $movement['label'] }}</option>
                        @endforeach
                    </x-forms.tom-select>
                </div>

                <input
                    type="text"
                    wire:model="cancelStockMovementReason"
                    placeholder="{{ __('Cancellation reason') }}"
                    class="min-h-9 self-end rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
                >

                <button type="button" wire:click="cancelStockMovement" wire:confirm="{{ __('Cancel this stock movement with reversal?') }}" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                    <x-heroicon-m-x-mark class="h-5 w-5" />
                    <span>{{ __('Cancel Stock') }}</span>
                </button>
            </div>

            <div id="toko-stock-opname" class="grid scroll-mt-24 gap-2 px-4 py-4 lg:grid-cols-[minmax(0,1fr)_8rem_auto]">
                <x-forms.tom-select
                    id="toko-inventory-adjustment-product"
                    wire:model="selectedAdjustmentProductId"
                    placeholder="{{ __('Opname product') }}"
                    :options="$productOptions"
                    dropdown-direction="down"
                >
                    <option value="">{{ __('Opname product') }}</option>
                    @foreach ($productOptions as $product)
                        <option value="{{ $product['id'] }}">{{ $product['label'] }}</option>
                    @endforeach
                </x-forms.tom-select>

                <input
                    type="number"
                    min="0"
                    step="0.001"
                    wire:model="countedStockQuantity"
                    class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
                >

                <button type="button" wire:click="recordStockOpname" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    <x-heroicon-m-check class="h-5 w-5" />
                    <span>{{ __('Record Opname') }}</span>
                </button>
            </div>

            <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Stock Movement List') }}</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Audit trail for stock in, stock out, returns, cancellations, and opname.') }}</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <label for="toko-inventory-movement-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                        <input id="toko-inventory-movement-search" type="search" wire:model.live.debounce.250ms="inventoryMovementSearch" class="min-h-9 w-64 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    </div>
                </div>

                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900">
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Date') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Product') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Type') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Reference') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Qty') }}</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Unit Cost') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Source') }}</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($inventoryMovementRows as $movement)
                                <tr wire:key="toko-inventory-movement-row-{{ $movement['id'] }}">
                                    <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $movement['date'] }}</td>
                                    <td class="px-3 py-2 font-semibold text-slate-900 dark:text-slate-100">{{ $movement['product'] }}</td>
                                    <td class="px-3 py-2">
                                        <span class="rounded-xl bg-slate-100 px-2 py-1 text-xs font-semibold uppercase text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $movement['type'] }}</span>
                                    </td>
                                    <td class="px-3 py-2 font-mono text-xs text-slate-700 dark:text-slate-200">{{ $movement['reference'] }}</td>
                                    <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($movement['quantity'], 3) }}</td>
                                    <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($movement['unit_cost']) }}</td>
                                    <td class="px-3 py-2 text-xs text-slate-600 dark:text-slate-300">{{ $movement['source'] }}</td>
                                    <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $movement['notes'] }}</td>
                                </tr>
                            @empty
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <td colspan="8" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No stock movements yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-2 border-t border-slate-100/80 px-1 py-3 dark:border-slate-800/80 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($inventoryMovementTableMeta['start']) }} to {{ $idNumber($inventoryMovementTableMeta['end']) }} of {{ $idNumber($inventoryMovementTableMeta['total']) }} stock movement entries</p>
                    <div class="flex flex-wrap items-center gap-1">
                        <button type="button" wire:click="previousInventoryMovementPage" @disabled($inventoryMovementTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                        @php
                            $inventoryMovementPageStart = max(1, $inventoryMovementTableMeta['page'] - 2);
                            $inventoryMovementPageEnd = min($inventoryMovementTableMeta['pages'], $inventoryMovementPageStart + 4);
                            $inventoryMovementPageStart = max(1, $inventoryMovementPageEnd - 4);
                        @endphp
                        @if ($inventoryMovementPageStart > 1)
                            <button type="button" wire:click="gotoInventoryMovementPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                            @if ($inventoryMovementPageStart > 2)
                                <span class="px-2 text-sm text-slate-500 dark:text-slate-400">...</span>
                            @endif
                        @endif
                        @for ($pageNumber = $inventoryMovementPageStart; $pageNumber <= $inventoryMovementPageEnd; $pageNumber++)
                            <button
                                type="button"
                                wire:key="toko-inventory-movement-page-{{ $pageNumber }}"
                                wire:click="gotoInventoryMovementPage({{ $pageNumber }})"
                                class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $inventoryMovementTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-100/80 text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                            >
                                {{ $idNumber($pageNumber) }}
                            </button>
                        @endfor
                        @if ($inventoryMovementPageEnd < $inventoryMovementTableMeta['pages'])
                            @if ($inventoryMovementPageEnd < $inventoryMovementTableMeta['pages'] - 1)
                                <span class="px-2 text-sm text-slate-500 dark:text-slate-400">...</span>
                            @endif
                            <button type="button" wire:click="gotoInventoryMovementPage({{ $inventoryMovementTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($inventoryMovementTableMeta['pages']) }}</button>
                        @endif
                        <button type="button" wire:click="nextInventoryMovementPage" @disabled($inventoryMovementTableMeta['page'] >= $inventoryMovementTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                    </div>
                </div>
            </div>
        </x-admin.panel>

        <x-admin.panel>
            <div class="border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Toko Reports') }}</h2>
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
                <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                    <div>
                        <label for="toko-report-from" class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Report Period') }}</label>
                        <input id="toko-report-from" type="date" wire:model.live="reportFromDate" class="min-h-9 w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    </div>
                    <div>
                        <label for="toko-report-to" class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Until') }}</label>
                        <input id="toko-report-to" type="date" wire:model.live="reportToDate" class="min-h-9 w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    </div>
                    <div class="self-end rounded-xl border border-slate-100/80 px-3 py-2 text-sm text-slate-600 dark:border-slate-800/80 dark:text-slate-300">
                        {{ ($reportPeriod['from'] ?? '') !== '' ? $reportPeriod['from'] : __('All start') }}
                        <span class="mx-1 text-slate-400">-</span>
                        {{ ($reportPeriod['to'] ?? '') !== '' ? $reportPeriod['to'] : __('All end') }}
                    </div>
                </div>

                <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 sm:grid-cols-3">
                    @foreach ([
                        __('Sales') => $tokoReport['sales']['total'],
                        __('Purchases') => $tokoReport['purchases']['total'],
                        __('Gross Profit') => $tokoReport['gross_profit']['estimated'],
                        __('Stock Value') => $tokoReport['stock_valuation']['estimated'],
                        __('AR') => $tokoReport['aging']['accounts_receivable'],
                        __('AP') => $tokoReport['aging']['accounts_payable'],
                        __('Low Stock') => count($tokoReport['low_stock']),
                    ] as $label => $value)
                        <div class="rounded-xl border border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ $label }}</p>
                            <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber((float) $value) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="grid gap-2 px-4 py-4 lg:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Low Stock') }}</h3>
                        <div class="mt-2 space-y-2">
                            @forelse (array_slice($tokoReport['low_stock'], 0, 5) as $product)
                                <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $product['name'] }}</span>
                                    <span class="text-slate-500 dark:text-slate-400"> · {{ $idNumber($product['balance'], 3) }} / {{ $idNumber($product['reorder_point'], 3) }}</span>
                                </div>
                            @empty
                                <p class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm text-slate-500 dark:border-slate-800/80 dark:text-slate-400">{{ __('No low-stock products.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Stock Card') }}</h3>
                        <div class="mt-2 space-y-2">
                            @forelse (array_slice($tokoReport['stock_card'], 0, 5) as $movement)
                                <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $movement['product'] }}</span>
                                    <span class="text-slate-500 dark:text-slate-400"> · {{ $movement['type'] }} · {{ $idNumber($movement['quantity'], 3) }}</span>
                                </div>
                            @empty
                                <p class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm text-slate-500 dark:border-slate-800/80 dark:text-slate-400">{{ __('No stock movements yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Stock Adjustment Report') }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Stock opname adjustment history with previous, counted, and delta quantities.') }}</p>
                        </div>
                        <x-actions.icon-button href="{{ route('admin.toko.stock-adjustments.print') }}" target="_blank" label="{{ __('Print Adjustments') }}">
                            <x-heroicon-o-printer class="h-5 w-5" />
                        </x-actions.icon-button>
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Date') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Product') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Reference') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Previous') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Counted') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Delta') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($stockAdjustmentReportRows as $movement)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $movement['date'] }}</td>
                                        <td class="px-3 py-2 font-semibold text-slate-900 dark:text-slate-100">{{ $movement['product'] }}</td>
                                        <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $movement['reference'] }}</td>
                                        <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($movement['previous_quantity'], 3) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($movement['counted_quantity'], 3) }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($movement['delta'], 3) }}</td>
                                        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $movement['notes'] }}</td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No stock adjustments yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Purchase Recap Report') }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Legacy purchase recap with bill status, vendor, cancellation note, total, and line items.') }}</p>
                        </div>
                        @if ($canExport)
                            <x-actions.icon-button href="{{ route('admin.toko.exports.purchases') }}" label="{{ __('Purchase CSV') }}">
                                <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                            </x-actions.icon-button>
                        @endif
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Bill') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Vendor') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Status') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Note') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($purchaseBillRows as $bill)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-2">
                                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $bill['number'] }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $bill['issued_at'] ?? '-' }}</p>
                                        </td>
                                        <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $bill['vendor'] }}</td>
                                        <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $bill['status'] }}</td>
                                        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $bill['cancel_reason'] ?: '-' }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($bill['total']) }}</td>
                                    </tr>
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td colspan="5" class="bg-slate-50 px-3 py-2 dark:bg-slate-900/70">
                                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Line Items') }}</p>
                                            <div class="mt-2 grid gap-2 lg:grid-cols-2">
                                                @foreach ($bill['items'] as $item)
                                                    <div class="rounded-xl border border-slate-100/80 bg-white px-3 py-2 text-xs dark:border-slate-800/80 dark:bg-slate-950">
                                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $item['description'] }}</p>
                                                        <p class="mt-1 text-slate-600 dark:text-slate-300">{{ $idNumber($item['quantity'], 3) }} x {{ $idNumber($item['unit_cost']) }} = {{ $idNumber($item['line_total']) }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No purchases yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Product Movement Report') }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Period-aware stock movement audit by product, source, reference, quantity, and cost.') }}</p>
                        </div>
                        @if ($canExport)
                            <x-actions.icon-button href="{{ route('admin.toko.exports.report-product-movements', $reportExportQuery) }}" label="{{ __('Movement CSV') }}">
                                <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                            </x-actions.icon-button>
                        @endif
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th scope="col" class="px-3 py-2 text-left">{{ __('Date') }}</th>
                                    <th scope="col" class="px-3 py-2 text-left">{{ __('Product') }}</th>
                                    <th scope="col" class="px-3 py-2 text-left">{{ __('Type') }}</th>
                                    <th scope="col" class="px-3 py-2 text-left">{{ __('Reference') }}</th>
                                    <th scope="col" class="px-3 py-2 text-right">{{ __('Qty') }}</th>
                                    <th scope="col" class="px-3 py-2 text-right">{{ __('Unit Cost') }}</th>
                                    <th scope="col" class="px-3 py-2 text-left">{{ __('Source') }}</th>
                                    <th scope="col" class="px-3 py-2 text-left">{{ __('Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($productMovementReportRows as $movement)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $movement['date'] }}</td>
                                        <td class="px-3 py-2">
                                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $movement['product'] }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $movement['sku'] }}</p>
                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="rounded-xl bg-slate-100 px-2 py-1 text-xs font-semibold uppercase text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $movement['type'] }}</span>
                                        </td>
                                        <td class="px-3 py-2 font-mono text-xs text-slate-700 dark:text-slate-200">{{ $movement['reference'] }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($movement['quantity'], 3) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($movement['unit_cost']) }}</td>
                                        <td class="px-3 py-2 text-xs text-slate-600 dark:text-slate-300">{{ $movement['source'] }}</td>
                                        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $movement['notes'] }}</td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td colspan="8" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No product movements in this period.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid gap-2 border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80 lg:grid-cols-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Sales By Product') }}</h3>
                        <div class="mt-2 space-y-2">
                            @forelse (array_slice($tokoReport['sales']['by_product'], 0, 5) as $row)
                                <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['product'] }}</span>
                                    <span class="text-slate-500 dark:text-slate-400"> · {{ $idNumber($row['quantity'], 3) }} · {{ $idNumber($row['total']) }}</span>
                                </div>
                            @empty
                                <p class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm text-slate-500 dark:border-slate-800/80 dark:text-slate-400">{{ __('No sales yet.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Sales By Customer') }}</h3>
                        <div class="mt-2 space-y-2">
                            @forelse (array_slice($tokoReport['sales']['by_customer'], 0, 5) as $row)
                                <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['customer'] }}</span>
                                    <span class="text-slate-500 dark:text-slate-400"> · {{ $idNumber($row['total']) }}</span>
                                </div>
                            @empty
                                <p class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm text-slate-500 dark:border-slate-800/80 dark:text-slate-400">{{ __('No customers yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="grid gap-2 border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80 lg:grid-cols-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Purchases By Date') }}</h3>
                        <div class="mt-2 space-y-2">
                            @forelse (array_slice($tokoReport['purchases']['by_date'], 0, 5) as $row)
                                <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['date'] }}</span>
                                    <span class="text-slate-500 dark:text-slate-400"> · {{ $idNumber($row['total']) }}</span>
                                </div>
                            @empty
                                <p class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm text-slate-500 dark:border-slate-800/80 dark:text-slate-400">{{ __('No purchases yet.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Purchases By Vendor') }}</h3>
                        <div class="mt-2 space-y-2">
                            @forelse (array_slice($tokoReport['purchases']['by_vendor'], 0, 5) as $row)
                                <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['vendor'] }}</span>
                                    <span class="text-slate-500 dark:text-slate-400"> · {{ $idNumber($row['total']) }}</span>
                                </div>
                            @empty
                                <p class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm text-slate-500 dark:border-slate-800/80 dark:text-slate-400">{{ __('No purchases yet.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Purchases By Product') }}</h3>
                        <div class="mt-2 space-y-2">
                            @forelse (array_slice($tokoReport['purchases']['by_product'], 0, 5) as $row)
                                <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['product'] }}</span>
                                    <span class="text-slate-500 dark:text-slate-400"> · {{ $idNumber($row['quantity'], 3) }} · {{ $idNumber($row['total']) }}</span>
                                </div>
                            @empty
                                <p class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm text-slate-500 dark:border-slate-800/80 dark:text-slate-400">{{ __('No purchases yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Operational Expense Report') }}</h3>
                        @if ($canExport)
                            <x-actions.icon-button href="{{ route('admin.toko.exports.report-operational-expenses', $reportExportQuery) }}" label="{{ __('Export CSV') }}">
                                <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                            </x-actions.icon-button>
                        @endif
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th scope="col" class="px-3 py-2 text-left">{{ __('Date') }}</th>
                                    <th scope="col" class="px-3 py-2 text-left">{{ __('Type') }}</th>
                                    <th scope="col" class="px-3 py-2 text-left">{{ __('Description') }}</th>
                                    <th scope="col" class="px-3 py-2 text-left">{{ __('Payment') }}</th>
                                    <th scope="col" class="px-3 py-2 text-right">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($operationalExpenseReportRows as $row)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $row['date'] }}</td>
                                        <td class="px-3 py-2 font-semibold text-slate-900 dark:text-slate-100">{{ $row['type'] }}</td>
                                        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $row['description'] }}</td>
                                        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $row['payment_method'] }} · {{ $row['bank_code'] }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($row['amount']) }}</td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No operational expenses yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </x-admin.panel>
    </div>
    @endif

    @if ($activePage === 'quotations')
    <x-admin.panel>
        <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Quotation Desk') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Create offers, print quotation, then convert accepted offers to invoice.') }}</p>
            </div>

            <div class="rounded-xl bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-900 dark:bg-slate-900 dark:text-slate-100">
                {{ __('Total') }}: {{ $idNumber($quotationCartTotal) }}
            </div>
        </div>

        <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_8rem_10rem_auto]">
            <x-forms.tom-select
                id="toko-quotation-client"
                wire:model="selectedQuotationClientId"
                placeholder="{{ __('Customer') }}"
                :options="$clientOptions"
                dropdown-direction="down"
            >
                <option value="">{{ __('Customer') }}</option>
                @foreach ($clientOptions as $client)
                    <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
                @endforeach
            </x-forms.tom-select>

            <x-forms.tom-select
                id="toko-quotation-product"
                wire:model="selectedQuotationProductId"
                placeholder="{{ __('Product') }}"
                :options="$productOptions"
                dropdown-direction="down"
            >
                <option value="">{{ __('Product') }}</option>
                @foreach ($productOptions as $product)
                    <option value="{{ $product['id'] }}">{{ $product['label'] }}</option>
                @endforeach
            </x-forms.tom-select>

            <input
                type="number"
                min="0.001"
                step="0.001"
                wire:model="quotationQuantity"
                class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
            >

            <input
                type="number"
                min="0"
                step="0.01"
                wire:model="quotationUnitPrice"
                class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
            >

            <button type="button" wire:click="addToQuotationCart" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-800/80 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900">
                <x-heroicon-m-plus class="h-5 w-5" />
                <span>{{ __('Add') }}</span>
            </button>
        </div>

        <div class="grid gap-2 px-4 py-4 lg:grid-cols-[minmax(0,1fr)_16rem]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Item') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Qty') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Price') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Line') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Remove') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($quotationCart as $index => $item)
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td class="px-3 py-2 text-slate-900 dark:text-slate-100">{{ $item['name'] }}</td>
                                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['quantity'], 3) }}</td>
                                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['unit_price']) }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($item['line_total']) }}</td>
                                <td class="px-3 py-2 text-right">
	                                    <x-actions.icon-button wire:click="removeQuotationCartItem({{ $index }})" variant="danger" label="{{ __('Remove') }}">
                                            <x-heroicon-m-trash class="h-5 w-5" />
                                        </x-actions.icon-button>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Quotation cart is empty.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <button type="button" wire:click="createQuotation" class="inline-flex min-h-9 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                <x-heroicon-m-check class="h-5 w-5" />
                <span>{{ __('Create Quotation') }}</span>
            </button>
        </div>

        <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Data Penawaran') }}</h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Recent Quotations') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <label for="toko-quotation-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                    <input id="toko-quotation-search" type="search" wire:model.live.debounce.250ms="quotationSearch" class="min-h-9 w-64 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                </div>
            </div>

            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Number') }}</th>
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Customer') }}</th>
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Issued') }}</th>
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Valid Until') }}</th>
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Status') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Total') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($quotationRows as $quotation)
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td class="px-3 py-2 font-semibold text-slate-900 dark:text-slate-100">{{ $quotation['number'] }}</td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $quotation['customer'] }}</td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $quotation['issued_at'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $quotation['valid_until'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $quotation['status'] }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($quotation['total']) }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex justify-end gap-2">
                                        <x-actions.icon-button href="{{ $quotation['print_url'] }}" target="_blank" label="{{ __('Print') }}">
                                            <x-heroicon-o-printer class="h-5 w-5" />
                                        </x-actions.icon-button>
                                        @if (! $quotation['converted'] && ! $quotation['rejected'])
                                            <x-actions.icon-button wire:click="markQuotationAccepted({{ $quotation['id'] }})" label="{{ __('Final') }}">
                                                <x-heroicon-m-check class="h-5 w-5" />
                                            </x-actions.icon-button>
                                            <x-actions.icon-button wire:click="markQuotationRejected({{ $quotation['id'] }})" wire:confirm="{{ __('Reject this quotation?') }}" variant="danger" label="{{ __('Reject') }}">
                                                <x-heroicon-m-x-mark class="h-5 w-5" />
                                            </x-actions.icon-button>
                                            <x-actions.icon-button wire:click="convertQuotationToInvoice({{ $quotation['id'] }})" variant="primary" label="{{ __('Invoice') }}">
                                                <x-heroicon-o-document-text class="h-5 w-5" />
                                            </x-actions.icon-button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No quotations yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($quotationTableMeta['start']) }} to {{ $idNumber($quotationTableMeta['end']) }} of {{ $idNumber($quotationTableMeta['total']) }} quotation entries</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="previousQuotationPage" @disabled($quotationTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                    @php
                        $quotationPageStart = max(1, $quotationTableMeta['page'] - 2);
                        $quotationPageEnd = min($quotationTableMeta['pages'], $quotationPageStart + 4);
                        $quotationPageStart = max(1, $quotationPageEnd - 4);
                    @endphp
                    @if ($quotationPageStart > 1)
                        <button type="button" wire:click="gotoQuotationPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                    @endif
                    @for ($pageNumber = $quotationPageStart; $pageNumber <= $quotationPageEnd; $pageNumber++)
                        <button
                            type="button"
                            wire:click="gotoQuotationPage({{ $pageNumber }})"
                            @class([
                                'inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border px-3 text-xs font-semibold',
                                'border-primary-600 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' => $quotationTableMeta['page'] === $pageNumber,
                                'border-slate-100/80 text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900' => $quotationTableMeta['page'] !== $pageNumber,
                            ])
                        >{{ $pageNumber }}</button>
                    @endfor
                    @if ($quotationPageEnd < $quotationTableMeta['pages'])
                        <button type="button" wire:click="gotoQuotationPage({{ $quotationTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($quotationTableMeta['pages']) }}</button>
                    @endif
                    <button type="button" wire:click="nextQuotationPage" @disabled($quotationTableMeta['page'] >= $quotationTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                </div>
            </div>
        </div>
    </x-admin.panel>
    @endif

    @if ($activePage === 'purchases')
    <x-admin.panel>
        <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Purchase Receiving') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Create vendor bill and post stock-in from received items.') }}</p>
            </div>

            <div class="rounded-xl bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-900 dark:bg-slate-900 dark:text-slate-100">
                {{ __('Total') }}: {{ $idNumber($purchaseCartTotal) }}
            </div>
        </div>

        <div class="flex flex-wrap gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
            <a href="#toko-purchase-create" class="inline-flex min-h-9 items-center gap-2 rounded-xl border border-slate-100/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">
                <x-heroicon-o-plus-circle class="h-5 w-5" />
                <span>{{ __('Buat Pembelian') }}</span>
            </a>
            <a href="#toko-purchase-transactions" class="inline-flex min-h-9 items-center gap-2 rounded-xl border border-slate-100/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">
                <x-heroicon-o-clipboard-document-list class="h-5 w-5" />
                <span>{{ __('Data Transaksi') }}</span>
            </a>
            <a href="#toko-purchase-ap" class="inline-flex min-h-9 items-center gap-2 rounded-xl border border-slate-100/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">
                <x-heroicon-o-banknotes class="h-5 w-5" />
                <span>{{ __('Hutang') }}</span>
            </a>
            <a href="#toko-purchase-recap" class="inline-flex min-h-9 items-center gap-2 rounded-xl border border-slate-100/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">
                <x-heroicon-o-chart-bar-square class="h-5 w-5" />
                <span>{{ __('Rekap Pembelian') }}</span>
            </a>
        </div>

        <div id="toko-purchase-create" class="grid scroll-mt-24 gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_8rem_10rem_auto]">
            <x-forms.tom-select
                id="toko-purchase-vendor"
                wire:model="selectedPurchaseVendorId"
                placeholder="{{ __('Vendor') }}"
                :options="$vendorOptions"
                dropdown-direction="down"
            >
                <option value="">{{ __('Vendor') }}</option>
                @foreach ($vendorOptions as $vendor)
                    <option value="{{ $vendor['id'] }}">{{ $vendor['label'] }}</option>
                @endforeach
            </x-forms.tom-select>

            <x-forms.tom-select
                id="toko-purchase-product"
                wire:model="selectedPurchaseProductId"
                placeholder="{{ __('Product') }}"
                :options="$productOptions"
                dropdown-direction="down"
            >
                <option value="">{{ __('Product') }}</option>
                @foreach ($productOptions as $product)
                    <option value="{{ $product['id'] }}">{{ $product['label'] }}</option>
                @endforeach
            </x-forms.tom-select>

            <input
                type="number"
                min="0.001"
                step="0.001"
                wire:model="purchaseQuantity"
                class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
            >

            <input
                type="number"
                min="0"
                step="0.01"
                wire:model="purchaseUnitCost"
                class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
            >

            <button type="button" wire:click="addToPurchaseCart" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-800/80 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900">
                <x-heroicon-m-plus class="h-5 w-5" />
                <span>{{ __('Add') }}</span>
            </button>
        </div>

        <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 lg:grid-cols-[10rem_minmax(0,1fr)_10rem_minmax(0,1fr)]">
            <input
                type="date"
                wire:model="purchaseDueAt"
                aria-label="{{ __('Due Date') }}"
                class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
            >
            <input
                type="text"
                wire:model="purchasePoNumber"
                placeholder="{{ __('PO / Faktur') }}"
                class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
            >
            <input
                type="number"
                min="0"
                step="0.01"
                wire:model="purchaseExtraCost"
                placeholder="{{ __('Biaya lain') }}"
                class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
            >
            <input
                type="text"
                wire:model="purchaseReceiverName"
                placeholder="{{ __('Penerima') }}"
                class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
            >
            <textarea
                wire:model="purchaseNotes"
                rows="2"
                placeholder="{{ __('Keterangan pembelian') }}"
                class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white lg:col-span-4"
            ></textarea>
        </div>

        <div class="grid gap-2 px-4 py-4 lg:grid-cols-[minmax(0,1fr)_16rem]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Item') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Qty') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Cost') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Line') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Remove') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($purchaseCart as $index => $item)
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td class="px-3 py-2 text-slate-900 dark:text-slate-100">{{ $item['name'] }}</td>
                                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['quantity'], 3) }}</td>
                                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['unit_cost']) }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($item['line_total']) }}</td>
                                <td class="px-3 py-2 text-right">
	                                    <x-actions.icon-button wire:click="removePurchaseCartItem({{ $index }})" variant="danger" label="{{ __('Remove') }}">
                                            <x-heroicon-m-trash class="h-5 w-5" />
                                        </x-actions.icon-button>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Purchase cart is empty.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <button type="button" wire:click="createPurchase" class="inline-flex min-h-9 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                <x-heroicon-m-check class="h-5 w-5" />
                <span>{{ __('Create Purchase') }}</span>
            </button>
        </div>

        <div id="toko-purchase-ap" class="scroll-mt-24 border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('AP Aging') }}</h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Open vendor bills grouped by due date.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ $purchaseApAging['total']['label'] }}</p>
                    <p class="text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($purchaseApAging['total']['total']) }}</p>
                </div>
            </div>
            <div class="mt-3 grid gap-2 md:grid-cols-3">
                @foreach (['overdue', 'due_soon', 'not_yet_due'] as $agingBucket)
                    <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ $purchaseApAging[$agingBucket]['label'] }}</p>
                                <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($purchaseApAging[$agingBucket]['total']) }}</p>
                            </div>
                            <span class="rounded-xl bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                {{ $idNumber($purchaseApAging[$agingBucket]['count']) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Pay Vendor Bill') }}</h3>
            <div class="mt-3 grid gap-2 lg:grid-cols-[minmax(0,1fr)_12rem_auto]">
                <x-forms.tom-select
                    id="toko-purchase-vendor-bill-payment"
                    wire:model="selectedVendorBillPaymentId"
                    placeholder="{{ __('Vendor bill') }}"
                    dropdown-direction="down"
                >
                    <option value="">{{ __('Vendor bill') }}</option>
                    @foreach ($vendorBillPaymentOptions as $bill)
                        <option value="{{ $bill['id'] }}">{{ $bill['label'] }}</option>
                    @endforeach
                </x-forms.tom-select>
                <input type="number" min="0" step="0.01" wire:model="vendorBillPaymentAmount" placeholder="{{ __('Amount') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                <button type="button" wire:click="payVendorBill" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    <x-heroicon-o-banknotes class="h-5 w-5" />
                    <span>{{ __('Pay Bill') }}</span>
                </button>
            </div>
        </div>

        <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Vendor Payment History') }}</h3>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Bill') }}</th>
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Vendor') }}</th>
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Paid At') }}</th>
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Journal') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($vendorPaymentHistoryRows as $payment)
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td class="px-3 py-2 font-semibold text-slate-900 dark:text-slate-100">{{ $payment['bill_number'] }}</td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $payment['vendor'] }}</td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $payment['paid_at'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $payment['journal_number'] }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($payment['amount']) }}</td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No vendor payments yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Cancel Purchase') }}</h3>
            <div class="mt-3 grid gap-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.5fr)_auto]">
                <x-forms.tom-select
                    id="toko-purchase-cancel-vendor-bill"
                    wire:model="selectedCancelVendorBillId"
                    placeholder="{{ __('Vendor bill') }}"
                    dropdown-direction="down"
                >
                    <option value="">{{ __('Vendor bill') }}</option>
                    @foreach ($cancelVendorBillOptions as $bill)
                        <option value="{{ $bill['id'] }}">{{ $bill['label'] }}</option>
                    @endforeach
                </x-forms.tom-select>
                <textarea wire:model="cancelPurchaseReason" rows="1" placeholder="{{ __('Reason') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"></textarea>
                <button type="button" wire:click="cancelPurchase" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                    <x-heroicon-m-x-mark class="h-5 w-5" />
                    <span>{{ __('Cancel') }}</span>
                </button>
            </div>
        </div>

        <div id="toko-purchase-recap" class="scroll-mt-24 border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Rekap Pembelian') }}</h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Vendor bill totals, AP exposure, and export shortcuts for purchasing review.') }}</p>
                </div>
                @if ($canExport)
                    <div class="flex flex-wrap gap-2">
                        <x-actions.icon-button href="{{ route('admin.toko.exports.purchases') }}" label="{{ __('Export CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                        <x-actions.icon-button href="{{ route('admin.toko.exports.purchase-lines') }}" label="{{ __('Export Lines') }}">
                            <x-heroicon-o-document-arrow-down class="h-5 w-5" />
                        </x-actions.icon-button>
                    </div>
                @endif
            </div>
            <div class="mt-3 grid gap-2 md:grid-cols-3">
                <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Total Transaksi') }}</p>
                    <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($purchaseTableMeta['total']) }}</p>
                </div>
                <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Total Hutang Terbuka') }}</p>
                    <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($purchaseApAging['total']['total']) }}</p>
                </div>
                <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Pembayaran Terekam') }}</p>
                    <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber(count($vendorPaymentHistoryRows)) }}</p>
                </div>
            </div>
        </div>

        <div id="toko-purchase-transactions" class="scroll-mt-24 border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Purchase List') }}</h3>
                @if ($canExport)
                    <div class="flex flex-wrap gap-2">
                        <x-actions.icon-button href="{{ route('admin.toko.exports.purchases') }}" label="{{ __('Export CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                        <x-actions.icon-button href="{{ route('admin.toko.exports.purchase-lines') }}" label="{{ __('Export Lines') }}">
                            <x-heroicon-o-document-arrow-down class="h-5 w-5" />
                        </x-actions.icon-button>
                    </div>
                @endif
            </div>
            <div class="mt-3 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-slate-600 dark:text-slate-300">Show</span>
                    <span class="rounded-xl border border-slate-100/80 px-3 py-1.5 text-slate-700 dark:border-slate-800/80 dark:text-slate-200">10</span>
                    <span class="text-slate-600 dark:text-slate-300">entries</span>
                </div>
                <div class="flex items-center gap-2">
                    <label for="toko-purchase-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                    <input id="toko-purchase-search" type="search" wire:model.live.debounce.250ms="purchaseSearch" class="min-h-9 w-64 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                </div>
            </div>
            @if ($purchaseBillDetail)
                <div class="mt-3 rounded-xl border border-slate-100/80 bg-slate-50 p-2 dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Purchase Detail') }}</p>
                            <h4 class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $purchaseBillDetail['number'] }}</h4>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $purchaseBillDetail['vendor'] }} · {{ $purchaseBillDetail['issued_at'] ?? '-' }} · {{ $purchaseBillDetail['status'] }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-actions.icon-button href="{{ $purchaseBillDetail['print_url'] }}" target="_blank" label="{{ __('Print') }}">
                                <x-heroicon-o-printer class="h-5 w-5" />
                            </x-actions.icon-button>
                            <x-actions.icon-button wire:click="clearPurchaseBillDetail" label="{{ __('Close') }}">
                                <x-heroicon-m-x-mark class="h-5 w-5" />
                            </x-actions.icon-button>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-2 text-sm md:grid-cols-3 xl:grid-cols-6">
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('PO / Faktur') }}</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $purchaseBillDetail['po_number'] ?: '-' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Penerima') }}</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $purchaseBillDetail['receiver_name'] ?: '-' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Due Date') }}</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $purchaseBillDetail['due_at'] ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Paid At') }}</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $purchaseBillDetail['paid_at'] ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Biaya lain') }}</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($purchaseBillDetail['extra_cost']) }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 text-right dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Total') }}</p>
                            <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($purchaseBillDetail['total']) }}</p>
                        </div>
                    </div>
                    <div class="mt-2 grid gap-2 text-sm md:grid-cols-2">
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Notes') }}</p>
                            <p class="mt-1 text-slate-700 dark:text-slate-200">{{ $purchaseBillDetail['notes'] ?: '-' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Cancel Reason') }}</p>
                            <p class="mt-1 text-slate-700 dark:text-slate-200">{{ $purchaseBillDetail['cancel_reason'] ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-white text-xs font-semibold uppercase text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-2 text-left">{{ __('Item') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Qty') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Cost') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Line') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach ($purchaseBillDetail['items'] as $item)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-2 font-semibold text-slate-900 dark:text-slate-100">{{ $item['description'] }}</td>
                                        <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['quantity'], 3) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['unit_cost']) }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($item['line_total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-900">
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Bill') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Vendor') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Status') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Note') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Total') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($purchaseBillRows as $bill)
                            <tr wire:key="toko-purchase-row-{{ $bill['id'] }}">
                                <td class="px-3 py-2">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $bill['number'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $bill['issued_at'] ?? '-' }}</p>
	                                    <x-actions.icon-button href="{{ $bill['print_url'] }}" target="_blank" label="{{ __('Print') }}">
                                            <x-heroicon-o-printer class="h-5 w-5" />
                                        </x-actions.icon-button>
                                </td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $bill['vendor'] }}</td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $bill['status'] }}</td>
                                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $bill['cancel_reason'] ?: '-' }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($bill['total']) }}</td>
                                <td class="px-3 py-2 text-right">
                                    <x-actions.icon-button wire:click="viewPurchaseBillDetail({{ $bill['id'] }})" label="{{ __('Detail') }}">
                                        <x-heroicon-o-eye class="h-5 w-5" />
                                    </x-actions.icon-button>
                                </td>
                            </tr>
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td colspan="6" class="bg-slate-50 px-3 py-2 dark:bg-slate-900/70">
                                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Line Items') }}</p>
                                    <div class="mt-2 grid gap-2 lg:grid-cols-2">
                                        @foreach ($bill['items'] as $item)
                                            <div class="rounded-xl border border-slate-100/80 bg-white px-3 py-2 text-xs dark:border-slate-800/80 dark:bg-slate-950">
                                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $item['description'] }}</p>
                                                <p class="mt-1 text-slate-600 dark:text-slate-300">{{ $idNumber($item['quantity'], 3) }} x {{ $idNumber($item['unit_cost']) }} = {{ $idNumber($item['line_total']) }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td colspan="6" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No purchases yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 flex flex-col gap-2 border-t border-slate-100/80 pt-3 dark:border-slate-800/80 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($purchaseTableMeta['start']) }} to {{ $idNumber($purchaseTableMeta['end']) }} of {{ $idNumber($purchaseTableMeta['total']) }} purchase entries</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="button" wire:click="previousPurchasePage" @disabled($purchaseTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                    @php
                        $purchasePageStart = max(1, $purchaseTableMeta['page'] - 2);
                        $purchasePageEnd = min($purchaseTableMeta['pages'], $purchasePageStart + 4);
                        $purchasePageStart = max(1, $purchasePageEnd - 4);
                    @endphp
                    @if ($purchasePageStart > 1)
                        <button type="button" wire:click="gotoPurchasePage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                    @endif
                    @for ($pageNumber = $purchasePageStart; $pageNumber <= $purchasePageEnd; $pageNumber++)
                        <button
                            type="button"
                            wire:key="toko-purchase-page-{{ $pageNumber }}"
                            wire:click="gotoPurchasePage({{ $pageNumber }})"
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $purchaseTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-100/80 text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                        >
                            {{ $idNumber($pageNumber) }}
                        </button>
                    @endfor
                    @if ($purchasePageEnd < $purchaseTableMeta['pages'])
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                        <button type="button" wire:click="gotoPurchasePage({{ $purchaseTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($purchaseTableMeta['pages']) }}</button>
                    @endif
                    <button type="button" wire:click="nextPurchasePage" @disabled($purchaseTableMeta['page'] >= $purchaseTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                </div>
            </div>
        </div>
    </x-admin.panel>
    @endif

    @if ($activePage === 'migration')
    <x-admin.panel>
        <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Legacy Import Preview') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $legacyPreview['file']['path'] }}</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                @if ($canImport && $legacyPreview['available'])
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <button type="button" wire:click="importMasterData" wire:confirm="{{ __('Run master data import from the selected legacy dump? Existing records will be skipped.') }}" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                            <span>{{ __('Run Master Import') }}</span>
                        </button>
                        <button type="button" wire:click="importHistoricalDocuments" wire:confirm="{{ __('Run historical document import from the selected legacy dump? Existing legacy quotations, returns, and delivery letters will be skipped.') }}" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-800/80 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900">
                            <x-heroicon-o-document-arrow-down class="h-5 w-5" />
                            <span>{{ __('Run History Import') }}</span>
                        </button>
                    </div>
                @endif

                <label class="sr-only" for="toko-dump-source">{{ __('Dump source') }}</label>
                <x-forms.tom-select
                    id="toko-dump-source"
                    wire:model.live="selectedDumpKey"
                    placeholder="{{ __('Dump source') }}"
                    dropdown-direction="down"
                >
                    @foreach ($dumpSources as $source)
                        <option value="{{ $source['key'] }}" @disabled(! $source['exists'])>
                            {{ $source['label'] }}{{ $source['exists'] ? '' : ' - missing' }}
                        </option>
                    @endforeach
                </x-forms.tom-select>

                <div class="flex flex-wrap gap-2 text-xs font-semibold">
                    <span class="rounded-xl bg-slate-100 px-2.5 py-1 text-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        {{ __('Legacy rows') }}: {{ $idNumber($legacyPreview['totals']['legacy_rows']) }}
                    </span>
                    <span class="rounded-xl bg-slate-100 px-2.5 py-1 text-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        {{ __('Mapped rows') }}: {{ $idNumber($legacyPreview['totals']['mapped_rows']) }}
                    </span>
                    <span class="rounded-xl {{ $legacyPreview['totals']['unmapped_rows'] > 0 ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-200' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200' }} px-2.5 py-1">
                        {{ __('Unmapped rows') }}: {{ $idNumber($legacyPreview['totals']['unmapped_rows']) }}
                    </span>
                    <span class="rounded-xl bg-slate-100 px-2.5 py-1 text-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        {{ __('Tables') }}: {{ $idNumber($legacyPreview['totals']['mapped_tables']) }} / {{ $idNumber($legacyPreview['totals']['legacy_tables']) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid gap-2 border-b border-slate-100/80 px-3 py-2 text-xs text-slate-600 dark:border-slate-800/80 dark:text-slate-300 sm:grid-cols-3">
            @foreach ($dumpSources as $source)
                <div class="rounded-xl border border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                    <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $source['label'] }}</div>
                    <div>{{ $source['exists'] ? __('Available') : __('Missing') }}</div>
                    @if ($source['exists'])
                        <div>{{ $idNumber((int) $source['size_bytes']) }} {{ __('bytes') }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        @if (! $legacyPreview['available'])
            <div class="px-4 py-4">
                <p class="rounded-lg border border-amber-200 bg-amber-50 p-2 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/50 dark:text-amber-200">
                    {{ $legacyPreview['warnings'][0] ?? __('Legacy SQL dump is not available yet.') }}
                </p>
            </div>
        @else
            <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 xl:grid-cols-[1fr_1.4fr]">
                <div class="grid gap-2 sm:grid-cols-2">
                    <div class="rounded-lg border border-slate-100/80 bg-slate-50 p-2 dark:border-slate-800/80 dark:bg-slate-900">
                        <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Mapped Tables') }}</p>
                        <p class="mt-2 text-base font-semibold text-slate-950 dark:text-white">
                            {{ $idNumber($legacyPreview['totals']['mapped_tables']) }} / {{ $idNumber($legacyPreview['totals']['legacy_tables']) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $idNumber($legacyPreview['totals']['mapped_rows']) }} {{ __('rows covered') }}</p>
                    </div>

                    <div class="rounded-lg border {{ $legacyPreview['totals']['unmapped_tables'] > 0 ? 'border-amber-200 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/40' : 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/40' }} p-2">
                        <p class="text-xs font-semibold uppercase {{ $legacyPreview['totals']['unmapped_tables'] > 0 ? 'text-amber-700 dark:text-amber-200' : 'text-emerald-700 dark:text-emerald-200' }}">{{ __('Unmapped Tables') }}</p>
                        <p class="mt-2 text-base font-semibold {{ $legacyPreview['totals']['unmapped_tables'] > 0 ? 'text-amber-900 dark:text-amber-100' : 'text-emerald-900 dark:text-emerald-100' }}">
                            {{ $idNumber($legacyPreview['totals']['unmapped_tables']) }}
                        </p>
                        <p class="mt-1 text-xs {{ $legacyPreview['totals']['unmapped_tables'] > 0 ? 'text-amber-700 dark:text-amber-200' : 'text-emerald-700 dark:text-emerald-200' }}">{{ $idNumber($legacyPreview['totals']['unmapped_rows']) }} {{ __('rows need decision') }}</p>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-100/80 bg-white p-2 dark:border-slate-800/80 dark:bg-slate-950">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Migration Coverage') }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Legacy tables that still need mapping are shown first by row count.') }}</p>
                        </div>
                        <span class="rounded-xl bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            {{ $idNumber($legacyPreview['totals']['legacy_rows']) }} {{ __('legacy rows') }}
                        </span>
                    </div>

                    @if ($legacyPreview['coverage']['unmapped'] !== [])
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach (array_slice($legacyPreview['coverage']['unmapped'], 0, 8) as $gap)
                                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs dark:border-amber-900/60 dark:bg-amber-950/40">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-semibold text-amber-900 dark:text-amber-100">{{ $gap['table'] }}</span>
                                        <span class="text-amber-700 dark:text-amber-200">{{ $idNumber($gap['rows']) }} {{ __('rows') }}</span>
                                    </div>
                                    <p class="mt-1 text-amber-700 dark:text-amber-200">{{ __('Target') }}: {{ $gap['target'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 rounded-xl border border-emerald-100 shadow-sm bg-emerald-50/20 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                            {{ __('All legacy tables in the selected dump have a target mapping.') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($legacyPreview['readiness'] as $readiness)
                    <div class="rounded-lg border border-slate-100/80 bg-slate-50 p-2 dark:border-slate-800/80 dark:bg-slate-900">
                        <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ $readiness['label'] }}</p>
                        <div class="mt-2 flex items-end justify-between gap-2">
                            <div>
                                <p class="text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($readiness['ready']) }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('ready') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-base font-semibold {{ $readiness['issues'] > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-emerald-700 dark:text-emerald-300' }}">{{ $idNumber($readiness['issues']) }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('issues') }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 xl:grid-cols-2">
                <div>
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Existing Data Collisions') }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ __('Target') }}: {{ $targetCompany?->name ?? __('No company selected') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 grid gap-2 sm:grid-cols-3">
                        @foreach ($legacyPreview['collisions'] as $collision)
                            <div class="rounded-xl border border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                                <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ $collision['count'] === 1 ? __('Collision') : __('Collisions') }}</p>
                                <p class="mt-1 text-base font-semibold {{ $collision['count'] > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                                    {{ $idNumber($collision['count']) }}
                                </p>
                                @if ($collision['keys'] !== [])
                                    <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ implode(', ', array_slice($collision['keys'], 0, 3)) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Dry-run Master Import') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('No records are written during this preview.') }}</p>

                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach ([
                            __('Products') => $legacyPreview['dry_run']['products'],
                            __('Customers') => $legacyPreview['dry_run']['customers'],
                            __('Vendors') => $legacyPreview['dry_run']['vendors'],
                            __('Opening Stock') => $legacyPreview['dry_run']['opening_stock'],
                        ] as $label => $plan)
                            <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-xs dark:border-slate-800/80">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $label }}</p>
                                <div class="mt-2 grid grid-cols-3 gap-2 text-slate-600 dark:text-slate-300">
                                    <span>{{ __('Create') }} <strong class="block text-slate-950 dark:text-white">{{ $idNumber($plan['create']) }}</strong></span>
                                    <span>{{ __('Skip') }} <strong class="block text-slate-950 dark:text-white">{{ $idNumber($plan['skip_existing'] ?? $plan['skip_existing_product'] ?? 0) }}</strong></span>
                                    <span>{{ __('Invalid') }} <strong class="block text-slate-950 dark:text-white">{{ $idNumber($plan['invalid']) }}</strong></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if ($latestHistoricalReconciliation !== [])
                <div class="border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Historical Reconciliation') }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Latest historical import totals compared against target records.') }}</p>
                        </div>
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Bucket') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Legacy Count') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Target Count') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Legacy Total') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Target Total') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Gap') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($latestHistoricalReconciliation as $bucket)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-2 font-semibold text-slate-900 dark:text-slate-100">{{ __($bucket['label']) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($bucket['legacy_count']) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($bucket['target_count']) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $bucket['legacy_total'] === null ? '-' : ($bucket['legacy_total']) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $bucket['target_total'] === null ? '-' : ($bucket['target_total']) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">
                                            {{ $idNumber($bucket['count_gap']) }}
                                            @if ($bucket['total_gap'] !== null)
                                                / {{ $idNumber($bucket['total_gap']) }}
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <span class="rounded-xl px-2 py-1 text-xs font-semibold {{ $bucket['matched'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-200' }}">
                                                {{ $bucket['matched'] ? __('Matched') : __('Review') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($latestMonthlyHistoricalReconciliation !== [])
                <div class="border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Monthly Report Reconciliation') }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Legacy monthly report totals compared with imported sales, purchases, expenses, and net income.') }}</p>
                        </div>
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Month') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Sales') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Purchases') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Expenses') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Net Income') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Gap') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($latestMonthlyHistoricalReconciliation as $month)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-2 font-semibold text-slate-900 dark:text-slate-100">{{ $month['month'] }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($month['target']['sales']) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($month['target']['purchases']) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($month['target']['operational_expenses']) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($month['target']['net_income']) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">
                                            {{ $idNumber($month['gaps']['sales']) }}
                                            / {{ $idNumber($month['gaps']['purchases']) }}
                                            / {{ $idNumber($month['gaps']['operational_expenses']) }}
                                            / {{ $idNumber($month['gaps']['net_income']) }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <span class="rounded-xl px-2 py-1 text-xs font-semibold {{ $month['matched'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-200' }}">
                                                {{ $month['matched'] ? __('Matched') : __('Review') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($latestCashBankHistoricalReconciliation !== [])
                <div class="border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Cash/Bank Reconciliation') }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Legacy payment method and bank account totals compared with imported payment metadata.') }}</p>
                        </div>
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Group') }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Account') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Legacy Total') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Target Total') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Gap') }}</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($latestCashBankHistoricalReconciliation as $bucket)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-2 font-semibold text-slate-900 dark:text-slate-100">{{ __($bucket['group']) }}</td>
                                        <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $bucket['bucket'] }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($bucket['legacy_total']) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($bucket['target_total']) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($bucket['gap']) }}</td>
                                        <td class="px-3 py-2 text-right">
                                            <span class="rounded-xl px-2 py-1 text-xs font-semibold {{ $bucket['matched'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-200' }}">
                                                {{ $bucket['matched'] ? __('Matched') : __('Review') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($legacyPreview['issues'] !== [])
                <div class="border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Validation Issues') }}</h3>
                    <div class="mt-2 grid gap-2 lg:grid-cols-2">
                        @foreach (array_slice($legacyPreview['issues'], 0, 12) as $issue)
                            <p class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/50 dark:text-amber-200">{{ $issue }}</p>
                        @endforeach
                    </div>
                    @if (count($legacyPreview['issues']) > 12)
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Showing first :count issues of :total.', ['count' => 12, 'total' => count($legacyPreview['issues'])]) }}
                        </p>
                    @endif
                </div>
            @endif

            @if ($recentRuns !== [])
                <div class="border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Recent Imports') }}</h3>
                    <div class="mt-3 grid gap-2 lg:grid-cols-2">
                        @foreach ($recentRuns as $run)
                            <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">#{{ $run['id'] }} {{ $run['label'] }}</span>
                                    <span class="rounded-xl bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ $run['status'] }}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $idNumber($run['processed_rows']) }} / {{ $idNumber((int) ($run['total_rows'] ?? 0)) }} {{ __('rows') }} · {{ $run['updated_at_human'] }}
                                </p>
                                @if ($run['error_message'])
                                    <p class="mt-1 text-xs text-rose-700 dark:text-rose-300">{{ $run['error_message'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($cutoverReadiness)
                <div class="border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Cutover Readiness') }}</h3>
                        <div class="flex items-center gap-2">
                            @if ($canExport && $legacyPreview['available'])
                                <x-actions.icon-button wire:click="archiveCutoverReport" wire:confirm="{{ __('Archive the selected legacy dump and current migration report?') }}" label="{{ __('Archive Report') }}">
                                    <x-heroicon-o-document-arrow-down class="h-5 w-5" />
                                </x-actions.icon-button>
                            @endif
                            <span class="rounded-xl px-2.5 py-1 text-xs font-semibold {{ $cutoverReadiness['ready'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-200' }}">
                                {{ $cutoverReadiness['ready'] ? __('Ready') : __('Needs Review') }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($cutoverReadiness['checks'] as $key => $check)
                            <div class="rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ __(str($key)->replace('_', ' ')->headline()->toString()) }}</span>
                                    <span class="{{ $check['ready'] ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                                        {{ $check['target'] }} / {{ $check['legacy'] }}
                                    </span>
                                </div>
                                @if ($check['gap'] > 0)
                                    <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">{{ __('Gap') }}: {{ $idNumber($check['gap']) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Legacy Table') }}</th>
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Target') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Rows') }}</th>
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Sample') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach ($legacyPreview['tables'] as $table => $info)
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td class="px-3 py-2 font-mono text-xs text-slate-900 dark:text-slate-100">{{ $table }}</td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $info['target'] }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($info['rows']) }}</td>
                                <td class="max-w-md px-3 py-2 text-xs text-slate-600 dark:text-slate-300">
                                    {{ collect($info['sample'])->take(3)->map(fn ($value, $key) => $key.': '.(is_scalar($value) || $value === null ? (string) $value : json_encode($value)))->join(' / ') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.panel>
    @endif

    @if ($activePage === 'dashboard')
    <div class="grid gap-2 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <x-admin.panel>
            <div class="border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Add-on Scope') }}</h2>
            </div>

            <div class="divide-y divide-slate-200 dark:divide-slate-700">
                @foreach ([
                    ['title' => __('Catalog & Barcode'), 'body' => __('Products, SKU, barcode, unit, cost, selling price, reorder point, and clean search for counter staff.')],
                    ['title' => __('Inventory'), 'body' => __('Stock in, stock out, adjustment, return, stock opname, low-stock view, and legacy stock balance reconciliation.')],
                    ['title' => __('Counter Sales'), 'body' => __('Simple cashier flow for customer, cart, discount, payment status, invoice print, and paid/unpaid tracking.')],
                    ['title' => __('Purchasing'), 'body' => __('Supplier, purchase invoice, received quantity, payable status, and stock-in automation.')],
                    ['title' => __('Documents & Reports'), 'body' => __('Quotation, invoice, delivery letter, return report, profit summary, and CSV/PDF exports.')],
                    ['title' => __('Legacy Migration'), 'body' => __('Toko Pandan tables stay as import sources, then map into audited, company-scoped Laravel tables.')],
                ] as $scope)
                    <div class="grid gap-2 px-3 py-2 sm:grid-cols-[12rem_minmax(0,1fr)]">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $scope['title'] }}</p>
                        <p class="text-sm leading-5 text-slate-600 dark:text-slate-300">{{ $scope['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-admin.panel>

        <x-admin.panel class="p-3">
            <div class="space-y-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Premium Boundary') }}</h2>
                    <p class="mt-1 text-sm leading-5 text-slate-600 dark:text-slate-300">
                        {{ __('This add-on is controlled by the toko_pos license feature and admin.toko_pos permissions.') }}
                    </p>
                </div>

                <div class="rounded-lg border border-slate-100/80 bg-slate-50 p-2 text-sm text-slate-700 dark:border-slate-800/80 dark:bg-slate-900 dark:text-slate-200">
                    <div class="font-mono text-xs">module_type: addon</div>
                    <div class="mt-1 font-mono text-xs">license_feature: toko_pos</div>
                    <div class="mt-1 font-mono text-xs">feature: toko_pos</div>
                    <div class="mt-1 font-mono text-xs">route: admin.toko</div>
                    <div class="mt-1 font-mono text-xs">permission: admin.toko_pos.*</div>
                </div>

                @if (! $canManage)
                    <p class="rounded-lg border border-slate-100/80 bg-white p-2 text-sm text-slate-600 dark:border-slate-800/80 dark:bg-slate-900 dark:text-slate-300">
                        {{ __('You can view this add-on, but need manage permission to run imports or change POS records.') }}
                    </p>
                @endif
            </div>
        </x-admin.panel>
    </div>
    @endif

    @once
        <script>
            window.renderTokoDashboardCharts = window.renderTokoDashboardCharts || function () {
                if (!window.Chart) {
                    return;
                }

                const formatId = (value, decimals = 0) => new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                }).format(Number(value || 0));

                const formatRp = (value) => `Rp${formatId(value, 0)}`;

                document.querySelectorAll('[data-toko-dashboard-charts]').forEach((root) => {
                    let payload = {};

                    try {
                        payload = JSON.parse(root.dataset.chartPayload || '{}');
                    } catch (error) {
                        payload = {};
                    }

	                    const draw = (selector, type, labels, values, color) => {
	                        const canvas = root.querySelector(selector);

                        if (!canvas) {
                            return;
                        }

                        const existing = Chart.getChart(canvas);
                        if (existing) {
                            existing.destroy();
                        }

                        new Chart(canvas, {
                            type,
                            data: {
                                labels: labels || [],
                                datasets: [{
                                    data: values || [],
                                    borderColor: color,
                                    backgroundColor: type === 'bar' ? color : color.replace('1)', '0.16)'),
                                    fill: type !== 'bar',
                                    tension: 0.35,
                                    borderWidth: 2,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: (context) => `${context.dataset.label || ''} ${formatRp(context.parsed.y ?? context.parsed ?? 0)}`.trim(),
                                        },
                                    },
                                },
                                scales: {
                                    x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true } },
                                    y: { beginAtZero: true, grid: { color: 'rgba(148, 163, 184, 0.18)' } },
                                },
                            },
	                        });
	                    };

	                    const drawPie = (selector, labels, values, colors) => {
	                        const canvas = root.querySelector(selector);

	                        if (!canvas) {
	                            return;
	                        }

	                        const existing = Chart.getChart(canvas);
	                        if (existing) {
	                            existing.destroy();
	                        }

	                        new Chart(canvas, {
	                            type: 'pie',
	                            data: {
	                                labels: labels || [],
	                                datasets: [{
	                                    data: values || [],
	                                    backgroundColor: colors,
	                                    borderColor: 'rgba(15, 23, 42, 0.12)',
	                                    borderWidth: 1,
	                                }],
	                            },
	                            options: {
	                                responsive: true,
	                                maintainAspectRatio: false,
	                                plugins: {
	                                    legend: {
	                                        position: 'top',
	                                        labels: { boxWidth: 12 },
	                                    },
                                        tooltip: {
                                            callbacks: {
                                                label: (context) => `${context.label}: ${formatRp(context.parsed || 0)}`,
                                            },
                                        },
	                                },
	                            },
	                        });
	                    };
	
	                    draw('[data-toko-sales-chart]', 'line', payload.sales?.labels, payload.sales?.values, 'rgba(37, 99, 235, 1)');
	                    draw('[data-toko-purchase-chart]', 'line', payload.purchases?.labels, payload.purchases?.values, 'rgba(5, 150, 105, 1)');
	                    draw('[data-toko-products-chart]', 'bar', payload.products?.labels, payload.products?.values, 'rgba(79, 70, 229, 0.85)');
	                    drawPie('[data-toko-revenue-mix-chart]', payload.revenueMix?.labels, payload.revenueMix?.values, ['rgba(244, 63, 94, 0.3)', 'rgba(59, 130, 246, 0.3)', 'rgba(251, 191, 36, 0.35)']);
	                    drawPie('[data-toko-expense-chart]', payload.expenseMix?.labels, payload.expenseMix?.values, ['rgba(168, 85, 247, 0.35)', 'rgba(20, 184, 166, 0.32)', 'rgba(245, 158, 11, 0.32)', 'rgba(96, 165, 250, 0.32)', 'rgba(248, 113, 113, 0.32)', 'rgba(34, 197, 94, 0.32)']);
	                });
	            };

            document.addEventListener('livewire:navigated', () => window.renderTokoDashboardCharts?.());
            document.addEventListener('livewire:updated', () => window.renderTokoDashboardCharts?.());
            queueMicrotask(() => window.renderTokoDashboardCharts?.());
        </script>
    @endonce

    <x-overlays.dialog-modal id="quick-customer-modal" wire:model.live="showingQuickCustomerModal">
        <x-slot name="title">
            {{ __('Tambah Pelanggan Baru') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-forms.label for="quickCustomerName" value="{{ __('Nama Pelanggan') }}" />
                    <x-forms.input id="quickCustomerName" type="text" class="mt-1 block w-full" wire:model="quickCustomerName" placeholder="Contoh: Budi Santoso" />
                    <x-forms.input-error for="quickCustomerName" class="mt-2" />
                </div>
                <div>
                    <x-forms.label for="quickCustomerPhone" value="{{ __('Nomor Telepon') }} ({{ __('Opsional') }})" />
                    <x-forms.input id="quickCustomerPhone" type="text" class="mt-1 block w-full" wire:model="quickCustomerPhone" placeholder="Contoh: 081234567890" />
                    <x-forms.input-error for="quickCustomerPhone" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-actions.button variant="neutral" wire:click="$set('showingQuickCustomerModal', false)" class="mr-3">
                {{ __('Batal') }}
            </x-actions.button>
            <x-actions.button wire:click="createQuickCustomer" variant="primary">
                {{ __('Simpan Pelanggan') }}
            </x-actions.button>
        </x-slot>
    </x-overlays.dialog-modal>

</x-admin.page-shell>
