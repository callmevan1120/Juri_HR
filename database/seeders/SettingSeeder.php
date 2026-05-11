<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'security.rate_limit_global',
                'value' => '1000',
                'group' => 'security',
                'type' => 'number',
                'description' => 'Global API rate limit per minute',
            ],
            [
                'key' => 'security.rate_limit_login',
                'value' => '5',
                'group' => 'security',
                'type' => 'number',
                'description' => 'Login rate limit per minute',
            ],
            [
                'key' => 'attendance.grace_period',
                'value' => '10',
                'group' => 'attendance',
                'type' => 'number',
                'description' => 'Late Grace Period (minutes)',
            ],
            [
                'key' => 'app.name',
                'value' => config('app.name', 'PasPapan'),
                'group' => 'identity',
                'type' => 'text',
                'description' => 'Application Name',
            ],
            [
                'key' => 'app.company_name',
                'value' => 'PT. PasPapan Indonesia',
                'group' => 'identity',
                'type' => 'text',
                'description' => 'Company Name for Reports',
            ],
            [
                'key' => 'app.support_contact',
                'value' => 'https://t.me/RiprLutuk',
                'group' => 'identity',
                'type' => 'text',
                'description' => 'Support Telegram',
            ],
            [
                'key' => 'feature.require_photo',
                'value' => '1',
                'group' => 'features',
                'type' => 'boolean',
                'description' => 'Require Photo for Attendance',
            ],
            [
                'key' => 'attendance.require_face_enrollment',
                'value' => '0',
                'group' => 'attendance',
                'type' => 'boolean',
                'description' => 'Require Face ID enrollment before attendance',
            ],
            [
                'key' => 'attendance.require_face_verification',
                'value' => '1',
                'group' => 'attendance',
                'type' => 'boolean',
                'description' => 'Require Face ID verification during attendance capture',
            ],
            [
                'key' => 'app.maintenance_mode',
                'value' => '0',
                'group' => 'features',
                'type' => 'boolean',
                'description' => 'Enable Maintenance Mode',
            ],
            [
                'key' => 'app.time_format',
                'value' => '24',
                'group' => 'general',
                'type' => 'select',
                'description' => 'Time Format (12h/24h)',
            ],
            [
                'key' => 'app.show_seconds',
                'value' => '0',
                'group' => 'general',
                'type' => 'boolean',
                'description' => 'Show Seconds in Time Display',
            ],
            [
                'key' => 'leave.annual_quota',
                'value' => '12',
                'group' => 'leave',
                'type' => 'number',
                'description' => 'Jatah Cuti Tahunan (hari)',
            ],
            [
                'key' => 'leave.require_attachment',
                'value' => '0',
                'group' => 'leave',
                'type' => 'boolean',
                'description' => 'Wajib Lampiran untuk Pengajuan Cuti/Sakit',
            ],
            [
                'key' => 'leave.auto_approve_days',
                'value' => '3',
                'group' => 'leave',
                'type' => 'number',
                'description' => 'Auto-Approve jika tidak diproses dalam X hari (0 = disabled)',
            ],
            [
                'key' => 'notif.admin_email',
                'value' => 'example@gmail.com',
                'group' => 'notification',
                'type' => 'text',
                'description' => 'Email Admin untuk Notifikasi (kosongkan jika tidak ada)',
            ],
            [
                'key' => 'attendance.work_hours_per_day',
                'value' => '8',
                'group' => 'attendance',
                'type' => 'number',
                'description' => 'Jam Kerja per Hari',
            ],
            [
                'key' => 'app.company_address',
                'value' => 'Jalan example, example, example, example, example',
                'group' => 'identity',
                'type' => 'textarea',
                'description' => 'Company Address',
            ],
            [
                'key' => 'enterprise_license_key',
                'value' => '',
                'group' => 'enterprise',
                'type' => 'textarea',
                'description' => 'Enterprise License Key',
            ],
            [
                'key' => 'appraisal.attendance_weight',
                'value' => '30',
                'group' => 'appraisal',
                'type' => 'number',
                'description' => 'Bobot Skor Absensi dalam Penilaian Appraisal (%)',
            ],
            [
                'key' => 'payroll.country',
                'value' => 'ID',
                'group' => 'payroll',
                'type' => 'text',
                'description' => 'Payroll localization country code',
            ],
            [
                'key' => 'payroll.tax_method',
                'value' => 'pph21_ter',
                'group' => 'payroll',
                'type' => 'select',
                'description' => 'Default Indonesian payroll tax method',
            ],
            [
                'key' => 'payroll.bpjs_kesehatan_cap',
                'value' => '12000000',
                'group' => 'payroll',
                'type' => 'number',
                'description' => 'BPJS Kesehatan wage cap for payroll calculation',
            ],
            [
                'key' => 'payroll.bpjs_jp_cap',
                'value' => '10000000',
                'group' => 'payroll',
                'type' => 'number',
                'description' => 'BPJS Ketenagakerjaan JP wage cap for payroll calculation',
            ],
            [
                'key' => 'payroll.thr_prorata_enabled',
                'value' => '1',
                'group' => 'payroll',
                'type' => 'boolean',
                'description' => 'Enable prorated THR calculation for eligible employees',
            ],
            [
                'key' => 'payroll.bank_instruction_format',
                'value' => 'generic_csv',
                'group' => 'payroll',
                'type' => 'select',
                'description' => 'Default payroll payment instruction export format',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
