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
                'selling_price' => $this->decimal($row['harga_jual'] ?? $row['selling_price'] ?? 0),
                'cost_price' => $this->decimal($row['harga_modal'] ?? $row['cost_price'] ?? 0),
                'stock_tracking' => $this->truthy($row['pantau_stok'] ?? $row['stock_tracking'] ?? 'yes'),
                'reorder_point' => $this->decimal($row['reorder_point'] ?? 0),
                'status' => $this->normalizeStatus($row['status'] ?? Product::STATUS_ACTIVE),
                'metadata' => [
                    'source' => 'toko_csv_product',
                ],
            ]
        );
    }

    public function rules(): array
    {
        return [
            'nama_produk' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'harga_jual' => ['nullable', 'numeric'],
            'selling_price' => ['nullable', 'numeric'],
            'harga_modal' => ['nullable', 'numeric'],
            'cost_price' => ['nullable', 'numeric'],
            'reorder_point' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string', 'in:active,inactive,aktif,nonaktif'],
        ];
    }

    private function decimal(mixed $value): float
    {
        return (float) str_replace(',', '.', trim((string) $value));
    }

    private function truthy(mixed $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'ya', 'y'], true);
    }

    private function normalizeStatus(mixed $status): string
    {
        return in_array(strtolower(trim((string) $status)), ['inactive', 'nonaktif'], true)
            ? Product::STATUS_INACTIVE
            : Product::STATUS_ACTIVE;
    }
}
