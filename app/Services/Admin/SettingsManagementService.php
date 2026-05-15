<?php

namespace App\Services\Admin;

use App\Console\Commands\EnterpriseHwId;
use App\Models\Setting;
use App\Services\Enterprise\LicenseGuard;
use App\Support\DefaultApplicationSettings;
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
        $setting = $this->enterpriseLicenseSetting();

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
        $setting = $this->enterpriseLicenseSetting();
        $draft = $reloadDraft ? (string) ($setting?->value ?? '') : (string) $currentDraft;
        $validation = LicenseGuard::validateDetailed($draft);

        if (blank($draft)) {
            LicenseGuard::clearLicenseCache();
        } else {
            $cacheUntil = $this->licenseValidationCacheExpiration($validation);

            Cache::put('ent_lic_status', ($validation['valid'] ?? false) ? 'valid' : 'invalid', $cacheUntil);
            Cache::put('ent_lic_hash', LicenseGuard::cacheFingerprint($draft), $cacheUntil);
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
        $this->ensureDefaultSettings();

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

    private function enterpriseLicenseSetting(): Setting
    {
        $setting = Setting::query()->firstOrNew(['key' => 'enterprise_license_key']);

        if (! $setting->exists || blank($setting->value)) {
            $setting->value = config('app.enterprise_license_key') ?: '';
        }

        $setting->group = 'enterprise';
        $setting->type = 'textarea';
        $setting->description = 'Enterprise License Key';

        if (! $setting->exists || $setting->isDirty()) {
            $setting->save();
        }

        return $setting;
    }

    private function ensureDefaultSettings(): void
    {
        foreach (DefaultApplicationSettings::all() as $default) {
            $setting = Setting::query()->firstOrNew(['key' => $default['key']]);

            if (! $setting->exists) {
                $setting->value = $default['value'];
            }

            if ($default['key'] === 'enterprise_license_key' && blank($setting->value)) {
                $setting->value = config('app.enterprise_license_key') ?: '';
            }

            $setting->group = $default['group'];
            $setting->type = $default['type'];
            $setting->description = $default['description'];

            if (! $setting->exists || $setting->isDirty()) {
                $setting->save();
            }
        }
    }
}
