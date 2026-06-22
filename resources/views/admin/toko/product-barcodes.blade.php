@php
    $format = request('format', 'a4'); // 'a4' or 'thermal'
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Toko Product Barcodes') }}</title>
    <style>
        body { color: #000; font-family: Arial, sans-serif; margin: 0; }
        
        @if ($format === 'thermal')
            @page { margin: 0; size: 40mm 30mm; }
            .sheet { display: block; margin: 0; padding: 0; }
            .label { 
                page-break-after: always; 
                width: 40mm; 
                height: 30mm; 
                box-sizing: border-box;
                padding: 1mm; 
                text-align: center; 
                display: flex; flex-direction: column; justify-content: center; align-items: center;
                overflow: hidden;
                border: none;
            }
            .name { font-size: 10px; font-weight: 700; line-height: 1.1; margin-bottom: 2px; max-width: 100%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
            .sku { font-size: 8px; margin-bottom: 2px; }
            .barcode { font-family: "Courier New", monospace; font-size: 14px; font-weight: 700; letter-spacing: 0px; }
            .bars { background: repeating-linear-gradient(90deg, #000 0 1px, transparent 1px 2px, #000 2px 3px, transparent 3px 4px); height: 12px; margin: 2px auto; width: 100%; max-width: 90%; }
            .meta { font-size: 7px; margin-top: 2px; }
        @else
            @page { margin: 10mm; size: A4; }
            .sheet { display: grid; gap: 2mm 4mm; grid-template-columns: repeat(3, 1fr); padding: 5mm; }
            /* Tom & Jerry format approximation (e.g. 103, 107) */
            .label { border: 1px dashed #cbd5e1; border-radius: 4px; height: 32mm; padding: 2mm; text-align: center; display: flex; flex-direction: column; justify-content: center; overflow: hidden; }
            .name { font-size: 11px; font-weight: 700; line-height: 1.25; margin-bottom: 3px; }
            .sku { color: #475569; font-size: 9px; margin-bottom: 4px; }
            .barcode { font-family: "Courier New", monospace; font-size: 16px; font-weight: 700; letter-spacing: 1px; }
            .bars { background: repeating-linear-gradient(90deg, #0f172a 0 2px, transparent 2px 4px, #0f172a 4px 5px, transparent 5px 8px); height: 20px; margin: 4px auto; width: 100%; max-width: 160px; }
            .meta { color: #475569; font-size: 9px; margin-top: 2px; }
        @endif

        .print-button { align-items: center; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; color: #0f172a; cursor: pointer; display: inline-flex; height: 36px; justify-content: center; margin: 12px; width: 36px; }
        @media print { .no-print { display: none; } .label { border: none; } }
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
            body { border-color: #334155; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5), 0 10px 10px -5px rgba(0,0,0,0.3); }
        }
    </style>
</head>
<body>
    <button class="no-print print-button" onclick="window.print()" aria-label="{{ __('Print') }}" title="{{ __('Print') }}">
        @svg('heroicon-o-printer', 'h-5 w-5')
    </button>

    <main class="sheet">
        @foreach ($products as $product)
            @php
                $metadata = is_array($product->metadata) ? $product->metadata : [];
                $barcode = $metadata['barcode'] ?? $product->sku ?? $product->id;
                $printQty = request('use_stock') ? max(1, (int) $product->stockBalance()) : 1;
            @endphp
            @for ($i = 0; $i < $printQty; $i++)
                <section class="label">
                    <div class="name">{{ $product->name }}</div>
                    <div class="sku">{{ $product->sku ?? '-' }}</div>
                    <div class="bars"></div>
                    <div class="barcode">{{ $barcode }}</div>
                    <div class="meta">{{ $metadata['brand'] ?? '-' }} · {{ $metadata['category'] ?? '-' }}</div>
                </section>
            @endfor
        @endforeach
    </main>
</body>
</html>
