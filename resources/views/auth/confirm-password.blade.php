<x-guest-layout>
    <x-overlays.authentication-card>
        <div class="auth-card__header auth-native-header">
            <div class="auth-native-mark" aria-hidden="true">
                <x-heroicon-o-lock-closed class="h-8 w-8" />
            </div>
            <div>
                <p class="auth-card__eyebrow">{{ __('Security') }}</p>
                <h1 class="auth-card__title">{{ __('Confirm Password') }}</h1>
                <p class="auth-card__copy">
                    {{ __('This area is protected. Confirm your password to continue.') }}
                </p>
            </div>
        </div>

        <div class="auth-form">
            <x-forms.validation-errors />

            <form method="POST" action="{{ route('password.confirm') }}" novalidate>
                @csrf

                <div class="auth-section">
                    <div class="auth-field">
                        <label for="password" class="auth-label">{{ __('Password') }}</label>
                        <x-forms.input id="password" type="password" name="password" required autocomplete="current-password" />
                    </div>
                </div>

                <div class="auth-actions auth-actions--split">
                    <x-actions.button class="auth-button auth-button--full sm:w-auto">
                        <x-heroicon-o-shield-check class="mr-2 h-5 w-5" />
                        {{ __('Confirm') }}
                    </x-actions.button>
                </div>
            </form>
        </div>
    </x-overlays.authentication-card>
</x-guest-layout>
