<div>
    <x-admin.page-shell
        :title="__('API Integrations')"
        :description="__('Manage third-party clients that connect to PasPapan APIs.')"
    >
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(320px,0.8fr)_minmax(0,1.2fr)]">
            <x-admin.panel>
                <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-700/70">
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">{{ __('Create Integration Client') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Pick a template, enter the client name, then generate credentials.') }}</p>
                </div>

                <form wire:submit="save" class="space-y-4 p-4">
                    <div>
                        <x-forms.label for="integration-preset" value="{{ __('Integration Template') }}" />
                        <x-forms.select id="integration-preset" class="mt-1 w-full" wire:model.live="preset">
                            <option value="attendance">{{ __('Attendance machine / vendor write') }}</option>
                            <option value="hris_read">{{ __('HRIS read-only sync') }}</option>
                            <option value="schedule_read">{{ __('Schedule read-only sync') }}</option>
                            <option value="custom">{{ __('Custom scopes') }}</option>
                        </x-forms.select>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('The template fills sensible scopes. You can still adjust scopes before creating the client.') }}</p>
                        <x-forms.input-error for="preset" class="mt-2" />
                    </div>

                    <div>
                        <x-forms.label for="integration-name" value="{{ __('Client Name') }}" />
                        <x-forms.input id="integration-name" type="text" class="mt-1 w-full" wire:model="name" />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Example: Vendor Attendance Bridge. If source is empty, PasPapan will use a slug from this name.') }}</p>
                        <x-forms.input-error for="name" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <x-forms.label for="integration-contact-name" value="{{ __('Contact Name') }}" />
                            <x-forms.input id="integration-contact-name" type="text" class="mt-1 w-full" wire:model="contactName" />
                            <x-forms.input-error for="contactName" class="mt-2" />
                        </div>

                        <div>
                            <x-forms.label for="integration-contact-email" value="{{ __('Contact Email') }}" />
                            <x-forms.input id="integration-contact-email" type="email" class="mt-1 w-full" wire:model="contactEmail" />
                            <x-forms.input-error for="contactEmail" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-forms.label for="integration-abilities" value="{{ __('Scopes') }}" />
                        <div id="integration-abilities" class="mt-2 grid grid-cols-1 gap-2">
                            @foreach ($availableAbilities as $ability)
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-200">
                                    <x-forms.checkbox wire:model="abilities" value="{{ $ability }}" />
                                    <span>{{ $ability }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-forms.input-error for="abilities" class="mt-2" />
                    </div>

                    <div>
                        <x-forms.label for="integration-sources" value="{{ __('Allowed Sources') }}" />
                        <x-forms.textarea id="integration-sources" class="mt-1 w-full" rows="3" wire:model="allowedSourcesText" placeholder="{{ __('Auto from client name when empty') }}" />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Optional. Use this when one credential serves multiple machines or source codes.') }}</p>
                        <x-forms.input-error for="allowedSourcesText" class="mt-2" />
                    </div>

                    <div>
                        <x-forms.label for="integration-ips" value="{{ __('Allowed IPs') }}" />
                        <x-forms.textarea id="integration-ips" class="mt-1 w-full" rows="3" wire:model="allowedIpsText" placeholder="{{ __('Leave empty to allow any IP') }}" />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Optional but recommended when the vendor has fixed server IPs.') }}</p>
                        <x-forms.input-error for="allowedIpsText" class="mt-2" />
                    </div>

                    <div>
                        <x-forms.label for="integration-expires-at" value="{{ __('Expires At') }}" />
                        <x-forms.input id="integration-expires-at" type="date" class="mt-1 w-full" wire:model="expiresAt" />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Optional. Leave empty for long-running integrations, then rotate credentials periodically.') }}</p>
                        <x-forms.input-error for="expiresAt" class="mt-2" />
                    </div>

                    <x-actions.button type="submit" label="{{ __('Create Integration Client') }}">
                        <x-heroicon-m-key class="h-5 w-5" />
                        <span>{{ __('Create') }}</span>
                    </x-actions.button>
                </form>
            </x-admin.panel>

            <x-admin.panel>
                <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-700/70">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">{{ __('Integration Clients') }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Rotate or revoke credentials without touching user accounts.') }}</p>
                        </div>

                        <div class="relative w-full lg:max-w-xs">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <x-heroicon-m-magnifying-glass class="h-5 w-5" />
                            </span>
                            <x-forms.input type="search" class="w-full pl-10" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search clients...') }}" />
                        </div>
                    </div>
                </div>

                <div class="space-y-3 p-4">
                    @forelse ($clients as $client)
                        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-semibold text-slate-950 dark:text-white">{{ $client->name }}</h3>
                                        <x-admin.status-badge tone="{{ $client->isUsable() ? 'success' : 'danger' }}">
                                            {{ $client->isUsable() ? __('Active') : __('Inactive') }}
                                        </x-admin.status-badge>
                                    </div>

                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                                        @if ($client->contact_email)
                                            <span>{{ $client->contact_email }}</span>
                                        @endif
                                        <span>{{ $client->last_used_at ? __('Last used: :time', ['time' => $client->last_used_at->diffForHumans()]) : __('Never used') }}</span>
                                        <span>{{ $client->expires_at ? __('Expires: :time', ['time' => $client->expires_at->diffForHumans()]) : __('Never expires') }}</span>
                                    </div>

                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($client->abilities ?? [] as $ability)
                                            <x-admin.status-badge tone="neutral">{{ $ability }}</x-admin.status-badge>
                                        @endforeach
                                    </div>

                                    @if (($client->allowed_sources ?? []) !== [])
                                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Sources') }}: {{ implode(', ', $client->allowed_sources) }}</p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <x-actions.button
                                        type="button"
                                        variant="secondary"
                                        wire:click="rotateSecret('{{ $client->id }}')"
                                        wire:confirm="{{ __('Rotate this integration credential?') }}"
                                        label="{{ __('Rotate') }}"
                                    >
                                        <x-heroicon-m-arrow-path class="h-5 w-5" />
                                        <span>{{ __('Rotate') }}</span>
                                    </x-actions.button>

                                    @if ($client->revoked_at)
                                        <x-actions.button
                                            type="button"
                                            variant="secondary"
                                            wire:click="restore('{{ $client->id }}')"
                                            label="{{ __('Restore') }}"
                                        >
                                            <x-heroicon-m-check class="h-5 w-5" />
                                            <span>{{ __('Restore') }}</span>
                                        </x-actions.button>
                                    @else
                                        <x-actions.button
                                            type="button"
                                            variant="danger"
                                            wire:click="revoke('{{ $client->id }}')"
                                            wire:confirm="{{ __('Revoke this integration client?') }}"
                                            label="{{ __('Revoke') }}"
                                        >
                                            <x-heroicon-m-x-mark class="h-5 w-5" />
                                            <span>{{ __('Revoke') }}</span>
                                        </x-actions.button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-admin.empty-state
                            :title="__('No integration clients yet')"
                            :description="__('Create a client when a third party needs controlled API access.')"
                            class="border-0 bg-transparent p-8 shadow-none dark:bg-transparent"
                        >
                            <x-slot name="icon">
                                <x-heroicon-o-key class="h-12 w-12 text-slate-300 dark:text-slate-600" />
                            </x-slot>
                        </x-admin.empty-state>
                    @endforelse

                    {{ $clients->links() }}
                </div>
            </x-admin.panel>
        </div>
    </x-admin.page-shell>

    <x-overlays.dialog-modal wire:model.live="showCredentialModal">
        <x-slot name="title">
            {{ __('Integration Credentials') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Copy these credentials now. The secret will not be shown again.') }}</p>

                <div>
                    <x-forms.label value="{{ __('API Key') }}" />
                    <x-forms.input type="text" readonly class="mt-1 w-full font-mono text-sm" value="{{ $plainTextApiKey }}" />
                </div>

                <div>
                    <x-forms.label value="{{ __('API Secret') }}" />
                    <x-forms.input type="text" readonly class="mt-1 w-full font-mono text-sm" value="{{ $plainTextSecret }}" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-actions.secondary-button wire:click="$set('showCredentialModal', false)" wire:loading.attr="disabled">
                {{ __('Close') }}
            </x-actions.secondary-button>
        </x-slot>
    </x-overlays.dialog-modal>
</div>
