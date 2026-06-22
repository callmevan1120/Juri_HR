@if ($activePage === 'customers')
        <x-admin.panel class="border-0 shadow-sm  bg-white dark:bg-slate-900" x-data="{ showForm: false }" x-init="$watch('$wire.editingCustomerId', value => { if(value) showForm = true })">
            <div class="flex flex-col gap-2 px-4 py-3.5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Customers') }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Customer code, contact, address, status, and AR-ready profile data.') }}</p>
                </div>
                <div class="flex gap-2">
                    <x-actions.icon-button @click="showForm = true; $wire.resetTokoCustomerForm()" variant="success" label="Tambah Customer">
                        <x-heroicon-m-plus class="h-5 w-5" />
                    </x-actions.icon-button>
                </div>
            </div>

            <!-- Modal Form -->
            <div x-show="showForm" style="display: none" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                <div @click.away="showForm = false; $wire.resetTokoCustomerForm()" class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $editingCustomerId ? __('Update Customer') : __('Tambah Customer') }}</h3>
                        <button @click="showForm = false; $wire.resetTokoCustomerForm()" class="text-slate-400 hover:text-slate-500">
                            <x-heroicon-m-x-mark class="h-6 w-6" />
                        </button>
                    </div>
                    <div class="grid gap-4 px-6 py-4 lg:grid-cols-2 max-h-[70vh] overflow-y-auto">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Code') }}</label>
                            <input type="text" wire:model="customerCode" placeholder="{{ __('Code') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Customer name') }}</label>
                            <input type="text" wire:model="customerName" placeholder="{{ __('Customer name') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Status') }}</label>
                            <x-forms.tom-select id="toko-customer-status" wire:model="customerStatus" placeholder="{{ __('Status') }}" dropdown-direction="down">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </x-forms.tom-select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Phone') }}</label>
                            <input type="text" wire:model="customerPhone" placeholder="{{ __('Phone') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Email') }}</label>
                            <input type="email" wire:model="customerEmail" placeholder="{{ __('Email') }}" class="w-full min-h-9 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Address') }}</label>
                            <textarea wire:model="customerAddress" placeholder="{{ __('Address') }}" class="w-full min-h-20 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/50">
                        <button type="button" @click="showForm = false; $wire.resetTokoCustomerForm()" class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                            Batal
                        </button>
                        <button type="button" @click="showForm = false; $wire.saveTokoCustomer()" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 px-4 text-sm font-semibold text-white shadow-md transition-all hover:from-primary-500 hover:to-primary-400 hover:shadow-lg">
                            <x-heroicon-m-check class="h-5 w-5" />
                            <span>{{ $editingCustomerId ? __('Update') : __('Simpan') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2 px-4 py-3.5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-slate-600 dark:text-slate-300">Show</span>
                    <span class="rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-4 py-3 text-slate-700 dark:text-slate-200">10</span>
                    <span class="text-slate-600 dark:text-slate-300">entries</span>
                </div>
                <div class="flex items-center gap-2">
                    <label for="toko-customer-search" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Search</label>
                    <input id="toko-customer-search" type="search" wire:model.live.debounce.250ms="customerSearch" class="min-h-9 w-64 rounded-xl border-slate-200 bg-white text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-950 dark:text-white">
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl shadow-sm mt-4">
                <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                    <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                            <th scope="col" class="px-4 py-3 text-left">{{ __('Customer') }}</th>
                            <th scope="col" class="px-4 py-3 text-left">{{ __('Contact') }}</th>
                            <th scope="col" class="px-4 py-3 text-left">{{ __('Address') }}</th>
                            <th scope="col" class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($customerRows as $customer)
                            <tr wire:key="toko-customer-row-{{ $customer['id'] }}">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $customer['name'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $customer['code'] ?? '-' }} · {{ $customer['status'] }}</p>
                                    <span class="mt-1 inline-flex rounded-xl bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200">{{ $customer['membership_status'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $customer['phone'] ?: '-' }}<br>{{ $customer['email'] ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $customer['address'] ?: '-' }}</td>
                                <td class="px-4 py-3 text-right">
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
                                             class="absolute right-0 z-50 mt-2 w-40 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-slate-900/5 focus:outline-none dark:bg-slate-800 dark:ring-slate-700 overflow-hidden"
                                        >
                                            <div class="py-1">
                                                <button type="button" @click.stop="openOptions = false; $wire.editTokoCustomer({{ $customer['id'] }})" class="group flex w-full items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white transition-colors">
                                                    <x-heroicon-m-pencil-square class="mr-3 h-4 w-4 shrink-0 text-slate-400 group-hover:text-slate-600 dark:text-slate-400 dark:group-hover:text-slate-200" />
                                                    {{ __('Edit') }}
                                                </button>
                                                <button type="button" @click.stop="openOptions = false; $wire.convertTokoCustomer({{ $customer['id'] }})" class="group flex w-full items-center px-4 py-2 text-sm text-amber-700 hover:bg-amber-100 hover:text-amber-900 dark:text-amber-400 dark:hover:bg-amber-900/30 dark:hover:text-amber-300 transition-colors">
                                                    <x-heroicon-o-arrow-path-rounded-square class="mr-3 h-4 w-4 shrink-0 text-amber-500 group-hover:text-amber-600 dark:text-amber-500 dark:group-hover:text-amber-400" />
                                                    {{ __('Convert') }}
                                                </button>
                                                <button type="button" @click.stop="openOptions = false; window.PasPapanAlert.confirm('{{ __('Deactivate this customer?') }}', () => $wire.deactivateTokoCustomer({{ $customer['id'] }}))" class="group flex w-full items-center px-4 py-2 text-sm text-rose-700 hover:bg-rose-100 hover:text-rose-900 dark:text-rose-400 dark:hover:bg-rose-900/30 dark:hover:text-rose-300 transition-colors">
                                                    <x-heroicon-m-trash class="mr-3 h-4 w-4 shrink-0 text-rose-500 group-hover:text-rose-600 dark:text-rose-500 dark:group-hover:text-rose-400" />
                                                    {{ __('Deactivate') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300"><td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400 font-medium">{{ __('No customers yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-2 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-300">Showing {{ $idNumber($customerTableMeta['start']) }} to {{ $idNumber($customerTableMeta['end']) }} of {{ $idNumber($customerTableMeta['total']) }} customer entries</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="button" wire:click="previousCustomerPage" @disabled($customerTableMeta['page'] <= 1) class="inline-flex min-h-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Previous</button>
                    @php
                        $customerPageStart = max(1, $customerTableMeta['page'] - 2);
                        $customerPageEnd = min($customerTableMeta['pages'], $customerPageStart + 4);
                        $customerPageStart = max(1, $customerPageEnd - 4);
                    @endphp
                    @if ($customerPageStart > 1)
                        <button type="button" wire:click="gotoCustomerPage(1)" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">1</button>
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                    @endif
                    @for ($pageNumber = $customerPageStart; $pageNumber <= $customerPageEnd; $pageNumber++)
                        <button
                            type="button"
                            wire:key="toko-customer-page-{{ $pageNumber }}"
                            wire:click="gotoCustomerPage({{ $pageNumber }})"
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold {{ $customerTableMeta['page'] === $pageNumber ? 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-md hover:shadow-lg transition-all text-white' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900' }}"
                        >
                            {{ $idNumber($pageNumber) }}
                        </button>
                    @endfor
                    @if ($customerPageEnd < $customerTableMeta['pages'])
                        <span class="inline-flex min-h-9 items-center justify-center px-1 text-xs font-semibold text-slate-400">...</span>
                        <button type="button" wire:click="gotoCustomerPage({{ $customerTableMeta['pages'] }})" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900">{{ $idNumber($customerTableMeta['pages']) }}</button>
                    @endif
                    <button type="button" wire:click="nextCustomerPage" @disabled($customerTableMeta['page'] >= $customerTableMeta['pages']) class="inline-flex min-h-9 items-center justify-center rounded-2xl shadow-sm transition-all hover:shadow-md/80 px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400 disabled:opacity-60 dark:text-slate-200 dark:hover:bg-slate-900 dark:disabled:text-slate-500">Next</button>
                </div>
            </div>

            <div class="px-4 py-4 ">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Customer Income') }}</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Sales history and outstanding AR summarized by customer.') }}</p>
                    </div>
                    @if ($canExport)
                        <x-actions.icon-button href="{{ route('admin.toko.exports.customer-income') }}" label="{{ __('Export CSV') }}">
                            <x-heroicon-m-arrow-down-tray class="h-5 w-5" />
                        </x-actions.icon-button>
                    @endif
                </div>

                <div class="mt-3 overflow-x-auto rounded-2xl shadow-sm mt-4">
                    <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/50">
                        <thead class="bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                            <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                <th scope="col" class="px-4 py-3 text-left">{{ __('Customer') }}</th>
                                <th scope="col" class="px-4 py-3 text-right">{{ __('Invoices') }}</th>
                                <th scope="col" class="px-4 py-3 text-right">{{ __('Total') }}</th>
                                <th scope="col" class="px-4 py-3 text-right">{{ __('AR') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($customerIncomeRows as $row)
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300">
                                    <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">{{ $row['customer'] }}</td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($row['invoice_count']) }}</td>
                                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $idNumber($row['total']) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-slate-100">{{ $idNumber($row['ar_total']) }}</td>
                                </tr>
                            @empty
                                <tr class="group hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-sm transition-all duration-300"><td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400 font-medium">{{ __('No customer income yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-admin.panel>
    @endif