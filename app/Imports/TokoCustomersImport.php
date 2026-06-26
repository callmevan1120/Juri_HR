<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TokoCustomersImport implements SkipsEmptyRows, SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    public function __construct(private readonly int $companyId) {}

    public function model(array $row): ?Client
    {
        $code = trim((string) ($row['code'] ?? $row['kode'] ?? ''));
        $name = trim((string) ($row['name'] ?? $row['nama'] ?? ''));

        if ($code === '' || $name === '') {
            return null;
        }

        return Client::query()->updateOrCreate(
            [
                'company_id' => $this->companyId,
                'code' => $code,
            ],
            [
                'name' => $name,
                'status' => $this->normalizeStatus($row['status'] ?? Client::STATUS_ACTIVE),
                'contact_name' => $name,
                'contact_phone' => trim((string) ($row['phone'] ?? $row['nohp'] ?? $row['telepon'] ?? '')),
                'contact_email' => trim((string) ($row['email'] ?? '')),
                'address' => trim((string) ($row['address'] ?? $row['alamat'] ?? '')),
                'metadata' => [
                    'source' => 'toko_csv_customer',
                ],
            ],
        );
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'in:active,inactive,aktif,nonaktif'],
        ];
    }

    private function normalizeStatus(mixed $status): string
    {
        return in_array(strtolower(trim((string) $status)), ['inactive', 'nonaktif'], true)
            ? Client::STATUS_INACTIVE
            : Client::STATUS_ACTIVE;
    }
}
