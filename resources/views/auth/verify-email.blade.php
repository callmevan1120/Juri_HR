<x-guest-layout>
    <div class="auth-shell">
        <div class="auth-shell__backdrop" aria-hidden="true"></div>

        <div class="auth-shell__container">
            <section class="auth-card lg:col-span-2" aria-labelledby="verify-email-title">
                <div class="auth-card__header text-center">
                    <h2 id="verify-email-title" class="auth-card__title !text-2xl !font-bold">{{ __('Check your inbox') }}</h2>
                    <p class="auth-card__copy mt-2 text-sm text-gray-500">
                        {{ __('Enter the verification code we sent to your email address.') }}
                    </p>
                </div>

                <div class="auth-form">
                    @if (session('status') == 'verification-link-sent')
                        <div class="auth-status" role="status" aria-live="polite">
                            {{ __('A new verification code has been sent to your email address.') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verification.code.verify') }}" class="space-y-5" novalidate>
                        @csrf

                        <div class="auth-field">
                            <label for="code" class="auth-label">{{ __('Verification Code') }}</label>
                            <input id="code" name="code" type="text" value="{{ old('code') }}" required
                                inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code"
                                aria-describedby="@error('code') code-error @enderror"
                                aria-invalid="@error('code') true @else false @enderror"
                                class="auth-input auth-code-input @error('code') auth-input--error @enderror"
                                placeholder="{{ __('6 digit code') }}">
                            @error('code')
                                <p id="code-error" class="auth-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="auth-button auth-button--full" aria-label="{{ __('Verify and Continue') }}">
                            <x-heroicon-o-check-badge class="mr-2 h-5 w-5" />
                            {{ __('Verify and Continue') }}
                        </button>
                    </form>

                    <div class="auth-actions">
                        <form method="POST" action="{{ route('verification.send') }}" class="inline">
                            @csrf
                            <button type="submit" class="auth-link">
                                {{ __('Resend Verification Code') }}
                            </button>
                        </form>

                        <a href="{{ route('profile.show') }}" class="auth-link">
                            {{ __('Edit Profile') }}
                        </a>
                    </div>

                    <div class="auth-footer">
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="auth-link">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>
