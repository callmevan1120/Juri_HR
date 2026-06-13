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
    <x-admin.panel>
        <div class="flex flex-col gap-2 px-3 py-2 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-emerald-700 dark:text-emerald-300">{{ __('Mode Kasir') }}</p>
                <h2 class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ __('Terminal POS') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Kasir fokus') }} · POS Counter Sale · Nota / Struk · {{ $targetCompany?->name ?? __('No company selected') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <x-actions.icon-button wire:click="$refresh" label="Refresh">
                    <x-heroicon-m-arrow-path class="h-5 w-5" />
                </x-actions.icon-button>
                <x-actions.icon-button wire:click="$toggle('showPosBackOffice')" variant="{{ $showPosBackOffice ? 'primary' : 'neutral' }}" label="{{ $showPosBackOffice ? __('Tutup tools admin POS') : __('Buka tools admin POS') }}" aria-expanded="{{ $showPosBackOffice ? 'true' : 'false' }}">
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5" />
                </x-actions.icon-button>
            </div>
        </div>
    </x-admin.panel>
    <x-admin.panel>
        <div class="flex flex-col gap-2 border-b border-slate-100/80 px-3 py-2 dark:border-slate-800/80 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Data Penjualan') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Scan barang, pilih produk, lalu konfirmasi pembayaran.') }}</p>
            </div>

            <div class="rounded-xl border border-rose-100 bg-gradient-to-r from-rose-50/50 to-white shadow-sm px-5 py-3 text-right shadow-sm dark:border-rose-900/60 dark:bg-slate-950">
                <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Total Pembayaran') }}</p>
                <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idMoney($salePayableTotal) }}</p>
            </div>
        </div>

        <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 xl:grid-cols-[minmax(0,1.4fr)_minmax(18rem,0.5fr)]">
            <div class="rounded-xl border border-emerald-100 shadow-sm bg-emerald-50/20 p-3 dark:border-emerald-900/60">
                <div class="grid gap-2 lg:grid-cols-[8rem_minmax(0,1fr)_4rem] lg:items-center">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200" for="toko-pos-barcode">{{ __('Scan Barcode') }}</label>
                    <input
                        id="toko-pos-barcode"
                        type="text"
                        wire:model="saleBarcode"
                        wire:keydown.enter="addScannedSaleBarcode"
                        class="min-h-9 w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
                    >
                    <span class="text-center text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Atau') }}</span>
                </div>

                <div class="mt-3 grid gap-2 lg:grid-cols-[8rem_minmax(0,1fr)] lg:items-center">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200" for="toko-pos-product">{{ __('Pilih Produk') }}</label>
                    <x-forms.tom-select
                        id="toko-pos-product"
                        wire:model.live="selectedProductId"
                        placeholder="{{ __('Search Product') }}"
                        :options="$productOptions"
                        dropdown-direction="down"
                    >
                        <option value="">{{ __('Search Product') }}</option>
                        @foreach ($productOptions as $product)
                            <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                        @endforeach
                    </x-forms.tom-select>
                </div>
            </div>

            <div class="rounded-xl border border-sky-100 shadow-sm bg-sky-50/20 p-3 dark:border-sky-900/60">
                <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200" for="toko-pos-draft-number">No Nota · Nota / Struk</label>
                <input
                    id="toko-pos-draft-number"
                    type="text"
                    value="{{ $nextSaleDraftNumber }}"
                    readonly
                    class="min-h-9 w-full rounded-xl border-slate-300 bg-slate-100 text-sm text-slate-700 shadow-sm dark:border-slate-800/80 dark:bg-slate-900 dark:text-slate-200"
                >
            </div>
        </div>

        <div class="grid gap-2 border-b border-slate-100/80 px-4 py-4 dark:border-slate-800/80 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,0.65fr)_minmax(0,0.65fr)_minmax(0,0.65fr)_minmax(0,0.75fr)]">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Nama Barang') }}</label>
                <input type="text" readonly value="{{ $saleProductPreview['name'] ?? '' }}" class="min-h-9 w-full rounded-xl border-slate-300 bg-slate-100 text-sm text-slate-700 shadow-sm dark:border-slate-800/80 dark:bg-slate-900 dark:text-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Sisa Stok') }}</label>
                <input type="text" readonly value="{{ isset($saleProductPreview) ? $idNumber($saleProductPreview['stock'], 3) : '' }}" class="min-h-9 w-full rounded-xl border-slate-300 bg-slate-100 text-sm text-slate-700 shadow-sm dark:border-slate-800/80 dark:bg-slate-900 dark:text-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Harga Satuan') }}</label>
                <input type="text" readonly value="{{ isset($saleProductPreview) ? $idNumber($saleProductPreview['unit_price']) : '' }}" class="min-h-9 w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400" for="toko-pos-qty">{{ __('Jumlah Jual') }}</label>
                <input
                    id="toko-pos-qty"
                    type="number"
                    min="0.001"
                    step="0.001"
                    wire:model.live="saleQuantity"
                    class="min-h-9 w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white"
                >
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Total Pembayaran') }}</label>
                <input type="text" readonly value="{{ isset($saleProductPreview) ? $idNumber($saleProductPreview['line_total']) : '' }}" class="min-h-9 w-full rounded-xl border-slate-300 bg-slate-100 text-sm font-semibold text-slate-900 shadow-sm dark:border-slate-800/80 dark:bg-slate-900 dark:text-white">
            </div>
            <div class="xl:col-span-5">
                <button type="button" wire:click="addToSaleCart" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    <x-heroicon-m-plus class="h-5 w-5" />
                    <span>{{ __('Tambah') }}</span>
                </button>
            </div>
        </div>

        <div class="grid gap-2 px-4 py-4 lg:grid-cols-[minmax(0,1fr)_24rem]">
            <div class="overflow-x-auto">
                <h3 class="mb-3 text-sm font-semibold text-slate-950 dark:text-white">{{ __('Daftar Transaksi') }}</h3>
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                            <th scope="col" class="px-3 py-2 text-left">No.</th>
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Kode Barang') }}</th>
                            <th scope="col" class="px-3 py-2 text-left">{{ __('Nama Barang') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Harga Satuan') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Jumlah Jual') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Total Pembayaran') }}</th>
                            <th scope="col" class="px-3 py-2 text-right">{{ __('Opsi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($saleCart as $index => $item)
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $index + 1 }}</td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $item['sku'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-slate-900 dark:text-slate-100">{{ $item['name'] }}</td>
                                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['unit_price']) }}</td>
                                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['quantity'], 3) }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($item['line_total']) }}</td>
                                <td class="px-3 py-2 text-right">
	                                    <x-actions.icon-button wire:click="removeSaleCartItem({{ $index }})" variant="danger" label="{{ __('Remove') }}">
                                            <x-heroicon-m-trash class="h-5 w-5" />
                                        </x-actions.icon-button>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Cart is empty.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 lg:sticky lg:top-4 lg:self-start">
                <div class="rounded-xl border border-slate-100/80 bg-white p-3 shadow-sm dark:border-slate-800/80 dark:bg-slate-950">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-950 dark:text-white">Tampilan Struk</h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $nextSaleDraftNumber }}</p>
                        </div>
                        <span class="rounded-xl bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200">POS</span>
                    </div>

                    <div class="mt-3 space-y-3 border-y border-dashed border-slate-300 py-3 dark:border-slate-800/80">
                        @forelse ($saleCart as $item)
                            <div class="text-xs">
                                <div class="flex justify-between gap-2">
                                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $item['name'] }}</span>
                                    <span class="text-slate-700 dark:text-slate-200">{{ $idNumber($item['line_total']) }}</span>
                                </div>
                                <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $idNumber($item['quantity'], 3) }} x {{ $idNumber($item['unit_price']) }}</p>
                            </div>
                        @empty
                            <p class="text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Cart is empty.') }}</p>
                        @endforelse
                    </div>

                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-slate-600 dark:text-slate-300">Sub total</span>
                            <span class="font-semibold text-slate-950 dark:text-white">{{ $idNumber($saleCartTotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-slate-600 dark:text-slate-300">Diskon</span>
                            <span class="font-semibold text-slate-950 dark:text-white">{{ $idNumber((float) $saleDiscountAmount) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-slate-600 dark:text-slate-300">Charge</span>
                            <span class="font-semibold text-slate-950 dark:text-white">{{ $idNumber((float) $saleAdditionalCharge) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 border-t border-slate-100/80 pt-2 dark:border-slate-800/80">
                            <span class="font-semibold text-slate-950 dark:text-white">Total Bayar</span>
                            <span class="text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($salePayableTotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-slate-600 dark:text-slate-300">Jumlah Bayar</span>
                            <span class="font-semibold text-slate-950 dark:text-white">{{ $idNumber($saleTenderTotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-slate-600 dark:text-slate-300">Uang Kembali</span>
                            <span class="font-semibold text-emerald-700 dark:text-emerald-300">{{ $idNumber($saleChangeDue) }}</span>
                        </div>
                        @if ($saleTenderLines !== [])
                            <div class="space-y-1 border-t border-slate-100/80 pt-2 text-xs dark:border-slate-800/80">
                                @foreach ($saleTenderLines as $line)
                                    <div class="flex items-center justify-between gap-2 text-slate-600 dark:text-slate-300">
                                        <span>{{ $line['method'] }}{{ $line['reference'] !== '' ? ' · '.$line['reference'] : '' }}</span>
                                        <span class="font-semibold text-slate-950 dark:text-white">{{ $idNumber((float) $line['amount']) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400" for="toko-pos-client">{{ __('Customer') }}</label>
                    <x-forms.tom-select
                        id="toko-pos-client"
                        wire:model="selectedClientId"
                        placeholder="{{ __('Walk-in') }}"
                        :options="$clientOptions"
                        dropdown-direction="down"
                    >
                        <option value="">{{ __('Walk-in') }}</option>
                        @foreach ($clientOptions as $client)
                            <option value="{{ $client['id'] }}">{{ $client['label'] }}</option>
                        @endforeach
                    </x-forms.tom-select>
                </div>

	                <div>
	                    <p class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Metode Pembayaran') }}</p>
                        <div class="flex gap-2">
                            <x-actions.icon-button wire:click="setSalePaymentMode('cash')" variant="{{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('cash') ? 'primary' : 'neutral' }}" label="{{ __('Bayar Sekarang') }}" aria-pressed="{{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('cash') ? 'true' : 'false' }}">
                                <x-heroicon-o-banknotes class="h-5 w-5" />
                            </x-actions.icon-button>
                            <x-actions.icon-button wire:click="setSalePaymentMode('transfer')" variant="{{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('transfer') ? 'primary' : 'neutral' }}" label="{{ __('Transfer Bank') }}" aria-pressed="{{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('transfer') ? 'true' : 'false' }}">
                                <x-heroicon-o-credit-card class="h-5 w-5" />
                            </x-actions.icon-button>
                            <x-actions.icon-button wire:click="setSalePaymentMode('split')" variant="{{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('split') ? 'primary' : 'neutral' }}" label="{{ __('Split Tender') }}" aria-pressed="{{ $salePaymentStatus === 'paid' && str($salePaymentMethod)->lower()->contains('split') ? 'true' : 'false' }}">
                                <x-heroicon-o-rectangle-stack class="h-5 w-5" />
                            </x-actions.icon-button>
                            <x-actions.icon-button wire:click="setSalePaymentMode('unpaid')" variant="{{ $salePaymentStatus === 'unpaid' ? 'warning' : 'neutral' }}" label="{{ __('Piutang / Tempo') }}" aria-pressed="{{ $salePaymentStatus === 'unpaid' ? 'true' : 'false' }}">
                                <x-heroicon-o-clock class="h-5 w-5" />
                            </x-actions.icon-button>
                        </div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $salePaymentStatus === 'unpaid' ? __('Piutang / Tempo') : $salePaymentMethod }}</p>
	                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400" for="toko-pos-tendered">Jumlah Bayar</label>
                    <input id="toko-pos-tendered" type="number" min="0" step="0.01" wire:model.live="saleTenderedAmount" class="min-h-9 w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                </div>

                <div class="rounded-xl border border-slate-100/80 bg-slate-50 p-2 dark:border-slate-800/80 dark:bg-slate-900/40">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Split Tender') }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Pakai jika pelanggan bayar dengan lebih dari satu metode.') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Total Split') }}</p>
                            <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ $idNumber($saleTenderTotal) }}</p>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-2 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                        <label for="toko-pos-tender-method" class="sr-only">{{ __('Split tender method') }}</label>
                        <x-forms.tom-select id="toko-pos-tender-method" wire:model="saleTenderMethod" aria-label="{{ __('Split tender method') }}" placeholder="{{ __('Split tender method') }}" dropdown-direction="down">
                            <option value="Cash">Cash</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="QRIS">QRIS</option>
                            <option value="Card">Card</option>
                        </x-forms.tom-select>
                        <input type="number" min="0.01" step="0.01" wire:model="saleTenderAmount" placeholder="{{ __('Amount') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    </div>
                    <div class="mt-2 grid gap-2 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                        <input type="text" wire:model="saleTenderBankCode" placeholder="{{ __('Bank') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <input type="text" wire:model="saleTenderReference" placeholder="{{ __('Reference') }}" class="min-h-9 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                        <button type="button" wire:click="addSaleTenderLine" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 shadow-md hover:shadow-lg transition-all px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                            <x-heroicon-m-plus class="h-5 w-5" />
                            <span>{{ __('Tambah Pembayaran') }}</span>
                        </button>
                    </div>
                    @if ($saleTenderLines !== [])
                        <div class="mt-3 divide-y divide-slate-200 overflow-hidden rounded-xl border border-slate-100/80 bg-white dark:divide-slate-700 dark:border-slate-800/80 dark:bg-slate-950">
                            @foreach ($saleTenderLines as $index => $line)
                                <div class="flex items-center justify-between gap-2 px-3 py-2 text-sm">
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $line['method'] }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $line['bank_code'] ?: '-' }}{{ $line['reference'] !== '' ? ' · '.$line['reference'] : '' }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-950 dark:text-white">{{ $idNumber((float) $line['amount']) }}</span>
                                        <x-actions.icon-button wire:click="removeSaleTenderLine({{ $index }})" variant="danger" label="{{ __('Remove tender line') }}">
                                            <x-heroicon-o-trash class="h-5 w-5" />
                                        </x-actions.icon-button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400" for="toko-pos-discount">{{ __('Discount') }}</label>
                        <input id="toko-pos-discount" type="number" min="0" step="0.01" wire:model="saleDiscountAmount" class="min-h-9 w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400" for="toko-pos-charge">{{ __('Charge') }}</label>
                        <input id="toko-pos-charge" type="number" min="0" step="0.01" wire:model="saleAdditionalCharge" class="min-h-9 w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    </div>
                </div>

                <div class="grid gap-2 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400" for="toko-pos-due-days">{{ __('Due Days') }}</label>
                        <input id="toko-pos-due-days" type="number" min="0" max="365" step="1" wire:model="saleDueDays" class="min-h-9 w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400" for="toko-pos-payment-method">{{ __('Method') }}</label>
                        <input id="toko-pos-payment-method" type="text" wire:model="salePaymentMethod" class="min-h-9 w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400" for="toko-pos-bank-code">{{ __('Bank') }}</label>
                        <input id="toko-pos-bank-code" type="text" wire:model="saleBankCode" class="min-h-9 w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    </div>
                </div>

                    <button type="button" wire:click="createCounterSale" class="inline-flex min-h-9 w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                        <x-heroicon-m-check class="h-5 w-5" />
                        <span>{{ __('Konfirmasi Pembayaran') }}</span>
                    </button>
            </div>
        </div>

        @if ($showPosBackOffice)
        <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
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

        @if ($recentPosInvoices !== [])
            <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
                <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Recent POS Invoices') }}</h3>
                <div class="mt-3 grid gap-2 lg:grid-cols-2">
                    @foreach ($recentPosInvoices as $invoice)
                        <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-100/80 px-3 py-2 text-sm dark:border-slate-800/80">
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $invoice['number'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $invoice['issued_at'] ?? '-' }} · {{ $invoice['status'] }} · {{ $idNumber($invoice['total']) }}</p>
                            </div>
                            <div class="flex gap-2">
                                <x-actions.icon-button href="{{ $invoice['print_url'] }}" target="_blank" label="{{ __('Print') }}">
                                    <x-heroicon-o-printer class="h-5 w-5" />
                                </x-actions.icon-button>
                                @if ($invoice['has_delivery_letter'])
                                    <x-actions.icon-button href="{{ $invoice['delivery_letter_url'] }}" target="_blank" label="{{ __('Surat Jalan') }}">
                                        <x-heroicon-o-document-text class="h-5 w-5" />
                                    </x-actions.icon-button>
                                @else
                                    <x-actions.icon-button wire:click="createDeliveryLetterFromInvoice({{ $invoice['id'] }})" variant="primary" label="{{ __('Surat Jalan') }}">
                                        <x-heroicon-o-document-text class="h-5 w-5" />
                                    </x-actions.icon-button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="border-t border-slate-100/80 px-4 py-4 dark:border-slate-800/80">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Retail Transaction List') }}</h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('POS retail transactions, invoice status, payment status, cancellation notes, and line items.') }}</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <label for="toko-sales-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                    <input id="toko-sales-search" type="search" wire:model.live.debounce.250ms="salesSearch" class="min-h-9 w-64 rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-800/80 dark:bg-slate-950 dark:text-white">
                    @if ($canExport)
                        <x-actions.icon-button href="{{ route('admin.toko.exports.sales') }}" label="{{ __('Export CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                        <x-actions.icon-button href="{{ route('admin.toko.exports.sales-lines') }}" label="{{ __('Export Lines') }}">
                            <x-heroicon-o-document-arrow-down class="h-5 w-5" />
                        </x-actions.icon-button>
                    @endif
                </div>
            </div>
            @if ($salesInvoiceDetail)
                <div class="mt-3 rounded-xl border border-primary-200 bg-primary-50/70 p-3 dark:border-primary-900/50 dark:bg-primary-950/20">
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase text-primary-700 dark:text-primary-200">{{ __('Transaction Detail') }}</p>
                            <h4 class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $salesInvoiceDetail['number'] }}</h4>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                {{ $salesInvoiceDetail['customer'] }} · {{ $salesInvoiceDetail['issued_at'] ?? '-' }} · {{ $salesInvoiceDetail['status'] }} / {{ $salesInvoiceDetail['payment_status'] }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-actions.icon-button href="{{ $salesInvoiceDetail['print_url'] }}" target="_blank" label="{{ __('Print') }}">
                                <x-heroicon-o-printer class="h-5 w-5" />
                            </x-actions.icon-button>
                            @if ($salesInvoiceDetail['has_delivery_letter'])
                                <x-actions.icon-button href="{{ $salesInvoiceDetail['delivery_letter_url'] }}" target="_blank" label="{{ __('Surat Jalan') }}">
                                    <x-heroicon-o-document-text class="h-5 w-5" />
                                </x-actions.icon-button>
                            @else
                                <x-actions.icon-button wire:click="createDeliveryLetterFromInvoice({{ $salesInvoiceDetail['id'] }})" variant="primary" label="{{ __('Surat Jalan') }}">
                                    <x-heroicon-o-document-text class="h-5 w-5" />
                                </x-actions.icon-button>
                            @endif
                            <x-actions.icon-button wire:click="clearSalesInvoiceDetail" label="{{ __('Close') }}">
                                <x-heroicon-m-x-mark class="h-5 w-5" />
                            </x-actions.icon-button>
                        </div>
                    </div>

                    <div class="mt-3 grid gap-2 md:grid-cols-4">
                        <div class="rounded-xl border border-slate-100/80 bg-white px-3 py-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Payment') }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $salesInvoiceDetail['payment_summary'] }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white px-3 py-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Due Date') }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $salesInvoiceDetail['due_at'] ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white px-3 py-2 dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Cancel Note') }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $salesInvoiceDetail['cancel_reason'] ?: '-' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100/80 bg-white px-3 py-2 text-right dark:border-slate-800/80 dark:bg-slate-950">
                            <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Total') }}</p>
                            <p class="mt-1 text-base font-semibold text-slate-950 dark:text-white">{{ $idNumber($salesInvoiceDetail['total']) }}</p>
                        </div>
                    </div>

                    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-100/80 dark:border-slate-800/80">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50/50 text-[11px] tracking-wider font-semibold uppercase text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                    <th class="px-3 py-2 text-left">{{ __('Item') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Qty') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Unit Price') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Line Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-950">
                                @foreach ($salesInvoiceDetail['items'] as $item)
                                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                        <td class="px-3 py-2 font-semibold text-slate-900 dark:text-slate-100">{{ $item['description'] }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($item['quantity'], 3) }}</td>
                                        <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($item['unit_price']) }}</td>
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
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Invoice') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Customer') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Status') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Payment') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Note') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Total') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($salesInvoiceRows as $invoice)
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td class="px-3 py-2">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $invoice['number'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $invoice['issued_at'] ?? '-' }}</p>
                                </td>
                                <td class="px-3 py-2 font-semibold text-slate-700 dark:text-slate-200">{{ $invoice['customer'] }}</td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $invoice['status'] }} · {{ $invoice['payment_status'] }}</td>
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ $invoice['payment_summary'] }}</td>
                                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $invoice['cancel_reason'] ?: '-' }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($invoice['total']) }}</td>
                                <td class="px-3 py-2 text-right">
                                    <x-actions.icon-button wire:click="viewSalesInvoiceDetail({{ $invoice['id'] }})" label="{{ __('Detail') }}">
                                        <x-heroicon-o-eye class="h-5 w-5" />
                                    </x-actions.icon-button>
                                    <x-actions.icon-button href="{{ $invoice['print_url'] }}" target="_blank" label="{{ __('Print') }}">
                                        <x-heroicon-o-printer class="h-5 w-5" />
                                    </x-actions.icon-button>
                                </td>
                            </tr>
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td colspan="7" class="bg-slate-50 px-3 py-2 dark:bg-slate-900/70">
                                    <p class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ __('Line Items') }}</p>
                                    <div class="mt-2 grid gap-2 lg:grid-cols-2">
                                        @foreach ($invoice['items'] as $item)
                                            <div class="rounded-xl border border-slate-100/80 bg-white px-3 py-2 text-xs dark:border-slate-800/80 dark:bg-slate-950">
                                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $item['description'] }}</p>
                                                <p class="mt-1 text-slate-600 dark:text-slate-300">{{ $idNumber($item['quantity'], 3) }} x {{ $idNumber($item['unit_price']) }} = {{ $idNumber($item['line_total']) }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors duration-200">
                                <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No sales invoices yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-2 border-t border-slate-100/80 px-1 py-3 dark:border-slate-800/80 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($salesTableMeta['start']) }} to {{ $idNumber($salesTableMeta['end']) }} of {{ $idNumber($salesTableMeta['total']) }} sales entries</p>
                <div class="flex flex-wrap items-center gap-1">
                    <button type="button" wire:click="previousSalesPage" @disabled($salesTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                    @php
                        $salesPageStart = max(1, $salesTableMeta['page'] - 2);
                        $salesPageEnd = min($salesTableMeta['pages'], $salesPageStart + 4);
                        $salesPageStart = max(1, $salesPageEnd - 4);
                    @endphp
                    @if ($salesPageStart > 1)
                        <button type="button" wire:click="gotoSalesPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border border-slate-100/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                        @if ($salesPageStart > 2)
                            <span class="px-2 text-sm text-slate-500 dark:text-slate-400">...</span>
                        @endif
                    @endif
