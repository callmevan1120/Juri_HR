<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\Shift;
use Illuminate\Support\Carbon;

class AttendanceRiskScorer
{
    public function score(
        Attendance $attendance,
        Barcode $barcode,
        ?Shift $shift,
        string $event,
        array $context = [],
    ): array {
        $factors = [];
        $gpsAccuracy = $context['gps_accuracy'] ?? null;
        $gpsVariance = $context['gps_variance'] ?? null;
        $distance = $context['distance'] ?? null;
        $faceConfidence = $context['face_confidence'] ?? null;
        $qrTokenRetries = (int) ($context['qr_token_retries'] ?? 0);

        if (($context['mock_location_detected'] ?? false) === true) {
            $factors[] = $this->factor('mock_location_detected', 40, $event, [
                'source' => $context['source'] ?? null,
            ]);
        }

        if (($context['offline_submitted'] ?? false) === true) {
            $factors[] = $this->factor('offline_submitted', 25, $event);
        }

        if ($gpsAccuracy !== null && (float) $gpsAccuracy < 5.0) {
            $factors[] = $this->factor('gps_accuracy_too_perfect', 20, $event, [
                'accuracy' => (float) $gpsAccuracy,
            ]);
        }

        if ($gpsVariance !== null && (float) $gpsVariance == 0.0) {
            $factors[] = $this->factor('gps_zero_variance', 20, $event);
        }

        if ($distance !== null && $barcode->radius > 0) {
            $distance = (float) $distance;
            $radius = (float) $barcode->radius;

            if ($distance >= ($radius * 0.85) && $distance <= $radius) {
                $factors[] = $this->factor('near_attendance_radius', 15, $event, [
                    'distance' => $distance,
                    'radius' => $radius,
                ]);
            }
        }

        if (($context['device_changed'] ?? false) === true) {
            $factors[] = $this->factor('device_changed', 15, $event);
        }

        if ($faceConfidence !== null && (float) $faceConfidence < 0.65) {
            $factors[] = $this->factor('face_confidence_low', 20, $event, [
                'confidence' => (float) $faceConfidence,
            ]);
        }

        if ($qrTokenRetries > 0) {
            $factors[] = $this->factor('qr_token_retry', min(25, 10 * $qrTokenRetries), $event, [
                'retries' => $qrTokenRetries,
            ]);
        }

        if ($event === 'check_in' && $shift !== null && $attendance->time_in !== null) {
            $factors = array_merge($factors, $this->timeRiskFactors($attendance, $shift, $event));
        }

        $score = min(100, array_sum(array_column($factors, 'score')));

        return [
            'score' => $score,
            'level' => $this->level($score),
            'factors' => $factors,
            'evaluated_at' => now(),
        ];
    }

    public function merge(?array $existingFactors, int $existingScore, array $newRisk): array
    {
        $factors = array_values(array_merge($existingFactors ?? [], $newRisk['factors'] ?? []));
        $score = min(100, $existingScore + (int) ($newRisk['score'] ?? 0));

        return [
            'score' => $score,
            'level' => $this->level($score),
            'factors' => $factors,
            'evaluated_at' => $newRisk['evaluated_at'] ?? now(),
        ];
    }

    public function level(int $score): string
    {
        return match (true) {
            $score >= 60 => 'high',
            $score >= 25 => 'medium',
            default => 'low',
        };
    }

    private function timeRiskFactors(Attendance $attendance, Shift $shift, string $event): array
    {
        $timeIn = Carbon::parse($attendance->time_in);
        $shiftStart = Carbon::parse($shift->start_time)->setDate($timeIn->year, $timeIn->month, $timeIn->day);

        if ($timeIn->lt($shiftStart->copy()->subMinutes(120))) {
            return [$this->factor('check_in_too_early', 10, $event, [
                'shift_start' => $shiftStart->toDateTimeString(),
                'time_in' => $timeIn->toDateTimeString(),
            ])];
        }

        if ($attendance->status === 'late') {
            return [$this->factor('check_in_late', 10, $event, [
                'shift_start' => $shiftStart->toDateTimeString(),
                'time_in' => $timeIn->toDateTimeString(),
            ])];
        }

        return [];
    }

    private function factor(string $code, int $score, string $event, array $context = []): array
    {
        return [
            'code' => $code,
            'event' => $event,
            'score' => $score,
            'context' => array_filter(
                $context,
                fn ($value): bool => $value !== null && $value !== '',
            ),
        ];
    }
}
