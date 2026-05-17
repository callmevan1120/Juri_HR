<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CustomFormSubmission;
use App\Models\CustomFormTemplate;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Database\Seeders\Concerns\GuardsDemoSeeding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoCustomFormSeeder extends Seeder
{
    use GuardsDemoSeeding;

    public function run(): void
    {
        if ($this->shouldSkipDemoSeeding() || ! $this->hasRequiredTables()) {
            return;
        }

        $company = $this->company();
        $project = Project::query()->where('company_id', $company->id)->orderBy('id')->first();
        $submitter = $this->submitter($company);

        if (! $submitter) {
            $this->command?->warn('No employee found for demo custom forms. Run FakeDataSeeder first.');

            return;
        }

        $template = CustomFormTemplate::query()->updateOrCreate([
            'company_id' => $company->id,
            'title' => 'Bukti Kunjungan Lokasi',
        ], [
            'category' => 'visit',
            'description' => 'Demo custom form untuk bukti kunjungan, kondisi lokasi, dan tindak lanjut.',
            'fields' => [
                [
                    'key' => 'lokasi',
                    'label' => 'Lokasi',
                    'type' => CustomFormTemplate::TYPE_TEXT,
                    'required' => true,
                    'options' => [],
                ],
                [
                    'key' => 'hasil_kunjungan',
                    'label' => 'Hasil Kunjungan',
                    'type' => CustomFormTemplate::TYPE_SELECT,
                    'required' => true,
                    'options' => ['Siap', 'Butuh Follow-up', 'Ditunda'],
                ],
                [
                    'key' => 'catatan',
                    'label' => 'Catatan',
                    'type' => CustomFormTemplate::TYPE_TEXTAREA,
                    'required' => false,
                    'options' => [],
                ],
            ],
            'is_active' => true,
            'metadata' => [
                'seeded' => true,
                'automation' => $project ? [
                    'type' => 'project_task',
                    'project_id' => $project->id,
                    'task_title' => 'Review bukti kunjungan lokasi',
                    'priority' => ProjectTask::PRIORITY_HIGH,
                ] : null,
            ],
        ]);

        CustomFormSubmission::query()->updateOrCreate([
            'custom_form_template_id' => $template->id,
            'company_id' => $company->id,
            'submitted_by' => $submitter->id,
        ], [
            'status' => CustomFormSubmission::STATUS_SUBMITTED,
            'payload' => [
                'lokasi' => 'Outlet ACME Sudirman',
                'hasil_kunjungan' => 'Butuh Follow-up',
                'catatan' => 'Signage belum tersedia, PIC meminta jadwal ulang pemasangan.',
            ],
            'metadata' => [
                'seeded' => true,
                'source' => 'demo_custom_form',
            ],
        ]);
    }

    private function hasRequiredTables(): bool
    {
        return collect([
            'companies',
            'users',
            'custom_form_templates',
            'custom_form_submissions',
        ])->every(fn (string $table): bool => Schema::hasTable($table));
    }

    private function company(): Company
    {
        return Company::query()->firstOrCreate([
            'slug' => 'paspapan-demo',
        ], [
            'name' => 'PasPapan Demo',
            'status' => Company::STATUS_ACTIVE,
            'metadata' => ['seeded' => true],
        ]);
    }

    private function submitter(Company $company): ?User
    {
        return User::query()
            ->where('company_id', $company->id)
            ->where('group', 'user')
            ->orderBy('email')
            ->first();
    }
}
