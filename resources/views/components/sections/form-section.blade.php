@props(['submit'])

<div {{ $attributes->merge(['class' => '']) }}>
    <form wire:submit="{{ $submit }}">
        <div class="relative overflow-hidden rounded-2xl border border-primary-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

            <!-- Card Header -->
            <div class="relative z-10 rounded-t-2xl border-b border-primary-50 bg-white/50 px-4 py-4 backdrop-blur-sm dark:border-gray-700/50 dark:bg-gray-800/50 sm:px-5">
                <div class="flex items-start gap-3 sm:items-center">
                    @if (isset($icon))
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-primary-100 bg-primary-50 text-primary-600 shadow-sm dark:border-primary-700/50 dark:bg-primary-900/40 dark:text-primary-400">
                            {{ $icon }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        @if (isset($description) && filled(trim(strip_tags((string) $description))))
                            <div class="sr-only">{{ $description }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card Body -->
            <div class="relative z-10 bg-white/50 px-4 py-4 backdrop-blur-sm dark:bg-gray-800/50 sm:px-5">
                <div class="grid grid-cols-6 gap-4">
                    {{ $form }}
                </div>
            </div>

            <!-- Card Footer -->
            @if (isset($actions))
                <div class="relative z-10 flex flex-col-reverse gap-2 rounded-b-2xl border-t border-primary-50 px-4 py-3 backdrop-blur-sm dark:border-gray-700/50 sm:flex-row sm:items-center sm:justify-end sm:px-5">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </form>
</div>
