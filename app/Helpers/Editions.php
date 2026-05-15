<?php

namespace App\Helpers;

use App\Contracts\AttendanceServiceInterface;
use App\Contracts\AuditServiceInterface;
use App\Contracts\PayrollServiceInterface;
use App\Contracts\ReportingServiceInterface;
use App\Services\Enterprise\LicenseGuard;

class Editions
{
    /**
     * Check if a specific feature service is running in Community Mode (Locked).
     */
    public static function isLocked(string $contractClass): bool
    {
        $feature = self::featureForContract($contractClass);

        return $feature === null
            ? ! LicenseGuard::hasValidLicense()
            : ! LicenseGuard::hasFeature($feature);
    }

    public static function payrollLocked(): bool
    {
        return self::isLocked(PayrollServiceInterface::class);
    }

    public static function reportingLocked(): bool
    {
        return self::isLocked(ReportingServiceInterface::class);
    }

    public static function auditLocked(): bool
    {
        return self::isLocked(AuditServiceInterface::class);
    }

    public static function attendanceLocked(): bool
    {
        return self::isLocked(AttendanceServiceInterface::class);
    }

    public static function assetLocked(): bool
    {
        return ! LicenseGuard::hasFeature('asset_management');
    }

    public static function appraisalLocked(): bool
    {
        return ! LicenseGuard::hasFeature('appraisal');
    }

    public static function analyticsLocked(): bool
    {
        return ! LicenseGuard::hasFeature('analytics');
    }

    public static function cashAdvanceLocked(): bool
    {
        return ! LicenseGuard::hasFeature('cash_advance');
    }

    public static function systemBackupLocked(): bool
    {
        return ! LicenseGuard::hasFeature('system_backup');
    }

    public static function documentRequestsLocked(): bool
    {
        return ! LicenseGuard::hasFeature('document_requests');
    }

    private static function featureForContract(string $contractClass): ?string
    {
        return match ($contractClass) {
            AttendanceServiceInterface::class => 'attendance',
            PayrollServiceInterface::class => 'payroll',
            ReportingServiceInterface::class => 'reporting',
            AuditServiceInterface::class => 'audit',
            default => null,
        };
    }
}
