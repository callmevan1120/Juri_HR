<x-guest-layout>
    <div class="auth-shell">
        <div class="auth-shell__backdrop" aria-hidden="true"></div>

        <div class="auth-shell__container">
            <section class="auth-card lg:col-span-2" aria-labelledby="login-form-title">
                <div class="auth-card__header text-center">
                    <h2 id="login-form-title" class="auth-card__title !text-2xl !font-bold">{{ __('Welcome Back!') }}</h2>
                    <p class="auth-card__copy mt-2 text-sm text-gray-500">{{ __('Sign in to start attendance, review tasks, and manage daily requests.') }}</p>
                </div>

                <div class="auth-form">
                    @if (session('status'))
                        <div class="auth-status" role="status" aria-live="polite">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
                        @csrf

                        <div class="auth-section">
                            <div class="auth-field">
                                <label for="email" class="auth-label">{{ __('Email or Phone') }}</label>
                                <div class="auth-input-wrap">
                                    <div class="auth-input-icon" aria-hidden="true">
                                        <x-heroicon-o-at-symbol class="h-5 w-5" />
                                    </div>
                                    <input id="email" name="email" type="text" autocomplete="username"
                                        required aria-describedby="@error('email') email-error @enderror"
                                        aria-invalid="@error('email') true @else false @enderror"
                                        class="auth-input auth-input--icon @error('email') auth-input--error @enderror"
                                        value="{{ old('email') }}" placeholder="{{ __('Email or phone number') }}">
                                </div>
                                @error('email')
                                    <p id="email-error" class="auth-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="auth-field">
                                <label for="password" class="auth-label">{{ __('Password') }}</label>
                                <div class="auth-input-wrap">
                                    <div class="auth-input-icon" aria-hidden="true">
                                        <x-heroicon-o-lock-closed class="h-5 w-5" />
                                    </div>
                                    <input id="password" name="password" type="password"
                                        autocomplete="current-password" required
                                        aria-describedby="@error('password') password-error @enderror"
                                        aria-invalid="@error('password') true @else false @enderror"
                                        class="auth-input auth-input--icon @error('password') auth-input--error @enderror"
                                        placeholder="{{ __('********') }}">
                                </div>
                                @error('password')
                                    <p id="password-error" class="auth-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="auth-check-row">
                            <label for="remember_me" class="auth-check">
                                <input id="remember_me" name="remember" type="checkbox" class="auth-check__box"
                                    @checked(old('remember'))>
                                <span class="auth-check__label">{{ __('Remember me') }}</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="auth-link">
                                    {{ __('Forgot password?') }}
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="auth-button auth-button--full" aria-label="{{ __('Log in') }}">
                            <x-heroicon-o-arrow-right-on-rectangle class="mr-2 h-5 w-5" />
                            {{ __('Log in') }}
                        </button>
                    </form>

                    <div class="auth-footer">
                        {{ __("Don't have an account?") }}
                        <a href="{{ route('register') }}" class="auth-link">{{ __('Register') }}</a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>
