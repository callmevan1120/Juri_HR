<?php
$file = '/Users/lutuk/Project/learning/absensi-gps-barcode/resources/views/livewire/admin/toko-pos-addon.blade.php';
$content = file_get_contents($file);

$startMarker = "    <x-admin.panel class=\"mt-4\">\n        @if (\$showPosBackOffice)";
$endMarker = "    @if (\$activePage === 'delivery-letters')";

$posStart = strpos($content, $startMarker);
$posEnd = strpos($content, $endMarker, $posStart);

if ($posStart !== false && $posEnd !== false) {
    $newBottom = <<<'HTML'
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
                            <x-actions.icon-button href="{{ $invoice['print_url'] }}" target="_blank" variant="primary" label="{{ __('Cetak Nota') }}">
                                <x-heroicon-s-printer class="h-4 w-4" />
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
                                            <span class="font-medium">{{ $item['name'] }}</span>
                                            <span class="ml-2 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-500 dark:bg-slate-800">{{ $item['sku'] ?? '-' }}</span>
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
                    @forelse ($salesTable as $sale)
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
                                <x-actions.icon-button wire:click="loadSalesInvoiceDetail({{ $sale['id'] }})" label="{{ __('View') }}" class="bg-white hover:bg-slate-50 text-slate-500">
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

HTML;

    $content = substr_replace($content, $newBottom . "\n", $posStart, $posEnd - $posStart);
    file_put_contents($file, $content);
    echo "Bottom area replaced.\n";
} else {
    echo "Markers not found.\n";
}
