<?php

use App\Livewire\Admin\ApiIntegrationManager;
use App\Models\IntegrationAttendanceEvent;
use App\Models\IntegrationClient;
use App\Models\User;
use Livewire\Livewire;

function signedIntegrationHeadersForClient(string $apiKey, string $secret, string $body, int|string|null $timestamp = null): array
{
    $timestamp ??= time();

    return [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_PASPAPAN_API_KEY' => $apiKey,
        'HTTP_X_PASPAPAN_TIMESTAMP' => (string) $timestamp,
        'HTTP_X_PASPAPAN_SIGNATURE' => 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, $secret),
    ];
}

test('superadmin can create rotate and revoke third party integration clients', function () {
    $superadmin = User::factory()->admin(true)->create();

    Livewire::actingAs($superadmin)
        ->test(ApiIntegrationManager::class)
        ->set('name', 'Vendor Attendance Bridge')
        ->set('contactEmail', 'ops@vendor.test')
        ->set('allowedSourcesText', "vendor\nvendor-kiosk")
        ->set('abilities', [IntegrationClient::ABILITY_ATTENDANCE_WRITE])
        ->call('save')
        ->assertSet('showCredentialModal', true)
        ->assertSee('Vendor Attendance Bridge')
        ->assertSee('ppk_')
        ->assertSee('pps_');

    $client = IntegrationClient::query()->firstOrFail();

    expect($client->api_key_hash)->not->toBeNull()
        ->and($client->secret_encrypted)->not->toBeNull()
        ->and($client->allowed_sources)->toBe(['vendor', 'vendor-kiosk'])
        ->and($client->abilities)->toContain(IntegrationClient::ABILITY_ATTENDANCE_WRITE)
        ->and($client->revoked_at)->toBeNull();

    Livewire::actingAs($superadmin)
        ->test(ApiIntegrationManager::class)
        ->call('rotateSecret', $client->id)
        ->assertSet('showCredentialModal', true)
        ->assertSee('pps_')
        ->call('revoke', $client->id)
        ->assertDispatched('banner-message');

    expect($client->refresh()->revoked_at)->not->toBeNull();
});

test('integration client form can use preset defaults and auto source from name', function () {
    $superadmin = User::factory()->admin(true)->create();

    Livewire::actingAs($superadmin)
        ->test(ApiIntegrationManager::class)
        ->assertSet('preset', 'attendance')
        ->set('name', 'Vendor Attendance Bridge')
        ->set('allowedSourcesText', '')
        ->call('save')
        ->assertSet('showCredentialModal', true);

    $client = IntegrationClient::query()->firstOrFail();

    expect($client->allowed_sources)->toBe(['vendor-attendance-bridge'])
        ->and($client->allowed_ips)->toBe([])
        ->and($client->abilities)->toBe([IntegrationClient::ABILITY_ATTENDANCE_WRITE]);
});

test('api integrations page exposes the machine attendance api endpoint guidance', function () {
    $superadmin = User::factory()->admin(true)->create();

    Livewire::actingAs($superadmin)
        ->test(ApiIntegrationManager::class)
        ->assertSee(__('Machine Attendance API'))
        ->assertSee('/api/integrations/attendance-events')
        ->assertSee('X-PasPapan-Api-Key')
        ->assertSee('employee_code')
        ->assertSee('idempotency_key');
});

test('attendance integration endpoint accepts active clients and enforces scope and source', function () {
    [$client, $apiKey, $secret] = IntegrationClient::issue([
        'name' => 'Vendor Attendance Bridge',
        'abilities' => [IntegrationClient::ABILITY_ATTENDANCE_WRITE],
        'allowed_sources' => ['vendor'],
    ]);
    $user = User::factory()->create([
        'group' => 'user',
        'nip' => 'EMP-CLIENT-001',
    ]);
    $payload = [
        'source' => 'vendor',
        'idempotency_key' => 'vendor-1001',
        'employee_code' => 'EMP-CLIENT-001',
        'event_type' => 'check_in',
        'occurred_at' => '2026-06-07 08:00:00',
    ];
    $body = json_encode($payload, JSON_THROW_ON_ERROR);

    $this->call('POST', '/api/integrations/attendance-events', [], [], [], signedIntegrationHeadersForClient($apiKey, $secret, $body), $body)
        ->assertAccepted()
        ->assertJsonPath('status', IntegrationAttendanceEvent::STATUS_PROCESSED);

    $event = IntegrationAttendanceEvent::query()->firstOrFail();

    expect($client->refresh()->last_used_at)->not->toBeNull()
        ->and($event->integration_client_id)->toBe($client->id)
        ->and($event->user_id)->toBe($user->id);

    $payload['idempotency_key'] = 'vendor-1002';
    $payload['source'] = 'wrong-source';
    $wrongSourceBody = json_encode($payload, JSON_THROW_ON_ERROR);

    $this->call('POST', '/api/integrations/attendance-events', [], [], [], signedIntegrationHeadersForClient($apiKey, $secret, $wrongSourceBody), $wrongSourceBody)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('source');

    [$readonlyClient, $readonlyApiKey, $readonlySecret] = IntegrationClient::issue([
        'name' => 'Read Only Vendor',
        'abilities' => [IntegrationClient::ABILITY_ATTENDANCE_READ],
        'allowed_sources' => ['vendor'],
    ]);
    $payload['source'] = 'vendor';
    $payload['idempotency_key'] = 'vendor-readonly';
    $readonlyBody = json_encode($payload, JSON_THROW_ON_ERROR);

    $this->call('POST', '/api/integrations/attendance-events', [], [], [], signedIntegrationHeadersForClient($readonlyApiKey, $readonlySecret, $readonlyBody), $readonlyBody)
        ->assertForbidden()
        ->assertJsonPath('message', 'Integration client is not allowed to write attendance events.');

    expect($readonlyClient->refresh()->last_used_at)->toBeNull();
});
