@php
    $formatMoney = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
    $formatQuantity = fn ($value) => rtrim(rtrim(number_format((float) $value, 3, ',', '.'), '0'), ',');
    $contactLines = collect([
        $companyAddress ?: null,
        $companyPhone ? __('Phone: :value', ['value' => $companyPhone]) : null,
        $supportContact ? __('Contact: :value', ['value' => $supportContact]) : null,
        $companyWebsite ? __('Website: :value', ['value' => $companyWebsite]) : null,
    ])->filter()->values();
    $partyLines = collect([
        $document['partyContact'] ?? null,
        $document['partyEmail'] ?? null,
        $document['partyPhone'] ?? null,
        ($document['partyTaxNumber'] ?? null) ? __('Tax ID: :value', ['value' => $document['partyTaxNumber']]) : null,
        $document['partyAddress'] ?? null,
    ])->filter()->values();
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $document['title'] }} - {{ $document['number'] }}</title>
    <style>
        @page { margin: 34px 42px 54px; }
        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
        }
        .header {
            border-bottom: 2px solid #0f766e;
            margin-bottom: 24px;
            padding-bottom: 14px;
            width: 100%;
        }
        .header td { border: 0; padding: 0; vertical-align: top; }
        .logo { height: 46px; width: auto; }
        .company { padding-left: 14px; }
        .company-name { font-size: 16px; font-weight: 700; margin: 0 0 4px; text-transform: uppercase; }
        .company-contact { color: #4b5563; font-size: 9px; margin: 0; }
        .document-title { color: #0f766e; font-size: 24px; font-weight: 700; margin: 0 0 6px; text-align: right; text-transform: uppercase; }
        .document-number { color: #374151; font-size: 10px; margin: 0; text-align: right; }
        .grid { margin-bottom: 20px; width: 100%; }
        .grid td { border: 0; padding: 0; vertical-align: top; width: 50%; }
        .box {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px;
        }
        .box-title {
            color: #0f766e;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            margin: 0 0 8px;
            text-transform: uppercase;
        }
        .party-name { font-size: 13px; font-weight: 700; margin: 0 0 5px; }
        .muted { color: #6b7280; }
        .meta-table { margin-left: auto; width: 82%; }
        .meta-table td { border: 0; padding: 0 0 5px; }
        .meta-label { color: #6b7280; width: 42%; }
        .meta-value { font-weight: 700; text-align: right; }
        table.items {
            border-collapse: collapse;
            margin-top: 12px;
            width: 100%;
        }
        .items th {
            background: #0f766e;
            border: 1px solid #0f766e;
            color: #ffffff;
            font-size: 9px;
            padding: 8px;
            text-align: left;
            text-transform: uppercase;
        }
        .items td {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
        }
        .right { text-align: right; }
        .totals {
            margin: 16px 0 0 auto;
            width: 42%;
        }
        .totals td {
            border: 0;
            border-bottom: 1px solid #e5e7eb;
            padding: 7px 0;
        }
        .totals .grand td {
            border-bottom: 0;
            color: #0f766e;
            font-size: 13px;
            font-weight: 700;
            padding-top: 10px;
        }
        .notes {
            border-top: 1px solid #e5e7eb;
            color: #4b5563;
            margin-top: 20px;
            padding-top: 12px;
        }
        .footer {
            border-top: 1px solid #d1fae5;
            bottom: -34px;
            color: #6b7280;
            font-size: 9px;
            left: 0;
            padding-top: 8px;
            position: fixed;
            right: 0;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 58%;">
                <table>
                    <tr>
                        @if ($logoSrc)
                            <td style="width: 56px;"><img class="logo" src="{{ $logoSrc }}" alt=""></td>
                        @endif
                        <td class="company">
                            <p class="company-name">{{ $companyName }}</p>
                            @foreach ($contactLines as $line)
                                <p class="company-contact">{{ $line }}</p>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <p class="document-title">{{ $document['title'] }}</p>
                <p class="document-number">{{ $document['numberLabel'] }}: {{ $document['number'] }}</p>
            </td>
        </tr>
    </table>

    <table class="grid">
        <tr>
            <td style="padding-right: 10px;">
                <div class="box">
                    <p class="box-title">{{ $document['partyLabel'] }}</p>
                    <p class="party-name">{{ $document['partyName'] }}</p>
                    @foreach ($partyLines as $line)
                        <div class="muted">{{ $line }}</div>
                    @endforeach
                    @if ($document['projectName'] ?? null)
                        <div style="margin-top: 8px;"><strong>{{ __('Project') }}:</strong> {{ $document['projectName'] }}</div>
                    @endif
                </div>
            </td>
            <td style="padding-left: 10px;">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">{{ $document['numberLabel'] }}</td>
                        <td class="meta-value">{{ $document['number'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">{{ $document['dateLabel'] }}</td>
                        <td class="meta-value">{{ $document['issuedAt']?->format('d M Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">{{ $document['dueLabel'] }}</td>
                        <td class="meta-value">{{ $document['dueAt']?->format('d M Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">{{ __('Status') }}</td>
                        <td class="meta-value">{{ __(str($document['status'])->headline()->toString()) }}</td>
                    </tr>
                    @if ($document['sourceNumber'] ?? null)
                        <tr>
                            <td class="meta-label">{{ __('Source') }}</td>
                            <td class="meta-value">{{ $document['sourceNumber'] }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 42%;">{{ __('Description') }}</th>
                <th style="width: 14%;" class="right">{{ __('Qty') }}</th>
                <th style="width: 16%;" class="right">{{ $type === 'vendor-bill' ? __('Unit Cost') : __('Unit Price') }}</th>
                <th style="width: 10%;" class="right">{{ __('Tax') }}</th>
                <th style="width: 18%;" class="right">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document['items'] as $item)
                @php
                    $unitValue = $type === 'vendor-bill' ? $item->unit_cost : $item->unit_price;
                    $lineValue = $type === 'vendor-bill' ? $item->line_total : $item->line_total;
                @endphp
                <tr>
                    <td>
                        <strong>{{ $item->description }}</strong>
                        @if ($item->product)
                            <div class="muted">{{ $item->product->name }}</div>
                        @endif
                    </td>
                    <td class="right">{{ $formatQuantity($item->quantity) }}</td>
                    <td class="right">{{ $formatMoney($unitValue) }}</td>
                    <td class="right">{{ number_format((float) $item->tax_rate, 2, ',', '.') }}%</td>
                    <td class="right">{{ $formatMoney($lineValue) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>{{ __('Subtotal') }}</td>
            <td class="right">{{ $formatMoney($record->subtotal) }}</td>
        </tr>
        <tr>
            <td>{{ __('Tax') }}</td>
            <td class="right">{{ $formatMoney($record->tax_total) }}</td>
        </tr>
        <tr class="grand">
            <td>{{ __('Grand Total') }}</td>
            <td class="right">{{ $formatMoney($record->grand_total) }}</td>
        </tr>
    </table>

    @if ($document['notes'] ?? null)
        <div class="notes">
            <strong>{{ __('Notes') }}</strong>
            <div>{{ $document['notes'] }}</div>
        </div>
    @endif

    <div class="footer">
        {{ __('Generated by :app on :date.', ['app' => config('app.name'), 'date' => now()->format('d M Y H:i')]) }}
    </div>
</body>
</html>
