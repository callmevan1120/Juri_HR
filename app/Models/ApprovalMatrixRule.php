<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalMatrixRule extends Model
{
    use HasFactory;

    public const WORKFLOW_REIMBURSEMENT = 'reimbursement';

    public const WORKFLOW_CASH_ADVANCE = 'cash_advance';

    public const WORKFLOW_LEAVE = 'leave';

    public const WORKFLOW_OVERTIME = 'overtime';

    public const WORKFLOW_ATTENDANCE_CORRECTION = 'attendance_correction';

    public const WORKFLOW_SHIFT_SWAP = 'shift_swap';

    public const WORKFLOW_ASSET = 'asset';

    public const WORKFLOW_DOCUMENT_REQUEST = 'document_request';

    public const WORKFLOW_PAYROLL_SENSITIVE_ACTION = 'payroll_sensitive_action';

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
