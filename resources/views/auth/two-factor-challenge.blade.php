<x-guest-layout>
    <x-overlays.authentication-card>
        <div x-data="{ recovery: false }">
            <div class="auth-card__header auth-native-header">
                <div class="auth-native-mark" aria-hidden="true">
                    <x-heroicon-o-device-phone-mobile class="h-8 w-8" />
                </div>
                <div>
                    <p class="auth-card__eyebrow">{{ __('Two Factor Authentication') }}</p>
                    <h1 class="auth-card__title">{{ __('Verify your login') }}</h1>
                    <p class="auth-card__copy" x-show="! recovery">
                        {{ __('Enter the authentication code from your authenticator app.') }}
                    </p>
                    <p class="auth-card__copy" x-cloak x-show="recovery">
                        {{ __('Enter one of your emergency recovery codes.') }}
                    </p>
                </div>
            </div>

            <div class="auth-form">
                <x-forms.validation-errors />

                <form method="POST" action="{{ route('two-factor.login') }}" novalidate>
                    @csrf

                    <div class="auth-section">
                        <div class="auth-field" x-show="! recovery">
                            <label for="code" class="auth-label">{{ __('Code') }}</label>
                            <x-forms.input id="code" type="text" inputmode="numeric" name="code" x-ref="code" autocomplete="one-time-code" />
                        </div>

                        <div class="auth-field" x-cloak x-show="recovery">
                            <label for="recovery_code" class="auth-label">{{ __('Recovery Code') }}</label>
                            <x-forms.input id="recovery_code" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" />
                        </div>
                    </div>

                    <div class="auth-actions auth-actions--split">
                        <button type="button" class="auth-link"
                                        x-show="! recovery"
                                        x-on:click="
                                            recovery = true;
                                            $nextTick(() => { $refs.recovery_code.focus() })
                                        ">
                            {{ __('Use a recovery code') }}
                        </button>

                        <button type="button" class="auth-link"
                                        x-cloak
                                        x-show="recovery"
                                        x-on:click="
                                            recovery = false;
                                            $nextTick(() => { $refs.code.focus() })
                                        ">
                            {{ __('Use an authentication code') }}
                        </button>

                        <x-actions.button class="auth-button auth-button--full sm:w-auto">
                            <x-heroicon-o-arrow-right-on-rectangle class="mr-2 h-5 w-5" />
                            {{ __('Log in') }}
                        </x-actions.button>
                    </div>
                </form>
            </div>
        </div>
    </x-overlays.authentication-card>
</x-guest-layout>
