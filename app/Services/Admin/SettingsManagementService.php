<?php

namespace App\Services\Admin;

use App\Console\Commands\EnterpriseHwId;
use App\Models\Setting;
use App\Services\Enterprise\LicenseGuard;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsManagementService
{
    /**
     * @return array{setting: Setting|null, license_state: array{setting_id: int|null, draft: string, validation: array<string, mixed>}}
     */
    public function updateValue(int|string $id, mixed $value): array
    {
        $setting = Setting::query()->find($id);

        if (! $setting instanceof Setting) {
            return [
                'setting' => null,
                'license_state' => $this->enterpriseLicenseState(),
            ];
        }

        if ($setting->type === 'boolean') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }

        $setting->update(['value' => $value]);
        Setting::flushCache($setting->key);

        if ($this->shouldRefreshEnterpriseLicense($setting->key)) {
            LicenseGuard::clearLicenseCache();

            return [
                'setting' => $setting,
                'license_state' => $this->enterpriseLicenseState($setting->key !== 'enterprise_license_key'),
            ];
        }

        return [
            'setting' => $setting,
            'license_state' => $this->enterpriseLicenseState(),
        ];
    }

    /**
     * @return array{setting: Setting, license_state: array{setting_id: int|null, draft: string, validation: array<string, mixed>}}
     */
    public function applyEnterpriseLicense(string $draft): array
    {
        $setting = Setting::query()->firstOrCreate(
            ['key' => 'enterprise_license_key'],
            [
                'value' => '',
                'group' => 'enterprise',
                'type' => 'textarea',
                'description' => 'Enterprise License Key',
            ],
        );

        $setting->update(['value' => trim($draft)]);
        Setting::flushCache($setting->key);
        LicenseGuard::clearLicenseCache();

        return [
            'setting' => $setting,
            'license_state' => $this->enterpriseLicenseState(),
        ];
    }

    /**
     * @return array{setting_id: int|null, draft: string, validation: array<string, mixed>}
     */
    public function enterpriseLicenseState(bool $reloadDraft = true, ?string $currentDraft = null): array
    {
        $setting = Setting::query()->where('key', 'enterprise_license_key')->first();
        $draft = $reloadDraft ? (string) ($setting?->value ?? '') : (string) $currentDraft;
        $validation = LicenseGuard::validateDetailed($draft);

        if (blank($draft)) {
            LicenseGuard::clearLicenseCache();
        } else {
            $cacheUntil = $this->licenseValidationCacheExpiration($validation);

            Cache::put('ent_lic_status', ($validation['valid'] ?? false) ? 'valid' : 'invalid', $cacheUntil);
            Cache::put('ent_lic_hash', hash('sha256', $draft), $cacheUntil);
            Cache::put('ent_lic_result', $validation, $cacheUntil);
        }

        return [
            'setting_id' => $setting?->id,
            'draft' => $draft,
            'validation' => $validation,
        ];
    }

    /**
     * @return Collection<string, Collection<int, Setting>>
     */
    public function groupedSettings(): Collection
    {
        return Setting::query()
            // Verification already implies enrollment when the user has no Face ID.
            // Keep the enrollment setting for backward compatibility, but avoid
            // exposing two near-identical admin toggles.
            ->where('key', '!=', 'attendance.require_face_enrollment')
            ->get()
            ->groupBy('group');
    }

    public function hardwareId(): string
    {
        return EnterpriseHwId::generate();
    }

    /**
     * @param  array<string, mixed>  $validation
     */
    private function licenseValidationCacheExpiration(array $validation): Carbon
    {
        if (! ($validation['valid'] ?? false)) {
            return now()->addMinutes(5);
        }

        $license = $validation['license'] ?? [];
        $expiresAt = is_array($license) ? ($license['expires_at'] ?? null) : null;

        if (is_string($expiresAt) && trim($expiresAt) !== '') {
            try {
                $licenseExpiry = Carbon::parse($expiresAt)->endOfDay();

                if ($licenseExpiry->isBefore(now()->addHours(24))) {
                    return $licenseExpiry;
                }
            } catch (\Throwable) {
                // Keep the default cache TTL if the validated date cannot be parsed here.
            }
        }

        return now()->addHours(24);
    }

    private function shouldRefreshEnterpriseLicense(string $settingKey): bool
    {
        return in_array($settingKey, ['app.company_name', 'app.support_contact', 'enterprise_license_key'], true);
    }
}
