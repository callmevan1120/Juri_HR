<x-app-layout>
    @php($profileUser = auth()->user())
    @php($backRoute = $profileUser->preferredHomeUrl())
    @php($profilePanels = [])
    @if (Laravel\Fortify\Features::canUpdateProfileInformation())
        @php($profilePanels['details'] = ['title' => __('Details'), 'copy' => __('Update your personal profile information'), 'icon' => 'heroicon-o-identification'])
    @endif
    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
        @php($profilePanels['password'] = ['title' => __('Password'), 'copy' => __('Change and strengthen your password'), 'icon' => 'heroicon-o-key'])
    @endif
    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
        @php($profilePanels['security'] = ['title' => __('Security'), 'copy' => __('Manage verification and account protection'), 'icon' => 'heroicon-o-shield-check'])
    @endif
    @php($profilePanels['sessions'] = ['title' => __('Sessions'), 'copy' => __('Review and sign out active devices'), 'icon' => 'heroicon-o-device-phone-mobile'])
    @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
        @php($profilePanels['danger'] = ['title' => __('Danger'), 'copy' => __('Request account deletion with admin approval'), 'icon' => 'heroicon-o-trash'])
    @endif

    <div class="profile-page user-page-shell" x-data="{
        panels: @js($profilePanels),
        activePanel: null,
        openPanel(panel) {
            if (!this.panels[panel]) {
                return;
            }

            this.activePanel = panel;
        },
        closePanel() {
            this.activePanel = null;
        },
    }">
        <div class="user-page-container user-page-container--standard">
            <div aria-labelledby="profile-page-title">
                <x-user.page-header :back-href="$backRoute" :title="__('Profile')" title-id="profile-page-title"
                    plain>
                    <x-slot name="icon">
                        <x-heroicon-o-user-circle class="h-6 w-6" />
                    </x-slot>

                    <x-slot name="meta">
                        @if ($profileUser->hasVerifiedEmail())
                            <span class="profile-verified-mark" aria-label="{{ __('Verified account') }}" title="{{ __('Verified') }}">
                                <x-heroicon-s-check-badge class="h-4 w-4 text-emerald-600 dark:text-emerald-300" />
                            </span>
                        @else
                            <span class="profile-verified-mark profile-verified-mark--warning" aria-label="{{ __('Unverified account') }}" title="{{ __('Unverified') }}">
                                <x-heroicon-s-exclamation-circle class="h-4 w-4 text-amber-600 dark:text-amber-300" />
                            </span>
                        @endif
                    </x-slot>

                    <x-slot name="actions">
                        <div class="profile-header-notification">
                            <livewire:shared.notifications-dropdown />
                        </div>
                    </x-slot>
                </x-user.page-header>

                <section class="profile-identity" aria-label="{{ __('Account summary') }}">
                    <img class="profile-identity__avatar" src="{{ $profileUser->profile_photo_url }}" alt="{{ $profileUser->name }}">

                    <div class="profile-identity__content">
                        <p class="profile-identity__eyebrow">{{ __('Signed in as') }}</p>
                        <h2 class="profile-identity__name">{{ $profileUser->name }}</h2>
                        <p class="profile-identity__email">{{ $profileUser->email }}</p>

                        <div class="profile-identity__meta">
                            @if($profileUser->nip)
                                <span>{{ $profileUser->nip }}</span>
                            @endif

                            @if($profileUser->division?->name)
                                <span>{{ $profileUser->division->name }}</span>
                            @endif

                            @if($profileUser->jobTitle?->name)
                                <span>{{ $profileUser->jobTitle->name }}</span>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="profile-logout-form">
                        @csrf
                        <button type="submit" class="profile-logout-button" aria-label="{{ __('Log Out') }}">
                            <x-heroicon-o-arrow-right-on-rectangle class="h-5 w-5" />
                            <span>{{ __('Log Out') }}</span>
                        </button>
                    </form>
                </section>

                <section class="profile-control-grid" aria-label="{{ __('Profile controls') }}">
                    @unless (\App\Helpers\Editions::attendanceLocked())
                        <a href="{{ route('face.enrollment') }}" class="profile-preferences__item !flex !items-center !justify-between no-underline" style="text-decoration: none;">
                            <div class="flex items-center gap-4">
                                <div class="profile-preferences__icon bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200">
                                    <x-heroicon-o-face-smile class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2 class="profile-preferences__title">{{ __('Face ID') }}</h2>
                                    <p class="profile-preferences__copy">{{ auth()->user()->hasFaceRegistered() ? __('Face ID is ready to use.') : __('Manage face verification.') }}</p>
                                </div>
                            </div>
                            <x-heroicon-o-chevron-right class="h-5 w-5 text-slate-400 dark:text-slate-500" />
                        </a>
                    @endunless

                    <div class="profile-preferences__item">
                        <div class="flex items-center gap-4">
                            <div class="profile-preferences__icon">
                                <x-heroicon-o-language class="h-5 w-5" />
                            </div>
                            <div>
                                <h2 class="profile-preferences__title">{{ __('Language') }}</h2>
                                <p class="profile-preferences__copy">{{ __('Switch between Indonesian and English') }}</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('user.language.update') }}">
                            @csrf
                            <input type="hidden" name="language" value="{{ app()->getLocale() == 'id' ? 'en' : 'id' }}">
                            <button type="submit" class="language-toggle language-toggle--compact"
                                aria-label="{{ __('Switch language to :language', ['language' => app()->getLocale() == 'id' ? 'English' : 'Bahasa Indonesia']) }}">
                                <span class="sr-only">{{ __('Switch Language') }}</span>
                                <span class="language-toggle__labels">
                                    <span class="language-toggle__label">{{ __('ID') }}</span>
                                    <span class="language-toggle__label">{{ __('EN') }}</span>
                                </span>
                                <span class="language-toggle__thumb {{ app()->getLocale() == 'en' ? 'language-toggle__thumb--end' : 'translate-x-0' }}">
                                    <span class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity opacity-100">
                                        <span class="leading-none">
                                            {{ app()->getLocale() == 'id' ? '🇮🇩' : '🇺🇸' }}
                                        </span>
                                    </span>
                                </span>
                            </button>
                        </form>
                    </div>

                    <div class="profile-preferences__item">
                        <div class="flex items-center gap-4">
                            <div class="profile-preferences__icon">
                                <x-heroicon-o-swatch class="h-5 w-5" />
                            </div>
                            <div>
                                <h2 class="profile-preferences__title">{{ __('Appearance') }}</h2>
                                <p class="profile-preferences__copy">{{ __('Toggle light or dark mode') }}</p>
                            </div>
                        </div>

                        <x-navigation.theme-toggle id="theme-switcher-profile" class="shrink-0" />
                    </div>

                    @foreach ($profilePanels as $panelKey => $panel)
                        <button type="button" class="profile-section-nav__link"
                            data-profile-panel="{{ $panelKey }}"
                            x-on:click="openPanel(@js($panelKey))"
                            x-bind:aria-haspopup="'dialog'"
                            x-bind:aria-expanded="(activePanel === @js($panelKey)).toString()"
                            aria-label="{{ $panel['title'] }}">
                            <span class="profile-section-nav__icon">
                                <x-dynamic-component :component="$panel['icon']" class="h-5 w-5" />
                            </span>
                            <span class="min-w-0">
                                <span class="profile-section-nav__title">{{ $panel['title'] }}</span>
                                <span class="profile-section-nav__copy">{{ $panel['copy'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </section>

                <template x-teleport="body">
                    <div x-cloak x-show="activePanel" x-trap.inert.noscroll="activePanel !== null"
                        x-on:keydown.escape.window="closePanel()" class="profile-modal">
                        <div class="profile-modal__backdrop" x-on:click="closePanel()"></div>

                        <div x-show="activePanel" class="profile-modal__panel" role="dialog" aria-modal="true"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <div class="profile-modal__header">
                                <div>
                                    <h2 class="profile-modal__title" x-text="activePanel ? panels[activePanel].title : ''"></h2>
                                    <p class="profile-modal__copy" x-text="activePanel ? panels[activePanel].copy : ''"></p>
                                </div>

                                <button type="button" class="profile-modal__close" x-on:click="closePanel()"
                                    aria-label="{{ __('Close panel') }}">
                                    <x-heroicon-o-x-mark class="h-5 w-5" />
                                </button>
                            </div>

                            <div class="profile-modal__body">
                                @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                                    <section x-cloak x-show="activePanel === 'details'" x-transition.opacity.duration.150ms>
                                        <h3 class="sr-only">{{ __('Profile Information') }}</h3>
                                        <livewire:profile.update-profile-information-form />
                                    </section>
                                @endif

                                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                                    <section x-cloak x-show="activePanel === 'password'" x-transition.opacity.duration.150ms>
                                        <h3 class="sr-only">{{ __('Update Password') }}</h3>
                                        <livewire:profile.update-password-form />
                                    </section>
                                @endif

                                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                                    <section x-cloak x-show="activePanel === 'security'" x-transition.opacity.duration.150ms>
                                        <h3 class="sr-only">{{ __('Two Factor Authentication') }}</h3>
                                        <livewire:profile.two-factor-authentication-form />
                                    </section>
                                @endif

                                <section x-cloak x-show="activePanel === 'sessions'" x-transition.opacity.duration.150ms>
                                    <h3 class="sr-only">{{ __('Browser Sessions') }}</h3>
                                    <livewire:profile.logout-other-browser-sessions-form />
                                </section>

                                @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                                    <section x-cloak x-show="activePanel === 'danger'" x-transition.opacity.duration.150ms>
                                        <h3 class="sr-only">{{ __('Delete Account') }}</h3>
                                        <livewire:profile.request-account-deletion-form />
                                    </section>
                                @endif
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>
