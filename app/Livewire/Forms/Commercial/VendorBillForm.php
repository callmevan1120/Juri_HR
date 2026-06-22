<?php

namespace App\Livewire\Forms\Commercial;

use App\Models\Vendor;
use Illuminate\Validation\Rule;
use Livewire\Form;

class VendorBillForm extends Form
{
    public string $vendorId = '';

    public string $productId = '';

    public string $description = '';

    public string $quantity = '1';

    public string $unitCost = '0';

    public string $taxRate = '11';

    public string $dueAt = '';

    public string $notes = '';

    public function rules(): array
    {
        $vendorCompanyId = Vendor::query()->whereKey($this->vendorId)->value('company_id');

        return [
            'vendorId' => ['required', 'integer', Rule::exists('vendors', 'id')],
            'productId' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')->where('company_id', $vendorCompanyId),
            ],
            'description' => ['required', 'string', 'max:180'],
            'quantity' => ['required', 'numeric', 'min:0.001', 'max:999999999'],
            'unitCost' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'taxRate' => ['required', 'numeric', 'min:0', 'max:100'],
            'dueAt' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function resetForm(): void
    {
        $this->reset(['productId', 'description', 'quantity', 'unitCost', 'taxRate', 'dueAt', 'notes']);
        $this->quantity = '1';
        $this->unitCost = '0';
        $this->taxRate = '11';
    }
}
