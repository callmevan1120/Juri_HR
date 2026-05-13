<?php

use App\Livewire\Admin\ActivityLogs;
use App\Livewire\Admin\AnnouncementManager;
use App\Livewire\Admin\RolePermissionManager;
use Illuminate\Support\Facades\Route;

Route::get('/activity-logs', ActivityLogs::class)->name('admin.activity-logs')->middleware('feature.lock:audit,admin.activity_logs.view,admin.dashboard')->can('viewActivityLogs');
Route::get('/announcements', AnnouncementManager::class)->name('admin.announcements')->can('manageAnnouncements');
Route::get('/roles-permissions', RolePermissionManager::class)->name('admin.roles.permissions')->can('manageRbac');
