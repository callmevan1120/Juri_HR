<?php

namespace App\Console\Commands;

use App\Models\SystemBackupRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupRestoreDrill extends Command
{
    protected $signature = 'maintenance:backup-restore-drill {--backup-id= : Completed backup run id to verify}';

    protected $description = 'Print a safe backup restore drill checklist and verify backup artifact presence/checksum.';

    public function handle(): int
    {
        $backup = $this->resolveBackup();

        if (! $backup) {
            $this->error('No completed backup run found. Create a backup before running the drill.');

            return self::FAILURE;
        }

        $path = (string) $backup->file_path;
        $disk = $backup->file_disk ?: 'local';
        $present = $disk === 'local' && $path !== '' && Storage::disk('local')->exists($path);
        $checksum = $present ? hash_file('sha256', Storage::disk('local')->path($path)) : null;
        $recordedChecksum = data_get($backup->meta, 'checksum_sha256');

        $this->info('Backup restore drill');
        $this->line("Backup run: {$backup->id}");
        $this->line("File: {$backup->file_name}");
        $this->line('Artifact present: '.($present ? 'yes' : 'no'));
        $this->line('Checksum: '.($checksum ?: 'not available'));

        if (is_string($recordedChecksum)) {
            $this->line('Checksum matches metadata: '.($checksum !== null && hash_equals($recordedChecksum, $checksum) ? 'yes' : 'no'));
        }

        $this->newLine();
        $this->line('Drill steps:');
        $this->line('1. Restore only into staging or an isolated database.');
        $this->line('2. Verify APP_KEY and storage settings before restore.');
        $this->line('3. Run migrations/status checks after restore.');
        $this->line('4. Smoke test login, admin dashboard, attendance, payroll, and downloads.');
        $this->line('5. Record drill result, duration, and incident notes.');

        return $present ? self::SUCCESS : self::FAILURE;
    }

    private function resolveBackup(): ?SystemBackupRun
    {
        $backupId = $this->option('backup-id');

        if ($backupId) {
            return SystemBackupRun::query()
                ->where('status', 'completed')
                ->find((int) $backupId);
        }

        return SystemBackupRun::query()
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();
    }
}
