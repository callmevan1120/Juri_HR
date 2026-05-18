<?php

use App\Events\CollaborationWorkspaceUpdated;
use App\Livewire\Admin\CollaborationWorkspace;
use App\Livewire\User\CollaborationInbox;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\CloudFile;
use App\Models\OnlineMeeting;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\CollaborationWorkspaceService;
use App\Support\MultiCompanyService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('admin can manage company scoped collaboration workspace', function () {
    Storage::fake('local');

    $admin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Collaboration');
    $member = User::factory()->create(['company_id' => $company->id]);
    $project = Project::query()->create([
        'company_id' => $company->id,
        'name' => 'Collaboration Rollout',
        'status' => Project::STATUS_ACTIVE,
    ]);

    $this->actingAs($admin);

    Livewire::test(CollaborationWorkspace::class)
        ->set('threadCompanyId', (string) $company->id)
        ->set('threadProjectId', (string) $project->id)
        ->set('threadType', ChatThread::TYPE_PROJECT)
        ->set('threadTitle', 'Project sync')
        ->set('threadMemberIds', [$member->id])
        ->call('createThread')
        ->assertHasNoErrors();

    $thread = ChatThread::query()->where('title', 'Project sync')->firstOrFail();

    Livewire::test(CollaborationWorkspace::class)
        ->set('messageThreadId', (string) $thread->id)
        ->set('messageBody', 'Kickoff notes are ready.')
        ->call('postMessage')
        ->assertHasNoErrors()
        ->set('activeTab', 'files')
        ->set('fileCompanyId', (string) $company->id)
        ->set('fileProjectId', (string) $project->id)
        ->set('fileThreadId', (string) $thread->id)
        ->set('uploadedFile', UploadedFile::fake()->create('kickoff.pdf', 100, 'application/pdf'))
        ->set('fileVisibility', CloudFile::VISIBILITY_PROJECT)
        ->call('registerFile')
        ->assertHasNoErrors()
        ->set('activeTab', 'meetings')
        ->set('meetingCompanyId', (string) $company->id)
        ->set('meetingProjectId', (string) $project->id)
        ->set('meetingThreadId', (string) $thread->id)
        ->set('meetingTitle', 'Weekly Sync')
        ->set('meetingProvider', 'jitsi')
        ->set('meetingUrl', 'https://meet.example.test/project-sync')
        ->call('scheduleMeeting')
        ->assertHasNoErrors();

    $file = CloudFile::query()->where('company_id', $company->id)->where('project_id', $project->id)->firstOrFail();

    Storage::disk('local')->assertExists($file->path);

    expect($thread->members()->whereKey($member->id)->exists())->toBeTrue()
        ->and(ChatMessage::query()->where('chat_thread_id', $thread->id)->count())->toBe(1)
        ->and($file->original_name)->toBe('kickoff.pdf')
        ->and(OnlineMeeting::query()->where('title', 'Weekly Sync')->where('company_id', $company->id)->exists())->toBeTrue();
});

test('tenant scoped admin cannot create collaboration records for another company', function () {
    $admin = User::factory()->admin()->create();
    $companyA = app(MultiCompanyService::class)->createCompany('PT Collaboration A', $admin);
    $companyB = app(MultiCompanyService::class)->createCompany('PT Collaboration B');

    $role = Role::query()->create([
        'name' => 'Collaboration Manager',
        'slug' => 'collaboration_manager',
        'permissions' => ['admin.collaboration.view', 'admin.collaboration.manage'],
    ]);
    $admin->roles()->sync([$role->id]);

    $this->actingAs($admin->fresh());

    Livewire::test(CollaborationWorkspace::class)
        ->set('threadCompanyId', (string) $companyB->id)
        ->set('threadTitle', 'Cross tenant thread')
        ->call('createThread')
        ->assertForbidden();

    expect(ChatThread::query()->where('company_id', $companyB->id)->exists())->toBeFalse()
        ->and($admin->fresh()->company_id)->toBe($companyA->id);
});

test('collaboration route requires explicit permission', function () {
    $admin = User::factory()->admin()->create();
    $admin->roles()->detach();

    $this->actingAs($admin)
        ->get(route('admin.collaboration'))
        ->assertForbidden();

    $role = Role::query()->create([
        'name' => 'Collaboration Viewer',
        'slug' => 'collaboration_viewer',
        'permissions' => ['admin.collaboration.view'],
    ]);
    $admin->roles()->sync([$role->id]);

    $this->actingAs($admin->fresh())
        ->get(route('admin.collaboration'))
        ->assertOk();
});

test('collaboration file download is private and company scoped', function () {
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $companyA = app(MultiCompanyService::class)->createCompany('PT File Scope A', $admin);
    $companyB = app(MultiCompanyService::class)->createCompany('PT File Scope B');
    $otherAdmin = User::factory()->admin()->create(['company_id' => $companyB->id]);
    $role = Role::query()->create([
        'name' => 'Collaboration File Manager',
        'slug' => 'collaboration_file_manager',
        'permissions' => ['admin.collaboration.view', 'admin.collaboration.manage'],
    ]);
    $admin->roles()->sync([$role->id]);
    $otherAdmin->roles()->sync([$role->id]);

    Storage::disk('local')->put('collaboration/private/report.pdf', 'pdf-content');

    $file = CloudFile::query()->create([
        'company_id' => $companyA->id,
        'owner_id' => $admin->id,
        'disk' => 'local',
        'path' => 'collaboration/private/report.pdf',
        'original_name' => 'report.pdf',
        'mime_type' => 'application/pdf',
        'size' => 11,
        'visibility' => CloudFile::VISIBILITY_COMPANY,
    ]);

    $this->actingAs($admin->fresh())
        ->get(route('admin.collaboration.files.download', $file))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    $this->actingAs($otherAdmin->fresh())
        ->get(route('admin.collaboration.files.download', $file))
        ->assertForbidden();
});

test('collaboration upload rejects unsupported file types', function () {
    Storage::fake('local');

    $admin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Upload Guard');

    $this->actingAs($admin);

    Livewire::test(CollaborationWorkspace::class)
        ->set('activeTab', 'files')
        ->set('fileCompanyId', (string) $company->id)
        ->set('uploadedFile', UploadedFile::fake()->create('payload.exe', 20, 'application/x-msdownload'))
        ->call('registerFile')
        ->assertHasErrors(['uploadedFile']);

    expect(CloudFile::query()->where('company_id', $company->id)->exists())->toBeFalse();
});

test('collaboration realtime broadcasts workspace updates only when enabled for vps mode', function () {
    Event::fake([CollaborationWorkspaceUpdated::class]);

    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-reverb-key',
        'broadcasting.connections.reverb.options.host' => '127.0.0.1',
        'broadcasting.connections.reverb.options.port' => 8080,
        'broadcasting.connections.reverb.options.scheme' => 'http',
        'realtime.collaboration.enabled' => true,
    ]);

    $admin = User::factory()->admin(true)->create();
    $company = app(MultiCompanyService::class)->createCompany('PT Realtime Collaboration');
    $thread = ChatThread::query()->create([
        'company_id' => $company->id,
        'created_by' => $admin->id,
        'type' => ChatThread::TYPE_GROUP,
        'title' => 'Realtime room',
    ]);
    $thread->members()->sync([$admin->id => ['role' => 'owner']]);

    app(CollaborationWorkspaceService::class)
        ->postMessage($admin, $thread, 'Realtime update.');

    Event::assertDispatched(
        CollaborationWorkspaceUpdated::class,
        fn (CollaborationWorkspaceUpdated $event): bool => $event->companyId === $company->id
            && $event->action === 'message.created'
            && $event->resource === 'message',
    );

    Event::fake([CollaborationWorkspaceUpdated::class]);

    config(['realtime.collaboration.enabled' => false]);

    app(CollaborationWorkspaceService::class)
        ->postMessage($admin, $thread, 'Fallback update.');

    Event::assertNotDispatched(CollaborationWorkspaceUpdated::class);
});

test('user can read assigned collaboration thread post message and download shared file', function () {
    Storage::fake('local');

    $company = app(MultiCompanyService::class)->createCompany('PT User Collaboration');
    $user = User::factory()->create(['company_id' => $company->id]);
    $otherUser = User::factory()->create(['company_id' => $company->id]);
    $thread = ChatThread::query()->create([
        'company_id' => $company->id,
        'created_by' => $otherUser->id,
        'type' => ChatThread::TYPE_GROUP,
        'title' => 'Field ops room',
    ]);
    $thread->members()->sync([
        $user->id => ['role' => 'member'],
        $otherUser->id => ['role' => 'owner'],
    ]);

    Storage::disk('local')->put('collaboration/user/thread-note.pdf', 'thread-file');
    $file = CloudFile::query()->create([
        'company_id' => $company->id,
        'chat_thread_id' => $thread->id,
        'owner_id' => $otherUser->id,
        'disk' => 'local',
        'path' => 'collaboration/user/thread-note.pdf',
        'original_name' => 'thread-note.pdf',
        'mime_type' => 'application/pdf',
        'size' => 11,
        'visibility' => CloudFile::VISIBILITY_THREAD,
    ]);

    $this->actingAs($user)
        ->get(route('collaboration'))
        ->assertOk()
        ->assertSee('Field ops room');

    Livewire::actingAs($user)
        ->test(CollaborationInbox::class, ['selectedThreadId' => (string) $thread->id])
        ->set('messageBody', 'Siap, saya update dari lapangan.')
        ->set('uploadedFile', UploadedFile::fake()->create('visit-note.pdf', 80, 'application/pdf'))
        ->call('postMessage')
        ->assertHasNoErrors();

    $uploadedFile = CloudFile::query()
        ->where('chat_thread_id', $thread->id)
        ->where('owner_id', $user->id)
        ->where('original_name', 'visit-note.pdf')
        ->firstOrFail();

    Storage::disk('local')->assertExists($uploadedFile->path);

    expect(ChatMessage::query()->where('chat_thread_id', $thread->id)->where('user_id', $user->id)->exists())->toBeTrue()
        ->and($uploadedFile->visibility)->toBe(CloudFile::VISIBILITY_THREAD);

    $this->actingAs($user)
        ->get(route('collaboration.files.download', $file))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    $this->actingAs($user)
        ->get(route('collaboration.files.download', $uploadedFile))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('user collaboration inbox denies threads from other companies', function () {
    $companyA = app(MultiCompanyService::class)->createCompany('PT User Collaboration A');
    $companyB = app(MultiCompanyService::class)->createCompany('PT User Collaboration B');
    $user = User::factory()->create(['company_id' => $companyA->id]);
    $otherUser = User::factory()->create(['company_id' => $companyB->id]);
    $thread = ChatThread::query()->create([
        'company_id' => $companyB->id,
        'created_by' => $otherUser->id,
        'type' => ChatThread::TYPE_GROUP,
        'title' => 'Other tenant room',
    ]);
    $thread->members()->sync([$otherUser->id => ['role' => 'owner']]);

    $this->actingAs($user)
        ->get(route('collaboration', ['selectedThreadId' => $thread->id]))
        ->assertOk()
        ->assertDontSee('Other tenant room');

    Livewire::actingAs($user)
        ->test(CollaborationInbox::class)
        ->set('selectedThreadId', (string) $thread->id)
        ->set('messageBody', 'Should not pass')
        ->call('postMessage')
        ->assertNotFound();
});
