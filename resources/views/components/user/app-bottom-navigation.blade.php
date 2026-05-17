@php
    $items = [
        [
            'label' => __('Home'),
            'href' => route('home'),
            'active' => request()->routeIs('home'),
            'icon' => 'heroicon-o-home',
        ],
        [
            'label' => __('Schedule'),
            'href' => route('my-schedule'),
            'active' => request()->routeIs('my-schedule', 'shift-swap-requests', 'wfh-requests'),
            'icon' => 'heroicon-o-calendar-days',
        ],
        [
            'label' => __('Clock In'),
            'href' => route('scan'),
            'active' => request()->routeIs('scan'),
            'icon' => 'heroicon-o-qr-code',
            'primary' => true,
        ],
        [
            'label' => __('Tasks'),
            'href' => route('hr-tasks'),
            'active' => request()->routeIs('hr-tasks', 'my-tasks', 'my-forms', 'approvals', 'approvals.history'),
            'icon' => 'heroicon-o-clipboard-document-check',
        ],
        [
            'label' => __('Profile'),
            'href' => route('profile.show'),
            'active' => request()->routeIs('profile.show'),
            'icon' => 'heroicon-o-user-circle',
        ],
    ];
@endphp

<nav
    aria-label="{{ __('User navigation') }}"
    class="user-bottom-navigation"
>
    <div class="user-bottom-navigation__dock">
        @foreach ($items as $item)
            @php
                $active = (bool) $item['active'];
                $isPrimary = (bool) ($item['primary'] ?? false);
            @endphp

            <a
                href="{{ $item['href'] }}"
                @if ($active) aria-current="page" @endif
                class="group user-bottom-navigation__item {{ $active ? 'is-active' : '' }} {{ $isPrimary ? 'is-primary' : '' }}"
            >
                <span class="user-bottom-navigation__icon">
                    <x-dynamic-component :component="$item['icon']" class="h-5 w-5" />
                </span>
                <span class="user-bottom-navigation__label">
                    {{ $item['label'] }}
                </span>
            </a>
        @endforeach
    </div>
</nav>
