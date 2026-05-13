<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Http\Controllers\Controller;
use App\Services\Integrations\AttendanceEventIngestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceEventController extends Controller
{
    public function __construct(
        private readonly AttendanceEventIngestionService $ingestion,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9_.-]+$/'],
            'idempotency_key' => ['required', 'string', 'max:160'],
            'employee_code' => ['required', 'string', 'max:120'],
            'event_type' => ['required', 'string', Rule::in(['check_in', 'check_out', 'clock_in', 'clock_out', 'in', 'out'])],
            'occurred_at' => ['required', 'date'],
            'device_id' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'payload' => ['nullable', 'array'],
        ]);

        $event = $this->ingestion->ingest($validated, $request->all());

        return response()->json([
            'success' => $event->status !== 'failed',
            'event_id' => $event->id,
            'status' => $event->status,
            'attendance_id' => $event->attendance_id,
            'error_message' => $event->error_message,
        ], $event->status === 'failed' ? 422 : 202);
    }
}
