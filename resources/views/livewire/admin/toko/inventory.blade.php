@if (in_array($activePage, ['inventory', 'returns'], true))
    <x-admin.panel class="border-0 shadow-sm bg-white dark:bg-slate-900">
        <div class="flex flex-col gap-2 border-b border-slate-200/60/80 px-4 py-3.5 dark:border-slate-700/50/80 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Inventory Movements') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Record manual stock documents, returns, and counted stock adjustments.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="#toko-stock-in" class="inline-flex min-h-9 items-center gap-2 rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">
                    <x-heroicon-o-arrow-down-tray class="h-5 w-5" />
                    <span>{{ __('Stok Masuk') }}</span>
                </a>
                <a href="#toko-stock-out" class="inline-flex min-h-9 items-center gap-2 rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">
                    <x-heroicon-o-arrow-up-tray class="h-5 w-5" />
                    <span>{{ __('Stok Keluar') }}</span>
                </a>
                <a href="#toko-stock-opname" class="inline-flex min-h-9 items-center gap-2 rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">
                    <x-heroicon-o-adjustments-horizontal class="h-5 w-5" />
                    <span>{{ __('Stok Sesuai') }}</span>
                </a>
                <a href="{{ route('admin.toko.delivery-letters') }}" class="inline-flex min-h-9 items-center gap-2 rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">
                    <x-heroicon-o-truck class="h-5 w-5" />
                    <span>{{ __('Surat Jalan') }}</span>
                </a>
            </div>
        </div>

        <div class="grid gap-4 border-b border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80 lg:grid-cols-[minmax(0,1fr)_8rem_minmax(0,10rem)_auto]">
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
                placeholder="{{ __('Qty') }}"
                class="min-h-9 rounded-xl border-slate-200 dark:border-slate-700/50 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-white"
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

            <button type="button" wire:click="recordInventoryReturn" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900">
                <x-heroicon-m-arrow-uturn-left class="h-5 w-5" />
                <span>{{ __('Record Return') }}</span>
            </button>
        </div>

        <div id="toko-stock-in" class="relative grid scroll-mt-24 gap-4 border-b border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80 lg:grid-cols-[minmax(0,1fr)_8rem_8rem_minmax(0,9rem)_minmax(0,1fr)_auto]">
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
                placeholder="{{ __('Qty') }}"
                class="min-h-9 rounded-xl border-slate-200 dark:border-slate-700/50 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-white"
            >

            <input
                type="text"
                wire:model="manualStockReferenceNumber"
                placeholder="{{ __('Reference') }}"
                class="min-h-9 rounded-xl border-slate-200 dark:border-slate-700/50 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-white"
            >

            <input
                type="text"
                wire:model="manualStockNotes"
                placeholder="{{ __('Notes') }}"
                class="min-h-9 rounded-xl border-slate-200 dark:border-slate-700/50 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-white"
            >

            <button type="button" wire:click="recordManualStockMovement" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900">
                <x-heroicon-m-check class="h-5 w-5" />
                <span>{{ __('Record Stock') }}</span>
            </button>
        </div>

        <div class="grid gap-4 border-b border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Stock Cancellation') }}</label>
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
                class="min-h-9 self-end rounded-xl border-slate-200 dark:border-slate-700/50 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-white"
            >

            <button type="button" @click="window.PasPapanAlert.confirm('{{ __('Cancel this stock movement with reversal?') }}', () => $wire.cancelStockMovement())" class="inline-flex self-end min-h-9 items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                <x-heroicon-m-x-mark class="h-5 w-5" />
                <span>{{ __('Cancel') }}</span>
            </button>
        </div>

        <div id="toko-stock-opname" class="scroll-mt-24 border-b border-slate-200/60/80 px-4 py-4 dark:border-slate-700/50/80">
            <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Stock Opname') }}</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Enter counted quantities. Leave blank to skip.') }}</p>

            <div class="mt-3 grid gap-4 sm:grid-cols-[minmax(0,1fr)_auto]">
                <x-forms.tom-select
                    id="toko-inventory-adjustment-product"
                    wire:model="selectedAdjustmentProductId"
                    placeholder="{{ __('Product to count') }}"
                    :options="$productOptions"
                    dropdown-direction="down"
                >
                    <option value="">{{ __('Product to count') }}</option>
                    @foreach ($productOptions as $product)
                        <option value="{{ $product['id'] }}">{{ $product['label'] }}</option>
                    @endforeach
                </x-forms.tom-select>

                <div class="flex items-center gap-2">
                    <input type="number" min="0" step="0.001" wire:model="countedStockQuantity" placeholder="{{ __('Counted') }}" class="min-h-9 w-24 rounded-xl border-slate-200 dark:border-slate-700/50 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-white">
                    <button type="button" wire:click="recordStockOpname" class="inline-flex min-h-9 items-center justify-center rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 px-4 text-sm font-semibold text-white shadow-md transition-all hover:from-primary-500 hover:to-primary-400 hover:shadow-lg">
                        {{ __('Adjust') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="px-4 py-4 ">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Stock Movement List') }}</h3>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <label for="toko-inventory-movement-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                    <input id="toko-inventory-movement-search" type="search" wire:model.live.debounce.250ms="inventoryMovementSearch" class="min-h-9 w-64 rounded-xl border-slate-200 dark:border-slate-700/50 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700/50/80 dark:bg-slate-950 dark:text-white">
                </div>
            </div>

            <div class="mt-3 overflow-x-auto rounded-2xl border border-slate-200/60 dark:border-slate-700/50 shadow-sm mt-4">
                <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                    <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                            <th scope="col" class="px-4 py-3 text-left">{{ __('Product') }}</th>
                            <th scope="col" class="px-4 py-3 text-left">{{ __('Type') }}</th>
                            <th scope="col" class="px-4 py-3 text-right">{{ __('Qty') }}</th>
                            <th scope="col" class="px-4 py-3 text-left">{{ __('Ref / Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($inventoryMovementRows as $movement)
                            <tr wire:key="toko-inventory-movement-row-{{ $movement['id'] }}" class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $movement['product'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $movement['date'] }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-xl bg-slate-100 px-2 py-1 text-xs font-semibold uppercase text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $movement['type'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $idNumber($movement['quantity'], 3) }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                                    {{ $movement['reference'] }}<br>{{ $movement['notes'] }}
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300"><td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400 font-medium">{{ __('No stock movements found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($inventoryMovementTableMeta['start']) }} to {{ $idNumber($inventoryMovementTableMeta['end']) }} of {{ $idNumber($inventoryMovementTableMeta['total']) }} stock movement entries</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="button" wire:click="previousInventoryMovementPage" @disabled($inventoryMovementTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                    @php
                        $inventoryMovementPageStart = max(1, $inventoryMovementTableMeta['page'] - 2);
                        $inventoryMovementPageEnd = min($inventoryMovementTableMeta['pages'], $inventoryMovementPageStart + 4);
                        $inventoryMovementPageStart = max(1, $inventoryMovementPageEnd - 4);
                    @endphp
                    @if ($inventoryMovementPageStart > 1)
                        <button type="button" wire:click="gotoInventoryMovementPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                        @if ($inventoryMovementPageStart > 2)
                            <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                        @endif
                    @endif
                    @for ($pageNumber = $inventoryMovementPageStart; $pageNumber <= $inventoryMovementPageEnd; $pageNumber++)
                        <button
                            type="button"
                            wire:key="toko-inventory-movement-page-{{ $pageNumber }}"
                            wire:click="gotoInventoryMovementPage({{ $pageNumber }})"
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $inventoryMovementTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'border border-slate-200/60/80 text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                        >
                            {{ $idNumber($pageNumber) }}
                        </button>
                    @endfor
                    @if ($inventoryMovementPageEnd < $inventoryMovementTableMeta['pages'])
                        @if ($inventoryMovementPageEnd < $inventoryMovementTableMeta['pages'] - 1)
                            <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                        @endif
                        <button type="button" wire:click="gotoInventoryMovementPage({{ $inventoryMovementTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($inventoryMovementTableMeta['pages']) }}</button>
                    @endif
                    <button type="button" wire:click="nextInventoryMovementPage" @disabled($inventoryMovementTableMeta['page'] >= $inventoryMovementTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-2xl border border-slate-200/60 shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-700/50/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                </div>
            </div>
        </div>
    </x-admin.panel>
@endif
