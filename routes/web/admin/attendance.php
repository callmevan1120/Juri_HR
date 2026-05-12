<?php

use App\Http\Controllers\Admin\Attendance\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\Barcode\BarcodeController;
use App\Livewire\Admin\AttendanceCorrectionManager;
use App\Livewire\Admin\LeaveApproval;
use App\Livewire\Admin\OvertimeManager;
use App\Livewire\Admin\ScheduleComponent;
use App\Livewire\Admin\ShiftSwapApprovalManager;
use App\Models\Attendance as AttendanceRecord;
use App\Models\AttendanceCorrection;
use Illuminate\Support\Facades\Route;

Route::resource('/barcodes', BarcodeController::class)
    ->only(['index', 'show', 'create', 'store', 'edit', 'update'])
    ->middleware('can:manageBarcodes')
    ->names([
        'index' => 'admin.barcodes',
        'show' => 'admin.barcodes.show',
        'create' => 'admin.barcodes.create',
        'store' => 'admin.barcodes.store',
        'edit' => 'admin.barcodes.edit',
        'update' => 'admin.barcodes.update',
    ]);

Route::post('/barcodes/{barcode}/regenerate-secret', [BarcodeController::class, 'regenerateSecret'])->name('admin.barcodes.regenerate-secret')->can('manageBarcodes');
Route::get('/barcodes/{barcode}/dynamic-display', [BarcodeController::class, 'dynamicDisplay'])->name('admin.barcodes.dynamic-display')->can('manageBarcodes');
Route::get('/barcodes/{barcode}/dynamic-token', [BarcodeController::class, 'dynamicToken'])->name('admin.barcodes.dynamic-token')->can('manageBarcodes');
Route::get('/barcodes/download/all', [BarcodeController::class, 'downloadAll'])->name('admin.barcodes.downloadall')->can('manageBarcodes');
Route::get('/barcodes/{id}/download', [BarcodeController::class, 'download'])->name('admin.barcodes.download')->can('manageBarcodes');

Route::get('/schedules', ScheduleComponent::class)->name('admin.schedules')->can('manageSchedules');
Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('admin.attendances')->can('viewAdminAny', AttendanceRecord::class);
Route::get('/attendances/report', [AdminAttendanceController::class, 'report'])->name('admin.attendances.report')->can('viewAttendanceReports');
Route::get('/attendance-corrections', AttendanceCorrectionManager::class)->name('admin.attendance-corrections')->can('viewAdminAny', AttendanceCorrection::class);
Route::get('/leaves', LeaveApproval::class)->name('admin.leaves')->can('manageLeaveApprovals');
Route::get('/shift-swaps', ShiftSwapApprovalManager::class)->name('admin.shift-swaps')->can('manageShiftSwapApprovals');
Route::get('/overtime', OvertimeManager::class)->name('admin.overtime')->can('manageOvertime');
