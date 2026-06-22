<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private ?int $companyId = null) {}

    public function query(): Builder
    {
        return Product::query()
            ->when($this->companyId, fn ($q) => $q->where('company_id', $this->companyId))
            ->orderBy('name');
    }

    public function headings(): array
    {
        return ['ID', 'Nama Produk', 'SKU / Barcode', 'Satuan', 'Harga Jual', 'Harga Modal', 'Pantau Stok', 'Reorder Point', 'Status', 'Didaftarkan'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->sku,
            $row->unit,
            (float) $row->selling_price,
            (float) $row->cost_price,
            $row->stock_tracking ? 'Ya' : 'Tidak',
            (float) $row->reorder_point,
            $row->status === 'active' ? 'Aktif' : 'Non-Aktif',
            $row->created_at?->format('Y-m-d H:i'),
        ];
    }
}
