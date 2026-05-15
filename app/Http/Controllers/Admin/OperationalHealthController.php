<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\OperationalHealthService;
use Illuminate\Contracts\View\View;

class OperationalHealthController extends Controller
{
    public function __invoke(OperationalHealthService $health): View
    {
        return view('admin.operational-health', [
            'health' => $health->snapshot(),
        ]);
    }
}
