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

    public function mount(): void
    {
        $this->applyPresetDefaults($this->preset, force: true);
    }

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
        $this->applyPresetDefaults($preset, force: true);
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
            'integrationPresets' => $this->integrationPresets(),
            'activePreset' => $this->integrationPresets()[$this->preset] ?? $this->integrationPresets()['custom'],
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
        $this->applyPresetDefaults('attendance', force: true);
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

    private function applyPresetDefaults(string $preset, bool $force = false): void
    {
        $config = $this->integrationPresets()[$preset] ?? $this->integrationPresets()['custom'];

        if ($force || trim($this->name) === '') {
            $this->name = $config['default_name'];
        }

        if ($force || trim($this->allowedSourcesText) === '') {
            $this->allowedSourcesText = $config['default_source'];
        }

        $this->abilities = $config['abilities'];
    }

    /**
     * @return array<string, array{label: string, description: string, default_name: string, default_source: string, capabilities: array<int, string>, abilities: array<int, string>}>
     */
    private function integrationPresets(): array
    {
        return [
            'attendance' => [
                'label' => __('Attendance machine / vendor write'),
                'description' => __('For fingerprint machines, kiosks, vendor bridges, or attendance devices.'),
                'default_name' => __('Mesin Absensi / Kiosk'),
                'default_source' => 'mesin-absensi',
                'capabilities' => [
                    __('Can send check-in/check-out attendance events from machines, kiosks, or vendor bridges.'),
                    __('Can include employee code, event time, machine ID, and optional coordinates.'),
                    __('Cannot access user sessions or employee self-service APIs.'),
                ],
                'abilities' => [IntegrationClient::ABILITY_ATTENDANCE_WRITE],
            ],
            'hris_read' => [
                'label' => __('HRIS read-only sync'),
                'description' => __('For HRIS, BI, or reporting systems that only need to read operational data.'),
                'default_name' => __('HRIS Read-only Sync'),
                'default_source' => 'hris-sync',
                'capabilities' => [
                    __('Can read attendance, employee, and schedule data for reporting or HRIS synchronization.'),
                    __('Cannot write attendance events or mutate PasPapan records.'),
                ],
                'abilities' => [
                    IntegrationClient::ABILITY_ATTENDANCE_READ,
                    IntegrationClient::ABILITY_EMPLOYEES_READ,
                    IntegrationClient::ABILITY_SCHEDULES_READ,
                ],
            ],
            'schedule_read' => [
                'label' => __('Schedule read-only sync'),
                'description' => __('For external workforce planners that only need schedule data.'),
                'default_name' => __('Schedule Read-only Sync'),
                'default_source' => 'schedule-sync',
                'capabilities' => [
                    __('Can read schedule data for planning or external roster displays.'),
                    __('Cannot read employee details beyond allowed schedule payloads.'),
                ],
                'abilities' => [IntegrationClient::ABILITY_SCHEDULES_READ],
            ],
            'custom' => [
                'label' => __('Custom scopes'),
                'description' => __('For advanced integrations that need manually selected scopes.'),
                'default_name' => __('Custom Integration Client'),
                'default_source' => 'custom-integration',
                'capabilities' => [
                    __('Capabilities depend on the scopes selected below.'),
                    __('Use the smallest scope set that the integration actually needs.'),
                ],
                'abilities' => [IntegrationClient::ABILITY_ATTENDANCE_WRITE],
            ],
        ];
    }
}
