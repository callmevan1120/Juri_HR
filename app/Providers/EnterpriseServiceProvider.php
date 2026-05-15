<?php

namespace App\Providers;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\PayrollServiceInterface;
use App\Contracts\ReportingServiceInterface;
use App\Services\Attendance\CommunityService;
use App\Services\Attendance\EnterpriseService;
use App\Services\Enterprise\LicenseGuard;
use App\Services\Payroll\CommunityPayrollService;
use App\Services\Payroll\EnterprisePayrollService;
use App\Services\Reporting\CommunityReportingService;
use App\Services\Reporting\EnterpriseReportingService;
use Illuminate\Support\ServiceProvider;

class EnterpriseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AttendanceServiceInterface::class, function () {
            if (LicenseGuard::hasFeature('attendance') && class_exists(EnterpriseService::class)) {
                return new EnterpriseService;
            }

            return new CommunityService;
        });

        $this->app->singleton(PayrollServiceInterface::class, function () {
            if (LicenseGuard::hasFeature('payroll') && class_exists(EnterprisePayrollService::class)) {
                return new EnterprisePayrollService;
            }

            return new CommunityPayrollService;
        });

        $this->app->singleton(ReportingServiceInterface::class, function () {
            if (LicenseGuard::hasFeature('reporting') && class_exists(EnterpriseReportingService::class)) {
                return new EnterpriseReportingService;
            }

            return new CommunityReportingService;
        });
    }
}
