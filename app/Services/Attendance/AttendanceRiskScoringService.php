<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\Shift;
use App\Support\AttendanceRiskScorer;

class AttendanceRiskScoringService
{
    public function __construct(
        protected AttendanceRiskScorer $scorer,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     * @return array{score:int,level:string,factors:array<int,array<string,mixed>>,evaluated_at:mixed}
     */
    public function evaluate(Attendance $attendance, Barcode $barcode, ?Shift $shift, string $event = 'check_in', array $context = []): array
    {
        $normalizedContext = [
            ...$context,
            'device_changed' => (bool) ($context['device_changed'] ?? false),
            'offline_submitted' => (bool) ($context['offline_submitted'] ?? $context['cached_location'] ?? false),
            'cached_location' => (bool) ($context['cached_location'] ?? false),
        ];

        if (($context['barcode_source'] ?? null) === 'static') {
            $normalizedContext['qr_token_retries'] = max((int) ($normalizedContext['qr_token_retries'] ?? 0), 1);
        }

        if (($context['device_info_missing'] ?? false) === true) {
            $normalizedContext['device_changed'] = true;
        }

        if (($context['face_verification_failed'] ?? false) === true || ($context['face_verification_skipped'] ?? false) === true) {
            $normalizedContext['face_confidence'] = 0.0;
        }

        return $this->scorer->score($attendance, $barcode, $shift, $event, $normalizedContext);
    }

    public function persist(Attendance $attendance, array $risk): Attendance
    {
        $attendance->forceFill([
            'risk_score' => (int) ($risk['score'] ?? 0),
            'risk_level' => (string) ($risk['level'] ?? 'low'),
            'risk_factors' => $risk['factors'] ?? [],
            'risk_evaluated_at' => $risk['evaluated_at'] ?? now(),
            'is_suspicious' => (int) ($risk['score'] ?? 0) >= 60,
        ])->save();

        return $attendance->refresh();
    }
}
