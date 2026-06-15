<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Toko Product Barcodes') }}</title>
    <style>
        @page { margin: 10mm; }
        body { color: #0f172a; font-family: Arial, sans-serif; margin: 0; }
        .sheet { display: grid; gap: 8px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .label { border: 1px solid #cbd5e1; border-radius: 4px; min-height: 88px; padding: 8px; text-align: center; }
        .name { font-size: 12px; font-weight: 700; line-height: 1.25; margin-bottom: 3px; }
        .sku { color: #475569; font-size: 10px; margin-bottom: 6px; }
        .barcode { font-family: "Courier New", monospace; font-size: 18px; font-weight: 700; letter-spacing: 1px; }
        .bars { background: repeating-linear-gradient(90deg, #0f172a 0 2px, transparent 2px 4px, #0f172a 4px 5px, transparent 5px 8px); height: 28px; margin: 6px auto 4px; max-width: 160px; }
        .meta { color: #475569; font-size: 10px; }
        .print-button { align-items: center; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; color: #0f172a; cursor: pointer; display: inline-flex; height: 36px; justify-content: center; margin: 12px; width: 36px; }
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
    <button class="no-print print-button" onclick="window.print()" aria-label="{{ __('Print') }}" title="{{ __('Print') }}">
        @svg('heroicon-o-printer', 'h-5 w-5')
    </button>

    <main class="sheet">
        @foreach ($products as $product)
            @php($metadata = is_array($product->metadata) ? $product->metadata : [])
            @php($barcode = $metadata['barcode'] ?? $product->sku ?? $product->id)
            <section class="label">
                <div class="name">{{ $product->name }}</div>
                <div class="sku">{{ $product->sku ?? '-' }}</div>
                <div class="bars"></div>
                <div class="barcode">{{ $barcode }}</div>
                <div class="meta">{{ $metadata['brand'] ?? '-' }} · {{ $metadata['category'] ?? '-' }}</div>
            </section>
        @endforeach
    </main>
</body>
</html>
