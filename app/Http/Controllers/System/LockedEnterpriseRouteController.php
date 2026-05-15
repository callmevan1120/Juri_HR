<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LockedEnterpriseRouteController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $payload = [
            'title' => __('Enterprise Feature'),
            'message' => __('This feature is available in the Enterprise Edition. Please upgrade.'),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $payload['message'],
                'feature_locked' => true,
            ], 423);
        }

        $user = $request->user();
        $fallbackRoute = $user?->preferredAdminRouteName() ?? ($user?->isUser ? 'home' : 'admin.dashboard');

        return redirect()
            ->route($fallbackRoute)
            ->with('show-feature-lock', $payload)
            ->with('flash.banner', $payload['message'])
            ->with('flash.bannerStyle', 'warning');
    }
}
