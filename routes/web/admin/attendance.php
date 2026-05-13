<?php

use App\Http\Controllers\Admin\Attendance\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\Barcode\BarcodeController;
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

Route::livewire('/schedules', 'admin.schedule-component')->name('admin.schedules')->can('manageSchedules');
Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('admin.attendances')->can('viewAdminAny', AttendanceRecord::class);
Route::get('/attendances/report', [AdminAttendanceController::class, 'report'])->name('admin.attendances.report')->can('viewAttendanceReports');
Route::livewire('/attendance-corrections', 'admin.attendance-correction-manager')->name('admin.attendance-corrections')->can('viewAdminAny', AttendanceCorrection::class);
Route::livewire('/leaves', 'admin.leave-approval')->name('admin.leaves')->can('manageLeaveApprovals');
Route::livewire('/shift-swaps', 'admin.shift-swap-approval-manager')->name('admin.shift-swaps')->can('manageShiftSwapApprovals');
Route::livewire('/overtime', 'admin.overtime-manager')->name('admin.overtime')->can('manageOvertime');
