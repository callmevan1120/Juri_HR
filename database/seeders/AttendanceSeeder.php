<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if ($this->runningProduction() && ! $this->demoSeedingEnabled()) {
            $this->command?->warn('Skipping generated attendance demo data in production. Set DEMO_SEEDING_ENABLED=true only for staging/demo.');

            return;
        }

        $this->ensureDefaultShifts();
        $this->ensureDefaultBarcodes();

        $start = Carbon::now()->subDays(131);
        $end = Carbon::now();
        $dates = $start->range($end)->toArray();

        $statuses = ['present', 'present', 'present', 'present', 'late', 'excused', 'sick'];

        foreach ($dates as $date) {
            if ($date->isWeekend() && ! $date->isToday()) {
                continue;
            }

            /** @var User[] */
            $users = User::where('group', 'user')->get();

            foreach ($users as $user) {
                $status = fake()->randomElement($statuses);
                $attr = ['date' => $date->toDateString(), 'user_id' => $user->id];
                if (! Attendance::where($attr)->exists()) {
                    switch ($status) {
                        case 'present':
                            Attendance::factory()->present()->create($attr);
                            break;
                        case 'late':
                            Attendance::factory()->present(late: true)->create($attr);
                            break;
                        case 'excused':
                            Attendance::factory()->excused()->create($attr);
                            break;
                        case 'sick':
                            Attendance::factory()->excused(sick: true)->create($attr);
                            break;
                        default:
                            Attendance::factory()->absent()->create($attr);
                            break;
                    }
                }
            }
        }
    }

    private function demoSeedingEnabled(): bool
    {
        return filter_var(config('paspapan.demo_seeding_enabled', false), FILTER_VALIDATE_BOOL);
    }

    private function runningProduction(): bool
    {
        return app()->environment('production') || config('app.env') === 'production';
    }

    private function ensureDefaultShifts(): void
    {
        foreach ([
            ['name' => 'Shift Pagi', 'start_time' => '07:00', 'end_time' => '15:00'],
            ['name' => 'Shift Sore', 'start_time' => '15:00', 'end_time' => '23:00'],
            ['name' => 'Shift Malam', 'start_time' => '23:00', 'end_time' => '07:00'],
        ] as $shift) {
            Shift::query()->updateOrCreate([
                'name' => $shift['name'],
            ], $shift);
        }
    }

    private function ensureDefaultBarcodes(): void
    {
        foreach ([
            [
                'name' => 'Kantor Pusat',
                'value' => 'PASPAPAN-HQ-ATTENDANCE',
                'secret_key' => hash('sha256', 'PASPAPAN-HQ-ATTENDANCE'),
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius' => 75,
                'dynamic_enabled' => true,
                'dynamic_ttl_seconds' => 60,
            ],
            [
                'name' => 'Gudang Operasional',
                'value' => 'PASPAPAN-WAREHOUSE-ATTENDANCE',
                'secret_key' => hash('sha256', 'PASPAPAN-WAREHOUSE-ATTENDANCE'),
                'latitude' => -6.238270,
                'longitude' => 106.975570,
                'radius' => 100,
                'dynamic_enabled' => true,
                'dynamic_ttl_seconds' => 60,
            ],
        ] as $barcode) {
            Barcode::query()->updateOrCreate([
                'value' => $barcode['value'],
            ], $barcode);
        }
    }
}
