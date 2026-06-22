    @if ($activePage === 'delivery-letters')
    <x-admin.panel class="border-0 shadow-sm  bg-white dark:bg-slate-900">
        <div class="flex flex-col gap-2 px-4 py-3.5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Delivery Letter List') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Surat jalan list, invoice source, destination, driver, vehicle, and print action.') }}</p>
            </div>
            <x-actions.icon-button href="{{ route('admin.toko.pos') }}" variant="primary" label="{{ __('Create From POS Invoice') }}">
                <x-heroicon-m-plus class="h-5 w-5" />
            </x-actions.icon-button>
        </div>

        <div class="flex flex-col gap-2 px-4 py-3.5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300">Show</span>
                <span class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-4 py-3 text-slate-700 dark:text-slate-200">10</span>
                <span class="text-slate-600 dark:text-slate-300">entries</span>
            </div>
            <div class="flex items-center gap-2">
                <label for="toko-delivery-letter-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                <input id="toko-delivery-letter-search" type="search" wire:model.live.debounce.250ms="deliveryLetterSearch" class="min-h-9 w-64 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl shadow-sm mt-4 p-3">
            <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                <thead class="bg-slate-50 dark:bg-slate-900">
                    <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                        <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Surat Jalan') }}</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Invoice') }}</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Destination') }}</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Driver') }}</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Vehicle') }}</th>
                        <th class="px-4 py-3.5 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($deliveryLetterRows as $letter)
                        <tr wire:key="toko-delivery-letter-row-{{ $letter['id'] }}">
                            <td class="px-4 py-3.5">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $letter['number'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $letter['issued_at'] ?? '-' }} · {{ $letter['status'] }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $letter['invoice_number'] }}</td>
                            <td class="px-4 py-3.5">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $letter['destination'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $letter['customer'] }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $letter['driver_name'] }}</td>
                            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-200">{{ $letter['vehicle_number'] }}</td>
                            <td class="px-4 py-3.5 text-right">
                                <x-actions.icon-button href="{{ $letter['print_url'] }}" target="_blank" label="{{ __('Print') }}">
                                    <x-heroicon-o-printer class="h-5 w-5" />
                                </x-actions.icon-button>
                            </td>
                        </tr>
                    @empty
                        <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                            <td colspan="6" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">{{ __('No delivery letters yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col gap-2 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($deliveryLetterTableMeta['start']) }} to {{ $idNumber($deliveryLetterTableMeta['end']) }} of {{ $idNumber($deliveryLetterTableMeta['total']) }} delivery letter entries</p>
            <div class="flex flex-wrap justify-end gap-2">
                <button type="button" wire:click="previousDeliveryLetterPage" @disabled($deliveryLetterTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                @php
                    $deliveryLetterPageStart = max(1, $deliveryLetterTableMeta['page'] - 2);
                    $deliveryLetterPageEnd = min($deliveryLetterTableMeta['pages'], $deliveryLetterPageStart + 4);
                    $deliveryLetterPageStart = max(1, $deliveryLetterPageEnd - 4);
                @endphp
                @if ($deliveryLetterPageStart > 1)
                    <button type="button" wire:click="gotoDeliveryLetterPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                    <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                @endif
                @for ($pageNumber = $deliveryLetterPageStart; $pageNumber <= $deliveryLetterPageEnd; $pageNumber++)
                    <button
                        type="button"
                        wire:key="toko-delivery-letter-page-{{ $pageNumber }}"
                        wire:click="gotoDeliveryLetterPage({{ $pageNumber }})"
                        class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $deliveryLetterTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                    >
                        {{ $idNumber($pageNumber) }}
                    </button>
                @endfor
                @if ($deliveryLetterPageEnd < $deliveryLetterTableMeta['pages'])
                    <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                    <button type="button" wire:click="gotoDeliveryLetterPage({{ $deliveryLetterTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($deliveryLetterTableMeta['pages']) }}</button>
                @endif
                <button type="button" wire:click="nextDeliveryLetterPage" @disabled($deliveryLetterTableMeta['page'] >= $deliveryLetterTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
            </div>
        </div>
    </x-admin.panel>
    @endif