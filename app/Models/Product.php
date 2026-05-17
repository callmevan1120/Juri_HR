<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'company_id',
        'name',
        'sku',
        'status',
        'unit',
        'selling_price',
        'cost_price',
        'stock_tracking',
        'reorder_point',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock_tracking' => 'boolean',
            'reorder_point' => 'decimal:3',
            'metadata' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest('occurred_at');
    }

    public function vendorBillItems(): HasMany
    {
        return $this->hasMany(VendorBillItem::class);
    }

    public function stockBalance(): float
    {
        $movements = $this->relationLoaded('stockMovements')
            ? $this->stockMovements
            : $this->stockMovements()->get();

        return round((float) $movements->sum(function (StockMovement $movement): float {
            return in_array($movement->type, [StockMovement::TYPE_IN, StockMovement::TYPE_ADJUSTMENT], true)
                ? (float) $movement->quantity
                : -1 * (float) $movement->quantity;
        }), 3);
    }

    public function isLowStock(): bool
    {
        return $this->stock_tracking
            && (float) $this->reorder_point > 0
            && $this->stockBalance() <= (float) $this->reorder_point;
    }
}
