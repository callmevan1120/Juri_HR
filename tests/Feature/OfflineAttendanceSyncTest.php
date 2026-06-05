<?php

use App\Models\Attendance;
use App\Models\AttendanceOfflineSubmission;
use App\Models\Barcode;
use App\Models\User;
use App\Support\ApiTokenPermission;
use Laravel\Sanctum\Sanctum;

test('device offline attendance sync processes queued local submissions with risk flag', function () {
    $user = User::factory()->create();
    $barcode = Barcode::factory()->create([
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius' => 5000,
    ]);

    Sanctum::actingAs($user, deviceApiAbilities());

    $response = $this->postJson('/api/device/offline-attendance', [
        'items' => [
            [
                'client_uuid' => 'offline-001',
                'barcode_data' => $barcode->value,
                'latitude' => -6.2,
                'longitude' => 106.8,
                'timestamp' => now()->subHour()->format('Y-m-d H:i:s'),
                'accuracy' => 1.4,
                'gps_variance' => 0,
                'mock_location_detected' => false,
                'qr_token_retries' => 1,
            ],
        ],
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('results.0.client_uuid', 'offline-001')
        ->assertJsonPath('results.0.status', 'processed')
        ->assertJsonPath('results.0.action', 'check_in');

    $attendance = Attendance::firstOrFail();
    $submission = AttendanceOfflineSubmission::firstOrFail();
    $codes = collect($attendance->risk_factors)->pluck('code')->all();

    expect($submission->processed_attendance_id)->toBe($attendance->id)
        ->and($submission->status)->toBe('processed')
        ->and($attendance->date?->toDateString())->toBe(now()->toDateString())
        ->and($submission->risk_score)->toBe($attendance->risk_score)
        ->and($attendance->risk_level)->toBe('high')
        ->and($codes)->toContain('offline_submitted')
        ->and($codes)->toContain('gps_accuracy_too_perfect')
        ->and($codes)->toContain('gps_zero_variance')
        ->and($codes)->toContain('qr_token_retry');
});

test('device offline attendance sync is idempotent per client uuid', function () {
    $user = User::factory()->create();
    $barcode = Barcode::factory()->create([
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius' => 5000,
    ]);

    Sanctum::actingAs($user, deviceApiAbilities());

    $payload = [
        'items' => [
            [
                'client_uuid' => 'offline-repeat-001',
                'barcode_data' => $barcode->value,
                'latitude' => -6.2,
                'longitude' => 106.8,
                'timestamp' => now()->subHour()->format('Y-m-d H:i:s'),
            ],
        ],
    ];

    $this->postJson('/api/device/offline-attendance', $payload)->assertOk();
    $this->postJson('/api/device/offline-attendance', $payload)->assertOk();

    expect(AttendanceOfflineSubmission::count())->toBe(1)
        ->and(Attendance::count())->toBe(1);
});

test('device offline attendance sync requires dedicated offline attendance ability', function () {
    $user = User::factory()->create();
    $barcode = Barcode::factory()->create([
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius' => 5000,
    ]);

    Sanctum::actingAs($user, [ApiTokenPermission::DEVICE_BARCODE]);

    $this->postJson('/api/device/offline-attendance', [
        'items' => [
            [
                'client_uuid' => 'offline-barcode-only-001',
                'barcode_data' => $barcode->value,
                'latitude' => -6.2,
                'longitude' => 106.8,
                'timestamp' => now()->subHour()->format('Y-m-d H:i:s'),
            ],
        ],
    ])->assertForbidden();

    expect(AttendanceOfflineSubmission::count())->toBe(0)
        ->and(Attendance::count())->toBe(0);
});
