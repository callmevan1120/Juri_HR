<?php

namespace App\Livewire\Forms\Commercial;

use App\Models\StockMovement;
use Illuminate\Validation\Rule;
use Livewire\Form;

class StockMovementForm extends Form
{
    public string $productId = '';

    public string $type = StockMovement::TYPE_IN;

    public string $quantity = '1';

    public string $unitCost = '0';

    public string $notes = '';

    public function rules(): array
    {
        return [
            'productId' => ['required', 'integer', Rule::exists('products', 'id')],
            'type' => ['required', Rule::in([StockMovement::TYPE_IN, StockMovement::TYPE_OUT, StockMovement::TYPE_ADJUSTMENT])],
            'quantity' => ['required', 'numeric', 'min:0.001', 'max:999999999'],
            'unitCost' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function resetForm(): void
    {
        $this->reset(['quantity', 'unitCost', 'notes']);
        $this->quantity = '1';
        $this->unitCost = '0';
    }
}
