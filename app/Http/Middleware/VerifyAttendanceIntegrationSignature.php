<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAttendanceIntegrationSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('services.attendance_integration.secret', '');

        if (trim($secret) === '') {
            return response()->json(['message' => 'Attendance integration is not configured.'], 503);
        }

        $timestamp = (string) $request->header('X-PasPapan-Timestamp', '');
        $signature = (string) $request->header('X-PasPapan-Signature', '');

        if ($timestamp === '' || $signature === '') {
            return response()->json(['message' => 'Missing integration signature.'], 401);
        }

        if (! ctype_digit($timestamp)) {
            return response()->json(['message' => 'Invalid integration signature.'], 401);
        }

        $tolerance = (int) config('services.attendance_integration.signature_tolerance_seconds', 300);
        if (abs(time() - (int) $timestamp) > $tolerance) {
            return response()->json(['message' => 'Expired integration signature.'], 401);
        }

        $expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        if (! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid integration signature.'], 401);
        }

        return $next($request);
    }
}
