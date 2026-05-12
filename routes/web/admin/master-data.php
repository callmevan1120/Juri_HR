<?php

use App\Http\Controllers\Admin\MasterData\AdminController as MasterAdminController;
use App\Http\Controllers\Admin\MasterData\DivisionController;
use App\Http\Controllers\Admin\MasterData\EducationController;
use App\Http\Controllers\Admin\MasterData\JobTitleController;
use App\Http\Controllers\Admin\MasterData\ShiftController;
use App\Livewire\Admin\HolidayManager;
use App\Livewire\Admin\MasterData\LeaveTypeManager;
use Illuminate\Support\Facades\Route;

Route::get('/masterdata/division', DivisionController::class)->name('admin.masters.division')->can('manageDivisions');
Route::get('/masterdata/job-title', JobTitleController::class)->name('admin.masters.job-title')->can('manageJobTitles');
Route::get('/masterdata/education', EducationController::class)->name('admin.masters.education')->can('manageEducations');
Route::get('/masterdata/shift', ShiftController::class)->name('admin.masters.shift')->can('manageShifts');
Route::get('/masterdata/leave-types', LeaveTypeManager::class)->name('admin.masters.leave-types')->can('manageLeaveTypes');
Route::get('/masterdata/admin', MasterAdminController::class)->name('admin.masters.admin')->can('viewAdminAccounts');
Route::get('/holidays', HolidayManager::class)->name('admin.holidays')->can('manageHolidays');
