<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\CloudFile;
use App\Models\Company;
use App\Models\OnlineMeeting;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\Concerns\GuardsDemoSeeding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoCollaborationSeeder extends Seeder
{
    use GuardsDemoSeeding;

    public function run(): void
    {
        if ($this->shouldSkipDemoSeeding() || ! $this->hasRequiredTables()) {
            return;
        }

        $company = $this->company();
        $project = Project::query()->where('company_id', $company->id)->latest()->first();
        $owner = $this->owner($company);
        $members = User::query()
            ->where('company_id', $company->id)
            ->where('group', 'user')
            ->latest()
            ->limit(3)
            ->pluck('id')
            ->all();

        $thread = ChatThread::query()->updateOrCreate([
            'company_id' => $company->id,
            'title' => 'Koordinasi operasional harian',
        ], [
            'project_id' => $project?->id,
            'created_by' => $owner?->id,
            'type' => $project ? ChatThread::TYPE_PROJECT : ChatThread::TYPE_GROUP,
            'is_archived' => false,
            'metadata' => ['seeded' => true],
        ]);

        $thread->members()->syncWithoutDetaching(
            collect([$owner?->id, ...$members])
                ->filter()
                ->unique()
                ->mapWithKeys(fn (string $userId): array => [
                    $userId => ['role' => $userId === $owner?->id ? 'owner' : 'member'],
                ])
                ->all()
        );

        foreach ([
            'Selamat pagi, update progress kunjungan dan invoice hari ini di thread ini ya.',
            'Checklist lokasi sudah disiapkan, file briefing ada di workspace.',
        ] as $body) {
            ChatMessage::query()->updateOrCreate([
                'chat_thread_id' => $thread->id,
                'body' => $body,
            ], [
                'user_id' => $owner?->id,
                'metadata' => ['seeded' => true],
            ]);
        }

        CloudFile::query()->updateOrCreate([
            'company_id' => $company->id,
            'path' => 'collaboration/demo/briefing-operasional.pdf',
        ], [
            'project_id' => $project?->id,
            'chat_thread_id' => $thread->id,
            'owner_id' => $owner?->id,
            'disk' => 'local',
            'original_name' => 'briefing-operasional.pdf',
            'mime_type' => 'application/pdf',
            'size' => 256000,
            'visibility' => CloudFile::VISIBILITY_PROJECT,
            'metadata' => ['seeded' => true],
        ]);

        OnlineMeeting::query()->updateOrCreate([
            'company_id' => $company->id,
            'title' => 'Daily coordination sync',
        ], [
            'project_id' => $project?->id,
            'chat_thread_id' => $thread->id,
            'host_id' => $owner?->id,
            'provider' => 'external',
            'meeting_url' => 'https://meet.example.test/paspapan-demo',
            'starts_at' => now()->addDay()->setTime(9, 0),
            'ends_at' => now()->addDay()->setTime(9, 30),
            'status' => OnlineMeeting::STATUS_SCHEDULED,
            'notes' => 'Demo meeting link untuk koordinasi lintas HR, finance, CRM, dan operasional.',
            'metadata' => ['seeded' => true],
        ]);
    }

    private function hasRequiredTables(): bool
    {
        return collect([
            'companies',
            'users',
            'chat_threads',
            'chat_thread_user',
            'chat_messages',
            'cloud_files',
            'online_meetings',
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

    private function owner(Company $company): ?User
    {
        return User::query()
            ->where(fn ($query) => $query->where('company_id', $company->id)->orWhereNull('company_id'))
            ->whereIn('group', ['admin', 'superadmin'])
            ->orderByDesc('group')
            ->first()
            ?? User::query()->where('company_id', $company->id)->first();
    }
}
