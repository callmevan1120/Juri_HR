<div>
    <x-admin.page-shell
        :title="__('Active User Sessions')"
        :description="__('Disconnect stuck browser sessions when users cannot log in because their account is still active on another device.')"
    >
        <x-slot name="toolbar">
            <x-admin.page-tools grid-class="grid grid-cols-1 items-end gap-4 lg:grid-cols-[minmax(0,1fr)_auto]">
                <div>
                    <x-forms.label for="user-session-search" value="{{ __('Search users') }}" class="mb-1.5 block" />
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-slate-500">
                            <x-heroicon-m-magnifying-glass class="h-5 w-5" />
                        </span>
                        <x-forms.input
                            id="user-session-search"
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            class="w-full pl-11"
                            placeholder="{{ __('Search name, email, phone, or NIP...') }}"
                        />
                    </div>
                </div>

                <div class="rounded-xl border border-emerald-100 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-100">
                    <p class="font-semibold">{{ __('Database session guard') }}</p>
                    <p class="mt-0.5 text-xs text-emerald-700 dark:text-emerald-200">
                        {{ __('This tool only affects session rows, not passwords or account status.') }}
                    </p>
                </div>
            </x-admin.page-tools>
        </x-slot>

        @unless ($databaseSessionsAvailable)
            <x-admin.panel class="border-amber-200 bg-amber-50/80 p-4 text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-100">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-exclamation-triangle class="mt-0.5 h-6 w-6 shrink-0" />
                    <div>
                        <h2 class="font-semibold">{{ __('Database sessions are not active') }}</h2>
                        <p class="mt-1 text-sm">{{ __('Set SESSION_DRIVER=database to manage stuck active sessions from this page.') }}</p>
                    </div>
                </div>
            </x-admin.panel>
        @endunless

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(280px,0.8fr)_minmax(0,1.2fr)]">
            <x-admin.panel>
                <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-700/70">
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">{{ __('Users With Active Sessions') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Only non-expired database sessions are shown.') }}</p>
                </div>

                <div class="max-h-[34rem] space-y-2 overflow-y-auto p-3">
                    @forelse ($users as $user)
                        <button
                            type="button"
                            wire:click="selectUser('{{ $user->id }}')"
                            class="w-full rounded-xl border px-4 py-3 text-left transition hover:border-emerald-300 hover:bg-emerald-50/70 dark:hover:border-emerald-700 dark:hover:bg-emerald-950/30 {{ $selectedUser?->is($user) ? 'border-emerald-400 bg-emerald-50 shadow-sm dark:border-emerald-700 dark:bg-emerald-950/40' : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900' }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-950 dark:text-white">{{ $user->name }}</p>
                                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <x-admin.status-badge tone="{{ $user->isSuperadmin ? 'danger' : ($user->isAdmin ? 'primary' : 'neutral') }}">
                                            {{ ucfirst($user->group) }}
                                        </x-admin.status-badge>
                                        @if ($user->company?->name)
                                            <x-admin.status-badge tone="neutral">{{ $user->company->name }}</x-admin.status-badge>
                                        @endif
                                    </div>
                                </div>
                                <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-full bg-slate-100 px-2 text-sm font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                    {{ (int) $user->active_sessions_count }}
                                </span>
                            </div>
                        </button>
                    @empty
                        <x-admin.empty-state
                            :title="__('No stuck sessions found')"
                            :description="__('Try another search term, or the active session may have already expired.')"
                            class="border-0 bg-transparent p-6 shadow-none dark:bg-transparent"
                        >
                            <x-slot name="icon">
                                <x-heroicon-o-device-phone-mobile class="h-12 w-12 text-slate-300 dark:text-slate-600" />
                            </x-slot>
                        </x-admin.empty-state>
                    @endforelse
                </div>
            </x-admin.panel>

            <x-admin.panel>
                <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-700/70">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">{{ __('Session Detail') }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $selectedUser ? __('Disconnect one device or clear every active session for this user.') : __('Select a user from the left panel first.') }}
                            </p>
                        </div>

                        @if ($selectedUser && $activeSessions->isNotEmpty())
                            <x-actions.button
                                type="button"
                                variant="danger"
                                wire:click="forgetAllSessions"
                                wire:confirm="{{ __('Disconnect all active sessions for this user?') }}"
                                label="{{ __('Disconnect All') }}"
                            >
                                <x-heroicon-m-no-symbol class="h-5 w-5" />
                                <span>{{ __('Disconnect All') }}</span>
                            </x-actions.button>
                        @endif
                    </div>
                </div>

                <div class="space-y-3 p-4">
                    @if (! $selectedUser)
                        <x-admin.empty-state
                            :title="__('Choose a user')"
                            :description="__('Use this page when a user sees “account is still active on another device” and cannot access the app.')"
                            class="border-0 bg-transparent p-8 shadow-none dark:bg-transparent"
                        >
                            <x-slot name="icon">
                                <x-heroicon-o-lock-open class="h-12 w-12 text-slate-300 dark:text-slate-600" />
                            </x-slot>
                        </x-admin.empty-state>
                    @elseif ($activeSessions->isEmpty())
                        <x-admin.empty-state
                            :title="__('No active session remains')"
                            :description="__('The user can try logging in again now.')"
                            class="border-0 bg-transparent p-8 shadow-none dark:bg-transparent"
                        >
                            <x-slot name="icon">
                                <x-heroicon-o-check-circle class="h-12 w-12 text-emerald-400 dark:text-emerald-500" />
                            </x-slot>
                        </x-admin.empty-state>
                    @else
                        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/50">
                            <div class="flex flex-wrap items-center gap-3">
                                <img src="{{ $selectedUser->profile_photo_url }}" alt="" class="h-12 w-12 rounded-full object-cover">
                                <div>
                                    <p class="font-semibold text-slate-950 dark:text-white">{{ $selectedUser->name }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $selectedUser->email }}</p>
                                </div>
                            </div>
                        </div>

                        @foreach ($activeSessions as $session)
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <x-heroicon-o-computer-desktop class="h-5 w-5 text-slate-400" />
                                            <p class="font-semibold text-slate-950 dark:text-white">{{ $session['user_agent'] }}</p>
                                            @if ($session['is_current_device'])
                                                <x-admin.status-badge tone="primary">{{ __('This device') }}</x-admin.status-badge>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                                            <span>{{ __('IP: :ip', ['ip' => $session['ip_address'] ?: __('Unknown')]) }}</span>
                                            <span>{{ __('Last active: :time', ['time' => $session['last_activity']->diffForHumans()]) }}</span>
                                        </div>
                                    </div>

                                    @if ($session['is_current_device'])
                                        <x-actions.button
                                            type="button"
                                            variant="soft-danger"
                                            label="{{ __('Current admin session cannot be disconnected here') }}"
                                            disabled
                                        >
                                            <x-heroicon-m-lock-closed class="h-5 w-5" />
                                            <span>{{ __('Protected') }}</span>
                                        </x-actions.button>
                                    @else
                                        <x-actions.button
                                            type="button"
                                            variant="danger"
                                            wire:click="forgetSession('{{ $session['id'] }}')"
                                            wire:confirm="{{ __('Disconnect this session?') }}"
                                            label="{{ __('Disconnect') }}"
                                        >
                                            <x-heroicon-m-x-mark class="h-5 w-5" />
                                            <span>{{ __('Disconnect') }}</span>
                                        </x-actions.button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </x-admin.panel>
        </div>
    </x-admin.page-shell>
</div>
