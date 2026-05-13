<?php

use App\Http\Controllers\Admin\OperationalHealthController;
use App\Models\SystemBackupRun;
use Illuminate\Support\Facades\Route;

Route::livewire('/settings', 'admin.settings')->name('admin.settings')->can('viewAdminSettings');
Route::livewire('/settings/kpi', 'admin.settings.kpi-settings')->name('admin.settings.kpi')->middleware('feature.lock:appraisal,admin.kpi_settings.manage,admin.dashboard')->can('manageKpiSettings');
Route::get('/operational-health', OperationalHealthController::class)->name('admin.operational-health')->can('viewAny', SystemBackupRun::class);
Route::get('/profile', fn () => view('profile.admin-show'))->name('admin.profile.show')->can('accessAdminPanel');
