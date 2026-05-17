<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Exports\AccountingStatementsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportAccountingReportController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('viewAccountingWorkspace');

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $filename = 'accounting-statements-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new AccountingStatementsExport($request->user(), $validated), $filename);
    }
}
