    @if ($activePage === 'products')
        <x-admin.panel 
            class="border-0 shadow-sm bg-white dark:bg-slate-900"
            x-data="{
                init() {
                    let hash = window.location.hash.replace('#', '');
                    if (['categories', 'brands', 'barcode', 'create'].includes(hash)) {
                        $wire.setProductWorkspace(hash);
                    }
                    
                    window.addEventListener('hashchange', () => {
                        let newHash = window.location.hash.replace('#', '');
                        if (['categories', 'brands', 'barcode', 'create'].includes(newHash)) {
                            $wire.setProductWorkspace(newHash);
                        } else if (newHash === '') {
                            $wire.setProductWorkspace('catalog');
                        }
                    });
                }
            }"
            x-effect="
                let current = $wire.productWorkspace;
                if (current && current !== 'catalog') {
                    if (window.location.hash !== '#' + current) {
                        history.pushState(null, null, '#' + current);
                    }
                } else if (current === 'catalog') {
                    if (window.location.hash) {
                        history.pushState(null, null, window.location.pathname + window.location.search);
                    }
                }
            "
        >
            <div class="flex flex-col gap-2 px-4 py-3.5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Data Barang</h2>
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
                        <x-actions.icon-button href="{{ route('admin.toko.exports.products') }}" target="_blank" variant="secondary" label="Export Data">
                            <x-heroicon-m-arrow-up-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                    @else
                        <span aria-label="Impor Data" title="Impor Data" class="wcag-touch-target inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-700 opacity-60 dark:bg-primary-950/30 dark:text-primary-200">
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
                    <!-- Workspace Menu Dropdown -->
                    <div x-data="{ openWorkspaceMenu: false }" class="relative inline-block text-left">
                        <x-actions.icon-button @click="openWorkspaceMenu = !openWorkspaceMenu" @click.away="openWorkspaceMenu = false" label="Menu Workspace">
                            <x-heroicon-m-ellipsis-vertical class="h-5 w-5" />
                        </x-actions.icon-button>
                        
                        <div x-show="openWorkspaceMenu"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             style="display: none;"
                             class="absolute right-0 z-[70] mt-2 w-48 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-slate-900/5 dark:bg-slate-800 dark:ring-slate-700 focus:outline-none overflow-hidden"
                        >
                            <div class="py-1">
                                @php
                                    $customTabs = [
                                        'catalog' => ['label' => 'Data Barang', 'icon' => 'heroicon-m-cube'],
                                        'create' => ['label' => 'Tambah Barang', 'icon' => 'heroicon-m-plus-circle'],
                                        'barcode' => ['label' => 'Barcode', 'icon' => 'heroicon-m-qr-code'],
                                        'categories' => ['label' => 'Kategori', 'icon' => 'heroicon-m-tag'],
                                        'brands' => ['label' => 'Brand', 'icon' => 'heroicon-m-sparkles']
                                    ];
                                @endphp
                                @foreach ($productWorkspaceTabs as $tab)
                                    @php
                                        $tabKey = $tab['key'];
                                        $custom = $customTabs[$tabKey] ?? null;
                                        $label = $custom ? $custom['label'] : $tab['label'];
                                        $icon = $custom ? $custom['icon'] : 'heroicon-m-squares-2x2';
                                        $isActive = $productWorkspace === $tabKey;
                                    @endphp
                                    <button type="button" @click="openWorkspaceMenu = false; $wire.setProductWorkspace('{{ $tabKey }}')" class="group flex w-full items-center px-4 py-2 text-sm transition-colors {{ $isActive ? 'bg-primary-50 text-primary-700 font-bold dark:bg-primary-900/30 dark:text-primary-400' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white' }}">
                                        @svg($icon, 'mr-3 h-4 w-4 shrink-0 ' . ($isActive ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-400 dark:group-hover:text-slate-200'))
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-2 px-4 py-4 sm:grid-cols-2 xl:grid-cols-6">
                @foreach ([
                    ['label' => 'Total Barang', 'value' => $productCatalogSummary['total']],
                    ['label' => 'Aktif', 'value' => $productCatalogSummary['active']],
                    ['label' => 'Stok Limit', 'value' => $productCatalogSummary['low_stock'], 'suffix' => 'stok limit'],
                    ['label' => 'Expired', 'value' => $productCatalogSummary['expired'], 'suffix' => 'expired'],
                    ['label' => 'Brand', 'value' => $productCatalogSummary['brands']],
                    ['label' => 'Kategori', 'value' => $productCatalogSummary['categories']],
                ] as $metric)
                    <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-4 py-3.5 ">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber($metric['value']) }}@if (isset($metric['suffix'])) <span class="text-sm font-medium text-slate-500 dark:text-slate-400 font-medium">{{ $metric['suffix'] }}</span>@endif</p>
                        @if (isset($metric['suffix']))
                            <p class="sr-only">{{ $idNumber($metric['value']) }} {{ $metric['suffix'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($productWorkspace === 'create' || $productWorkspace === 'edit')
                <div x-data="{ 
                        show: true,
                        generateBarcode() {
                            $wire.set('productBarcode', 'BRG' + Math.floor(1000000 + Math.random() * 9000000).toString());
                        }
                    }" 
                    x-init="if('{{ $productWorkspace }}' === 'create' && !$wire.productBarcode) { generateBarcode() }"
                    x-show="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;">
                    <div @click.away="$wire.setProductWorkspace('catalog')" class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-900">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                            <div>
                                <h3 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ $editingProductId ? __('Update Product') : __('Tambah Barang') }}</h3>
                                <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Standard fields stay fast for cashier backoffice; advanced fields keep legacy detail complete.') }}</p>
                            </div>
                            <button type="button" wire:click="setProductWorkspace('catalog')" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-500 dark:hover:bg-slate-800 dark:hover:text-slate-300">
                                <x-heroicon-m-x-mark class="h-6 w-6" />
                            </button>
                        </div>
                        
                        <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                            <div class="mb-4">
                                <div class="inline-flex min-h-9 items-center border-b-2 border-primary-500 px-3 text-sm font-semibold text-primary-700 dark:text-primary-200">Standard</div>
                                <div class="inline-flex min-h-9 items-center px-3 text-sm font-semibold text-slate-500 dark:text-slate-400 font-medium">Advanced</div>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Product name') }}</label>
                                    <input type="text" wire:model="productName" placeholder="{{ __('Product name') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('SKU') }}</label>
                                    <input type="text" wire:model="productSku" placeholder="{{ __('SKU') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Barcode') }}</label>
                                    <div class="flex gap-2">
                                        <input type="text" wire:model="productBarcode" placeholder="{{ __('Barcode') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                        <button type="button" @click="generateBarcode()" class="inline-flex min-h-9 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200" title="Buat Barcode">
                                            <x-heroicon-o-arrow-path class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Status') }}</label>
                                    <x-forms.tom-select id="toko-product-status" wire:model="productStatus" placeholder="{{ __('Status') }}" dropdown-direction="down">
                                        <option value="active">{{ __('Active') }}</option>
                                        <option value="inactive">{{ __('Inactive') }}</option>
                                    </x-forms.tom-select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Brand') }}</label>
                                    <input list="toko-brand-options" type="text" wire:model="productBrand" placeholder="{{ __('Brand') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                    <datalist id="toko-brand-options">
                                        @foreach ($productBrandRows as $brand)
                                            <option value="{{ $brand['name'] }}"></option>
                                        @endforeach
                                    </datalist>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Category') }}</label>
                                    <input list="toko-category-options" type="text" wire:model="productCategory" placeholder="{{ __('Category') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                    <datalist id="toko-category-options">
                                        @foreach ($productCategoryRows as $category)
                                            <option value="{{ $category['name'] }}"></option>
                                        @endforeach
                                    </datalist>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Unit') }}</label>
                                    <input type="text" wire:model="productUnit" placeholder="{{ __('Unit') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Location') }}</label>
                                    <input type="text" wire:model="productLocation" placeholder="{{ __('Location') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Color') }}</label>
                                    <input type="text" wire:model="productColor" placeholder="{{ __('Color') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Size') }}</label>
                                    <input type="text" wire:model="productSize" placeholder="{{ __('Size') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Expired At') }}</label>
                                    <input type="date" wire:model="productExpiredAt" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Reorder point') }}</label>
                                    <input type="number" min="0" step="0.001" wire:model="productReorderPoint" placeholder="{{ __('Reorder point') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Cost price') }}</label>
                                    <input type="number" min="0" step="0.01" wire:model="productCostPrice" placeholder="{{ __('Cost price') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Selling price') }}</label>
                                    <input type="number" min="0" step="0.01" wire:model="productSellingPrice" placeholder="{{ __('Selling price') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/50">
                            <button type="button" wire:click="resetCatalogProductForm" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                <x-heroicon-o-arrow-path class="h-5 w-5" />
                                <span>{{ __('Reset') }}</span>
                            </button>
                            <button
                                type="button"
                                wire:click="saveCatalogProduct"
                                data-form-action="catalog-product"
                                class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-4 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
                            >
                                <x-heroicon-m-check class="h-5 w-5" />
                                <span>{{ $editingProductId ? __('Update') : 'Simpan' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="border-b border-rose-200 bg-rose-50 px-4 py-3.5 text-sm text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">
                    {{ $errors->first() }}
                </div>
            @endif

            @if ($productWorkspace === 'catalog')
            <div class="flex flex-col gap-2 px-4 py-3.5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2 text-sm">
	                    <span class="text-slate-600 dark:text-slate-300">Show</span>
	                    <span class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-4 py-3 text-slate-700 dark:text-slate-200">10</span>
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
                    <input id="toko-product-search" type="search" wire:model.live.debounce.250ms="productSearch" class="min-h-9 w-64 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                </div>
            </div>

            @if ($productStockCardDetail)
                <div class="bg-slate-50 px-4 py-4 dark:bg-slate-900/60">
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Product Stock Card') }}</p>
                            <h3 class="mt-1 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $productStockCardDetail['name'] }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $productStockCardDetail['sku'] ?? '-' }} · {{ $productStockCardDetail['brand'] ?: '-' }} · {{ $productStockCardDetail['category'] ?: '-' }} · {{ $productStockCardDetail['location'] ?: '-' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-actions.icon-button wire:click="$set('barcodeProductId', '{{ $productStockCardDetail['id'] }}'); setProductWorkspace('barcode'); clearProductStockCard();" label="{{ __('Cetak Barcode') }}">
                                <x-heroicon-o-qr-code class="h-5 w-5" />
                            </x-actions.icon-button>
                            <x-actions.icon-button wire:click="clearProductStockCard" label="{{ __('Close') }}">
                                <x-heroicon-m-x-mark class="h-5 w-5" />
                            </x-actions.icon-button>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-2 md:grid-cols-4">
                        <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white p-2 dark:bg-slate-950">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Current Stock') }}</p>
                            <p class="mt-1 font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber($productStockCardDetail['stock_balance'], 3) }} {{ $productStockCardDetail['unit'] }}</p>
                        </div>
                        <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white p-2 dark:bg-slate-950">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Cost') }}</p>
                            <p class="mt-1 font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber($productStockCardDetail['cost_price']) }}</p>
                        </div>
                        <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white p-2 dark:bg-slate-950">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Sale Price') }}</p>
                            <p class="mt-1 font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber($productStockCardDetail['selling_price']) }}</p>
                        </div>
                        <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white p-2 dark:bg-slate-950">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Margin') }}</p>
                            <p class="mt-1 font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber($productStockCardDetail['margin']) }}</p>
                        </div>
                    </div>
                    <div class="mt-3 overflow-x-auto rounded-2xl shadow-sm mt-4">
                        <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                            <thead class="bg-white text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <th class="px-4 py-3.5 text-left">{{ __('Date') }}</th>
                                    <th class="px-4 py-3.5 text-left">{{ __('Type') }}</th>
                                    <th class="px-4 py-3.5 text-left">{{ __('Reference') }}</th>
                                    <th class="px-4 py-3.5 text-right">{{ __('Qty') }}</th>
                                    <th class="px-4 py-3.5 text-right">{{ __('Balance') }}</th>
                                    <th class="px-4 py-3.5 text-right">{{ __('Unit Cost') }}</th>
                                    <th class="px-4 py-3.5 text-left">{{ __('Source') }}</th>
                                    <th class="px-4 py-3.5 text-left">{{ __('Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @forelse ($productStockCardDetail['movements'] as $movement)
                                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                        <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $movement['date'] }}</td>
                                        <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $movement['type'] }}</td>
                                        <td class="px-4 py-3.5 font-mono text-xs text-slate-700 dark:text-slate-200">{{ $movement['reference'] }}</td>
                                        <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($movement['quantity'], 3) }}</td>
                                        <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($movement['balance'], 3) }}</td>
                                        <td class="px-4 py-3.5 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($movement['unit_cost']) }}</td>
                                        <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $movement['source'] }}</td>
                                        <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $movement['notes'] }}</td>
                                    </tr>
                                @empty
                                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                        <td colspan="8" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No stock movements yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto rounded-2xl shadow-sm mt-4">
                <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                    <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                            <th scope="col" class="px-4 py-3 text-left">Action</th>
                            <th scope="col" class="px-4 py-3 text-left">Nama Barang</th>
                            <th scope="col" class="px-4 py-3 text-right">Harga Beli</th>
                            <th scope="col" class="px-4 py-3 text-right">Harga Jual</th>
                            <th scope="col" class="px-4 py-3 text-right">Stok</th>
                            <th scope="col" class="px-4 py-3 text-left">satuan</th>
                            <th scope="col" class="px-4 py-3 text-left">{{ __('Brand') }}</th>
                            <th scope="col" class="px-4 py-3 text-left">Kategori</th>
                            <th scope="col" class="px-4 py-3 text-left">{{ __('Barcode') }}</th>
                            <th scope="col" class="px-4 py-3 text-left">{{ __('Location') }}</th>
                            <th scope="col" class="px-4 py-3 text-left">{{ __('Workflow') }}</th>
                            <th scope="col" class="px-4 py-3 text-right">Margin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($productRows as $product)
                            <tr wire:key="toko-product-row-{{ $product['id'] }}">
                                <td class="px-4 py-3">
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
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $product['name'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $product['sku'] ?? '-' }} · {{ $product['status'] }}@if ($product['is_low_stock']) · Stok Limit @endif @if ($product['is_expired']) · Expired @endif</p>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($product['cost_price']) }}</td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($product['selling_price']) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($product['stock_balance'], 3) }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $product['unit'] }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $product['brand'] ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $product['category'] ?: '-' }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $product['barcode'] ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $product['location'] ?: '-' }}</td>
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
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
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($product['margin']) }}</td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <td colspan="12" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400 font-medium">{{ __('No products yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-2 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($productTableMeta['start']) }} to {{ $idNumber($productTableMeta['end']) }} of {{ $idNumber($productTableMeta['total']) }} entries</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="button" wire:click="previousProductPage" @disabled($productTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                    @php
                        $productPageStart = max(1, $productTableMeta['page'] - 2);
                        $productPageEnd = min($productTableMeta['pages'], $productPageStart + 4);
                        $productPageStart = max(1, $productPageEnd - 4);
                    @endphp
                    @if ($productPageStart > 1)
                        <button type="button" wire:click="gotoProductPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                    @endif
                    @for ($pageNumber = $productPageStart; $pageNumber <= $productPageEnd; $pageNumber++)
                        <button
                            type="button"
                            wire:key="toko-product-page-{{ $pageNumber }}"
                            wire:click="gotoProductPage({{ $pageNumber }})"
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $productTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                        >
                            {{ $idNumber($pageNumber) }}
                        </button>
                    @endfor
                    @if ($productPageEnd < $productTableMeta['pages'])
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                        <button type="button" wire:click="gotoProductPage({{ $productTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($productTableMeta['pages']) }}</button>
                    @endif
                    <button type="button" wire:click="nextProductPage" @disabled($productTableMeta['page'] >= $productTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                    <a href="{{ route('admin.toko.exports.products', $productCatalogFilter === 'all' ? [] : ['filter' => $productCatalogFilter]) }}" aria-label="Excel" title="Excel" class="wcag-touch-target inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">
                        <x-heroicon-o-table-cells class="h-5 w-5" />
                    </a>
                    @if (count($productRows) > 0)
                        <a href="{{ route('admin.toko.products.barcodes', ['products' => collect($productRows)->pluck('id')->take(24)->all(), 'use_stock' => 1, 'format' => 'thermal']) }}" target="_blank" aria-label="Cetak Thermal" title="Cetak Thermal (Roll)" class="wcag-touch-target inline-flex h-10 w-10 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 text-slate-700 dark:text-slate-200">
                            <x-heroicon-o-document-text class="h-5 w-5" />
                        </a>
                        <a href="{{ route('admin.toko.products.barcodes', ['products' => collect($productRows)->pluck('id')->take(24)->all(), 'use_stock' => 1, 'format' => 'a4']) }}" target="_blank" aria-label="Cetak A4" title="Cetak A4 (Stiker)" class="wcag-touch-target inline-flex h-10 w-10 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 text-slate-700 dark:text-slate-200">
                            <x-heroicon-o-printer class="h-5 w-5" />
                        </a>
                    @else
                        <span aria-label="Cetak Thermal" title="Cetak Thermal (Roll)" class="wcag-touch-target inline-flex h-10 w-10 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 text-slate-400 opacity-60 dark:text-slate-500">
                            <x-heroicon-o-document-text class="h-5 w-5" />
                        </span>
                        <span aria-label="Cetak A4" title="Cetak A4 (Stiker)" class="wcag-touch-target inline-flex h-10 w-10 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 text-slate-400 opacity-60 dark:text-slate-500">
                            <x-heroicon-o-printer class="h-5 w-5" />
                        </span>
                    @endif
                </div>
            </div>
            @endif

            @if ($productWorkspace === 'barcode')
                <div x-data="{ useStock: true }">
                    <div class="flex flex-col gap-2 px-4 py-3.5 lg:flex-row lg:items-center lg:justify-between mt-4">
                        <div>
                            <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Modul Cetak Barcode</h2>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Pilih barang untuk mencetak label stiker barcode</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="setProductWorkspace('catalog')" class="inline-flex h-9 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                                <x-heroicon-m-x-mark class="h-4 w-4" />
                                <span>Tutup</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="px-4 py-4">
                        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                            <div class="flex flex-col gap-6">
                                <div class="grid gap-4">
                                    <div>
                                        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Pilih Barang</label>
                                        <x-forms.tom-select
                                            id="toko-barcode-product"
                                            wire:model.live="barcodeProductId"
                                            placeholder="{{ __('Ketik nama atau SKU barang...') }}"
                                            :options="$productOptions"
                                            dropdown-direction="down"
                                        >
                                            <option value="">{{ __('Ketik nama atau SKU barang...') }}</option>
                                            @foreach ($productOptions as $option)
                                                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </x-forms.tom-select>
                                    </div>
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Sesuai Stok?</label>
                                            <label class="flex h-9 items-center gap-2 rounded-xl border border-slate-200 px-3 text-sm font-medium text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900/50">
                                                <input type="checkbox" x-model="useStock" class="rounded border-slate-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 focus:ring-2 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:ring-offset-slate-900">
                                                <span>Otomatis ikuti stok</span>
                                            </label>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Print Custom (Manual)</label>
                                            <input type="number" min="1" wire:model.live="barcodePrintQuantity" placeholder="{{ __('Berapa lembar?') }}" class="h-9 w-full rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800 dark:bg-slate-950 dark:text-white" :disabled="useStock" :class="useStock ? 'opacity-50 cursor-not-allowed bg-slate-50 dark:bg-slate-900' : ''">
                                        </div>
                                    </div>
                                </div>

                                @if ($barcodeProductPreview)
                                    <div class="rounded-2xl border border-slate-200 p-4 shadow-sm dark:border-slate-800">
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">Nama Barang</p>
                                                <p class="mt-1 text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ $barcodeProductPreview['name'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">SKU / Kode</p>
                                                <p class="mt-1 font-mono text-sm text-slate-700 dark:text-slate-200">{{ $barcodeProductPreview['sku'] ?: '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">Barcode Asli</p>
                                                <p class="mt-1 font-mono text-sm text-slate-700 dark:text-slate-200">{{ $barcodeProductPreview['barcode'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">Akan Dicetak</p>
                                                <p class="mt-1 font-bold text-lg text-primary-600 dark:text-primary-400">{{ $barcodeProductPreview['quantity'] }} <span class="text-sm text-slate-500">lembar</span></p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col gap-4">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/50">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium mb-3 text-center">{{ __('Preview Stiker') }}</p>
                                    <div class="mx-auto w-full max-w-[200px] overflow-hidden rounded-lg border border-slate-300 bg-white p-3 text-center text-slate-950 shadow-sm">
                                        <p class="text-[11px] font-bold leading-tight">{{ $barcodeProductPreview['name'] ?? 'Pilih barang dahulu' }}</p>
                                        <p class="mt-2 mb-1 h-8 w-full" style="background: repeating-linear-gradient(90deg, #0f172a 0 2px, transparent 2px 4px, #0f172a 4px 5px, transparent 5px 8px);"></p>
                                        <p class="font-mono text-[13px] font-bold tracking-widest">{{ $barcodeProductPreview['barcode'] ?? '0000000000' }}</p>
                                        <p class="mt-1 text-[9px] text-slate-500">{{ $barcodeProductPreview['sku'] ?? 'SKU' }}</p>
                                    </div>
                                </div>
                                
                                @if ($barcodeProductPreview)
                                    <div class="grid grid-cols-2 gap-2">
                                        <a :href="'{{ $barcodeProductPreview['print_url'] }}' + (String('{{ $barcodeProductPreview['print_url'] }}').includes('?') ? '&' : '?') + (useStock ? 'use_stock=1&' : '') + 'format=thermal'" target="_blank" aria-label="Cetak Thermal" title="Cetak Thermal (Roll)" class="inline-flex min-h-10 w-full items-center justify-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 shadow-sm transition-all px-2 text-xs font-semibold text-white">
                                            <x-heroicon-o-document-text class="h-4 w-4 shrink-0" />
                                            <span class="truncate">Thermal</span>
                                        </a>
                                        <a :href="'{{ $barcodeProductPreview['print_url'] }}' + (String('{{ $barcodeProductPreview['print_url'] }}').includes('?') ? '&' : '?') + (useStock ? 'use_stock=1&' : '') + 'format=a4'" target="_blank" aria-label="Cetak A4" title="Cetak A4 (Stiker)" class="inline-flex min-h-10 w-full items-center justify-center gap-1.5 rounded-xl bg-primary-600 hover:bg-primary-500 shadow-sm transition-all px-2 text-xs font-semibold text-white">
                                            <x-heroicon-o-printer class="h-4 w-4 shrink-0" />
                                            <span class="truncate">A4 Label</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
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
                <div x-data="{ 
                        page: 1, 
                        perPage: 10,
                        search: '',
                        showModal: false,
                        newItem: '',
                        editOldName: null,
                        error: '',
                        rows: @js($taxonomyRows),
                        init() {
                            let observer = new MutationObserver((mutations) => {
                                mutations.forEach((m) => {
                                    if (m.type === 'attributes' && m.attributeName === 'data-rows') {
                                        this.rows = JSON.parse(this.$el.getAttribute('data-rows'));
                                    }
                                });
                            });
                            observer.observe(this.$el, { attributes: true });
                            
                            this.$watch('search', () => { this.page = 1; });
                        },
                        openAdd() {
                            this.editOldName = null;
                            this.newItem = '';
                            this.error = '';
                            this.showModal = true;
                            setTimeout(() => {
                                if (this.$refs.newItemInput) {
                                    this.$refs.newItemInput.focus();
                                }
                            }, 50);
                        },
                        openEdit(row) {
                            this.editOldName = row.name;
                            this.newItem = row.name;
                            this.error = '';
                            this.showModal = true;
                            setTimeout(() => {
                                if (this.$refs.newItemInput) {
                                    this.$refs.newItemInput.focus();
                                }
                            }, 50);
                        },
                        get filteredRows() {
                            if (this.search === '') return this.rows;
                            return this.rows.filter(row => String(row.name).toLowerCase().includes(this.search.toLowerCase()));
                        },
                        get paginatedRows() { 
                            let start = (this.page - 1) * this.perPage;
                            return this.filteredRows.slice(start, start + parseInt(this.perPage));
                        },
                        get totalPages() { return Math.max(1, Math.ceil(this.filteredRows.length / this.perPage)); },
                        get pagesArray() {
                            let start = Math.max(1, this.page - 2);
                            let end = Math.min(this.totalPages, start + 4);
                            start = Math.max(1, end - 4);
                            return Array.from({length: end - start + 1}, (_, i) => start + i);
                        },
                        validateAndSave() {
                            this.error = '';
                            if (!this.newItem.trim()) {
                                this.error = 'Nama tidak boleh kosong';
                                return;
                            }
                            
                            if (this.editOldName && this.newItem.trim().toLowerCase() === this.editOldName.toLowerCase()) {
                                this.showModal = false;
                                return;
                            }
                            
                            let exists = this.rows.some(r => String(r.name).toLowerCase() === this.newItem.trim().toLowerCase());
                            if (exists) {
                                this.error = 'Data sudah ada! (Tidak boleh duplikat)';
                                return;
                            }
                            
                            if (this.editOldName) {
                                $wire.call('{{ $taxonomyDeleteAction }}', this.editOldName);
                            }
                            
                            $wire.set('{{ $taxonomyInput }}', this.newItem.trim());
                            $wire.call('{{ $taxonomyAction }}');
                            
                            this.showModal = false;
                            this.newItem = '';
                            this.search = '';
                        },
                        async confirmDelete(row) {
                            if (row.products_count && row.products_count > 0) {
                                window.PasPapanAlert.modal({
                                    icon: 'error',
                                    title: 'Penghapusan Ditolak!',
                                    text: row.name + ' masih memiliki barang/stok terkait. Kosongkan atau pindahkan barang terlebih dahulu.'
                                });
                                return;
                            }
                            
                            let isConfirmed = await window.PasPapanAlert.confirm('Yakin ingin menghapus ' + row.name + '?');
                            if (isConfirmed) {
                                $wire.call('{{ $taxonomyDeleteAction }}', row.name);
                            }
                        }
                     }"
                     data-rows="{{ json_encode($taxonomyRows) }}"
                >
                    <div class="flex flex-col gap-2 px-4 py-3.5 lg:flex-row lg:items-center lg:justify-between mt-4">
                        <div>
                            <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Kelola {{ $taxonomyTitle }}</h2>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Tambahkan atau hapus daftar {{ strtolower($taxonomyTitle) }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Search Input -->
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <x-heroicon-m-magnifying-glass class="h-5 w-5" />
                                </div>
                                <input 
                                    type="text" 
                                    x-model="search"
                                    placeholder="Cari {{ strtolower($taxonomyTitle) }}..." 
                                    class="h-9 w-48 rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white placeholder:text-slate-400"
                                >
                            </div>
                            <button 
                                type="button" 
                                @click="openAdd" 
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary-600 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-colors"
                                title="Tambah Baru"
                            >
                                <x-heroicon-m-plus class="h-5 w-5" />
                            </button>
                            <button type="button" wire:click="setProductWorkspace('catalog')" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800" title="Tutup">
                                <x-heroicon-m-x-mark class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Add Modal -->
                    <div 
                        x-show="showModal" 
                        style="display: none;"
                        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity"
                    >
                        <div 
                            @click.away="showModal = false"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="w-full max-w-sm mx-4 rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="editOldName ? 'Edit ' + '{{ $taxonomyTitle }}' : 'Tambah ' + '{{ $taxonomyTitle }}'"></h3>
                                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                                    <x-heroicon-m-x-mark class="h-6 w-6" />
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama {{ $taxonomyTitle }}</label>
                                    <input 
                                        type="text" 
                                        x-model="newItem"
                                        x-ref="newItemInput"
                                        @keydown.enter="validateAndSave"
                                        placeholder="Ketik disini..." 
                                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white placeholder:text-slate-400"
                                    >
                                    <template x-if="error">
                                        <p class="mt-2 text-xs font-semibold text-red-500" x-text="error"></p>
                                    </template>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button 
                                    type="button" 
                                    @click="showModal = false"
                                    class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="button" 
                                    @click="validateAndSave"
                                    class="inline-flex h-10 items-center justify-center rounded-xl bg-primary-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-primary-500"
                                >
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl shadow-sm mt-4 min-h-[250px]">
                        <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                            <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <th scope="col" class="px-4 py-3 text-left">Kode</th>
                                    <th scope="col" class="px-4 py-3 text-left">Nama</th>
                                    <th scope="col" class="px-4 py-3 text-right">Barang</th>
                                    <th scope="col" class="px-4 py-3 text-left">Source</th>
                                    <th scope="col" class="px-4 py-3 text-right">Opsi</th>
                                </tr>
                            </thead>
                            <tbody wire:ignore class="divide-y divide-slate-200 dark:divide-slate-700">
                                <template x-for="row in paginatedRows" :key="row.name">
                                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                        <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-400" x-text="row.code"></td>
                                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white" x-text="row.name"></td>
                                        <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-400">
                                            <span x-text="row.products_count ? new Intl.NumberFormat('id-ID').format(row.products_count) : '0'"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-medium ring-1 ring-inset" 
                                                  :class="row.source === 'setting' ? 'bg-blue-50 text-blue-700 ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-slate-50 text-slate-600 ring-slate-500/10 dark:bg-slate-800 dark:text-slate-400'"
                                                  x-text="row.source">
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div x-data="{ openOptions: false }" class="relative inline-block text-left">
                                                <button type="button" @click.stop="openOptions = !openOptions" @click.away="openOptions = false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-500 dark:hover:bg-slate-800 dark:hover:text-slate-300 transition-colors">
                                                    <x-heroicon-m-ellipsis-vertical class="h-5 w-5" />
                                                </button>
                                                
                                                <div x-show="openOptions"
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="transform opacity-0 scale-95"
                                                     x-transition:enter-end="transform opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-75"
                                                     x-transition:leave-start="transform opacity-100 scale-100"
                                                     x-transition:leave-end="transform opacity-0 scale-95"
                                                     style="display: none;"
                                                     class="absolute right-0 z-[60] mt-1 w-32 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-slate-900/5 dark:bg-slate-800 dark:ring-slate-700 focus:outline-none overflow-hidden"
                                                >
                                                    <div class="py-1">
                                                        <button type="button" @click.stop="openOptions = false; openEdit(row)" class="group flex w-full items-center px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700/50">
                                                            <x-heroicon-m-pencil-square class="mr-3 h-4 w-4 text-slate-400 group-hover:text-primary-500 dark:text-slate-400 dark:group-hover:text-primary-400" />
                                                            Edit
                                                        </button>
                                                        <button type="button" @click.stop="openOptions = false; confirmDelete(row)" class="group flex w-full items-center px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                                            <x-heroicon-m-trash class="mr-3 h-4 w-4 text-red-500 group-hover:text-red-600 dark:text-red-400 dark:group-hover:text-red-300" />
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="rows.length === 0">
                                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                        <td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400 font-medium">{{ __('No data yet.') }}</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="flex flex-col gap-2 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between" x-show="rows.length > 0">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Showing <span x-text="Math.min((page - 1) * perPage + 1, rows.length)"></span> to <span x-text="Math.min(page * perPage, rows.length)"></span> of <span x-text="rows.length"></span> entries
                        </p>
                        <div class="flex flex-wrap justify-end gap-2">
                            <button type="button" @click="page--" :disabled="page <= 1" class="inline-flex min-h-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                            
                            <template x-for="p in pagesArray" :key="p">
                                <button
                                    type="button"
                                    @click="page = p"
                                    class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold"
                                    :class="page === p ? 'bg-gradient-to-r from-primary-600 to-primary-500 text-white hover:from-primary-500 hover:to-primary-400 hover:shadow-lg' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900'"
                                    x-text="p"
                                ></button>
                            </template>
                            
                            <button type="button" @click="page++" :disabled="page >= totalPages" class="inline-flex min-h-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                        </div>
                    </div>
                </div>
            @endif

        </x-admin.panel>
    @endif