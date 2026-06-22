    @if ($activePage === 'purchases')
    <x-admin.panel class="border-0 shadow-sm  bg-white dark:bg-slate-900">
        <div class="flex flex-col gap-2 px-4 py-3.5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Purchase Receiving') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Create vendor bill and post stock-in from received items.') }}</p>
            </div>

            <div class="rounded-xl bg-slate-100 px-4 py-3.5 text-sm font-semibold text-slate-900 dark:bg-slate-900 dark:text-slate-100">
                {{ __('Total') }}: {{ $idNumber($purchaseCartTotal) }}
            </div>
        </div>

        <div class="flex flex-wrap gap-2 px-4 py-3.5 ">
            <a href="#toko-purchase-create" class="inline-flex min-h-9 items-center gap-2 rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">
                <x-heroicon-o-plus-circle class="h-5 w-5" />
                <span>{{ __('Buat Pembelian') }}</span>
            </a>
            <a href="#toko-purchase-transactions" class="inline-flex min-h-9 items-center gap-2 rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">
                <x-heroicon-o-clipboard-document-list class="h-5 w-5" />
                <span>{{ __('Data Transaksi') }}</span>
            </a>
            <a href="#toko-purchase-ap" class="inline-flex min-h-9 items-center gap-2 rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">
                <x-heroicon-o-banknotes class="h-5 w-5" />
                <span>{{ __('Hutang') }}</span>
            </a>
            <a href="#toko-purchase-recap" class="inline-flex min-h-9 items-center gap-2 rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">
                <x-heroicon-o-chart-bar-square class="h-5 w-5" />
                <span>{{ __('Rekap Pembelian') }}</span>
            </a>
            <a href="{{ route('admin.toko.exports.purchases') }}" target="_blank" class="inline-flex min-h-9 items-center gap-2 rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">
                <x-heroicon-o-arrow-up-tray class="h-5 w-5" />
                <span>{{ __('Export Data') }}</span>
            </a>
        </div>

        <div id="toko-purchase-create" class="grid scroll-mt-24 gap-2 px-4 py-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_8rem_10rem_auto]">
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
                placeholder="{{ __('Qty') }}"
                class="min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white"
            >

            <div x-data="{ 
                display: '',
                format(val) {
                    let num = String(val).replace(/\D/g, '');
                    return num ? Number(num).toLocaleString('id-ID') : '';
                }
            }" x-init="display = format($wire.purchaseUnitCost); $watch('$wire.purchaseUnitCost', val => display = format(val))">
                <input type="hidden" wire:model="purchaseUnitCost" x-ref="hiddenVal">
                <input
                    type="text"
                    inputmode="numeric"
                    placeholder="{{ __('Cost') }}"
                    :value="display"
                    @input="display = format($event.target.value); $refs.hiddenVal.value = String($event.target.value).replace(/\D/g, ''); $refs.hiddenVal.dispatchEvent(new Event('input', { bubbles: true }))"
                    class="min-h-9 w-full rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white"
                >
            </div>

            <button type="button" wire:click="addToPurchaseCart" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900">
                <x-heroicon-m-plus class="h-5 w-5" />
                <span>{{ __('Add') }}</span>
            </button>
        </div>

        <div class="grid gap-2 px-4 py-4 lg:grid-cols-[10rem_minmax(0,1fr)_10rem_minmax(0,1fr)]">
            <input
                type="date"
                wire:model="purchaseDueAt"
                aria-label="{{ __('Due Date') }}"
                class="min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white"
            >
            <input
                type="text"
                wire:model="purchasePoNumber"
                placeholder="{{ __('PO / Faktur') }}"
                class="min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white"
            >
            <div x-data="{ 
                display: '',
                format(val) {
                    let num = String(val).replace(/\D/g, '');
                    return num ? Number(num).toLocaleString('id-ID') : '';
                }
            }" x-init="display = format($wire.purchaseExtraCost); $watch('$wire.purchaseExtraCost', val => display = format(val))">
                <input type="hidden" wire:model="purchaseExtraCost" x-ref="hiddenVal">
                <input
                    type="text"
                    inputmode="numeric"
                    placeholder="{{ __('Biaya lain') }}"
                    :value="display"
                    @input="display = format($event.target.value); $refs.hiddenVal.value = String($event.target.value).replace(/\D/g, ''); $refs.hiddenVal.dispatchEvent(new Event('input', { bubbles: true }))"
                    class="min-h-9 w-full rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white"
                >
            </div>
            <input
                type="text"
                wire:model="purchaseReceiverName"
                placeholder="{{ __('Penerima') }}"
                class="min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white"
            >
            <textarea
                wire:model="purchaseNotes"
                rows="2"
                placeholder="{{ __('Keterangan pembelian') }}"
                class="min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white lg:col-span-4"
            ></textarea>
        </div>

        <div class="grid gap-4 px-4 py-4 lg:grid-cols-[minmax(0,1fr)_auto]">
            <div class="overflow-x-auto rounded-2xl shadow-sm mt-4">
                <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                    <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                            <th scope="col" class="px-4 py-3.5 text-left">{{ __('Item') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-right">{{ __('Qty') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-right">{{ __('Cost') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-right">{{ __('Line') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-right">{{ __('Remove') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($purchaseCart as $index => $item)
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <td class="px-4 py-3.5 text-slate-900 dark:text-slate-100">{{ $item['name'] }}</td>
                                <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['quantity'], 3) }}</td>
                                <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['unit_cost']) }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($item['line_total']) }}</td>
                                <td class="px-4 py-3.5 text-right">
	                                    <x-actions.icon-button wire:click="removePurchaseCartItem({{ $index }})" variant="danger" label="{{ __('Remove') }}">
                                            <x-heroicon-m-trash class="h-5 w-5" />
                                        </x-actions.icon-button>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('Purchase cart is empty.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex items-start">
                <button type="button" wire:click="createPurchase" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-4 text-sm font-semibold text-white">
                    <x-heroicon-m-check class="h-5 w-5" />
                    <span>{{ __('Create Purchase') }}</span>
                </button>
            </div>
        </div>

        <div id="toko-purchase-ap" class="scroll-mt-24 px-4 py-4 ">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('AP Aging') }}</h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Open vendor bills grouped by due date.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ $purchaseApAging['total']['label'] }}</p>
                    <p class="text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber($purchaseApAging['total']['total']) }}</p>
                </div>
            </div>
            <div class="mt-3 grid gap-2 md:grid-cols-3">
                @foreach (['overdue', 'due_soon', 'not_yet_due'] as $agingBucket)
                    <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 dark:bg-slate-950">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ $purchaseApAging[$agingBucket]['label'] }}</p>
                                <p class="mt-1 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber($purchaseApAging[$agingBucket]['total']) }}</p>
                            </div>
                            <span class="rounded-xl bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                {{ $idNumber($purchaseApAging[$agingBucket]['count']) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="px-4 py-4 ">
            <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Pay Vendor Bill') }}</h3>
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
                <div x-data="{ 
                    display: '',
                    format(val) {
                        let num = String(val).replace(/\D/g, '');
                        return num ? Number(num).toLocaleString('id-ID') : '';
                    }
                }" x-init="display = format($wire.vendorBillPaymentAmount); $watch('$wire.vendorBillPaymentAmount', val => display = format(val))">
                    <input type="hidden" wire:model="vendorBillPaymentAmount" x-ref="hiddenVal">
                    <input type="text" inputmode="numeric" placeholder="{{ __('Amount') }}" :value="display" @input="display = format($event.target.value); $refs.hiddenVal.value = String($event.target.value).replace(/\D/g, ''); $refs.hiddenVal.dispatchEvent(new Event('input', { bubbles: true }))" class="min-h-9 w-full rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                </div>
                <button type="button" wire:click="payVendorBill" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-4 text-sm font-semibold text-white">
                    <x-heroicon-o-banknotes class="h-5 w-5" />
                    <span>{{ __('Pay Bill') }}</span>
                </button>
            </div>
        </div>

        <div class="px-4 py-4 ">
            <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Vendor Payment History') }}</h3>
            <div class="mt-3 overflow-x-auto rounded-2xl shadow-sm mt-4">
                <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                    <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                            <th scope="col" class="px-4 py-3.5 text-left">{{ __('Bill') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-left">{{ __('Vendor') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-left">{{ __('Paid At') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-left">{{ __('Journal') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-right">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($vendorPaymentHistoryRows as $payment)
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <td class="px-4 py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $payment['bill_number'] }}</td>
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $payment['vendor'] }}</td>
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $payment['paid_at'] ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $payment['journal_number'] }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($payment['amount']) }}</td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No vendor payments yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-4 py-4 ">
            <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Cancel Purchase') }}</h3>
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
                <textarea wire:model="cancelPurchaseReason" rows="1" placeholder="{{ __('Reason') }}" class="min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white"></textarea>
                <button type="button" @click="window.PasPapanAlert.confirm('{{ __('Are you sure you want to cancel this purchase?') }}', () => $wire.cancelPurchase())" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                    <x-heroicon-m-x-mark class="h-5 w-5" />
                    <span>{{ __('Cancel') }}</span>
                </button>
            </div>
        </div>

        <div id="toko-purchase-recap" class="scroll-mt-24 px-4 py-4 ">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Rekap Pembelian') }}</h3>
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
                <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 dark:bg-slate-950">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Total Transaksi') }}</p>
                    <p class="mt-1 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber($purchaseTableMeta['total']) }}</p>
                </div>
                <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 dark:bg-slate-950">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Total Hutang Terbuka') }}</p>
                    <p class="mt-1 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber($purchaseApAging['total']['total']) }}</p>
                </div>
                <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 dark:bg-slate-950">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Pembayaran Terekam') }}</p>
                    <p class="mt-1 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber(count($vendorPaymentHistoryRows)) }}</p>
                </div>
            </div>
        </div>

        <div id="toko-purchase-transactions" class="scroll-mt-24 px-4 py-4 ">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Purchase List') }}</h3>
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
                    <span class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-4 py-3 text-slate-700 dark:text-slate-200">10</span>
                    <span class="text-slate-600 dark:text-slate-300">entries</span>
                </div>
                <div class="flex items-center gap-2">
                    <label for="toko-purchase-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                    <input id="toko-purchase-search" type="search" wire:model.live.debounce.250ms="purchaseSearch" class="min-h-9 w-64 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                </div>
            </div>
            @if ($purchaseBillDetail)
                <div class="mt-3 rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-slate-50 p-2 dark:bg-slate-900/60">
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Purchase Detail') }}</p>
                            <h4 class="mt-1 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $purchaseBillDetail['number'] }}</h4>
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
                        <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 dark:bg-slate-950">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('PO / Faktur') }}</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $purchaseBillDetail['po_number'] ?: '-' }}</p>
                        </div>
                        <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 dark:bg-slate-950">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Penerima') }}</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $purchaseBillDetail['receiver_name'] ?: '-' }}</p>
                        </div>
                        <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 dark:bg-slate-950">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Due Date') }}</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $purchaseBillDetail['due_at'] ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 dark:bg-slate-950">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Paid At') }}</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $purchaseBillDetail['paid_at'] ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 dark:bg-slate-950">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Biaya lain') }}</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($purchaseBillDetail['extra_cost']) }}</p>
                        </div>
                        <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 text-right dark:bg-slate-950">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Total') }}</p>
                            <p class="mt-1 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ $idNumber($purchaseBillDetail['total']) }}</p>
                        </div>
                    </div>
                    <div class="mt-2 grid gap-2 text-sm md:grid-cols-2">
                        <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 dark:bg-slate-950">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Notes') }}</p>
                            <p class="mt-1 text-slate-700 dark:text-slate-200">{{ $purchaseBillDetail['notes'] ?: '-' }}</p>
                        </div>
                        <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 dark:bg-slate-950">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Cancel Reason') }}</p>
                            <p class="mt-1 text-slate-700 dark:text-slate-200">{{ $purchaseBillDetail['cancel_reason'] ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="mt-3 overflow-x-auto rounded-2xl shadow-sm mt-4">
                        <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                            <thead class="bg-white text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <th class="px-4 py-3.5 text-left">{{ __('Item') }}</th>
                                    <th class="px-4 py-3.5 text-right">{{ __('Qty') }}</th>
                                    <th class="px-4 py-3.5 text-right">{{ __('Cost') }}</th>
                                    <th class="px-4 py-3.5 text-right">{{ __('Line') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach ($purchaseBillDetail['items'] as $item)
                                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                        <td class="px-4 py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $item['description'] }}</td>
                                        <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['quantity'], 3) }}</td>
                                        <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['unit_cost']) }}</td>
                                        <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($item['line_total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
            <div class="mt-3 overflow-x-auto rounded-2xl shadow-sm mt-4">
                <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                    <thead class="bg-slate-50 dark:bg-slate-900">
                        <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Bill') }}</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Vendor') }}</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Status') }}</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Note') }}</th>
                            <th class="px-4 py-3.5 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Total') }}</th>
                            <th class="px-4 py-3.5 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($purchaseBillRows as $bill)
                            <tr wire:key="toko-purchase-row-{{ $bill['id'] }}">
                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $bill['number'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $bill['issued_at'] ?? '-' }}</p>
	                                    <x-actions.icon-button href="{{ $bill['print_url'] }}" target="_blank" label="{{ __('Print') }}">
                                            <x-heroicon-o-printer class="h-5 w-5" />
                                        </x-actions.icon-button>
                                </td>
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $bill['vendor'] }}</td>
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $bill['status'] }}</td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $bill['cancel_reason'] ?: '-' }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($bill['total']) }}</td>
                                <td class="px-4 py-3.5 text-right">
                                    <x-actions.icon-button wire:click="viewPurchaseBillDetail({{ $bill['id'] }})" label="{{ __('Detail') }}">
                                        <x-heroicon-o-eye class="h-5 w-5" />
                                    </x-actions.icon-button>
                                </td>
                            </tr>
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <td colspan="6" class="bg-slate-50 px-4 py-3.5 dark:bg-slate-900">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Line Items') }}</p>
                                    <div class="mt-2 grid gap-2 lg:grid-cols-2">
                                        @foreach ($bill['items'] as $item)
                                            <div class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 bg-white px-4 py-3.5 text-xs dark:bg-slate-950">
                                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $item['description'] }}</p>
                                                <p class="mt-1 text-slate-600 dark:text-slate-300">{{ $idNumber($item['quantity'], 3) }} x {{ $idNumber($item['unit_cost']) }} = {{ $idNumber($item['line_total']) }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <td colspan="6" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No purchases yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 flex flex-col gap-2 pt-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($purchaseTableMeta['start']) }} to {{ $idNumber($purchaseTableMeta['end']) }} of {{ $idNumber($purchaseTableMeta['total']) }} purchase entries</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="button" wire:click="previousPurchasePage" @disabled($purchaseTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                    @php
                        $purchasePageStart = max(1, $purchaseTableMeta['page'] - 2);
                        $purchasePageEnd = min($purchaseTableMeta['pages'], $purchasePageStart + 4);
                        $purchasePageStart = max(1, $purchasePageEnd - 4);
                    @endphp
                    @if ($purchasePageStart > 1)
                        <button type="button" wire:click="gotoPurchasePage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                    @endif
                    @for ($pageNumber = $purchasePageStart; $pageNumber <= $purchasePageEnd; $pageNumber++)
                        <button
                            type="button"
                            wire:key="toko-purchase-page-{{ $pageNumber }}"
                            wire:click="gotoPurchasePage({{ $pageNumber }})"
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $purchaseTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                        >
                            {{ $idNumber($pageNumber) }}
                        </button>
                    @endfor
                    @if ($purchasePageEnd < $purchaseTableMeta['pages'])
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                        <button type="button" wire:click="gotoPurchasePage({{ $purchaseTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($purchaseTableMeta['pages']) }}</button>
                    @endif
                    <button type="button" wire:click="nextPurchasePage" @disabled($purchaseTableMeta['page'] >= $purchaseTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                </div>
            </div>
        </div>
    </x-admin.panel>
    @endif