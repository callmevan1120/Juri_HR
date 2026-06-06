@php
    $isDynamic = (bool) $barcode->dynamic_enabled;
    $latitude = number_format((float) $barcode->latitude, 6, '.', '');
    $longitude = number_format((float) $barcode->longitude, 6, '.', '');
    $radius = rtrim(rtrim(number_format((float) $barcode->radius, 2, '.', ''), '0'), '.');
    $mapsUrl = 'https://www.google.com/maps/search/?api=1&query='.$latitude.','.$longitude;
@endphp

<x-app-layout>
    <x-admin.page-shell
        :title="$barcode->name"
        :description="__('Inspect checkpoint QR mode, location, radius, and operational actions.')"
    >
        @if ($isDynamic)
            <form id="regenerate-secret-form" action="{{ route('admin.barcodes.regenerate-secret', $barcode) }}" method="post" class="hidden">
                @csrf
            </form>
        @endif

        <div class="space-y-4">
            <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($isDynamic)
                            <x-admin.status-badge tone="warning">{{ __('Dynamic QR') }}</x-admin.status-badge>
                        @else
                            <x-admin.status-badge tone="neutral">{{ __('Static QR') }}</x-admin.status-badge>
                        @endif

                        <x-admin.status-badge tone="info">
                            {{ __('Radius') }} {{ $radius }}m
                        </x-admin.status-badge>
                    </div>

                    <h2 class="mt-3 truncate text-xl font-semibold tracking-tight text-slate-950 dark:text-white">
                        {{ $barcode->name }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                        {{ __('Checkpoint ID') }}: {{ $barcode->id }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-actions.secondary-button href="{{ route('admin.barcodes') }}">
                        {{ __('Back') }}
                    </x-actions.secondary-button>

                    <x-actions.button href="{{ route('admin.barcodes.edit', $barcode) }}" variant="warning">
                        <x-heroicon-o-pencil-square class="h-5 w-5" />
                        {{ __('Edit') }}
                    </x-actions.button>

                    @if ($isDynamic)
                        <x-actions.button href="{{ route('admin.barcodes.dynamic-display', $barcode) }}">
                            <x-heroicon-o-eye class="h-5 w-5" />
                            {{ __('Open Live Display') }}
                        </x-actions.button>
                    @else
                        <x-actions.button href="{{ route('admin.barcodes.download', $barcode->id) }}">
                            <x-heroicon-o-arrow-down-tray class="h-5 w-5" />
                            {{ __('Download QR') }}
                        </x-actions.button>
                    @endif
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-[minmax(0,0.95fr)_minmax(320px,1.05fr)]">
                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                                {{ __('QR Status') }}
                            </p>
                            <h3 class="mt-1 text-base font-semibold text-slate-950 dark:text-white">
                                {{ $isDynamic ? __('Rotating token display') : __('Static checkpoint QR') }}
                            </h3>
                        </div>
                    </div>

                    <div class="mt-4 flex min-h-[320px] items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900">
                        @if ($isDynamic)
                            <div class="max-w-md text-center">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-200">
                                    <x-heroicon-o-arrow-path class="h-10 w-10" />
                                </div>
                                <p class="mt-4 text-sm font-semibold text-slate-950 dark:text-white">
                                    {{ __('Dynamic QR Active') }}
                                </p>
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                    {{ __('Use the live display page for scanning. Static downloads are disabled for dynamic checkpoints.') }}
                                </p>
                            </div>
                        @elseif ($qrPreviewDataUri)
                            <img
                                src="{{ $qrPreviewDataUri }}"
                                alt="{{ __('QR preview for') }} {{ $barcode->name }}"
                                class="h-auto w-full max-w-[320px] rounded-lg bg-white p-2 shadow-sm"
                            >
                        @endif
                    </div>

                    <dl class="mt-4 grid gap-3 text-sm">
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">
                                {{ $isDynamic ? __('Internal Checkpoint Code') : __('Static Barcode Value') }}
                            </dt>
                            <dd class="mt-1 break-all font-mono text-slate-950 dark:text-white">
                                {{ $barcode->value }}
                            </dd>
                        </div>

                        @if ($isDynamic)
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                                    <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('Refresh Every') }}</dt>
                                    <dd class="mt-1 text-slate-950 dark:text-white">{{ $barcode->dynamic_ttl_seconds }} {{ __('sec') }}</dd>
                                </div>
                                <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                                    <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('Secret Status') }}</dt>
                                    <dd class="mt-1 text-slate-950 dark:text-white">{{ filled($barcode->secret_key) ? __('Active') : __('Missing') }}</dd>
                                </div>
                            </div>

                            <x-actions.button
                                type="button"
                                variant="secondary"
                                data-sweet-confirm
                                data-confirm-message="{{ __('Regenerate the dynamic secret now? Any currently displayed QR will stop working immediately.') }}"
                                data-confirm-form="regenerate-secret-form"
                                data-confirm-icon="warning"
                            >
                                <x-heroicon-o-key class="h-5 w-5" />
                                {{ __('Regenerate Secret') }}
                            </x-actions.button>
                        @endif
                    </dl>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        {{ __('Checkpoint Location') }}
                    </p>
                    <h3 class="mt-1 text-base font-semibold text-slate-950 dark:text-white">
                        {{ __('Location and Attendance Boundary') }}
                    </h3>

                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('Latitude') }}</dt>
                            <dd class="mt-1 font-mono text-slate-950 dark:text-white">{{ $latitude }}</dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('Longitude') }}</dt>
                            <dd class="mt-1 font-mono text-slate-950 dark:text-white">{{ $longitude }}</dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('Attendance Radius') }}</dt>
                            <dd class="mt-1 text-slate-950 dark:text-white">{{ $radius }}m</dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('Updated') }}</dt>
                            <dd class="mt-1 text-slate-950 dark:text-white">{{ optional($barcode->updated_at)->format('Y-m-d H:i') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-start gap-3">
                            <x-heroicon-o-map-pin class="mt-0.5 h-5 w-5 shrink-0 text-primary-600 dark:text-primary-300" />
                            <div>
                                <p class="text-sm font-semibold text-slate-950 dark:text-white">
                                    {{ __('Map Pin') }}
                                </p>
                                <p class="mt-1 break-all text-sm text-slate-600 dark:text-slate-300">
                                    {{ $latitude }}, {{ $longitude }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-actions.secondary-button href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer">
                                <x-heroicon-o-arrow-top-right-on-square class="h-5 w-5" />
                                {{ __('Open Map') }}
                            </x-actions.secondary-button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </x-admin.page-shell>
</x-app-layout>
