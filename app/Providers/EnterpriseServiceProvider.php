<?php

namespace App\Providers;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\PayrollServiceInterface;
use App\Contracts\ReportingServiceInterface;
use App\Services\Attendance\CommunityService;
use App\Services\Attendance\EnterpriseService;
use App\Services\Payroll\CommunityPayrollService;
use App\Services\Payroll\EnterprisePayrollService;
use App\Services\Reporting\CommunityReportingService;
use App\Services\Reporting\EnterpriseReportingService;
use App\Support\EnterpriseRuntime;
use Illuminate\Support\ServiceProvider;

class EnterpriseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AttendanceServiceInterface::class, function () {
            return EnterpriseRuntime::resolve('attendance', EnterpriseService::class, new CommunityService);
        });

        $this->app->singleton(PayrollServiceInterface::class, function () {
            return EnterpriseRuntime::resolve('payroll', EnterprisePayrollService::class, new CommunityPayrollService);
        });

        $this->app->singleton(ReportingServiceInterface::class, function () {
            return EnterpriseRuntime::resolve('reporting', EnterpriseReportingService::class, new CommunityReportingService);
        });
    }
}
