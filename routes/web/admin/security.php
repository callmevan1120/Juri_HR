<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/activity-logs', 'admin.activity-logs')->name('admin.activity-logs')->middleware('feature.lock:audit,admin.activity_logs.view,admin.dashboard')->can('viewActivityLogs');
Route::livewire('/announcements', 'admin.announcement-manager')->name('admin.announcements')->can('manageAnnouncements');

Route::livewire('/api-integrations', 'admin.api-integration-manager')
    ->name('admin.api-integrations')
    ->middleware('feature.lock:security,gate:manageApiIntegrations,admin.dashboard')
    ->can('manageApiIntegrations');

Route::livewire('/user-sessions', 'admin.user-session-manager')
    ->name('admin.user-sessions')
    ->middleware('feature.lock:security,gate:manageUserSessions,admin.dashboard')
    ->can('manageUserSessions');

Route::livewire('/roles-permissions', 'admin.role-permission-manager')->name('admin.roles.permissions')->can('manageRbac');
