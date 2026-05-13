<?php

namespace Database\Seeders;

use App\Models\Attendance;
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
}
