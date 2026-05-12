<?php

use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\Shift;
use App\Models\User;
use App\Services\Attendance\AttendanceRiskScoringService;

test('attendance risk scoring service evaluates and persists common fraud indicators', function () {
    $user = User::factory()->create();
    $barcode = Barcode::factory()->create(['radius' => 100]);
    $shift = Shift::create(['name' => 'Morning', 'start_time' => '08:00', 'end_time' => '17:00']);
    $attendance = Attendance::create([
        'user_id' => $user->id,
        'barcode_id' => $barcode->id,
        'shift_id' => $shift->id,
        'date' => '2026-05-12',
        'time_in' => '2026-05-12 10:15:00',
        'status' => 'late',
        'approval_status' => Attendance::STATUS_APPROVED,
    ]);

    $risk = app(AttendanceRiskScoringService::class)->evaluate($attendance, $barcode, $shift, 'check_in', [
        'mock_location_detected' => true,
        'distance' => 92,
        'device_info_missing' => true,
        'barcode_source' => 'static',
        'face_verification_skipped' => true,
        'cached_location' => true,
    ]);

    $attendance = app(AttendanceRiskScoringService::class)->persist($attendance, $risk);

    expect($risk['score'])->toBeGreaterThanOrEqual(60)
        ->and($risk['level'])->toBe('high')
        ->and(collect($risk['factors'])->pluck('code'))->toContain(
            'mock_location_detected',
            'near_attendance_radius',
            'device_changed',
            'face_confidence_low',
            'offline_submitted',
            'qr_token_retry',
            'check_in_late',
        )
        ->and($attendance->risk_level)->toBe('high')
        ->and($attendance->is_suspicious)->toBeTrue();
});
