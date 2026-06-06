<?php

namespace App\Livewire\Admin;

use App\Services\Admin\SettingsManagementService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Settings extends Component
{
    public string $enterpriseLicenseDraft = '';

    public array $licenseValidation = [];

    public ?int $enterpriseLicenseSettingId = null;

    protected SettingsManagementService $settings;

    public function boot(SettingsManagementService $settings): void
    {
        $this->settings = $settings;
    }

    public function mount()
    {
        Gate::authorize('viewAdminSettings');

        $this->syncEnterpriseLicenseState();
    }

    public function updateValue($id, $value)
    {
        Gate::authorize('manageSystemSettings');

        if (auth()->user()?->is_demo) {
            $this->dispatch('error', message: __('Settings cannot be modified in demo mode.'));
            return;
        }

        $result = $this->settings->updateValue($id, $value);
        $this->hydrateEnterpriseLicenseState($result['license_state']);

        if ($result['setting']) {
            $this->dispatch('saved');
        }
    }

    public function applyEnterpriseLicense()
    {
        Gate::authorize('manageEnterpriseLicense');

        if (auth()->user()?->is_demo) {
            $this->dispatch('error', message: __('License cannot be modified in demo mode.'));
            return;
        }

        $result = $this->settings->applyEnterpriseLicense($this->enterpriseLicenseDraft);
        $this->hydrateEnterpriseLicenseState($result['license_state']);
        $this->dispatch('saved');
        $this->dispatch('enterprise-license-applied', reload: (bool) ($this->licenseValidation['valid'] ?? false));
    }

    private function syncEnterpriseLicenseState(bool $reloadDraft = true): void
    {
        $this->hydrateEnterpriseLicenseState(
            $this->settings->enterpriseLicenseState($reloadDraft, $this->enterpriseLicenseDraft),
        );
    }

    /**
     * @param  array{setting_id: int|null, draft: string, validation: array<string, mixed>}  $state
     */
    private function hydrateEnterpriseLicenseState(array $state): void
    {
        $this->enterpriseLicenseSettingId = $state['setting_id'];
        $this->enterpriseLicenseDraft = $state['draft'];
        $this->licenseValidation = $state['validation'];
    }

    public function render()
    {
        $licenseInfo = $this->licenseValidation['valid'] ?? false ? ($this->licenseValidation['license'] ?? null) : null;

        return view('livewire.admin.settings', [
            'groups' => $this->settings->groupedSettings(),
            'licenseInfo' => $licenseInfo,
            'licenseValidation' => $this->licenseValidation,
            'hwid' => $this->settings->hardwareId(),
        ]);
    }
}
