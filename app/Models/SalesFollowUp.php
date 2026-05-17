<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesFollowUp extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DONE = 'done';

    protected $fillable = [
        'sales_opportunity_id',
        'assigned_to',
        'due_at',
        'status',
        'notes',
        'completed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(SalesOpportunity::class, 'sales_opportunity_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
