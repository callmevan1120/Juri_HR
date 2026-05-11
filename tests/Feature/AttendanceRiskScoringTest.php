<?php

use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('device barcode scan stores attendance risk score and factors', function () {
    $user = User::factory()->create();
    $barcode = Barcode::factory()->create([
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius' => 5000,
    ]);

    Sanctum::actingAs($user, deviceApiAbilities());

    $this->postJson('/api/device/barcode', [
        'barcode_data' => $barcode->value,
        'latitude' => -6.2,
        'longitude' => 106.8,
        'timestamp' => '2026-05-11 08:00:00',
        'accuracy' => 1.2,
        'gps_variance' => 0,
        'mock_location_detected' => true,
        'offline_submitted' => true,
        'qr_token_retries' => 1,
    ])->assertOk();

    $attendance = Attendance::firstOrFail();
    $codes = collect($attendance->risk_factors)->pluck('code')->all();

    expect($attendance->risk_score)->toBe(100)
        ->and($attendance->risk_level)->toBe('high')
        ->and($attendance->is_suspicious)->toBeTrue()
        ->and($attendance->risk_evaluated_at)->not->toBeNull()
        ->and($codes)->toContain('gps_accuracy_too_perfect')
        ->and($codes)->toContain('gps_zero_variance')
        ->and($codes)->toContain('mock_location_detected')
        ->and($codes)->toContain('offline_submitted')
        ->and($codes)->toContain('qr_token_retry');
});

test('device checkout merges new risk factors with existing check in risk', function () {
    $user = User::factory()->create();
    $barcode = Barcode::factory()->create([
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius' => 5000,
    ]);

    Sanctum::actingAs($user, deviceApiAbilities());

    $this->postJson('/api/device/barcode', [
        'barcode_data' => $barcode->value,
        'latitude' => -6.2,
        'longitude' => 106.8,
        'timestamp' => '2026-05-11 08:00:00',
        'accuracy' => 1.2,
    ])->assertOk();

    $this->postJson('/api/device/barcode', [
        'barcode_data' => $barcode->value,
        'latitude' => -6.21,
        'longitude' => 106.81,
        'timestamp' => '2026-05-11 17:00:00',
        'gps_variance' => 0,
    ])->assertOk();

    $attendance = Attendance::firstOrFail();
    $codes = collect($attendance->risk_factors)->pluck('code')->all();

    expect($attendance->risk_score)->toBe(40)
        ->and($attendance->risk_level)->toBe('medium')
        ->and($codes)->toContain('gps_accuracy_too_perfect')
        ->and($codes)->toContain('gps_zero_variance');
});
