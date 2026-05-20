<?php

use App\Livewire\Admin\AttendanceComponent;
use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\User;
use App\Services\Attendance\AttendanceRiskScoringService;
use App\Support\AttendanceRiskScorer;
use App\Support\AttendanceScanService;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;

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

    expect($attendance->risk_score)->toBeGreaterThanOrEqual(40)
        ->and($attendance->risk_level)->toBeIn(['medium', 'high'])
        ->and($codes)->toContain('gps_accuracy_too_perfect')
        ->and($codes)->toContain('gps_zero_variance')
        ->and($codes)->toContain('timestamp_anomaly');
});

test('device barcode scan scores cross platform telemetry without android mock location', function () {
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
        'timestamp' => now()->toDateTimeString(),
        'accuracy' => 3.2,
        'cached_location' => true,
        'device_changed' => true,
        'device_id' => 'ios-device-1',
        'platform' => 'ios',
        'face_confidence' => 0.42,
    ])->assertOk();

    $attendance = Attendance::firstOrFail();
    $codes = collect($attendance->risk_factors)->pluck('code')->all();

    expect($attendance->risk_score)->toBeGreaterThanOrEqual(60)
        ->and($attendance->risk_level)->toBe('high')
        ->and($codes)->toContain('gps_accuracy_too_perfect')
        ->and($codes)->toContain('cached_location_used')
        ->and($codes)->toContain('device_changed')
        ->and($codes)->toContain('face_confidence_low')
        ->and($codes)->not->toContain('mock_location_detected');
});

test('web attendance scan production flow evaluates risk through scoring service', function () {
    Setting::create(['key' => 'feature.require_photo', 'value' => '0']);

    $calls = new class(app(AttendanceRiskScorer::class)) extends AttendanceRiskScoringService
    {
        public int $count = 0;

        public function evaluate(Attendance $attendance, Barcode $barcode, ?Shift $shift, string $event = 'check_in', array $context = []): array
        {
            $this->count++;

            return parent::evaluate($attendance, $barcode, $shift, $event, $context);
        }
    };
    app()->instance(AttendanceRiskScoringService::class, $calls);

    $user = User::factory()->create();
    $shift = Shift::create(['name' => 'Morning', 'start_time' => '08:00', 'end_time' => '17:00']);
    $barcode = Barcode::factory()->create([
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius' => 200,
    ]);

    $result = app(AttendanceScanService::class)->performScan(
        user: $user,
        shiftId: $shift->id,
        coords: [-6.1998, 106.8002],
        barcodePayload: $barcode->value,
        photo: null,
        note: null,
        gracePeriod: 0,
        gpsAccuracy: 1.2,
        gpsVariance: 0,
    );

    expect($result['ok'])->toBeTrue()
        ->and($calls->count)->toBe(1)
        ->and(Attendance::firstOrFail()->risk_score)->toBeGreaterThan(0);
});

test('admin attendance can filter medium and high risk rows', function () {
    $admin = User::factory()->admin()->create();
    $safeUser = User::factory()->create(['name' => 'Safe Employee']);
    $riskUser = User::factory()->create(['name' => 'Risk Employee']);
    $barcode = Barcode::factory()->create();

    Attendance::create([
        'user_id' => $safeUser->id,
        'barcode_id' => $barcode->id,
        'date' => now()->toDateString(),
        'status' => 'present',
        'approval_status' => Attendance::STATUS_APPROVED,
        'risk_score' => 0,
        'risk_level' => 'low',
    ]);
    Attendance::create([
        'user_id' => $riskUser->id,
        'barcode_id' => $barcode->id,
        'date' => now()->toDateString(),
        'status' => 'present',
        'approval_status' => Attendance::STATUS_APPROVED,
        'risk_score' => 70,
        'risk_level' => 'high',
        'is_suspicious' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(AttendanceComponent::class)
        ->set('riskFilter', 'medium_high')
        ->assertSee('Risk Employee')
        ->assertDontSee('Safe Employee');
});
