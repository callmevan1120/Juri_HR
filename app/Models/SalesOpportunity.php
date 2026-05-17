<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOpportunity extends Model
{
    public const STAGE_LEAD = 'lead';

    public const STAGE_QUALIFIED = 'qualified';

    public const STAGE_PROPOSAL = 'proposal';

    public const STAGE_WON = 'won';

    public const STAGE_LOST = 'lost';

    protected $fillable = [
        'company_id',
        'client_id',
        'project_id',
        'owner_id',
        'title',
        'stage',
        'expected_value',
        'probability',
        'expected_close_at',
        'next_follow_up_at',
        'source',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'expected_value' => 'decimal:2',
            'probability' => 'integer',
            'expected_close_at' => 'date',
            'next_follow_up_at' => 'datetime',
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(SalesFollowUp::class)->latest('due_at');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }
}
