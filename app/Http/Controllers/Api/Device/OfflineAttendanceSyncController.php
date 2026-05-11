<?php

namespace App\Http\Controllers\Api\Device;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DeviceOfflineAttendanceSyncRequest;
use App\Services\Attendance\OfflineAttendanceSyncService;
use Illuminate\Support\Facades\Auth;

class OfflineAttendanceSyncController extends Controller
{
    public function __construct(
        private readonly OfflineAttendanceSyncService $offlineAttendanceSync,
    ) {}

    public function __invoke(DeviceOfflineAttendanceSyncRequest $request)
    {
        return response()->json([
            'success' => true,
            'results' => $this->offlineAttendanceSync->sync(Auth::user(), $request->validated('items')),
        ]);
    }
}
