<?php

namespace App\Contracts;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface ReportingServiceInterface
{
    /**
     * Export users to Excel.
     *
     * @return BinaryFileResponse|null
     */
    public function exportUsers(array $groups);

    /**
     * Export attendances to Excel with filters.
     *
     * @param  int|null  $month
     * @param  int|null  $year
     * @param  string|null  $division
     * @param  string|null  $jobTitle
     * @param  string|null  $education
     * @return BinaryFileResponse|null
     */
    public function exportAttendances($month, $year, $division, $jobTitle, $education);

    /**
     * Export activity logs to Excel.
     *
     * @return BinaryFileResponse|null
     */
    public function exportActivityLogs();

    /**
     * Export monthly report to PDF.
     *
     * @param  int  $month
     * @param  int  $year
     * @return Response|null
     */
    public function exportMonthlyReportPdf($month, $year);
}
