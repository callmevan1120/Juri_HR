<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\IntegrationAttendanceEvent;
use App\Models\IntegrationClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ApiIntegrationManager extends Component
{
    use InteractsWithBanner;
    use WithPagination;

    public string $search = '';

    public string $preset = 'attendance';

    public string $name = '';

    public string $contactName = '';

    public string $contactEmail = '';

    public string $allowedSourcesText = '';

    public string $allowedIpsText = '';

    public string $expiresAt = '';

    /**
     * @var array<int, string>
     */
    public array $abilities = [IntegrationClient::ABILITY_ATTENDANCE_WRITE];

    public bool $showCredentialModal = false;

    public string $plainTextApiKey = '';

    public string $plainTextSecret = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'preset' => ['required', 'string', 'in:attendance,hris_read,schedule_read,custom'],
            'contactName' => ['nullable', 'string', 'max:120'],
            'contactEmail' => ['nullable', 'email', 'max:160'],
            'allowedSourcesText' => ['nullable', 'string', 'max:2000'],
            'allowedIpsText' => ['nullable', 'string', 'max:2000'],
            'expiresAt' => ['nullable', 'date', 'after:today'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['required', 'string', 'in:'.implode(',', $this->availableAbilities())],
        ];
    }

    public function boot(): void
    {
        Gate::authorize('manageApiIntegrations');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPreset(string $preset): void
    {
        if ($preset === 'custom') {
            return;
        }

        $this->abilities = $this->presetAbilities($preset);
    }

    public function save(): void
    {
        $this->validate();

        $allowedSources = $this->lines($this->allowedSourcesText);

        if ($allowedSources === []) {
            $allowedSources = [(string) Str::of($this->name)->slug('-')];
        }

        [$client, $apiKey, $secret] = IntegrationClient::issue([
            'name' => $this->name,
            'contact_name' => $this->blankToNull($this->contactName),
            'contact_email' => $this->blankToNull($this->contactEmail),
            'abilities' => array_values(array_unique($this->abilities)),
            'allowed_sources' => $allowedSources,
            'allowed_ips' => $this->lines($this->allowedIpsText),
            'expires_at' => $this->blankToNull($this->expiresAt),
            'created_by' => Auth::id(),
        ]);

        ActivityLog::record('Integration Client Created', "Admin created integration client {$client->name}.");

        $this->plainTextApiKey = $apiKey;
        $this->plainTextSecret = $secret;
        $this->showCredentialModal = true;
        $this->resetForm();
        $this->banner(__('Integration client created.'));
    }

    public function rotateSecret(string $clientId): void
    {
        $client = IntegrationClient::query()->findOrFail($clientId);
        [$apiKey, $secret] = $client->rotateCredentials();

        ActivityLog::record('Integration Client Rotated', "Admin rotated credentials for integration client {$client->name}.");

        $this->plainTextApiKey = $apiKey;
        $this->plainTextSecret = $secret;
        $this->showCredentialModal = true;
        $this->banner(__('Integration client credentials rotated.'));
    }

    public function revoke(string $clientId): void
    {
        $client = IntegrationClient::query()->findOrFail($clientId);

        $client->forceFill([
            'revoked_at' => now(),
        ])->save();

        ActivityLog::record('Integration Client Revoked', "Admin revoked integration client {$client->name}.");

        $this->banner(__('Integration client revoked.'));
    }

    public function restore(string $clientId): void
    {
        $client = IntegrationClient::query()->findOrFail($clientId);

        $client->forceFill([
            'revoked_at' => null,
        ])->save();

        ActivityLog::record('Integration Client Restored', "Admin restored integration client {$client->name}.");

        $this->banner(__('Integration client restored.'));
    }

    public function render()
    {
        return view('livewire.admin.api-integration-manager', [
            'clients' => IntegrationClient::query()
                ->when($this->search !== '', function ($query): void {
                    $query->where(function ($query): void {
                        $query
                            ->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('contact_email', 'like', '%'.$this->search.'%')
                            ->orWhere('contact_name', 'like', '%'.$this->search.'%');
                    });
                })
                ->orderByRaw('revoked_at is not null')
                ->orderByDesc('last_used_at')
                ->orderBy('name')
                ->paginate(10),
            'availableAbilities' => $this->availableAbilities(),
            'machineEndpoint' => url('/api/integrations/attendance-events'),
            'recentAttendanceEvents' => IntegrationAttendanceEvent::query()
                ->with('integrationClient')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function availableAbilities(): array
    {
        return [
            IntegrationClient::ABILITY_ATTENDANCE_WRITE,
            IntegrationClient::ABILITY_ATTENDANCE_READ,
            IntegrationClient::ABILITY_EMPLOYEES_READ,
            IntegrationClient::ABILITY_SCHEDULES_READ,
        ];
    }

    private function resetForm(): void
    {
        $this->reset([
            'name',
            'contactName',
            'contactEmail',
            'allowedSourcesText',
            'allowedIpsText',
            'expiresAt',
        ]);
        $this->preset = 'attendance';
        $this->abilities = [IntegrationClient::ABILITY_ATTENDANCE_WRITE];
    }

    /**
     * @return array<int, string>
     */
    private function lines(string $value): array
    {
        return collect(preg_split('/[\r\n,]+/', $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function blankToNull(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<int, string>
     */
    private function presetAbilities(string $preset): array
    {
        return match ($preset) {
            'hris_read' => [
                IntegrationClient::ABILITY_ATTENDANCE_READ,
                IntegrationClient::ABILITY_EMPLOYEES_READ,
                IntegrationClient::ABILITY_SCHEDULES_READ,
            ],
            'schedule_read' => [
                IntegrationClient::ABILITY_SCHEDULES_READ,
            ],
            default => [
                IntegrationClient::ABILITY_ATTENDANCE_WRITE,
            ],
        };
    }
}
