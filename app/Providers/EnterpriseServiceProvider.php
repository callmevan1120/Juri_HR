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
            if (class_exists(EnterpriseService::class) && LicenseGuard::hasFeature('attendance')) {
                return new EnterpriseService;
            }

            return new CommunityService;
        });

        $this->app->singleton(PayrollServiceInterface::class, function () {
            if (class_exists(EnterprisePayrollService::class) && LicenseGuard::hasFeature('payroll')) {
                return new EnterprisePayrollService;
            }

            return new CommunityPayrollService;
        });

        $this->app->singleton(ReportingServiceInterface::class, function () {
            if (class_exists(EnterpriseReportingService::class) && LicenseGuard::hasFeature('reporting')) {
                return new EnterpriseReportingService;
            }

            return new CommunityReportingService;
        });
    }
}
