<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f8fafc">
    <title>@yield('title') | {{ config('app.name') }}</title>

    <script>
        if (localStorage.getItem('isDark') === 'true' || (!('isDark' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                try {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    await Promise.all(registrations.map((registration) => registration.unregister()));

                    if ('caches' in window) {
                        const cacheNames = await caches.keys();
                        await Promise.all(cacheNames.map((cacheName) => caches.delete(cacheName)));
                    }
                } catch (error) {
                    console.warn('Error page cache reset failed', error);
                }
            });
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50 font-sans antialiased text-slate-950 selection:bg-primary-600 selection:text-white dark:bg-slate-950 dark:text-slate-100">
    <a href="#error-main" class="skip-link">{{ __('Skip to main content') }}</a>

    <div class="min-h-screen px-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] pt-[max(1.25rem,env(safe-area-inset-top))] sm:px-6 lg:px-8">
        <main id="error-main" tabindex="-1" class="mx-auto flex min-h-[calc(100vh-2.5rem)] w-full max-w-5xl items-center justify-center">
            <section aria-labelledby="error-page-title" class="w-full overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_26px_80px_-48px_rgba(15,23,42,0.55)] dark:border-slate-800 dark:bg-slate-900">
                <div class="grid min-h-[34rem] lg:grid-cols-[18rem_minmax(0,1fr)]">
                    <aside class="flex flex-col justify-between gap-8 border-b border-slate-200 bg-slate-100/70 p-5 dark:border-slate-800 dark:bg-slate-950/55 sm:p-6 lg:border-b-0 lg:border-r">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary-700 text-white shadow-sm dark:bg-primary-400 dark:text-slate-950">
                                <x-branding.application-mark class="h-7 w-7 text-current" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-950 dark:text-white">
                                    {{ config('app.name') }}
                                </p>
                                <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                                    {{ __('System status') }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white bg-white/70 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary-700 dark:text-primary-300">
                                {{ __('Need help?') }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                {{ __('If this keeps happening, note the page and time before contacting your administrator.') }}
                            </p>
                        </div>
                    </aside>

                    <div class="min-w-0 p-5 sm:p-6 lg:p-8">
                        @yield('content')
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
