<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class UserSessionManagementService
{
    public function databaseSessionsAvailable(): bool
    {
        return config('session.driver') === 'database'
            && Schema::hasTable($this->table());
    }

    /**
     * @return Collection<int, User>
     */
    public function usersWithActiveSessions(User $actor, string $search = '', int $limit = 25): Collection
    {
        if (! $this->databaseSessionsAvailable()) {
            return collect();
        }

        $cutoff = $this->activeCutoff();
        $search = trim($search);

        return User::query()
            ->select('users.*')
            ->selectSub(function ($query) use ($cutoff): void {
                $query->from($this->table())
                    ->selectRaw('count(*)')
                    ->whereColumn("{$this->table()}.user_id", 'users.id')
                    ->where("{$this->table()}.last_activity", '>=', $cutoff);
            }, 'active_sessions_count')
            ->whereExists(function ($query) use ($cutoff): void {
                $query->from($this->table())
                    ->selectRaw('1')
                    ->whereColumn("{$this->table()}.user_id", 'users.id')
                    ->where("{$this->table()}.last_activity", '>=', $cutoff);
            })
            ->when(! $this->canUseGlobalScope($actor), fn (Builder $query) => $query->where('company_id', $actor->company_id))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, array{id: string, ip_address: ?string, user_agent: string, last_activity: Carbon, is_current_device: bool}>
     */
    public function activeSessionsFor(User $actor, User $target, ?string $currentSessionId = null): Collection
    {
        $this->ensureCanManageTarget($actor, $target);

        if (! $this->databaseSessionsAvailable()) {
            return collect();
        }

        return DB::table($this->table())
            ->where('user_id', $target->getKey())
            ->where('last_activity', '>=', $this->activeCutoff())
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn ($session): array => [
                'id' => (string) $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $this->friendlyUserAgent((string) $session->user_agent),
                'last_activity' => Carbon::createFromTimestamp((int) $session->last_activity),
                'is_current_device' => $currentSessionId !== null && hash_equals((string) $session->id, $currentSessionId),
            ]);
    }

    public function forgetSession(User $actor, User $target, string $sessionId, ?string $currentSessionId = null): int
    {
        $this->ensureCanManageTarget($actor, $target);

        if ($actor->is($target) && $currentSessionId !== null && hash_equals($sessionId, $currentSessionId)) {
            throw ValidationException::withMessages([
                'selectedUserId' => __('You cannot disconnect the current admin session from this page.'),
            ]);
        }

        $deleted = DB::table($this->table())
            ->where('user_id', $target->getKey())
            ->where('id', $sessionId)
            ->delete();

        if ($deleted > 0) {
            ActivityLog::record(
                'User Session Revoked',
                "Admin {$actor->email} revoked one active session for {$target->email}."
            );
        }

        return $deleted;
    }

    public function forgetAllSessions(User $actor, User $target, ?string $currentSessionId = null): int
    {
        $this->ensureCanManageTarget($actor, $target);

        $query = DB::table($this->table())->where('user_id', $target->getKey());

        if ($actor->is($target) && $currentSessionId !== null) {
            $query->where('id', '!=', $currentSessionId);
        }

        $deleted = $query->delete();

        if ($deleted > 0) {
            ActivityLog::record(
                'User Sessions Revoked',
                "Admin {$actor->email} revoked {$deleted} session(s) for {$target->email}."
            );
        }

        return $deleted;
    }

    public function ensureCanManageTarget(User $actor, User $target): void
    {
        if (! $actor->can('manageUserSessions')) {
            abort(403);
        }

        if ($target->isSuperadmin && ! $actor->isSuperadmin) {
            abort(403);
        }

        if (! $this->canUseGlobalScope($actor) && $actor->company_id !== $target->company_id) {
            abort(403);
        }
    }

    private function activeCutoff(): int
    {
        return now()->subMinutes((int) config('session.lifetime', 120))->getTimestamp();
    }

    private function table(): string
    {
        return config('session.table', 'sessions');
    }

    private function canUseGlobalScope(User $actor): bool
    {
        return $actor->isSuperadmin || $actor->hasGlobalAdminScope() || blank($actor->company_id);
    }

    private function friendlyUserAgent(string $userAgent): string
    {
        $userAgent = trim($userAgent);

        return $userAgent !== '' ? mb_strimwidth($userAgent, 0, 110, '...') : __('Unknown device');
    }
}
