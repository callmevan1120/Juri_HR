<?php

namespace App\Policies;

use App\Models\Reimbursement;
use App\Models\User;
use App\Support\ApprovalMatrixService;
use App\Support\MultiCompanyService;

class ReimbursementPolicy
{
    public function __construct(
        private readonly ApprovalMatrixService $approvalMatrix,
        private readonly MultiCompanyService $multiCompany,
    ) {}

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function viewAdminAny(User $user): bool
    {
        return $user->can('viewAdminReimbursements');
    }

    public function view(User $user, Reimbursement $reimbursement): bool
    {
        if (! $this->sameCompany($user, $reimbursement)) {
            return false;
        }

        return $user->can('viewAdminReimbursements')
            || $reimbursement->user_id === $user->id
            || $this->canReview($user, $reimbursement);
    }

    public function create(User $user): bool
    {
        return $user->isUser;
    }

    public function approve(User $user, Reimbursement $reimbursement): bool
    {
        if (! $this->sameCompany($user, $reimbursement)) {
            return false;
        }

        return $user->allowsAdminPermission('admin.reimbursements.approve')
            || $this->canReview($user, $reimbursement);
    }

    public function reject(User $user, Reimbursement $reimbursement): bool
    {
        return $this->approve($user, $reimbursement);
    }

    private function canReview(User $user, Reimbursement $reimbursement): bool
    {
        if (! $this->sameCompany($user, $reimbursement)) {
            return false;
        }

        if ($this->approvalMatrix->canActorApprove($user, 'reimbursement', $reimbursement)) {
            return true;
        }

        if ($user->subordinates->contains('id', $reimbursement->user_id)) {
            return true;
        }

        return $this->isFinanceHead($user) && $reimbursement->status === 'pending_finance';
    }

    private function isFinanceHead(User $user): bool
    {
        return (int) ($user->jobTitle?->jobLevel?->rank ?? 99) <= 2
            && strtolower((string) $user->division?->name) === 'finance';
    }

    private function sameCompany(User $actor, Reimbursement $reimbursement): bool
    {
        $reimbursement->loadMissing('user');

        return $reimbursement->user !== null
            && $this->multiCompany->canAccessUser($actor, $reimbursement->user);
    }
}
