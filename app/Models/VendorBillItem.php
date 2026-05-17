<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBillItem extends Model
{
    protected $fillable = [
        'vendor_bill_id',
        'product_id',
        'description',
        'quantity',
        'unit_cost',
        'tax_rate',
        'line_subtotal',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'line_subtotal' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class, 'vendor_bill_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
