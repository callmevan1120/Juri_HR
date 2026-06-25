    @if ($activePage === 'quotations')
    <x-admin.panel class="border-0 shadow-sm  bg-white dark:bg-slate-900">
        <div class="flex flex-col gap-2 px-4 py-3.5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Quotation Desk') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Create offers, print quotation, then convert accepted offers to invoice.') }}</p>
            </div>

            <div class="rounded-xl bg-slate-100 px-4 py-3.5 text-sm font-semibold text-slate-900 dark:bg-slate-900 dark:text-slate-100">
                {{ __('Total') }}: {{ $idNumber($quotationCartTotal) }}
            </div>
        </div>

        <div class="grid gap-2 px-4 py-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_8rem_10rem_auto]">
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
                placeholder="{{ __('Qty') }}"
                class="min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white"
            >

            <div x-data="{ 
                display: '',
                format(val) {
                    let num = String(val).replace(/\D/g, '');
                    return num ? Number(num).toLocaleString('id-ID') : '';
                }
            }" x-init="display = format($wire.quotationUnitPrice); $watch('$wire.quotationUnitPrice', val => display = format(val))">
                <input type="hidden" wire:model="quotationUnitPrice" x-ref="hiddenVal">
                <input
                    type="text"
                    inputmode="numeric"
                    placeholder="{{ __('Price') }}"
                    :value="display"
                    @input="display = format($event.target.value); $refs.hiddenVal.value = String($event.target.value).replace(/\D/g, ''); $refs.hiddenVal.dispatchEvent(new Event('input', { bubbles: true }))"
                    class="min-h-9 w-full rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white"
                >
            </div>

            <button type="button" wire:click="addToQuotationCart" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900">
                <x-heroicon-m-plus class="h-5 w-5" />
                <span>{{ __('Add') }}</span>
            </button>
        </div>

        <div class="grid gap-4 px-4 py-4 lg:grid-cols-[minmax(0,1fr)_auto]">
            <div class="overflow-x-auto rounded-2xl shadow-sm mt-4">
                <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                    <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                            <th scope="col" class="px-4 py-3.5 text-left">{{ __('Item') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-right">{{ __('Qty') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-right">{{ __('Price') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-right">{{ __('Line') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-right">{{ __('Remove') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($quotationCart as $index => $item)
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <td class="px-4 py-3.5 text-slate-900 dark:text-slate-100">{{ $item['name'] }}</td>
                                <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['quantity'], 3) }}</td>
                                <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-200">{{ $idNumber($item['unit_price']) }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($item['line_total']) }}</td>
                                <td class="px-4 py-3.5 text-right">
	                                    <x-actions.icon-button wire:click="removeQuotationCartItem({{ $index }})" variant="danger" label="{{ __('Remove') }}">
                                            <x-heroicon-m-trash class="h-5 w-5" />
                                        </x-actions.icon-button>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('Quotation cart is empty.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex items-start">
                <button type="button" wire:click="createQuotation" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all px-4 text-sm font-semibold text-white">
                    <x-heroicon-m-check class="h-5 w-5" />
                    <span>{{ __('Create Quotation') }}</span>
                </button>
            </div>
        </div>

        <div class="px-4 py-4 ">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Data Penawaran') }}</h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Recent Quotations') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <label for="toko-quotation-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                    <input id="toko-quotation-search" type="search" wire:model.live.debounce.250ms="quotationSearch" class="min-h-9 w-64 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                </div>
            </div>

            <div class="mt-3 overflow-x-auto rounded-2xl shadow-sm mt-4">
                <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                    <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                            <th scope="col" class="px-4 py-3.5 text-left">{{ __('Number') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-left">{{ __('Customer') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-left">{{ __('Issued') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-left">{{ __('Valid Until') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-left">{{ __('Status') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-right">{{ __('Total') }}</th>
                            <th scope="col" class="px-4 py-3.5 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($quotationRows as $quotation)
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <td class="px-4 py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $quotation['number'] }}</td>
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $quotation['customer'] }}</td>
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $quotation['issued_at'] ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $quotation['valid_until'] ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $quotation['status'] === \App\Models\Quotation::STATUS_ACCEPTED ? __('Final') : $quotation['status'] }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($quotation['total']) }}</td>
                                <td class="px-4 py-3.5">
                                    <div x-data="{ openOptions: false }" class="relative inline-block text-left">
                                        <x-actions.icon-button @click.stop="openOptions = !openOptions" @click.away="openOptions = false" label="Options">
                                            <x-heroicon-m-ellipsis-vertical class="h-5 w-5" />
                                        </x-actions.icon-button>

                                        <div x-show="openOptions"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             style="display: none;"
                                             class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-slate-900/5 focus:outline-none dark:bg-slate-800 dark:ring-slate-700 overflow-hidden"
                                        >
                                            <div class="py-1">
                                                <a href="{{ $quotation['print_url'] }}" target="_blank" class="group flex w-full items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white transition-colors">
                                                    <x-heroicon-o-printer class="mr-3 h-4 w-4 shrink-0 text-slate-400 group-hover:text-slate-600 dark:text-slate-400 dark:group-hover:text-slate-200" />
                                                    Print
                                                </a>
                                                @if (! $quotation['converted'] && ! $quotation['rejected'])
                                                    <button type="button" @click.stop="openOptions = false; $wire.markQuotationAccepted({{ $quotation['id'] }})" class="group flex w-full items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white transition-colors">
                                                        <x-heroicon-m-check class="mr-3 h-4 w-4 shrink-0 text-slate-400 group-hover:text-slate-600 dark:text-slate-400 dark:group-hover:text-slate-200" />
                                                        {{ __('Final') }}
                                                    </button>
                                                    <button type="button" @click.stop="openOptions = false; $wire.convertQuotationToInvoice({{ $quotation['id'] }})" class="group flex w-full items-center px-4 py-2 text-sm text-primary-600 hover:bg-primary-50 hover:text-primary-700 dark:text-primary-400 dark:hover:bg-primary-900/30 dark:hover:text-primary-300 transition-colors">
                                                        <x-heroicon-o-document-text class="mr-3 h-4 w-4 shrink-0 text-primary-500 group-hover:text-primary-600 dark:text-primary-400 dark:group-hover:text-primary-300" />
                                                        Create Invoice
                                                    </button>
                                                    <button type="button" @click.stop="openOptions = false; window.PasPapanAlert.confirm('Reject Quotation', 'Reject this quotation?', 'warning', 'Reject').then((res) => { if(res.isConfirmed) $wire.markQuotationRejected({{ $quotation['id'] }}); })" class="group flex w-full items-center px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:text-rose-400 dark:hover:bg-rose-900/30 dark:hover:text-rose-300 transition-colors">
                                                        <x-heroicon-m-x-mark class="mr-3 h-4 w-4 shrink-0 text-rose-500 group-hover:text-rose-600 dark:text-rose-400 dark:group-hover:text-rose-300" />
                                                        Reject
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No quotations yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($quotationTableMeta['start']) }} to {{ $idNumber($quotationTableMeta['end']) }} of {{ $idNumber($quotationTableMeta['total']) }} quotation entries</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="previousQuotationPage" @disabled($quotationTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                    @php
                        $quotationPageStart = max(1, $quotationTableMeta['page'] - 2);
                        $quotationPageEnd = min($quotationTableMeta['pages'], $quotationPageStart + 4);
                        $quotationPageStart = max(1, $quotationPageEnd - 4);
                    @endphp
                    @if ($quotationPageStart > 1)
                        <button type="button" wire:click="gotoQuotationPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                    @endif
                    @for ($pageNumber = $quotationPageStart; $pageNumber <= $quotationPageEnd; $pageNumber++)
                        <button
                            type="button"
                            wire:click="gotoQuotationPage({{ $pageNumber }})"
                            @class([
                                'inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl border px-3 text-xs font-semibold',
                                'border-primary-600 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' => $quotationTableMeta['page'] === $pageNumber,
                                'border-slate-200/60/80 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900' => $quotationTableMeta['page'] !== $pageNumber,
                            ])
                        >{{ $pageNumber }}</button>
                    @endfor
                    @if ($quotationPageEnd < $quotationTableMeta['pages'])
                        <button type="button" wire:click="gotoQuotationPage({{ $quotationTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($quotationTableMeta['pages']) }}</button>
                    @endif
                    <button type="button" wire:click="nextQuotationPage" @disabled($quotationTableMeta['page'] >= $quotationTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                </div>
            </div>
        </div>
    </x-admin.panel>
    @endif
