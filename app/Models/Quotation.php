<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_ACCEPTED = 'accepted';

    protected $fillable = [
        'company_id',
        'client_id',
        'project_id',
        'sales_opportunity_id',
        'number',
        'status',
        'issued_at',
        'valid_until',
        'subtotal',
        'tax_total',
        'grand_total',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function salesOpportunity(): BelongsTo
    {
        return $this->belongsTo(SalesOpportunity::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }
}
