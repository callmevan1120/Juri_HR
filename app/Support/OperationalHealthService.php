<?php

namespace App\Support;

use App\Helpers\Editions;
use App\Models\SystemBackupRun;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OperationalHealthService
{
    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        $database = $this->databaseHealth();

        return [
            'status' => $database['ok'] && $this->storageWritable() ? 'ok' : 'attention',
            'app_version' => $this->appVersion(),
            'database' => $database,
            'failed_jobs_count' => $this->failedJobsCount(),
            'backup_last_success_at' => SystemBackupRun::query()
                ->where('status', 'completed')
                ->latest('completed_at')
                ->value('completed_at'),
            'storage_writable' => $this->storageWritable(),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_connection' => config('queue.default'),
            'scheduler_last_run' => Cache::get('scheduler:last_run'),
            'license' => [
                'payroll_locked' => Editions::payrollLocked(),
                'reporting_locked' => Editions::reportingLocked(),
                'system_backup_locked' => Editions::systemBackupLocked(),
            ],
            'realtime' => [
                'broadcast_connection' => config('broadcasting.default'),
                'reverb_enabled' => config('broadcasting.default') === 'reverb',
                'polling_fallback' => config('realtime.poll_interval', '60s'),
            ],
        ];
    }

    /**
     * @return array{ok:bool,latency_ms:float|null,error:string|null}
     */
    protected function databaseHealth(): array
    {
        $started = microtime(true);

        try {
            DB::select('select 1');

            return [
                'ok' => true,
                'latency_ms' => round((microtime(true) - $started) * 1000, 2),
                'error' => null,
            ];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'latency_ms' => null,
                'error' => 'Database connectivity check failed.',
            ];
        }
    }

    protected function failedJobsCount(): int
    {
        return Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->count()
            : 0;
    }

    protected function storageWritable(): bool
    {
        return is_writable(storage_path('app'));
    }

    protected function appVersion(): string
    {
        $packagePath = base_path('package.json');

        if (! is_file($packagePath)) {
            return (string) config('app.version', 'unknown');
        }

        $package = json_decode((string) file_get_contents($packagePath), true);

        return (string) ($package['version'] ?? config('app.version', 'unknown'));
    }
}
