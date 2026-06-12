@php
    $formatMoney = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
    $formatQuantity = fn ($value) => rtrim(rtrim(number_format((float) $value, 3, ',', '.'), '0'), ',');
    $showMoney = $type !== 'delivery-letter';
    $partyLines = collect([
        $document['partyContact'] ?? null,
        $document['partyEmail'] ?? null,
        $document['partyPhone'] ?? null,
        ($document['partyTaxNumber'] ?? null) ? __('Tax ID: :value', ['value' => $document['partyTaxNumber']]) : null,
        $document['partyAddress'] ?? null,
    ])->filter()->values();
@endphp

<style>
    .commercial-document-title {
        border-bottom: 1.4px solid #badcb3;
        color: #083344;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: .03em;
        line-height: 1.35;
        margin: 4px 0 18px;
        padding-bottom: 8px;
        text-transform: uppercase;
    }

    .commercial-document-summary {
        margin: 0 0 16px;
        width: 100%;
    }

    .commercial-document-summary,
    .commercial-document-summary td {
        border: 0;
    }

    .commercial-document-summary td {
        padding: 0;
        vertical-align: top;
        width: 50%;
    }

    .commercial-document-party {
        background: #f5faf4;
        border: 1px solid #d7ead3;
        border-left: 4px solid #6ab45b;
        border-radius: 6px;
        padding: 10px 18px 10px 12px;
    }

    .commercial-document-party-label,
    .commercial-document-meta-label {
        color: #31542a;
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .08em;
        margin: 0 0 6px;
        text-transform: uppercase;
    }

    .commercial-document-party-name {
        color: #111827;
        font-size: 13px;
        font-weight: 700;
        margin: 0 0 5px;
    }

    .commercial-document-muted {
        color: #4b5563;
        font-size: 10px;
        line-height: 1.45;
    }

    .commercial-document-meta {
        margin: 0 0 0 auto;
        width: 88%;
    }

    .commercial-document-meta,
    .commercial-document-meta td {
        border: 0;
    }

    .commercial-document-meta td {
        padding: 0 0 6px;
    }

    .commercial-document-meta .label {
        color: #4b5563;
        width: 42%;
    }

    .commercial-document-meta .value {
        color: #111827;
        font-weight: 700;
        text-align: right;
    }

    .commercial-document-status {
        background: #f0f9ee;
        border-radius: 5px;
        color: #31542a;
        display: inline-block;
        font-size: 9.5px;
        font-weight: 700;
        padding: 3px 8px;
    }

    .commercial-document-items {
        border-collapse: collapse;
        margin: 16px 0 0;
        width: 100%;
    }

    .commercial-document-items th {
        background: #083344;
        border: 1px solid #083344;
        color: #ffffff;
        font-size: 9px;
        font-weight: 700;
        padding: 7px;
        text-align: left;
        text-transform: uppercase;
    }

    .commercial-document-items td {
        border: 1px solid #d1d5db;
        padding: 7px;
        vertical-align: top;
    }

    .commercial-document-right {
        text-align: right;
    }

    .commercial-document-totals {
        background: #f5faf4;
        border: 1px solid #d7ead3;
        border-radius: 6px;
        margin: 16px 0 0 auto;
        padding: 8px 10px;
        width: 42%;
    }

    .commercial-document-totals,
    .commercial-document-totals td {
        border: 0;
    }

    .commercial-document-totals td {
        border-bottom: 1px solid #d7ead3;
        padding: 6px 0;
    }

    .commercial-document-totals .grand td {
        border-bottom: 0;
        color: #31542a;
        font-size: 13px;
        font-weight: 700;
        padding-top: 9px;
    }

    .commercial-document-notes {
        background: #f9fafb;
        border: 1px solid #d1d5db;
        border-left: 3px solid #6ab45b;
        border-radius: 6px;
        color: #4b5563;
        margin-top: 18px;
        padding: 9px 10px;
    }
</style>

<h2 class="commercial-document-title">{{ $document['title'] }}</h2>

<table class="commercial-document-summary">
    <tr>
        <td>
            <div class="commercial-document-party">
                <p class="commercial-document-party-label">{{ $document['partyLabel'] }}</p>
                <p class="commercial-document-party-name">{{ $document['partyName'] }}</p>
                @foreach ($partyLines as $line)
                    <div class="commercial-document-muted">{{ $line }}</div>
                @endforeach
                @if ($document['projectName'] ?? null)
                    <div class="commercial-document-muted" style="margin-top: 8px;">
                        <strong>{{ __('Project') }}:</strong> {{ $document['projectName'] }}
                    </div>
                @endif
            </div>
        </td>
        <td>
            <table class="commercial-document-meta">
                <tr>
                    <td class="label">{{ $document['numberLabel'] }}</td>
                    <td class="value">{{ $document['number'] }}</td>
                </tr>
                <tr>
                    <td class="label">{{ $document['dateLabel'] }}</td>
                    <td class="value">{{ $document['issuedAt']?->format('d M Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">{{ $document['dueLabel'] }}</td>
                    <td class="value">{{ $document['dueAt']?->format('d M Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">{{ __('Status') }}</td>
                    <td class="value">
                        <span class="commercial-document-status">{{ __(str($document['status'])->headline()->toString()) }}</span>
                    </td>
                </tr>
                @if ($document['sourceNumber'] ?? null)
                    <tr>
                        <td class="label">{{ __('Source') }}</td>
                        <td class="value">{{ $document['sourceNumber'] }}</td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<table class="commercial-document-items">
    <thead>
        <tr>
            <th style="width: 42%;">{{ __('Description') }}</th>
            <th style="width: 14%;" class="commercial-document-right">{{ __('Qty') }}</th>
            @if ($showMoney)
                <th style="width: 16%;" class="commercial-document-right">{{ $type === 'vendor-bill' ? __('Unit Cost') : __('Unit Price') }}</th>
                <th style="width: 10%;" class="commercial-document-right">{{ __('Tax') }}</th>
                <th style="width: 18%;" class="commercial-document-right">{{ __('Total') }}</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($document['items'] as $item)
            @php
                $unitValue = $type === 'vendor-bill' ? $item->unit_cost : $item->unit_price;
            @endphp
            <tr>
                <td>
                    <strong>{{ $item->description }}</strong>
                    @if ($item->product)
                        <div class="commercial-document-muted">{{ $item->product->name }}</div>
                    @endif
                </td>
                <td class="commercial-document-right">{{ $formatQuantity($item->quantity) }}</td>
                @if ($showMoney)
                    <td class="commercial-document-right">{{ $formatMoney($unitValue) }}</td>
                    <td class="commercial-document-right">{{ number_format((float) $item->tax_rate, 2, ',', '.') }}%</td>
                    <td class="commercial-document-right">{{ $formatMoney($item->line_total) }}</td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>

@if ($showMoney)
    <table class="commercial-document-totals">
        <tr>
            <td>{{ __('Subtotal') }}</td>
            <td class="commercial-document-right">{{ $formatMoney($record->subtotal) }}</td>
        </tr>
        <tr>
            <td>{{ __('Tax') }}</td>
            <td class="commercial-document-right">{{ $formatMoney($record->tax_total) }}</td>
        </tr>
        <tr class="grand">
            <td>{{ __('Grand Total') }}</td>
            <td class="commercial-document-right">{{ $formatMoney($record->grand_total) }}</td>
        </tr>
    </table>
@endif

@if ($document['notes'] ?? null)
    <div class="commercial-document-notes">
        <strong>{{ __('Notes') }}</strong>
        <div>{{ $document['notes'] }}</div>
    </div>
@endif
