<?php

namespace App\Queries\Hr;

use App\Models\HrChecklistCase;
use App\Models\HrChecklistTask;
use App\Models\HrChecklistTemplate;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class HrChecklistManagerQuery
{
    public function cases(string $typeFilter, string $statusFilter, string $search): LengthAwarePaginator
    {
        return HrChecklistCase::query()
            ->with(['user.division', 'user.jobTitle', 'template'])
            ->withCount([
                'tasks',
                'tasks as closed_tasks_count' => fn (Builder $query) => $query->whereIn('status', HrChecklistTask::closedStatuses()),
                'tasks as overdue_tasks_count' => fn (Builder $query) => $query->reminderReady(),
            ])
            ->when($typeFilter !== 'all', fn (Builder $query) => $query->where('type', $typeFilter))
            ->when($statusFilter !== 'all', fn (Builder $query) => $query->where('status', $statusFilter))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery
                        ->whereHas('user', fn (Builder $userQuery) => $userQuery
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('nip', 'like', '%'.$search.'%'))
                        ->orWhereHas('template', fn (Builder $templateQuery) => $templateQuery->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest('effective_date')
            ->latest()
            ->paginate(30);
    }

    public function selectedCase(?int $caseId): ?HrChecklistCase
    {
        if ($caseId === null) {
            return null;
        }

        return HrChecklistCase::with(['user.directManager', 'template', 'tasks.assignee', 'tasks.completer', 'tasks.dependency'])
            ->withCount([
                'tasks',
                'tasks as closed_tasks_count' => fn (Builder $query) => $query->whereIn('status', HrChecklistTask::closedStatuses()),
                'tasks as overdue_tasks_count' => fn (Builder $query) => $query->reminderReady(),
            ])
            ->find($caseId);
    }

    /**
     * @return Collection<int, HrChecklistTemplate>
     */
    public function templates(): Collection
    {
        return HrChecklistTemplate::with(['items', 'division', 'jobTitle'])
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, HrChecklistTemplate>
     */
    public function templateOptions(string $type): Collection
    {
        return HrChecklistTemplate::query()
            ->where('type', $type)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function employeeOptions(User $actor): Collection
    {
        return User::query()
            ->where('group', 'user')
            ->managedBy($actor)
            ->orderBy('name')
            ->get(['id', 'name', 'nip', 'division_id', 'job_title_id']);
    }
}
