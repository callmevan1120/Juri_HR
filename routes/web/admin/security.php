<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/activity-logs', 'admin.activity-logs')->name('admin.activity-logs')->middleware('feature.lock:audit,admin.activity_logs.view,admin.dashboard')->can('viewActivityLogs');
Route::livewire('/announcements', 'admin.announcement-manager')->name('admin.announcements')->can('manageAnnouncements');
Route::livewire('/roles-permissions', 'admin.role-permission-manager')->name('admin.roles.permissions')->can('manageRbac');
Route::livewire('/user-sessions', 'admin.user-session-manager')->name('admin.user-sessions')->can('manageUserSessions');
