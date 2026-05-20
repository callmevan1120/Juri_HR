<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingTaxFiling extends Model
{
    public const TYPE_PPN_OUTPUT = 'ppn_output';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_FILED = 'filed';

    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'company_id',
        'period_start',
        'period_end',
        'tax_type',
        'taxable_turnover',
        'output_tax',
        'input_tax',
        'net_tax_payable',
        'status',
        'prepared_by',
        'prepared_at',
        'filed_by',
        'filed_at',
        'paid_by',
        'paid_at',
        'filing_reference',
        'payment_reference',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'taxable_turnover' => 'float',
            'output_tax' => 'float',
            'input_tax' => 'float',
            'net_tax_payable' => 'float',
            'prepared_at' => 'datetime',
            'filed_at' => 'datetime',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function filedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
