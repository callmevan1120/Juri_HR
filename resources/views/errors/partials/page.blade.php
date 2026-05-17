@php
    $primaryAction = $primaryAction ?? null;
    $secondaryAction = $secondaryAction ?? null;
    $details = array_values(array_filter($details ?? []));

    $tone = $tone ?? 'primary';
    $toneClasses = match ($tone) {
        'amber' => [
            'badge' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200',
            'icon' => 'bg-amber-500 text-white dark:bg-amber-300 dark:text-amber-950',
            'dot' => 'bg-amber-500 dark:bg-amber-300',
        ],
        'red' => [
            'badge' => 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200',
            'icon' => 'bg-red-600 text-white dark:bg-red-300 dark:text-red-950',
            'dot' => 'bg-red-600 dark:bg-red-300',
        ],
        'blue' => [
            'badge' => 'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-200',
            'icon' => 'bg-sky-600 text-white dark:bg-sky-300 dark:text-sky-950',
            'dot' => 'bg-sky-600 dark:bg-sky-300',
        ],
        'slate' => [
            'badge' => 'border-slate-200 bg-slate-100 text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200',
            'icon' => 'bg-slate-700 text-white dark:bg-slate-200 dark:text-slate-950',
            'dot' => 'bg-slate-600 dark:bg-slate-300',
        ],
        default => [
            'badge' => 'border-primary-200 bg-primary-50 text-primary-800 dark:border-primary-900/50 dark:bg-primary-950/30 dark:text-primary-200',
            'icon' => 'bg-primary-700 text-white dark:bg-primary-300 dark:text-slate-950',
            'dot' => 'bg-primary-700 dark:bg-primary-300',
        ],
    };

    $icon = match ($tone) {
        'amber' => 'heroicon-o-exclamation-triangle',
        'red' => 'heroicon-o-x-circle',
        'blue' => 'heroicon-o-clock',
        'slate' => 'heroicon-o-lock-closed',
        default => 'heroicon-o-information-circle',
    };
@endphp

<div class="flex min-h-full flex-col justify-center gap-7">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="inline-grid h-14 w-14 shrink-0 place-items-center rounded-2xl shadow-sm {{ $toneClasses['icon'] }}">
                <x-dynamic-component :component="$icon" class="h-7 w-7" />
            </span>

            <div>
                <div class="inline-flex min-h-[2.25rem] items-center gap-2 rounded-full border px-3 py-1.5 {{ $toneClasses['badge'] }}">
                    <span class="text-xs font-black uppercase tracking-[0.18em]">{{ __('HTTP') }}</span>
                    <span class="text-sm font-black">{{ $status }}</span>
                </div>
            </div>
        </div>

        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}"
            class="inline-flex min-h-[2.75rem] items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            <span>{{ __('Go back') }}</span>
        </a>
    </div>

    <div class="space-y-3">
        @if (!empty($eyebrow))
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary-700 dark:text-primary-300">{{ $eyebrow }}</p>
        @endif

        <h1 id="error-page-title" class="max-w-2xl text-2xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-3xl">
            {{ $titleText }}
        </h1>

        <p class="max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300">
            {{ $summary }}
        </p>
    </div>

    @if ($details !== [])
        <section aria-labelledby="error-details-title" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/45 sm:p-5">
            <h2 id="error-details-title" class="text-sm font-semibold text-slate-950 dark:text-white">
                {{ __('What this usually means') }}
            </h2>

            <ul class="mt-4 grid gap-3 text-sm leading-6 text-slate-600 dark:text-slate-300 sm:grid-cols-2">
                @foreach ($details as $detail)
                    <li class="flex gap-3">
                        <span class="mt-2 h-2 w-2 shrink-0 rounded-full {{ $toneClasses['dot'] }}" aria-hidden="true"></span>
                        <span>{{ $detail }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="grid gap-3 sm:grid-cols-2" aria-label="{{ __('Available actions') }}">
        @if ($primaryAction)
            <a href="{{ $primaryAction['href'] }}"
               class="wcag-touch-target inline-flex items-center justify-center rounded-2xl bg-primary-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition duration-150 ease-in-out hover:bg-primary-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-600 focus-visible:ring-offset-2 dark:bg-primary-400 dark:text-slate-950 dark:hover:bg-primary-300 dark:focus-visible:ring-primary-300 dark:focus-visible:ring-offset-slate-900">
                {{ $primaryAction['label'] }}
            </a>
        @endif

        @if ($secondaryAction)
            <a href="{{ $secondaryAction['href'] }}"
               class="wcag-touch-target inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition duration-150 ease-in-out hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800 dark:focus-visible:ring-slate-500 dark:focus-visible:ring-offset-slate-900">
                {{ $secondaryAction['label'] }}
            </a>
        @endif
    </div>
</div>
