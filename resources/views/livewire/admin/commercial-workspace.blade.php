<x-admin.page-shell
    :title="__('Commercial Workspace')"
    :description="__('Manage products, stock movements, quotations, and invoices with company-scoped controls.')"
    :show-description="true"
>
    <x-slot name="toolbar">
        <x-admin.page-tools grid-class="grid grid-cols-1 items-end gap-3 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <x-forms.label for="commercial-search" value="{{ __('Search commercial records') }}" class="mb-1.5 block" />
                <x-forms.input id="commercial-search" type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search product, SKU, client, or opportunity...') }}" />
            </div>

            <div class="lg:col-span-5">
                <div class="grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1 text-xs font-semibold dark:bg-slate-800 sm:text-sm md:grid-cols-6">
                    @foreach ([
                        'pipeline' => __('Pipeline'),
                        'products' => __('Products'),
                        'stock' => __('Stock'),
                        'purchases' => __('Purchases'),
                        'quotations' => __('Quotations'),
                        'invoices' => __('Invoices'),
                    ] as $tab => $label)
                        <button
                            type="button"
                            wire:click="$set('activeTab', '{{ $tab }}')"
                            class="rounded-lg px-2.5 py-2 transition sm:px-3 {{ $activeTab === $tab ? 'bg-white text-primary-700 shadow-sm dark:bg-slate-950 dark:text-primary-300' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </x-admin.page-tools>
    </x-slot>

    <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Open Pipeline') }}</p>
            <p class="mt-2 text-xl font-bold text-slate-950 dark:text-white">Rp{{ number_format($salesSummary['open_value'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Weighted') }}</p>
            <p class="mt-2 text-xl font-bold text-primary-700 dark:text-primary-300">Rp{{ number_format($salesSummary['weighted_value'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Won') }}</p>
            <p class="mt-2 text-xl font-bold text-emerald-600 dark:text-emerald-300">Rp{{ number_format($salesSummary['won_value'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Overdue Follow-up') }}</p>
            <p class="mt-2 text-xl font-bold {{ $salesSummary['overdue_follow_ups'] > 0 ? 'text-amber-600 dark:text-amber-300' : 'text-slate-950 dark:text-white' }}">
                {{ number_format($salesSummary['overdue_follow_ups'], 0, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="order-2 space-y-4 xl:order-1">
            @if ($activeTab === 'pipeline')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Sales Pipeline') }}</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4 xl:grid-cols-2">
                        @forelse ($opportunities as $opportunity)
                            @php
                                $isOverdue = $opportunity->next_follow_up_at && $opportunity->next_follow_up_at->isPast() && ! in_array($opportunity->stage, [\App\Models\SalesOpportunity::STAGE_WON, \App\Models\SalesOpportunity::STAGE_LOST], true);
                            @endphp
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-slate-950 dark:text-white">{{ $opportunity->title }}</h3>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            {{ $opportunity->company?->name }}
                                            @if ($opportunity->client)
                                                · {{ $opportunity->client->name }}
                                            @endif
                                            @if ($opportunity->project)
                                                · {{ $opportunity->project->name }}
                                            @endif
                                        </p>
                                        @if ($opportunity->quotations->isNotEmpty())
                                            <p class="mt-2 text-xs font-semibold text-primary-700 dark:text-primary-300">
                                                {{ __('Quotation') }}: {{ $opportunity->quotations->first()->number }}
                                            </p>
                                        @endif
                                    </div>
                                    <x-admin.status-badge :tone="$opportunity->stage === \App\Models\SalesOpportunity::STAGE_WON ? 'success' : ($opportunity->stage === \App\Models\SalesOpportunity::STAGE_LOST ? 'danger' : 'primary')">
                                        {{ __(str($opportunity->stage)->headline()->toString()) }}
                                    </x-admin.status-badge>
                                </div>

                                <dl class="mt-4 grid grid-cols-2 gap-2 text-sm">
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/50">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Value') }}</dt>
                                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">Rp{{ number_format((float) $opportunity->expected_value, 0, ',', '.') }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/50">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Probability') }}</dt>
                                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $opportunity->probability }}%</dd>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/50">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Close') }}</dt>
                                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $opportunity->expected_close_at?->format('d M Y') ?? '-' }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/50">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Follow-up') }}</dt>
                                        <dd class="mt-1 font-semibold {{ $isOverdue ? 'text-amber-600 dark:text-amber-300' : 'text-slate-900 dark:text-white' }}">
                                            {{ $opportunity->next_follow_up_at?->format('d M Y') ?? '-' }}
                                        </dd>
                                    </div>
                                </dl>

                                @if ($opportunity->followUps->isNotEmpty())
                                    <div class="mt-4 space-y-2">
                                        @foreach ($opportunity->followUps->take(2) as $followUp)
                                            @php
                                                $followUpOverdue = $followUp->status === \App\Models\SalesFollowUp::STATUS_PENDING
                                                    && $followUp->due_at
                                                    && $followUp->due_at->isPast();
                                            @endphp
                                            <div class="flex flex-col gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs dark:border-slate-800 dark:bg-slate-950/50 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="min-w-0">
                                                    <div class="font-semibold {{ $followUpOverdue ? 'text-amber-700 dark:text-amber-300' : 'text-slate-700 dark:text-slate-200' }}">
                                                        {{ __('Follow-up') }} · {{ $followUp->due_at?->format('d M Y') ?? __('No date') }}
                                                    </div>
                                                    @if ($followUp->notes)
                                                        <div class="mt-0.5 truncate text-slate-500 dark:text-slate-400">{{ $followUp->notes }}</div>
                                                    @endif
                                                </div>
                                                @if ($canManage && $followUp->status === \App\Models\SalesFollowUp::STATUS_PENDING)
                                                    <button
                                                        type="button"
                                                        wire:click="completeFollowUp({{ $followUp->id }})"
                                                        class="inline-flex items-center justify-center rounded-md bg-white px-2.5 py-1.5 font-semibold text-emerald-700 ring-1 ring-emerald-200 transition hover:bg-emerald-50 dark:bg-slate-900 dark:text-emerald-300 dark:ring-emerald-900/50 dark:hover:bg-emerald-950/40"
                                                    >
                                                        {{ __('Done') }}
                                                    </button>
                                                @else
                                                    <span class="rounded-md bg-emerald-100 px-2.5 py-1.5 font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                                                        {{ __(str($followUp->status)->headline()->toString()) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($canManage)
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            wire:click="createQuotationFromOpportunity({{ $opportunity->id }})"
                                            class="rounded-lg border border-primary-200 bg-primary-50 px-2.5 py-1.5 text-xs font-semibold text-primary-700 transition hover:border-primary-300 hover:bg-primary-100 dark:border-primary-900/50 dark:bg-primary-950/40 dark:text-primary-300 dark:hover:bg-primary-900/40"
                                        >
                                            {{ $opportunity->quotations->isNotEmpty() ? __('Open Quotation') : __('Create Quotation') }}
                                        </button>
                                        @foreach ($opportunityStages as $stage)
                                            @continue($stage === $opportunity->stage)
                                            <button
                                                type="button"
                                                wire:click="moveOpportunityStage({{ $opportunity->id }}, '{{ $stage }}')"
                                                class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-primary-300 hover:text-primary-700 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-600 dark:hover:text-primary-300"
                                            >
                                                {{ __(str($stage)->headline()->toString()) }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @empty
                            <x-admin.empty-state :title="__('No sales opportunities yet')" :description="__('Create opportunities from the active action panel to start sales tracking.')" class="border-0 bg-transparent shadow-none" />
                        @endforelse
                    </div>
                </x-admin.panel>
            @elseif ($activeTab === 'products' || $activeTab === 'stock')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ $activeTab === 'stock' ? __('Stock Overview') : __('Products') }}</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2">
                        @forelse ($products as $product)
                            @php
                                $stockBalance = $product->stockBalance();
                                $isLowStock = $product->isLowStock();
                            @endphp
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-slate-950 dark:text-white">{{ $product->name }}</h3>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            {{ $product->company?->name }}
                                            @if ($product->sku)
                                                · {{ $product->sku }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <x-admin.status-badge tone="success">{{ __(str($product->status)->headline()->toString()) }}</x-admin.status-badge>
                                        @if ($isLowStock)
                                            <x-admin.status-badge tone="warning">{{ __('Low Stock') }}</x-admin.status-badge>
                                        @endif
                                    </div>
                                </div>

                                <dl class="mt-4 grid grid-cols-3 gap-2 text-sm">
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/50">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Price') }}</dt>
                                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">Rp{{ number_format((float) $product->selling_price, 0, ',', '.') }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/50">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Cost') }}</dt>
                                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">Rp{{ number_format((float) $product->cost_price, 0, ',', '.') }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/50">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Stock') }}</dt>
                                        <dd class="mt-1 font-semibold {{ $isLowStock ? 'text-amber-600 dark:text-amber-300' : 'text-slate-900 dark:text-white' }}">{{ number_format($stockBalance, 3, ',', '.') }} {{ $product->unit }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950/50">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Min') }}</dt>
                                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ number_format((float) $product->reorder_point, 3, ',', '.') }} {{ $product->unit }}</dd>
                                    </div>
                                </dl>

                                @if ($activeTab === 'stock' && $product->stockMovements->isNotEmpty())
                                    <div class="mt-3 space-y-2">
                                        @foreach ($product->stockMovements->take(3) as $movement)
                                            <div class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600 dark:bg-slate-950/50 dark:text-slate-300">
                                                <span class="font-semibold">{{ __(str($movement->type)->headline()->toString()) }}</span>
                                                · {{ number_format((float) $movement->quantity, 3, ',', '.') }}
                                                · {{ $movement->occurred_at?->format('d M Y H:i') }}
                                                @if ($movement->metadata['accounting_journal_entry_id'] ?? null)
                                                    · {{ __('Journal #:id', ['id' => $movement->metadata['accounting_journal_entry_id']]) }}
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @empty
                            <x-admin.empty-state :title="__('No products yet')" :description="__('Create products from the active action panel.')" class="border-0 bg-transparent shadow-none">
                                <x-slot name="icon">
                                    <x-heroicon-o-cube class="h-12 w-12 text-slate-300 dark:text-slate-600" />
                                </x-slot>
                            </x-admin.empty-state>
                        @endforelse
                    </div>
                </x-admin.panel>
            @elseif ($activeTab === 'purchases')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Vendor Bills & AP') }}</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4">
                        @forelse ($vendorBills as $bill)
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <h3 class="font-semibold text-slate-950 dark:text-white">{{ $bill->number }}</h3>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            {{ $bill->company?->name }} · {{ $bill->vendor?->name }}
                                            @if ($bill->due_at)
                                                · {{ __('Due') }} {{ $bill->due_at->format('d M Y') }}
                                            @endif
                                        </p>
                                        @if ($bill->accounting_journal_entry_id)
                                            <p class="mt-2 text-xs font-semibold text-primary-700 dark:text-primary-300">
                                                {{ __('AP journal #:id', ['id' => $bill->accounting_journal_entry_id]) }}
                                            </p>
                                        @endif
                                        @if ($bill->payment_journal_entry_id)
                                            <p class="mt-1 text-xs font-semibold text-emerald-600 dark:text-emerald-300">
                                                {{ __('Payment journal #:id', ['id' => $bill->payment_journal_entry_id]) }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-left md:text-right">
                                        <x-admin.status-badge :tone="$bill->status === \App\Models\VendorBill::STATUS_PAID ? 'success' : 'warning'">{{ __(str($bill->status)->headline()->toString()) }}</x-admin.status-badge>
                                        <p class="mt-2 text-sm font-bold text-slate-900 dark:text-white">Rp{{ number_format((float) $bill->grand_total, 0, ',', '.') }}</p>
                                        @if ($canManage && $bill->status !== \App\Models\VendorBill::STATUS_PAID)
                                            <x-actions.button
                                                type="button"
                                                wire:click="markVendorBillPaid({{ $bill->id }})"
                                                wire:confirm="{{ __('Mark this vendor bill as paid and post the payment journal?') }}"
                                                variant="soft-success"
                                                class="mt-3 w-full justify-center md:w-auto"
                                            >
                                                {{ __('Mark Paid') }}
                                            </x-actions.button>
                                        @endif
                                    </div>
                                </div>

                                @if ($bill->items->isNotEmpty())
                                    <div class="mt-4 space-y-2">
                                        @foreach ($bill->items as $item)
                                            <div class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600 dark:bg-slate-950/50 dark:text-slate-300">
                                                <span class="font-semibold">{{ $item->description }}</span>
                                                @if ($item->product)
                                                    · {{ $item->product->name }}
                                                @endif
                                                · {{ number_format((float) $item->quantity, 3, ',', '.') }} x Rp{{ number_format((float) $item->unit_cost, 0, ',', '.') }}
                                                · Rp{{ number_format((float) $item->line_total, 0, ',', '.') }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @empty
                            <x-admin.empty-state :title="__('No vendor bills yet')" :description="__('Create vendor bills from the active action panel to start AP tracking.')" class="border-0 bg-transparent shadow-none" />
                        @endforelse
                    </div>
                </x-admin.panel>
            @elseif ($activeTab === 'quotations')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Quotations') }}</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4">
                        @forelse ($quotations as $quotation)
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <h3 class="font-semibold text-slate-950 dark:text-white">{{ $quotation->number }}</h3>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            {{ $quotation->company?->name }}
                                            @if ($quotation->client)
                                                · {{ $quotation->client->name }}
                                            @endif
                                            @if ($quotation->project)
                                                · {{ $quotation->project->name }}
                                            @endif
                                        </p>
                                        @if ($quotation->metadata['converted_invoice_id'] ?? null)
                                            <p class="mt-2 text-xs font-semibold text-emerald-600 dark:text-emerald-300">
                                                {{ __('Converted to invoice #:id', ['id' => $quotation->metadata['converted_invoice_id']]) }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-left md:text-right">
                                        <x-admin.status-badge tone="primary">{{ __(str($quotation->status)->headline()->toString()) }}</x-admin.status-badge>
                                        <p class="mt-2 text-sm font-bold text-slate-900 dark:text-white">Rp{{ number_format((float) $quotation->grand_total, 0, ',', '.') }}</p>
                                        @if ($canManage)
                                            <x-actions.button
                                                type="button"
                                                wire:click="convertQuotationToInvoice({{ $quotation->id }})"
                                                wire:confirm="{{ __('Convert this quotation to an invoice?') }}"
                                                variant="soft-success"
                                                class="mt-3 w-full justify-center md:w-auto"
                                            >
                                                {{ __('Convert to Invoice') }}
                                            </x-actions.button>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <x-admin.empty-state :title="__('No quotations yet')" :description="__('Create quotations from the document form.')" class="border-0 bg-transparent shadow-none" />
                        @endforelse
                    </div>
                </x-admin.panel>
            @elseif ($activeTab === 'invoices')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Invoices') }}</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4">
                        @forelse ($invoices as $invoice)
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <h3 class="font-semibold text-slate-950 dark:text-white">{{ $invoice->number }}</h3>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            {{ $invoice->company?->name }}
                                            @if ($invoice->client)
                                                · {{ $invoice->client->name }}
                                            @endif
                                            @if ($invoice->project)
                                                · {{ $invoice->project->name }}
                                            @endif
                                        </p>
                                        @if ($invoice->metadata['accounting_journal_entry_id'] ?? null)
                                            <p class="mt-2 text-xs font-semibold text-emerald-600 dark:text-emerald-300">
                                                {{ __('Posted to accounting journal #:id', ['id' => $invoice->metadata['accounting_journal_entry_id']]) }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-left md:text-right">
                                        <x-admin.status-badge :tone="$invoice->status === \App\Models\Invoice::STATUS_PAID ? 'success' : 'warning'">{{ __(str($invoice->status)->headline()->toString()) }}</x-admin.status-badge>
                                        <p class="mt-2 text-sm font-bold text-slate-900 dark:text-white">Rp{{ number_format((float) $invoice->grand_total, 0, ',', '.') }}</p>
                                        @if ($canManage && $invoice->status !== \App\Models\Invoice::STATUS_PAID)
                                            <x-actions.button
                                                type="button"
                                                wire:click="markInvoicePaid({{ $invoice->id }})"
                                                wire:confirm="{{ __('Mark this invoice as paid and post the accounting journal?') }}"
                                                variant="soft-success"
                                                class="mt-3 w-full justify-center md:w-auto"
                                            >
                                                {{ __('Mark Paid') }}
                                            </x-actions.button>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <x-admin.empty-state :title="__('No invoices yet')" :description="__('Create invoices from the document form.')" class="border-0 bg-transparent shadow-none" />
                        @endforelse
                    </div>
                </x-admin.panel>
            @else
                <x-admin.empty-state :title="__('Select a commercial tab')" :description="__('Choose pipeline, products, stock, quotations, or invoices.')" />
            @endif
        </div>

        <div class="order-1 space-y-4 xl:order-2">
            @if ($canManage)
                <x-admin.panel class="border-primary-200 bg-primary-50/60 dark:border-primary-900/60 dark:bg-primary-950/20">
                    <div class="space-y-1 p-3.5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-primary-800 dark:text-primary-200">{{ __('Quick action') }}</p>
                        <p class="text-sm leading-5 text-primary-700 dark:text-primary-100">
                            {{ __('The form follows your selected tab so you only see the action you need right now.') }}
                        </p>
                    </div>
                </x-admin.panel>

                @if ($activeTab === 'pipeline')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Create Opportunity') }}</h2>
                        <p class="mt-1 text-sm leading-5 text-slate-500 dark:text-slate-400">{{ __('Track a lead, expected value, close date, and next follow-up in one step.') }}</p>
                    </div>
                    <form wire:submit.prevent="createOpportunity" class="space-y-3 p-4">
                        <x-forms.select id="opportunity-company" wire:model.live="opportunityCompanyId" class="w-full" aria-label="{{ __('Company') }}">
                            <option value="">{{ __('Company') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <x-forms.input-error for="opportunityCompanyId" />
                        <x-forms.select id="opportunity-client" wire:model.live="opportunityClientId" class="w-full" aria-label="{{ __('Client optional') }}">
                            <option value="">{{ __('Client optional') }}</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <x-forms.select id="opportunity-project" wire:model.live="opportunityProjectId" class="w-full" aria-label="{{ __('Project optional') }}">
                            <option value="">{{ __('Project optional') }}</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <x-forms.input wire:model.live="opportunityTitle" placeholder="{{ __('Opportunity title') }}" />
                        <x-forms.input-error for="opportunityTitle" />
                        <div class="grid grid-cols-1 gap-2">
                            <x-forms.select id="opportunity-stage" wire:model.live="opportunityStage" class="w-full" aria-label="{{ __('Stage') }}">
                                @foreach ($opportunityStages as $stage)
                                    <option value="{{ $stage }}">{{ __(str($stage)->headline()->toString()) }}</option>
                                @endforeach
                            </x-forms.select>
                            <x-forms.input type="number" min="0" step="0.01" wire:model.live="opportunityExpectedValue" placeholder="{{ __('Value') }}" />
                        </div>
                        <div class="grid grid-cols-1 gap-2">
                            <x-forms.input type="date" wire:model.live="opportunityExpectedCloseAt" />
                            <x-forms.input type="date" wire:model.live="opportunityNextFollowUpAt" />
                        </div>
                        <x-forms.input wire:model.live="opportunitySource" placeholder="{{ __('Source optional') }}" />
                        <x-forms.textarea wire:model.live="opportunityNotes" rows="2" placeholder="{{ __('Follow-up notes') }}" />
                        <x-actions.button type="submit" class="w-full">{{ __('Create Opportunity') }}</x-actions.button>
                    </form>
                </x-admin.panel>

                @elseif ($activeTab === 'products')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Create Product') }}</h2>
                        <p class="mt-1 text-sm leading-5 text-slate-500 dark:text-slate-400">{{ __('Add a sellable item with pricing, cost, unit, and reorder threshold.') }}</p>
                    </div>
                    <form wire:submit.prevent="createProduct" class="space-y-3 p-4">
                        <x-forms.select id="product-company" wire:model.live="productCompanyId" class="w-full" aria-label="{{ __('Company') }}">
                            <option value="">{{ __('Company') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <x-forms.input-error for="productCompanyId" />
                        <x-forms.input wire:model.live="productName" placeholder="{{ __('Product name') }}" />
                        <x-forms.input-error for="productName" />
                        <x-forms.input wire:model.live="productSku" placeholder="{{ __('SKU optional') }}" />
                        <div class="grid grid-cols-1 gap-2">
                            <x-forms.input wire:model.live="productUnit" placeholder="{{ __('Unit') }}" />
                            <x-forms.input type="number" min="0" step="0.01" wire:model.live="productSellingPrice" placeholder="{{ __('Selling price') }}" />
                        </div>
                        <div class="grid grid-cols-1 gap-2">
                            <x-forms.input type="number" min="0" step="0.01" wire:model.live="productCostPrice" placeholder="{{ __('Cost price') }}" />
                            <x-forms.input type="number" min="0" step="0.001" wire:model.live="productReorderPoint" placeholder="{{ __('Min stock') }}" />
                        </div>
                        <x-actions.button type="submit" class="w-full">
                            <x-heroicon-m-plus class="h-5 w-5" />
                            <span>{{ __('Create Product') }}</span>
                        </x-actions.button>
                    </form>
                </x-admin.panel>

                @elseif ($activeTab === 'stock')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Record Stock') }}</h2>
                        <p class="mt-1 text-sm leading-5 text-slate-500 dark:text-slate-400">{{ __('Record stock in, stock out, or adjustment with an accounting cost when needed.') }}</p>
                    </div>
                    <form wire:submit.prevent="recordStockMovement" class="space-y-3 p-4">
                        <x-forms.select id="stock-product" wire:model.live="stockProductId" class="w-full" aria-label="{{ __('Product') }}">
                            <option value="">{{ __('Product') }}</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <div class="grid grid-cols-1 gap-2">
                            <x-forms.select id="stock-type" wire:model.live="stockType" class="w-full" aria-label="{{ __('Stock type') }}">
                                <option value="{{ \App\Models\StockMovement::TYPE_IN }}">{{ __('Stock In') }}</option>
                                <option value="{{ \App\Models\StockMovement::TYPE_OUT }}">{{ __('Stock Out') }}</option>
                                <option value="{{ \App\Models\StockMovement::TYPE_ADJUSTMENT }}">{{ __('Adjustment') }}</option>
                            </x-forms.select>
                            <x-forms.input type="number" min="0.001" step="0.001" wire:model.live="stockQuantity" placeholder="{{ __('Qty') }}" />
                        </div>
                        <x-forms.input type="number" min="0" step="0.01" wire:model.live="stockUnitCost" placeholder="{{ __('Unit cost for accounting') }}" />
                        <x-forms.textarea wire:model.live="stockNotes" rows="2" placeholder="{{ __('Stock notes') }}" />
                        <x-actions.button type="submit" variant="soft-primary" class="w-full">{{ __('Record Stock') }}</x-actions.button>
                    </form>
                </x-admin.panel>

                @elseif ($activeTab === 'purchases')
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Create Vendor') }}</h2>
                        <p class="mt-1 text-sm leading-5 text-slate-500 dark:text-slate-400">{{ __('Add supplier contacts once, then reuse them for bills and AP tracking.') }}</p>
                    </div>
                    <form wire:submit.prevent="createVendor" class="space-y-3 p-4">
                        <x-forms.select id="vendor-company" wire:model.live="vendorCompanyId" class="w-full" aria-label="{{ __('Company') }}">
                            <option value="">{{ __('Company') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <x-forms.input-error for="vendorCompanyId" />
                        <x-forms.input wire:model.live="vendorName" placeholder="{{ __('Vendor name') }}" />
                        <x-forms.input-error for="vendorName" />
                        <x-forms.input wire:model.live="vendorContactName" placeholder="{{ __('Contact optional') }}" />
                        <div class="grid grid-cols-1 gap-2">
                            <x-forms.input type="email" wire:model.live="vendorEmail" placeholder="{{ __('Email optional') }}" />
                            <x-forms.input wire:model.live="vendorPhone" placeholder="{{ __('Phone optional') }}" />
                        </div>
                        <x-actions.button type="submit" variant="soft-primary" class="w-full">{{ __('Create Vendor') }}</x-actions.button>
                    </form>
                </x-admin.panel>

                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Post Vendor Bill') }}</h2>
                        <p class="mt-1 text-sm leading-5 text-slate-500 dark:text-slate-400">{{ __('Post a vendor bill to accounts payable and optionally connect it to stock.') }}</p>
                    </div>
                    <form wire:submit.prevent="createVendorBill" class="space-y-3 p-4">
                        <x-forms.select id="bill-vendor" wire:model.live="billVendorId" class="w-full" aria-label="{{ __('Vendor') }}">
                            <option value="">{{ __('Vendor') }}</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <x-forms.input-error for="billVendorId" />
                        <x-forms.select id="bill-product" wire:model.live="billProductId" class="w-full" aria-label="{{ __('Expense line / no product') }}">
                            <option value="">{{ __('Expense line / no product') }}</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <x-forms.input wire:model.live="billDescription" placeholder="{{ __('Bill line description') }}" />
                        <x-forms.input-error for="billDescription" />
                        <div class="grid grid-cols-1 gap-2">
                            <x-forms.input type="number" min="0.001" step="0.001" wire:model.live="billQuantity" placeholder="{{ __('Qty') }}" />
                            <x-forms.input type="number" min="0" step="0.01" wire:model.live="billUnitCost" placeholder="{{ __('Cost') }}" />
                            <x-forms.input type="number" min="0" max="100" step="0.01" wire:model.live="billTaxRate" placeholder="{{ __('Tax') }}" />
                        </div>
                        <x-forms.input type="date" wire:model.live="billDueAt" />
                        <x-forms.textarea wire:model.live="billNotes" rows="2" placeholder="{{ __('Bill notes') }}" />
                        <x-actions.button type="submit" variant="soft-success" class="w-full">{{ __('Post Bill to AP') }}</x-actions.button>
                    </form>
                </x-admin.panel>

                @elseif (in_array($activeTab, ['quotations', 'invoices'], true))
                <x-admin.panel>
                    <div class="border-b border-slate-200/70 px-4 py-3 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Create Quotation / Invoice') }}</h2>
                        <p class="mt-1 text-sm leading-5 text-slate-500 dark:text-slate-400">{{ __('Choose the customer, project, product line, and whether this should become a quotation or invoice.') }}</p>
                    </div>
                    <div class="space-y-3 p-4">
                        <x-forms.select id="document-company" wire:model.live="documentCompanyId" class="w-full" aria-label="{{ __('Company') }}">
                            <option value="">{{ __('Company') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <x-forms.select id="document-client" wire:model.live="documentClientId" class="w-full" aria-label="{{ __('Client optional') }}">
                            <option value="">{{ __('Client optional') }}</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <x-forms.select id="document-project" wire:model.live="documentProjectId" class="w-full" aria-label="{{ __('Project optional') }}">
                            <option value="">{{ __('Project optional') }}</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <x-forms.select id="document-product" wire:model.live="documentProductId" class="w-full" aria-label="{{ __('Product optional') }}">
                            <option value="">{{ __('Product optional') }}</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </x-forms.select>
                        <x-forms.input wire:model.live="documentDescription" placeholder="{{ __('Line description') }}" />
                        <div class="grid grid-cols-1 gap-2">
                            <x-forms.input type="number" min="0.001" step="0.001" wire:model.live="documentQuantity" placeholder="{{ __('Qty') }}" />
                            <x-forms.input type="number" min="0" step="0.01" wire:model.live="documentUnitPrice" placeholder="{{ __('Price') }}" />
                            <x-forms.input type="number" min="0" max="100" step="0.01" wire:model.live="documentTaxRate" placeholder="{{ __('Tax') }}" />
                        </div>
                        <x-forms.textarea wire:model.live="documentNotes" rows="2" placeholder="{{ __('Notes') }}" />
                        <div class="grid grid-cols-2 gap-2">
                            <x-actions.button type="button" wire:click="createQuotation" variant="soft-primary">{{ __('Quotation') }}</x-actions.button>
                            <x-actions.button type="button" wire:click="createInvoice" variant="soft-success">{{ __('Invoice') }}</x-actions.button>
                        </div>
                    </div>
                </x-admin.panel>
                @endif
            @else
                <x-admin.alert tone="info">
                    {{ __('You can view commercial records, but need manage permission to create products, stock movements, quotations, or invoices.') }}
                </x-admin.alert>
            @endif
        </div>
    </div>
</x-admin.page-shell>
