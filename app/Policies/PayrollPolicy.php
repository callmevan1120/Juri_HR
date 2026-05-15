<?php

namespace App\Policies;

use App\Helpers\Editions;
use App\Models\Payroll;
use App\Models\User;
use App\Support\MultiCompanyService;

class PayrollPolicy
{
    public function __construct(
        private readonly MultiCompanyService $multiCompany,
    ) {}

    public function viewAny(User $user): bool
    {
        return ! Editions::payrollLocked();
    }

    public function viewAdminAny(User $user): bool
    {
        return ! Editions::payrollLocked() && $user->can('viewAdminPayroll');
    }

    public function view(User $user, Payroll $payroll): bool
    {
        if (! $this->sameCompany($user, $payroll)) {
            return false;
        }

        return ! Editions::payrollLocked()
            && ($user->can('viewAdminPayroll') || $payroll->user_id === $user->id);
    }

    public function download(User $user, Payroll $payroll): bool
    {
        return $this->view($user, $payroll) && $payroll->status === 'paid';
    }

    protected function sameCompany(User $actor, Payroll $payroll): bool
    {
        $payroll->loadMissing('user');

        return $payroll->user !== null
            && $this->multiCompany->canAccessUser($actor, $payroll->user);
    }
}
