<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
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
    }
}
