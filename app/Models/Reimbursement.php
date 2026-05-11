<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'type',
        'amount',
        'description',
        'attachment',
        'status',
        'approval_matrix_rule_id',
        'approval_steps',
        'approval_current_step',
        'approval_completed_steps',
        'admin_note',
        'approved_by',
        'head_approved_by',
        'head_approved_at',
        'finance_approved_by',
        'finance_approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'approval_steps' => 'array',
        'approval_completed_steps' => 'array',
        'head_approved_at' => 'datetime',
        'finance_approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function headApprover()
    {
        return $this->belongsTo(User::class, 'head_approved_by');
    }

    public function financeApprover()
    {
        return $this->belongsTo(User::class, 'finance_approved_by');
    }
}
