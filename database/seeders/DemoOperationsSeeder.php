<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\CompanyBranch;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskChecklistItem;
use App\Models\ProjectVisitEvidence;
use App\Models\User;
use Database\Seeders\Concerns\GuardsDemoSeeding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoOperationsSeeder extends Seeder
{
    use GuardsDemoSeeding;

    public function run(): void
    {
        if ($this->shouldSkipDemoSeeding() || ! $this->hasRequiredTables()) {
            return;
        }

        $company = $this->company();
        $manager = $this->manager($company);
        $staff = $this->staff($company);

        $branch = CompanyBranch::query()->updateOrCreate([
            'company_id' => $company->id,
            'code' => 'JKT-HQ',
        ], [
            'name' => 'Jakarta Head Office',
            'type' => 'head_office',
            'status' => CompanyBranch::STATUS_ACTIVE,
            'address' => 'Jl. Sudirman Kav. 1, Jakarta',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
            'metadata' => ['seeded' => true],
        ]);

        $client = Client::query()->updateOrCreate([
            'company_id' => $company->id,
            'code' => 'CL-ACME',
        ], [
            'name' => 'PT Acme Nusantara',
            'status' => Client::STATUS_ACTIVE,
            'contact_name' => 'Rani Procurement',
            'contact_email' => 'procurement@acme.test',
            'contact_phone' => '081234560001',
            'address' => 'Kawasan Bisnis TB Simatupang, Jakarta',
            'metadata' => ['seeded' => true],
        ]);

        $project = Project::query()->updateOrCreate([
            'company_id' => $company->id,
            'code' => 'PRJ-OPS-001',
        ], [
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'manager_id' => $manager?->id,
            'name' => 'ACME Site Rollout',
            'status' => Project::STATUS_ACTIVE,
            'starts_at' => now()->subWeeks(2)->toDateString(),
            'ends_at' => now()->addWeeks(6)->toDateString(),
            'description' => 'Demo project for operational tasks, checklist, and visit evidence.',
            'metadata' => ['seeded' => true],
        ]);

        $task = ProjectTask::query()->updateOrCreate([
            'project_id' => $project->id,
            'title' => 'Survey lokasi outlet pertama',
        ], [
            'company_id' => $company->id,
            'assigned_to' => $staff?->id,
            'status' => ProjectTask::STATUS_IN_PROGRESS,
            'priority' => ProjectTask::PRIORITY_HIGH,
            'due_date' => now()->addDays(3)->toDateString(),
            'description' => 'Kumpulkan bukti foto, koordinat, dan checklist kesiapan lokasi.',
            'metadata' => ['seeded' => true],
            'completed_at' => null,
        ]);

        foreach (['Foto tampak depan', 'Validasi koordinat GPS', 'Konfirmasi PIC outlet'] as $index => $title) {
            ProjectTaskChecklistItem::query()->updateOrCreate([
                'project_task_id' => $task->id,
                'title' => $title,
            ], [
                'is_done' => $index === 0,
                'sort_order' => $index + 1,
                'completed_at' => $index === 0 ? now()->subDay() : null,
            ]);
        }

        if ($staff) {
            ProjectVisitEvidence::query()->updateOrCreate([
                'company_id' => $company->id,
                'project_id' => $project->id,
                'project_task_id' => $task->id,
                'user_id' => $staff->id,
                'address' => 'Outlet ACME Sudirman',
            ], [
                'visited_at' => now()->subDay(),
                'latitude' => -6.214620,
                'longitude' => 106.845130,
                'accuracy_meters' => 18,
                'notes' => 'Lokasi sudah sesuai, butuh follow-up pemasangan signage.',
                'metadata' => ['seeded' => true],
            ]);
        }
    }

    private function hasRequiredTables(): bool
    {
        return collect([
            'companies',
            'company_branches',
            'clients',
            'projects',
            'project_tasks',
            'project_task_checklist_items',
            'project_visit_evidences',
            'users',
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

    private function manager(Company $company): ?User
    {
        return User::query()
            ->where(fn ($query) => $query->where('company_id', $company->id)->orWhereNull('company_id'))
            ->whereIn('group', ['admin', 'superadmin', 'user'])
            ->whereNotNull('manager_id')
            ->orderBy('group')
            ->first()
            ?? User::query()->where('company_id', $company->id)->first();
    }

    private function staff(Company $company): ?User
    {
        return User::query()
            ->where('company_id', $company->id)
            ->where('group', 'user')
            ->latest()
            ->first();
    }
}
