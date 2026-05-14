<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnterpriseSupportController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $supportNumber = preg_replace('/\D+/', '', (string) config('services.whatsapp.support_number', ''));

        if ($supportNumber === '') {
            abort(404);
        }

        $message = trim((string) $request->query('text', ''));
        $message = mb_substr($message, 0, 3500);
        $targetUrl = 'https://wa.me/'.$supportNumber;

        if ($message !== '') {
            $targetUrl .= '?text='.rawurlencode($message);
        }

        return redirect()->away($targetUrl);
    }
}
