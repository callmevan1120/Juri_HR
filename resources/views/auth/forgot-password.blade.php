<x-guest-layout>
    <div class="auth-shell">
        <div class="auth-shell__backdrop" aria-hidden="true"></div>

        <div class="auth-shell__container">
            <section class="auth-card lg:col-span-2" aria-labelledby="forgot-password-title">
                <div class="auth-card__header auth-native-header">
                    <div class="auth-native-mark" aria-hidden="true">
                        <x-heroicon-o-key class="h-8 w-8" />
                    </div>
                    <div>
                        <p class="auth-card__eyebrow">{{ __('Password Recovery') }}</p>
                        <h2 id="forgot-password-title" class="auth-card__title">{{ __('Forgot your password?') }}</h2>
                        <p class="auth-card__copy">
                            {{ __('Enter your registered email and we will send a secure reset link.') }}
                        </p>
                    </div>
                </div>

                <div class="auth-form">
                    @if (session('status'))
                        <div class="auth-status" role="status" aria-live="polite">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate>
                        @csrf

                        <div class="auth-section">

                            <div class="auth-grid auth-grid--single">
                                <div class="auth-field">
                                    <label for="email" class="auth-label">{{ __('Email Address') }}</label>
                                    <div class="auth-input-wrap">
                                        <div class="auth-input-icon" aria-hidden="true">
                                            <x-heroicon-o-at-symbol class="h-5 w-5" />
                                        </div>
                                        <input id="email" name="email" type="email" autocomplete="email"
                                            required aria-describedby="@error('email') email-error @enderror"
                                            aria-invalid="@error('email') true @else false @enderror"
                                            class="auth-input auth-input--icon @error('email') auth-input--error @enderror"
                                            value="{{ old('email') }}" placeholder="{{ __('email@example.com') }}">
                                    </div>
                                    @error('email')
                                        <p id="email-error" class="auth-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="auth-actions auth-actions--split">
                            <a href="{{ route('login') }}" class="auth-link">
                                {{ __('Back to Login') }}
                            </a>

                            <button type="submit" class="auth-button auth-button--full sm:w-auto" aria-label="{{ __('Send Password Reset Link') }}">
                                <x-heroicon-o-paper-airplane class="mr-2 h-5 w-5" />
                                {{ __('Send Password Reset Link') }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>
