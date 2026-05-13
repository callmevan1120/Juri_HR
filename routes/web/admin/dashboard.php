<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(request()->user()?->preferredAdminRouteName() ?? 'home'))
    ->can('accessAdminPanel');

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    if (config('auth.debug_log', false)) {
        Log::debug('Admin dashboard route reached.', [
            'path' => $request->path(),
            'user_id' => $user?->id,
            'group' => $user?->group,
            'can_access_admin_panel' => $user?->can('accessAdminPanel'),
            'can_view_admin_dashboard' => $user?->can('viewAdminDashboard'),
            'email' => $user?->email,
            'roles' => $user?->roles()->pluck('slug')->all() ?? [],
        ]);
    }

    return response()
        ->view('admin.dashboard')
        ->header('X-Paspapan-Dashboard-Route', 'reached');
})->name('admin.dashboard')->can('viewAdminDashboard');

Route::livewire('/inbox', 'admin.manager-inbox')->name('admin.inbox')->can('accessAdminPanel');
Route::livewire('/notifications', 'admin.notifications-page')->name('admin.notifications')->can('manageAdminNotifications');
Route::livewire('/analytics', 'admin.analytics-dashboard')
    ->name('admin.analytics')
    ->middleware('feature.lock:analytics,admin.analytics.view,admin.dashboard')
    ->can('viewAnalyticsDashboard');
