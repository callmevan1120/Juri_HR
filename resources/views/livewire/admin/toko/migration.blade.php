@if ($activePage === 'migration')
    <x-admin.panel class="border-0 bg-white shadow-sm dark:bg-slate-900">
        <div class="border-b border-slate-100 px-6 py-6 dark:border-slate-800">
            <h2 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ __('CSV Template Import') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Template-based master data migration.') }}</p>
        </div>

        <div class="space-y-6 px-6 py-6">
            @if (session('success'))
                <x-admin.alert tone="success">{{ session('success') }}</x-admin.alert>
            @endif

            @if (session('error'))
                <x-admin.alert tone="danger">{{ session('error') }}</x-admin.alert>
            @endif

            <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
                @foreach (($csvImportTemplates ?? []) as $template)
                    <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-white">{{ __($template['label']) }}</h3>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __($template['description']) }}</p>
                            </div>
                            <span class="rounded-full border border-slate-200 px-2 py-1 text-[11px] font-bold uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:text-slate-300">CSV</span>
                        </div>

                        <div class="mt-4 rounded-md bg-slate-50 p-3 font-mono text-xs text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                            {{ implode(', ', $template['headers']) }}
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <x-actions.secondary-button href="{{ route('admin.toko.import-template', ['type' => $template['key']]) }}" size="sm">
                                <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                                {{ __('Download Template') }}
                            </x-actions.secondary-button>
                        </div>

                        @if ($canImport)
                            <form method="POST" action="{{ route('admin.toko.import') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                                @csrf
                                <input type="hidden" name="import_type" value="{{ $template['key'] }}">

                                <div>
                                    <x-forms.label for="toko-csv-import-{{ $template['key'] }}" value="{{ __('CSV file') }}" />
                                    <x-forms.input
                                        id="toko-csv-import-{{ $template['key'] }}"
                                        name="import_file"
                                        type="file"
                                        accept=".csv,.txt,.xlsx,.xls"
                                        required
                                        class="mt-1"
                                    />
                                </div>

                                <x-actions.button type="submit" size="sm">
                                    <x-heroicon-o-arrow-up-tray class="h-4 w-4" />
                                    {{ __('Import CSV') }}
                                </x-actions.button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </x-admin.panel>
@endif
