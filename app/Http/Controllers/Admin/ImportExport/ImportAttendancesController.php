<?php

namespace App\Http\Controllers\Admin\ImportExport;

use App\Helpers\Editions;
use App\Http\Controllers\Controller;
use App\Support\ImportExportRunService;
use App\Support\SecureUploadPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImportAttendancesController extends Controller
{
    public function __invoke(Request $request, ImportExportRunService $runService, SecureUploadPolicy $secureUploadPolicy): RedirectResponse
    {
        $this->authorize('importAttendances');

        if (Editions::reportingLocked()) {
            return to_route('admin.import-export.attendances')
                ->with('flash.banner', __('This feature is available in the Enterprise Edition. Please upgrade.'))
                ->with('flash.bannerStyle', 'danger');
        }

        $validated = $request->validate([
            'file' => ['required', ...$secureUploadPolicy->rules('spreadsheet')],
        ]);

        $run = $runService->queueAttendanceImport($request->user(), $validated['file']);

        return to_route('admin.import-export.attendances')
            ->with('flash.banner', "Attendance import queued in background. Track progress from run #{$run->id}.")
            ->with('flash.bannerStyle', 'success');
    }
}
