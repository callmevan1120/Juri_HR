        @if ($activePage === 'pos')
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start" x-data="{ barcodeFocus: true }" @keydown.window="if($event.key === 'F2') { $refs.barcodeInput.focus(); $event.preventDefault(); }">
        @if ($lastCreatedInvoice)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 dark:bg-black/60 transition-all" wire:transition>
            <div class="w-full max-w-sm scale-100 rounded-3xl bg-white p-8 shadow-[0_0_40px_-10px_rgba(0,0,0,0.1)] transition-all dark:bg-slate-900 dark:shadow-[0_0_40px_-10px_rgba(0,0,0,0.5)] ">
                <div class="flex flex-col items-center text-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <x-heroicon-o-check class="h-8 w-8" stroke-width="2.5" />
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-50">{{ __('Transaksi Berhasil!') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('Nota :number telah dibuat dengan total tagihan :total.', ['number' => $lastCreatedInvoice['number'], 'total' => \App\Support\Helpers::formatNumberId($lastCreatedInvoice['total'])]) }}</p>
                    
                    <div class="mt-6 w-full space-y-3">
                        <a href="{{ $lastCreatedInvoice['thermal_print_url'] }}" target="_blank" class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                            <x-heroicon-s-ticket class="h-5 w-5" />
                            {{ __('Cetak Struk (Thermal)') }}
                        </a>
                        <a href="{{ $lastCreatedInvoice['print_url'] }}" target="_blank" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-white/50  px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition-all dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                            <x-heroicon-s-printer class="h-5 w-5" />
                            {{ __('Cetak Nota (A4 / A5)') }}
                        </a>
                    </div>
                    
                    <div class="mt-6 w-full pt-4 ">
                        <button type="button" wire:click="$set('lastCreatedInvoice', null)" class="w-full text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200">
                            {{ __('Tutup & Transaksi Baru') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- LEFT COLUMN (Products & Cart) -->
        <div class="flex-1 space-y-4">
            
            <!-- HEADER & PRODUCT SELECTION -->
            <x-admin.panel style="overflow: visible;" class="p-4 shadow-sm border-0 shadow-sm  bg-white dark:bg-slate-900">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-primary-50 text-primary-700 ring-1 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-500/20">
                                <x-heroicon-s-shopping-bag class="h-5 w-5" />
                            </span>
                            {{ __('Terminal POS') }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">{{ __('Tekan F2 untuk fokus ke barcode scanner.') }}</p>
                    </div>
                    <div class="flex gap-2">
                        <x-actions.icon-button wire:click="$refresh" label="Refresh" class="bg-white dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 hover:bg-slate-50">
                            <x-heroicon-m-arrow-path class="h-4 w-4" />
                        </x-actions.icon-button>
                        <x-actions.icon-button wire:click="$toggle('showPosBackOffice')" variant="{{ $showPosBackOffice ? 'primary' : 'neutral' }}" label="Tools Admin" class="{{ $showPosBackOffice ? '' : 'bg-white hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
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
                    <div class="flex items-center bg-white  dark:bg-slate-900 rounded-xl shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500 transition-all">
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
                        class="absolute z-50 w-full mt-3 bg-white dark:bg-slate-800 rounded-2xl shadow-sm transition-all hover:shadow-md shadow-xl overflow-hidden"
                    >
                        <div class="max-h-60 overflow-y-auto p-1">
                            <template x-for="(opt, index) in filteredOptions" :key="opt.id">
                                <div 
                                    @click="selectOption(opt.id)" 
                                    @mouseenter="highlightedIndex = index"
                                    :data-index="index"
                                    :class="{'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300': highlightedIndex === index, 'text-slate-700 dark:text-slate-200': highlightedIndex !== index}"
                                    class="px-4 py-3 mb-1 text-sm cursor-pointer rounded-lg transition-colors flex items-center justify-between group"
                                >
                                    <span x-text="opt.name" class="truncate pr-4"></span>
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-primary-500 opacity-0 group-hover:opacity-100" x-show="highlightedIndex === index">{{ __('Pilih') }}</span>
                                </div>
                            </template>
                            <template x-if="filteredOptions.length === 0">
                                <div class="px-4 py-4 text-sm text-slate-500 dark:text-slate-400 font-medium text-center flex flex-col items-center justify-center gap-2">
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
            <x-admin.panel class="overflow-hidden border-0 shadow-sm  bg-white dark:bg-slate-900 shadow-sm">
                <div class="px-4 py-3 ">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">{{ __('Keranjang Transaksi') }}</h3>
                </div>
                <div class="overflow-x-auto rounded-2xl shadow-sm mt-4 min-h-[300px]">
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
        <div class="w-full shrink-0 lg:w-96 space-y-4 sticky top-6">
            
            <x-admin.panel class="p-4 border-0 shadow-sm  bg-white dark:bg-slate-900 shadow-sm bg-gradient-to-b from-slate-50/50 to-white dark:from-slate-900/50 dark:to-slate-950">
                <!-- CUSTOMER & INVOICE NO -->
                <div class="flex items-center justify-between mb-4 pb-4 gap-2">
                    <div class="flex-1">
                        <label class="sr-only" for="toko-pos-client">{{ __('Customer') }}</label>
                        <x-forms.tom-select id="toko-pos-client" wire:model="selectedClientId" placeholder="{{ __('Pelanggan Umum (Walk-in)') }}" :options="$clientOptions" dropdown-direction="down">
                            <option value="">{{ __('Pelanggan Umum (Walk-in)') }}</option>
                            @foreach ($clientOptions as $client)
                                <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
                            @endforeach
                        </x-forms.tom-select>
                    </div>
                    <button type="button" wire:click="$set('showingQuickCustomerModal', true)" class="p-2.5 rounded-lg bg-white dark:bg-slate-800 text-slate-500 hover:text-primary-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors tooltip" data-tippy-content="{{ __('Tambah Pelanggan Baru') }}">
                        <x-heroicon-m-user-plus class="h-5 w-5" />
                    </button>
                </div>

                <!-- GRAND TOTAL DISPLAY -->
                <div class="rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 p-6 text-center shadow-lg relative overflow-hidden dark:from-slate-950 dark:to-black ring-1 ring-white/10">
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
                            <input type="number" min="0" step="0.01" wire:model.live.debounce.500ms="saleDiscountAmount" class="w-full rounded-lg border-slate-200 py-1.5 text-sm shadow-sm focus:ring-primary-500 dark:bg-slate-900">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Charge (+)</label>
                            <input type="number" min="0" step="0.01" wire:model.live.debounce.500ms="saleAdditionalCharge" class="w-full rounded-lg border-slate-200 py-1.5 text-sm shadow-sm focus:ring-primary-500 dark:bg-slate-900">
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-3 mt-3 ">
                        <span class="font-semibold text-slate-900 dark:text-slate-50">Jumlah Bayar</span>
                        <span class="font-bold text-slate-900 dark:text-slate-50">{{ $idNumber($saleTenderTotal) }}</span>
                    </div>
                </div>

                <!-- PAYMENT METHODS -->
                <div class="mt-5 pt-5 ">
                    <p class="mb-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Pilih Pembayaran') }}</p>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" wire:click="setSalePaymentMode('cash')" class="flex flex-col items-center justify-center gap-1 rounded-xl py-2 transition-all {{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('cash') ? 'bg-primary-50 border border-primary-200 text-primary-700 shadow-sm dark:bg-primary-900/30 dark:border-primary-800 dark:text-primary-300' : 'bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <x-heroicon-o-banknotes class="h-5 w-5" />
                            <span class="text-[10px] font-bold tracking-wide">Tunai</span>
                        </button>
                        <button type="button" wire:click="setSalePaymentMode('qris')" class="flex flex-col items-center justify-center gap-1 rounded-xl py-2 transition-all {{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('qris') ? 'bg-primary-50 border border-primary-200 text-primary-700 shadow-sm dark:bg-primary-900/30 dark:border-primary-800 dark:text-primary-300' : 'bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <x-heroicon-o-qr-code class="h-5 w-5" />
                            <span class="text-[10px] font-bold tracking-wide">QRIS</span>
                        </button>
                        <button type="button" wire:click="setSalePaymentMode('debit')" class="flex flex-col items-center justify-center gap-1 rounded-xl py-2 transition-all {{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('debit') ? 'bg-primary-50 border border-primary-200 text-primary-700 shadow-sm dark:bg-primary-900/30 dark:border-primary-800 dark:text-primary-300' : 'bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <x-heroicon-o-credit-card class="h-5 w-5" />
                            <span class="text-[10px] font-bold tracking-wide">Debit</span>
                        </button>
                        <button type="button" wire:click="setSalePaymentMode('transfer')" class="flex flex-col items-center justify-center gap-1 rounded-xl py-2 transition-all {{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('transfer') ? 'bg-primary-50 border border-primary-200 text-primary-700 shadow-sm dark:bg-primary-900/30 dark:border-primary-800 dark:text-primary-300' : 'bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <x-heroicon-o-arrows-right-left class="h-5 w-5" />
                            <span class="text-[10px] font-bold tracking-wide">Transfer</span>
                        </button>
                        <button type="button" wire:click="setSalePaymentMode('split')" class="flex flex-col items-center justify-center gap-1 rounded-xl py-2 transition-all {{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('split') ? 'bg-primary-50 border border-primary-200 text-primary-700 shadow-sm dark:bg-primary-900/30 dark:border-primary-800 dark:text-primary-300' : 'bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <x-heroicon-o-rectangle-stack class="h-5 w-5" />
                            <span class="text-[10px] font-bold tracking-wide">Split</span>
                        </button>
                        <button type="button" wire:click="setSalePaymentMode('unpaid')" class="flex flex-col items-center justify-center gap-1 rounded-xl py-2 transition-all {{ $salePaymentStatus === 'unpaid' ? 'bg-amber-50 border border-amber-200 text-amber-700 shadow-sm dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-300' : 'bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <x-heroicon-o-clock class="h-5 w-5" />
                            <span class="text-[10px] font-bold tracking-wide">Tempo</span>
                        </button>
                    </div>
                </div>

                <!-- TENDER INPUT (Dynamic) -->
                @if ($salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('cash'))
                    <div class="mt-4 p-3 rounded-xl bg-slate-100 dark:bg-slate-900/50 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-white dark:hover:bg-slate-800 focus:bg-white dark:focus:bg-slate-900 ">
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
                                class="w-full rounded-lg border-slate-300 text-lg font-bold shadow-sm focus:ring-primary-500 dark:bg-slate-950">
                        </div>
                        
                        <div class="flex justify-between items-center mt-3 pt-3 ">
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
                    <div class="mt-4 p-3 rounded-xl bg-slate-100 dark:bg-slate-900/50 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-white dark:hover:bg-slate-800 focus:bg-white dark:focus:bg-slate-900 ">
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
                                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm dark:bg-slate-950">
                            </div>
                            <div class="flex gap-2">
                                <input type="text" wire:model="saleTenderBankCode" placeholder="{{ __('Bank') }}" class="w-1/2 rounded-lg border-slate-300 text-sm shadow-sm dark:bg-slate-950">
                                <input type="text" wire:model="saleTenderReference" placeholder="{{ __('Ref') }}" class="w-1/2 rounded-lg border-slate-300 text-sm shadow-sm dark:bg-slate-950">
                            </div>
                            <button type="button" wire:click="addSaleTenderLine" class="w-full rounded-lg bg-slate-800 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">
                                Tambah Split
                            </button>
                        </div>
                        
                        @if ($saleTenderLines !== [])
                            <div class="space-y-2 pt-2 ">
                                @foreach ($saleTenderLines as $index => $line)
                                    <div class="flex items-center justify-between bg-white  dark:bg-slate-900 p-2 rounded-lg shadow-sm">
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
                            <div class="flex justify-between items-center mt-3 pt-2 ">
                                <span class="text-xs text-slate-500 font-medium">Total Split / Kembali</span>
                                <span class="text-sm font-bold {{ $saleChangeDue > 0 ? 'text-amber-600' : 'text-slate-900 dark:text-slate-50' }}">{{ $idNumber($saleTenderTotal) }} / {{ $idNumber($saleChangeDue) }}</span>
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
            <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Invoice Payments') }}</h3>
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
                <input type="number" min="0.01" step="0.01" wire:model="invoicePaymentAmount" placeholder="{{ __('Amount') }}" class="min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                <input type="text" wire:model="invoicePaymentMethod" placeholder="{{ __('Method') }}" class="min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                <input type="text" wire:model="invoicePaymentBankCode" placeholder="{{ __('Bank') }}" class="min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                <input type="text" wire:model="invoicePaymentReference" placeholder="{{ __('Reference') }}" class="min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                <button type="button" wire:click="recordInvoicePayment" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-4 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    <x-heroicon-m-check class="h-5 w-5" />
                    <span>{{ __('Record') }}</span>
                </button>
            </div>
        </div>

        <div class="px-4 py-4 ">
            <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Cancel Counter Sale') }}</h3>
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
                <textarea wire:model="cancelInvoiceReason" rows="1" placeholder="{{ __('Reason') }}" class="min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white"></textarea>
                <button type="button" wire:click="cancelCounterSale" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                    <x-heroicon-m-x-mark class="h-5 w-5" />
                    <span>{{ __('Cancel') }}</span>
                </button>
            </div>
        </div>
    </x-admin.panel>
    @endif

    @if ($recentPosInvoices !== [])
    <x-admin.panel class="mt-4 overflow-hidden border-0 ring-1 ring-emerald-500/30 dark:ring-emerald-500/20 shadow-md">
        <div class="bg-gradient-to-r from-emerald-50 to-transparent dark:from-emerald-900/20 dark:to-transparent px-5 py-5 ">
            <h3 class="text-sm font-semibold text-emerald-900 dark:text-emerald-400 flex items-center gap-2">
                <x-heroicon-s-clock class="w-5 h-5" />
                {{ __('Transaksi POS Terbaru (Nota & Surat Jalan)') }}
            </h3>
            <div class="mt-3 grid gap-3 lg:grid-cols-2">
                @foreach ($recentPosInvoices as $invoice)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-emerald-100/80 bg-white px-4 py-3 shadow-sm transition-all hover:shadow-md dark:border-emerald-800/40 dark:bg-slate-900/50">
                        <div>
                            <p class="font-bold text-slate-900 dark:text-slate-50">{{ $invoice['number'] }}</p>
                            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                                <span class="{{ $invoice['status'] === 'paid' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">{{ strtoupper($invoice['status']) }}</span>
                                <span class="mx-1">•</span>
                                {{ $invoice['issued_at'] ?? '-' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 dark:text-slate-50 mr-2">{{ $idNumber($invoice['total']) }}</span>
                            <x-actions.icon-button href="{{ $invoice['print_url'] }}" target="_blank" variant="primary" label="{{ __('Cetak Nota (A4)') }}">
                                <x-heroicon-s-printer class="h-4 w-4" />
                            </x-actions.icon-button>
                            <x-actions.icon-button href="{{ $invoice['thermal_print_url'] }}" target="_blank" variant="neutral" label="{{ __('Cetak Struk (Thermal)') }}">
                                <x-heroicon-s-ticket class="h-4 w-4" />
                            </x-actions.icon-button>
                            @if ($invoice['has_delivery_letter'])
                                <x-actions.icon-button href="{{ $invoice['delivery_letter_url'] }}" target="_blank" label="{{ __('Cetak SJ') }}" class="bg-white hover:bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 ">
                                    <x-heroicon-o-document-text class="h-4 w-4" />
                                </x-actions.icon-button>
                            @else
                                <x-actions.icon-button wire:click="createDeliveryLetterFromInvoice({{ $invoice['id'] }})" label="{{ __('Buat SJ') }}" class="bg-white hover:bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 ">
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

    <x-admin.panel class="mt-4 border-0 shadow-sm  bg-white dark:bg-slate-900 shadow-sm">
        <div class="px-4 py-4 ">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Retail Transaction List') }}</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('Semua histori transaksi retail POS.') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-heroicon-m-magnifying-glass class="h-4 w-4 text-slate-400" />
                        </div>
                        <input id="toko-sales-search" type="search" wire:model.live.debounce.250ms="salesSearch" placeholder="Cari transaksi..." class="min-h-9 w-64 rounded-xl border-slate-200 pl-9 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-900 dark:text-white">
                    </div>
                    @if ($canExport)
                        <x-actions.icon-button href="{{ route('admin.toko.exports.sales') }}" label="{{ __('Export CSV') }}" class="bg-white hover:bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 ">
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
                            <h4 class="mt-1 text-lg font-bold text-slate-900 dark:text-slate-50">{{ $salesInvoiceDetail['number'] }}</h4>
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
                            <x-actions.icon-button wire:click="clearSalesInvoiceDetail" label="{{ __('Tutup') }}" class="bg-white hover:bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 ">
                                <x-heroicon-m-x-mark class="h-4 w-4" />
                            </x-actions.icon-button>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 grid-cols-2 md:grid-cols-4">
                        <div class="rounded-lg border border-white/60 bg-white/60 px-4 py-3.5 shadow-sm dark:bg-slate-900/40">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Payment') }}</p>
                            <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $salesInvoiceDetail['payment_summary'] }}</p>
                        </div>
                        <div class="rounded-lg border border-white/60 bg-white/60 px-4 py-3.5 shadow-sm dark:bg-slate-900/40">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Due Date') }}</p>
                            <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $salesInvoiceDetail['due_at'] ?? '-' }}</p>
                        </div>
                        <div class="rounded-lg border border-white/60 bg-white/60 px-4 py-3.5 shadow-sm dark:bg-slate-900/40">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Cancel Note') }}</p>
                            <p class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $salesInvoiceDetail['cancel_reason'] ?: '-' }}</p>
                        </div>
                        <div class="rounded-lg border border-primary-200/60 bg-primary-100/50 px-4 py-3.5 text-right shadow-sm dark:border-primary-800/60 dark:bg-primary-900/40">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400">{{ __('Total') }}</p>
                            <p class="mt-0.5 text-base font-black text-slate-900 dark:text-slate-50">{{ $idNumber($salesInvoiceDetail['total']) }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl shadow-sm mt-4 rounded-xl bg-white shadow-sm dark:bg-slate-950">
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
        
        <div class="overflow-x-auto rounded-2xl shadow-sm mt-4">
            <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800/80">
                <thead class="bg-slate-50/50 dark:bg-slate-900/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">
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
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500 dark:text-slate-400 font-medium">{{ $sale['issued_at'] ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 w-fit">{{ $sale['status'] }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $sale['payment_status'] === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }} w-fit">{{ $sale['payment_status'] }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-bold text-slate-900 dark:text-slate-50">{{ $idNumber($sale['total']) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <x-actions.icon-button wire:click="viewSalesInvoiceDetail({{ $sale['id'] }})" label="{{ __('View') }}" class="bg-white hover:bg-slate-50 text-slate-500 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 ">
                                    <x-heroicon-m-eye class="h-4 w-4" />
                                </x-actions.icon-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No sales found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="px-4 py-3 bg-slate-50/30 dark:bg-slate-900/30">
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-medium">
                    <span>{{ __('Page :page of :pages', ['page' => $salesTableMeta['page'], 'pages' => $salesTableMeta['pages']]) }}</span>
                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="prevSalesPage" @disabled($salesTableMeta['page'] <= 1) class="inline-flex min-h-8 items-center justify-center rounded-lg px-3 font-semibold text-slate-700 hover:bg-white disabled:opacity-50 dark:text-slate-300 dark:hover:bg-slate-800">Prev</button>
                        <button type="button" wire:click="nextSalesPage" @disabled($salesTableMeta['page'] >= $salesTableMeta['pages']) class="inline-flex min-h-8 items-center justify-center rounded-lg px-3 font-semibold text-slate-700 hover:bg-white disabled:opacity-50 dark:text-slate-300 dark:hover:bg-slate-800">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </x-admin.panel>
    @endif