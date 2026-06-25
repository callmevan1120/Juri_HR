@if ($activePage === 'migration')
    <x-admin.panel class="border-0 bg-white shadow-sm dark:bg-slate-900">
        <div class="border-b border-slate-100 px-6 py-6 dark:border-slate-800">
            <h2 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Legacy Import Preview') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Import preview and cutover.') }}</p>
        </div>

        <div class="space-y-6 px-6 py-6">
            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div>
                    <x-forms.label for="toko-dump-source" value="{{ __('Legacy dump source') }}" />
                    <x-forms.tom-select id="toko-dump-source" wire:model.live="selectedDumpKey" class="mt-1 w-full" dropdown-direction="down">
                        @foreach (($dumpSources ?? []) as $source)
                            <option value="{{ $source['key'] }}">{{ $source['label'] }} - {{ $source['filename'] }}</option>
                        @endforeach
                    </x-forms.tom-select>
                </div>

                @if ($canImport)
                    <div class="flex flex-wrap gap-2">
                        <x-actions.button type="button" wire:click="importMasterData">
                            {{ __('Dry-run Master Import') }}
                        </x-actions.button>
                        <x-actions.secondary-button type="button" wire:click="importHistoricalDocuments">
                            {{ __('Import Historical Documents') }}
                        </x-actions.secondary-button>
                        @if ($canExport)
                            <x-actions.secondary-button type="button" wire:click="archiveCutoverReport">
                                {{ __('Archive Cutover Report') }}
                            </x-actions.secondary-button>
                        @endif
                    </div>
                @endif
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                @foreach (($dumpSources ?? []) as $source)
                    <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="font-semibold text-slate-900 dark:text-white">{{ $source['filename'] }}</div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $source['path'] }}</div>
                        <div class="mt-3 text-xs font-medium {{ $source['exists'] ? 'text-emerald-600 dark:text-emerald-300' : 'text-amber-600 dark:text-amber-300' }}">
                            {{ $source['exists'] ? __('Available') : __('Missing') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    __('Rows') => data_get($legacyPreview ?? [], 'totals.legacy_rows', 0),
                    __('Tables') => data_get($legacyPreview ?? [], 'totals.legacy_tables', 0),
                    __('Mapped Rows') => data_get($legacyPreview ?? [], 'totals.mapped_rows', 0),
                    __('Unmapped Tables') => data_get($legacyPreview ?? [], 'totals.unmapped_tables', 0),
                ] as $label => $value)
                    <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</div>
                        <div class="mt-1 text-lg font-bold text-slate-950 dark:text-white">{{ number_format((int) $value) }}</div>
                    </div>
                @endforeach
            </div>

            @foreach ((array) data_get($legacyPreview ?? [], 'warnings', []) as $warning)
                <x-admin.alert tone="warning">{{ __($warning) }}</x-admin.alert>
            @endforeach

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-950">
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ __('Historical Reconciliation') }}</h3>
                    <div class="mt-3 space-y-2 text-slate-600 dark:text-slate-300">
                        @foreach (($latestHistoricalReconciliation ?? []) as $name => $row)
                            @if (is_array($row))
                                <div class="flex items-center justify-between gap-3">
                                    <span>{{ __(str($name)->headline()->toString()) }}</span>
                                    <span class="font-medium">{{ ($row['matched'] ?? false) ? __('Matched') : __('Review') }}</span>
                                </div>
                            @endif
                        @endforeach
                        <div>{{ __('Sales') }}</div>
                        <div>{{ __('Operational Expenses') }}</div>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-950">
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ __('Monthly Report Reconciliation') }}</h3>
                    <div class="mt-3 space-y-2 text-slate-600 dark:text-slate-300">
                        @forelse (($latestMonthlyHistoricalReconciliation ?? []) as $month => $row)
                            <div class="flex items-center justify-between gap-3">
                                <span>{{ $month }}</span>
                                <span class="font-medium">{{ (is_array($row) && ($row['matched'] ?? false)) ? __('Matched') : __('Review') }}</span>
                            </div>
                        @empty
                            <div>{{ __('No monthly reconciliation yet.') }}</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-950">
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ __('Cash/Bank Reconciliation') }}</h3>
                    <div class="mt-3 space-y-2 text-slate-600 dark:text-slate-300">
                        @forelse ((array) data_get($latestCashBankHistoricalReconciliation ?? [], 'sales_payments', []) as $method => $row)
                            <div class="flex items-center justify-between gap-3">
                                <span>{{ $method }}</span>
                                <span class="font-medium">{{ (is_array($row) && ($row['matched'] ?? false)) ? __('Matched') : __('Review') }}</span>
                            </div>
                        @empty
                            <div>{{ __('No cash/bank reconciliation yet.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </x-admin.panel>
@endif
