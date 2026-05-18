<div class="user-page-shell">
    <div class="user-page-container user-page-container--wide">
        <div class="user-page-surface">
            <x-user.page-header
                :back-href="route('home')"
                :title="__('Team Kasbon')"
                title-id="team-kasbon-title"
                class="border-b-0">
                <x-slot name="icon">
                    <x-heroicon-o-wallet class="h-5 w-5" />
                </x-slot>
            </x-user.page-header>

            <div class="user-page-body pt-0">
                <div class="user-compact-filter mb-4">
                    <div class="user-filter-grid lg:max-w-2xl">
                        <div class="relative w-full">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <x-heroicon-m-magnifying-glass class="h-5 w-5 text-gray-400" />
                            </div>
                            <x-forms.input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('Search Employee...') }}" class="block w-full py-2.5 pl-10 sm:text-sm sm:leading-6" />
                        </div>
                        @if($activeTab === 'requests')
                        <x-forms.select wire:model.live="statusFilter" class="block w-full py-2.5 pl-3 pr-10 text-gray-900 dark:text-white sm:text-sm sm:leading-6">
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="approved">{{ __('Approved') }}</option>
                            <option value="rejected">{{ __('Rejected') }}</option>
                            <option value="paid">{{ __('Paid') }}</option>
                            <option value="all">{{ __('All Status') }}</option>
                        </x-forms.select>
                        @endif
                    </div>
                </div>

                @include('livewire.shared.finance.cash-advance-manager-content')
            </div>
        </div>
    </div>
</div>
