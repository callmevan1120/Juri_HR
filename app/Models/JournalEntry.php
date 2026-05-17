<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    public const STATUS_POSTED = 'posted';

    protected $fillable = [
        'company_id',
        'created_by',
        'number',
        'entry_date',
        'status',
        'source_type',
        'source_id',
        'reference_number',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }
}
