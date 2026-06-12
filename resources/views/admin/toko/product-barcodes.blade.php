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
