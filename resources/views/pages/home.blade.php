<x-app-layout>
    @php($currentUser = request()->user())

    <section aria-labelledby="home-page-title" class="user-home-hero">
        <div class="user-home-hero__inner">
            <div class="user-home-hero__copy">
                <p class="user-home-hero__eyebrow">{{ __('Welcome back') }}</p>
                <h1 id="home-page-title" class="user-home-hero__title">{{ $currentUser->name }}</h1>
            </div>

            <a href="{{ route('profile.show') }}" class="user-home-hero__profile" aria-label="{{ __('Open profile') }}">
                <img class="h-full w-full object-cover" src="{{ $currentUser->profile_photo_url }}" alt="{{ $currentUser->name }}" />
            </a>
        </div>
    </section>

    <div class="user-home-content">
        <section aria-labelledby="attendance-summary-heading">
            <h2 id="attendance-summary-heading" class="sr-only">{{ __('Today attendance summary') }}</h2>
            <livewire:user.home-attendance-status />
        </section>

        <section aria-labelledby="my-menu-heading">
            <h2 id="my-menu-heading" class="sr-only">{{ __('Quick Access') }}</h2>
            <livewire:user.quick-actions />
        </section>

        <section aria-labelledby="happening-now-heading">
            <div class="user-section-heading">
                <h2 id="happening-now-heading" class="user-section-heading__title">{{ __('Happening Now') }}</h2>
                <a href="{{ route('notifications') }}" class="user-section-heading__action">{{ __('View All') }}</a>
            </div>
            <livewire:user.upcoming-events-widget />
        </section>
    </div>

    @push('scripts')
        <script>
            if (sessionStorage.getItem('force_reload_next')) {
                sessionStorage.removeItem('force_reload_next');
                window.location.reload();
            }
        </script>
    @endpush
</x-app-layout>
