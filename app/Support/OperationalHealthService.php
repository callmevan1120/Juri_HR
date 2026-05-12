<?php

namespace App\Support;

use App\Helpers\Editions;
use App\Models\SystemBackupRun;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class OperationalHealthService
{
    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        $database = $this->databaseHealth();
        $backup = $this->backupHealth();
        $storageWritable = $this->storageWritable();

        return [
            'status' => $database['ok'] && $storageWritable ? 'ok' : 'attention',
            'app_version' => $this->appVersion(),
            'database' => $database,
            'failed_jobs_count' => $this->failedJobsCount(),
            'queue_heartbeat_at' => Cache::get('health:queue_heartbeat_at'),
            'scheduler_heartbeat_at' => Cache::get('health:scheduler_heartbeat_at') ?? Cache::get('scheduler:last_run'),
            'backup' => $backup,
            'backup_last_success_at' => $backup['last_success_at'],
            'storage_writable' => $storageWritable,
            'disk_free_bytes' => $this->diskFreeBytes(),
            'disk_free_human' => $this->formatBytes($this->diskFreeBytes()),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_connection' => config('queue.default'),
            'scheduler_last_run' => Cache::get('health:scheduler_heartbeat_at') ?? Cache::get('scheduler:last_run'),
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

    /**
     * @return array{last_success_at:mixed,file_present:bool,checksum_sha256:string|null,checksum_matches_meta:bool|null}
     */
    protected function backupHealth(): array
    {
        $backup = SystemBackupRun::query()
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();

        if (! $backup) {
            return [
                'last_success_at' => null,
                'file_present' => false,
                'checksum_sha256' => null,
                'checksum_matches_meta' => null,
            ];
        }

        $path = (string) $backup->file_path;
        $disk = $backup->file_disk ?: 'local';
        $absolutePath = $disk === 'local' && $path !== ''
            ? Storage::disk('local')->path($path)
            : null;
        $checksum = $absolutePath && is_file($absolutePath)
            ? hash_file('sha256', $absolutePath)
            : null;
        $storedChecksum = data_get($backup->meta, 'checksum_sha256');

        return [
            'last_success_at' => $backup->completed_at,
            'file_present' => $checksum !== null,
            'checksum_sha256' => $checksum,
            'checksum_matches_meta' => is_string($storedChecksum) && $checksum !== null
                ? hash_equals($storedChecksum, $checksum)
                : null,
        ];
    }

    protected function diskFreeBytes(): int
    {
        $free = @disk_free_space(storage_path('app'));

        return is_float($free) ? (int) $free : 0;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return number_format($bytes / (1024 ** $power), $power === 0 ? 0 : 1).' '.$units[$power];
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
