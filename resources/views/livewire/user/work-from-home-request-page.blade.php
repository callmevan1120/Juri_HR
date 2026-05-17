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
            </x-user.page-header>

            <div class="user-page-body bg-gray-50/50 dark:bg-gray-900/20">
                @include('components.feedback.alert-messages')

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-[360px_minmax(0,1fr)]">
                    <form wire:submit.prevent="submit" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h2 class="text-base font-bold text-gray-950 dark:text-white">{{ __('New Request') }}</h2>
                        <div class="mt-4 space-y-3">
                            <div>
                                <x-forms.label for="wfh-date" value="{{ __('Date') }}" class="mb-1.5 block" />
                                <x-forms.input id="wfh-date" type="date" wire:model.live="date" />
                                <x-forms.input-error for="date" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <x-forms.label for="wfh-start" value="{{ __('Start') }}" class="mb-1.5 block" />
                                    <x-forms.input id="wfh-start" type="time" wire:model.live="startTime" />
                                    <x-forms.input-error for="startTime" class="mt-2" />
                                </div>
                                <div>
                                    <x-forms.label for="wfh-end" value="{{ __('End') }}" class="mb-1.5 block" />
                                    <x-forms.input id="wfh-end" type="time" wire:model.live="endTime" />
                                    <x-forms.input-error for="endTime" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <x-forms.label for="wfh-location" value="{{ __('Location') }}" class="mb-1.5 block" />
                                <x-forms.input id="wfh-location" wire:model.live="locationAddress" placeholder="{{ __('Home address or work location') }}" />
                                <x-forms.input-error for="locationAddress" class="mt-2" />
                            </div>

                            <div>
                                <x-forms.label for="wfh-reason" value="{{ __('Reason') }}" class="mb-1.5 block" />
                                <x-forms.textarea id="wfh-reason" rows="4" wire:model.live="reason" placeholder="{{ __('Explain why you need to work from home...') }}" />
                                <x-forms.input-error for="reason" class="mt-2" />
                            </div>

                            <x-actions.button type="submit" class="w-full">
                                <x-heroicon-m-paper-airplane class="h-5 w-5" />
                                <span>{{ __('Submit WFH Request') }}</span>
                            </x-actions.button>
                        </div>
                    </form>

                    <div class="space-y-3">
                        @forelse ($requests as $request)
                            @php
                                $tone = match ($request->status) {
                                    \App\Models\WorkFromHomeRequest::STATUS_APPROVED => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200',
                                    \App\Models\WorkFromHomeRequest::STATUS_REJECTED => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
                                    default => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200',
                                };
                            @endphp
                            <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h3 class="text-base font-bold text-gray-950 dark:text-white">{{ $request->date?->translatedFormat('d M Y') }}</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $request->start_time ?: '--:--' }} - {{ $request->end_time ?: '--:--' }}
                                            @if ($request->location_address)
                                                · {{ $request->location_address }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide {{ $tone }}">
                                        {{ __(str($request->status)->headline()->toString()) }}
                                    </span>
                                </div>
                                <p class="mt-3 text-sm text-gray-700 dark:text-gray-200">{{ $request->reason }}</p>
                                @if ($request->reviewer)
                                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
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
                                    <x-heroicon-o-home-modern class="h-12 w-12 text-gray-300 dark:text-gray-500" />
                                </div>
                                <h3 class="user-empty-state__title">{{ __('No WFH requests yet') }}</h3>
                                <p class="user-empty-state__copy">{{ __('Your submitted work-from-home requests will appear here.') }}</p>
                            </div>
                        @endforelse

                        <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
                            {{ $requests->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
