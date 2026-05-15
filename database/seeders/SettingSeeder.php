<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\DefaultApplicationSettings;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (DefaultApplicationSettings::all() as $setting) {
            if ($setting['key'] === 'enterprise_license_key' && $setting['value'] === '') {
                unset($setting['value']);
            }

            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
