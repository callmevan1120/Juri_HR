<?php

namespace App\Console\Commands;

use App\Support\FeatureMaturityMatrix;
use Illuminate\Console\Command;

class FeatureMaturityAudit extends Command
{
    protected $signature = 'feature:maturity {--json : Output machine-readable JSON}';

    protected $description = 'Report product feature maturity, evidence, and release-readiness gaps.';

    public function handle(FeatureMaturityMatrix $matrix): int
    {
        $summary = $matrix->summary();

        if ($this->option('json')) {
            $this->line((string) json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info('PasPapan Feature Maturity');
        $this->line('Overall score: '.$summary['score'].'/'.$summary['target']);
        $this->line('Status: '.($summary['ready'] ? 'release target met' : 'maturity work remains'));
        $this->newLine();

        $this->table(
            ['Module', 'Score', 'Status', 'Missing Evidence', 'Main Gap'],
            collect($summary['modules'])->map(fn (array $module): array => [
                $module['label'],
                $module['score'].'%',
                str((string) $module['status'])->headline()->toString(),
                count($module['missing_evidence']),
                $module['gaps'][0] ?? '-',
            ])->all(),
        );

        if ($summary['missing_evidence'] !== []) {
            $this->warn('Missing evidence:');
            foreach ($summary['missing_evidence'] as $path) {
                $this->line('- '.$path);
            }
        }

        return self::SUCCESS;
    }
}
