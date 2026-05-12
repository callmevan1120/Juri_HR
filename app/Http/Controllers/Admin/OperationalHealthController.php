<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\OperationalHealthService;

class OperationalHealthController extends Controller
{
    public function __invoke(OperationalHealthService $health)
    {
        return view('admin.operational-health', [
            'health' => $health->snapshot(),
        ]);
    }
}
