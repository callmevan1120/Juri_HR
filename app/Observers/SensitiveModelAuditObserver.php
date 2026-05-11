<?php

namespace App\Observers;

use App\Support\SensitiveAuditTrail;
use Illuminate\Database\Eloquent\Model;

class SensitiveModelAuditObserver
{
    public function __construct(
        private readonly SensitiveAuditTrail $auditTrail,
    ) {}

    public function updated(Model $model): void
    {
        $this->auditTrail->recordModelUpdate($model);
    }
}
