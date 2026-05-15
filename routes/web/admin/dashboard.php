<?php

use App\Http\Controllers\Admin\AdminRootRedirectController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', AdminRootRedirectController::class)
    ->can('accessAdminPanel');

Route::get('/dashboard', DashboardController::class)->name('admin.dashboard')->can('viewAdminDashboard');

Route::livewire('/inbox', 'admin.manager-inbox')->name('admin.inbox')->can('accessAdminPanel');
Route::livewire('/notifications', 'admin.notifications-page')->name('admin.notifications')->can('manageAdminNotifications');
enterprise_livewire_route('/analytics', 'admin.analytics-dashboard')
    ->name('admin.analytics')
    ->middleware('feature.lock:analytics,admin.analytics.view,admin.dashboard')
    ->can('viewAnalyticsDashboard');
