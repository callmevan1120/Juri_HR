<?php

namespace App\Console\Commands;

use App\Jobs\RunSystemBackup;
use App\Models\Setting;
use App\Models\SystemBackupRun;
use App\Services\Enterprise\LicenseGuard;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class RunScheduledBackups extends Command
{
    protected $signature = 'maintenance:scheduled-backups {--force : Dispatch backup jobs regardless of the configured schedule window}';

    protected $description = 'Dispatch scheduled maintenance backups and prune expired retained artifacts';

    public function handle(): int
    {
        if (! LicenseGuard::hasRuntimeObfuscatorKey()) {
            $this->warn('Enterprise backup automation is locked because ENTERPRISE_OBFUSCATOR_KEY is not configured. Free/open-source runtime remains available.');

            return self::SUCCESS;
        }

        if (! Schema::hasTable('system_backup_runs')) {
            $this->warn('The system_backup_runs table is missing. Apply the latest migrations before enabling scheduled backups.');

            return self::SUCCESS;
        }

        $retentionDays = max(1, (int) Setting::getValue('maintenance.backup_retention_days', 14));
        $prunedCount = $this->pruneExpiredBackups($retentionDays);

        $enabled = (bool) Setting::getValue('maintenance.backup_schedule_enabled', 0);
        $force = (bool) $this->option('force');

        if (! $enabled && ! $force) {
            $this->info("Backup automation is disabled. Pruned {$prunedCount} expired backups.");

            return self::SUCCESS;
        }

        $type = (string) Setting::getValue('maintenance.backup_schedule_type', 'database');
        $frequency = (string) Setting::getValue('maintenance.backup_schedule_frequency', 'daily');
        $time = (string) Setting::getValue('maintenance.backup_schedule_time', '02:00');
        $day = (string) Setting::getValue('maintenance.backup_schedule_day', 'sunday');

        $slotKey = $force ? now()->format('Y-m-d H:i:s') : $this->dueSlotKey(now(), $frequency, $day, $time);

        if (! $force && $slotKey === null) {
            $this->info("No scheduled backup due right now. Pruned {$prunedCount} expired backups.");

            return self::SUCCESS;
        }

        $lastRunSlot = (string) Setting::getValue('maintenance.backup_schedule_last_run_slot', '');

        if (! $force && $lastRunSlot === $slotKey) {
            $this->info("Scheduled backup for slot {$slotKey} already dispatched.");

            return self::SUCCESS;
        }

        $dispatched = 0;

        foreach ($this->scheduledTypes($type) as $backupType) {
            $backupRun = SystemBackupRun::create([
                'type' => $backupType,
                'status' => 'queued',
                'requested_by_user_id' => null,
                'queue' => 'maintenance',
                'file_disk' => 'local',
                'meta' => [
                    'execution' => $force ? 'forced-schedule' : 'scheduled',
                    'scheduled_slot' => $slotKey,
                ],
            ]);

            RunSystemBackup::dispatch($backupRun->id)->onQueue('maintenance');
            $dispatched++;
        }

        if (! $force) {
            Setting::updateOrCreate(
                ['key' => 'maintenance.backup_schedule_last_run_slot'],
                ['value' => $slotKey, 'group' => 'maintenance', 'type' => 'text', 'description' => 'Last dispatched scheduled backup slot']
            );
            Setting::flushCache('maintenance.backup_schedule_last_run_slot');
        }

        $this->info("Dispatched {$dispatched} backup job(s). Pruned {$prunedCount} expired backups.");

        return self::SUCCESS;
    }

    private function dueSlotKey(Carbon $now, string $frequency, string $day, string $time): ?string
    {
        [$hour, $minute] = array_pad(explode(':', $time, 2), 2, '00');
        $slot = $now->copy()->setTime((int) $hour, (int) $minute, 0);

        if ($now->format('H:i') !== $slot->format('H:i')) {
            return null;
        }

        if ($frequency === 'weekly' && strtolower($now->englishDayOfWeek) !== strtolower($day)) {
            return null;
        }

        return $slot->format('Y-m-d H:i');
    }

    private function scheduledTypes(string $type): array
    {
        return match ($type) {
            'application' => ['application'],
            'both' => ['database', 'application'],
            default => ['database'],
        };
    }

    private function pruneExpiredBackups(int $retentionDays): int
    {
        $cutoff = now()->subDays($retentionDays);
        $runs = SystemBackupRun::query()
            ->where('status', 'completed')
            ->whereNull('deleted_at')
            ->whereNotNull('file_path')
            ->where('completed_at', '<', $cutoff)
            ->get();

        $pruned = 0;

        foreach ($runs as $run) {
            $disk = $run->file_disk ?? 'local';

            if (Storage::disk($disk)->exists($run->file_path)) {
                Storage::disk($disk)->delete($run->file_path);
            }

            $run->update([
                'status' => 'deleted',
                'deleted_at' => now(),
                'file_path' => null,
            ]);

            $pruned++;
        }

        return $pruned;
    }
}
