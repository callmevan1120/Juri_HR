<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Support\UserSessionManagementService;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;

class UserSessionManager extends Component
{
    use InteractsWithBanner;

    public string $search = '';

    public ?string $selectedUserId = null;

    protected UserSessionManagementService $sessions;

    public function boot(UserSessionManagementService $sessions): void
    {
        Gate::authorize('manageUserSessions');

        $this->sessions = $sessions;
    }

    public function selectUser(string $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $this->sessions->ensureCanManageTarget(request()->user(), $user);
        $this->selectedUserId = $user->id;
    }

    public function forgetSession(string $sessionId): void
    {
        $target = $this->selectedUser();

        if (! $target instanceof User) {
            $this->dangerBanner(__('Select a user first.'));

            return;
        }

        $deleted = $this->sessions->forgetSession(
            request()->user(),
            $target,
            $sessionId,
            $this->currentSessionId()
        );

        $deleted > 0
            ? $this->banner(__('Selected session disconnected.'))
            : $this->dangerBanner(__('Session was already gone.'));
    }

    public function forgetAllSessions(): void
    {
        $target = $this->selectedUser();

        if (! $target instanceof User) {
            $this->dangerBanner(__('Select a user first.'));

            return;
        }

        $deleted = $this->sessions->forgetAllSessions(
            request()->user(),
            $target,
            $this->currentSessionId()
        );

        $this->banner(trans_choice('{0} No active sessions were found.|{1} One session disconnected.|[2,*] :count sessions disconnected.', $deleted, [
            'count' => $deleted,
        ]));
    }

    public function revokeApiToken(string $tokenId): void
    {
        $target = $this->selectedUser();

        if (! $target instanceof User) {
            $this->dangerBanner(__('Select a user first.'));

            return;
        }

        $deleted = $this->sessions->revokeApiToken(
            request()->user(),
            $target,
            $tokenId
        );

        $deleted > 0
            ? $this->banner(__('API token revoked.'))
            : $this->dangerBanner(__('API token was already gone.'));
    }

    public function render()
    {
        $actor = request()->user();
        $selectedUser = $this->selectedUser();
        $currentSessionId = $this->currentSessionId();

        return view('livewire.admin.user-session-manager', [
            'databaseSessionsAvailable' => $this->sessions->databaseSessionsAvailable(),
            'users' => $this->sessions->usersWithActiveSessions($actor, $this->search),
            'selectedUser' => $selectedUser,
            'activeSessions' => $selectedUser instanceof User
                ? $this->sessions->activeSessionsFor($actor, $selectedUser, $currentSessionId)
                : collect(),
            'apiTokens' => $selectedUser instanceof User
                ? $this->sessions->apiTokensFor($actor, $selectedUser)
                : collect(),
        ])->layout('layouts.app');
    }

    private function selectedUser(): ?User
    {
        if ($this->selectedUserId === null) {
            return null;
        }

        return User::query()->find($this->selectedUserId);
    }

    private function currentSessionId(): ?string
    {
        return request()->hasSession()
            ? request()->session()->getId()
            : null;
    }
}
