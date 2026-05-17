<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingPeriodClosing extends Model
{
    public const STATUS_CLOSED = 'closed';

    public const STATUS_REOPENED = 'reopened';

    protected $fillable = [
        'company_id',
        'period_start',
        'period_end',
        'status',
        'closed_by',
        'closed_at',
        'reopened_by',
        'reopened_at',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'closed_at' => 'datetime',
            'reopened_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }
}
