<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalMatrixRule extends Model
{
    use HasFactory;

    public const WORKFLOW_REIMBURSEMENT = 'reimbursement';

    public const WORKFLOW_CASH_ADVANCE = 'cash_advance';

    protected $fillable = [
        'workflow',
        'name',
        'is_active',
        'priority',
        'conditions',
        'steps',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'conditions' => 'array',
            'steps' => 'array',
        ];
    }
}
