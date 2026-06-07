<?php

namespace App\Services\Integrations;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\IntegrationAttendanceEvent;
use App\Models\IntegrationClient;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceEventIngestionService
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $rawPayload
     */
    public function ingest(array $payload, array $rawPayload, ?IntegrationClient $client = null): IntegrationAttendanceEvent
    {
        $normalized = $this->normalize($payload);

        return DB::transaction(function () use ($normalized, $rawPayload, $client): IntegrationAttendanceEvent {
            $event = IntegrationAttendanceEvent::query()
                ->where('source', $normalized['source'])
                ->where('idempotency_key', $normalized['idempotency_key'])
                ->lockForUpdate()
                ->first();

            if ($event instanceof IntegrationAttendanceEvent) {
                return $event;
            }

            $event = IntegrationAttendanceEvent::query()->create([
                ...$normalized,
                'integration_client_id' => $client?->id,
                'status' => IntegrationAttendanceEvent::STATUS_ACCEPTED,
                'normalized_payload' => $normalized,
                'raw_payload' => $rawPayload,
            ]);

            return $this->process($event);
        });
    }

    private function process(IntegrationAttendanceEvent $event): IntegrationAttendanceEvent
    {
        $user = User::query()
            ->where('group', 'user')
            ->where('nip', $event->employee_code)
            ->first();

        if (! $user instanceof User) {
            return $this->fail($event, __('Employee code was not found.'));
        }

        $occurredAt = $event->occurred_at instanceof Carbon ? $event->occurred_at : Carbon::parse($event->occurred_at);

        $attendance = Attendance::query()->firstOrNew([
            'user_id' => $user->id,
            'date' => $occurredAt->toDateString(),
        ]);

        if (
            $attendance->exists
            && in_array($attendance->status, ['sick', 'excused', 'permission', 'leave'], true)
            && $attendance->approval_status === Attendance::STATUS_APPROVED
        ) {
            return $this->fail($event, __('Attendance is blocked because the employee is on approved leave.'));
        }

        if ($event->event_type === IntegrationAttendanceEvent::EVENT_CHECK_IN) {
            if ($attendance->time_in === null) {
                $attendance->fill([
                    'time_in' => $occurredAt,
                    'latitude_in' => $event->latitude,
                    'longitude_in' => $event->longitude,
                    'status' => $attendance->status === 'absent' ? 'present' : ($attendance->status ?: 'present'),
                ]);
            }
        } elseif ($attendance->time_out === null) {
            $attendance->fill([
                'time_out' => $occurredAt,
                'latitude_out' => $event->latitude,
                'longitude_out' => $event->longitude,
                'status' => $attendance->status === 'absent' ? 'present' : ($attendance->status ?: 'present'),
            ]);
        }

        $attendance->save();
        Attendance::clearUserAttendanceCache($user, $occurredAt);

        $event->forceFill([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => IntegrationAttendanceEvent::STATUS_PROCESSED,
            'error_message' => null,
            'processed_at' => now(),
        ])->save();

        ActivityLog::record(
            'Attendance Integration Event',
            __(':source attendance event :key processed for :employee.', [
                'source' => $event->source,
                'key' => $event->idempotency_key,
                'employee' => $user->name,
            ]),
        );

        return $event;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalize(array $payload): array
    {
        $eventType = match ((string) $payload['event_type']) {
            'clock_in', 'in' => IntegrationAttendanceEvent::EVENT_CHECK_IN,
            'clock_out', 'out' => IntegrationAttendanceEvent::EVENT_CHECK_OUT,
            default => (string) $payload['event_type'],
        };

        return [
            'source' => Str::of((string) ($payload['source'] ?? 'generic'))->lower()->limit(80, '')->toString(),
            'idempotency_key' => (string) $payload['idempotency_key'],
            'employee_code' => trim((string) $payload['employee_code']),
            'event_type' => $eventType,
            'occurred_at' => Carbon::parse((string) $payload['occurred_at']),
            'latitude' => array_key_exists('latitude', $payload) ? $payload['latitude'] : null,
            'longitude' => array_key_exists('longitude', $payload) ? $payload['longitude'] : null,
            'device_id' => $payload['device_id'] ?? null,
        ];
    }

    private function fail(IntegrationAttendanceEvent $event, string $message): IntegrationAttendanceEvent
    {
        $event->forceFill([
            'status' => IntegrationAttendanceEvent::STATUS_FAILED,
            'error_message' => $message,
            'processed_at' => now(),
        ])->save();

        return $event;
    }
}
