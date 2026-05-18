<div class="user-page-shell">
    <div class="user-page-container user-page-container--wide">
        <section aria-labelledby="my-assets-title" class="user-page-surface relative">
            <x-user.page-header
                :back-href="route('home')"
                :title="__('My Assets')"
                title-id="my-assets-title">
                <x-slot name="icon">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-50 via-white to-emerald-50 text-primary-700 ring-1 ring-inset ring-primary-100 shadow-sm dark:from-primary-900/30 dark:via-gray-800 dark:to-emerald-900/20 dark:text-primary-300 dark:ring-primary-800/60">
                        <x-heroicon-o-archive-box class="h-5 w-5" />
                    </div>
                </x-slot>
            </x-user.page-header>

            <div class="user-page-body bg-gray-50/50 dark:bg-gray-900/20">
                <div class="mb-6">
                    @include('components.feedback.alert-messages')
                </div>

                <div class="mb-6">
                    <nav class="user-segmented-tabs" aria-label="{{ __('Tabs') }}">
                        <button
                            type="button"
                            wire:click="setAssetFilter('active')"
                            aria-selected="{{ $assetFilter === 'active' ? 'true' : 'false' }}"
                            class="user-segmented-tab">
                            <span>{{ __('Active') }}</span>
                            <span @class([
                                'ms-2 rounded-full px-2 py-0.5 text-xs font-bold',
                                'bg-primary-50 text-primary-800 dark:bg-primary-950/50 dark:text-primary-100' => $assetFilter === 'active',
                                'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => $assetFilter !== 'active',
                            ])>{{ $assets->count() }}</span>
                        </button>

                        <button
                            type="button"
                            wire:click="setAssetFilter('returned')"
                            aria-selected="{{ $assetFilter === 'returned' ? 'true' : 'false' }}"
                            class="user-segmented-tab">
                            <span>{{ __('Returned') }}</span>
                            <span @class([
                                'ms-2 rounded-full px-2 py-0.5 text-xs font-bold',
                                'bg-primary-50 text-primary-800 dark:bg-primary-950/50 dark:text-primary-100' => $assetFilter === 'returned',
                                'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => $assetFilter !== 'returned',
                            ])>{{ $returnedHistories->count() }}</span>
                        </button>
                    </nav>
                </div>

                @if($assetFilter === 'active' && $assets->isEmpty())
                    <div class="user-empty-state">
                        <div class="user-empty-state__icon">
                            <x-heroicon-o-computer-desktop class="h-12 w-12 text-gray-300 dark:text-gray-500" />
                        </div>
                        <h3 class="user-empty-state__title">{{ __('No assets assigned to you') }}</h3>
                        <p class="user-empty-state__copy">{{ __('Contact your administrator if you believe this is an error.') }}</p>
                    </div>
                @elseif($assetFilter === 'active')
                    <div class="space-y-4">
                        @foreach($assets as $asset)
                            @php
                                $isReturnable = $asset->status === 'assigned';
                                $assetName = \Illuminate\Support\Str::lower($asset->name ?? '');
                                $assetTypeMeta = match ($asset->type) {
                                    'vehicle' => [
                                        'icon' => 'heroicon-o-truck',
                                        'classes' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/35 dark:text-sky-300',
                                    ],
                                    'furniture' => [
                                        'icon' => 'heroicon-o-building-office-2',
                                        'classes' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/35 dark:text-amber-300',
                                    ],
                                    'uniform' => [
                                        'icon' => 'heroicon-o-shield-check',
                                        'classes' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/35 dark:text-violet-300',
                                    ],
                                    default => [
                                        'icon' => \Illuminate\Support\Str::contains($assetName, ['iphone', 'phone', 'mobile', 'tablet', 'tab', 'ipad'])
                                            ? 'heroicon-o-device-phone-mobile'
                                            : 'heroicon-o-computer-desktop',
                                        'classes' => 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300',
                                    ],
                                };
                                $statusClasses = match ($asset->status) {
                                    'assigned' => 'bg-emerald-100 text-emerald-900 ring-emerald-700/20 dark:bg-emerald-900/30 dark:text-emerald-300',
                                    'maintenance' => 'bg-amber-100 text-amber-900 ring-amber-700/20 dark:bg-amber-900/30 dark:text-amber-300',
                                    'available' => 'bg-sky-100 text-sky-900 ring-sky-700/20 dark:bg-sky-900/30 dark:text-sky-300',
                                    default => 'bg-gray-100 text-gray-800 ring-gray-700/10 dark:bg-gray-800 dark:text-gray-200',
                                };
                            @endphp

                            @php
                                $expiryTone = $asset->expiration_date
                                    ? ($asset->isExpired()
                                        ? 'text-red-700 dark:text-red-300'
                                        : ($asset->isExpiringSoon()
                                            ? 'text-amber-700 dark:text-amber-300'
                                            : 'text-emerald-700 dark:text-emerald-300'))
                                    : 'text-gray-500 dark:text-gray-400';
                                $expiryLabel = $asset->expiration_date
                                    ? ($asset->isExpired()
                                        ? __('Expired')
                                        : ($asset->isExpiringSoon() ? __('Expiring') : __('Valid till')))
                                    : null;
                            @endphp

                            <article class="asset-mobile-card">
                                <div class="asset-mobile-card__top">
                                    <div class="asset-mobile-card__icon {{ $assetTypeMeta['classes'] }}">
                                        <x-dynamic-component :component="$assetTypeMeta['icon']" class="h-6 w-6" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <h3 class="asset-mobile-card__title">{{ $asset->name }}</h3>
                                                <p class="asset-mobile-card__serial">
                                                    {{ $asset->serial_number ?: __('No serial number') }}
                                                </p>
                                            </div>

                                            <span class="asset-mobile-card__status {{ $statusClasses }}">
                                                {{ $asset->displayStatus() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <dl class="asset-mobile-card__facts">
                                    <div class="asset-mobile-card__fact">
                                        <dt>{{ __('Asset Type') }}</dt>
                                        <dd>{{ filled($asset->type) ? __(ucfirst($asset->type)) : '—' }}</dd>
                                    </div>
                                    <div class="asset-mobile-card__fact">
                                        <dt>{{ __('Date Assigned') }}</dt>
                                        <dd>{{ $asset->date_assigned?->format('d M Y') ?? '—' }}</dd>
                                    </div>
                                    <div class="asset-mobile-card__fact">
                                        <dt>{{ __('Planned Return') }}</dt>
                                        <dd>{{ $asset->return_date?->format('d M Y') ?? '—' }}</dd>
                                    </div>
                                </dl>

                                <details class="asset-mobile-card__details">
                                    <summary>
                                        <span>{{ __('Details') }}</span>
                                        <x-heroicon-o-chevron-down class="h-4 w-4" />
                                    </summary>

                                    <dl class="asset-mobile-card__detail-list">
                                        <div>
                                            <dt>{{ __('Purchase Date') }}</dt>
                                            <dd>{{ $asset->purchase_date?->format('d M Y') ?? '—' }}</dd>
                                        </div>
                                        <div>
                                            <dt>{{ __('Expiration Date') }}</dt>
                                            <dd>
                                                <span>{{ $asset->expiration_date?->format('d M Y') ?? '—' }}</span>
                                                @if($expiryLabel)
                                                    <span class="ms-2 text-xs font-bold uppercase tracking-[0.14em] {{ $expiryTone }}">{{ $expiryLabel }}</span>
                                                @endif
                                            </dd>
                                        </div>
                                        @if($asset->notes)
                                            <div>
                                                <dt>{{ __('Asset Notes') }}</dt>
                                                <dd>{{ $asset->notes }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                </details>

                                <div class="asset-mobile-card__actions">
                                    <p class="sr-only">{{ __('Return request uses OTP verification and is available only for assigned assets.') }}</p>

                                    @if($isReturnable)
                                        <x-actions.button
                                            type="button"
                                            wire:click="openReturnModal('{{ $asset->id }}')"
                                            class="!rounded-2xl !px-4 !py-2.5 !text-sm !font-semibold">
                                            <x-heroicon-m-arrow-path class="h-4 w-4" />
                                            {{ __('Request Return') }}
                                        </x-actions.button>
                                    @else
                                        <button
                                            type="button"
                                            disabled
                                            aria-label="{{ __('Request Return') }}"
                                            class="user-disabled-action">
                                            <x-heroicon-m-arrow-path class="h-4 w-4" />
                                            {{ __('Request Return') }}
                                        </button>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @elseif($returnedHistories->isEmpty())
                    <div class="user-empty-state">
                        <div class="user-empty-state__icon">
                            <x-heroicon-o-arrow-uturn-left class="h-8 w-8 text-gray-300 dark:text-gray-500" />
                        </div>
                        <h3 class="user-empty-state__title">{{ __('No returned asset history yet.') }}</h3>
                        <p class="sr-only">{{ __('Assets that you have already returned will appear here as history.') }}</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($returnedHistories as $history)
                            @php
                                $historyAssetName = \Illuminate\Support\Str::lower($history->asset?->name ?? '');
                                $historyTypeMeta = match ($history->asset?->type) {
                                    'vehicle' => [
                                        'icon' => 'heroicon-o-truck',
                                        'classes' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/35 dark:text-sky-300',
                                    ],
                                    'furniture' => [
                                        'icon' => 'heroicon-o-building-office-2',
                                        'classes' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/35 dark:text-amber-300',
                                    ],
                                    'uniform' => [
                                        'icon' => 'heroicon-o-shield-check',
                                        'classes' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/35 dark:text-violet-300',
                                    ],
                                    default => [
                                        'icon' => \Illuminate\Support\Str::contains($historyAssetName, ['iphone', 'phone', 'mobile', 'tablet', 'tab', 'ipad'])
                                            ? 'heroicon-o-device-phone-mobile'
                                            : 'heroicon-o-computer-desktop',
                                        'classes' => 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300',
                                    ],
                                };
                            @endphp
                            <article class="asset-mobile-card">
                                <div class="asset-mobile-card__top">
                                    <div class="asset-mobile-card__icon {{ $historyTypeMeta['classes'] }}">
                                        <x-dynamic-component :component="$historyTypeMeta['icon']" class="h-6 w-6" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <h3 class="asset-mobile-card__title">
                                                    {{ $history->asset?->name ?? __('Deleted asset record') }}
                                                </h3>
                                                <p class="asset-mobile-card__serial">
                                                    {{ $history->asset?->serial_number ?: __('No serial number') }}
                                                </p>
                                            </div>

                                            <span class="asset-mobile-card__status bg-sky-100 text-sky-900 ring-sky-700/20 dark:bg-sky-900/30 dark:text-sky-300">
                                                {{ __('Returned') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <dl class="asset-mobile-card__facts">
                                    <div class="asset-mobile-card__fact">
                                        <dt>{{ __('Asset Type') }}</dt>
                                        <dd>{{ filled($history->asset?->type) ? __(ucfirst($history->asset->type)) : '—' }}</dd>
                                    </div>
                                    <div class="asset-mobile-card__fact sm:col-span-2">
                                        <dt>{{ __('Returned On') }}</dt>
                                        <dd>{{ $history->date?->format('d M Y H:i') ?? $history->created_at?->format('d M Y H:i') ?? '—' }}</dd>
                                    </div>
                                </dl>

                                @if($history->notes)
                                    <details class="asset-mobile-card__details">
                                        <summary>
                                            <span>{{ __('Details') }}</span>
                                            <x-heroicon-o-chevron-down class="h-4 w-4" />
                                        </summary>
                                        <dl class="asset-mobile-card__detail-list">
                                            <div>
                                                <dt>{{ __('Note') }}</dt>
                                                <dd>{{ $history->notes }}</dd>
                                            </div>
                                        </dl>
                                    </details>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>

    <x-overlays.dialog-modal wire:model.live="showReturnModal">
        <x-slot name="title">
            {{ __('Confirm Asset Return') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                @if($selectedAssetName)
                    <div class="rounded-2xl border border-primary-100 bg-primary-50 px-4 py-3 dark:border-primary-900/40 dark:bg-primary-950/25">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-700 dark:text-primary-300">{{ __('Asset Name') }}</p>
                        <p class="mt-1 text-sm font-semibold text-primary-950 dark:text-white">{{ $selectedAssetName }}</p>
                    </div>
                @endif

                @if(!$otpRequested)
                    <p class="sr-only">
                        {{ __('To return this asset, an OTP code will be sent to your immediate supervisor or the administrator. You must acquire this 6-digit code from them to confirm the handover.') }}
                    </p>
                @else
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
                        <p class="font-semibold">{{ __('OTP expires in 15 minutes.') }}</p>
                        <p class="mt-1">{{ __('An OTP code has been sent. Please contact your manager or administrator, ask for the code, and enter it below to finalize the return.') }}</p>
                    </div>

                    <div>
                        <x-forms.label for="otpCode" value="{{ __('Enter 6-Digit OTP Code') }}" />
                        <x-forms.input
                            id="otpCode"
                            type="text"
                            wire:model.live="otpCode"
                            maxlength="6"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            autocomplete="one-time-code"
                            class="mt-2 block w-full rounded-2xl py-3 text-center font-mono text-xl tracking-[0.35em]"
                            placeholder="------"
                            autofocus />
                        <x-forms.input-error for="otpCode" class="mt-2" />
                    </div>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-actions.secondary-button wire:click="closeReturnModal" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-actions.secondary-button>

            @if(!$otpRequested)
                <x-actions.button class="ms-3" wire:click="requestOtp" wire:loading.attr="disabled">
                    {{ __('Request OTP') }}
                </x-actions.button>
            @else
                <x-actions.button class="ms-3" wire:click="verifyOtp" wire:loading.attr="disabled">
                    {{ __('Confirm Return') }}
                </x-actions.button>
            @endif
        </x-slot>
    </x-overlays.dialog-modal>
</div>
