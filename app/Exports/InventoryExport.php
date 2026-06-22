<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private ?int $companyId = null) {}

    public function query(): Builder
    {
        return Product::query()
            ->with('stockMovements')
            ->where('stock_tracking', true)
            ->when($this->companyId, fn ($q) => $q->where('company_id', $this->companyId))
            ->orderBy('name');
    }

    public function headings(): array
    {
        return ['ID Produk', 'Nama Produk', 'SKU', 'Sisa Stok (Saldo)', 'Satuan', 'Nilai Valuasi (Modal)', 'Nilai Valuasi (Jual)'];
    }

    public function map($row): array
    {
        $balance = $row->stockBalance();

        return [
            $row->id,
            $row->name,
            $row->sku,
            $balance,
            $row->unit,
            $balance * (float) $row->cost_price,
            $balance * (float) $row->selling_price,
        ];
    }
}
