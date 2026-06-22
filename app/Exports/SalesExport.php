<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private ?int $companyId = null, private ?int $branchId = null) {}

    public function query(): Builder
    {
        return Invoice::query()
            ->with(['client', 'creator'])
            ->where('type', 'pos')
            ->when($this->companyId, fn ($q) => $q->where('company_id', $this->companyId))
            ->when($this->branchId, fn ($q) => $q->where('company_branch_id', $this->branchId))
            ->orderByDesc('issued_at');
    }

    public function headings(): array
    {
        return ['Nomor Invoice', 'Tanggal', 'Status', 'Pelanggan', 'Kasir', 'Subtotal', 'Diskon', 'Total Akhir', 'Pembayaran', 'Metode', 'Kembalian'];
    }

    public function map($row): array
    {
        return [
            $row->invoice_number,
            $row->issued_at?->format('Y-m-d H:i'),
            $row->status,
            $row->client?->name ?? 'Walk-in Customer',
            $row->creator?->name,
            (float) $row->subtotal,
            (float) $row->discount_amount,
            (float) $row->grand_total,
            (float) ($row->metadata['payment_amount'] ?? $row->grand_total),
            $row->metadata['payment_method'] ?? 'cash',
            (float) ($row->metadata['change_amount'] ?? 0),
        ];
    }
}
