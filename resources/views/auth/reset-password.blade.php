<x-guest-layout>
    <div class="auth-shell">
        <div class="auth-shell__backdrop" aria-hidden="true"></div>

        <div class="auth-shell__container">
            <section class="auth-card lg:col-span-2" aria-labelledby="reset-password-title">
                <div class="auth-card__header auth-native-header">
                    <div class="auth-native-mark" aria-hidden="true">
                        <x-heroicon-o-shield-check class="h-8 w-8" />
                    </div>
                    <div>
                        <p class="auth-card__eyebrow">{{ __('Password Reset') }}</p>
                        <h2 id="reset-password-title" class="auth-card__title">{{ __('Create a new password') }}</h2>
                        <p class="auth-card__copy">
                            {{ __('Choose a new password that is secure and easy for you to remember.') }}
                        </p>
                    </div>
                </div>

                <div class="auth-form">
                    <x-forms.validation-errors />

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-5" novalidate>
                        @csrf

                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <div class="auth-section">
                            <div class="auth-section__header">
                                <h3 class="auth-section__title">{{ __('Account verification') }}</h3>
                                <p class="auth-section__copy sr-only">
                                    {{ __('Confirm the account email and choose a strong password to complete the reset process.') }}
                                </p>
                            </div>

                            <div class="auth-grid">
                                <div class="auth-field auth-field--full">
                                    <label for="email" class="auth-label">{{ __('Email Address') }}</label>
                                    <div class="auth-input-wrap">
                                        <div class="auth-input-icon" aria-hidden="true">
                                            <x-heroicon-o-at-symbol class="h-5 w-5" />
                                        </div>
                                        <input id="email" name="email" type="email" required autocomplete="username"
                                            aria-describedby="@error('email') email-error @enderror"
                                            aria-invalid="@error('email') true @else false @enderror"
                                            class="auth-input auth-input--icon @error('email') auth-input--error @enderror"
                                            value="{{ old('email', $request->email) }}" placeholder="{{ __('email@example.com') }}">
                                    </div>
                                    @error('email')
                                        <p id="email-error" class="auth-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="auth-field">
                                    <label for="password" class="auth-label">{{ __('New Password') }}</label>
                                    <div class="auth-input-wrap">
                                        <div class="auth-input-icon" aria-hidden="true">
                                            <x-heroicon-o-lock-closed class="h-5 w-5" />
                                        </div>
                                        <input id="password" name="password" type="password" required autocomplete="new-password"
                                            aria-describedby="@error('password') password-error @enderror"
                                            aria-invalid="@error('password') true @else false @enderror"
                                            class="auth-input auth-input--icon @error('password') auth-input--error @enderror"
                                            placeholder="{{ __('********') }}">
                                    </div>
                                    @error('password')
                                        <p id="password-error" class="auth-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="auth-field">
                                    <label for="password_confirmation" class="auth-label">{{ __('Confirm Password') }}</label>
                                    <div class="auth-input-wrap">
                                        <div class="auth-input-icon" aria-hidden="true">
                                            <x-heroicon-o-check class="h-5 w-5" />
                                        </div>
                                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                                            aria-describedby="@error('password_confirmation') password-confirmation-error @enderror"
                                            aria-invalid="@error('password_confirmation') true @else false @enderror"
                                            class="auth-input auth-input--icon @error('password_confirmation') auth-input--error @enderror"
                                            placeholder="{{ __('********') }}">
                                    </div>
                                    @error('password_confirmation')
                                        <p id="password-confirmation-error" class="auth-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="auth-actions auth-actions--split">
                            <a href="{{ route('login') }}" class="auth-link">
                                {{ __('Back to Login') }}
                            </a>

                            <button type="submit" class="auth-button auth-button--full sm:w-auto" aria-label="{{ __('Reset Password') }}">
                                <x-heroicon-o-check-circle class="mr-2 h-5 w-5" />
                                {{ __('Reset Password') }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>
