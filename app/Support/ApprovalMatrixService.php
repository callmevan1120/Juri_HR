<?php

namespace App\Support;

use App\Models\ApprovalMatrixRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ApprovalMatrixService
{
    public function __construct(
        private readonly ApprovalActorService $approvalActor,
    ) {}

    public function matchingRule(string $workflow, Model $subject): ?ApprovalMatrixRule
    {
        return ApprovalMatrixRule::query()
            ->where('workflow', $workflow)
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get()
            ->first(fn (ApprovalMatrixRule $rule): bool => $this->matchesConditions($rule, $subject));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function initializeSteps(string $workflow, Model $subject): array
    {
        $rule = $this->matchingRule($workflow, $subject);

        return $rule?->steps ?? [];
    }

    public function ruleId(string $workflow, Model $subject): ?int
    {
        return $this->matchingRule($workflow, $subject)?->id;
    }

    /**
     * @param  list<array<string, mixed>>|null  $steps
     * @param  list<array<string, mixed>>|null  $completed
     */
    public function currentStep(?array $steps, ?array $completed): ?array
    {
        $completedKeys = collect($completed ?? [])->pluck('key')->filter()->all();

        foreach ($steps ?? [] as $step) {
            $key = (string) ($step['key'] ?? '');

            if ($key !== '' && ! in_array($key, $completedKeys, true)) {
                return $step;
            }
        }

        return null;
    }

    public function canActorApproveStep(User $actor, Model $subject, ?array $step): bool
    {
        if ($step === null) {
            return false;
        }

        return match ((string) ($step['approver_type'] ?? '')) {
            'direct_manager' => $this->requester($subject)?->supervisor?->id === $actor->id,
            'finance_head' => $this->approvalActor->isFinanceHead($actor),
            'permission' => $actor->allowsAdminPermission((string) ($step['permission'] ?? '')),
            'role' => $actor->hasRole((string) ($step['role'] ?? '')),
            'admin_permission' => $actor->allowsAdminPermission((string) ($step['permission'] ?? '')),
            default => false,
        };
    }

    public function canActorApprove(User $actor, string $workflow, Model $subject): bool
    {
        $steps = $this->storedOrResolvedSteps($workflow, $subject);

        if ($steps === []) {
            return false;
        }

        return $this->canActorApproveStep(
            $actor,
            $subject,
            $this->currentStep($steps, $this->completedSteps($subject)),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function storedOrResolvedSteps(string $workflow, Model $subject): array
    {
        $steps = $subject->getAttribute('approval_steps');

        if (is_array($steps) && $steps !== []) {
            return $steps;
        }

        return $this->initializeSteps($workflow, $subject);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function completedSteps(Model $subject): array
    {
        $completed = $subject->getAttribute('approval_completed_steps');

        return is_array($completed) ? $completed : [];
    }

    public function statusForStep(?array $step): string
    {
        return match ((string) ($step['key'] ?? '')) {
            'finance', 'finance_head' => 'pending_finance',
            default => 'pending_matrix',
        };
    }

    private function matchesConditions(ApprovalMatrixRule $rule, Model $subject): bool
    {
        $conditions = $rule->conditions ?? [];
        $requester = $this->requester($subject);
        $amount = $this->amount($subject);

        if (array_key_exists('min_amount', $conditions) && $amount < (float) $conditions['min_amount']) {
            return false;
        }

        if (array_key_exists('max_amount', $conditions) && $amount > (float) $conditions['max_amount']) {
            return false;
        }

        if (array_key_exists('division_id', $conditions) && (string) $requester?->division_id !== (string) $conditions['division_id']) {
            return false;
        }

        if (array_key_exists('requester_group', $conditions) && (string) $requester?->group !== (string) $conditions['requester_group']) {
            return false;
        }

        if (array_key_exists('requester_role', $conditions) && ! $requester?->hasRole((string) $conditions['requester_role'])) {
            return false;
        }

        return true;
    }

    private function requester(Model $subject): ?User
    {
        if (method_exists($subject, 'user')) {
            $subject->loadMissing('user');

            $user = $subject->getRelation('user');

            return $user instanceof User ? $user : null;
        }

        return null;
    }

    private function amount(Model $subject): float
    {
        foreach (['amount', 'total_amount', 'net_salary', 'purchase_cost', 'duration'] as $attribute) {
            $value = $subject->getAttribute($attribute);

            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return 0.0;
    }
}
