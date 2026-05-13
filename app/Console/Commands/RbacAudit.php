<?php

namespace App\Console\Commands;

use App\Support\RbacAuditService;
use Illuminate\Console\Command;

class RbacAudit extends Command
{
    protected $signature = 'rbac:audit {--json : Output machine-readable JSON}';

    protected $description = 'Report RBAC route, menu, policy, and role coverage gaps.';

    public function handle(RbacAuditService $audit): int
    {
        $report = $audit->report();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info('RBAC Audit Report');

        foreach ($report as $section => $items) {
            $this->newLine();
            $this->line(str($section)->replace('_', ' ')->title()->toString());

            if ($items === []) {
                $this->line('  OK');

                continue;
            }

            foreach ($items as $item) {
                $this->line('  - '.$item);
            }
        }

        return self::SUCCESS;
    }
}
