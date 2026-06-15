<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Struk Penjualan') }} - {{ $invoice->number }}</title>
    
    <style>
        @page {
            margin: 0;
            size: 80mm 200mm; /* adjust height as needed or use auto if supported */
        }
        
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 5mm;
            width: 80mm;
            box-sizing: border-box;
            line-height: 1.2;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        .header { margin-bottom: 5mm; border-bottom: 1px dashed #000; padding-bottom: 3mm; }
        .header h1 { font-size: 16px; margin: 0 0 2mm 0; }
        .header p { margin: 0; font-size: 11px; }

        .meta { margin-bottom: 4mm; font-size: 11px; }
        .meta table { width: 100%; }
        .meta td { vertical-align: top; padding: 0.5mm 0; }

        .items { border-bottom: 1px dashed #000; margin-bottom: 3mm; padding-bottom: 2mm; width: 100%; border-collapse: collapse; }
        .items th { text-align: left; border-bottom: 1px dashed #000; padding: 1mm 0; font-size: 11px; }
        .items td { vertical-align: top; padding: 1.5mm 0; font-size: 11px; }
        .items .product-name { font-weight: bold; display: block; margin-bottom: 1mm; }

        .totals { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
        .totals td { padding: 1mm 0; font-size: 11px; }
        .totals .grand-total { font-weight: bold; font-size: 13px; border-top: 1px dashed #000; padding-top: 2mm; }

        .footer { text-align: center; font-size: 10px; border-top: 1px dashed #000; padding-top: 3mm; }
        
        /* Utility classes */
        .w-full { width: 100%; }
        .mb-1 { margin-bottom: 1mm; }
        
        @media print {
            body { padding: 0; margin-top: 5mm; margin-left: auto; margin-right: auto; }
            .no-print { display: none; }
        }

        /* Web preview wrapper */
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
            .print-btn { 
                position: fixed; top: 20px; right: 20px; padding: 10px 20px; 
                background: #2563eb; color: white; border: none; border-radius: 5px; 
                cursor: pointer; font-family: sans-serif; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }
            .print-btn:hover { background: #1d4ed8; }
        }
    </style>
</head>
<body onload="window.print()">


    <div class="header text-center">
        <h1>{{ $company->name }}</h1>
        @if($company->address)
            <p>{{ $company->address }}</p>
        @endif
        @if($company->phone)
            <p>Telp: {{ $company->phone }}</p>
        @endif
    </div>

    <div class="meta">
        <table>
            <tr>
                <td>{{ __('No') }}</td>
                <td>: {{ $invoice->number }}</td>
            </tr>
            <tr>
                <td>{{ __('Tgl') }}</td>
                <td>: {{ $invoice->issued_at?->format('d/m/Y H:i') ?? $invoice->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>{{ __('Kasir') }}</td>
                <td>: {{ request()->user()?->name ?? 'Admin' }}</td>
            </tr>
            <tr>
                <td>{{ __('Plg') }}</td>
                <td>: {{ $client?->name ?? 'Umum (Walk-in)' }}</td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>{{ __('Item') }}</th>
                <th class="text-right">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lines as $line)
                <tr>
                    <td>
                        <span class="product-name">{{ $line->item_name }}</span>
                        {{ rtrim(rtrim(number_format($line->quantity, 2, ',', '.'), '0'), ',') }} x {{ number_format($line->unit_price, 0, ',', '.') }}
                        @if($line->discount_amount > 0)
                            <br><small>Diskon: -{{ number_format($line->discount_amount, 0, ',', '.') }}</small>
                        @endif
                    </td>
                    <td class="text-right font-bold">
                        {{ number_format($line->line_total, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>{{ __('Subtotal') }}</td>
            <td class="text-right">{{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($invoice->tax_total > 0)
            <tr>
                <td>{{ __('Pajak') }}</td>
                <td class="text-right">{{ number_format($invoice->tax_total, 0, ',', '.') }}</td>
            </tr>
        @endif
        @if(($invoice->metadata['additional_charge'] ?? 0) > 0)
            <tr>
                <td>{{ __('Biaya Lain') }}</td>
                <td class="text-right">{{ number_format($invoice->metadata['additional_charge'], 0, ',', '.') }}</td>
            </tr>
        @endif
        @if(($invoice->metadata['discount_amount'] ?? 0) > 0)
            <tr>
                <td>{{ __('Diskon') }}</td>
                <td class="text-right">-{{ number_format($invoice->metadata['discount_amount'], 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr>
            <td class="grand-total">{{ __('Total Akhir') }}</td>
            <td class="text-right grand-total">{{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
        </tr>
        @if(!empty($invoice->metadata['payments']))
            @foreach($invoice->metadata['payments'] as $payment)
                <tr>
                    <td>{{ __('Bayar') }} ({{ $payment['method'] ?? 'Tunai' }})</td>
                    <td class="text-right">{{ number_format($payment['amount'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            @php
                $totalPaid = collect($invoice->metadata['payments'])->sum('amount');
                $change = $totalPaid - $invoice->grand_total;
            @endphp
            @if($change > 0)
                <tr>
                    <td>{{ __('Kembali') }}</td>
                    <td class="text-right">{{ number_format($change, 0, ',', '.') }}</td>
                </tr>
            @endif
        @endif
    </table>

    <div class="footer">
        <p class="mb-1">{{ __('Terima kasih atas kunjungan Anda') }}</p>
        <p>{{ __('Barang yang sudah dibeli tidak dapat ditukar/dikembalikan') }}</p>
    </div>

</body>
</html>
