<?php

namespace App\Exports;

use App\Models\VendorBill;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchasesExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private ?int $companyId = null, private ?int $branchId = null) {}

    public function query(): Builder
    {
        return VendorBill::query()
            ->with(['vendor', 'creator'])
            ->when($this->companyId, fn ($q) => $q->where('company_id', $this->companyId))
            ->when($this->branchId, fn ($q) => $q->where('company_branch_id', $this->branchId))
            ->orderByDesc('bill_date');
    }

    public function headings(): array
    {
        return ['Nomor Tagihan', 'Tanggal', 'Jatuh Tempo', 'Status', 'Vendor', 'Dibuat Oleh', 'Subtotal', 'Pajak', 'Total Akhir', 'Sisa Tagihan'];
    }

    public function map($row): array
    {
        return [
            $row->bill_number,
            $row->bill_date?->format('Y-m-d'),
            $row->due_date?->format('Y-m-d'),
            $row->status,
            $row->vendor?->name,
            $row->creator?->name,
            (float) $row->subtotal,
            (float) $row->tax_amount,
            (float) $row->grand_total,
            (float) $row->amount_due,
        ];
    }
}
