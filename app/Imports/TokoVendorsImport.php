<?php

namespace App\Imports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TokoVendorsImport implements SkipsEmptyRows, SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    public function __construct(private readonly int $companyId) {}

    public function model(array $row): ?Vendor
    {
        $code = trim((string) ($row['code'] ?? $row['kode'] ?? ''));
        $name = trim((string) ($row['name'] ?? $row['nama'] ?? ''));

        if ($code === '' || $name === '') {
            return null;
        }

        $vendor = Vendor::query()
            ->where('company_id', $this->companyId)
            ->where('metadata->legacy_code', $code)
            ->first();

        if (! $vendor) {
            $vendor = Vendor::query()->firstOrNew([
                'company_id' => $this->companyId,
                'name' => $name,
            ]);
        }

        $metadata = is_array($vendor->metadata) ? $vendor->metadata : [];

        $vendor->fill([
            'company_id' => $this->companyId,
            'name' => $name,
            'status' => $this->normalizeStatus($row['status'] ?? Vendor::STATUS_ACTIVE),
            'contact_name' => $name,
            'phone' => trim((string) ($row['phone'] ?? $row['nohp'] ?? $row['telepon'] ?? '')),
            'email' => trim((string) ($row['email'] ?? '')),
            'tax_number' => trim((string) ($row['tax_number'] ?? $row['npwp'] ?? '')),
            'address' => trim((string) ($row['address'] ?? $row['alamat'] ?? '')),
            'metadata' => [
                ...$metadata,
                'source' => 'toko_csv_vendor',
                'legacy_code' => $code,
            ],
        ]);
        $vendor->save();

        return $vendor;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:active,inactive,aktif,nonaktif'],
        ];
    }

    private function normalizeStatus(mixed $status): string
    {
        return in_array(strtolower(trim((string) $status)), ['inactive', 'nonaktif'], true)
            ? Vendor::STATUS_INACTIVE
            : Vendor::STATUS_ACTIVE;
    }
}
