<x-guest-layout>
    <x-overlays.authentication-card>
        <x-slot name="logo">
            <x-branding.authentication-card-logo />
        </x-slot>

        <div class="auth-card__header">
            <p class="auth-card__eyebrow">{{ __('Security') }}</p>
            <h1 class="auth-card__title">{{ __('Confirm Password') }}</h1>
            <p class="auth-card__copy">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </p>
        </div>

        <div class="auth-form">
            <x-forms.validation-errors />

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="auth-section">
                    <div class="auth-field">
                        <label for="password" class="auth-label">{{ __('Password') }}</label>
                        <x-forms.input id="password" type="password" name="password" required autocomplete="current-password" />
                    </div>
                </div>

                <div class="auth-actions auth-actions--split">
                    <x-actions.button class="auth-button auth-button--full sm:w-auto">
                        {{ __('Confirm') }}
                    </x-actions.button>
                </div>
            </form>
        </div>
    </x-overlays.authentication-card>
</x-guest-layout>
