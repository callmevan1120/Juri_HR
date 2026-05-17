<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveEntitlement extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'leave_type_id',
        'year',
        'allocated_days',
        'carried_over_days',
        'expires_at',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'allocated_days' => 'decimal:2',
            'carried_over_days' => 'decimal:2',
            'expires_at' => 'date',
            'metadata' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
