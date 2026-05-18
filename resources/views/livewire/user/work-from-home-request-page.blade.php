<div class="user-page-shell">
    <div class="user-page-container user-page-container--wide">
        <section class="user-page-surface" aria-labelledby="wfh-request-title">
            <x-user.page-header
                :back-href="route('home')"
                :title="__('WFH Request')"
                :description="__('Request work-from-home approval with date, time, location, and reason.')"
                title-id="wfh-request-title">
                <x-slot name="icon">
                    <x-heroicon-o-home-modern class="h-5 w-5" />
                </x-slot>
                <x-slot name="actions">
                    <button type="button" wire:click="create" class="wcag-touch-target inline-flex items-center justify-center gap-2 rounded-2xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-none transition hover:bg-primary-700" aria-label="{{ __('New Request') }}">
                        <x-heroicon-o-plus class="h-5 w-5" />
                        <span>{{ __('New Request') }}</span>
                    </button>
                </x-slot>
            </x-user.page-header>

            <div class="user-page-body bg-gray-50/50 dark:bg-gray-900/20">
                @include('components.feedback.alert-messages')

                <div class="wfh-request-list">
                        @forelse ($requests as $request)
                            @php
                                $tone = match ($request->status) {
                                    \App\Models\WorkFromHomeRequest::STATUS_APPROVED => 'wfh-request-item__status--approved',
                                    \App\Models\WorkFromHomeRequest::STATUS_REJECTED => 'wfh-request-item__status--rejected',
                                    default => 'wfh-request-item__status--pending',
                                };
                            @endphp
                            <article class="wfh-request-item">
                                <div class="wfh-request-item__top">
                                    <div class="wfh-request-item__date">
                                        <span class="wfh-request-item__day">{{ $request->date?->translatedFormat('d') }}</span>
                                        <span class="wfh-request-item__month">{{ $request->date?->translatedFormat('M') }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <h3 class="wfh-request-item__title">{{ $request->date?->translatedFormat('l') }}</h3>
                                                <p class="wfh-request-item__meta">
                                                    {{ $request->start_time ?: '--:--' }} - {{ $request->end_time ?: '--:--' }}
                                                </p>
                                            </div>
                                            <span class="wfh-request-item__status {{ $tone }}">
                                                {{ __(str($request->status)->headline()->toString()) }}
                                            </span>
                                        </div>

                                        @if ($request->location_address)
                                            <p class="wfh-request-item__line">
                                                <x-heroicon-o-map-pin class="h-4 w-4" />
                                                <span>{{ $request->location_address }}</span>
                                            </p>
                                        @endif

                                        <p class="wfh-request-item__reason">{{ $request->reason }}</p>
                                    </div>
                                </div>

                                @if ($request->reviewer)
                                    <p class="wfh-request-item__review">
                                        {{ __('Reviewed by :name', ['name' => $request->reviewer->name]) }}
                                        @if ($request->review_note)
                                            · {{ $request->review_note }}
                                        @endif
                                    </p>
                                @endif
                            </article>
                        @empty
                            <div class="user-empty-state">
                                <div class="user-empty-state__icon">
                                    <x-heroicon-o-home-modern class="h-10 w-10" />
                                </div>
                                <h3 class="user-empty-state__title">{{ __('No WFH requests yet') }}</h3>
                                <p class="user-empty-state__copy">{{ __('Your submitted work-from-home requests will appear here.') }}</p>
                            </div>
                        @endforelse

                        @if ($requests->hasPages())
                            <div class="wfh-request-pagination">
                                {{ $requests->links() }}
                            </div>
                        @endif
                </div>
            </div>
        </section>

        <x-overlays.dialog-modal wire:model.live="showCreateModal">
            <x-slot name="title">{{ __('New Request') }}</x-slot>

            <x-slot name="content">
                <form wire:submit.prevent="submit" class="wfh-request-form wfh-request-form--modal">
                    <div class="wfh-request-form__header">
                        <div class="wfh-request-form__icon">
                            <x-heroicon-o-home-modern class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="wfh-request-form__title">{{ __('WFH Request') }}</h2>
                            <p class="wfh-request-form__copy">{{ __('Request work-from-home approval with date, time, location, and reason.') }}</p>
                        </div>
                    </div>

                    <div class="wfh-request-form__fields">
                        <x-user.native-date-field
                            id="wfh-date"
                            :label="__('Date')"
                            model="date"
                            error="date"
                            :min="now()->toDateString()"
                        />

                        <div class="grid grid-cols-2 gap-2.5">
                            <x-user.native-date-field
                                id="wfh-start"
                                :label="__('Start')"
                                model="startTime"
                                error="startTime"
                                type="time"
                            />
                            <x-user.native-date-field
                                id="wfh-end"
                                :label="__('End')"
                                model="endTime"
                                error="endTime"
                                type="time"
                            />
                        </div>

                        <x-user.native-text-field
                            id="wfh-location"
                            :label="__('Location')"
                            model="locationAddress"
                            error="locationAddress"
                            icon="heroicon-o-map-pin"
                            placeholder="{{ __('Home address or work location') }}"
                        />

                        <x-user.native-textarea-field
                            id="wfh-reason"
                            :label="__('Reason')"
                            model="reason"
                            error="reason"
                            placeholder="{{ __('Explain why you need to work from home...') }}"
                        />

                        <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
                            <x-actions.secondary-button type="button" wire:click="close" wire:loading.attr="disabled">
                                {{ __('Cancel') }}
                            </x-actions.secondary-button>
                            <button type="submit" class="wfh-submit-button sm:w-auto" aria-label="{{ __('Submit WFH Request') }}" wire:loading.attr="disabled">
                                <x-heroicon-m-paper-airplane class="h-5 w-5" />
                                <span>{{ __('Submit WFH Request') }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </x-slot>

            <x-slot name="footer"></x-slot>
        </x-overlays.dialog-modal>
    </div>
</div>
