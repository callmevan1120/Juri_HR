<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements SkipsEmptyRows, SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    public function __construct(private ?int $companyId = null) {}

    public function model(array $row)
    {
        $sku = $row['sku_barcode'] ?? $row['sku'] ?? $row['barcode'] ?? null;
        if (empty($sku)) {
            $sku = (string) Str::uuid();
        }

        return Product::updateOrCreate(
            ['sku' => $sku, 'company_id' => $this->companyId],
            [
                'name' => $row['nama_produk'] ?? $row['name'] ?? 'Produk',
                'unit' => $row['satuan'] ?? $row['unit'] ?? 'pcs',
                'selling_price' => isset($row['harga_jual']) ? (float) $row['harga_jual'] : 0,
                'cost_price' => isset($row['harga_modal']) ? (float) $row['harga_modal'] : 0,
                'stock_tracking' => strtolower((string) ($row['pantau_stok'] ?? 'ya')) === 'ya',
                'reorder_point' => isset($row['reorder_point']) ? (float) $row['reorder_point'] : 0,
                'status' => strtolower((string) ($row['status'] ?? 'aktif')) === 'aktif' ? 'active' : 'inactive',
            ]
        );
    }

    public function rules(): array
    {
        return [
            'nama_produk' => ['nullable', 'string'],
            'harga_jual' => ['nullable', 'numeric'],
            'harga_modal' => ['nullable', 'numeric'],
        ];
    }
}
