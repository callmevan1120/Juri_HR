<x-admin.page-shell :title="__('Operational Health')" :description="__('Internal status checks for maintenance and support.')">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <x-admin.insight-panel class="p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Overall') }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">{{ strtoupper($health['status']) }}</p>
        </x-admin.insight-panel>

        <x-admin.insight-panel class="p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Database') }}</p>
            <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">
                {{ $health['database']['ok'] ? __('Connected') : __('Attention') }}
                @if($health['database']['latency_ms'] !== null)
                    · {{ $health['database']['latency_ms'] }} ms
                @endif
            </p>
        </x-admin.insight-panel>

        <x-admin.insight-panel class="p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Queue') }}</p>
            <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">
                {{ __('Connection') }}: {{ $health['queue_connection'] }} · {{ __('Failed') }}: {{ $health['failed_jobs_count'] }}
            </p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Heartbeat') }}: {{ $health['queue_heartbeat_at'] ?? __('Not seen') }}</p>
        </x-admin.insight-panel>

        <x-admin.insight-panel class="p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Storage') }}</p>
            <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">{{ $health['storage_writable'] ? __('Writable') : __('Not writable') }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Free') }}: {{ $health['disk_free_human'] }}</p>
        </x-admin.insight-panel>

        <x-admin.insight-panel class="p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Backup') }}</p>
            <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">{{ $health['backup']['last_success_at'] ?? __('No completed backup') }}</p>
            <p class="mt-1 text-xs text-slate-500">
                {{ __('File') }}: {{ $health['backup']['file_present'] ? __('Present') : __('Missing') }}
                @if($health['backup']['checksum_matches_meta'] !== null)
                    · {{ __('Checksum') }}: {{ $health['backup']['checksum_matches_meta'] ? __('OK') : __('Mismatch') }}
                @endif
            </p>
        </x-admin.insight-panel>

        <x-admin.insight-panel class="p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Runtime') }}</p>
            <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">
                {{ __('App') }} {{ $health['app_version'] }} · {{ __('Cache') }} {{ $health['cache_driver'] }} · {{ __('Session') }} {{ $health['session_driver'] }}
            </p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Scheduler') }}: {{ $health['scheduler_heartbeat_at'] ?? __('Not seen') }}</p>
        </x-admin.insight-panel>

        <x-admin.insight-panel class="p-4 md:col-span-2 xl:col-span-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Feature Locks') }}</p>
            <div class="mt-3 grid gap-2 text-sm sm:grid-cols-3">
                <div>{{ __('Payroll locked') }}: <strong>{{ $health['license']['payroll_locked'] ? __('Yes') : __('No') }}</strong></div>
                <div>{{ __('Reporting locked') }}: <strong>{{ $health['license']['reporting_locked'] ? __('Yes') : __('No') }}</strong></div>
                <div>{{ __('System backup locked') }}: <strong>{{ $health['license']['system_backup_locked'] ? __('Yes') : __('No') }}</strong></div>
            </div>
        </x-admin.insight-panel>
    </div>
</x-admin.page-shell>
