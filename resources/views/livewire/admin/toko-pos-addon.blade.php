<x-admin.page-shell
    :title="$pageTitle"
    data-toko-addon-flag="toko_pos"
    data-toko-nav-addon-flag="toko_pos"
>
    @php
        $idNumber = fn ($value, int $decimals = 0, bool $trimZeros = true) => \App\Helpers::formatNumberId($value, $decimals, $trimZeros);
        $idMoney = fn ($value, int $decimals = 0) => \App\Helpers::formatRupiah($value, $decimals);
        $idPercent = fn ($value, int $decimals = 2, bool $trimZeros = true) => \App\Helpers::formatPercentId($value, $decimals, $trimZeros);
        $idUnit = fn ($value, string $unit, int $decimals = 3) => \App\Helpers::formatUnitId($value, $unit, $decimals);
    @endphp

    <div data-toko-addon-flag="toko_pos" data-toko-nav-addon-flag="toko_pos" class="hidden">
        <span>feature: toko_pos</span>
        <span>module_type: addon</span>
        <span>license_feature: toko_pos</span>
        @foreach (($tokoNavigation ?? []) as $navigationItem)
            <span>{{ $navigationItem['href'] }}</span>
        @endforeach
    </div>

    @if (($companyOptions ?? []) !== [] || ($branchOptions ?? []) !== [])
        <x-slot name="actions">
            <div class="flex flex-col sm:flex-row items-center gap-2">
                @if (($companyOptions ?? []) !== [])
                    <div class="w-full sm:w-max sm:min-w-48">
                        <label class="sr-only" for="toko-company-selector">{{ __('Company') }}</label>
                        <x-forms.tom-select id="toko-company-selector" wire:model.live="selectedCompanyId" placeholder="{{ __('Company') }}" :disabled="count($companyOptions) === 1" dropdown-direction="down">
                            @foreach ($companyOptions as $companyOption)
                                <option value="{{ $companyOption['id'] }}">{{ $companyOption['name'] }}</option>
                            @endforeach
                        </x-forms.tom-select>
                    </div>
                @endif
                @if (($branchOptions ?? []) !== [])
                    <div class="w-full sm:w-max sm:min-w-48">
                        <label class="sr-only" for="toko-branch-selector">{{ __('Branch / Store') }}</label>
                        <x-forms.tom-select id="toko-branch-selector" wire:model.live="selectedBranchId" placeholder="{{ __('Semua branch/store') }}" dropdown-direction="down">
                            <option value="">{{ __('Semua branch/store') }}</option>
                            @foreach ($branchOptions as $branchOption)
                                <option value="{{ $branchOption['id'] }}">{{ $branchOption['label'] }}</option>
                            @endforeach
                        </x-forms.tom-select>
                    </div>
                @endif
            </div>
        </x-slot>
    @endif

    @include('livewire.admin.toko.dashboard')

    @include('livewire.admin.toko.products')

    @include('livewire.admin.toko.customers')

    @include('livewire.admin.toko.vendors')

    @include('livewire.admin.toko.cash')


    @include('livewire.admin.toko.pos')

    @include('livewire.admin.toko.delivery-letters')

    @if (in_array($activePage, ['inventory', 'returns', 'reports'], true))
        @include('livewire.admin.toko.inventory')

        @include('livewire.admin.toko.reports')
    @endif

    @include('livewire.admin.toko.quotations')

    @include('livewire.admin.toko.purchases')

    @include('livewire.admin.toko.migration')


    @once
        <script>
            window.renderTokoDashboardCharts = window.renderTokoDashboardCharts || function () {
                if (!window.Chart) {
                    return;
                }

                const formatId = (value, decimals = 0) => new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                }).format(Number(value || 0));

                const formatRp = (value) => `Rp${formatId(value, 0)}`;

                document.querySelectorAll('[data-toko-dashboard-charts]').forEach((root) => {
                    let payload = {};

                    try {
                        payload = JSON.parse(root.dataset.chartPayload || '{}');
                    } catch (error) {
                        payload = {};
                    }

	                    const draw = (selector, type, labels, values, color) => {
	                        const canvas = root.querySelector(selector);

                        if (!canvas) {
                            return;
                        }

                        const existing = Chart.getChart(canvas);
                        if (existing) {
                            existing.destroy();
                        }

                        new Chart(canvas, {
                            type,
                            data: {
                                labels: labels || [],
                                datasets: [{
                                    data: values || [],
                                    borderColor: color,
                                    backgroundColor: type === 'bar' ? color : color.replace('1)', '0.16)'),
                                    fill: type !== 'bar',
                                    tension: 0.35,
                                    borderWidth: 2,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: (context) => `${context.dataset.label || ''} ${formatRp(context.parsed.y ?? context.parsed ?? 0)}`.trim(),
                                        },
                                    },
                                },
                                scales: {
                                    x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true } },
                                    y: { beginAtZero: true, grid: { color: 'rgba(148, 163, 184, 0.18)' } },
                                },
                            },
	                        });
	                    };

	                    const drawPie = (selector, labels, values, colors) => {
	                        const canvas = root.querySelector(selector);

	                        if (!canvas) {
	                            return;
	                        }

	                        const existing = Chart.getChart(canvas);
	                        if (existing) {
	                            existing.destroy();
	                        }

	                        new Chart(canvas, {
	                            type: 'pie',
	                            data: {
	                                labels: labels || [],
	                                datasets: [{
	                                    data: values || [],
	                                    backgroundColor: colors,
	                                    borderColor: 'rgba(15, 23, 42, 0.12)',
	                                    borderWidth: 1,
	                                }],
	                            },
	                            options: {
	                                responsive: true,
	                                maintainAspectRatio: false,
	                                plugins: {
	                                    legend: {
	                                        position: 'top',
	                                        labels: { boxWidth: 12 },
	                                    },
                                        tooltip: {
                                            callbacks: {
                                                label: (context) => `${context.label}: ${formatRp(context.parsed || 0)}`,
                                            },
                                        },
	                                },
	                            },
	                        });
	                    };
	
	                    draw('[data-toko-sales-chart]', 'line', payload.sales?.labels, payload.sales?.values, 'rgba(37, 99, 235, 1)');
	                    draw('[data-toko-purchase-chart]', 'line', payload.purchases?.labels, payload.purchases?.values, 'rgba(5, 150, 105, 1)');
	                    draw('[data-toko-products-chart]', 'bar', payload.products?.labels, payload.products?.values, 'rgba(79, 70, 229, 0.85)');
	                    drawPie('[data-toko-revenue-mix-chart]', payload.revenueMix?.labels, payload.revenueMix?.values, ['rgba(244, 63, 94, 0.3)', 'rgba(59, 130, 246, 0.3)', 'rgba(251, 191, 36, 0.35)']);
	                    drawPie('[data-toko-expense-chart]', payload.expenseMix?.labels, payload.expenseMix?.values, ['rgba(168, 85, 247, 0.35)', 'rgba(20, 184, 166, 0.32)', 'rgba(245, 158, 11, 0.32)', 'rgba(96, 165, 250, 0.32)', 'rgba(248, 113, 113, 0.32)', 'rgba(34, 197, 94, 0.32)']);
	                });
	            };

            document.addEventListener('livewire:navigated', () => window.renderTokoDashboardCharts?.());
            document.addEventListener('livewire:updated', () => window.renderTokoDashboardCharts?.());
            queueMicrotask(() => window.renderTokoDashboardCharts?.());
        </script>
    @endonce

    <x-overlays.dialog-modal id="quick-customer-modal" wire:model.live="showingQuickCustomerModal">
        <x-slot name="title">
            {{ __('Tambah Pelanggan Baru') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-forms.label for="quickCustomerName" value="{{ __('Nama Pelanggan') }}" />
                    <x-forms.input id="quickCustomerName" type="text" class="mt-1 block w-full" wire:model="quickCustomerName" placeholder="Contoh: Budi Santoso" />
                    <x-forms.input-error for="quickCustomerName" class="mt-2" />
                </div>
                <div>
                    <x-forms.label for="quickCustomerPhone" value="{{ __('Nomor Telepon') }} ({{ __('Opsional') }})" />
                    <x-forms.input id="quickCustomerPhone" type="text" class="mt-1 block w-full" wire:model="quickCustomerPhone" placeholder="Contoh: 081234567890" />
                    <x-forms.input-error for="quickCustomerPhone" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-actions.button variant="neutral" wire:click="$set('showingQuickCustomerModal', false)" class="mr-3">
                {{ __('Batal') }}
            </x-actions.button>
            <x-actions.button wire:click="createQuickCustomer" variant="primary">
                {{ __('Simpan Pelanggan') }}
            </x-actions.button>
        </x-slot>
    </x-overlays.dialog-modal>

</x-admin.page-shell>
