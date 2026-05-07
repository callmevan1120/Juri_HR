@props(['title', 'description', 'content'])

<div {{ $attributes->merge(['class' => '']) }}>
    <div class="relative overflow-hidden rounded-2xl border border-primary-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

        <!-- Card Header -->
        <div class="relative z-10 rounded-t-2xl border-b border-primary-50 bg-white/50 px-4 py-4 backdrop-blur-sm dark:border-gray-700/50 dark:bg-gray-800/50 sm:px-5">
             <div class="flex items-center gap-3">
                @if (isset($icon))
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-primary-100 bg-primary-50 text-primary-600 shadow-sm dark:border-primary-700/50 dark:bg-primary-900/40 dark:text-primary-400">
                        {{ $icon }}
                    </div>
                @endif
                <div>
                     <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                     <p class="sr-only">{{ $description }}</p>
                </div>
            </div>
        </div>

        <!-- Card Content -->
        <div class="relative z-10 bg-white/50 px-4 py-4 backdrop-blur-sm dark:bg-gray-800/50 sm:px-5">
            {{ $content }}
        </div>

        <!-- Card Footer -->
        @if (isset($actions))
            <div class="relative z-10 flex items-center justify-end rounded-b-2xl border-t border-primary-50 px-4 py-3 backdrop-blur-sm dark:border-gray-700/50 sm:px-5">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
