<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Toko Stock Adjustment Report') }}</title>
    <style>
        @page { margin: 12mm; }
        body { color: #0f172a; font-family: Arial, sans-serif; font-size: 12px; margin: 0; }
        header { border-bottom: 1px solid #cbd5e1; margin-bottom: 12px; padding-bottom: 10px; }
        h1 { font-size: 18px; margin: 0; }
        .meta { color: #475569; margin-top: 4px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 7px 6px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; color: #475569; font-size: 10px; text-transform: uppercase; }
        .right { text-align: right; }
        .strong { font-weight: 700; }
        .muted { color: #64748b; font-size: 10px; margin-top: 2px; }
        .no-print { align-items: center; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; color: #0f172a; cursor: pointer; display: inline-flex; height: 36px; justify-content: center; margin: 12px 0; width: 36px; }
        @media print { .no-print { display: none; } }
    @media screen {
            html { background: #f8fafc; min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding-top: 40px; padding-bottom: 40px; }
            body { 
                margin: 0; 
                background: #ffffff; 
                box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); 
                border: 1px solid #e2e8f0;
                border-radius: 2px;
                position: relative;
            }
            .print-btn { 
                position: fixed; top: 24px; right: 24px; padding: 12px 24px; 
                background: #10b981; color: white; border: none; border-radius: 12px; 
                cursor: pointer; font-family: ui-sans-serif, system-ui, sans-serif; font-weight: 600; font-size: 14px;
                box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3), 0 2px 4px -1px rgba(16, 185, 129, 0.2);
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 50;
            }
            .print-btn:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3), 0 4px 6px -2px rgba(16, 185, 129, 0.2); }
            .print-btn:active { transform: translateY(0); }
        }
        @media screen and (prefers-color-scheme: dark) {
            html { background: #0f172a; }
            body { 
                border-color: #334155;
                box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5), 0 10px 10px -5px rgba(0,0,0,0.3);
            }
            /* The receipt itself stays white to reflect actual thermal paper */
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" aria-label="{{ __('Print') }}" title="{{ __('Print') }}">
        @svg('heroicon-o-printer', 'h-5 w-5')
    </button>

    <header>
        <h1>{{ __('Toko Stock Adjustment Report') }}</h1>
        <div class="meta">{{ __('Printed') }}: {{ $printedAt->format('d M Y H:i') }}</div>
    </header>

    <table>
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Company') }}</th>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Reference') }}</th>
                <th class="right">{{ __('Previous') }}</th>
                <th class="right">{{ __('Counted') }}</th>
                <th class="right">{{ __('Delta') }}</th>
                <th>{{ __('Notes') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $movement)
                @php($metadata = is_array($movement->metadata) ? $movement->metadata : [])
                <tr>
                    <td>{{ $movement->occurred_at?->format('d M Y H:i') ?? '-' }}</td>
                    <td>{{ $movement->company?->name ?? '-' }}</td>
                    <td>
                        <div class="strong">{{ $movement->product?->name ?? '-' }}</div>
                        <div class="muted">{{ $movement->product?->sku ?? '-' }}</div>
                    </td>
                    <td>{{ $movement->reference_number ?: '-' }}</td>
                    <td class="right">{{ number_format((float) ($metadata['previous_quantity'] ?? 0), 3) }}</td>
                    <td class="right">{{ number_format((float) ($metadata['counted_quantity'] ?? 0), 3) }}</td>
                    <td class="right strong">{{ number_format((float) ($metadata['delta'] ?? 0), 3) }}</td>
                    <td>{{ $movement->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">{{ __('No stock adjustments yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
