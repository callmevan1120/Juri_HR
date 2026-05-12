<?php

/**
 * Builds obfuscated enterprise artifacts without replacing auditable source files.
 *
 * Usage:
 *   php scripts/build-enterprise.php
 *   php scripts/build-enterprise.php --output=/tmp/paspapan-enterprise
 */
$filesToSecure = [
    'app/Console/Commands/EnterpriseHwId.php',
    'app/Services/Enterprise/LicenseGuard.php',
    'app/Helpers/Editions.php',
    'app/Services/Payroll/EnterprisePayrollService.php',
    'app/Services/Audit/EnterpriseAuditService.php',
    'app/Services/Attendance/EnterpriseService.php',
    'app/Services/Reporting/EnterpriseReportingService.php',
    'app/Livewire/Admin/AnalyticsDashboard.php',
    'app/Livewire/Admin/AssetManager.php',
    'app/Livewire/Admin/AppraisalManager.php',
    'app/Livewire/Admin/PayrollManager.php',
    'app/Livewire/Admin/PayrollSettings.php',
    'app/Livewire/Admin/DocumentTemplateLibrary.php',
    'app/Livewire/Admin/DocumentTemplateManager.php',
    'app/Livewire/Admin/EmployeeDocumentRequestManager.php',
    'app/Livewire/Admin/ImportExport/User.php',
    'app/Livewire/Admin/ImportExport/Attendance.php',
    'app/Livewire/Admin/SystemMaintenance.php',
    'app/Livewire/Admin/HrChecklistManager.php',
    'app/Livewire/User/MyPayslips.php',
    'app/Livewire/User/EmployeeDocumentRequestPage.php',
    'app/Livewire/User/Finance/MyCashAdvances.php',
    'app/Livewire/Finance/Concerns/ManagesCashAdvances.php',
    'app/Services/AppraisalService.php',
    'app/Support/EmployeeDocumentPdfFactory.php',
    'app/Support/EmployeeDocumentRequestService.php',
    'app/Support/SystemBackupService.php',
    'app/Support/SystemMaintenanceActionService.php',
    'app/Support/SystemMaintenanceViewService.php',
    'app/Support/ImportExportRunService.php',
    'app/Support/ImportExportRunViewService.php',
    'app/Support/SpreadsheetInspectionService.php',
    'app/Support/UserPayslipService.php',
    'app/Support/UserCashAdvanceService.php',
    'app/Jobs/RunSystemBackup.php',
    'app/Jobs/ProcessUserImportRun.php',
    'app/Jobs/ProcessAttendanceImportRun.php',
    'app/Jobs/ProcessUserExportRun.php',
    'app/Jobs/ProcessAttendanceExportRun.php',
    'app/Jobs/ProcessActivityLogExportRun.php',
    'app/Jobs/ProcessMonthlyAttendanceReportRun.php',
    'app/Jobs/ProcessEmployeeDocumentUpload.php',
    'app/Console/Commands/RunScheduledBackups.php',
    'app/Http/Controllers/Admin/ImportExport/DownloadImportExportRunController.php',
    'app/Http/Controllers/User/EmployeeDocumentDownloadController.php',
    'app/Notifications/EmployeeDocumentRequestStatusUpdated.php',
    'app/Policies/EmployeeDocumentRequestPolicy.php',
];

$root = dirname(__DIR__);
$output = $root.'/enterprise_build';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output=')) {
        $output = substr($argument, strlen('--output='));
    }
}

$output = rtrim($output, DIRECTORY_SEPARATOR);

function enterpriseEnvKeyName(): string
{
    return base64_decode('RU5URVJQUklTRV9PQkZVU0NBVE9SX0tFWQ==');
}

function readEnterpriseEnvValue(string $key): ?string
{
    $value = getenv($key) ?: ($_ENV[$key] ?? $_SERVER[$key] ?? null);

    if (is_string($value) && trim($value) !== '') {
        return trim($value);
    }

    $envPath = dirname(__DIR__).'/.env';

    if (! is_file($envPath)) {
        return null;
    }

    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$name, $rawValue] = explode('=', $line, 2);

        if (trim($name) !== $key) {
            continue;
        }

        $rawValue = trim($rawValue);

        if (
            (str_starts_with($rawValue, '"') && str_ends_with($rawValue, '"'))
            || (str_starts_with($rawValue, "'") && str_ends_with($rawValue, "'"))
        ) {
            $rawValue = substr($rawValue, 1, -1);
        }

        return trim(str_replace('\n', PHP_EOL, $rawValue));
    }

    return null;
}

function enterpriseObfuscatorKey(): ?string
{
    $key = readEnterpriseEnvValue(enterpriseEnvKeyName());

    return is_string($key) && trim($key) !== '' ? trim($key) : null;
}

function encodeEnterpriseSource(string $source, ?string $key = null): string
{
    $source = preg_replace('/^<\?php\s*/', '', $source);

    if ($key !== null) {
        if (! extension_loaded('openssl')) {
            throw new RuntimeException('OpenSSL extension is required for salted enterprise obfuscation.');
        }

        $salt = random_bytes(16);
        $iv = random_bytes(16);
        $derivedKey = hash('sha256', trim($key)."\0".$salt, true);
        $encrypted = openssl_encrypt($source, 'aes-256-cbc', $derivedKey, OPENSSL_RAW_DATA, $iv);

        if (! is_string($encrypted)) {
            throw new RuntimeException('Could not encrypt enterprise source.');
        }

        $payload = base64_encode($salt.$iv.$encrypted);
        $envKeyName = base64_encode(enterpriseEnvKeyName());
        $decodingLogic = <<<PHP
\$payload = base64_decode('{$payload}', true);
\$keyName = base64_decode('{$envKeyName}');
\$secret = getenv(\$keyName) ?: (\$_ENV[\$keyName] ?? \$_SERVER[\$keyName] ?? '');
if (! is_string(\$payload) || \$payload === '' || ! is_string(\$secret) || trim(\$secret) === '') {
    throw new RuntimeException('Enterprise obfuscator key is missing.');
}
\$salt = substr(\$payload, 0, 16);
\$iv = substr(\$payload, 16, 16);
\$ciphertext = substr(\$payload, 32);
\$derived = hash('sha256', trim(\$secret)."\\0".\$salt, true);
\$source = openssl_decrypt(\$ciphertext, 'aes-256-cbc', \$derived, OPENSSL_RAW_DATA, \$iv);
if (! is_string(\$source) || trim(\$source) === '') {
    throw new RuntimeException('Enterprise source decryption failed.');
}
eval(\$source);
PHP;
    } else {
        $compressed = gzdeflate($source, 9);
        $encoded = base64_encode($compressed);
        $reversed = strrev($encoded);
        $hexed = bin2hex($reversed);

        $decodingLogic = <<<PHP
\$a = hex2bin('{$hexed}');
\$b = strrev(\$a);
\$c = base64_decode(\$b);
eval(gzinflate(\$c));
PHP;
    }

    $finalCompressed = gzdeflate($decodingLogic, 9);
    $finalEncoded = base64_encode($finalCompressed);

    return "<?php\n\n/**\n * Enterprise Core Secured\n * (c) RiprLutuk\n * Generated artifact. Do not edit manually.\n */\n".
        "eval(gzinflate(base64_decode('{$finalEncoded}')));\n";
}

$obfuscatorKey = enterpriseObfuscatorKey();
echo $obfuscatorKey === null
    ? "Mode: standard obfuscation\n"
    : "Mode: salted key obfuscation\n";
echo "Output: {$output}\n";

foreach ($filesToSecure as $relativePath) {
    $sourceFile = $root.'/'.$relativePath;
    $targetFile = $output.'/'.$relativePath;

    if (! is_file($sourceFile)) {
        echo "Skipped missing source: {$relativePath}\n";

        continue;
    }

    if (! is_dir(dirname($targetFile)) && ! mkdir(dirname($targetFile), 0755, true) && ! is_dir(dirname($targetFile))) {
        throw new RuntimeException('Could not create output directory: '.dirname($targetFile));
    }

    file_put_contents($targetFile, encodeEnterpriseSource((string) file_get_contents($sourceFile), $obfuscatorKey));
    echo "Secured: {$relativePath}\n";
}

echo "Enterprise artifacts generated without replacing source files.\n";
