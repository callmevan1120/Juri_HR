<?php

namespace App\Support;

use App\Models\User;

class ManagerHierarchyGuard
{
    /**
     * Determine whether assigning $managerId as the direct manager of
     * $employee would introduce a circular reporting line by walking the
     * prospective manager's reporting chain upwards.
     */
    public function wouldCreateCycle(User $employee, int|string|null $managerId): bool
    {
        if (blank($managerId)) {
            return false;
        }

        $visited = [];
        $manager = User::query()
            ->select(['id', 'manager_id'])
            ->find($managerId);

        while ($manager !== null) {
            if ($manager->id === $employee->id) {
                return true;
            }

            if (blank($manager->manager_id) || in_array($manager->id, $visited, true)) {
                return false;
            }

            $visited[] = $manager->id;
            $manager = User::query()
                ->select(['id', 'manager_id'])
                ->find($manager->manager_id);
        }

        return false;
    }
}
